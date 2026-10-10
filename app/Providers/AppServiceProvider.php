<?php

namespace App\Providers;

use App\Models\Platform\Tenant;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Blade::if('tenantFeature', function (string $feature, ?Tenant $tenant = null): bool {
            $tenant ??= request()->attributes->get('tenant');

            return $tenant instanceof Tenant && $tenant->hasFeature($feature);
        });
    }
}
