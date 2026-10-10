<?php

namespace App\Services\Platform;

use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;
use App\Models\Platform\TenantDatabaseConfig;
use App\Models\Platform\TenantDomain;
use App\Models\Platform\TenantProvisionLog;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use PDO;
use RuntimeException;
use Throwable;

class TenantProvisioningService
{
    public function __construct(
        private readonly TenantDomainSuggestionService $tenantDomainSuggestionService,
        private readonly CpanelMysqlProvisioner $cpanelMysqlProvisioner,
    ) {}

    public function provision(Tenant $tenant): Tenant
    {
        return DB::transaction(function () use ($tenant) {
            $tenant = Tenant::query()->with(['databaseConfigs', 'domains', 'subscriptions'])->findOrFail($tenant->getKey());

            $databaseConfig = $tenant->databaseConfigs->sortByDesc('id')->first()
                ?? TenantDatabaseConfig::query()->create([
                    'tenant_id' => $tenant->getKey(),
                    'connection_name' => 'tenant_'.$tenant->code,
                    'db_name' => 'tenant_'.$tenant->code,
                    'status' => 'pending',
                ]);

            [$databaseConfigPayload, $databaseCreated] = $this->resolveDatabaseConfig($tenant, $databaseConfig);

            $databaseConfig->forceFill($databaseConfigPayload)->save();

            if (! $tenant->domains->where('is_primary', true)->first()) {
                if ($domain = $this->tenantDomainSuggestionService->suggest($tenant->code)) {
                    TenantDomain::query()->create([
                        'tenant_id' => $tenant->getKey(),
                        'domain' => $domain,
                        'type' => 'subdomain',
                        'is_primary' => true,
                    ]);
                }
            }

            $tenant->forceFill([
                'status' => in_array($tenant->status, ['active', 'suspended', 'expired'], true) ? $tenant->status : 'trial',
                'onboarded_at' => $tenant->onboarded_at ?? now(),
            ])->save();

            Subscription::query()
                ->where('tenant_id', $tenant->getKey())
                ->whereNull('started_at')
                ->update([
                    'started_at' => now(),
                ]);

            TenantProvisionLog::query()->create([
                'tenant_id' => $tenant->getKey(),
                'step' => 'database_provisioning',
                'status' => 'completed',
                'message' => $databaseCreated
                    ? 'Database tenant berhasil dibuat dan siap dipakai.'
                    : 'Database tenant sudah ada dan diverifikasi ulang.',
                'payload' => [
                    'driver' => $databaseConfig->db_host === 'sqlite' ? 'sqlite' : 'mysql',
                    'database' => $databaseConfig->db_name,
                    'host' => $databaseConfig->db_host,
                    'port' => $databaseConfig->db_port,
                    'actor_id' => auth()->id(),
                    'actor_email' => auth()->user()?->email,
                ],
            ]);

            TenantProvisionLog::query()->create([
                'tenant_id' => $tenant->getKey(),
                'step' => 'tenant_ready',
                'status' => 'completed',
                'message' => 'Tenant siap untuk onboarding tenant app dan setup owner awal.',
                'payload' => [
                    'connection_name' => $databaseConfig->connection_name,
                    'database_status' => $databaseConfig->status,
                ],
            ]);

            return $tenant->fresh(['subscriptions.plan', 'domains', 'databaseConfigs', 'provisionLogs']);
        });
    }

    private function resolveDatabaseConfig(Tenant $tenant, TenantDatabaseConfig $databaseConfig): array
    {
        $driver = strtolower((string) config('platform.tenant_database_driver', 'mysql'));

        if ($driver === 'sqlite') {
            $directory = (string) config('platform.tenant_database_directory', storage_path('app/tenants/databases'));
            File::ensureDirectoryExists($directory);

            $databasePath = $directory.DIRECTORY_SEPARATOR.'tenant_'.$tenant->code.'.sqlite';
            $databaseCreated = false;

            if (! File::exists($databasePath)) {
                File::put($databasePath, '');
                $databaseCreated = true;
            }

            return [[
                'connection_name' => 'tenant_'.$tenant->code,
                'db_host' => 'sqlite',
                'db_port' => null,
                'db_name' => $databasePath,
                'db_username' => null,
                'db_password_encrypted' => null,
                'status' => 'provisioned',
            ], $databaseCreated];
        }

        if ($driver !== 'mysql') {
            throw new RuntimeException('Driver tenant database tidak didukung pada platform app.');
        }

        $configuredDatabaseName = trim((string) config('platform.tenant_mysql_database', ''));
        $databaseName = $configuredDatabaseName !== ''
            ? $configuredDatabaseName
            : ($databaseConfig->db_name ?: 'tenant_'.$tenant->code);
        $databaseName = preg_replace('/[^A-Za-z0-9_]+/', '_', $databaseName);
        $databaseName = trim((string) $databaseName, '_');
        $databaseName = $databaseName !== '' ? strtolower($databaseName) : 'tenant_'.$tenant->getKey();
        $databaseName = $this->applyMysqlDatabasePrefix($databaseName);

        $host = (string) config('platform.tenant_mysql_host', '127.0.0.1');
        $port = (string) config('platform.tenant_mysql_port', '3306');
        $adminDatabase = (string) config('platform.tenant_mysql_admin_database', 'mysql');
        $adminUsername = (string) config('platform.tenant_mysql_admin_username', 'root');
        $adminPassword = (string) config('platform.tenant_mysql_admin_password', '');

        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $adminDatabase);
        $pdo = new PDO($dsn, $adminUsername, $adminPassword, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        $statement = $pdo->prepare('SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = :database');
        $statement->execute(['database' => $databaseName]);
        $databaseExists = (bool) $statement->fetchColumn();
        $databaseCreated = false;

        if (! $databaseExists) {
            if ($this->shouldUseCpanelMysqlProvisioning()) {
                $this->cpanelMysqlProvisioner->createDatabase($databaseName);
                $databaseExists = true;
                $databaseCreated = true;
            } elseif (! $this->shouldManageMysqlDatabases()) {
                throw new RuntimeException("Database tenant {$databaseName} belum ada. Buat database ini dari cPanel atau aktifkan izin create database.");
            } else {
                $pdo->exec(sprintf('CREATE DATABASE `%s` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci', str_replace('`', '``', $databaseName)));
                $databaseExists = true;
                $databaseCreated = true;
            }
        }

        [$runtimeUsername, $runtimePassword] = $this->resolveMysqlRuntimeCredentials(
            $tenant,
            $databaseConfig,
            $pdo,
            $databaseName,
            $adminUsername,
            $adminPassword,
        );

        if ($this->shouldUseCpanelMysqlProvisioning()) {
            $this->grantRuntimeUserWithCpanel($databaseName, $runtimeUsername);
        }

        $statement = null;
        $pdo = null;

        if ($databaseCreated) {
            usleep(1500000);
        }

        $this->waitUntilMysqlDatabaseReachable(
            $host,
            $port,
            $databaseName,
            $runtimeUsername,
            $runtimePassword,
        );

        return [[
            'connection_name' => 'tenant_'.$tenant->code,
            'db_host' => $host,
            'db_port' => (int) $port,
            'db_name' => $databaseName,
            'db_username' => $runtimeUsername,
            'db_password_encrypted' => Crypt::encryptString($runtimePassword),
            'status' => 'provisioned',
        ], $databaseCreated];
    }

    private function applyMysqlDatabasePrefix(string $databaseName): string
    {
        $prefix = trim((string) config('platform.tenant_mysql_database_prefix', ''));
        $prefix = preg_replace('/[^A-Za-z0-9_]+/', '_', $prefix);
        $prefix = trim((string) $prefix, '_');

        if ($prefix === '') {
            return $databaseName;
        }

        $prefix .= '_';

        return str_starts_with($databaseName, $prefix)
            ? $databaseName
            : $prefix.$databaseName;
    }

    private function resolveMysqlRuntimeCredentials(
        Tenant $tenant,
        TenantDatabaseConfig $databaseConfig,
        PDO $pdo,
        string $databaseName,
        string $adminUsername,
        string $adminPassword,
    ): array {
        $existingUsername = trim((string) ($databaseConfig->db_username ?? ''));
        $existingPassword = $this->resolveStoredPassword($databaseConfig);

        if ($this->hasUsableMysqlCredentials($existingUsername, $existingPassword, $adminUsername, $adminPassword)) {
            if ($this->shouldManageMysqlUsers()) {
                $this->ensureMysqlUserAccess($pdo, $existingUsername, $existingPassword, $databaseName);
            }

            return [$existingUsername, $existingPassword];
        }

        $runtimeUsername = trim((string) config('platform.tenant_mysql_runtime_username', $adminUsername));
        $runtimePassword = (string) config('platform.tenant_mysql_runtime_password', $adminPassword);

        if ($this->hasUsableMysqlCredentials($runtimeUsername, $runtimePassword, $adminUsername, $adminPassword)) {
            if ($this->shouldManageMysqlUsers()) {
                $this->ensureMysqlUserAccess($pdo, $runtimeUsername, $runtimePassword, $databaseName);
            }

            return [$runtimeUsername, $runtimePassword];
        }

        if (! $this->shouldManageMysqlUsers()) {
            throw new RuntimeException('User database tenant belum dikonfigurasi. Isi PLATFORM_TENANT_DB_RUNTIME_USERNAME dan PLATFORM_TENANT_DB_RUNTIME_PASSWORD dengan user MySQL yang sudah dibuat di cPanel.');
        }

        $generatedUsername = $this->generateTenantMysqlUsername($tenant);
        $generatedPassword = $this->generateTenantMysqlPassword();

        $this->ensureMysqlUserAccess($pdo, $generatedUsername, $generatedPassword, $databaseName);

        return [$generatedUsername, $generatedPassword];
    }

    private function hasUsableMysqlCredentials(
        string $username,
        string $password,
        string $adminUsername,
        string $adminPassword,
    ): bool {
        if ($username === '' || $password === '') {
            return false;
        }

        if (! $this->shouldManageMysqlUsers()) {
            return true;
        }

        $reservedUsernames = array_filter([
            'root',
            trim($adminUsername),
        ]);

        if (in_array($username, $reservedUsernames, true)) {
            return false;
        }

        return $password !== $adminPassword;
    }

    private function shouldManageMysqlDatabases(): bool
    {
        return filter_var(config('platform.tenant_mysql_manage_databases', true), FILTER_VALIDATE_BOOLEAN);
    }

    private function shouldManageMysqlUsers(): bool
    {
        return filter_var(config('platform.tenant_mysql_manage_users', true), FILTER_VALIDATE_BOOLEAN);
    }

    private function shouldUseCpanelMysqlProvisioning(): bool
    {
        return $this->cpanelMysqlProvisioner->enabled();
    }

    private function grantRuntimeUserWithCpanel(string $databaseName, string $runtimeUsername): void
    {
        $cpanelDatabaseUser = trim((string) config('platform.cpanel_database_user', ''));
        $username = $cpanelDatabaseUser !== '' ? $cpanelDatabaseUser : $runtimeUsername;

        if ($username === '') {
            throw new RuntimeException('User database cPanel belum dikonfigurasi. Isi CPANEL_DATABASE_USER atau PLATFORM_TENANT_DB_RUNTIME_USERNAME.');
        }

        $this->cpanelMysqlProvisioner->grantAllPrivileges($databaseName, $username);
    }

    private function generateTenantMysqlUsername(Tenant $tenant): string
    {
        $base = preg_replace('/[^a-z0-9_]+/', '_', strtolower((string) $tenant->code));
        $base = trim((string) $base, '_');
        $base = $base !== '' ? $base : 'tenant_'.$tenant->getKey();
        $suffix = strtolower(Str::random(6));
        $username = 'tenant_'.$base;
        $username = substr($username, 0, 24);

        return $username.'_'.$suffix;
    }

    private function generateTenantMysqlPassword(): string
    {
        return bin2hex(random_bytes(24));
    }

    private function ensureMysqlUserAccess(PDO $pdo, string $username, string $password, string $databaseName): void
    {
        $quotedUsername = $this->quoteMysqlIdentifierValue($username);
        $quotedPassword = $this->quoteMysqlIdentifierValue($password);
        $quotedDatabaseName = str_replace('`', '``', $databaseName);

        foreach ($this->tenantMysqlGrantHosts() as $host) {
            $quotedHost = $this->quoteMysqlIdentifierValue($host);

            $pdo->exec("CREATE USER IF NOT EXISTS '{$quotedUsername}'@'{$quotedHost}' IDENTIFIED BY '{$quotedPassword}'");
            $pdo->exec("ALTER USER '{$quotedUsername}'@'{$quotedHost}' IDENTIFIED BY '{$quotedPassword}'");
            $pdo->exec("GRANT ALL PRIVILEGES ON `{$quotedDatabaseName}`.* TO '{$quotedUsername}'@'{$quotedHost}'");
        }

        $pdo->exec('FLUSH PRIVILEGES');
    }

    private function tenantMysqlGrantHosts(): array
    {
        $hosts = config('platform.tenant_mysql_runtime_grant_hosts', ['127.0.0.1', 'localhost']);

        if (! is_array($hosts)) {
            $hosts = ['127.0.0.1', 'localhost'];
        }

        return collect($hosts)
            ->filter(fn ($host) => is_string($host) && trim($host) !== '')
            ->map(fn (string $host) => trim($host))
            ->unique()
            ->values()
            ->all();
    }

    private function quoteMysqlIdentifierValue(string $value): string
    {
        return str_replace(['\\', '\''], ['\\\\', '\\\''], $value);
    }

    private function resolveStoredPassword(TenantDatabaseConfig $databaseConfig): string
    {
        $encryptedPassword = $databaseConfig->db_password_encrypted ?? null;

        if (! is_string($encryptedPassword) || $encryptedPassword === '') {
            return '';
        }

        try {
            return Crypt::decryptString($encryptedPassword);
        } catch (Throwable) {
            return (string) $encryptedPassword;
        }
    }

    private function waitUntilMysqlDatabaseReachable(
        string $host,
        string $port,
        string $databaseName,
        string $username,
        string $password,
    ): void {
        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $databaseName);
        $lastException = null;

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            try {
                $pdo = new PDO($dsn, $username, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT => 5,
                ]);

                $pdo->query('SELECT 1');

                return;
            } catch (Throwable $exception) {
                $lastException = $exception;

                if ($attempt < 5) {
                    usleep($attempt * 400000);
                }
            }
        }

        throw new RuntimeException('Database tenant baru dibuat tetapi belum bisa diakses stabil. Detail: '.($lastException?->getMessage() ?: 'unknown MySQL error'));
    }
}
