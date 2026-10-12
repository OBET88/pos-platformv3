<?php

namespace Tests\Feature;

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
}
