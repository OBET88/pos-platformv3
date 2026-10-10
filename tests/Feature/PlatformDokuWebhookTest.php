<?php

namespace Tests\Feature;

use App\Models\Platform\Plan;
use App\Models\Platform\PlatformPaymentEvent;
use App\Models\Platform\PlatformPaymentTransaction;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformDokuWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_doku_success_webhook_marks_invoice_paid_and_activates_subscription(): void
    {
        $plan = Plan::query()->create([
            'code' => 'pro-doku-test',
            'name' => 'Pro DOKU Test',
            'price' => 75000,
            'billing_cycle' => 'monthly',
            'max_businesses' => 1,
            'max_branches' => 1,
            'max_users' => 3,
            'features_json' => ['pos'],
            'is_active' => true,
        ]);

        $tenant = Tenant::query()->create([
            'code' => 'doku-webhook-tenant',
            'name' => 'DOKU Webhook Tenant',
            'owner_name' => 'Owner DOKU',
            'owner_email' => 'owner-doku@example.com',
            'status' => 'trial',
        ]);

        $subscription = Subscription::query()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => 'trial',
            'started_at' => now(),
            'trial_ends_at' => now()->addDays(7),
            'expired_at' => now()->addDays(7),
        ]);

        $invoice = PlatformPaymentTransaction::query()->create([
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription->id,
            'plan_id' => $plan->id,
            'invoice_number' => 'INV-DOKU-WEBHOOK-001',
            'type' => 'renewal',
            'payment_method' => 'gateway',
            'payment_provider' => 'doku',
            'amount_subtotal' => 75000,
            'amount_discount' => 0,
            'amount_total' => 75000,
            'billing_cycle' => 'monthly',
            'status' => 'pending_payment',
            'due_at' => now()->addDays(1),
            'notes' => 'Invoice test webhook DOKU',
        ]);

        $payload = [
            'order' => [
                'invoice_number' => $invoice->invoice_number,
                'status' => 'ORDER_COMPLETED',
            ],
            'transaction' => [
                'status' => 'SUCCESS',
                'original_request_id' => 'REQ-DOKU-001',
            ],
        ];

        $this->postJson(route('platform.webhooks.doku.checkout'), $payload)
            ->assertOk()
            ->assertJson([
                'status' => 'processed',
                'invoice_number' => $invoice->invoice_number,
            ]);

        $invoice->refresh();
        $tenant->refresh();
        $subscription->refresh();

        $this->assertSame('paid', $invoice->status);
        $this->assertSame('doku', $invoice->payment_provider);
        $this->assertNotNull($invoice->paid_at);
        $this->assertSame('active', $tenant->status);
        $this->assertSame('active', $subscription->status);
        $this->assertNotNull($subscription->expired_at);

        $event = PlatformPaymentEvent::query()->where('payment_transaction_id', $invoice->id)->first();

        $this->assertNotNull($event);
        $this->assertSame('doku', $event->payment_provider);
        $this->assertSame('SUCCESS', $event->event_key);
        $this->assertFalse((bool) $event->signature_valid);
        $this->assertSame('processed', $event->process_status);

        $this->assertDatabaseHas('platform_inbox_items', [
            'tenant_id' => $tenant->id,
            'event_key' => 'tenant.payment.approved',
        ]);
    }

    public function test_doku_failed_webhook_marks_invoice_failed_without_activating_subscription(): void
    {
        [$tenant, $subscription, $invoice] = $this->seedPendingGatewayInvoice('INV-DOKU-WEBHOOK-FAILED');

        $payload = [
            'order' => [
                'invoice_number' => $invoice->invoice_number,
                'status' => 'ORDER_FAILED',
            ],
            'transaction' => [
                'status' => 'FAILED',
                'original_request_id' => 'REQ-DOKU-FAILED-001',
            ],
        ];

        $this->postJson(route('platform.webhooks.doku.checkout'), $payload)
            ->assertOk()
            ->assertJson([
                'status' => 'processed',
                'invoice_number' => $invoice->invoice_number,
            ]);

        $invoice->refresh();
        $tenant->refresh();
        $subscription->refresh();

        $this->assertSame('failed', $invoice->status);
        $this->assertSame('doku', $invoice->payment_provider);
        $this->assertSame('REQ-DOKU-FAILED-001', $invoice->provider_reference);
        $this->assertNull($invoice->paid_at);
        $this->assertSame('trial', $tenant->status);
        $this->assertSame('trial', $subscription->status);

        $event = PlatformPaymentEvent::query()->where('payment_transaction_id', $invoice->id)->first();

        $this->assertNotNull($event);
        $this->assertSame('FAILED', $event->event_key);
        $this->assertSame('processed', $event->process_status);
    }

    public function test_doku_expired_webhook_marks_invoice_expired_without_activating_subscription(): void
    {
        [$tenant, $subscription, $invoice] = $this->seedPendingGatewayInvoice('INV-DOKU-WEBHOOK-EXPIRED');

        $payload = [
            'order' => [
                'invoice_number' => $invoice->invoice_number,
                'status' => 'ORDER_EXPIRED',
            ],
            'transaction' => [
                'status' => 'EXPIRED',
                'original_request_id' => 'REQ-DOKU-EXPIRED-001',
            ],
        ];

        $this->postJson(route('platform.webhooks.doku.checkout'), $payload)
            ->assertOk()
            ->assertJson([
                'status' => 'processed',
                'invoice_number' => $invoice->invoice_number,
            ]);

        $invoice->refresh();
        $tenant->refresh();
        $subscription->refresh();

        $this->assertSame('expired', $invoice->status);
        $this->assertSame('doku', $invoice->payment_provider);
        $this->assertSame('REQ-DOKU-EXPIRED-001', $invoice->provider_reference);
        $this->assertNull($invoice->paid_at);
        $this->assertSame('trial', $tenant->status);
        $this->assertSame('trial', $subscription->status);

        $event = PlatformPaymentEvent::query()->where('payment_transaction_id', $invoice->id)->first();

        $this->assertNotNull($event);
        $this->assertSame('EXPIRED', $event->event_key);
        $this->assertSame('processed', $event->process_status);
    }

    private function seedPendingGatewayInvoice(string $invoiceNumber): array
    {
        $plan = Plan::query()->create([
            'code' => strtolower($invoiceNumber),
            'name' => 'Plan '.$invoiceNumber,
            'price' => 75000,
            'billing_cycle' => 'monthly',
            'max_businesses' => 1,
            'max_branches' => 1,
            'max_users' => 3,
            'features_json' => ['pos'],
            'is_active' => true,
        ]);

        $tenant = Tenant::query()->create([
            'code' => strtolower($invoiceNumber).'-tenant',
            'name' => 'Tenant '.$invoiceNumber,
            'owner_name' => 'Owner '.$invoiceNumber,
            'owner_email' => strtolower($invoiceNumber).'@example.com',
            'status' => 'trial',
        ]);

        $subscription = Subscription::query()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => 'trial',
            'started_at' => now(),
            'trial_ends_at' => now()->addDays(7),
            'expired_at' => now()->addDays(7),
        ]);

        $invoice = PlatformPaymentTransaction::query()->create([
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription->id,
            'plan_id' => $plan->id,
            'invoice_number' => $invoiceNumber,
            'type' => 'renewal',
            'payment_method' => 'gateway',
            'payment_provider' => 'doku',
            'amount_subtotal' => 75000,
            'amount_discount' => 0,
            'amount_total' => 75000,
            'billing_cycle' => 'monthly',
            'status' => 'pending_payment',
            'due_at' => now()->addDays(1),
            'notes' => 'Invoice test webhook DOKU '.$invoiceNumber,
        ]);

        return [$tenant, $subscription, $invoice];
    }
}
