<div class="layout">
    <section class="panel sidebar">
        <span class="eyebrow">Konfirmasi Pembayaran</span>
        <h1>Lihat invoice aktif dan lanjutkan konfirmasi pembayaran dari satu tempat.</h1>
        <p class="lead">Setelah transfer, pelanggan cukup masuk ke menu ini untuk mengecek tagihan, melihat status pembayaran, lalu membuka invoice yang tepat untuk upload bukti atau melanjutkan pembayaran.</p>

        @if ($errors->any())
            <div class="alert alert-error">
                Periksa lagi data pencarian pesanan.
            </div>
        @endif

        @if (session('payment_proof_submitted'))
            <div class="alert alert-success">
                Bukti pembayaran untuk invoice {{ session('payment_invoice_number') ?: 'ini' }} berhasil dikirim. Sekarang statusnya menunggu validasi admin.
            </div>
        @endif

        <form wire:submit="lookup">
            <div class="field">
                <label for="tenant_code">Kode Tenant</label>
                <input
                    id="tenant_code"
                    type="text"
                    wire:model="tenantCode"
                    placeholder="Contoh: toko-hebat"
                    autocomplete="off"
                    required
                >
                @error('tenantCode')<small class="field-error">{{ $message }}</small>@enderror
            </div>

            <div class="field">
                <label for="owner_email">Email Owner</label>
                <input
                    id="owner_email"
                    type="email"
                    wire:model="ownerEmail"
                    placeholder="owner@bisnisanda.com"
                    autocomplete="off"
                    required
                >
                @error('ownerEmail')<small class="field-error">{{ $message }}</small>@enderror
            </div>

            <button type="submit" class="btn" wire:loading.attr="disabled" wire:target="lookup">
                <span wire:loading.remove wire:target="lookup">Tampilkan Invoice &amp; Konfirmasi</span>
                <span wire:loading wire:target="lookup">Mencari invoice...</span>
            </button>
        </form>

        <div class="helper-list">
            <div class="helper-card">
                <strong>Setelah transfer, lanjutkan dari invoice aktif</strong>
                <span>Kalau status masih `pending payment`, `waiting validation`, atau bukti ditolak, buka lagi invoice yang sama lalu lanjutkan konfirmasi pembayaran dari sana.</span>
            </div>
            <div class="helper-card">
                <strong>Urutan pakainya sederhana</strong>
                <span>Masukkan kode tenant dan email owner, buka invoice yang aktif, lalu unggah bukti transfer atau cek hasil validasinya di halaman invoice.</span>
            </div>
            <div class="helper-card">
                <strong>Akses tetap dibatasi</strong>
                <span>Histori hanya muncul kalau kode tenant dan email owner cocok, jadi pelanggan lain tidak bisa melihat pesanan tenant yang berbeda.</span>
            </div>
        </div>
    </section>

    <section class="panel content">
        @if ($tenant)
            <span class="eyebrow">Riwayat Tenant</span>
            <h2 style="margin:18px 0 10px;font-size:32px;">{{ $tenant->name }}</h2>
            <p class="muted" style="margin:0;">{{ $tenant->code }} · {{ $tenant->owner_email }}</p>

            <div class="summary-grid">
                <div class="summary-card">
                    <strong>Paket Saat Ini</strong>
                    <span>{{ $subscription?->plan?->name ?? '-' }}</span>
                </div>
                <div class="summary-card">
                    <strong>Status Langganan</strong>
                    <span>{{ strtoupper((string) ($subscription?->status ?? $tenant->status)) }}</span>
                </div>
                <div class="summary-card">
                    <strong>Total Pesanan</strong>
                    <span>{{ $transactions->count() }} invoice</span>
                </div>
                <div class="summary-card">
                    <strong>Invoice Terbaru</strong>
                    <span>{{ $transactions->first()['invoice_number'] ?? '-' }}</span>
                </div>
            </div>

            @if ($transactions->isNotEmpty())
                <div class="order-list">
                    @foreach ($transactions as $transaction)
                        <article class="order-card" wire:key="order-{{ $transaction['invoice_number'] }}">
                            <div class="order-top">
                                <div>
                                    <strong>{{ $transaction['invoice_number'] }}</strong>
                                    <span>{{ $transaction['plan_name'] }} · {{ ucfirst(str_replace('_', ' ', $transaction['type'])) }}</span>
                                </div>
                                <div class="order-amount">Rp{{ number_format((float) $transaction['amount_total'], 0, ',', '.') }}</div>
                            </div>

                            <span class="status-pill {{ $transaction['status'] === 'paid' ? 'status-paid' : ($transaction['status'] === 'waiting_validation' ? 'status-waiting' : 'status-pending') }}">
                                {{ str_replace('_', ' ', strtoupper($transaction['status'])) }}
                            </span>

                            <div class="order-meta">
                                <div class="mini">
                                    Dibuat: {{ optional($transaction['created_at'])->format('d M Y H:i') ?? '-' }}<br>
                                    Jatuh tempo: {{ optional($transaction['due_at'])->format('d M Y H:i') ?? '-' }}
                                </div>
                                <div class="mini">
                                    Siklus: {{ strtoupper($transaction['billing_cycle']) }}<br>
                                    Dibayar: {{ optional($transaction['paid_at'])->format('d M Y H:i') ?? '-' }}
                                </div>
                            </div>

                            @if ($transaction['latest_proof_status'])
                                <p class="mini" style="margin:14px 0 0;">
                                    Bukti terbaru: {{ strtoupper((string) $transaction['latest_proof_status']) }}
                                    @if ($transaction['latest_proof_notes'])
                                        · {{ $transaction['latest_proof_notes'] }}
                                    @endif
                                </p>
                            @endif

                            <div class="cta-row">
                                <a href="{{ $transaction['invoice_url'] }}" class="cta-link">
                                    Lihat Invoice
                                </a>
                                @if ($transaction['can_open_confirmation'])
                                    <a href="{{ $transaction['confirmation_url'] }}" class="cta-link">
                                        {{ $transaction['action_label'] }}
                                    </a>
                                @else
                                    <span class="cta-link-disabled">Menunggu Validasi Admin</span>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="empty-card" style="margin-top:18px;">
                    <strong>Belum ada histori invoice</strong>
                    <span>Tenant ini belum punya invoice renewal atau upgrade yang bisa ditampilkan.</span>
                </div>
            @endif
        @elseif ($lookupPerformed)
            <div class="empty-card">
                <strong>Invoice belum bisa ditampilkan</strong>
                <span>Pastikan kode tenant dan email owner sudah benar, lalu coba lagi.</span>
            </div>
        @else
            <div class="empty-card">
                <strong>Invoice dan status konfirmasi akan muncul di sini</strong>
                <span>Masukkan kode tenant dan email owner di panel kiri untuk melihat semua invoice pelanggan, statusnya, dan tombol lanjut ke detail invoice.</span>
            </div>
        @endif
    </section>
</div>
