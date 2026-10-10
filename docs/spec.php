<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Master UAT POS Platform + POSLive</title>
    <style>
        :root {
            --bg: #eef3f7;
            --paper: #ffffff;
            --ink: #122033;
            --muted: #56657a;
            --line: #d8e1ea;
            --soft: #f7fafc;
            --brand: #0f766e;
            --brand-deep: #0b3a53;
            --accent: #d97706;
            --accent-soft: #fff4db;
            --ok-soft: #dcfce7;
            --ok-line: #86efac;
            --warn-soft: #fef3c7;
            --warn-line: #facc15;
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            background:
                radial-gradient(circle at top right, rgba(15, 118, 110, 0.08), transparent 24%),
                linear-gradient(180deg, #f8fbfd 0%, var(--bg) 100%);
            color: var(--ink);
            font-family: "Segoe UI", Tahoma, sans-serif;
            line-height: 1.58;
        }

        a {
            color: inherit;
        }

        .shell {
            max-width: 1360px;
            margin: 0 auto;
            padding: 24px;
        }

        .hero {
            border-radius: 30px;
            padding: 32px;
            background: linear-gradient(135deg, #0f172a 0%, #123b57 55%, #0f766e 100%);
            color: white;
            box-shadow: 0 30px 70px rgba(18, 32, 51, 0.18);
        }

        .hero-top {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            align-items: flex-start;
            justify-content: space-between;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.14);
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .12em;
        }

        .hero h1 {
            margin: 14px 0 10px;
            font-size: 38px;
            line-height: 1.12;
        }

        .hero p {
            margin: 0;
            max-width: 860px;
            color: rgba(255, 255, 255, 0.88);
        }

        .hero-stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
            margin-top: 24px;
        }

        .hero-stat {
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 18px;
            padding: 14px 16px;
            background: rgba(255, 255, 255, 0.08);
        }

        .hero-stat strong {
            display: block;
            margin-bottom: 4px;
            font-size: 14px;
        }

        .hero-stat span {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.84);
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 24px;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 16px;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            background: rgba(255, 255, 255, 0.08);
            color: white;
            text-decoration: none;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
        }

        .button.light {
            background: white;
            color: var(--ink);
            border-color: white;
        }

        .layout {
            display: grid;
            grid-template-columns: 320px minmax(0, 1fr);
            gap: 24px;
            margin-top: 24px;
        }

        .toc,
        .card {
            background: var(--paper);
            border: 1px solid var(--line);
            border-radius: 26px;
            box-shadow: 0 14px 36px rgba(18, 32, 51, 0.06);
        }

        .toc {
            position: sticky;
            top: 20px;
            align-self: start;
            padding: 20px;
            max-height: calc(100vh - 40px);
            overflow: auto;
        }

        .toc h2 {
            margin: 0 0 14px;
            font-size: 15px;
        }

        .toc-group + .toc-group {
            margin-top: 14px;
            padding-top: 14px;
            border-top: 1px solid var(--line);
        }

        .toc-label {
            margin-bottom: 8px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--muted);
        }

        .toc a {
            display: block;
            padding: 8px 10px;
            border-radius: 12px;
            text-decoration: none;
            color: var(--muted);
            font-size: 14px;
        }

        .toc a:hover {
            background: var(--soft);
            color: var(--ink);
        }

        .content {
            display: grid;
            gap: 18px;
        }

        .card {
            padding: 24px 26px;
        }

        .card h2 {
            margin: 0 0 12px;
            font-size: 26px;
            line-height: 1.18;
        }

        .card h3 {
            margin: 22px 0 10px;
            font-size: 18px;
        }

        .card h4 {
            margin: 18px 0 8px;
            font-size: 15px;
        }

        .card p {
            margin: 0 0 10px;
            color: var(--muted);
        }

        .card ul,
        .card ol {
            margin: 8px 0 0 20px;
            padding: 0;
            color: var(--muted);
        }

        .card li + li {
            margin-top: 6px;
        }

        .grid-2,
        .grid-3,
        .grid-4 {
            display: grid;
            gap: 14px;
        }

        .grid-2 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .grid-3 {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .grid-4 {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .mini {
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 14px 16px;
            background: var(--soft);
        }

        .mini h4,
        .mini h5 {
            margin: 0 0 8px;
            font-size: 14px;
        }

        .mini p {
            margin: 0;
            font-size: 14px;
        }

        .callout {
            border-radius: 18px;
            padding: 14px 16px;
            border: 1px solid transparent;
        }

        .callout.info {
            background: #eff6ff;
            border-color: #bfdbfe;
        }

        .callout.ok {
            background: var(--ok-soft);
            border-color: var(--ok-line);
        }

        .callout.warn {
            background: var(--warn-soft);
            border-color: var(--warn-line);
        }

        .callout p {
            margin: 0;
            color: var(--ink);
        }

        .pill-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 12px;
        }

        .pill {
            display: inline-flex;
            align-items: center;
            padding: 7px 11px;
            border-radius: 999px;
            border: 1px solid var(--line);
            background: white;
            font-size: 12px;
            font-weight: 700;
            color: var(--muted);
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            margin-top: 12px;
        }

        .menu-box {
            border: 1px solid var(--line);
            border-radius: 20px;
            padding: 16px;
            background: #fcfdff;
        }

        .menu-box h4 {
            margin: 0 0 10px;
            font-size: 15px;
        }

        .menu-box ul {
            margin-left: 18px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            font-size: 14px;
        }

        th,
        td {
            border: 1px solid var(--line);
            padding: 10px 12px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #f7fafc;
            color: var(--ink);
        }

        .status-table td:last-child,
        .status-table th:last-child {
            width: 110px;
            text-align: center;
        }

        .section-head {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: baseline;
            justify-content: space-between;
        }

        .section-head p {
            margin: 0;
        }

        .signatures {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
            margin-top: 24px;
        }

        .signature {
            margin-top: 48px;
            padding-top: 10px;
            border-top: 1px solid var(--ink);
            text-align: center;
            font-size: 13px;
            color: var(--muted);
        }

        .muted {
            color: var(--muted);
        }

        @media (max-width: 1100px) {
            .layout {
                grid-template-columns: 1fr;
            }

            .toc {
                position: static;
                max-height: none;
            }

            .hero-stats,
            .grid-2,
            .grid-3,
            .grid-4,
            .menu-grid,
            .signatures {
                grid-template-columns: 1fr;
            }
        }

        @media print {
            body {
                background: white;
            }

            .shell {
                max-width: none;
                padding: 0;
            }

            .hero {
                color: black;
                background: white;
                border: 1px solid var(--line);
                box-shadow: none;
            }

            .badge,
            .button {
                color: black;
                background: white;
                border-color: var(--line);
            }

            .hero p,
            .hero-stat span {
                color: #334155;
            }

            .layout {
                display: block;
            }

            .toc {
                display: none;
            }

            .card,
            .mini,
            .menu-box {
                box-shadow: none;
                break-inside: avoid;
            }
        }
    </style>
</head>
<body>
<div class="shell">
    <section class="hero">
        <div class="hero-top">
            <div>
                <div class="badge">Master UAT HTML</div>
                <h1>UAT Lengkap Semua Menu POS Platform + POSLive</h1>
                <p>Dokumen ini adalah versi kerja utama untuk UAT end-to-end. Isinya dibuat lengkap agar tester, operasional, finance, dan tim dev bisa memakai satu acuan yang sama dari onboarding tenant, master data, diskon, voucher, jadwal jual, POS, shift, laporan, sampai akuntansi dan langganan.</p>
            </div>
        </div>
        <div class="hero-stats">
            <div class="hero-stat">
                <strong>Cakupan</strong>
                <span>Semua menu tenant yang aktif saat ini</span>
            </div>
            <div class="hero-stat">
                <strong>Fokus</strong>
                <span>UI, alur bisnis, laporan, dan hasil akuntansi</span>
            </div>
            <div class="hero-stat">
                <strong>Tester</strong>
                <span>Owner, admin, kasir, QA, finance</span>
            </div>
            <div class="hero-stat">
                <strong>Mode</strong>
                <span>Bisa dibuka di browser dan diprint</span>
            </div>
        </div>
        <div class="hero-actions">
            <button class="button light" onclick="window.print()">Print Halaman Ini</button>
            <a class="button" href="uat-print.html">Buka Versi Print Ringkas</a>
        </div>
    </section>

    <div class="layout">
        <aside class="toc">
            <h2>Daftar Isi</h2>

            <div class="toc-group">
                <div class="toc-label">Dasar</div>
                <a href="#tujuan">Tujuan Dan Cara Pakai</a>
                <a href="#menu-audit">Cakupan Semua Menu</a>
                <a href="#urutan">Urutan Eksekusi UAT</a>
                <a href="#tenant">Tenant Dan Data Uji</a>
                <a href="#role">Role Dan User UAT</a>
            </div>

            <div class="toc-group">
                <div class="toc-label">Master & Setup</div>
                <a href="#organisasi">Usaha Dan Cabang</a>
                <a href="#produk">Produk Dan Kategori</a>
                <a href="#branch-setting">Setting Cabang Dan Jadwal Jual</a>
                <a href="#stok">Stok Masuk, Stok Keluar, Resep, Produksi</a>
                <a href="#floor">Floor Map, Meja, QR Order</a>
                <a href="#promo">Promo Voucher, Diskon, Redeem</a>
                <a href="#branding">Branding</a>
                <a href="#permission">Role Permission Dan User</a>
            </div>

            <div class="toc-group">
                <div class="toc-label">Operasional</div>
                <a href="#shift">Shift, Cash In, Cash Out</a>
                <a href="#pos">POS Dan Semua Variasi Transaksi</a>
                <a href="#split">Split Amount Dan Split Item</a>
                <a href="#refund">Refund Dan Retur</a>
            </div>

            <div class="toc-group">
                <div class="toc-label">Laporan</div>
                <a href="#laporan-op">Laporan Operasional</a>
                <a href="#laporan-shift">Laporan Shift Dan Rekap Kasir</a>
                <a href="#laporan-stock">Laporan Stok Dan Produksi</a>
                <a href="#akuntansi">Akuntansi, Jurnal, COA</a>
            </div>

            <div class="toc-group">
                <div class="toc-label">Lainnya</div>
                <a href="#langganan">Langganan Dan Subscription</a>
                <a href="#audit">Audit Aktivitas</a>
                <a href="#isolasi">Isolasi Antar Tenant</a>
                <a href="#checklist">Checklist Lengkap UAT</a>
                <a href="#hasil">Template Hasil Dan Temuan</a>
                <a href="#ttd">Tanda Tangan</a>
            </div>
        </aside>

        <main class="content">
            <section class="card" id="tujuan">
                <div class="section-head">
                    <h2>Tujuan Dan Cara Pakai</h2>
                    <p class="muted">Dokumen kerja utama untuk tester</p>
                </div>
                <p>Dokumen ini disusun untuk menguji aplikasi dari awal tenant dibuat sampai owner menutup hari operasional. Formatnya sengaja dibuat praktis agar tester bisa bergerak menu demi menu tanpa perlu membuka banyak catatan terpisah.</p>
                <div class="grid-2">
                    <div class="mini">
                        <h4>Cara Pakai</h4>
                        <ol>
                            <li>Isi identitas tenant, user, dan cabang uji lebih dulu.</li>
                            <li>Ikuti urutan eksekusi dari setup dasar sampai laporan.</li>
                            <li>Setelah tiap blok selesai, cocokkan hasil UI dengan expected.</li>
                            <li>Tandai status dengan PASS, FAIL, BLOCKED, atau NA.</li>
                        </ol>
                    </div>
                    <div class="mini">
                        <h4>Aturan UAT</h4>
                        <ul>
                            <li>Kalau data dasar belum benar, jangan lanjut ke transaksi.</li>
                            <li>Kalau angka berbeda, catat expected, aktual, dan selisih.</li>
                            <li>Kalau menu ada tapi belum selesai, tandai FAIL atau BLOCKED sesuai kasus.</li>
                            <li>Kalau fitur memang belum tersedia pada build ini, tandai NA dan beri catatan jelas.</li>
                        </ul>
                    </div>
                </div>
                <div class="callout warn" style="margin-top: 14px;">
                    <p>Fokus UAT ini bukan hanya apakah tombol bisa diklik, tetapi apakah alur bisnis, pembatasan role, stok, laporan, dan hasil akuntansinya tetap konsisten setelah dipakai penuh.</p>
                </div>
            </section>

            <section class="card" id="menu-audit">
                <h2>Cakupan Semua Menu Yang Harus Dicek</h2>
                <p>Bagian ini merangkum semua menu tenant yang saat ini ada di aplikasi. Tujuannya supaya tidak ada area yang terlewat saat UAT.</p>
                <div class="menu-grid">
                    <div class="menu-box">
                        <h4>Utama</h4>
                        <ul>
                            <li>Home</li>
                            <li>POS</li>
                            <li>Langganan</li>
                            <li>Akun</li>
                        </ul>
                    </div>
                    <div class="menu-box">
                        <h4>Operasional</h4>
                        <ul>
                            <li>Laporan</li>
                            <li>Rekap Produk</li>
                            <li>Stok Masuk</li>
                            <li>Stok Keluar</li>
                            <li>Laporan Stok Mentah</li>
                            <li>Produksi / Masak</li>
                            <li>Shift History Self</li>
                            <li>Laporan Harian Self</li>
                        </ul>
                    </div>
                    <div class="menu-box">
                        <h4>Admin</h4>
                        <ul>
                            <li>Produk</li>
                            <li>Kategori</li>
                            <li>Floor Map Manager</li>
                            <li>User</li>
                            <li>Usaha &amp; Cabang</li>
                            <li>Produksi / Masak vs Jual</li>
                            <li>Setting Resep</li>
                            <li>Role Permission</li>
                            <li>Rekap Kasir Owner</li>
                            <li>Shift History [Admin]</li>
                            <li>Laporan Harian [Admin]</li>
                            <li>Audit Aktivitas</li>
                            <li>Laporan Akuntansi</li>
                            <li>Entry Keuangan Admin</li>
                            <li>Jurnal Manual</li>
                            <li>Setting Akuntansi</li>
                            <li>Branding</li>
                            <li>Promo Voucher</li>
                            <li>Daftar Voucher</li>
                        </ul>
                    </div>
                </div>
                <div class="pill-row">
                    <span class="pill">Wajib diuji sebagai owner</span>
                    <span class="pill">Uji pembatasan sebagai admin</span>
                    <span class="pill">Uji pembatasan sebagai kasir</span>
                    <span class="pill">Uji menu yang dipengaruhi paket langganan</span>
                </div>
            </section>

            <section class="card" id="urutan">
                <h2>Urutan Eksekusi UAT Yang Disarankan</h2>
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tahap</th>
                            <th>Tujuan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>1</td><td>Pendaftaran dan aktivasi tenant</td><td>Memastikan tenant lahir dengan struktur awal yang benar</td></tr>
                        <tr><td>2</td><td>Audit menu owner</td><td>Memastikan menu lengkap tampil sesuai role dan paket</td></tr>
                        <tr><td>3</td><td>Setup usaha, cabang, dan user</td><td>Memastikan organisasi tenant siap dipakai</td></tr>
                        <tr><td>4</td><td>Setup kategori, produk, dan setting cabang</td><td>Memastikan produk global dan perilaku per cabang benar</td></tr>
                        <tr><td>5</td><td>Setup stok, resep, dan produksi</td><td>Memastikan alur bahan ke barang jual hidup</td></tr>
                        <tr><td>6</td><td>Setup floor map, meja, QR, promo, branding</td><td>Menyiapkan semua alat operasional harian</td></tr>
                        <tr><td>7</td><td>Buka shift dan transaksi POS</td><td>Menguji semua variasi transaksi</td></tr>
                        <tr><td>8</td><td>Laporan operasional dan stok</td><td>Memastikan hasil transaksi tercermin di laporan</td></tr>
                        <tr><td>9</td><td>Entry admin, jurnal, dan laporan akuntansi</td><td>Memastikan finance bisa mencocokkan data</td></tr>
                        <tr><td>10</td><td>Langganan, audit, dan isolasi tenant</td><td>Memastikan lifecycle tenant dan keamanan data</td></tr>
                    </tbody>
                </table>
            </section>

            <section class="card" id="tenant">
                <h2>Tenant Dan Data Uji</h2>
                <div class="grid-2">
                    <div class="mini">
                        <h4>Tenant 1</h4>
                        <p><strong>Nama:</strong> kopi-nusantara<br><strong>Tipe:</strong> F&amp;B dine-in multi cabang<br><strong>Owner:</strong> ____________________<br><strong>Email:</strong> ____________________<br><strong>No. HP:</strong> ____________________</p>
                        <p style="margin-top: 10px;"><strong>Cabang contoh</strong><br>Sudirman, Bintaro</p>
                    </div>
                    <div class="mini">
                        <h4>Tenant 2</h4>
                        <p><strong>Nama:</strong> mart-serbaada<br><strong>Tipe:</strong> retail sederhana untuk uji isolasi<br><strong>Owner:</strong> ____________________<br><strong>Email:</strong> ____________________<br><strong>No. HP:</strong> ____________________</p>
                        <p style="margin-top: 10px;"><strong>Cabang contoh</strong><br>Pusat</p>
                    </div>
                </div>
                <h3>Data Master Uji Tenant 1</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Kategori</th>
                            <th>Produk</th>
                            <th>Harga Master</th>
                            <th>Catatan UAT</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>Makanan</td><td>Nasi ayam original</td><td>28.000</td><td>Produk reguler</td></tr>
                        <tr><td>Makanan</td><td>Nasi ayam spicy</td><td>30.000</td><td>Uji override cabang</td></tr>
                        <tr><td>Snack</td><td>Kulit crispy</td><td>14.000</td><td>Uji item tambahan meja</td></tr>
                        <tr><td>Minuman</td><td>Es teh manis</td><td>8.000</td><td>Uji QR order</td></tr>
                        <tr><td>Minuman</td><td>Kopi susu gula aren</td><td>22.000</td><td>Uji voucher minimum belanja</td></tr>
                        <tr><td>Minuman</td><td>Air mineral</td><td>6.000</td><td>Uji produk nonaktif cabang</td></tr>
                    </tbody>
                </table>
            </section>

            <section class="card" id="role">
                <h2>Role Dan User UAT</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Role</th>
                            <th>Jumlah Minimal</th>
                            <th>Fokus UAT</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>Owner</td><td>1 per tenant</td><td>Audit menu lengkap, laporan, akuntansi, langganan</td></tr>
                        <tr><td>Admin</td><td>1 per tenant</td><td>Master data, promo, user, cabang, branding</td></tr>
                        <tr><td>Kasir</td><td>1 per cabang</td><td>Shift, POS, laporan diri, transaksi harian</td></tr>
                    </tbody>
                </table>
                <div class="callout info" style="margin-top: 14px;">
                    <p>Wajib tes minimal 3 kondisi role: owner melihat semua menu bisnis, admin tidak melihat menu owner sensitif, dan kasir tidak melihat menu admin serta akuntansi.</p>
                </div>
            </section>

            <section class="card" id="organisasi">
                <h2>Usaha Dan Cabang</h2>
                <p>Area ini memastikan tenant bisa punya banyak usaha atau minimal banyak cabang, dan user ditautkan ke struktur yang benar.</p>
                <div class="grid-2">
                    <div class="mini">
                        <h4>Langkah Uji</h4>
                        <ol>
                            <li>Login sebagai owner.</li>
                            <li>Buka menu <strong>Usaha &amp; Cabang</strong>.</li>
                            <li>Tambah cabang baru Bintaro.</li>
                            <li>Isi alamat, kode cabang, dan status aktif.</li>
                            <li>Pastikan cabang muncul di selector POS dan laporan.</li>
                        </ol>
                    </div>
                    <div class="mini">
                        <h4>Expected</h4>
                        <ul>
                            <li>Cabang baru tersimpan tanpa mengubah cabang lama.</li>
                            <li>Kasir cabang hanya bekerja di cabang yang dipilih.</li>
                            <li>Laporan dapat difilter per cabang dan keseluruhan.</li>
                            <li>Tenant 2 tidak melihat cabang tenant 1.</li>
                        </ul>
                    </div>
                </div>
            </section>

            <section class="card" id="produk">
                <h2>Produk Dan Kategori</h2>
                <p>Tujuan blok ini adalah memastikan konsep produk global tetap rapi dan tidak perlu duplikasi row produk hanya karena cabangnya berbeda.</p>
                <h3>Kategori</h3>
                <ul>
                    <li>Tambah kategori makanan, minuman, snack, dan bahan baku bila kategori bahan dipakai di UI.</li>
                    <li>Pastikan kategori tampil di daftar, filter, dan POS bila kategori dipakai sebagai filter penjualan.</li>
                </ul>
                <h3>Produk</h3>
                <div class="grid-2">
                    <div class="mini">
                        <h4>Langkah Uji</h4>
                        <ol>
                            <li>Buka menu <strong>Produk</strong>.</li>
                            <li>Tambah semua produk master.</li>
                            <li>Pastikan produk tersimpan dengan kategori, harga, satuan, dan status aktif.</li>
                            <li>Edit 1 produk lalu cek perubahan terbaca di daftar.</li>
                            <li>Uji nonaktifkan 1 produk secara global bila UI mendukung.</li>
                        </ol>
                    </div>
                    <div class="mini">
                        <h4>Expected</h4>
                        <ul>
                            <li>Produk tidak perlu dibuat ulang per cabang.</li>
                            <li>Harga master tetap menjadi fallback default.</li>
                            <li>POS membaca produk sesuai status aktif dan aturan cabang.</li>
                            <li>Laporan tetap memakai satu nama produk yang sama.</li>
                        </ul>
                    </div>
                </div>
            </section>

            <section class="card" id="branch-setting">
                <h2>Setting Cabang, Harga Override, Availability, Dan Jadwal Jual</h2>
                <p>Bagian ini penting karena Anda minta dokumen lengkap sampai pengaturan diskon dan jadwal jual. Di aplikasi sekarang, pengujian paling aman adalah memastikan perilaku produk bisa berbeda per cabang, termasuk aktif atau tidak, harga override, tersedia sementara, dan jadwal jika field-nya ada di UI.</p>
                <table>
                    <thead>
                        <tr>
                            <th>Area</th>
                            <th>Langkah Uji</th>
                            <th>Expected</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Harga override cabang</td>
                            <td>Set harga `kopi susu gula aren` di Sudirman 22.000 dan di Bintaro 24.000</td>
                            <td>POS tiap cabang membaca harga yang berbeda tanpa duplikasi produk</td>
                        </tr>
                        <tr>
                            <td>Produk nonaktif per cabang</td>
                            <td>Nonaktifkan `air mineral` di Bintaro</td>
                            <td>Produk tidak tampil atau tidak bisa dijual di POS Bintaro, tetapi tetap ada di Sudirman</td>
                        </tr>
                        <tr>
                            <td>Availability sementara</td>
                            <td>Set `kulit crispy` unavailable sementara bila field tersedia</td>
                            <td>Produk aktif master tapi tidak bisa dijual sementara di cabang itu</td>
                        </tr>
                        <tr>
                            <td>Jadwal jual</td>
                            <td>Bila UI punya field jadwal, atur produk atau promo hanya aktif jam tertentu</td>
                            <td>Produk atau promo hanya muncul pada jam yang valid</td>
                        </tr>
                        <tr>
                            <td>Snapshot harga transaksi</td>
                            <td>Lakukan transaksi sebelum dan sesudah harga override berubah</td>
                            <td>Laporan lama tidak ikut berubah; transaksi tetap simpan harga saat checkout</td>
                        </tr>
                    </tbody>
                </table>
                <div class="callout warn" style="margin-top: 14px;">
                    <p>Jika menu atau field jadwal jual belum tampil di build ini, jangan dihapus dari UAT. Tandai sebagai gap implementasi karena kebutuhan bisnisnya sudah jelas dan perlu dicocokkan saat fitur diaktifkan.</p>
                </div>
            </section>

            <section class="card" id="stok">
                <h2>Stok Masuk, Stok Keluar, Laporan Stok Mentah, Setting Resep, Dan Produksi</h2>
                <div class="grid-3">
                    <div class="mini">
                        <h4>Stok Masuk</h4>
                        <p>Input stok awal bahan baku per cabang, lalu pastikan saldo stok terbentuk di laporan stok.</p>
                    </div>
                    <div class="mini">
                        <h4>Stok Keluar</h4>
                        <p>Catat pemakaian non-penjualan, misalnya rusak atau internal use, lalu pastikan stok berkurang.</p>
                    </div>
                    <div class="mini">
                        <h4>Laporan Stok Mentah</h4>
                        <p>Pastikan stok per cabang dan mutasinya bisa dibaca owner dan admin.</p>
                    </div>
                </div>
                <h3>Contoh Data Bahan Dan Produksi</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Bahan</th>
                            <th>Stok Awal</th>
                            <th>Satuan</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>Ayam karkas</td><td>10</td><td>ekor</td><td>Bahan produksi ayam goreng</td></tr>
                        <tr><td>Tepung</td><td>5</td><td>pack</td><td>Bahan pelapis</td></tr>
                        <tr><td>Saus sambal</td><td>8</td><td>botol</td><td>Bahan pendamping</td></tr>
                        <tr><td>Es teh concentrate</td><td>3</td><td>jerigen</td><td>Uji minuman</td></tr>
                    </tbody>
                </table>
                <h3>Uji Resep Dan Produksi</h3>
                <ol>
                    <li>Buka <strong>Setting Resep</strong> dan tautkan bahan ke produk yang relevan.</li>
                    <li>Buka <strong>Produksi / Masak</strong> lalu jalankan 1 transaksi produksi.</li>
                    <li>Cek menu <strong>Produksi / Masak vs Jual</strong> untuk memastikan hasil produksi bisa dibandingkan dengan penjualan.</li>
                    <li>Lakukan 1 penjualan lalu cocokkan stok bergerak dan laporan bahan baku.</li>
                </ol>
                <div class="callout ok" style="margin-top: 14px;">
                    <p>Expected akhir: stok bisa dibedakan per cabang, resep terbaca, produksi tercatat, dan laporan stok berubah setelah transaksi produksi maupun penjualan.</p>
                </div>
            </section>

            <section class="card" id="floor">
                <h2>Floor Map, Meja, Dan QR Order</h2>
                <p>Bagian ini khusus F&amp;B dine-in agar alur meja dan QR table order benar-benar diuji, bukan hanya transaksi kasir biasa.</p>
                <div class="grid-2">
                    <div class="mini">
                        <h4>Langkah Uji</h4>
                        <ol>
                            <li>Buka <strong>Floor Map Manager</strong>.</li>
                            <li>Buat area lantai dan meja A1, A2, B1, B2.</li>
                            <li>Generate QR tiap meja bila fitur tersedia.</li>
                            <li>Scan QR meja A1 dari perangkat pelanggan.</li>
                            <li>Buat order dari QR lalu cek order masuk ke cabang dan meja yang benar.</li>
                        </ol>
                    </div>
                    <div class="mini">
                        <h4>Expected</h4>
                        <ul>
                            <li>QR tiap meja unik.</li>
                            <li>Order masuk ke meja yang benar.</li>
                            <li>Kasir bisa mengambil alih order QR ke alur pembayaran.</li>
                            <li>Meja yang sudah punya order tetap bisa ditambah item dari POS.</li>
                        </ul>
                    </div>
                </div>
            </section>

            <section class="card" id="promo">
                <h2>Promo Voucher, Daftar Voucher, Diskon, Dan Redeem</h2>
                <p>Area ini menjawab kebutuhan Anda untuk menguji sampai bikin diskon. UAT harus menutup seluruh siklus promo: membuat voucher, mengatur syarat minimum belanja, membatasi cabang bila ada, menerapkan di POS, dan mencocokkan dampaknya ke struk serta laporan.</p>
                <h3>Dua Mode Promo Yang Harus Dibedakan</h3>
                <div class="grid-2">
                    <div class="mini">
                        <h4>Mode 1: Promo Manual Dari Menu Voucher</h4>
                        <p>Kode promo dibuat lebih dulu oleh admin atau owner di menu <strong>Promo Voucher</strong>, lalu dipakai saat checkout. Struk hanya menampilkan promo yang dipakai dan nilai potongannya.</p>
                    </div>
                    <div class="mini">
                        <h4>Mode 2: Promo Turunan Dari Struk</h4>
                        <p>Struk transaksi menghasilkan kode promo baru untuk kunjungan berikutnya. Kode ini bukan sumber promo awal, tetapi hadiah lanjutan setelah transaksi berhasil.</p>
                    </div>
                </div>
                <h3>Skenario Voucher Yang Wajib Dibuat</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Mode</th>
                            <th>Jenis</th>
                            <th>Syarat</th>
                            <th>Expected</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>NGOPI10</td><td>Manual</td><td>Diskon nominal / persen</td><td>Minimum pembelian 40.000</td><td>Diskon hanya aktif saat syarat terpenuhi</td></tr>
                        <tr><td>LUNCH25</td><td>Manual</td><td>Redeem / promo</td><td>Minimum pembelian 30.000</td><td>Potongan redeem tampil jelas di struk dan laporan</td></tr>
                        <tr><td>DINNERCAB</td><td>Manual</td><td>Voucher cabang tertentu</td><td>Cabang Sudirman, jam makan malam bila UI mendukung</td><td>Voucher tidak berlaku di cabang atau jam yang salah</td></tr>
                        <tr><td>COMEBACK10</td><td>Dari struk</td><td>Kupon kunjungan berikutnya</td><td>Muncul setelah transaksi sukses, punya masa berlaku dan rule minimum pembelian bila fitur tersedia</td><td>Struk mencetak kode promo baru yang bisa dipakai pada transaksi berikutnya</td></tr>
                    </tbody>
                </table>
                <h3>Campaign Promo Terjadwal</h3>
                <p>Untuk campaign terjadwal, mekanik promo sebaiknya dibagi menjadi dua versi utama agar mudah dijelaskan ke tenant dan mudah diuji di kasir.</p>
                <table>
                    <thead>
                        <tr>
                            <th>Versi</th>
                            <th>Contoh Rule</th>
                            <th>Expected</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Gratis produk</td>
                            <td>Minimum belanja 75.000 dapat gratis 1 es teh manis, aktif Senin-Jumat jam 11:00-14:00</td>
                            <td>Sistem menambah benefit item gratis tanpa mengubah item utama customer, dan struk menampilkan hadiah dengan jelas</td>
                        </tr>
                        <tr>
                            <td>Potong harga</td>
                            <td>Minimum belanja 50.000 potongan 10.000 atau 10%, aktif pada periode campaign tertentu</td>
                            <td>Sistem memotong total sesuai rule, tetap menghormati minimum belanja, cabang, dan jadwal aktif</td>
                        </tr>
                    </tbody>
                </table>
                <h3>Syarat Promo Terjadwal Yang Disarankan</h3>
                <ul>
                    <li>Minimum pembelian nominal, misalnya 50.000 atau 75.000.</li>
                    <li>Periode aktif tanggal mulai dan tanggal selesai.</li>
                    <li>Jam aktif campaign bila dipakai untuk lunch atau dinner promo.</li>
                    <li>Batas cabang atau outlet tertentu bila campaign tidak global.</li>
                    <li>Batas penggunaan per struk, per customer, atau per hari bila fiturnya ada.</li>
                </ul>
                <h3>Output Voucher Cetak Kecil</h3>
                <p>Untuk campaign yang dibagikan manual, sebaiknya ada output voucher ukuran kecil agar mudah diselipkan ke struk atau dibagikan kasir ke pelanggan.</p>
                <ul>
                    <li>Format kecil mirip kupon: kode promo, nama promo, masa berlaku, minimum belanja, dan cabang bila relevan.</li>
                    <li>Bisa dicetak terpisah dari struk utama atau sebagai potongan kecil di bawah struk.</li>
                    <li>Tampilan harus hemat kertas dan tetap terbaca jelas untuk dibagi-bagikan.</li>
                </ul>
                <h3>Checklist Promo Manual</h3>
                <ul>
                    <li>Buat voucher di menu <strong>Promo Voucher</strong>.</li>
                    <li>Pastikan voucher muncul di <strong>Daftar Voucher</strong>.</li>
                    <li>Uji voucher yang gagal karena minimum pembelian tidak terpenuhi.</li>
                    <li>Uji voucher yang berhasil saat syarat terpenuhi.</li>
                    <li>Uji voucher yang dibatasi cabang bila field tersedia.</li>
                    <li>Uji jadwal voucher bila field jadwal tersedia.</li>
                    <li>Uji campaign versi gratis produk dan pastikan hadiah item tercatat jelas.</li>
                    <li>Uji campaign versi potong harga dan pastikan diskon tidak salah hitung.</li>
                    <li>Uji minimum belanja untuk kedua versi promo terjadwal.</li>
                    <li>Pastikan output voucher kecil atau kupon cetak tampil rapi bila fitur print voucher aktif.</li>
                    <li>Pastikan struk menampilkan subtotal, diskon, redeem, dan grand total dengan jelas.</li>
                </ul>
                <h3>Checklist Promo Turunan Dari Struk</h3>
                <ul>
                    <li>Selesaikan 1 transaksi yang memenuhi rule promo turunan.</li>
                    <li>Pastikan struk menampilkan kode promo baru bila fitur ini aktif.</li>
                    <li>Catat kode promo yang keluar, masa berlaku, dan syarat minimumnya.</li>
                    <li>Pakai kode tersebut di transaksi berikutnya.</li>
                    <li>Pastikan kode hanya bisa dipakai sesuai rule, tidak dobel pakai di luar batas, dan tercatat di laporan promo bila ada.</li>
                    <li>Pastikan bila konfigurasi default voucher dari struk aktif dan campaign terjadwal juga aktif, sistem memakai rule campaign yang sedang aktif dan memenuhi syarat lebih dulu.</li>
                    <li>Pastikan konfigurasi default voucher dari struk tetap menjadi fallback bila tidak ada campaign aktif yang cocok.</li>
                </ul>
                <h3>Checklist Voucher Manual Siap Print</h3>
                <ul>
                    <li>Pastikan admin bisa membuat voucher manual tanpa transaksi atau struk.</li>
                    <li>Pastikan metode voucher manual menyesuaikan tampilan field: mode produk gratis hanya menampilkan produk gratis, sedangkan mode diskon produk hanya menampilkan target produk dan nilai diskon.</li>
                    <li>Pastikan setelah submit voucher manual, sistem membuka halaman print voucher kecil secara otomatis.</li>
                    <li>Pastikan halaman print voucher manual menampilkan kode, benefit, minimum belanja redeem, masa berlaku, dan sumber sebagai Manual Voucher.</li>
                    <li>Pastikan tombol <code>Tutup</code> di halaman print voucher manual berfungsi: menutup tab bila memungkinkan, kembali ke halaman sebelumnya, atau fallback ke daftar voucher.</li>
                </ul>
                <h3>Checklist Campaign Override</h3>
                <ul>
                    <li>Pastikan urutan logika promo dipahami tester: 1. voucher otomatis dari struk, 2. voucher manual, 3. campaign voucher terjadwal sebagai override.</li>
                    <li>Pastikan campaign terjadwal tidak memengaruhi pembuatan voucher manual.</li>
                    <li>Pastikan campaign terjadwal hanya mengoverride voucher default dari struk, bukan mengganti voucher manual yang sudah dicetak.</li>
                </ul>
                <div class="callout warn" style="margin-top: 14px;">
                    <p>Kalau build saat ini belum punya fitur promo turunan dari struk, tandai sebagai NA atau gap implementasi. Jangan disamakan dengan voucher manual biasa.</p>
                </div>
                <div class="callout info" style="margin-top: 14px;">
                    <p>Asumsi akuntansi UAT ini tetap: diskon minimum pembelian masuk ke akun diskon penjualan, sedangkan redeem diperlakukan sebagai beban promosi.</p>
                </div>
            </section>

            <section class="card" id="branding">
                <h2>Branding</h2>
                <p>Branding sering terlihat kecil, tetapi penting untuk pengalaman owner dan tampilan struk atau UI tenant.</p>
                <table>
                    <thead>
                        <tr>
                            <th>Langkah Uji</th>
                            <th>Expected</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>Upload logo tenant</td><td>Logo tampil di area yang memang memakai branding</td></tr>
                        <tr><td>Ubah nama tampilan bisnis</td><td>Nama baru terbaca di header, struk, atau halaman yang relevan</td></tr>
                        <tr><td>Uji fallback saat logo kosong</td><td>UI tetap rapi dan tidak rusak</td></tr>
                    </tbody>
                </table>
            </section>

            <section class="card" id="permission">
                <h2>Role Permission Dan User</h2>
                <p>Bagian ini memastikan pembatasan menu bukan cuma di sidebar, tetapi benar-benar aman secara akses.</p>
                <table>
                    <thead>
                        <tr>
                            <th>Role</th>
                            <th>Yang Harus Bisa</th>
                            <th>Yang Tidak Boleh Bisa</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>Kasir</td><td>POS, Home, Akun, Shift History Self, Laporan Harian Self</td><td>Akuntansi, promo, user, branding, langganan, audit</td></tr>
                        <tr><td>Admin</td><td>Master data, floor map, cabang, promo, branding, laporan operasional</td><td>Menu owner sensitif seperti langganan, audit, akuntansi owner jika desain final membatasinya</td></tr>
                        <tr><td>Owner</td><td>Semua menu bisnis tenant</td><td>Tidak relevan, kecuali aksi khusus administrator platform</td></tr>
                    </tbody>
                </table>
            </section>

            <section class="card" id="shift">
                <h2>Shift, Cash In, Cash Out, Dan Penutupan Shift</h2>
                <div class="grid-3">
                    <div class="mini">
                        <h4>Buka Shift</h4>
                        <p>Kasir membuka shift dengan saldo awal 200.000 dan status shift harus terlihat.</p>
                    </div>
                    <div class="mini">
                        <h4>Cash In</h4>
                        <p>Input DP atau kas masuk admin 100.000. Ini tidak boleh menambah omzet penjualan.</p>
                    </div>
                    <div class="mini">
                        <h4>Cash Out</h4>
                        <p>Input beli gas 50.000. Pastikan mengurangi kas dan terbaca di laporan admin.</p>
                    </div>
                </div>
                <h3>Expected Shift</h3>
                <ul>
                    <li>Kasir tidak bisa bertransaksi jika sistem mensyaratkan shift aktif.</li>
                    <li>Self history dan admin shift history sama-sama bertambah.</li>
                    <li>Saat tutup shift, ringkasan transaksi, cash in, cash out, dan selisih kas bisa dibaca.</li>
                    <li>Bila ada guard shift terbuka, aksi sensitif harus menolak selama shift belum ditutup.</li>
                </ul>
            </section>

            <section class="card" id="pos">
                <h2>POS Dan Semua Variasi Transaksi</h2>
                <p>Blok ini adalah jantung UAT. Semua variasi transaksi utama harus diuji, termasuk dine in, take away, cash, non tunai, promo, dan transaksi meja.</p>
                <table>
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Skenario</th>
                            <th>Expected Ringkas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>T1</td><td>Cash, dine in, total 28.000</td><td>Paid, struk tampil label dine in, kas bertambah</td></tr>
                        <tr><td>T2</td><td>QRIS, take away, total 22.000</td><td>Paid, metode bayar benar, label take away</td></tr>
                        <tr><td>T3</td><td>Order meja dari QR lalu dibayar kasir</td><td>Order QR dan pembayaran kasir nyambung</td></tr>
                        <tr><td>T4</td><td>Order meja ditambah item manual dari POS</td><td>Semua item tetap berada di meja yang sama</td></tr>
                        <tr><td>T5</td><td>Voucher minimum pembelian berhasil</td><td>Diskon tampil di struk dan total turun sesuai aturan</td></tr>
                        <tr><td>T6</td><td>Voucher gagal karena belum mencapai minimum pembelian</td><td>Sistem menolak voucher dengan pesan yang jelas</td></tr>
                        <tr><td>T7</td><td>Produk nonaktif cabang dicoba dijual</td><td>Produk tidak tampil atau tidak bisa di-checkout</td></tr>
                    </tbody>
                </table>
            </section>

            <section class="card" id="split">
                <h2>Split Amount Dan Split Item</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Jenis</th>
                            <th>Contoh</th>
                            <th>Expected</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>Split amount</td><td>Total 35.000 dibayar cash 20.000 + QRIS 15.000</td><td>Pembayaran terbagi, omzet tidak dobel, metode bayar terbaca di laporan</td></tr>
                        <tr><td>Split item</td><td>Total 34.000 dibagi Bill A 16.000 cash dan Bill B 18.000 QRIS</td><td>Item tiap bill tepat, pembayaran tiap bill tepat, total meja tetap konsisten</td></tr>
                    </tbody>
                </table>
                <div class="callout warn" style="margin-top: 14px;">
                    <p>Validasi utama split adalah jangan sampai omzet, stok, atau pembayaran tercatat ganda. Ini salah satu area paling rawan di UAT.</p>
                </div>
            </section>

            <section class="card" id="refund">
                <h2>Refund Dan Retur</h2>
                <p>Refund wajib diuji karena menyentuh transaksi, kas, stok, dan akuntansi sekaligus.</p>
                <ol>
                    <li>Buat transaksi take away 10.000.</li>
                    <li>Lakukan refund sesuai alur UI.</li>
                    <li>Cek status transaksi menjadi refunded atau setara.</li>
                    <li>Cek kas atau QRIS berkurang sesuai metode bayar awal.</li>
                    <li>Cek stok kembali bila desain stok aplikasi memang mengembalikan item refund.</li>
                    <li>Cek laporan penjualan dan laporan akuntansi membaca retur dengan benar.</li>
                </ol>
            </section>

            <section class="card" id="laporan-op">
                <h2>Laporan Operasional</h2>
                <p>Bagian ini mencakup menu <strong>Laporan</strong>, <strong>Rekap Produk</strong>, <strong>Laporan Harian Self</strong>, dan <strong>Laporan Harian [Admin]</strong>.</p>
                <table>
                    <thead>
                        <tr>
                            <th>Menu</th>
                            <th>Yang Dicek</th>
                            <th>Expected</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>Laporan</td><td>Total sales, transaksi, metode bayar, filter tanggal dan cabang</td><td>Angka sesuai transaksi uji</td></tr>
                        <tr><td>Rekap Produk</td><td>Produk terjual, qty, omzet per produk</td><td>Produk terlaris dan qty sesuai transaksi</td></tr>
                        <tr><td>Laporan Harian Self</td><td>Transaksi milik user sendiri</td><td>Kasir hanya melihat data dirinya sesuai rule</td></tr>
                        <tr><td>Laporan Harian [Admin]</td><td>Rekap lintas kasir atau cabang</td><td>Owner atau admin melihat ringkasan lebih luas</td></tr>
                    </tbody>
                </table>
            </section>

            <section class="card" id="laporan-shift">
                <h2>Laporan Shift Dan Rekap Kasir Owner</h2>
                <p>Area ini menutup pengawasan kasir harian.</p>
                <ul>
                    <li><strong>Shift History Self</strong>: kasir melihat riwayat shift miliknya sendiri.</li>
                    <li><strong>Shift History [Admin]</strong>: admin atau owner melihat semua shift yang relevan.</li>
                    <li><strong>Rekap Kasir Owner</strong>: owner bisa membandingkan performa dan setoran tiap kasir.</li>
                </ul>
                <table>
                    <thead>
                        <tr>
                            <th>Komponen</th>
                            <th>Expected</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>Opening cash</td><td>200.000</td></tr>
                        <tr><td>Cash in non-sales</td><td>100.000</td></tr>
                        <tr><td>Cash out</td><td>50.000</td></tr>
                        <tr><td>Cash paid sales</td><td>131.000</td></tr>
                        <tr><td>Closing drawer expected</td><td>371.000</td></tr>
                    </tbody>
                </table>
            </section>

            <section class="card" id="laporan-stock">
                <h2>Laporan Stok Dan Produksi</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Menu</th>
                            <th>Yang Dicek</th>
                            <th>Expected</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>Laporan Stok Mentah</td><td>Saldo bahan sebelum dan sesudah transaksi</td><td>Turun sesuai produksi dan penjualan</td></tr>
                        <tr><td>Produksi / Masak vs Jual</td><td>Perbandingan hasil produksi dengan qty terjual</td><td>Bisa dipakai membaca selisih</td></tr>
                        <tr><td>Rekap Produk</td><td>Hubungan qty jual dengan stok dan resep</td><td>Konsisten dengan transaksi POS</td></tr>
                    </tbody>
                </table>
            </section>

            <section class="card" id="akuntansi">
                <h2>Akuntansi, Entry Keuangan Admin, Jurnal Manual, Laporan Akuntansi, Dan COA</h2>
                <p>Bagian ini dipakai untuk mencocokkan hasil UI dengan ekspektasi finance manual yang sebelumnya sudah kita tetapkan.</p>
                <h3>Expected Akuntansi Per Cabang</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Komponen</th>
                            <th>Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>Gross sales</td><td>230.000</td></tr>
                        <tr><td>Diskon penjualan</td><td>4.000</td></tr>
                        <tr><td>Net sales setelah diskon</td><td>226.000</td></tr>
                        <tr><td>Retur penjualan</td><td>10.000</td></tr>
                        <tr><td>Net sales setelah refund</td><td>216.000</td></tr>
                        <tr><td>Beban promosi redeem</td><td>5.000</td></tr>
                        <tr><td>Net collectible pelanggan</td><td>211.000</td></tr>
                        <tr><td>HPP</td><td>105.000</td></tr>
                        <tr><td>Laba kotor</td><td>111.000</td></tr>
                        <tr><td>Laba setelah promo</td><td>106.000</td></tr>
                    </tbody>
                </table>
                <h3>Uji Menu Akuntansi</h3>
                <div class="grid-2">
                    <div class="mini">
                        <h4>Setting Akuntansi</h4>
                        <ul>
                            <li>Pastikan tenant punya COA default.</li>
                            <li>Pastikan mapping akun diskon, redeem, kas, QRIS, HPP, retur, dan uang muka bisa diset.</li>
                            <li>Jangan hardcode nomor akun di transaksi.</li>
                        </ul>
                    </div>
                    <div class="mini">
                        <h4>Entry Keuangan Dan Jurnal Manual</h4>
                        <ul>
                            <li>Input cash out beli gas 50.000.</li>
                            <li>Input cash in DP 100.000.</li>
                            <li>Pastikan owner bisa membuat jurnal manual koreksi bila perlu.</li>
                        </ul>
                    </div>
                </div>
                <h3>COA Inti UAT</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama Akun</th>
                            <th>Fungsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>1120</td><td>Kas Kecil / Laci Kas</td><td>Kas fisik cabang</td></tr>
                        <tr><td>1140</td><td>QRIS Clearing / Piutang Gateway</td><td>Penerimaan non-tunai</td></tr>
                        <tr><td>2120</td><td>Uang Muka Pelanggan</td><td>DP sebelum penjualan final</td></tr>
                        <tr><td>4110</td><td>Penjualan Makanan</td><td>Revenue makanan</td></tr>
                        <tr><td>4120</td><td>Penjualan Minuman</td><td>Revenue minuman</td></tr>
                        <tr><td>4190</td><td>Diskon Penjualan</td><td>Contra revenue</td></tr>
                        <tr><td>4191</td><td>Retur Penjualan</td><td>Contra revenue</td></tr>
                        <tr><td>5110</td><td>HPP Makanan</td><td>Beban pokok makanan</td></tr>
                        <tr><td>5120</td><td>HPP Minuman</td><td>Beban pokok minuman</td></tr>
                        <tr><td>6110</td><td>Beban Gas</td><td>Cash out operasional</td></tr>
                        <tr><td>6140</td><td>Beban Promosi</td><td>Redeem dan promo yang diperlakukan sebagai expense</td></tr>
                    </tbody>
                </table>
                <div class="callout warn" style="margin-top: 14px;">
                    <p>Diskon jangan diasumsikan otomatis masuk ke COA tertentu kalau mapping belum diset. Desain yang benar adalah rule jurnal di code dan pemilihan akun via UI mapping tenant.</p>
                </div>
            </section>

            <section class="card" id="langganan">
                <h2>Langganan Dan Subscription Center</h2>
                <p>Blok ini memastikan owner bisa mengelola lifecycle tenant setelah operasional jalan.</p>
                <ul>
                    <li>Buka menu <strong>Langganan</strong>.</li>
                    <li>Cek paket aktif, fitur yang didapat, dan masa berlaku.</li>
                    <li>Uji renewal atau upgrade plan bila flow tersedia.</li>
                    <li>Pastikan perubahan paket memengaruhi visibilitas menu sesuai fitur.</li>
                </ul>
            </section>

            <section class="card" id="audit">
                <h2>Audit Aktivitas</h2>
                <p>Owner harus bisa melacak perubahan penting yang dilakukan user tenant.</p>
                <table>
                    <thead>
                        <tr>
                            <th>Aktivitas Yang Dicoba</th>
                            <th>Expected Audit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>Tambah produk</td><td>Nama user, waktu, dan objek perubahan tercatat</td></tr>
                        <tr><td>Edit harga atau setting cabang</td><td>Perubahan sensitif tercatat</td></tr>
                        <tr><td>Buat voucher promo</td><td>Aksi pembuatan promo tercatat</td></tr>
                        <tr><td>Input entry keuangan admin</td><td>Aksi keuangan tercatat dan bisa ditelusuri</td></tr>
                    </tbody>
                </table>
            </section>

            <section class="card" id="isolasi">
                <h2>Isolasi Antar Tenant</h2>
                <p>Uji tenant kedua dipakai untuk memastikan tidak ada data bocor lintas tenant.</p>
                <ol>
                    <li>Login ke tenant 2.</li>
                    <li>Pastikan tenant 2 tidak melihat produk, cabang, meja, voucher, user, laporan, dan jurnal tenant 1.</li>
                    <li>Buat 1 produk retail di tenant 2.</li>
                    <li>Kembali ke tenant 1 dan pastikan produk tenant 2 tidak muncul.</li>
                </ol>
            </section>

            <section class="card" id="checklist">
                <h2>Checklist Lengkap UAT Per Menu</h2>
                <table class="status-table">
                    <thead>
                        <tr>
                            <th>Area Uji</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>TC-01 Pendaftaran tenant</td><td>_____</td></tr>
                        <tr><td>TC-02 Aktivasi tenant dan provisioning</td><td>_____</td></tr>
                        <tr><td>TC-03 Audit semua menu owner</td><td>_____</td></tr>
                        <tr><td>TC-04 Setup usaha dan cabang</td><td>_____</td></tr>
                        <tr><td>TC-05 Setup role permission dan user</td><td>_____</td></tr>
                        <tr><td>TC-06 Kategori</td><td>_____</td></tr>
                        <tr><td>TC-07 Produk master</td><td>_____</td></tr>
                        <tr><td>TC-08 Setting cabang, override harga, availability</td><td>_____</td></tr>
                        <tr><td>TC-09 Jadwal jual produk atau promo bila tersedia</td><td>_____</td></tr>
                        <tr><td>TC-10 Stok masuk</td><td>_____</td></tr>
                        <tr><td>TC-11 Stok keluar</td><td>_____</td></tr>
                        <tr><td>TC-12 Laporan stok mentah</td><td>_____</td></tr>
                        <tr><td>TC-13 Setting resep</td><td>_____</td></tr>
                        <tr><td>TC-14 Produksi / Masak</td><td>_____</td></tr>
                        <tr><td>TC-15 Produksi / Masak vs Jual</td><td>_____</td></tr>
                        <tr><td>TC-16 Floor map manager</td><td>_____</td></tr>
                        <tr><td>TC-17 QR table order</td><td>_____</td></tr>
                        <tr><td>TC-18 Promo voucher manual</td><td>_____</td></tr>
                        <tr><td>TC-19 Daftar voucher</td><td>_____</td></tr>
                        <tr><td>TC-20 Promo dari struk untuk kunjungan berikutnya</td><td>_____</td></tr>
                        <tr><td>TC-21 Campaign promo terjadwal versi gratis produk</td><td>_____</td></tr>
                        <tr><td>TC-22 Campaign promo terjadwal versi potong harga</td><td>_____</td></tr>
                        <tr><td>TC-23 Campaign override terhadap voucher default struk</td><td>_____</td></tr>
                        <tr><td>TC-24 Cetak voucher kecil / kupon promo</td><td>_____</td></tr>
                        <tr><td>TC-25 Tombol tutup pada halaman print voucher kecil</td><td>_____</td></tr>
                        <tr><td>TC-26 Branding</td><td>_____</td></tr>
                        <tr><td>TC-27 Buka shift</td><td>_____</td></tr>
                        <tr><td>TC-28 Cash in</td><td>_____</td></tr>
                        <tr><td>TC-29 Cash out</td><td>_____</td></tr>
                        <tr><td>TC-30 POS dine in</td><td>_____</td></tr>
                        <tr><td>TC-31 POS take away</td><td>_____</td></tr>
                        <tr><td>TC-32 Split amount</td><td>_____</td></tr>
                        <tr><td>TC-33 Split item</td><td>_____</td></tr>
                        <tr><td>TC-34 Diskon minimum pembelian</td><td>_____</td></tr>
                        <tr><td>TC-35 Redeem minimum pembelian</td><td>_____</td></tr>
                        <tr><td>TC-36 Refund / retur</td><td>_____</td></tr>
                        <tr><td>TC-37 Laporan</td><td>_____</td></tr>
                        <tr><td>TC-38 Rekap produk</td><td>_____</td></tr>
                        <tr><td>TC-39 Shift history self</td><td>_____</td></tr>
                        <tr><td>TC-40 Laporan harian self</td><td>_____</td></tr>
                        <tr><td>TC-41 Rekap kasir owner</td><td>_____</td></tr>
                        <tr><td>TC-42 Shift history admin</td><td>_____</td></tr>
                        <tr><td>TC-43 Laporan harian admin</td><td>_____</td></tr>
                        <tr><td>TC-44 Audit aktivitas</td><td>_____</td></tr>
                        <tr><td>TC-43 Setting akuntansi dan COA</td><td>_____</td></tr>
                        <tr><td>TC-44 Entry keuangan admin</td><td>_____</td></tr>
                        <tr><td>TC-45 Jurnal manual</td><td>_____</td></tr>
                        <tr><td>TC-46 Laporan akuntansi</td><td>_____</td></tr>
                        <tr><td>TC-47 Langganan / subscription center</td><td>_____</td></tr>
                        <tr><td>TC-48 Isolasi antar tenant</td><td>_____</td></tr>
                    </tbody>
                </table>
            </section>

            <section class="card" id="hasil">
                <h2>Template Hasil UAT Dan Catatan Temuan</h2>
                <div class="callout warn">
                    <p>Gunakan PASS jika sesuai expected, FAIL jika hasilnya salah, BLOCKED jika tidak bisa diuji karena bug lain, dan NA jika fitur memang belum tersedia di build ini.</p>
                </div>
                <table class="status-table" style="margin-top: 14px;">
                    <thead>
                        <tr>
                            <th>Area</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>Onboarding tenant</td><td>_____</td></tr>
                        <tr><td>Master data dan cabang</td><td>_____</td></tr>
                        <tr><td>Pengaturan produk per cabang dan jadwal jual</td><td>_____</td></tr>
                        <tr><td>Promo, voucher, diskon, redeem</td><td>_____</td></tr>
                        <tr><td>POS dan transaksi meja</td><td>_____</td></tr>
                        <tr><td>Shift dan kasir</td><td>_____</td></tr>
                        <tr><td>Stok, resep, produksi</td><td>_____</td></tr>
                        <tr><td>Laporan operasional</td><td>_____</td></tr>
                        <tr><td>Akuntansi dan COA</td><td>_____</td></tr>
                        <tr><td>Subscription dan audit</td><td>_____</td></tr>
                        <tr><td>Isolasi tenant</td><td>_____</td></tr>
                    </tbody>
                </table>
                <h3>Daftar Temuan</h3>
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Menu / Area</th>
                            <th>Expected</th>
                            <th>Aktual</th>
                            <th>Status</th>
                            <th>Catatan Tindak Lanjut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>1</td><td>________________</td><td>________________</td><td>________________</td><td>_____</td><td>________________</td></tr>
                        <tr><td>2</td><td>________________</td><td>________________</td><td>________________</td><td>_____</td><td>________________</td></tr>
                        <tr><td>3</td><td>________________</td><td>________________</td><td>________________</td><td>_____</td><td>________________</td></tr>
                        <tr><td>4</td><td>________________</td><td>________________</td><td>________________</td><td>_____</td><td>________________</td></tr>
                        <tr><td>5</td><td>________________</td><td>________________</td><td>________________</td><td>_____</td><td>________________</td></tr>
                        <tr><td>6</td><td>________________</td><td>________________</td><td>________________</td><td>_____</td><td>________________</td></tr>
                    </tbody>
                </table>
            </section>

            <section class="card" id="ttd">
                <h2>Penutup Dan Tanda Tangan</h2>
                <p>UAT dianggap lengkap bila semua menu utama tenant sudah disentuh, transaksi kritikal berjalan, stok dan laporan konsisten, promo tidak merusak total pembayaran, serta hasil operasional bisa dicocokkan dengan ekspektasi akuntansi.</p>
                <div class="signatures">
                    <div class="signature">QA / Tester</div>
                    <div class="signature">PIC Operasional</div>
                    <div class="signature">PIC Finance</div>
                    <div class="signature">PIC Dev / Product</div>
                </div>
            </section>
        </main>
    </div>
</div>
</body>
</html>
