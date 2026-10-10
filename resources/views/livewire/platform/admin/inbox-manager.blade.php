<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div class="max-w-3xl">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-sky-700">Inbox Lengkap</p>
            <h1 class="mt-2 text-2xl font-semibold text-slate-900">Pusat event tenant lintas status, perpanjangan, dan update admin</h1>
            <p class="mt-2 text-sm text-slate-500">Halaman ini menyimpan event penting secara persisten, jadi tim admin bisa tandai terbaca, filter kategori, dan lanjutkan tindak lanjut tanpa kehilangan history operasional.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('platform.admin.tenants.index') }}" class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-900 transition hover:bg-slate-50">
                Kembali ke Tenant Manager
            </a>
            <button wire:click="markFilteredAsRead" type="button" wire:loading.attr="disabled" wire:target="markFilteredAsRead" class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-70">
                <span wire:loading.remove wire:target="markFilteredAsRead">Tandai Filter Terbaca</span>
                <span wire:loading wire:target="markFilteredAsRead">Memproses Filter...</span>
            </button>
        </div>
    </div>

    @if (session('platform_admin_message'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-4 text-sm text-emerald-800 shadow-sm">
            {{ session('platform_admin_message') }}
        </div>
    @endif

    @if (session('platform_admin_error'))
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-4 text-sm text-rose-800 shadow-sm">
            {{ session('platform_admin_error') }}
        </div>
    @endif

    <div class="grid gap-2 md:grid-cols-2 xl:grid-cols-4 2xl:grid-cols-5">
        <div class="rounded-xl bg-white px-4 py-3 shadow-sm ring-1 ring-slate-200">
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Total Belum Dibaca</p>
            <p class="mt-1 text-3xl font-semibold leading-none text-slate-900">{{ number_format($unreadTotal) }}</p>
        </div>
        <div class="rounded-xl bg-white px-4 py-3 shadow-sm ring-1 ring-slate-200">
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Butuh Perhatian</p>
            <p class="mt-1 text-3xl font-semibold leading-none text-rose-600">{{ number_format($unreadAttention) }}</p>
        </div>
        <div class="rounded-xl bg-white px-4 py-3 shadow-sm ring-1 ring-slate-200">
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Registrasi</p>
            <p class="mt-1 text-3xl font-semibold leading-none text-sky-600">{{ number_format($unreadRegistration) }}</p>
        </div>
        <div class="rounded-xl bg-white px-4 py-3 shadow-sm ring-1 ring-slate-200">
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Perpanjangan</p>
            <p class="mt-1 text-3xl font-semibold leading-none text-emerald-600">{{ number_format($unreadRenewal) }}</p>
        </div>
        <div class="rounded-xl bg-white px-4 py-3 shadow-sm ring-1 ring-slate-200">
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Aktivasi</p>
            <p class="mt-1 text-3xl font-semibold leading-none text-violet-600">{{ number_format($unreadActivation) }}</p>
        </div>
        <div class="rounded-xl bg-white px-4 py-3 shadow-sm ring-1 ring-slate-200">
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Tindak Lanjut Terbuka</p>
            <p class="mt-1 text-3xl font-semibold leading-none text-amber-600">{{ number_format($followUpOpen) }}</p>
        </div>
        <div class="rounded-xl bg-white px-4 py-3 shadow-sm ring-1 ring-slate-200">
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Sedang Dikerjakan</p>
            <p class="mt-1 text-3xl font-semibold leading-none text-sky-600">{{ number_format($followUpInProgress) }}</p>
        </div>
        <div class="rounded-xl bg-white px-4 py-3 shadow-sm ring-1 ring-slate-200">
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Selesai</p>
            <p class="mt-1 text-3xl font-semibold leading-none text-emerald-600">{{ number_format($followUpResolved) }}</p>
        </div>
    </div>

    <div class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <form class="grid gap-3 md:grid-cols-[minmax(220px,1fr)_auto]">
                <input
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    placeholder="Cari judul, pesan, nama tenant, kode, atau email owner"
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-400 focus:outline-none"
                >
            </form>

            <div class="flex flex-wrap gap-2 text-xs font-semibold">
                <button wire:click="$set('categoryFilter', 'all')" type="button" class="rounded-full px-3 py-1 transition {{ $categoryFilter === 'all' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">Semua</button>
                <button wire:click="$set('categoryFilter', 'attention')" type="button" class="rounded-full px-3 py-1 transition {{ $categoryFilter === 'attention' ? 'bg-rose-500 text-white' : 'bg-rose-100 text-rose-700 hover:bg-rose-200' }}">Butuh Perhatian</button>
                <button wire:click="$set('categoryFilter', 'renewal')" type="button" class="rounded-full px-3 py-1 transition {{ $categoryFilter === 'renewal' ? 'bg-emerald-500 text-slate-950' : 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' }}">Perpanjangan</button>
                <button wire:click="$set('categoryFilter', 'registration')" type="button" class="rounded-full px-3 py-1 transition {{ $categoryFilter === 'registration' ? 'bg-sky-500 text-white' : 'bg-sky-100 text-sky-700 hover:bg-sky-200' }}">Registrasi</button>
                <button wire:click="$set('categoryFilter', 'activation')" type="button" class="rounded-full px-3 py-1 transition {{ $categoryFilter === 'activation' ? 'bg-violet-500 text-white' : 'bg-violet-100 text-violet-700 hover:bg-violet-200' }}">Aktivasi</button>
                <button wire:click="$set('categoryFilter', 'admin')" type="button" class="rounded-full px-3 py-1 transition {{ $categoryFilter === 'admin' ? 'bg-slate-700 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">Admin</button>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap gap-2 text-xs font-semibold">
            <button wire:click="$set('readFilter', 'all')" type="button" class="rounded-full px-3 py-1 transition {{ $readFilter === 'all' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">Semua Status Baca</button>
            <button wire:click="$set('readFilter', 'unread')" type="button" class="rounded-full px-3 py-1 transition {{ $readFilter === 'unread' ? 'bg-amber-500 text-slate-950' : 'bg-amber-100 text-amber-700 hover:bg-amber-200' }}">Belum Dibaca</button>
            <button wire:click="$set('readFilter', 'read')" type="button" class="rounded-full px-3 py-1 transition {{ $readFilter === 'read' ? 'bg-emerald-500 text-slate-950' : 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' }}">Sudah Dibaca</button>
        </div>

        <div class="mt-3 flex flex-wrap gap-2 text-xs font-semibold">
            <button wire:click="$set('followUpFilter', 'all')" type="button" class="rounded-full px-3 py-1 transition {{ $followUpFilter === 'all' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">Semua Tindak Lanjut</button>
            <button wire:click="$set('followUpFilter', 'open')" type="button" class="rounded-full px-3 py-1 transition {{ $followUpFilter === 'open' ? 'bg-amber-500 text-slate-950' : 'bg-amber-100 text-amber-700 hover:bg-amber-200' }}">Terbuka</button>
            <button wire:click="$set('followUpFilter', 'in_progress')" type="button" class="rounded-full px-3 py-1 transition {{ $followUpFilter === 'in_progress' ? 'bg-sky-500 text-white' : 'bg-sky-100 text-sky-700 hover:bg-sky-200' }}">Sedang Dikerjakan</button>
            <button wire:click="$set('followUpFilter', 'resolved')" type="button" class="rounded-full px-3 py-1 transition {{ $followUpFilter === 'resolved' ? 'bg-emerald-500 text-slate-950' : 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' }}">Selesai</button>
        </div>

        <div class="mt-5 space-y-3">
            @forelse ($items as $entry)
                @php($item = $entry['model'])
                @php($meta = $entry['meta'])
                @php($isRead = $item->reads->isNotEmpty())
                @php($toneClasses = match($item->tone) {
                    'emerald' => 'bg-emerald-50 ring-emerald-200',
                    'amber' => 'bg-amber-50 ring-amber-200',
                    'rose' => 'bg-rose-50 ring-rose-200',
                    'sky' => 'bg-sky-50 ring-sky-200',
                    'violet' => 'bg-violet-50 ring-violet-200',
                    default => 'bg-slate-50 ring-slate-200',
                })
                @php($badgeClasses = match($item->tone) {
                    'emerald' => 'bg-emerald-100 text-emerald-700',
                    'amber' => 'bg-amber-100 text-amber-700',
                    'rose' => 'bg-rose-100 text-rose-700',
                    'sky' => 'bg-sky-100 text-sky-700',
                    'violet' => 'bg-violet-100 text-violet-700',
                    default => 'bg-slate-200 text-slate-700',
                })
                <div class="rounded-2xl p-4 ring-1 {{ $toneClasses }} {{ $isRead ? 'opacity-80' : '' }}">
                    <div class="flex flex-col gap-3 xl:flex-row xl:items-start xl:justify-between">
                        <div class="space-y-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $badgeClasses }}">{{ $meta['headline'] }}</span>
                                <span class="rounded-full {{ $isRead ? 'bg-white/70 text-slate-600' : 'bg-slate-900 text-white' }} px-3 py-1 text-xs font-semibold">
                                    {{ $isRead ? 'Sudah Dibaca' : 'Belum Dibaca' }}
                                </span>
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ match($item->follow_up_status) { 'in_progress' => 'bg-sky-100 text-sky-700', 'resolved' => 'bg-emerald-100 text-emerald-700', default => 'bg-amber-100 text-amber-700' } }}">
                                    {{ $meta['follow_up_label'] }}
                                </span>
                                @if ($meta['eyebrow'])
                                    <span class="rounded-full bg-white/70 px-3 py-1 text-xs font-semibold text-slate-600">{{ $meta['eyebrow'] }}</span>
                                @endif
                                @if ($item->tenant?->name)
                                    <span class="text-sm font-semibold text-slate-900">{{ $item->tenant->name }}</span>
                                @endif
                            </div>
                            <p class="text-sm text-slate-700">{{ $meta['summary'] }}</p>
                            @if (!empty($meta['highlights']))
                                <div class="flex flex-wrap gap-2 text-xs text-slate-600">
                                    @foreach ($meta['highlights'] as $highlight)
                                        <span class="rounded-full bg-white/80 px-2.5 py-1">{{ $highlight }}</span>
                                    @endforeach
                                </div>
                            @endif
                            <div class="flex flex-wrap gap-2 text-xs text-slate-500">
                                @if ($item->tenant?->code)
                                    <span class="rounded-full bg-white/70 px-2.5 py-1">Kode: {{ $item->tenant->code }}</span>
                                @endif
                                @if ($item->tenant?->owner_email)
                                    <span class="rounded-full bg-white/70 px-2.5 py-1">{{ $item->tenant->owner_email }}</span>
                                @endif
                                @if ($item->followUpUser?->email)
                                    <span class="rounded-full bg-white/70 px-2.5 py-1">PIC: {{ $item->followUpUser->email }}</span>
                                @endif
                                @if ($item->follow_up_at)
                                    <span class="rounded-full bg-white/70 px-2.5 py-1">Tindak lanjut: {{ $item->follow_up_at->format('d M Y H:i') }}</span>
                                @endif
                                <span class="rounded-full bg-white/70 px-2.5 py-1">{{ $item->occurred_at?->format('d M Y H:i') }}</span>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            @if ($item->tenant_id)
                                <button wire:click="openTenantWorkspace({{ $item->id }})" type="button" wire:loading.attr="disabled" wire:target="openTenantWorkspace({{ $item->id }})" class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-70">
                                    <span wire:loading.remove wire:target="openTenantWorkspace({{ $item->id }})">{{ in_array($item->event_key, ['tenant.subscription.upgrade_requested', 'tenant.subscription.renewal_requested'], true) ? 'Buka Langganan' : 'Buka Tenant' }}</span>
                                    <span wire:loading wire:target="openTenantWorkspace({{ $item->id }})">Membuka...</span>
                                </button>
                            @endif
                            @if ($item->follow_up_status !== 'in_progress')
                                <button wire:click="updateFollowUpStatus({{ $item->id }}, 'in_progress')" type="button" wire:loading.attr="disabled" wire:target="updateFollowUpStatus({{ $item->id }}, 'in_progress')" class="rounded-xl bg-sky-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-sky-500 disabled:cursor-not-allowed disabled:opacity-70">
                                    <span wire:loading.remove wire:target="updateFollowUpStatus({{ $item->id }}, 'in_progress')">Kerjakan</span>
                                    <span wire:loading wire:target="updateFollowUpStatus({{ $item->id }}, 'in_progress')">Memproses...</span>
                                </button>
                            @endif
                            @if ($item->follow_up_status !== 'resolved')
                                <button wire:click="updateFollowUpStatus({{ $item->id }}, 'resolved')" type="button" wire:loading.attr="disabled" wire:target="updateFollowUpStatus({{ $item->id }}, 'resolved')" class="rounded-xl bg-emerald-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-emerald-500 disabled:cursor-not-allowed disabled:opacity-70">
                                    <span wire:loading.remove wire:target="updateFollowUpStatus({{ $item->id }}, 'resolved')">Selesai</span>
                                    <span wire:loading wire:target="updateFollowUpStatus({{ $item->id }}, 'resolved')">Memproses...</span>
                                </button>
                            @endif
                            @if ($item->follow_up_status !== 'open')
                                <button wire:click="updateFollowUpStatus({{ $item->id }}, 'open')" type="button" wire:loading.attr="disabled" wire:target="updateFollowUpStatus({{ $item->id }}, 'open')" class="rounded-xl bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-70">
                                    <span wire:loading.remove wire:target="updateFollowUpStatus({{ $item->id }}, 'open')">Buka Kembali</span>
                                    <span wire:loading wire:target="updateFollowUpStatus({{ $item->id }}, 'open')">Memproses...</span>
                                </button>
                            @endif
                            @if ($isRead)
                                <button wire:click="markAsUnread({{ $item->id }})" type="button" wire:loading.attr="disabled" wire:target="markAsUnread({{ $item->id }})" class="rounded-xl bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-70">
                                    <span wire:loading.remove wire:target="markAsUnread({{ $item->id }})">Tandai Belum Dibaca</span>
                                    <span wire:loading wire:target="markAsUnread({{ $item->id }})">Memproses...</span>
                                </button>
                            @else
                                <button wire:click="markAsRead({{ $item->id }})" type="button" wire:loading.attr="disabled" wire:target="markAsRead({{ $item->id }})" class="rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-70">
                                    <span wire:loading.remove wire:target="markAsRead({{ $item->id }})">Tandai Sudah Dibaca</span>
                                    <span wire:loading wire:target="markAsRead({{ $item->id }})">Memproses...</span>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50/70 px-4 py-10 text-center">
                    <div class="mx-auto max-w-md space-y-3">
                        <div class="inline-flex rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-600 ring-1 ring-slate-200">
                            Inbox Admin
                        </div>
                        <p class="text-sm font-medium text-slate-700">Inbox masih kosong untuk filter ini.</p>
                        <p class="text-sm text-slate-500">Coba ubah kategori, state, atau filter tindak lanjut. Event baru akan muncul di sini saat ada registrasi, aktivasi, perpanjangan, tenant berakhir, atau update admin yang perlu tindak lanjut.</p>
                        <div class="flex flex-wrap items-center justify-center gap-2">
                            <button wire:click="$set('categoryFilter', 'all')" type="button" class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">
                                Lihat Semua Kategori
                            </button>
                            <button wire:click="$set('followUpFilter', 'all')" type="button" class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                Reset Tindak Lanjut
                            </button>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        @if ($items->hasPages())
            <div class="mt-5">
                {{ $items->links() }}
            </div>
        @endif
    </div>
</div>
