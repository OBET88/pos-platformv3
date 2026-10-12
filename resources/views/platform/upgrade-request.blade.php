<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Upgrade Paket Tenant</title>
    @livewireStyles
    <style>
        :root {
            --bg: #f8fafc;
            --panel: rgba(255, 255, 255, 0.96);
            --text: #0f172a;
            --muted: #64748b;
            --line: rgba(148, 163, 184, 0.26);
            --brand: #0f766e;
            --brand-strong: #115e59;
            --accent: #f59e0b;
            --danger: #be123c;
            --soft: #ecfeff;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Poppins", sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at top left, rgba(20, 184, 166, 0.12), transparent 26%),
                radial-gradient(circle at bottom right, rgba(245, 158, 11, 0.12), transparent 24%),
                linear-gradient(180deg, #f8fafc 0%, #ecfeff 100%);
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
            background: linear-gradient(135deg, var(--brand), #14b8a6);
            color: #fff;
            font-weight: 800;
            box-shadow: 0 16px 34px rgba(15, 118, 110, 0.22);
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
            border: 1px solid rgba(15, 118, 110, 0.18);
            background: rgba(255, 255, 255, 0.8);
            font-size: 13px;
            font-weight: 700;
            color: var(--brand-strong);
        }

        .layout {
            display: grid;
            grid-template-columns: minmax(0, 1.08fr) minmax(340px, 0.92fr);
            gap: 18px;
        }

        .panel {
            background: var(--panel);
            border: 1px solid rgba(255, 255, 255, 0.85);
            border-radius: 28px;
            box-shadow: 0 24px 64px rgba(15, 23, 42, 0.08);
        }

        .hero, .form-panel {
            padding: 28px;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 8px 14px;
            border-radius: 999px;
            background: rgba(15, 118, 110, 0.08);
            color: var(--brand-strong);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        h1 {
            margin: 18px 0 12px;
            font-size: clamp(32px, 5vw, 48px);
            line-height: 1.04;
        }

        .lead, .muted {
            color: var(--muted);
            line-height: 1.8;
        }

        .checklist, .summary-list, .plan-list {
            display: grid;
            gap: 12px;
            margin-top: 22px;
        }

        .item, .summary-card, .plan-card, .preview-card {
            border: 1px solid var(--line);
            border-radius: 22px;
            background: #fff;
            padding: 16px 18px;
        }

        .item strong, .summary-card strong, .plan-card strong, .preview-card strong {
            display: block;
            font-size: 14px;
        }

        .item span, .summary-card span, .plan-card span, .preview-card span {
            display: block;
            margin-top: 6px;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.7;
        }

        .plan-card {
            background: linear-gradient(180deg, #ffffff 0%, #f8fffd 100%);
        }

        .plan-price {
            margin-top: 10px;
            font-size: 22px;
            font-weight: 800;
            color: var(--brand-strong);
        }

        .plan-meta {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 10px;
        }

        .pill {
            display: inline-flex;
            align-items: center;
            min-height: 28px;
            padding: 0 10px;
            border-radius: 999px;
            background: var(--soft);
            color: var(--brand-strong);
            font-size: 12px;
            font-weight: 700;
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

        .field input, .field select, .field textarea {
            width: 100%;
            border-radius: 16px;
            border: 1px solid rgba(148, 163, 184, 0.4);
            background: #fcfffe;
            padding: 13px 14px;
            font-size: 14px;
            color: var(--text);
        }

        .field textarea {
            min-height: 110px;
            resize: vertical;
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
            background: linear-gradient(135deg, var(--brand-strong), #14b8a6);
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            box-shadow: 0 18px 34px rgba(15, 118, 110, 0.24);
            cursor: pointer;
        }

        .preview-card {
            margin: 18px 0;
            background: linear-gradient(135deg, rgba(15, 118, 110, 0.08), rgba(245, 158, 11, 0.08));
        }

        .preview-amount {
            margin-top: 10px;
            font-size: 28px;
            font-weight: 800;
            color: var(--brand-strong);
        }

        @media (max-width: 980px) {
            .layout {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <div class="shell">
        <div class="topbar">
            <a href="{{ route('platform.landing') }}" class="brand" aria-label="RWP POS">
                <span class="brand-badge">RP</span>
                <span>
                    <strong>RWP POS</strong><br>
                    <span class="muted" style="font-size:12px;">Public Upgrade Flow</span>
                </span>
            </a>
            <div class="topbar-links">
                <a href="{{ route('platform.orders.index', ['tenant' => $tenantCode]) }}" class="nav-link">Konfirmasi Pembayaran</a>
                <a href="{{ route('platform.renewal.create') }}" class="nav-link">Perpanjang</a>
                <a href="{{ route('platform.register.create') }}" class="nav-link">Daftar Tenant Baru</a>
            </div>
        </div>

        @livewire('platform.public.upgrade-request', [
            'tenantCode' => $tenantCode,
            'plan' => $selectedPlanId,
            'billingCycle' => $selectedBillingCycle,
        ])
    </div>

    @livewireScripts
</body>
</html>
