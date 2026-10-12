<?php

namespace App\Livewire\Platform\Public;

use App\Services\Platform\SubscriptionInvoiceService;
use Livewire\Component;

/**
 * Form publik perpanjangan langganan (Livewire).
 *
 * Menampilkan ringkasan tenant begitu kode tenant cocok, lalu membuat invoice
 * renewal dan mengarahkan ke halaman pembayaran.
 */
class RenewalRequest extends Component
{
    public string $tenantCode = '';

    public string $ownerEmail = '';

    public string $billingCycle = 'monthly';

    public string $notes = '';

    /** Kode tenant terakhir yang berhasil dicocokkan (untuk ringkasan). */
    public ?int $resolvedTenantId = null;

    public function mount(string $tenantCode = ''): void
    {
        $this->tenantCode = trim($tenantCode);

        $this->syncTenantSummary();
    }

    /**
     * Saat kode tenant diketik, perbarui ringkasan tenant. Email owner ikut
     * terisi otomatis bila masih kosong agar pelanggan tidak salah ketik.
     */
    public function updatedTenantCode(): void
    {
        $this->syncTenantSummary(prefillOwnerEmail: true);
    }

    public function submit(SubscriptionInvoiceService $invoices): void
    {
        $validated = $this->validate([
            'tenantCode' => ['required', 'string', 'max:255'],
            'ownerEmail' => ['required', 'email', 'max:255'],
            'billingCycle' => ['required', 'in:monthly,quarterly,yearly'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'tenantCode.required' => 'Kode tenant wajib diisi.',
            'ownerEmail.required' => 'Email owner wajib diisi.',
            'ownerEmail.email' => 'Format email owner tidak valid.',
            'billingCycle.required' => 'Siklus renewal wajib dipilih.',
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
            $this->addError('tenantCode', 'Tenant ini belum memiliki paket aktif yang bisa diperpanjang.');

            return;
        }

        $existingInvoice = $invoices->findExistingPendingInvoice($tenant, 'renewal');

        if ($existingInvoice) {
            $this->redirectRoute('platform.payment.show', [
                'invoiceNumber' => $existingInvoice->invoice_number,
            ], navigate: true);

            return;
        }

        $transaction = $invoices->createRenewalInvoice(
            $tenant,
            $subscription,
            (string) $validated['billingCycle'],
            (string) $validated['ownerEmail'],
            $validated['notes'] !== '' ? (string) $validated['notes'] : null,
        );

        $this->redirectRoute('platform.payment.show', [
            'invoiceNumber' => $transaction->invoice_number,
        ], navigate: true);
    }

    private function syncTenantSummary(bool $prefillOwnerEmail = false): void
    {
        $tenant = app(SubscriptionInvoiceService::class)->findTenant($this->tenantCode);

        $this->resolvedTenantId = $tenant?->getKey();

        if ($prefillOwnerEmail && $tenant && trim($this->ownerEmail) === '') {
            $this->ownerEmail = (string) $tenant->owner_email;
        }
    }

    public function render(SubscriptionInvoiceService $invoices)
    {
        $tenant = $this->resolvedTenantId
            ? $invoices->findTenant($this->tenantCode)
            : null;

        return view('livewire.platform.public.renewal-request', [
            'tenant' => $tenant,
            'subscription' => $tenant?->latestSubscription(),
            'plan' => $tenant?->latestSubscription()?->plan,
        ]);
    }
}
