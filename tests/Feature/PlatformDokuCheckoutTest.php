<?php

namespace Tests\Feature;

use App\Models\Platform\Plan;
use App\Models\Platform\PlatformPaymentTransaction;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PlatformDokuCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_redirect_to_doku_checkout_creates_remote_order_and_stores_checkout_metadata(): void
    {
        Carbon::setTestNow('2026-04-24 10:00:00');

        config()->set('services.doku', [
            'enabled' => true,
            'base_url' => 'https://api-sandbox.doku.test',
            'client_id' => 'client-test-123',
            'secret_key' => 'secret-test-456',
            'merchant_name' => 'POS Platform Test',
            'payment_method_types' => 'VIRTUAL_ACCOUNT,BANK_TRANSFER',
        ]);

        [$tenant, $invoice] = $this->seedInvoice('INV-DOKU-CHECKOUT-001');

        Http::fake([
            'https://api-sandbox.doku.test/checkout/v1/payment' => Http::response([
                'response' => [
                    'payment' => [
                        'url' => 'https://pay.doku.test/checkout/inv-001',
                    ],
                ],
            ], 200),
        ]);

        $response = $this->post(route('platform.payment.doku', ['invoiceNumber' => $invoice->invoice_number]));

        $response->assertRedirect('https://pay.doku.test/checkout/inv-001');

        Http::assertSent(function ($request) use ($invoice, $tenant) {
            $data = $request->data();

            return $request->url() === 'https://api-sandbox.doku.test/checkout/v1/payment'
                && $request->method() === 'POST'
                && $request->hasHeader('Client-Id', 'client-test-123')
                && $request->hasHeader('Request-Id')
                && $request->hasHeader('Request-Timestamp')
                && $request->hasHeader('Signature')
                && ($data['order']['invoice_number'] ?? null) === $invoice->invoice_number
                && ($data['order']['amount'] ?? null) === 75000
                && ($data['order']['callback_url'] ?? null) === route('platform.payment.show', ['invoiceNumber' => $invoice->invoice_number])
                && ($data['order']['callback_url_result'] ?? null) === route('platform.payment.show', ['invoiceNumber' => $invoice->invoice_number])
                && ($data['customer']['email'] ?? null) === $tenant->owner_email
                && ($data['additional_info']['merchant_notification_url'] ?? null) === route('platform.webhooks.doku.checkout')
                && ($data['payment']['payment_method_types'] ?? null) === ['VIRTUAL_ACCOUNT', 'BANK_TRANSFER']
                && ($data['payment']['payment_due_date'] ?? null) === 90;
        });

        $invoice->refresh();

        $this->assertSame('gateway', $invoice->payment_method);
        $this->assertSame('doku', $invoice->payment_provider);
        $this->assertSame('pending_payment', $invoice->status);
        $this->assertNotEmpty($invoice->provider_reference);
        $this->assertSame($invoice->provider_reference, $invoice->meta_json['doku_request_id'] ?? null);
        $this->assertSame('https://pay.doku.test/checkout/inv-001', $invoice->meta_json['doku_checkout_url'] ?? null);
        $this->assertSame(
            ['response' => ['payment' => ['url' => 'https://pay.doku.test/checkout/inv-001']]],
            $invoice->meta_json['doku_response'] ?? null
        );
    }

    public function test_redirect_to_doku_checkout_returns_to_invoice_when_checkout_creation_fails(): void
    {
        config()->set('services.doku', [
            'enabled' => true,
            'base_url' => 'https://api-sandbox.doku.test',
            'client_id' => 'client-test-123',
            'secret_key' => 'secret-test-456',
            'merchant_name' => 'POS Platform Test',
            'payment_method_types' => '',
        ]);

        [, $invoice] = $this->seedInvoice('INV-DOKU-CHECKOUT-FAILED');

        Http::fake([
            'https://api-sandbox.doku.test/checkout/v1/payment' => Http::response([
                'message' => 'gateway unavailable',
            ], 500),
        ]);

        $response = $this->from(route('platform.payment.show', ['invoiceNumber' => $invoice->invoice_number]))
            ->post(route('platform.payment.doku', ['invoiceNumber' => $invoice->invoice_number]));

        $response->assertRedirect(route('platform.payment.show', ['invoiceNumber' => $invoice->invoice_number]));
        $response->assertSessionHasErrors([
            'submitted_by_name' => 'Checkout DOKU belum berhasil dibuat: Permintaan DOKU Checkout gagal. Status: 500',
        ]);

        $invoice->refresh();

        $this->assertSame('manual_transfer', $invoice->payment_method);
        $this->assertNull($invoice->payment_provider);
        $this->assertSame('pending_payment', $invoice->status);
        $this->assertNull($invoice->provider_reference);
        $this->assertNull($invoice->meta_json);
    }

    private function seedInvoice(string $invoiceNumber): array
    {
        $plan = Plan::query()->create([
            'code' => strtolower($invoiceNumber).'-plan',
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
            'owner_phone' => '081234567890',
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
            'payment_method' => 'manual_transfer',
            'amount_subtotal' => 75000,
            'amount_discount' => 0,
            'amount_total' => 75000,
            'billing_cycle' => 'monthly',
            'status' => 'pending_payment',
            'due_at' => now()->addMinutes(90),
            'notes' => 'Invoice test checkout DOKU '.$invoiceNumber,
        ]);

        return [$tenant, $invoice];
    }
}
