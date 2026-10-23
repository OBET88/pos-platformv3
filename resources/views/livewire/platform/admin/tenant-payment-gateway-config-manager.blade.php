<div
    class="space-y-6"
    x-data="{ toast: { show: false, message: '', type: 'success' }, timeout: null }"
    x-on:notify.window="
        toast = { show: true, message: $event.detail.message, type: $event.detail.type ?? 'success' };
        clearTimeout(timeout);
        timeout = setTimeout(() => toast.show = false, 3000);
    "
>
    <div
        x-show="toast.show"
        x-transition.opacity
        x-cloak
        class="fixed right-5 top-5 z-[100] max-w-md rounded-2xl px-4 py-3 text-sm font-semibold text-white shadow-lg"
        :class="{
            'bg-emerald-600': toast.type === 'success',
            'bg-rose-600': toast.type === 'danger',
            'bg-slate-800': toast.type === 'info',
        }"
        x-text="toast.message"
    ></div>

    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div class="max-w-3xl">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-emerald-700">Pengaturan Platform</p>
            <h1 class="mt-2 text-2xl font-semibold text-slate-900">Payment Gateway per tenant</h1>
            <p class="mt-2 text-sm text-slate-500">
                Simpan kredensial DOKU / Midtrans / Xendit milik masing-masing tenant di sini (bukan di <code>.env</code>).
                Kredensial dienkripsi dan dipakai tenant untuk QRIS dinamis.
            </p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('platform.admin.tenants.index') }}" class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-900 transition hover:bg-slate-50">
                Kembali ke Tenant Manager
            </a>
        </div>
    </div>

    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Pilih Tenant</p>
                <p class="mt-1 text-sm text-slate-500">Kredensial yang disimpan berlaku hanya untuk tenant yang dipilih.</p>
            </div>
            <select
                wire:model.live="selectedTenantId"
                class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none lg:max-w-sm"
            >
                <option value="">— Pilih tenant —</option>
                @foreach ($tenants as $tenant)
                    <option value="{{ $tenant->id }}">{{ $tenant->name }} ({{ $tenant->code }})</option>
                @endforeach
            </select>
        </div>
    </section>

    @if ($selectedTenant)
        <div class="grid gap-6 xl:grid-cols-[minmax(0,420px)_minmax(0,1fr)]">
            <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">
                            {{ $editingId ? 'Edit Konfigurasi' : 'Tambah Konfigurasi' }}
                        </p>
                        <h2 class="mt-1 text-lg font-semibold text-slate-900">{{ $selectedTenant->name }}</h2>
                    </div>
                    @if ($editingId)
                        <button type="button" wire:click="cancelEdit" class="rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                            Batal
                        </button>
                    @endif
                </div>

                <form wire:submit="save" class="space-y-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Provider</label>
                            <select wire:model="provider" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none">
                                @foreach ($providers as $option)
                                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                @endforeach
                            </select>
                            @error('provider')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Mode</label>
                            <select wire:model="mode" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none">
                                <option value="sandbox">Sandbox</option>
                                <option value="production">Production</option>
                            </select>
                            @error('mode')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Client ID</label>
                        <input wire:model="client_id" type="text" autocomplete="off" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none" placeholder="mis. BRN-xxxx">
                        @error('client_id')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Secret Key</label>
                        <input wire:model="secret_key" type="password" autocomplete="new-password" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none" placeholder="••••••••">
                        @error('secret_key')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Merchant ID</label>
                            <input wire:model="merchant_id" type="text" autocomplete="off" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none" placeholder="opsional">
                            @error('merchant_id')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Nama Merchant</label>
                            <input wire:model="merchant_name" type="text" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none" placeholder="opsional">
                            @error('merchant_name')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Callback URL</label>
                        <input wire:model="callback_url" type="text" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none" placeholder="https://...">
                        @error('callback_url')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Extra Config (JSON)</label>
                        <textarea
                            rows="3"
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 font-mono text-xs text-slate-900 focus:border-emerald-400 focus:outline-none"
                            x-data="{ raw: @js(json_encode($extra_config ?: new stdClass(), JSON_PRETTY_PRINT)) }"
                            x-model="raw"
                            x-on:input="try { $wire.set('extra_config', JSON.parse(raw || '{}')) } catch (e) {}"
                        ></textarea>
                        <p class="mt-1 text-xs text-slate-400">Opsional. Contoh: kunci tambahan khusus provider.</p>
                        @error('extra_config')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex flex-wrap gap-4">
                        <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                            <input wire:model="is_active" type="checkbox" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-400">
                            Aktif
                        </label>
                        <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                            <input wire:model="is_default" type="checkbox" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-400">
                            Jadikan default tenant ini
                        </label>
                    </div>

                    <button type="submit" class="w-full rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800" wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save">{{ $editingId ? 'Simpan Perubahan' : 'Tambah Konfigurasi' }}</span>
                        <span wire:loading wire:target="save">Menyimpan...</span>
                    </button>
                </form>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Daftar Konfigurasi</p>
                        <h2 class="mt-1 text-lg font-semibold text-slate-900">{{ $selectedTenant->name }}</h2>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $configs->total() }} konfigurasi</span>
                </div>

                <div class="space-y-3">
                    @forelse ($configs as $config)
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                <div class="space-y-2">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="rounded-full bg-slate-900 px-3 py-1 text-xs font-semibold uppercase text-white">{{ $config->provider }}</span>
                                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $config->mode === 'production' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                            {{ strtoupper($config->mode) }}
                                        </span>
                                        @if ($config->is_active)
                                            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">AKTIF</span>
                                        @else
                                            <span class="rounded-full bg-slate-200 px-3 py-1 text-xs font-semibold text-slate-600">NONAKTIF</span>
                                        @endif
                                        @if ($config->is_default)
                                            <span class="rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-700">DEFAULT</span>
                                        @endif
                                    </div>
                                    <div class="flex flex-wrap gap-2 text-xs text-slate-500">
                                        <span class="rounded-full bg-white px-2.5 py-1">Client ID: {{ $config->getClientId() !== '' ? $config->getClientId() : '-' }}</span>
                                        <span class="rounded-full bg-white px-2.5 py-1">Secret: {{ $config->getSecretKey() !== '' ? '••••••' : 'kosong' }}</span>
                                        @if ($config->getMerchantId() !== '')
                                            <span class="rounded-full bg-white px-2.5 py-1">Merchant: {{ $config->getMerchantId() }}</span>
                                        @endif
                                        @if ($config->merchant_name)
                                            <span class="rounded-full bg-white px-2.5 py-1">{{ $config->merchant_name }}</span>
                                        @endif
                                    </div>
                                    @if ($config->getCallbackUrl() !== '')
                                        <p class="break-all text-xs text-slate-500">Callback: {{ $config->getCallbackUrl() }}</p>
                                    @endif
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    <button type="button" wire:click="edit({{ $config->id }})" class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-100">
                                        Ubah
                                    </button>
                                    @unless ($config->is_default)
                                        <button type="button" wire:click="toggleDefault({{ $config->id }})" class="rounded-xl border border-sky-300 bg-white px-3 py-2 text-xs font-semibold text-sky-700 transition hover:bg-sky-50">
                                            Jadikan Default
                                        </button>
                                        <button type="button" wire:click="delete({{ $config->id }})" wire:confirm="Hapus konfigurasi gateway ini?" class="rounded-xl border border-rose-300 bg-white px-3 py-2 text-xs font-semibold text-rose-700 transition hover:bg-rose-50">
                                            Hapus
                                        </button>
                                    @endunless
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-10 text-center text-sm text-slate-500">
                            Belum ada konfigurasi gateway untuk tenant ini. Tambahkan lewat formulir di sebelah kiri.
                        </div>
                    @endforelse
                </div>

                @if ($configs->hasPages())
                    <div class="mt-4">
                        {{ $configs->links() }}
                    </div>
                @endif
            </section>
        </div>
    @else
        <div class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 px-4 py-12 text-center text-sm text-slate-500">
            Pilih tenant terlebih dahulu untuk mengatur kredensial payment gateway.
        </div>
    @endif
</div>
