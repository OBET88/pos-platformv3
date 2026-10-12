<?php

namespace App\Http\Controllers;

use App\Models\Platform\Plan;
use App\Services\Platform\SubscriptionInvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Shell halaman publik upgrade paket.
 *
 * Logika harga & invoice ada di `SubscriptionInvoiceService`, UI di komponen
 * Livewire `App\Livewire\Platform\Public\UpgradeRequest`. Rute POST dipertahankan
 * sebagai fallback bila JavaScript mati.
 */
class PlatformUpgradeController extends Controller
{
    public function create(Request $request): View
    {
        return view('platform.upgrade-request', [
            'tenantCode' => trim((string) $request->query('tenant', '')),
            'selectedPlanId' => trim((string) $request->query('plan', '')),
            'selectedBillingCycle' => trim((string) $request->query('billing_cycle', '')),
        ]);
    }

    public function store(Request $request, SubscriptionInvoiceService $invoices): RedirectResponse
    {
        $validated = $request->validate([
            'tenant_code' => ['required', 'string', 'max:255'],
            'owner_email' => ['required', 'email', 'max:255'],
            'target_plan_id' => ['required', 'integer', 'exists:plans,id'],
            'billing_cycle' => ['required', 'in:monthly,quarterly,yearly'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $tenant = $invoices->findTenant((string) $validated['tenant_code']);

        if (! $tenant) {
            return back()->withErrors(['tenant_code' => 'Kode tenant tidak ditemukan.'])->withInput();
        }

        if (! $invoices->ownerEmailMatches($tenant, (string) $validated['owner_email'])) {
            return back()->withErrors(['owner_email' => 'Email owner tidak cocok dengan tenant yang dipilih.'])->withInput();
        }

        $subscription = $tenant->latestSubscription();

        if (! $subscription || ! $subscription->plan) {
            return back()->withErrors(['tenant_code' => 'Tenant ini belum memiliki paket aktif yang bisa di-upgrade.'])->withInput();
        }

        $targetPlan = Plan::query()
            ->where('is_active', true)
            ->find((int) $validated['target_plan_id']);

        if (! $targetPlan) {
            return back()->withErrors(['target_plan_id' => 'Paket tujuan upgrade tidak tersedia.'])->withInput();
        }

        $existingInvoice = $invoices->findExistingPendingInvoice($tenant, 'upgrade');

        if ($existingInvoice) {
            return redirect()
                ->route('platform.payment.show', ['invoiceNumber' => $existingInvoice->invoice_number])
                ->with('upgrade_existing_invoice', true);
        }

        try {
            $transaction = $invoices->createUpgradeInvoice(
                $tenant,
                $subscription,
                $targetPlan,
                (string) $validated['billing_cycle'],
                (string) $validated['owner_email'],
                $validated['notes'] ?? null,
            );
        } catch (\RuntimeException $exception) {
            return back()->withErrors(['target_plan_id' => $exception->getMessage()])->withInput();
        }

        return redirect()->route('platform.payment.show', ['invoiceNumber' => $transaction->invoice_number]);
    }
}
