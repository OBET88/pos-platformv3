<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div class="max-w-3xl">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-emerald-700">Akuntansi Tenant · Laporan</p>
            <h1 class="mt-2 text-2xl font-semibold text-slate-900">Pantau jurnal posted dan pending mapping lintas tenant.</h1>
            <p class="mt-2 text-sm text-slate-500">Halaman ini membaca dari tabel jurnal akuntansi, bukan langsung dari transaksi POS mentah. Jadi ini cocok dipakai tim untuk validasi hasil posting saat UAT.</p>
        </div>
    </div>

    <div class="flex flex-wrap gap-2 rounded-2xl border border-slate-200 bg-white p-2 shadow-sm">
        <a href="{{ route('platform.admin.accounting.settings') }}" wire:navigate class="inline-flex items-center rounded-xl px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
            Setting COA & Mapping
        </a>
        <span class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">
            Laporan & Jurnal
        </span>
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

    <div class="grid gap-2 md:grid-cols-2 xl:grid-cols-6">
        <div class="rounded-xl bg-white px-4 py-3 shadow-sm ring-1 ring-slate-200">
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Total Jurnal</p>
            <p class="mt-1 text-3xl font-semibold leading-none text-slate-900">{{ number_format($stats['total_journals']) }}</p>
        </div>
        <div class="rounded-xl bg-white px-4 py-3 shadow-sm ring-1 ring-slate-200">
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Posted</p>
            <p class="mt-1 text-3xl font-semibold leading-none text-emerald-600">{{ number_format($stats['posted']) }}</p>
        </div>
        <div class="rounded-xl bg-white px-4 py-3 shadow-sm ring-1 ring-slate-200">
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Pending Mapping</p>
            <p class="mt-1 text-3xl font-semibold leading-none text-amber-600">{{ number_format($stats['pending_mapping']) }}</p>
        </div>
        <div class="rounded-xl bg-white px-4 py-3 shadow-sm ring-1 ring-slate-200">
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Queue</p>
            <p class="mt-1 text-3xl font-semibold leading-none text-sky-600">{{ number_format($stats['queue']) }}</p>
        </div>
        <div class="rounded-xl bg-white px-4 py-3 shadow-sm ring-1 ring-slate-200">
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Total Debit</p>
            <p class="mt-1 text-xl font-semibold leading-none text-slate-900">Rp{{ number_format($stats['total_debit'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-xl bg-white px-4 py-3 shadow-sm ring-1 ring-slate-200">
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Total Credit</p>
            <p class="mt-1 text-xl font-semibold leading-none text-slate-900">Rp{{ number_format($stats['total_credit'], 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <div class="grid gap-4 xl:grid-cols-[240px_220px_1fr_180px_180px]">
            <div>
                <label for="tenant-filter" class="text-sm font-semibold text-slate-900">Tenant</label>
                <select id="tenant-filter" wire:model.live="selectedTenantId" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none">
                    @foreach ($tenants as $tenant)
                        <option value="{{ $tenant->id }}">{{ $tenant->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="status-filter" class="text-sm font-semibold text-slate-900">Status</label>
                <select id="status-filter" wire:model.live="statusFilter" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none">
                    <option value="all">Semua</option>
                    <option value="posted">Posted</option>
                    <option value="pending_mapping">Pending Mapping</option>
                    <option value="failed">Failed</option>
                    <option value="draft">Draft</option>
                </select>
            </div>
            <div>
                <label for="search-filter" class="text-sm font-semibold text-slate-900">Cari</label>
                <input id="search-filter" wire:model.live.debounce.300ms="search" type="text" placeholder="Cari jurnal, reference, event key, atau deskripsi" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-400 focus:outline-none">
            </div>
            <div>
                <label for="date-from" class="text-sm font-semibold text-slate-900">Dari Tanggal</label>
                <input id="date-from" wire:model.live="dateFrom" type="date" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none">
            </div>
            <div>
                <label for="date-to" class="text-sm font-semibold text-slate-900">Sampai Tanggal</label>
                <input id="date-to" wire:model.live="dateTo" type="date" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-400 focus:outline-none">
            </div>
        </div>

        <div class="mt-4 flex justify-end">
            <button type="button" wire:click="resetFilters" class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                Reset Filter
            </button>
        </div>
    </div>

    <div class="space-y-3">
        @forelse ($journals as $journal)
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex flex-col gap-3 xl:flex-row xl:items-start xl:justify-between">
                    <div class="space-y-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ match($journal->posting_status) { 'posted' => 'bg-emerald-100 text-emerald-700', 'pending_mapping' => 'bg-amber-100 text-amber-700', 'failed' => 'bg-rose-100 text-rose-700', default => 'bg-slate-100 text-slate-700' } }}">
                                {{ strtoupper($journal->posting_status) }}
                            </span>
                            <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-600 ring-1 ring-slate-200">{{ $journal->event_key }}</span>
                            <span class="text-sm font-semibold text-slate-900">{{ $journal->journal_no }}</span>
                        </div>
                        <p class="text-sm text-slate-700">
                            {{ $journal->tenant?->name ?? '-' }} · {{ $journal->description }}
                        </p>
                        <div class="flex flex-wrap gap-2 text-xs text-slate-500">
                            <span class="rounded-full bg-slate-50 px-2.5 py-1">Tanggal: {{ optional($journal->journal_date)->format('d M Y') ?? '-' }}</span>
                            <span class="rounded-full bg-slate-50 px-2.5 py-1">Source: {{ $journal->source_type }}</span>
                            @if ($journal->reference_no)
                                <span class="rounded-full bg-slate-50 px-2.5 py-1">Ref: {{ $journal->reference_no }}</span>
                            @endif
                            @if ($journal->source_id)
                                <span class="rounded-full bg-slate-50 px-2.5 py-1">Source ID: {{ $journal->source_id }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="rounded-2xl bg-slate-50 px-4 py-3 ring-1 ring-slate-200">
                        <p class="text-[11px] uppercase tracking-wide text-slate-400">Nilai Jurnal</p>
                        <p class="mt-2 text-sm font-semibold text-slate-900">
                            Dr Rp{{ number_format($journal->lines->sum(fn ($line) => (float) $line->debit), 0, ',', '.') }}
                        </p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">
                            Cr Rp{{ number_format($journal->lines->sum(fn ($line) => (float) $line->credit), 0, ',', '.') }}
                        </p>
                    </div>
                </div>

                @if (($journal->meta['missing_mappings'] ?? []) !== [])
                    <div class="mt-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                        Mapping akun yang belum lengkap: {{ implode(', ', $journal->meta['missing_mappings']) }}
                    </div>
                @endif

                <div class="mt-3 overflow-hidden rounded-2xl border border-slate-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Akun</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Deskripsi</th>
                                    <th class="px-4 py-3 text-right font-semibold text-slate-600">Debit</th>
                                    <th class="px-4 py-3 text-right font-semibold text-slate-600">Credit</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @forelse ($journal->lines as $line)
                                    <tr>
                                        <td class="px-4 py-3 text-slate-900">{{ $line->account?->code }} · {{ $line->account?->name }}</td>
                                        <td class="px-4 py-3 text-slate-600">{{ $line->description ?: '-' }}</td>
                                        <td class="px-4 py-3 text-right text-slate-700">{{ $line->debit > 0 ? 'Rp'.number_format((float) $line->debit, 0, ',', '.') : '-' }}</td>
                                        <td class="px-4 py-3 text-right text-slate-700">{{ $line->credit > 0 ? 'Rp'.number_format((float) $line->credit, 0, ',', '.') : '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-6 text-center text-slate-500">Belum ada line jurnal.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50/70 px-4 py-10 text-center">
                <div class="mx-auto max-w-md space-y-3">
                    <div class="inline-flex rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-600 ring-1 ring-slate-200">
                        Laporan Akuntansi
                    </div>
                    <p class="text-sm font-medium text-slate-700">Belum ada jurnal untuk filter ini.</p>
                    <p class="text-sm text-slate-500">Pastikan tenant sudah punya COA dan transaksi sudah diposting ke engine jurnal. Jurnal dengan mapping yang belum lengkap akan muncul sebagai `pending mapping`.</p>
                </div>
            </div>
        @endforelse
    </div>

    @if ($journals->hasPages())
        <div>
            {{ $journals->links() }}
        </div>
    @endif
</div>
