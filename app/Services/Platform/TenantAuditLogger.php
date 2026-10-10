<?php

namespace App\Services\Platform;

use App\Models\Platform\Tenant;
use App\Models\Platform\TenantAudit;
use Illuminate\Support\Facades\Auth;

class TenantAuditLogger
{
    public function __construct(
        private readonly PlatformInboxPublisher $inboxPublisher
    ) {
    }

    public function log(Tenant $tenant, string $action, string $summary, array $changes = [], array $metadata = []): TenantAudit
    {
        $audit = TenantAudit::query()->create([
            'tenant_id' => $tenant->getKey(),
            'actor_id' => Auth::id(),
            'action' => $action,
            'summary' => $summary,
            'changes' => $changes === [] ? null : $changes,
            'metadata' => array_filter([
                'actor_email' => Auth::user()?->email,
                ...$metadata,
            ], fn ($value) => $value !== null && $value !== ''),
        ]);

        $this->inboxPublisher->publishForAudit($audit);

        return $audit;
    }

    public function logSystem(Tenant $tenant, string $action, string $summary, array $changes = [], array $metadata = []): ?TenantAudit
    {
        $dateFingerprint = now()->format('Y-m-d');
        $stageFingerprint = $metadata['stage'] ?? null;
        $existing = TenantAudit::query()
            ->where('tenant_id', $tenant->getKey())
            ->where('action', $action)
            ->whereDate('created_at', today())
            ->when($stageFingerprint !== null, function ($query) use ($stageFingerprint): void {
                $query->where('metadata->stage', $stageFingerprint);
            })
            ->exists();

        if ($existing) {
            return null;
        }

        $audit = TenantAudit::query()->create([
            'tenant_id' => $tenant->getKey(),
            'actor_id' => null,
            'action' => $action,
            'summary' => $summary,
            'changes' => $changes === [] ? null : $changes,
            'metadata' => array_filter([
                'source' => 'scheduler',
                'date' => $dateFingerprint,
                ...$metadata,
            ], fn ($value) => $value !== null && $value !== ''),
        ]);

        $this->inboxPublisher->publishForAudit($audit);

        return $audit;
    }
}
