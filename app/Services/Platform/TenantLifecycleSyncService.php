<?php

namespace App\Services\Platform;

use App\Models\Platform\Subscription;
use Illuminate\Support\Collection;

class TenantLifecycleSyncService
{
    public function __construct(
        private readonly TenantAuditLogger $auditLogger,
    ) {
    }

    public function syncExpiredTenants(): Collection
    {
        $changes = collect();

        Subscription::query()
            ->with(['tenant', 'plan'])
            ->latest('id')
            ->get()
            ->unique('tenant_id')
            ->each(function (Subscription $subscription) use ($changes): void {
                $tenant = $subscription->tenant;
                $expiresAt = $subscription->expired_at ?? $subscription->trial_ends_at;

                if (! $tenant || ! $expiresAt || ! $expiresAt->isPast()) {
                    return;
                }

                $subscriptionChanged = false;
                $tenantChanged = false;

                if ($subscription->status !== 'expired') {
                    $subscription->forceFill([
                        'status' => 'expired',
                    ])->save();

                    $subscriptionChanged = true;
                }

                if (! in_array($tenant->status, ['expired', 'suspended'], true)) {
                    $previousStatus = $tenant->status;

                    $tenant->forceFill([
                        'status' => 'expired',
                    ])->save();

                    $tenantChanged = true;

                    $this->auditLogger->logSystem(
                        $tenant,
                        'tenant.status.updated',
                        'Status tenant otomatis diubah menjadi expired karena masa trial/langganan sudah lewat.',
                        [
                            'before' => ['status' => $previousStatus],
                            'after' => ['status' => 'expired'],
                        ],
                        [
                            'stage' => 'auto-expired',
                            'subscription_status' => $subscription->status,
                            'expired_at' => $expiresAt->toISOString(),
                        ]
                    );
                }

                if ($subscriptionChanged || $tenantChanged) {
                    $changes->push([
                        'tenant_id' => $tenant->getKey(),
                        'tenant_code' => $tenant->code,
                        'subscription_changed' => $subscriptionChanged,
                        'tenant_changed' => $tenantChanged,
                    ]);
                }
            });

        return $changes->values();
    }
}
