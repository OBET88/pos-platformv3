<?php

namespace Tests\Feature;

use App\Models\Platform\Plan;
use App\Models\Platform\TenantProvisionLog;
use App\Services\Platform\TenantProvisioningService;
use App\Services\Platform\TenantRegistrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class PlatformTenantProvisioningTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_provisions_a_pending_tenant_database(): void
    {
        $directory = storage_path('framework/testing/platform-tenants');
        File::deleteDirectory($directory);
        config()->set('platform.tenant_base_domain', 'poslive.test');
        config()->set('platform.tenant_database_driver', 'sqlite');
        config()->set('platform.tenant_database_directory', $directory);

        $plan = Plan::query()->where('code', 'basic')->firstOrFail();

        $tenant = app(TenantRegistrationService::class)->register([
            'company_name' => 'PT Provision Test',
            'owner_name' => 'Owner Provision',
            'owner_email' => 'owner-provision@example.com',
            'owner_phone' => '08123456789',
            'plan_code' => $plan->code,
            'notes' => 'Provision test tenant',
        ]);

        $provisionedTenant = app(TenantProvisioningService::class)->provision($tenant);
        $databaseConfig = $provisionedTenant->databaseConfigs->sortByDesc('id')->first();

        $this->assertSame('trial', $provisionedTenant->status);
        $this->assertNotNull($provisionedTenant->onboarded_at);
        $this->assertNotNull($databaseConfig);
        $this->assertSame('provisioned', $databaseConfig->status);
        $this->assertSame('sqlite', $databaseConfig->db_host);
        $this->assertTrue(File::exists($databaseConfig->db_name));
        $this->assertSame($tenant->code.'.poslive.test', $provisionedTenant->domains->firstWhere('is_primary', true)?->domain);
        $this->assertDatabaseHas('tenant_provision_logs', [
            'tenant_id' => $provisionedTenant->id,
            'step' => 'tenant_ready',
            'status' => 'completed',
        ]);
        $this->assertGreaterThanOrEqual(1, TenantProvisionLog::query()->where('tenant_id', $provisionedTenant->id)->count());

        File::deleteDirectory($directory);
    }
}
