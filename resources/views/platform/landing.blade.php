<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="POS Platform untuk onboarding tenant, trial, dan operasional SaaS POS yang lebih profesional.">
    <title>POS Platform</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <link href="{{ asset('chain-1.0.0/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('chain-1.0.0/assets/css/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('chain-1.0.0/assets/css/templatemo-chain-app-dev.css') }}">
    <link rel="stylesheet" href="{{ asset('chain-1.0.0/assets/css/animated.css') }}">
    <link rel="stylesheet" href="{{ asset('chain-1.0.0/assets/css/owl.css') }}">
    <style>
        :root {
            --platform-ink: #19233f;
            --platform-muted: #6a7797;
            --platform-line: rgba(25, 35, 63, 0.1);
            --platform-surface: rgba(255, 255, 255, 0.94);
            --platform-surface-soft: rgba(246, 249, 255, 0.92);
            --platform-shadow: 0 28px 72px rgba(28, 41, 79, 0.14);
            --platform-shadow-soft: 0 16px 44px rgba(28, 41, 79, 0.08);
            --platform-radius: 28px;
        }

        body.platform-landing {
            color: var(--platform-ink);
            background:
                radial-gradient(circle at top left, rgba(91, 104, 235, 0.18), transparent 24%),
                radial-gradient(circle at 100% 6%, rgba(40, 225, 253, 0.13), transparent 20%),
                #fff;
        }

        .platform-landing .header-area {
            position: absolute;
        }

        .platform-landing .main-banner {
            padding: 185px 0 90px;
        }

        .platform-landing .left-content p,
        .platform-landing .section-copy,
        .platform-landing .feature-card p,
        .platform-landing .process-card p,
        .platform-landing .metric-card span,
        .platform-landing .story-card p,
        .platform-landing .dashboard-row small,
        .platform-landing .cta-band p {
            color: var(--platform-muted);
            line-height: 1.85;
        }

        .platform-landing .hero-badges,
        .platform-landing .mini-proof-grid,
        .platform-landing .feature-grid,
        .platform-landing .process-grid,
        .platform-landing .metric-grid {
            display: grid;
            gap: 16px;
        }

        .platform-landing .hero-badges {
            grid-template-columns: repeat(3, minmax(0, 1fr));
            margin-top: 26px;
        }

        .platform-landing .hero-badge,
        .platform-landing .feature-card,
        .platform-landing .process-card,
        .platform-landing .dashboard-shell,
        .platform-landing .section-shell,
        .platform-landing .cta-band {
            border-radius: var(--platform-radius);
            background: var(--platform-surface);
            box-shadow: var(--platform-shadow-soft);
        }

        .platform-landing .hero-badge {
            padding: 18px 20px;
            border: 1px solid rgba(255, 255, 255, 0.78);
        }

        .platform-landing .hero-badge strong,
        .platform-landing .feature-card strong,
        .platform-landing .process-card strong,
        .platform-landing .dashboard-row strong {
            display: block;
            margin-bottom: 8px;
            font-size: 16px;
            font-weight: 700;
        }

        .platform-landing .hero-badge span {
            display: block;
            color: var(--platform-muted);
            font-size: 14px;
            line-height: 1.75;
        }

        .platform-landing .hero-buttons {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
            margin-top: 30px;
        }

        .platform-landing .hero-buttons .white-button a,
        .platform-landing .hero-buttons .gradient-button a {
            min-width: 220px;
            text-align: center;
        }

        .platform-landing .mini-proof-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
            margin-top: 28px;
        }

        .platform-landing .proof-chip {
            padding: 16px 18px;
            border-radius: 20px;
            border: 1px solid rgba(91, 104, 235, 0.08);
            background: rgba(255, 255, 255, 0.82);
            color: #5a6787;
            font-size: 13px;
            font-weight: 700;
            text-align: center;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .platform-landing .dashboard-shell {
            padding: 24px;
            border: 1px solid rgba(255, 255, 255, 0.82);
            background: linear-gradient(180deg, rgba(13, 27, 55, 0.98), rgba(18, 45, 88, 0.96));
            box-shadow: var(--platform-shadow);
            overflow: hidden;
        }

        .platform-landing .dashboard-window {
            padding: 22px;
            border-radius: 26px;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(245, 249, 255, 0.94));
        }

        .platform-landing .dashboard-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
            margin-bottom: 20px;
        }

        .platform-landing .dashboard-head strong {
            display: block;
            font-size: 19px;
            letter-spacing: -0.03em;
        }

        .platform-landing .dashboard-head span {
            display: block;
            margin-top: 4px;
            color: var(--platform-muted);
            font-size: 13px;
        }

        .platform-landing .live-chip,
        .platform-landing .section-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border-radius: 999px;
            background: rgba(91, 104, 235, 0.12);
            color: #5b68eb;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .platform-landing .live-chip::before,
        .platform-landing .section-chip::before {
            content: "";
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: linear-gradient(105deg, rgba(91, 104, 235, 1) 0%, rgba(40, 225, 253, 1) 100%);
        }

        .platform-landing .metric-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
            margin-bottom: 16px;
        }

        .platform-landing .metric-card {
            padding: 18px;
            border-radius: 22px;
            border: 1px solid var(--platform-line);
            background: #fff;
        }

        .platform-landing .metric-card strong {
            display: block;
            font-size: 30px;
            letter-spacing: -0.05em;
        }

        .platform-landing .dashboard-list {
            display: grid;
            gap: 12px;
        }

        .platform-landing .dashboard-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            padding: 14px 16px;
            border-radius: 18px;
            border: 1px solid var(--platform-line);
            background: rgba(255, 255, 255, 0.9);
        }

        .platform-landing .status-pill {
            padding: 8px 11px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }

        .platform-landing .status-ready {
            color: #0f8f7a;
            background: rgba(15, 157, 129, 0.14);
        }

        .platform-landing .status-trial {
            color: #3158d9;
            background: rgba(91, 104, 235, 0.12);
        }

        .platform-landing .status-alert {
            color: #a46a00;
            background: rgba(255, 190, 51, 0.18);
        }

        .platform-landing .section-shell {
            margin-top: 28px;
            padding: 34px;
            border: 1px solid rgba(255, 255, 255, 0.82);
        }

        .platform-landing .section-head {
            display: flex;
            justify-content: space-between;
            align-items: end;
            gap: 20px;
            margin-bottom: 24px;
        }

        .platform-landing .section-head h2 {
            margin: 14px 0 0;
            max-width: 13ch;
            font-size: clamp(2rem, 3vw, 2.8rem);
            line-height: 1.04;
            letter-spacing: -0.05em;
        }

        .platform-landing .section-copy {
            max-width: 58ch;
            margin: 0;
        }

        .platform-landing .feature-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .platform-landing .feature-card,
        .platform-landing .process-card {
            padding: 24px;
            border: 1px solid var(--platform-line);
        }

        .platform-landing .feature-card .feature-tag,
        .platform-landing .process-card .process-step {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 46px;
            margin-bottom: 14px;
            padding: 10px 14px;
            border-radius: 16px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .platform-landing .feature-card .feature-tag {
            color: #5b68eb;
            background: rgba(91, 104, 235, 0.12);
        }

        .platform-landing .process-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .platform-landing .process-card .process-step {
            color: #fff;
            background: linear-gradient(105deg, rgba(91, 104, 235, 1) 0%, rgba(40, 225, 253, 1) 100%);
        }

        .platform-landing .story-card {
            padding: 28px;
            border-radius: var(--platform-radius);
            background: linear-gradient(135deg, rgba(12, 27, 55, 0.98), rgba(16, 47, 93, 0.96));
            box-shadow: var(--platform-shadow);
            color: #fff;
        }

        .platform-landing .story-card h3 {
            margin: 14px 0 0;
            max-width: 16ch;
            font-size: clamp(2rem, 3vw, 2.8rem);
            line-height: 1.04;
            letter-spacing: -0.05em;
        }

        .platform-landing .story-card p {
            margin: 16px 0 0;
            max-width: 64ch;
            color: rgba(255, 255, 255, 0.76);
        }

        .platform-landing .cta-band {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 24px;
            margin-top: 28px;
            padding: 30px 32px;
            border: 1px solid rgba(91, 104, 235, 0.1);
            background: linear-gradient(135deg, #ffffff 0%, #eef5ff 100%);
        }

        .platform-landing .cta-band h3 {
            margin: 0;
            font-size: clamp(1.9rem, 3vw, 2.5rem);
            letter-spacing: -0.05em;
        }

        .platform-landing .cta-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        @media (max-width: 1080px) {
            .platform-landing .hero-badges,
            .platform-landing .mini-proof-grid,
            .platform-landing .metric-grid,
            .platform-landing .feature-grid,
            .platform-landing .process-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 991px) {
            .platform-landing .main-banner {
                padding-top: 145px;
            }

            .platform-landing .section-shell,
            .platform-landing .cta-band,
            .platform-landing .story-card {
                padding: 24px;
            }

            .platform-landing .cta-band,
            .platform-landing .section-head {
                display: grid;
            }
        }
    </style>
</head>
<body class="platform-landing">
    <header class="header-area header-sticky wow slideInDown" data-wow-duration="0.75s" data-wow-delay="0s">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <nav class="main-nav">
                        <a href="{{ route('platform.landing') }}" class="logo">
                            <img src="{{ asset('chain-1.0.0/assets/images/logo.png') }}" alt="POS Platform">
                        </a>
                        <ul class="nav">
                            <li><a href="#top" class="active">Home</a></li>
                            <li><a href="#solusi">Solusi</a></li>
                            <li><a href="#fitur">Fitur</a></li>
                            <li><a href="#proses">Proses</a></li>
                            <li><a href="{{ route('platform.login') }}">Admin</a></li>
                            <li><div class="gradient-button"><a href="{{ route('platform.register.create') }}"><i class="fa fa-rocket"></i> Daftar Sekarang</a></div></li>
                        </ul>
                        <a class="menu-trigger">
                            <span>Menu</span>
                        </a>
                    </nav>
                </div>
            </div>
        </div>
    </header>

    <div class="main-banner wow fadeIn" id="top" data-wow-duration="1s" data-wow-delay="0.5s">
        <div class="container">
            <div class="row align-items-start">
                <div class="col-lg-6 align-self-center">
                    <div class="left-content show-up header-text wow fadeInLeft" data-wow-duration="1s" data-wow-delay="0.7s">
                        <div class="section-heading mb-0">
                            <h2>Platform SaaS POS yang terlihat profesional dari landing sampai onboarding tenant</h2>
                            <img src="{{ asset('chain-1.0.0/assets/images/heading-line-dec.png') }}" alt="">
                        </div>
                        <p>
                            Sekarang visual landing dan `/daftar` sudah searah: gaya `Chain App` yang modern, bersih, dan lebih layak untuk dipakai
                            sebagai wajah publik produk. Fokusnya bukan hanya promosi, tapi juga mengarahkan calon tenant ke flow trial dan onboarding
                            yang terasa meyakinkan.
                        </p>

                        <div class="hero-buttons">
                            <div class="gradient-button">
                                <a href="{{ route('platform.register.create') }}">Mulai Trial Tenant</a>
                                <a href="{{ route('platform.renewal.create') }}">Perpanjang Langganan</a>
                                <a href="{{ route('platform.upgrade.create') }}">Upgrade Paket</a>
                            </div>
                            <div class="white-button">
                                <a href="{{ route('platform.login') }}">Login Platform Admin</a>
                            </div>
                        </div>

                        <div class="hero-badges">
                            <div class="hero-badge">
                                <strong>Onboarding lebih rapi</strong>
                                <span>Registrasi tenant publik, draft resume, dan monitoring admin sudah nyambung dalam satu flow.</span>
                            </div>
                            <div class="hero-badge">
                                <strong>Siap operasional</strong>
                                <span>Provisioning, bootstrap, inbox, audit, dan renewal tetap jalan di belakang layar.</span>
                            </div>
                            <div class="hero-badge">
                                <strong>Lebih siap jual</strong>
                                <span>Tampilan sekarang lebih cocok dipakai untuk demo, campaign, dan pintu masuk tenant baru.</span>
                            </div>
                        </div>

                        <div class="mini-proof-grid">
                            <div class="proof-chip">Retail Chain</div>
                            <div class="proof-chip">F&amp;B Group</div>
                            <div class="proof-chip">Grosir</div>
                            <div class="proof-chip">Multi Cabang</div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="dashboard-shell wow fadeInRight" data-wow-duration="1s" data-wow-delay="0.6s">
                        <div class="dashboard-window">
                            <div class="dashboard-head">
                                <div>
                                    <strong>Business Control Center</strong>
                                    <span>Ringkasan tenant, trial, dan alert operasional dalam satu panel.</span>
                                </div>
                                <span class="live-chip">Live SaaS Ops</span>
                            </div>

                            <div class="metric-grid">
                                <div class="metric-card">
                                    <strong>154</strong>
                                    <span>Tenant aktif yang siap dipantau dari admin console.</span>
                                </div>
                                <div class="metric-card">
                                    <strong>14 Hari</strong>
                                    <span>Trial yang jelas untuk onboarding awal tenant baru.</span>
                                </div>
                                <div class="metric-card">
                                    <strong>98.7%</strong>
                                    <span>Tingkat provisioning berhasil untuk tenant yang sudah diproses.</span>
                                </div>
                            </div>

                            <div class="dashboard-list">
                                <div class="dashboard-row">
                                    <div>
                                        <strong>Kopi Sudut Kota</strong>
                                        <small>2 cabang aktif, owner siap login tenant app</small>
                                    </div>
                                    <span class="status-pill status-ready">Ready</span>
                                </div>
                                <div class="dashboard-row">
                                    <div>
                                        <strong>Gudang Prima Retail</strong>
                                        <small>Trial aktif, plan dan domain sudah dipetakan</small>
                                    </div>
                                    <span class="status-pill status-trial">Trial</span>
                                </div>
                                <div class="dashboard-row">
                                    <div>
                                        <strong>Rasa Nusantara Mart</strong>
                                        <small>Perlu follow-up renewal dan inbox attention</small>
                                    </div>
                                    <span class="status-pill status-alert">Alert</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section id="solusi" class="services section">
        <div class="container">
            <div class="section-shell">
                <div class="section-head">
                    <div>
                        <span class="section-chip">Positioning Baru</span>
                        <h2>Landing ini sekarang bicara seperti produk SaaS yang benar-benar siap dijual.</h2>
                    </div>
                    <p class="section-copy">
                        Saya samakan gaya visualnya dengan halaman `/daftar` supaya user tidak merasa pindah ke dunia yang berbeda saat menekan CTA.
                        Jadi dari halaman promosi ke form registrasi, ritmenya tetap terasa satu brand, satu sistem, dan satu pengalaman.
                    </p>
                </div>

                <div class="feature-grid">
                    <article class="feature-card">
                        <span class="feature-tag">Trust</span>
                        <strong>Lebih meyakinkan di first impression</strong>
                        <p>Hero, CTA, dashboard preview, dan section penjelas sekarang lebih mirip landing SaaS premium daripada halaman internal biasa.</p>
                    </article>
                    <article class="feature-card">
                        <span class="feature-tag">Flow</span>
                        <strong>CTA nyambung langsung ke registrasi</strong>
                        <p>User diarahkan ke `/daftar` yang sekarang tampil dengan bahasa visual yang sama, jadi conversion flow terasa jauh lebih halus.</p>
                    </article>
                    <article class="feature-card">
                        <span class="feature-tag">Ops</span>
                        <strong>Tetap aman untuk backend yang sudah ada</strong>
                        <p>Yang berubah adalah tampilan dan cara menyajikan value, bukan kestabilan route, form, draft, email, dan operasional admin.</p>
                    </article>
                </div>
            </div>
        </div>
    </section>

    <section id="fitur" class="about-us section">
        <div class="container">
            <div class="section-shell">
                <div class="section-head">
                    <div>
                        <span class="section-chip">Fitur Utama</span>
                        <h2>Dirancang untuk alur multi-tenant yang rapi dari marketing sampai operasional.</h2>
                    </div>
                    <p class="section-copy">
                        Produk ini sekarang lebih mudah dijelaskan ke calon tenant: daftar, trial, provisioning, monitoring, renewal, dan email alert
                        semuanya punya tempat yang jelas dalam perjalanan user.
                    </p>
                </div>

                <div class="feature-grid">
                    <article class="feature-card">
                        <span class="feature-tag">Register</span>
                        <strong>Pendaftaran publik yang lebih proper</strong>
                        <p>Form tenant baru sudah punya draft otomatis, server-side resume, dan halaman sukses yang lebih jelas.</p>
                    </article>
                    <article class="feature-card">
                        <span class="feature-tag">Inbox</span>
                        <strong>Operasional harian lebih terlihat</strong>
                        <p>Admin sekarang bisa melihat attention item, renewal, expired, dan activity feed dari inbox khusus.</p>
                    </article>
                    <article class="feature-card">
                        <span class="feature-tag">Audit</span>
                        <strong>Jejak perubahan lebih transparan</strong>
                        <p>Audit trail tenant membuat history perubahan, recovery, status, dan follow-up jadi lebih mudah dilacak.</p>
                    </article>
                </div>
            </div>
        </div>
    </section>

    <section id="proses" class="pricing section">
        <div class="container">
            <div class="section-shell">
                <div class="section-head">
                    <div>
                        <span class="section-chip">Cara Kerja</span>
                        <h2>Perjalanan tenant dibuat singkat, jelas, dan siap didukung tim internal.</h2>
                    </div>
                    <p class="section-copy">
                        Dengan struktur ini, calon pelanggan lebih mudah paham bahwa platform Anda bukan cuma software POS,
                        tapi juga sistem onboarding dan operasional SaaS yang matang.
                    </p>
                </div>

                <div class="process-grid">
                    <article class="process-card">
                        <span class="process-step">Step 1</span>
                        <strong>Daftar tenant</strong>
                        <p>Calon tenant masuk dari landing ini lalu lanjut ke halaman `/daftar` yang sekarang bertema sama dan lebih fokus ke conversion.</p>
                    </article>
                    <article class="process-card">
                        <span class="process-step">Step 2</span>
                        <strong>Sistem memproses onboarding</strong>
                        <p>Tenant dicatat, subscription dibuat, progres bisa dipantau, dan admin tetap punya backup action bila diperlukan.</p>
                    </article>
                    <article class="process-card">
                        <span class="process-step">Step 3</span>
                        <strong>Operasional dan renewal</strong>
                        <p>Setelah tenant aktif, platform tetap mengirim alert renewal, expiry, dan event penting ke inbox maupun email.</p>
                    </article>
                </div>
            </div>
        </div>
    </section>

    <section class="the-clients">
        <div class="container">
            <div class="story-card">
                <span class="section-chip" style="background: rgba(255,255,255,0.12); color: #fff;">Satu Tema</span>
                <h3>Landing dan form daftar sekarang sudah terasa seperti satu produk yang utuh.</h3>
                <p>
                    Itu yang tadi kamu maksud, dan sekarang sudah saya arahkan ke sana: halaman depan dan halaman registrasi
                    tidak lagi terasa terpisah. Mereka sekarang punya rasa visual yang sama, CTA yang selaras, dan ritme konten
                    yang konsisten untuk onboarding tenant baru.
                </p>
            </div>
        </div>
    </section>

    <section class="footer" style="padding-bottom:60px;">
        <div class="container">
            <div class="cta-band">
                <div>
                    <h3>Siap arahkan traffic ke halaman daftar yang sekarang lebih premium?</h3>
                    <p>Gunakan landing ini sebagai pintu masuk utama campaign, presentasi sales, atau invite onboarding tenant baru.</p>
                </div>
                <div class="cta-actions">
                    <div class="gradient-button">
                        <a href="{{ route('platform.register.create') }}">Buka /daftar</a>
                    </div>
                    <div class="white-button">
                        <a href="{{ route('platform.login') }}">Login Admin</a>
                    </div>
                    <div class="white-button">
                        <a href="/login">Login Tenant</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="{{ asset('chain-1.0.0/vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('chain-1.0.0/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('chain-1.0.0/assets/js/custom.js') }}"></script>
</body>
</html>
