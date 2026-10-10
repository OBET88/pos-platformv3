<div class="space-y-6">
    <div class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
            <div class="max-w-4xl">
                <p class="text-xs font-semibold uppercase tracking-[0.32em] text-sky-700">Pusat Paket</p>
                <h1 class="mt-3 text-3xl font-semibold tracking-tight text-slate-900">Kelola daftar paket, harga, limit, dan menu tiap paket.</h1>
                <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-500">Di halaman ini Anda mengatur paket itu sendiri. Untuk isi menu dan tool tiap paket gunakan tombol <strong>Menu Mapping</strong>, dan untuk identitas/harga/limit gunakan <strong>Edit Paket</strong>. Untuk menentukan paket mana yang muncul di form pendaftaran publik, buka <strong>Pengaturan Pendaftaran</strong>.</p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('platform.admin.settings.registration') }}" wire:navigate class="inline-flex items-center rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    Pengaturan Pendaftaran
                </a>
                <button type="button" wire:click="openCreatePlanForm" wire:loading.attr="disabled" wire:target="openCreatePlanForm" class="inline-flex items-center rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-70">
                    <span wire:loading.remove wire:target="openCreatePlanForm">Tambah Paket Baru</span>
                    <span wire:loading wire:target="openCreatePlanForm">Membuka Form...</span>
                </button>
            </div>
        </div>

        <div class="mt-6 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-xs uppercase tracking-wide text-slate-500">Total Paket</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900">{{ number_format($plans->count()) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-xs uppercase tracking-wide text-slate-500">Paket Aktif</p>
                <p class="mt-2 text-2xl font-semibold text-emerald-600">{{ number_format($plans->where('is_active', true)->count()) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-xs uppercase tracking-wide text-slate-500">Tampil di Form Daftar</p>
                <p class="mt-2 text-2xl font-semibold text-sky-600">{{ number_format(count($publicRegistrationPlanCodes)) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-xs uppercase tracking-wide text-slate-500">Katalog Menu</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900">{{ number_format(count($this->packageFeatureCatalog())) }}</p>
                <p class="mt-1 text-xs text-slate-500">Total key menu tersedia</p>
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

    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-sky-700">Prinsip Mapping</p>
                <h2 class="mt-2 text-xl font-semibold text-slate-900">List paket dulu, lalu masuk ke halaman detail sesuai kebutuhan.</h2>
                <p class="mt-2 text-sm leading-6 text-slate-500">`Menu Mapping` untuk atur menu dan tool. `Edit Paket` untuk ubah nama, harga, limit, dan status paket. Preset hanya template awal, owner platform tetap bisa ubah semuanya secara dinamis.</p>
            </div>
            <button type="button" wire:click="$refresh" wire:loading.attr="disabled" wire:target="$refresh" class="inline-flex items-center rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-70">
                <span wire:loading.remove wire:target="$refresh">Refresh Data</span>
                <span wire:loading wire:target="$refresh">Memuat Ulang...</span>
            </button>
        </div>
    </div>

    @if ($showCreatePlanForm)
        <section class="rounded-[32px] border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 bg-slate-50/80 px-5 py-5 md:px-6">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-3xl">
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-emerald-700">Buat Paket Baru</p>
                        <h2 class="mt-2 text-2xl font-semibold text-slate-900">Tambah paket baru dari halaman ini.</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-500">Setelah dibuat, paket akan langsung muncul di daftar dan bisa dibuka lewat halaman mapping atau edit. Jadi owner platform bisa mulai dari template lalu menyesuaikan sesuai strategi bisnis.</p>
                    </div>
                    <button type="button" wire:click="closeCreatePlanForm" wire:loading.attr="disabled" wire:target="closeCreatePlanForm" class="rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-70">
                        <span wire:loading.remove wire:target="closeCreatePlanForm">Tutup Form</span>
                        <span wire:loading wire:target="closeCreatePlanForm">Menutup...</span>
                    </button>
                </div>
            </div>

            <form wire:submit="createPlan" class="grid gap-0 xl:grid-cols-[minmax(0,1.2fr)_360px]">
                <div class="p-5 md:p-6">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="new-plan-code" class="text-sm font-semibold text-slate-900">Kode Paket</label>
                            <input id="new-plan-code" wire:model.live="newPlanForm.code" type="text" placeholder="misalnya: starter_plus" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-400 focus:outline-none">
                            @error('newPlanForm.code')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="new-plan-name" class="text-sm font-semibold text-slate-900">Nama Paket</label>
                            <input id="new-plan-name" wire:model.live="newPlanForm.name" type="text" placeholder="misalnya: Starter Plus" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-400 focus:outline-none">
                            @error('newPlanForm.name')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="new-plan-price" class="text-sm font-semibold text-slate-900">Harga</label>
                            <input id="new-plan-price" wire:model.live="newPlanForm.price" type="number" min="0" step="0.01" placeholder="0" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-400 focus:outline-none">
                            @error('newPlanForm.price')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="new-plan-billing-cycle" class="text-sm font-semibold text-slate-900">Siklus Tagihan</label>
                            <select id="new-plan-billing-cycle" wire:model.live="newPlanForm.billing_cycle" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none">
                                <option value="monthly">Bulanan</option>
                                <option value="quarterly">Triwulanan</option>
                                <option value="yearly">Tahunan</option>
                                <option value="custom">Custom</option>
                            </select>
                            @error('newPlanForm.billing_cycle')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="mt-4 grid gap-4 md:grid-cols-3">
                        <div>
                            <label for="new-plan-max-businesses" class="text-sm font-semibold text-slate-900">Maks. Bisnis</label>
                            <input id="new-plan-max-businesses" wire:model.live="newPlanForm.max_businesses" type="number" min="1" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none">
                            @error('newPlanForm.max_businesses')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="new-plan-max-branches" class="text-sm font-semibold text-slate-900">Maks. Cabang</label>
                            <input id="new-plan-max-branches" wire:model.live="newPlanForm.max_branches" type="number" min="1" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none">
                            @error('newPlanForm.max_branches')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="new-plan-max-users" class="text-sm font-semibold text-slate-900">Maks. User</label>
                            <input id="new-plan-max-users" wire:model.live="newPlanForm.max_users" type="number" min="1" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none">
                            @error('newPlanForm.max_users')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="mt-4 grid gap-4 md:grid-cols-[minmax(0,1fr)_220px]">
                        <div>
                            <label for="new-plan-preset" class="text-sm font-semibold text-slate-900">Template Awal</label>
                            <select id="new-plan-preset" wire:model.live="newPlanForm.preset" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none">
                                @foreach ($planPresets as $presetKey => $preset)
                                    <option value="{{ $presetKey }}">{{ $preset['label'] }} - {{ $preset['description'] }}</option>
                                @endforeach
                            </select>
                            @error('newPlanForm.preset')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div class="flex items-end">
                            <label class="flex w-full items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                                <input wire:model.live="newPlanForm.is_active" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-emerald-500 focus:ring-emerald-400">
                                <span class="font-medium">Langsung aktif</span>
                            </label>
                        </div>
                    </div>
                </div>

                <aside class="border-t border-slate-200 bg-slate-50/70 p-5 xl:border-l xl:border-t-0 xl:p-6">
                    @php($selectedPreset = $planPresets[$newPlanForm['preset'] ?? 'basic'] ?? null)
                    <div class="rounded-3xl bg-white p-4 ring-1 ring-slate-200">
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Pratinjau Template</p>
                        @if ($selectedPreset)
                            <div class="mt-3 flex flex-wrap items-center gap-2">
                                <h3 class="text-lg font-semibold text-slate-900">{{ $selectedPreset['label'] }}</h3>
                                @if (!empty($selectedPreset['badge']))
                                    <span class="rounded-full bg-sky-100 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.2em] text-sky-700">{{ $selectedPreset['badge'] }}</span>
                                @endif
                            </div>
                            <p class="mt-2 text-sm leading-6 text-slate-500">{{ $selectedPreset['description'] }}</p>
                            <div class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-emerald-700">Hint Harga</p>
                                <p class="mt-1 text-sm font-semibold text-emerald-900">{{ $selectedPreset['price_hint'] ?? 'Harga mengikuti strategi platform.' }}</p>
                            </div>
                            <div class="mt-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Cocok Dijual Ke</p>
                                <p class="mt-1 text-sm leading-6 text-slate-700">{{ $selectedPreset['ideal_for'] ?? 'Gunakan sebagai titik awal lalu sesuaikan dengan target tenant.' }}</p>
                            </div>
                            <div class="mt-4 space-y-3">
                                <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-3 py-3">
                                    <span class="text-sm text-slate-600">Kunci awal</span>
                                    <span class="text-sm font-semibold text-slate-900">{{ $this->packageFeatureCount($selectedPreset['features']) }}</span>
                                </div>
                                <div class="grid grid-cols-3 gap-2 text-center">
                                    <div class="rounded-2xl bg-slate-50 px-3 py-3">
                                        <p class="text-[11px] uppercase tracking-wide text-slate-400">Bisnis</p>
                                        <p class="mt-1 text-sm font-semibold text-slate-900">{{ number_format($selectedPreset['limits']['max_businesses'] ?? 1) }}</p>
                                    </div>
                                    <div class="rounded-2xl bg-slate-50 px-3 py-3">
                                        <p class="text-[11px] uppercase tracking-wide text-slate-400">Cabang</p>
                                        <p class="mt-1 text-sm font-semibold text-slate-900">{{ number_format($selectedPreset['limits']['max_branches'] ?? 1) }}</p>
                                    </div>
                                    <div class="rounded-2xl bg-slate-50 px-3 py-3">
                                        <p class="text-[11px] uppercase tracking-wide text-slate-400">User</p>
                                        <p class="mt-1 text-sm font-semibold text-slate-900">{{ number_format($selectedPreset['limits']['max_users'] ?? 3) }}</p>
                                    </div>
                                </div>
                                <p class="text-xs leading-5 text-slate-500">Checklist dan limit default mengikuti plan acuan di DB saat ini, lalu tetap bisa diedit manual sebelum paket disimpan.</p>
                            </div>
                        @endif
                    </div>

                    <button type="submit" class="mt-4 inline-flex w-full items-center justify-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-70" wire:loading.attr="disabled" wire:target="createPlan">
                        <span wire:loading.remove wire:target="createPlan">Buat Paket Baru</span>
                        <span wire:loading wire:target="createPlan">Menyimpan Paket...</span>
                    </button>
                </aside>
            </form>
        </section>
    @endif

    <section class="rounded-[32px] border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-5 py-5 md:px-6">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-sky-700">Daftar Paket</p>
                    <h2 class="mt-2 text-2xl font-semibold text-slate-900">Pilih paket, lihat tool yang dibutuhkan, lalu masuk ke halaman yang tepat.</h2>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ number_format($plans->count()) }} paket</span>
            </div>
        </div>

        <div class="grid gap-4 p-5 md:grid-cols-2 2xl:grid-cols-3">
            @foreach ($plans as $plan)
                @php($currentFeatures = collect($plan->features_json ?? []))
                @php($packageFeatures = $this->packageFeatureKeys($currentFeatures))
                @php($showsOnRegister = in_array($plan->code, $publicRegistrationPlanCodes, true))
                <article class="rounded-3xl border border-slate-200 bg-slate-50/70 p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full bg-slate-900 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-white">{{ strtoupper($plan->code) }}</span>
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $plan->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700' }}">
                                    {{ $plan->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                                @if ($showsOnRegister)
                                    <span class="rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-700">
                                        Tampil di Form Daftar{{ $publicRegistrationDefaultPlanCode === $plan->code ? ' · Default' : '' }}
                                    </span>
                                @else
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-500">
                                        Tidak Tampil di Form Daftar
                                    </span>
                                @endif
                            </div>
                            <h3 class="mt-3 text-xl font-semibold text-slate-900">{{ $plan->name }}</h3>
                            <p class="mt-1 text-sm text-slate-500">Rp{{ number_format((float) $plan->price, 0, ',', '.') }} / {{ $plan->billing_cycle }}</p>
                        </div>
                        <div class="rounded-2xl bg-white px-3 py-2 text-right ring-1 ring-slate-200">
                            <p class="text-[11px] uppercase tracking-wide text-slate-400">Tool Dibutuhkan</p>
                            <p class="mt-1 text-lg font-semibold text-slate-900">{{ number_format($packageFeatures->count()) }}</p>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-3 gap-3 text-sm">
                        <div class="rounded-2xl bg-white px-3 py-3 ring-1 ring-slate-200">
                            <p class="text-[11px] uppercase tracking-wide text-slate-400">Bisnis</p>
                            <p class="mt-1 font-semibold text-slate-900">{{ number_format($plan->max_businesses) }}</p>
                        </div>
                        <div class="rounded-2xl bg-white px-3 py-3 ring-1 ring-slate-200">
                            <p class="text-[11px] uppercase tracking-wide text-slate-400">Cabang</p>
                            <p class="mt-1 font-semibold text-slate-900">{{ number_format($plan->max_branches) }}</p>
                        </div>
                        <div class="rounded-2xl bg-white px-3 py-3 ring-1 ring-slate-200">
                            <p class="text-[11px] uppercase tracking-wide text-slate-400">User</p>
                            <p class="mt-1 font-semibold text-slate-900">{{ number_format($plan->max_users) }}</p>
                        </div>
                    </div>

                    <div class="mt-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Tool / Menu Utama</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @forelse ($packageFeatures->take(6) as $feature)
                                <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-700 ring-1 ring-slate-200">
                                    {{ $this->featureLabel($feature) }}
                                </span>
                            @empty
                                <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Belum ada key aktif</span>
                            @endforelse
                            @if ($packageFeatures->count() > 6)
                                <span class="rounded-full bg-slate-200 px-3 py-1 text-xs font-semibold text-slate-700">
                                    +{{ $packageFeatures->count() - 6 }} lainnya
                                </span>
                            @endif
                        </div>
                        <p class="mt-3 text-xs leading-5 text-slate-500">Pusat Langganan tetap tersedia sebagai menu sistem dan tidak dihitung di total tool paket.</p>
                    </div>

                    <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        <a href="{{ route('platform.admin.plans.mapping', $plan) }}" wire:navigate class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">
                            Menu Mapping
                        </a>
                        <a href="{{ route('platform.admin.plans.edit', $plan) }}" wire:navigate class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                            Edit Paket
                        </a>
                        <button type="button" wire:click="duplicatePlan({{ $plan->id }})" wire:loading.attr="disabled" wire:target="duplicatePlan({{ $plan->id }})" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-70">
                            <span wire:loading.remove wire:target="duplicatePlan({{ $plan->id }})">Duplikasi</span>
                            <span wire:loading wire:target="duplicatePlan({{ $plan->id }})">Menduplikasi...</span>
                        </button>
                    </div>
                </article>
            @endforeach
        </div>
    </section>
</div>
