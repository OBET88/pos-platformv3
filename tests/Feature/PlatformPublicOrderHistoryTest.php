<?php

namespace Tests\Feature;

use App\Models\Platform\Plan;
use App\Models\Platform\PlatformPaymentProof;
use App\Models\Platform\PlatformPaymentTransaction;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformPublicOrderHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_lookup_public_order_history_and_see_invoice_statuses(): void
    {
        $plan = Plan::query()->create([
            'code' => 'basic-public-orders',
            'name' => 'Basic Public Orders',
            'price' => 100000,
            'billing_cycle' => 'monthly',
            'max_businesses' => 1,
            'max_branches' => 1,
            'max_users' => 3,
            'features_json' => ['pos'],
            'is_active' => true,
        ]);

        $tenant = Tenant::query()->create([
            'code' => 'tenant-public-orders',
            'name' => 'Tenant Public Orders',
            'owner_name' => 'Owner Public Orders',
            'owner_email' => 'owner-public-orders@example.com',
            'status' => 'active',
        ]);

        $subscription = Subscription::query()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'started_at' => now()->subMonth(),
            'trial_ends_at' => now()->addDays(15),
            'expired_at' => now()->addDays(15),
        ]);

        $pendingInvoice = PlatformPaymentTransaction::query()->create([
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription->id,
            'plan_id' => $plan->id,
            'invoice_number' => 'INV-ORDER-HISTORY-001',
            'type' => 'renewal',
            'payment_method' => 'manual_transfer',
            'payment_channel' => 'bank_transfer',
            'amount_subtotal' => 100000,
            'amount_discount' => 0,
            'amount_total' => 100000,
            'billing_cycle' => 'monthly',
            'status' => 'pending_payment',
            'due_at' => now()->addDays(3),
        ]);

        $waitingInvoice = PlatformPaymentTransaction::query()->create([
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription->id,
            'plan_id' => $plan->id,
            'invoice_number' => 'INV-ORDER-HISTORY-002',
            'type' => 'upgrade',
            'payment_method' => 'manual_transfer',
            'payment_channel' => 'bank_transfer',
            'amount_subtotal' => 150000,
            'amount_discount' => 0,
            'amount_total' => 150000,
            'billing_cycle' => 'monthly',
            'status' => 'waiting_validation',
            'due_at' => now()->addDays(3),
        ]);

        PlatformPaymentProof::query()->create([
            'payment_transaction_id' => $waitingInvoice->id,
            'transfer_amount' => 150000,
            'transfer_date' => now(),
            'submitted_by_name' => 'Owner Public Orders',
            'submitted_by_email' => $tenant->owner_email,
            'review_status' => 'pending',
        ]);

        $response = $this->post(route('platform.orders.lookup'), [
            'tenant_code' => $tenant->code,
            'owner_email' => $tenant->owner_email,
        ]);

        $response->assertOk();
        $response->assertSee('Tenant Public Orders');
        $response->assertSee($pendingInvoice->invoice_number);
        $response->assertSee($waitingInvoice->invoice_number);
        $response->assertSee('PENDING PAYMENT');
        $response->assertSee('WAITING VALIDATION');
        $response->assertSee(route('platform.payment.show', ['invoiceNumber' => $pendingInvoice->invoice_number]));
        $response->assertSee(route('platform.payment.show', ['invoiceNumber' => $waitingInvoice->invoice_number]));
    }

    public function test_customer_cannot_view_public_order_history_with_wrong_owner_email(): void
    {
        $tenant = Tenant::query()->create([
            'code' => 'tenant-public-orders-guard',
            'name' => 'Tenant Public Orders Guard',
            'owner_name' => 'Owner Public Orders Guard',
            'owner_email' => 'owner-public-orders-guard@example.com',
            'status' => 'active',
        ]);

        $response = $this->from(route('platform.orders.index'))
            ->post(route('platform.orders.lookup'), [
                'tenant_code' => $tenant->code,
                'owner_email' => 'wrong-owner@example.com',
            ]);

        $response->assertStatus(200);
        $response->assertSee('Email owner tidak cocok dengan tenant yang dipilih.');
        $response->assertDontSee('Tenant Public Orders Guard');
    }
}
