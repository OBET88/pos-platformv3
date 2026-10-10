<?php

namespace App\Services\Platform;

use App\Models\Platform\Tenant;
use App\Models\Platform\TenantProvisionLog;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;
use PDO;
use RuntimeException;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Throwable;
use Illuminate\Support\Facades\Auth;

class TenantAppBootstrapService
{
    private const REQUIRED_TENANT_TABLES = [
        'users',
        'businesses',
        'branches',
        'categories',
        'products',
        'transactions',
        'transaction_items',
        'stock_movements',
        'shifts',
        'roles',
        'permissions',
        'app_settings',
    ];
    private const PLATFORM_MIGRATION_EXCLUSIONS = [
        '2026_04_15_220000_create_platform_saas_tables.php',
    ];

    public function __construct(
        private readonly TenantProvisioningService $tenantProvisioningService,
    ) {
    }

    public function bootstrap(Tenant $tenant, array $payload): Tenant
    {
        $tenant = $tenant->fresh(['databaseConfigs']);
        $databaseConfig = $tenant?->databaseConfigs?->sortByDesc('id')->first();

        if (!$tenant || !$databaseConfig || !$databaseConfig->db_name) {
            throw new RuntimeException('Tenant belum punya database config yang siap dibootstrap.');
        }

        $tenantAppPath = $this->resolveTenantAppPath((string) config('platform.tenant_app_path'));
        $phpBinary = $this->resolvePhpBinary((string) config('platform.tenant_app_php_binary', PHP_BINARY));

        if ($tenantAppPath === '' || !is_dir($tenantAppPath)) {
            throw new RuntimeException('Path tenant app belum valid. Atur PLATFORM_TENANT_APP_PATH lebih dulu.');
        }

        $driver = $databaseConfig->db_host === 'sqlite' ? 'sqlite' : 'mysql';
        $migrationSnapshot = $this->createTenantMigrationSnapshot($tenantAppPath, $tenant);
        [$tenant, $databaseConfig, $connectivityCheck, $childProcessCheck, $recoveryActions] = $this->prepareBootstrapTarget(
            $tenant,
            $databaseConfig,
            $driver,
            $phpBinary,
            $tenantAppPath,
        );

        $processCommand = $this->buildProcessCommand($databaseConfig, $payload, $phpBinary, $driver, $migrationSnapshot);

        [$process, $attempts] = $this->runBootstrapProcess($processCommand, $tenantAppPath, $databaseConfig, $driver);
        $bootstrapVerified = $this->verifyBootstrapOutcome($databaseConfig, $driver, (string) $payload['owner_email']);

        if (
            !$process->isSuccessful()
            && !$bootstrapVerified
            && $driver === 'mysql'
            && (
                $this->shouldAttemptRecoveryAfterBootstrapFailure($process)
                || !$this->hasBootstrapProgress($databaseConfig, $driver)
            )
        ) {
            [$tenant, $databaseConfig, $connectivityCheck, $childProcessCheck, $recoveryAction] = $this->recoverTenantDatabase(
                $tenant,
                $driver,
                'post_bootstrap_failure',
                $process->getErrorOutput() !== '' ? trim($process->getErrorOutput()) : trim($process->getOutput()),
                $phpBinary,
                $tenantAppPath,
            );

            $recoveryActions[] = $recoveryAction;
            $processCommand = $this->buildProcessCommand($databaseConfig, $payload, $phpBinary, $driver, $migrationSnapshot);
            [$process, $attempts] = $this->runBootstrapProcess($processCommand, $tenantAppPath, $databaseConfig, $driver);
            $bootstrapVerified = $this->verifyBootstrapOutcome($databaseConfig, $driver, (string) $payload['owner_email']);
        }

        $bootstrapSucceeded = $process->isSuccessful() || $bootstrapVerified;

        if ($bootstrapSucceeded) {
            $this->syncOwnerEmailVerification($databaseConfig, $driver, $payload);
        }

        $migrationSnapshotCleanup = $bootstrapSucceeded
            ? $this->cleanupTenantMigrationSnapshot($migrationSnapshot)
            : [
                'status' => 'retained_for_debugging',
                'path' => $migrationSnapshot['path'] ?? null,
            ];

        TenantProvisionLog::query()->create([
            'tenant_id' => $tenant->getKey(),
            'step' => 'tenant_app_bootstrap',
            'status' => $bootstrapSucceeded ? 'completed' : 'failed',
            'message' => $bootstrapSucceeded
                ? ($process->isSuccessful()
                    ? 'Tenant app berhasil dibootstrap dan owner awal sudah dibuat.'
                    : 'Tenant app berhasil diverifikasi selesai walau process bootstrap sempat mengembalikan error.')
                : 'Bootstrap tenant app gagal dijalankan.',
            'payload' => [
                'driver' => $driver,
                'command' => $process->getCommandLine(),
                'attempts' => $attempts,
                'bootstrap_verified' => $bootstrapVerified,
                'connectivity_check' => $connectivityCheck,
                'child_process_check' => $childProcessCheck,
                'recovery_actions' => $recoveryActions,
                'migration_snapshot' => $migrationSnapshot,
                'migration_snapshot_cleanup' => $migrationSnapshotCleanup,
                'output' => trim($process->getOutput()),
                'error_output' => trim($process->getErrorOutput()),
                'actor_id' => Auth::id(),
                'actor_email' => Auth::user()?->email,
            ],
        ]);

        if (!$bootstrapSucceeded) {
            throw new RuntimeException($this->resolveBootstrapFailureMessage($process, $connectivityCheck, $attempts));
        }

        return $tenant->fresh(['subscriptions.plan', 'domains', 'databaseConfigs', 'provisionLogs']);
    }

    public function startBootstrapInBackground(Tenant $tenant, array $payload): void
    {
        $tenant = $tenant->fresh(['databaseConfigs']);
        $databaseConfig = $tenant?->databaseConfigs?->sortByDesc('id')->first();

        if (!$tenant || !$databaseConfig || !$databaseConfig->db_name) {
            throw new RuntimeException('Tenant belum punya database config yang siap dibootstrap.');
        }

        $tenantAppPath = $this->resolveTenantAppPath((string) config('platform.tenant_app_path'));
        $phpBinary = $this->resolvePhpBinary((string) config('platform.tenant_app_php_binary', PHP_BINARY));

        if ($tenantAppPath === '' || !is_dir($tenantAppPath)) {
            throw new RuntimeException('Path tenant app belum valid. Atur PLATFORM_TENANT_APP_PATH lebih dulu.');
        }

        $driver = $databaseConfig->db_host === 'sqlite' ? 'sqlite' : 'mysql';
        $migrationSnapshot = $this->createTenantMigrationSnapshot($tenantAppPath, $tenant);
        $processCommand = $this->buildProcessCommand($databaseConfig, $payload, $phpBinary, $driver, $migrationSnapshot);

        TenantProvisionLog::query()->updateOrCreate([
            'tenant_id' => $tenant->getKey(),
            'step' => 'tenant_app_bootstrap',
        ], [
            'status' => 'pending',
            'message' => 'Bootstrap tenant app sedang dijalankan di background.',
            'payload' => [
                'driver' => $driver,
                'command' => implode(' ', array_map(
                    fn (string $argument) => DIRECTORY_SEPARATOR === '\\'
                        ? $this->quoteForCmd($this->trimWrappingQuotes($argument))
                        : $argument,
                    $processCommand
                )),
                'migration_snapshot' => $migrationSnapshot,
            ],
        ]);

        $this->launchBackgroundProcess($processCommand, $tenantAppPath);
    }

    public function finalizeBootstrapIfReady(Tenant $tenant, array $payload): bool
    {
        $tenant = $tenant->fresh(['databaseConfigs', 'subscriptions.plan', 'domains', 'provisionLogs']);
        $databaseConfig = $tenant?->databaseConfigs?->sortByDesc('id')->first();

        if (!$tenant || !$databaseConfig || !$databaseConfig->db_name) {
            return false;
        }

        $driver = $databaseConfig->db_host === 'sqlite' ? 'sqlite' : 'mysql';
        $ownerEmail = (string) ($payload['owner_email'] ?? $tenant->owner_email);

        if (!$this->verifyBootstrapOutcome($databaseConfig, $driver, $ownerEmail)) {
            return false;
        }

        $this->syncOwnerEmailVerification($databaseConfig, $driver, $payload);

        TenantProvisionLog::query()->updateOrCreate([
            'tenant_id' => $tenant->getKey(),
            'step' => 'tenant_app_bootstrap',
        ], [
            'status' => 'completed',
            'message' => 'Tenant app berhasil dibootstrap dan owner awal sudah dibuat.',
            'payload' => [
                'driver' => $driver,
                'bootstrap_verified' => true,
            ],
        ]);

        TenantProvisionLog::query()->updateOrCreate([
            'tenant_id' => $tenant->getKey(),
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

        return true;
    }

    private function prepareBootstrapTarget(
        Tenant $tenant,
        object $databaseConfig,
        string $driver,
        string $phpBinary,
        string $tenantAppPath,
    ): array {
        $recoveryActions = [];

        try {
            $connectivityCheck = $this->assertTenantDatabaseReachable($databaseConfig, $driver);
        } catch (RuntimeException $exception) {
            if (!$this->shouldAttemptProvisionRecovery($driver, $exception->getMessage())) {
                throw $exception;
            }

            [$tenant, $databaseConfig, $connectivityCheck, $childProcessCheck, $recoveryAction] = $this->recoverTenantDatabase(
                $tenant,
                $driver,
                'preflight_connectivity',
                $exception->getMessage(),
                $phpBinary,
                $tenantAppPath,
            );

            $recoveryActions[] = $recoveryAction;

            return [$tenant, $databaseConfig, $connectivityCheck, $childProcessCheck, $recoveryActions];
        }

        $childProcessCheck = null;

        if ($driver === 'mysql') {
            $childProcessCheck = $this->probeTenantDatabaseViaChildProcess($databaseConfig, $phpBinary, $tenantAppPath);

            if (($childProcessCheck['status'] ?? null) !== 'ok') {
                $childProcessCheck = $this->waitForStableChildProcessConnectivity(
                    $databaseConfig,
                    $phpBinary,
                    $tenantAppPath,
                    $childProcessCheck,
                );
            }
        }

        return [$tenant, $databaseConfig, $connectivityCheck, $childProcessCheck, $recoveryActions];
    }

    private function buildProcessCommand(
        object $databaseConfig,
        array $payload,
        string $phpBinary,
        string $driver,
        array $migrationSnapshot,
    ): array
    {
        $processCommand = [
            $phpBinary,
            'artisan',
            'tenant:bootstrap-database',
            $databaseConfig->db_name,
            (string) $payload['owner_name'],
            (string) $payload['owner_email'],
            (string) $payload['owner_password'],
            '--driver='.$driver,
            '--business='.(string) ($payload['business_name'] ?? 'Usaha Utama'),
            '--branch='.(string) ($payload['branch_name'] ?? 'Cabang 1'),
            '--owner-role='.(string) ($payload['owner_role'] ?? 'owner'),
            '--migration-path='.(string) ($migrationSnapshot['path'] ?? ''),
        ];

        if ($driver === 'mysql') {
            $processCommand[] = '--db-host='.(string) ($databaseConfig->db_host ?: config('platform.tenant_mysql_host', '127.0.0.1'));
            $processCommand[] = '--db-port='.(string) ($databaseConfig->db_port ?: config('platform.tenant_mysql_port', '3306'));
            $processCommand[] = '--db-username='.(string) ($databaseConfig->db_username ?: config('platform.tenant_mysql_runtime_username', 'root'));
            $processCommand[] = '--db-password='.$this->resolveDatabasePassword($databaseConfig);
        }

        return $processCommand;
    }

    private function createTenantMigrationSnapshot(string $tenantAppPath, Tenant $tenant): array
    {
        $sourceDirectory = $tenantAppPath.DIRECTORY_SEPARATOR.'database'.DIRECTORY_SEPARATOR.'migrations';

        if (!is_dir($sourceDirectory)) {
            throw new RuntimeException('Folder migration tenant app tidak ditemukan di '.$sourceDirectory);
        }

        $snapshotDirectory = storage_path('app/tenant-bootstrap/'.$tenant->code.'-'.now()->format('YmdHisv'));
        $snapshotMigrationDirectory = $snapshotDirectory.DIRECTORY_SEPARATOR.'migrations';
        File::ensureDirectoryExists($snapshotMigrationDirectory);

        $files = collect(File::files($sourceDirectory))
            ->filter(fn ($file) => !in_array($file->getFilename(), self::PLATFORM_MIGRATION_EXCLUSIONS, true))
            ->sortBy(fn ($file) => $file->getFilename())
            ->values();

        if ($files->isEmpty()) {
            throw new RuntimeException('Tidak ada migration tenant yang berhasil disalin dari tenant app.');
        }

        $copiedFiles = [];

        foreach ($files as $file) {
            $targetPath = $snapshotMigrationDirectory.DIRECTORY_SEPARATOR.$file->getFilename();
            File::copy($file->getRealPath(), $targetPath);
            $copiedFiles[] = $file->getFilename();
        }

        return [
            'path' => $snapshotMigrationDirectory,
            'file_count' => count($copiedFiles),
            'files' => $copiedFiles,
        ];
    }

    private function cleanupTenantMigrationSnapshot(array $migrationSnapshot): array
    {
        $snapshotMigrationDirectory = (string) ($migrationSnapshot['path'] ?? '');

        if ($snapshotMigrationDirectory === '') {
            return [
                'status' => 'skipped',
                'reason' => 'empty_path',
            ];
        }

        $snapshotDirectory = dirname($snapshotMigrationDirectory);

        if (!is_dir($snapshotDirectory)) {
            return [
                'status' => 'skipped',
                'reason' => 'missing_directory',
                'path' => $snapshotDirectory,
            ];
        }

        File::deleteDirectory($snapshotDirectory);

        return [
            'status' => 'deleted',
            'path' => $snapshotDirectory,
        ];
    }

    private function resolveTenantAppPath(string $path): string
    {
        $path = trim($path);

        if ($path === '') {
            return '';
        }

        if ($this->isAbsolutePath($path)) {
            return realpath($path) ?: $path;
        }

        $resolvedPath = base_path($path);

        return realpath($resolvedPath) ?: $resolvedPath;
    }

    private function resolvePhpBinary(string $configuredBinary): string
    {
        $configuredBinary = $this->trimWrappingQuotes(trim($configuredBinary));

        if ($configuredBinary !== '') {
            $resolvedConfiguredBinary = $this->trimWrappingQuotes(realpath($configuredBinary) ?: $configuredBinary);

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

        if (DIRECTORY_SEPARATOR === '\\') {
            $cliBinary = $this->resolveDefaultCliPhpBinary();

            if ($cliBinary !== null) {
                return $cliBinary;
            }
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
            $normalizedRoot = $this->trimWrappingQuotes((string) $root);

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

    private function trimWrappingQuotes(string $value): string
    {
        return trim($value, " \t\n\r\0\x0B\"'");
    }

    private function isAbsolutePath(string $path): bool
    {
        return str_starts_with($path, DIRECTORY_SEPARATOR)
            || preg_match('/^[A-Za-z]:[\\\\\\/]/', $path) === 1;
    }

    private function assertTenantDatabaseReachable(object $databaseConfig, string $driver): array
    {
        if ($driver === 'sqlite') {
            $databasePath = (string) $databaseConfig->db_name;

            if ($databasePath === '' || !is_file($databasePath)) {
                throw new RuntimeException('File database SQLite tenant belum ditemukan. Jalankan provisioning lebih dulu.');
            }

            return [
                'status' => 'ok',
                'mode' => 'sqlite_file',
                'database' => $databasePath,
            ];
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            (string) ($databaseConfig->db_host ?: config('platform.tenant_mysql_host', '127.0.0.1')),
            (string) ($databaseConfig->db_port ?: config('platform.tenant_mysql_port', '3306')),
            (string) $databaseConfig->db_name,
        );

        $username = (string) ($databaseConfig->db_username ?: config('platform.tenant_mysql_runtime_username', 'root'));
        $password = $this->resolveDatabasePassword($databaseConfig);
        $lastExceptionMessage = null;

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            try {
                $pdo = new PDO($dsn, $username, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT => 5,
                ]);

                $pdo->query('SELECT 1');

                return [
                    'status' => 'ok',
                    'mode' => 'mysql_pdo',
                    'attempt' => $attempt,
                    'dsn' => $dsn,
                ];
            } catch (Throwable $exception) {
                $lastExceptionMessage = $exception->getMessage();

                if ($attempt < 5) {
                    usleep($attempt * 400000);
                }
            }
        }

        throw new RuntimeException('Database tenant belum bisa diakses untuk bootstrap. Detail: '.($lastExceptionMessage ?: 'unknown connection error'));
    }

    private function recoverTenantDatabase(
        Tenant $tenant,
        string $driver,
        string $phase,
        string $reason,
        string $phpBinary,
        string $tenantAppPath,
    ): array {
        $tenant = $this->tenantProvisioningService->provision($tenant);
        $databaseConfig = $tenant->databaseConfigs->sortByDesc('id')->first();

        if (!$databaseConfig || !$databaseConfig->db_name) {
            throw new RuntimeException('Recovery database tenant gagal karena database config belum tersedia setelah reprovision.');
        }

        $connectivityCheck = $this->assertTenantDatabaseReachable($databaseConfig, $driver);
        $childProcessCheck = $driver === 'mysql'
            ? $this->probeTenantDatabaseViaChildProcess($databaseConfig, $phpBinary, $tenantAppPath)
            : null;

        return [
            $tenant,
            $databaseConfig,
            $connectivityCheck,
            $childProcessCheck,
            [
                'phase' => $phase,
                'reason' => $reason,
                'database' => $databaseConfig->db_name,
                'status' => 'reprovisioned',
            ],
        ];
    }

    private function probeTenantDatabaseViaChildProcess(object $databaseConfig, string $phpBinary, string $tenantAppPath): array
    {
        $host = (string) ($databaseConfig->db_host ?: config('platform.tenant_mysql_host', '127.0.0.1'));
        $port = (string) ($databaseConfig->db_port ?: config('platform.tenant_mysql_port', '3306'));
        $database = (string) $databaseConfig->db_name;
        $username = (string) ($databaseConfig->db_username ?: config('platform.tenant_mysql_runtime_username', 'root'));
        $password = $this->resolveDatabasePassword($databaseConfig);

        $probeScript = <<<'PHP'
$host = $argv[1] ?? '127.0.0.1';
$port = $argv[2] ?? '3306';
$database = $argv[3] ?? '';
$username = $argv[4] ?? 'root';
$password = $argv[5] ?? '';

try {
    $pdo = new PDO(
        sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $database),
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5,
        ]
    );

    $pdo->query('SELECT 1');
    echo 'ok';
    exit(0);
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage());
    exit(1);
}
PHP;

        $lastErrorOutput = null;
        $successfulChecks = 0;

        for ($attempt = 1; $attempt <= 12; $attempt++) {
            $process = new Process([
                $phpBinary,
                '-r',
                $probeScript,
                $host,
                $port,
                $database,
                $username,
                $password,
            ], $tenantAppPath, [
                'DB_CONNECTION' => false,
                'DB_DATABASE' => false,
                'DB_URL' => false,
                'DB_HOST' => false,
                'DB_PORT' => false,
                'DB_USERNAME' => false,
                'DB_PASSWORD' => false,
            ]);

            $process->setTimeout(20);
            $process->run();

            if ($process->isSuccessful()) {
                $successfulChecks++;

                if ($successfulChecks >= 2) {
                    return [
                        'status' => 'ok',
                        'attempt' => $attempt,
                        'checks' => $successfulChecks,
                    ];
                }

                usleep(800000);

                continue;
            }

            $successfulChecks = 0;
            $lastErrorOutput = trim($process->getErrorOutput()) ?: trim($process->getOutput());

            if ($attempt < 12) {
                usleep($this->childProcessProbeDelay($attempt));
            }
        }

        return [
            'status' => 'warning',
            'message' => 'Database tenant lolos preflight internal tetapi belum stabil untuk proses bootstrap lintas-app.',
            'detail' => $lastErrorOutput ?: 'unknown child-process connection error',
        ];
    }

    private function childProcessProbeDelay(int $attempt): int
    {
        return match ($attempt) {
            1 => 1200000,
            2 => 1800000,
            3 => 2600000,
            4 => 3600000,
            5 => 4800000,
            default => 6000000,
        };
    }

    private function runBootstrapProcess(array $processCommand, string $tenantAppPath, object $databaseConfig, string $driver): array
    {
        $attempts = 0;
        $lastProcess = null;

        do {
            $attempts++;
            $abortedForNoProgress = false;
            $lastProcess = $this->createBootstrapProcess($processCommand, $tenantAppPath);

            $lastProcess->setTimeout(420);

            try {
                $lastProcess->start();
                $startedAt = microtime(true);
                $initialTableCount = $driver === 'mysql' ? $this->countTenantTables($databaseConfig) : 0;

                while ($lastProcess->isRunning()) {
                    usleep(3000000);

                    if (
                        $driver === 'mysql'
                        && (microtime(true) - $startedAt) >= 120
                        && $this->countTenantTables($databaseConfig) <= $initialTableCount
                    ) {
                        $abortedForNoProgress = true;
                        $lastProcess->stop(3);
                        break;
                    }
                }
            } catch (ProcessTimedOutException $exception) {
                if ($attempts >= 5) {
                    return [$lastProcess, $attempts];
                }

                usleep($attempts * 900000);

                continue;
            }

            if ($abortedForNoProgress) {
                return [$lastProcess, $attempts];
            }

            if ($lastProcess->isSuccessful()) {
                return [$lastProcess, $attempts];
            }

            if ($this->shouldRetryViaWindowsCmd($lastProcess, $attempts)) {
                $lastProcess = $this->runBootstrapProcessViaWindowsCmd($processCommand, $tenantAppPath);

                if ($lastProcess->isSuccessful()) {
                    return [$lastProcess, $attempts];
                }
            }

            if (!$this->shouldRetryBootstrap($lastProcess, $attempts)) {
                return [$lastProcess, $attempts];
            }

            usleep($attempts * 700000);
        } while ($attempts < 5);

        return [$lastProcess, $attempts];
    }

    private function countTenantTables(object $databaseConfig): int
    {
        $host = (string) ($databaseConfig->db_host ?: config('platform.tenant_mysql_host', '127.0.0.1'));
        $port = (string) ($databaseConfig->db_port ?: config('platform.tenant_mysql_port', '3306'));
        $database = (string) $databaseConfig->db_name;
        $username = (string) ($databaseConfig->db_username ?: config('platform.tenant_mysql_runtime_username', 'root'));
        $password = $this->resolveDatabasePassword($databaseConfig);

        try {
            $pdo = new PDO(
                sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $database),
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT => 5,
                ]
            );

            return (int) $pdo->query(
                "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = ".$pdo->quote($database)
            )->fetchColumn();
        } catch (Throwable) {
            return 0;
        }
    }

    private function hasBootstrapProgress(object $databaseConfig, string $driver): bool
    {
        if ($driver !== 'mysql') {
            return false;
        }

        return $this->countTenantTables($databaseConfig) > 0;
    }

    private function shouldRetryBootstrap(Process $process, int $attempts): bool
    {
        if ($attempts >= 5) {
            return false;
        }

        $combinedOutput = strtolower(trim($process->getErrorOutput()."\n".$process->getOutput()));

        return str_contains($combinedOutput, 'sqlstate[hy000] [2002]')
            || str_contains($combinedOutput, 'unknown error while connecting')
            || str_contains($combinedOutput, 'connection refused')
            || str_contains($combinedOutput, 'base table or view already exists')
            || str_contains($combinedOutput, 'duplicate column name')
            || str_contains($combinedOutput, 'duplicate key name')
            || str_contains($combinedOutput, 'duplicate entry');
    }

    private function shouldRetryViaWindowsCmd(Process $process, int $attempts): bool
    {
        if (DIRECTORY_SEPARATOR !== '\\' || $attempts > 1) {
            return false;
        }

        $combinedOutput = strtolower(trim($process->getErrorOutput()."\n".$process->getOutput()));

        return str_contains($combinedOutput, 'sqlstate[hy000] [2002]')
            || str_contains($combinedOutput, 'unknown error while connecting');
    }

    private function shouldAttemptProvisionRecovery(string $driver, string $message): bool
    {
        if ($driver !== 'mysql') {
            return false;
        }

        $message = strtolower($message);

        return str_contains($message, 'unknown database')
            || str_contains($message, 'sqlstate[hy000] [1049]')
            || str_contains($message, 'sqlstate[hy000] [2002]')
            || str_contains($message, 'unknown error while connecting')
            || str_contains($message, 'connection refused');
    }

    private function shouldAttemptRecoveryAfterBootstrapFailure(Process $process): bool
    {
        $combinedOutput = strtolower(trim($process->getErrorOutput()."\n".$process->getOutput()));

        return str_contains($combinedOutput, 'unknown database')
            || str_contains($combinedOutput, 'sqlstate[hy000] [1049]')
            || str_contains($combinedOutput, 'sqlstate[hy000] [2002]')
            || str_contains($combinedOutput, 'unknown error while connecting')
            || str_contains($combinedOutput, 'connection refused');
    }

    private function runBootstrapProcessViaWindowsCmd(array $processCommand, string $tenantAppPath): Process
    {
        $process = new Process($processCommand, $tenantAppPath, $this->tenantAppProcessEnvironment());

        $process->setTimeout(180);
        $process->run();

        return $process;
    }

    private function launchBackgroundProcess(array $processCommand, string $tenantAppPath): void
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            $commandLine = $this->buildWindowsBackgroundCommandLine($processCommand, $tenantAppPath);
            @pclose(@popen('cmd /c '.$commandLine, 'r'));

            return;
        }

        $process = new Process($processCommand, $tenantAppPath, $this->tenantAppProcessEnvironment());

        $process->setTimeout(null);
        $process->start();
    }

    private function createBootstrapProcess(array $processCommand, string $tenantAppPath): Process
    {
        $process = new Process($processCommand, $tenantAppPath, $this->tenantAppProcessEnvironment());

        $process->setTimeout(420);

        return $process;
    }

    private function buildWindowsCommandLine(array $processCommand): string
    {
        return implode(' ', array_map(
            fn (string $argument) => $this->quoteForCmd($this->trimWrappingQuotes($argument)),
            $processCommand
        ));
    }

    private function buildWindowsBackgroundCommandLine(array $processCommand, string $tenantAppPath): string
    {
        $phpBinary = (string) $processCommand[0];
        $commandLine = implode(' ', array_map(
            fn (string $argument) => $this->quoteForCmd($this->trimWrappingQuotes($argument)),
            array_slice($processCommand, 1)
        ));

        return sprintf(
            'cd /d %s && start "" /B %s %s > NUL 2>&1',
            $this->quoteForCmd($tenantAppPath),
            $this->quoteForCmd($phpBinary),
            $commandLine,
        );
    }

    private function tenantAppProcessEnvironment(): array
    {
        return [
            'CACHE_STORE' => 'array',
            'SESSION_DRIVER' => 'file',
        ];
    }

    private function waitForStableChildProcessConnectivity(
        object $databaseConfig,
        string $phpBinary,
        string $tenantAppPath,
        array $initialResult,
    ): array {
        $lastResult = $initialResult;

        for ($attempt = 1; $attempt <= 3; $attempt++) {
            usleep($attempt * 1500000);

            $lastResult = $this->probeTenantDatabaseViaChildProcess($databaseConfig, $phpBinary, $tenantAppPath);

            if (($lastResult['status'] ?? null) === 'ok') {
                $lastResult['stabilized_after_retry'] = true;

                return $lastResult;
            }
        }

        $lastResult['stabilized_after_retry'] = false;

        return $lastResult;
    }

    private function quoteForCmd(string $value): string
    {
        $value = $this->trimWrappingQuotes($value);

        return '"'.str_replace('"', '""', $value).'"';
    }

    private function verifyBootstrapOutcome(object $databaseConfig, string $driver, string $ownerEmail): bool
    {
        try {
            $pdo = $this->connectToTenantDatabase($databaseConfig, $driver);

            foreach (self::REQUIRED_TENANT_TABLES as $table) {
                if (!$this->tableExists($pdo, $table, $driver)) {
                    return false;
                }
            }

            $statement = $pdo->prepare('SELECT email FROM users WHERE email = :email LIMIT 1');
            $statement->execute(['email' => $ownerEmail]);

            return (string) $statement->fetchColumn() === $ownerEmail;
        } catch (Throwable) {
            return false;
        }
    }

    private function syncOwnerEmailVerification(object $databaseConfig, string $driver, array $payload): void
    {
        $verifiedAt = $payload['owner_email_verified_at'] ?? null;
        $ownerEmail = (string) ($payload['owner_email'] ?? '');

        if ($verifiedAt === null || $verifiedAt === '' || $ownerEmail === '') {
            return;
        }

        try {
            $pdo = $this->connectToTenantDatabase($databaseConfig, $driver);
            $statement = $pdo->prepare('UPDATE users SET email_verified_at = :verified_at WHERE email = :email');
            $statement->execute([
                'verified_at' => (string) $verifiedAt,
                'email' => $ownerEmail,
            ]);
        } catch (Throwable) {
            // Keep bootstrap resilient; failed verification sync should not block tenant provisioning.
        }
    }

    private function connectToTenantDatabase(object $databaseConfig, string $driver): PDO
    {
        if ($driver === 'sqlite') {
            $databasePath = (string) $databaseConfig->db_name;

            if ($databasePath === '' || !is_file($databasePath)) {
                throw new RuntimeException('File database SQLite tenant belum ditemukan. Jalankan provisioning lebih dulu.');
            }

            return new PDO('sqlite:'.$databasePath, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
        }

        return new PDO(
            sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                (string) ($databaseConfig->db_host ?: config('platform.tenant_mysql_host', '127.0.0.1')),
                (string) ($databaseConfig->db_port ?: config('platform.tenant_mysql_port', '3306')),
                (string) $databaseConfig->db_name,
            ),
            (string) ($databaseConfig->db_username ?: config('platform.tenant_mysql_runtime_username', 'root')),
            $this->resolveDatabasePassword($databaseConfig),
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }

    private function tableExists(PDO $pdo, string $table, string $driver): bool
    {
        if ($driver === 'sqlite') {
            $statement = $pdo->prepare("SELECT name FROM sqlite_master WHERE type = 'table' AND name = :table LIMIT 1");
            $statement->execute(['table' => $table]);

            return (string) $statement->fetchColumn() === $table;
        }

        $statement = $pdo->prepare('SELECT TABLE_NAME FROM information_schema.tables WHERE table_schema = database() AND table_name = :table LIMIT 1');
        $statement->execute(['table' => $table]);

        return (string) $statement->fetchColumn() === $table;
    }

    private function resolveBootstrapFailureMessage(Process $process, array $connectivityCheck, int $attempts): string
    {
        $errorOutput = trim($process->getErrorOutput());
        $standardOutput = trim($process->getOutput());
        $combinedOutput = trim($errorOutput."\n".$standardOutput);
        $combinedOutputLower = strtolower($combinedOutput);

        if (
            ($connectivityCheck['status'] ?? null) === 'ok'
            && (
                str_contains($combinedOutputLower, 'sqlstate[hy000] [2002]')
                || str_contains($combinedOutputLower, 'unknown error while connecting')
            )
        ) {
            return 'Koneksi tenant DB lolos saat preflight, tetapi command bootstrap tenant app gagal saat dijalankan'.($attempts > 1 ? ' setelah retry otomatis' : '').'. Biasanya ini menandakan migration atau bootstrap command tenant app masih bermasalah. Cek Provision Log untuk output teknis detail.';
        }

        if (
            str_contains($combinedOutputLower, 'base table or view already exists')
            || str_contains($combinedOutputLower, 'duplicate column name')
            || str_contains($combinedOutputLower, 'duplicate key name')
            || str_contains($combinedOutputLower, 'duplicate entry')
        ) {
            return 'Bootstrap tenant app bentrok dengan state database yang sudah terlanjur terbentuk sebagian. Sistem sudah mencoba retry otomatis, tetapi tenant app masih butuh recovery bootstrap lanjutan. Cek Provision Log untuk output teknis detail.';
        }

        return $errorOutput !== ''
            ? $errorOutput
            : ($standardOutput !== '' ? $standardOutput : 'Bootstrap tenant app gagal tanpa pesan error eksplisit.');
    }

    private function resolveDatabasePassword(object $databaseConfig): string
    {
        $encryptedPassword = $databaseConfig->db_password_encrypted ?? null;

        if (!is_string($encryptedPassword) || $encryptedPassword === '') {
            return (string) config('platform.tenant_mysql_runtime_password', '');
        }

        try {
            return Crypt::decryptString($encryptedPassword);
        } catch (Throwable) {
            // Keep backward compatibility if older data was stored as plain text.
            return (string) $encryptedPassword;
        }
    }
}
