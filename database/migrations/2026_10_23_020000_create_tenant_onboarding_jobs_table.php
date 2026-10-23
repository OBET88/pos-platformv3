<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_onboarding_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->unique()->constrained('tenants')->cascadeOnDelete();
            $table->string('status')->default('queued')->index();
            $table->json('payload');
            $table->string('access_token_hash', 64)->nullable();
            $table->timestamp('access_expires_at')->nullable();
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('available_at')->nullable()->index();
            $table->timestamp('leased_at')->nullable();
            $table->timestamp('lease_expires_at')->nullable()->index();
            $table->string('lease_token', 64)->nullable();
            $table->string('last_error_class')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamp('last_started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'available_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_onboarding_jobs');
    }
};
