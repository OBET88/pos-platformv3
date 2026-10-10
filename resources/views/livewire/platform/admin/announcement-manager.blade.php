<div class="space-y-6">
    <div class="max-w-3xl">
        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-emerald-700">Komunikasi Platform</p>
        <h1 class="mt-2 text-2xl font-semibold text-slate-900">Kelola pengumuman yang tampil ke tim admin.</h1>
        <p class="mt-2 text-sm text-slate-500">Pengumuman aktif dapat muncul sebagai popup setelah login, dan riwayatnya tetap bisa dilihat ulang dari halaman ini.</p>
    </div>

    @if (session('platform_admin_message'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('platform_admin_message') }}
        </div>
    @endif

    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="mb-4">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Broadcast</p>
            <h2 class="mt-2 text-xl font-semibold text-slate-900">Buat Pengumuman Platform</h2>
            <p class="mt-1 text-sm text-slate-500">Pengumuman aktif bisa muncul sebagai modal setelah login dan tetap bisa dilihat ulang dari halaman ini.</p>
        </div>

        <form wire:submit="createAnnouncement" class="space-y-4">
            <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Judul</label>
                <input wire:model.live="form.title" type="text" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none" placeholder="Contoh: Maintenance sistem malam ini">
                @error('form.title')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Isi Pengumuman</label>
                <textarea wire:model.live="form.message" rows="5" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none" placeholder="Tuliskan perubahan fitur, maintenance, atau security notice yang perlu diketahui user."></textarea>
                @error('form.message')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Tone</label>
                    <select wire:model.live="form.tone" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none">
                        <option value="info">Info</option>
                        <option value="feature">Update Fitur</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="security">Security</option>
                    </select>
                    @error('form.tone')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Mulai Tayang</label>
                        <input wire:model.live="form.starts_at" type="datetime-local" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none">
                        @error('form.starts_at')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Selesai Tayang</label>
                        <input wire:model.live="form.ends_at" type="datetime-local" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none">
                        @error('form.ends_at')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap gap-5 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                <label class="inline-flex items-center gap-3">
                    <input wire:model.live="form.is_active" type="checkbox" class="rounded border-slate-300 text-emerald-500 focus:ring-emerald-400">
                    Aktifkan sekarang
                </label>
                <label class="inline-flex items-center gap-3">
                    <input wire:model.live="form.show_as_modal" type="checkbox" class="rounded border-slate-300 text-emerald-500 focus:ring-emerald-400">
                    Tampilkan sebagai popup setelah login
                </label>
            </div>

            <button type="submit" class="rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800" data-ui-submit>
                <span data-ui-submit-label>Simpan Pengumuman</span>
            </button>
        </form>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="mb-4">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Riwayat</p>
            <h3 class="mt-2 text-xl font-semibold text-slate-900">Daftar Pengumuman</h3>
        </div>

        <div class="space-y-4">
            @forelse ($announcements as $announcement)
                <article class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h4 class="text-base font-semibold text-slate-900">{{ $announcement->title }}</h4>
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ match($announcement->tone) {
                                    'maintenance' => 'bg-amber-100 text-amber-700',
                                    'security' => 'bg-rose-100 text-rose-700',
                                    'feature' => 'bg-sky-100 text-sky-700',
                                    default => 'bg-emerald-100 text-emerald-700',
                                } }}">
                                    {{ strtoupper($announcement->tone) }}
                                </span>
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $announcement->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700' }}">
                                    {{ $announcement->is_active ? 'AKTIF' : 'NONAKTIF' }}
                                </span>
                                @if ($announcement->show_as_modal)
                                    <span class="rounded-full bg-violet-100 px-3 py-1 text-xs font-semibold text-violet-700">POPUP LOGIN</span>
                                @endif
                            </div>
                            <p class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-600">{{ $announcement->message }}</p>
                            <div class="mt-3 text-xs text-slate-500">
                                Dibuat oleh {{ $announcement->creator?->email ?? 'system' }} · Mulai {{ optional($announcement->starts_at)->format('d M Y H:i') ?? 'sekarang' }} · Selesai {{ optional($announcement->ends_at)->format('d M Y H:i') ?? 'tanpa batas' }}
                            </div>
                        </div>

                        <button type="button" wire:click="toggleActive({{ $announcement->id }})" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">
                            {{ $announcement->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                        </button>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-sm text-slate-500">
                    Belum ada pengumuman platform. Buat pengumuman pertama untuk update fitur, maintenance, atau security notice.
                </div>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $announcements->links() }}
        </div>
    </section>
</div>
