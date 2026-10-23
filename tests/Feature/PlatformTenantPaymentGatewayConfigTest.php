<?php

namespace Tests\Feature;

use App\Livewire\Platform\Admin\TenantPaymentGatewayConfigManager;
use App\Models\Platform\Tenant;
use App\Models\Platform\TenantPaymentGatewayConfig;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Tests\TestCase;

class PlatformTenantPaymentGatewayConfigTest extends TestCase
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

    private function tenant(): Tenant
    {
        return Tenant::query()->create([
            'name' => 'Tenant Uji',
            'code' => 'TENANT-UJI',
            'owner_name' => 'Owner Uji',
            'owner_email' => 'owner-uji@example.com',
            'status' => 'active',
        ]);
    }

    public function test_gateway_config_page_renders_for_platform_admin(): void
    {
        $admin = $this->platformAdmin();
        $tenant = $this->tenant();

        $this->actingAs($admin);

        Livewire::test(TenantPaymentGatewayConfigManager::class)
            ->assertOk()
            ->assertSee('Payment Gateway per tenant')
            ->assertSee('Client ID');
    }

    public function test_platform_admin_can_save_encrypted_credentials_for_a_tenant(): void
    {
        $admin = $this->platformAdmin();
        $tenant = $this->tenant();

        $this->actingAs($admin);

        Livewire::test(TenantPaymentGatewayConfigManager::class)
            ->call('selectTenant', $tenant->id)
            ->set('provider', 'doku')
            ->set('mode', 'sandbox')
            ->set('client_id', 'BRN-TEST-123')
            ->set('secret_key', 'rahasia-secret')
            ->set('is_active', true)
            ->set('is_default', true)
            ->call('save')
            ->assertHasNoErrors();

        $config = TenantPaymentGatewayConfig::query()
            ->where('tenant_id', $tenant->id)
            ->where('provider', 'doku')
            ->first();

        $this->assertNotNull($config);
        $this->assertSame('BRN-TEST-123', $config->getClientId());
        $this->assertSame('rahasia-secret', $config->getSecretKey());
        // Nilai mentah di DB harus terenkripsi, bukan plaintext.
        $this->assertStringNotContainsString('rahasia-secret', (string) $config->getRawOriginal('secret_key_encrypted'));
    }

    public function test_route_is_guarded_by_the_gateway_permission(): void
    {
        $route = Route::getRoutes()->getByName('platform.admin.gateways.index');

        $this->assertNotNull($route);
        $this->assertContains('platform.can:manage_tenant_gateways', $route->gatherMiddleware());
    }
}
