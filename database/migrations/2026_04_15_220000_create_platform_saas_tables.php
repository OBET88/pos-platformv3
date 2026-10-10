<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->decimal('price', 15, 2)->default(0);
            $table->string('billing_cycle')->default('monthly');
            $table->unsignedInteger('max_businesses')->default(1);
            $table->unsignedInteger('max_branches')->default(1);
            $table->unsignedInteger('max_users')->default(3);
            $table->json('features_json')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('owner_name');
            $table->string('owner_email')->index();
            $table->string('owner_phone')->nullable();
            $table->string('status')->default('pending_setup')->index();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('onboarded_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('plans')->restrictOnDelete();
            $table->string('status')->default('trial')->index();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamps();
        });

        Schema::create('tenant_domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('domain')->unique();
            $table->string('type')->default('subdomain');
            $table->boolean('is_primary')->default(true);
            $table->timestamps();
        });

        Schema::create('tenant_database_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('connection_name')->nullable();
            $table->string('db_host')->nullable();
            $table->unsignedInteger('db_port')->nullable();
            $table->string('db_name')->nullable();
            $table->string('db_username')->nullable();
            $table->text('db_password_encrypted')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });

        Schema::create('tenant_provision_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('step');
            $table->string('status')->default('pending');
            $table->text('message')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });

        DB::table('plans')->insert([
            [
                'code' => 'basic',
                'name' => 'Basic',
                'price' => 149000,
                'billing_cycle' => 'monthly',
                'max_businesses' => 1,
                'max_branches' => 1,
                'max_users' => 3,
                'features_json' => json_encode([
                    'menu_dashboard',
                    'menu_subscription_center',
                    'menu_reports',
                    'menu_product_reports',
                    'menu_stock_in',
                    'menu_stock_out',
                    'menu_stock_conversion',
                    'menu_shift_history_self',
                    'menu_daily_report_self',
                    'menu_products',
                    'menu_categories',
                    'menu_users',
                    'menu_membership',
                    'membership_points',
                    'menu_shift_history_admin',
                    'menu_daily_report_admin',
                    'menu_branding',
                    'subscription_center',
                    'pos',
                    'stock',
                    'basic_reports',
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'pro',
                'name' => 'Pro',
                'price' => 399000,
                'billing_cycle' => 'monthly',
                'max_businesses' => 3,
                'max_branches' => 5,
                'max_users' => 15,
                'features_json' => json_encode([
                    'pos',
                    'stock',
                    'production',
                    'accounting',
                    'advanced_reports',
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'enterprise',
                'name' => 'Enterprise',
                'price' => 0,
                'billing_cycle' => 'custom',
                'max_businesses' => 999,
                'max_branches' => 999,
                'max_users' => 999,
                'features_json' => json_encode([
                    'pos',
                    'stock',
                    'production',
                    'accounting',
                    'advanced_reports',
                    'priority_support',
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_provision_logs');
        Schema::dropIfExists('tenant_database_configs');
        Schema::dropIfExists('tenant_domains');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('tenants');
        Schema::dropIfExists('plans');
    }
};
