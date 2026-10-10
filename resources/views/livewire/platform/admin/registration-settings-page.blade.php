<div class="space-y-6">
    <div class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
            <div class="max-w-4xl">
                <p class="text-xs font-semibold uppercase tracking-[0.32em] text-sky-700">Pendaftaran Publik</p>
                <h1 class="mt-3 text-3xl font-semibold tracking-tight text-slate-900">Atur trial dan paket yang tampil di form daftar.</h1>
                <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-500">Semua pengaturan di halaman ini bersifat global untuk tenant baru, jadi tidak lagi bercampur dengan halaman tenant tertentu.</p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('platform.admin.tenants.index') }}" wire:navigate class="inline-flex items-center rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    Kembali ke Tenant Manager
                </a>
                <a href="{{ route('platform.register.create') }}" wire:navigate class="inline-flex items-center rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">
                    Buka Form Publik
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

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.15fr)_minmax(320px,0.85fr)]">
        <section class="space-y-6 rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-sky-700">Strategi Trial</p>
                <h2 class="mt-2 text-2xl font-semibold text-slate-900">Durasi trial default untuk tenant baru</h2>
                <p class="mt-2 text-sm leading-6 text-slate-500">Dipakai saat tenant baru selesai verifikasi email dan langsung dibuat. Promo khusus per tenant tetap bisa diatur dari panel tenant bila diperlukan.</p>
            </div>

            <form wire:submit="updateDefaultTrialDays" class="space-y-4 rounded-3xl border border-slate-200 bg-slate-50/70 p-5">
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Trial default tenant baru</label>
                    <select wire:model.live.number="defaultTrialDays" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none">
                        <option value="3">3 hari</option>
                        <option value="7">7 hari</option>
                        <option value="14">14 hari</option>
                        <option value="21">21 hari</option>
                        <option value="30">30 hari</option>
                        <option value="60">60 hari</option>
                        <option value="90">90 hari</option>
                    </select>
                    @error('defaultTrialDays')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-70" wire:loading.attr="disabled" wire:target="updateDefaultTrialDays">
                    <span wire:loading.remove wire:target="updateDefaultTrialDays">Simpan Default Trial</span>
                    <span wire:loading wire:target="updateDefaultTrialDays">Menyimpan Default...</span>
                </button>
            </form>
        </section>

        <aside class="space-y-4 rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-sky-700">Arah Marketing</p>
                <h2 class="mt-2 text-xl font-semibold text-slate-900">Rekomendasi penggunaan</h2>
                <p class="mt-2 text-sm leading-6 text-slate-500">Jika ingin trial terasa premium, jadikan `plus_plus` sebagai default. Tenant tetap masuk status trial dulu dan upgrade/renewal tetap membaca subscription aktif di database.</p>
            </div>

            <div class="rounded-3xl border border-sky-200 bg-sky-50 p-5 text-sm text-sky-900">
                <p class="font-semibold">Contoh strategi yang kuat</p>
                <p class="mt-2">Tampilkan `basic`, `pro`, dan `plus_plus`, lalu pilih `plus_plus` sebagai default agar pelanggan mencoba fitur paling lengkap dulu.</p>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5 text-sm text-slate-700">
                <p class="font-semibold text-slate-900">Yang tidak berubah</p>
                <p class="mt-2">Flow upgrade dan renewal tetap membaca paket aktif tenant dari subscription di database, bukan dari pengaturan halaman publik ini.</p>
            </div>
        </aside>
    </div>

    <section class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-4xl">
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-sky-700">Paket Form Daftar</p>
                <h2 class="mt-2 text-2xl font-semibold text-slate-900">Tentukan paket yang boleh muncul saat daftar pertama</h2>
                <p class="mt-2 text-sm leading-6 text-slate-500">Paket yang dicentang di sini akan tampil di form publik. Satu paket bisa dijadikan default saat form dibuka.</p>
            </div>
            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $publicRegistrationPreviewPlans->count() }} paket tampil</span>
        </div>

        <form wire:submit="updatePublicRegistrationPlans" class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1.15fr)_minmax(320px,0.85fr)]">
            <div class="space-y-4">
                <div class="grid gap-3">
                    @foreach ($plans as $plan)
                        <label class="flex items-start gap-3 rounded-2xl border border-slate-200 px-4 py-4 text-sm text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">
                            <input type="checkbox" value="{{ $plan->code }}" wire:model.live="publicRegistrationPlanCodes" class="mt-1 rounded border-slate-300 text-emerald-600 focus:ring-emerald-400">
                            <span>
                                <span class="block font-semibold text-slate-900">{{ $plan->name }}</span>
                                <span class="mt-1 block text-xs text-slate-500">{{ strtoupper($plan->code) }} · Rp{{ number_format((float) $plan->price, 0, ',', '.') }} / {{ $plan->billing_cycle }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('publicRegistrationPlanCodes')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
                @error('publicRegistrationPlanCodes.*')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="space-y-4 rounded-3xl border border-slate-200 bg-slate-50/70 p-5">
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Paket default saat form dibuka</label>
                    <select wire:model.live="publicRegistrationDefaultPlanCode" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none">
                        @foreach ($publicRegistrationPreviewPlans as $plan)
                            <option value="{{ $plan->code }}">{{ $plan->name }} · {{ strtoupper($plan->code) }}</option>
                        @endforeach
                    </select>
                    @error('publicRegistrationDefaultPlanCode')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">
                    <p class="font-semibold">Preview perilaku form publik</p>
                    <p class="mt-2">Saat calon tenant membuka form daftar, sistem akan langsung menyorot paket <strong>{{ strtoupper($publicRegistrationDefaultPlanCode ?: '-') }}</strong> selama paket itu masih termasuk dalam daftar pilihan publik.</p>
                </div>

                <button type="submit" class="w-full rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-70" wire:loading.attr="disabled" wire:target="updatePublicRegistrationPlans">
                    <span wire:loading.remove wire:target="updatePublicRegistrationPlans">Simpan Paket Publik</span>
                    <span wire:loading wire:target="updatePublicRegistrationPlans">Menyimpan Paket...</span>
                </button>
            </div>
        </form>
    </section>
</div>
