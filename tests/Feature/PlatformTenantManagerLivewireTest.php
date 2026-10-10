<?php

namespace Tests\Feature;

use App\Livewire\Platform\Admin\TenantManager;
use App\Models\Platform\Plan;
use App\Models\Platform\Tenant;
use App\Models\Platform\TenantDatabaseConfig;
use App\Models\Platform\TenantDomain;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Livewire\Livewire;
use PDO;
use Tests\TestCase;

class PlatformTenantManagerLivewireTest extends TestCase
{
    use RefreshDatabase;

    public function test_platform_admin_can_update_primary_domain_from_livewire(): void
    {
        $admin = User::query()->create([
            'name' => 'Platform Admin',
            'email' => 'platform-admin@example.com',
            'password' => 'secret123',
            'is_platform_admin' => true,
        ]);

        $tenant = Tenant::query()->create([
            'code' => 'livewire-tenant',
            'name' => 'Livewire Tenant',
            'owner_name' => 'Owner Livewire',
            'owner_email' => 'livewire-owner@example.com',
            'status' => 'active',
        ]);

        $tenant->domains()->create([
            'domain' => 'old.example.test',
            'type' => 'subdomain',
            'is_primary' => true,
        ]);

        $this->actingAs($admin);

        Livewire::test(TenantManager::class)
            ->call('selectTenant', $tenant->id)
            ->set('primaryDomain', 'new.example.test')
            ->call('updatePrimaryDomain')
            ->assertHasNoErrors();

        $this->assertSame('new.example.test', TenantDomain::query()->where('tenant_id', $tenant->id)->where('is_primary', true)->value('domain'));
        $this->assertDatabaseHas('tenant_provision_logs', [
            'tenant_id' => $tenant->id,
            'step' => 'primary-domain-update',
            'status' => 'done',
        ]);
        $this->assertDatabaseHas('platform_tenant_audits', [
            'tenant_id' => $tenant->id,
            'action' => 'tenant.domain.updated',
        ]);
    }

    public function test_platform_admin_can_assign_enterprise_as_trial_promo_package(): void
    {
        $admin = User::query()->create([
            'name' => 'Platform Admin',
            'email' => 'platform-admin@example.com',
            'password' => 'secret123',
            'is_platform_admin' => true,
        ]);

        $trial = Plan::query()->where('code', 'trial')->firstOrFail();
        $enterprise = Plan::query()->where('code', 'enterprise')->firstOrFail();

        $tenant = Tenant::query()->create([
            'code' => 'promo-enterprise',
            'name' => 'Promo Enterprise Tenant',
            'owner_name' => 'Owner Promo',
            'owner_email' => 'promo-owner@example.com',
            'status' => 'trial',
        ]);

        $subscription = $tenant->subscriptions()->create([
            'plan_id' => $trial->id,
            'status' => 'trial',
            'started_at' => now(),
            'trial_ends_at' => now()->addDays(14),
            'expired_at' => now()->addDays(14),
        ]);

        $this->actingAs($admin);

        Livewire::test(TenantManager::class)
            ->call('selectTenant', $tenant->id)
            ->set('statusUpdate', 'trial')
            ->set('selectedPlanId', (string) $enterprise->id)
            ->set('trialPlanDays', '7')
            ->call('updateSubscriptionPlan')
            ->assertHasNoErrors();

        $subscription->refresh();

        $this->assertSame('trial', $tenant->fresh()->status);
        $this->assertSame($enterprise->id, $subscription->plan_id);
        $this->assertSame('trial', $subscription->status);
        $this->assertNotNull($subscription->trial_ends_at);
        $this->assertTrue($subscription->trial_ends_at->between(now()->addDays(6), now()->addDays(8)));
        $this->assertDatabaseHas('tenant_provision_logs', [
            'tenant_id' => $tenant->id,
            'step' => 'subscription-plan-update',
            'status' => 'done',
        ]);
        $this->assertDatabaseHas('platform_tenant_audits', [
            'tenant_id' => $tenant->id,
            'action' => 'tenant.plan.updated',
        ]);
    }

    public function test_platform_admin_cannot_renew_subscription_directly_from_livewire(): void
    {
        $admin = User::query()->create([
            'name' => 'Platform Admin',
            'email' => 'platform-admin@example.com',
            'password' => 'secret123',
            'is_platform_admin' => true,
        ]);

        $plan = Plan::query()->where('code', 'basic')->firstOrFail();
        $tenant = Tenant::query()->create([
            'code' => 'renewal-blocked-livewire',
            'name' => 'Renewal Blocked Tenant',
            'owner_name' => 'Owner Renewal',
            'owner_email' => 'renewal-blocked@example.com',
            'status' => 'expired',
        ]);

        $subscription = $tenant->subscriptions()->create([
            'plan_id' => $plan->id,
            'status' => 'expired',
            'started_at' => now()->subDays(30),
            'trial_ends_at' => now()->subDay(),
            'expired_at' => now()->subDay(),
        ]);

        $this->actingAs($admin);

        Livewire::test(TenantManager::class)
            ->call('selectTenant', $tenant->id)
            ->set('renewDays', 30)
            ->call('renewSubscription')
            ->assertHasErrors(['renewDays']);

        $tenant->refresh();
        $subscription->refresh();

        $this->assertSame('expired', $tenant->status);
        $this->assertSame('expired', $subscription->status);
        $this->assertDatabaseMissing('tenant_provision_logs', [
            'tenant_id' => $tenant->id,
            'step' => 'subscription-renewed',
        ]);
        $this->assertDatabaseHas('platform_tenant_audits', [
            'tenant_id' => $tenant->id,
            'action' => 'tenant.subscription.renewal_blocked',
        ]);
    }

    public function test_platform_admin_cannot_suspend_tenant_from_livewire_when_shift_is_still_open(): void
    {
        $admin = User::query()->create([
            'name' => 'Platform Admin',
            'email' => 'platform-admin@example.com',
            'password' => 'secret123',
            'is_platform_admin' => true,
        ]);

        $tenant = Tenant::query()->create([
            'code' => 'livewire-open-shift',
            'name' => 'Livewire Open Shift',
            'owner_name' => 'Owner Shift',
            'owner_email' => 'livewire-shift@example.com',
            'status' => 'active',
        ]);

        $directory = storage_path('framework/testing/platform-tenant-open-shift-livewire');
        File::deleteDirectory($directory);
        File::ensureDirectoryExists($directory);
        $databasePath = $directory.DIRECTORY_SEPARATOR.'livewire-open-shift.sqlite';
        touch($databasePath);

        TenantDatabaseConfig::query()->create([
            'tenant_id' => $tenant->id,
            'connection_name' => 'tenant_'.$tenant->code,
            'db_host' => 'sqlite',
            'db_name' => $databasePath,
            'status' => 'provisioned',
        ]);

        $pdo = new PDO('sqlite:'.$databasePath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec('CREATE TABLE shifts (id INTEGER PRIMARY KEY AUTOINCREMENT, closed_at TEXT NULL)');
        $pdo->exec("INSERT INTO shifts (closed_at) VALUES (NULL)");

        $this->actingAs($admin);

        Livewire::test(TenantManager::class)
            ->call('selectTenant', $tenant->id)
            ->set('statusUpdate', 'suspended')
            ->call('updateTenantStatus')
            ->assertHasNoErrors();

        $this->assertSame('active', $tenant->fresh()->status);
        $this->assertDatabaseMissing('tenant_provision_logs', [
            'tenant_id' => $tenant->id,
            'step' => 'admin-status-update',
        ]);

        File::deleteDirectory($directory);
    }
}
