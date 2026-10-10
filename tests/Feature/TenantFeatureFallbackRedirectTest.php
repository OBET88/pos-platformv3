<?php

namespace Tests\Feature;

use App\Models\Platform\Plan;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class TenantFeatureFallbackRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_html_request_is_redirected_to_first_allowed_fallback_route(): void
    {
        $tenant = $this->makeTenantWithPlanFeatures([
            'menu_products',
        ]);

        Route::get('/_test/tenant/{tenant}/home', fn () => 'home')
            ->name('home');

        Route::middleware('tenant.feature:menu_dashboard')
            ->get('/_test/tenant/{tenant}/dashboard', fn () => 'dashboard')
            ->name('dashboard');

        Route::middleware('tenant.feature:menu_products')
            ->get('/_test/tenant/{tenant}/products', fn () => 'products')
            ->name('products.index');

        app('router')->getRoutes()->refreshNameLookups();

        $this->get(route('dashboard', ['tenant' => $tenant]))
            ->assertRedirect(route('home', ['tenant' => $tenant]))
            ->assertSessionHas('warning');
    }

    public function test_json_request_still_returns_forbidden_when_feature_is_missing(): void
    {
        $tenant = $this->makeTenantWithPlanFeatures([
            'menu_products',
        ]);

        Route::middleware('tenant.feature:menu_dashboard')
            ->get('/_test/tenant/{tenant}/dashboard-json', fn () => response()->json(['ok' => true]))
            ->name('tenant.dashboard.json');

        app('router')->getRoutes()->refreshNameLookups();

        $this->getJson(route('tenant.dashboard.json', ['tenant' => $tenant]))
            ->assertForbidden()
            ->assertSeeText('Fitur paket tidak tersedia untuk tenant ini.');
    }

    public function test_missing_feature_still_returns_forbidden_when_no_fallback_route_is_available(): void
    {
        $tenant = $this->makeTenantWithPlanFeatures([
            'menu_products',
        ]);

        Route::middleware('tenant.feature:menu_dashboard')
            ->get('/_test/tenant/{tenant}/dashboard-no-fallback', fn () => 'dashboard')
            ->name('tenant.dashboard.no-fallback');

        app('router')->getRoutes()->refreshNameLookups();

        $this->get(route('tenant.dashboard.no-fallback', ['tenant' => $tenant]))
            ->assertForbidden()
            ->assertSeeText('Fitur paket tidak tersedia untuk tenant ini.');
    }

    private function makeTenantWithPlanFeatures(array $features): Tenant
    {
        $plan = Plan::query()->create([
            'code' => 'custom-'.str()->lower(str()->random(6)),
            'name' => 'Custom Plan',
            'price' => 0,
            'billing_cycle' => 'monthly',
            'max_businesses' => 1,
            'max_branches' => 1,
            'max_users' => 3,
            'features_json' => $features,
            'is_active' => true,
        ]);

        $tenant = Tenant::query()->create([
            'code' => 'tenant-'.str()->lower(str()->random(6)),
            'name' => 'Tenant Redirect',
            'owner_name' => 'Owner Redirect',
            'owner_email' => 'owner-redirect@example.com',
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

        return $tenant->fresh(['subscriptions.plan']);
    }
}
