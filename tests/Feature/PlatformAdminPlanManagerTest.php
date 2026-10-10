<?php

namespace Tests\Feature;

use App\Livewire\Platform\Admin\PlanManager;
use App\Models\Platform\Plan;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class PlatformAdminPlanManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_platform_admin_can_open_plan_manager_page(): void
    {
        $admin = User::query()->create([
            'name' => 'Platform Admin',
            'email' => 'platform-admin@example.com',
            'password' => 'secret123',
            'is_platform_admin' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('platform.admin.plans.index'))
            ->assertOk()
            ->assertSee('Kelola daftar paket, harga, limit, dan menu tiap paket.')
            ->assertSeeText('Tampil di Form Daftar')
            ->assertSeeText('Total Paket')
            ->assertSeeText('Paket & Fitur');
    }

    public function test_platform_admin_can_open_plan_mapping_page(): void
    {
        $admin = User::query()->create([
            'name' => 'Platform Admin',
            'email' => 'platform-admin@example.com',
            'password' => 'secret123',
            'is_platform_admin' => true,
        ]);

        $plan = Plan::query()->where('code', 'basic')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('platform.admin.plans.mapping', $plan))
            ->assertOk()
            ->assertSee('Atur tool dan menu yang benar-benar dibutuhkan paket ini.')
            ->assertSeeText('Menu Mapping')
            ->assertSeeText('Membership')
            ->assertSeeText($plan->name);
    }

    public function test_platform_admin_can_open_plan_edit_page(): void
    {
        $admin = User::query()->create([
            'name' => 'Platform Admin',
            'email' => 'platform-admin@example.com',
            'password' => 'secret123',
            'is_platform_admin' => true,
        ]);

        $plan = Plan::query()->where('code', 'basic')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('platform.admin.plans.edit', $plan))
            ->assertOk()
            ->assertSee('Halaman ini khusus untuk identitas paket, harga, limit, dan status aktif.')
            ->assertSeeText('Edit Paket')
            ->assertSeeText($plan->name);
    }

    public function test_platform_admin_can_update_plan_features_from_livewire(): void
    {
        $admin = User::query()->create([
            'name' => 'Platform Admin',
            'email' => 'platform-admin@example.com',
            'password' => 'secret123',
            'is_platform_admin' => true,
        ]);

        $plan = Plan::query()->where('code', 'basic')->firstOrFail();

        $this->actingAs($admin);

        Livewire::test(PlanManager::class)
            ->set('planFeatureInputs.'.$plan->id, ['pos', 'stock', 'advanced_reports'])
            ->set('planCustomFeatureInputs.'.$plan->id, "multi_warehouse\ncustomer_display")
            ->call('savePlanFeatures', $plan->id);

        $this->assertSame(
            ['pos', 'stock', 'advanced_reports', 'multi_warehouse', 'customer_display'],
            $plan->fresh()->features_json
        );
    }

    public function test_modern_menu_keys_are_expanded_to_legacy_keys_when_saving_plan_features(): void
    {
        $admin = User::query()->create([
            'name' => 'Platform Admin',
            'email' => 'platform-admin@example.com',
            'password' => 'secret123',
            'is_platform_admin' => true,
        ]);

        $plan = Plan::query()->where('code', 'basic')->firstOrFail();

        $this->actingAs($admin);

        Livewire::test(PlanManager::class)
            ->set('planFeatureInputs.'.$plan->id, ['menu_dashboard', 'menu_reports'])
            ->set('planCustomFeatureInputs.'.$plan->id, '')
            ->call('savePlanFeatures', $plan->id);

        $this->assertSame(
            ['menu_dashboard', 'menu_reports', 'pos', 'basic_reports'],
            $plan->fresh()->features_json
        );
    }

    public function test_membership_feature_keys_are_expanded_to_legacy_keys_when_saving_plan_features(): void
    {
        $admin = User::query()->create([
            'name' => 'Platform Admin',
            'email' => 'platform-admin@example.com',
            'password' => 'secret123',
            'is_platform_admin' => true,
        ]);

        $plan = Plan::query()->where('code', 'basic')->firstOrFail();

        $this->actingAs($admin);

        Livewire::test(PlanManager::class)
            ->set('planFeatureInputs.'.$plan->id, ['menu_membership', 'membership_points', 'membership_redeem', 'membership_tier'])
            ->set('planCustomFeatureInputs.'.$plan->id, '')
            ->call('savePlanFeatures', $plan->id);

        $this->assertSame(
            ['menu_membership', 'membership_points', 'membership_redeem', 'membership_tier', 'menu_users', 'pos'],
            $plan->fresh()->features_json
        );
    }

    public function test_platform_admin_can_select_all_features_in_a_group(): void
    {
        $admin = User::query()->create([
            'name' => 'Platform Admin',
            'email' => 'platform-admin@example.com',
            'password' => 'secret123',
            'is_platform_admin' => true,
        ]);

        $plan = Plan::query()->where('code', 'basic')->firstOrFail();

        $this->actingAs($admin);

        Livewire::test(PlanManager::class)
            ->set('planFeatureInputs.'.$plan->id, [])
            ->call('selectGroupFeatures', $plan->id, 'Utama')
            ->assertSet('planFeatureInputs.'.$plan->id, ['menu_dashboard', 'menu_subscription_center']);
    }

    public function test_platform_admin_can_apply_plan_preset(): void
    {
        $admin = User::query()->create([
            'name' => 'Platform Admin',
            'email' => 'platform-admin@example.com',
            'password' => 'secret123',
            'is_platform_admin' => true,
        ]);

        $plan = Plan::query()->where('code', 'basic')->firstOrFail();

        $this->actingAs($admin);

        Livewire::test(PlanManager::class)
            ->set('planCustomFeatureInputs.'.$plan->id, 'multi_warehouse')
            ->call('applyPlanPreset', $plan->id, 'basic')
            ->assertSet('planCustomFeatureInputs.'.$plan->id, '')
            ->assertSet('planFeatureInputs.'.$plan->id, [
                'menu_dashboard',
                'menu_subscription_center',
                'menu_reports',
                'menu_product_reports',
                'menu_stock_in',
                'menu_stock_out',
                'menu_stock_conversion',
                'menu_shift_history_self',
                'menu_daily_report_self',
                'menu_products',
                'menu_categories',
                'menu_users',
                'menu_membership',
                'membership_points',
                'menu_shift_history_admin',
                'menu_daily_report_admin',
                'menu_branding',
                'subscription_center',
                'pos',
                'stock',
                'basic_reports',
            ]);
    }

    public function test_platform_admin_can_create_a_new_plan(): void
    {
        $admin = User::query()->create([
            'name' => 'Platform Admin',
            'email' => 'platform-admin@example.com',
            'password' => 'secret123',
            'is_platform_admin' => true,
        ]);

        $this->actingAs($admin);

        Livewire::test(PlanManager::class)
            ->set('newPlanForm.code', 'starter_plus')
            ->set('newPlanForm.name', 'Starter Plus')
            ->set('newPlanForm.price', '149000')
            ->set('newPlanForm.billing_cycle', 'monthly')
            ->set('newPlanForm.preset', 'basic')
            ->set('newPlanForm.max_businesses', 2)
            ->set('newPlanForm.max_branches', 3)
            ->set('newPlanForm.max_users', 10)
            ->set('newPlanForm.is_active', true)
            ->call('createPlan')
            ->assertHasNoErrors();

        $plan = Plan::query()->where('code', 'starter_plus')->first();

        $this->assertNotNull($plan);
        $this->assertSame('Starter Plus', $plan->name);
        $this->assertSame('149000.00', $plan->price);
        $this->assertSame('monthly', $plan->billing_cycle);
        $this->assertSame(2, $plan->max_businesses);
        $this->assertSame(3, $plan->max_branches);
        $this->assertSame(10, $plan->max_users);
        $this->assertTrue($plan->is_active);
        $this->assertContains('menu_dashboard', $plan->features_json);
        $this->assertContains('basic_reports', $plan->features_json);
    }

    public function test_platform_admin_can_update_plan_meta(): void
    {
        $admin = User::query()->create([
            'name' => 'Platform Admin',
            'email' => 'platform-admin@example.com',
            'password' => 'secret123',
            'is_platform_admin' => true,
        ]);

        $plan = Plan::query()->where('code', 'basic')->firstOrFail();

        $this->actingAs($admin);

        Livewire::test(PlanManager::class)
            ->set('planMetaInputs.'.$plan->id.'.code', 'basic_plus')
            ->set('planMetaInputs.'.$plan->id.'.name', 'Basic Plus')
            ->set('planMetaInputs.'.$plan->id.'.price', '199000')
            ->set('planMetaInputs.'.$plan->id.'.billing_cycle', 'quarterly')
            ->set('planMetaInputs.'.$plan->id.'.max_businesses', 2)
            ->set('planMetaInputs.'.$plan->id.'.max_branches', 4)
            ->set('planMetaInputs.'.$plan->id.'.max_users', 12)
            ->call('updatePlanMeta', $plan->id)
            ->assertHasNoErrors();

        $plan->refresh();

        $this->assertSame('basic_plus', $plan->code);
        $this->assertSame('Basic Plus', $plan->name);
        $this->assertSame('199000.00', $plan->price);
        $this->assertSame('quarterly', $plan->billing_cycle);
        $this->assertSame(2, $plan->max_businesses);
        $this->assertSame(4, $plan->max_branches);
        $this->assertSame(12, $plan->max_users);
    }

    public function test_platform_admin_can_toggle_plan_active_state(): void
    {
        $admin = User::query()->create([
            'name' => 'Platform Admin',
            'email' => 'platform-admin@example.com',
            'password' => 'secret123',
            'is_platform_admin' => true,
        ]);

        $plan = Plan::query()->where('code', 'basic')->firstOrFail();

        $this->actingAs($admin);

        Livewire::test(PlanManager::class)
            ->call('togglePlanActive', $plan->id);

        $this->assertFalse($plan->fresh()->is_active);
    }

    public function test_platform_admin_can_duplicate_plan(): void
    {
        $admin = User::query()->create([
            'name' => 'Platform Admin',
            'email' => 'platform-admin@example.com',
            'password' => 'secret123',
            'is_platform_admin' => true,
        ]);

        $plan = Plan::query()->where('code', 'basic')->firstOrFail();

        $this->actingAs($admin);

        Livewire::test(PlanManager::class)
            ->call('duplicatePlan', $plan->id);

        $duplicate = Plan::query()->where('code', 'basic_copy')->first();

        $this->assertNotNull($duplicate);
        $this->assertSame('Basic Copy', $duplicate->name);
        $this->assertFalse($duplicate->is_active);
        $this->assertSame($plan->features_json, $duplicate->features_json);
    }

    public function test_plan_deactivation_requires_confirmation_when_still_used_by_active_tenants(): void
    {
        $admin = User::query()->create([
            'name' => 'Platform Admin',
            'email' => 'platform-admin@example.com',
            'password' => 'secret123',
            'is_platform_admin' => true,
        ]);

        $plan = Plan::query()->where('code', 'basic')->firstOrFail();

        $tenant = Tenant::query()->create([
            'code' => 'tenant-basic',
            'name' => 'Tenant Basic',
            'owner_name' => 'Owner Basic',
            'owner_email' => 'owner-basic@example.com',
            'status' => 'active',
            'trial_ends_at' => Carbon::now()->addDays(14),
        ]);

        Subscription::query()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'started_at' => Carbon::now()->subDay(),
            'expired_at' => Carbon::now()->addDays(30),
        ]);

        $this->actingAs($admin);

        Livewire::test(PlanManager::class)
            ->call('togglePlanActive', $plan->id)
            ->assertSet('planDeactivationConfirmations.'.$plan->id, true);

        $this->assertTrue($plan->fresh()->is_active);
    }
}
