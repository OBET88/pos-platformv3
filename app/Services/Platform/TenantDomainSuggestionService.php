<?php

namespace App\Services\Platform;

use RuntimeException;

class TenantDomainSuggestionService
{
    /**
     * Subdomain yang tidak boleh dipakai tenant (worksheet preflight deploy IDwebhost §4).
     *
     * `resto`/`mm` = host landing eksplisit platform (resto.nuhafrandesa.com,
     * mm.nuhafrandesa.com; A-record, dipanggil tenant dengan wildcard
     * <kode>.nuhafrandesa.com); sisanya = host utility hosting
     * (cPanel/webmail/landing/dll).
     * Catatan (keputusan Bos Robert 2026-10-31): 'mm' menggantikan 'minimarket'
     * — 'minimarket' kini BUKAN reserved, tenant boleh memakai kode itu.
     */
    public const RESERVED_TENANT_CODES = [
        'www',
        'resto',
        'mm',
        'admin',
        'api',
        'app',
        'mail',
        'ftp',
        'cpanel',
        'webmail',
        'support',
        'status',
    ];

    public function suggest(string $tenantCode): ?string
    {
        $this->assertTenantCodeIsUsable($tenantCode);

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

    /**
     * Tolak (throw) tenant code yang reserved atau ilegal sebagai subdomain.
     *
     * Dipakai di awal pembuatan tenant (TenantRegistrationService) supaya tenant
     * bermasalah tidak pernah dibuat, dan di setiap pembentukan hostname
     * (method `suggest()`) sebagai guard kedua sebelum hostname tersimpan.
     */
    public function assertTenantCodeIsUsable(string $tenantCode): void
    {
        $code = strtolower(trim($tenantCode));

        if ($code === '') {
            throw new RuntimeException('Kode tenant tidak boleh kosong.');
        }

        // 'mm' = 2 char: diizinkan sebagai reserved list karena ia harus
        // SELURUHNYA ditolak sebagai tenant code (host eksplisit
        // mm.nuhafrandesa.com). Check reserved berjalan SEBELUM check
        // panjang minimal sehingga 'mm' lolos guard panjang tapi tetap
        // ditolak dengan pesan reserved yang jelas.
        if (in_array($code, self::RESERVED_TENANT_CODES, true)) {
            throw new RuntimeException(sprintf(
                'Kode tenant "%s" tidak boleh dipakai karena reserved untuk host platform/utility. Pilih nama usaha lain atau tambahkan pembeda (mis. "%s-pos").',
                $code,
                $code,
            ));
        }

        if (
            strlen($code) < 3
            || strlen($code) > 20
            || str_starts_with($code, '-')
            || str_ends_with($code, '-')
            || preg_match('/[^a-z0-9-]/', $code)
        ) {
            throw new RuntimeException(sprintf(
                'Kode tenant "%s" ilegal untuk subdomain. Kode harus 3-20 karakter alfanumerik atau hyphen, tidak boleh diawali/diakhiri hyphen.',
                $tenantCode,
            ));
        }
    }
}
