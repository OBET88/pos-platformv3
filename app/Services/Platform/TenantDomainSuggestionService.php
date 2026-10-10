<?php

namespace App\Services\Platform;

class TenantDomainSuggestionService
{
    public function suggest(string $tenantCode): ?string
    {
        $baseDomain = trim((string) config('platform.tenant_base_domain'));

        if ($baseDomain !== '') {
            return $tenantCode.'.'.$baseDomain;
        }

        $host = parse_url((string) config('app.url'), PHP_URL_HOST);

        if (!$host || in_array($host, ['localhost', '127.0.0.1'], true)) {
            return null;
        }

        return $tenantCode.'.'.$host;
    }
}
