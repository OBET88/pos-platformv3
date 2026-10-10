<?php

namespace Tests\Feature;

use App\Models\Platform\Announcement;
use App\Models\Platform\AnnouncementDismissal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PlatformAnnouncementTest extends TestCase
{
    use RefreshDatabase;

    public function test_platform_admin_can_create_announcement_from_livewire_manager(): void
    {
        $admin = User::query()->create([
            'name' => 'Platform Admin',
            'email' => 'platform-admin@example.com',
            'password' => 'secret123',
            'is_platform_admin' => true,
        ]);

        $this->actingAs($admin);

        Livewire::test(\App\Livewire\Platform\Admin\AnnouncementManager::class)
            ->set('form.title', 'Maintenance Tengah Malam')
            ->set('form.message', 'Sistem akan maintenance pukul 23:00 sampai 23:30.')
            ->set('form.tone', 'maintenance')
            ->set('form.is_active', true)
            ->set('form.show_as_modal', true)
            ->call('createAnnouncement')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('platform_announcements', [
            'title' => 'Maintenance Tengah Malam',
            'tone' => 'maintenance',
            'is_active' => true,
            'show_as_modal' => true,
            'created_by' => $admin->id,
        ]);
    }

    public function test_active_announcement_is_shown_as_modal_after_login_until_dismissed(): void
    {
        $admin = User::query()->create([
            'name' => 'Platform Admin',
            'email' => 'platform-admin@example.com',
            'password' => 'secret123',
            'is_platform_admin' => true,
        ]);

        $announcement = Announcement::query()->create([
            'title' => 'Security Notice',
            'message' => 'Mohon reset password admin jika belum diperbarui bulan ini.',
            'tone' => 'security',
            'is_active' => true,
            'show_as_modal' => true,
            'starts_at' => now()->subMinute(),
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get(route('platform.admin.tenants.index'))
            ->assertOk()
            ->assertSee('Pengumuman Platform')
            ->assertSee('Security Notice')
            ->assertSee('Tutup Popup');

        $this->actingAs($admin)
            ->post(route('platform.admin.announcements.dismiss', $announcement))
            ->assertRedirect();

        $this->assertDatabaseHas('platform_announcement_dismissals', [
            'announcement_id' => $announcement->id,
            'user_id' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get(route('platform.admin.tenants.index'))
            ->assertOk()
            ->assertDontSee('Security Notice');
    }
}
