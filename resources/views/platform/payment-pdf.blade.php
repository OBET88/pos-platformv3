<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $transaction->invoice_number }}</title>
    <style>
        :root {
            --ink: #1f2937;
            --muted: #6b7280;
            --line: #d1d5db;
            --panel: #ffffff;
            --accent: #f4b400;
            --accent-soft: #fff4cc;
            --dark: #374151;
            --bg: #f3f4f6;
            --success: #15803d;
            --warning: #b45309;
            --pending: #1d4ed8;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: var(--ink);
            background: var(--bg);
        }

        .printbar {
            position: sticky;
            top: 0;
            z-index: 20;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 16px 24px;
            background: rgba(255, 255, 255, 0.96);
            border-bottom: 1px solid #e5e7eb;
        }

        .printbar strong {
            font-size: 14px;
        }

        .printbar-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn,
        .btn-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            padding: 0 16px;
            border-radius: 999px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
            border: 1px solid transparent;
            cursor: pointer;
        }

        .btn {
            color: #111827;
            background: var(--accent);
        }

        .btn-secondary {
            color: #374151;
            background: #fff;
            border-color: #d1d5db;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            margin: 18px auto 40px;
            padding: 18mm 16mm 16mm;
            background: var(--panel);
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.12);
        }

        .header {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 20px;
            align-items: start;
        }

        .brand {
            display: grid;
            gap: 6px;
        }

        .brand-mark {
            display: inline-flex;
            align-items: center;
            gap: 12px;
        }

        .brand-badge {
            width: 42px;
            height: 42px;
            border: 3px solid var(--dark);
            border-radius: 12px;
            display: grid;
            place-items: center;
            font-size: 16px;
            font-weight: 800;
            color: var(--dark);
        }

        .brand-name {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: .02em;
        }

        .brand-subtitle {
            font-size: 11px;
            letter-spacing: .18em;
            text-transform: uppercase;
            color: var(--muted);
        }

        .invoice-head {
            text-align: right;
        }

        .invoice-title-row {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 14px;
        }

        .invoice-title-row::before,
        .invoice-title-row::after {
            content: "";
            width: 64px;
            height: 18px;
            background: var(--accent);
        }

        .invoice-title {
            font-size: 32px;
            font-weight: 300;
            letter-spacing: .06em;
            color: #111827;
        }

        .hero-bar {
            margin-top: 18px;
            height: 16px;
            width: 100%;
            background: var(--accent);
        }

        .meta {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 280px;
            gap: 22px;
            margin-top: 22px;
        }

        .section-label {
            font-size: 13px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 10px;
        }

        .recipient strong,
        .meta-box strong {
            display: block;
            font-size: 15px;
            margin-bottom: 6px;
        }

        .recipient p,
        .meta-box p {
            margin: 0;
            font-size: 13px;
            line-height: 1.7;
            color: var(--muted);
        }

        .meta-box {
            display: grid;
            gap: 12px;
        }

        .meta-row {
            display: grid;
            grid-template-columns: 90px minmax(0, 1fr);
            gap: 10px;
            font-size: 13px;
            align-items: start;
        }

        .meta-row span:first-child {
            color: var(--muted);
            font-weight: 700;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 24px;
        }

        thead th {
            background: var(--dark);
            color: #fff;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .04em;
            padding: 12px 10px;
            text-align: left;
        }

        tbody td {
            border-bottom: 1px solid #e5e7eb;
            padding: 14px 10px;
            font-size: 13px;
            vertical-align: top;
        }

        tbody tr:last-child td {
            border-bottom: 1px solid var(--line);
        }

        .col-no { width: 54px; text-align: center; }
        .col-price,
        .col-qty,
        .col-total { width: 120px; text-align: right; }

        .item-title {
            font-weight: 700;
            color: #111827;
        }

        .item-note {
            display: block;
            margin-top: 6px;
            color: var(--muted);
            line-height: 1.6;
        }

        .summary {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 290px;
            gap: 28px;
            margin-top: 28px;
            align-items: end;
        }

        .notes {
            display: grid;
            gap: 16px;
        }

        .note-card {
            padding: 16px 18px;
            border: 1px solid var(--line);
            background: #fff;
        }

        .note-card p {
            margin: 0;
            font-size: 13px;
            line-height: 1.8;
            color: var(--muted);
        }

        .totals {
            border: 1px solid var(--line);
        }

        .totals-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 130px;
            gap: 12px;
            padding: 12px 16px;
            border-bottom: 1px solid var(--line);
            font-size: 13px;
            align-items: center;
        }

        .totals-row:last-child {
            border-bottom: 0;
        }

        .totals-row strong:last-child,
        .totals-row span:last-child {
            text-align: right;
        }

        .grand-total {
            background: var(--accent);
            color: #111827;
        }

        .grand-total strong {
            font-size: 22px;
        }

        .footer {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 240px;
            gap: 28px;
            margin-top: 34px;
            align-items: end;
        }

        .footer-left {
            border-top: 4px solid var(--accent);
            padding-top: 12px;
            font-size: 13px;
            color: var(--muted);
            line-height: 1.8;
        }

        .signature {
            border-top: 4px solid var(--accent);
            padding-top: 12px;
            text-align: center;
            font-size: 13px;
            color: var(--muted);
        }

        .status-chip {
            display: inline-flex;
            align-items: center;
            min-height: 28px;
            padding: 0 12px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .status-paid { color: var(--success); background: #dcfce7; }
        .status-waiting { color: var(--warning); background: #fef3c7; }
        .status-pending { color: var(--pending); background: #dbeafe; }

        @page {
            size: A4;
            margin: 10mm;
        }

        /* Layar kecil: invoice A4 dibuat fluid agar tidak overflow di ponsel. */
        .table-scroll {
            width: 100%;
        }

        @media screen and (max-width: 900px) {
            .page {
                width: 100%;
                min-height: 0;
                margin: 12px auto 24px;
                padding: 16px 14px;
            }

            .header,
            .meta,
            .meta-row,
            .summary,
            .totals-row,
            .footer {
                grid-template-columns: minmax(0, 1fr);
            }

            .invoice-title-row::before,
            .invoice-title-row::after {
                display: none;
            }

            .table-scroll {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            table {
                min-width: 520px;
            }

            .grand-total strong {
                font-size: 18px;
            }
        }

        @media print {
            body {
                background: #fff;
            }

            .printbar {
                display: none;
            }

            .page {
                margin: 0;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    @php($itemLabel = ucfirst(str_replace('_', ' ', $transaction->type)))
    @php($itemDescription = $transaction->plan?->name ? $transaction->plan->name.' - '.strtoupper($transaction->billing_cycle) : strtoupper($transaction->billing_cycle))

    <div class="printbar">
        <strong>Invoice {{ $transaction->invoice_number }}</strong>
        <div class="printbar-actions">
            <button type="button" class="btn" onclick="window.print()">Cetak / Simpan PDF</button>
            <a href="{{ route('platform.payment.show', ['invoiceNumber' => $transaction->invoice_number]) }}" class="btn-secondary">Kembali ke Detail</a>
        </div>
    </div>

    <main class="page">
        <section class="header">
            <div class="brand">
                <div class="brand-mark">
                    <span class="brand-badge">RP</span>
                    <div>
                        <div class="brand-name">RWP POS</div>
                        <div class="brand-subtitle">Retail Platform Billing</div>
                    </div>
                </div>
            </div>

            <div class="invoice-head">
                <div class="invoice-title-row">
                    <span class="invoice-title">INVOICE</span>
                </div>
            </div>
        </section>

        <div class="hero-bar"></div>

        <section class="meta">
            <div class="recipient">
                <div class="section-label">Invoice To :</div>
                <strong>{{ $transaction->tenant?->name ?? '-' }}</strong>
                <p>
                    {{ $transaction->tenant?->owner_email ?? '-' }}<br>
                    Kode tenant: {{ $transaction->tenant?->code ?? '-' }}<br>
                    Paket: {{ $transaction->plan?->name ?? '-' }}
                </p>
            </div>

            <div class="meta-box">
                <div class="meta-row">
                    <span>Invoice#</span>
                    <strong>{{ $transaction->invoice_number }}</strong>
                </div>
                <div class="meta-row">
                    <span>Date</span>
                    <strong>{{ optional($transaction->created_at)->format('d / m / Y') ?? '-' }}</strong>
                </div>
                <div class="meta-row">
                    <span>Due</span>
                    <strong>{{ optional($transaction->due_at)->format('d / m / Y') ?? '-' }}</strong>
                </div>
                <div class="meta-row">
                    <span>Status</span>
                    <div>
                        <span class="status-chip {{ $transaction->status === 'paid' ? 'status-paid' : ($transaction->status === 'waiting_validation' ? 'status-waiting' : 'status-pending') }}">
                            {{ str_replace('_', ' ', strtoupper($transaction->status)) }}
                        </span>
                    </div>
                </div>
            </div>
        </section>

        <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th class="col-no">SL.</th>
                    <th>Item Description</th>
                    <th class="col-price">Price</th>
                    <th class="col-qty">Qty.</th>
                    <th class="col-total">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="col-no">1</td>
                    <td>
                        <span class="item-title">{{ $itemLabel }}</span>
                        <span class="item-note">{{ $itemDescription }}</span>
                        <span class="item-note">
                            @if ($transaction->status === 'paid')
                                Pembayaran sudah tervalidasi dan invoice dinyatakan lunas.
                            @elseif ($transaction->status === 'waiting_validation')
                                Bukti pembayaran sudah diterima dan sedang menunggu validasi admin.
                            @else
                                Invoice masih aktif dan menunggu pembayaran atau konfirmasi transfer.
                            @endif
                        </span>
                    </td>
                    <td class="col-price">Rp{{ number_format((float) $transaction->amount_subtotal, 0, ',', '.') }}</td>
                    <td class="col-qty">1</td>
                    <td class="col-total">Rp{{ number_format((float) $transaction->amount_subtotal, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
        </div>

        <section class="summary">
            <div class="notes">
                <div class="note-card">
                    <div class="section-label">Payment Info</div>
                    <p>
                        Account: {{ $paymentDestination['account_number'] }}<br>
                        A/C Name: {{ $paymentDestination['account_name'] }}<br>
                        Bank Detail: {{ $paymentDestination['bank_name'] }}{{ $paymentDestination['branch'] ? ' - '.$paymentDestination['branch'] : '' }}
                    </p>
                </div>

                <div class="note-card">
                    <div class="section-label">Term &amp; Conditions</div>
                    <p>{{ $paymentDestination['instructions'] }}</p>
                </div>
            </div>

            <div class="totals">
                <div class="totals-row">
                    <span>Sub Total:</span>
                    <strong>Rp{{ number_format((float) $transaction->amount_subtotal, 0, ',', '.') }}</strong>
                </div>
                <div class="totals-row">
                    <span>Discount:</span>
                    <strong>Rp{{ number_format((float) $transaction->amount_discount, 0, ',', '.') }}</strong>
                </div>
                <div class="totals-row grand-total">
                    <strong>Total:</strong>
                    <strong>Rp{{ number_format((float) $transaction->amount_total, 0, ',', '.') }}</strong>
                </div>
            </div>
        </section>

        <section class="footer">
            <div class="footer-left">
                Phone # | Address | Website<br>
                Invoice ini dibuat oleh sistem platform RWP POS untuk kebutuhan renewal atau upgrade langganan tenant.
            </div>
            <div class="signature">
                Authorised Sign
            </div>
        </section>
    </main>
</body>
</html>
