<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Shell halaman publik "Pesanan Saya".
 *
 * Seluruh pencarian invoice, validasi, dan tampilan hasil kini ditangani
 * komponen Livewire `App\Livewire\Platform\Public\OrderLookup`. Controller ini
 * hanya menyiapkan nilai awal dari query string / submit lama agar tautan lama
 * (termasuk POST) tetap berfungsi.
 */
class PlatformOrderController extends Controller
{
    public function create(Request $request): View
    {
        return view('platform.orders', [
            'tenantCode' => trim((string) $request->query('tenant', '')),
            'ownerEmail' => trim((string) $request->query('owner_email', '')),
        ]);
    }

    public function lookup(Request $request): View
    {
        $validated = $request->validate([
            'tenant_code' => ['required', 'string', 'max:255'],
            'owner_email' => ['required', 'email', 'max:255'],
        ]);

        return view('platform.orders', [
            'tenantCode' => trim((string) $validated['tenant_code']),
            'ownerEmail' => trim((string) $validated['owner_email']),
        ]);
    }
}
