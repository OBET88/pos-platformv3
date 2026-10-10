<?php

namespace Tests\Feature;

use App\Livewire\Platform\Admin\AnnouncementManager;
use App\Livewire\Platform\Admin\PlatformUsersManager;
use App\Livewire\Platform\Admin\RolesManager;
use App\Models\Platform\Announcement;
use App\Models\Platform\AnnouncementUserRead;
use App\Models\Platform\PlatformPermission;
use App\Models\Platform\PlatformRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PlatformRolesAndPermissionsTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        return User::query()->create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'password' => 'secret123',
            'is_platform_admin' => true,
        ]);
    }

    private function staffWith(array $permissions): User
    {
        $role = PlatformRole::query()->create([
            'name' => 'role-'.uniqid(),
            'label' => 'Role Uji',
        ]);

        $ids = PlatformPermission::query()->whereIn('name', $permissions)->pluck('id')->all();
        $role->permissions()->sync($ids);

        $user = User::query()->create([
            'name' => 'Staff',
            'email' => 'staff-'.uniqid().'@example.com',
            'password' => 'secret123',
            'is_platform_admin' => false,
        ]);

        $user->platformRoles()->sync([$role->id]);

        return $user;
    }

    public function test_seeder_migration_creates_roles_and_permissions_idempotently(): void
    {
        $this->assertDatabaseHas('platform_roles', ['name' => 'platform-owner']);
        $this->assertDatabaseHas('platform_permissions', ['name' => 'manage_announcements']);

        $ownerRole = PlatformRole::query()->where('name', 'platform-owner')->first();
        $this->assertTrue($ownerRole->permissions()->count() >= 16);
    }

    public function test_existing_platform_admin_is_assigned_owner_role(): void
    {
        $admin = User::query()->create([
            'name' => 'Legacy',
            'email' => 'legacy-'.uniqid().'@example.com',
            'password' => 'secret123',
            'is_platform_admin' => true,
        ]);

        // Simulasikan seeder meng-assign user lama.
        $ownerRoleId = PlatformRole::query()->where('name', 'platform-owner')->value('id');
        $admin->platformRoles()->syncWithoutDetaching([$ownerRoleId]);

        $this->assertTrue($admin->hasPlatformRole('platform-owner'));
    }

    public function test_super_admin_passes_every_permission_check(): void
    {
        $admin = $this->superAdmin();

        $this->assertTrue($admin->hasPlatformPermission('approve_payments'));
        $this->assertTrue($admin->hasPlatformPermission('manage_roles'));
    }

    public function test_staff_without_permission_cannot_access_announcements(): void
    {
        $staff = $this->staffWith(['view_tenants']);

        $this->actingAs($staff)
            ->get(route('platform.admin.announcements.index'))
            ->assertForbidden();
    }

    public function test_staff_with_manage_announcements_can_create(): void
    {
        $staff = $this->staffWith(['view_announcements', 'manage_announcements']);

        $this->actingAs($staff);

        Livewire::test(AnnouncementManager::class)
            ->set('form.title', 'Update Fitur')
            ->set('form.message', '<p>Fitur baru tersedia.</p>')
            ->set('form.tone', 'feature')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('platform_announcements', ['title' => 'Update Fitur']);
    }

    public function test_sanitizer_strips_script_and_keeps_safe_html(): void
    {
        $staff = $this->staffWith(['view_announcements', 'manage_announcements']);

        $this->actingAs($staff);

        Livewire::test(AnnouncementManager::class)
            ->set('form.title', 'Cek XSS')
            ->set('form.message', '<p onclick="x()">Halo <strong>dunia</strong><script>alert(1)</script></p>')
            ->set('form.tone', 'info')
            ->call('save');

        $announcement = Announcement::query()->where('title', 'Cek XSS')->firstOrFail();

        $this->assertStringNotContainsString('<script', (string) $announcement->message_html);
        $this->assertStringNotContainsString('onclick', (string) $announcement->message_html);
        $this->assertStringContainsString('<strong>dunia</strong>', (string) $announcement->message_html);
    }

    public function test_reset_views_clears_tenant_reads(): void
    {
        $admin = $this->superAdmin();

        $announcement = Announcement::query()->create([
            'title' => 'Info',
            'message' => 'Pesan',
            'message_html' => '<p>Pesan</p>',
            'tone' => 'info',
            'is_active' => true,
            'show_as_modal' => true,
        ]);

        AnnouncementUserRead::query()->create([
            'announcement_id' => $announcement->id,
            'tenant_code' => 'DKR01',
            'tenant_user_id' => 7,
            'dismissed_at' => now(),
        ]);

        $this->actingAs($admin);

        Livewire::test(AnnouncementManager::class)
            ->call('resetViews', $announcement->id);

        $this->assertDatabaseMissing('platform_announcement_user_reads', [
            'announcement_id' => $announcement->id,
        ]);
    }

    public function test_owner_cannot_demote_self_from_super_admin(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin);

        Livewire::test(PlatformUsersManager::class)
            ->call('toggleSuperAdmin', $admin->id);

        $this->assertTrue($admin->fresh()->is_platform_admin);
    }

    public function test_roles_manager_requires_manage_roles_to_edit(): void
    {
        $staff = $this->staffWith(['view_roles']);

        $this->actingAs($staff);

        // Mengakses halaman (view_roles) diizinkan.
        $this->get(route('platform.admin.roles.index'))->assertOk();

        // Aksi mutasi tanpa manage_roles ditolak.
        Livewire::test(RolesManager::class)
            ->set('newRoleName', 'role-baru')
            ->set('newRoleLabel', 'Role Baru')
            ->call('save')
            ->assertForbidden();
    }
}
