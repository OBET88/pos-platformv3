<?php

namespace App\Livewire\Platform\Admin;

use App\Models\Platform\InboxItem;
use App\Models\Platform\PlatformPaymentTransaction;
use App\Services\Platform\PaymentActivationService;
use App\Services\Platform\PlatformInboxPublisher;
use App\Services\Platform\TenantAuditLogger;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.platform-admin')]
class PaymentValidationManager extends Component
{
    use WithPagination;

    public array $rejectConfirmations = [];

    #[Url(as: 'status', except: 'waiting_validation')]
    public string $statusFilter = 'waiting_validation';

    #[Url(as: 'method', except: 'all')]
    public string $methodFilter = 'all';

    #[Url(as: 'q', except: '')]
    public string $search = '';

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingMethodFilter(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function approvePayment(int $paymentTransactionId, PaymentActivationService $paymentActivationService): void
    {
        $transaction = PlatformPaymentTransaction::query()
            ->with(['tenant', 'proofs'])
            ->findOrFail($paymentTransactionId);

        if (in_array($transaction->status, ['paid', 'cancelled', 'refunded', 'expired'], true)) {
            session()->flash('platform_admin_error', 'Invoice ini tidak bisa divalidasi lagi.');

            return;
        }

        $transaction = $paymentActivationService->markAsPaid($transaction, now(), [
            'approved_via' => 'payment_validation_manager',
            'approved_by_email' => auth()->user()?->email,
        ]);

        $transaction->proofs()
            ->where('review_status', 'pending')
            ->update([
                'review_status' => 'approved',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'review_notes' => 'Bukti bayar divalidasi dari pusat validasi pembayaran.',
            ]);

        session()->flash('platform_admin_message', 'Pembayaran invoice '.$transaction->invoice_number.' berhasil divalidasi.');
    }

    public function rejectPayment(
        int $paymentTransactionId,
        TenantAuditLogger $auditLogger,
        PlatformInboxPublisher $inboxPublisher
    ): void {
        $transaction = PlatformPaymentTransaction::query()
            ->with(['tenant', 'proofs'])
            ->findOrFail($paymentTransactionId);

        if (in_array($transaction->status, ['paid', 'cancelled', 'refunded', 'expired'], true)) {
            $this->rejectConfirmations[$paymentTransactionId] = false;
            session()->flash('platform_admin_error', 'Invoice ini tidak bisa ditolak lagi.');

            return;
        }

        if (! ($this->rejectConfirmations[$paymentTransactionId] ?? false)) {
            $this->rejectConfirmations[$paymentTransactionId] = true;

            session()->flash(
                'platform_admin_error',
                'Reject pembayaran akan mengembalikan invoice ke `pending payment` dan meminta tenant upload bukti baru. Klik sekali lagi untuk konfirmasi.'
            );

            return;
        }

        $previousStatus = $transaction->status;

        $transaction->forceFill([
            'status' => 'pending_payment',
            'meta_json' => array_filter([
                ...($transaction->meta_json ?? []),
                'payment_rejected_at' => now()->toISOString(),
                'payment_rejected_by' => auth()->user()?->email,
            ], fn ($value) => $value !== null && $value !== ''),
        ])->save();

        $transaction->proofs()
            ->where('review_status', 'pending')
            ->update([
                'review_status' => 'rejected',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'review_notes' => 'Bukti bayar ditolak dari pusat validasi pembayaran. Silakan upload ulang bukti yang benar.',
            ]);

        InboxItem::query()
            ->where('event_key', 'tenant.payment.awaiting_validation')
            ->where('source_type', PlatformPaymentTransaction::class)
            ->where('source_id', $transaction->getKey())
            ->delete();

        $auditLogger->log(
            $transaction->tenant,
            'tenant.payment.rejected',
            'Bukti pembayaran ditolak dan invoice dikembalikan ke status pending payment.',
            [
                'before' => [
                    'status' => $previousStatus,
                ],
                'after' => [
                    'status' => $transaction->status,
                    'invoice_number' => $transaction->invoice_number,
                ],
            ]
        );

        $inboxPublisher->publish(
            tenant: $transaction->tenant,
            eventKey: 'tenant.payment.rejected',
            title: 'Pembayaran perlu dicek ulang',
            message: 'Invoice '.$transaction->invoice_number.' perlu bukti bayar baru atau pengecekan ulang.',
            category: 'renewal',
            tone: 'rose',
            payload: [
                'payment_transaction_id' => $transaction->getKey(),
                'invoice_number' => $transaction->invoice_number,
            ],
            occurredAt: now(),
            fingerprint: 'payment.rejected:'.$transaction->getKey().':'.now()->format('YmdHis'),
            sourceType: PlatformPaymentTransaction::class,
            sourceId: $transaction->getKey(),
        );

        $this->rejectConfirmations[$paymentTransactionId] = false;
        session()->flash('platform_admin_message', 'Invoice '.$transaction->invoice_number.' dikembalikan ke pending payment.');
    }

    public function render()
    {
        $query = PlatformPaymentTransaction::query()
            ->with(['tenant', 'plan', 'proofs' => fn ($query) => $query->latest('id')])
            ->when($this->statusFilter !== 'all', fn ($query) => $query->where('status', $this->statusFilter))
            ->when($this->methodFilter !== 'all', fn ($query) => $query->where('payment_method', $this->methodFilter))
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($inner): void {
                    $inner->where('invoice_number', 'like', '%'.$this->search.'%')
                        ->orWhere('provider_reference', 'like', '%'.$this->search.'%')
                        ->orWhereHas('tenant', function ($tenantQuery): void {
                            $tenantQuery->where('name', 'like', '%'.$this->search.'%')
                                ->orWhere('code', 'like', '%'.$this->search.'%')
                                ->orWhere('owner_email', 'like', '%'.$this->search.'%');
                        });
                });
            })
            ->latest('id');

        return view('livewire.platform.admin.payment-validation-manager', [
            'payments' => $query->paginate(12),
            'stats' => [
                'waiting_validation' => PlatformPaymentTransaction::query()->where('status', 'waiting_validation')->count(),
                'pending_payment' => PlatformPaymentTransaction::query()->where('status', 'pending_payment')->count(),
                'paid_today' => PlatformPaymentTransaction::query()->where('status', 'paid')->whereDate('paid_at', today())->count(),
                'manual_transfer' => PlatformPaymentTransaction::query()->where('payment_method', 'manual_transfer')->count(),
            ],
            'title' => 'Platform Admin - Validasi Pembayaran',
        ]);
    }
}
