<?php

namespace App\Http\Controllers;

use App\Models\Platform\Plan;
use App\Models\Platform\Tenant;
use App\Models\Platform\TenantDomain;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class TenantSubscriptionCenterController extends Controller
{
    public function __invoke(Request $request): View
    {
        $tenant = $this->resolveTenant($request);
        $subscription = $tenant?->latestSubscription();
        $currentPlan = $subscription?->plan;
        $availableUpgradePlans = collect();

        if ($currentPlan) {
            $availableUpgradePlans = Plan::query()
                ->where('is_active', true)
                ->where('id', '!=', $currentPlan->getKey())
                ->orderBy('price')
                ->get()
                ->filter(fn (Plan $plan) => (float) $plan->price > (float) $currentPlan->price)
                ->values();
        }

        return view('subscription.index', [
            'tenant' => $tenant,
            'subscription' => $subscription,
            'currentPlan' => $currentPlan,
            'availableUpgradePlans' => $availableUpgradePlans,
            'renewalUrl' => route('platform.renewal.create', ['tenant' => $tenant?->code]),
            'upgradeUrl' => route('platform.upgrade.create', ['tenant' => $tenant?->code]),
            'ordersUrl' => route('platform.orders.index', ['tenant' => $tenant?->code]),
        ]);
    }

    private function resolveTenant(Request $request): ?Tenant
    {
        $host = strtolower(trim((string) (
            $request->header('host')
            ?: $request->server('HTTP_HOST')
            ?: $request->server('SERVER_NAME')
            ?: $request->getHost()
        )));

        if (str_contains($host, ':')) {
            $host = (string) str($host)->before(':');
        }

        if ($host === '') {
            return null;
        }

        $tenantDomain = TenantDomain::query()
            ->with(['tenant.subscriptions.plan'])
            ->whereRaw('LOWER(domain) = ?', [$host])
            ->first();

        return $tenantDomain?->tenant;
    }
}
