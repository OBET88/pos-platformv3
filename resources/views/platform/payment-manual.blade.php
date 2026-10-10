<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Invoice Pembayaran | RWP POS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root {
            --bg: #f7f1ff;
            --panel: rgba(255, 255, 255, 0.95);
            --line: rgba(148, 163, 184, 0.2);
            --text: #1f2937;
            --muted: #6b7280;
            --brand: #7c3aed;
            --accent: #ec4899;
            --success: #059669;
            --warning: #b45309;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Poppins", sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at top left, rgba(236, 72, 153, 0.12), transparent 28%),
                radial-gradient(circle at bottom right, rgba(124, 58, 237, 0.12), transparent 30%),
                var(--bg);
        }

        .shell {
            width: min(1120px, calc(100% - 32px));
            margin: 0 auto;
            padding: 28px 0 48px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: inherit;
        }

        .brand-badge {
            width: 54px;
            height: 54px;
            border-radius: 16px;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, var(--brand), var(--accent));
            color: #fff;
            font-weight: 800;
            font-size: 20px;
            box-shadow: 0 16px 32px rgba(124, 58, 237, 0.24);
        }

        .brand-copy strong {
            display: block;
            font-size: 18px;
        }

        .brand-copy span {
            display: block;
            margin-top: 6px;
            color: var(--muted);
            font-size: 11px;
            letter-spacing: .14em;
            text-transform: uppercase;
        }

        .nav-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            padding: 0 18px;
            border-radius: 999px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
            color: var(--brand);
            background: rgba(124, 58, 237, 0.06);
            border: 1px solid rgba(124, 58, 237, 0.12);
        }

        .layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(320px, 0.9fr);
            gap: 18px;
        }

        .panel {
            background: var(--panel);
            border: 1px solid rgba(255, 255, 255, 0.82);
            border-radius: 28px;
            box-shadow: 0 24px 60px rgba(91, 33, 182, 0.12);
        }

        .summary {
            padding: 30px;
        }

        .eyebrow {
            display: inline-flex;
            padding: 8px 14px;
            border-radius: 999px;
            background: rgba(124, 58, 237, 0.08);
            color: var(--brand);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        h1 {
            margin: 18px 0 10px;
            font-size: clamp(32px, 5vw, 46px);
            line-height: 1.02;
        }

        .lead,
        .muted,
        .detail p,
        .helper,
        .status-note {
            color: var(--muted);
            line-height: 1.8;
        }

        .invoice-meta {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
            margin-top: 22px;
        }

        .meta-card,
        .detail,
        .status-card,
        .proof-list {
            padding: 18px;
            border-radius: 22px;
            background: #fff;
            border: 1px solid var(--line);
        }

        .meta-card strong,
        .detail strong,
        .status-card strong {
            display: block;
            font-size: 14px;
            color: #111827;
        }

        .meta-card span,
        .detail span {
            display: block;
            margin-top: 6px;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.7;
        }

        .stack {
            display: grid;
            gap: 14px;
            margin-top: 20px;
        }

        .payment-destination {
            padding: 22px;
            border-radius: 24px;
            background: linear-gradient(135deg, rgba(124, 58, 237, 0.1), rgba(236, 72, 153, 0.08));
            border: 1px solid rgba(124, 58, 237, 0.12);
        }

        .payment-destination-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin-top: 16px;
        }

        .destination-card {
            padding: 16px 18px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.82);
        }

        .destination-card strong {
            display: block;
            font-size: 13px;
            color: #111827;
        }

        .destination-card span {
            display: block;
            margin-top: 8px;
            font-size: 15px;
            line-height: 1.7;
            color: var(--muted);
        }

        .destination-amount {
            font-size: 30px;
            font-weight: 800;
            color: #4c1d95;
            line-height: 1.1;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        .status-paid { color: var(--success); background: rgba(5, 150, 105, 0.12); }
        .status-waiting { color: var(--warning); background: rgba(245, 158, 11, 0.14); }
        .status-pending { color: var(--brand); background: rgba(124, 58, 237, 0.1); }

        .form-panel {
            padding: 24px;
        }

        .alert {
            margin-bottom: 14px;
            padding: 12px 14px;
            border-radius: 16px;
            font-size: 13px;
            line-height: 1.6;
        }

        .alert-success {
            background: rgba(5, 150, 105, 0.1);
            border: 1px solid rgba(5, 150, 105, 0.18);
            color: #065f46;
        }

        .alert-error {
            background: rgba(225, 29, 72, 0.08);
            border: 1px solid rgba(225, 29, 72, 0.14);
            color: #9f1239;
        }

        .field {
            margin-bottom: 14px;
        }

        .field label {
            display: block;
            margin-bottom: 6px;
            font-size: 12px;
            font-weight: 700;
            color: #4c1d95;
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        .field input {
            width: 100%;
            height: 48px;
            border-radius: 16px;
            border: 1px solid rgba(196, 181, 253, 0.5);
            background: #fbf9ff;
            padding: 0 14px;
            font-size: 14px;
            color: #312e81;
        }

        .field input[type="file"] {
            height: auto;
            padding: 12px 14px;
        }

        .field-error {
            display: block;
            margin-top: 6px;
            font-size: 12px;
            color: #be123c;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 50px;
            padding: 0 22px;
            border: 0;
            border-radius: 999px;
            font-size: 14px;
            font-weight: 700;
            color: #fff;
            background: linear-gradient(135deg, var(--brand), var(--accent));
            box-shadow: 0 18px 34px rgba(124, 58, 237, 0.22);
            cursor: pointer;
        }

        .btn-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 50px;
            padding: 0 22px;
            border-radius: 999px;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            color: #4c1d95;
            background: rgba(124, 58, 237, 0.08);
            border: 1px solid rgba(124, 58, 237, 0.14);
            cursor: pointer;
        }

        .action-stack {
            display: grid;
            gap: 12px;
            margin-top: 18px;
        }

        .invoice-sheet {
            padding: 20px;
            border-radius: 24px;
            background: linear-gradient(180deg, #ffffff 0%, #faf5ff 100%);
            border: 1px solid rgba(196, 181, 253, 0.28);
        }

        .invoice-sheet-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin-top: 18px;
        }

        .invoice-sheet-card {
            padding: 16px 18px;
            border-radius: 18px;
            border: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.94);
        }

        .invoice-sheet-card strong {
            display: block;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #6d28d9;
        }

        .invoice-sheet-card span {
            display: block;
            margin-top: 8px;
            font-size: 15px;
            line-height: 1.7;
            color: var(--text);
        }

        .invoice-total {
            font-size: 34px;
            font-weight: 800;
            color: #111827;
            line-height: 1.05;
        }

        @media print {
            body {
                background: #fff;
            }

            .topbar,
            .form-panel {
                display: none !important;
            }

            .shell {
                width: 100%;
                margin: 0;
                padding: 0;
            }

            .layout {
                display: block;
            }

            .panel {
                box-shadow: none;
                border: 0;
                border-radius: 0;
            }

            .summary {
                padding: 0;
            }
        }

        .proof-item {
            padding: 12px 0;
            border-top: 1px solid var(--line);
        }

        .proof-item:first-child {
            border-top: 0;
            padding-top: 0;
        }

        @media (max-width: 980px) {
            .layout,
            .invoice-meta,
            .payment-destination-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    @php($latestProof = $transaction->proofs->first())
    @php($latestRejectedProof = $latestProof?->review_status === 'rejected' ? $latestProof : null)
    @php($isPaidLike = $transaction->status === 'paid' || $transaction->paid_at !== null || $latestProof?->review_status === 'approved')
    @php($isWaitingValidationLike = $transaction->status === 'waiting_validation' && ! $latestRejectedProof)
    @php($isInvoiceOnlyState = ($isPaidLike || $isWaitingValidationLike) && ! $latestRejectedProof)
    @php($isRegistrationFlow = $transaction->type === 'registration')
    @php($registrationStatusUrl = $isRegistrationFlow && $transaction->tenant ? route('platform.register.success', ['tenant' => $transaction->tenant->code]) : null)

    <div class="shell">
        <div class="topbar">
            <a href="{{ route('platform.landing') }}" class="brand" aria-label="RWP POS">
                <span class="brand-badge">RP</span>
                <span class="brand-copy">
                    <strong>RWP POS</strong>
                    <span>Retail Platform</span>
                </span>
            </a>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <a href="{{ route('platform.orders.index', ['tenant' => $transaction->tenant?->code, 'owner_email' => $transaction->tenant?->owner_email]) }}" class="nav-link">Pesanan Saya</a>
                <a href="{{ route('platform.contact') }}" class="nav-link">Butuh Bantuan?</a>
            </div>
        </div>

        <div class="layout">
            <section class="panel summary">
                <span class="eyebrow">Detail Invoice</span>
                <h1>{{ $transaction->invoice_number }}</h1>
                <p class="lead">
                    Invoice ini dibuat untuk tenant <strong>{{ $transaction->tenant?->name ?? '-' }}</strong>.
                    @if ($isInvoiceOnlyState)
                        {{ $isRegistrationFlow
                            ? 'Pembayaran sudah tercatat. Anda bisa memantau invoice ini dan lanjut ke status setup tenant dari satu tempat.'
                            : 'Halaman ini menampilkan detail invoice dan status verifikasi terbaru agar pelanggan cukup memantau dari satu tempat.' }}
                    @else
                        {{ $isRegistrationFlow
                            ? 'Pendaftaran tenant sudah terverifikasi. Langkah berikutnya adalah menyelesaikan invoice ini agar proses setup tenant bisa dimulai.'
                            : 'Silakan transfer sesuai nominal, lalu unggah bukti pembayaran agar tim admin bisa memvalidasi langganan Anda.' }}
                    @endif
                </p>

                <div class="stack">
                    <div class="payment-destination">
                        <strong style="display:block;font-size:18px;color:#111827;">Tagihan Siap Dibayar</strong>
                        <p class="status-note" style="margin:10px 0 0;">
                            @if ($isInvoiceOnlyState)
                                Rincian utama invoice tetap disimpan di sini agar mudah dicetak, dibagikan, atau disimpan sebagai PDF.
                            @else
                                Sebelum kirim konfirmasi manual, pastikan pelanggan melihat nominal tagihan dan rekening tujuan transfer berikut.
                            @endif
                        </p>

                        <div class="payment-destination-grid">
                            <div class="destination-card">
                                <strong>Nominal Transfer</strong>
                                <span class="destination-amount">Rp{{ number_format((float) $transaction->amount_total, 0, ',', '.') }}</span>
                            </div>
                            <div class="destination-card">
                                <strong>Invoice</strong>
                                <span>{{ $transaction->invoice_number }}</span>
                            </div>
                            <div class="destination-card">
                                <strong>Transfer Ke Bank</strong>
                                <span>{{ $paymentDestination['bank_name'] }}</span>
                            </div>
                            <div class="destination-card">
                                <strong>Atas Nama</strong>
                                <span>{{ $paymentDestination['account_name'] }}</span>
                            </div>
                            <div class="destination-card">
                                <strong>No. Rekening Tujuan</strong>
                                <span>{{ $paymentDestination['account_number'] }}</span>
                            </div>
                            <div class="destination-card">
                                <strong>Cabang / Keterangan</strong>
                                <span>{{ $paymentDestination['branch'] }}</span>
                            </div>
                        </div>

                        <div class="detail" style="margin-top:14px;">
                            <strong>Langkah Pembayaran</strong>
                            <span>{{ $paymentDestination['instructions'] }}</span>
                        </div>
                    </div>

                    <div class="status-card">
                        <strong>Status Saat Ini</strong>
                        <div style="margin-top:10px;">
                            <span class="status-pill {{ $transaction->status === 'paid' ? 'status-paid' : ($transaction->status === 'waiting_validation' ? 'status-waiting' : 'status-pending') }}">
                                {{ str_replace('_', ' ', strtoupper($transaction->status)) }}
                            </span>
                        </div>
                        <p class="status-note" style="margin-top:12px;">
                            @if ($transaction->status === 'paid')
                                {{ $isRegistrationFlow
                                    ? 'Pembayaran sudah tervalidasi. Sistem sedang menyiapkan tenant baru Anda.'
                                    : 'Pembayaran sudah tervalidasi. Langganan tenant sudah diaktifkan atau diperpanjang.' }}
                            @elseif ($transaction->status === 'waiting_validation')
                                {{ $isRegistrationFlow
                                    ? 'Bukti pembayaran registrasi sudah masuk dan sedang menunggu validasi admin sebelum setup tenant dimulai.'
                                    : 'Bukti pembayaran sudah masuk dan sedang menunggu validasi admin.' }}
                            @elseif ($latestRejectedProof)
                                Bukti pembayaran terakhir ditolak. Silakan periksa catatan admin dan unggah ulang bukti yang lebih sesuai.
                            @else
                                {{ $isRegistrationFlow
                                    ? 'Invoice registrasi masih menunggu pembayaran. Tenant belum akan di-setup sebelum invoice ini lunas.'
                                    : 'Invoice masih menunggu pembayaran atau unggahan bukti transfer.' }}
                            @endif
                        </p>
                        @if ($latestRejectedProof)
                            <div class="alert alert-error" style="margin-top:12px;margin-bottom:0;">
                                <strong style="display:block;margin-bottom:4px;">Perlu upload ulang bukti bayar</strong>
                                {{ $latestRejectedProof->review_notes ?: 'Tim admin meminta bukti pembayaran baru untuk invoice ini.' }}
                            </div>
                        @endif
                    </div>

                    <div class="invoice-meta">
                        <div class="meta-card">
                            <strong>Paket</strong>
                            <span>{{ $transaction->plan?->name ?? '-' }} · {{ ucfirst(str_replace('_', ' ', $transaction->type)) }}</span>
                        </div>
                        <div class="meta-card">
                            <strong>Nominal</strong>
                            <span>Rp{{ number_format((float) $transaction->amount_total, 0, ',', '.') }}</span>
                        </div>
                        <div class="meta-card">
                            <strong>Siklus</strong>
                            <span>{{ strtoupper($transaction->billing_cycle) }}</span>
                        </div>
                        <div class="meta-card">
                            <strong>Jatuh Tempo</strong>
                            <span>{{ optional($transaction->due_at)->format('d M Y H:i') ?? '-' }}</span>
                        </div>
                    </div>

                    <div class="detail">
                        <strong>Instruksi Singkat</strong>
                        <span>
                            @if ($isInvoiceOnlyState)
                                Simpan halaman ini sebagai bukti invoice. Jika perlu dokumen formal, gunakan tombol cetak untuk menyimpan invoice sebagai PDF dari browser.
                            @else
                                Transfer sesuai nominal invoice. Setelah itu unggah bukti bayar yang jelas agar tim admin bisa mengecek nominal, waktu transfer, dan akun pengirim. File yang didukung: JPG, PNG, atau PDF maksimal 4 MB.
                            @endif
                        </span>
                    </div>

                    @if ($transaction->proofs->isNotEmpty())
                        <div class="proof-list">
                            <strong>Bukti Terakhir</strong>
                            @foreach ($transaction->proofs->take(3) as $proof)
                                <div class="proof-item">
                                    <p class="muted" style="margin:0;">
                                        {{ $proof->submitted_by_name ?: '-' }} · {{ $proof->submitted_by_email ?: '-' }}<br>
                                        Review: {{ strtoupper($proof->review_status) }} · Transfer: {{ optional($proof->transfer_date)->format('d M Y H:i') ?? '-' }}
                                    </p>
                                    @if ($proof->review_notes)
                                        <p class="muted" style="margin:6px 0 0;">
                                            Catatan admin: {{ $proof->review_notes }}
                                        </p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>

            <section class="panel form-panel">
                @if ($isInvoiceOnlyState)
                    <h2 style="margin:0 0 8px;font-size:26px;">Invoice Siap Disimpan</h2>
                    <p class="helper" style="margin:0 0 18px;">Tampilan ini sengaja dibuat fokus ke detail invoice. Anda bisa mencetak atau menyimpan sebagai PDF langsung dari browser untuk kebutuhan administrasi.</p>

                    <div class="invoice-sheet">
                        <div style="display:flex;justify-content:space-between;gap:16px;align-items:flex-start;flex-wrap:wrap;">
                            <div>
                                <strong style="display:block;font-size:12px;letter-spacing:.08em;text-transform:uppercase;color:#6d28d9;">Invoice Resmi</strong>
                                <div style="margin-top:10px;font-size:24px;font-weight:800;color:#111827;">{{ $transaction->invoice_number }}</div>
                                <p class="helper" style="margin:10px 0 0;">{{ $transaction->tenant?->name ?? '-' }} · {{ $transaction->tenant?->code ?? '-' }}</p>
                            </div>
                            <div style="text-align:right;">
                                <span class="status-pill {{ $transaction->status === 'paid' ? 'status-paid' : 'status-waiting' }}">
                                    {{ str_replace('_', ' ', strtoupper($transaction->status)) }}
                                </span>
                                <div class="invoice-total" style="margin-top:14px;">Rp{{ number_format((float) $transaction->amount_total, 0, ',', '.') }}</div>
                            </div>
                        </div>

                        <div class="invoice-sheet-grid">
                            <div class="invoice-sheet-card">
                                <strong>Jenis Invoice</strong>
                                <span>{{ ucfirst(str_replace('_', ' ', $transaction->type)) }}</span>
                            </div>
                            <div class="invoice-sheet-card">
                                <strong>Paket</strong>
                                <span>{{ $transaction->plan?->name ?? '-' }} · {{ strtoupper($transaction->billing_cycle) }}</span>
                            </div>
                            <div class="invoice-sheet-card">
                                <strong>Tanggal Dibuat</strong>
                                <span>{{ optional($transaction->created_at)->format('d M Y H:i') ?? '-' }}</span>
                            </div>
                            <div class="invoice-sheet-card">
                                <strong>Jatuh Tempo</strong>
                                <span>{{ optional($transaction->due_at)->format('d M Y H:i') ?? '-' }}</span>
                            </div>
                            <div class="invoice-sheet-card">
                                <strong>Status Pembayaran</strong>
                                <span>{{ $transaction->status === 'paid' ? 'Sudah lunas dan tervalidasi.' : 'Bukti pembayaran sudah diterima dan sedang menunggu validasi admin.' }}</span>
                            </div>
                            <div class="invoice-sheet-card">
                                <strong>Pembayaran Tercatat</strong>
                                <span>{{ optional($transaction->paid_at)->format('d M Y H:i') ?? 'Belum ada waktu pelunasan final' }}</span>
                            </div>
                        </div>

                        <div class="action-stack">
                            <a href="{{ route('platform.payment.pdf', ['invoiceNumber' => $transaction->invoice_number]) }}" class="btn">Buka Versi PDF</a>
                            @if ($registrationStatusUrl)
                                <a href="{{ $registrationStatusUrl }}" class="btn-secondary">Lihat Status Setup Tenant</a>
                            @endif
                            <a href="{{ route('platform.orders.index', ['tenant' => $transaction->tenant?->code, 'owner_email' => $transaction->tenant?->owner_email]) }}" class="btn-secondary">Kembali ke Pesanan Saya</a>
                        </div>
                    </div>
                @else
                    @if (session('payment_proof_submitted'))
                        <div class="alert alert-success">
                            Bukti pembayaran berhasil dikirim. Tim admin akan memvalidasi invoice ini secepatnya.
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-error">
                            Periksa lagi data bukti pembayaran yang masih belum valid.
                        </div>
                    @endif

                    @if ($latestRejectedProof)
                        <div class="alert alert-error">
                            Bukti terakhir ditolak. Anda bisa langsung kirim ulang dari form ini setelah memastikan nominal, tanggal transfer, dan file bukti sudah benar.
                        </div>
                    @endif

                    <h2 style="margin:0 0 8px;font-size:26px;">Upload Bukti Bayar</h2>
                    <p class="helper" style="margin:0 0 18px;">Isi nama pengirim, nominal transfer, tanggal transfer, lalu lampirkan bukti pembayaran. Begitu terkirim, status invoice otomatis berubah ke `waiting_validation`.</p>

                    @if ($dokuEnabled && $transaction->status !== 'paid')
                        <form method="POST" action="{{ route('platform.payment.doku', ['invoiceNumber' => $transaction->invoice_number]) }}" style="margin-bottom:18px;">
                            @csrf
                            <button type="submit" class="btn" style="width:100%;">Bayar via DOKU Checkout</button>
                        </form>
                        <p class="helper" style="margin:0 0 18px;">Kalau ingin pembayaran otomatis, gunakan DOKU Checkout. Setelah DOKU mengirim notifikasi sukses, invoice ini akan diproses tanpa upload bukti transfer manual{{ $isRegistrationFlow ? ' dan setup tenant akan mulai dijalankan.' : '.' }}</p>
                    @endif

                    <form method="POST" action="{{ route('platform.payment.proof', ['invoiceNumber' => $transaction->invoice_number]) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="field">
                            <label for="submitted_by_name">Nama Pengirim</label>
                            <input id="submitted_by_name" type="text" name="submitted_by_name" value="{{ old('submitted_by_name') }}" placeholder="Nama pemilik rekening" required>
                            @error('submitted_by_name')<small class="field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="field">
                            <label for="submitted_by_email">Email Pengirim</label>
                            <input id="submitted_by_email" type="email" name="submitted_by_email" value="{{ old('submitted_by_email', $transaction->tenant?->owner_email) }}" placeholder="email@contoh.com" required>
                            @error('submitted_by_email')<small class="field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="field">
                            <label for="bank_name">Bank Pengirim</label>
                            <input id="bank_name" type="text" name="bank_name" value="{{ old('bank_name') }}" placeholder="Contoh: BCA">
                            @error('bank_name')<small class="field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="field">
                            <label for="account_name">Nama Rekening</label>
                            <input id="account_name" type="text" name="account_name" value="{{ old('account_name') }}" placeholder="Nama di rekening pengirim">
                            @error('account_name')<small class="field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="field">
                            <label for="account_number_masked">No. Rekening Pengirim</label>
                            <input id="account_number_masked" type="text" name="account_number_masked" value="{{ old('account_number_masked') }}" placeholder="Contoh: 1234****7890">
                            @error('account_number_masked')<small class="field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="field">
                            <label for="transfer_amount">Nominal Transfer</label>
                            <input id="transfer_amount" type="number" step="0.01" name="transfer_amount" value="{{ old('transfer_amount', (float) $transaction->amount_total) }}" required>
                            @error('transfer_amount')<small class="field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="field">
                            <label for="transfer_date">Tanggal Transfer</label>
                            <input id="transfer_date" type="datetime-local" name="transfer_date" value="{{ old('transfer_date') }}" required>
                            @error('transfer_date')<small class="field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="field">
                            <label for="proof_file">File Bukti Bayar</label>
                            <input id="proof_file" type="file" name="proof_file" accept=".jpg,.jpeg,.png,.pdf">
                            @error('proof_file')<small class="field-error">{{ $message }}</small>@enderror
                        </div>
                        <button type="submit" class="btn">Kirim Bukti Pembayaran</button>
                    </form>
                @endif
            </section>
        </div>
    </div>
</body>
</html>
