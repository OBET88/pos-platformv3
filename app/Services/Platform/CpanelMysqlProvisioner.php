<?php

namespace App\Services\Platform;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class CpanelMysqlProvisioner
{
    public function enabled(): bool
    {
        return filter_var(config('platform.cpanel_api_enabled', false), FILTER_VALIDATE_BOOLEAN);
    }

    public function createDatabase(string $databaseName): void
    {
        $this->call('Mysql/create_database', [
            'name' => $databaseName,
        ]);
    }

    public function grantAllPrivileges(string $databaseName, string $username): void
    {
        $this->call('Mysql/set_privileges_on_database', [
            'database' => $databaseName,
            'user' => $username,
            'privileges' => 'ALL PRIVILEGES',
        ]);
    }

    private function call(string $endpoint, array $query): array
    {
        $baseUrl = $this->baseUrl();
        $username = trim((string) config('platform.cpanel_username', ''));
        $token = trim((string) config('platform.cpanel_token', ''));

        if ($username === '' || $token === '') {
            throw new RuntimeException('cPanel API belum dikonfigurasi. Isi CPANEL_USERNAME dan CPANEL_TOKEN.');
        }

        $response = Http::withHeaders([
            'Authorization' => 'cpanel '.$username.':'.$token,
        ])
            ->acceptJson()
            ->asForm()
            ->timeout((int) config('platform.cpanel_timeout', 30))
            ->withOptions([
                'verify' => filter_var(config('platform.cpanel_verify_ssl', true), FILTER_VALIDATE_BOOLEAN),
            ])
            ->get($baseUrl.'/execute/'.$endpoint, $query);

        if (! $response->successful()) {
            throw new RuntimeException('cPanel API gagal: HTTP '.$response->status().' '.$response->body());
        }

        $payload = $response->json();
        $result = is_array($payload) ? ($payload['result'] ?? null) : null;
        $status = is_array($result) ? (int) ($result['status'] ?? 0) : 0;

        if ($status !== 1) {
            $errors = is_array($result) ? ($result['errors'] ?? null) : null;
            $messages = is_array($result) ? ($result['messages'] ?? null) : null;
            $details = $errors ?: $messages ?: $payload;

            throw new RuntimeException('cPanel API menolak request: '.json_encode($details));
        }

        return $payload;
    }

    private function baseUrl(): string
    {
        $host = trim((string) config('platform.cpanel_host', ''));
        $port = trim((string) config('platform.cpanel_port', '2083'));

        if ($host === '') {
            throw new RuntimeException('cPanel host belum dikonfigurasi. Isi CPANEL_HOST.');
        }

        if (! str_starts_with($host, 'http://') && ! str_starts_with($host, 'https://')) {
            $host = 'https://'.$host;
        }

        $parts = parse_url($host);
        $scheme = $parts['scheme'] ?? 'https';
        $hostname = $parts['host'] ?? $host;

        return rtrim($scheme.'://'.$hostname.($port !== '' ? ':'.$port : ''), '/');
    }
}
