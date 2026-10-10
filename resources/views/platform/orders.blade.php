<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pesanan Saya</title>
    <style>
        :root {
            --bg: #fffaf1;
            --panel: rgba(255, 255, 255, 0.96);
            --text: #1f2937;
            --muted: #6b7280;
            --line: rgba(148, 163, 184, 0.22);
            --brand: #d97706;
            --brand-strong: #92400e;
            --accent: #0f766e;
            --success: #047857;
            --danger: #be123c;
            --warning: #b45309;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Poppins", sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at top left, rgba(245, 158, 11, 0.13), transparent 24%),
                radial-gradient(circle at bottom right, rgba(20, 184, 166, 0.12), transparent 26%),
                linear-gradient(180deg, #fffaf1 0%, #f8fafc 100%);
        }

        .shell {
            width: min(1180px, calc(100% - 32px));
            margin: 0 auto;
            padding: 28px 0 48px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
            margin-bottom: 22px;
        }

        .brand, .nav-link {
            text-decoration: none;
            color: inherit;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 12px;
        }

        .brand-badge {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, var(--brand), #f59e0b);
            color: #fff;
            font-weight: 800;
            box-shadow: 0 16px 34px rgba(217, 119, 6, 0.22);
        }

        .topbar-links {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .nav-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            padding: 0 18px;
            border-radius: 999px;
            border: 1px solid rgba(217, 119, 6, 0.18);
            background: rgba(255, 255, 255, 0.8);
            font-size: 13px;
            font-weight: 700;
            color: var(--brand-strong);
        }

        .layout {
            display: grid;
            grid-template-columns: minmax(340px, 0.95fr) minmax(0, 1.05fr);
            gap: 18px;
        }

        .panel {
            background: var(--panel);
            border: 1px solid rgba(255, 255, 255, 0.82);
            border-radius: 28px;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.08);
        }

        .sidebar, .content {
            padding: 28px;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 8px 14px;
            border-radius: 999px;
            background: rgba(217, 119, 6, 0.08);
            color: var(--brand-strong);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        h1 {
            margin: 18px 0 12px;
            font-size: clamp(30px, 4vw, 44px);
            line-height: 1.05;
        }

        .lead, .muted {
            color: var(--muted);
            line-height: 1.8;
        }

        .field {
            margin-bottom: 14px;
        }

        .field label {
            display: block;
            margin-bottom: 6px;
            font-size: 12px;
            font-weight: 700;
            color: var(--brand-strong);
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        .field input {
            width: 100%;
            border-radius: 16px;
            border: 1px solid rgba(148, 163, 184, 0.4);
            background: #fffdfa;
            padding: 13px 14px;
            font-size: 14px;
            color: var(--text);
        }

        .field-error {
            display: block;
            margin-top: 6px;
            font-size: 12px;
            color: var(--danger);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 50px;
            width: 100%;
            border: 0;
            border-radius: 999px;
            background: linear-gradient(135deg, var(--brand-strong), var(--brand));
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            box-shadow: 0 18px 34px rgba(217, 119, 6, 0.22);
            cursor: pointer;
        }

        .helper-card, .summary-card, .order-card, .empty-card {
            border: 1px solid var(--line);
            border-radius: 22px;
            background: #fff;
            padding: 18px;
        }

        .helper-list, .summary-grid, .order-list {
            display: grid;
            gap: 12px;
            margin-top: 18px;
        }

        .summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .helper-card strong, .summary-card strong, .order-card strong, .empty-card strong {
            display: block;
            font-size: 14px;
        }

        .helper-card span, .summary-card span, .order-card span, .empty-card span {
            display: block;
            margin-top: 6px;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.7;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            min-height: 30px;
            padding: 0 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            margin-top: 12px;
        }

        .status-paid { color: var(--success); background: rgba(4, 120, 87, 0.12); }
        .status-waiting { color: var(--warning); background: rgba(245, 158, 11, 0.16); }
        .status-pending { color: #7c2d12; background: rgba(251, 191, 36, 0.18); }

        .order-top, .order-meta {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .order-amount {
            font-size: 24px;
            font-weight: 800;
            color: var(--brand-strong);
        }

        .order-meta {
            margin-top: 14px;
        }

        .mini {
            color: var(--muted);
            font-size: 13px;
            line-height: 1.7;
        }

        .cta-row {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 16px;
        }

        .cta-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            padding: 0 16px;
            border-radius: 999px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
            background: rgba(15, 118, 110, 0.1);
            color: var(--accent);
            border: 1px solid rgba(15, 118, 110, 0.16);
        }

        .cta-link-disabled {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            padding: 0 16px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 700;
            background: rgba(148, 163, 184, 0.14);
            color: #64748b;
            border: 1px solid rgba(148, 163, 184, 0.2);
            cursor: not-allowed;
        }

        .alert {
            margin-bottom: 14px;
            padding: 12px 14px;
            border-radius: 16px;
            font-size: 13px;
            line-height: 1.6;
        }

        .alert-error {
            border: 1px solid rgba(190, 24, 93, 0.16);
            background: rgba(244, 63, 94, 0.08);
            color: var(--danger);
        }

        .alert-success {
            border: 1px solid rgba(4, 120, 87, 0.16);
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        @media (max-width: 980px) {
            .layout,
            .summary-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    @php($subscription = $tenant?->latestSubscription())

    <div class="shell">
        <div class="topbar">
            <a href="{{ route('platform.landing') }}" class="brand" aria-label="RWP POS">
                <span class="brand-badge">RP</span>
                <span>
                    <strong>RWP POS</strong><br>
                    <span class="muted" style="font-size:12px;">Order History</span>
                </span>
            </a>
            <div class="topbar-links">
                <a href="{{ route('platform.renewal.create') }}" class="nav-link">Perpanjang</a>
                <a href="{{ route('platform.upgrade.create') }}" class="nav-link">Upgrade</a>
                <a href="{{ route('platform.orders.index') }}" class="nav-link">Konfirmasi Pembayaran</a>
            </div>
        </div>

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

                <form method="POST" action="{{ route('platform.orders.lookup') }}">
                    @csrf
                    <div class="field">
                        <label for="tenant_code">Kode Tenant</label>
                        <input id="tenant_code" name="tenant_code" type="text" value="{{ old('tenant_code', $tenantCode) }}" placeholder="Contoh: toko-hebat" required>
                        @error('tenant_code')<small class="field-error">{{ $message }}</small>@enderror
                    </div>

                    <div class="field">
                        <label for="owner_email">Email Owner</label>
                        <input id="owner_email" name="owner_email" type="email" value="{{ old('owner_email', $ownerEmail) }}" placeholder="owner@bisnisanda.com" required>
                        @error('owner_email')<small class="field-error">{{ $message }}</small>@enderror
                    </div>

                    <button type="submit" class="btn">Tampilkan Invoice &amp; Konfirmasi</button>
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
                                <article class="order-card">
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
    </div>
</body>
</html>
