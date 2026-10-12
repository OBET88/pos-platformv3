<!doctype html>
<html class="no-js" lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>POS|Verifikasi Email Pendaftaran</title>
    @livewireStyles
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('ecohosting-main/assets/img/favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('ecohosting-main/assets/css/bootstrap.min.css') }}">
    <style>
        body { margin: 0; font-family: 'Segoe UI', Arial, sans-serif; background: linear-gradient(135deg, rgba(39, 15, 47, 0.88), rgba(22, 10, 40, 0.76)); }
        .shell { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 18px; }
        .card { width: min(760px, 100%); background: rgba(255,255,255,.97); border-radius: 24px; box-shadow: 0 22px 60px rgba(19,8,30,.24); overflow: hidden; }
        .hero { padding: 28px 28px 20px; background: linear-gradient(180deg, rgba(86,33,120,.92), rgba(47,16,66,.98)); color: #fff; }
        .hero h1 { margin: 12px 0 10px; font-size: 30px; line-height: 1.15; }
        .hero p { margin: 0; color: rgba(255,255,255,.82); line-height: 1.6; font-size: 14px; }
        .body { padding: 22px 24px 24px; color: #554b66; }
        .note { margin-bottom: 14px; padding: 12px 14px; border-radius: 14px; background: rgba(122,61,240,.06); border: 1px solid rgba(122,61,240,.14); }
        .note strong { color: #442178; }
        .alert-box { margin-bottom: 14px; padding: 12px 14px; border-radius: 14px; font-size: 14px; line-height: 1.6; }
        .alert-box.success { background: rgba(34,197,94,.09); border: 1px solid rgba(34,197,94,.2); color: #166534; }
        .alert-box.warning { background: rgba(245,158,11,.1); border: 1px solid rgba(245,158,11,.22); color: #92400e; }
        .cooldown-note { margin-top: 10px; font-size: 13px; color: #7c6f91; }
        .actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 18px; }
        .actions form { margin: 0; }
        .cta { display: inline-flex; align-items: center; justify-content: center; min-width: 200px; height: 46px; padding: 0 16px; border-radius: 999px; text-decoration: none; font-weight: 700; transition: transform .18s ease, box-shadow .18s ease, background-color .18s ease, color .18s ease; }
        button.cta { border: 0; cursor: pointer; }
        .cta:hover { transform: translateY(-1px); }
        .cta.primary { color: #fff; background: linear-gradient(135deg,#8b62f3,#ec63ef); box-shadow: 0 18px 34px rgba(122,61,240,.2); }
        .cta.secondary { color: #5d36b0; background: rgba(122,61,240,.08); }
        .cta[disabled] { opacity: .65; cursor: not-allowed; transform: none; box-shadow: none; }
    </style>
</head>
<body>
    <main class="shell">
        @livewire('platform.public.pending-verification')
    </main>
    @livewireScripts
</body>
</html>
