<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformAdminNavigationTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::query()->create([
            'name' => 'Platform Admin',
            'email' => 'platform-admin-nav@example.com',
            'password' => 'secret123',
            'is_platform_admin' => true,
        ]);
    }

    public function test_sidebar_exposes_every_admin_section(): void
    {
        $this->actingAs($this->admin())
            ->get(route('platform.admin.tenants.index'))
            ->assertOk()
            ->assertSee('Tenant Manager')
            ->assertSee('Inbox Operasional')
            ->assertSee('Validasi Pembayaran')
            ->assertSee('Paket &amp; Fitur', false)
            ->assertSee('Pengaturan Pendaftaran')
            ->assertSee('Pengumuman');
    }

    public function test_plan_manager_surfaces_public_registration_visibility(): void
    {
        $this->actingAs($this->admin())
            ->get(route('platform.admin.plans.index'))
            ->assertOk()
            ->assertSee('Tampil di Form Daftar');
    }
}
