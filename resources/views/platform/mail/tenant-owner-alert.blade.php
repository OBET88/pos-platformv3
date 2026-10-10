<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Info Subscription Tenant</title>
</head>
<body style="margin:0;padding:24px;background:#f8fafc;font-family:'Segoe UI',Arial,sans-serif;color:#0f172a;">
    <div style="max-width:680px;margin:0 auto;background:#ffffff;border:1px solid #dbe5f0;border-radius:24px;overflow:hidden;">
        <div style="padding:28px 32px;background:linear-gradient(135deg,#1367ff 0%,#0f172a 100%);color:#ffffff;">
            <div style="font-size:12px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;opacity:.84;">POS Platform</div>
            <h1 style="margin:14px 0 0;font-size:28px;line-height:1.15;">{{ $alertTitle }}</h1>
        </div>

        <div style="padding:32px;">
            <p style="margin:0 0 16px;font-size:16px;line-height:1.8;">
                Halo {{ $tenant->owner_name }},
            </p>

            <p style="margin:0 0 16px;font-size:16px;line-height:1.8;">
                Kami ingin memberi tahu update penting terkait tenant <strong>{{ $tenant->name }}</strong>.
            </p>

            <div style="margin:22px 0;padding:18px 20px;border-radius:18px;background:#eef4ff;border:1px solid #d4e3ff;">
                <div style="font-size:13px;color:#4d6281;margin-bottom:6px;">Ringkasan</div>
                <div style="font-size:16px;line-height:1.8;">{{ $summary }}</div>
            </div>

            @if ($loginUrl)
                <p style="margin:0 0 18px;">
                    <a href="{{ $loginUrl }}" style="display:inline-block;padding:14px 20px;border-radius:999px;background:#1367ff;color:#ffffff;text-decoration:none;font-weight:700;">Buka Login Tenant</a>
                </p>
            @endif

            @if ($renewalUrl)
                <p style="margin:0 0 18px;">
                    <a href="{{ $renewalUrl }}" style="display:inline-block;padding:14px 20px;border-radius:999px;background:#0f766e;color:#ffffff;text-decoration:none;font-weight:700;">Perpanjang Langganan Sekarang</a>
                </p>
                <p style="margin:0 0 18px;font-size:15px;line-height:1.8;color:#5e6b82;">
                    Jika masa aktif tenant akan segera berakhir atau sudah berakhir, gunakan tombol di atas untuk langsung membuat invoice renewal dan melanjutkan pembayaran dari halaman publik.
                </p>
            @endif

            <p style="margin:24px 0 0;font-size:15px;line-height:1.8;color:#5e6b82;">
                Jika Anda membutuhkan bantuan terkait perpanjangan atau aktivasi tenant, silakan hubungi tim platform Anda.
            </p>
        </div>
    </div>
</body>
</html>
