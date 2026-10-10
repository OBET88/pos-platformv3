<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Kontak | RWP POS</title>
    <meta name="description" content="Hubungi tim RWP POS untuk tanya paket, demo fitur, onboarding tenant, dan langkah langganan yang paling sesuai untuk bisnis Anda.">
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
            --wa: #25d366;
            --wa-deep: #128c7e;
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
        .contact-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
            margin-top: 28px;
        }
        .contact-item {
            padding: 20px;
            border-radius: 22px;
            background: #fff;
            border: 1px solid var(--border);
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        }
        .contact-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 36px rgba(148, 163, 184, 0.14);
            border-color: rgba(124,58,237,0.16);
        }
        .contact-item h2 {
            margin: 0 0 8px;
            font-size: 18px;
        }
        .contact-item a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
        }
        .contact-item a.wa-link {
            color: var(--wa-deep);
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
        .btn-whatsapp {
            color: #fff;
            background: linear-gradient(135deg, var(--wa), var(--wa-deep));
            box-shadow: 0 18px 34px rgba(37, 211, 102, 0.22);
        }
        .contact-highlight {
            margin-top: 22px;
            padding: 18px 20px;
            border-radius: 22px;
            background: linear-gradient(135deg, rgba(124,58,237,0.06), rgba(236,72,153,0.06));
            border: 1px solid rgba(124,58,237,0.1);
            color: var(--text);
        }
        .contact-highlight strong {
            display: block;
            margin-bottom: 6px;
            font-size: 15px;
        }
        .contact-highlight span {
            color: var(--muted);
            line-height: 1.7;
        }
        @media (max-width: 900px) {
            .contact-grid { grid-template-columns: 1fr; }
            .card { padding: 24px; }
            .topbar { padding-top: 18px; }
        }
    </style>
</head>
<body>
    @php
        $waNumber = '6282173030483';
        $waMessage = "Halo tim RWP POS, saya ingin bertanya dan memesan aplikasi RWP POS.\n\nMohon info paket, demo fitur, dan langkah selanjutnya.\nNama bisnis: \nKebutuhan saya: ";
        $waLink = 'https://wa.me/' . $waNumber . '?text=' . urlencode($waMessage);
    @endphp
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
            <a href="{{ route('platform.blog') }}" class="topbar-link">Blog</a>
            <a href="{{ route('platform.register.create') }}" class="topbar-link">Daftar Tenant</a>
        </div>
    </div>
    <div class="wrap">
        <div class="card">
            <div class="eyebrow">Kontak</div>
            <h1>Hubungi tim RWP POS untuk demo, tanya paket, atau bahas onboarding tenant.</h1>
            <p>Kalau Anda ingin diskusi soal paket, kebutuhan outlet, atau langkah go-live, jalur tercepatnya lewat WhatsApp. Tim kami siap bantu arahkan opsi yang paling masuk akal untuk bisnis Anda.</p>
            <div class="section-note">WhatsApp utama: <strong>082173030483</strong>. Silakan kirim pesan singkat, nanti tim akan bantu lanjutkan.</div>
            <div class="contact-highlight">
                <strong>Respon cepat via WhatsApp</strong>
                <span>Untuk pertanyaan paket, demo fitur, atau pemesanan aplikasi, hubungi nomor <strong>082173030483</strong> atau klik tombol WhatsApp di bawah. Pesan awal sudah disiapkan agar kamu tinggal isi nama bisnis dan kebutuhan.</span>
            </div>

            <div class="contact-grid">
                <section class="contact-item">
                    <h2>WhatsApp Support</h2>
                    <p>Langsung chat untuk tanya fitur, paket, atau minta arahan pendaftaran.</p>
                    <a class="wa-link" href="{{ $waLink }}" target="_blank" rel="noopener">082173030483</a>
                </section>
                <section class="contact-item">
                    <h2>Mulai Daftar Tenant</h2>
                    <p>Kalau sudah siap lanjut, buka form pendaftaran tenant lalu pilih paket yang paling sesuai.</p>
                    <a href="{{ route('platform.register.create') }}">Buka form daftar</a>
                </section>
                <section class="contact-item">
                    <h2>Lihat Help Center</h2>
                    <p>Masih ingin memahami fitur dan alur pemakaian sebelum chat? Buka halaman bantuan dulu.</p>
                    <a href="{{ route('platform.help') }}">Buka Help Center</a>
                </section>
            </div>

            <div class="actions">
                <a href="{{ $waLink }}" class="btn btn-whatsapp" target="_blank" rel="noopener">Chat WhatsApp</a>
                <a href="{{ route('platform.register.create') }}" class="btn btn-primary">Mulai Daftar Tenant</a>
                <a href="{{ route('platform.landing') }}" class="btn btn-secondary">Kembali ke Landing</a>
            </div>
        </div>
    </div>
</body>
</html>
