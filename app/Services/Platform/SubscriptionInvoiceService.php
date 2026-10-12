<?php

namespace App\Services\Platform;

use App\Models\Platform\Plan;
use App\Models\Platform\PlatformPaymentTransaction;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;
use App\Support\Billing\SubscriptionBilling;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Pembuatan invoice langganan dari alur PUBLIK (perpanjangan & upgrade).
 *
 * Dipakai bersama oleh controller (fallback tanpa JavaScript) dan komponen
 * Livewire, supaya aturan bisnis hanya hidup di satu tempat: pencocokan tenant,
 * pemakaian invoice pending yang sudah ada, perhitungan harga (termasuk prorata
 * upgrade), dan publikasi ke inbox platform.
 */
class SubscriptionInvoiceService
{
    public function __construct(
        private readonly PlatformInboxPublisher $inboxPublisher,
    ) {}

    public function findTenant(string $tenantCode): ?Tenant
    {
        $tenantCode = trim($tenantCode);

        if ($tenantCode === '') {
            return null;
        }

        return Tenant::query()
            ->with(['subscriptions.plan'])
            ->where('code', $tenantCode)
            ->first();
    }

    public function ownerEmailMatches(Tenant $tenant, string $ownerEmail): bool
    {
        return strtolower(trim((string) $tenant->owner_email)) === strtolower(trim($ownerEmail));
    }

    public function findExistingPendingInvoice(Tenant $tenant, string $type): ?PlatformPaymentTransaction
    {
        return PlatformPaymentTransaction::query()
            ->where('tenant_id', $tenant->getKey())
            ->where('type', $type)
            ->whereIn('status', ['pending_payment', 'waiting_validation'])
            ->latest('id')
            ->first();
    }

    /**
     * Paket yang boleh dipilih untuk upgrade: aktif, bukan paket saat ini,
     * dan harganya lebih tinggi.
     *
     * @return Collection<int, Plan>
     */
    public function availableUpgradePlans(?Subscription $subscription): Collection
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

    public function billingCycleLabel(string $billingCycle): string
    {
        return match ($billingCycle) {
            'quarterly' => '3 bulan',
            'yearly' => '12 bulan',
            default => '1 bulan',
        };
    }

    public function renewalAmount(Subscription $subscription, string $billingCycle): float
    {
        $multiplier = match ($billingCycle) {
            'quarterly' => 3,
            'yearly' => 12,
            default => 1,
        };

        return (float) ($subscription->plan?->price ?? 0) * $multiplier;
    }

    /**
     * @return array{amount:float, target:float, credit:float, resulting_expires_at:CarbonInterface}
     */
    public function upgradePricing(Plan $currentPlan, Plan $targetPlan, ?Subscription $subscription, string $billingCycle): array
    {
        $upgrade = SubscriptionBilling::upgradeAmount(
            (float) $currentPlan->price,
            (float) $targetPlan->price,
            $subscription?->expiresAt(),
            now(),
            $billingCycle,
        );

        return [
            'amount' => (float) $upgrade['amount'],
            'target' => (float) $upgrade['target'],
            'credit' => (float) $upgrade['credit'],
            'resulting_expires_at' => SubscriptionBilling::changeExpiresAt(now(), $billingCycle),
        ];
    }

    public function createRenewalInvoice(Tenant $tenant, Subscription $subscription, string $billingCycle, string $ownerEmail, ?string $notes): PlatformPaymentTransaction
    {
        $amount = $this->renewalAmount($subscription, $billingCycle);
        $termLabel = $this->billingCycleLabel($billingCycle);

        $transaction = PlatformPaymentTransaction::query()->create([
            'tenant_id' => $tenant->getKey(),
            'subscription_id' => $subscription->getKey(),
            'plan_id' => $subscription->plan->getKey(),
            'invoice_number' => $this->generateInvoiceNumber($tenant, 'INV-REN'),
            'type' => 'renewal',
            'payment_method' => 'manual_transfer',
            'payment_channel' => 'bank_transfer',
            'amount_subtotal' => $amount,
            'amount_discount' => 0,
            'amount_total' => $amount,
            'billing_cycle' => $billingCycle,
            'status' => 'pending_payment',
            'due_at' => now()->addDays(3),
            'notes' => filled($notes) ? (string) $notes : null,
            'meta_json' => [
                'created_via' => 'public_renewal',
                'requested_by_email' => strtolower(trim($ownerEmail)),
                'requested_term_label' => $termLabel,
                'selected_plan_id' => $subscription->plan->getKey(),
            ],
        ]);

        $this->inboxPublisher->publish(
            tenant: $tenant,
            eventKey: 'tenant.subscription.renewal_requested',
            title: 'Request perpanjangan baru',
            message: $tenant->name.' meminta perpanjangan '.$termLabel.' dan invoice siap ditindaklanjuti.',
            category: 'renewal',
            tone: 'emerald',
            payload: [
                'invoice_number' => $transaction->invoice_number,
                'requested_by_email' => strtolower(trim($ownerEmail)),
                'requested_term_label' => $termLabel,
                'billing_cycle' => $transaction->billing_cycle,
            ],
            occurredAt: now(),
            fingerprint: 'renewal.requested:'.$transaction->getKey(),
            sourceType: PlatformPaymentTransaction::class,
            sourceId: $transaction->getKey(),
        );

        $this->publishInvoiceCreated($tenant, $transaction, 'renewal');

        return $transaction;
    }

    public function createUpgradeInvoice(Tenant $tenant, Subscription $subscription, Plan $targetPlan, string $billingCycle, string $ownerEmail, ?string $notes): PlatformPaymentTransaction
    {
        $currentPlan = $subscription->plan;

        if (! $currentPlan) {
            throw new RuntimeException('Tenant ini belum memiliki paket aktif yang bisa di-upgrade.');
        }

        $pricing = $this->upgradePricing($currentPlan, $targetPlan, $subscription, $billingCycle);

        if ($pricing['amount'] <= 0) {
            throw new RuntimeException('Pilih paket yang lebih tinggi agar invoice upgrade bisa dibuat.');
        }

        $termLabel = $this->billingCycleLabel($billingCycle);

        $transaction = PlatformPaymentTransaction::query()->create([
            'tenant_id' => $tenant->getKey(),
            'subscription_id' => $subscription->getKey(),
            'plan_id' => $targetPlan->getKey(),
            'invoice_number' => $this->generateInvoiceNumber($tenant, 'INV-UPG'),
            'type' => 'upgrade',
            'payment_method' => 'manual_transfer',
            'payment_channel' => 'bank_transfer',
            'amount_subtotal' => $pricing['target'],
            'amount_discount' => $pricing['credit'],
            'amount_total' => $pricing['amount'],
            'billing_cycle' => $billingCycle,
            'status' => 'pending_payment',
            'due_at' => now()->addDays(3),
            'notes' => filled($notes) ? (string) $notes : null,
            'meta_json' => [
                'created_via' => 'public_upgrade',
                'pricing_mode' => 'plan_difference_prorata',
                'requested_by_email' => strtolower(trim($ownerEmail)),
                'requested_term_label' => $termLabel,
                'selected_plan_id' => $targetPlan->getKey(),
                'current_plan_id' => $currentPlan->getKey(),
                'upgrade_credit' => $pricing['credit'] > 0 ? $pricing['credit'] : null,
                'resulting_expires_at' => $pricing['resulting_expires_at']->toDateTimeString(),
            ],
        ]);

        $this->inboxPublisher->publish(
            tenant: $tenant,
            eventKey: 'tenant.subscription.upgrade_requested',
            title: 'Request upgrade paket baru',
            message: $tenant->name.' meminta upgrade ke paket '.$targetPlan->name.' untuk '.$termLabel.'.',
            category: 'renewal',
            tone: 'emerald',
            payload: [
                'invoice_number' => $transaction->invoice_number,
                'requested_by_email' => strtolower(trim($ownerEmail)),
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

        $this->publishInvoiceCreated($tenant, $transaction, 'upgrade');

        return $transaction;
    }

    private function publishInvoiceCreated(Tenant $tenant, PlatformPaymentTransaction $transaction, string $flow): void
    {
        $this->inboxPublisher->publish(
            tenant: $tenant,
            eventKey: 'tenant.payment.invoice_created',
            title: 'Invoice '.$flow.' dibuat',
            message: 'Invoice '.$transaction->invoice_number.' dibuat dari form publik '.$flow.'.',
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
    }

    private function generateInvoiceNumber(Tenant $tenant, string $prefix): string
    {
        do {
            $candidate = $prefix.'-'.strtoupper(Str::limit(Str::slug($tenant->code, ''), 6, '')).'-'.now()->format('YmdHis').'-'.random_int(100, 999);
        } while (PlatformPaymentTransaction::query()->where('invoice_number', $candidate)->exists());

        return $candidate;
    }
}
