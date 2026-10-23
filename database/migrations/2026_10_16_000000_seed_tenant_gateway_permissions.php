<?php

use App\Models\Platform\PlatformPermission;
use App\Models\Platform\PlatformRole;
use Illuminate\Database\Migrations\Migration;

/**
 * Memastikan permission `view_tenant_gateways` & `manage_tenant_gateways` ada
 * dan terpasang ke role. Di lingkungan yang sudah menjalankan seeder role
 * sebelum kedua permission ini ditambahkan, permission-nya belum ada —
 * migrasi ini menambalnya tanpa mengganggu data lain. Idempotent.
 */
return new class extends Migration
{
    private const PERMISSIONS = [
        ['name' => 'view_tenant_gateways', 'label' => 'Lihat payment gateway tenant', 'group' => 'Payment Gateway'],
        ['name' => 'manage_tenant_gateways', 'label' => 'Kelola payment gateway tenant', 'group' => 'Payment Gateway'],
    ];

    public function up(): void
    {
        foreach (self::PERMISSIONS as $permission) {
            PlatformPermission::query()->updateOrCreate(
                ['name' => $permission['name']],
                ['label' => $permission['label'], 'group' => $permission['group']]
            );
        }

        // platform-owner memegang semua permission (termasuk yang baru).
        $allPermissionIds = PlatformPermission::query()->pluck('id')->all();
        $ownerRole = PlatformRole::query()->where('name', 'platform-owner')->first();

        if ($ownerRole) {
            $ownerRole->permissions()->syncWithoutDetaching($allPermissionIds);
        }

        // tenant-admin boleh melihat (read-only) konfigurasi gateway.
        $tenantAdminRole = PlatformRole::query()->where('name', 'tenant-admin')->first();
        $viewId = PlatformPermission::query()->where('name', 'view_tenant_gateways')->value('id');

        if ($tenantAdminRole && $viewId) {
            $tenantAdminRole->permissions()->syncWithoutDetaching([$viewId]);
        }
    }

    public function down(): void
    {
        // Biarkan permission tetap ada; seeder bersifat idempotent.
    }
};
