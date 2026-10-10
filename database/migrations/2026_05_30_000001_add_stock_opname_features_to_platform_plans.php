<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const PLAN_FEATURES = [
        'pro' => [
            'menu_stock_transfer',
            'menu_finished_stock_opname',
            'menu_raw_stock_opname',
        ],
        'enterprise' => [
            'menu_stock_transfer',
            'menu_finished_stock_opname',
            'menu_raw_stock_opname',
        ],
        'plus_plus' => [
            'menu_marketplace_orders',
            'menu_stock_conversion',
            'menu_stock_transfer',
            'menu_finished_stock_opname',
            'menu_raw_stock_opname',
            'menu_accounting_cash_flow',
            'menu_accounting_owner_summary',
        ],
    ];

    public function up(): void
    {
        if (! Schema::hasTable('plans')) {
            return;
        }

        foreach (self::PLAN_FEATURES as $code => $featuresToAdd) {
            $plan = DB::table('plans')
                ->where('code', $code)
                ->first(['id', 'features_json']);

            if (! $plan) {
                continue;
            }

            $features = $this->decodeFeatures($plan->features_json);

            DB::table('plans')
                ->where('id', $plan->id)
                ->update([
                    'features_json' => json_encode(array_values(array_unique([
                        ...$features,
                        ...$featuresToAdd,
                    ]))),
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('plans')) {
            return;
        }

        foreach (self::PLAN_FEATURES as $code => $featuresToRemove) {
            $plan = DB::table('plans')
                ->where('code', $code)
                ->first(['id', 'features_json']);

            if (! $plan) {
                continue;
            }

            $features = array_values(array_diff(
                $this->decodeFeatures($plan->features_json),
                $featuresToRemove,
            ));

            DB::table('plans')
                ->where('id', $plan->id)
                ->update([
                    'features_json' => json_encode($features),
                    'updated_at' => now(),
                ]);
        }
    }

    private function decodeFeatures(mixed $features): array
    {
        if (is_string($features)) {
            $decoded = json_decode($features, true);
            $features = is_array($decoded) ? $decoded : [];
        }

        return collect(is_array($features) ? $features : [])
            ->filter(fn ($feature) => is_string($feature) && trim($feature) !== '')
            ->map(fn (string $feature) => trim($feature))
            ->values()
            ->all();
    }
};
