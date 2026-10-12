<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmokePublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_render(): void
    {
        foreach ([
            '/',
            '/help-center',
            '/kontak',
            '/blog',
            '/daftar',
            '/perpanjang',
            '/upgrade',
            '/pesanan',
        ] as $uri) {
            $response = $this->get($uri);
            $this->assertTrue(
                in_array($response->getStatusCode(), [200, 302], true),
                "GET {$uri} mengembalikan ".$response->getStatusCode()
            );
        }
    }

    public function test_public_livewire_pages_contain_component(): void
    {
        $orders = $this->get('/pesanan');
        $orders->assertOk();
        $orders->assertSee('platform.public.order-lookup', false);

        $renewal = $this->get('/perpanjang');
        $renewal->assertOk();
        $renewal->assertSee('platform.public.renewal-request', false);

        $upgrade = $this->get('/upgrade');
        $upgrade->assertOk();
        $upgrade->assertSee('platform.public.upgrade-request', false);
    }

    public function test_admin_layout_keeps_main_inside_the_grid_beside_sidebar(): void
    {
        $admin = User::query()->create([
            'name' => 'Smoke Admin',
            'email' => 'smoke-admin@example.com',
            'password' => 'secret123',
            'is_platform_admin' => true,
        ]);

        $html = $this->actingAs($admin)->get(route('platform.admin.tenants.index'))->assertOk()->getContent();

        // Regresi: <main> harus berada di dalam grid yang sama dengan <aside>,
        // supaya konten tidak jatuh ke bawah sidebar.
        $this->assertLessThan(strpos($html, '<aside'), strpos($html, 'platform-shell-grid'));
        $this->assertLessThan(strpos($html, '<main'), strpos($html, 'platform-shell-grid'));
        $this->assertGreaterThan(strpos($html, '</main>'), strpos($html, 'platform-nav-backdrop'));

        // Tag <div> harus seimbang (tidak ada elemen yang bocor).
        $this->assertSame(substr_count($html, '<div'), substr_count($html, '</div>'));
    }
}
