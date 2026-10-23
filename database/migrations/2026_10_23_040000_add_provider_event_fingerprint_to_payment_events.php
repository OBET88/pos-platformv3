<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('platform_payment_events', function (Blueprint $table) {
            $table->string('provider_event_key', 128)->nullable()->after('provider_reference');
            $table->unique(['payment_provider', 'provider_event_key'], 'payment_events_provider_event_unique');
        });
    }

    public function down(): void
    {
        Schema::table('platform_payment_events', function (Blueprint $table) {
            $table->dropUnique('payment_events_provider_event_unique');
            $table->dropColumn('provider_event_key');
        });
    }
};
