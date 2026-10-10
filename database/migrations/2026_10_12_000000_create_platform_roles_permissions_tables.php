<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Role & permission granular untuk admin platform.
 *
 * Dibuat mandiri (tanpa package eksternal) karena aplikasi platform memakai
 * Laravel 13 yang belum tentu didukung paket permission pihak ketiga. Struktur
 * sengaja dibuat mirip pola role-permission yang sudah dipakai di app tenant.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('platform_roles')) {
            Schema::create('platform_roles', function (Blueprint $table): void {
                $table->id();
                $table->string('name')->unique();
                $table->string('label');
                $table->boolean('is_system')->default(false);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('platform_permissions')) {
            Schema::create('platform_permissions', function (Blueprint $table): void {
                $table->id();
                $table->string('name')->unique();
                $table->string('label');
                $table->string('group')->default('umum');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('platform_permission_role')) {
            Schema::create('platform_permission_role', function (Blueprint $table): void {
                $table->foreignId('platform_permission_id')->constrained('platform_permissions')->cascadeOnDelete();
                $table->foreignId('platform_role_id')->constrained('platform_roles')->cascadeOnDelete();
                $table->primary(['platform_permission_id', 'platform_role_id']);
            });
        }

        if (! Schema::hasTable('platform_role_user')) {
            Schema::create('platform_role_user', function (Blueprint $table): void {
                $table->foreignId('platform_role_id')->constrained('platform_roles')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->primary(['platform_role_id', 'user_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_role_user');
        Schema::dropIfExists('platform_permission_role');
        Schema::dropIfExists('platform_permissions');
        Schema::dropIfExists('platform_roles');
    }
};
