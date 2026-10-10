@php($selectedKeys = collect($planFeatureInputs[$plan->id] ?? []))
@php($packageSelectedKeys = $this->packageFeatureKeys($selectedKeys))
@php($customKeys = collect(preg_split('/[\r\n,]+/', (string) ($planCustomFeatureInputs[$plan->id] ?? '')) ?: [])->filter(fn ($item) => trim((string) $item) !== ''))
@php($visibleFeatureGroups = $this->filteredFeatureGroups())
@php($groupedFeatures = collect($visibleFeatureGroups)->flatMap(fn ($group) => array_keys($group['features']))->all())
@php($systemFeatureLabels = $this->systemFeatureLabels())
@php($ungroupedFeatures = collect($featureCatalog)->except(array_merge($groupedFeatures, array_keys($systemFeatureLabels))))

<div class="space-y-6">
    <div class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
            <div class="max-w-4xl">
                <div class="flex flex-wrap items-center gap-3 text-sm text-slate-500">
                    <a href="{{ route('platform.admin.plans.index') }}" wire:navigate class="font-semibold text-sky-700 hover:text-sky-600">Daftar Paket</a>
                    <span>/</span>
                    <span>Menu Mapping</span>
                </div>
                <p class="mt-4 text-xs font-semibold uppercase tracking-[0.32em] text-sky-700">Menu Mapping</p>
                <h1 class="mt-3 text-3xl font-semibold tracking-tight text-slate-900">{{ $plan->name }}</h1>
                <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-500">Atur tool dan menu yang benar-benar dibutuhkan paket ini. Halaman ini sengaja full page supaya konten panjang tetap nyaman di-scroll.</p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('platform.admin.plans.edit', $plan) }}" wire:navigate class="inline-flex items-center rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    Edit Paket
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
                        <span class="text-sm text-slate-600">Key dipilih</span>
                        <span class="text-sm font-semibold text-slate-900">{{ number_format($packageSelectedKeys->count()) }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3 rounded-2xl bg-slate-50 px-3 py-3">
                        <span class="text-sm text-slate-600">Key custom</span>
                        <span class="text-sm font-semibold text-slate-900">{{ number_format($customKeys->count()) }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3 rounded-2xl bg-slate-50 px-3 py-3">
                        <span class="text-sm text-slate-600">Status paket</span>
                        <span class="text-sm font-semibold {{ $plan->is_active ? 'text-emerald-700' : 'text-slate-700' }}">{{ $plan->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                    </div>
                    <div class="rounded-2xl border border-sky-200 bg-sky-50 px-3 py-3">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-sky-700">Menu Sistem</p>
                        <p class="mt-1 text-xs leading-5 text-sky-900">Pusat Langganan selalu tersedia untuk semua tenant dan tidak dihitung sebagai key paket.</p>
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-semibold text-slate-900">Arah Halaman</p>
                <div class="mt-3 space-y-2 text-sm leading-6 text-slate-500">
                    <p>`Menu Mapping` hanya untuk menu dan tool.</p>
                    <p>Kalau mau ubah nama, harga, limit, atau status paket, pindah ke halaman `Edit Paket`.</p>
                </div>
            </div>
        </aside>

        <form wire:submit="savePlanFeatures" class="space-y-5">
            <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_280px]">
                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-sky-700">Mapping Paket</p>
                            <h2 class="mt-2 text-2xl font-semibold text-slate-900">Atur menu untuk paket {{ $plan->name }}</h2>
                            <p class="mt-2 text-sm leading-6 text-slate-500">Grup Akuntansi dipisah sendiri agar lebih gampang bikin paket yang hanya berisi COA atau bagian tertentu saja.</p>
                            <p class="mt-2 text-xs leading-5 text-amber-700">Saat Anda pilih menu baru seperti <span class="font-semibold">Dashboard / POS</span>, sistem akan otomatis menambahkan key legacy yang masih dibaca aplikasi tenant lama.</p>
                        </div>
                        <div class="rounded-2xl bg-slate-100 px-4 py-3 text-sm text-slate-600">
                            <span class="font-semibold text-slate-900">{{ number_format($packageSelectedKeys->count()) }}</span> key paket sedang dipilih
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-5 shadow-sm">
                    <label for="custom-features-{{ $plan->id }}" class="text-sm font-semibold text-slate-900">Key custom</label>
                    <p class="mt-1 text-xs leading-5 text-slate-500">Untuk rollout baru yang belum masuk katalog utama.</p>
                    <textarea id="custom-features-{{ $plan->id }}" wire:model.live="planCustomFeatureInputs.{{ $plan->id }}" rows="6" class="mt-3 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-400 focus:outline-none" placeholder="menu_kitchen_board&#10;multi_warehouse"></textarea>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">Template Rekomendasi</p>
                        <p class="mt-1 text-xs text-slate-500">Checklist default mengikuti konfigurasi plan acuan di DB saat ini, lalu tetap bisa Anda edit dinamis. Pusat Langganan tetap tersedia sebagai menu sistem di semua tenant.</p>
                    </div>
                </div>

                <div class="mt-4 grid gap-3 xl:grid-cols-3">
                    @foreach ($planPresets as $presetKey => $preset)
                        <button type="button" wire:click="applyPlanPreset('{{ $presetKey }}')" wire:loading.attr="disabled" wire:target="applyPlanPreset('{{ $presetKey }}')" class="rounded-3xl border border-slate-200 bg-slate-50/70 p-4 text-left transition hover:border-sky-300 hover:bg-sky-50 disabled:cursor-not-allowed disabled:opacity-70">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-sm font-semibold text-slate-900">{{ $preset['label'] }}</span>
                                    @if (!empty($preset['badge']))
                                        <span class="rounded-full bg-sky-100 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-sky-700">{{ $preset['badge'] }}</span>
                                    @endif
                                </div>
                                <span class="rounded-full bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-500 ring-1 ring-slate-200">{{ $this->packageFeatureCount($preset['features']) }} key paket</span>
                            </div>
                            <p class="mt-2 text-xs leading-5 text-slate-500">
                                <span wire:loading.remove wire:target="applyPlanPreset('{{ $presetKey }}')">{{ $preset['description'] }}</span>
                                <span wire:loading wire:target="applyPlanPreset('{{ $presetKey }}')">Menerapkan preset paket...</span>
                            </p>
                            <p class="mt-3 text-xs font-semibold uppercase tracking-[0.22em] text-emerald-700">{{ $preset['price_hint'] ?? 'Harga fleksibel' }}</p>
                            <p class="mt-2 text-xs leading-5 text-slate-600">{{ $preset['ideal_for'] ?? 'Preset ini bisa tetap diedit sesuai strategi paket.' }}</p>
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">Cari menu atau key</p>
                        <p class="mt-1 text-xs text-slate-500">Ketik nama menu, area, atau key teknis untuk mempersempit daftar item.</p>
                    </div>
                    <div class="w-full lg:max-w-md">
                        <input wire:model.live.debounce.250ms="planSearchInputs.{{ $plan->id }}" type="text" placeholder="Cari contoh: akuntansi, promo, menu_products" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-400 focus:outline-none">
                    </div>
                </div>
            </div>

            <div class="grid gap-4 2xl:grid-cols-2">
                @foreach ($visibleFeatureGroups as $groupName => $group)
                    @php($groupFeatureKeys = array_keys($group['features']))
                    @php($selectedInGroup = $selectedKeys->intersect($groupFeatureKeys)->count())
                    <section class="rounded-3xl border border-slate-200 bg-slate-50/70 p-4 shadow-sm">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-base font-semibold text-slate-900">{{ $groupName }}</h3>
                                    @if (!empty($group['badge']))
                                        @php($groupBadgeClass = match($group['badge']) {
                                            'Wajib' => 'bg-emerald-100 text-emerald-700',
                                            'Premium Add-on' => 'bg-violet-100 text-violet-700',
                                            'Teknis' => 'bg-slate-200 text-slate-700',
                                            default => 'bg-sky-100 text-sky-700',
                                        })
                                        <span class="rounded-full px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] {{ $groupBadgeClass }}">{{ $group['badge'] }}</span>
                                    @endif
                                </div>
                                <p class="mt-1 text-sm leading-6 text-slate-500">{{ $group['description'] }}</p>
                                @if (!empty($group['business_context']) || !empty($group['sell_when']))
                                    <div class="mt-3 grid gap-2 md:grid-cols-2">
                                        @if (!empty($group['business_context']))
                                            <div class="rounded-2xl border border-slate-200 bg-white px-3 py-3">
                                                <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-500">Nilai Bisnis</p>
                                                <p class="mt-1 text-xs leading-5 text-slate-600">{{ $group['business_context'] }}</p>
                                            </div>
                                        @endif
                                        @if (!empty($group['sell_when']))
                                            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-3 py-3">
                                                <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-amber-700">Kapan Dijual</p>
                                                <p class="mt-1 text-xs leading-5 text-amber-800">{{ $group['sell_when'] }}</p>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            <div class="flex flex-wrap items-center justify-end gap-2">
                                <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-600 ring-1 ring-slate-200">
                                    {{ $selectedInGroup }}/{{ count($group['features']) }} dipilih
                                </span>
                                <button type="button" wire:click="selectGroupFeatures('{{ addslashes($groupName) }}')" wire:loading.attr="disabled" wire:target="selectGroupFeatures('{{ addslashes($groupName) }}')" class="rounded-full border border-slate-300 bg-white px-3 py-1 text-xs font-semibold text-slate-700 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-70">
                                    <span wire:loading.remove wire:target="selectGroupFeatures('{{ addslashes($groupName) }}')">Pilih Semua</span>
                                    <span wire:loading wire:target="selectGroupFeatures('{{ addslashes($groupName) }}')">Memilih...</span>
                                </button>
                                <button type="button" wire:click="clearGroupFeatures('{{ addslashes($groupName) }}')" wire:loading.attr="disabled" wire:target="clearGroupFeatures('{{ addslashes($groupName) }}')" class="rounded-full border border-rose-200 bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700 transition hover:bg-rose-100 disabled:cursor-not-allowed disabled:opacity-70">
                                    <span wire:loading.remove wire:target="clearGroupFeatures('{{ addslashes($groupName) }}')">Kosongkan Grup</span>
                                    <span wire:loading wire:target="clearGroupFeatures('{{ addslashes($groupName) }}')">Mengosongkan...</span>
                                </button>
                            </div>
                        </div>

                        <div class="mt-4 grid gap-2">
                            @foreach ($group['features'] as $featureKey => $featureLabel)
                                @php($checked = $selectedKeys->contains($featureKey))
                                <label class="group flex cursor-pointer items-start gap-3 rounded-2xl border px-4 py-3 transition {{ $checked ? 'border-emerald-300 bg-emerald-50/80' : 'border-slate-200 bg-white hover:border-sky-300 hover:bg-sky-50/60' }}">
                                    <input wire:model.live="planFeatureInputs.{{ $plan->id }}" type="checkbox" value="{{ $featureKey }}" class="mt-0.5 h-4 w-4 rounded border-slate-300 text-emerald-500 focus:ring-emerald-400">
                                    <span class="min-w-0">
                                        <span class="block text-sm font-semibold text-slate-800">{{ $featureLabel }}</span>
                                        <span class="mt-1 block text-xs text-slate-500">{{ $featureKey }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </section>
                @endforeach

                @if ($ungroupedFeatures->isNotEmpty())
                    <section class="rounded-3xl border border-dashed border-slate-300 bg-white p-4 shadow-sm 2xl:col-span-2">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-base font-semibold text-slate-900">Key Tambahan</h3>
                                <p class="mt-1 text-sm leading-6 text-slate-500">Item katalog yang belum masuk grup utama.</p>
                            </div>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                                {{ $selectedKeys->intersect($ungroupedFeatures->keys())->count() }}/{{ $ungroupedFeatures->count() }} dipilih
                            </span>
                        </div>

                        <div class="mt-4 grid gap-2 lg:grid-cols-2">
                            @foreach ($ungroupedFeatures as $featureKey => $featureLabel)
                                @php($checked = $selectedKeys->contains($featureKey))
                                <label class="group flex cursor-pointer items-start gap-3 rounded-2xl border px-4 py-3 transition {{ $checked ? 'border-emerald-300 bg-emerald-50/80' : 'border-slate-200 bg-white hover:border-sky-300 hover:bg-sky-50/60' }}">
                                    <input wire:model.live="planFeatureInputs.{{ $plan->id }}" type="checkbox" value="{{ $featureKey }}" class="mt-0.5 h-4 w-4 rounded border-slate-300 text-emerald-500 focus:ring-emerald-400">
                                    <span class="min-w-0">
                                        <span class="block text-sm font-semibold text-slate-800">{{ $featureLabel }}</span>
                                        <span class="mt-1 block text-xs text-slate-500">{{ $featureKey }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">{{ number_format($packageSelectedKeys->count()) }} key paket dipilih untuk {{ $plan->name }}</p>
                        <p class="mt-1 text-xs text-slate-500">Pusat Langganan tetap tersedia sebagai menu sistem, jadi tidak masuk hitungan key paket.</p>
                    </div>
                    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-70" wire:loading.attr="disabled" wire:target="savePlanFeatures">
                        <span wire:loading.remove wire:target="savePlanFeatures">Simpan Mapping Paket</span>
                        <span wire:loading wire:target="savePlanFeatures">Menyimpan Mapping...</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
