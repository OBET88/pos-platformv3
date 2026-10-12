<?php

namespace App\Livewire\Platform\Public;

use App\Models\Platform\Plan;
use App\Models\Platform\Tenant;
use App\Services\Platform\SubscriptionInvoiceService;
use App\Support\Billing\SubscriptionBilling;
use Illuminate\Support\Collection;
use Livewire\Component;

/**
 * Form publik upgrade paket (Livewire).
 *
 * Estimasi tagihan dihitung di server memakai `SubscriptionBilling` yang sama
 * dengan pembuatan invoice, jadi angka pada pratinjau selalu sama dengan
 * nominal invoice (sebelumnya dihitung ulang di JavaScript sehingga bisa beda).
 */
class UpgradeRequest extends Component
{
    public string $tenantCode = '';

    public string $ownerEmail = '';

    public string $targetPlanId = '';

    public string $billingCycle = 'monthly';

    public string $notes = '';

    public ?int $resolvedTenantId = null;

    public function mount(string $tenantCode = '', string $plan = '', string $billingCycle = ''): void
    {
        $this->tenantCode = trim($tenantCode);

        if ($billingCycle !== '') {
            $this->billingCycle = $billingCycle;
        }

        $this->syncTenantSummary();

        if ($plan !== '') {
            $this->targetPlanId = (string) $plan;
        } else {
            $this->targetPlanId = (string) ($this->availablePlans()->first()?->getKey() ?? '');
        }
    }

    public function updatedTenantCode(): void
    {
        $this->syncTenantSummary(prefillOwnerEmail: true);
        $this->targetPlanId = (string) ($this->availablePlans()->first()?->getKey() ?? '');
    }

    public function submit(SubscriptionInvoiceService $invoices): void
    {
        $validated = $this->validate([
            'tenantCode' => ['required', 'string', 'max:255'],
            'ownerEmail' => ['required', 'email', 'max:255'],
            'targetPlanId' => ['required', 'integer'],
            'billingCycle' => ['required', 'in:monthly,quarterly,yearly'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'tenantCode.required' => 'Kode tenant wajib diisi.',
            'ownerEmail.required' => 'Email owner wajib diisi.',
            'ownerEmail.email' => 'Format email owner tidak valid.',
            'targetPlanId.required' => 'Pilih paket tujuan upgrade.',
            'billingCycle.required' => 'Siklus billing wajib dipilih.',
        ]);

        $tenant = $invoices->findTenant((string) $validated['tenantCode']);

        if (! $tenant) {
            $this->addError('tenantCode', 'Kode tenant tidak ditemukan.');

            return;
        }

        if (! $invoices->ownerEmailMatches($tenant, (string) $validated['ownerEmail'])) {
            $this->addError('ownerEmail', 'Email owner tidak cocok dengan tenant yang dipilih.');

            return;
        }

        $subscription = $tenant->latestSubscription();

        if (! $subscription || ! $subscription->plan) {
            $this->addError('tenantCode', 'Tenant ini belum memiliki paket aktif yang bisa di-upgrade.');

            return;
        }

        $targetPlan = Plan::query()
            ->where('is_active', true)
            ->find((int) $validated['targetPlanId']);

        if (! $targetPlan) {
            $this->addError('targetPlanId', 'Paket tujuan upgrade tidak tersedia.');

            return;
        }

        $existingInvoice = $invoices->findExistingPendingInvoice($tenant, 'upgrade');

        if ($existingInvoice) {
            $this->redirectRoute('platform.payment.show', [
                'invoiceNumber' => $existingInvoice->invoice_number,
            ], navigate: true);

            return;
        }

        try {
            $transaction = $invoices->createUpgradeInvoice(
                $tenant,
                $subscription,
                $targetPlan,
                (string) $validated['billingCycle'],
                (string) $validated['ownerEmail'],
                $validated['notes'] !== '' ? (string) $validated['notes'] : null,
            );
        } catch (\RuntimeException $exception) {
            $this->addError('targetPlanId', $exception->getMessage());

            return;
        }

        $this->redirectRoute('platform.payment.show', [
            'invoiceNumber' => $transaction->invoice_number,
        ], navigate: true);
    }

    /**
     * @return Collection<int, Plan>
     */
    public function availablePlans(): Collection
    {
        $tenant = $this->resolveTenant();

        return app(SubscriptionInvoiceService::class)
            ->availableUpgradePlans($tenant?->latestSubscription());
    }

    /**
     * Pratinjau tagihan upgrade, dihitung dengan rumus yang sama seperti invoice.
     *
     * @return array{amount:float, target:float, credit:float, label:string, expires_at:?string}|null
     */
    public function preview(): ?array
    {
        $tenant = $this->resolveTenant();
        $subscription = $tenant?->latestSubscription();
        $currentPlan = $subscription?->plan;

        if (! $currentPlan || $this->targetPlanId === '') {
            return null;
        }

        $targetPlan = $this->availablePlans()->firstWhere('id', (int) $this->targetPlanId);

        if (! $targetPlan) {
            return null;
        }

        $pricing = app(SubscriptionInvoiceService::class)
            ->upgradePricing($currentPlan, $targetPlan, $subscription, $this->billingCycle);

        return [
            'amount' => $pricing['amount'],
            'target' => $pricing['target'],
            'credit' => $pricing['credit'],
            'label' => app(SubscriptionInvoiceService::class)->billingCycleLabel($this->billingCycle),
            'expires_at' => $pricing['resulting_expires_at']->format('d M Y'),
        ];
    }

    public function remainingDays(): int
    {
        $tenant = $this->resolveTenant();

        return SubscriptionBilling::remainingDays($tenant?->latestSubscription()?->expiresAt(), now());
    }

    private function resolveTenant(): ?Tenant
    {
        if (! $this->resolvedTenantId) {
            return null;
        }

        return app(SubscriptionInvoiceService::class)->findTenant($this->tenantCode);
    }

    private function syncTenantSummary(bool $prefillOwnerEmail = false): void
    {
        $tenant = app(SubscriptionInvoiceService::class)->findTenant($this->tenantCode);

        $this->resolvedTenantId = $tenant?->getKey();

        if ($prefillOwnerEmail && $tenant && trim($this->ownerEmail) === '') {
            $this->ownerEmail = (string) $tenant->owner_email;
        }
    }

    public function render()
    {
        $tenant = $this->resolveTenant();

        return view('livewire.platform.public.upgrade-request', [
            'tenant' => $tenant,
            'subscription' => $tenant?->latestSubscription(),
            'currentPlan' => $tenant?->latestSubscription()?->plan,
            'plans' => $this->availablePlans(),
            'preview' => $this->preview(),
            'remainingDays' => $this->remainingDays(),
        ]);
    }
}
