<div class="layout">
    <section class="panel hero">
        <span class="eyebrow">Perpanjangan Mandiri</span>
        <h1>Perpanjang langganan tenant langsung dari pelanggan.</h1>
        <p class="lead">Alurnya dibuat serupa dengan pendaftaran publik: pelanggan isi data tenant, sistem membuat invoice renewal, lalu pembayaran dilanjutkan di halaman publik tanpa menunggu admin membuat invoice manual.</p>

        <div class="checklist">
            <div class="item">
                <strong>1. Cocokkan identitas tenant</strong>
                <span>Masukkan kode tenant dan email owner yang terdaftar agar renewal hanya bisa dibuat oleh pihak yang sesuai.</span>
            </div>
            <div class="item">
                <strong>2. Pilih durasi tagihan</strong>
                <span>Pelanggan tinggal memilih bulanan, 3 bulanan, atau tahunan untuk paket yang sedang aktif dipakai tenant.</span>
            </div>
            <div class="item">
                <strong>3. Lanjut ke pembayaran publik</strong>
                <span>Begitu invoice dibuat, pelanggan langsung diarahkan ke halaman pembayaran yang sudah mendukung upload bukti transfer atau DOKU Checkout.</span>
            </div>
        </div>

        @if ($tenant && $plan)
            <div class="summary-list">
                <div class="summary-card">
                    <strong>Tenant ditemukan</strong>
                    <span>{{ $tenant->name }} ({{ $tenant->code }})</span>
                </div>
                <div class="summary-card">
                    <strong>Paket saat ini</strong>
                    <span>{{ $plan->name }} · Rp{{ number_format((float) $plan->price, 0, ',', '.') }}/bulan dasar</span>
                </div>
                <div class="summary-card">
                    <strong>Status langganan</strong>
                    <span>{{ strtoupper((string) ($subscription?->status ?? $tenant->status)) }} · Berakhir {{ optional($subscription?->expiresAt())->format('d M Y H:i') ?? '-' }}</span>
                </div>
            </div>
        @endif
    </section>

    <section class="panel form-panel">
        @if ($errors->any())
            <div class="alert alert-error">
                Periksa lagi data renewal yang belum cocok.
            </div>
        @endif

        <h2 style="margin:0 0 8px;font-size:28px;">Buat Invoice Renewal</h2>
        <p class="muted" style="margin:0 0 18px;">Gunakan kode tenant dan email owner yang terdaftar. Sistem akan membuat invoice untuk paket yang sedang dipakai tenant.</p>

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
                <label for="billing_cycle">Siklus Renewal</label>
                <select id="billing_cycle" wire:model="billingCycle" required>
                    <option value="monthly">Bulanan</option>
                    <option value="quarterly">3 Bulan</option>
                    <option value="yearly">Tahunan</option>
                </select>
                @error('billingCycle')<small class="field-error">{{ $message }}</small>@enderror
            </div>

            <div class="field">
                <label for="notes">Catatan Tambahan</label>
                <textarea id="notes" wire:model="notes" placeholder="Opsional: catatan untuk admin atau referensi invoice"></textarea>
                @error('notes')<small class="field-error">{{ $message }}</small>@enderror
            </div>

            <button type="submit" class="btn" wire:loading.attr="disabled" wire:target="submit">
                <span wire:loading.remove wire:target="submit">Lanjut Buat Invoice Renewal</span>
                <span wire:loading wire:target="submit">Membuat invoice...</span>
            </button>
        </form>
    </section>
</div>
