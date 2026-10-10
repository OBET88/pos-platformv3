<?php

namespace Tests\Feature;

use App\Models\Platform\Plan;
use App\Models\Platform\PlatformPaymentTransaction;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformPublicRenewalRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_create_public_renewal_invoice_and_is_redirected_to_payment_page(): void
    {
        $plan = Plan::query()->create([
            'code' => 'basic-public-renewal',
            'name' => 'Basic Public Renewal',
            'price' => 50000,
            'billing_cycle' => 'monthly',
            'max_businesses' => 1,
            'max_branches' => 1,
            'max_users' => 3,
            'features_json' => ['pos'],
            'is_active' => true,
        ]);

        $tenant = Tenant::query()->create([
            'code' => 'tenant-public-renewal',
            'name' => 'Tenant Public Renewal',
            'owner_name' => 'Owner Public',
            'owner_email' => 'owner-public@example.com',
            'status' => 'active',
        ]);

        $subscription = Subscription::query()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'started_at' => now()->subMonth(),
            'trial_ends_at' => now()->addDays(10),
            'expired_at' => now()->addDays(10),
        ]);

        $response = $this->post(route('platform.renewal.store'), [
            'tenant_code' => $tenant->code,
            'owner_email' => $tenant->owner_email,
            'billing_cycle' => 'quarterly',
            'notes' => 'Pelanggan minta renewal mandiri',
        ]);

        $invoice = PlatformPaymentTransaction::query()->latest('id')->firstOrFail();

        $response->assertRedirect(route('platform.payment.show', ['invoiceNumber' => $invoice->invoice_number]));
        $this->assertSame('renewal', $invoice->type);
        $this->assertSame('pending_payment', $invoice->status);
        $this->assertSame($tenant->id, $invoice->tenant_id);
        $this->assertSame($subscription->id, $invoice->subscription_id);
        $this->assertSame($plan->id, $invoice->plan_id);
        $this->assertSame(150000.0, (float) $invoice->amount_total);
        $this->assertSame('public_renewal', $invoice->meta_json['created_via'] ?? null);
        $this->assertSame('3 bulan', $invoice->meta_json['requested_term_label'] ?? null);
        $this->assertDatabaseHas('platform_inbox_items', [
            'tenant_id' => $tenant->id,
            'event_key' => 'tenant.subscription.renewal_requested',
        ]);
        $this->assertDatabaseHas('platform_inbox_items', [
            'tenant_id' => $tenant->id,
            'event_key' => 'tenant.payment.invoice_created',
        ]);
    }

    public function test_customer_is_redirected_to_existing_pending_renewal_invoice_instead_of_creating_duplicate(): void
    {
        $plan = Plan::query()->create([
            'code' => 'basic-existing-renewal',
            'name' => 'Basic Existing Renewal',
            'price' => 50000,
            'billing_cycle' => 'monthly',
            'max_businesses' => 1,
            'max_branches' => 1,
            'max_users' => 3,
            'features_json' => ['pos'],
            'is_active' => true,
        ]);

        $tenant = Tenant::query()->create([
            'code' => 'tenant-existing-renewal',
            'name' => 'Tenant Existing Renewal',
            'owner_name' => 'Owner Existing',
            'owner_email' => 'owner-existing@example.com',
            'status' => 'active',
        ]);

        $subscription = Subscription::query()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'started_at' => now()->subMonth(),
            'trial_ends_at' => now()->addDays(10),
            'expired_at' => now()->addDays(10),
        ]);

        $existingInvoice = PlatformPaymentTransaction::query()->create([
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription->id,
            'plan_id' => $plan->id,
            'invoice_number' => 'INV-EXISTING-RENEWAL-001',
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

        $response = $this->post(route('platform.renewal.store'), [
            'tenant_code' => $tenant->code,
            'owner_email' => $tenant->owner_email,
            'billing_cycle' => 'monthly',
        ]);

        $response->assertRedirect(route('platform.payment.show', ['invoiceNumber' => $existingInvoice->invoice_number]));
        $this->assertSame(1, PlatformPaymentTransaction::query()->count());
    }
}
