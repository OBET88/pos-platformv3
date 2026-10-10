<?php

namespace App\Providers;

use App\Http\Middleware\EnsurePlatformAdmin;
use App\Models\Platform\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Livewire\Mechanisms\PersistentMiddleware\PersistentMiddleware;

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
        // Super-admin (flag lama) lolos semua cek Gate/permission platform.
        Gate::before(function (User $user, string $ability): ?bool {
            return $user->is_platform_admin ? true : null;
        });

        Blade::if('tenantFeature', function (string $feature, ?Tenant $tenant = null): bool {
            $tenant ??= request()->attributes->get('tenant');

            return $tenant instanceof Tenant && $tenant->hasFeature($feature);
        });

        // Livewire update requests tidak melewati middleware route halaman.
        // Middleware yang terdaftar sebagai "persistent" dijalankan ulang pada
        // endpoint /livewire/update, sehingga proteksi admin platform tetap
        // berlaku untuk semua aksi Livewire (bukan hanya render halaman).
        app(PersistentMiddleware::class)
            ->addPersistentMiddleware([EnsurePlatformAdmin::class]);
    }
}
