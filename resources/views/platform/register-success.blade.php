<!doctype html>
<html class="no-js" lang="id">
<head>
    <meta name="referrer" content="no-referrer">
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>POS|Platform Registration Success</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="manifest" href="{{ asset('ecohosting-main/site.webmanifest') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('ecohosting-main/assets/img/favicon.ico') }}">

    <link rel="stylesheet" href="{{ asset('ecohosting-main/assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('ecohosting-main/assets/css/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{ asset('ecohosting-main/assets/css/slicknav.css') }}">
    <link rel="stylesheet" href="{{ asset('ecohosting-main/assets/css/flaticon.css') }}">
    <link rel="stylesheet" href="{{ asset('ecohosting-main/assets/css/progressbar_barfiller.css') }}">
    <link rel="stylesheet" href="{{ asset('ecohosting-main/assets/css/gijgo.css') }}">
    <link rel="stylesheet" href="{{ asset('ecohosting-main/assets/css/animate.min.css') }}">
    <link rel="stylesheet" href="{{ asset('ecohosting-main/assets/css/animated-headline.css') }}">
    <link rel="stylesheet" href="{{ asset('ecohosting-main/assets/css/magnific-popup.css') }}">
    <link rel="stylesheet" href="{{ asset('ecohosting-main/assets/css/fontawesome-all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('ecohosting-main/assets/css/themify-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('ecohosting-main/assets/css/slick.css') }}">
    <link rel="stylesheet" href="{{ asset('ecohosting-main/assets/css/nice-select.css') }}">
    <link rel="stylesheet" href="{{ asset('ecohosting-main/assets/css/style.css') }}">
    <style>
        .brand-mark {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .brand-mark-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 54px;
            height: 54px;
            padding: 0 16px;
            border-radius: 16px;
            background: linear-gradient(135deg, #8b62f3, #ec63ef);
            color: #fff;
            font-size: 24px;
            font-weight: 700;
            box-shadow: 0 14px 26px rgba(156, 91, 240, 0.22);
        }

        .brand-mark-text {
            display: flex;
            flex-direction: column;
            line-height: 1.05;
        }

        .brand-mark-title {
            color: #fff;
            font-size: 20px;
            font-weight: 800;
            letter-spacing: 0.08em;
        }

        .brand-mark-subtitle {
            color: rgba(255, 255, 255, 0.68);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.24em;
            text-transform: uppercase;
        }

        .success-shell {
            min-height: 100vh;
            padding: 12px 12px;
            display: flex;
            align-items: center;
            position: relative;
        }

        .success-shell::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(39, 15, 47, 0.84), rgba(22, 10, 40, 0.7));
        }

        .success-card {
            position: relative;
            z-index: 1;
            width: min(1020px, 100%);
            margin: 0 auto;
            border-radius: 20px;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 18px 48px rgba(19, 8, 30, 0.22);
        }

        .success-layout {
            display: grid;
            grid-template-columns: 0.9fr 1.1fr;
            min-height: 580px;
        }

        .success-aside {
            padding: 22px 22px;
            color: #fff;
            background:
                linear-gradient(180deg, rgba(86, 33, 120, 0.86), rgba(47, 16, 66, 0.94)),
                url('{{ asset('ecohosting-main/assets/img/login-bg.jpg') }}') center/cover no-repeat;
        }

        .success-brand img {
            max-width: 102px;
        }

        .success-eyebrow {
            display: inline-block;
            margin-top: 14px;
            margin-bottom: 12px;
            padding: 6px 11px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.14);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .success-aside h1 {
            margin: 0 0 12px;
            color: #fff;
            font-size: 28px;
            line-height: 1.12;
        }

        .success-aside p {
            color: rgba(255, 255, 255, 0.82);
            line-height: 1.55;
            font-size: 13px;
        }

        .success-points {
            margin-top: 16px;
            display: grid;
            gap: 8px;
        }

        .success-point {
            display: flex;
            gap: 9px;
            align-items: flex-start;
            padding: 10px 11px;
            border-radius: 13px;
            background: rgba(255, 255, 255, 0.08);
        }

        .success-point i {
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 9px;
            background: rgba(255, 255, 255, 0.14);
            font-size: 12px;
        }

        .success-point strong {
            display: block;
            margin-bottom: 2px;
            font-size: 13px;
            color: #fff;
        }

        .success-point span {
            display: block;
            color: rgba(255, 255, 255, 0.74);
            font-size: 11px;
            line-height: 1.45;
        }

        .success-panel {
            padding: 20px 20px 18px;
        }

        .success-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 12px;
        }

        .success-topbar h2 {
            margin: 0;
            font-size: 23px;
        }

        .success-topbar p {
            margin: 4px 0 0;
            font-size: 12px;
            line-height: 1.45;
            color: #6c7280;
        }

        .success-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #7a3df0;
            font-weight: 700;
            white-space: nowrap;
            transition: transform 0.2s ease, color 0.2s ease, opacity 0.2s ease;
        }

        .success-back:hover {
            transform: translateX(-2px);
            color: #5e2fc2;
            opacity: 1;
        }

        .success-code,
        .progress-panel,
        .status-panel,
        .mail-note,
        .success-note {
            margin-bottom: 10px;
            padding: 10px 12px;
            border-radius: 13px;
            font-size: 12px;
            line-height: 1.5;
        }

        .success-code {
            border: 1px solid rgba(122, 61, 240, 0.16);
            background: rgba(122, 61, 240, 0.06);
            color: #442178;
        }

        .progress-panel {
            border: 1px solid rgba(122, 61, 240, 0.12);
            background: linear-gradient(135deg, rgba(122, 61, 240, 0.06), rgba(236, 99, 239, 0.06));
        }

        .processing-marquee {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
            padding: 11px 12px;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.84);
            border: 1px solid rgba(122, 61, 240, 0.12);
        }

        .processing-marquee.hidden {
            display: none;
        }

        .processing-spinner {
            position: relative;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            border: 3px solid rgba(122, 61, 240, 0.16);
            border-top-color: #8b62f3;
            animation: processing-spin 0.9s linear infinite;
            flex: 0 0 auto;
        }

        .processing-copy {
            min-width: 0;
        }

        .processing-copy strong {
            display: block;
            color: #442178;
            font-size: 12px;
            line-height: 1.45;
        }

        .processing-copy span {
            display: block;
            margin-top: 2px;
            color: #6f6580;
            font-size: 11px;
            line-height: 1.45;
        }

        .processing-dots::after {
            content: '';
            display: inline-block;
            width: 1.3em;
            text-align: left;
            animation: processing-dots 1.2s steps(4, end) infinite;
            overflow: hidden;
            vertical-align: bottom;
        }

        .progress-visual {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .progress-ring {
            --progress-angle: 18deg;
            width: 92px;
            height: 92px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background:
                radial-gradient(circle at center, #ffffff 0 58%, transparent 59%),
                conic-gradient(#8b62f3 0deg, #ec63ef var(--progress-angle), rgba(139, 98, 243, 0.12) var(--progress-angle), rgba(139, 98, 243, 0.12) 360deg);
            box-shadow: inset 0 0 0 1px rgba(122, 61, 240, 0.08);
            flex: 0 0 auto;
        }

        .progress-ring strong {
            color: #442178;
            font-size: 20px;
            line-height: 1;
        }

        .progress-copy {
            min-width: 0;
        }

        .progress-copy h3 {
            margin: 0 0 6px;
            font-size: 16px;
            color: #442178;
        }

        .progress-copy p {
            margin: 0;
            font-size: 12px;
            line-height: 1.5;
            color: #675b79;
        }

        .progress-bar {
            height: 10px;
            margin-top: 14px;
            border-radius: 999px;
            overflow: hidden;
            background: rgba(122, 61, 240, 0.1);
        }

        .progress-bar-fill {
            width: 0;
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, #8b62f3, #ec63ef);
            transition: width 0.35s ease;
        }

        .progress-steps {
            margin-top: 12px;
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 8px;
        }

        .progress-step {
            padding: 9px 10px;
            border-radius: 11px;
            background: rgba(255, 255, 255, 0.78);
            border: 1px solid rgba(122, 61, 240, 0.08);
        }

        .progress-step strong {
            display: block;
            margin-bottom: 2px;
            font-size: 11px;
            color: #442178;
        }

        .progress-step span {
            display: block;
            font-size: 10px;
            line-height: 1.4;
            color: #766b86;
        }

        .status-panel {
            border: 1px solid #eadff4;
            background: #fbf9fe;
        }

        .status-badge {
            display: inline-block;
            padding: 7px 11px;
            border-radius: 999px;
            background: rgba(122, 61, 240, 0.12);
            color: #5d36b0;
            font-weight: 700;
            font-size: 11px;
        }

        .status-badge.complete {
            background: rgba(54, 167, 102, 0.14);
            color: #1f7a46;
        }

        .status-badge.failed {
            background: rgba(219, 83, 83, 0.14);
            color: #b13737;
        }

        .status-log {
            margin: 10px 0 0;
            padding-left: 18px;
            color: #61566f;
            line-height: 1.5;
            font-size: 12px;
        }

        .retry-action,
        .success-cta {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 185px;
            height: 44px;
            padding: 0 16px;
            border: 0;
            border-radius: 13px;
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            background: linear-gradient(135deg, #8b62f3, #ec63ef);
            box-shadow: 0 12px 26px rgba(156, 91, 240, 0.22);
            transition: transform 0.22s ease, box-shadow 0.22s ease, filter 0.22s ease;
        }

        .retry-action:hover,
        .success-cta:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 30px rgba(156, 91, 240, 0.28);
            filter: saturate(1.08);
            color: #fff;
        }

        .retry-action {
            display: none;
            margin-top: 10px;
        }

        .retry-action.visible {
            display: inline-flex;
        }

        .helper-text,
        .redirect-note {
            margin-top: 10px;
            font-size: 11px;
            line-height: 1.45;
            color: #7c8091;
        }

        .redirect-note {
            display: none;
            padding: 9px 11px;
            border-radius: 12px;
            background: rgba(54, 167, 102, 0.1);
            border: 1px solid rgba(54, 167, 102, 0.14);
            color: #2c6d46;
        }

        .redirect-note.visible {
            display: block;
        }

        .stuck-note {
            display: none;
            margin-top: 10px;
            padding: 9px 11px;
            border-radius: 12px;
            background: rgba(235, 169, 71, 0.1);
            border: 1px solid rgba(235, 169, 71, 0.16);
            color: #8c6427;
            font-size: 11px;
            line-height: 1.5;
        }

        .stuck-note.visible {
            display: block;
        }

        .mail-note.sent {
            background: rgba(54, 167, 102, 0.1);
            border: 1px solid rgba(54, 167, 102, 0.14);
            color: #2c6d46;
        }

        .mail-note.pending {
            background: rgba(235, 169, 71, 0.1);
            border: 1px solid rgba(235, 169, 71, 0.16);
            color: #8c6427;
        }

        .success-note {
            background: rgba(122, 61, 240, 0.05);
            border: 1px solid rgba(122, 61, 240, 0.12);
            color: #5e5470;
        }

        .success-note strong {
            color: #442178;
        }

        .success-note code {
            display: block;
            margin-top: 8px;
            padding: 9px 11px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.66);
            border: 1px solid rgba(122, 61, 240, 0.12);
            color: #442178;
            word-break: break-all;
        }

        .success-links {
            margin-top: 12px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .success-login-url {
            font-size: 12px;
            color: #5e5470;
            line-height: 1.5;
        }

        .success-back,
        .brand-mark,
        .success-point,
        .progress-step {
            transition: transform 0.18s ease, box-shadow 0.18s ease, background-color 0.18s ease;
        }

        .success-point:hover,
        .progress-step:hover {
            transform: translateY(-1px);
        }

        @media (max-width: 1199px) {
            .success-layout {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 767px) {
            .success-shell {
                padding: 10px;
            }

            .success-aside,
            .success-panel {
                padding: 16px 14px;
            }

            .success-topbar {
                flex-direction: column;
                align-items: flex-start;
            }

            .success-aside h1 {
                font-size: 24px;
            }

            .success-links {
                flex-direction: column;
            }

            .progress-visual {
                flex-direction: column;
                align-items: flex-start;
            }

            .progress-steps {
                grid-template-columns: 1fr 1fr;
            }

            .success-cta,
            .retry-action {
                width: 100%;
                min-width: 0;
            }
        }

        @keyframes processing-spin {
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes processing-dots {
            0% {
                content: '';
            }
            25% {
                content: '.';
            }
            50% {
                content: '..';
            }
            75% {
                content: '...';
            }
            100% {
                content: '';
            }
        }
    </style>
</head>
<body>
    <main class="success-shell">
        <div class="success-card">
            <div class="success-layout">
                <aside class="success-aside">
                    <div class="success-brand">
                        <a href="{{ route('platform.landing') }}" class="brand-mark" aria-label="RWP POS">
                            <span class="brand-mark-badge"><i class="fas fa-cash-register"></i></span>
                            <span class="brand-mark-text">
                                <span class="brand-mark-title">RWP POS</span>
                                <span class="brand-mark-subtitle">Retail Platform</span>
                            </span>
                        </a>
                    </div>
                    <span class="success-eyebrow">POS Platform</span>
                    <h1>Tenant sudah tercatat dan onboarding sedang berjalan.</h1>
                    <p>Tone halaman ini diselaraskan dengan register baru: ringkas, jelas, dan fokus ke langkah berikutnya sampai tenant siap dipakai.</p>

                    <div class="success-points">
                        <div class="success-point">
                            <i class="fas fa-building"></i>
                            <div>
                                <strong>Data inti sudah masuk</strong>
                                <span>Tenant, owner, usaha, dan cabang awal sudah tercatat di sistem.</span>
                            </div>
                        </div>
                        <div class="success-point">
                            <i class="fas fa-layer-group"></i>
                            <div>
                                <strong>Progress tetap dipantau</strong>
                                <span>Setelah proses terpicu, tab ini boleh ditutup dan statusnya bisa dibuka lagi nanti.</span>
                            </div>
                        </div>
                        <div class="success-point">
                            <i class="fas fa-shield-alt"></i>
                            <div>
                                <strong>Siap lanjut ke tenant login</strong>
                                <span>Saat provisioning selesai, halaman akan bantu arahkan ke login tenant.</span>
                            </div>
                        </div>
                    </div>
                </aside>

                <section class="success-panel">
                    <div class="success-topbar">
                        <div>
                            <h2>Progress Tenant</h2>
                            <p>Pantau proses onboarding tenant dari sini sampai akun dan tenant benar-benar siap dipakai.</p>
                        </div>
                        <a href="{{ url('/') }}" class="success-back">
                            <i class="fas fa-arrow-left"></i>
                            Kembali ke landing
                        </a>
                    </div>

                    <div class="success-code">
                        Kode tenant: <strong>{{ $tenantCode }}</strong>
                    </div>

                    <div class="progress-panel">
                        <div id="processing-marquee" class="processing-marquee">
                            <span class="processing-spinner" aria-hidden="true"></span>
                            <div class="processing-copy">
                                <strong class="processing-dots">Sedang memproses pendaftaran Anda, mohon tunggu sebentar</strong>
                                <span>Sistem sedang menyiapkan database tenant, akun owner, dan finalisasi onboarding secara otomatis.</span>
                            </div>
                        </div>
                        <div class="progress-visual">
                            <div id="progress-ring" class="progress-ring" style="--progress-angle: 18deg;">
                                <strong id="progress-percent">5%</strong>
                            </div>
                            <div class="progress-copy">
                                <h3 id="progress-title">Menyiapkan onboarding tenant</h3>
                                <p id="progress-description">Sistem sedang memulai provisioning tenant dan akan melanjutkan sampai akun siap dipakai.</p>
                            </div>
                        </div>
                        <div class="progress-bar" aria-hidden="true">
                            <div id="progress-bar-fill" class="progress-bar-fill"></div>
                        </div>
                        <div class="progress-steps">
                            <div class="progress-step">
                                <strong>1. Registrasi</strong>
                                <span>Data tenant diterima platform.</span>
                            </div>
                            <div class="progress-step">
                                <strong>2. Database</strong>
                                <span>Database tenant disiapkan otomatis.</span>
                            </div>
                            <div class="progress-step">
                                <strong>3. Bootstrap App</strong>
                                <span>Schema app dan owner awal dibuat.</span>
                            </div>
                            <div class="progress-step">
                                <strong>4. Selesai</strong>
                                <span>Tenant aktif dan siap login.</span>
                            </div>
                        </div>
                    </div>

                    <div class="status-panel">
                        <div
                            id="bootstrap-status"
                            class="status-badge {{ $bootstrapStatus === 'completed' ? 'complete' : ($bootstrapStatus === 'failed' ? 'failed' : '') }}"
                        >
                            {{ $bootstrapStatus === 'completed' ? 'Tenant siap digunakan' : ($bootstrapStatus === 'failed' ? 'Provisioning butuh pengecekan' : ($bootstrapStatus === 'blocked' ? 'Menunggu pembayaran' : 'Sedang menyiapkan tenant...')) }}
                        </div>
                        <ol id="bootstrap-log" class="status-log">
                            <li>Pendaftaran diterima.</li>
                            @if (in_array($bootstrapStatus, ['processing', 'retry_wait'], true))
                                <li>Provisioning tenant sedang dijalankan otomatis.</li>
                            @elseif ($bootstrapStatus === 'blocked')
                                <li>Setup tenant akan dimulai setelah pembayaran tervalidasi.</li>
                            @elseif ($bootstrapStatus === 'failed')
                                <li>Provisioning tenant terakhir gagal dan perlu pengecekan.</li>
                            @else
                                <li>Auto onboarding tenant sudah selesai.</li>
                            @endif
                        </ol>
                        <button id="retry-bootstrap" type="button" class="retry-action">Coba provisioning lagi</button>
                        <p id="bootstrap-helper" class="helper-text">
                            {{ $bootstrapDispatched ? 'Auto onboarding sudah dipicu dari backend. Tab ini boleh ditutup, lalu buka lagi halaman status ini kapan saja untuk cek progres terbaru.' : 'Halaman ini sedang memicu provisioning tenant secara otomatis. Biarkan terbuka sebentar sampai prosesnya terpicu, setelah itu tab boleh ditutup.' }}
                        </p>
                        <div id="stuck-note" class="stuck-note">
                            Proses masih berjalan lebih lama dari biasanya. Sistem sedang mencoba melanjutkan onboarding otomatis di background. Jika tetap berhenti lama, gunakan tombol retry.
                        </div>
                        <div id="redirect-note" class="redirect-note">
                            Tenant sudah siap. Halaman akan mengarahkan ke login tenant dalam beberapa detik.
                        </div>
                    </div>

                    <div class="success-note">
                        Simpan kode tenant ini untuk referensi. Jika browser ditutup setelah proses terpicu, onboarding tenant tetap lanjut di backend dan bisa dipantau lagi dari halaman status ini.
                    </div>

                    <div class="mail-note {{ session('registration_email_sent') ? 'sent' : 'pending' }}">
                        @if (session('registration_email_sent'))
                            Email konfirmasi sudah dikirim ke owner untuk membantu melanjutkan onboarding tenant.
                        @else
                            Email konfirmasi belum berhasil dikirim otomatis. Tenant tetap diproses dan Anda masih bisa lanjut dari halaman status ini.
                        @endif
                    </div>

                    @if ($tenantLoginUrl)
                        <div class="success-login-url">
                            URL login tenant: <strong>{{ $tenantLoginUrl }}</strong><br>
                            Jika halaman login belum bisa dipakai segera, tunggu sebentar lalu refresh kembali.
                        </div>
                    @endif

                    @if (app()->environment('local') && $tenantLoginUrl)
                        @php($tenantHost = parse_url((string) $tenantLoginUrl, PHP_URL_HOST))
                        @if ($tenantHost)
                            <div class="success-note" style="margin-top:12px;">
                                <strong>Catatan local Windows</strong><br>
                                Jika subdomain tenant belum resolve di browser, tambahkan host ini ke file
                                <strong>C:\Windows\System32\drivers\etc\hosts</strong>:
                                <code>127.0.0.1 {{ $tenantHost }}</code>
                            </div>
                        @endif
                    @endif

                    <div class="success-links">
                        <a href="{{ url('/') }}" class="success-cta">Kembali ke landing</a>
                        @if ($tenantLoginUrl)
                            <a href="{{ $tenantLoginUrl }}" class="success-cta" rel="noreferrer">Buka Login Tenant</a>
                        @endif
                    </div>
                </section>
            </div>
        </div>
    </main>

    <script>
        (() => {
            const statusBadge = document.getElementById('bootstrap-status');
            const logList = document.getElementById('bootstrap-log');
            const retryButton = document.getElementById('retry-bootstrap');
            const helperText = document.getElementById('bootstrap-helper');
            const bootstrapUrl = @json($bootstrapUrl);
            const statusUrl = @json($statusUrl);
            const tenantLoginUrl = @json($tenantLoginUrl);
            const successUrl = @json(url()->current());
            const csrfToken = @json(csrf_token());
            const bootstrapDispatched = @json($bootstrapDispatched);
            const redirectNote = document.getElementById('redirect-note');
            const draftKey = 'platform_registration_draft_v1';
            const draftServerKey = 'platform_registration_server_draft_v1';
            const registrationKey = 'platform_registration_progress_v1';
            const progressRing = document.getElementById('progress-ring');
            const progressPercent = document.getElementById('progress-percent');
            const progressTitle = document.getElementById('progress-title');
            const progressDescription = document.getElementById('progress-description');
            const progressBarFill = document.getElementById('progress-bar-fill');
            const processingMarquee = document.getElementById('processing-marquee');
            const stuckNote = document.getElementById('stuck-note');
            let currentStatus = @json($bootstrapStatus);
            let latestLogs = @json($initialLogs);
            let pollingHandle = null;
            let bootstrapRequestInFlight = false;
            let redirectHandle = null;
            let stuckNoteHandle = null;
            const pollingIntervalInMs = 5000;

            try {
                window.localStorage.removeItem(draftKey);
                window.localStorage.removeItem(draftServerKey);
            } catch (error) {
                // Ignore storage failures and keep the status page working.
            }

            const labels = {
                processing: 'Sedang menyiapkan tenant...',
                retry_wait: 'Menunggu percobaan ulang otomatis...',
                blocked: 'Menunggu pembayaran',
                completed: 'Tenant siap digunakan',
                failed: 'Provisioning butuh pengecekan',
            };

            const stepProgressMap = {
                registration_received: 12,
                auto_onboarding_requested: 24,
                database_provisioning: 52,
                tenant_ready: 66,
                tenant_app_bootstrap: 86,
                auto_onboarding_completed: 100,
                tenant_ready_email_sent: 100,
            };

            function deriveProgress(status, logs) {
                if (status === 'completed') {
                    return {
                        percent: 100,
                        title: 'Tenant siap digunakan',
                        description: 'Provisioning tenant sudah selesai. Sistem akan membantu mengarahkan ke login tenant.',
                    };
                }

                if (status === 'failed') {
                    const fallbackLog = Array.isArray(logs) && logs.length > 0 ? logs[logs.length - 1] : null;

                    return {
                        percent: 92,
                        title: 'Provisioning perlu pengecekan',
                        description: fallbackLog?.message || 'Sebagian besar proses sudah berjalan, tetapi ada langkah yang perlu diulang atau dicek dari backend.',
                    };
                }

                const resolvedLogs = Array.isArray(logs) ? logs : [];
                let percent = 8;
                let title = 'Menyiapkan onboarding tenant';
                let description = 'Sistem sedang memulai provisioning tenant dan akan melanjutkan sampai akun siap dipakai.';

                resolvedLogs.forEach((log) => {
                    const step = log?.step;
                    const logStatus = log?.status;
                    const mapped = stepProgressMap[step] ?? percent;

                    if (typeof mapped === 'number') {
                        percent = Math.max(percent, logStatus === 'failed' ? Math.max(28, mapped - 8) : mapped);
                    }

                    if (typeof log?.message === 'string' && log.message.trim() !== '') {
                        description = log.message;
                    }

                    if (step === 'registration_received') {
                        title = 'Registrasi tenant sudah masuk. Mohon tunggu sampai prosesnya selesai...';
                    } else if (step === 'database_provisioning' || step === 'tenant_ready') {
                        title = 'Database tenant sedang disiapkan otomatis. Mohon tunggu sampai prosesnya selesai...';
                    } else if (step === 'tenant_app_bootstrap') {
                        title = logStatus === 'completed'
                            ? 'Tenant app selesai dibootstrap'
                            : 'Pembuatan tenant app sedang berjalan. Mohon tunggu sampai prosesnya selesai...';
                    } else if (step === 'auto_onboarding_completed') {
                        title = 'Finalisasi tenant sedang diselesaikan';
                    }
                });

                return {
                    percent: Math.min(Math.max(percent, 0), 99),
                    title,
                    description,
                };
            }

            function renderProgress(status, logs) {
                latestLogs = Array.isArray(logs) ? logs : latestLogs;
                const progress = deriveProgress(status, latestLogs);
                const angle = Math.round((progress.percent / 100) * 360);

                progressRing?.style.setProperty('--progress-angle', `${angle}deg`);

                if (progressPercent) {
                    progressPercent.textContent = `${progress.percent}%`;
                }

                if (progressTitle) {
                    progressTitle.textContent = progress.title;
                }

                if (progressDescription) {
                    progressDescription.textContent = progress.description;
                }

                if (progressBarFill) {
                    progressBarFill.style.width = `${progress.percent}%`;
                }
            }

            function setStatus(status) {
                currentStatus = status;
                statusBadge.textContent = labels[status] ?? labels.processing;
                statusBadge.className = 'status-badge';

                if (status === 'completed') {
                    statusBadge.classList.add('complete');
                } else if (status === 'failed') {
                    statusBadge.classList.add('failed');
                }

                retryButton.classList.toggle('visible', status === 'failed');
                processingMarquee?.classList.toggle('hidden', !['processing', 'retry_wait'].includes(status));
                persistProgress(status);
                renderProgress(status, latestLogs);
                updateStuckHint(status);

                if (status === 'completed') {
                    scheduleRedirect();
                } else {
                    clearRedirect();
                }
            }

            function persistProgress(status) {
                try {
                    window.localStorage.setItem(registrationKey, JSON.stringify({
                        tenant_code: @json($tenantCode),
                        status,
                        success_url: successUrl,
                        login_url: tenantLoginUrl,
                        updated_at: new Date().toISOString(),
                    }));
                } catch (error) {
                    // Ignore local storage failures and keep the status page working.
                }
            }

            function clearRedirect() {
                if (redirectHandle !== null) {
                    window.clearTimeout(redirectHandle);
                    redirectHandle = null;
                }

                if (redirectNote) {
                    redirectNote.classList.remove('visible');
                }
            }

            function clearStuckHint() {
                if (stuckNoteHandle !== null) {
                    window.clearTimeout(stuckNoteHandle);
                    stuckNoteHandle = null;
                }

                stuckNote?.classList.remove('visible');
            }

            function updateStuckHint(status) {
                clearStuckHint();

                if (!['processing', 'retry_wait'].includes(status)) {
                    return;
                }

                stuckNoteHandle = window.setTimeout(() => {
                    if (['processing', 'retry_wait'].includes(currentStatus)) {
                        stuckNote?.classList.add('visible');
                    }
                }, 20000);
            }

            function scheduleRedirect() {
                if (!tenantLoginUrl) {
                    return;
                }

                clearRedirect();

                if (redirectNote) {
                    redirectNote.classList.add('visible');
                }

                redirectHandle = window.setTimeout(() => {
                    window.location.href = tenantLoginUrl;
                }, 4000);
            }

            function renderLogs(logs) {
                if (!Array.isArray(logs) || logs.length === 0) {
                    return;
                }

                latestLogs = logs;
                logList.innerHTML = '';

                logs.forEach((log) => {
                    const item = document.createElement('li');
                    const timestamp = log.created_at
                        ? new Date(log.created_at).toLocaleString('id-ID')
                        : null;
                    item.textContent = timestamp ? `${log.message} (${timestamp})` : log.message;
                    logList.appendChild(item);
                });
            }

            async function fetchStatus() {
                const response = await fetch(statusUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',
                });

                if (!response.ok) {
                    throw new Error('Gagal mengambil status provisioning tenant.');
                }

                const data = await response.json();
                setStatus(data.status ?? 'processing');
                renderLogs(data.logs ?? []);
                renderProgress(data.status ?? 'processing', data.logs ?? []);

                if ((data.status ?? 'processing') === 'completed') {
                    helperText.textContent = 'Provisioning tenant selesai. Jika subdomain tenant belum langsung aktif di local, cek hosts file Windows.';
                    stopPolling();
                } else if ((data.status ?? 'processing') === 'failed') {
                    helperText.textContent = 'Provisioning tenant gagal di backend. Tombol retry bisa dipakai untuk memicu ulang proses.';
                    stopPolling();
                }
            }

            function startPolling() {
                stopPolling();
                pollingHandle = window.setInterval(() => {
                    fetchStatus().catch(() => {
                        helperText.textContent = 'Status provisioning belum bisa diambil sementara. Halaman akan mencoba lagi otomatis.';
                    });
                }, pollingIntervalInMs);
            }

            function stopPolling() {
                if (pollingHandle !== null) {
                    window.clearInterval(pollingHandle);
                    pollingHandle = null;
                }
            }

            async function triggerBootstrap() {
                if (bootstrapRequestInFlight) {
                    return;
                }

                bootstrapRequestInFlight = true;
                helperText.textContent = 'Sedang memproses pendaftaran Anda, mohon tunggu sebentar...';

                try {
                    const response = await fetch(bootstrapUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({}),
                    });

                    const data = await response.json().catch(() => ({}));

                    if (!response.ok && response.status !== 202) {
                        throw new Error(data.message || 'Gagal memicu provisioning tenant.');
                    }

                    setStatus(data.status ?? 'processing');
                    helperText.textContent = currentStatus === 'failed'
                        ? 'Provisioning tenant gagal. Silakan retry lagi setelah backend diperiksa.'
                        : 'Sedang memproses pendaftaran Anda, mohon tunggu sebentar...';

                    await fetchStatus();

                    if (['processing', 'retry_wait'].includes(currentStatus)) {
                        startPolling();
                    }
                } catch (error) {
                    setStatus('failed');
                    helperText.textContent = error.message || 'Provisioning tenant gagal dipicu dari browser.';
                } finally {
                    bootstrapRequestInFlight = false;
                }
            }

            retryButton.addEventListener('click', () => {
                setStatus('processing');
                triggerBootstrap();
            });

            renderLogs(latestLogs);
            renderProgress(currentStatus, latestLogs);

            if (['processing', 'retry_wait'].includes(currentStatus)) {
                persistProgress(currentStatus);
                startPolling();

                if (!bootstrapDispatched) {
                    triggerBootstrap();
                }
            } else if (currentStatus === 'failed') {
                persistProgress(currentStatus);
                retryButton.classList.add('visible');
            } else if (currentStatus === 'completed') {
                persistProgress(currentStatus);
                scheduleRedirect();
            }
        })();
    </script>
</body>
</html>
