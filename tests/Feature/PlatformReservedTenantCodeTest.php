<?php

namespace Tests\Feature;

use App\Models\Platform\Tenant;
use App\Services\Platform\TenantDomainSuggestionService;
use App\Services\Platform\TenantRegistrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\TestCase;

class PlatformReservedTenantCodeTest extends TestCase
{
    use RefreshDatabase;

    private function service(): TenantDomainSuggestionService
    {
        config()->set('platform.tenant_base_domain', 'nuhafrandesa.test');
        config()->set('platform.tenant_database_driver', 'sqlite');

        $directory = storage_path('framework/testing/platform-reserved-codes');
        File::deleteDirectory($directory);
        config()->set('platform.tenant_database_directory', $directory);

        return app(TenantDomainSuggestionService::class);
    }

    private function cleanup(): void
    {
        $directory = storage_path('framework/testing/platform-reserved-codes');
        File::deleteDirectory($directory);
    }

    public function test_every_reserved_tenant_code_is_rejected_with_a_clear_error(): void
    {
        $service = $this->service();

        $this->assertSame(
            ['www', 'resto', 'mm', 'admin', 'api', 'app', 'mail', 'ftp', 'cpanel', 'webmail', 'support', 'status'],
            TenantDomainSuggestionService::RESERVED_TENANT_CODES,
        );

        // 'mm' menggantikan 'minimarket' (keputusan Bos Robert 2026-10-31:
        // host eksplisit mm.nuhafrandesa.com; 'minimarket' kini BOLEH dipakai
        // tenant karena satu kode = satu tenant lintas aplikasi).
        $this->assertContains('mm', TenantDomainSuggestionService::RESERVED_TENANT_CODES);
        $this->assertNotContains('minimarket', TenantDomainSuggestionService::RESERVED_TENANT_CODES);

        foreach (TenantDomainSuggestionService::RESERVED_TENANT_CODES as $reservedCode) {
            try {
                $service->assertTenantCodeIsUsable($reservedCode);
                $this->fail("Kode reserved '{$reservedCode}' seharusnya ditolak.");
            } catch (RuntimeException $exception) {
                $this->assertStringContainsString('reserved', $exception->getMessage());
            }

            // Guard di `suggest()`: reserved code tidak boleh menghasilkan hostname.
            try {
                $service->suggest($reservedCode);
                $this->fail("suggest('{$reservedCode}') seharusnya ditolak.");
            } catch (RuntimeException $exception) {
                $this->assertStringContainsString('reserved', $exception->getMessage());
            }
        }

        $this->cleanup();
    }

    public function test_a_legal_tenant_code_is_accepted_and_builds_a_hostname(): void
    {
        $service = $this->service();

        $service->assertTenantCodeIsUsable('pt-sinar-maju');

        $this->assertSame('pt-sinar-maju.nuhafrandesa.test', $service->suggest('pt-sinar-maju'));

        $this->cleanup();
    }

    public function test_minimarket_is_no_longer_reserved_and_builds_a_hostname(): void
    {
        $service = $this->service();

        // Keputusan Bos Robert 2026-10-31: 'mm' menggantikan 'minimarket' sebagai
        // reserved (host eksplisit mm.nuhafrandesa.com). 'minimarket' kini boleh
        // dipakai tenant → hostname terbentuk normal.
        $service->assertTenantCodeIsUsable('minimarket');

        $this->assertSame('minimarket.nuhafrandesa.test', $service->suggest('minimarket'));

        // Sebaliknya 'mm' (reserved baru) ditolak.
        try {
            $service->assertTenantCodeIsUsable('mm');
            $this->fail("Kode 'mm' seharusnya ditolak karena reserved.");
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('reserved', $exception->getMessage());
        }

        try {
            $service->suggest('mm');
            $this->fail("suggest('mm') seharusnya ditolak karena reserved.");
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('reserved', $exception->getMessage());
        }

        $this->cleanup();
    }

    public function test_tenant_codes_with_illegal_subdomain_characters_are_rejected(): void
    {
        $service = $this->service();

        $cases = [
            // karakter ilegal (underscore/titik)
            ['pt_sinar', 'ilegal untuk subdomain'],
            ['pt.sinar', 'ilegal untuk subdomain'],
            // terlalu pendek (< 3)
            ['ab', 'ilegal untuk subdomain'],
            // terlalu panjang (> 20)
            ['sangat-banyak-karakter-di-sini', 'ilegal untuk subdomain'],
            // diawali / diakhiri hyphen
            ['-resto-sinar', 'ilegal untuk subdomain'],
            ['resto-sinar-', 'ilegal untuk subdomain'],
            // kosong
            ['', 'tidak boleh kosong'],
            ['   ', 'tidak boleh kosong'],
        ];

        foreach ($cases as [$code, $needle]) {
            try {
                $service->assertTenantCodeIsUsable($code);
                $this->fail("Kode '{$code}' seharusnya ditolak.");
            } catch (RuntimeException $exception) {
                $this->assertStringContainsString($needle, $exception->getMessage());
            }
        }

        $this->cleanup();
    }

    public function test_hyphenated_codes_without_edge_hyphens_are_allowed(): void
    {
        $service = $this->service();

        $service->assertTenantCodeIsUsable('resto-sinar-jaya');

        $this->assertSame('resto-sinar-jaya.nuhafrandesa.test', $service->suggest('resto-sinar-jaya'));

        $this->cleanup();
    }

    public function test_manual_tenant_code_from_registration_payload_is_used(): void
    {
        $service = $this->service();

        $tenant = app(TenantRegistrationService::class)->register([
            'company_name' => 'PT Sinar Maju',
            'tenant_code' => 'berkah',
            'owner_name' => 'Owner Manual',
            'owner_email' => 'manual-code@example.com',
            'owner_password' => 'secret12345',
            'plan_code' => 'trial',
            'business_name' => 'Usaha Berkah',
            'branch_name' => 'Cabang Berkah',
        ]);

        // Kode manual 'berkah' dipakai apa adanya — BUKAN slug auto 'pt-sinar-maju'.
        $this->assertSame('berkah', $tenant->code);
        $this->assertSame(
            'berkah.nuhafrandesa.test',
            $tenant->domains->firstWhere('is_primary', true)?->domain,
        );

        $this->cleanup();
    }

    public function test_manual_tenant_code_that_is_reserved_is_rejected(): void
    {
        $service = $this->service();

        try {
            app(TenantRegistrationService::class)->register([
                'company_name' => 'PT Sinar Maju',
                'tenant_code' => 'mm', // reserved host eksplisit mm.nuhafrandesa.com
                'owner_name' => 'Owner Manual',
                'owner_email' => 'manual-reserved@example.com',
                'owner_password' => 'secret12345',
                'plan_code' => 'trial',
                'business_name' => 'Usaha MM',
                'branch_name' => 'Cabang MM',
            ]);
            $this->fail('Kode subdomain manual reserved "mm" seharusnya ditolak.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('reserved', $exception->getMessage());
        }

        $this->assertSame(0, Tenant::query()->where('owner_email', 'manual-reserved@example.com')->count());

        $this->cleanup();
    }

    public function test_manual_tenant_code_conflict_is_rejected_and_empty_falls_back_to_auto(): void
    {
        $service = $this->service();

        // Tenant pertama membuat kode 'berkah'.
        app(TenantRegistrationService::class)->register([
            'company_name' => 'PT Sinar Maju',
            'tenant_code' => 'berkah',
            'owner_name' => 'Owner A',
            'owner_email' => 'manual-conflict-a@example.com',
            'owner_password' => 'secret12345',
            'plan_code' => 'trial',
            'business_name' => 'Usaha A',
            'branch_name' => 'Cabang A',
        ]);

        // Kode manual yang sama → bentrok, ditolak (di form: validasi unique;
        // di service: resolveTenantCode melempar RuntimeException).
        try {
            app(TenantRegistrationService::class)->register([
                'company_name' => 'PT Lain',
                'tenant_code' => 'berkah',
                'owner_name' => 'Owner B',
                'owner_email' => 'manual-conflict-b@example.com',
                'owner_password' => 'secret12345',
                'plan_code' => 'trial',
                'business_name' => 'Usaha B',
                'branch_name' => 'Cabang B',
            ]);
            $this->fail('Kode subdomain manual yang sudah dipakai seharusnya ditolak.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('sudah dipakai', $exception->getMessage());
        }

        // tenant_code kosong → fallback ke generateTenantCode dari nama perusahaan.
        // Nama panjang "PT Berkah Sinar Jaya" (Str::slug → "pt-berkah-sinar-jaya",
        // 20 char) → base auto 12 char: substr(0,12) = "pt-berkah-si",
        // rtrim '-' tidak mengubah apa-apa (sudah berakhir 'i').
        $tenant = app(TenantRegistrationService::class)->register([
            'company_name' => 'PT Berkah Sinar Jaya',
            'owner_name' => 'Owner C',
            'owner_email' => 'manual-conflict-c@example.com',
            'owner_password' => 'secret12345',
            'plan_code' => 'trial',
            'business_name' => 'Usaha C',
            'branch_name' => 'Cabang C',
        ]);

        $this->assertSame('pt-berkah-si', $tenant->code);
        $this->assertLessThanOrEqual(12, strlen($tenant->code));
        $this->assertSame(0, Tenant::query()->where('owner_email', 'manual-conflict-b@example.com')->count());

        $this->cleanup();
    }

    public function test_registration_is_blocked_when_the_generated_tenant_code_is_reserved(): void
    {
        $service = $this->service();

        try {
            app(TenantRegistrationService::class)->register([
                'company_name' => 'resto', // slug = "resto" = reserved
                'owner_name' => 'Owner Reserved',
                'owner_email' => 'reserved-registro@example.com',
                'owner_password' => 'secret12345',
                'plan_code' => 'trial',
                'business_name' => 'Usaha Resto',
                'branch_name' => 'Cabang Resto',
            ]);
            $this->fail('Registrasi dengan kode generated reserved seharusnya ditolak.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('reserved', $exception->getMessage());
        }

        // Tidak ada tenant tersimpan sama sekali.
        $this->assertSame(0, Tenant::query()->where('owner_email', 'reserved-registro@example.com')->count());

        $this->cleanup();
    }
}
