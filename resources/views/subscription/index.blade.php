<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pusat Langganan</title>
    <style>
        :root {
            --bg: #f8fafc;
            --panel: rgba(255, 255, 255, 0.96);
            --text: #0f172a;
            --muted: #64748b;
            --line: rgba(148, 163, 184, 0.24);
            --brand: #0f766e;
            --brand-strong: #115e59;
            --danger: #be123c;
            --warning: #b45309;
            --success: #047857;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Poppins", sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at top left, rgba(20, 184, 166, 0.12), transparent 24%),
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

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            color: inherit;
            text-decoration: none;
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

        .nav-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            padding: 0 18px;
            border-radius: 999px;
            border: 1px solid rgba(15, 118, 110, 0.18);
            background: rgba(255, 255, 255, 0.8);
            color: var(--brand-strong);
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
        }

        .layout {
            display: grid;
            grid-template-columns: minmax(0, 1.08fr) minmax(340px, 0.92fr);
            gap: 18px;
        }

        .panel {
            background: var(--panel);
            border: 1px solid rgba(255, 255, 255, 0.82);
            border-radius: 28px;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.08);
        }

        .hero, .sidebar {
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

        .status-banner,
        .summary-card,
        .action-card,
        .empty-card,
        .plan-card {
            border: 1px solid var(--line);
            border-radius: 22px;
            background: #fff;
            padding: 18px;
        }

        .summary-grid, .action-list, .plan-list {
            display: grid;
            gap: 12px;
            margin-top: 18px;
        }

        .summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
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

        .status-expired { color: var(--danger); background: rgba(244, 63, 94, 0.12); }
        .status-active { color: var(--success); background: rgba(4, 120, 87, 0.12); }
        .status-trial { color: var(--warning); background: rgba(245, 158, 11, 0.15); }

        .cta {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 48px;
            width: 100%;
            border-radius: 999px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 700;
            color: #fff;
            background: linear-gradient(135deg, var(--brand-strong), #14b8a6);
            box-shadow: 0 18px 34px rgba(15, 118, 110, 0.22);
        }

        .cta.secondary {
            background: #fff;
            color: var(--brand-strong);
            border: 1px solid rgba(15, 118, 110, 0.18);
            box-shadow: none;
        }

        .plan-price {
            margin-top: 10px;
            font-size: 22px;
            font-weight: 800;
            color: var(--brand-strong);
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
    <div class="shell">
        <div class="topbar">
            <a href="/" class="brand" aria-label="RWP POS">
                <span class="brand-badge">RP</span>
                <span>
                    <strong>RWP POS</strong><br>
                    <span class="muted" style="font-size:12px;">Subscription Center</span>
                </span>
            </a>
            @if ($tenant)
                <a href="{{ $ordersUrl }}" class="nav-link">Pesanan Saya</a>
            @endif
        </div>

        @if (!$tenant)
            <section class="panel hero">
                <span class="eyebrow">Pusat Langganan</span>
                <h1>Tenant untuk domain ini belum ditemukan.</h1>
                <div class="empty-card">
                    <strong>Domain tenant belum tersambung</strong>
                    <span>Halaman ini dipakai untuk renewal, upgrade, dan histori invoice saat tenant aktif maupun expired. Pastikan domain primary tenant sudah terdaftar di platform admin.</span>
                </div>
            </section>
        @else
            <div class="layout">
                <section class="panel hero">
                    <span class="eyebrow">Pusat Langganan</span>
                    <h1>{{ $tenant->name }}</h1>
                    <p class="lead">Saat tenant expired, halaman ini tetap aktif supaya pelanggan bisa langsung memperpanjang, upgrade paket, atau cek histori pesanan tanpa membuka menu operasional lain.</p>

                    <div class="status-banner">
                        <strong>Status tenant saat ini</strong>
                        <div>
                            @php($subscriptionStatus = strtolower((string) ($subscription?->status ?? $tenant->status)))
                            <span class="status-pill {{ $subscriptionStatus === 'expired' ? 'status-expired' : ($subscriptionStatus === 'trial' ? 'status-trial' : 'status-active') }}">
                                {{ strtoupper((string) ($subscription?->status ?? $tenant->status)) }}
                            </span>
                        </div>
                        <p class="muted" style="margin:12px 0 0;">
                            Berakhir pada {{ optional($subscription?->expiresAt())->format('d M Y H:i') ?? '-' }}
                        </p>
                    </div>

                    <div class="summary-grid">
                        <div class="summary-card">
                            <strong>Paket saat ini</strong>
                            <span>{{ $currentPlan?->name ?? '-' }}</span>
                        </div>
                        <div class="summary-card">
                            <strong>Email owner</strong>
                            <span>{{ $tenant->owner_email ?? '-' }}</span>
                        </div>
                        <div class="summary-card">
                            <strong>Kode tenant</strong>
                            <span>{{ $tenant->code }}</span>
                        </div>
                        <div class="summary-card">
                            <strong>Menu yang tetap hidup saat expired</strong>
                            <span>Perpanjang, upgrade, dan histori pesanan.</span>
                        </div>
                    </div>

                    @if ($availableUpgradePlans->isNotEmpty())
                        <div class="plan-list">
                            @foreach ($availableUpgradePlans as $plan)
                                <div class="plan-card">
                                    <strong>{{ $plan->name }}</strong>
                                    <span>Paket upgrade yang tersedia untuk tenant ini.</span>
                                    <div class="plan-price">Rp{{ number_format((float) $plan->price, 0, ',', '.') }}/bulan dasar</div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>

                <aside class="panel sidebar">
                    <span class="eyebrow">Aksi Cepat</span>
                    <div class="action-list">
                        <div class="action-card">
                            <strong>Perpanjang langganan</strong>
                            <span>Buat invoice renewal dan lanjutkan pembayaran publik tanpa tergantung admin.</span>
                            <div style="margin-top:16px;">
                                <a href="{{ $renewalUrl }}" class="cta">Perpanjang Sekarang</a>
                            </div>
                        </div>

                        <div class="action-card">
                            <strong>Upgrade paket</strong>
                            <span>Pilih paket yang lebih tinggi dan lihat selisih tagihan sebelum membuat invoice.</span>
                            <div style="margin-top:16px;">
                                <a href="{{ $upgradeUrl }}" class="cta secondary">Lihat Opsi Upgrade</a>
                            </div>
                        </div>

                        <div class="action-card">
                            <strong>Pesanan saya</strong>
                            <span>Lihat histori renewal dan upgrade, cek status pembayaran, lalu buka lagi invoice jika perlu upload bukti bayar.</span>
                            <div style="margin-top:16px;">
                                <a href="{{ $ordersUrl }}" class="cta secondary">Buka Histori Pesanan</a>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        @endif
    </div>
</body>
</html>
