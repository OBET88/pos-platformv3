<?php

namespace App\Services\Platform;

use App\Models\Platform\PlatformPaymentTransaction;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

class PaymentActivationService
{
    public function __construct(
        private readonly TenantAuditLogger $auditLogger,
        private readonly PlatformInboxPublisher $inboxPublisher,
        private readonly TenantRegistrationService $tenantRegistrationService,
        private readonly TenantOnboardingDispatcher $onboardingDispatcher,
    ) {}

    public function markAsPaid(
        PlatformPaymentTransaction $transaction,
        ?Carbon $paidAt = null,
        array $meta = [],
        bool $activateSubscription = true
    ): PlatformPaymentTransaction {
        $paidAt ??= now();
        $wasAlreadyPaid = false;

        $paidTransaction = DB::transaction(function () use ($transaction, $paidAt, $meta, $activateSubscription, &$wasAlreadyPaid): PlatformPaymentTransaction {
            /** @var PlatformPaymentTransaction $transaction */
            $transaction = PlatformPaymentTransaction::query()
                ->with(['tenant.subscriptions.plan', 'subscription.plan', 'plan'])
                ->lockForUpdate()
                ->findOrFail($transaction->getKey());

            if (in_array($transaction->status, ['cancelled', 'refunded'], true)) {
                throw new InvalidArgumentException('Transaksi pembayaran yang sudah dibatalkan atau direfund tidak bisa diaktifkan.');
            }

            if ($transaction->isPaid()) {
                $wasAlreadyPaid = true;

                return $transaction;
            }

            $before = [
                'status' => $transaction->status,
                'paid_at' => optional($transaction->paid_at)->toISOString(),
                'expires_at' => optional($transaction->expires_at)->toISOString(),
            ];

            $transaction->forceFill([
                'status' => 'paid',
                'paid_at' => $paidAt,
                'meta_json' => $this->mergeMeta($transaction->meta_json, $meta),
            ])->save();

            $subscription = $activateSubscription
                ? $this->activateSubscriptionForTransaction($transaction, $paidAt)
                : null;

            $tenant = $transaction->tenant;

            if ($tenant instanceof Tenant) {
                $this->auditLogger->log(
                    $tenant,
                    'tenant.payment.approved',
                    'Pembayaran tenant divalidasi dan ditandai lunas.',
                    [
                        'before' => $before,
                        'after' => [
                            'status' => $transaction->status,
                            'paid_at' => optional($transaction->paid_at)->toISOString(),
                            'expires_at' => optional($transaction->expires_at)->toISOString(),
                            'subscription_status' => $subscription?->status,
                        ],
                    ],
                    [
                        'invoice_number' => $transaction->invoice_number,
                        'payment_type' => $transaction->type,
                        'payment_method' => $transaction->payment_method,
                    ]
                );

                $this->inboxPublisher->publish(
                    tenant: $tenant,
                    eventKey: 'tenant.payment.approved',
                    title: 'Pembayaran tervalidasi',
                    message: $transaction->type === 'registration'
                        ? 'Invoice '.$transaction->invoice_number.' sudah divalidasi dan tenant baru sedang disiapkan.'
                        : 'Invoice '.$transaction->invoice_number.' sudah divalidasi dan langganan tenant diperbarui.',
                    category: $transaction->type === 'registration' ? 'registration' : 'renewal',
                    tone: 'emerald',
                    payload: [
                        'payment_transaction_id' => $transaction->getKey(),
                        'subscription_id' => $subscription?->getKey(),
                        'invoice_number' => $transaction->invoice_number,
                    ],
                    occurredAt: $paidAt,
                    fingerprint: 'payment.approved:'.$transaction->getKey(),
                    sourceType: PlatformPaymentTransaction::class,
                    sourceId: $transaction->getKey(),
                );
            }

            return $transaction->fresh(['tenant', 'plan', 'subscription', 'proofs', 'events']);
        });

        if (! $wasAlreadyPaid && $activateSubscription && $paidTransaction->type === 'registration') {
            $this->resumeRegistrationOnboarding($paidTransaction, $paidAt);
        }

        return $paidTransaction->fresh(['tenant', 'plan', 'subscription', 'proofs', 'events']);
    }

    public function activateSubscriptionForTransaction(
        PlatformPaymentTransaction $transaction,
        ?Carbon $paidAt = null
    ): Subscription {
        $paidAt ??= $transaction->paid_at ?? now();

        return DB::transaction(function () use ($transaction, $paidAt): Subscription {
            /** @var PlatformPaymentTransaction $transaction */
            $transaction = PlatformPaymentTransaction::query()
                ->with(['tenant.subscriptions.plan', 'subscription.plan', 'plan'])
                ->lockForUpdate()
                ->findOrFail($transaction->getKey());

            if ($transaction->tenant === null) {
                throw new InvalidArgumentException('Transaksi pembayaran belum terkait tenant.');
            }

            if ($transaction->plan === null) {
                throw new InvalidArgumentException('Transaksi pembayaran belum terkait plan.');
            }

            $subscription = $transaction->subscription
                ?? $transaction->tenant->latestSubscription()
                ?? new Subscription([
                    'tenant_id' => $transaction->tenant->getKey(),
                    'started_at' => $paidAt,
                ]);

            $resolvedExpiresAt = $this->resolveExpiresAt($transaction, $subscription, $paidAt);

            $subscription->forceFill([
                'tenant_id' => $transaction->tenant->getKey(),
                'plan_id' => $transaction->plan->getKey(),
                'status' => 'active',
                'started_at' => $subscription->exists ? ($subscription->started_at ?? $paidAt) : $paidAt,
                'expired_at' => $resolvedExpiresAt,
                'trial_ends_at' => $resolvedExpiresAt,
            ])->save();

            $transaction->forceFill([
                'subscription_id' => $subscription->getKey(),
                'expires_at' => $resolvedExpiresAt,
            ])->save();

            $transaction->tenant->forceFill([
                'status' => 'active',
            ])->save();

            return $subscription->fresh(['plan', 'tenant']);
        });
    }

    private function resolveExpiresAt(
        PlatformPaymentTransaction $transaction,
        Subscription $subscription,
        Carbon $paidAt
    ): Carbon {
        if ($transaction->expires_at instanceof Carbon) {
            return $transaction->expires_at->copy();
        }

        // Downgrade: masa aktif DIPERTAHANKAN (pelanggan sudah bayar periode ini).
        if (
            $transaction->type === 'downgrade'
            && $subscription->exists
            && $subscription->expiresAt() instanceof Carbon
        ) {
            return $subscription->expiresAt()->copy();
        }

        $baseDate = $paidAt->copy();

        if (
            $transaction->type === 'renewal'
            && $subscription->exists
            && $subscription->expiresAt() instanceof Carbon
            && $subscription->expiresAt()?->isFuture()
        ) {
            $baseDate = $subscription->expiresAt()->copy();
        }

        return match (strtolower(trim((string) $transaction->billing_cycle))) {
            'yearly', 'annual' => $baseDate->addYear(),
            'quarterly' => $baseDate->addMonths(3),
            'weekly' => $baseDate->addWeek(),
            'daily' => $baseDate->addDay(),
            'custom' => $baseDate->addMonth(),
            default => $baseDate->addMonth(),
        };
    }

    private function mergeMeta(?array $existing, array $extra): ?array
    {
        $merged = array_filter([
            ...($existing ?? []),
            ...$extra,
        ], fn ($value) => $value !== null && $value !== '');

        return $merged === [] ? null : $merged;
    }

    private function resumeRegistrationOnboarding(PlatformPaymentTransaction $transaction, Carbon $paidAt): void
    {
        $tenant = $transaction->tenant?->fresh(['provisionLogs', 'domains']);

        if (! $tenant instanceof Tenant) {
            return;
        }

        try {
            $released = $this->onboardingDispatcher->releaseAfterPayment($tenant);

            if (! $released) {
                $this->tenantRegistrationService->startAutoOnboarding($tenant);

                if (! $this->onboardingDispatcher->releaseAfterPayment($tenant)) {
                    throw new \RuntimeException('Job onboarding tenant tidak tersedia untuk dirilis setelah pembayaran.');
                }
            }

            $this->inboxPublisher->publish(
                tenant: $tenant,
                eventKey: 'tenant.registration.onboarding_started',
                title: 'Setup tenant dimulai',
                message: 'Pembayaran pendaftaran sudah diterima. Sistem mulai menyiapkan tenant '.$tenant->name.'.',
                category: 'registration',
                tone: 'sky',
                payload: [
                    'payment_transaction_id' => $transaction->getKey(),
                    'invoice_number' => $transaction->invoice_number,
                    'tenant_code' => $tenant->code,
                ],
                occurredAt: $paidAt,
                fingerprint: 'tenant.registration.onboarding_started:'.$transaction->getKey(),
                sourceType: PlatformPaymentTransaction::class,
                sourceId: $transaction->getKey(),
            );
        } catch (Throwable $exception) {
            $this->auditLogger->logSystem(
                $tenant,
                'tenant.registration.onboarding_failed_after_payment',
                'Pembayaran pendaftaran sudah lunas, tetapi auto onboarding tenant gagal dijalankan.',
                [
                    'invoice_number' => $transaction->invoice_number,
                    'error' => $exception->getMessage(),
                ],
                [
                    'payment_transaction_id' => $transaction->getKey(),
                ]
            );

            throw $exception;
        }
    }
}
