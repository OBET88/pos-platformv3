<?php

namespace Tests\Feature;

use App\Models\Platform\Tenant;
use App\Models\Platform\TenantDatabaseConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class PlatformOnboardDemoTenantCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_onboards_a_demo_tenant_and_prints_access_details(): void
    {
        config()->set('platform.tenant_base_domain', 'poslive.test');
        config()->set('platform.tenant_database_driver', 'sqlite');
        $directory = storage_path('framework/testing/platform-demo-command-tenants');
        File::deleteDirectory($directory);
        config()->set('platform.tenant_database_directory', $directory);

        // Nama "PT Demo Command" (slug "pt-demo-command", 15 char) kini
        // DITEBAT menjadi base 12 char: "pt-demo-comm".
        $expectedCode = 'pt-demo-comm';
        $expectedDomain = $expectedCode.'.poslive.test';

        $this->artisan('platform:onboard-demo-tenant', [
            'company_name' => 'PT Demo Command',
            'owner_name' => 'Owner Command',
            'owner_email' => 'owner-command@example.com',
            'owner_password' => 'secret12345',
            '--plan' => 'basic',
            '--business' => 'Usaha Demo Command',
            '--branch' => 'Cabang Demo Command 1',
            '--phone' => '08120000001',
            '--notes' => 'Demo onboarding via command',
        ])
            ->expectsOutput('Tenant demo berhasil dibuat.')
            ->expectsOutputToContain('Tenant Code: '.$expectedCode)
            ->expectsOutput('Status: trial')
            ->expectsOutput('Owner Email: owner-command@example.com')
            ->expectsOutput('Owner Password: secret12345')
            ->assertExitCode(0);

        $tenant = Tenant::query()->where('owner_email', 'owner-command@example.com')->first();

        $this->assertNotNull($tenant);
        $this->assertSame('trial', $tenant->status);
        $this->assertSame($expectedDomain, $tenant->domains()->where('is_primary', true)->value('domain'));
        $this->assertSame('completed', $tenant->provisionLogs()->where('step', 'auto_onboarding_completed')->value('status'));

        $databasePath = TenantDatabaseConfig::query()->where('tenant_id', $tenant->id)->value('db_name');
        $this->assertNotFalse($databasePath);
        $this->assertTrue(File::exists($databasePath));

        File::deleteDirectory($directory);
    }
}
