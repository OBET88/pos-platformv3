<?php

namespace Tests\Feature;

use App\Models\Platform\Plan;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PlatformTenantFeatureAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_resolves_features_from_active_subscription(): void
    {
        $basic = Plan::query()->where('code', 'basic')->firstOrFail();
        $pro = Plan::query()->where('code', 'pro')->firstOrFail();

        $tenant = Tenant::query()->create([
            'code' => 'feature-tenant',
            'name' => 'Feature Tenant',
            'owner_name' => 'Owner Feature',
            'owner_email' => 'owner-feature@example.com',
            'status' => 'active',
        ]);

        Subscription::query()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $basic->id,
            'status' => 'expired',
            'started_at' => now()->subDays(30),
            'expired_at' => now()->subDay(),
            'trial_ends_at' => now()->subDay(),
        ]);

        Subscription::query()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $pro->id,
            'status' => 'active',
            'started_at' => now()->subDay(),
            'expired_at' => now()->addDays(29),
            'trial_ends_at' => now()->addDays(29),
        ]);

        $tenant = $tenant->fresh(['subscriptions.plan']);

        $this->assertTrue($tenant->hasFeature('production'));
        $this->assertTrue($tenant->hasFeature('advanced_reports'));
        $this->assertFalse($tenant->hasFeature('priority_support'));
        $this->assertSame($pro->id, $tenant->activeSubscription()?->plan_id);
    }

    public function test_trial_subscription_uses_trial_feature_plan_while_preserving_selected_plan(): void
    {
        $basic = Plan::query()->where('code', 'basic')->firstOrFail();
        $trial = Plan::query()->where('code', 'trial')->firstOrFail();

        $trial->forceFill([
            'features_json' => array_values(array_unique([
                ...($trial->features_json ?? []),
                'advanced_reports',
                'priority_support',
                'accounting',
            ])),
        ])->save();

        $tenant = Tenant::query()->create([
            'code' => 'trial-feature-tenant',
            'name' => 'Trial Feature Tenant',
            'owner_name' => 'Owner Trial Feature',
            'owner_email' => 'owner-trial-feature@example.com',
            'status' => 'trial',
        ]);

        Subscription::query()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $basic->id,
            'status' => 'trial',
            'started_at' => now()->subHour(),
            'expired_at' => now()->addDays(14),
            'trial_ends_at' => now()->addDays(14),
        ]);

        $tenant = $tenant->fresh(['subscriptions.plan']);

        $this->assertSame($basic->id, $tenant->activeSubscription()?->plan_id);
        $this->assertSame('trial', $tenant->activeSubscription()?->status);
        $this->assertSame('trial', $tenant->activeSubscription()?->featurePlan()?->code);
        $this->assertTrue($tenant->hasFeature('advanced_reports'));
        $this->assertTrue($tenant->hasFeature('priority_support'));
        $this->assertTrue($tenant->hasFeature('accounting'));
    }

    public function test_tenant_feature_middleware_blocks_unavailable_features(): void
    {
        $tenant = $this->makeTenantWithPlan('basic');

        Route::middleware('tenant.feature:accounting')->get('/_test/tenant/{tenant}/accounting', function (Tenant $tenant) {
            return response()->json(['tenant' => $tenant->code]);
        });

        $this->get('/_test/tenant/'.$tenant->id.'/accounting')
            ->assertForbidden()
            ->assertSeeText('Fitur paket tidak tersedia untuk tenant ini.');
    }

    public function test_tenant_feature_middleware_allows_available_features(): void
    {
        $tenant = $this->makeTenantWithPlan('pro');

        Route::middleware('tenant.feature:accounting')->get('/_test/tenant/{tenant}/accounting-allowed', function (Request $request) {
            $tenant = $request->attributes->get('tenant');

            return response()->json(['tenant' => $tenant?->code, 'feature' => 'accounting']);
        });

        $this->get('/_test/tenant/'.$tenant->id.'/accounting-allowed')
            ->assertOk()
            ->assertJson([
                'tenant' => $tenant->code,
                'feature' => 'accounting',
            ]);
    }

    public function test_expired_tenant_only_keeps_subscription_center_features(): void
    {
        $plan = Plan::query()->where('code', 'pro')->firstOrFail();

        $tenant = Tenant::query()->create([
            'code' => 'expired-renewal-only',
            'name' => 'Expired Renewal Only',
            'owner_name' => 'Owner Expired',
            'owner_email' => 'owner-expired@example.com',
            'status' => 'expired',
        ]);

        Subscription::query()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => 'expired',
            'started_at' => now()->subDays(30),
            'expired_at' => now()->subDay(),
            'trial_ends_at' => now()->subDay(),
        ]);

        $tenant = $tenant->fresh(['subscriptions.plan']);

        $this->assertTrue($tenant->hasFeature('menu_subscription_center'));
        $this->assertTrue($tenant->hasFeature('subscription_center'));
        $this->assertFalse($tenant->hasFeature('accounting'));
        $this->assertFalse($tenant->hasFeature('advanced_reports'));
    }

    private function makeTenantWithPlan(string $planCode): Tenant
    {
        $plan = Plan::query()->where('code', $planCode)->firstOrFail();

        $tenant = Tenant::query()->create([
            'code' => 'tenant-'.$planCode.'-'.str()->lower(str()->random(6)),
            'name' => 'Tenant '.strtoupper($planCode),
            'owner_name' => 'Owner '.strtoupper($planCode),
            'owner_email' => $planCode.'@example.com',
            'status' => 'active',
        ]);

        Subscription::query()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'started_at' => now()->subDay(),
            'expired_at' => now()->addDays(30),
            'trial_ends_at' => now()->addDays(30),
        ]);

        return $tenant;
    }
}
