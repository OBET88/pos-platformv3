<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Help Center | RWP POS</title>
    <meta name="description" content="Pelajari fitur utama RWP POS, alur onboarding tenant, dan jawaban singkat untuk kebutuhan retail, F&B, dan operasional multi cabang.">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root {
            --bg: #f9f7ff;
            --panel: rgba(255, 255, 255, 0.9);
            --panel-strong: #ffffff;
            --line: rgba(148, 163, 184, 0.22);
            --text: #1f2937;
            --muted: #6b7280;
            --brand: #8b62f3;
            --brand-soft: #f3ebff;
            --brand-deep: #7a3df0;
            --accent: #ec63ef;
            --accent-soft: #fbe7fb;
            --shadow: 0 24px 60px rgba(91, 33, 182, 0.1);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Poppins", sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at top left, rgba(236, 99, 239, 0.16), transparent 28%),
                radial-gradient(circle at right 15%, rgba(139, 98, 243, 0.16), transparent 24%),
                linear-gradient(180deg, #f9f7ff 0%, #ffffff 100%);
        }

        a { color: inherit; }

        .shell {
            width: min(1180px, calc(100% - 32px));
            margin: 0 auto;
            padding: 24px 0 72px;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 14px;
            text-decoration: none;
        }

        .brand-mark {
            width: 56px;
            height: 56px;
            border-radius: 18px;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, #8b62f3, #ec63ef);
            color: #fff;
            font-size: 24px;
            font-weight: 800;
            box-shadow: 0 18px 36px rgba(156, 91, 240, 0.24);
        }

        .brand-copy strong {
            display: block;
            font-size: 18px;
            line-height: 1;
        }

        .brand-copy span {
            display: block;
            margin-top: 6px;
            font-size: 11px;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: #7c3aed;
        }

        .nav-links {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .nav-link,
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 46px;
            padding: 0 18px;
            border-radius: 999px;
            text-decoration: none;
            font-weight: 600;
        }

        .nav-link {
            background: rgba(255, 255, 255, 0.72);
            border: 1px solid rgba(139, 98, 243, 0.14);
            color: var(--brand-deep);
            transition: transform .18s ease, box-shadow .18s ease, background-color .18s ease, color .18s ease;
        }
        .nav-link:hover { transform: translateY(-1px); background: rgba(255,255,255,0.92); }

        .hero {
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) minmax(320px, 0.8fr);
            gap: 22px;
            margin-bottom: 22px;
        }

        .panel {
            background: var(--panel);
            border: 1px solid rgba(255, 255, 255, 0.75);
            border-radius: 30px;
            box-shadow: var(--shadow);
            backdrop-filter: blur(18px);
        }

        .hero-main {
            padding: 34px;
            position: relative;
            overflow: hidden;
        }

        .hero-main::after {
            content: "";
            position: absolute;
            inset: auto -40px -60px auto;
            width: 220px;
            height: 220px;
            border-radius: 999px;
            background: radial-gradient(circle, rgba(236, 99, 239, 0.18), transparent 70%);
        }

        .eyebrow {
            display: inline-flex;
            padding: 8px 14px;
            border-radius: 999px;
            background: var(--brand-soft);
            color: var(--brand-deep);
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        h1 {
            margin: 18px 0 14px;
            font-size: clamp(34px, 5vw, 56px);
            line-height: 1.02;
            max-width: 11ch;
        }

        .lead {
            margin: 0;
            max-width: 640px;
            color: var(--muted);
            font-size: 16px;
            line-height: 1.8;
        }

        .hero-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            margin-top: 28px;
        }

        .stat {
            padding: 18px;
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.72);
            border: 1px solid rgba(148, 163, 184, 0.16);
        }

        .stat strong {
            display: block;
            font-size: 28px;
            color: var(--brand-deep);
        }

        .stat span {
            display: block;
            margin-top: 6px;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.6;
        }

        .hero-side {
            padding: 28px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .mini-card {
            padding: 18px 18px 20px;
            border-radius: 24px;
            background: var(--panel-strong);
            border: 1px solid var(--line);
        }

        .mini-card .tag {
            display: inline-flex;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #7c3aed;
            background: var(--accent-soft);
        }

        .mini-card h2 {
            margin: 14px 0 8px;
            font-size: 20px;
        }

        .mini-card p,
        .feature-card p,
        .journey-card p,
        .section-head p,
        .quick-item p,
        .faq-card p {
            margin: 0;
            color: var(--muted);
            line-height: 1.75;
            font-size: 14px;
        }

        .mini-list {
            margin: 16px 0 0;
            padding: 0;
            list-style: none;
            display: grid;
            gap: 10px;
        }

        .mini-list li {
            padding: 12px 14px;
            border-radius: 16px;
            background: #f5f3ff;
            color: #6d28d9;
            font-size: 13px;
            font-weight: 600;
        }

        .section {
            margin-top: 22px;
            padding: 32px;
        }

        .section-head {
            display: flex;
            align-items: end;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 22px;
            flex-wrap: wrap;
        }

        .section-head h2 {
            margin: 0 0 8px;
            font-size: 30px;
        }

        .section-kicker {
            display: inline-flex;
            padding: 7px 12px;
            border-radius: 999px;
            background: rgba(139, 98, 243, 0.08);
            color: #7c3aed;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
        }

        .feature-card {
            padding: 22px;
            border-radius: 24px;
            background: var(--panel-strong);
            border: 1px solid var(--line);
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        }
        .feature-card:hover,
        .journey-card:hover,
        .quick-item:hover,
        .faq-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 36px rgba(148, 163, 184, 0.14);
            border-color: rgba(139, 98, 243, 0.16);
        }

        .feature-icon {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            margin-bottom: 16px;
            font-size: 20px;
            font-weight: 700;
            color: #fff;
            background: linear-gradient(135deg, #8b62f3, #ec63ef);
            box-shadow: 0 12px 24px rgba(156, 91, 240, 0.18);
        }

        .feature-card h3,
        .journey-card h3,
        .quick-item h3,
        .faq-card h3 {
            margin: 0 0 8px;
            font-size: 18px;
        }

        .journey-grid,
        .quick-grid,
        .faq-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
        }

        .journey-card,
        .quick-item,
        .faq-card {
            padding: 22px;
            border-radius: 24px;
            background: var(--panel-strong);
            border: 1px solid var(--line);
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        }

        .journey-step {
            display: inline-flex;
            min-width: 38px;
            height: 38px;
            padding: 0 12px;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            margin-bottom: 14px;
            background: #f5f3ff;
            color: var(--brand-deep);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .05em;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 24px;
        }

        .btn-primary {
            color: #fff;
            background: linear-gradient(135deg, #8b62f3, #ec63ef);
            box-shadow: 0 18px 34px rgba(156, 91, 240, 0.22);
        }

        .btn-secondary {
            color: var(--brand-deep);
            background: rgba(243, 235, 255, 0.88);
            border: 1px solid rgba(139, 98, 243, 0.12);
        }

        .support-banner {
            margin-top: 22px;
            padding: 30px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            flex-wrap: wrap;
            background:
                linear-gradient(135deg, rgba(139, 98, 243, 0.96), rgba(236, 99, 239, 0.92)),
                #0f172a;
            color: #fff;
        }

        .support-banner h2 {
            margin: 0 0 8px;
            font-size: 30px;
        }

        .support-banner p {
            margin: 0;
            max-width: 680px;
            color: rgba(255, 255, 255, 0.82);
        }

        .support-banner .btn-secondary {
            background: rgba(255, 255, 255, 0.14);
            border-color: rgba(255, 255, 255, 0.22);
            color: #fff;
        }
        .btn:hover { transform: translateY(-1px); }

        @media (max-width: 980px) {
            .hero,
            .feature-grid,
            .journey-grid,
            .quick-grid,
            .faq-grid {
                grid-template-columns: 1fr;
            }

            .hero-stats {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .shell {
                width: min(100% - 20px, 1180px);
                padding-bottom: 36px;
            }

            .hero-main,
            .hero-side,
            .section,
            .support-banner {
                padding: 22px;
            }

            h1 {
                max-width: none;
                font-size: 34px;
            }
        }
    </style>
</head>
<body>
    <div class="shell">
        <div class="topbar">
            <a href="{{ route('platform.landing') }}" class="brand" aria-label="RWP POS">
                <span class="brand-mark">RP</span>
                <span class="brand-copy">
                    <strong>RWP POS</strong>
                    <span>Retail Platform</span>
                </span>
            </a>
            <div class="nav-links">
                <a href="{{ route('platform.landing') }}" class="nav-link">Home</a>
                <a href="{{ route('platform.blog') }}" class="nav-link">Blog</a>
                <a href="{{ route('platform.contact') }}" class="nav-link">Kontak</a>
                <a href="{{ route('platform.register.create') }}" class="nav-link">Daftar Tenant</a>
                <a href="{{ route('platform.renewal.create') }}" class="nav-link">Perpanjang</a>
                <a href="{{ route('platform.upgrade.create') }}" class="nav-link">Upgrade</a>
            </div>
        </div>

        <section class="hero">
            <div class="panel hero-main">
                <div class="eyebrow">Help Center</div>
                <h1>Pelajari alur dan fitur inti sebelum mulai onboarding tenant.</h1>
                

                <div class="hero-stats">
                    <div class="stat">
                        <strong>6+</strong>
                        <span>Modul inti untuk operasional harian, kontrol stok, dan pelaporan bisnis.</span>
                    </div>
                    <div class="stat">
                        <strong>1 Alur</strong>
                        <span>Order, pembayaran, update stok, dan rekap berjalan dalam proses yang saling terhubung.</span>
                    </div>
                    <div class="stat">
                        <strong>Siap Tumbuh</strong>
                        <span>Bisa mulai dari kasir lalu berkembang ke promo, meja, dan pencatatan keuangan.</span>
                    </div>
                </div>
            </div>

            <aside class="panel hero-side">
                <div class="mini-card">
                    <span class="tag">Cocok Untuk</span>
                    <h2>Retail, outlet, dan bisnis dine-in</h2>
                    <p>RWP POS dirancang untuk operasional yang butuh transaksi cepat, kontrol barang rapi, dan visibilitas laporan yang mudah dipahami owner.</p>
                </div>

                <div class="mini-card">
                    <span class="tag">Yang Sering Ditanya</span>
                    <ul class="mini-list">
                        <li>Apakah hanya untuk kasir? Tidak, ada stok, laporan, promo, dan akuntansi.</li>
                        <li>Apakah bisa untuk meja? Bisa, dengan floor map dan QR table order.</li>
                        <li>Apakah owner bisa pantau performa? Bisa, lewat laporan dan ringkasan operasional.</li>
                    </ul>
                </div>
            </aside>
        </section>

        <section class="panel section">
            <div class="section-head">
                <div>
                    <span class="section-kicker">Fitur Aplikasi</span>
                    <h2>Modul utama yang tersedia di RWP POS</h2>
                    
                </div>
            </div>

            <div class="feature-grid">
                <article class="feature-card">
                    <div class="feature-icon">01</div>
                    <h3>Kasir & Operasional Shift</h3>
                    <p>Transaksi penjualan, kontrol shift, dan alur kerja kasir berjalan cepat untuk membantu operasional toko tetap stabil di jam sibuk.</p>
                </article>
                <article class="feature-card">
                    <div class="feature-icon">02</div>
                    <h3>Laporan & Rekap Penjualan</h3>
                    <p>Owner bisa melihat laporan penjualan, rekap produk, laporan stok, dan ringkasan kasir untuk mendukung keputusan harian maupun mingguan.</p>
                </article>
                <article class="feature-card">
                    <div class="feature-icon">03</div>
                    <h3>Produk, Resep & Stok</h3>
                    <p>Manajemen produk, kategori, resep, stok masuk-keluar, dan kebutuhan produksi membantu bisnis menjaga ketersediaan barang jadi maupun bahan baku.</p>
                </article>
                <article class="feature-card">
                    <div class="feature-icon">04</div>
                    <h3>Promo & Pertumbuhan Penjualan</h3>
                    <p>Fitur promo membantu tenant menyusun penawaran yang lebih menarik agar transaksi meningkat tanpa mengorbankan kontrol operasional.</p>
                </article>
                <article class="feature-card">
                    <div class="feature-icon">05</div>
                    <h3>Floor Map & QR Table Order</h3>
                    <p>Untuk bisnis dine-in, meja dapat diatur lewat floor map dan pelanggan bisa memesan dari QR table order yang langsung terhubung ke kasir.</p>
                </article>
                <article class="feature-card">
                    <div class="feature-icon">06</div>
                    <h3>Akuntansi & Kontrol Keuangan</h3>
                    <p>Tenant yang membutuhkan pencatatan lebih rapi dapat memakai COA, entry keuangan admin, jurnal manual, dan laporan akuntansi sesuai paket aktif.</p>
                </article>
            </div>
        </section>

        <section class="panel section">
            <div class="section-head">
                <div>
                    <span class="section-kicker">Alur Pemakaian</span>
                    <h2>Gambaran singkat perjalanan tenant</h2>
                    <p>Ringkasan ini membantu calon tenant memahami apa yang terjadi sejak form dikirim sampai tim mulai memakai aplikasi di outlet.</p>
                </div>
            </div>

            <div class="journey-grid">
                <article class="journey-card">
                    <div class="journey-step">Step 1</div>
                    <h3>Daftar Tenant</h3>
                    <p>Owner mengisi form pendaftaran, lalu sistem memproses data tenant agar akun bisnis siap dibuat di platform.</p>
                </article>
                <article class="journey-card">
                    <div class="journey-step">Step 2</div>
                    <h3>Aktivasi & Provisioning</h3>
                    <p>Setelah tenant aktif, akun owner dan ruang kerja aplikasi disiapkan sehingga tim bisa segera mulai mengatur data awal.</p>
                </article>
                <article class="journey-card">
                    <div class="journey-step">Step 3</div>
                    <h3>Mulai Operasional</h3>
                    <p>Tenant dapat mengatur produk, stok, meja, promo, dan laporan sesuai modul yang tersedia pada paket yang dipilih.</p>
                </article>
            </div>

            <div class="actions">
                <a href="{{ route('platform.register.create') }}" class="btn btn-primary">Mulai Daftar Tenant</a>
                <a href="{{ route('platform.landing') }}" class="btn btn-secondary">Lihat Landing Page</a>
            </div>
        </section>

        <section class="panel section">
            <div class="section-head">
                <div>
                    <span class="section-kicker">Ringkasan Cepat</span>
                    <h2>Jawaban singkat sebelum Anda mencoba</h2>
                    
                </div>
            </div>

            <div class="quick-grid">
                <article class="quick-item">
                    <h3>Tidak berhenti di kasir</h3>
                    <p>RWP POS bukan hanya tempat input transaksi, tetapi juga pusat kontrol operasional, stok, dan pelaporan.</p>
                </article>
                <article class="quick-item">
                    <h3>Bisa dipakai untuk dine-in</h3>
                    <p>Floor map dan QR table order membuat pengalaman meja lebih rapi tanpa memutus alur kerja kasir.</p>
                </article>
                <article class="quick-item">
                    <h3>Bertahap sesuai kebutuhan</h3>
                    <p>Bisnis bisa mulai dari modul dasar, lalu berkembang ke promo dan akuntansi seiring operasional makin kompleks.</p>
                </article>
            </div>
        </section>

        <section class="panel section">
            <div class="section-head">
                <div>
                    <span class="section-kicker">FAQ Fitur</span>
                    <h2>Pertanyaan yang paling sering muncul</h2>
                    
                </div>
            </div>

            <div class="faq-grid">
                <article class="faq-card">
                    <h3>Apakah aplikasi ini hanya cocok untuk retail?</h3>
                    <p>Tidak. Selain retail dan outlet, aplikasi ini juga mendukung skenario dine-in melalui pengelolaan meja dan QR table order.</p>
                </article>
                <article class="faq-card">
                    <h3>Apakah owner bisa memantau performa tanpa ikut jaga kasir?</h3>
                    <p>Bisa. Laporan penjualan, rekap stok, dan ringkasan operasional membantu owner tetap memantau bisnis dari data yang tercatat.</p>
                </article>
                <article class="faq-card">
                    <h3>Apakah fitur akuntansi langsung tersedia untuk semua paket?</h3>
                    <p>Fitur akuntansi mengikuti paket aktif tenant. Jadi modul keuangan yang tampil akan menyesuaikan konfigurasi paket dari platform.</p>
                </article>
            </div>
        </section>

        <section class="panel support-banner">
            <div>
                <h2>Siap melihat alur pendaftaran tenant?</h2>
                <p>Jika fitur-fiturnya sudah sesuai, lanjutkan ke form pendaftaran untuk memulai proses aktivasi tenant di RWP POS.</p>
            </div>
            <div class="actions">
                <a href="{{ route('platform.register.create') }}" class="btn btn-primary">Buka Form Daftar</a>
                <a href="{{ route('platform.landing') }}" class="btn btn-secondary">Kembali ke Landing</a>
            </div>
        </section>
    </div>
</body>
</html>
