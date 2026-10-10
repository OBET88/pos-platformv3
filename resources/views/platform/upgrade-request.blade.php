<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Upgrade Paket Tenant</title>
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
    @php($currentPlan = $subscription?->plan)
    @php($selectedPlanId = (int) old('target_plan_id', request()->query('plan', $plans->first()?->id ?? 0)))
    @php($selectedBillingCycle = (string) old('billing_cycle', request()->query('billing_cycle', 'monthly')))

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

        <div class="layout">
            <section class="panel hero">
                <span class="eyebrow">Upgrade Mandiri</span>
                <h1>Upgrade paket tenant langsung dari pelanggan.</h1>
                <p class="lead">Flow ini tidak perlu menunggu admin membuat invoice manual. Pelanggan cukup cocokkan identitas tenant, pilih paket yang lebih tinggi, lihat nominal tagihan upgrade, lalu lanjut bayar dari halaman publik.</p>

                <div class="checklist">
                    <div class="item">
                        <strong>1. Verifikasi tenant dan owner</strong>
                        <span>Kode tenant dan email owner harus cocok agar upgrade hanya bisa diproses oleh pihak yang sah.</span>
                    </div>
                    <div class="item">
                        <strong>2. Pilih paket tujuan</strong>
                        <span>Hanya paket aktif yang lebih tinggi dari paket saat ini yang akan ditampilkan sebagai opsi upgrade.</span>
                    </div>
                    <div class="item">
                        <strong>3. Tagihan muncul sebelum submit</strong>
                        <span>Nominal dihitung otomatis dari selisih paket lama ke paket baru sesuai siklus billing yang dipilih.</span>
                    </div>
                </div>

                @if ($tenant && $currentPlan)
                    <div class="summary-list">
                        <div class="summary-card">
                            <strong>Tenant ditemukan</strong>
                            <span>{{ $tenant->name }} ({{ $tenant->code }})</span>
                        </div>
                        <div class="summary-card">
                            <strong>Paket saat ini</strong>
                            <span>{{ $currentPlan->name }} - Rp{{ number_format((float) $currentPlan->price, 0, ',', '.') }}/bulan dasar</span>
                        </div>
                        <div class="summary-card">
                            <strong>Status langganan</strong>
                            <span>{{ strtoupper((string) ($subscription?->status ?? $tenant->status)) }} - Berakhir {{ optional($subscription?->expiresAt())->format('d M Y H:i') ?? '-' }}</span>
                        </div>
                    </div>

                    @if ($plans->isNotEmpty())
                        <div class="plan-list">
                            @foreach ($plans as $plan)
                                <div class="plan-card">
                                    <strong>{{ $plan->name }}</strong>
                                    <span>Paket tujuan upgrade dengan kapasitas lebih besar untuk tenant aktif.</span>
                                    <div class="plan-price">Rp{{ number_format((float) $plan->price, 0, ',', '.') }}/bulan dasar</div>
                                    <div class="plan-meta">
                                        <span class="pill">{{ $plan->max_branches }} cabang</span>
                                        <span class="pill">{{ $plan->max_users }} user</span>
                                        <span class="pill">{{ $plan->max_businesses }} bisnis</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="summary-list">
                            <div class="summary-card">
                                <strong>Belum ada opsi upgrade</strong>
                                <span>Tidak ditemukan paket aktif yang lebih tinggi dari paket tenant saat ini.</span>
                            </div>
                        </div>
                    @endif
                @endif
            </section>

            <section class="panel form-panel">
                @if ($errors->any())
                    <div class="alert alert-error">
                        Periksa lagi data upgrade yang belum cocok.
                    </div>
                @endif

                <h2 style="margin:0 0 8px;font-size:28px;">Buat Invoice Upgrade</h2>
                <p class="muted" style="margin:0 0 18px;">Pilih paket tujuan dan lihat estimasi nominal sebelum membuat invoice upgrade.</p>

                <form method="POST" action="{{ route('platform.upgrade.store') }}">
                    @csrf

                    <div class="field">
                        <label for="tenant_code">Kode Tenant</label>
                        <input id="tenant_code" name="tenant_code" type="text" value="{{ old('tenant_code', $tenantCode) }}" placeholder="Contoh: toko-hebat" required>
                        @error('tenant_code')<small class="field-error">{{ $message }}</small>@enderror
                    </div>

                    <div class="field">
                        <label for="owner_email">Email Owner</label>
                        <input id="owner_email" name="owner_email" type="email" value="{{ old('owner_email', $tenant?->owner_email) }}" placeholder="owner@bisnisanda.com" required>
                        @error('owner_email')<small class="field-error">{{ $message }}</small>@enderror
                    </div>

                    <div class="field">
                        <label for="target_plan_id">Paket Tujuan</label>
                        <select id="target_plan_id" name="target_plan_id" @disabled($plans->isEmpty()) required>
                            <option value="">Pilih paket upgrade</option>
                            @foreach ($plans as $plan)
                                <option
                                    value="{{ $plan->id }}"
                                    data-price="{{ (float) $plan->price }}"
                                    @selected($selectedPlanId === $plan->id)
                                >
                                    {{ $plan->name }} - Rp{{ number_format((float) $plan->price, 0, ',', '.') }}/bulan
                                </option>
                            @endforeach
                        </select>
                        @error('target_plan_id')<small class="field-error">{{ $message }}</small>@enderror
                    </div>

                    <div class="field">
                        <label for="billing_cycle">Siklus Billing Upgrade</label>
                        <select id="billing_cycle" name="billing_cycle" required>
                            <option value="monthly" @selected($selectedBillingCycle === 'monthly')>Bulanan</option>
                            <option value="quarterly" @selected($selectedBillingCycle === 'quarterly')>3 Bulan</option>
                            <option value="yearly" @selected($selectedBillingCycle === 'yearly')>Tahunan</option>
                        </select>
                        @error('billing_cycle')<small class="field-error">{{ $message }}</small>@enderror
                    </div>

                    <div class="preview-card">
                        <strong>Estimasi tagihan upgrade</strong>
                        <span>Nominal dihitung dari selisih paket saat ini ke paket tujuan sesuai siklus billing yang dipilih.</span>
                        <div id="upgrade-amount" class="preview-amount">Rp0</div>
                        <span id="upgrade-summary" class="muted">Pilih paket tujuan untuk melihat estimasi tagihan.</span>
                    </div>

                    <div class="field">
                        <label for="notes">Catatan Tambahan</label>
                        <textarea id="notes" name="notes" placeholder="Opsional: catatan untuk admin atau referensi invoice">{{ old('notes') }}</textarea>
                        @error('notes')<small class="field-error">{{ $message }}</small>@enderror
                    </div>

                    <button type="submit" class="btn" @disabled($plans->isEmpty())>Lanjut Buat Invoice Upgrade</button>
                </form>
            </section>
        </div>
    </div>

    <script>
        (function () {
            const currentPlanPrice = {{ json_encode((float) ($currentPlan?->price ?? 0)) }};
            const planSelect = document.getElementById('target_plan_id');
            const billingCycleSelect = document.getElementById('billing_cycle');
            const amountNode = document.getElementById('upgrade-amount');
            const summaryNode = document.getElementById('upgrade-summary');

            if (!planSelect || !billingCycleSelect || !amountNode || !summaryNode) {
                return;
            }

            const formatter = new Intl.NumberFormat('id-ID');
            const multipliers = {
                monthly: 1,
                quarterly: 3,
                yearly: 12,
            };
            const labels = {
                monthly: '1 bulan',
                quarterly: '3 bulan',
                yearly: '12 bulan',
            };

            const updateAmount = () => {
                const option = planSelect.options[planSelect.selectedIndex];
                const targetPrice = Number(option?.dataset?.price || 0);
                const cycle = billingCycleSelect.value || 'monthly';
                const multiplier = multipliers[cycle] || 1;
                const amount = Math.max(0, (targetPrice - currentPlanPrice) * multiplier);

                amountNode.textContent = 'Rp' + formatter.format(amount);

                if (!option || !option.value) {
                    summaryNode.textContent = 'Pilih paket tujuan untuk melihat estimasi tagihan.';
                    return;
                }

                summaryNode.textContent = 'Tagihan upgrade dihitung untuk ' + labels[cycle] + ' berdasarkan selisih harga paket lama dan paket baru.';
            };

            planSelect.addEventListener('change', updateAmount);
            billingCycleSelect.addEventListener('change', updateAmount);
            updateAmount();
        }());
    </script>
</body>
</html>
