<?php

use App\Models\Platform\PlatformPermission;
use App\Models\Platform\PlatformRole;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;

/**
 * Seed role & permission platform secara idempotent.
 *
 * Dijalankan sebagai migration (bukan db:seed manual) agar aman di server yang
 * hanya menjalankan `php artisan migrate --force`. Bisa dijalankan berulang.
 */
return new class extends Migration
{
    private const PERMISSIONS = [
        ['name' => 'view_tenants', 'label' => 'Lihat tenant', 'group' => 'Tenant'],
        ['name' => 'manage_tenants', 'label' => 'Kelola tenant', 'group' => 'Tenant'],
        ['name' => 'view_payments', 'label' => 'Lihat pembayaran', 'group' => 'Pembayaran'],
        ['name' => 'approve_payments', 'label' => 'Setujui pembayaran', 'group' => 'Pembayaran'],
        ['name' => 'view_announcements', 'label' => 'Lihat pengumuman', 'group' => 'Pengumuman'],
        ['name' => 'manage_announcements', 'label' => 'Kelola pengumuman', 'group' => 'Pengumuman'],
        ['name' => 'view_plans', 'label' => 'Lihat paket', 'group' => 'Paket'],
        ['name' => 'manage_plans', 'label' => 'Kelola paket', 'group' => 'Paket'],
        ['name' => 'manage_settings', 'label' => 'Kelola pengaturan platform', 'group' => 'Pengaturan'],
        ['name' => 'view_inbox', 'label' => 'Lihat inbox', 'group' => 'Inbox'],
        ['name' => 'manage_inbox', 'label' => 'Kelola inbox', 'group' => 'Inbox'],
        ['name' => 'view_accounting', 'label' => 'Lihat akuntansi tenant', 'group' => 'Akuntansi'],
        ['name' => 'manage_accounting', 'label' => 'Kelola akuntansi tenant', 'group' => 'Akuntansi'],
        ['name' => 'view_roles', 'label' => 'Lihat role & hak akses', 'group' => 'Hak Akses'],
        ['name' => 'manage_roles', 'label' => 'Kelola role & hak akses', 'group' => 'Hak Akses'],
        ['name' => 'manage_platform_users', 'label' => 'Kelola pengguna platform', 'group' => 'Hak Akses'],
    ];

    private const ROLES = [
        'platform-owner' => [
            'label' => 'Owner Platform',
            'permissions' => '*',
        ],
        'payment-verifier' => [
            'label' => 'Verifikator Pembayaran',
            'permissions' => ['view_tenants', 'view_payments', 'approve_payments', 'view_inbox'],
        ],
        'content-admin' => [
            'label' => 'Admin Konten',
            'permissions' => ['view_announcements', 'manage_announcements', 'view_tenants'],
        ],
        'tenant-admin' => [
            'label' => 'Admin Tenant',
            'permissions' => ['view_tenants', 'manage_tenants', 'view_plans', 'view_inbox'],
        ],
        'accountant' => [
            'label' => 'Akuntan',
            'permissions' => ['view_tenants', 'view_payments', 'view_accounting', 'manage_accounting'],
        ],
    ];

    public function up(): void
    {
        foreach (self::PERMISSIONS as $permission) {
            PlatformPermission::query()->updateOrCreate(
                ['name' => $permission['name']],
                ['label' => $permission['label'], 'group' => $permission['group']]
            );
        }

        $allPermissionNames = PlatformPermission::query()->pluck('name')->all();

        foreach (self::ROLES as $roleName => $definition) {
            $role = PlatformRole::query()->updateOrCreate(
                ['name' => $roleName],
                ['label' => $definition['label'], 'is_system' => $roleName === 'platform-owner']
            );

            $permissionNames = $definition['permissions'] === '*'
                ? $allPermissionNames
                : $definition['permissions'];

            $permissionIds = PlatformPermission::query()
                ->whereIn('name', $permissionNames)
                ->pluck('id')
                ->all();

            $role->permissions()->sync($permissionIds);
        }

        // User lama dengan flag is_platform_admin tetap punya semua akses lewat
        // Gate::before; sekaligus di-assign ke role system agar terlihat di UI.
        $ownerRoleId = PlatformRole::query()->where('name', 'platform-owner')->value('id');

        if ($ownerRoleId) {
            User::query()
                ->where('is_platform_admin', true)
                ->orderBy('id')
                ->chunkById(100, function ($users) use ($ownerRoleId): void {
                    foreach ($users as $user) {
                        $user->platformRoles()->syncWithoutDetaching([$ownerRoleId]);
                    }
                });
        }
    }

    public function down(): void
    {
        // Biarkan data role/permission tetap ada; hanya bersihkan pivot user.
        // (Rollback penuh tidak perlu, seeder ini idempotent.)
    }
};
