<?php

namespace App\Services\Platform;

use App\Models\Platform\InboxItem;
use App\Models\Platform\Tenant;
use App\Models\Platform\TenantAudit;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class PlatformInboxPublisher
{
    public function publish(
        ?Tenant $tenant,
        string $eventKey,
        string $title,
        string $message,
        string $category = 'admin',
        string $tone = 'slate',
        array $payload = [],
        ?Carbon $occurredAt = null,
        ?string $fingerprint = null,
        ?string $sourceType = null,
        ?int $sourceId = null
    ): InboxItem {
        $occurredAt ??= now();
        $fingerprint ??= $this->defaultFingerprint($tenant, $eventKey, $occurredAt, $payload, $sourceType, $sourceId);

        return InboxItem::query()->firstOrCreate(
            ['fingerprint' => $fingerprint],
            [
                'tenant_id' => $tenant?->getKey(),
                'category' => $category,
                'event_key' => $eventKey,
                'title' => $title,
                'message' => $message,
                'tone' => $tone,
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'payload' => $payload === [] ? null : $payload,
                'occurred_at' => $occurredAt,
            ]
        );
    }

    public function publishForAudit(TenantAudit $audit): ?InboxItem
    {
        [$category, $title, $tone] = match ($audit->action) {
            'tenant.subscription.expiring' => ['attention', 'Tenant mau expire', 'amber'],
            'tenant.subscription.expired' => ['attention', 'Tenant expired', 'rose'],
            'tenant.subscription.renewed' => ['renewal', 'Perpanjangan terbaru', 'emerald'],
            'tenant.payment.awaiting_validation' => ['renewal', 'Menunggu validasi pembayaran', 'amber'],
            'tenant.payment.rejected' => ['renewal', 'Pembayaran perlu dicek ulang', 'rose'],
            'tenant.bootstrap.failed',
            'tenant.provision.failed' => ['attention', 'Butuh tindakan admin', 'rose'],
            'tenant.profile.updated',
            'tenant.plan.updated',
            'tenant.domain.updated',
            'tenant.status.updated',
            'tenant.note.added',
            'tenant.provision.recovered',
            'tenant.bootstrap.recovered' => ['admin', 'Update admin terbaru', 'sky'],
            default => [null, null, null],
        };

        if ($category === null || $title === null || $tone === null) {
            return null;
        }

        return $this->publish(
            tenant: $audit->tenant,
            eventKey: $audit->action,
            title: $title,
            message: $audit->summary,
            category: $category,
            tone: $tone,
            payload: [
                'audit_id' => $audit->getKey(),
                'metadata' => $audit->metadata,
            ],
            occurredAt: $audit->created_at,
            fingerprint: 'audit:'.$audit->getKey(),
            sourceType: TenantAudit::class,
            sourceId: $audit->getKey(),
        );
    }

    private function defaultFingerprint(
        ?Tenant $tenant,
        string $eventKey,
        Carbon $occurredAt,
        array $payload,
        ?string $sourceType,
        ?int $sourceId
    ): string {
        $raw = implode('|', [
            $tenant?->getKey() ?? 'none',
            $eventKey,
            $occurredAt->toIso8601String(),
            $sourceType ?? 'source:none',
            $sourceId ?? '0',
            json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ]);

        return Str::limit(hash('sha256', $raw), 64, '');
    }
}
