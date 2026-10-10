<?php

use App\Mail\PlatformTenantAlert;
use App\Mail\PlatformTenantOwnerAlert;
use App\Models\Platform\InboxItem;
use App\Models\Platform\PendingRegistration;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;
use App\Models\Platform\TenantAudit;
use App\Services\Platform\PlatformInboxPublisher;
use App\Services\Platform\TenantAuditLogger;
use App\Services\Platform\TenantLifecycleSyncService;
use App\Models\Platform\TenantProvisionLog;
use App\Models\User;
use App\Services\Platform\TenantAppBootstrapService;
use App\Services\Platform\TenantRegistrationService;
use App\Services\Platform\TenantProvisioningService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Schema;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('platform:create-admin {name} {email} {password}', function () {
    $user = User::query()->updateOrCreate(
        ['email' => (string) $this->argument('email')],
        [
            'name' => (string) $this->argument('name'),
            'password' => Hash::make((string) $this->argument('password')),
            'is_platform_admin' => true,
        ]
    );

    $this->info('Platform admin siap: '.$user->email);
})->purpose('Create or update a platform admin user');

Artisan::command('platform:provision-tenant {tenant}', function (TenantProvisioningService $provisioningService) {
    $tenant = Tenant::query()->findOrFail((int) $this->argument('tenant'));
    $tenant = $provisioningService->provision($tenant);

    $this->info('Tenant siap diprovision: '.$tenant->name.' ['.$tenant->status.']');
})->purpose('Provision a tenant database and mark the tenant ready');

Artisan::command('platform:bootstrap-tenant-app {tenant} {owner_name} {owner_email} {owner_password} {--business=Usaha Utama} {--branch=Cabang 1} {--owner-role=owner}', function (TenantAppBootstrapService $bootstrapService) {
    $tenant = Tenant::query()->findOrFail((int) $this->argument('tenant'));

    $tenant = $bootstrapService->bootstrap($tenant, [
        'owner_name' => (string) $this->argument('owner_name'),
        'owner_email' => (string) $this->argument('owner_email'),
        'owner_password' => (string) $this->argument('owner_password'),
        'business_name' => (string) $this->option('business'),
        'branch_name' => (string) $this->option('branch'),
        'owner_role' => (string) $this->option('owner-role'),
    ]);

    $this->info('Tenant app berhasil dibootstrap: '.$tenant->name);
})->purpose('Bootstrap tenant app schema and first owner user from the platform app');

Artisan::command('platform:complete-tenant-registration {tenant} {owner_name} {owner_email} {owner_password} {--business=Usaha Utama} {--branch=Cabang 1} {--owner-role=owner}', function (
    TenantProvisioningService $provisioningService,
    TenantAppBootstrapService $bootstrapService
) {
    $tenantId = (int) $this->argument('tenant');
    $payload = [
        'owner_name' => (string) $this->argument('owner_name'),
        'owner_email' => (string) $this->argument('owner_email'),
        'owner_password' => (string) $this->argument('owner_password'),
        'business_name' => (string) $this->option('business'),
        'branch_name' => (string) $this->option('branch'),
        'owner_role' => (string) $this->option('owner-role'),
    ];
    $attemptDelaysInMicroseconds = [
        1000000,
        2000000,
        4000000,
    ];
    $lastException = null;
    $platformConnectionNames = array_values(array_unique(array_filter([
        config('database.default'),
        'mysql',
    ])));
    $reconnectPlatform = function () use ($platformConnectionNames): void {
        foreach ($platformConnectionNames as $connectionName) {
            DB::purge($connectionName);
            DB::disconnect($connectionName);
        }
    };
    $waitForPlatformConnection = function () use ($platformConnectionNames, $reconnectPlatform): void {
        $lastException = null;

        for ($attempt = 1; $attempt <= 6; $attempt++) {
            try {
                $reconnectPlatform();

                foreach ($platformConnectionNames as $connectionName) {
                    DB::connection($connectionName)->select('SELECT 1');
                }

                return;
            } catch (Throwable $exception) {
                $lastException = $exception;

                if ($attempt < 6) {
                    usleep($attempt * 1200000);
                }
            }
        }

        throw $lastException ?? new RuntimeException('Koneksi database platform belum stabil.');
    };

    for ($attempt = 1; $attempt <= count($attemptDelaysInMicroseconds) + 1; $attempt++) {
        try {
            $waitForPlatformConnection();
            $tenant = Tenant::query()->findOrFail($tenantId);
            $tenant = $provisioningService->provision($tenant);
            $tenant = $bootstrapService->bootstrap($tenant, $payload);

            TenantProvisionLog::query()->updateOrCreate([
                'tenant_id' => $tenant->getKey(),
                'step' => 'auto_onboarding_completed',
            ], [
                'status' => 'completed',
                'message' => 'Auto provisioning tenant selesai. Database tenant, schema tenant app, dan owner awal sudah siap.',
                'payload' => [
                    'owner_email' => $payload['owner_email'],
                    'business_name' => $payload['business_name'],
                    'branch_name' => $payload['branch_name'],
                ],
            ]);

            $this->info('Auto onboarding tenant selesai: '.$tenant->name);

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $lastException = $exception;

            if ($this->output->isVerbose()) {
                $this->warn('Percobaan auto onboarding tenant gagal pada attempt '.$attempt.': '.$exception->getMessage());
            }

            if ($attempt <= count($attemptDelaysInMicroseconds)) {
                $reconnectPlatform();
                usleep($attemptDelaysInMicroseconds[$attempt - 1]);
            }
        }
    }

    $waitForPlatformConnection();
    $tenant = Tenant::query()->findOrFail($tenantId);
    TenantProvisionLog::query()->updateOrCreate([
        'tenant_id' => $tenant->getKey(),
        'step' => 'auto_onboarding_completed',
    ], [
        'status' => 'failed',
        'message' => 'Auto provisioning tenant gagal dilanjutkan otomatis.',
        'payload' => [
            'owner_email' => $payload['owner_email'],
            'business_name' => $payload['business_name'],
            'branch_name' => $payload['branch_name'],
            'error' => $lastException?->getMessage(),
        ],
    ]);

    throw $lastException ?? new RuntimeException('Auto onboarding tenant gagal tanpa error eksplisit.');
})->purpose('Provision and bootstrap a tenant using a dedicated CLI flow with retries');

Artisan::command('platform:resume-tenant-registration {tenant}', function (TenantRegistrationService $registrationService) {
    $tenant = Tenant::query()->findOrFail((int) $this->argument('tenant'));
    $tenant = $registrationService->resumeStoredAutoOnboarding($tenant);

    $this->info('Auto onboarding tenant selesai: '.$tenant->name);

    return self::SUCCESS;
})->purpose('Resume tenant onboarding using the stored platform payload');

Artisan::command('platform:tenant-host-entry {tenant}', function () {
    $tenantIdentifier = trim((string) $this->argument('tenant'));

    $tenant = Tenant::query()
        ->with(['domains' => fn ($query) => $query->orderByDesc('is_primary')->orderBy('id')])
        ->when(
            ctype_digit($tenantIdentifier),
            fn ($query) => $query->whereKey((int) $tenantIdentifier),
            fn ($query) => $query->where('code', $tenantIdentifier)
        )
        ->firstOrFail();

    $domain = $tenant->domains->firstWhere('is_primary', true)?->domain
        ?? $tenant->domains->first()?->domain;

    $this->info('Tenant: '.$tenant->name.' ['.$tenant->code.']');
    $this->line('Status: '.$tenant->status);
    $this->line('Owner: '.$tenant->owner_email);

    if (!$domain) {
        $this->warn('Tenant ini belum memiliki domain primary.');

        return self::FAILURE;
    }

    $this->newLine();
    $this->info('Entry hosts local Windows:');
    $this->line('127.0.0.1 '.$domain);
    $this->newLine();
    $this->info('Login URL tenant:');
    $this->line('http://'.$domain.'/login');
    $this->newLine();
    $this->comment('Tambahkan entry tersebut ke C:\Windows\System32\drivers\etc\hosts bila subdomain tenant belum resolve di local Windows.');

    return self::SUCCESS;
})->purpose('Show the local hosts entry and login URL for a tenant domain');

Artisan::command('platform:onboard-demo-tenant {company_name} {owner_name} {owner_email} {owner_password} {--plan=basic} {--business=Usaha Demo} {--branch=Cabang 1} {--phone=} {--notes=Tenant demo dari command}', function (TenantRegistrationService $registrationService) {
    $tenant = $registrationService->register([
        'company_name' => (string) $this->argument('company_name'),
        'owner_name' => (string) $this->argument('owner_name'),
        'owner_email' => (string) $this->argument('owner_email'),
        'owner_password' => (string) $this->argument('owner_password'),
        'owner_phone' => ($phone = trim((string) $this->option('phone'))) !== '' ? $phone : null,
        'plan_code' => (string) $this->option('plan'),
        'business_name' => (string) $this->option('business'),
        'branch_name' => (string) $this->option('branch'),
        'notes' => (string) $this->option('notes'),
    ]);

    $domain = $tenant->domains->firstWhere('is_primary', true)?->domain
        ?? $tenant->domains->first()?->domain;
    $database = $tenant->databaseConfigs->sortByDesc('id')->first()?->db_name;

    $this->info('Tenant demo berhasil dibuat.');
    $this->line('Tenant ID: '.$tenant->id);
    $this->line('Tenant Code: '.$tenant->code);
    $this->line('Status: '.$tenant->status);
    $this->line('Owner Email: '.$tenant->owner_email);
    $this->line('Owner Password: '.(string) $this->argument('owner_password'));

    if ($database) {
        $this->line('Database Tenant: '.$database);
    }

    if ($domain) {
        $this->newLine();
        $this->info('Entry hosts local Windows:');
        $this->line('127.0.0.1 '.$domain);
        $this->newLine();
        $this->info('Login URL tenant:');
        $this->line('http://'.$domain.'/login');
        $this->newLine();
        $this->comment('Tambahkan entry tersebut ke C:\Windows\System32\drivers\etc\hosts bila subdomain tenant belum resolve di local Windows.');
    } else {
        $this->warn('Tenant berhasil dibuat, tetapi domain primary belum tersedia.');
    }

    return self::SUCCESS;
})->purpose('Register, provision, and bootstrap a demo tenant in one command');

Artisan::command('platform:backfill-tenant-home-access {tenants?* : Tenant ID atau code. Kosongkan jika memakai --all} {--all : Proses semua tenant existing yang punya tenant DB} {--dry-run : Tampilkan tenant yang akan diproses tanpa menulis perubahan}', function () {
    $tenantArguments = collect((array) $this->argument('tenants'))
        ->map(fn (mixed $value) => trim((string) $value))
        ->filter()
        ->values();
    $processAll = (bool) $this->option('all');
    $dryRun = (bool) $this->option('dry-run');

    if (!$processAll && $tenantArguments->isEmpty()) {
        $this->error('Pilih tenant yang ingin diproses atau gunakan opsi --all.');

        return self::FAILURE;
    }

    $tenantQuery = Tenant::query()
        ->with(['databaseConfigs' => fn ($query) => $query->latest('id')]);

    if (!$processAll) {
        $numericIds = $tenantArguments
            ->filter(fn (string $value) => ctype_digit($value))
            ->map(fn (string $value) => (int) $value)
            ->values();
        $codes = $tenantArguments
            ->reject(fn (string $value) => ctype_digit($value))
            ->values();

        $tenantQuery->where(function ($query) use ($numericIds, $codes): void {
            if ($numericIds->isNotEmpty()) {
                $query->orWhereIn('id', $numericIds->all());
            }

            if ($codes->isNotEmpty()) {
                $query->orWhereIn('code', $codes->all());
            }
        });
    }

    $tenants = $tenantQuery
        ->get()
        ->filter(fn (Tenant $tenant) => $tenant->databaseConfigs->isNotEmpty())
        ->values();

    if ($tenants->isEmpty()) {
        $this->warn('Tidak ada tenant dengan database config yang cocok untuk diproses.');

        return self::SUCCESS;
    }

    $requiredPermissions = ['view_home', 'manage_own_profile'];
    $defaultRoles = ['owner', 'admin', 'kasir'];
    $processed = 0;
    $skipped = 0;
    $failed = 0;

    foreach ($tenants as $tenant) {
        $databaseConfig = $tenant->databaseConfigs->sortByDesc('id')->first();

        if (!$databaseConfig || !$databaseConfig->db_name) {
            $skipped++;
            $this->warn("Skip tenant {$tenant->id} [{$tenant->code}] karena database config belum lengkap.");

            continue;
        }

        $connectionName = 'tenant_backfill_'.$tenant->id;
        $driver = $databaseConfig->db_host === 'sqlite' ? 'sqlite' : 'mysql';

        try {
            $connectionConfig = $driver === 'sqlite'
                ? [
                    'driver' => 'sqlite',
                    'database' => $databaseConfig->db_name,
                    'prefix' => '',
                    'foreign_key_constraints' => true,
                ]
                : [
                    'driver' => 'mysql',
                    'host' => $databaseConfig->db_host ?: config('platform.tenant_mysql_host', '127.0.0.1'),
                    'port' => $databaseConfig->db_port ?: config('platform.tenant_mysql_port', '3306'),
                    'database' => $databaseConfig->db_name,
                    'username' => $databaseConfig->db_username ?: config('platform.tenant_mysql_runtime_username', 'root'),
                    'password' => filled($databaseConfig->db_password_encrypted)
                        ? Crypt::decryptString((string) $databaseConfig->db_password_encrypted)
                        : (string) config('platform.tenant_mysql_runtime_password', ''),
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'prefix' => '',
                    'prefix_indexes' => true,
                    'strict' => true,
                    'engine' => null,
                ];

            config()->set("database.connections.{$connectionName}", $connectionConfig);
            DB::purge($connectionName);
            $tenantDb = DB::connection($connectionName);
            $tenantDb->getPdo();

            foreach (['permissions', 'roles', 'role_has_permissions'] as $tableName) {
                if (!Schema::connection($connectionName)->hasTable($tableName)) {
                    throw new RuntimeException("Table {$tableName} tidak ditemukan di tenant DB.");
                }
            }

            $roleRows = $tenantDb->table('roles')
                ->whereIn('name', $defaultRoles)
                ->where('guard_name', 'web')
                ->get(['id', 'name']);

            if ($dryRun) {
                $processed++;
                $this->line("DRY RUN tenant {$tenant->id} [{$tenant->code}] => db={$databaseConfig->db_name}, roles=".$roleRows->pluck('name')->implode(', '));

                continue;
            }

            $tenantDb->transaction(function () use ($tenantDb, $requiredPermissions, $defaultRoles): void {
                foreach ($requiredPermissions as $permissionName) {
                    $tenantDb->table('permissions')->updateOrInsert(
                        ['name' => $permissionName, 'guard_name' => 'web'],
                        ['name' => $permissionName, 'guard_name' => 'web']
                    );
                }

                $permissionsByName = $tenantDb->table('permissions')
                    ->whereIn('name', $requiredPermissions)
                    ->where('guard_name', 'web')
                    ->pluck('id', 'name');

                $rolesByName = $tenantDb->table('roles')
                    ->whereIn('name', $defaultRoles)
                    ->where('guard_name', 'web')
                    ->pluck('id', 'name');

                foreach ($defaultRoles as $roleName) {
                    $roleId = $rolesByName[$roleName] ?? null;

                    if (!$roleId) {
                        continue;
                    }

                    foreach ($requiredPermissions as $permissionName) {
                        $permissionId = $permissionsByName[$permissionName] ?? null;

                        if (!$permissionId) {
                            continue;
                        }

                        $tenantDb->table('role_has_permissions')->updateOrInsert([
                            'permission_id' => $permissionId,
                            'role_id' => $roleId,
                        ], [
                            'permission_id' => $permissionId,
                            'role_id' => $roleId,
                        ]);
                    }
                }
            });

            $processed++;
            $this->info("Tenant {$tenant->id} [{$tenant->code}] berhasil dibackfill.");
        } catch (Throwable $exception) {
            $failed++;
            $this->error("Tenant {$tenant->id} [{$tenant->code}] gagal: {$exception->getMessage()}");
        } finally {
            DB::disconnect($connectionName);
            DB::purge($connectionName);
            config()->offsetUnset("database.connections.{$connectionName}");
        }
    }

    $this->newLine();
    $this->line("Selesai. processed={$processed}, skipped={$skipped}, failed={$failed}");

    if (!$dryRun) {
        $this->comment('Catatan: tenant app mungkin perlu reset cache permission jika masih ada sesi lama yang belum memuat ulang permission baru.');
    }

    return $failed > 0 ? self::FAILURE : self::SUCCESS;
})->purpose('Backfill permission Home dan Account ke tenant existing tanpa mereset custom permission tenant');

Artisan::command('platform:refresh-tenant-events {--days=3}', function (TenantAuditLogger $auditLogger, PlatformInboxPublisher $inboxPublisher) {
    $days = max(1, (int) $this->option('days'));
    $latestSubscriptions = Subscription::query()
        ->with('tenant')
        ->latest('id')
        ->get()
        ->unique('tenant_id')
        ->values();
    $platformAdminEmails = User::query()
        ->where('is_platform_admin', true)
        ->pluck('email')
        ->filter()
        ->values()
        ->all();

    $created = 0;

    $writeSystemAudit = function (Tenant $tenant, string $action, string $summary, array $changes = [], array $metadata = []) use (&$created, $platformAdminEmails, $auditLogger): void {
        $audit = $auditLogger->logSystem($tenant, $action, $summary, $changes, $metadata);

        if (!$audit) {
            return;
        }

        $created++;

        if ($platformAdminEmails === []) {
            return;
        }

        $alertTitle = match ($action) {
            'tenant.subscription.expiring' => 'Tenant Akan Expire',
            'tenant.subscription.expired' => 'Tenant Expired',
            'tenant.subscription.renewed' => 'Perpanjangan Tenant Terdeteksi',
            default => 'Alert Tenant Platform',
        };

        try {
            Mail::to($platformAdminEmails)->send(new PlatformTenantAlert(
                tenant: $tenant,
                alertTitle: $alertTitle,
                summary: $summary,
                adminUrl: route('platform.admin.tenants.index', ['tenant' => $tenant->getKey()]).'#tenant-detail',
            ));
        } catch (Throwable $exception) {
            Log::warning('Platform tenant alert email failed to send.', [
                'tenant_id' => $tenant->getKey(),
                'tenant_code' => $tenant->code,
                'action' => $action,
                'error' => $exception->getMessage(),
            ]);
        }

        if (
            in_array($action, ['tenant.subscription.expiring', 'tenant.subscription.expired'], true)
            && filled($tenant->owner_email)
        ) {
            try {
                $loginUrl = $tenant->domains()->where('is_primary', true)->value('domain')
                    ? 'http://'.$tenant->domains()->where('is_primary', true)->value('domain').'/login'
                    : null;
                $renewalUrl = route('platform.renewal.create', ['tenant' => $tenant->code]);

                Mail::to($tenant->owner_email)->send(new PlatformTenantOwnerAlert(
                    tenant: $tenant,
                    alertTitle: $alertTitle,
                    summary: $summary,
                    loginUrl: $loginUrl,
                    renewalUrl: $renewalUrl,
                ));
            } catch (Throwable $exception) {
                Log::warning('Platform tenant owner alert email failed to send.', [
                    'tenant_id' => $tenant->getKey(),
                    'tenant_code' => $tenant->code,
                    'action' => $action,
                    'owner_email' => $tenant->owner_email,
                    'error' => $exception->getMessage(),
                ]);
            }
        }
    };

    foreach ($latestSubscriptions as $subscription) {
        $tenant = $subscription->tenant;
        $expiresAt = $subscription->expired_at ?? $subscription->trial_ends_at;

        if (!$tenant || !$expiresAt) {
            continue;
        }

        if (
            $subscription->updated_at !== null
            && $subscription->created_at !== null
            && $subscription->updated_at->isToday()
            && $subscription->created_at->isBefore(today())
            && $subscription->updated_at->greaterThan($subscription->created_at)
            && $expiresAt->isFuture()
            && ($subscription->started_at?->isBefore(today()) ?? true)
        ) {
            $writeSystemAudit(
                $tenant,
                'tenant.subscription.renewed',
                'Subscription tenant diperpanjang atau diperbarui hari ini.',
                [
                    'after' => [
                        'expired_at' => $expiresAt->toISOString(),
                        'subscription_status' => $subscription->status,
                    ],
                ]
            );
        }

        $daysRemaining = (int) ceil(now()->diffInSeconds($expiresAt, false) / 86400);

        if (in_array($daysRemaining, [7, 3, 1], true)) {
            $writeSystemAudit(
                $tenant,
                'tenant.subscription.expiring',
                'Subscription tenant memasuki reminder H-'.$daysRemaining.' dan perlu follow-up renewal.',
                [
                    'after' => [
                        'expired_at' => $expiresAt->toISOString(),
                        'subscription_status' => $subscription->status,
                    ],
                ],
                ['stage' => 'h-'.$daysRemaining]
            );
        } elseif ($expiresAt->isFuture() && $expiresAt->lessThanOrEqualTo(now()->addDays($days))) {
            $writeSystemAudit(
                $tenant,
                'tenant.subscription.expiring',
                'Subscription tenant akan expire dalam '.$days.' hari atau kurang.',
                [
                    'after' => [
                        'expired_at' => $expiresAt->toISOString(),
                        'subscription_status' => $subscription->status,
                    ],
                ],
                ['stage' => 'window-'.$days]
            );
        }

        if ($expiresAt->isPast()) {
            $writeSystemAudit(
                $tenant,
                'tenant.subscription.expired',
                'Subscription tenant sudah melewati masa aktif dan perlu follow-up.',
                [
                    'after' => [
                        'expired_at' => $expiresAt->toISOString(),
                        'subscription_status' => $subscription->status,
                    ],
                ]
            );
        }
    }

    Tenant::query()
        ->where('status', 'active')
        ->where(function ($query) {
            $query->whereDate('onboarded_at', today())
                ->orWhereDate('updated_at', today());
        })
        ->latest('updated_at')
        ->get()
        ->each(function (Tenant $tenant) use ($inboxPublisher, &$created): void {
            $item = $inboxPublisher->publish(
                tenant: $tenant,
                eventKey: 'tenant.activated',
                title: 'Tenant baru aktif',
                message: $tenant->name.' tercatat aktif dan siap dipakai operasional.',
                category: 'activation',
                tone: 'violet',
                payload: ['tenant_code' => $tenant->code],
                occurredAt: $tenant->onboarded_at ?? $tenant->updated_at ?? now(),
                fingerprint: 'tenant.activated:'.$tenant->getKey().':'.today()->format('Y-m-d'),
            );

            if ($item->wasRecentlyCreated) {
                $created++;
            }
        });

    $this->info('Refresh event tenant selesai. Event baru dibuat: '.$created);

    return self::SUCCESS;
})->purpose('Refresh scheduled tenant inbox events such as expiring, expired, and renewed subscriptions');

Artisan::command('platform:sync-tenant-lifecycle', function (TenantLifecycleSyncService $lifecycleSyncService) {
    $changes = $lifecycleSyncService->syncExpiredTenants();

    $this->info('Tenant lifecycle sync selesai. Tenant yang diperbarui: '.$changes->count());

    if ($this->output->isVerbose()) {
        $changes->each(function (array $change): void {
            $this->line(sprintf(
                '- #%d [%s] subscription_changed=%s tenant_changed=%s',
                $change['tenant_id'],
                $change['tenant_code'],
                $change['subscription_changed'] ? 'yes' : 'no',
                $change['tenant_changed'] ? 'yes' : 'no',
            ));
        });
    }

    return self::SUCCESS;
})->purpose('Sync tenant and subscription status to expired when the active period has already passed');

Artisan::command('platform:prune-pending-registrations {--hours=72}', function () {
    $hours = max(1, (int) $this->option('hours'));
    $cutoff = now()->subHours($hours);

    if (!Schema::hasTable('platform_pending_registrations')) {
        $this->warn('Table platform_pending_registrations belum tersedia. Jalankan migration platform terbaru lebih dulu.');

        return self::SUCCESS;
    }

    $query = PendingRegistration::query()
        ->whereNull('tenant_id')
        ->whereNull('consumed_at')
        ->where(function ($inner) use ($cutoff) {
            $inner->where('expires_at', '<=', $cutoff)
                ->orWhere(function ($expired) use ($cutoff) {
                    $expired->whereNull('expires_at')
                        ->where('updated_at', '<=', $cutoff);
                });
        });

    $count = (clone $query)->count();
    $query->delete();

    $this->info('Pending registration yang dibersihkan: '.$count);

    return self::SUCCESS;
})->purpose('Delete expired pending public registrations that were never verified');

Schedule::command('platform:prune-pending-registrations --hours=72')
    ->daily()
    ->description('Delete expired unverified public registration requests older than 72 hours');

Schedule::command('platform:sync-tenant-lifecycle')
    ->hourly()
    ->description('Sync expired tenant subscriptions and tenant lifecycle status');
