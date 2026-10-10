<?php

namespace Tests\Feature;

use App\Livewire\Platform\Admin\TenantManager;
use App\Mail\PlatformTenantAlert;
use App\Mail\PlatformTenantOwnerAlert;
use App\Models\Platform\Plan;
use App\Models\Platform\Tenant;
use App\Models\Platform\TenantAudit;
use App\Models\Platform\TenantDatabaseConfig;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use PDO;
use Tests\TestCase;

class PlatformAdminTenantDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_platform_login(): void
    {
        $this->get(route('platform.admin.tenants.index'))
            ->assertRedirect(route('platform.login'));
    }

    public function test_platform_admin_can_view_the_livewire_tenant_dashboard(): void
    {
        $admin = User::query()->create([
            'name' => 'Platform Admin',
            'email' => 'platform-admin@example.com',
            'password' => 'secret123',
            'is_platform_admin' => true,
        ]);

        Tenant::query()->create([
            'code' => 'dashboard-check',
            'name' => 'Dashboard Check Tenant',
            'owner_name' => 'Owner Dashboard',
            'owner_email' => 'dashboard-check@example.com',
            'status' => 'pending_setup',
        ]);

        $this->actingAs($admin)
            ->get(route('platform.admin.tenants.index'))
            ->assertOk()
            ->assertSee('Tenant Manager')
            ->assertSee('Pusat Monitoring Tenant')
            ->assertSee('Inbox Operasional')
            ->assertSee('Ringkasan')
            ->assertSee('Dashboard Check Tenant');
    }

    public function test_platform_admin_dashboard_still_loads_when_pending_registration_table_is_missing(): void
    {
        $admin = User::query()->create([
            'name' => 'Platform Admin',
            'email' => 'platform-admin@example.com',
            'password' => 'secret123',
            'is_platform_admin' => true,
        ]);

        Tenant::query()->create([
            'code' => 'dashboard-safe-check',
            'name' => 'Dashboard Safe Tenant',
            'owner_name' => 'Owner Safe',
            'owner_email' => 'dashboard-safe@example.com',
            'status' => 'pending_setup',
        ]);

        Schema::dropIfExists('platform_pending_registrations');

        $this->actingAs($admin)
            ->get(route('platform.admin.tenants.index'))
            ->assertOk()
            ->assertSee('Tenant Manager')
            ->assertSee('Dashboard Safe Tenant');
    }

    public function test_platform_admin_can_update_tenant_profile_from_dashboard(): void
    {
        $admin = User::query()->create([
            'name' => 'Platform Admin',
            'email' => 'platform-admin@example.com',
            'password' => 'secret123',
            'is_platform_admin' => true,
        ]);

        $tenant = Tenant::query()->create([
            'code' => 'tenant-profile-edit',
            'name' => 'Tenant Lama',
            'owner_name' => 'Owner Lama',
            'owner_email' => 'owner-lama@example.com',
            'owner_phone' => '0812000001',
            'status' => 'trial',
            'notes' => 'Catatan lama',
        ]);

        $this->actingAs($admin)
            ->post(route('platform.admin.tenants.profile', $tenant), [
                'tenant_name' => 'Tenant Baru',
                'owner_name' => 'Owner Baru',
                'owner_phone' => '0812333444',
                'notes' => 'Catatan baru untuk support',
            ])
            ->assertRedirect();

        $tenant->refresh();

        $this->assertSame('Tenant Baru', $tenant->name);
        $this->assertSame('Owner Baru', $tenant->owner_name);
        $this->assertSame('0812333444', $tenant->owner_phone);
        $this->assertSame('Catatan baru untuk support', $tenant->notes);
        $this->assertDatabaseHas('tenant_provision_logs', [
            'tenant_id' => $tenant->id,
            'step' => 'tenant-profile-update',
            'status' => 'done',
        ]);
        $this->assertDatabaseHas('platform_tenant_audits', [
            'tenant_id' => $tenant->id,
            'action' => 'tenant.profile.updated',
        ]);
    }

    public function test_platform_admin_cannot_renew_tenant_subscription_directly_from_dashboard(): void
    {
        $admin = User::query()->create([
            'name' => 'Platform Admin',
            'email' => 'platform-admin@example.com',
            'password' => 'secret123',
            'is_platform_admin' => true,
        ]);

        $plan = Plan::query()->where('code', 'basic')->firstOrFail();
        $tenant = Tenant::query()->create([
            'code' => 'tenant-renew',
            'name' => 'Tenant Renew',
            'owner_name' => 'Owner Renew',
            'owner_email' => 'renew-owner@example.com',
            'status' => 'expired',
        ]);
        $subscription = $tenant->subscriptions()->create([
            'plan_id' => $plan->id,
            'status' => 'expired',
            'started_at' => now()->subDays(30),
            'trial_ends_at' => now()->subDay(),
            'expired_at' => now()->subDay(),
        ]);

        $this->actingAs($admin)
            ->post(route('platform.admin.tenants.renew', $tenant), [
                'renew_days' => 30,
            ])
            ->assertRedirect();

        $tenant->refresh();
        $subscription->refresh();

        $this->assertSame('expired', $tenant->status);
        $this->assertSame('expired', $subscription->status);
        $this->assertTrue($subscription->expired_at !== null && $subscription->expired_at->isPast());
        $this->assertDatabaseMissing('tenant_provision_logs', [
            'tenant_id' => $tenant->id,
            'step' => 'subscription-renewed',
        ]);
        $this->assertDatabaseHas('platform_tenant_audits', [
            'tenant_id' => $tenant->id,
            'action' => 'tenant.subscription.renewal_blocked',
        ]);
    }

    public function test_platform_admin_cannot_suspend_tenant_when_shift_is_still_open(): void
    {
        $admin = User::query()->create([
            'name' => 'Platform Admin',
            'email' => 'platform-admin@example.com',
            'password' => 'secret123',
            'is_platform_admin' => true,
        ]);

        $tenant = Tenant::query()->create([
            'code' => 'tenant-open-shift',
            'name' => 'Tenant Open Shift',
            'owner_name' => 'Owner Shift',
            'owner_email' => 'shift-owner@example.com',
            'status' => 'active',
        ]);

        $directory = storage_path('framework/testing/platform-tenant-open-shift-controller');
        File::deleteDirectory($directory);
        File::ensureDirectoryExists($directory);
        $databasePath = $directory.DIRECTORY_SEPARATOR.'tenant-open-shift.sqlite';
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
        $pdo->exec('INSERT INTO shifts (closed_at) VALUES (NULL)');

        $this->actingAs($admin)
            ->post(route('platform.admin.tenants.status', $tenant), [
                'status_update' => 'suspended',
            ])
            ->assertRedirect()
            ->assertSessionHas('platform_admin_error', 'Tenant tidak bisa ditangguhkan karena masih ada 1 shift yang terbuka. Tutup semua shift terlebih dahulu.');

        $this->assertSame('active', $tenant->fresh()->status);
        $this->assertDatabaseMissing('tenant_provision_logs', [
            'tenant_id' => $tenant->id,
            'step' => 'admin-status-update',
        ]);

        File::deleteDirectory($directory);
    }

    public function test_platform_admin_can_see_operational_inbox_and_summary_cards(): void
    {
        $admin = User::query()->create([
            'name' => 'Platform Admin',
            'email' => 'platform-admin@example.com',
            'password' => 'secret123',
            'is_platform_admin' => true,
        ]);

        $plan = Plan::query()->where('code', 'basic')->firstOrFail();

        $activeTenant = Tenant::query()->create([
            'code' => 'active-today',
            'name' => 'Tenant Baru Aktif',
            'owner_name' => 'Owner Aktif',
            'owner_email' => 'aktif@example.com',
            'status' => 'active',
            'onboarded_at' => now(),
        ]);
        $activeTenant->subscriptions()->create([
            'plan_id' => $plan->id,
            'status' => 'active',
            'started_at' => now()->subDays(7),
            'trial_ends_at' => now()->addDays(10),
            'expired_at' => now()->addDays(10),
        ]);

        $expiringTenant = Tenant::query()->create([
            'code' => 'expiring-soon',
            'name' => 'Tenant Mau Expire',
            'owner_name' => 'Owner Mau Expire',
            'owner_email' => 'expiring@example.com',
            'status' => 'trial',
        ]);
        $expiringTenant->subscriptions()->create([
            'plan_id' => $plan->id,
            'status' => 'trial',
            'started_at' => now()->subDays(10),
            'trial_ends_at' => now()->addDays(2),
            'expired_at' => now()->addDays(2),
        ]);

        $expiredTenant = Tenant::query()->create([
            'code' => 'expired-tenant',
            'name' => 'Tenant Expired',
            'owner_name' => 'Owner Expired',
            'owner_email' => 'expired@example.com',
            'status' => 'expired',
        ]);
        $expiredTenant->subscriptions()->create([
            'plan_id' => $plan->id,
            'status' => 'expired',
            'started_at' => now()->subDays(30),
            'trial_ends_at' => now()->subDay(),
            'expired_at' => now()->subDay(),
        ]);

        $renewedTenant = Tenant::query()->create([
            'code' => 'renewed-tenant',
            'name' => 'Tenant Diperpanjang',
            'owner_name' => 'Owner Renewed',
            'owner_email' => 'renewed@example.com',
            'status' => 'active',
        ]);
        $renewedSubscription = $renewedTenant->subscriptions()->create([
            'plan_id' => $plan->id,
            'status' => 'active',
            'started_at' => now()->subDays(30),
            'trial_ends_at' => now()->addDays(20),
            'expired_at' => now()->addDays(20),
        ]);
        $renewedSubscription->timestamps = false;
        $renewedSubscription->forceFill([
            'created_at' => now()->subDays(30),
            'updated_at' => now(),
        ])->save();

        TenantAudit::query()->create([
            'tenant_id' => $activeTenant->id,
            'actor_id' => $admin->id,
            'action' => 'tenant.plan.updated',
            'summary' => 'Paket tenant diperbarui dari panel admin.',
            'changes' => ['after' => ['plan_name' => 'Basic']],
            'metadata' => ['actor_email' => $admin->email],
        ]);

        $this->actingAs($admin)
            ->get(route('platform.admin.tenants.index'))
            ->assertOk()
            ->assertSee('Baru Aktif')
            ->assertSee('Mau Expire')
            ->assertSee('Expired')
            ->assertSee('Perpanjangan Terbaru')
            ->assertSee('Inbox Operasional')
            ->assertSee('Butuh Perhatian')
            ->assertSee('Tenant Baru Aktif')
            ->assertSee('Tenant Mau Expire')
            ->assertSee('Tenant Expired')
            ->assertSee('Update admin terbaru');

        $this->actingAs($admin)
            ->get(route('platform.admin.tenants.index', ['inbox' => 'attention']))
            ->assertOk()
            ->assertSee('Tenant Mau Expire')
            ->assertSee('Tenant Expired')
            ->assertDontSee('Update admin terbaru');

        $this->actingAs($admin)
            ->get(route('platform.admin.tenants.index', ['lifecycle' => 'expired']))
            ->assertOk()
            ->assertSee('Tenant Expired')
            ->assertSee('Siklus: Expired');
    }

    public function test_platform_admin_tenant_detail_uses_tabbed_panels(): void
    {
        $admin = User::query()->create([
            'name' => 'Platform Admin',
            'email' => 'platform-admin@example.com',
            'password' => 'secret123',
            'is_platform_admin' => true,
        ]);

        $tenant = Tenant::query()->create([
            'code' => 'tab-check',
            'name' => 'Tab Check Tenant',
            'owner_name' => 'Owner Tab',
            'owner_email' => 'tab-check@example.com',
            'status' => 'pending_setup',
        ]);

        $this->actingAs($admin)
            ->get(route('platform.admin.tenants.index', ['tenant' => $tenant->id]))
            ->assertOk()
            ->assertSee('Tab Check Tenant')
            ->assertSee('Ringkasan')
            ->assertSee('Recovery')
            ->assertSee('Langganan')
            ->assertSee('Penagihan')
            ->assertSee('Audit');

        Livewire::test(TenantManager::class, ['selectedTenantId' => (string) $tenant->id])
            ->call('setTab', 'billing')
            ->assertSet('activeTab', 'billing')
            ->call('setTab', 'not-a-real-tab')
            ->assertSet('activeTab', 'billing');
    }

    public function test_platform_refresh_tenant_events_command_creates_system_audit_events(): void
    {
        Mail::fake();

        $admin = User::query()->create([
            'name' => 'Platform Admin',
            'email' => 'platform-admin@example.com',
            'password' => 'secret123',
            'is_platform_admin' => true,
        ]);

        $plan = Plan::query()->where('code', 'basic')->firstOrFail();

        $expiringTenant = Tenant::query()->create([
            'code' => 'system-expiring',
            'name' => 'System Expiring Tenant',
            'owner_name' => 'Owner Expiring',
            'owner_email' => 'system-expiring@example.com',
            'status' => 'trial',
        ]);
        $expiringTenant->subscriptions()->create([
            'plan_id' => $plan->id,
            'status' => 'trial',
            'started_at' => now()->subDays(10),
            'trial_ends_at' => now()->addDay(),
            'expired_at' => now()->addDay(),
        ]);

        $expiredTenant = Tenant::query()->create([
            'code' => 'system-expired',
            'name' => 'System Expired Tenant',
            'owner_name' => 'Owner Expired',
            'owner_email' => 'system-expired@example.com',
            'status' => 'expired',
        ]);
        $expiredTenant->subscriptions()->create([
            'plan_id' => $plan->id,
            'status' => 'expired',
            'started_at' => now()->subDays(20),
            'trial_ends_at' => now()->subDay(),
            'expired_at' => now()->subDay(),
        ]);

        $renewedTenant = Tenant::query()->create([
            'code' => 'system-renewed',
            'name' => 'System Renewed Tenant',
            'owner_name' => 'Owner Renewed',
            'owner_email' => 'system-renewed@example.com',
            'status' => 'active',
        ]);
        $renewedSubscription = $renewedTenant->subscriptions()->create([
            'plan_id' => $plan->id,
            'status' => 'active',
            'started_at' => now()->subDays(14),
            'trial_ends_at' => now()->addDays(14),
            'expired_at' => now()->addDays(14),
        ]);
        $renewedSubscription->timestamps = false;
        $renewedSubscription->forceFill([
            'created_at' => now()->subDays(14),
            'updated_at' => now(),
        ])->save();

        $this->artisan('platform:refresh-tenant-events --days=3')
            ->assertExitCode(0);

        $this->assertDatabaseHas('platform_tenant_audits', [
            'tenant_id' => $expiringTenant->id,
            'action' => 'tenant.subscription.expiring',
        ]);
        $this->assertDatabaseHas('platform_tenant_audits', [
            'tenant_id' => $expiredTenant->id,
            'action' => 'tenant.subscription.expired',
        ]);
        $this->assertDatabaseHas('platform_tenant_audits', [
            'tenant_id' => $renewedTenant->id,
            'action' => 'tenant.subscription.renewed',
        ]);
        Mail::assertSent(PlatformTenantAlert::class, 3);
        Mail::assertSent(PlatformTenantOwnerAlert::class, 2);
        Mail::assertSent(PlatformTenantAlert::class, function (PlatformTenantAlert $mail) use ($admin, $expiringTenant) {
            return $mail->hasTo($admin->email)
                && $mail->tenant->is($expiringTenant);
        });
        Mail::assertSent(PlatformTenantOwnerAlert::class, function (PlatformTenantOwnerAlert $mail) use ($expiringTenant) {
            return $mail->hasTo($expiringTenant->owner_email)
                && $mail->tenant->is($expiringTenant)
                && $mail->renewalUrl === route('platform.renewal.create', ['tenant' => $expiringTenant->code]);
        });
        $this->assertDatabaseHas('platform_inbox_items', [
            'tenant_id' => $expiringTenant->id,
            'category' => 'attention',
            'event_key' => 'tenant.subscription.expiring',
        ]);
        $this->assertDatabaseHas('platform_inbox_items', [
            'tenant_id' => $expiredTenant->id,
            'category' => 'attention',
            'event_key' => 'tenant.subscription.expired',
        ]);
        $this->assertDatabaseHas('platform_inbox_items', [
            'tenant_id' => $renewedTenant->id,
            'category' => 'renewal',
            'event_key' => 'tenant.subscription.renewed',
        ]);
    }

    public function test_platform_refresh_tenant_events_command_creates_staged_expiry_reminder(): void
    {
        Mail::fake();

        $plan = Plan::query()->where('code', 'basic')->firstOrFail();
        $tenant = Tenant::query()->create([
            'code' => 'stage-h7',
            'name' => 'Stage H7 Tenant',
            'owner_name' => 'Owner Stage',
            'owner_email' => 'stage-owner@example.com',
            'status' => 'trial',
        ]);
        $tenant->subscriptions()->create([
            'plan_id' => $plan->id,
            'status' => 'trial',
            'started_at' => now()->subDays(7),
            'trial_ends_at' => now()->addDays(7),
            'expired_at' => now()->addDays(7),
        ]);

        $this->artisan('platform:refresh-tenant-events --days=3')
            ->assertExitCode(0);

        $this->assertDatabaseHas('platform_tenant_audits', [
            'tenant_id' => $tenant->id,
            'action' => 'tenant.subscription.expiring',
        ]);
        $this->assertDatabaseHas('platform_inbox_items', [
            'tenant_id' => $tenant->id,
            'event_key' => 'tenant.subscription.expiring',
            'category' => 'attention',
        ]);

        $audit = TenantAudit::query()
            ->where('tenant_id', $tenant->id)
            ->where('action', 'tenant.subscription.expiring')
            ->first();

        $this->assertSame('h-7', $audit?->metadata['stage'] ?? null);
    }
}
