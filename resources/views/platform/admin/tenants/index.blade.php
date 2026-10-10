<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Platform Admin - Tenants</title>
    <style>
        :root {
            --ink: #1d1a17;
            --muted: #6e6259;
            --line: #ddd0c3;
            --panel: rgba(255,255,255,.95);
            --accent: #855127;
            --success: #2f7a52;
            --warning: #b97716;
            --danger: #b43f3f;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Georgia, "Times New Roman", serif;
            color: var(--ink);
            background: linear-gradient(135deg, #f7f0e7 0%, #ece1d5 100%);
        }
        .shell { max-width: 1180px; margin: 0 auto; padding: 32px 20px 56px; }
        .topbar {
            display: flex; justify-content: space-between; align-items: center; gap: 16px; margin-bottom: 24px;
        }
        .topbar a { text-decoration: none; color: var(--accent); font-weight: 700; }
        h1 { margin: 0; font-size: 42px; }
        .lead { margin: 8px 0 0; color: var(--muted); }
        .stats {
            display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; margin-bottom: 24px;
        }
        .card, .table-wrap {
            background: var(--panel); border: 1px solid var(--line); border-radius: 20px; box-shadow: 0 18px 54px rgba(60,40,20,.06);
        }
        .card { padding: 18px; }
        .label { color: var(--muted); font-size: 13px; text-transform: uppercase; letter-spacing: .06em; }
        .value { margin-top: 8px; font-size: 30px; font-weight: 700; }
        .table-wrap { padding: 18px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 14px 12px; border-bottom: 1px solid #eadfd4; text-align: left; vertical-align: top; }
        th { font-size: 13px; text-transform: uppercase; letter-spacing: .06em; color: var(--muted); }
        tr:last-child td { border-bottom: 0; }
        .badge {
            display: inline-block; padding: 6px 10px; border-radius: 999px; font-size: 12px; font-weight: 700;
            background: #f3e5d6; color: #6c4521;
        }
        .badge.success { background: #e4f5eb; color: var(--success); }
        .badge.warning { background: #fff1db; color: var(--warning); }
        .badge.danger { background: #ffe4e4; color: var(--danger); }
        .tenant-name { font-weight: 700; font-size: 17px; }
        .tenant-meta { color: var(--muted); font-size: 14px; margin-top: 6px; }
        .action {
            display: inline-block; text-decoration: none; color: white; background: linear-gradient(135deg, #855127 0%, #6b401d 100%);
            padding: 10px 14px; border-radius: 12px; font-weight: 700;
        }
        .pagination { margin-top: 18px; }
        @media (max-width: 900px) { .stats { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 640px) { .stats { grid-template-columns: 1fr; } h1 { font-size: 34px; } }
    </style>
</head>
<body>
    <div class="shell">
        @php($statusLabel = fn (string $status) => match ($status) {
            'pending_setup' => 'Perlu Recovery',
            'trial' => 'Trial Berjalan',
            'active' => 'Aktif',
            'suspended' => 'Ditangguhkan',
            'expired' => 'Berakhir',
            default => str($status)->replace('_', ' ')->title()->toString(),
        })
        <div class="topbar">
            <div>
                <h1>Tenant Dashboard</h1>
                <p class="lead">Pantau tenant yang mendaftar, status subscription, dan kebutuhan monitoring atau recovery.</p>
            </div>
            <a href="{{ route('platform.register.create') }}">Buka Form Daftar</a>
        </div>

        <section class="stats">
            <div class="card"><div class="label">Total Tenant</div><div class="value">{{ $stats['total_tenants'] }}</div></div>
            <div class="card"><div class="label">Perlu Recovery</div><div class="value">{{ $stats['pending_setup'] }}</div></div>
            <div class="card"><div class="label">Trial Berjalan</div><div class="value">{{ $stats['trial'] }}</div></div>
            <div class="card"><div class="label">Aktif</div><div class="value">{{ $stats['active'] }}</div></div>
        </section>

        <section class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Tenant</th>
                        <th>Owner</th>
                        <th>Plan</th>
                        <th>Status</th>
                        <th>Trial Ends</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tenants as $tenant)
                        @php
                            $subscription = $tenant->subscriptions->sortByDesc('id')->first();
                            $statusClass = match ($tenant->status) {
                                'active' => 'success',
                                'suspended', 'expired' => 'danger',
                                default => 'warning',
                            };
                        @endphp
                        <tr>
                            <td>
                                <div class="tenant-name">{{ $tenant->name }}</div>
                                <div class="tenant-meta">Code: {{ $tenant->code }}</div>
                            </td>
                            <td>
                                <div>{{ $tenant->owner_name }}</div>
                                <div class="tenant-meta">{{ $tenant->owner_email }}</div>
                            </td>
                            <td>{{ $subscription?->plan?->name ?? '-' }}</td>
                            <td><span class="badge {{ $statusClass }}">{{ $statusLabel($tenant->status) }}</span></td>
                            <td>{{ optional($tenant->trial_ends_at)->format('d/m/Y H:i') ?? '-' }}</td>
                            <td><a class="action" href="{{ route('platform.admin.tenants.show', $tenant) }}">Lihat Detail</a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">Belum ada tenant yang mendaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="pagination">{{ $tenants->links() }}</div>
        </section>
    </div>
</body>
</html>
