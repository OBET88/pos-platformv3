<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div class="max-w-3xl">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-emerald-700">Pusat Pembayaran</p>
            <h1 class="mt-2 text-2xl font-semibold text-slate-900">Pusat validasi pembayaran lintas tenant</h1>
            <p class="mt-2 text-sm text-slate-500">Halaman ini mengumpulkan invoice dari semua tenant, terutama yang sudah upload bukti bayar dan menunggu validasi admin.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('platform.admin.tenants.index') }}" class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-900 transition hover:bg-slate-50">
                Kembali ke Tenant Manager
            </a>
        </div>
    </div>

    <div class="rounded-3xl border border-emerald-200 bg-gradient-to-r from-emerald-50 via-white to-sky-50 p-5 shadow-sm">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div class="max-w-3xl">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-emerald-700">Alur Kerja Cepat</p>
                <h2 class="mt-2 text-lg font-semibold text-slate-900">Tim finance dan support bisa validasi pembayaran tanpa kehilangan konteks tenant.</h2>
                <div class="mt-3 flex flex-wrap gap-2 text-xs text-slate-600">
                    <span class="rounded-full bg-white px-3 py-1 ring-1 ring-emerald-200">1. Cek bukti bayar dan invoice</span>
                    <span class="rounded-full bg-white px-3 py-1 ring-1 ring-emerald-200">2. Validasi atau minta upload ulang</span>
                    <span class="rounded-full bg-white px-3 py-1 ring-1 ring-emerald-200">3. Lanjut ke panel tenant bila perlu ubah paket atau perpanjangan</span>
                </div>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('platform.admin.inbox.index', ['filter' => 'renewal', 'state' => 'unread']) }}" class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-900 transition hover:bg-slate-50">
                    Buka Inbox Renewal
                </a>
                <a href="{{ route('platform.admin.tenants.index', ['focus' => 'billing']) }}#billing-panel" class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">
                    Buka Panel Penagihan Tenant
                </a>
            </div>
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

    <div class="grid gap-2 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl bg-white px-4 py-3 shadow-sm ring-1 ring-slate-200">
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Menunggu Validasi</p>
            <p class="mt-1 text-3xl font-semibold leading-none text-amber-600">{{ number_format($stats['waiting_validation']) }}</p>
        </div>
        <div class="rounded-xl bg-white px-4 py-3 shadow-sm ring-1 ring-slate-200">
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Menunggu Pembayaran</p>
            <p class="mt-1 text-3xl font-semibold leading-none text-sky-600">{{ number_format($stats['pending_payment']) }}</p>
        </div>
        <div class="rounded-xl bg-white px-4 py-3 shadow-sm ring-1 ring-slate-200">
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Lunas Hari Ini</p>
            <p class="mt-1 text-3xl font-semibold leading-none text-emerald-600">{{ number_format($stats['paid_today']) }}</p>
        </div>
        <div class="rounded-xl bg-white px-4 py-3 shadow-sm ring-1 ring-slate-200">
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Manual Transfer</p>
            <p class="mt-1 text-3xl font-semibold leading-none text-violet-600">{{ number_format($stats['manual_transfer']) }}</p>
        </div>
    </div>

    <div class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Cari invoice, tenant, kode tenant, email owner, atau provider reference"
                class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-400 focus:outline-none lg:max-w-xl"
            >
            <div class="flex flex-wrap gap-2 text-xs font-semibold">
                <button wire:click="$set('statusFilter', 'all')" type="button" class="rounded-full px-3 py-1 transition {{ $statusFilter === 'all' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">Semua</button>
                <button wire:click="$set('statusFilter', 'waiting_validation')" type="button" class="rounded-full px-3 py-1 transition {{ $statusFilter === 'waiting_validation' ? 'bg-amber-500 text-slate-950' : 'bg-amber-100 text-amber-700 hover:bg-amber-200' }}">Menunggu Validasi</button>
                <button wire:click="$set('statusFilter', 'pending_payment')" type="button" class="rounded-full px-3 py-1 transition {{ $statusFilter === 'pending_payment' ? 'bg-sky-500 text-white' : 'bg-sky-100 text-sky-700 hover:bg-sky-200' }}">Menunggu Pembayaran</button>
                <button wire:click="$set('statusFilter', 'paid')" type="button" class="rounded-full px-3 py-1 transition {{ $statusFilter === 'paid' ? 'bg-emerald-500 text-slate-950' : 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' }}">Lunas</button>
            </div>
        </div>

        <div class="mt-3 flex flex-wrap gap-2 text-xs font-semibold">
            <button wire:click="$set('methodFilter', 'all')" type="button" class="rounded-full px-3 py-1 transition {{ $methodFilter === 'all' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">Semua Metode</button>
            <button wire:click="$set('methodFilter', 'manual_transfer')" type="button" class="rounded-full px-3 py-1 transition {{ $methodFilter === 'manual_transfer' ? 'bg-violet-500 text-white' : 'bg-violet-100 text-violet-700 hover:bg-violet-200' }}">Manual Transfer</button>
            <button wire:click="$set('methodFilter', 'gateway')" type="button" class="rounded-full px-3 py-1 transition {{ $methodFilter === 'gateway' ? 'bg-fuchsia-500 text-white' : 'bg-fuchsia-100 text-fuchsia-700 hover:bg-fuchsia-200' }}">Gateway</button>
        </div>

        <div class="mt-5 space-y-3">
            @forelse ($payments as $payment)
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <div class="flex flex-col gap-3 xl:flex-row xl:items-start xl:justify-between">
                        <div class="space-y-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ match($payment->status) { 'paid' => 'bg-emerald-100 text-emerald-700', 'waiting_validation' => 'bg-amber-100 text-amber-700', 'pending_payment' => 'bg-sky-100 text-sky-700', default => 'bg-slate-100 text-slate-700' } }}">
                                    {{ strtoupper($payment->status) }}
                                </span>
                                <span class="rounded-full bg-white/80 px-3 py-1 text-xs font-semibold text-slate-600">{{ strtoupper($payment->payment_method) }}</span>
                                <span class="text-sm font-semibold text-slate-900">{{ $payment->invoice_number }}</span>
                            </div>
                            <p class="text-sm text-slate-700">
                                {{ $payment->tenant?->name ?? '-' }} · {{ $payment->plan?->name ?? '-' }} · {{ ucfirst(str_replace('_', ' ', $payment->type)) }}
                            </p>
                            <div class="flex flex-wrap gap-2 text-xs text-slate-500">
                                @if ($payment->tenant?->code)
                                    <span class="rounded-full bg-white px-2.5 py-1">Kode: {{ $payment->tenant->code }}</span>
                                @endif
                                @if ($payment->tenant?->owner_email)
                                    <span class="rounded-full bg-white px-2.5 py-1">{{ $payment->tenant->owner_email }}</span>
                                @endif
                                <span class="rounded-full bg-white px-2.5 py-1">Rp{{ number_format((float) $payment->amount_total, 0, ',', '.') }}</span>
                                <span class="rounded-full bg-white px-2.5 py-1">Due: {{ optional($payment->due_at)->format('d M Y H:i') ?? '-' }}</span>
                                @if ($payment->proofs->isNotEmpty())
                                    <span class="rounded-full bg-white px-2.5 py-1">{{ $payment->proofs->count() }} bukti bayar</span>
                                @endif
                                @if ($payment->provider_reference)
                                    <span class="rounded-full bg-white px-2.5 py-1">Ref: {{ $payment->provider_reference }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('platform.payment.pdf', ['invoiceNumber' => $payment->invoice_number]) }}" target="_blank" rel="noopener" class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                                Buka Invoice
                            </a>
                            @if ($payment->tenant_id)
                                <a href="{{ route('platform.admin.tenants.index', ['tenant' => $payment->tenant_id, 'focus' => 'billing']) }}#billing-panel" class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                                    Buka Penagihan Tenant
                                </a>
                            @endif
                            @if (!in_array($payment->status, ['paid', 'cancelled', 'refunded', 'expired'], true))
                                <button wire:click="approvePayment({{ $payment->id }})" type="button" wire:loading.attr="disabled" wire:target="approvePayment({{ $payment->id }})" class="rounded-xl bg-emerald-500 px-3 py-2 text-xs font-semibold text-slate-950 transition hover:bg-emerald-400 disabled:cursor-not-allowed disabled:opacity-70">
                                    <span wire:loading.remove wire:target="approvePayment({{ $payment->id }})">Validasi Pembayaran</span>
                                    <span wire:loading wire:target="approvePayment({{ $payment->id }})">Memvalidasi...</span>
                                </button>
                                <button wire:click="rejectPayment({{ $payment->id }})" type="button" wire:loading.attr="disabled" wire:target="rejectPayment({{ $payment->id }})" class="rounded-xl px-3 py-2 text-xs font-semibold transition disabled:cursor-not-allowed disabled:opacity-70 {{ ($rejectConfirmations[$payment->id] ?? false) ? 'bg-amber-500 text-slate-950 hover:bg-amber-400' : 'bg-rose-500 text-white hover:bg-rose-400' }}">
                                    <span wire:loading.remove wire:target="rejectPayment({{ $payment->id }})">{{ ($rejectConfirmations[$payment->id] ?? false) ? 'Konfirmasi Tolak' : 'Tolak / Upload Ulang' }}</span>
                                    <span wire:loading wire:target="rejectPayment({{ $payment->id }})">Memproses Penolakan...</span>
                                </button>
                            @endif
                        </div>
                    </div>
                    @if ($payment->proofs->isNotEmpty())
                        <div class="mt-3 space-y-2 rounded-2xl bg-white p-3 ring-1 ring-slate-200">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Bukti Pembayaran Terbaru</p>
                            @foreach ($payment->proofs->take(2) as $proof)
                                <div class="rounded-xl bg-slate-50 px-3 py-3 text-xs text-slate-600 ring-1 ring-slate-200">
                                    <p class="font-semibold text-slate-900">{{ $proof->submitted_by_name ?: '-' }} · {{ $proof->submitted_by_email ?: '-' }}</p>
                                    <p class="mt-1">Review: {{ strtoupper($proof->review_status) }} · Transfer: {{ optional($proof->transfer_date)->format('d M Y H:i') ?? '-' }}</p>
                                    <p class="mt-1">Bank: {{ $proof->bank_name ?: '-' }} · Rekening: {{ $proof->account_name ?: '-' }}</p>
                                    <p class="mt-1">Nominal: Rp{{ number_format((float) $proof->transfer_amount, 0, ',', '.') }}</p>
                                    @if ($proof->proof_file_path)
                                        <a href="{{ asset('storage/' . $proof->proof_file_path) }}" target="_blank" rel="noopener" class="mt-2 inline-flex rounded-full bg-sky-100 px-3 py-1.5 text-xs font-semibold text-sky-700 transition hover:bg-sky-200">
                                            Buka File Bukti
                                        </a>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                    @if ($payment->notes)
                        <p class="mt-3 text-xs leading-5 text-slate-500">{{ $payment->notes }}</p>
                    @endif
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50/70 px-4 py-10 text-center">
                    <div class="mx-auto max-w-md space-y-3">
                        <div class="inline-flex rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-600 ring-1 ring-slate-200">
                            Pusat Pembayaran
                        </div>
                        <p class="text-sm font-medium text-slate-700">Belum ada invoice untuk filter ini.</p>
                        <p class="text-sm text-slate-500">Coba ubah status atau metode pembayaran. Invoice manual transfer dan gateway yang perlu validasi akan terkumpul di halaman ini agar tim finance dan support bisa follow-up lebih cepat.</p>
                        <div class="flex flex-wrap items-center justify-center gap-2">
                            <button wire:click="$set('statusFilter', 'all')" type="button" class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">
                                Lihat Semua Status
                            </button>
                            <button wire:click="$set('methodFilter', 'all')" type="button" class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                Reset Metode
                            </button>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        @if ($payments->hasPages())
            <div class="mt-5">
                {{ $payments->links() }}
            </div>
        @endif
    </div>
</div>
