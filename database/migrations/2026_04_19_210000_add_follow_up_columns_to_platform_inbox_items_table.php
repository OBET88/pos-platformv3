<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('platform_inbox_items', function (Blueprint $table) {
            $table->string('follow_up_status', 30)->default('open')->after('tone');
            $table->text('follow_up_note')->nullable()->after('payload');
            $table->foreignId('follow_up_user_id')->nullable()->after('follow_up_note')->constrained('users')->nullOnDelete();
            $table->timestamp('follow_up_at')->nullable()->after('follow_up_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('platform_inbox_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('follow_up_user_id');
            $table->dropColumn([
                'follow_up_status',
                'follow_up_note',
                'follow_up_at',
            ]);
        });
    }
};
