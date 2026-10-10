<?php

use App\Http\Middleware\EnsurePlatformAdmin;
use App\Http\Middleware\EnsurePlatformPermission;
use App\Http\Middleware\EnsureTenantHasFeature;
use App\Http\Middleware\ForceHttps;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('platform:refresh-tenant-events --days=3')->hourly();
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(ForceHttps::class);

        $middleware->alias([
            'platform.admin' => EnsurePlatformAdmin::class,
            'platform.can' => EnsurePlatformPermission::class,
            'tenant.feature' => EnsureTenantHasFeature::class,
        ]);

        $middleware->redirectGuestsTo(fn (Request $request) => route('platform.login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
