<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Daftar tenant baru POS Platform dengan tampilan modern dan alur onboarding yang jelas.">
    <title>Daftar Tenant | POS Platform</title>
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
            --platform-ink: #18243f;
            --platform-muted: #687799;
            --platform-line: rgba(24, 36, 63, 0.1);
            --platform-surface: rgba(255, 255, 255, 0.96);
            --platform-danger: #c33854;
            --platform-danger-soft: #fff2f6;
            --platform-success: #0f9d81;
            --platform-success-soft: rgba(15, 157, 129, 0.12);
            --platform-shadow: 0 28px 72px rgba(37, 45, 89, 0.14);
            --platform-shadow-soft: 0 18px 48px rgba(37, 45, 89, 0.1);
            --platform-radius: 26px;
        }

        body.register-chain {
            color: var(--platform-ink);
            background:
                radial-gradient(circle at top left, rgba(91, 104, 235, 0.18), transparent 22%),
                radial-gradient(circle at top right, rgba(40, 225, 253, 0.12), transparent 18%),
                #fff;
        }

        .register-chain .header-area {
            position: absolute;
        }

        .register-chain .main-banner {
            padding: 190px 0 110px;
        }

        .register-chain .left-content p {
            margin-right: 0;
            color: var(--platform-muted);
            font-size: 16px;
            line-height: 1.9;
        }

        .register-chain .hero-mini-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
            margin-top: 28px;
        }

        .register-chain .hero-mini-card,
        .register-chain .trust-panel,
        .register-chain .register-panel,
        .register-chain .status-panel,
        .register-chain .feature-card,
        .register-chain .timeline-card {
            border-radius: var(--platform-radius);
            background: var(--platform-surface);
            box-shadow: var(--platform-shadow-soft);
        }

        .register-chain .hero-mini-card {
            padding: 20px 22px;
            border: 1px solid rgba(255, 255, 255, 0.76);
        }

        .register-chain .hero-mini-card strong {
            display: block;
            margin-bottom: 8px;
            font-size: 16px;
            font-weight: 700;
        }

        .register-chain .hero-mini-card span {
            color: var(--platform-muted);
            font-size: 14px;
            line-height: 1.8;
        }

        .register-chain .hero-cta-stack {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
            margin-top: 28px;
        }

        .register-chain .hero-cta-stack .white-button a,
        .register-chain .hero-cta-stack .gradient-button a {
            min-width: 210px;
            text-align: center;
        }

        .register-chain .trust-panel {
            margin-top: 26px;
            padding: 24px;
            border: 1px solid var(--platform-line);
        }

        .register-chain .trust-panel .badge-label,
        .register-chain .register-panel .badge-label,
        .register-chain .status-panel .badge-label {
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

        .register-chain .trust-panel .badge-label::before,
        .register-chain .register-panel .badge-label::before,
        .register-chain .status-panel .badge-label::before {
            content: "";
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: linear-gradient(105deg, rgba(91, 104, 235, 1) 0%, rgba(40, 225, 253, 1) 100%);
        }

        .register-chain .trust-panel h5,
        .register-chain .register-panel h4,
        .register-chain .status-panel h4 {
            margin: 18px 0 10px;
            font-size: 28px;
            line-height: 1.2;
        }

        .register-chain .trust-panel p,
        .register-chain .status-panel p,
        .register-chain .helper-copy,
        .register-chain .field-copy,
        .register-chain .timeline-card p,
        .register-chain .feature-card p,
        .register-chain .status-note {
            color: var(--platform-muted);
            line-height: 1.8;
        }

        .register-chain .timeline-list,
        .register-chain .feature-grid {
            display: grid;
            gap: 16px;
            margin-top: 24px;
        }

        .register-chain .timeline-card,
        .register-chain .feature-card {
            padding: 20px;
            border: 1px solid var(--platform-line);
        }

        .register-chain .timeline-card strong,
        .register-chain .feature-card strong {
            display: block;
            margin-bottom: 8px;
            font-size: 17px;
        }

        .register-chain .timeline-step {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            margin-bottom: 14px;
            border-radius: 16px;
            color: #fff;
            font-size: 13px;
            font-weight: 700;
            background: linear-gradient(105deg, rgba(91, 104, 235, 1) 0%, rgba(40, 225, 253, 1) 100%);
        }

        .register-chain .register-panel,
        .register-chain .status-panel {
            padding: 30px;
            border: 1px solid rgba(91, 104, 235, 0.08);
        }

        .register-chain .status-panel {
            margin-bottom: 22px;
        }

        .register-chain .status-panel[hidden] {
            display: none;
        }

        .register-chain .status-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 16px;
        }

        .register-chain .status-actions a,
        .register-chain .status-actions button {
            border: 0;
            cursor: pointer;
        }

        .register-chain .alert-errors {
            margin-bottom: 22px;
            padding: 18px 20px 18px 38px;
            border: 1px solid rgba(195, 56, 84, 0.2);
            border-radius: 20px;
            background: var(--platform-danger-soft);
            color: var(--platform-danger);
        }

        .register-chain .cross-device-panel {
            margin-bottom: 22px;
            padding: 18px 20px;
            border-radius: 20px;
            border: 1px solid rgba(15, 157, 129, 0.14);
            background: var(--platform-success-soft);
        }

        .register-chain .cross-device-panel[hidden] {
            display: none;
        }

        .register-chain .cross-device-panel strong {
            display: block;
            margin-bottom: 8px;
            font-size: 15px;
        }

        .register-chain .cross-device-panel a {
            color: #5b68eb;
            font-weight: 700;
            text-decoration: none;
        }

        .register-chain .info-box {
            margin-bottom: 22px;
            padding: 20px;
            border-radius: 22px;
            border: 1px solid var(--platform-line);
            background: rgba(245, 247, 255, 0.9);
        }

        .register-chain .info-box strong {
            display: block;
            margin-bottom: 10px;
            font-size: 16px;
        }

        .register-chain .info-box ul {
            margin: 0;
            padding-left: 18px;
        }

        .register-chain .info-box li {
            color: var(--platform-muted);
            line-height: 1.8;
        }

        .register-chain .field-grid {
            display: grid;
            gap: 18px;
            margin-bottom: 18px;
        }

        .register-chain .field-grid.two {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .register-chain .field label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 700;
        }

        .register-chain .field-copy {
            display: block;
            margin-bottom: 10px;
            font-size: 13px;
        }

        .register-chain .field input,
        .register-chain .field select,
        .register-chain .field textarea {
            width: 100%;
            padding: 15px 18px;
            border: 1px solid rgba(91, 104, 235, 0.14);
            border-radius: 18px;
            font-size: 15px;
            color: var(--platform-ink);
            background: #fff;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .register-chain .field input:focus,
        .register-chain .field select:focus,
        .register-chain .field textarea:focus {
            outline: none;
            border-color: rgba(91, 104, 235, 0.42);
            box-shadow: 0 0 0 4px rgba(91, 104, 235, 0.08);
        }

        .register-chain .field textarea {
            min-height: 130px;
            resize: vertical;
        }

        .register-chain .field-error {
            display: block;
            margin-top: 8px;
            font-size: 13px;
            color: var(--platform-danger);
        }

        .register-chain .submit-wrap {
            margin-top: 8px;
        }

        .register-chain .submit-note {
            display: block;
            margin-top: 12px;
            color: var(--platform-muted);
            font-size: 13px;
            line-height: 1.8;
        }

        .register-chain .plan-note {
            margin: 24px 0 0;
            padding: 18px 20px;
            border-radius: 20px;
            border: 1px solid rgba(91, 104, 235, 0.12);
            background: linear-gradient(135deg, rgba(91, 104, 235, 0.08), rgba(40, 225, 253, 0.08));
            color: var(--platform-muted);
            line-height: 1.8;
        }

        .register-chain .register-footer-links {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 22px;
        }

        .register-chain .register-footer-links a {
            color: #5b68eb;
            font-weight: 700;
            text-decoration: none;
        }

        @media (max-width: 991px) {
            .register-chain .main-banner {
                padding-top: 140px;
            }

            .register-chain .hero-mini-grid,
            .register-chain .field-grid.two {
                grid-template-columns: 1fr;
            }

            .register-chain .register-panel,
            .register-chain .status-panel,
            .register-chain .trust-panel {
                padding: 22px;
            }
        }
    </style>
</head>
<body class="register-chain">
    <header class="header-area header-sticky wow slideInDown" data-wow-duration="0.75s" data-wow-delay="0s">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <nav class="main-nav">
                        <a href="{{ route('platform.landing') }}" class="logo">
                            <img src="{{ asset('chain-1.0.0/assets/images/logo.png') }}" alt="POS Platform">
                        </a>
                        <ul class="nav">
                            <li><a href="{{ route('platform.landing') }}">Landing</a></li>
                            <li><a href="#benefit">Benefit</a></li>
                            <li><a href="#alur">Alur</a></li>
                            <li><a href="#form-daftar" class="active">Daftar</a></li>
                            <li><a href="{{ route('platform.login') }}">Admin</a></li>
                            <li><div class="gradient-button"><a href="/login"><i class="fa fa-sign-in-alt"></i> Login Tenant</a></div></li>
                        </ul>
                        <a class="menu-trigger">
                            <span>Menu</span>
                        </a>
                    </nav>
                </div>
            </div>
        </div>
    </header>

    <div class="main-banner" id="top">
        <div class="container">
            <div class="row align-items-start">
                <div class="col-lg-6">
                    <div class="left-content show-up header-text wow fadeInLeft" data-wow-duration="1s" data-wow-delay="0.4s">
                        <div class="section-heading mb-0">
                            <h2>Theme baru untuk pendaftaran tenant yang terasa seperti landing SaaS premium</h2>
                            <img src="{{ asset('chain-1.0.0/assets/images/heading-line-dec.png') }}" alt="">
                        </div>
                        <p>
                            Form `/daftar` sekarang dibungkus visual ala `Chain App` agar lebih kuat secara first impression.
                            Fokusnya bukan cuma cantik, tapi juga lebih jelas, lebih meyakinkan, dan tetap aman untuk flow produksi:
                            calon tenant tinggal isi, sistem tetap menyimpan draft, dan admin masih bisa memantau onboarding dari belakang layar.
                        </p>

                        <div class="hero-cta-stack">
                            <div class="gradient-button">
                                <a href="#form-daftar">Daftar Sekarang</a>
                            </div>
                            <div class="white-button">
                                <a href="{{ route('platform.landing') }}">Lihat Landing Utama</a>
                            </div>
                        </div>

                        <div class="hero-mini-grid">
                            <div class="hero-mini-card">
                                <strong>Draft otomatis</strong>
                                <span>Form tetap tersimpan lokal dan bisa lanjut lagi tanpa mulai dari nol.</span>
                            </div>
                            <div class="hero-mini-card">
                                <strong>Resume lintas perangkat</strong>
                                <span>Kalau perlu, draft juga bisa dibuka ulang dari perangkat lain via link aman.</span>
                            </div>
                            <div class="hero-mini-card">
                                <strong>Onboarding terarah</strong>
                                <span>Nama bisnis, owner, plan, usaha awal, dan cabang awal dikumpulkan dalam satu alur.</span>
                            </div>
                            <div class="hero-mini-card">
                                <strong>Siap produksi</strong>
                                <span>Visual baru tetap menjaga struktur backend, validasi, dan monitoring yang sudah ada.</span>
                            </div>
                        </div>

                        <div class="trust-panel" id="benefit">
                            <span class="badge-label">Why It Works</span>
                            <h5>Pendaftaran publik yang lebih meyakinkan untuk conversion, tanpa bikin operasional belakangnya berantakan.</h5>
                            <p>
                                Halaman ini sengaja dibuat mirip SaaS landing modern: hero yang kuat, value proposition yang jelas,
                                dan blok form yang terasa fokus. User jadi lebih percaya diri buat mulai trial atau onboarding.
                            </p>

                            <div class="feature-grid">
                                <div class="feature-card">
                                    <strong>Lebih enak di-scan</strong>
                                    <p>Informasi penting dibagi ke blok yang jelas, jadi calon tenant cepat paham apa yang harus diisi dan apa yang akan terjadi setelah submit.</p>
                                </div>
                                <div class="feature-card">
                                    <strong>Lebih cocok untuk sales dan marketing</strong>
                                    <p>Tampilan visualnya sekarang bisa dipakai langsung untuk campaign, demo, atau invite onboarding tanpa terasa seperti halaman admin internal.</p>
                                </div>
                            </div>
                        </div>

                        <div class="trust-panel" id="alur">
                            <span class="badge-label">Alur Onboarding</span>
                            <h5>Calon tenant isi form, sistem siapkan fondasi, dan tim platform tetap bisa bantu kalau ada yang macet.</h5>
                            <div class="timeline-list">
                                <div class="timeline-card">
                                    <span class="timeline-step">01</span>
                                    <strong>Data bisnis dan owner dikumpulkan</strong>
                                    <p>Nama tenant, email owner, plan awal, usaha, dan cabang pertama langsung menjadi titik awal tenant baru.</p>
                                </div>
                                <div class="timeline-card">
                                    <span class="timeline-step">02</span>
                                    <strong>Draft tetap aman kalau browser ditutup</strong>
                                    <p>Progress tidak hilang begitu saja. User bisa lanjut lagi tanpa mengulang semua field dari awal.</p>
                                </div>
                                <div class="timeline-card">
                                    <span class="timeline-step">03</span>
                                    <strong>Admin tetap punya backup console</strong>
                                    <p>Kalau provisioning perlu recovery atau follow-up, tim internal tetap bisa menangani tenant dari panel operasional.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="right-image wow fadeInRight" data-wow-duration="1s" data-wow-delay="0.5s">
                        <div class="status-panel" id="resume-registration" hidden>
                            <span class="badge-label">Resume Registration</span>
                            <h4 id="resume-title">Ada pendaftaran yang masih berjalan</h4>
                            <p id="resume-copy" class="status-note">Jika browser sempat ditutup, Anda bisa melanjutkan proses tanpa mengulang isi form dari awal.</p>
                            <div class="status-actions">
                                <div class="gradient-button">
                                    <a id="resume-link" href="{{ route('platform.register.create') }}">Lanjutkan Pantau Status</a>
                                </div>
                                <button class="white-button" type="button" id="dismiss-resume">
                                    <span style="display:inline-block;padding:0 22px;line-height:46px;">Tutup Info</span>
                                </button>
                            </div>
                        </div>

                        <section class="register-panel wow fadeInUp" id="form-daftar" data-wow-duration="1s" data-wow-delay="0.6s">
                            <span class="badge-label">Public Registration Flow</span>
                            <h4>Daftarkan tenant baru dengan tampilan yang lebih profesional</h4>
                            <p class="helper-copy">
                                Form ini tetap tersambung ke flow pendaftaran yang sama, jadi yang berubah adalah rasa dan tampilannya,
                                bukan kestabilan proses di belakangnya.
                            </p>

                            @if ($errors->any())
                                <div class="alert-errors">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="cross-device-panel" id="server-draft-status" hidden>
                                <strong>Draft lintas perangkat aktif</strong>
                                <span id="server-draft-copy" class="status-note">Link resume aman sedang disiapkan agar form ini bisa dibuka lagi dari perangkat lain.</span>
                                <div class="register-footer-links">
                                    <a id="server-draft-link" href="{{ route('platform.register.create') }}">Buka Link Resume</a>
                                </div>
                            </div>

                            <div class="info-box">
                                <strong>Apa yang terjadi setelah submit?</strong>
                                <ul>
                                    <li>Tenant baru dicatat ke platform dengan owner utama dan plan awalnya.</li>
                                    <li>Data usaha pertama dan cabang awal langsung jadi fondasi onboarding.</li>
                                    <li>Kalau perlu bantuan, admin platform tetap bisa melakukan recovery dari panel internal.</li>
                                </ul>
                            </div>

                            <form method="POST" action="{{ route('platform.register.store') }}">
                                @csrf
                                <input id="registration_draft_token" type="hidden" name="registration_draft_token" value="{{ $initialDraftToken }}">

                                <div class="field-grid">
                                    <div class="field">
                                        <label for="company_name">Nama Perusahaan / Tenant</label>
                                        <span class="field-copy">Nama brand atau legal entity yang akan tampil sebagai identitas tenant.</span>
                                        <input id="company_name" type="text" name="company_name" value="{{ old('company_name') }}" placeholder="PT Maju Makmur Kuliner" required>
                                        @error('company_name')<span class="field-error">{{ $message }}</span>@enderror
                                    </div>
                                </div>

                                <div class="field-grid two">
                                    <div class="field">
                                        <label for="owner_name">Nama Owner</label>
                                        <span class="field-copy">Penanggung jawab utama untuk akun tenant pertama.</span>
                                        <input id="owner_name" type="text" name="owner_name" value="{{ old('owner_name') }}" placeholder="Frans" required>
                                        @error('owner_name')<span class="field-error">{{ $message }}</span>@enderror
                                    </div>
                                    <div class="field">
                                        <label for="owner_email">Email Owner</label>
                                        <span class="field-copy">Dipakai untuk login owner dan komunikasi onboarding tenant.</span>
                                        <input id="owner_email" type="email" name="owner_email" value="{{ old('owner_email') }}" placeholder="owner@contoh.com" required>
                                        @error('owner_email')<span class="field-error">{{ $message }}</span>@enderror
                                    </div>
                                </div>

                                <div class="field-grid two">
                                    <div class="field">
                                        <label for="owner_password">Password Awal Owner</label>
                                        <span class="field-copy">Gunakan minimal 8 karakter agar akun awal lebih aman.</span>
                                        <input id="owner_password" type="password" name="owner_password" placeholder="Minimal 8 karakter" required>
                                        @error('owner_password')<span class="field-error">{{ $message }}</span>@enderror
                                    </div>
                                    <div class="field">
                                        <label for="owner_phone">No. HP</label>
                                        <span class="field-copy">Opsional, tetapi membantu untuk follow-up aktivasi dan support.</span>
                                        <input id="owner_phone" type="text" name="owner_phone" value="{{ old('owner_phone') }}" placeholder="0812xxxxxx">
                                        @error('owner_phone')<span class="field-error">{{ $message }}</span>@enderror
                                    </div>
                                </div>

                                <div class="field-grid two">
                                    <div class="field">
                                        <label for="business_name">Nama Usaha Awal</label>
                                        <span class="field-copy">Unit bisnis pertama yang akan langsung aktif di tenant baru.</span>
                                        <input id="business_name" type="text" name="business_name" value="{{ old('business_name', old('company_name')) }}" placeholder="Usaha Utama" required>
                                        @error('business_name')<span class="field-error">{{ $message }}</span>@enderror
                                    </div>
                                    <div class="field">
                                        <label for="branch_name">Nama Cabang Awal</label>
                                        <span class="field-copy">Cabang pertama untuk mulai operasional dan monitoring.</span>
                                        <input id="branch_name" type="text" name="branch_name" value="{{ old('branch_name', 'Cabang 1') }}" placeholder="Cabang 1" required>
                                        @error('branch_name')<span class="field-error">{{ $message }}</span>@enderror
                                    </div>
                                </div>

                                <div class="field-grid">
                                    <div class="field">
                                        <label for="plan_code">Paket</label>
                                        <span class="field-copy">Pilih plan awal yang paling sesuai dengan skala bisnis tenant.</span>
                                        <select id="plan_code" name="plan_code" required>
                                            <option value="">Pilih paket</option>
                                            @foreach ($plans as $plan)
                                                <option value="{{ $plan->code }}" @selected(old('plan_code') === $plan->code)>
                                                    {{ $plan->name }} - {{ $plan->code === 'trial' ? 'Gratis 14 hari' : ($plan->price > 0 ? 'Rp ' . number_format((float) $plan->price, 0, ',', '.') . '/' . $plan->billing_cycle : 'Hubungi Sales') }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('plan_code')<span class="field-error">{{ $message }}</span>@enderror
                                    </div>
                                </div>

                                <div class="field-grid">
                                    <div class="field">
                                        <label for="notes">Catatan Tambahan</label>
                                        <span class="field-copy">Opsional. Isi konteks seperti jumlah outlet, target go-live, atau kebutuhan onboarding khusus.</span>
                                        <textarea id="notes" name="notes" placeholder="Contoh: butuh 3 cabang, ingin onboarding minggu ini">{{ old('notes') }}</textarea>
                                        @error('notes')<span class="field-error">{{ $message }}</span>@enderror
                                    </div>
                                </div>

                                <div class="plan-note">
                                    Setelah form ini dikirim, sistem akan menyiapkan data tenant, subscription, dan fondasi onboarding awal.
                                    Tema halaman berubah jadi lebih premium, tapi backend dan flow operasional tetap aman seperti sebelumnya.
                                </div>

                                <div class="submit-wrap">
                                    <div class="gradient-button">
                                        <button id="submit-registration" type="submit" style="border:0;background:none;color:#fff;font-weight:700;padding:0 32px;line-height:46px;">
                                            Kirim Pendaftaran
                                        </button>
                                    </div>
                                    <span class="submit-note">Pastikan email owner aktif karena akan menjadi akun utama tenant baru.</span>
                                </div>
                            </form>

                            <div class="register-footer-links">
                                <a href="{{ route('platform.landing') }}">Kembali ke landing</a>
                                <a href="{{ route('platform.login') }}">Login Platform Admin</a>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('chain-1.0.0/vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('chain-1.0.0/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('chain-1.0.0/assets/js/custom.js') }}"></script>
    <script>
        (() => {
            const draftKey = 'platform_registration_draft_v1';
            const draftServerKey = 'platform_registration_server_draft_v1';
            const registrationKey = 'platform_registration_progress_v1';
            const csrfToken = @json(csrf_token());
            const initialDraftToken = @json($initialDraftToken);
            const draftSaveUrl = @json($draftSaveUrl);
            const draftLoadUrlTemplate = @json($draftLoadUrlTemplate);
            const form = document.querySelector('form[action="{{ route('platform.register.store') }}"]');
            const submitButton = document.getElementById('submit-registration');
            const draftTokenField = document.getElementById('registration_draft_token');
            const resumePanel = document.getElementById('resume-registration');
            const resumeTitle = document.getElementById('resume-title');
            const resumeCopy = document.getElementById('resume-copy');
            const resumeLink = document.getElementById('resume-link');
            const dismissResume = document.getElementById('dismiss-resume');
            const serverDraftStatus = document.getElementById('server-draft-status');
            const serverDraftCopy = document.getElementById('server-draft-copy');
            const serverDraftLink = document.getElementById('server-draft-link');

            if (!form) {
                return;
            }

            const fields = Array.from(form.querySelectorAll('input[name], select[name], textarea[name]'));
            const persistableFields = fields.filter((field) => field.name !== 'owner_password' && field.type !== 'password');
            let serverDraftToken = initialDraftToken || '';
            let serverDraftSaveHandle = null;
            let serverDraftRequestInFlight = false;

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
                    // Ignore storage failures and keep the form usable.
                }
            }

            function removeKey(key) {
                try {
                    window.localStorage.removeItem(key);
                } catch (error) {
                    // Ignore storage failures and keep the form usable.
                }
            }

            function mergeDraftIntoForm(draft) {
                if (!draft || typeof draft !== 'object') {
                    return;
                }

                persistableFields.forEach((field) => {
                    const savedValue = draft[field.name];

                    if (field.value || savedValue === undefined || savedValue === null) {
                        return;
                    }

                    field.value = savedValue;
                });
            }

            function restoreDraft() {
                mergeDraftIntoForm(readJson(draftKey));
            }

            function collectDraftPayload() {
                const payload = {};

                persistableFields.forEach((field) => {
                    payload[field.name] = field.value;
                });

                return payload;
            }

            function saveDraft() {
                writeJson(draftKey, collectDraftPayload());
            }

            function collectMeaningfulPayload() {
                return Object.fromEntries(
                    Object.entries(collectDraftPayload()).filter(([, value]) => value !== null && value !== '')
                );
            }

            function buildDraftLoadUrl(token) {
                return draftLoadUrlTemplate.replace('__TOKEN__', encodeURIComponent(token));
            }

            function syncServerDraftStatus(meta) {
                if (!serverDraftStatus || !serverDraftCopy || !serverDraftLink) {
                    return;
                }

                if (!meta || !meta.draft_token || !meta.resume_url) {
                    serverDraftStatus.hidden = true;
                    return;
                }

                serverDraftStatus.hidden = false;
                serverDraftLink.href = meta.resume_url;
                serverDraftCopy.textContent = 'Link resume ini bisa dibuka lagi dari perangkat lain untuk melanjutkan pengisian form yang sama.';
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
                    // Keep the form usable even if the background draft sync fails.
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

            function syncResumePanel() {
                const progress = readJson(registrationKey);

                if (!progress || !progress.success_url || progress.dismissed === true) {
                    resumePanel.hidden = true;
                    return;
                }

                resumePanel.hidden = false;

                if (progress.status === 'completed' && progress.login_url) {
                    resumeTitle.textContent = 'Tenant Anda sudah siap digunakan';
                    resumeCopy.textContent = 'Onboarding terakhir tercatat selesai. Anda bisa langsung masuk ke halaman login tenant.';
                    resumeLink.textContent = 'Masuk ke Tenant';
                    resumeLink.href = progress.login_url;
                    return;
                }

                if (progress.status === 'failed') {
                    resumeTitle.textContent = 'Provisioning tenant terakhir perlu dicek';
                    resumeCopy.textContent = 'Anda tidak perlu mengisi ulang form. Buka halaman status untuk retry atau memeriksa progres terakhir.';
                    resumeLink.textContent = 'Buka Halaman Status';
                    resumeLink.href = progress.success_url;
                    return;
                }

                resumeTitle.textContent = 'Ada pendaftaran yang masih berjalan';
                resumeCopy.textContent = 'Jika browser sempat ditutup, Anda bisa lanjut memantau status pendaftaran tanpa mengulang isi form.';
                resumeLink.textContent = 'Lanjutkan Pantau Status';
                resumeLink.href = progress.success_url;
            }

            restoreDraft();
            syncResumePanel();

            const savedServerDraft = readJson(draftServerKey);

            if (!serverDraftToken && savedServerDraft?.draft_token) {
                serverDraftToken = savedServerDraft.draft_token;
            }

            if (savedServerDraft?.resume_url) {
                syncServerDraftStatus(savedServerDraft);
            }

            if (serverDraftToken) {
                loadServerDraft(serverDraftToken);
            }

            persistableFields.forEach((field) => {
                field.addEventListener('input', () => {
                    saveDraft();
                    queueServerDraftSave();
                });
                field.addEventListener('change', () => {
                    saveDraft();
                    queueServerDraftSave();
                });
            });

            if (dismissResume) {
                dismissResume.addEventListener('click', () => {
                    const progress = readJson(registrationKey);

                    if (!progress) {
                        resumePanel.hidden = true;
                        return;
                    }

                    progress.dismissed = true;
                    writeJson(registrationKey, progress);
                    syncResumePanel();
                });
            }

            form.addEventListener('submit', () => {
                saveDraft();
                queueServerDraftSave();
                removeKey(registrationKey);
                syncResumePanel();

                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.textContent = 'Memproses Pendaftaran...';
                }
            });
        })();
    </script>
</body>
</html>
