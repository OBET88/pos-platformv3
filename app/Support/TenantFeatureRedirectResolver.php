<?php

namespace App\Support;

use App\Models\Platform\Tenant;
use Illuminate\Support\Facades\Route;

class TenantFeatureRedirectResolver
{
    public function resolve(Tenant $tenant, ?string $currentRouteName = null): ?string
    {
        $fallbacks = config('tenant-feature-fallbacks.redirect_priority', []);

        foreach ($fallbacks as $fallback) {
            $routeName = $fallback['route'] ?? null;
            $feature = $fallback['feature'] ?? null;

            if (!is_string($routeName) || $routeName === '') {
                continue;
            }

            if ($currentRouteName !== null && $routeName === $currentRouteName) {
                continue;
            }

            if (!Route::has($routeName)) {
                continue;
            }

            if (is_string($feature) && $feature !== '' && !$tenant->hasFeature($feature)) {
                continue;
            }

            return route($routeName, ['tenant' => $tenant]);
        }

        return null;
    }
}
