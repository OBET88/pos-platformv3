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

        <form wire:submit="submit">
            <div class="field">
                <label for="tenant_code">Kode Tenant</label>
                <input
                    id="tenant_code"
                    type="text"
                    wire:model.live.debounce.600ms="tenantCode"
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

            <div class="field">
                <label for="target_plan_id">Paket Tujuan</label>
                <select
                    id="target_plan_id"
                    wire:model.live="targetPlanId"
                    @disabled($plans->isEmpty())
                    required
                >
                    <option value="">Pilih paket upgrade</option>
                    @foreach ($plans as $plan)
                        <option value="{{ $plan->id }}">
                            {{ $plan->name }} - Rp{{ number_format((float) $plan->price, 0, ',', '.') }}/bulan
                        </option>
                    @endforeach
                </select>
                @error('targetPlanId')<small class="field-error">{{ $message }}</small>@enderror
            </div>

            <div class="field">
                <label for="billing_cycle">Siklus Billing Upgrade</label>
                <select id="billing_cycle" wire:model.live="billingCycle" required>
                    <option value="monthly">Bulanan</option>
                    <option value="quarterly">3 Bulan</option>
                    <option value="yearly">Tahunan</option>
                </select>
                @error('billingCycle')<small class="field-error">{{ $message }}</small>@enderror
            </div>

            <div class="preview-card">
                <strong>Estimasi tagihan upgrade</strong>
                <span>Nominal dihitung dari selisih paket saat ini ke paket tujuan sesuai siklus billing yang dipilih.</span>
                @if ($preview)
                    <div class="preview-amount">Rp{{ number_format($preview['amount'], 0, ',', '.') }}</div>
                    <span class="muted">
                        Tagihan upgrade {{ $preview['label'] }}: Rp{{ number_format($preview['target'], 0, ',', '.') }}
                        @if ($preview['credit'] > 0)
                            − potongan sisa {{ $remainingDays }} hari Rp{{ number_format($preview['credit'], 0, ',', '.') }}
                        @endif
                        = Rp{{ number_format($preview['amount'], 0, ',', '.') }}.
                        Aktif s/d {{ $preview['expires_at'] }}.
                    </span>
                @else
                    <div class="preview-amount">Rp0</div>
                    <span class="muted">Pilih paket tujuan untuk melihat estimasi tagihan.</span>
                @endif
            </div>

            <div class="field">
                <label for="notes">Catatan Tambahan</label>
                <textarea id="notes" wire:model="notes" placeholder="Opsional: catatan untuk admin atau referensi invoice"></textarea>
                @error('notes')<small class="field-error">{{ $message }}</small>@enderror
            </div>

            <button type="submit" class="btn" @disabled($plans->isEmpty()) wire:loading.attr="disabled" wire:target="submit">
                <span wire:loading.remove wire:target="submit">Lanjut Buat Invoice Upgrade</span>
                <span wire:loading wire:target="submit">Membuat invoice...</span>
            </button>
        </form>
    </section>
</div>
