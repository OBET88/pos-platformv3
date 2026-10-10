<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>POS Platform Registration Received</title>
</head>
<body style="margin:0;padding:20px;background:linear-gradient(135deg,#261131 0%,#140b28 100%);font-family:'Segoe UI',Arial,sans-serif;color:#22152b;">
    <div style="max-width:760px;margin:0 auto;background:rgba(255,255,255,.96);border-radius:22px;overflow:hidden;box-shadow:0 18px 48px rgba(19,8,30,.22);">
        <div style="padding:26px 26px 22px;background:linear-gradient(180deg,rgba(86,33,120,.92) 0%,rgba(47,16,66,.98) 100%);color:#ffffff;">
            <div style="font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;opacity:.84;">POS Platform</div>
            <h1 style="margin:14px 0 10px;font-size:28px;line-height:1.12;">Tenant sudah tercatat dan onboarding sedang berjalan.</h1>
            <p style="margin:0;font-size:13px;line-height:1.6;color:rgba(255,255,255,.82);">
                Email ini dibuat dengan tone yang sama seperti halaman register baru: ringkas, jelas, dan fokus ke langkah berikutnya.
            </p>
        </div>

        <div style="padding:22px 22px 20px;">
            <p style="margin:0 0 14px;font-size:15px;line-height:1.75;">Halo {{ $ownerName }},</p>
            <p style="margin:0 0 14px;font-size:14px;line-height:1.7;color:#5f5670;">
                Kami sudah menerima pendaftaran untuk <strong style="color:#442178;">{{ $companyName }}</strong>.
                Sistem sedang melanjutkan proses onboarding tenant, termasuk penyiapan owner awal dan langkah teknis yang dibutuhkan sampai tenant siap dipakai.
            </p>

            <div style="margin:18px 0;padding:12px 14px;border-radius:14px;background:rgba(122,61,240,.06);border:1px solid rgba(122,61,240,.14);">
                <div style="font-size:12px;color:#6d6390;margin-bottom:5px;">Kode tenant</div>
                <div style="font-size:20px;font-weight:800;letter-spacing:-.03em;color:#442178;">{{ $tenantCode }}</div>
            </div>

            <div style="margin:0 0 14px;padding:12px 14px;border-radius:14px;background:#fbf9fe;border:1px solid #eadff4;">
                <div style="margin:0 0 8px;font-size:13px;font-weight:700;color:#442178;">Progress tenant bisa dipantau dari sini</div>
                <div style="font-size:13px;line-height:1.6;color:#5f5670;">
                    Gunakan halaman status untuk memantau provisioning tenant, retry bila perlu, dan lanjut ke login tenant saat onboarding selesai.
                </div>
            </div>

            <p style="margin:0 0 14px;">
                <a href="{{ $statusUrl }}" style="display:inline-block;min-width:200px;padding:13px 18px;border-radius:999px;background:linear-gradient(135deg,#8b62f3,#ec63ef);color:#ffffff;text-decoration:none;font-weight:700;box-shadow:0 12px 26px rgba(156,91,240,.22);">
                    Buka Progress Tenant
                </a>
            </p>

            @if ($loginUrl)
                <div style="margin:0 0 14px;padding:12px 14px;border-radius:14px;background:rgba(54,167,102,.1);border:1px solid rgba(54,167,102,.14);">
                    <div style="margin:0 0 6px;font-size:13px;font-weight:700;color:#245f40;">Login tenant akan tersedia di URL ini</div>
                    <div style="font-size:13px;line-height:1.6;word-break:break-all;">
                        <a href="{{ $loginUrl }}" style="color:#245f40;text-decoration:none;font-weight:700;">{{ $loginUrl }}</a>
                    </div>
                </div>
            @endif

            <p style="margin:0 0 14px;font-size:14px;line-height:1.7;color:#5f5670;">
                Jika browser sempat ditutup, tidak masalah. Halaman progress tenant akan membantu Anda melanjutkan dari status terakhir.
            </p>

            <div style="margin:18px 0 0;padding-top:14px;border-top:1px solid #ebe4f3;font-size:12px;line-height:1.7;color:#7b738b;">
                Email ini dikirim otomatis oleh POS Platform sebagai konfirmasi awal pendaftaran tenant.
            </div>
        </div>
    </div>
</body>
</html>
