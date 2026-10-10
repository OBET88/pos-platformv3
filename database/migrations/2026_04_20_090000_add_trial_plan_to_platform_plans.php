<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('plans')->updateOrInsert(
            ['code' => 'trial'],
            [
                'name' => 'Trial',
                'price' => 0,
                'billing_cycle' => 'monthly',
                'max_businesses' => 1,
                'max_branches' => 1,
                'max_users' => 3,
                'features_json' => json_encode([
                    'menu_dashboard',
                    'menu_reports',
                    'menu_product_reports',
                    'menu_products',
                    'menu_categories',
                    'menu_accounting_settings',
                    'pos',
                    'basic_reports',
                    'accounting_coa',
                ]),
                'is_active' => true,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );
    }

    public function down(): void
    {
        DB::table('plans')
            ->where('code', 'trial')
            ->delete();
    }
};
