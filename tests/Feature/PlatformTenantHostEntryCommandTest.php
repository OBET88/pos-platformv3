<?php

namespace Tests\Feature;

use App\Models\Platform\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformTenantHostEntryCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_shows_hosts_entry_for_tenant_code(): void
    {
        $tenant = Tenant::query()->create([
            'code' => 'demo-host-entry',
            'name' => 'Demo Host Entry',
            'owner_name' => 'Owner Demo',
            'owner_email' => 'owner-demo@example.com',
            'status' => 'trial',
        ]);

        $tenant->domains()->create([
            'domain' => 'demo-host-entry.poslive.test',
            'type' => 'subdomain',
            'is_primary' => true,
        ]);

        $this->artisan('platform:tenant-host-entry', ['tenant' => $tenant->code])
            ->expectsOutput('Tenant: Demo Host Entry [demo-host-entry]')
            ->expectsOutput('Status: trial')
            ->expectsOutput('Owner: owner-demo@example.com')
            ->expectsOutput('127.0.0.1 demo-host-entry.poslive.test')
            ->expectsOutput('http://demo-host-entry.poslive.test/login')
            ->assertExitCode(0);
    }

    public function test_it_fails_when_tenant_has_no_domain(): void
    {
        $tenant = Tenant::query()->create([
            'code' => 'demo-no-domain',
            'name' => 'Demo No Domain',
            'owner_name' => 'Owner Demo',
            'owner_email' => 'owner-no-domain@example.com',
            'status' => 'pending_setup',
        ]);

        $this->artisan('platform:tenant-host-entry', ['tenant' => (string) $tenant->id])
            ->expectsOutput('Tenant: Demo No Domain [demo-no-domain]')
            ->expectsOutput('Status: pending_setup')
            ->expectsOutput('Owner: owner-no-domain@example.com')
            ->expectsOutput('Tenant ini belum memiliki domain primary.')
            ->assertExitCode(1);
    }
}
