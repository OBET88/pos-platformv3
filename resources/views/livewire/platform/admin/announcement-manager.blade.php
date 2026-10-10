<div class="space-y-6">
    <div class="max-w-3xl">
        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-emerald-700">Komunikasi Platform</p>
        <h1 class="mt-2 text-2xl font-semibold text-slate-900">Kelola pengumuman yang tampil ke seluruh tenant.</h1>
        <p class="mt-2 text-sm text-slate-500">Pengumuman aktif muncul sebagai popup setelah login di setiap tenant. Isi mendukung teks berformat (bold, list, tautan).</p>
    </div>

    @if (session('platform_admin_message'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('platform_admin_message') }}
        </div>
    @endif

    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="mb-4">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Broadcast</p>
            <h2 class="mt-2 text-xl font-semibold text-slate-900">{{ $editingId ? 'Edit Pengumuman' : 'Buat Pengumuman Platform' }}</h2>
            <p class="mt-1 text-sm text-slate-500">Pengumuman berlaku untuk semua tenant sekaligus.</p>
        </div>

        @if ($canManage)
            <form wire:submit="save" class="space-y-4">
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Judul</label>
                    <input wire:model="form.title" type="text" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none" placeholder="Contoh: Maintenance sistem malam ini">
                    @error('form.title')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div
                    wire:ignore
                    x-data="{
                        editor: null,
                        init() {
                            this.editor = this.$refs.editor;
                            this.editor.addEventListener('trix-change', () => {
                                const html = this.editor.editor
                                    ? this.editor.editor.getDocument().toString()
                                    : this.editor.innerHTML;
                                $wire.set('form.message', html, false);
                            });
                            this.$wire.$on('announcement-editor-sync', (payload) => {
                                const html = payload.html ?? '';
                                if (this.editor.editor) {
                                    this.editor.editor.loadHTML(html);
                                } else {
                                    this.editor.innerHTML = html;
                                }
                            });
                        },
                    }"
                >
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Isi Pengumuman</label>
                    <input id="announcement-trix-input" type="hidden" value="{{ $form['message'] }}">
                    <trix-editor x-ref="editor" input="announcement-trix-input" class="trix-content rounded-xl border border-slate-300 bg-white"></trix-editor>
                    @error('form.message')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Tone</label>
                        <select wire:model="form.tone" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none">
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
                            <input wire:model="form.starts_at" type="datetime-local" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none">
                            @error('form.starts_at')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Selesai Tayang</label>
                            <input wire:model="form.ends_at" type="datetime-local" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none">
                            @error('form.ends_at')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap gap-5 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                    <label class="inline-flex items-center gap-3">
                        <input wire:model="form.is_active" type="checkbox" class="rounded border-slate-300 text-emerald-500 focus:ring-emerald-400">
                        Aktifkan sekarang
                    </label>
                    <label class="inline-flex items-center gap-3">
                        <input wire:model="form.show_as_modal" type="checkbox" class="rounded border-slate-300 text-emerald-500 focus:ring-emerald-400">
                        Tampilkan sebagai popup setelah login
                    </label>
                </div>

                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800" data-ui-submit>
                        <span data-ui-submit-label>{{ $editingId ? 'Simpan Perubahan' : 'Simpan Pengumuman' }}</span>
                    </button>
                    @if ($editingId)
                        <button type="button" wire:click="cancelEdit" class="rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">
                            Batal
                        </button>
                    @endif
                </div>
            </form>
        @else
            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-sm text-slate-500">
                Anda hanya dapat melihat pengumuman. Hubungi pemilik platform untuk hak mengelola.
            </div>
        @endif
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
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $announcement->tenant_reads_count }} sudah baca</span>
                            </div>
                            <div class="mt-3 text-sm leading-6 text-slate-600 trix-content">{!! $announcement->renderedHtml() !!}</div>
                            <div class="mt-3 text-xs text-slate-500">
                                Dibuat oleh {{ $announcement->creator?->email ?? 'system' }} · Mulai {{ optional($announcement->starts_at)->format('d M Y H:i') ?? 'sekarang' }} · Selesai {{ optional($announcement->ends_at)->format('d M Y H:i') ?? 'tanpa batas' }}
                            </div>
                        </div>

                        @if ($canManage)
                            <div class="flex shrink-0 flex-wrap gap-2">
                                <button type="button" wire:click="edit({{ $announcement->id }})" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">
                                    Edit
                                </button>
                                <button type="button" wire:click="toggleActive({{ $announcement->id }})" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">
                                    {{ $announcement->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                                <button type="button" wire:click="resetViews({{ $announcement->id }})" wire:confirm="Tampilkan ulang pengumuman ini ke semua user?" class="rounded-xl border border-sky-300 bg-white px-4 py-2 text-sm font-semibold text-sky-700 transition hover:bg-sky-50">
                                    Tampilkan Ulang
                                </button>
                                <button type="button" wire:click="confirmDelete({{ $announcement->id }})" class="rounded-xl border border-rose-300 bg-white px-4 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-50">
                                    Hapus
                                </button>
                            </div>
                        @endif
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

    @if ($confirmingDelete)
        <div class="fixed inset-0 z-[90] flex items-center justify-center bg-slate-950/70 px-4 py-6">
            <div class="w-full max-w-md rounded-[1.5rem] border border-white/10 bg-white p-6 shadow-[0_28px_90px_rgba(15,23,42,0.34)]">
                <h3 class="text-lg font-semibold text-slate-900">Hapus pengumuman?</h3>
                <p class="mt-2 text-sm text-slate-600">Pengumuman yang dihapus tidak bisa dikembalikan. Semua catatan baca user tenant ikut terhapus.</p>
                <div class="mt-5 flex justify-end gap-3">
                    <button type="button" wire:click="cancelDelete" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">Batal</button>
                    <button type="button" wire:click="deleteAnnouncement" class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-rose-700">Ya, Hapus</button>
                </div>
            </div>
        </div>
    @endif

    @assets
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/trix@2.1.15/dist/trix.css">
        <script src="https://cdn.jsdelivr.net/npm/trix@2.1.15/dist/trix.umd.min.js" defer></script>
        <style>
            .trix-content { line-height: 1.7; }
            .trix-content p:last-child { margin-bottom: 0; }
            trix-editor { min-height: 200px; }
        </style>
    @endassets

    <script>
        document.addEventListener('trix-attachment-add', (event) => {
            event.preventDefault();
        });
    </script>
</div>