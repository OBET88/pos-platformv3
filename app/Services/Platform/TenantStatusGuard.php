<?php

namespace App\Services\Platform;

use App\Models\Platform\Tenant;
use Illuminate\Support\Facades\Crypt;
use PDO;
use RuntimeException;
use Throwable;

class TenantStatusGuard
{
    public function ensureCanChangeStatus(Tenant $tenant, string $targetStatus): void
    {
        if (!$this->isDeactivationStatus($targetStatus)) {
            return;
        }

        $openShiftCount = $this->countOpenShifts($tenant);

        if ($openShiftCount > 0) {
            $statusLabel = match ($targetStatus) {
                'suspended' => 'ditangguhkan',
                'expired' => 'diubah ke expired',
                default => 'dinonaktifkan',
            };

            throw new RuntimeException(
                'Tenant tidak bisa '.$statusLabel.' karena masih ada '.$openShiftCount.' shift yang terbuka. Tutup semua shift terlebih dahulu.'
            );
        }
    }

    private function isDeactivationStatus(string $targetStatus): bool
    {
        return in_array($targetStatus, ['suspended', 'expired'], true);
    }

    private function countOpenShifts(Tenant $tenant): int
    {
        $tenant = $tenant->fresh(['databaseConfigs']);
        $databaseConfig = $tenant?->databaseConfigs?->sortByDesc('id')->first();

        if (!$tenant || !$databaseConfig || !$databaseConfig->db_name) {
            return 0;
        }

        $driver = $databaseConfig->db_host === 'sqlite' ? 'sqlite' : 'mysql';

        try {
            $pdo = $this->connectToTenantDatabase($databaseConfig, $driver);

            if (!$this->tableExists($pdo, 'shifts', $driver)) {
                return 0;
            }

            $columns = $this->shiftColumns($pdo, $driver);

            foreach ($this->candidateOpenShiftConditions($columns) as $condition) {
                $count = (int) $pdo->query('SELECT COUNT(*) FROM shifts WHERE '.$condition)->fetchColumn();

                if ($count > 0) {
                    return $count;
                }
            }

            return 0;
        } catch (Throwable $exception) {
            throw new RuntimeException('Status tenant belum bisa diubah karena data shift tenant tidak dapat diverifikasi. Coba lagi setelah koneksi tenant stabil.');
        }
    }

    private function candidateOpenShiftConditions(array $columns): array
    {
        $conditions = [];

        if (in_array('closed_at', $columns, true)) {
            $conditions[] = 'closed_at IS NULL';
        }

        if (in_array('ended_at', $columns, true)) {
            $conditions[] = 'ended_at IS NULL';
        }

        if (in_array('end_time', $columns, true)) {
            $conditions[] = 'end_time IS NULL';
        }

        if (in_array('is_closed', $columns, true)) {
            $conditions[] = 'is_closed = 0';
        }

        if (in_array('status', $columns, true)) {
            $conditions[] = "LOWER(status) IN ('open', 'opened', 'active', 'in_progress')";
        }

        return array_values(array_unique($conditions));
    }

    private function shiftColumns(PDO $pdo, string $driver): array
    {
        if ($driver === 'sqlite') {
            $statement = $pdo->query("PRAGMA table_info('shifts')");

            return collect($statement->fetchAll(PDO::FETCH_ASSOC))
                ->pluck('name')
                ->filter()
                ->values()
                ->all();
        }

        $statement = $pdo->query('SHOW COLUMNS FROM shifts');

        return collect($statement->fetchAll(PDO::FETCH_ASSOC))
            ->pluck('Field')
            ->filter()
            ->values()
            ->all();
    }

    private function connectToTenantDatabase(object $databaseConfig, string $driver): PDO
    {
        if ($driver === 'sqlite') {
            $databasePath = (string) $databaseConfig->db_name;

            if ($databasePath === '' || !is_file($databasePath)) {
                throw new RuntimeException('File database SQLite tenant belum ditemukan.');
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

    private function resolveDatabasePassword(object $databaseConfig): string
    {
        $encryptedPassword = $databaseConfig->db_password_encrypted ?? null;

        if (!is_string($encryptedPassword) || $encryptedPassword === '') {
            return (string) config('platform.tenant_mysql_runtime_password', '');
        }

        try {
            return Crypt::decryptString($encryptedPassword);
        } catch (Throwable) {
            return (string) $encryptedPassword;
        }
    }
}
