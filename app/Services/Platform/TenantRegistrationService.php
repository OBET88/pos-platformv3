<?php

namespace App\Services\Platform;

use App\Mail\PlatformTenantReady;
use App\Models\Platform\Plan;
use App\Models\Platform\PlatformPaymentTransaction;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;
use App\Models\Platform\TenantBootstrapSecret;
use App\Models\Platform\TenantDatabaseConfig;
use App\Models\Platform\TenantDomain;
use App\Models\Platform\TenantProvisionLog;
use App\Services\Accounting\AccountingDefaultChartService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

class TenantRegistrationService
{
    public function __construct(
        private readonly TenantDomainSuggestionService $tenantDomainSuggestionService,
        private readonly TenantProvisioningService $tenantProvisioningService,
        private readonly TenantAppBootstrapService $tenantAppBootstrapService,
        private readonly TenantOnboardingDispatcher $onboardingDispatcher,
        private readonly PlatformSettings $platformSettings,
        private readonly AccountingDefaultChartService $accountingDefaultChartService,
    ) {
    }

    public function register(array $payload, bool $completeOnboarding = true): Tenant
    {
        $this->prepareLongRunningExecution();

        $ownerEmail = Str::lower(trim((string) $payload['owner_email']));
        $businessName = trim((string) ($payload['business_name'] ?? $payload['company_name'] ?? 'Usaha Utama'));
        $branchName = trim((string) ($payload['branch_name'] ?? 'Cabang 1'));
        $ownerPassword = (string) ($payload['owner_password'] ?? Str::random(16));
        $defaultTrialDays = $this->platformSettings->defaultTrialDays();

        $tenant = DB::transaction(function () use ($payload, $ownerEmail, $businessName, $branchName, $ownerPassword, $defaultTrialDays) {
            $plan = Plan::query()
                ->where('code', $payload['plan_code'])
                ->where('is_active', true)
                ->firstOrFail();

            $tenant = Tenant::query()->create([
                'code' => $this->resolveTenantCode($payload),
                'name' => trim($payload['company_name']),
                'owner_name' => trim($payload['owner_name']),
                'owner_email' => Str::lower(trim($payload['owner_email'])),
                'owner_phone' => filled($payload['owner_phone'] ?? null) ? trim((string) $payload['owner_phone']) : null,
                'status' => 'pending_setup',
                'trial_ends_at' => now()->addDays($defaultTrialDays),
                'notes' => filled($payload['notes'] ?? null) ? trim((string) $payload['notes']) : null,
            ]);

            Subscription::query()->create([
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'status' => 'trial',
                'started_at' => now(),
                'trial_ends_at' => now()->addDays($defaultTrialDays),
                'expired_at' => now()->addDays($defaultTrialDays),
            ]);

            if ($domain = $this->tenantDomainSuggestionService->suggest($tenant->code)) {
                TenantDomain::query()->create([
                    'tenant_id' => $tenant->id,
                    'domain' => $domain,
                    'type' => 'subdomain',
                    'is_primary' => true,
                ]);
            }

            TenantDatabaseConfig::query()->create([
                'tenant_id' => $tenant->id,
                'connection_name' => 'tenant_' . $tenant->code,
                'db_name' => 'tenant_' . $tenant->code,
                'status' => 'pending',
            ]);

            TenantProvisionLog::query()->create([
                'tenant_id' => $tenant->id,
                'step' => 'registration_received',
                'status' => 'completed',
                'message' => 'Pendaftaran tenant berhasil diterima dan memulai auto provisioning tenant.',
                'payload' => [
                    'plan_code' => $plan->code,
                    'plan_name' => $plan->name,
                ],
            ]);

            TenantProvisionLog::query()->create([
                'tenant_id' => $tenant->id,
                'step' => 'auto_onboarding_requested',
                'status' => 'pending',
                'message' => 'Sistem sedang menyiapkan database tenant, schema tenant app, dan owner awal secara otomatis.',
                'payload' => [
                    'owner_email' => $ownerEmail,
                    'owner_name' => trim((string) $payload['owner_name']),
                    'owner_email_verified_at' => $payload['owner_email_verified_at'] ?? null,
                    'business_name' => $businessName,
                    'branch_name' => $branchName,
                    'owner_role' => 'owner',
                ],
            ]);

            TenantBootstrapSecret::query()->create([
                'tenant_id' => $tenant->getKey(),
                'owner_password_encrypted' => Crypt::encryptString($ownerPassword),
            ]);

            $this->onboardingDispatcher->enqueue($tenant, [
                'owner_name' => trim((string) $payload['owner_name']),
                'owner_email' => $ownerEmail,
                'owner_email_verified_at' => $payload['owner_email_verified_at'] ?? null,
                'business_name' => $businessName,
                'branch_name' => $branchName,
                'owner_role' => 'owner',
            ]);

            $this->accountingDefaultChartService->provisionForTenant($tenant);

            return $tenant->load(['subscriptions.plan', 'domains', 'databaseConfigs']);
        });

        if ($completeOnboarding) {
            return $this->completeAutoOnboarding($tenant, [
                'owner_name' => trim((string) $payload['owner_name']),
                'owner_email' => $ownerEmail,
                'owner_password' => $ownerPassword,
                'owner_email_verified_at' => $payload['owner_email_verified_at'] ?? null,
                'business_name' => $businessName,
                'branch_name' => $branchName,
                'owner_role' => 'owner',
            ]);
        }

        return $tenant->fresh(['domains']);
    }

    public function registerWithPendingPayment(array $payload): PlatformPaymentTransaction
    {
        $this->prepareLongRunningExecution();

        $ownerEmail = Str::lower(trim((string) $payload['owner_email']));
        $businessName = trim((string) ($payload['business_name'] ?? $payload['company_name'] ?? 'Usaha Utama'));
        $branchName = trim((string) ($payload['branch_name'] ?? 'Cabang 1'));
        $ownerPassword = (string) ($payload['owner_password'] ?? Str::random(16));

        return DB::transaction(function () use ($payload, $ownerEmail, $businessName, $branchName, $ownerPassword) {
            $plan = Plan::query()
                ->where('code', $payload['plan_code'])
                ->where('is_active', true)
                ->firstOrFail();

            $tenant = Tenant::query()->create([
                'code' => $this->resolveTenantCode($payload),
                'name' => trim($payload['company_name']),
                'owner_name' => trim($payload['owner_name']),
                'owner_email' => $ownerEmail,
                'owner_phone' => filled($payload['owner_phone'] ?? null) ? trim((string) $payload['owner_phone']) : null,
                'status' => 'pending_payment',
                'trial_ends_at' => null,
                'notes' => filled($payload['notes'] ?? null) ? trim((string) $payload['notes']) : null,
            ]);

            $subscription = Subscription::query()->create([
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'status' => 'pending_payment',
                'started_at' => null,
                'trial_ends_at' => null,
                'expired_at' => null,
            ]);

            if ($domain = $this->tenantDomainSuggestionService->suggest($tenant->code)) {
                TenantDomain::query()->create([
                    'tenant_id' => $tenant->id,
                    'domain' => $domain,
                    'type' => 'subdomain',
                    'is_primary' => true,
                ]);
            }

            TenantDatabaseConfig::query()->create([
                'tenant_id' => $tenant->id,
                'connection_name' => 'tenant_' . $tenant->code,
                'db_name' => 'tenant_' . $tenant->code,
                'status' => 'pending',
            ]);

            TenantProvisionLog::query()->create([
                'tenant_id' => $tenant->id,
                'step' => 'registration_received',
                'status' => 'completed',
                'message' => 'Pendaftaran tenant terverifikasi dan menunggu pembayaran invoice paket sebelum auto provisioning dijalankan.',
                'payload' => [
                    'plan_code' => $plan->code,
                    'plan_name' => $plan->name,
                    'payment_status' => 'pending_payment',
                ],
            ]);

            TenantProvisionLog::query()->create([
                'tenant_id' => $tenant->id,
                'step' => 'auto_onboarding_requested',
                'status' => 'pending',
                'message' => 'Auto provisioning tenant akan dijalankan setelah invoice pendaftaran berhasil dibayar.',
                'payload' => [
                    'owner_email' => $ownerEmail,
                    'owner_name' => trim((string) $payload['owner_name']),
                    'owner_email_verified_at' => $payload['owner_email_verified_at'] ?? null,
                    'business_name' => $businessName,
                    'branch_name' => $branchName,
                    'owner_role' => 'owner',
                ],
            ]);

            TenantBootstrapSecret::query()->create([
                'tenant_id' => $tenant->getKey(),
                'owner_password_encrypted' => Crypt::encryptString($ownerPassword),
            ]);

            $this->onboardingDispatcher->enqueue($tenant, [
                'owner_name' => trim((string) $payload['owner_name']),
                'owner_email' => $ownerEmail,
                'owner_email_verified_at' => $payload['owner_email_verified_at'] ?? null,
                'business_name' => $businessName,
                'branch_name' => $branchName,
                'owner_role' => 'owner',
            ], false);

            $this->accountingDefaultChartService->provisionForTenant($tenant);

            $invoiceNumber = $this->generateRegistrationInvoiceNumber($tenant);

            return PlatformPaymentTransaction::query()->create([
                'tenant_id' => $tenant->getKey(),
                'subscription_id' => $subscription->getKey(),
                'plan_id' => $plan->getKey(),
                'invoice_number' => $invoiceNumber,
                'type' => 'registration',
                'payment_method' => 'manual_transfer',
                'payment_channel' => 'bank_transfer',
                'amount_subtotal' => (float) $plan->price,
                'amount_discount' => 0,
                'amount_total' => (float) $plan->price,
                'billing_cycle' => (string) $plan->billing_cycle,
                'status' => 'pending_payment',
                'due_at' => now()->addDays(3),
                'notes' => filled($payload['notes'] ?? null) ? trim((string) $payload['notes']) : null,
                'meta_json' => [
                    'created_via' => 'public_registration',
                    'requested_by_email' => $ownerEmail,
                    'selected_plan_id' => $plan->getKey(),
                    'selected_plan_code' => $plan->code,
                    'registration_payment_required' => true,
                ],
            ])->fresh(['tenant.domains', 'plan', 'subscription']);
        });
    }

    public function completeAutoOnboarding(Tenant $tenant, array $payload): Tenant
    {
        $this->prepareLongRunningExecution();
        $ownerEmail = Str::lower(trim((string) ($payload['owner_email'] ?? '')));

        $tenant = $this->tenantProvisioningService->provision($tenant);

        $tenant = $this->tenantAppBootstrapService->bootstrap($tenant, $payload);

        TenantProvisionLog::query()->updateOrCreate([
            'tenant_id' => $tenant->id,
            'step' => 'auto_onboarding_completed',
        ], [
            'status' => 'completed',
            'message' => 'Auto provisioning tenant selesai. Database tenant, schema tenant app, dan owner awal sudah siap.',
            'payload' => [
                'owner_email' => $ownerEmail,
                'business_name' => $payload['business_name'] ?? null,
                'branch_name' => $payload['branch_name'] ?? null,
            ],
        ]);

        $this->sendTenantReadyEmailIfNeeded($tenant, $payload);
        TenantBootstrapSecret::query()->where('tenant_id', $tenant->getKey())->delete();

        return $tenant->fresh(['subscriptions.plan', 'domains', 'databaseConfigs', 'provisionLogs']);
    }

    public function refreshAutoOnboardingProgress(Tenant $tenant): Tenant
    {
        $tenant = Tenant::query()->findOrFail($tenant->getKey());

        if ($tenant->provisionLogs()->where('step', 'auto_onboarding_completed')->where('status', 'completed')->exists()) {
            return $tenant->fresh(['subscriptions.plan', 'domains', 'databaseConfigs', 'provisionLogs']);
        }

        [$payload, $latestBootstrapLog] = $this->resolveStoredAutoOnboardingContext($tenant);

        if ($payload === null) {
            return $tenant->fresh(['subscriptions.plan', 'domains', 'databaseConfigs', 'provisionLogs']);
        }

        if ($this->tenantAppBootstrapService->finalizeBootstrapIfReady($tenant, $payload)) {
            $this->sendTenantReadyEmailIfNeeded($tenant, $payload);
            return $tenant->fresh(['subscriptions.plan', 'domains', 'databaseConfigs', 'provisionLogs']);
        }

        $bootstrapIsPending = $latestBootstrapLog?->status === 'pending';
        $bootstrapIsStale = $bootstrapIsPending
            && $latestBootstrapLog?->updated_at !== null
            && $latestBootstrapLog->updated_at->lt(now()->subMinute());

        if ($latestBootstrapLog === null || $latestBootstrapLog?->status === 'failed' || $bootstrapIsStale) {
            try {
                $this->startAutoOnboarding($tenant);
            } catch (Throwable) {
                // Keep status endpoint best-effort; failures remain visible through provision logs.
            }
        }

        return $tenant->fresh(['subscriptions.plan', 'domains', 'databaseConfigs', 'provisionLogs']);
    }

    public function startAutoOnboarding(Tenant $tenant): bool
    {
        $tenant = Tenant::query()->findOrFail($tenant->getKey());

        if ($tenant->provisionLogs()->where('step', 'auto_onboarding_completed')->where('status', 'completed')->exists()) {
            return false;
        }

        [$payload] = $this->resolveStoredAutoOnboardingContext($tenant);

        if ($payload === null) {
            return false;
        }

        $this->onboardingDispatcher->enqueue($tenant, $payload, $tenant->status !== 'pending_payment');

        return true;
    }

    public function resumeStoredAutoOnboarding(Tenant $tenant): Tenant
    {
        $tenant = Tenant::query()->findOrFail($tenant->getKey());

        if ($tenant->provisionLogs()->where('step', 'auto_onboarding_completed')->where('status', 'completed')->exists()) {
            return $tenant->fresh(['subscriptions.plan', 'domains', 'databaseConfigs', 'provisionLogs']);
        }

        [$payload, $latestBootstrapLog] = $this->resolveStoredAutoOnboardingContext($tenant);

        if ($payload === null) {
            throw new RuntimeException('Payload onboarding tenant tidak lengkap untuk auto provisioning.');
        }

        if ($this->tenantAppBootstrapService->finalizeBootstrapIfReady($tenant, $payload)) {
            $this->sendTenantReadyEmailIfNeeded($tenant, $payload);
            return $tenant->fresh(['subscriptions.plan', 'domains', 'databaseConfigs', 'provisionLogs']);
        }

        $bootstrapIsPending = $latestBootstrapLog?->status === 'pending';
        $bootstrapIsStale = $bootstrapIsPending
            && $latestBootstrapLog?->updated_at !== null
            && $latestBootstrapLog->updated_at->lt(now()->subMinute());

        if ($bootstrapIsPending && !$bootstrapIsStale) {
            return $tenant->fresh(['subscriptions.plan', 'domains', 'databaseConfigs', 'provisionLogs']);
        }

        $tenant = $this->tenantProvisioningService->provision($tenant);

        if ($this->tenantAppBootstrapService->finalizeBootstrapIfReady($tenant, $payload)) {
            $this->sendTenantReadyEmailIfNeeded($tenant, $payload);
            return $tenant->fresh(['subscriptions.plan', 'domains', 'databaseConfigs', 'provisionLogs']);
        }

        return $this->completeAutoOnboardingWithRetries($tenant, $payload);
    }

    public function completeStoredAutoOnboarding(Tenant $tenant): Tenant
    {
        $tenant = Tenant::query()->findOrFail($tenant->getKey());

        if ($tenant->provisionLogs()->where('step', 'auto_onboarding_completed')->where('status', 'completed')->exists()) {
            return $tenant->fresh(['subscriptions.plan', 'domains', 'databaseConfigs', 'provisionLogs']);
        }

        [$payload] = $this->resolveStoredAutoOnboardingContext($tenant);

        if ($payload === null) {
            throw new RuntimeException('Payload onboarding tenant tidak lengkap untuk auto provisioning.');
        }

        return $this->completeAutoOnboarding($tenant, $payload);
    }

    public function dispatchAutoOnboardingCli(Tenant $tenant): bool
    {
        $tenant = $tenant->fresh(['provisionLogs']);

        if (!$tenant) {
            return false;
        }

        if ($tenant->provisionLogs()->where('step', 'auto_onboarding_completed')->where('status', 'completed')->exists()) {
            return false;
        }

        $requestedPayload = (array) ($tenant->provisionLogs()
            ->where('step', 'auto_onboarding_requested')
            ->latest('id')
            ->value('payload') ?? []);

        $ownerPasswordEncrypted = (string) (TenantBootstrapSecret::query()
            ->where('tenant_id', $tenant->getKey())
            ->value('owner_password_encrypted') ?? '');

        if ($ownerPasswordEncrypted === '') {
            return false;
        }

        $dispatchLockKey = 'platform-register-cli-dispatch:'.$tenant->getKey();

        if (!Cache::add($dispatchLockKey, now()->toIso8601String(), now()->addMinutes(10))) {
            return false;
        }

        try {
            $phpBinary = $this->resolvePhpBinary((string) config('platform.tenant_app_php_binary', PHP_BINARY));
            $commandArguments = [
                base_path('artisan'),
                'platform:resume-tenant-registration',
                (string) $tenant->getKey(),
            ];

            TenantProvisionLog::query()->updateOrCreate([
                'tenant_id' => $tenant->getKey(),
                'step' => 'tenant_app_bootstrap',
            ], [
                'status' => 'pending',
                'message' => 'Bootstrap tenant app sedang dijalankan otomatis.',
                'payload' => [
                    'mode' => 'platform_cli_background',
                ],
            ]);

            if (DIRECTORY_SEPARATOR === '\\') {
                $logPath = storage_path('logs/platform-auto-onboarding-'.$tenant->getKey().'.log');
                $innerCommand = sprintf(
                    '%s %s >> %s 2>&1',
                    $this->quoteForCmd($phpBinary),
                    implode(' ', array_map(
                        fn (string $argument) => $this->quoteForCmd($argument),
                        $commandArguments
                    )),
                    $this->quoteForCmd($logPath),
                );
                $commandLine = sprintf(
                    'cd /d %s && start "" /B cmd /c %s',
                    $this->quoteForCmd(base_path()),
                    $this->quoteForCmd($innerCommand),
                );

                $handle = @popen('cmd /c '.$commandLine, 'r');

                if ($handle === false) {
                    throw new \RuntimeException('Gagal menjalankan background CLI onboarding tenant.');
                }

                @pclose($handle);

                return true;
            }

            $process = new Process(array_merge([$phpBinary], $commandArguments), base_path());
            $process->setTimeout(null);
            $process->start();

            return true;
        } finally {
            Cache::forget($dispatchLockKey);
        }
    }

    public function completeAutoOnboardingViaCli(Tenant $tenant): bool
    {
        $this->prepareLongRunningExecution();
        $tenant = $tenant->fresh(['provisionLogs']);

        if (!$tenant) {
            return false;
        }

        if ($tenant->provisionLogs()->where('step', 'auto_onboarding_completed')->where('status', 'completed')->exists()) {
            return true;
        }

        $phpBinary = $this->resolvePhpBinary(PHP_BINARY);
        $command = [
            $phpBinary,
            'artisan',
            'platform:resume-tenant-registration',
            (string) $tenant->getKey(),
        ];
        $attemptDelaysInSeconds = [1, 2, 4];
        $lastError = null;

        foreach ($attemptDelaysInSeconds as $attemptIndex => $delayInSeconds) {
            $process = new Process($command, base_path());
            $process->setTimeout(180);
            $process->run();

            if ($process->isSuccessful()) {
                return true;
            }

            $lastError = trim($process->getErrorOutput()) ?: trim($process->getOutput()) ?: 'CLI onboarding tenant gagal dijalankan.';
            sleep($delayInSeconds);
        }

        $finalProcess = new Process($command, base_path());
        $finalProcess->setTimeout(180);
        $finalProcess->run();

        if (!$finalProcess->isSuccessful()) {
            $lastError = trim($finalProcess->getErrorOutput()) ?: trim($finalProcess->getOutput()) ?: $lastError ?: 'CLI onboarding tenant gagal dijalankan.';
            throw new \RuntimeException($lastError);
        }

        return true;
    }

    private function prepareLongRunningExecution(): void
    {
        if (PHP_SAPI === 'cli') {
            return;
        }

        if (function_exists('set_time_limit')) {
            try {
                set_time_limit(0);
            } catch (\Throwable) {
                // Ignore hosts that disable runtime time-limit changes.
            }
        }

        if (function_exists('ignore_user_abort')) {
            ignore_user_abort(true);
        }
    }

    private function resolvePhpBinary(string $configuredBinary): string
    {
        $configuredBinary = trim($configuredBinary, " \t\n\r\0\x0B\"'");

        if ($configuredBinary !== '') {
            $resolvedConfiguredBinary = trim(realpath($configuredBinary) ?: $configuredBinary, " \t\n\r\0\x0B\"'");

            if (
                DIRECTORY_SEPARATOR === '\\'
                && $this->shouldSwapToCliPhp($resolvedConfiguredBinary)
            ) {
                $cliBinary = dirname($resolvedConfiguredBinary).DIRECTORY_SEPARATOR.'php.exe';

                if (is_file($cliBinary)) {
                    return $cliBinary;
                }

                $fallbackCliBinary = $this->resolveDefaultCliPhpBinary();

                if ($fallbackCliBinary !== null) {
                    return $fallbackCliBinary;
                }
            }

            return $resolvedConfiguredBinary;
        }

        return PHP_BINARY;
    }

    private function resolveDefaultCliPhpBinary(): ?string
    {
        $candidates = [
            PHP_BINDIR.DIRECTORY_SEPARATOR.'php.exe',
            dirname(PHP_BINARY).DIRECTORY_SEPARATOR.'php.exe',
        ];

        if (DIRECTORY_SEPARATOR === '\\') {
            $candidates = array_merge($candidates, $this->windowsCliPhpCandidates());
        }

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function shouldSwapToCliPhp(string $resolvedBinary): bool
    {
        $normalizedBinary = strtolower(str_replace('/', DIRECTORY_SEPARATOR, $resolvedBinary));

        return str_ends_with($normalizedBinary, 'php-cgi.exe')
            || str_ends_with($normalizedBinary, DIRECTORY_SEPARATOR.'httpd.exe')
            || str_ends_with($normalizedBinary, DIRECTORY_SEPARATOR.'apache.exe');
    }

    private function windowsCliPhpCandidates(): array
    {
        $candidates = [];
        $roots = array_filter([
            env('PLATFORM_TENANT_APP_PHP_BINARY'),
            'D:\\laragon\\bin\\php',
            'C:\\laragon\\bin\\php',
        ]);

        foreach ($roots as $root) {
            $normalizedRoot = trim((string) $root, " \t\n\r\0\x0B\"'");

            if ($normalizedRoot === '') {
                continue;
            }

            if (is_file($normalizedRoot) && str_ends_with(strtolower($normalizedRoot), 'php.exe')) {
                $candidates[] = $normalizedRoot;
                continue;
            }

            if (!is_dir($normalizedRoot)) {
                continue;
            }

            $matches = glob(rtrim($normalizedRoot, '\\/').DIRECTORY_SEPARATOR.'*'.DIRECTORY_SEPARATOR.'php.exe') ?: [];
            rsort($matches);

            foreach ($matches as $match) {
                $candidates[] = $match;
            }
        }

        return array_values(array_unique($candidates));
    }

    private function quoteForPowerShell(string $value): string
    {
        return "'".str_replace("'", "''", $value)."'";
    }

    private function quoteForCmd(string $value): string
    {
        $value = trim($value, " \t\n\r\0\x0B\"'");

        return '"'.str_replace('"', '""', $value).'"';
    }

    private function resolveStoredAutoOnboardingContext(Tenant $tenant): array
    {
        $requestedPayload = (array) ($tenant->provisionLogs()
            ->where('step', 'auto_onboarding_requested')
            ->latest('id')
            ->value('payload') ?? []);

        $ownerPasswordEncrypted = (string) (TenantBootstrapSecret::query()
            ->where('tenant_id', $tenant->getKey())
            ->value('owner_password_encrypted') ?? '');

        if ($ownerPasswordEncrypted === '') {
            return [null, null];
        }

        try {
            $ownerPassword = Crypt::decryptString($ownerPasswordEncrypted);
        } catch (Throwable) {
            throw new RuntimeException('Password onboarding tenant tidak bisa dipulihkan untuk auto provisioning.');
        }

        $payload = [
            'owner_name' => (string) ($requestedPayload['owner_name'] ?? $tenant->owner_name),
            'owner_email' => (string) ($requestedPayload['owner_email'] ?? $tenant->owner_email),
            'owner_password' => $ownerPassword,
            'owner_email_verified_at' => $requestedPayload['owner_email_verified_at'] ?? null,
            'business_name' => (string) ($requestedPayload['business_name'] ?? $tenant->name),
            'branch_name' => (string) ($requestedPayload['branch_name'] ?? 'Cabang 1'),
            'owner_role' => (string) ($requestedPayload['owner_role'] ?? 'owner'),
        ];

        $latestBootstrapLog = $tenant->provisionLogs()
            ->where('step', 'tenant_app_bootstrap')
            ->latest('id')
            ->first(['status', 'created_at', 'updated_at']);

        return [$payload, $latestBootstrapLog];
    }

    private function completeAutoOnboardingWithRetries(Tenant $tenant, array $payload): Tenant
    {
        $attemptDelaysInMicroseconds = [
            1000000,
            2000000,
            4000000,
        ];
        $platformConnectionNames = array_values(array_unique(array_filter([
            config('database.default'),
            'mysql',
        ])));
        $lastException = null;

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
                $tenant = Tenant::query()->findOrFail($tenant->getKey());

                return $this->completeAutoOnboarding($tenant, $payload);
            } catch (Throwable $exception) {
                $lastException = $exception;

                if ($attempt <= count($attemptDelaysInMicroseconds)) {
                    $reconnectPlatform();
                    usleep($attemptDelaysInMicroseconds[$attempt - 1]);
                }
            }
        }

        throw $lastException ?? new RuntimeException('Auto onboarding tenant gagal tanpa error eksplisit.');
    }

    /**
     * Opsi A (keputusan Bos Robert 2026-10-31): tenant boleh mengisi kode
     * subdomain manual (`tenant_code`, opsional). Bila terisi & legal, kode
     * itu dipakai apa adanya TANPA suffix — validasi unik/reserved sudah
     * dijaga di form (validateRegistration) dan di sini (guard kedua).
     * Bila kosong, fallback ke slug auto dari nama perusahaan.
     */
    private function resolveTenantCode(array $payload): string
    {
        $manualCode = strtolower(trim((string) ($payload['tenant_code'] ?? '')));

        if ($manualCode !== '') {
            // Guard kedua: reserved/ilegal ditolak sebelum tenant dibuat,
            // walau form sudah validasi (mis. payload dari draft lama).
            $this->tenantDomainSuggestionService->assertTenantCodeIsUsable($manualCode);

            if (Tenant::query()->where('code', $manualCode)->exists()) {
                throw new RuntimeException(sprintf(
                    'Kode subdomain "%s" sudah dipakai tenant lain. Pilih kode lain.',
                    $manualCode,
                ));
            }

            return $manualCode;
        }

        return $this->generateTenantCode((string) $payload['company_name']);
    }

    private function generateTenantCode(string $companyName): string
    {
        // Batasi base slug auto 12 karakter (bukan 18) agar nama panjang
        // (mis. "PT Berkah Sinar Jaya" → "pt-berkah-si") menghasilkan kode
        // pendek. Str::slug() sudah lowercase + ganti separator '-' + strip
        // karakter non-alfanumerik; rtrim '-' karena substr(0,12) bisa memotong
        // di tengah kata sehingga menghasilkan akhiran hyphen (ilegal per
        // assertTenantCodeIsUsable). Suffix bentrok: 8 char awal base (rtrim
        // '-') + "-N" → total selalu <= 20 (batas maks kode subdomain).
        $base = rtrim(substr(Str::slug($companyName), 0, 12), '-');

        $base = $base !== '' ? $base : 'tenant';
        $candidate = $base;
        $counter = 1;

        while (Tenant::query()->where('code', $candidate)->exists()) {
            $suffixedBase = rtrim(substr($base, 0, 8), '-');
            $candidate = $suffixedBase !== '' ? $suffixedBase.'-'.$counter : 'tenant-'.$counter;
            $counter++;
        }

        // Release blocker wildcard DNS (worksheet deploy IDwebhost §4): tolak kode yang
        // bentrok dengan host eksplisit/reserved (resto, mm, www, cpanel, dll.).
        // Tanpa ini, nama usaha "resto" akan membuat tenant `resto` dan host
        // resto.<base_domain> akan menabrak host platform eksplisit.
        $this->tenantDomainSuggestionService->assertTenantCodeIsUsable($candidate);

        return $candidate;
    }

    private function generateRegistrationInvoiceNumber(Tenant $tenant): string
    {
        do {
            $candidate = 'INV-REG-'.strtoupper(Str::limit(Str::slug($tenant->code, ''), 6, '')).'-'.now()->format('YmdHis').'-'.random_int(100, 999);
        } while (PlatformPaymentTransaction::query()->where('invoice_number', $candidate)->exists());

        return $candidate;
    }

    private function sendTenantReadyEmailIfNeeded(Tenant $tenant, array $payload): void
    {
        $tenant = $tenant->fresh(['domains', 'provisionLogs']);

        if (!$tenant || !filled($tenant->owner_email)) {
            return;
        }

        if ($tenant->provisionLogs()->where('step', 'tenant_ready_email_sent')->where('status', 'completed')->exists()) {
            return;
        }

        $loginUrl = $tenant->domains->firstWhere('is_primary', true)?->domain
            ? 'http://'.$tenant->domains->firstWhere('is_primary', true)?->domain.'/login'
            : null;
        $forgotPasswordUrl = $tenant->domains->firstWhere('is_primary', true)?->domain
            ? 'http://'.$tenant->domains->firstWhere('is_primary', true)?->domain.'/forgot-password'
            : null;

        try {
            Mail::to($tenant->owner_email)->send(new PlatformTenantReady(
                ownerName: (string) ($payload['owner_name'] ?? $tenant->owner_name),
                companyName: $tenant->name,
                tenantCode: $tenant->code,
                username: (string) ($payload['owner_email'] ?? $tenant->owner_email),
                loginUrl: $loginUrl,
                forgotPasswordUrl: $forgotPasswordUrl,
                businessName: $payload['business_name'] ?? null,
                branchName: $payload['branch_name'] ?? null,
            ));

            TenantProvisionLog::query()->updateOrCreate([
                'tenant_id' => $tenant->getKey(),
                'step' => 'tenant_ready_email_sent',
            ], [
                'status' => 'completed',
                'message' => 'Email tenant siap login berhasil dikirim ke owner.',
                'payload' => [
                    'owner_email' => $tenant->owner_email,
                    'login_url' => $loginUrl,
                    'forgot_password_url' => $forgotPasswordUrl,
                ],
            ]);
        } catch (Throwable $exception) {
            Log::warning('Platform tenant ready email failed to send.', [
                'tenant_id' => $tenant->getKey(),
                'tenant_code' => $tenant->code,
                'owner_email' => $tenant->owner_email,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
