<?php

namespace App\Http\Middleware;

use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;
use App\Support\TenantFeatureRedirectResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantHasFeature
{
    public function __construct(
        private readonly TenantFeatureRedirectResolver $redirectResolver,
    ) {
    }

    public function handle(Request $request, Closure $next, string ...$features): Response
    {
        $tenant = $this->resolveTenant($request);

        abort_if($tenant === null, 404, 'Tenant context tidak ditemukan.');

        foreach ($features as $feature) {
            if (!$tenant->hasFeature($feature)) {
                if ($redirect = $this->resolveFallbackRedirect($request, $tenant)) {
                    return redirect($redirect)->with(
                        'warning',
                        'Akses ke halaman yang diminta tidak tersedia untuk paket tenant ini.'
                    );
                }

                abort(403, 'Fitur paket tidak tersedia untuk tenant ini.');
            }
        }

        $request->attributes->set('tenant', $tenant);

        return $next($request);
    }

    private function resolveTenant(Request $request): ?Tenant
    {
        $tenant = $request->attributes->get('tenant');

        if ($tenant instanceof Tenant) {
            return $tenant;
        }

        $routeTenant = $request->route('tenant');

        if ($routeTenant instanceof Tenant) {
            return $routeTenant->loadMissing('subscriptions.plan');
        }

        if (is_string($routeTenant) || is_int($routeTenant)) {
            return Tenant::query()
                ->with('subscriptions.plan')
                ->find($routeTenant);
        }

        $subscription = $request->route('subscription');

        if ($subscription instanceof Subscription) {
            return $subscription->tenant?->loadMissing('subscriptions.plan');
        }

        if (is_string($subscription) || is_int($subscription)) {
            $subscriptionModel = Subscription::query()
                ->with(['tenant.subscriptions.plan'])
                ->find($subscription);

            return $subscriptionModel?->tenant;
        }

        return null;
    }

    private function resolveFallbackRedirect(Request $request, Tenant $tenant): ?string
    {
        if (!$request->isMethod('GET') || $request->expectsJson() || $request->ajax()) {
            return null;
        }

        return $this->redirectResolver->resolve(
            $tenant,
            $request->route()?->getName()
        );
    }
}
