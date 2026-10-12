<section class="card">
    <div class="hero">
        <div style="font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;opacity:.84;">POS Platform</div>
        <h1>Cek email dulu sebelum tenant dibuat.</h1>
        <p>Pendaftaran publik sekarang menunggu verifikasi email owner agar tenant tidak dibuat dari spam, typo, atau pendaftaran yang tidak valid.</p>
    </div>
    <div class="body">
        <div class="note">
            <strong>Email tujuan:</strong> {{ $ownerEmail }}
        </div>

        @if ($status)
            <div class="alert-box success">{{ $status }}</div>
        @endif
        @if ($warning)
            <div class="alert-box warning">{{ $warning }}</div>
        @endif

        @if ($emailSent)
            <p>Link verifikasi sudah kami kirim. Setelah link dibuka, sistem baru akan membuat tenant dan menjalankan onboarding otomatis seperti flow yang sudah ada sekarang.</p>
        @else
            <p>Pengiriman email verifikasi belum terkonfirmasi dari aplikasi. Coba kirim ulang pendaftaran, lalu cek folder spam atau hubungi admin untuk verifikasi manual.</p>
        @endif

        <p>Kalau email belum masuk, cek folder spam/promotions dulu. Mengirim ulang pendaftaran dengan email yang sama tidak membuat tenant ganda. Sistem hanya memperbarui request verifikasi yang masih pending.</p>

        <div class="actions">
            <button
                type="button"
                class="cta primary"
                id="resend-button"
                wire:click="resend"
                wire:loading.attr="disabled"
                wire:target="resend"
                @disabled($cooldownSecondsRemaining > 0)
                x-data="{
                    remaining: @js($cooldownSecondsRemaining),
                    label: 'Kirim Ulang Verifikasi',
                    timer: null,
                    init() {
                        this.tick();
                    },
                    tick() {
                        if (this.timer) { clearInterval(this.timer); this.timer = null; }
                        if (this.remaining <= 0) { return; }
                        this.timer = setInterval(() => {
                            this.remaining -= 1;
                            if (this.remaining <= 0) { clearInterval(this.timer); this.timer = null; }
                        }, 1000);
                    },
                }"
                x-init="init()"
                x-text="remaining > 0 ? ('Kirim Ulang (' + remaining + ' dtk)') : label"
            >
                Kirim Ulang Verifikasi
            </button>
            <a href="{{ route('platform.register.create') }}" class="cta secondary">Kembali ke Form</a>
            <a href="{{ route('platform.help') }}" class="cta secondary">Butuh Bantuan?</a>
            <a href="{{ url('/') }}" class="cta secondary">Kembali ke Landing</a>
        </div>

        <p class="cooldown-note" @if ($cooldownSecondsRemaining <= 0) hidden @endif>
            Tombol kirim ulang akan aktif dalam <strong>{{ $cooldownSecondsRemaining }}</strong> detik.
        </p>
    </div>
</section>
