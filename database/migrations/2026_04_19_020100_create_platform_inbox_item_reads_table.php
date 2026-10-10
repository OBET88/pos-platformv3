<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_inbox_item_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inbox_item_id')->constrained('platform_inbox_items')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('read_at');
            $table->timestamps();

            $table->unique(['inbox_item_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_inbox_item_reads');
    }
};
