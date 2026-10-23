<?php

namespace App\Services\Platform;

use App\Models\Platform\Tenant;
use App\Models\Platform\TenantOnboardingJob;
use App\Models\Platform\TenantBootstrapSecret;
use App\Models\Platform\TenantProvisionLog;
use Illuminate\Support\Str;

class TenantOnboardingDispatcher
{
    public function enqueue(Tenant $tenant, array $payload, bool $eligible = true): TenantOnboardingJob
    {
        $safePayload = collect($payload)
            ->only(['owner_name', 'owner_email', 'owner_email_verified_at', 'business_name', 'branch_name', 'owner_role'])
            ->all();

        $job = TenantOnboardingJob::query()->firstOrNew(['tenant_id' => $tenant->getKey()]);

        if ($job->exists && $job->status === 'completed') {
            return $job;
        }

        $job->forceFill([
            'status' => $eligible ? 'queued' : 'blocked',
            'payload' => $safePayload,
            'available_at' => $eligible ? now() : null,
            'lease_token' => null,
            'leased_at' => null,
            'lease_expires_at' => null,
            'last_error_class' => null,
            'last_error' => null,
            'failed_at' => null,
        ])->save();

        return $job;
    }

    public function releaseAfterPayment(Tenant $tenant): bool
    {
        return TenantOnboardingJob::query()
            ->where('tenant_id', $tenant->getKey())
            ->whereIn('status', ['blocked', 'retry_wait', 'failed'])
            ->update([
                'status' => 'queued',
                'available_at' => now(),
                'failed_at' => null,
                'last_error_class' => null,
                'last_error' => null,
                'updated_at' => now(),
            ]) > 0;
    }

    public function issuePublicAccessToken(Tenant $tenant): string
    {
        $token = Str::random(80);

        TenantOnboardingJob::query()
            ->where('tenant_id', $tenant->getKey())
            ->update([
                'access_token_hash' => hash('sha256', $token),
                'access_expires_at' => now()->addHours(48),
                'updated_at' => now(),
            ]);

        return $token;
    }

    public function accessUrl(Tenant $tenant, string $token): string
    {
        return route('platform.register.success', [
            'tenant' => $tenant->code,
            'access' => $token,
        ]);
    }

    public function hasValidPublicAccess(Tenant $tenant, ?string $token): bool
    {
        if (! is_string($token) || $token === '') {
            return false;
        }

        $job = TenantOnboardingJob::query()->where('tenant_id', $tenant->getKey())->first();

        return $job !== null
            && $job->access_expires_at?->isFuture()
            && is_string($job->access_token_hash)
            && hash_equals($job->access_token_hash, hash('sha256', $token));
    }

    public function retry(Tenant $tenant): bool
    {
        return TenantOnboardingJob::query()
            ->where('tenant_id', $tenant->getKey())
            ->where('status', 'failed')
            ->update([
                'status' => 'queued',
                'attempts' => 0,
                'available_at' => now(),
                'lease_token' => null,
                'leased_at' => null,
                'lease_expires_at' => null,
                'last_error_class' => null,
                'last_error' => null,
                'failed_at' => null,
                'updated_at' => now(),
            ]) > 0;
    }

    public function finalizeAdminRecovery(Tenant $tenant, array $payload): void
    {
        $safePayload = collect($payload)
            ->only(['owner_name', 'owner_email', 'owner_email_verified_at', 'business_name', 'branch_name', 'owner_role'])
            ->all();

        TenantOnboardingJob::query()->updateOrCreate(
            ['tenant_id' => $tenant->getKey()],
            [
                'status' => 'completed',
                'payload' => $safePayload,
                'completed_at' => now(),
                'available_at' => null,
                'lease_token' => null,
                'leased_at' => null,
                'lease_expires_at' => null,
                'last_error_class' => null,
                'last_error' => null,
                'failed_at' => null,
            ],
        );

        TenantProvisionLog::query()->updateOrCreate([
            'tenant_id' => $tenant->getKey(),
            'step' => 'auto_onboarding_completed',
        ], [
            'status' => 'completed',
            'message' => 'Auto provisioning tenant selesai melalui recovery admin.',
            'payload' => $safePayload,
        ]);

        TenantBootstrapSecret::query()->where('tenant_id', $tenant->getKey())->delete();
    }
}
