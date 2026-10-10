<?php

namespace Tests\Feature;

use App\Livewire\Platform\Admin\PaymentValidationManager;
use App\Models\Platform\InboxItem;
use App\Models\Platform\Plan;
use App\Models\Platform\PlatformPaymentProof;
use App\Models\Platform\PlatformSetting;
use App\Models\Platform\PlatformPaymentTransaction;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class PlatformPaymentManualFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_manual_payment_flow_can_submit_reject_resubmit_and_approve(): void
    {
        Storage::fake('public');

        $admin = User::query()->create([
            'name' => 'Platform Admin',
            'email' => 'platform-admin@example.com',
            'password' => 'secret123',
            'is_platform_admin' => true,
        ]);

        $plan = Plan::query()->create([
            'code' => 'basic-test',
            'name' => 'Basic Test',
            'price' => 50000,
            'billing_cycle' => 'monthly',
            'max_businesses' => 1,
            'max_branches' => 1,
            'max_users' => 3,
            'features_json' => ['pos'],
            'is_active' => true,
        ]);

        $tenant = Tenant::query()->create([
            'code' => 'payment-flow-tenant',
            'name' => 'Payment Flow Tenant',
            'owner_name' => 'Owner Payment',
            'owner_email' => 'owner-payment@example.com',
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
            'invoice_number' => 'INV-MANUAL-FLOW-001',
            'type' => 'renewal',
            'payment_method' => 'manual_transfer',
            'payment_channel' => 'bank_transfer',
            'amount_subtotal' => 50000,
            'amount_discount' => 0,
            'amount_total' => 50000,
            'billing_cycle' => 'monthly',
            'status' => 'pending_payment',
            'due_at' => now()->addDays(3),
            'notes' => 'Invoice test flow manual payment',
            'meta_json' => ['created_via' => 'test'],
        ]);

        $firstProof = UploadedFile::fake()->image('proof-first.jpg');

        $this->post(route('platform.payment.proof', ['invoiceNumber' => $invoice->invoice_number]), [
            'submitted_by_name' => 'Tester Manual',
            'submitted_by_email' => 'tester@example.com',
            'bank_name' => 'BCA',
            'account_name' => 'Tester Manual',
            'account_number_masked' => '1234****7890',
            'transfer_amount' => '50000',
            'transfer_date' => now()->format('Y-m-d H:i:s'),
            'proof_file' => $firstProof,
        ])->assertRedirect(route('platform.orders.index', [
            'tenant' => $tenant->code,
            'owner_email' => $tenant->owner_email,
            'invoice' => $invoice->invoice_number,
        ]));

        $invoice->refresh();

        $this->assertSame('waiting_validation', $invoice->status);
        $this->assertDatabaseHas('platform_payment_proofs', [
            'payment_transaction_id' => $invoice->id,
            'submitted_by_email' => 'tester@example.com',
            'review_status' => 'pending',
        ]);
        $this->assertDatabaseHas('platform_inbox_items', [
            'tenant_id' => $tenant->id,
            'event_key' => 'tenant.payment.awaiting_validation',
        ]);
        Storage::disk('public')->assertExists(PlatformPaymentProof::query()->firstOrFail()->proof_file_path);

        $this->actingAs($admin);

        Livewire::test(PaymentValidationManager::class)
            ->call('rejectPayment', $invoice->id)
            ->assertHasNoErrors()
            ->call('rejectPayment', $invoice->id)
            ->assertHasNoErrors();

        $invoice->refresh();

        $this->assertSame('pending_payment', $invoice->status);
        $this->assertDatabaseHas('platform_payment_proofs', [
            'payment_transaction_id' => $invoice->id,
            'submitted_by_email' => 'tester@example.com',
            'review_status' => 'rejected',
        ]);
        $this->assertDatabaseHas('platform_inbox_items', [
            'tenant_id' => $tenant->id,
            'event_key' => 'tenant.payment.rejected',
        ]);
        $this->assertDatabaseMissing('platform_inbox_items', [
            'tenant_id' => $tenant->id,
            'event_key' => 'tenant.payment.awaiting_validation',
            'source_id' => $invoice->id,
        ]);

        $this->get(route('platform.payment.show', ['invoiceNumber' => $invoice->invoice_number]))
            ->assertOk()
            ->assertSee('Perlu upload ulang bukti bayar')
            ->assertSee('Catatan admin:')
            ->assertSee('Silakan upload ulang bukti yang benar.');

        $secondProof = UploadedFile::fake()->image('proof-second.jpg');

        $this->post(route('platform.payment.proof', ['invoiceNumber' => $invoice->invoice_number]), [
            'submitted_by_name' => 'Tester Manual',
            'submitted_by_email' => 'tester@example.com',
            'bank_name' => 'BCA',
            'account_name' => 'Tester Manual',
            'account_number_masked' => '1234****7890',
            'transfer_amount' => '50000',
            'transfer_date' => now()->addMinute()->format('Y-m-d H:i:s'),
            'proof_file' => $secondProof,
        ])->assertRedirect(route('platform.orders.index', [
            'tenant' => $tenant->code,
            'owner_email' => $tenant->owner_email,
            'invoice' => $invoice->invoice_number,
        ]));

        $invoice->refresh();

        $this->assertSame('waiting_validation', $invoice->status);
        $this->assertSame(2, PlatformPaymentProof::query()->where('payment_transaction_id', $invoice->id)->count());
        $this->assertDatabaseHas('platform_payment_proofs', [
            'payment_transaction_id' => $invoice->id,
            'proof_file_path' => PlatformPaymentProof::query()->latest('id')->firstOrFail()->proof_file_path,
            'review_status' => 'pending',
        ]);

        Livewire::test(PaymentValidationManager::class)
            ->call('approvePayment', $invoice->id)
            ->assertHasNoErrors();

        $invoice->refresh();
        $subscription->refresh();
        $tenant->refresh();

        $this->assertSame('paid', $invoice->status);
        $this->assertNotNull($invoice->paid_at);
        $this->assertSame('active', $tenant->status);
        $this->assertSame('active', $subscription->status);
        $this->assertNotNull($subscription->expired_at);
        $this->assertDatabaseHas('platform_payment_proofs', [
            'payment_transaction_id' => $invoice->id,
            'review_status' => 'approved',
        ]);
        $this->assertDatabaseHas('platform_inbox_items', [
            'tenant_id' => $tenant->id,
            'event_key' => 'tenant.payment.approved',
        ]);
    }

    public function test_payment_page_shows_invoice_amount_and_manual_transfer_destination_before_uploading_proof(): void
    {
        $plan = Plan::query()->create([
            'code' => 'basic-payment-page',
            'name' => 'Basic Payment Page',
            'price' => 50000,
            'billing_cycle' => 'monthly',
            'max_businesses' => 1,
            'max_branches' => 1,
            'max_users' => 3,
            'features_json' => ['pos'],
            'is_active' => true,
        ]);

        $tenant = Tenant::query()->create([
            'code' => 'tenant-payment-page',
            'name' => 'Tenant Payment Page',
            'owner_name' => 'Owner Payment Page',
            'owner_email' => 'owner-payment-page@example.com',
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

        PlatformSetting::query()->updateOrCreate(['key' => 'payment_bank_name'], ['value' => 'BCA']);
        PlatformSetting::query()->updateOrCreate(['key' => 'payment_account_name'], ['value' => 'RWP POS INDONESIA']);
        PlatformSetting::query()->updateOrCreate(['key' => 'payment_account_number'], ['value' => '1234567890']);
        PlatformSetting::query()->updateOrCreate(['key' => 'payment_branch'], ['value' => 'Cabang Jakarta']);
        PlatformSetting::query()->updateOrCreate(['key' => 'payment_manual_instructions'], ['value' => 'Transfer dulu, lalu upload bukti pembayaran dari halaman ini.']);

        $invoice = PlatformPaymentTransaction::query()->create([
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription->id,
            'plan_id' => $plan->id,
            'invoice_number' => 'INV-MANUAL-DETAIL-001',
            'type' => 'renewal',
            'payment_method' => 'manual_transfer',
            'payment_channel' => 'bank_transfer',
            'amount_subtotal' => 50000,
            'amount_discount' => 0,
            'amount_total' => 50000,
            'billing_cycle' => 'monthly',
            'status' => 'pending_payment',
            'due_at' => now()->addDays(3),
            'notes' => 'Invoice detail payment page',
            'meta_json' => ['created_via' => 'test'],
        ]);

        $this->get(route('platform.payment.show', ['invoiceNumber' => $invoice->invoice_number]))
            ->assertOk()
            ->assertSee('Tagihan Siap Dibayar')
            ->assertSee('Rp50.000', false)
            ->assertSee('BCA')
            ->assertSee('RWP POS INDONESIA')
            ->assertSee('1234567890')
            ->assertSee('Transfer dulu, lalu upload bukti pembayaran dari halaman ini.');
    }

    public function test_paid_invoice_with_old_rejected_proof_still_shows_invoice_only_ui(): void
    {
        $plan = Plan::query()->create([
            'code' => 'basic-paid-proof-history',
            'name' => 'Basic Paid Proof History',
            'price' => 50000,
            'billing_cycle' => 'monthly',
            'max_businesses' => 1,
            'max_branches' => 1,
            'max_users' => 3,
            'features_json' => ['pos'],
            'is_active' => true,
        ]);

        $tenant = Tenant::query()->create([
            'code' => 'tenant-paid-proof-history',
            'name' => 'Tenant Paid Proof History',
            'owner_name' => 'Owner Paid Proof History',
            'owner_email' => 'owner-paid-proof-history@example.com',
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

        $invoice = PlatformPaymentTransaction::query()->create([
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription->id,
            'plan_id' => $plan->id,
            'invoice_number' => 'INV-PAID-HISTORY-001',
            'type' => 'renewal',
            'payment_method' => 'manual_transfer',
            'payment_channel' => 'bank_transfer',
            'amount_subtotal' => 50000,
            'amount_discount' => 0,
            'amount_total' => 50000,
            'billing_cycle' => 'monthly',
            'status' => 'paid',
            'paid_at' => now(),
            'due_at' => now()->addDays(3),
            'notes' => 'Invoice with proof history',
            'meta_json' => ['created_via' => 'test'],
        ]);

        PlatformPaymentProof::query()->create([
            'payment_transaction_id' => $invoice->id,
            'bank_name' => 'BCA',
            'account_name' => 'Owner Paid Proof History',
            'account_number_masked' => '1111****2222',
            'transfer_amount' => 50000,
            'transfer_date' => now()->subHour(),
            'submitted_by_name' => 'Owner Paid Proof History',
            'submitted_by_email' => 'owner-paid-proof-history@example.com',
            'review_status' => 'rejected',
            'review_notes' => 'Proof lama ditolak sebelum ada bukti baru.',
        ]);

        PlatformPaymentProof::query()->create([
            'payment_transaction_id' => $invoice->id,
            'bank_name' => 'BCA',
            'account_name' => 'Owner Paid Proof History',
            'account_number_masked' => '1111****2222',
            'transfer_amount' => 50000,
            'transfer_date' => now(),
            'submitted_by_name' => 'Owner Paid Proof History',
            'submitted_by_email' => 'owner-paid-proof-history@example.com',
            'review_status' => 'approved',
            'review_notes' => 'Bukti terbaru valid.',
        ]);

        $this->get(route('platform.payment.show', ['invoiceNumber' => $invoice->invoice_number]))
            ->assertOk()
            ->assertSee('Invoice Siap Disimpan')
            ->assertDontSee('Upload Bukti Bayar');
    }

    public function test_upgrade_invoice_uses_plan_price_difference_instead_of_full_target_price(): void
    {
        $admin = User::query()->create([
            'name' => 'Platform Admin',
            'email' => 'platform-admin-upgrade@example.com',
            'password' => 'secret123',
            'is_platform_admin' => true,
        ]);

        $basic = Plan::query()->create([
            'code' => 'basic-upgrade',
            'name' => 'Basic Upgrade',
            'price' => 100000,
            'billing_cycle' => 'monthly',
            'max_businesses' => 1,
            'max_branches' => 1,
            'max_users' => 3,
            'features_json' => ['pos'],
            'is_active' => true,
        ]);

        $plus = Plan::query()->create([
            'code' => 'plus-upgrade',
            'name' => 'Plus Upgrade',
            'price' => 250000,
            'billing_cycle' => 'monthly',
            'max_businesses' => 3,
            'max_branches' => 5,
            'max_users' => 15,
            'features_json' => ['pos', 'stock', 'advanced_reports'],
            'is_active' => true,
        ]);

        $tenant = Tenant::query()->create([
            'code' => 'tenant-upgrade-gap',
            'name' => 'Tenant Upgrade Gap',
            'owner_name' => 'Owner Upgrade Gap',
            'owner_email' => 'owner-upgrade-gap@example.com',
            'status' => 'active',
        ]);

        $subscription = Subscription::query()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $basic->id,
            'status' => 'active',
            'started_at' => now()->subDay(),
            'trial_ends_at' => now()->addDays(30),
            'expired_at' => now()->addDays(30),
        ]);

        $this->actingAs($admin);

        Livewire::test(PaymentValidationManager::class); // warm auth/livewire context

        \Livewire\Livewire::test(\App\Livewire\Platform\Admin\TenantManager::class)
            ->call('selectTenant', $tenant->id)
            ->set('selectedPlanId', (string) $plus->id)
            ->set('paymentType', 'upgrade')
            ->set('paymentBillingCycle', 'monthly')
            ->set('paymentDueDays', 3)
            ->set('paymentAmount', '999999')
            ->set('paymentNotes', 'Upgrade ke plan plus')
            ->call('createManualPayment')
            ->assertHasNoErrors();

        $invoice = PlatformPaymentTransaction::query()->latest('id')->firstOrFail();

        $this->assertSame('upgrade', $invoice->type);
        $this->assertSame($plus->id, $invoice->plan_id);
        $this->assertSame(150000.0, (float) $invoice->amount_total);
        $this->assertSame('plan_difference', $invoice->meta_json['pricing_mode'] ?? null);
        $this->assertSame($basic->id, $invoice->meta_json['current_plan_id'] ?? null);
        $this->assertSame($plus->id, $invoice->meta_json['selected_plan_id'] ?? null);
        $this->assertSame($subscription->id, $invoice->subscription_id);
    }
}
