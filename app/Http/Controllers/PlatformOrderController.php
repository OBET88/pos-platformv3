<?php

namespace App\Http\Controllers;

use App\Models\Platform\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class PlatformOrderController extends Controller
{
    public function create(Request $request): View
    {
        $tenantCode = trim((string) $request->query('tenant', ''));
        $ownerEmail = trim((string) $request->query('owner_email', ''));

        if ($tenantCode !== '' && $ownerEmail !== '') {
            return $this->renderLookupResult($tenantCode, $ownerEmail, true);
        }

        return view('platform.orders', [
            'tenant' => null,
            'transactions' => collect(),
            'tenantCode' => $tenantCode,
            'ownerEmail' => $ownerEmail,
            'lookupPerformed' => false,
        ]);
    }

    public function lookup(Request $request): View
    {
        $validated = $request->validate([
            'tenant_code' => ['required', 'string', 'max:255'],
            'owner_email' => ['required', 'email', 'max:255'],
        ]);

        return $this->renderLookupResult(
            trim((string) $validated['tenant_code']),
            trim((string) $validated['owner_email']),
            true,
        );
    }

    private function renderLookupResult(string $tenantCode, string $ownerEmail, bool $lookupPerformed): View
    {
        $tenant = Tenant::query()
            ->with([
                'subscriptions.plan',
                'paymentTransactions' => fn ($query) => $query
                    ->with(['plan', 'proofs'])
                    ->latest('id'),
            ])
            ->where('code', $tenantCode)
            ->first();

        if (!$tenant) {
            return view('platform.orders', [
                'tenant' => null,
                'transactions' => collect(),
                'tenantCode' => $tenantCode,
                'ownerEmail' => $ownerEmail,
                'lookupPerformed' => $lookupPerformed,
            ])->withErrors([
                'tenant_code' => 'Kode tenant tidak ditemukan.',
            ]);
        }

        if (strtolower(trim((string) $tenant->owner_email)) !== strtolower($ownerEmail)) {
            return view('platform.orders', [
                'tenant' => null,
                'transactions' => collect(),
                'tenantCode' => $tenantCode,
                'ownerEmail' => $ownerEmail,
                'lookupPerformed' => $lookupPerformed,
            ])->withErrors([
                'owner_email' => 'Email owner tidak cocok dengan tenant yang dipilih.',
            ]);
        }

        return view('platform.orders', [
            'tenant' => $tenant,
            'transactions' => $this->transformTransactions($tenant->paymentTransactions),
            'tenantCode' => $tenantCode,
            'ownerEmail' => $ownerEmail,
            'lookupPerformed' => $lookupPerformed,
        ]);
    }

    /**
     * @param  Collection<int, \App\Models\Platform\PlatformPaymentTransaction>  $transactions
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
