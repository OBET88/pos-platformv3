<div class="space-y-6" wire:poll.60s.keep-alive>
    @php($statusLabel = fn (string $status) => match ($status) {
        'pending_setup' => 'Perlu Recovery',
        'trial' => 'Trial Berjalan',
        'active' => 'Aktif',
        'suspended' => 'Ditangguhkan',
        'expired' => 'Berakhir',
        default => str($status)->replace('_', ' ')->title()->toString(),
    })
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
            <p class="text-xs uppercase tracking-wide text-slate-500">Total Tenant</p>
            <p class="mt-2 text-2xl font-semibold text-slate-900">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
            <p class="text-xs uppercase tracking-wide text-slate-500">Perlu Recovery</p>
            <p class="mt-2 text-2xl font-semibold text-amber-600">{{ number_format($stats['pending_setup']) }}</p>
        </div>
        <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
            <p class="text-xs uppercase tracking-wide text-slate-500">Pending Verifikasi Email</p>
            <p class="mt-2 text-2xl font-semibold text-amber-500">{{ number_format($stats['pending_email_verification']) }}</p>
            <p class="mt-1 text-xs text-slate-500">Belum jadi tenant sampai email owner diklik</p>
        </div>
        <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
            <p class="text-xs uppercase tracking-wide text-slate-500">Trial</p>
            <p class="mt-2 text-2xl font-semibold text-sky-600">{{ number_format($stats['trial']) }}</p>
        </div>
        <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
            <p class="text-xs uppercase tracking-wide text-slate-500">Aktif</p>
            <p class="mt-2 text-2xl font-semibold text-emerald-600">{{ number_format($stats['active']) }}</p>
        </div>
        <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
            <p class="text-xs uppercase tracking-wide text-slate-500">Baru Aktif</p>
            <p class="mt-2 text-2xl font-semibold text-emerald-500">{{ number_format($stats['new_active']) }}</p>
            <p class="mt-1 text-xs text-slate-500">Aktif atau onboard hari ini</p>
        </div>
        <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
            <p class="text-xs uppercase tracking-wide text-slate-500">Segera Berakhir</p>
            <p class="mt-2 text-2xl font-semibold text-amber-500">{{ number_format($stats['expiring_soon']) }}</p>
            <p class="mt-1 text-xs text-slate-500">Dalam 3 hari ke depan</p>
        </div>
        <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
            <p class="text-xs uppercase tracking-wide text-slate-500">Berakhir</p>
            <p class="mt-2 text-2xl font-semibold text-rose-500">{{ number_format($stats['expired']) }}</p>
            <p class="mt-1 text-xs text-slate-500">Butuh follow-up atau perpanjangan</p>
        </div>
        <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
            <p class="text-xs uppercase tracking-wide text-slate-500">Perpanjangan Terbaru</p>
            <p class="mt-2 text-2xl font-semibold text-emerald-500">{{ number_format($stats['renewed_recent']) }}</p>
            <p class="mt-1 text-xs text-slate-500">Langganan diperbarui hari ini</p>
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

    <div class="rounded-3xl border border-sky-200 bg-gradient-to-r from-sky-50 via-white to-emerald-50 p-5 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="max-w-3xl">
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-sky-700">Pusat Monitoring Tenant</p>
                <h2 class="mt-2 text-xl font-semibold text-slate-900">Pantau tenant, request verifikasi, dan kasus onboarding yang perlu bantuan admin.</h2>
                <p class="mt-2 text-sm text-slate-600">Form publik tetap berjalan terpisah. Halaman ini dipakai saat tim platform perlu follow-up tenant yang sudah masuk atau membantu proses yang tersendat.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('platform.register.create') }}" wire:navigate class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">
                    Form Publik
                </a>
                <a href="{{ route('platform.admin.inbox.index') }}" wire:navigate class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-900 transition hover:bg-slate-50">
                    Inbox Lengkap
                </a>
                <button type="button" wire:click="$refresh" wire:loading.attr="disabled" wire:target="$refresh" class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-900 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-70">
                    <span wire:loading.remove wire:target="$refresh">Refresh</span>
                    <span wire:loading wire:target="$refresh">Memuat...</span>
                </button>
            </div>
        </div>
    </div>

    <div id="pending-verification" class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-slate-200 scroll-mt-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-amber-600">Pending Email Verification</p>
                <h2 class="mt-2 text-xl font-semibold text-slate-900">Request pendaftaran yang belum berubah menjadi tenant</h2>
                <p class="mt-1 text-sm text-slate-500">Selama masih ada di blok ini, tenant belum dibuat karena owner belum klik link verifikasi.</p>
            </div>
            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">
                {{ $pendingRegistrations->count() }} request ditampilkan
            </span>
        </div>

        <div class="mt-5 grid gap-3">
            @forelse ($pendingRegistrations as $pendingRegistration)
                @php($pendingPayload = (array) ($pendingRegistration->payload ?? []))
                @php($pendingState = $this->pendingRegistrationState($pendingRegistration))
                <div class="rounded-2xl border border-amber-200 bg-amber-50/70 p-4">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <div class="space-y-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ match($pendingState) { 'expired' => 'bg-rose-100 text-rose-700', 'expiring_soon' => 'bg-amber-100 text-amber-700', default => 'bg-sky-100 text-sky-700' } }}">Menunggu verifikasi email</span>
                                @if ($pendingState === 'expired')
                                    <span class="rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-700">Berakhir</span>
                                @elseif ($pendingState === 'expiring_soon')
                                    <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Segera Berakhir</span>
                                @endif
                                <span class="text-sm font-semibold text-slate-900">{{ $pendingPayload['company_name'] ?? 'Tenant Baru' }}</span>
                            </div>
                            <p class="text-sm text-slate-700">{{ $pendingPayload['owner_name'] ?? '-' }} · {{ $pendingRegistration->owner_email }}</p>
                            <p class="text-xs text-slate-500">
                                Dikirim terakhir: {{ optional($pendingRegistration->verification_sent_at)->format('d M Y H:i') ?? '-' }}
                                · Kedaluwarsa: {{ optional($pendingRegistration->expires_at)->format('d M Y H:i') ?? '-' }}
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button
                                type="button"
                                wire:click="resendPendingRegistrationVerification({{ $pendingRegistration->id }})"
                                wire:loading.attr="disabled"
                                wire:target="resendPendingRegistrationVerification({{ $pendingRegistration->id }})"
                                class="inline-flex items-center rounded-xl border border-amber-300 bg-white px-4 py-3 text-sm font-semibold text-amber-700 transition hover:bg-amber-100 disabled:cursor-not-allowed disabled:opacity-70"
                            >
                                <span wire:loading.remove wire:target="resendPendingRegistrationVerification({{ $pendingRegistration->id }})">Kirim Ulang Verifikasi</span>
                                <span wire:loading wire:target="resendPendingRegistrationVerification({{ $pendingRegistration->id }})">Mengirim Ulang...</span>
                            </button>
                            <a
                                href="{{ route('platform.register.verify', ['token' => $pendingRegistration->verification_token]) }}"
                                target="_blank"
                                rel="noreferrer"
                                class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800"
                            >
                                Buka Link Verifikasi
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 px-4 py-10 text-center">
                    <p class="text-sm font-medium text-slate-700">Tidak ada request verifikasi email yang masih pending.</p>
                    <p class="mt-1 text-sm text-slate-500">Begitu owner klik link verifikasi, request ini akan hilang dari blok ini dan tenant baru muncul di daftar tenant utama.</p>
                </div>
            @endforelse
        </div>
    </div>

    <div class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Inbox Operasional</p>
                <h2 class="mt-2 text-xl font-semibold text-slate-900">Event tenant terbaru yang perlu dilihat cepat</h2>
                <p class="mt-1 text-sm text-slate-500">Ringkasan tenant baru, expiry, renewal, pembayaran, dan update admin terbaru.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                    {{ $activityInbox->count() }} event terbaru
                </span>
                <a href="{{ route('platform.admin.inbox.index') }}" wire:navigate class="rounded-full border border-slate-300 bg-white px-3 py-1 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                    Inbox Lengkap
                </a>
                <button type="button" wire:click="$set('inboxFilter', 'all')" class="rounded-full px-3 py-1 text-xs font-semibold transition {{ $inboxFilter === 'all' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    Semua
                </button>
                <button type="button" wire:click="$set('inboxFilter', 'attention')" class="rounded-full px-3 py-1 text-xs font-semibold transition {{ $inboxFilter === 'attention' ? 'bg-amber-500 text-slate-950' : 'bg-amber-100 text-amber-700 hover:bg-amber-200' }}">
                    Butuh Perhatian
                    @if ($attentionCount > 0)
                        <span class="ml-1 rounded-full bg-rose-500 px-1.5 py-0.5 text-[10px] font-bold text-white">{{ $attentionCount }}</span>
                    @endif
                </button>
                <button type="button" wire:click="$set('inboxFilter', 'renewal')" class="rounded-full px-3 py-1 text-xs font-semibold transition {{ $inboxFilter === 'renewal' ? 'bg-emerald-500 text-slate-950' : 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' }}">
                    Perpanjangan
                </button>
                <button type="button" wire:click="$set('inboxFilter', 'admin')" class="rounded-full px-3 py-1 text-xs font-semibold transition {{ $inboxFilter === 'admin' ? 'bg-sky-500 text-white' : 'bg-sky-100 text-sky-700 hover:bg-sky-200' }}">
                    Admin Update
                </button>
            </div>
        </div>

        <div class="mt-5 grid gap-3">
            @forelse ($activityInbox as $event)
                @php($toneClasses = match($event['tone']) {
                    'emerald' => 'bg-emerald-50 ring-emerald-200',
                    'amber' => 'bg-amber-50 ring-amber-200',
                    'rose' => 'bg-rose-50 ring-rose-200',
                    'sky' => 'bg-sky-50 ring-sky-200',
                    default => 'bg-slate-50 ring-slate-200',
                })
                @php($badgeClasses = match($event['tone']) {
                    'emerald' => 'bg-emerald-100 text-emerald-700',
                    'amber' => 'bg-amber-100 text-amber-700',
                    'rose' => 'bg-rose-100 text-rose-700',
                    'sky' => 'bg-sky-100 text-sky-700',
                    default => 'bg-slate-200 text-slate-700',
                })
                @php($contextClasses = match($event['focus'] ?? '') {
                    'subscription' => 'bg-emerald-100 text-emerald-700',
                    'billing' => 'bg-violet-100 text-violet-700',
                    'recovery' => 'bg-amber-100 text-amber-700',
                    'profile' => 'bg-sky-100 text-sky-700',
                    'domain' => 'bg-cyan-100 text-cyan-700',
                    'status' => 'bg-indigo-100 text-indigo-700',
                    'audit' => 'bg-slate-200 text-slate-700',
                    default => 'bg-white/80 text-slate-600',
                })
                @php($actionClasses = match($event['focus'] ?? '') {
                    'subscription' => 'border-emerald-300 bg-white text-emerald-700 hover:bg-emerald-50',
                    'billing' => 'border-violet-300 bg-white text-violet-700 hover:bg-violet-50',
                    'recovery' => 'border-amber-300 bg-white text-amber-700 hover:bg-amber-50',
                    'profile' => 'border-sky-300 bg-white text-sky-700 hover:bg-sky-50',
                    'domain' => 'border-cyan-300 bg-white text-cyan-700 hover:bg-cyan-50',
                    'status' => 'border-indigo-300 bg-white text-indigo-700 hover:bg-indigo-50',
                    'audit' => 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50',
                    default => 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50',
                })
                <div class="rounded-2xl p-4 ring-1 {{ $toneClasses }}">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                        <div class="space-y-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $badgeClasses }}">{{ $event['title'] }}</span>
                                @if (!empty($event['context_label']))
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $contextClasses }}">{{ $event['context_label'] }}</span>
                                @endif
                                @if (!empty($event['tenant_name']))
                                    <span class="text-sm font-semibold text-slate-900">{{ $event['tenant_name'] }}</span>
                                @endif
                            </div>
                            <p class="text-sm text-slate-700">{{ $event['message'] }}</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500">
                            @if (!empty($event['tenant_id']))
                                <a href="{{ route('platform.admin.tenants.index', array_filter(['tenant' => $event['tenant_id'], 'focus' => $event['focus'] ?? null])) }}" class="rounded-full border px-3 py-1 font-medium transition {{ $actionClasses }}">
                                    {{ $event['action_label'] ?? 'Buka Tenant' }}
                                </a>
                            @elseif (!empty($event['anchor']))
                                <a href="{{ route('platform.admin.tenants.index') }}{{ $event['anchor'] }}" class="rounded-full border px-3 py-1 font-medium transition {{ $actionClasses }}">
                                    {{ $event['action_label'] ?? 'Lihat Detail' }}
                                </a>
                            @endif
                            <span>Update {{ optional($event['timestamp'])->format('d M Y H:i') }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50/70 px-4 py-10 text-center">
                    <div class="mx-auto max-w-md space-y-3">
                        <div class="inline-flex rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-600 ring-1 ring-slate-200">
                            Inbox Operasional
                        </div>
                        <p class="text-sm font-medium text-slate-700">Inbox operasional masih kosong.</p>
                        <p class="text-sm text-slate-500">Event baru akan muncul di sini saat ada pendaftaran publik, perubahan admin, renewal, expiry, atau proses onboarding yang butuh perhatian tim platform.</p>
                        <div class="flex flex-wrap items-center justify-center gap-2 text-xs text-slate-500">
                            <span class="rounded-full bg-white px-3 py-1 ring-1 ring-slate-200">Belum ada follow-up terbuka</span>
                            <span class="rounded-full bg-white px-3 py-1 ring-1 ring-slate-200">Belum ada alert tenant</span>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.05fr)_minmax(340px,0.95fr)]">
        <section id="tenant-detail" class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-slate-200 scroll-mt-6">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-slate-900">Tenant Manager</h2>
                    <p class="text-sm text-slate-500">Cari tenant, filter status, lalu pilih tenant untuk melihat detail onboarding otomatis, monitoring, dan recovery cadangan.</p>
                </div>
                <div class="grid gap-3 md:grid-cols-2">
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama, kode, atau email owner" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-400 focus:outline-none">
                    <div class="flex gap-3">
                        <select wire:model.live="statusFilter" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none">
                            <option value="">Semua status</option>
                            <option value="pending_setup">Perlu Recovery</option>
                            <option value="trial">Trial Berjalan</option>
                            <option value="active">Aktif</option>
                            <option value="suspended">Ditangguhkan</option>
                            <option value="expired">Berakhir</option>
                        </select>
                        <button type="button" wire:click="resetTenantFilters" wire:loading.attr="disabled" wire:target="resetTenantFilters" class="rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-70">
                            <span wire:loading.remove wire:target="resetTenantFilters">Reset</span>
                            <span wire:loading wire:target="resetTenantFilters">Mereset...</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="mt-4 flex flex-wrap items-center gap-2 text-xs">
                <button type="button" wire:click="$set('lifecycleFilter', 'all')" class="rounded-full px-3 py-1 font-semibold transition {{ $lifecycleFilter === 'all' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    Semua Siklus
                </button>
                <button type="button" wire:click="$set('lifecycleFilter', 'expiring')" class="rounded-full px-3 py-1 font-semibold transition {{ $lifecycleFilter === 'expiring' ? 'bg-amber-500 text-slate-950' : 'bg-amber-100 text-amber-700 hover:bg-amber-200' }}">
                    Segera Berakhir
                </button>
                <button type="button" wire:click="$set('lifecycleFilter', 'expired')" class="rounded-full px-3 py-1 font-semibold transition {{ $lifecycleFilter === 'expired' ? 'bg-rose-500 text-white' : 'bg-rose-100 text-rose-700 hover:bg-rose-200' }}">
                    Berakhir
                </button>
                <button type="button" wire:click="$set('lifecycleFilter', 'renewed')" class="rounded-full px-3 py-1 font-semibold transition {{ $lifecycleFilter === 'renewed' ? 'bg-emerald-500 text-slate-950' : 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' }}">
                    Renewed
                </button>
            </div>

            <div class="mt-4 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                <span class="rounded-full bg-slate-100 px-3 py-1">
                    Menampilkan {{ $tenants->count() }} tenant di halaman ini
                </span>
                <span class="rounded-full bg-slate-100 px-3 py-1">
                    Total hasil: {{ $tenants->total() }}
                </span>
                @if ($search !== '')
                    <span class="rounded-full bg-emerald-100 px-3 py-1 font-medium text-emerald-700">
                        Keyword: {{ $search }}
                    </span>
                @endif
                @if ($statusFilter !== '')
                    <span class="rounded-full bg-sky-100 px-3 py-1 font-medium text-sky-700">
                        Status: {{ $statusLabel($statusFilter) }}
                    </span>
                @endif
                @if ($lifecycleFilter !== 'all')
                    <span class="rounded-full bg-violet-100 px-3 py-1 font-medium text-violet-700">
                        Siklus: {{ str($lifecycleFilter)->replace('_', ' ')->title() }}
                    </span>
                @endif
                @if ($search !== '' || $statusFilter !== '' || $lifecycleFilter !== 'all')
                    <button type="button" wire:click="resetTenantFilters" wire:loading.attr="disabled" wire:target="resetTenantFilters" class="rounded-full border border-slate-300 px-3 py-1 font-medium text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-70">
                        <span wire:loading.remove wire:target="resetTenantFilters">Reset Filter</span>
                        <span wire:loading wire:target="resetTenantFilters">Mereset...</span>
                    </button>
                @endif
            </div>

            <div class="mt-5 overflow-hidden rounded-2xl border border-slate-200">
                <div class="hidden grid-cols-[1.4fr_0.9fr_0.8fr_1fr] bg-slate-50 px-4 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500 md:grid">
                    <span>Tenant</span>
                    <span>Owner</span>
                    <span>Status</span>
                    <span>Plan</span>
                </div>

                <div class="divide-y divide-slate-200">
                    @forelse ($tenants as $tenant)
                        @php($plan = optional($tenant->subscriptions->sortByDesc('id')->first())->plan)
                        <button type="button" wire:click="selectTenant({{ $tenant->id }})" wire:loading.attr="disabled" wire:target="selectTenant({{ $tenant->id }})" class="grid w-full gap-2 px-4 py-4 text-left transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-70 md:grid-cols-[1.4fr_0.9fr_0.8fr_1fr] {{ (string) $tenant->id === $selectedTenantId ? 'bg-emerald-50' : 'bg-white' }}">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="font-semibold text-slate-900">{{ $tenant->name }}</p>
                                    @if ((string) $tenant->id === $selectedTenantId)
                                        <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide text-emerald-700">
                                            Aktif
                                        </span>
                                    @endif
                                    <span wire:loading wire:target="selectTenant({{ $tenant->id }})" class="rounded-full bg-sky-100 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide text-sky-700">
                                        Membuka
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500">{{ $tenant->code }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-slate-800">{{ $tenant->owner_name }}</p>
                                <p class="text-xs text-slate-500">{{ $tenant->owner_email }}</p>
                            </div>
                            <div>
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ match($tenant->status) { 'active' => 'bg-emerald-100 text-emerald-700', 'trial' => 'bg-sky-100 text-sky-700', 'suspended' => 'bg-rose-100 text-rose-700', 'expired' => 'bg-slate-200 text-slate-700', default => 'bg-amber-100 text-amber-700' } }}">
                                    {{ $statusLabel($tenant->status) }}
                                </span>
                            </div>
                            <div>
                                <p class="text-sm text-slate-800">{{ $plan?->name ?? '-' }}</p>
                                <p class="text-xs text-slate-500">{{ optional($tenant->subscriptions->sortByDesc('id')->first())->status ?? '-' }}</p>
                            </div>
                        </button>
                    @empty
                        <div class="bg-slate-50/70 px-4 py-10 text-center">
                            <div class="mx-auto max-w-md space-y-3">
                                <div class="inline-flex rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-600 ring-1 ring-slate-200">
                                    Daftar Tenant
                                </div>
                                <p class="text-sm font-medium text-slate-700">Belum ada tenant yang cocok dengan filter ini.</p>
                                <p class="text-sm text-slate-500">Ubah keyword pencarian, pilih status lain, atau reset filter untuk menampilkan tenant yang tersedia di platform.</p>
                                <div class="flex flex-wrap items-center justify-center gap-2">
                                    @if ($search !== '' || $statusFilter !== '' || $lifecycleFilter !== 'all')
                                        <button type="button" wire:click="resetTenantFilters" wire:loading.attr="disabled" wire:target="resetTenantFilters" class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-70">
                                            <span wire:loading.remove wire:target="resetTenantFilters">Reset Semua Filter</span>
                                            <span wire:loading wire:target="resetTenantFilters">Mereset...</span>
                                        </button>
                                    @endif
                                    <a href="{{ route('platform.register.create') }}" wire:navigate class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                        Buka Form Pendaftaran
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="mt-4">{{ $tenants->links() }}</div>
        </section>

        <section class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            @if ($selectedTenant)
                @php($databaseConfig = $selectedTenant->databaseConfigs->sortByDesc('id')->first())
                @php($latestSubscription = $selectedTenant->subscriptions->sortByDesc('id')->first())
                @php($primaryDomain = $selectedTenant->domains->firstWhere('is_primary', true)?->domain)
                @php($bootstrapLog = $selectedTenant->provisionLogs->firstWhere('step', 'tenant_app_bootstrap'))
                @php($recommendedAction = $this->tenantRecommendedAction($selectedTenant))
                @php($recommendationClasses = match($recommendedAction['tone'] ?? 'slate') {
                    'emerald' => 'border-emerald-200 bg-emerald-50 text-emerald-900',
                    'amber' => 'border-amber-200 bg-amber-50 text-amber-900',
                    'rose' => 'border-rose-200 bg-rose-50 text-rose-900',
                    'sky' => 'border-sky-200 bg-sky-50 text-sky-900',
                    'violet' => 'border-violet-200 bg-violet-50 text-violet-900',
                    'indigo' => 'border-indigo-200 bg-indigo-50 text-indigo-900',
                    default => 'border-slate-200 bg-slate-50 text-slate-900',
                })
                @php($recommendationBadgeClasses = match($recommendedAction['tone'] ?? 'slate') {
                    'emerald' => 'bg-emerald-100 text-emerald-700',
                    'amber' => 'bg-amber-100 text-amber-700',
                    'rose' => 'bg-rose-100 text-rose-700',
                    'sky' => 'bg-sky-100 text-sky-700',
                    'violet' => 'bg-violet-100 text-violet-700',
                    'indigo' => 'bg-indigo-100 text-indigo-700',
                    default => 'bg-slate-200 text-slate-700',
                })
                <div class="space-y-6">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-emerald-600">Tenant Detail</p>
                        <h2 class="mt-2 text-2xl font-semibold text-slate-900">{{ $selectedTenant->name }}</h2>
                        <p class="mt-1 text-sm text-slate-500">{{ $selectedTenant->owner_name }} · {{ $selectedTenant->owner_email }}</p>
                    </div>

                    <div class="rounded-2xl border p-4 {{ $recommendationClasses }}">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <div class="max-w-3xl">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $recommendationBadgeClasses }}">{{ $recommendedAction['label'] }}</span>
                                    <span class="text-xs font-medium text-slate-500">Langkah berikutnya yang disarankan</span>
                                </div>
                                <h3 class="mt-3 text-lg font-semibold text-slate-900">{{ $recommendedAction['headline'] }}</h3>
                                <p class="mt-2 text-sm text-slate-600">{{ $recommendedAction['description'] }}</p>
                            </div>
                            <div class="flex flex-wrap gap-3">
                                @php($primary = $recommendedAction['primary'] ?? null)
                                @if ($primary && $primary['url'])
                                    <a href="{{ $primary['url'] }}" class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">
                                        {{ $primary['label'] }}
                                    </a>
                                @elseif ($primary)
                                    <button type="button" wire:click="setTab('{{ $primary['tab'] }}')" class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">
                                        {{ $primary['label'] }}
                                    </button>
                                @endif
                                @php($secondary = $recommendedAction['secondary'] ?? null)
                                @if ($secondary && $secondary['url'])
                                    <a href="{{ $secondary['url'] }}" class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                        {{ $secondary['label'] }}
                                    </a>
                                @elseif ($secondary)
                                    <button type="button" wire:click="setTab('{{ $secondary['tab'] }}')" class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                        {{ $secondary['label'] }}
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-2">
                        <div class="flex flex-wrap gap-1.5">
                            @php($tabs = [
                                'summary' => 'Ringkasan',
                                'profile' => 'Profil',
                                'recovery' => 'Recovery',
                                'subscription' => 'Langganan',
                                'billing' => 'Penagihan',
                                'access' => 'Akses',
                                'audit' => 'Audit',
                            ])
                            @foreach ($tabs as $tabKey => $tabLabel)
                                <button
                                    type="button"
                                    wire:click="setTab('{{ $tabKey }}')"
                                    class="rounded-xl px-4 py-2 text-sm font-semibold transition {{ $activeTab === $tabKey ? 'bg-slate-900 text-white shadow-sm' : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-100' }}"
                                >
                                    {{ $tabLabel }}
                                </button>
                            @endforeach
                            <a href="{{ route('platform.admin.payments.index', ['q' => $selectedTenant->code ?: $selectedTenant->owner_email]) }}" class="ml-auto hidden rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800 sm:inline-flex">Pusat Pembayaran</a>
                        </div>
                    </div>

                    @php($tenantUrlScheme = (request()->secure() || str_starts_with((string) config('app.url'), 'https://')) ? 'https' : 'http')
                    @php($primaryDomainUrl = filled($primaryDomain) ? $tenantUrlScheme.'://'.$primaryDomain : null)
                    @if ($activeTab === 'summary')
                    <div class="flex flex-wrap gap-3">
                        @if ($primaryDomainUrl)
                            <a href="{{ $primaryDomainUrl }}" target="_blank" rel="noreferrer" class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">
                                Buka Aplikasi Tenant
                            </a>
                            <a href="{{ rtrim($primaryDomainUrl, '/').'/login' }}" target="_blank" rel="noreferrer" class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-900 transition hover:bg-slate-50">
                                Buka Login Tenant
                            </a>
                        @else
                            <span class="inline-flex items-center rounded-xl border border-dashed border-slate-300 px-4 py-3 text-sm text-slate-500">
                                Aplikasi tenant belum bisa dibuka karena domain utama belum siap.
                            </span>
                        @endif
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <h3 class="text-sm font-semibold text-slate-900">Bantuan Login Tenant</h3>
                                <p class="text-sm text-slate-500">Ringkasan cepat akun tenant untuk monitoring login pertama dan bantuan recovery jika onboarding otomatis sempat gagal.</p>
                            </div>
                            <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-700 ring-1 ring-slate-200">
                                Ringkasan bantuan
                            </span>
                        </div>

                        <div class="mt-4 grid gap-3 md:grid-cols-2">
                            <div class="rounded-2xl bg-white px-4 py-4 ring-1 ring-slate-200">
                                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">Email Pemilik</p>
                                <p class="mt-2 break-all text-sm font-medium text-slate-900">{{ $selectedTenant->owner_email ?: 'Belum tersedia' }}</p>
                            </div>
                            <div class="rounded-2xl bg-white px-4 py-4 ring-1 ring-slate-200">
                                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">URL Login</p>
                                <p class="mt-2 break-all text-sm font-medium text-slate-900">{{ $primaryDomainUrl ? rtrim($primaryDomainUrl, '/').'/login' : 'Belum tersedia' }}</p>
                            </div>
                        </div>

                        <p class="mt-4 text-xs text-slate-500">
                            Password awal tenant tidak ditampilkan kembali demi keamanan. Jika perlu reset, lakukan bootstrap ulang dengan password baru atau siapkan flow reset password tenant.
                        </p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <h3 class="text-sm font-semibold text-slate-900">Ringkasan Operasional</h3>
                                <p class="text-sm text-slate-500">Blok ringkasan yang bisa langsung dicopy untuk tim support, monitoring, atau handoff recovery internal.</p>
                            </div>
                            <div class="flex shrink-0 flex-wrap items-center gap-2">
                                <span id="tenant-summary-copied" class="hidden rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                                    Tersalin!
                                </span>
                                <button
                                    type="button"
                                    data-copy-tenant-summary
                                    class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-70"
                                >
                                    Salin Ringkasan
                                </button>
                            </div>
                        </div>

                        <textarea
                            readonly
                            rows="10"
                            id="tenant-operational-summary"
                            class="mt-4 w-full rounded-2xl border border-slate-300 bg-slate-950 px-4 py-4 font-mono text-xs text-slate-100 focus:border-emerald-400 focus:outline-none"
                        >Tenant: {{ $selectedTenant->name }}
Kode: {{ $selectedTenant->code }}
Status: {{ $statusLabel($selectedTenant->status) }}
Paket: {{ $latestSubscription?->plan?->name ?? '-' }}
Status DB: {{ $databaseConfig?->status ?? 'pending' }}
Domain Utama: {{ $primaryDomain ?? 'Belum ada' }}
URL Tenant: {{ $primaryDomainUrl ?? 'Belum tersedia' }}
URL Login Tenant: {{ $primaryDomainUrl ? rtrim($primaryDomainUrl, '/').'/login' : 'Belum tersedia' }}
Nama Pemilik: {{ $selectedTenant->owner_name ?: '-' }}
Email Pemilik: {{ $selectedTenant->owner_email ?: '-' }}
Bootstrap Aplikasi Tenant: {{ ($bootstrapLog?->status === 'completed') ? 'Selesai' : 'Belum selesai' }}</textarea>

                        @script
                        <script>
                            (() => {
                                const button = document.querySelector('[data-copy-tenant-summary]');

                                if (! button || button.dataset.copyBound === '1') {
                                    return;
                                }

                                button.dataset.copyBound = '1';

                                button.addEventListener('click', async () => {
                                    const textarea = document.getElementById('tenant-operational-summary');
                                    const badge = document.getElementById('tenant-summary-copied');

                                    if (! textarea) {
                                        return;
                                    }

                                    const text = textarea.value;
                                    let copied = false;

                                    try {
                                        if (navigator.clipboard && window.isSecureContext) {
                                            await navigator.clipboard.writeText(text);
                                            copied = true;
                                        }
                                    } catch (error) {
                                        copied = false;
                                    }

                                    if (! copied) {
                                        textarea.focus();
                                        textarea.select();

                                        try {
                                            copied = document.execCommand('copy');
                                        } catch (error) {
                                            copied = false;
                                        }

                                        textarea.setSelectionRange(0, 0);
                                        textarea.blur();
                                    }

                                    if (! badge) {
                                        return;
                                    }

                                    badge.textContent = copied ? 'Tersalin!' : 'Gagal menyalin';
                                    badge.classList.remove('hidden');

                                    window.clearTimeout(badge.dataset.hideTimer);
                                    badge.dataset.hideTimer = window.setTimeout(() => {
                                        badge.classList.add('hidden');
                                    }, 2200);
                                });
                            })();
                        </script>
                        @endscript
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-xs uppercase tracking-wide text-slate-500">Kode Tenant</p>
                            <p class="mt-1 font-semibold text-slate-900">{{ $selectedTenant->code }}</p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-xs uppercase tracking-wide text-slate-500">Telepon Owner</p>
                            <p class="mt-1 font-semibold text-slate-900">{{ $selectedTenant->owner_phone ?: '-' }}</p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-xs uppercase tracking-wide text-slate-500">Langganan</p>
                            <p class="mt-1 font-semibold text-slate-900">{{ $latestSubscription?->plan?->name ?? '-' }}</p>
                            <p class="text-xs text-slate-500">{{ $latestSubscription?->status ?? '-' }}</p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-xs uppercase tracking-wide text-slate-500">Kesiapan DB</p>
                            <p class="mt-1 font-semibold text-slate-900">{{ $databaseConfig?->status ?? 'pending' }}</p>
                            <p class="text-xs text-slate-500">{{ $selectedTenant->onboarded_at?->format('d M Y H:i') ?? 'Belum onboarded' }}</p>
                        </div>
                    </div>
                    @endif

                    @if ($activeTab === 'profile')
                    <div id="profile-panel" class="space-y-3 rounded-2xl border border-slate-200 p-4 scroll-mt-6">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <h3 class="text-sm font-semibold text-slate-900">Profil Tenant</h3>
                                <p class="text-sm text-slate-500">Perbarui nama tenant, nama owner, telepon, atau catatan internal tanpa menyentuh proses provisioning yang sudah ada.</p>
                            </div>
                            <span class="text-xs text-slate-500">Aksi admin</span>
                        </div>
                        @if ($focusPanel === 'profile')
                            <div class="rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800">
                                Panel profil sedang difokuskan dari inbox agar admin bisa langsung meninjau atau memperbarui data inti tenant ini.
                            </div>
                        @endif
                        <form wire:submit="updateTenantProfile" class="space-y-4">
                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Nama Tenant</label>
                                    <input wire:model.live="tenantName" type="text" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none">
                                    @error('tenantName')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Nama Owner</label>
                                    <input wire:model.live="ownerName" type="text" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none">
                                    @error('ownerName')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Telepon Owner</label>
                                    <input wire:model.live="ownerPhone" type="text" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none" placeholder="0812xxxxxx">
                                    @error('ownerPhone')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Email Owner</label>
                                    <input type="text" value="{{ $selectedTenant->owner_email }}" readonly class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-500 focus:outline-none">
                                    <p class="mt-2 text-xs text-slate-500">Email owner utama tetap dipertahankan dari alur registrasi saat ini.</p>
                                </div>
                            </div>

                            <div>
                                <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Catatan Internal Tenant</label>
                                <textarea wire:model.live="tenantNotes" rows="4" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none" placeholder="Catatan akun, hasil follow-up, kebutuhan khusus, atau konteks internal lain"></textarea>
                                @error('tenantNotes')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                            </div>

                            <button type="submit" class="rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-70" wire:loading.attr="disabled" wire:target="updateTenantProfile">
                                <span wire:loading.remove wire:target="updateTenantProfile">Simpan Profil Tenant</span>
                                <span wire:loading wire:target="updateTenantProfile">Menyimpan Profil...</span>
                            </button>
                        </form>
                    </div>
                    @endif

                    @if ($activeTab === 'recovery')
                    <div class="grid gap-3 lg:grid-cols-3">
                        <div class="rounded-2xl border px-4 py-4 {{ ($databaseConfig?->status ?? 'pending') === 'provisioned' ? 'border-emerald-200 bg-emerald-50' : 'border-amber-200 bg-amber-50' }}">
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] {{ ($databaseConfig?->status ?? 'pending') === 'provisioned' ? 'text-emerald-700' : 'text-amber-700' }}">
                                Auto Provisioning
                            </p>
                            <p class="mt-2 text-sm font-medium text-slate-900">
                                {{ ($databaseConfig?->status ?? 'pending') === 'provisioned' ? 'Database tenant sudah siap dipakai atau direcovery ulang bila diperlukan.' : 'Tenant ini perlu recovery provisioning database.' }}
                            </p>
                        </div>
                        <div class="rounded-2xl border px-4 py-4 {{ filled($selectedTenant->domains->firstWhere('is_primary', true)?->domain) ? 'border-sky-200 bg-sky-50' : 'border-slate-200 bg-slate-50' }}">
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] {{ filled($selectedTenant->domains->firstWhere('is_primary', true)?->domain) ? 'text-sky-700' : 'text-slate-600' }}">
                                Domain Utama
                            </p>
                            <p class="mt-2 break-all text-sm font-medium text-slate-900">
                                {{ $selectedTenant->domains->firstWhere('is_primary', true)?->domain ?? 'Belum ditetapkan' }}
                            </p>
                        </div>
                        <div class="rounded-2xl border px-4 py-4 {{ $selectedTenant->status === 'active' ? 'border-emerald-200 bg-emerald-50' : ($selectedTenant->status === 'trial' ? 'border-sky-200 bg-sky-50' : 'border-slate-200 bg-slate-50') }}">
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] {{ $selectedTenant->status === 'active' ? 'text-emerald-700' : ($selectedTenant->status === 'trial' ? 'text-sky-700' : 'text-slate-600') }}">
                                Status Saat Ini
                            </p>
                            <p class="mt-2 text-sm font-medium text-slate-900">
                                {{ $statusLabel($selectedTenant->status) }}
                            </p>
                        </div>
                    </div>

                    @php($ownerReady = filled($selectedTenant->owner_email))
                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <h3 class="text-sm font-semibold text-slate-900">Checklist Monitoring</h3>
                                <p class="text-sm text-slate-500">Ringkasan cepat untuk melihat tenant ini sudah siap sampai tahap mana.</p>
                            </div>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                                {{ collect([
                                    ($databaseConfig?->status ?? 'pending') === 'provisioned',
                                    filled($primaryDomain),
                                    $ownerReady,
                                    ($bootstrapLog?->status === 'completed'),
                                ])->filter()->count() }}/4 selesai
                            </span>
                        </div>

                        <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                            <div class="rounded-2xl border px-4 py-4 {{ ($databaseConfig?->status ?? 'pending') === 'provisioned' ? 'border-emerald-200 bg-emerald-50' : 'border-slate-200 bg-slate-50' }}">
                                <p class="text-xs font-semibold uppercase tracking-[0.22em] {{ ($databaseConfig?->status ?? 'pending') === 'provisioned' ? 'text-emerald-700' : 'text-slate-600' }}">
                                    1. Database
                                </p>
                                <p class="mt-2 text-sm font-medium text-slate-900">
                                    {{ ($databaseConfig?->status ?? 'pending') === 'provisioned' ? 'Siap' : 'Belum siap' }}
                                </p>
                                <p class="mt-1 text-xs text-slate-500">{{ $databaseConfig?->db_name ?: 'Belum ada target database' }}</p>
                            </div>

                            <div class="rounded-2xl border px-4 py-4 {{ filled($primaryDomain) ? 'border-emerald-200 bg-emerald-50' : 'border-slate-200 bg-slate-50' }}">
                                <p class="text-xs font-semibold uppercase tracking-[0.22em] {{ filled($primaryDomain) ? 'text-emerald-700' : 'text-slate-600' }}">
                                    2. Domain
                                </p>
                                <p class="mt-2 text-sm font-medium text-slate-900">
                                    {{ filled($primaryDomain) ? 'Sudah ditetapkan' : 'Belum ditetapkan' }}
                                </p>
                                <p class="mt-1 break-all text-xs text-slate-500">{{ $primaryDomain ?: 'Belum ada domain primary' }}</p>
                            </div>

                            <div class="rounded-2xl border px-4 py-4 {{ $ownerReady ? 'border-emerald-200 bg-emerald-50' : 'border-slate-200 bg-slate-50' }}">
                                <p class="text-xs font-semibold uppercase tracking-[0.22em] {{ $ownerReady ? 'text-emerald-700' : 'text-slate-600' }}">
                                    3. Pemilik Tenant
                                </p>
                                <p class="mt-2 text-sm font-medium text-slate-900">
                                    {{ $ownerReady ? 'Data owner tersedia' : 'Data owner belum lengkap' }}
                                </p>
                                <p class="mt-1 text-xs text-slate-500">{{ $selectedTenant->owner_email ?: 'Email owner belum tersedia' }}</p>
                            </div>

                            <div class="rounded-2xl border px-4 py-4 {{ ($bootstrapLog?->status === 'completed') ? 'border-emerald-200 bg-emerald-50' : 'border-slate-200 bg-slate-50' }}">
                                <p class="text-xs font-semibold uppercase tracking-[0.22em] {{ ($bootstrapLog?->status === 'completed') ? 'text-emerald-700' : 'text-slate-600' }}">
                                    4. Aplikasi Tenant
                                </p>
                                <p class="mt-2 text-sm font-medium text-slate-900">
                                    {{ ($bootstrapLog?->status === 'completed') ? 'Bootstrap selesai' : 'Belum dibootstrap' }}
                                </p>
                                <p class="mt-1 text-xs text-slate-500">{{ $bootstrapLog?->created_at?->format('d M Y H:i') ?: 'Belum ada aktivitas bootstrap' }}</p>
                            </div>
                        </div>
                    </div>

                    <div id="recovery-panel" class="space-y-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 scroll-mt-6">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <h3 class="text-sm font-semibold text-slate-900">Recovery Provisioning Tenant</h3>
                                <p class="text-sm text-slate-600">Jalankan hanya sebagai cadangan saat auto onboarding pelanggan sempat gagal atau database tenant perlu diverifikasi ulang.</p>
                            </div>
                            <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">Aksi Recovery</span>
                        </div>
                        @if ($focusPanel === 'recovery')
                            <div class="rounded-2xl border border-amber-200 bg-white/80 px-4 py-3 text-sm text-amber-800">
                                Panel recovery sedang difokuskan dari inbox agar admin bisa langsung menindaklanjuti provisioning atau bootstrap tenant ini.
                            </div>
                        @endif
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <p class="text-sm text-slate-600">
                                @if (($databaseConfig?->status ?? 'pending') === 'provisioned')
                                    Tenant ini sudah punya database yang siap dipakai. Anda tetap bisa menjalankan recovery provisioning untuk verifikasi ulang.
                                @else
                                    Tenant ini belum siap penuh. Jalankan recovery provisioning saat auto onboarding pelanggan perlu dibantu.
                                @endif
                            </p>
                            <div>
                                <button type="button" wire:click="provisionTenant" wire:loading.attr="disabled" wire:target="provisionTenant" class="rounded-xl bg-emerald-500 px-4 py-3 text-sm font-semibold text-slate-950 transition hover:bg-emerald-400 disabled:cursor-not-allowed disabled:opacity-70">
                                    <span wire:loading.remove wire:target="provisionTenant">{{ ($databaseConfig?->status ?? 'pending') === 'provisioned' ? 'Recovery Provisioning' : 'Jalankan Recovery Provisioning' }}</span>
                                    <span wire:loading wire:target="provisionTenant">Memproses Provisioning...</span>
                                </button>
                            </div>
                        </div>
                        <p class="text-xs text-slate-500">
                            Gunakan aksi ini untuk recovery database tenant. Jika database tenant sudah ada, tombol ini berfungsi sebagai verifikasi ulang.
                        </p>
                    </div>

                    <div class="space-y-4 rounded-2xl border border-slate-200 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <h3 class="text-sm font-semibold text-slate-900">Recovery Bootstrap Aplikasi Tenant</h3>
                                <p class="text-sm text-slate-600">Gunakan hanya sebagai cadangan saat auto onboarding pelanggan sempat gagal. Sistem akan memverifikasi database tenant, menjalankan setup tenant app, lalu memastikan owner awal siap login.</p>
                            </div>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">Aksi Bootstrap</span>
                        </div>

                        @if ($bootstrapLog)
                            <div class="rounded-2xl border {{ $bootstrapLog->status === 'completed' ? 'border-emerald-200 bg-emerald-50' : 'border-amber-200 bg-amber-50' }} px-4 py-3 text-sm {{ $bootstrapLog->status === 'completed' ? 'text-emerald-800' : 'text-amber-800' }}">
                                <p class="text-xs font-semibold uppercase tracking-[0.22em] {{ $bootstrapLog->status === 'completed' ? 'text-emerald-700' : 'text-amber-700' }}">
                                    Status Recovery Bootstrap Terakhir
                                </p>
                                <p class="mt-1">{{ $bootstrapLog->message }}</p>
                                <p class="mt-2 text-xs {{ $bootstrapLog->status === 'completed' ? 'text-emerald-700' : 'text-amber-700' }}">
                                    Update terakhir: {{ $bootstrapLog->created_at?->format('d M Y H:i') ?? '-' }}
                                </p>
                            </div>
                        @endif

                        <form wire:submit="bootstrapTenantApp">
                            <div class="mb-4 rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800">
                                Saat tombol ini diklik, sistem akan mengecek ulang provisioning tenant secara otomatis sebelum menjalankan recovery bootstrap tenant app. Jika tenant pernah gagal di tengah jalan, klik ulang tetap aman untuk recovery.
                            </div>
                            <div class="grid gap-3 md:grid-cols-2">
                                <div>
                                    <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Nama Owner</label>
                                    <input wire:model.live="ownerUserName" type="text" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none">
                                    @error('ownerUserName')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Email Owner</label>
                                    <input wire:model.live="ownerUserEmail" type="email" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none">
                                    @error('ownerUserEmail')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Password Awal</label>
                                    <input wire:model="ownerUserPassword" type="password" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none" placeholder="Minimal 8 karakter">
                                    @error('ownerUserPassword')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Role Owner</label>
                                    <select wire:model.live="ownerRole" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none">
                                        <option value="owner">Owner</option>
                                        <option value="administrator">Administrator</option>
                                        <option value="admin">Admin</option>
                                    </select>
                                    <p class="mt-2 text-xs text-slate-500">Disarankan pakai `Administrator` untuk owner awal tenant agar akses setup pertama lebih lengkap.</p>
                                    @error('ownerRole')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Usaha Awal</label>
                                    <input wire:model.live="businessName" type="text" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none">
                                    @error('businessName')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Cabang Awal</label>
                                    <input wire:model.live="branchName" type="text" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none">
                                    @error('branchName')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                                </div>
                            </div>

                            <div class="mt-4 rounded-2xl bg-slate-50 p-4 text-sm text-slate-600">
                                <p class="font-medium text-slate-900">Target tenant DB untuk recovery</p>
                                <p class="mt-1 break-all">{{ $databaseConfig?->db_name ?: 'Database tenant belum diprovision.' }}</p>
                            </div>

                            <button type="submit" class="mt-4 rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-70" wire:loading.attr="disabled" wire:target="bootstrapTenantApp">
                                <span wire:loading.remove wire:target="bootstrapTenantApp">Jalankan Recovery Bootstrap</span>
                                <span wire:loading wire:target="bootstrapTenantApp">Menjalankan Bootstrap...</span>
                            </button>
                        </form>
                    </div>
                    @endif

                    @if ($activeTab === 'subscription')
                    <div class="grid gap-4 lg:grid-cols-2">
                        <div class="space-y-3 rounded-2xl border border-slate-200 p-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-semibold text-slate-900">Setting Pendaftaran Global</h3>
                                <span class="text-xs text-slate-500">Halaman terpisah</span>
                            </div>
                            <div class="rounded-2xl border border-sky-200 bg-sky-50 p-4 text-sm text-sky-900">
                                <p class="font-medium text-sky-950">Panel trial dan paket form daftar sudah dipindahkan.</p>
                                <p class="mt-2">Supaya tidak tercampur dengan detail tenant tertentu, semua pengaturan global pendaftaran publik sekarang dikelola dari halaman khusus.</p>
                            </div>
                            <a href="{{ route('platform.admin.settings.registration') }}" wire:navigate class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">
                                Buka Setting Pendaftaran
                            </a>
                            <p class="text-xs text-slate-500">Gunakan halaman itu untuk mengatur durasi trial default, daftar paket publik, dan paket default yang langsung terpilih saat form daftar dibuka.</p>
                        </div>

                        <div id="subscription-panel" class="space-y-3 rounded-2xl border border-slate-200 p-4 scroll-mt-6">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-semibold text-slate-900">Paket Langganan</h3>
                                <span class="text-xs text-slate-500">Aksi langganan</span>
                            </div>
                            @if ($focusPanel === 'subscription')
                                <div class="rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800">
                                    Panel langganan sedang difokuskan dari inbox agar tim support bisa langsung follow-up request paket atau perpanjangan tenant ini.
                                </div>
                            @endif
                            <form wire:submit="updateSubscriptionPlan" class="space-y-3">
                                <select wire:model.live="selectedPlanId" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none">
                                    <option value="">Pilih paket</option>
                                    @foreach ($plans as $plan)
                                        <option value="{{ $plan->id }}" @selected((string) old('selected_plan_id', $selectedPlanId) === (string) $plan->id)>{{ $plan->name }} · Rp{{ number_format((float) $plan->price, 0, ',', '.') }} / {{ $plan->billing_cycle }}</option>
                                    @endforeach
                                </select>
                                <div>
                                    <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Masa Trial / Promo</label>
                                    <select wire:model.live.number="trialPlanDays" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none">
                                        <option value="7">7 hari</option>
                                        <option value="14">14 hari</option>
                                        <option value="30">30 hari</option>
                                        <option value="60">60 hari</option>
                                        <option value="90">90 hari</option>
                                    </select>
                                    @error('trialPlanDays')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div class="rounded-2xl bg-sky-50 p-4 text-sm text-sky-800 ring-1 ring-sky-200">
                                    <p class="font-medium text-sky-900">Trial promo fleksibel</p>
                                    <p class="mt-1">Kalau tenant berstatus `trial`, paket apa pun yang Anda pilih di sini, termasuk `Enterprise`, akan dipakai sebagai paket promo sampai masa trial berakhir.</p>
                                </div>
                                <button type="submit" class="rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-70" wire:loading.attr="disabled" wire:target="updateSubscriptionPlan">
                                    <span wire:loading.remove wire:target="updateSubscriptionPlan">Simpan Paket</span>
                                    <span wire:loading wire:target="updateSubscriptionPlan">Menyimpan Paket...</span>
                                </button>
                                @error('selectedPlanId')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
                            </form>
                            <p class="text-xs text-slate-500">Perubahan paket akan memperbarui subscription tenant terbaru. Jika tenant masih `trial`, durasi promo mengikuti pilihan masa trial di atas.</p>
                        </div>

                        <div class="space-y-3 rounded-2xl border border-slate-200 p-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-semibold text-slate-900">Renewal Wajib Bayar</h3>
                                <span class="text-xs text-slate-500">Kebijakan perpanjangan</span>
                            </div>
                            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                                <p class="font-medium">Perpanjangan langsung sudah dinonaktifkan.</p>
                                <p class="mt-2">Tenant hanya boleh diperpanjang melalui invoice renewal. Status aktif baru boleh berubah setelah pembayaran masuk dan divalidasi admin.</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-4 text-sm text-slate-600">
                                <p class="font-medium text-slate-900">Status langganan saat ini</p>
                                <p class="mt-1">Status: {{ $latestSubscription?->status ?? '-' }}</p>
                                <p class="mt-1">Berakhir pada: {{ optional($latestSubscription?->expired_at)->format('d M Y H:i') ?? optional($latestSubscription?->trial_ends_at)->format('d M Y H:i') ?? '-' }}</p>
                            </div>
                            <button type="button" wire:click="setTab('billing')" class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">
                                Buka Panel Invoice Renewal
                            </button>
                            <p class="text-xs text-slate-500">Gunakan invoice `renewal`, kirim ke tenant, lalu validasi pembayarannya dari riwayat invoice atau pusat pembayaran.</p>
                        </div>
                    </div>
                    @endif

                    @if ($activeTab === 'billing')
                    <div id="billing-panel" class="grid gap-4 scroll-mt-6 lg:grid-cols-2">
                        <div class="space-y-3 rounded-2xl border border-slate-200 p-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-semibold text-slate-900">Invoice Pembayaran Manual</h3>
                                <span class="text-xs text-slate-500">Aksi penagihan</span>
                            </div>
                            @if ($focusPanel === 'billing')
                                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                                    Panel penagihan sedang difokuskan dari inbox atau pusat pembayaran agar admin bisa langsung follow-up invoice tenant ini.
                                </div>
                            @endif
                            <form wire:submit="createManualPayment" class="space-y-3">
                                <div>
                                    <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Jenis Invoice</label>
                                    <select wire:model.live="paymentType" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none">
                                        <option value="renewal">Perpanjangan</option>
                                        <option value="new_subscription">Langganan Baru</option>
                                        <option value="upgrade">Upgrade Paket</option>
                                        <option value="downgrade">Downgrade Paket</option>
                                    </select>
                                    @error('paymentType')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Siklus Billing</label>
                                    <select wire:model.live="paymentBillingCycle" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none">
                                        <option value="monthly">Bulanan</option>
                                        <option value="quarterly">3 Bulan</option>
                                        <option value="yearly">Tahunan</option>
                                        <option value="custom">Custom</option>
                                    </select>
                                    @error('paymentBillingCycle')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div>
                                        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Jatuh Tempo</label>
                                        <select wire:model.live.number="paymentDueDays" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none">
                                            <option value="1">1 hari</option>
                                            <option value="3">3 hari</option>
                                            <option value="7">7 hari</option>
                                            <option value="14">14 hari</option>
                                        </select>
                                        @error('paymentDueDays')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                                    </div>
                                    <div>
                                        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Nominal</label>
                                        <input wire:model.live="paymentAmount" type="text" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none" placeholder="Contoh: 50000">
                                        @error('paymentAmount')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                                    </div>
                                </div>
                                <div>
                                    <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Catatan Invoice</label>
                                    <textarea wire:model.live="paymentNotes" rows="3" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none" placeholder="Contoh: transfer ke BCA, valid setelah bukti bayar masuk"></textarea>
                                    @error('paymentNotes')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div class="rounded-2xl bg-slate-50 p-4 text-sm text-slate-600">
                                    <p class="font-medium text-slate-900">Catatan alur saat ini</p>
                                    <p class="mt-1">Invoice dibuat dengan metode `manual_transfer`. Untuk `upgrade`, nominal akan dihitung otomatis dari selisih paket lama ke paket baru pada siklus billing yang dipilih. Setelah pembayaran diterima dan divalidasi admin, klik validasi di panel riwayat invoice agar subscription tenant aktif otomatis.</p>
                                </div>
                                <button type="submit" class="rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-70" wire:loading.attr="disabled" wire:target="createManualPayment">
                                    <span wire:loading.remove wire:target="createManualPayment">Buat Invoice Manual</span>
                                    <span wire:loading wire:target="createManualPayment">Membuat Invoice...</span>
                                </button>
                            </form>
                        </div>

                        <div class="space-y-3 rounded-2xl border border-slate-200 p-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-semibold text-slate-900">Riwayat Invoice</h3>
                                <span class="text-xs text-slate-500">Aksi validasi</span>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" wire:click="$set('paymentStatusFilter', 'all')" class="rounded-full px-3 py-1 text-xs font-semibold transition {{ $paymentStatusFilter === 'all' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                                    Semua
                                </button>
                                <button type="button" wire:click="$set('paymentStatusFilter', 'pending_payment')" class="rounded-full px-3 py-1 text-xs font-semibold transition {{ $paymentStatusFilter === 'pending_payment' ? 'bg-sky-500 text-white' : 'bg-sky-100 text-sky-700 hover:bg-sky-200' }}">
                                    Menunggu Pembayaran
                                </button>
                                <button type="button" wire:click="$set('paymentStatusFilter', 'waiting_validation')" class="rounded-full px-3 py-1 text-xs font-semibold transition {{ $paymentStatusFilter === 'waiting_validation' ? 'bg-amber-500 text-white' : 'bg-amber-100 text-amber-700 hover:bg-amber-200' }}">
                                    Menunggu Validasi
                                </button>
                                <button type="button" wire:click="$set('paymentStatusFilter', 'paid')" class="rounded-full px-3 py-1 text-xs font-semibold transition {{ $paymentStatusFilter === 'paid' ? 'bg-emerald-500 text-slate-950' : 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' }}">
                                    Lunas
                                </button>
                            </div>
                            <div class="space-y-3">
                                @forelse ($this->filteredPaymentTransactions($selectedTenant) as $payment)
                                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                            <div>
                                                <p class="text-sm font-semibold text-slate-900">{{ $payment->invoice_number }}</p>
                                                <p class="mt-1 text-xs text-slate-500">
                                                    {{ ucfirst(str_replace('_', ' ', $payment->type)) }} · {{ $payment->plan?->name ?? '-' }} · Rp{{ number_format((float) $payment->amount_total, 0, ',', '.') }}
                                                </p>
                                                <p class="mt-1 text-xs text-slate-500">
                                                    Status: {{ $payment->status }} · Due: {{ optional($payment->due_at)->format('d M Y H:i') ?? '-' }}
                                                </p>
                                                @if ($payment->paid_at)
                                                    <p class="mt-1 text-xs text-emerald-700">Lunas pada: {{ optional($payment->paid_at)->format('d M Y H:i') }}</p>
                                                @endif
                                            </div>
                                            @if (!in_array($payment->status, ['paid', 'cancelled', 'refunded', 'expired'], true))
                                                <button
                                                    type="button"
                                                    wire:click="approvePayment({{ $payment->id }})"
                                                    class="rounded-xl bg-emerald-500 px-4 py-2.5 text-sm font-semibold text-slate-950 transition hover:bg-emerald-400 disabled:cursor-not-allowed disabled:opacity-70"
                                                    wire:loading.attr="disabled"
                                                    wire:target="approvePayment({{ $payment->id }})"
                                                >
                                                    <span wire:loading.remove wire:target="approvePayment({{ $payment->id }})">Validasi Pembayaran</span>
                                                    <span wire:loading wire:target="approvePayment({{ $payment->id }})">Memvalidasi...</span>
                                                </button>
                                            @else
                                                <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                                                    {{ strtoupper($payment->status) }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="mt-3 flex flex-wrap gap-2">
                                            <a href="{{ route('platform.payment.pdf', ['invoiceNumber' => $payment->invoice_number]) }}" target="_blank" rel="noopener" class="inline-flex rounded-full bg-sky-100 px-3 py-1.5 text-xs font-semibold text-sky-700 transition hover:bg-sky-200">
                                                Buka Halaman Invoice
                                            </a>
                                            @if ($payment->proofs->isNotEmpty())
                                                <span class="inline-flex rounded-full bg-amber-100 px-3 py-1.5 text-xs font-semibold text-amber-700">
                                                    {{ $payment->proofs->count() }} bukti bayar
                                                </span>
                                            @endif
                                        </div>
                                        @if ($payment->notes)
                                            <p class="mt-3 text-xs leading-5 text-slate-500">{{ $payment->notes }}</p>
                                        @endif
                                    </div>
                                @empty
                                    <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-sm text-slate-500">
                                        Belum ada invoice pembayaran manual untuk tenant ini.
                                    </div>
                                @endforelse
                            </div>
                            <p class="text-xs text-slate-500">Validasi akan menandai invoice `paid`, mengaktifkan atau memperpanjang subscription tenant, lalu mencatat audit dan inbox event secara otomatis.</p>
                        </div>
                    </div>
                    @endif

                    @if ($activeTab === 'access')
                    <div id="domain-panel" class="grid gap-4 scroll-mt-6 lg:grid-cols-2">
                        <div class="space-y-3 rounded-2xl border border-slate-200 p-4">
                            <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-slate-900">Domain Utama</h3>
                                <span class="text-xs text-slate-500">Aksi domain</span>
                            </div>
                            @if ($focusPanel === 'domain')
                                <div class="rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800">
                                    Panel domain sedang difokuskan dari inbox agar admin bisa langsung memperbaiki akses domain tenant ini.
                                </div>
                            @endif
                            <form wire:submit="updatePrimaryDomain" class="space-y-3">
                                <input wire:model.live="primaryDomain" type="text" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-400 focus:outline-none" placeholder="contoh: tenant.domainanda.com">
                                <button type="submit" class="rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-70" wire:loading.attr="disabled" wire:target="updatePrimaryDomain">
                                    <span wire:loading.remove wire:target="updatePrimaryDomain">Simpan Domain</span>
                                    <span wire:loading wire:target="updatePrimaryDomain">Menyimpan Domain...</span>
                                </button>
                                @error('primaryDomain')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
                            </form>
                            <p class="text-xs text-slate-500">Domain primary dipakai tenant app untuk resolusi koneksi database tenant yang aktif.</p>
                        </div>
                    </div>

                    <div id="status-panel" class="space-y-3 rounded-2xl border border-slate-200 p-4 scroll-mt-6">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-slate-900">Update Status</h3>
                            <span class="text-xs text-slate-500">Aksi status</span>
                        </div>
                        @if ($focusPanel === 'status')
                            <div class="rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800">
                                Panel status sedang difokuskan dari inbox agar admin bisa langsung meninjau lifecycle tenant ini.
                            </div>
                        @endif
                        <form wire:submit="updateTenantStatus" class="flex flex-col gap-3 sm:flex-row">
                            <select wire:model.live="statusUpdate" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none">
                                <option value="pending_setup">Perlu Recovery</option>
                                <option value="trial">Trial Berjalan</option>
                                <option value="active">Aktif</option>
                                <option value="suspended">Ditangguhkan</option>
                                <option value="expired">Berakhir</option>
                            </select>
                            <button type="submit" class="rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-70" wire:loading.attr="disabled" wire:target="updateTenantStatus">
                                <span wire:loading.remove wire:target="updateTenantStatus">Simpan Status</span>
                                <span wire:loading wire:target="updateTenantStatus">Menyimpan Status...</span>
                            </button>
                        </form>
                        @error('statusUpdate')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
                        <p class="text-xs text-slate-500">Gunakan status `active` setelah tenant siap dipakai operasional. `trial` cocok untuk tenant yang sudah onboard tetapi masih masa percobaan.</p>
                    </div>
                    @endif

                    @if ($activeTab === 'audit')
                    <div class="space-y-3 rounded-2xl border border-slate-200 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <h3 class="text-sm font-semibold text-slate-900">Catatan Monitoring</h3>
                            <span class="text-xs text-slate-500">Catatan internal</span>
                        </div>
                        <form wire:submit="addProvisionNote" class="space-y-3">
                            <textarea wire:model.live="provisionNote" rows="4" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none" placeholder="Tulis catatan monitoring, hasil follow-up, atau blocker recovery tenant ini"></textarea>
                            <button type="submit" class="rounded-xl bg-emerald-500 px-4 py-3 text-sm font-semibold text-slate-950 transition hover:bg-emerald-400 disabled:cursor-not-allowed disabled:opacity-70" wire:loading.attr="disabled" wire:target="addProvisionNote">
                                <span wire:loading.remove wire:target="addProvisionNote">Tambah Catatan</span>
                                <span wire:loading wire:target="addProvisionNote">Menyimpan Catatan...</span>
                            </button>
                            @error('provisionNote')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
                        </form>
                    </div>

                    <div id="audit-panel" class="grid gap-4 scroll-mt-6 md:grid-cols-2">
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <h3 class="text-sm font-semibold text-slate-900">Domain</h3>
                            <div class="mt-3 space-y-2 text-sm text-slate-600">
                                @forelse ($selectedTenant->domains as $domain)
                                    <div class="rounded-xl bg-white px-3 py-2 ring-1 ring-slate-200">
                                        <p class="font-medium text-slate-900">{{ $domain->domain }}</p>
                                        <p class="text-xs text-slate-500">{{ $domain->type }}{{ $domain->is_primary ? ' · primary' : '' }}</p>
                                    </div>
                                @empty
                                    <p>Belum ada domain tenant.</p>
                                @endforelse
                            </div>
                        </div>

                        <div class="rounded-2xl bg-slate-50 p-4">
                            <h3 class="text-sm font-semibold text-slate-900">Konfigurasi Database</h3>
                            <div class="mt-3 space-y-2 text-sm text-slate-600">
                                @forelse ($selectedTenant->databaseConfigs as $config)
                                    <div class="rounded-xl bg-white px-3 py-2 ring-1 ring-slate-200">
                                        <p class="font-medium text-slate-900">{{ $config->connection_name ?: '-' }}</p>
                                        <p class="text-xs text-slate-500 break-all">{{ $config->db_host ?: '-' }}{{ $config->db_port ? ':'.$config->db_port : '' }} · {{ $config->db_name ?: '-' }}</p>
                                        <p class="text-xs text-slate-500">Status: {{ $config->status ?: '-' }}</p>
                                    </div>
                                @empty
                                    <p>Belum ada konfigurasi database tenant.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl bg-slate-50 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <h3 class="text-sm font-semibold text-slate-900">Riwayat Audit</h3>
                            <p class="text-xs text-slate-500">Jejak perubahan admin tenant</p>
                        </div>
                        @if ($focusPanel === 'audit')
                            <div class="mt-3 rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800">
                                Panel audit sedang difokuskan dari inbox agar admin bisa langsung melihat catatan dan jejak perubahan tenant ini.
                            </div>
                        @endif
                        <div class="mt-3 space-y-3">
                            @forelse ($selectedTenant->audits as $audit)
                                <div class="rounded-2xl bg-white p-4 ring-1 ring-slate-200">
                                    <div class="flex flex-wrap items-center justify-between gap-3">
                                        <div>
                                            <p class="font-medium text-slate-900">{{ $audit->summary }}</p>
                                            <p class="mt-1 text-xs text-slate-400">{{ $audit->created_at?->format('d M Y H:i') }}</p>
                                        </div>
                                        <span class="rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-700">
                                            {{ str($audit->action)->replace('.', ' ')->title() }}
                                        </span>
                                    </div>
                                    <div class="mt-3 flex flex-wrap gap-2 text-xs text-slate-500">
                                        @if ($audit->actor?->email || ($audit->metadata['actor_email'] ?? null))
                                            <span class="rounded-full bg-slate-100 px-2.5 py-1">Actor: {{ $audit->actor?->email ?? $audit->metadata['actor_email'] }}</span>
                                        @endif
                                    </div>
                                    @if (!empty($audit->changes))
                                        <details class="mt-3">
                                            <summary class="cursor-pointer text-xs font-medium text-slate-600">Lihat perubahan</summary>
                                            <pre class="mt-3 overflow-x-auto rounded-xl bg-slate-950 p-3 text-xs text-slate-200">{{ json_encode($audit->changes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                                        </details>
                                    @endif
                                </div>
                            @empty
                                <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-6 text-sm text-slate-500">
                                    Belum ada audit history untuk tenant ini. Aktivitas admin seperti update status, approval pembayaran, atau perubahan profil akan tercatat di sini.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="rounded-2xl bg-slate-50 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <h3 class="text-sm font-semibold text-slate-900">Log Monitoring & Recovery</h3>
                            <p class="text-xs text-slate-500">Urutan terbaru tampil paling atas</p>
                        </div>
                        <div class="mt-3 space-y-3">
                            @forelse ($selectedTenant->provisionLogs as $log)
                                @php($isLatestLog = $loop->first)
                                @php($statusBadgeClass = match($log->status) {
                                    'completed', 'done' => 'bg-emerald-100 text-emerald-700',
                                    'pending', 'info' => 'bg-amber-100 text-amber-700',
                                    'failed' => 'bg-rose-100 text-rose-700',
                                    default => 'bg-slate-200 text-slate-700',
                                })
                                <div class="rounded-2xl bg-white p-4 ring-1 {{ $isLatestLog ? 'ring-emerald-300' : 'ring-slate-200' }}">
                                    <div class="flex flex-wrap items-center justify-between gap-3">
                                        <div class="space-y-1">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <p class="font-medium text-slate-900">{{ str($log->step)->replace('-', ' ')->replace('_', ' ')->title() }}</p>
                                                @if ($isLatestLog)
                                                    <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide text-emerald-700">
                                                        Terbaru
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="text-xs text-slate-400">{{ $log->created_at?->format('d M Y H:i') }}</p>
                                        </div>
                                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusBadgeClass }}">{{ str($log->status)->replace('_', ' ')->title() }}</span>
                                    </div>
                                    <p class="mt-2 text-sm text-slate-600">{{ $log->message }}</p>
                                    @if (!empty($log->payload))
                                        <div class="mt-3 flex flex-wrap gap-2 text-xs text-slate-500">
                                            @if (!empty($log->payload['actor_email']))
                                                <span class="rounded-full bg-slate-100 px-2.5 py-1">Actor: {{ $log->payload['actor_email'] }}</span>
                                            @endif
                                            @if (!empty($log->payload['driver']))
                                                <span class="rounded-full bg-slate-100 px-2.5 py-1">Driver: {{ $log->payload['driver'] }}</span>
                                            @endif
                                            @if (!empty($log->payload['database']))
                                                <span class="rounded-full bg-slate-100 px-2.5 py-1">DB: {{ $log->payload['database'] }}</span>
                                            @endif
                                        </div>
                                    @endif
                                    @if (($log->payload['output'] ?? null) || ($log->payload['error_output'] ?? null))
                                        <details class="mt-3">
                                            <summary class="cursor-pointer text-xs font-medium text-slate-600">Lihat output teknis</summary>
                                            <pre class="mt-3 overflow-x-auto rounded-xl bg-slate-950 p-3 text-xs text-slate-200">{{ trim(($log->payload['output'] ?? '')."\n".($log->payload['error_output'] ?? '')) }}</pre>
                                        </details>
                                    @endif
                                </div>
                            @empty
                                <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-6 text-sm text-slate-500">
                                    Belum ada log monitoring atau recovery untuk tenant ini. Saat provisioning, bootstrap, atau recovery dijalankan, riwayat teknisnya akan muncul di panel ini.
                                </div>
                            @endforelse
                        </div>
                    </div>
                    @endif
                </div>
            @else
                <div class="flex h-full min-h-[320px] items-center justify-center rounded-3xl border border-dashed border-slate-300 bg-slate-50 px-6 text-center">
                    <div class="max-w-md space-y-3">
                        <div class="inline-flex rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-600 ring-1 ring-slate-200">
                            Tenant Detail
                        </div>
                        <p class="text-sm font-medium text-slate-700">Belum ada tenant yang dipilih.</p>
                        <p class="text-sm text-slate-500">Pilih tenant dari panel kiri untuk membuka detail langganan, domain, monitoring onboarding otomatis, audit admin, dan log recovery.</p>
                        <p class="text-xs text-slate-400">Tip: gunakan pencarian atau filter siklus untuk menemukan tenant yang perlu follow-up lebih cepat.</p>
                    </div>
                </div>
            @endif
        </section>
    </div>
</div>
