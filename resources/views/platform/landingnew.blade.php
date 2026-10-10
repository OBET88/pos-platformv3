<!doctype html>
<html class="no-js" lang="zxx">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>RWP POS | Platform POS untuk Retail, F&amp;B, dan Multi Cabang</title>
    <meta name="description" content="RWP POS membantu bisnis retail dan F&amp;B mengelola kasir, stok, laporan, promo, dan operasional multi cabang dalam satu platform yang rapi dan mudah dipakai.">
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

        .header-area .brand-mark-title {
            color: #fff;
        }

        .header-sticky.sticky-bar .brand-mark-title {
            color: #fff;
        }

        .menu-wrapper {
            gap: 18px;
        }

        .main-header .main-menu ul#navigation.platform-nav-compact {
            display: flex;
            align-items: center;
            gap: 2px;
        }

        .main-header .main-menu ul#navigation.platform-nav-compact > li > a {
            padding: 38px 12px;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.01em;
            white-space: nowrap;
        }

        .main-header .main-menu ul#navigation.platform-nav-compact > li.button-header > a {
            padding: 18px 28px !important;
        }

        .main-header .main-menu ul#navigation.platform-nav-compact ul.submenu {
            min-width: 240px;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.18);
        }

        .main-header .main-menu ul#navigation.platform-nav-compact ul.submenu li a {
            padding: 14px 22px;
            line-height: 1.5;
            white-space: normal;
        }

        .header-sticky.sticky-bar .main-header .main-menu ul#navigation.platform-nav-compact > li > a {
            padding-top: 30px;
            padding-bottom: 30px;
        }

        .btn3 {
            transition: transform 0.22s ease, box-shadow 0.22s ease, background-color 0.22s ease, color 0.22s ease;
        }

        .btn3:hover {
            transform: translateY(-2px);
            background: rgba(255, 255, 255, 0.14);
            box-shadow: 0 14px 28px rgba(33, 13, 43, 0.24);
            color: #fff !important;
        }

        .btn.radius-btn,
        .button-header .btn {
            transition: transform 0.22s ease, box-shadow 0.22s ease, filter 0.22s ease;
        }

        .btn.radius-btn:hover,
        .button-header .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 16px 34px rgba(156, 91, 240, 0.28);
            filter: saturate(1.08);
        }

        .platform-footer {
            position: relative;
            overflow: hidden;
            padding: 72px 0 28px;
            background:
                radial-gradient(circle at top left, rgba(236, 99, 239, 0.08), transparent 28%),
                radial-gradient(circle at bottom right, rgba(139, 98, 243, 0.1), transparent 26%),
                linear-gradient(180deg, #fff8fc 0%, #ffffff 100%);
        }

        .platform-footer::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.55), transparent 52%);
            pointer-events: none;
        }

        .platform-footer-card {
            position: relative;
            z-index: 1;
            padding: 30px 32px;
            border-radius: 28px;
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(226, 232, 240, 0.9);
            box-shadow: 0 24px 60px rgba(148, 163, 184, 0.18);
        }

        .platform-footer-brand img {
            max-width: 126px;
        }

        .platform-footer .brand-mark-badge {
            min-width: 60px;
            height: 60px;
            border-radius: 18px;
        }

        .platform-footer .brand-mark-title {
            font-size: 22px;
        }

        .platform-footer .brand-mark-subtitle {
            color: #7c3aed;
        }

        .platform-footer-copy {
            max-width: 430px;
            margin-top: 18px;
            color: #475569;
            font-family: "Poppins", sans-serif;
            font-size: 14px;
            font-weight: 400;
            line-height: 1.85;
            letter-spacing: 0.01em;
        }

        .platform-footer-title {
            margin-bottom: 16px;
            color: #0f172a;
            font-family: "Poppins", sans-serif;
            font-size: 16px;
            font-weight: 600;
            letter-spacing: 0.01em;
        }

        .platform-footer-grid {
            display: grid;
            grid-template-columns: 1.3fr 1fr 1fr 1fr 1fr 1fr;
            gap: 28px;
            align-items: start;
        }

        .platform-footer-list {
            display: grid;
            gap: 10px;
            padding: 0;
            margin: 0;
            list-style: none;
        }

        .platform-footer-list a,
        .platform-footer-contact a,
        .platform-footer-contact-list a {
            color: #334155;
            font-family: "Poppins", sans-serif;
            font-size: 13px;
            font-weight: 400;
            line-height: 1.85;
            transition: color 0.2s ease, transform 0.2s ease;
        }

        .platform-footer-list a:hover,
        .platform-footer-contact a:hover,
        .platform-footer-contact-list a:hover {
            color: #e11d48;
            transform: translateX(2px);
        }

        .platform-footer-contact {
            display: grid;
            gap: 12px;
            color: #475569;
            font-family: "Poppins", sans-serif;
            font-size: 13px;
            line-height: 1.8;
        }

        .platform-footer-contact-list {
            display: grid;
            gap: 10px;
        }

        .platform-footer-contact-item strong {
            display: block;
            margin-bottom: 6px;
            color: #111827;
            font-family: "Poppins", sans-serif;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.01em;
        }

        .platform-footer-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 22px;
        }

        .platform-footer-cta,
        .platform-footer-ghost {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 180px;
            height: 48px;
            padding: 0 22px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 700;
            transition: transform 0.22s ease, box-shadow 0.22s ease, background-color 0.22s ease, color 0.22s ease;
        }

        .platform-footer-cta {
            color: #fff;
            background: linear-gradient(135deg, #8b62f3, #ec63ef);
            box-shadow: 0 16px 34px rgba(156, 91, 240, 0.24);
        }

        .platform-footer-ghost {
            color: #7a3df0;
            background: rgba(122, 61, 240, 0.06);
            border: 1px solid rgba(122, 61, 240, 0.12);
        }

        .platform-footer-cta:hover,
        .platform-footer-ghost:hover {
            color: #fff;
            transform: translateY(-2px);
        }

        .platform-footer-cta:hover {
            box-shadow: 0 20px 38px rgba(156, 91, 240, 0.3);
        }

        .platform-footer-ghost:hover {
            background: #7a3df0;
        }

        .plan-carousel {
            margin: 0 -16px;
            padding-bottom: 10px;
        }

        .plan-slide {
            padding: 0 16px 12px;
        }

        .plan-carousel .single-card {
            margin-bottom: 0;
            display: flex;
            flex-direction: column;
            min-height: 100%;
            overflow: hidden;
            border-radius: 24px;
            border: 1px solid rgba(226, 232, 240, 0.92);
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
            transition: transform 0.22s ease, box-shadow 0.22s ease;
        }

        .plan-carousel .single-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 18px 34px rgba(15, 23, 42, 0.12);
        }

        .plan-carousel .slick-track {
            display: flex !important;
        }

        .plan-carousel .slick-slide {
            height: inherit !important;
        }

        .plan-carousel .slick-slide > div {
            height: 100%;
        }

        .plan-carousel .card-bottom {
            display: flex;
            flex-direction: column;
            flex: 1 1 auto;
        }

        .plan-carousel .borders-btn {
            margin-top: auto;
        }

        .plan-carousel .slick-dots {
            display: flex !important;
            justify-content: center;
            align-items: center;
            gap: 6px;
            bottom: -52px;
        }

        .plan-carousel .slick-dots li {
            width: auto;
            height: auto;
            margin: 0;
        }

        .plan-carousel .slick-dots li button {
            width: 9px;
            height: 9px;
            padding: 0;
            border-radius: 999px;
            background: rgba(122, 61, 240, 0.16);
            font-size: 0;
            line-height: 0;
            transition: transform 0.2s ease, background-color 0.2s ease, opacity 0.2s ease;
        }

        .plan-carousel .slick-dots li button::before {
            display: none;
        }

        .plan-carousel .slick-dots li button:before {
            font-size: 10px;
            color: #7a3df0;
            opacity: 0.28;
        }

        .plan-carousel .slick-dots li.slick-active button {
            background: rgba(122, 61, 240, 0.42);
            transform: scale(1.15);
        }

        .plan-carousel.is-static {
            margin: 0 -16px;
        }

        .plan-carousel.is-static .slick-dots {
            display: none !important;
        }

        .platform-footer-bottom {
            position: relative;
            z-index: 1;
            margin-top: 26px;
            padding-top: 20px;
            border-top: 1px solid rgba(226, 232, 240, 0.9);
            color: #64748b;
            font-family: "Poppins", sans-serif;
            font-size: 12px;
            line-height: 1.7;
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

        @media (max-width: 767px) {
            .platform-footer {
                padding: 56px 0 24px;
            }

            .platform-footer-grid {
                grid-template-columns: 1fr;
                gap: 24px;
            }

            .plan-carousel {
                margin: 0 -10px;
            }

            .plan-slide {
                padding: 0 10px 10px;
            }

            .plan-carousel .slick-dots {
                bottom: -44px;
            }

            .platform-footer-card {
                padding: 22px 18px;
                border-radius: 22px;
            }

            .platform-footer-actions {
                flex-direction: column;
            }

            .platform-footer-cta,
            .platform-footer-ghost {
                width: 100%;
                min-width: 0;
            }
        }
    </style>
    
</head>
<body>
    @php
        $primaryPlan = $plans->firstWhere('code', 'pro') ?? $plans->first();
        $publicPlans = $plans->values();
    @endphp
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
    <!-- Preloader Start -->
    <header>
        <!-- Header Start -->
        <div class="header-area header-transparent">
            <div class="main-header ">
                <div class="header-bottom  header-sticky">
                    <div class="container-fluid">
                        <div class="row align-items-center">
                            <!-- Logo -->
                            <div class="col-xl-2 col-lg-2">
                                <div class="logo">
                                    <a href="{{ route('platform.landing') }}" class="brand-mark" aria-label="RWP POS">
                                        <span class="brand-mark-badge"><i class="fas fa-cash-register"></i></span>
                                        <span class="brand-mark-text">
                                            <span class="brand-mark-title">RWP POS</span>
                                            <span class="brand-mark-subtitle">Retail Platform</span>
                                        </span>
                                    </a>
                                </div>
                            </div>
                            <div class="col-xl-10 col-lg-10">
                                <div class="menu-wrapper d-flex align-items-center justify-content-end">
                                    <!-- Main-menu -->
                                    <div class="main-menu d-none d-lg-block">
                                        <nav>
                                            <ul id="navigation" class="platform-nav-compact">
                                                <li><a href="#top">Home</a></li>
                                                <li><a href="#fitur-utama">Fitur</a></li>
                                                <li><a href="#paket">Paket</a></li>
                                                <li><a href="#faq">FAQ</a></li>
                                                <li>
                                                    <a href="#">Info</a>
                                                    <ul class="submenu">
                                                        <li><a href="{{ route('platform.help') }}">Bantuan</a></li>
                                                        <li><a href="{{ route('platform.contact') }}">Kontak</a></li>
                                                        <li><a href="{{ route('platform.blog') }}">Blog</a></li>
                                                    </ul>
                                                </li>
                                                <li>
                                                    <a href="#">Langganan</a>
                                                    <ul class="submenu">
                                                        <li><a href="{{ route('platform.renewal.create') }}">Perpanjang Langganan</a></li>
                                                        <li><a href="{{ route('platform.upgrade.create') }}">Upgrade Paket</a></li>
                                                        <li><a href="{{ route('platform.orders.index') }}">Konfirmasi Pembayaran</a></li>
                                                    </ul>
                                                </li>
                                                <li class="button-header margin-left "><a href="{{ route('platform.register.create') }}" class="btn">Daftar Tenant</a></li>
                                                <li class="button-header"><a href="{{ route('platform.contact') }}" class="btn3">Konsultasi</a></li>
                                            </ul>
                                        </nav>
                                    </div>
                                </div>
                            </div> 
                            <!-- Mobile Menu -->
                            <div class="col-12">
                                <div class="mobile_menu d-block d-lg-none"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Header End -->
    </header>
    <main>
        <!-- Slider Area Start-->
        <div id="top" class="slider-area slider-bg ">
            <div class="slider-active">
                <!-- Single Slider -->
                <div class="single-slider d-flex align-items-center slider-height ">
                    <div class="container">
                        <div class="row align-items-center justify-content-between">
                            <div class="col-xl-5 col-lg-5 col-md-9 ">
                                <div class="hero__caption">
                                    <span data-animation="fadeInLeft" data-delay=".3s">POS untuk retail, F&amp;B, dan multi cabang</span>
                                    <h1 data-animation="fadeInLeft" data-delay=".6s ">Kasir, stok, laporan, dan operasional outlet dalam satu platform</h1>
                                    <p data-animation="fadeInLeft" data-delay=".8s">RWP POS dirancang untuk bisnis yang ingin go-live lebih cepat tanpa kehilangan kontrol. Mulai dari transaksi kasir, pengelolaan produk, promo, sampai monitoring cabang bisa dijalankan dari satu sistem yang rapi.</p>
                                    <!-- Slider btn -->
                                    <div class="slider-btns">
                                        <!-- Hero-btn -->
                                        <a data-animation="fadeInLeft" data-delay="1s" href="{{ route('platform.register.create', $primaryPlan ? ['plan' => $primaryPlan->code] : []) }}" class="btn radius-btn">Mulai Daftar</a>
                                        <a data-animation="fadeInLeft" data-delay="1.1s" href="#paket" class="btn3">Lihat Paket</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-6 col-lg-6">
                                <div class="hero__img d-none d-lg-block f-right">
                                    <img src="{{ asset('ecohosting-main/assets/img/hero/hero_right.png') }}" alt="" data-animation="fadeInRight" data-delay="1s">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>      
                <!-- Single Slider -->
                <div class="single-slider d-flex align-items-center slider-height ">
                    <div class="container">
                        <div class="row align-items-center">
                            <div class="col-lg-6 col-md-9 ">
                                <div class="hero__caption">
                                    <span data-animation="fadeInLeft" data-delay=".3s">Onboarding owner tetap ringan</span>
                                    <h1 data-animation="fadeInLeft" data-delay=".6s">Daftar tenant sekali, lanjutkan tanpa alur yang membingungkan</h1>
                                    <p data-animation="fadeInLeft" data-delay=".8s">Form pendaftaran menyimpan draft, owner menerima verifikasi email, lalu status provisioning bisa dipantau sampai tenant siap login. Cocok untuk onboarding mandiri maupun dibantu tim sales.</p>
                                    <!-- Slider btn -->
                                    <div class="slider-btns">
                                        <!-- Hero-btn -->
                                        <a data-animation="fadeInLeft" data-delay="1s" href="{{ route('platform.register.create') }}" class="btn radius-btn">Isi Form</a>
                                        <a data-animation="fadeInLeft" data-delay="1.1s" href="#faq" class="btn3">Pelajari Alurnya</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="hero__img d-none d-lg-block f-right">
                                    <img src="{{ asset('ecohosting-main/assets/img/hero/hero_right.png') }}" alt="" data-animation="fadeInRight" data-delay="1s">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>      
            </div>
            <!-- Slider Shape -->
            <div class="slider-shape d-none d-lg-block">
                <img class="slider-shape1" src="{{ asset('ecohosting-main/assets/img/hero/top-left-shape.png') }}" alt="">
            </div>
        </div>
        <!-- Slider Area End -->
        <!-- Domain-search start -->
        
    </div>
    <!-- Domain-search End -->
    <!--? Team -->
    <section id="fitur-utama" class="team-area section-padding40 section-bg1">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-12">
                    <div class="section-tittle text-center mb-105">
                        <h2>Fitur Utama Aplikasi</h2>
                        <p>Susunan fitur di bawah ini merangkum area operasional yang paling sering dibutuhkan outlet aktif, dari transaksi sampai kontrol tim dan laporan.</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-4 col-md-4 col-sm-6">
                    <div class="single-cat">
                        <div class="cat-icon">
                            <img src="{{ asset('ecohosting-main/assets/img/icon/services1.svg') }}" alt="">
                        </div>
                        <div class="cat-cap">
                            <h5><a href="#">POS Kasir & Shift</a></h5>
                            <p>Transaksi kasir, kontrol shift, dan operasional harian berjalan dalam satu alur yang cepat untuk toko dan outlet.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-6">
                    <div class="single-cat">
                        <div class="cat-icon">
                            <img src="{{ asset('ecohosting-main/assets/img/icon/services2.svg') }}" alt="">
                        </div>
                        <div class="cat-cap">
                            <h5><a href="#">Laporan & Rekap</a></h5>
                            <p>Laporan penjualan, rekap produk, laporan stok, hingga ringkasan kasir owner tersedia untuk membantu pengambilan keputusan.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-6">
                    <div class="single-cat">
                        <div class="cat-icon">
                            <img src="{{ asset('ecohosting-main/assets/img/icon/services3.svg') }}" alt="">
                        </div>
                        <div class="cat-cap">
                            <h5><a href="#">Produk, Resep & Stok</a></h5>
                            <p>Manajemen produk, kategori, resep, stok masuk-keluar, sampai produksi membantu kontrol barang jadi dan bahan baku.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-6">
                    <div class="single-cat">
                        <div class="cat-icon">
                            <img src="{{ asset('ecohosting-main/assets/img/icon/services4.svg') }}" alt="">
                        </div>
                        <div class="cat-cap">
                            <h5><a href="#">Floor Map & QR Order Meja</a></h5>
                            <p>Untuk bisnis dine-in, meja bisa dikelola lewat floor map dan pelanggan dapat memesan dari QR table order yang terhubung ke kasir.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-6">
                    <div class="single-cat">
                        <div class="cat-icon">
                            <img src="{{ asset('ecohosting-main/assets/img/icon/services5.svg') }}" alt="">
                        </div>
                        <div class="cat-cap">
                            <h5><a href="#">Promo, Voucher & Langganan</a></h5>
                            <p>Tenant bisa mengelola promo voucher sekaligus memantau paket langganan dan jalur upgrade dari dalam aplikasi.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-6">
                    <div class="single-cat">
                        <div class="cat-icon">
                            <img src="{{ asset('ecohosting-main/assets/img/icon/services6.svg') }}" alt="">
                        </div>
                        <div class="cat-cap">
                            <h5><a href="#">Akuntansi & Jurnal</a></h5>
                            <p>Fitur akuntansi mencakup COA, entry keuangan admin, jurnal manual, dan laporan akuntansi untuk tenant yang butuh kontrol lebih rapi.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Services End -->
    <!--? Pricing Card Start -->
    <section id="paket" class="pricing-card-area fix">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-8 col-lg-8">
                    <div class="section-tittle text-center mb-90">
                        <h2>Paket Langganan</h2>
                        <p>Pilih paket yang paling sesuai dengan skala bisnis Anda. Isi paket mengikuti pengaturan aktif di platform, jadi informasi publik dan flow pendaftaran tetap sinkron.</p>
                    </div>
                </div>
            </div>
            <div class="plan-carousel">
                @forelse($plans as $index => $plan)
                    <div class="plan-slide">
                        <div class="single-card text-center mb-30">
                            <div class="card-top">
                                <img src="{{ asset('ecohosting-main/assets/img/icon/price' . (($index % 3) + 1) . '.svg') }}" alt="">
                                <h4>{{ $plan->name }}</h4>
                                <p>{{ $plan->code === 'basic' ? 'Paket operasional dasar' : ($plan->code === 'pro' ? 'Paket untuk bisnis berkembang' : ($plan->code === 'plus_plus' ? 'Paket lengkap untuk kontrol lebih luas' : 'Paket langganan')) }}</p>
                            </div>
                            <div class="card-mid">
                                <h4>
                                    @if((float) $plan->price > 0)
                                        Rp {{ number_format((float) $plan->price, 0, ',', '.') }} <span>/ {{ $plan->billing_cycle }}</span>
                                    @else
                                        Hubungi Sales <span>untuk harga</span>
                                    @endif
                                </h4>
                            </div>
                            <div class="card-bottom">
                                <ul>
                                    <li>Maks. {{ $plan->max_businesses }} usaha aktif</li>
                                    <li>Maks. {{ $plan->max_branches }} cabang aktif</li>
                                    <li>Maks. {{ $plan->max_users }} user</li>
                                    <li>{{ $plan->features()->count() }} fitur utama aktif</li>
                                    <li>{{ $plan->code === 'basic' ? 'Cocok untuk outlet yang ingin mulai stabil' : ($plan->code === 'pro' ? 'Cocok untuk bisnis dengan kebutuhan operasional lebih luas' : 'Siap untuk tenant dengan kebutuhan kontrol yang lebih lengkap') }}</li>
                                </ul>
                                <a href="{{ route('platform.register.create', ['plan' => $plan->code]) }}" class="borders-btn">
                                    Pilih Paket Ini
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="plan-slide">
                        <div class="single-card text-center mb-30">
                            <div class="card-top">
                                <h4>Belum Ada Paket Aktif</h4>
                                <p>Daftar paket publik akan muncul otomatis setelah paket aktif dipublikasikan dari panel platform.</p>
                            </div>
                            <div class="card-bottom">
                                <ul>
                                    <li>Form pendaftaran tetap bisa dibuka untuk kebutuhan follow up manual</li>
                                    <li>Begitu paket aktif tersedia, landing ini akan ikut ter-update otomatis</li>
                                </ul>
                                <a href="{{ route('platform.register.create') }}" class="borders-btn">Tetap Buka Form Daftar</a>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
    <!-- Pricing Card End -->
    <!--? About-1 Area Start -->
    <div class="about-area1 section-padding40">
        
    </div>
    <!-- About-1 Area End -->
    <!--? About-2 Area Start -->
    <div class="about-area1 pb-bottom">
        <div class="container">
            <div class="row align-items-center justify-content-between">
                <div class="col-xl-7 col-lg-7 col-md-12">
                    <div class="about-caption about-caption3 mb-50">
                        <!-- Section Tittle -->
                        <div class="section-tittle section-tittle2 mb-30">
                            <h2>Onboarding lebih cepat tanpa kehilangan kontrol</h2>
                        </div>
                        <p class="mb-40">Tenant bisa daftar mandiri, sementara platform tetap menjaga verifikasi owner, pemilihan paket, dan provisioning awal. Alurnya ringan untuk calon pelanggan, tapi tetap aman untuk tim internal.</p>
                        <ul class="mb-30">
                            <li>
                                <img src="{{ asset('ecohosting-main/assets/img/icon/right.svg') }}" alt="">
                                <p>Email owner diverifikasi sebelum tenant benar-benar aktif</p>
                            </li>
                            <li>
                                <img src="{{ asset('ecohosting-main/assets/img/icon/right.svg') }}" alt="">
                                <p>Tim sales dan support tidak perlu mengulang pengumpulan data dasar</p>
                            </li>
                        </ul>
                        <a href="{{ route('platform.register.create') }}" class="btn"><i class="fas fa-arrow-right"></i>Daftarkan Tenant</a>
                    </div>
                </div>
                <div class="col-xl-5 col-lg-5 col-md-8 col-sm-10">
                    <!-- about-img -->
                    <div class="about-img ">
                        <img src="{{ asset('ecohosting-main/assets/img/gallery/about2.png') }}" alt="">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- About-2 Area End -->
    <!-- ask questions -->
    <section id="faq" class="ask-questions section-bg1 section-padding30 fix">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-8 col-lg-9 col-md-10 ">
                    <!-- Section Tittle -->
                    <div class="section-tittle text-center mb-90">
                        <h2>Pertanyaan Yang Paling Sering Muncul</h2>
                        <p>Bagian ini membantu calon tenant memahami alur daftar sekaligus menjawab pertanyaan dasar tentang fitur yang tersedia di aplikasi.</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6 col-md-6">
                    <div class="single-question d-flex mb-50">
                        <span> Q.</span>
                        <div class="pera">
                            <h2>Apakah tenant langsung aktif setelah form dikirim?</h2>
                            <p>Belum. Owner harus memverifikasi email lebih dulu. Setelah itu platform mulai membuat tenant dan menyiapkan akun awal secara otomatis.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6">
                    <div class="single-question d-flex mb-50">
                        <span> Q.</span>
                        <div class="pera">
                            <h2>Kalau browser tertutup saat isi form, apakah datanya hilang?</h2>
                            <p>Tidak selalu. Form ini menyimpan draft lokal dan bisa membuat link resume, jadi pengisian dapat dilanjutkan tanpa mulai dari awal.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6">
                    <div class="single-question d-flex mb-50">
                        <span> Q.</span>
                        <div class="pera">
                            <h2>Siapa yang jadi akun utama tenant baru?</h2>
                            <p>Email owner yang didaftarkan akan menjadi akun utama tenant. Karena itu email aktif dan bisa menerima verifikasi sangat penting.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6">
                    <div class="single-question d-flex mb-50">
                        <span> Q.</span>
                        <div class="pera">
                            <h2>Bagaimana pelanggan tahu tenant sudah siap dipakai?</h2>
                            <p>Setelah verifikasi, halaman status provisioning akan memberi progres terbaru sampai tenant selesai dibuat dan siap login.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6">
                    <div class="single-question d-flex mb-50">
                        <span> Q.</span>
                        <div class="pera">
                            <h2>Apakah aplikasi ini hanya untuk kasir, atau juga ada laporan dan stok?</h2>
                            <p>Tidak hanya kasir. Tenant juga mendapatkan modul laporan, rekap, produk, resep, dan pengelolaan stok sesuai paket yang dipilih.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6">
                    <div class="single-question d-flex mb-50">
                        <span> Q.</span>
                        <div class="pera">
                            <h2>Kalau bisnis saya punya meja makan, apakah bisa pakai QR order?</h2>
                            <p>Bisa. Aplikasi mendukung floor map dan QR table order untuk kebutuhan dine-in, sehingga pelanggan bisa memesan dari meja dan order tetap terhubung ke kasir.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6">
                    <div class="single-question d-flex mb-50">
                        <span> Q.</span>
                        <div class="pera">
                            <h2>Apakah promo voucher dan paket langganan bisa dikelola dari aplikasi?</h2>
                            <p>Bisa. Tenant dapat mengelola promo voucher sekaligus memantau status langganan dan jalur upgrade dari dalam aplikasi.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6">
                    <div class="single-question d-flex mb-50">
                        <span> Q.</span>
                        <div class="pera">
                            <h2>Untuk bisnis yang butuh pencatatan keuangan, apakah ada fitur akuntansi?</h2>
                            <p>Ada. Untuk paket yang sesuai, tenant bisa memakai COA, entry keuangan admin, jurnal manual, dan laporan akuntansi.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xl-12 ">
                    <div class="more-btn text-center mt-20">
                        <a href="{{ route('platform.register.create') }}" class="btn">Buka Form Daftar Tenant</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- End ask questions -->
</main>
<footer class="platform-footer">
    <div class="container">
        <div class="platform-footer-card">
            <div class="platform-footer-grid">
                <div>
                    <div class="platform-footer-brand">
                        <a href="{{ route('platform.landing') }}" class="brand-mark" aria-label="RWP POS">
                            <span class="brand-mark-badge"><i class="fas fa-cash-register"></i></span>
                            <span class="brand-mark-text">
                                <span class="brand-mark-title">RWP POS</span>
                                <span class="brand-mark-subtitle">Retail Platform</span>
                            </span>
                        </a>
                    </div>
                    <p class="platform-footer-copy">
                        Aplikasi untuk bisnis yang ingin mulai dari kasir, berkembang ke laporan, stok, promo, operasional meja, dan akuntansi dalam satu alur yang tetap ringan dipelajari.
                    </p>
                    <div class="platform-footer-actions">
                        <a href="{{ route('platform.renewal.create') }}" class="platform-footer-cta">Perpanjang Langganan</a>
                        <a href="{{ route('platform.upgrade.create') }}" class="platform-footer-cta" style="background:rgba(255,255,255,0.12);">Upgrade Paket</a>
                        <a href="{{ route('platform.orders.index') }}" class="platform-footer-ghost">Konfirmasi Pembayaran</a>
                    </div>
                </div>
                <div>
                    <h4 class="platform-footer-title">Produk</h4>
                    <ul class="platform-footer-list">
                        <li><a href="{{ route('platform.landing') }}">RWP POS</a></li>
                        @foreach ($publicPlans as $plan)
                            <li><a href="{{ route('platform.register.create', ['plan' => $plan->code]) }}">Paket {{ $plan->name }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <h4 class="platform-footer-title">Fitur</h4>
                    <ul class="platform-footer-list">
                        <li><a href="#fitur-utama">POS Kasir & Shift</a></li>
                        <li><a href="#fitur-utama">Laporan & Rekap</a></li>
                        <li><a href="#fitur-utama">Produk, Resep & Stok</a></li>
                        <li><a href="#fitur-utama">QR Order & Floor Map</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="platform-footer-title">Resources</h4>
                    <ul class="platform-footer-list">
                        <li><a href="{{ route('platform.help') }}">Help Center</a></li>
                        <li><a href="#faq">FAQ</a></li>
                        <li><a href="{{ route('platform.register.create') }}">Coba Alur Daftar</a></li>
                        <li><a href="{{ route('platform.blog') }}">Blog</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="platform-footer-title">Perusahaan</h4>
                    <ul class="platform-footer-list">
                        <li><a href="#top">Tentang RWP POS</a></li>
                        <li><a href="{{ route('platform.register.create') }}">Mulai Berlangganan</a></li>
                        <li><a href="{{ route('platform.contact') }}">Kontak</a></li>
                        <li><a href="{{ route('platform.blog') }}">Update & Artikel</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="platform-footer-title">Hubungi Kami</h4>
                    <div class="platform-footer-contact-list">
                        <div class="platform-footer-contact-item">
                            <strong>Mulai Sekarang</strong>
                            <a href="{{ route('platform.register.create') }}">Buka form pendaftaran tenant</a>
                        </div>
                        <div class="platform-footer-contact-item">
                            <strong>Tanya Fitur</strong>
                            <a href="{{ route('platform.help') }}">Lihat bantuan umum</a>
                        </div>
                        <div class="platform-footer-contact-item">
                            <strong>Hubungi Tim</strong>
                            <a href="{{ route('platform.contact') }}">Buka halaman kontak</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="platform-footer-bottom text-center text-lg-left">
                Copyright &copy;<script>document.write(new Date().getFullYear());</script> POS Platform. All rights reserved | Made with <i class="fas fa-heart" style="color: #e11d48;"></i> by RWP Team
            </div>
        </div>
    </div>
</footer>
  <!-- Scroll Up -->
  <div id="back-top" >
    <a title="Go to Top" href="#"> <i class="fas fa-level-up-alt"></i></a>
</div>

<!-- JS here -->

<script src="{{ asset('ecohosting-main/./assets/js/vendor/modernizr-3.5.0.min.js') }}"></script>
<!-- Jquery, Popper, Bootstrap -->
<script src="{{ asset('ecohosting-main/./assets/js/vendor/jquery-1.12.4.min.js') }}"></script>
<script src="{{ asset('ecohosting-main/./assets/js/popper.min.js') }}"></script>
<script src="{{ asset('ecohosting-main/./assets/js/bootstrap.min.js') }}"></script>
<!-- Jquery Mobile Menu -->
<script src="{{ asset('ecohosting-main/./assets/js/jquery.slicknav.min.js') }}"></script>

<!-- Jquery Slick , Owl-Carousel Plugins -->
<script src="{{ asset('ecohosting-main/./assets/js/owl.carousel.min.js') }}"></script>
<script src="{{ asset('ecohosting-main/./assets/js/slick.min.js') }}"></script>
<!-- One Page, Animated-HeadLin -->
<script src="{{ asset('ecohosting-main/./assets/js/wow.min.js') }}"></script>
<script src="{{ asset('ecohosting-main/./assets/js/animated.headline.js') }}"></script>
<script src="{{ asset('ecohosting-main/./assets/js/jquery.magnific-popup.js') }}"></script>

<!-- Date Picker -->
<script src="{{ asset('ecohosting-main/./assets/js/gijgo.min.js') }}"></script>

<!-- Video bg -->
<script src="{{ asset('ecohosting-main/./assets/js/jquery.vide.js') }}"></script>

<!-- Nice-select, sticky -->
<script src="{{ asset('ecohosting-main/./assets/js/jquery.nice-select.min.js') }}"></script>
<script src="{{ asset('ecohosting-main/./assets/js/jquery.sticky.js') }}"></script>
<!-- Progress -->
<script src="{{ asset('ecohosting-main/./assets/js/jquery.barfiller.js') }}"></script>

<!-- counter , waypoint,Hover Direction -->
<script src="{{ asset('ecohosting-main/./assets/js/jquery.counterup.min.js') }}"></script>
<script src="{{ asset('ecohosting-main/./assets/js/waypoints.min.js') }}"></script>
<script src="{{ asset('ecohosting-main/./assets/js/jquery.countdown.min.js') }}"></script>
<script src="{{ asset('ecohosting-main/./assets/js/hover-direction-snake.min.js') }}"></script>

<!-- contact js -->
<script src="{{ asset('ecohosting-main/./assets/js/contact.js') }}"></script>
<script src="{{ asset('ecohosting-main/./assets/js/jquery.form.js') }}"></script>
<script src="{{ asset('ecohosting-main/./assets/js/jquery.validate.min.js') }}"></script>
<script src="{{ asset('ecohosting-main/./assets/js/mail-script.js') }}"></script>
<script src="{{ asset('ecohosting-main/./assets/js/jquery.ajaxchimp.min.js') }}"></script>

<!-- Jquery Plugins, main Jquery -->	
<script src="{{ asset('ecohosting-main/./assets/js/plugins.js') }}"></script>
<script src="{{ asset('ecohosting-main/assets/js/main.js') }}"></script>
<script>
    $(function () {
        var $planCarousel = $('.plan-carousel');
        var planCount = $planCarousel.find('.plan-slide').length;
        var shouldLoopDesktop = planCount > 3;
        var shouldShowDots = planCount > 1;

        if (!$planCarousel.length || $planCarousel.hasClass('slick-initialized')) {
            return;
        }

        if (!shouldLoopDesktop) {
            $planCarousel.addClass('is-static');
        }

        $planCarousel.slick({
            slidesToShow: 3,
            slidesToScroll: 1,
            infinite: shouldLoopDesktop,
            arrows: false,
            dots: shouldShowDots,
            adaptiveHeight: false,
            responsive: [
                {
                    breakpoint: 1200,
                    settings: {
                        slidesToShow: 2,
                        infinite: planCount > 2,
                        arrows: false,
                        dots: shouldShowDots
                    }
                },
                {
                    breakpoint: 768,
                    settings: {
                        slidesToShow: 1,
                        infinite: planCount > 1,
                        arrows: false,
                        dots: shouldShowDots
                    }
                }
            ]
        });
    });
</script>

</body>
</html>
