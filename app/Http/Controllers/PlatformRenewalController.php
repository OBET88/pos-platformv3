<?php

namespace App\Http\Controllers;

use App\Models\Platform\Plan;
use App\Models\Platform\PlatformPaymentTransaction;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;
use App\Services\Platform\PlatformInboxPublisher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PlatformRenewalController extends Controller
{
    public function create(Request $request): View
    {
        $tenantCode = trim((string) $request->query('tenant', ''));
        $tenant = $tenantCode !== ''
            ? Tenant::query()
                ->with(['subscriptions.plan'])
                ->where('code', $tenantCode)
                ->first()
            : null;

        return view('platform.renewal-request', [
            'tenant' => $tenant,
            'tenantCode' => $tenantCode,
        ]);
    }

    public function store(Request $request, PlatformInboxPublisher $inboxPublisher): RedirectResponse
    {
        $validated = $request->validate([
            'tenant_code' => ['required', 'string', 'max:255'],
            'owner_email' => ['required', 'email', 'max:255'],
            'billing_cycle' => ['required', 'in:monthly,quarterly,yearly'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $tenant = Tenant::query()
            ->with(['subscriptions.plan'])
            ->where('code', trim((string) $validated['tenant_code']))
            ->first();

        if (!$tenant) {
            return back()
                ->withErrors([
                    'tenant_code' => 'Kode tenant tidak ditemukan.',
                ])
                ->withInput();
        }

        if (strtolower(trim((string) $tenant->owner_email)) !== strtolower(trim((string) $validated['owner_email']))) {
            return back()
                ->withErrors([
                    'owner_email' => 'Email owner tidak cocok dengan tenant yang dipilih.',
                ])
                ->withInput();
        }

        $subscription = $tenant->latestSubscription();

        if (!$subscription || !$subscription->plan) {
            return back()
                ->withErrors([
                    'tenant_code' => 'Tenant ini belum memiliki paket aktif yang bisa diperpanjang.',
                ])
                ->withInput();
        }

        $existingInvoice = PlatformPaymentTransaction::query()
            ->where('tenant_id', $tenant->getKey())
            ->where('type', 'renewal')
            ->whereIn('status', ['pending_payment', 'waiting_validation'])
            ->latest('id')
            ->first();

        if ($existingInvoice) {
            return redirect()
                ->route('platform.payment.show', ['invoiceNumber' => $existingInvoice->invoice_number])
                ->with('renewal_existing_invoice', true);
        }

        $amount = $this->resolveRenewalAmount($subscription, (string) $validated['billing_cycle']);
        $invoiceNumber = $this->generateInvoiceNumber($tenant);
        $termLabel = $this->billingCycleLabel((string) $validated['billing_cycle']);

        $transaction = PlatformPaymentTransaction::query()->create([
            'tenant_id' => $tenant->getKey(),
            'subscription_id' => $subscription->getKey(),
            'plan_id' => $subscription->plan->getKey(),
            'invoice_number' => $invoiceNumber,
            'type' => 'renewal',
            'payment_method' => 'manual_transfer',
            'payment_channel' => 'bank_transfer',
            'amount_subtotal' => $amount,
            'amount_discount' => 0,
            'amount_total' => $amount,
            'billing_cycle' => (string) $validated['billing_cycle'],
            'status' => 'pending_payment',
            'due_at' => now()->addDays(3),
            'notes' => filled($validated['notes'] ?? null) ? (string) $validated['notes'] : null,
            'meta_json' => [
                'created_via' => 'public_renewal',
                'requested_by_email' => strtolower(trim((string) $validated['owner_email'])),
                'requested_term_label' => $termLabel,
                'selected_plan_id' => $subscription->plan->getKey(),
            ],
        ]);

        $inboxPublisher->publish(
            tenant: $tenant,
            eventKey: 'tenant.subscription.renewal_requested',
            title: 'Request perpanjangan baru',
            message: $tenant->name.' meminta perpanjangan '.$termLabel.' dan invoice siap ditindaklanjuti.',
            category: 'renewal',
            tone: 'emerald',
            payload: [
                'invoice_number' => $transaction->invoice_number,
                'requested_by_email' => strtolower(trim((string) $validated['owner_email'])),
                'requested_term_label' => $termLabel,
                'billing_cycle' => $transaction->billing_cycle,
            ],
            occurredAt: now(),
            fingerprint: 'renewal.requested:'.$transaction->getKey(),
            sourceType: PlatformPaymentTransaction::class,
            sourceId: $transaction->getKey(),
        );

        $inboxPublisher->publish(
            tenant: $tenant,
            eventKey: 'tenant.payment.invoice_created',
            title: 'Invoice renewal dibuat',
            message: 'Invoice '.$transaction->invoice_number.' dibuat dari form publik renewal.',
            category: 'renewal',
            tone: 'sky',
            payload: [
                'payment_transaction_id' => $transaction->getKey(),
                'invoice_number' => $transaction->invoice_number,
                'payment_type' => $transaction->type,
                'billing_cycle' => $transaction->billing_cycle,
                'amount_total' => $transaction->amount_total,
                'status' => $transaction->status,
            ],
            occurredAt: now(),
            fingerprint: 'payment.invoice_created:'.$transaction->getKey(),
            sourceType: PlatformPaymentTransaction::class,
            sourceId: $transaction->getKey(),
        );

        return redirect()->route('platform.payment.show', ['invoiceNumber' => $transaction->invoice_number]);
    }

    private function resolveRenewalAmount(Subscription $subscription, string $billingCycle): float
    {
        $multiplier = match ($billingCycle) {
            'quarterly' => 3,
            'yearly' => 12,
            default => 1,
        };

        return (float) ($subscription->plan?->price ?? 0) * $multiplier;
    }

    private function billingCycleLabel(string $billingCycle): string
    {
        return match ($billingCycle) {
            'quarterly' => '3 bulan',
            'yearly' => '12 bulan',
            default => '1 bulan',
        };
    }

    private function generateInvoiceNumber(Tenant $tenant): string
    {
        do {
            $candidate = 'INV-REN-'.strtoupper(Str::limit(Str::slug($tenant->code, ''), 6, '')).'-'.now()->format('YmdHis').'-'.random_int(100, 999);
        } while (PlatformPaymentTransaction::query()->where('invoice_number', $candidate)->exists());

        return $candidate;
    }
}
