<?php

namespace App\Services\Platform;

use App\Models\Platform\Tenant;
use App\Models\Platform\TenantBootstrapSecret;
use App\Models\Platform\TenantOnboardingJob;
use App\Models\Platform\TenantProvisionLog;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Throwable;

class TenantOnboardingWorker
{
    private const LEASE_SECONDS = 14400;
    private const MAX_ATTEMPTS = 8;

    public function __construct(
        private readonly TenantRegistrationService $registrationService,
    ) {}

    public function processNext(?int $tenantId = null): ?TenantOnboardingJob
    {
        $job = $this->claimNext($tenantId);

        if ($job === null) {
            return null;
        }

        $leaseToken = (string) $job->lease_token;

        try {
            $tenant = Tenant::query()->find($job->tenant_id);

            if (! $tenant instanceof Tenant || $tenant->status === 'pending_payment') {
                return $this->block($job, $leaseToken, 'Tenant belum memenuhi syarat pembayaran untuk onboarding.');
            }

            $secret = TenantBootstrapSecret::query()->where('tenant_id', $tenant->getKey())->first();

            if ($secret === null) {
                return $this->fail($job, $leaseToken, 'Bootstrap secret tidak tersedia.');
            }

            $payload = [
                ...($job->payload ?? []),
                'owner_password' => Crypt::decryptString($secret->owner_password_encrypted),
            ];

            $this->registrationService->completeAutoOnboarding($tenant, $payload);

            $this->complete($job, $leaseToken);
        } catch (Throwable $exception) {
            $this->retryOrFail($job, $leaseToken, $exception);
        }

        return $job->fresh();
    }

    private function claimNext(?int $tenantId): ?TenantOnboardingJob
    {
        $now = now();
        TenantOnboardingJob::query()
            ->when($tenantId !== null, fn ($query) => $query->where('tenant_id', $tenantId))
            ->where('status', 'running')
            ->where('lease_expires_at', '<=', $now)
            ->where('attempts', '>=', self::MAX_ATTEMPTS)
            ->update([
                'status' => 'failed',
                'failed_at' => $now,
                'last_error' => 'Lease onboarding berakhir setelah batas percobaan otomatis.',
                'lease_token' => null,
                'leased_at' => null,
                'lease_expires_at' => null,
                'updated_at' => $now,
            ]);

        $query = TenantOnboardingJob::query()
            ->when($tenantId !== null, fn ($query) => $query->where('tenant_id', $tenantId))
            ->where(function ($query) use ($now): void {
                $query->where(function ($eligible) use ($now): void {
                    $eligible->whereIn('status', ['queued', 'retry_wait'])
                        ->where(function ($available) use ($now): void {
                            $available->whereNull('available_at')->orWhere('available_at', '<=', $now);
                        });
                    })->orWhere(function ($expiredLease) use ($now): void {
                        $expiredLease->where('status', 'running')
                        ->where('lease_expires_at', '<=', $now)
                        ->where('attempts', '<', self::MAX_ATTEMPTS);
                });
            })
            ->orderBy('available_at')
            ->orderBy('id');

        foreach ($query->get() as $candidate) {
            $leaseToken = Str::random(64);
            $claimed = TenantOnboardingJob::query()
                ->whereKey($candidate->getKey())
                ->where(function ($claimable) use ($now): void {
                    $claimable->where(function ($eligible) use ($now): void {
                        $eligible->whereIn('status', ['queued', 'retry_wait'])
                            ->where(function ($available) use ($now): void {
                                $available->whereNull('available_at')->orWhere('available_at', '<=', $now);
                            });
                    })->orWhere(function ($expiredLease) use ($now): void {
                        $expiredLease->where('status', 'running')
                            ->where('lease_expires_at', '<=', $now)
                            ->where('attempts', '<', self::MAX_ATTEMPTS);
                    });
                })
                ->update([
                    'status' => 'running',
                    'attempts' => $candidate->attempts + 1,
                    'lease_token' => $leaseToken,
                    'leased_at' => $now,
                    'lease_expires_at' => $now->copy()->addSeconds(self::LEASE_SECONDS),
                    'last_started_at' => $now,
                    'updated_at' => $now,
                ]);

            if ($claimed === 1) {
                return TenantOnboardingJob::query()->find($candidate->getKey());
            }
        }

        return null;
    }

    private function complete(TenantOnboardingJob $job, string $leaseToken): void
    {
        TenantOnboardingJob::query()->whereKey($job->getKey())->where('lease_token', $leaseToken)->update([
            'status' => 'completed',
            'completed_at' => now(),
            'lease_token' => null,
            'leased_at' => null,
            'lease_expires_at' => null,
            'updated_at' => now(),
        ]);
    }

    private function block(TenantOnboardingJob $job, string $leaseToken, string $message): TenantOnboardingJob
    {
        TenantOnboardingJob::query()->whereKey($job->getKey())->where('lease_token', $leaseToken)->update([
            'status' => 'blocked', 'last_error' => $message, 'lease_token' => null, 'leased_at' => null,
            'lease_expires_at' => null, 'updated_at' => now(),
        ]);

        return $job->fresh();
    }

    private function fail(TenantOnboardingJob $job, string $leaseToken, string $message): TenantOnboardingJob
    {
        TenantOnboardingJob::query()->whereKey($job->getKey())->where('lease_token', $leaseToken)->update([
            'status' => 'failed', 'failed_at' => now(), 'last_error' => $message, 'lease_token' => null,
            'leased_at' => null, 'lease_expires_at' => null, 'updated_at' => now(),
        ]);

        return $job->fresh();
    }

    private function retryOrFail(TenantOnboardingJob $job, string $leaseToken, Throwable $exception): void
    {
        $message = Str::limit($exception->getMessage(), 500);
        $attempts = $job->attempts;
        $terminal = $attempts >= self::MAX_ATTEMPTS;
        $delayMinutes = min(60, max(2, 2 ** min($attempts, 5)));

        TenantOnboardingJob::query()->whereKey($job->getKey())->where('lease_token', $leaseToken)->update([
            'status' => $terminal ? 'failed' : 'retry_wait',
            'available_at' => $terminal ? null : now()->addMinutes($delayMinutes),
            'failed_at' => $terminal ? now() : null,
            'last_error_class' => $exception::class,
            'last_error' => $message,
            'lease_token' => null,
            'leased_at' => null,
            'lease_expires_at' => null,
            'updated_at' => now(),
        ]);

        TenantProvisionLog::query()->create([
            'tenant_id' => $job->tenant_id,
            'step' => 'auto_onboarding_worker',
            'status' => $terminal ? 'failed' : 'retry_wait',
            'message' => $terminal ? 'Auto onboarding membutuhkan recovery admin.' : 'Auto onboarding akan dicoba kembali otomatis.',
            'payload' => ['attempt' => $attempts, 'failure_category' => $exception::class],
        ]);
    }
}
