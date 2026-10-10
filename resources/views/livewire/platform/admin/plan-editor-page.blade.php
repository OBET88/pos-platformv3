@php($planMeta = $planMetaInputs[$plan->id] ?? [])
@php($currentFeatures = collect($plan->features_json ?? []))

<div class="space-y-6">
    <div class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
            <div class="max-w-4xl">
                <div class="flex flex-wrap items-center gap-3 text-sm text-slate-500">
                    <a href="{{ route('platform.admin.plans.index') }}" wire:navigate class="font-semibold text-emerald-700 hover:text-emerald-600">Daftar Paket</a>
                    <span>/</span>
                    <span>Edit Paket</span>
                </div>
                <p class="mt-4 text-xs font-semibold uppercase tracking-[0.32em] text-emerald-700">Edit Paket</p>
                <h1 class="mt-3 text-3xl font-semibold tracking-tight text-slate-900">{{ $plan->name }}</h1>
                <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-500">Halaman ini khusus untuk identitas paket, harga, limit, dan status aktif. Mapping menu dipisah ke halaman lain.</p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('platform.admin.plans.mapping', $plan) }}" wire:navigate class="inline-flex items-center rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    Menu Mapping
                </a>
                <a href="{{ route('platform.admin.plans.index') }}" wire:navigate class="inline-flex items-center rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">
                    Kembali ke Daftar
                </a>
            </div>
        </div>
    </div>

    @if (session('platform_admin_message'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-4 text-sm text-emerald-800 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-emerald-700">Update Tersimpan</p>
            <p class="mt-1">{{ session('platform_admin_message') }}</p>
        </div>
    @endif

    @if (session('platform_admin_error'))
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-4 text-sm text-rose-800 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-rose-700">Perlu Perhatian</p>
            <p class="mt-1">{{ session('platform_admin_error') }}</p>
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-[320px_minmax(0,1fr)]">
        <aside class="space-y-4">
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Ringkasan Paket</p>
                <div class="mt-4 space-y-3">
                    <div class="flex items-center justify-between gap-3 rounded-2xl bg-slate-50 px-3 py-3">
                        <span class="text-sm text-slate-600">Kode paket</span>
                        <span class="text-sm font-semibold text-slate-900">{{ strtoupper($plan->code) }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3 rounded-2xl bg-slate-50 px-3 py-3">
                        <span class="text-sm text-slate-600">Status</span>
                        <span class="text-sm font-semibold {{ $plan->is_active ? 'text-emerald-700' : 'text-slate-700' }}">{{ $plan->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3 rounded-2xl bg-slate-50 px-3 py-3">
                        <span class="text-sm text-slate-600">Fitur aktif</span>
                        <span class="text-sm font-semibold text-slate-900">{{ number_format($currentFeatures->count()) }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3 rounded-2xl bg-slate-50 px-3 py-3">
                        <span class="text-sm text-slate-600">Tagihan</span>
                        <span class="text-sm font-semibold text-slate-900">{{ $plan->billing_cycle }}</span>
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-semibold text-slate-900">Aksi Cepat</p>
                <div class="mt-4 grid gap-3">
                    <button type="button" wire:click="duplicatePlan" wire:loading.attr="disabled" wire:target="duplicatePlan" class="rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-70">
                        <span wire:loading.remove wire:target="duplicatePlan">Duplikasi Paket</span>
                        <span wire:loading wire:target="duplicatePlan">Menduplikasi...</span>
                    </button>
                    <button type="button" wire:click="togglePlanActive" wire:loading.attr="disabled" wire:target="togglePlanActive" class="rounded-2xl px-4 py-3 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-70 {{ $plan->is_active ? (($planDeactivationConfirmations[$plan->id] ?? false) ? 'border border-amber-300 bg-amber-100 text-amber-900 hover:bg-amber-200' : 'border border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100') : 'border border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                        <span wire:loading.remove wire:target="togglePlanActive">{{ $plan->is_active ? (($planDeactivationConfirmations[$plan->id] ?? false) ? 'Konfirmasi Nonaktifkan Paket' : 'Nonaktifkan Paket') : 'Aktifkan Kembali Paket' }}</span>
                        <span wire:loading wire:target="togglePlanActive">{{ $plan->is_active ? 'Memproses Status...' : 'Mengaktifkan Paket...' }}</span>
                    </button>
                </div>
            </div>
        </aside>

        <form wire:submit="updatePlanMeta" class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label for="plan-code-{{ $plan->id }}" class="text-sm font-semibold text-slate-900">Kode Paket</label>
                    <input id="plan-code-{{ $plan->id }}" value="{{ strtoupper($plan->code) }}" type="text" readonly class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-100 px-4 py-3 text-sm font-semibold text-slate-500 outline-none">
                    <p class="mt-2 text-xs leading-5 text-slate-500">Kode paket dikunci setelah dibuat agar integrasi dan referensi paket tetap stabil.</p>
                </div>
                <div>
                    <label for="plan-name-{{ $plan->id }}" class="text-sm font-semibold text-slate-900">Nama Paket</label>
                    <input id="plan-name-{{ $plan->id }}" wire:model.live="planMetaInputs.{{ $plan->id }}.name" type="text" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none">
                    @error('planMetaInputs.'.$plan->id.'.name')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="plan-price-{{ $plan->id }}" class="text-sm font-semibold text-slate-900">Harga</label>
                    <input id="plan-price-{{ $plan->id }}" wire:model.live="planMetaInputs.{{ $plan->id }}.price" type="number" min="0" step="0.01" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none">
                    @error('planMetaInputs.'.$plan->id.'.price')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="plan-billing-cycle-{{ $plan->id }}" class="text-sm font-semibold text-slate-900">Siklus Tagihan</label>
                    <select id="plan-billing-cycle-{{ $plan->id }}" wire:model.live="planMetaInputs.{{ $plan->id }}.billing_cycle" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none">
                        <option value="monthly">Bulanan</option>
                        <option value="quarterly">Triwulanan</option>
                        <option value="yearly">Tahunan</option>
                        <option value="custom">Custom</option>
                    </select>
                    @error('planMetaInputs.'.$plan->id.'.billing_cycle')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="mt-4 grid gap-4 md:grid-cols-3">
                <div>
                    <label for="plan-max-businesses-{{ $plan->id }}" class="text-sm font-semibold text-slate-900">Maks. Bisnis</label>
                    <input id="plan-max-businesses-{{ $plan->id }}" wire:model.live="planMetaInputs.{{ $plan->id }}.max_businesses" type="number" min="1" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none">
                    @error('planMetaInputs.'.$plan->id.'.max_businesses')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="plan-max-branches-{{ $plan->id }}" class="text-sm font-semibold text-slate-900">Maks. Cabang</label>
                    <input id="plan-max-branches-{{ $plan->id }}" wire:model.live="planMetaInputs.{{ $plan->id }}.max_branches" type="number" min="1" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none">
                    @error('planMetaInputs.'.$plan->id.'.max_branches')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="plan-max-users-{{ $plan->id }}" class="text-sm font-semibold text-slate-900">Maks. User</label>
                    <input id="plan-max-users-{{ $plan->id }}" wire:model.live="planMetaInputs.{{ $plan->id }}.max_users" type="number" min="1" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none">
                    @error('planMetaInputs.'.$plan->id.'.max_users')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="mt-6 rounded-3xl border border-slate-200 bg-slate-50/70 p-4">
                <p class="text-sm font-semibold text-slate-900">Arah Pengelolaan</p>
                <p class="mt-2 text-sm leading-6 text-slate-500">Halaman ini hanya untuk detail paket yang operasional. Kode paket bersifat permanen. Kalau mau ganti menu seperti `COA`, `Laporan Akuntansi`, atau tool lain, pindah ke halaman `Menu Mapping`.</p>
            </div>

            <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="text-sm text-slate-500">
                    <p>Kode aktif: <span class="font-semibold text-slate-900">{{ $plan->code }}</span></p>
                </div>
                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-70" wire:loading.attr="disabled" wire:target="updatePlanMeta">
                    <span wire:loading.remove wire:target="updatePlanMeta">Simpan Edit Paket</span>
                    <span wire:loading wire:target="updatePlanMeta">Menyimpan Paket...</span>
                </button>
            </div>
        </form>
    </div>
</div>
