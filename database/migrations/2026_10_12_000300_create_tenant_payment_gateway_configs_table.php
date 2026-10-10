<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_payment_gateway_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('provider'); // doku, midtrans, xendit
            $table->string('mode')->default('sandbox'); // sandbox, production
            $table->text('client_id_encrypted')->nullable();
            $table->text('secret_key_encrypted')->nullable();
            $table->text('merchant_id_encrypted')->nullable(); // untuk Midtrans/Xendit
            $table->string('merchant_name')->nullable();
            $table->text('callback_url_encrypted')->nullable();
            $table->json('extra_config_encrypted')->nullable(); // field tambahan per provider
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false); // fallback platform credential
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['tenant_id', 'provider'], 'tenant_gateway_unique');
            $table->index(['tenant_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_payment_gateway_configs');
    }
};
