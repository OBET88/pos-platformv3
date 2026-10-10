<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_pending_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('owner_email')->unique();
            $table->string('verification_token')->unique();
            $table->json('payload');
            $table->timestamp('verification_sent_at')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('consumed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_pending_registrations');
    }
};
