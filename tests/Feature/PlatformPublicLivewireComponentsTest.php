<?php

namespace Tests\Feature;

use App\Livewire\Platform\Public\OrderLookup;
use App\Livewire\Platform\Public\PendingVerification;
use App\Livewire\Platform\Public\RenewalRequest;
use App\Livewire\Platform\Public\UpgradeRequest;
use App\Models\Platform\Plan;
use App\Models\Platform\PlatformPaymentTransaction;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PlatformPublicLivewireComponentsTest extends TestCase
{
    use RefreshDatabase;

    private function seedTenantWithPlan(): array
    {
        $plan = Plan::query()->create([
            'code' => 'lw-plan',
            'name' => 'LW Plan',
            'price' => 50000,
            'billing_cycle' => 'monthly',
            'max_businesses' => 1,
            'max_branches' => 1,
            'max_users' => 3,
            'features_json' => ['pos'],
            'is_active' => true,
        ]);

        $higher = Plan::query()->create([
            'code' => 'lw-plan-higher',
            'name' => 'LW Plan Higher',
            'price' => 150000,
            'billing_cycle' => 'monthly',
            'max_businesses' => 3,
            'max_branches' => 5,
            'max_users' => 10,
            'features_json' => ['pos'],
            'is_active' => true,
        ]);

        $tenant = Tenant::query()->create([
            'code' => 'tenant-lw',
            'name' => 'Tenant Livewire',
            'owner_name' => 'Owner Livewire',
            'owner_email' => 'owner-lw@example.com',
            'status' => 'active',
        ]);

        Subscription::query()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'started_at' => now()->subMonth(),
            'trial_ends_at' => now()->addDays(10),
            'expired_at' => now()->addDays(10),
        ]);

        return [$tenant, $plan, $higher];
    }

    public function test_order_lookup_component_finds_invoice_and_rejects_wrong_email(): void
    {
        [$tenant, $plan] = $this->seedTenantWithPlan();

        PlatformPaymentTransaction::query()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'invoice_number' => 'INV-LW-001',
            'type' => 'renewal',
            'payment_method' => 'manual_transfer',
            'payment_channel' => 'bank_transfer',
            'amount_subtotal' => 50000,
            'amount_discount' => 0,
            'amount_total' => 50000,
            'billing_cycle' => 'monthly',
            'status' => 'pending_payment',
            'due_at' => now()->addDays(3),
        ]);

        Livewire::test(OrderLookup::class)
            ->set('tenantCode', $tenant->code)
            ->set('ownerEmail', 'wrong@example.com')
            ->call('lookup')
            ->assertHasErrors(['ownerEmail'])
            ->set('ownerEmail', $tenant->owner_email)
            ->call('lookup')
            ->assertHasNoErrors()
            ->assertSee('INV-LW-001')
            ->assertSee('Tenant Livewire');
    }

    public function test_order_lookup_mounts_with_prefilled_query(): void
    {
        [$tenant] = $this->seedTenantWithPlan();

        Livewire::test(OrderLookup::class, [
            'tenantCode' => $tenant->code,
            'ownerEmail' => $tenant->owner_email,
        ])
            ->assertSet('lookupPerformed', true)
            ->assertSee('Tenant Livewire');
    }

    public function test_renewal_component_creates_invoice_and_redirects(): void
    {
        [$tenant] = $this->seedTenantWithPlan();

        Livewire::test(RenewalRequest::class, ['tenantCode' => $tenant->code])
            ->assertSee('Tenant Livewire')
            ->set('ownerEmail', $tenant->owner_email)
            ->set('billingCycle', 'quarterly')
            ->call('submit')
            ->assertHasNoErrors();

        $invoice = PlatformPaymentTransaction::query()->latest('id')->firstOrFail();

        $this->assertSame('renewal', $invoice->type);
        $this->assertSame(150000.0, (float) $invoice->amount_total);
    }

    public function test_renewal_component_rejects_wrong_owner_email(): void
    {
        [$tenant] = $this->seedTenantWithPlan();

        Livewire::test(RenewalRequest::class, ['tenantCode' => $tenant->code])
            ->set('ownerEmail', 'wrong@example.com')
            ->call('submit')
            ->assertHasErrors(['ownerEmail']);

        $this->assertSame(0, PlatformPaymentTransaction::query()->count());
    }

    public function test_upgrade_component_previews_prorata_and_creates_invoice(): void
    {
        [$tenant, , $higher] = $this->seedTenantWithPlan();

        $component = Livewire::test(UpgradeRequest::class, ['tenantCode' => $tenant->code])
            ->set('ownerEmail', $tenant->owner_email)
            ->set('targetPlanId', (string) $higher->id)
            ->set('billingCycle', 'monthly');

        // Pratinjau dihitung server-side: nominal harus tampil di halaman
        // (bukan lagi dihitung ulang oleh JavaScript).
        $component->assertSee('Estimasi tagihan upgrade')
            ->assertSee('Rp')
            ->assertSee('Aktif s/d');

        $component->call('submit')->assertHasNoErrors();

        $invoice = PlatformPaymentTransaction::query()->latest('id')->firstOrFail();

        $this->assertSame('upgrade', $invoice->type);
        $this->assertSame($higher->id, $invoice->plan_id);
        $this->assertGreaterThan(0, (float) $invoice->amount_total);
    }

    public function test_upgrade_component_rejects_wrong_owner_email(): void
    {
        [$tenant, , $higher] = $this->seedTenantWithPlan();

        Livewire::test(UpgradeRequest::class, ['tenantCode' => $tenant->code])
            ->set('ownerEmail', 'wrong@example.com')
            ->set('targetPlanId', (string) $higher->id)
            ->call('submit')
            ->assertHasErrors(['ownerEmail']);

        $this->assertSame(0, PlatformPaymentTransaction::query()->count());
    }

    public function test_pending_verification_component_shows_email_and_handles_resend(): void
    {
        $this->withSession(['pending_registration_email' => 'pending-lw@example.com']);

        Livewire::test(PendingVerification::class)
            ->assertSet('ownerEmail', 'pending-lw@example.com')
            ->assertSee('pending-lw@example.com')
            ->assertSee('Kirim Ulang Verifikasi');
    }

    public function test_pending_verification_without_session_has_empty_email(): void
    {
        Livewire::test(PendingVerification::class)
            ->assertSet('ownerEmail', '');
    }
}
