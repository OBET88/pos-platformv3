<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Test harus environment-independent. `.env` lokal bisa berisi path/kredensial
        // hosting (mis. PLATFORM_TENANT_APP_PATH=/home/... pada server Linux) yang tidak
        // ada di mesin developer, sehingga bootstrap tenant app gagal. Untuk test,
        // arahkan ke sibling repo tenant app yang ada di mesin ini bila tersedia.
        if (! app()->environment('testing')) {
            return;
        }

        $localTenantAppPath = realpath(base_path('../poslive')) ?: base_path('../poslive');

        if (is_dir($localTenantAppPath)) {
            config()->set('platform.tenant_app_path', $localTenantAppPath);
        }

        // Jangan paksa redirect HTTPS selama test agar request http:// tetap 200.
        config()->set('app.force_https', false);
    }
}
