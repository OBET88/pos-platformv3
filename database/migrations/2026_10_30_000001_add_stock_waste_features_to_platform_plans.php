<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Plan yang sudah memiliki preset `menu_stock_waste` di
     * InteractsWithPlans::PLAN_PRESETS (basic, pro, enterprise, plus_plus).
     * Migrasi ini menyinkronkan features_json di DB agar plan yang sudah
     * ter-seed ikut mendapat fitur Waste Stok, karena planPresets()
     * memprioritaskan $plan->features_json dari DB bila plan sudah ada.
     */
    private const PLAN_FEATURES = [
        'basic' => [
            'menu_stock_waste',
        ],
        'pro' => [
            'menu_stock_waste',
        ],
        'enterprise' => [
            'menu_stock_waste',
        ],
        'plus_plus' => [
            'menu_stock_waste',
        ],
        'trial' => [
            'menu_stock_waste',
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
