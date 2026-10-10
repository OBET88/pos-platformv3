<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $plusPlusPlan = DB::table('plans')
            ->where('code', 'plus_plus')
            ->first();

        if ($plusPlusPlan) {
            DB::table('plans')
                ->where('code', 'trial')
                ->update([
                    'name' => 'Trial',
                    'price' => 0,
                    'billing_cycle' => $plusPlusPlan->billing_cycle,
                    'max_businesses' => $plusPlusPlan->max_businesses,
                    'max_branches' => $plusPlusPlan->max_branches,
                    'max_users' => $plusPlusPlan->max_users,
                    'features_json' => $plusPlusPlan->features_json,
                    'is_active' => true,
                    'updated_at' => now(),
                ]);

            return;
        }

        DB::table('plans')
            ->where('code', 'trial')
            ->update([
                'name' => 'Trial',
                'price' => 0,
                'billing_cycle' => 'monthly',
                'max_businesses' => 999,
                'max_branches' => 999,
                'max_users' => 999,
                'features_json' => json_encode([
                    'menu_dashboard',
                    'menu_subscription_center',
                    'menu_reports',
                    'menu_product_reports',
                    'menu_stock_in',
                    'menu_stock_out',
                    'menu_raw_stock_reports',
                    'menu_production_make',
                    'menu_shift_history_self',
                    'menu_daily_report_self',
                    'menu_products',
                    'menu_categories',
                    'menu_floor_map_manager',
                    'menu_users',
                    'menu_finished_vs_sold',
                    'menu_recipes',
                    'menu_roles',
                    'menu_owner_cashier_recap',
                    'menu_shift_history_admin',
                    'menu_daily_report_admin',
                    'menu_audit_resume',
                    'menu_accounting_report',
                    'menu_accounting_admin_entry',
                    'menu_accounting_manual_journal',
                    'menu_accounting_settings',
                    'menu_branding',
                    'menu_promo_settings',
                    'menu_promo_vouchers',
                    'subscription_center',
                    'pos',
                    'stock',
                    'basic_reports',
                    'advanced_reports',
                    'production',
                    'accounting',
                    'accounting_report',
                    'accounting_coa',
                    'accounting_admin_entry',
                    'accounting_manual_journal',
                    'audit',
                    'promotions',
                    'priority_support',
                ]),
                'is_active' => true,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('plans')
            ->where('code', 'trial')
            ->update([
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
                'updated_at' => now(),
            ]);
    }
};
