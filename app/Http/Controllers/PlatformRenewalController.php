<?php

namespace App\Http\Controllers;

use App\Services\Platform\SubscriptionInvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Shell halaman publik perpanjangan langganan.
 *
 * Logika invoice ada di `SubscriptionInvoiceService` dan UI-nya di komponen
 * Livewire `App\Livewire\Platform\Public\RenewalRequest`. Rute POST dipertahankan
 * sebagai fallback bila JavaScript mati.
 */
class PlatformRenewalController extends Controller
{
    public function create(Request $request): View
    {
        return view('platform.renewal-request', [
            'tenantCode' => trim((string) $request->query('tenant', '')),
        ]);
    }

    public function store(Request $request, SubscriptionInvoiceService $invoices): RedirectResponse
    {
        $validated = $request->validate([
            'tenant_code' => ['required', 'string', 'max:255'],
            'owner_email' => ['required', 'email', 'max:255'],
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
            return back()->withErrors(['tenant_code' => 'Tenant ini belum memiliki paket aktif yang bisa diperpanjang.'])->withInput();
        }

        $existingInvoice = $invoices->findExistingPendingInvoice($tenant, 'renewal');

        if ($existingInvoice) {
            return redirect()
                ->route('platform.payment.show', ['invoiceNumber' => $existingInvoice->invoice_number])
                ->with('renewal_existing_invoice', true);
        }

        $transaction = $invoices->createRenewalInvoice(
            $tenant,
            $subscription,
            (string) $validated['billing_cycle'],
            (string) $validated['owner_email'],
            $validated['notes'] ?? null,
        );

        return redirect()->route('platform.payment.show', ['invoiceNumber' => $transaction->invoice_number]);
    }
}
