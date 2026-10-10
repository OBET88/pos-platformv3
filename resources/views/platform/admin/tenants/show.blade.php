<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tenant Detail</title>
    <style>
        :root {
            --ink: #1d1a17;
            --muted: #6e6259;
            --line: #ddd0c3;
            --panel: rgba(255,255,255,.95);
            --accent: #855127;
        }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Georgia, "Times New Roman", serif; color: var(--ink); background: linear-gradient(135deg, #f7f0e7 0%, #ece1d5 100%); }
        .shell { max-width: 1100px; margin: 0 auto; padding: 32px 20px 56px; }
        .top { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; margin-bottom: 24px; }
        .top a { text-decoration: none; color: var(--accent); font-weight: 700; }
        h1 { margin: 0 0 8px; font-size: 42px; }
        .meta { color: var(--muted); }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
        .panel { background: var(--panel); border: 1px solid var(--line); border-radius: 20px; padding: 20px; box-shadow: 0 18px 54px rgba(60,40,20,.06); }
        .panel h2 { margin: 0 0 12px; font-size: 24px; }
        .row { padding: 10px 0; border-bottom: 1px solid #eadfd4; }
        .row:last-child { border-bottom: 0; }
        .label { color: var(--muted); font-size: 13px; text-transform: uppercase; letter-spacing: .05em; }
        .value { margin-top: 4px; font-size: 16px; }
        .log { padding: 12px 14px; border: 1px solid #eadfd4; border-radius: 14px; margin-bottom: 10px; background: #fffdfa; }
        .log strong { display: block; margin-bottom: 6px; }
        @media (max-width: 900px) { .grid { grid-template-columns: 1fr; } h1 { font-size: 34px; } }
    </style>
</head>
<body>
    <div class="shell">
        @php($statusLabel = fn (?string $status) => match ($status) {
            'pending_setup' => 'Perlu Recovery',
            'trial' => 'Trial Berjalan',
            'active' => 'Aktif',
            'suspended' => 'Ditangguhkan',
            'expired' => 'Berakhir',
            null, '' => '-',
            default => str($status)->replace('_', ' ')->title()->toString(),
        })
        <div class="top">
            <div>
                <h1>{{ $tenant->name }}</h1>
                <div class="meta">Code: {{ $tenant->code }} | Status: {{ $statusLabel($tenant->status) }}</div>
            </div>
            <a href="{{ route('platform.admin.tenants.index') }}">Kembali ke daftar tenant</a>
        </div>

        <div class="grid">
            <section class="panel">
                <h2>Profil Tenant</h2>
                <div class="row"><div class="label">Owner</div><div class="value">{{ $tenant->owner_name }}</div></div>
                <div class="row"><div class="label">Email</div><div class="value">{{ $tenant->owner_email }}</div></div>
                <div class="row"><div class="label">Phone</div><div class="value">{{ $tenant->owner_phone ?: '-' }}</div></div>
                <div class="row"><div class="label">Trial Ends</div><div class="value">{{ optional($tenant->trial_ends_at)->format('d/m/Y H:i') ?: '-' }}</div></div>
                <div class="row"><div class="label">Notes</div><div class="value">{{ $tenant->notes ?: '-' }}</div></div>
            </section>

            <section class="panel">
                <h2>Subscription & Domain</h2>
                <div class="row"><div class="label">Plan</div><div class="value">{{ $latestSubscription?->plan?->name ?? '-' }}</div></div>
                <div class="row"><div class="label">Subscription Status</div><div class="value">{{ $statusLabel($latestSubscription?->status) }}</div></div>
                <div class="row"><div class="label">Expired At</div><div class="value">{{ optional($latestSubscription?->expired_at)->format('d/m/Y H:i') ?: '-' }}</div></div>
                <div class="row"><div class="label">Primary Domain</div><div class="value">{{ optional($tenant->domains->firstWhere('is_primary', true))->domain ?: '-' }}</div></div>
                <div class="row"><div class="label">DB Name</div><div class="value">{{ $tenant->databaseConfigs->first()?->db_name ?: '-' }}</div></div>
                <div class="row"><div class="label">DB Status</div><div class="value">{{ $tenant->databaseConfigs->first()?->status ?: '-' }}</div></div>
            </section>
        </div>

        <section class="panel" style="margin-top: 18px;">
            <h2>Log Monitoring & Recovery</h2>
            @forelse ($tenant->provisionLogs as $log)
                <div class="log">
                    <strong>{{ str($log->step)->replace('_', ' ')->title() }} | {{ $statusLabel($log->status) }}</strong>
                    <div>{{ $log->message ?: '-' }}</div>
                    <div class="meta" style="margin-top:6px;">{{ optional($log->created_at)->format('d/m/Y H:i:s') }}</div>
                </div>
            @empty
                <div class="meta">Belum ada log monitoring atau recovery.</div>
            @endforelse
        </section>
    </div>
</body>
</html>
