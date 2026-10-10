<?php

namespace Tests\Feature;

use App\Models\Platform\Plan;
use App\Models\Platform\PlatformPaymentTransaction;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformPublicUpgradeRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_upgrade_page_shows_available_plan_and_estimated_amount_context(): void
    {
        $basic = Plan::query()->create([
            'code' => 'basic-public-upgrade-view',
            'name' => 'Basic Public Upgrade View',
            'price' => 100000,
            'billing_cycle' => 'monthly',
            'max_businesses' => 1,
            'max_branches' => 1,
            'max_users' => 3,
            'features_json' => ['pos'],
            'is_active' => true,
        ]);

        $plus = Plan::query()->create([
            'code' => 'plus-public-upgrade-view',
            'name' => 'Plus Public Upgrade View',
            'price' => 250000,
            'billing_cycle' => 'monthly',
            'max_businesses' => 3,
            'max_branches' => 5,
            'max_users' => 15,
            'features_json' => ['pos', 'stock'],
            'is_active' => true,
        ]);

        $tenant = Tenant::query()->create([
            'code' => 'tenant-public-upgrade-view',
            'name' => 'Tenant Public Upgrade View',
            'owner_name' => 'Owner Public Upgrade',
            'owner_email' => 'owner-public-upgrade@example.com',
            'status' => 'active',
        ]);

        Subscription::query()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $basic->id,
            'status' => 'active',
            'started_at' => now()->subMonth(),
            'trial_ends_at' => now()->addDays(10),
            'expired_at' => now()->addDays(10),
        ]);

        $response = $this->get(route('platform.upgrade.create', ['tenant' => $tenant->code]));

        $response->assertOk();
        $response->assertSee('Buat Invoice Upgrade');
        $response->assertSee($plus->name);
        $response->assertSee('Rp250.000/bulan', false);
    }

    public function test_customer_can_create_public_upgrade_invoice_and_is_redirected_to_payment_page(): void
    {
        $basic = Plan::query()->create([
            'code' => 'basic-public-upgrade',
            'name' => 'Basic Public Upgrade',
            'price' => 100000,
            'billing_cycle' => 'monthly',
            'max_businesses' => 1,
            'max_branches' => 1,
            'max_users' => 3,
            'features_json' => ['pos'],
            'is_active' => true,
        ]);

        $plus = Plan::query()->create([
            'code' => 'plus-public-upgrade',
            'name' => 'Plus Public Upgrade',
            'price' => 250000,
            'billing_cycle' => 'monthly',
            'max_businesses' => 3,
            'max_branches' => 5,
            'max_users' => 15,
            'features_json' => ['pos', 'stock'],
            'is_active' => true,
        ]);

        $tenant = Tenant::query()->create([
            'code' => 'tenant-public-upgrade',
            'name' => 'Tenant Public Upgrade',
            'owner_name' => 'Owner Public Upgrade',
            'owner_email' => 'owner-public-upgrade@example.com',
            'status' => 'active',
        ]);

        $subscription = Subscription::query()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $basic->id,
            'status' => 'active',
            'started_at' => now()->subMonth(),
            'trial_ends_at' => now()->addDays(10),
            'expired_at' => now()->addDays(10),
        ]);

        $response = $this->post(route('platform.upgrade.store'), [
            'tenant_code' => $tenant->code,
            'owner_email' => $tenant->owner_email,
            'target_plan_id' => $plus->id,
            'billing_cycle' => 'quarterly',
            'notes' => 'Pelanggan minta upgrade mandiri',
        ]);

        $invoice = PlatformPaymentTransaction::query()->latest('id')->firstOrFail();

        $response->assertRedirect(route('platform.payment.show', ['invoiceNumber' => $invoice->invoice_number]));
        $this->assertSame('upgrade', $invoice->type);
        $this->assertSame('pending_payment', $invoice->status);
        $this->assertSame($tenant->id, $invoice->tenant_id);
        $this->assertSame($subscription->id, $invoice->subscription_id);
        $this->assertSame($plus->id, $invoice->plan_id);
        $this->assertSame(450000.0, (float) $invoice->amount_total);
        $this->assertSame('public_upgrade', $invoice->meta_json['created_via'] ?? null);
        $this->assertSame('plan_difference', $invoice->meta_json['pricing_mode'] ?? null);
        $this->assertSame($basic->id, $invoice->meta_json['current_plan_id'] ?? null);
        $this->assertSame($plus->id, $invoice->meta_json['selected_plan_id'] ?? null);
        $this->assertDatabaseHas('platform_inbox_items', [
            'tenant_id' => $tenant->id,
            'event_key' => 'tenant.subscription.upgrade_requested',
        ]);
        $this->assertDatabaseHas('platform_inbox_items', [
            'tenant_id' => $tenant->id,
            'event_key' => 'tenant.payment.invoice_created',
        ]);
    }

    public function test_customer_is_redirected_to_existing_pending_upgrade_invoice_instead_of_creating_duplicate(): void
    {
        $basic = Plan::query()->create([
            'code' => 'basic-existing-upgrade',
            'name' => 'Basic Existing Upgrade',
            'price' => 100000,
            'billing_cycle' => 'monthly',
            'max_businesses' => 1,
            'max_branches' => 1,
            'max_users' => 3,
            'features_json' => ['pos'],
            'is_active' => true,
        ]);

        $plus = Plan::query()->create([
            'code' => 'plus-existing-upgrade',
            'name' => 'Plus Existing Upgrade',
            'price' => 250000,
            'billing_cycle' => 'monthly',
            'max_businesses' => 3,
            'max_branches' => 5,
            'max_users' => 15,
            'features_json' => ['pos', 'stock'],
            'is_active' => true,
        ]);

        $tenant = Tenant::query()->create([
            'code' => 'tenant-existing-upgrade',
            'name' => 'Tenant Existing Upgrade',
            'owner_name' => 'Owner Existing Upgrade',
            'owner_email' => 'owner-existing-upgrade@example.com',
            'status' => 'active',
        ]);

        $subscription = Subscription::query()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $basic->id,
            'status' => 'active',
            'started_at' => now()->subMonth(),
            'trial_ends_at' => now()->addDays(10),
            'expired_at' => now()->addDays(10),
        ]);

        $existingInvoice = PlatformPaymentTransaction::query()->create([
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription->id,
            'plan_id' => $plus->id,
            'invoice_number' => 'INV-EXISTING-UPGRADE-001',
            'type' => 'upgrade',
            'payment_method' => 'manual_transfer',
            'payment_channel' => 'bank_transfer',
            'amount_subtotal' => 150000,
            'amount_discount' => 0,
            'amount_total' => 150000,
            'billing_cycle' => 'monthly',
            'status' => 'pending_payment',
            'due_at' => now()->addDays(3),
        ]);

        $response = $this->post(route('platform.upgrade.store'), [
            'tenant_code' => $tenant->code,
            'owner_email' => $tenant->owner_email,
            'target_plan_id' => $plus->id,
            'billing_cycle' => 'monthly',
        ]);

        $response->assertRedirect(route('platform.payment.show', ['invoiceNumber' => $existingInvoice->invoice_number]));
        $this->assertSame(1, PlatformPaymentTransaction::query()->count());
    }
}
