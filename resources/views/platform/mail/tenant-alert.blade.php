<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Alert Tenant Platform</title>
</head>
<body style="margin:0;padding:24px;background:#f3f7fb;font-family:'Segoe UI',Arial,sans-serif;color:#0f172a;">
    <div style="max-width:680px;margin:0 auto;background:#ffffff;border:1px solid #dbe5f0;border-radius:24px;overflow:hidden;">
        <div style="padding:28px 32px;background:linear-gradient(135deg,#0f172a 0%,#1d4ed8 100%);color:#ffffff;">
            <div style="font-size:12px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;opacity:.84;">POS Platform Alert</div>
            <h1 style="margin:14px 0 0;font-size:28px;line-height:1.15;">{{ $alertTitle }}</h1>
        </div>

        <div style="padding:32px;">
            <p style="margin:0 0 16px;font-size:16px;line-height:1.8;">
                Tenant <strong>{{ $tenant->name }}</strong> membutuhkan perhatian atau memiliki update operasional terbaru.
            </p>

            <div style="margin:22px 0;padding:18px 20px;border-radius:18px;background:#f8fafc;border:1px solid #dbe5f0;">
                <div style="font-size:13px;color:#4d6281;margin-bottom:6px;">Ringkasan</div>
                <div style="font-size:16px;line-height:1.8;">{{ $summary }}</div>
            </div>

            <div style="margin:22px 0;padding:18px 20px;border-radius:18px;background:#eef4ff;border:1px solid #d4e3ff;">
                <div style="font-size:13px;color:#4d6281;margin-bottom:6px;">Tenant</div>
                <div style="font-size:20px;font-weight:800;letter-spacing:-.03em;">{{ $tenant->name }}</div>
                <div style="margin-top:6px;font-size:14px;color:#4d6281;">{{ $tenant->code }} • {{ $tenant->owner_email }}</div>
            </div>

            <p style="margin:0 0 18px;">
                <a href="{{ $adminUrl }}" style="display:inline-block;padding:14px 20px;border-radius:999px;background:#1367ff;color:#ffffff;text-decoration:none;font-weight:700;">Buka Tenant di Admin Console</a>
            </p>

            <p style="margin:24px 0 0;font-size:15px;line-height:1.8;color:#5e6b82;">
                Email ini dikirim otomatis oleh scheduler POS Platform untuk membantu monitoring tenant yang perlu perhatian.
            </p>
        </div>
    </div>
</body>
</html>
