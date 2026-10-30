<!doctype html>
<html class="no-js" lang="zxx">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>POS|Platform Register</title>
    <meta name="description" content="Halaman register baru POS Platform dengan sumber asset yang sama seperti landing baru.">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="manifest" href="{{ asset('ecohosting-main/site.webmanifest') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('ecohosting-main/assets/img/favicon.ico') }}">

	<!-- CSS here -->
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

        .register-shell {
            min-height: 100vh;
            min-height: 100svh;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            position: relative;
            overflow-y: auto;
        }

        .register-shell::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(39, 15, 47, 0.82), rgba(22, 10, 40, 0.68));
        }

        .register-card {
            position: relative;
            z-index: 1;
            width: min(1080px, 100%);
            margin: 0 auto;
            border-radius: 22px;
            overflow: visible;
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 22px 60px rgba(19, 8, 30, 0.24);
        }

        .register-layout {
            display: grid;
            grid-template-columns: 0.92fr 1.28fr;
            min-height: 0;
        }

        .register-aside {
            padding: 28px 26px;
            color: #fff;
            background:
                linear-gradient(180deg, rgba(86, 33, 120, 0.86), rgba(47, 16, 66, 0.94)),
                url('{{ asset('ecohosting-main/assets/img/login-bg.jpg') }}') center/cover no-repeat;
        }

        .register-brand img {
            max-width: 112px;
        }

        .register-eyebrow {
            display: inline-block;
            margin-top: 18px;
            margin-bottom: 14px;
            padding: 7px 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.14);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .register-aside h1 {
            color: #fff;
            font-size: 31px;
            line-height: 1.12;
            margin-bottom: 12px;
        }

        .register-aside p {
            color: rgba(255, 255, 255, 0.82);
            line-height: 1.6;
            font-size: 14px;
        }

        .register-points {
            margin-top: 20px;
            display: grid;
            gap: 10px;
        }

        .register-point {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            padding: 11px 12px;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.08);
        }

        .register-point i {
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.14);
            font-size: 13px;
        }

        .register-point strong {
            display: block;
            margin-bottom: 2px;
            font-size: 14px;
            color: #fff;
        }

        .register-point span {
            display: block;
            color: rgba(255, 255, 255, 0.74);
            font-size: 12px;
            line-height: 1.45;
        }

        .register-panel {
            padding: 24px 24px 20px;
        }

        .register-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
        }

        .register-topbar h2 {
            margin: 0;
            font-size: 25px;
        }

        .register-topbar p {
            margin: 4px 0 0;
            font-size: 13px;
            line-height: 1.5;
            color: #6c7280;
        }

        .register-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #7a3df0;
            font-weight: 700;
            white-space: nowrap;
            transition: transform 0.2s ease, color 0.2s ease, opacity 0.2s ease;
        }

        .register-back:hover {
            transform: translateX(-2px);
            color: #5e2fc2;
            opacity: 1;
        }

        .register-error {
            margin-bottom: 12px;
            padding: 10px 14px;
            border-radius: 14px;
            border: 1px solid rgba(228, 74, 74, 0.16);
            background: rgba(228, 74, 74, 0.08);
            color: #a12626;
            font-size: 13px;
        }

        .register-status {
            margin-bottom: 12px;
            padding: 10px 14px;
            border-radius: 14px;
            border: 1px solid rgba(122, 61, 240, 0.14);
            background: rgba(122, 61, 240, 0.06);
            color: #5d36b0;
            font-size: 12px;
            line-height: 1.5;
        }

        .register-status[hidden] {
            display: none !important;
        }

        .register-status strong {
            color: #442178;
        }

        .register-status a {
            color: #5d36b0;
            font-weight: 700;
        }

        .register-form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px 12px;
        }

        .field-span-2 {
            grid-column: span 2;
        }

        .form-input {
            margin-bottom: 0;
            position: relative;
            z-index: 1;
        }

        .form-input:focus-within {
            z-index: 30;
        }

        .form-input label {
            display: block;
            margin-bottom: 6px;
            font-size: 12px;
            font-weight: 700;
            color: #4c1e51;
        }

        .form-input input,
        .form-input select,
        .form-input textarea {
            width: 100%;
            height: 46px;
            border: 1px solid #eadff4;
            border-radius: 14px;
            background: #fbf9fe;
            padding: 0 14px;
            color: #35203c;
            font-size: 13px;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }

        .form-input select {
            appearance: none;
            background-image:
                linear-gradient(45deg, transparent 50%, #915fec 50%),
                linear-gradient(135deg, #915fec 50%, transparent 50%);
            background-position:
                calc(100% - 20px) 19px,
                calc(100% - 14px) 19px;
            background-size: 6px 6px, 6px 6px;
            background-repeat: no-repeat;
            padding-right: 36px;
        }

        .form-input .nice-select {
            width: 100%;
            float: none;
            height: 46px;
            line-height: 46px;
            margin-bottom: 0;
            border: 1px solid #eadff4;
            border-radius: 14px;
            background: #fbf9fe;
            color: #35203c;
            font-size: 13px;
            padding-left: 14px;
            padding-right: 36px;
        }

        .form-input .nice-select::after {
            right: 16px;
        }

        .form-input .nice-select .current {
            display: block;
            margin-right: 0;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .form-input .nice-select .list {
            z-index: 80;
            width: 100%;
            max-height: 280px;
            overflow-y: auto;
            margin-top: 8px;
            border-radius: 14px;
            box-shadow: 0 18px 36px rgba(19, 8, 30, 0.18);
        }

        .form-input textarea {
            height: 76px;
            padding-top: 12px;
            resize: none;
        }

        .form-input input:focus,
        .form-input select:focus,
        .form-input textarea:focus {
            border-color: #b66cf5;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(182, 108, 245, 0.12);
        }

        .form-input input:not(:placeholder-shown),
        .form-input textarea:not(:placeholder-shown),
        .form-input select:valid {
            border-color: #d9c5ef;
            background: #fff;
        }

        .field-hint {
            margin-top: 6px;
            font-size: 11px;
            line-height: 1.4;
            color: #7c8091;
        }

        .field-live-status {
            margin-top: 8px;
            padding: 9px 11px;
            border-radius: 12px;
            font-size: 11px;
            line-height: 1.45;
            border: 1px solid rgba(122, 61, 240, 0.12);
            background: rgba(122, 61, 240, 0.05);
            color: #5d36b0;
        }

        .field-live-status[hidden] {
            display: none !important;
        }

        .field-live-status.is-success {
            border-color: rgba(34,197,94,.18);
            background: rgba(34,197,94,.08);
            color: #166534;
        }

        .field-live-status.is-warning {
            border-color: rgba(245,158,11,.24);
            background: rgba(245,158,11,.08);
            color: #92400e;
        }

        .field-live-status.is-danger {
            border-color: rgba(228,74,74,.2);
            background: rgba(228,74,74,.08);
            color: #a12626;
        }

        .field-live-status a {
            color: inherit;
            font-weight: 700;
            text-decoration: underline;
        }

        .field-error {
            display: block;
            margin-top: 5px;
            font-size: 11px;
            font-weight: 600;
            color: #cf3653;
        }

        .register-actions {
            margin-top: 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .register-submit {
            min-width: 220px;
            height: 48px;
            border: 0;
            border-radius: 14px;
            color: #fff;
            font-weight: 700;
            font-size: 13px;
            letter-spacing: 0.02em;
            background: linear-gradient(135deg, #8b62f3, #ec63ef);
            box-shadow: 0 12px 26px rgba(156, 91, 240, 0.22);
            transition: transform 0.22s ease, box-shadow 0.22s ease, filter 0.22s ease;
        }

        .register-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 30px rgba(156, 91, 240, 0.28);
            filter: saturate(1.08);
        }

        .register-submit:disabled {
            cursor: wait;
            opacity: 0.88;
            transform: none;
        }

        .register-note {
            font-size: 12px;
            line-height: 1.5;
            color: #7c8091;
            max-width: 320px;
        }

        .register-note strong {
            color: #4c1e51;
        }

        .register-flow {
            margin-bottom: 14px;
            padding: 14px 16px;
            border-radius: 16px;
            border: 1px solid rgba(122, 61, 240, 0.12);
            background: linear-gradient(135deg, rgba(122, 61, 240, 0.06), rgba(236, 99, 239, 0.05));
        }

        .register-flow-title {
            margin: 0 0 8px;
            font-size: 13px;
            font-weight: 800;
            color: #4c1e51;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .register-flow-list {
            display: grid;
            gap: 8px;
            margin: 0;
            padding-left: 18px;
            color: #6c7280;
            font-size: 12px;
            line-height: 1.55;
        }

        .register-plan-focus {
            margin-bottom: 12px;
            padding: 12px 14px;
            border-radius: 14px;
            border: 1px solid rgba(122, 61, 240, 0.12);
            background: rgba(122, 61, 240, 0.05);
            color: #5d36b0;
            font-size: 12px;
            line-height: 1.55;
        }

        .register-plan-focus strong {
            color: #442178;
        }

        .preloader-pos-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 76px;
            height: 76px;
            border-radius: 22px;
            background: linear-gradient(135deg, #8b62f3, #ec63ef);
            color: #fff;
            font-size: 34px;
            box-shadow: 0 16px 34px rgba(156, 91, 240, 0.28);
        }

        @media (min-width: 1200px) {
            .register-shell {
                padding: 10px 14px;
                overflow-y: auto;
            }

            .register-card {
                max-height: none;
            }

            .register-layout {
                grid-template-columns: 0.82fr 1.18fr;
            }

            .register-aside {
                padding: 22px 22px 20px;
            }

            .register-eyebrow {
                margin-top: 14px;
                margin-bottom: 10px;
                padding: 6px 10px;
            }

            .register-aside h1 {
                font-size: 27px;
                margin-bottom: 10px;
            }

            .register-aside p {
                font-size: 13px;
                line-height: 1.5;
            }

            .register-points {
                margin-top: 14px;
                gap: 8px;
            }

            .register-point {
                padding: 10px 11px;
            }

            .register-point i {
                width: 30px;
                height: 30px;
                font-size: 12px;
            }

            .register-point strong {
                font-size: 13px;
            }

            .register-point span {
                font-size: 11px;
                line-height: 1.35;
            }

            .register-panel {
                padding: 18px 20px 16px;
            }

            .register-topbar {
                margin-bottom: 10px;
            }

            .register-topbar h2 {
                font-size: 22px;
            }

            .register-topbar p,
            .register-status,
            .register-error,
            .register-flow-list,
            .register-plan-focus,
            .register-note {
                font-size: 11px;
            }

            .register-flow {
                margin-bottom: 10px;
                padding: 11px 13px;
            }

            .register-flow-title {
                margin-bottom: 6px;
                font-size: 12px;
            }

            .register-flow-list {
                gap: 5px;
                line-height: 1.4;
            }

            .register-plan-focus,
            .register-status,
            .register-error {
                margin-bottom: 10px;
                padding: 9px 12px;
            }

            .register-form-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 8px 10px;
            }

            .field-span-2 {
                grid-column: span 3;
            }

            .form-input label {
                margin-bottom: 4px;
                font-size: 11px;
            }

            .form-input input,
            .form-input select,
            .form-input textarea {
                height: 42px;
                padding: 0 12px;
                font-size: 12px;
            }

            .form-input .nice-select {
                height: 42px;
                line-height: 42px;
                font-size: 12px;
                padding-left: 12px;
            }

            .form-input textarea {
                height: 62px;
                padding-top: 10px;
            }

            .field-hint,
            .field-live-status,
            .field-error {
                margin-top: 4px;
                font-size: 10px;
                line-height: 1.35;
            }

            .register-actions {
                margin-top: 10px;
                gap: 10px;
            }

            .register-submit {
                min-width: 210px;
                height: 44px;
                font-size: 12px;
            }
        }

        @media (min-width: 1200px) and (max-height: 820px) {
            .register-card {
                max-height: none;
            }

            .register-layout {
                grid-template-columns: 0.76fr 1.24fr;
            }

            .register-aside {
                padding: 16px 16px 14px;
            }

            .register-panel {
                padding: 14px 16px 12px;
            }

            .register-aside h1 {
                font-size: 22px;
                line-height: 1.08;
                margin-bottom: 8px;
            }

            .register-aside p {
                font-size: 12px;
                line-height: 1.4;
            }

            .register-eyebrow {
                margin-top: 10px;
                margin-bottom: 8px;
                font-size: 10px;
                padding: 5px 9px;
            }

            .register-points {
                margin-top: 10px;
                gap: 6px;
            }

            .register-point {
                padding: 8px 9px;
                gap: 8px;
            }

            .register-point i {
                width: 26px;
                height: 26px;
                font-size: 11px;
            }

            .register-point strong {
                font-size: 12px;
                margin-bottom: 1px;
            }

            .register-point span {
                font-size: 10px;
                line-height: 1.28;
            }

            .register-point:last-child {
                display: none;
            }

            .register-topbar {
                margin-bottom: 8px;
                gap: 8px;
            }

            .register-topbar h2 {
                font-size: 19px;
            }

            .register-topbar p,
            .register-status,
            .register-error,
            .register-flow-list,
            .register-plan-focus,
            .register-note {
                font-size: 10px;
                line-height: 1.3;
            }

            .register-flow,
            .register-plan-focus,
            .register-status,
            .register-error {
                margin-bottom: 8px;
                padding: 8px 10px;
            }

            .register-flow-title {
                margin-bottom: 4px;
                font-size: 11px;
                letter-spacing: 0.04em;
            }

            .register-flow-list {
                gap: 3px;
                padding-left: 16px;
            }

            .register-form-grid {
                gap: 6px 8px;
            }

            .form-input label {
                margin-bottom: 3px;
                font-size: 10px;
            }

            .form-input input,
            .form-input select,
            .form-input textarea {
                height: 38px;
                padding: 0 11px;
                font-size: 11px;
            }

            .form-input .nice-select {
                height: 38px;
                line-height: 38px;
                font-size: 11px;
                padding-left: 11px;
            }

            .form-input textarea {
                height: 50px;
                padding-top: 8px;
            }

            .field-hint,
            .field-live-status,
            .field-error {
                margin-top: 3px;
                font-size: 9px;
                line-height: 1.25;
            }

            .register-actions {
                margin-top: 8px;
                gap: 8px;
                align-items: center;
            }

            .register-submit {
                min-width: 190px;
                height: 40px;
                font-size: 11px;
            }

            .register-note {
                max-width: 260px;
            }
        }

        @media (max-width: 1199px) {
            .register-shell {
                align-items: flex-start;
            }

            .register-layout {
                grid-template-columns: 1fr;
            }

            .register-aside {
                padding-bottom: 20px;
            }
        }

        @media (max-width: 767px) {
            .register-shell {
                padding: 10px;
            }

            .register-panel,
            .register-aside {
                padding: 18px 16px;
            }

            .register-topbar {
                flex-direction: column;
                align-items: flex-start;
            }

            .register-form-grid {
                grid-template-columns: 1fr;
            }

            .field-span-2 {
                grid-column: span 1;
            }

            .register-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .register-submit {
                width: 100%;
                min-width: 0;
            }

            .register-aside h1 {
                font-size: 26px;
            }
        }
    </style>
</head>
<body>
    <!-- ? Preloader Start -->
    <div id="preloader-active">
        <div class="preloader d-flex align-items-center justify-content-center">
            <div class="preloader-inner position-relative">
                <div class="preloader-circle"></div>
                <div class="preloader-img pere-text">
                    <span class="preloader-pos-icon" aria-label="Loading RWP POS">
                        <i class="fas fa-cash-register"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <!-- Preloader Start-->


<!-- Register -->

<main class="register-shell login-body" data-vide-bg="{{ asset('ecohosting-main/assets/img/login-bg') }}">
    <div class="register-card">
        <div class="register-layout">
            <aside class="register-aside">
                <div class="register-brand">
                        <a href="{{ route('platform.landing') }}" class="brand-mark" aria-label="RWP POS">
                            <span class="brand-mark-badge"><i class="fas fa-cash-register"></i></span>
                            <span class="brand-mark-text">
                                <span class="brand-mark-title">RWP POS</span>
                                <span class="brand-mark-subtitle">Retail Platform</span>
                            </span>
                        </a>
                </div>
                <span class="register-eyebrow">POS Platform</span>
                <h1>Daftarkan usaha baru ke RWP POS.</h1>
                <p>Form ini khusus untuk membuat tenant/usaha baru. Jika Anda owner, admin, atau kasir tenant yang sudah aktif, gunakan halaman login di domain tenant masing-masing.</p>

                <div class="register-points">
                    <div class="register-point">
                        <i class="fas fa-building"></i>
                        <div>
                            <strong>Data inti langsung lengkap</strong>
                            <span>Tenant, owner, usaha, cabang awal, dan paket dikumpulkan dalam satu halaman yang tetap ringkas.</span>
                        </div>
                    </div>
                    <div class="register-point">
                        <i class="fas fa-layer-group"></i>
                        <div>
                            <strong>Draft bisa dilanjutkan</strong>
                            <span>Kalau form terhenti di tengah jalan, data non-sensitif tetap bisa diteruskan tanpa mengulang dari awal.</span>
                        </div>
                    </div>
                    <div class="register-point">
                        <i class="fas fa-shield-alt"></i>
                        <div>
                            <strong>Verifikasi tetap aman</strong>
                            <span>Tenant baru tidak langsung aktif sebelum email owner diverifikasi dan proses bootstrap selesai.</span>
                        </div>
                    </div>
                </div>
            </aside>

            <div class="register-panel">
                <div class="register-topbar">
                    <div>
                        <h2>Daftar Tenant / Usaha Baru</h2>
                        <p>Lengkapi data bisnis untuk membuat tenant baru di RWP POS. Ini bukan form daftar akun karyawan, admin, atau kasir tenant yang sudah aktif.</p>
                    </div>
                    <a href="{{ route('platform.landing') }}" class="register-back">
                        <i class="fas fa-arrow-left"></i>
                        Kembali ke landing
                    </a>
                </div>

                @if ($errors->any())
                    <div class="register-error">
                        Periksa lagi data yang masih belum valid.
                    </div>
                @endif

                @if (session('status'))
                    <div class="register-status">
                        <strong>Status pendaftaran diperbarui.</strong>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                @if (session('warning'))
                    <div class="register-status" style="border-color: rgba(245,158,11,.22); background: rgba(245,158,11,.08); color: #92400e;">
                        <strong style="color:#7c3d12;">Perlu perhatian.</strong>
                        <span>{{ session('warning') }}</span>
                    </div>
                @endif

                <div id="server-draft-status" class="register-status" hidden>
                    <strong id="server-draft-label">Draft tersimpan otomatis.</strong>
                    <span id="server-draft-copy">Link resume ini bisa dibuka lagi dari perangkat lain untuk melanjutkan pengisian form yang sama.</span>
                    <a id="server-draft-link" href="{{ route('platform.register.create') }}" class="register-back" style="margin-left:8px;font-size:12px;">
                        <i class="fas fa-link"></i>
                        Buka Link Resume
                    </a>
                </div>

                <div id="resume-registration" class="register-status" hidden>
                    <strong id="resume-title">Ada pendaftaran yang masih berjalan.</strong>
                    <span id="resume-copy">Jika browser sempat ditutup, Anda bisa lanjut pantau progres tenant tanpa mengisi form dari awal.</span>
                    <a id="resume-link" href="{{ route('platform.register.create') }}" class="register-back" style="margin-left:8px;font-size:12px;">
                        <i class="fas fa-arrow-right"></i>
                        Lanjutkan Status
                    </a>
                </div>

                <div class="register-flow">
                    <p class="register-flow-title">Info proses</p>
                    <p class="register-note" style="max-width:none;">
                        Setelah dikirim, owner akan menerima email verifikasi. Tenant dibuat otomatis setelah email dikonfirmasi. Akun user tambahan seperti admin cabang atau kasir dibuat dari dalam dashboard tenant setelah tenant aktif.
                    </p>
                </div>

                @if(!empty($preferredPlanCode))
                    <div class="register-plan-focus">
                        <strong>Paket rekomendasi sudah disiapkan.</strong>
                        Form ini dibuka dari landing dengan paket <strong>{{ strtoupper($preferredPlanCode) }}</strong>, tapi kamu masih bisa menggantinya sebelum submit.
                    </div>
                @endif

                <form class="form-default" action="{{ route('platform.register.store') }}" method="POST">
        @csrf
        <input id="registration_draft_token" type="hidden" name="registration_draft_token" value="{{ $initialDraftToken ?? '' }}">
                    <div class="register-form-grid">
                        <div class="form-input field-span-2">
                            <label for="company_name">Nama Perusahaan / Tenant</label>
                            <input id="company_name" type="text" name="company_name" value="{{ old('company_name') }}" placeholder="PT Maju Makmur Kuliner" required>
                            <div class="field-hint">Nama ini akan menjadi identitas tenant saat onboarding dimulai.</div>
                            @error('company_name')<small class="field-error">{{ $message }}</small>@enderror
                        </div>

                        <div class="form-input">
                            <label for="owner_name">Nama Owner</label>
                            <input id="owner_name" type="text" name="owner_name" value="{{ old('owner_name') }}" placeholder="Frans" required>
                            @error('owner_name')<small class="field-error">{{ $message }}</small>@enderror
                        </div>

                        <div class="form-input">
                            <label for="owner_email">Email Owner</label>
                            <input id="owner_email" type="email" name="owner_email" value="{{ old('owner_email') }}" placeholder="owner@contoh.com" required>
                            <div class="field-hint">Email ini akan menerima link verifikasi dan menjadi akun utama tenant baru.</div>
                            <div id="owner-email-live-status" class="field-live-status" hidden></div>
                            <div id="owner-email-live-action" class="field-hint" hidden></div>
                            @error('owner_email')<small class="field-error">{{ $message }}</small>@enderror
                        </div>

                        <div class="form-input">
                            <label for="owner_password">Password Awal Owner</label>
                            <input id="owner_password" type="password" name="owner_password" placeholder="Minimal 8 karakter" required>
                            <div class="field-hint">Password ini dipakai untuk akun awal owner setelah tenant selesai diproses.</div>
                            @error('owner_password')<small class="field-error">{{ $message }}</small>@enderror
                        </div>

                        <div class="form-input">
                            <label for="owner_phone">No. HP</label>
                            <input id="owner_phone" type="text" name="owner_phone" value="{{ old('owner_phone') }}" placeholder="0812xxxxxx">
                            @error('owner_phone')<small class="field-error">{{ $message }}</small>@enderror
                        </div>

                        <div class="form-input">
                            <label for="business_name">Nama Usaha Awal</label>
                            <input id="business_name" type="text" name="business_name" value="{{ old('business_name', old('company_name')) }}" placeholder="Usaha Utama" required>
                            @error('business_name')<small class="field-error">{{ $message }}</small>@enderror
                        </div>

                        <div class="form-input">
                            <label for="branch_name">Nama Cabang Awal</label>
                            <input id="branch_name" type="text" name="branch_name" value="{{ old('branch_name', 'Cabang 1') }}" placeholder="Cabang 1" required>
                            @error('branch_name')<small class="field-error">{{ $message }}</small>@enderror
                        </div>

                        <div class="form-input">
                            <label for="tenant_code">Kode Subdomain (opsional)</label>
                            <input id="tenant_code" type="text" name="tenant_code" value="{{ old('tenant_code') }}" placeholder="berkah" maxlength="20" pattern="[a-z0-9]([a-z0-9-]{1,18}[a-z0-9])?">
                            <div class="field-hint">Biarkan kosong untuk dibuat otomatis dari nama perusahaan. Contoh: berkah</div>
                            @error('tenant_code')<small class="field-error">{{ $message }}</small>@enderror
                        </div>

                        <div class="form-input">
                            <label for="plan_code">Paket</label>
                            <select id="plan_code" name="plan_code" required>
                                <option value="">Pilih paket</option>
                                @foreach ($plans as $plan)
                                    <option value="{{ $plan->code }}" @selected(old('plan_code', $preferredPlanCode ?? '') === $plan->code)>
                                        {{ $plan->name }} - {{ $plan->code === 'trial' ? 'Gratis 14 hari' : ($plan->price > 0 ? 'Rp ' . number_format((float) $plan->price, 0, ',', '.') . '/' . $plan->billing_cycle : 'Hubungi Sales') }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="field-hint">Paket menentukan batas awal fitur dan menu tenant sejak pertama kali aktif.</div>
                            @error('plan_code')<small class="field-error">{{ $message }}</small>@enderror
                        </div>

                        <div class="form-input field-span-2">
                            <label for="notes">Catatan Tambahan</label>
                            <textarea id="notes" name="notes" placeholder="Contoh: butuh 3 cabang, ingin onboarding minggu ini">{{ old('notes') }}</textarea>
                            <div class="field-hint">Opsional. Cocok untuk kebutuhan outlet, target go-live, atau catatan onboarding khusus.</div>
                            @error('notes')<small class="field-error">{{ $message }}</small>@enderror
                        </div>
                    </div>

                    <div class="register-actions">
                        <div>
                            <button id="submit-registration" type="submit" class="register-submit">Kirim & Minta Verifikasi</button>
                        </div>
                        <div>
                            <div class="register-note"><strong>Draft tersimpan otomatis.</strong> Pastikan email owner aktif karena link verifikasi dan status awal pendaftaran akan dikirim ke sana.</div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>


    <script src="{{ asset('ecohosting-main/assets/js/vendor/modernizr-3.5.0.min.js') }}"></script>
    <!-- Jquery, Popper, Bootstrap -->
    <script src="{{ asset('ecohosting-main/assets/js/vendor/jquery-1.12.4.min.js') }}"></script>
    <script src="{{ asset('ecohosting-main/assets/js/popper.min.js') }}"></script>
    <script src="{{ asset('ecohosting-main/assets/js/bootstrap.min.js') }}"></script>
    <!-- Jquery Mobile Menu -->
    <script src="{{ asset('ecohosting-main/assets/js/jquery.slicknav.min.js') }}"></script>

    <!-- Video bg -->
    <script src="{{ asset('ecohosting-main/assets/js/jquery.vide.js') }}"></script>

    <!-- Jquery Slick , Owl-Carousel Plugins -->
    <script src="{{ asset('ecohosting-main/assets/js/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('ecohosting-main/assets/js/slick.min.js') }}"></script>
    <!-- One Page, Animated-HeadLin -->
    <script src="{{ asset('ecohosting-main/assets/js/wow.min.js') }}"></script>
    <script src="{{ asset('ecohosting-main/assets/js/animated.headline.js') }}"></script>
    <script src="{{ asset('ecohosting-main/assets/js/jquery.magnific-popup.js') }}"></script>

    <!-- Date Picker -->
    <script src="{{ asset('ecohosting-main/assets/js/gijgo.min.js') }}"></script>
    <!-- Nice-select, sticky -->
    <script src="{{ asset('ecohosting-main/assets/js/jquery.nice-select.min.js') }}"></script>
    <script src="{{ asset('ecohosting-main/assets/js/jquery.sticky.js') }}"></script>
    <!-- Progress -->
    <script src="{{ asset('ecohosting-main/assets/js/jquery.barfiller.js') }}"></script>
    
    <!-- counter , waypoint,Hover Direction -->
    <script src="{{ asset('ecohosting-main/assets/js/jquery.counterup.min.js') }}"></script>
    <script src="{{ asset('ecohosting-main/assets/js/waypoints.min.js') }}"></script>
    <script src="{{ asset('ecohosting-main/assets/js/jquery.countdown.min.js') }}"></script>
    <script src="{{ asset('ecohosting-main/assets/js/hover-direction-snake.min.js') }}"></script>

    <!-- contact js -->
    <script src="{{ asset('ecohosting-main/assets/js/contact.js') }}"></script>
    <script src="{{ asset('ecohosting-main/assets/js/jquery.form.js') }}"></script>
    <script src="{{ asset('ecohosting-main/assets/js/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('ecohosting-main/assets/js/mail-script.js') }}"></script>
    <script src="{{ asset('ecohosting-main/assets/js/jquery.ajaxchimp.min.js') }}"></script>
    
    <!-- Jquery Plugins, main Jquery -->	
    <script src="{{ asset('ecohosting-main/assets/js/plugins.js') }}"></script>
    <script src="{{ asset('ecohosting-main/assets/js/main.js') }}"></script>
    <script>
        (() => {
            const draftKey = 'platform_registration_draft_v1';
            const draftServerKey = 'platform_registration_server_draft_v1';
            const registrationKey = 'platform_registration_progress_v1';
            const csrfToken = @json(csrf_token());
            const initialDraftToken = @json($initialDraftToken);
            const draftSaveUrl = @json($draftSaveUrl);
            const draftLoadUrlTemplate = @json($draftLoadUrlTemplate);
            const ownerEmailStatusUrl = @json(route('platform.register.owner-email-status'));
            const form = document.querySelector('form[action="{{ route('platform.register.store') }}"]');
            const submitButton = document.getElementById('submit-registration');
            const draftTokenField = document.getElementById('registration_draft_token');
            const serverDraftStatus = document.getElementById('server-draft-status');
            const serverDraftLabel = document.getElementById('server-draft-label');
            const serverDraftCopy = document.getElementById('server-draft-copy');
            const serverDraftLink = document.getElementById('server-draft-link');
            const resumePanel = document.getElementById('resume-registration');
            const resumeTitle = document.getElementById('resume-title');
            const resumeCopy = document.getElementById('resume-copy');
            const resumeLink = document.getElementById('resume-link');
            const ownerEmailField = document.getElementById('owner_email');
            const ownerEmailLiveStatus = document.getElementById('owner-email-live-status');
            const ownerEmailLiveAction = document.getElementById('owner-email-live-action');

            if (!form) {
                return;
            }

            const fields = Array.from(form.querySelectorAll('input[name], select[name], textarea[name]'));
            const persistableFields = fields.filter((field) => field.name !== 'owner_password' && field.type !== 'password');
            let serverDraftToken = initialDraftToken || '';
            let serverDraftSaveHandle = null;
            let serverDraftRequestInFlight = false;
            let ownerEmailStatusHandle = null;
            let ownerEmailStatusRequestId = 0;
            let hasUserInteractedWithForm = false;

            function readJson(key) {
                try {
                    const value = window.localStorage.getItem(key);
                    return value ? JSON.parse(value) : null;
                } catch (error) {
                    return null;
                }
            }

            function writeJson(key, value) {
                try {
                    window.localStorage.setItem(key, JSON.stringify(value));
                } catch (error) {
                    // Ignore storage failures.
                }
            }

            function removeKey(key) {
                try {
                    window.localStorage.removeItem(key);
                } catch (error) {
                    // Ignore storage failures.
                }
            }

            function buildDraftLoadUrl(token) {
                return draftLoadUrlTemplate.replace('__TOKEN__', encodeURIComponent(token));
            }

            function setDraftStatus(text, visible = true) {
                if (!serverDraftStatus || !serverDraftLabel) {
                    return;
                }

                serverDraftStatus.hidden = !visible;
                serverDraftLabel.textContent = text;
            }

            function hasMeaningfulFormData() {
                return Object.keys(collectMeaningfulPayload()).length > 0;
            }

            function shouldRevealRecoveryPanels() {
                return hasUserInteractedWithForm || Boolean(initialDraftToken) || hasMeaningfulFormData();
            }

            function syncServerDraftStatus(meta) {
                if (!serverDraftStatus || !serverDraftCopy || !serverDraftLink) {
                    return;
                }

                if (!meta || !meta.draft_token || !meta.resume_url || !shouldRevealRecoveryPanels()) {
                    serverDraftStatus.hidden = true;
                    return;
                }

                setDraftStatus('Draft tersimpan otomatis.');
                serverDraftLink.href = meta.resume_url;
                serverDraftCopy.textContent = 'Link resume ini bisa dibuka lagi dari perangkat lain untuk melanjutkan pengisian form yang sama.';
            }

            function syncResumePanel() {
                if (!resumePanel || !resumeTitle || !resumeCopy || !resumeLink) {
                    return;
                }

                const progress = readJson(registrationKey);

                if (!progress || !progress.success_url || progress.dismissed === true || !shouldRevealRecoveryPanels()) {
                    resumePanel.hidden = true;
                    return;
                }

                if (progress.status === 'completed') {
                    resumePanel.hidden = true;
                    return;
                }

                resumePanel.hidden = false;

                if (progress.status === 'failed') {
                    resumeTitle.textContent = 'Provisioning tenant terakhir perlu dicek.';
                    resumeCopy.textContent = 'Form tidak perlu diisi ulang. Buka lagi halaman status untuk retry atau cek progres terakhir.';
                    resumeLink.textContent = 'Buka Halaman Status';
                    resumeLink.href = progress.success_url;
                    return;
                }

                resumeTitle.textContent = 'Ada pendaftaran yang masih berjalan.';
                resumeCopy.textContent = 'Jika tab sempat ditutup, proses tetap bisa dilanjutkan dari halaman status tanpa mengulang isi form.';
                resumeLink.textContent = 'Lanjutkan Pantau Status';
                resumeLink.href = progress.success_url;
            }

            function mergeDraftIntoForm(draft) {
                if (!draft || typeof draft !== 'object') {
                    return;
                }

                persistableFields.forEach((field) => {
                    const savedValue = draft[field.name];

                    if (savedValue === undefined || savedValue === null || field.value) {
                        return;
                    }

                    field.value = savedValue;
                });
            }

            function collectDraftPayload() {
                const payload = {};

                persistableFields.forEach((field) => {
                    payload[field.name] = field.value;
                });

                return payload;
            }

            function collectMeaningfulPayload() {
                return Object.fromEntries(
                    Object.entries(collectDraftPayload()).filter(([, value]) => value !== null && value !== '')
                );
            }

            function saveDraftLocally() {
                writeJson(draftKey, collectDraftPayload());
                setDraftStatus('Draft lokal tersimpan.');
            }

            function persistServerDraftMeta(meta) {
                if (!meta || !meta.draft_token) {
                    removeKey(draftServerKey);
                    syncServerDraftStatus(null);
                    return;
                }

                serverDraftToken = meta.draft_token;

                if (draftTokenField) {
                    draftTokenField.value = serverDraftToken;
                }

                writeJson(draftServerKey, meta);
                syncServerDraftStatus(meta);
            }

            async function loadServerDraft(token) {
                if (!token) {
                    return;
                }

                try {
                    const response = await fetch(buildDraftLoadUrl(token), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                    });

                    if (!response.ok) {
                        throw new Error('Draft pendaftaran tidak ditemukan.');
                    }

                    const data = await response.json();
                    mergeDraftIntoForm(data.payload ?? {});
                    writeJson(draftKey, {
                        ...(readJson(draftKey) ?? {}),
                        ...(data.payload ?? {}),
                    });

                    persistServerDraftMeta({
                        draft_token: data.draft_token,
                        resume_url: data.resume_url,
                    });
                } catch (error) {
                    syncServerDraftStatus(null);
                }
            }

            async function saveDraftToServer() {
                if (serverDraftRequestInFlight) {
                    return;
                }

                const payload = collectMeaningfulPayload();

                if (Object.keys(payload).length === 0) {
                    return;
                }

                serverDraftRequestInFlight = true;
                setDraftStatus('Menyimpan draft...');

                try {
                    const response = await fetch(draftSaveUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({
                            draft_token: serverDraftToken || undefined,
                            ...payload,
                        }),
                    });

                    if (!response.ok) {
                        throw new Error('Draft server tidak bisa disimpan.');
                    }

                    const data = await response.json();

                    persistServerDraftMeta({
                        draft_token: data.draft_token,
                        resume_url: data.resume_url,
                    });
                } catch (error) {
                    setDraftStatus('Draft lokal tersimpan. Sinkron server belum berhasil.');
                } finally {
                    serverDraftRequestInFlight = false;
                }
            }

            function queueServerDraftSave() {
                if (serverDraftSaveHandle !== null) {
                    window.clearTimeout(serverDraftSaveHandle);
                }

                serverDraftSaveHandle = window.setTimeout(() => {
                    saveDraftToServer();
                }, 700);
            }

            function restoreLocalDraft() {
                mergeDraftIntoForm(readJson(draftKey));
                const savedServerDraft = readJson(draftServerKey);

                if (savedServerDraft?.draft_token && !serverDraftToken) {
                    serverDraftToken = savedServerDraft.draft_token;
                }

                syncServerDraftStatus(savedServerDraft);
            }

            function setOwnerEmailLiveStatus(message, tone = 'default', visible = true) {
                if (!ownerEmailLiveStatus) {
                    return;
                }

                ownerEmailLiveStatus.hidden = !visible;
                ownerEmailLiveStatus.textContent = message || '';
                ownerEmailLiveStatus.classList.remove('is-success', 'is-warning', 'is-danger');

                if (tone === 'success') {
                    ownerEmailLiveStatus.classList.add('is-success');
                } else if (tone === 'warning') {
                    ownerEmailLiveStatus.classList.add('is-warning');
                } else if (tone === 'danger') {
                    ownerEmailLiveStatus.classList.add('is-danger');
                }
            }

            function setOwnerEmailLiveAction(label, url, visible = true) {
                if (!ownerEmailLiveAction) {
                    return;
                }

                ownerEmailLiveAction.hidden = !visible || !label || !url;
                ownerEmailLiveAction.innerHTML = visible && label && url
                    ? '<a href="' + url + '">' + label + '</a>'
                    : '';
            }

            async function checkOwnerEmailStatus() {
                if (!ownerEmailField || !ownerEmailLiveStatus) {
                    return;
                }

                const rawValue = ownerEmailField.value || '';
                const normalizedValue = rawValue.trim().toLowerCase();

                if (normalizedValue === '') {
                    setOwnerEmailLiveStatus('', 'default', false);
                    setOwnerEmailLiveAction('', '', false);
                    return;
                }

                if (!ownerEmailField.checkValidity()) {
                    setOwnerEmailLiveStatus('Format email belum valid.', 'warning');
                    setOwnerEmailLiveAction('', '', false);
                    return;
                }

                const requestId = ++ownerEmailStatusRequestId;
                setOwnerEmailLiveStatus('Memeriksa status email owner...', 'default');

                try {
                    const url = new URL(ownerEmailStatusUrl, window.location.origin);
                    url.searchParams.set('owner_email', normalizedValue);

                    const response = await fetch(url.toString(), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                    });

                    if (!response.ok) {
                        throw new Error('Gagal memeriksa status email owner.');
                    }

                    const data = await response.json();

                    if (requestId !== ownerEmailStatusRequestId) {
                        return;
                    }

                    if (data.status === 'registered') {
                        setOwnerEmailLiveStatus(data.message, 'danger');
                        setOwnerEmailLiveAction(data.action_label || '', data.action_url || '', true);
                        return;
                    }

                    if (data.status === 'pending') {
                        setOwnerEmailLiveStatus(data.message, 'warning');
                        setOwnerEmailLiveAction(data.action_label || '', data.action_url || '', true);
                        return;
                    }

                    if (data.status === 'available') {
                        setOwnerEmailLiveStatus(data.message, 'success');
                        setOwnerEmailLiveAction('', '', false);
                        return;
                    }

                    setOwnerEmailLiveStatus('', 'default', false);
                    setOwnerEmailLiveAction('', '', false);
                } catch (error) {
                    if (requestId !== ownerEmailStatusRequestId) {
                        return;
                    }

                    setOwnerEmailLiveStatus('Status email belum bisa dicek sekarang. Anda tetap bisa lanjut isi form.', 'warning');
                    setOwnerEmailLiveAction('', '', false);
                }
            }

            function queueOwnerEmailStatusCheck() {
                if (ownerEmailStatusHandle !== null) {
                    window.clearTimeout(ownerEmailStatusHandle);
                }

                ownerEmailStatusHandle = window.setTimeout(() => {
                    checkOwnerEmailStatus();
                }, 350);
            }

            restoreLocalDraft();
            syncResumePanel();

            if (serverDraftToken) {
                loadServerDraft(serverDraftToken);
            }

            persistableFields.forEach((field) => {
                field.addEventListener('input', () => {
                    hasUserInteractedWithForm = true;
                    saveDraftLocally();
                    queueServerDraftSave();
                    syncResumePanel();
                });

                field.addEventListener('change', () => {
                    hasUserInteractedWithForm = true;
                    saveDraftLocally();
                    queueServerDraftSave();
                    syncResumePanel();
                });
            });

            if (ownerEmailField) {
                ownerEmailField.addEventListener('input', queueOwnerEmailStatusCheck);
                ownerEmailField.addEventListener('blur', checkOwnerEmailStatus);

                if ((ownerEmailField.value || '').trim() !== '') {
                    queueOwnerEmailStatusCheck();
                }
            }

            form.addEventListener('submit', () => {
                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.textContent = 'Memproses...';
                }

                removeKey(draftKey);
                removeKey(draftServerKey);
                removeKey(registrationKey);
            });
        })();
    </script>
    
    </body>
</html>
