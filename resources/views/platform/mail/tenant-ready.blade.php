<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>POS Platform Tenant Ready</title>
</head>
<body style="margin:0;padding:20px;background:linear-gradient(135deg,#261131 0%,#140b28 100%);font-family:'Segoe UI',Arial,sans-serif;color:#22152b;">
    <div style="max-width:760px;margin:0 auto;background:rgba(255,255,255,.96);border-radius:22px;overflow:hidden;box-shadow:0 18px 48px rgba(19,8,30,.22);">
        <div style="padding:26px 26px 22px;background:linear-gradient(180deg,rgba(86,33,120,.92) 0%,rgba(47,16,66,.98) 100%);color:#ffffff;">
            <div style="font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;opacity:.84;">POS Platform</div>
            <h1 style="margin:14px 0 10px;font-size:28px;line-height:1.12;">Tenant Anda sudah siap login.</h1>
            <p style="margin:0;font-size:13px;line-height:1.6;color:rgba(255,255,255,.82);">
                Seluruh proses onboarding inti sudah selesai dan tenant bisa mulai dipakai.
            </p>
        </div>

        <div style="padding:22px 22px 20px;">
            <p style="margin:0 0 14px;font-size:15px;line-height:1.75;">Halo {{ $ownerName }},</p>
            <p style="margin:0 0 14px;font-size:14px;line-height:1.7;color:#5f5670;">
                Tenant untuk <strong style="color:#442178;">{{ $companyName }}</strong> sudah selesai dipersiapkan.
                Anda sekarang bisa masuk ke aplikasi tenant menggunakan data berikut.
            </p>

            <div style="margin:18px 0;padding:12px 14px;border-radius:14px;background:rgba(122,61,240,.06);border:1px solid rgba(122,61,240,.14);">
                <div style="font-size:12px;color:#6d6390;margin-bottom:8px;">Ringkasan akses</div>
                <div style="font-size:13px;line-height:1.8;color:#442178;">
                    <strong>Kode tenant:</strong> {{ $tenantCode }}<br>
                    <strong>Username:</strong> {{ $username }}<br>
                    @if ($businessName)
                        <strong>Usaha awal:</strong> {{ $businessName }}<br>
                    @endif
                    @if ($branchName)
                        <strong>Cabang awal:</strong> {{ $branchName }}<br>
                    @endif
                    <strong>Password:</strong> gunakan password yang Anda buat saat pendaftaran.
                </div>
            </div>

            @if ($loginUrl)
                <p style="margin:0 0 14px;">
                    <a href="{{ $loginUrl }}" style="display:inline-block;min-width:200px;padding:13px 18px;border-radius:999px;background:linear-gradient(135deg,#8b62f3,#ec63ef);color:#ffffff;text-decoration:none;font-weight:700;box-shadow:0 12px 26px rgba(156,91,240,.22);">
                        Buka Login Tenant
                    </a>
                </p>

                <div style="margin:0 0 14px;padding:12px 14px;border-radius:14px;background:rgba(54,167,102,.1);border:1px solid rgba(54,167,102,.14);">
                    <div style="margin:0 0 6px;font-size:13px;font-weight:700;color:#245f40;">URL login tenant</div>
                    <div style="font-size:13px;line-height:1.6;word-break:break-all;">
                        <a href="{{ $loginUrl }}" style="color:#245f40;text-decoration:none;font-weight:700;">{{ $loginUrl }}</a>
                    </div>
                </div>
            @endif

            @if ($forgotPasswordUrl)
                <div style="margin:0 0 14px;padding:12px 14px;border-radius:14px;background:#fbf9fe;border:1px solid #eadff4;">
                    <div style="margin:0 0 6px;font-size:13px;font-weight:700;color:#442178;">Lupa password?</div>
                    <div style="font-size:13px;line-height:1.7;color:#5f5670;">
                        Jika password yang dibuat saat pendaftaran lupa, Anda bisa reset via email dari link ini:
                        <a href="{{ $forgotPasswordUrl }}" style="color:#442178;text-decoration:none;font-weight:700;">Reset Password Tenant</a>
                    </div>
                </div>
            @endif

            <div style="margin:0 0 14px;padding:12px 14px;border-radius:14px;background:#fff7e8;border:1px solid rgba(235,169,71,.2);font-size:13px;line-height:1.7;color:#7b5a1f;">
                Demi keamanan, password asli tidak kami tulis ulang di email. Gunakan password yang sudah Anda buat saat pendaftaran.
            </div>

            <div style="margin:18px 0 0;padding-top:14px;border-top:1px solid #ebe4f3;font-size:12px;line-height:1.7;color:#7b738b;">
                Email ini dikirim otomatis oleh POS Platform saat tenant selesai dibootstrap dan siap dipakai.
            </div>
        </div>
    </div>
</body>
</html>
