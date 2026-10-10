<?php

namespace App\Http\Controllers;

use App\Models\Platform\Plan;
use App\Models\Platform\PlatformPaymentTransaction;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;
use App\Services\Platform\PlatformInboxPublisher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PlatformUpgradeController extends Controller
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

        $subscription = $tenant?->latestSubscription();
        $plans = $this->availableUpgradePlans($subscription);

        return view('platform.upgrade-request', [
            'tenant' => $tenant,
            'tenantCode' => $tenantCode,
            'subscription' => $subscription,
            'plans' => $plans,
        ]);
    }

    public function store(Request $request, PlatformInboxPublisher $inboxPublisher): RedirectResponse
    {
        $validated = $request->validate([
            'tenant_code' => ['required', 'string', 'max:255'],
            'owner_email' => ['required', 'email', 'max:255'],
            'target_plan_id' => ['required', 'integer', Rule::exists('plans', 'id')],
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
        $currentPlan = $subscription?->plan;

        if (!$subscription || !$currentPlan) {
            return back()
                ->withErrors([
                    'tenant_code' => 'Tenant ini belum memiliki paket aktif yang bisa di-upgrade.',
                ])
                ->withInput();
        }

        $targetPlan = Plan::query()
            ->where('is_active', true)
            ->find((int) $validated['target_plan_id']);

        if (!$targetPlan) {
            return back()
                ->withErrors([
                    'target_plan_id' => 'Paket tujuan upgrade tidak tersedia.',
                ])
                ->withInput();
        }

        $amount = $this->resolveUpgradeAmount($currentPlan, $targetPlan, (string) $validated['billing_cycle']);

        if ($amount <= 0) {
            return back()
                ->withErrors([
                    'target_plan_id' => 'Pilih paket yang lebih tinggi agar invoice upgrade bisa dibuat.',
                ])
                ->withInput();
        }

        $existingInvoice = PlatformPaymentTransaction::query()
            ->where('tenant_id', $tenant->getKey())
            ->where('type', 'upgrade')
            ->whereIn('status', ['pending_payment', 'waiting_validation'])
            ->latest('id')
            ->first();

        if ($existingInvoice) {
            return redirect()
                ->route('platform.payment.show', ['invoiceNumber' => $existingInvoice->invoice_number])
                ->with('upgrade_existing_invoice', true);
        }

        $invoiceNumber = $this->generateInvoiceNumber($tenant);
        $termLabel = $this->billingCycleLabel((string) $validated['billing_cycle']);

        $transaction = PlatformPaymentTransaction::query()->create([
            'tenant_id' => $tenant->getKey(),
            'subscription_id' => $subscription->getKey(),
            'plan_id' => $targetPlan->getKey(),
            'invoice_number' => $invoiceNumber,
            'type' => 'upgrade',
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
                'created_via' => 'public_upgrade',
                'pricing_mode' => 'plan_difference',
                'requested_by_email' => strtolower(trim((string) $validated['owner_email'])),
                'requested_term_label' => $termLabel,
                'selected_plan_id' => $targetPlan->getKey(),
                'current_plan_id' => $currentPlan->getKey(),
            ],
        ]);

        $inboxPublisher->publish(
            tenant: $tenant,
            eventKey: 'tenant.subscription.upgrade_requested',
            title: 'Request upgrade paket baru',
            message: $tenant->name.' meminta upgrade ke paket '.$targetPlan->name.' untuk '.$termLabel.'.',
            category: 'renewal',
            tone: 'emerald',
            payload: [
                'invoice_number' => $transaction->invoice_number,
                'requested_by_email' => strtolower(trim((string) $validated['owner_email'])),
                'requested_term_label' => $termLabel,
                'billing_cycle' => $transaction->billing_cycle,
                'selected_plan_id' => $targetPlan->getKey(),
                'selected_plan_name' => $targetPlan->name,
                'current_plan_id' => $currentPlan->getKey(),
                'current_plan_name' => $currentPlan->name,
            ],
            occurredAt: now(),
            fingerprint: 'upgrade.requested:'.$transaction->getKey(),
            sourceType: PlatformPaymentTransaction::class,
            sourceId: $transaction->getKey(),
        );

        $inboxPublisher->publish(
            tenant: $tenant,
            eventKey: 'tenant.payment.invoice_created',
            title: 'Invoice upgrade dibuat',
            message: 'Invoice '.$transaction->invoice_number.' dibuat dari form publik upgrade.',
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

    /**
     * @return Collection<int, Plan>
     */
    private function availableUpgradePlans(?Subscription $subscription): Collection
    {
        $currentPlanPrice = (float) ($subscription?->plan?->price ?? 0);

        return Plan::query()
            ->where('is_active', true)
            ->when(
                $subscription?->plan_id,
                fn ($query, $planId) => $query->where('id', '!=', $planId)
            )
            ->orderBy('price')
            ->get()
            ->filter(fn (Plan $plan) => (float) $plan->price > $currentPlanPrice)
            ->values();
    }

    private function resolveUpgradeAmount(Plan $currentPlan, Plan $targetPlan, string $billingCycle): float
    {
        $multiplier = match ($billingCycle) {
            'quarterly' => 3,
            'yearly' => 12,
            default => 1,
        };

        $targetAmount = (float) $targetPlan->price * $multiplier;
        $currentAmount = (float) $currentPlan->price * $multiplier;

        return max(0, $targetAmount - $currentAmount);
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
            $candidate = 'INV-UPG-'.strtoupper(Str::limit(Str::slug($tenant->code, ''), 6, '')).'-'.now()->format('YmdHis').'-'.random_int(100, 999);
        } while (PlatformPaymentTransaction::query()->where('invoice_number', $candidate)->exists());

        return $candidate;
    }
}
