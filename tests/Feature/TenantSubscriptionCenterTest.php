<?php

namespace Tests\Feature;

use App\Models\Platform\Plan;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;
use App\Models\Platform\TenantDomain;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantSubscriptionCenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_expired_tenant_can_open_subscription_center_from_its_domain(): void
    {
        $plan = Plan::query()->create([
            'code' => 'basic-subscription-center',
            'name' => 'Basic Subscription Center',
            'price' => 100000,
            'billing_cycle' => 'monthly',
            'max_businesses' => 1,
            'max_branches' => 1,
            'max_users' => 3,
            'features_json' => ['pos'],
            'is_active' => true,
        ]);

        $upgradePlan = Plan::query()->create([
            'code' => 'plus-subscription-center',
            'name' => 'Plus Subscription Center',
            'price' => 250000,
            'billing_cycle' => 'monthly',
            'max_businesses' => 3,
            'max_branches' => 5,
            'max_users' => 15,
            'features_json' => ['pos', 'stock'],
            'is_active' => true,
        ]);

        $tenant = Tenant::query()->create([
            'code' => 'pt-malam',
            'name' => 'PT Malam',
            'owner_name' => 'Owner PT Malam',
            'owner_email' => 'owner@ptmalam.test',
            'status' => 'expired',
        ]);

        TenantDomain::query()->create([
            'tenant_id' => $tenant->id,
            'domain' => 'pt-malam.poslive.test',
            'type' => 'subdomain',
            'is_primary' => true,
        ]);

        Subscription::query()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => 'expired',
            'started_at' => now()->subMonths(2),
            'trial_ends_at' => now()->subMonth(),
            'expired_at' => now()->subDay(),
        ]);

        $response = $this->get('http://pt-malam.poslive.test/subscription');

        $response->assertOk();
        $response->assertSee('PT Malam');
        $response->assertSee('EXPIRED');
        $response->assertSee('Perpanjang Sekarang');
        $response->assertSee('Lihat Opsi Upgrade');
        $response->assertSee('Buka Histori Pesanan');
        $response->assertSee($upgradePlan->name);
    }
}
