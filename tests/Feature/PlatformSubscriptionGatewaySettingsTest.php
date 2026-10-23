<?php

namespace Tests\Feature;

use App\Livewire\Platform\Admin\SubscriptionGatewaySettingsPage;
use App\Models\Platform\PlatformSetting;
use App\Models\User;
use App\Services\Platform\DokuCheckoutService;
use App\Services\Platform\PlatformDokuConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Tests\TestCase;

class PlatformSubscriptionGatewaySettingsTest extends TestCase
{
    use RefreshDatabase;

    private function platformAdmin(): User
    {
        return User::query()->create([
            'name' => 'Platform Owner',
            'email' => 'platform-owner@example.com',
            'password' => 'secret123',
            'is_platform_admin' => true,
        ]);
    }

    public function test_page_renders_for_platform_admin(): void
    {
        $this->actingAs($this->platformAdmin());

        Livewire::test(SubscriptionGatewaySettingsPage::class)
            ->assertOk()
            ->assertSee('Gateway pembayaran langganan')
            ->assertSee('Client ID');
    }

    public function test_platform_admin_can_save_encrypted_doku_credentials(): void
    {
        $this->actingAs($this->platformAdmin());

        Livewire::test(SubscriptionGatewaySettingsPage::class)
            ->set('enabled', true)
            ->set('mode', 'sandbox')
            ->set('clientId', 'BRN-PLATFORM-123')
            ->set('secretKey', 'secret-platform-xyz')
            ->set('baseUrl', 'https://api-sandbox.doku.com')
            ->set('merchantName', 'RWP POS')
            ->call('save')
            ->assertHasNoErrors();

        $config = app(PlatformDokuConfig::class);

        $this->assertTrue($config->isEnabled());
        $this->assertSame('BRN-PLATFORM-123', $config->resolve()['client_id']);
        $this->assertSame('secret-platform-xyz', $config->secret());

        // Secret tersimpan terenkripsi, bukan plaintext.
        $raw = PlatformSetting::query()->where('key', PlatformDokuConfig::SECRET_KEY_KEY)->value('value');
        $this->assertNotNull($raw);
        $this->assertStringNotContainsString('secret-platform-xyz', (string) $raw);

        // DokuCheckoutService memakai konfigurasi platform dari DB.
        $this->assertTrue(app(DokuCheckoutService::class)->isEnabled());
    }

    public function test_enabling_without_client_id_is_rejected(): void
    {
        $this->actingAs($this->platformAdmin());

        Livewire::test(SubscriptionGatewaySettingsPage::class)
            ->set('enabled', true)
            ->set('clientId', '')
            ->set('secretKey', '')
            ->set('baseUrl', 'https://api-sandbox.doku.com')
            ->call('save')
            ->assertHasErrors('clientId');

        $this->assertFalse(app(PlatformDokuConfig::class)->isEnabled());
    }

    public function test_route_is_guarded_by_settings_permission(): void
    {
        $route = Route::getRoutes()->getByName('platform.admin.settings.subscription-gateway');

        $this->assertNotNull($route);
        $this->assertContains('platform.can:manage_settings', $route->gatherMiddleware());
    }
}
