<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Blog & Update | RWP POS</title>
    <meta name="description" content="Halaman update RWP POS untuk insight produk, alur onboarding tenant, dan ringkasan fitur yang relevan untuk retail, F&B, dan multi cabang.">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root {
            --bg: #fff8fc;
            --panel: rgba(255,255,255,0.94);
            --border: rgba(226, 232, 240, 0.95);
            --text: #0f172a;
            --muted: #64748b;
            --accent: #7c3aed;
            --accent-2: #ec4899;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Poppins", sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at top left, rgba(236,72,153,0.08), transparent 26%),
                radial-gradient(circle at bottom right, rgba(124,58,237,0.08), transparent 28%),
                var(--bg);
        }
        .topbar {
            width: min(1080px, calc(100% - 32px));
            margin: 0 auto;
            padding: 24px 0 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }
        .brand {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: var(--text);
        }
        .brand-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 54px;
            height: 54px;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            color: #fff;
            font-size: 24px;
            font-weight: 700;
        }
        .brand-copy strong {
            display: block;
            font-size: 18px;
            line-height: 1;
        }
        .brand-copy span {
            display: block;
            margin-top: 6px;
            color: var(--muted);
            font-size: 11px;
            letter-spacing: .12em;
            text-transform: uppercase;
        }
        .topbar-links {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .topbar-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            padding: 0 16px;
            border-radius: 999px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            color: var(--accent);
            background: rgba(124,58,237,0.06);
            border: 1px solid rgba(124,58,237,0.12);
            transition: transform .18s ease, box-shadow .18s ease, background-color .18s ease, color .18s ease;
        }
        .topbar-link:hover { transform: translateY(-1px); background: rgba(124,58,237,0.1); }
        .wrap {
            width: min(1080px, calc(100% - 32px));
            margin: 0 auto;
            padding: 24px 0 64px;
        }
        .card {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 28px;
            padding: 32px;
            box-shadow: 0 24px 60px rgba(148, 163, 184, 0.14);
        }
        .section-note {
            margin-top: 20px;
            padding: 14px 16px;
            border-radius: 18px;
            background: rgba(124,58,237,0.05);
            border: 1px solid rgba(124,58,237,0.1);
            color: var(--muted);
            font-size: 14px;
            line-height: 1.7;
        }
        .eyebrow {
            display: inline-flex;
            padding: 8px 14px;
            border-radius: 999px;
            background: rgba(124,58,237,0.08);
            color: var(--accent);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
        }
        h1 {
            margin: 18px 0 10px;
            font-size: clamp(32px, 5vw, 48px);
            line-height: 1.05;
        }
        p {
            margin: 0;
            color: var(--muted);
            font-size: 16px;
            line-height: 1.8;
        }
        .posts {
            display: grid;
            gap: 18px;
            margin-top: 28px;
        }
        .post {
            padding: 22px;
            border-radius: 22px;
            background: #fff;
            border: 1px solid var(--border);
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        }
        .post:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 36px rgba(148, 163, 184, 0.14);
            border-color: rgba(124,58,237,0.16);
        }
        .post span {
            display: inline-flex;
            margin-bottom: 10px;
            color: var(--accent);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .05em;
            text-transform: uppercase;
        }
        .post h2 {
            margin: 0 0 8px;
            font-size: 20px;
        }
        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 28px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 48px;
            padding: 0 20px;
            border-radius: 999px;
            text-decoration: none;
            font-weight: 700;
            transition: transform .18s ease, box-shadow .18s ease, background-color .18s ease, color .18s ease;
        }
        .btn:hover { transform: translateY(-1px); }
        .btn-primary {
            color: #fff;
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            box-shadow: 0 18px 34px rgba(124,58,237,0.2);
        }
        .btn-secondary {
            color: var(--accent);
            background: rgba(124,58,237,0.06);
            border: 1px solid rgba(124,58,237,0.12);
        }
        @media (max-width: 900px) {
            .card { padding: 24px; }
            .topbar { padding-top: 18px; }
        }
    </style>
</head>
<body>
    <div class="topbar">
        <a href="{{ route('platform.landing') }}" class="brand" aria-label="RWP POS">
            <span class="brand-badge">RP</span>
            <span class="brand-copy">
                <strong>RWP POS</strong>
                <span>Retail Platform</span>
            </span>
        </a>
        <div class="topbar-links">
            <a href="{{ route('platform.landing') }}" class="topbar-link">Home</a>
            <a href="{{ route('platform.help') }}" class="topbar-link">Help Center</a>
            <a href="{{ route('platform.contact') }}" class="topbar-link">Kontak</a>
            <a href="{{ route('platform.register.create') }}" class="topbar-link">Daftar Tenant</a>
        </div>
    </div>
    <div class="wrap">
        <div class="card">
            <div class="eyebrow">Blog & Update</div>
            <h1>Tempat untuk update produk, insight operasional, dan panduan onboarding.</h1>
            <p>Halaman ini sekarang berfungsi sebagai landing aman untuk konten artikel dan update. Nanti bisa diisi listing blog yang sesungguhnya tanpa perlu mengubah struktur public page yang sudah ada.</p>
            <div class="section-note">Saat ini isinya masih ringkasan editorial, tetapi sudah cukup rapi untuk dipakai sebagai tujuan menu, footer, dan CTA informasi dari halaman publik lainnya.</div>

            <div class="posts">
                <article class="post">
                    <span>Update</span>
                    <h2>Kenali alur daftar tenant yang baru</h2>
                    <p>Mulai dari pilih paket, isi data owner, verifikasi email, sampai tenant siap dipakai.</p>
                </article>
                <article class="post">
                    <span>Fitur</span>
                    <h2>Apa saja modul inti di RWP POS</h2>
                    <p>Kasir, laporan, stok, promo, QR order, dan akuntansi tersedia sesuai paket yang dipilih.</p>
                </article>
                <article class="post">
                    <span>Panduan</span>
                    <h2>Jalur cepat untuk calon pelanggan baru</h2>
                    <p>Landing, FAQ, bantuan umum, dan form daftar sekarang sudah saling terhubung untuk pengalaman awal yang lebih jelas.</p>
                </article>
            </div>

            <div class="actions">
                <a href="{{ route('platform.register.create') }}" class="btn btn-primary">Coba Alur Daftar</a>
                <a href="{{ route('platform.landing') }}" class="btn btn-secondary">Kembali ke Landing</a>
            </div>
        </div>
    </div>
</body>
</html>
