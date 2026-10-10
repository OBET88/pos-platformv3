<!doctype html>
<html class="no-js" lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>POS|Verifikasi Email Pendaftaran</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('ecohosting-main/assets/img/favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('ecohosting-main/assets/css/bootstrap.min.css') }}">
    <style>
        body { margin: 0; font-family: 'Segoe UI', Arial, sans-serif; background: linear-gradient(135deg, rgba(39, 15, 47, 0.88), rgba(22, 10, 40, 0.76)); }
        .shell { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 18px; }
        .card { width: min(760px, 100%); background: rgba(255,255,255,.97); border-radius: 24px; box-shadow: 0 22px 60px rgba(19,8,30,.24); overflow: hidden; }
        .hero { padding: 28px 28px 20px; background: linear-gradient(180deg, rgba(86,33,120,.92), rgba(47,16,66,.98)); color: #fff; }
        .hero h1 { margin: 12px 0 10px; font-size: 30px; line-height: 1.15; }
        .hero p { margin: 0; color: rgba(255,255,255,.82); line-height: 1.6; font-size: 14px; }
        .body { padding: 22px 24px 24px; color: #554b66; }
        .note { margin-bottom: 14px; padding: 12px 14px; border-radius: 14px; background: rgba(122,61,240,.06); border: 1px solid rgba(122,61,240,.14); }
        .note strong { color: #442178; }
        .alert-box { margin-bottom: 14px; padding: 12px 14px; border-radius: 14px; font-size: 14px; line-height: 1.6; }
        .alert-box.success { background: rgba(34,197,94,.09); border: 1px solid rgba(34,197,94,.2); color: #166534; }
        .alert-box.warning { background: rgba(245,158,11,.1); border: 1px solid rgba(245,158,11,.22); color: #92400e; }
        .cooldown-note { margin-top: 10px; font-size: 13px; color: #7c6f91; }
        .actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 18px; }
        .actions form { margin: 0; }
        .cta { display: inline-flex; align-items: center; justify-content: center; min-width: 200px; height: 46px; padding: 0 16px; border-radius: 999px; text-decoration: none; font-weight: 700; transition: transform .18s ease, box-shadow .18s ease, background-color .18s ease, color .18s ease; }
        button.cta { border: 0; cursor: pointer; }
        .cta:hover { transform: translateY(-1px); }
        .cta.primary { color: #fff; background: linear-gradient(135deg,#8b62f3,#ec63ef); box-shadow: 0 18px 34px rgba(122,61,240,.2); }
        .cta.secondary { color: #5d36b0; background: rgba(122,61,240,.08); }
        .cta[disabled] { opacity: .65; cursor: not-allowed; transform: none; box-shadow: none; }
    </style>
</head>
<body>
    <main class="shell">
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
                @if (session('status'))
                    <div class="alert-box success">{{ session('status') }}</div>
                @endif
                @if (session('warning'))
                    <div class="alert-box warning">{{ session('warning') }}</div>
                @endif
                @if (session('registration_email_sent', false))
                    <p>Link verifikasi sudah kami kirim. Setelah link dibuka, sistem baru akan membuat tenant dan menjalankan onboarding otomatis seperti flow yang sudah ada sekarang.</p>
                @else
                    <p>Pengiriman email verifikasi belum terkonfirmasi dari aplikasi. Coba kirim ulang pendaftaran, lalu cek folder spam atau hubungi admin untuk verifikasi manual.</p>
                @endif
                <p>Kalau email belum masuk, cek folder spam/promotions dulu. Mengirim ulang pendaftaran dengan email yang sama tidak membuat tenant ganda. Sistem hanya memperbarui request verifikasi yang masih pending.</p>
                <div class="actions">
                    <form method="POST" action="{{ route('platform.register.resend') }}">
                        @csrf
                        <input type="hidden" name="owner_email" value="{{ $ownerEmail }}">
                        <button
                            type="submit"
                            class="cta primary"
                            id="resend-button"
                            data-cooldown-seconds="{{ (int) $cooldownSecondsRemaining }}">
                            Kirim Ulang Verifikasi
                        </button>
                    </form>
                    <a href="{{ route('platform.register.create') }}" class="cta secondary">Kembali ke Form</a>
                    <a href="{{ route('platform.help') }}" class="cta secondary">Butuh Bantuan?</a>
                    <a href="{{ url('/') }}" class="cta secondary">Kembali ke Landing</a>
                </div>
                <p class="cooldown-note" id="resend-cooldown-note" @if (($cooldownSecondsRemaining ?? 0) <= 0) hidden @endif>
                    Tombol kirim ulang akan aktif dalam <strong><span id="resend-cooldown-seconds">{{ max(0, (int) ($cooldownSecondsRemaining ?? 0)) }}</span></strong> detik.
                </p>
            </div>
        </section>
    </main>
    <script>
        (function () {
            const button = document.getElementById('resend-button');
            const note = document.getElementById('resend-cooldown-note');
            const secondsLabel = document.getElementById('resend-cooldown-seconds');

            if (!button || !note || !secondsLabel) {
                return;
            }

            let remaining = Number(button.dataset.cooldownSeconds || 0);
            const defaultLabel = 'Kirim Ulang Verifikasi';

            const render = () => {
                if (remaining > 0) {
                    button.disabled = true;
                    button.textContent = 'Kirim Ulang (' + remaining + ' dtk)';
                    note.hidden = false;
                    secondsLabel.textContent = String(remaining);
                    return;
                }

                button.disabled = false;
                button.textContent = defaultLabel;
                note.hidden = true;
            };

            render();

            if (remaining <= 0) {
                return;
            }

            const timer = window.setInterval(() => {
                remaining -= 1;
                render();

                if (remaining <= 0) {
                    window.clearInterval(timer);
                }
            }, 1000);
        })();
    </script>
</body>
</html>
