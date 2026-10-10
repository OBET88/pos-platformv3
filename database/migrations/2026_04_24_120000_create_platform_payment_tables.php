<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained('subscriptions')->nullOnDelete();
            $table->foreignId('plan_id')->constrained('plans')->restrictOnDelete();
            $table->string('invoice_number')->unique();
            $table->string('type')->default('renewal')->index();
            $table->string('payment_method')->default('manual_transfer')->index();
            $table->string('payment_channel')->nullable();
            $table->string('payment_provider')->nullable()->index();
            $table->string('provider_reference')->nullable()->index();
            $table->decimal('amount_subtotal', 15, 2)->default(0);
            $table->decimal('amount_discount', 15, 2)->default(0);
            $table->decimal('amount_total', 15, 2)->default(0);
            $table->string('billing_cycle')->default('monthly');
            $table->string('status')->default('draft')->index();
            $table->timestamp('due_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->text('notes')->nullable();
            $table->json('meta_json')->nullable();
            $table->timestamps();
        });

        Schema::create('platform_payment_proofs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_transaction_id')->constrained('platform_payment_transactions')->cascadeOnDelete();
            $table->string('bank_name')->nullable();
            $table->string('account_name')->nullable();
            $table->string('account_number_masked')->nullable();
            $table->decimal('transfer_amount', 15, 2)->default(0);
            $table->timestamp('transfer_date')->nullable();
            $table->string('proof_file_path')->nullable();
            $table->string('submitted_by_name')->nullable();
            $table->string('submitted_by_email')->nullable();
            $table->string('review_status')->default('pending')->index();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('platform_payment_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_transaction_id')->nullable()->constrained('platform_payment_transactions')->nullOnDelete();
            $table->string('payment_provider')->index();
            $table->string('event_key')->index();
            $table->string('provider_reference')->nullable()->index();
            $table->boolean('signature_valid')->default(false);
            $table->json('payload_json')->nullable();
            $table->string('process_status')->default('received')->index();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_payment_events');
        Schema::dropIfExists('platform_payment_proofs');
        Schema::dropIfExists('platform_payment_transactions');
    }
};
