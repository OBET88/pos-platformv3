<?php

namespace App\Http\Controllers;

use App\Models\Platform\Tenant;
use Illuminate\View\View;

class PlatformAdminTenantController extends Controller
{
    public function index(): View
    {
        $tenants = Tenant::query()
            ->with(['subscriptions.plan', 'domains'])
            ->latest('id')
            ->paginate(12);

        $stats = [
            'total_tenants' => Tenant::query()->count(),
            'pending_setup' => Tenant::query()->where('status', 'pending_setup')->count(),
            'trial' => Tenant::query()->whereHas('subscriptions', fn ($query) => $query->where('status', 'trial'))->count(),
            'active' => Tenant::query()->where('status', 'active')->count(),
        ];

        return view('platform.admin.tenants.index', [
            'tenants' => $tenants,
            'stats' => $stats,
        ]);
    }

    public function show(Tenant $tenant): View
    {
        $tenant->load([
            'subscriptions.plan',
            'domains',
            'databaseConfigs',
            'provisionLogs' => fn ($query) => $query->latest('id'),
        ]);

        return view('platform.admin.tenants.show', [
            'tenant' => $tenant,
            'latestSubscription' => $tenant->subscriptions->sortByDesc('id')->first(),
        ]);
    }
}
