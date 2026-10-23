<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div class="max-w-3xl">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-sky-700">Pengaturan Platform</p>
            <h1 class="mt-2 text-2xl font-semibold text-slate-900">Gateway pembayaran langganan</h1>
            <p class="mt-2 text-sm text-slate-500">
                Kredensial DOKU milik <strong>platform</strong> untuk menagih perpanjangan, upgrade, dan downgrade tenant.
                Ini terpisah dari kredensial gateway per-tenant yang dipakai QRIS di POS penjualan.
            </p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('platform.admin.tenants.index') }}" wire:navigate class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                Tenant Manager
            </a>
            <a href="{{ route('platform.admin.gateways.index') }}" wire:navigate class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                Gateway per Tenant
            </a>
        </div>
    </div>

    @if (session('platform_admin_message'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-4 text-sm text-emerald-800 shadow-sm">
            {{ session('platform_admin_message') }}
        </div>
    @endif

    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-wrap items-center gap-3">
            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $activeGateway ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                {{ $activeGateway ? 'AKTIF — tombol DOKU Checkout tampil di halaman invoice' : 'NONAKTIF — tenant hanya bisa transfer manual' }}
            </span>
        </div>
    </div>

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <form wire:submit="save" class="space-y-5">
            <div class="grid gap-5 sm:grid-cols-2">
                <label class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <input wire:model="enabled" type="checkbox" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-400">
                    <span class="text-sm font-semibold text-slate-700">Aktifkan DOKU Checkout untuk langganan</span>
                </label>

                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Mode</label>
                    <select wire:model.live="mode" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none">
                        <option value="sandbox">Sandbox</option>
                        <option value="production">Production</option>
                    </select>
                    @error('mode')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Client ID</label>
                    <input wire:model="clientId" type="text" autocomplete="off" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none" placeholder="mis. BRN-xxxx">
                    @error('clientId')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Secret Key
                        @if ($secretConfigured)
                            <span class="ml-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">tersimpan</span>
                        @endif
                    </label>
                    <input wire:model="secretKey" type="password" autocomplete="new-password" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none" placeholder="{{ $secretConfigured ? 'Kosongkan bila tidak diubah' : '••••••••' }}">
                    @error('secretKey')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Base URL</label>
                <input wire:model="baseUrl" type="text" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none" placeholder="https://api-sandbox.doku.com">
                @error('baseUrl')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                <p class="mt-1 text-xs text-slate-400">Sandbox: <code>https://api-sandbox.doku.com</code> · Production: <code>https://api.doku.com</code></p>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Nama Merchant</label>
                    <input wire:model="merchantName" type="text" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none" placeholder="opsional">
                    @error('merchantName')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Metode Pembayaran</label>
                    <input wire:model="paymentMethodTypes" type="text" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none" placeholder="mis. VIRTUAL_ACCOUNT,QRIS (opsional)">
                    @error('paymentMethodTypes')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                    <p class="mt-1 text-xs text-slate-400">Pisahkan dengan koma. Kosongkan untuk memakai semua metode yang aktif di akun DOKU.</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3 border-t border-slate-200 pt-5">
                <button type="submit" class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save">Simpan Pengaturan</span>
                    <span wire:loading wire:target="save">Menyimpan...</span>
                </button>
                <p class="text-xs text-slate-500">Kredensial disimpan terenkripsi di database platform (bukan <code>.env</code>).</p>
            </div>
        </form>
    </section>

    <section class="rounded-3xl border border-sky-200 bg-sky-50 p-5 text-sm text-sky-900">
        <p class="font-semibold">Cara kerja</p>
        <ul class="mt-2 list-disc space-y-1 pl-5 text-sky-800">
            <li>Tenant membuka invoice perpanjangan/upgrade, lalu memilih <strong>Bayar via DOKU Checkout</strong>.</li>
            <li>Setelah DOKU mengirim notifikasi sukses ke webhook platform, langganan aktif/perpanjang otomatis tanpa validasi manual.</li>
            <li>Bila gateway belum aktif, tenant tetap bisa transfer manual + unggah bukti (divalidasi admin).</li>
        </ul>
    </section>
</div>
