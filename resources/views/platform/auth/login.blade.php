<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Admin Platform</title>
    @php($hasViteAssets = file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
    @if ($hasViteAssets)
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <style>
            :root {
                color-scheme: dark;
                --platform-bg: #020617;
                --platform-bg-soft: #0f172a;
                --platform-panel: rgba(15, 23, 42, 0.88);
                --platform-panel-strong: rgba(2, 6, 23, 0.9);
                --platform-line: rgba(148, 163, 184, 0.18);
                --platform-text: #e2e8f0;
                --platform-muted: #94a3b8;
                --platform-accent: #34d399;
                --platform-accent-strong: #10b981;
                --platform-danger: #fda4af;
            }

            * {
                box-sizing: border-box;
            }

            html,
            body {
                margin: 0;
                min-height: 100%;
                font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
                background:
                    radial-gradient(circle at top left, rgba(16, 185, 129, 0.18), transparent 30%),
                    radial-gradient(circle at bottom right, rgba(14, 165, 233, 0.14), transparent 26%),
                    linear-gradient(180deg, var(--platform-bg) 0%, var(--platform-bg) 35%, var(--platform-bg-soft) 100%);
                color: var(--platform-text);
            }

            body {
                line-height: 1.5;
            }

            .hidden {
                display: none !important;
            }

            .animate-spin {
                animation: platform-spin 0.9s linear infinite;
            }

            @keyframes platform-spin {
                to {
                    transform: rotate(360deg);
                }
            }

            body > .fixed {
                position: fixed;
                inset: 0;
            }

            body > .fixed:last-of-type {
                opacity: 0.4;
                background-image:
                    linear-gradient(rgba(148, 163, 184, 0.08) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(148, 163, 184, 0.08) 1px, transparent 1px);
                background-size: 72px 72px;
                pointer-events: none;
            }

            body > div.mx-auto {
                position: relative;
                z-index: 1;
                width: min(100%, 1280px);
                min-height: 100vh;
                display: flex;
                align-items: center;
                padding: 32px 16px;
                margin: 0 auto;
            }

            body > div.mx-auto > div.grid {
                width: 100%;
                display: grid;
                overflow: hidden;
                border: 1px solid rgba(255, 255, 255, 0.1);
                border-radius: 32px;
                background: rgba(15, 23, 42, 0.72);
                box-shadow: 0 40px 120px rgba(2, 6, 23, 0.55);
                backdrop-filter: blur(18px);
            }

            body section:first-of-type {
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                gap: 32px;
                padding: 28px;
            }

            body section:first-of-type .inline-flex {
                display: inline-flex;
                align-items: center;
                gap: 12px;
                width: fit-content;
                padding: 10px 16px;
                border-radius: 999px;
                border: 1px solid rgba(52, 211, 153, 0.2);
                background: rgba(52, 211, 153, 0.1);
                color: #d1fae5;
                font-size: 12px;
                font-weight: 700;
                letter-spacing: 0.28em;
                text-transform: uppercase;
            }

            body section:first-of-type .inline-flex span {
                display: inline-block;
                width: 8px;
                height: 8px;
                border-radius: 999px;
                background: #6ee7b7;
                box-shadow: 0 0 18px rgba(110, 231, 183, 0.95);
            }

            h1,
            h2,
            p {
                margin-top: 0;
            }

            h1 {
                max-width: 760px;
                margin-bottom: 16px;
                color: #fff;
                font-size: clamp(2rem, 4vw, 3.4rem);
                line-height: 1.1;
            }

            h2 {
                margin-bottom: 8px;
                color: #fff;
                font-size: clamp(1.7rem, 3vw, 2.25rem);
                line-height: 1.15;
            }

            body section:first-of-type > div:first-child p:last-child,
            body section:last-of-type > div > div:first-child p:last-child,
            body form > p:last-child {
                color: var(--platform-muted);
            }

            body section:first-of-type > div:last-child {
                display: grid;
                gap: 16px;
            }

            body section:first-of-type article,
            body form > div:nth-of-type(3) {
                border: 1px solid rgba(255, 255, 255, 0.1);
                border-radius: 24px;
                background: rgba(255, 255, 255, 0.05);
                padding: 16px;
            }

            body section:first-of-type article p:first-child {
                margin-bottom: 8px;
                color: #fff;
                font-weight: 700;
            }

            body section:first-of-type article p:last-child {
                color: #cbd5e1;
                font-size: 14px;
            }

            body section:last-of-type {
                padding: 24px 20px;
                border-top: 1px solid rgba(255, 255, 255, 0.1);
                background: var(--platform-panel-strong);
            }

            body section:last-of-type > div {
                max-width: 520px;
                margin: 0 auto;
            }

            body section:last-of-type > div > div:first-child p:first-child {
                margin-bottom: 10px;
                color: #bae6fd;
                font-size: 12px;
                font-weight: 700;
                letter-spacing: 0.2em;
                text-transform: uppercase;
            }

            form {
                display: grid;
                gap: 20px;
            }

            label {
                display: block;
                margin-bottom: 8px;
                font-size: 14px;
                font-weight: 600;
                color: #e2e8f0;
            }

            input[type="email"],
            input[type="password"],
            input[type="text"] {
                width: 100%;
                border: 1px solid rgba(71, 85, 105, 0.9);
                border-radius: 16px;
                background: rgba(15, 23, 42, 0.86);
                color: #f8fafc;
                padding: 14px 16px;
                font-size: 14px;
                outline: none;
            }

            input::placeholder {
                color: #64748b;
            }

            input:focus,
            button:focus {
                border-color: var(--platform-accent);
                box-shadow: 0 0 0 4px rgba(52, 211, 153, 0.15);
            }

            form .relative {
                position: relative;
            }

            #password {
                padding-right: 84px;
            }

            #toggle-password {
                position: absolute;
                top: 6px;
                right: 6px;
                bottom: 6px;
                border: 1px solid transparent;
                border-radius: 12px;
                background: transparent;
                color: #cbd5e1;
                padding: 0 14px;
                font-size: 12px;
                font-weight: 700;
                cursor: pointer;
            }

            #toggle-password:hover {
                border-color: rgba(255, 255, 255, 0.1);
                background: rgba(255, 255, 255, 0.05);
                color: #fff;
            }

            #caps-lock-hint,
            .text-rose-300 {
                color: #fde68a;
                font-size: 12px;
                font-weight: 600;
            }

            .text-rose-300 {
                color: var(--platform-danger);
                margin-top: 8px;
            }

            form > div:nth-of-type(2) > div:first-child {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
            }

            form > div:nth-of-type(3) {
                display: flex;
                flex-direction: column;
                gap: 12px;
            }

            form > div:nth-of-type(3) label {
                display: flex;
                align-items: center;
                gap: 10px;
                margin: 0;
                font-weight: 500;
                color: #cbd5e1;
            }

            input[type="checkbox"] {
                width: 16px;
                height: 16px;
                accent-color: var(--platform-accent-strong);
            }

            form > div:nth-of-type(3) p {
                margin: 0;
                color: var(--platform-muted);
                font-size: 12px;
            }

            button[type="submit"] {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 12px;
                width: 100%;
                border: 0;
                border-radius: 16px;
                background: var(--platform-accent);
                color: #022c22;
                padding: 14px 16px;
                font-size: 14px;
                font-weight: 700;
                cursor: pointer;
                box-shadow: 0 18px 40px rgba(16, 185, 129, 0.3);
            }

            button[type="submit"]:hover {
                background: #6ee7b7;
                transform: translateY(-1px);
            }

            button[disabled] {
                cursor: wait;
                opacity: 0.72;
                transform: none;
                box-shadow: none;
            }

            button[type="submit"] svg {
                width: 16px;
                height: 16px;
            }

            [class*="rose-400"] {
                border: 1px solid rgba(244, 63, 94, 0.25);
                border-radius: 16px;
                background: rgba(244, 63, 94, 0.1);
                color: #ffe4e6;
                padding: 14px 16px;
            }

            @media (min-width: 768px) {
                body > div.mx-auto > div.grid {
                    grid-template-columns: 1.08fr 0.92fr;
                }

                body section:first-of-type {
                    padding: 40px;
                }

                body section:first-of-type > div:last-child {
                    grid-template-columns: repeat(3, minmax(0, 1fr));
                }

                body section:last-of-type {
                    border-top: 0;
                    border-left: 1px solid rgba(255, 255, 255, 0.1);
                    padding: 32px 28px;
                }

                form > div:nth-of-type(3) {
                    flex-direction: row;
                    align-items: center;
                    justify-content: space-between;
                }
            }
        </style>
    @endif
</head>
<body class="min-h-screen overflow-x-hidden bg-slate-950 text-slate-100 antialiased">
    <div class="fixed inset-0 -z-10 bg-[radial-gradient(circle_at_top_left,_rgba(16,185,129,0.18),_transparent_30%),radial-gradient(circle_at_bottom_right,_rgba(14,165,233,0.14),_transparent_26%),linear-gradient(180deg,_#020617_0%,_#020617_35%,_#0f172a_100%)]"></div>
    <div class="fixed inset-0 -z-10 opacity-40 [background-image:linear-gradient(rgba(148,163,184,0.08)_1px,transparent_1px),linear-gradient(90deg,rgba(148,163,184,0.08)_1px,transparent_1px)] [background-size:72px_72px]"></div>

    <div class="mx-auto flex min-h-screen max-w-7xl items-center px-4 py-8 sm:px-6 lg:px-8">
        <div class="grid w-full overflow-hidden rounded-[2rem] border border-white/10 bg-slate-900/70 shadow-[0_40px_120px_rgba(2,6,23,0.55)] backdrop-blur-xl md:grid-cols-[1.08fr_0.92fr]">
            <section class="relative flex flex-col justify-between gap-10 p-7 sm:p-10 lg:p-12">
                <div class="space-y-6">
                    <div class="inline-flex w-fit items-center gap-3 rounded-full border border-emerald-400/20 bg-emerald-400/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.28em] text-emerald-200">
                        <span class="h-2 w-2 rounded-full bg-emerald-300 shadow-[0_0_18px_rgba(110,231,183,0.95)]"></span>
                        Platform Access
                    </div>

                    <div class="space-y-4">
                        <h1 class="max-w-2xl text-4xl font-semibold leading-tight text-white sm:text-5xl">
                            Login admin platform yang lebih jelas, aman, dan siap dipakai operasional.
                        </h1>
                        <p class="max-w-2xl text-base leading-7 text-slate-300 sm:text-lg">
                            Gunakan panel pusat ini untuk memantau onboarding tenant, provisioning database, paket langganan, dan status operasional tanpa tercampur dengan aplikasi tenant POS.
                        </p>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    <article class="rounded-3xl border border-white/10 bg-white/5 p-4">
                        <p class="text-sm font-semibold text-white">Akses Terpisah</p>
                        <p class="mt-2 text-sm leading-6 text-slate-300">Akun tenant dan akun admin platform dipisahkan agar kontrol provisioning tetap aman.</p>
                    </article>
                    <article class="rounded-3xl border border-white/10 bg-white/5 p-4">
                        <p class="text-sm font-semibold text-white">Monitoring Cepat</p>
                        <p class="mt-2 text-sm leading-6 text-slate-300">Masuk ke panel untuk cek trial, domain, inbox platform, dan log provisioning terbaru.</p>
                    </article>
                    <article class="rounded-3xl border border-white/10 bg-white/5 p-4">
                        <p class="text-sm font-semibold text-white">Ramah Operasional</p>
                        <p class="mt-2 text-sm leading-6 text-slate-300">State tombol, error form, dan feedback proses dibuat lebih tegas agar minim salah klik.</p>
                    </article>
                </div>
            </section>

            <section class="border-t border-white/10 bg-slate-950/70 p-5 sm:p-7 md:border-l md:border-t-0 lg:p-10">
                <div class="mx-auto max-w-md">
                    <div class="mb-6 space-y-2">
                        <p class="text-sm font-medium uppercase tracking-[0.2em] text-sky-200/80">Secure Sign In</p>
                        <h2 class="text-3xl font-semibold text-white">Masuk ke Admin Platform</h2>
                        <p class="text-sm leading-6 text-slate-400">Hanya akun yang memiliki akses admin platform yang bisa melanjutkan ke dashboard pusat.</p>
                    </div>

                    @if ($errors->any())
                        <div class="mb-5 rounded-2xl border border-rose-400/25 bg-rose-400/10 px-4 py-3 text-sm text-rose-100 shadow-[0_20px_40px_rgba(251,113,133,0.08)]">
                            <p class="font-semibold">Login belum berhasil.</p>
                            <p class="mt-1 text-rose-100/80">{{ $errors->first() }}</p>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('platform.login.store') }}" class="space-y-5" data-platform-login-form>
                        @csrf

                        <div class="space-y-2">
                            <label for="email" class="block text-sm font-medium text-slate-200">Email Admin</label>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus
                                class="w-full rounded-2xl border border-slate-700/80 bg-slate-900/80 px-4 py-3.5 text-sm text-slate-100 shadow-inner outline-none transition duration-200 placeholder:text-slate-500 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-400/15"
                                placeholder="nama@platformanda.com">
                            @error('email')
                                <p class="text-sm text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <div class="flex items-center justify-between gap-3">
                                <label for="password" class="block text-sm font-medium text-slate-200">Password</label>
                                <span id="caps-lock-hint" class="hidden text-xs font-medium text-amber-200">Caps Lock aktif</span>
                            </div>
                            <div class="relative">
                                <input id="password" name="password" type="password" autocomplete="current-password" required
                                    class="w-full rounded-2xl border border-slate-700/80 bg-slate-900/80 px-4 py-3.5 pr-14 text-sm text-slate-100 shadow-inner outline-none transition duration-200 placeholder:text-slate-500 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-400/15"
                                    placeholder="Masukkan password admin">
                                <button type="button" id="toggle-password" class="absolute inset-y-1.5 right-1.5 inline-flex items-center justify-center rounded-xl border border-transparent px-3 text-xs font-semibold text-slate-300 transition hover:border-white/10 hover:bg-white/5 hover:text-white focus:outline-none focus:ring-2 focus:ring-emerald-400/40">
                                    Lihat
                                </button>
                            </div>
                        </div>

                        <div class="flex flex-col gap-3 rounded-2xl border border-white/10 bg-white/5 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                            <label class="flex items-center gap-3 text-sm text-slate-300">
                                <input type="checkbox" name="remember" value="1" class="rounded border-slate-600 bg-slate-900 text-emerald-400 focus:ring-emerald-400">
                                Ingat sesi saya di browser ini
                            </label>
                            <p class="text-xs leading-5 text-slate-400">Gunakan hanya di perangkat kerja yang Anda percayai.</p>
                        </div>

                        <button type="submit" class="group inline-flex w-full items-center justify-center gap-3 rounded-2xl bg-emerald-400 px-4 py-3.5 text-sm font-semibold text-slate-950 shadow-[0_18px_40px_rgba(16,185,129,0.3)] transition duration-200 hover:-translate-y-0.5 hover:bg-emerald-300 focus:outline-none focus:ring-4 focus:ring-emerald-300/30 disabled:translate-y-0 disabled:cursor-not-allowed disabled:bg-emerald-500/60 disabled:text-slate-900/70 disabled:shadow-none" data-submit-button>
                            <svg class="hidden h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" data-submit-spinner aria-hidden="true">
                                <circle cx="12" cy="12" r="9" class="opacity-25" stroke="currentColor" stroke-width="3"></circle>
                                <path d="M21 12a9 9 0 0 0-9-9" class="opacity-90" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path>
                            </svg>
                            <span data-submit-label>Masuk ke Admin Platform</span>
                        </button>

                        <p class="text-center text-xs leading-5 text-slate-500">
                            Setelah login, Anda akan diarahkan ke dashboard admin tenant platform.
                        </p>
                    </form>
                </div>
            </section>
        </div>
    </div>

    <script>
        (() => {
            const form = document.querySelector('[data-platform-login-form]');
            const passwordInput = document.getElementById('password');
            const togglePasswordButton = document.getElementById('toggle-password');
            const capsLockHint = document.getElementById('caps-lock-hint');

            if (togglePasswordButton && passwordInput) {
                togglePasswordButton.addEventListener('click', () => {
                    const willShowPassword = passwordInput.type === 'password';
                    passwordInput.type = willShowPassword ? 'text' : 'password';
                    togglePasswordButton.textContent = willShowPassword ? 'Sembunyikan' : 'Lihat';
                    togglePasswordButton.setAttribute('aria-pressed', willShowPassword ? 'true' : 'false');
                });

                const updateCapsLockHint = (event) => {
                    if (!capsLockHint || typeof event.getModifierState !== 'function') {
                        return;
                    }

                    capsLockHint.classList.toggle('hidden', !event.getModifierState('CapsLock'));
                };

                passwordInput.addEventListener('keydown', updateCapsLockHint);
                passwordInput.addEventListener('keyup', updateCapsLockHint);
                passwordInput.addEventListener('blur', () => capsLockHint?.classList.add('hidden'));
            }

            if (form) {
                form.addEventListener('submit', () => {
                    const submitButton = form.querySelector('[data-submit-button]');
                    const submitLabel = form.querySelector('[data-submit-label]');
                    const submitSpinner = form.querySelector('[data-submit-spinner]');

                    if (!submitButton || !submitLabel || !submitSpinner) {
                        return;
                    }

                    submitButton.disabled = true;
                    submitButton.setAttribute('aria-busy', 'true');
                    submitSpinner.classList.remove('hidden');
                    submitLabel.textContent = 'Memproses login...';
                });
            }
        })();
    </script>
</body>
</html>
