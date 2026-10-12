<?php

namespace App\Livewire\Platform\Public;

use App\Models\Platform\PlatformPaymentTransaction;
use App\Models\Platform\Tenant;
use Illuminate\Support\Collection;
use Livewire\Component;

/**
 * Halaman publik "Pesanan Saya": cari invoice berdasarkan kode tenant + email
 * owner. Sebelumnya form POST biasa; sekarang Livewire agar validasi, error,
 * dan hasil tampil tanpa reload halaman.
 */
class OrderLookup extends Component
{
    public string $tenantCode = '';

    public string $ownerEmail = '';

    public bool $lookupPerformed = false;

    public ?int $tenantId = null;

    public function mount(string $tenantCode = '', string $ownerEmail = ''): void
    {
        $this->tenantCode = trim($tenantCode);
        $this->ownerEmail = trim($ownerEmail);

        if ($this->tenantCode !== '' && $this->ownerEmail !== '') {
            $this->lookup();
        }
    }

    public function lookup(): void
    {
        $this->validate([
            'tenantCode' => ['required', 'string', 'max:255'],
            'ownerEmail' => ['required', 'email', 'max:255'],
        ], [
            'tenantCode.required' => 'Kode tenant wajib diisi.',
            'ownerEmail.required' => 'Email owner wajib diisi.',
            'ownerEmail.email' => 'Format email owner tidak valid.',
        ]);

        $this->lookupPerformed = true;
        $this->tenantId = null;

        $tenant = Tenant::query()
            ->where('code', trim($this->tenantCode))
            ->first();

        if (! $tenant) {
            $this->addError('tenantCode', 'Kode tenant tidak ditemukan.');

            return;
        }

        if (strtolower(trim((string) $tenant->owner_email)) !== strtolower(trim($this->ownerEmail))) {
            $this->addError('ownerEmail', 'Email owner tidak cocok dengan tenant yang dipilih.');

            return;
        }

        $this->tenantId = (int) $tenant->id;
        $this->resetErrorBag();
    }

    public function render()
    {
        $tenant = $this->resolveTenant();

        return view('livewire.platform.public.order-lookup', [
            'tenant' => $tenant,
            'subscription' => $tenant?->latestSubscription(),
            'transactions' => $tenant
                ? $this->transformTransactions($tenant->paymentTransactions)
                : collect(),
        ]);
    }

    private function resolveTenant(): ?Tenant
    {
        if (! $this->tenantId) {
            return null;
        }

        return Tenant::query()
            ->with([
                'subscriptions.plan',
                'paymentTransactions' => fn ($query) => $query
                    ->with(['plan', 'proofs'])
                    ->latest('id'),
            ])
            ->find($this->tenantId);
    }

    /**
     * @param  Collection<int, PlatformPaymentTransaction>  $transactions
     * @return Collection<int, array<string, mixed>>
     */
    private function transformTransactions(Collection $transactions): Collection
    {
        return $transactions->map(function ($transaction): array {
            $latestProof = $transaction->proofs->sortByDesc('id')->first();
            $isRejected = $latestProof?->review_status === 'rejected';
            $isWaitingValidation = $transaction->status === 'waiting_validation' && ! $isRejected;

            return [
                'invoice_number' => $transaction->invoice_number,
                'type' => $transaction->type,
                'status' => $transaction->status,
                'plan_name' => $transaction->plan?->name ?? '-',
                'amount_total' => (float) $transaction->amount_total,
                'billing_cycle' => (string) $transaction->billing_cycle,
                'due_at' => $transaction->due_at,
                'paid_at' => $transaction->paid_at,
                'created_at' => $transaction->created_at,
                'latest_proof_status' => $latestProof?->review_status,
                'latest_proof_notes' => $latestProof?->review_notes,
                'invoice_url' => route('platform.payment.pdf', ['invoiceNumber' => $transaction->invoice_number]),
                'confirmation_url' => route('platform.payment.show', ['invoiceNumber' => $transaction->invoice_number]),
                'can_open_confirmation' => ! $isWaitingValidation,
                'action_label' => $transaction->status === 'paid'
                    ? 'Lihat Invoice'
                    : ($isRejected ? 'Upload Ulang Bukti' : 'Konfirmasi Pembayaran'),
            ];
        })->values();
    }
}
