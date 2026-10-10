<?php

namespace Tests\Feature;

use App\Models\Platform\Plan;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformTenantLifecycleSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_platform_admin_dashboard_auto_syncs_expired_tenant_status(): void
    {
        $admin = User::query()->create([
            'name' => 'Platform Admin',
            'email' => 'platform-admin@example.com',
            'password' => 'secret123',
            'is_platform_admin' => true,
        ]);

        $plan = Plan::query()->where('code', 'basic')->firstOrFail();
        $tenant = Tenant::query()->create([
            'code' => 'tenant-auto-expired',
            'name' => 'Tenant Auto Expired',
            'owner_name' => 'Owner Auto',
            'owner_email' => 'owner-auto@example.com',
            'status' => 'trial',
        ]);

        $subscription = Subscription::query()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => 'trial',
            'started_at' => now()->subDays(20),
            'trial_ends_at' => now()->subDay(),
            'expired_at' => now()->subDay(),
        ]);

        $this->actingAs($admin)
            ->get(route('platform.admin.tenants.index'))
            ->assertOk();

        $tenant->refresh();
        $subscription->refresh();

        $this->assertSame('expired', $tenant->status);
        $this->assertSame('expired', $subscription->status);
        $this->assertDatabaseHas('platform_tenant_audits', [
            'tenant_id' => $tenant->id,
            'action' => 'tenant.status.updated',
        ]);
    }

    public function test_platform_sync_tenant_lifecycle_command_marks_expired_subscriptions_and_tenants(): void
    {
        $plan = Plan::query()->where('code', 'basic')->firstOrFail();
        $tenant = Tenant::query()->create([
            'code' => 'tenant-command-expired',
            'name' => 'Tenant Command Expired',
            'owner_name' => 'Owner Command',
            'owner_email' => 'owner-command@example.com',
            'status' => 'active',
        ]);

        Subscription::query()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'started_at' => now()->subDays(45),
            'trial_ends_at' => now()->subDays(2),
            'expired_at' => now()->subDays(2),
        ]);

        $this->artisan('platform:sync-tenant-lifecycle')
            ->expectsOutput('Tenant lifecycle sync selesai. Tenant yang diperbarui: 1')
            ->assertSuccessful();

        $tenant->refresh();
        $subscription = $tenant->subscriptions()->latest('id')->first();

        $this->assertSame('expired', $tenant->status);
        $this->assertSame('expired', $subscription?->status);
    }
}
