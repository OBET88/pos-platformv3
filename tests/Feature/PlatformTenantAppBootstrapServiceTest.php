<?php

namespace Tests\Feature;

use App\Services\Platform\TenantAppBootstrapService;
use ReflectionMethod;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class PlatformTenantAppBootstrapServiceTest extends TestCase
{
    public function test_windows_command_line_keeps_php_binary_without_nested_wrapping_quotes(): void
    {
        if (DIRECTORY_SEPARATOR !== '\\') {
            $this->markTestSkipped('Perilaku command line Windows hanya relevan di Windows.');
        }

        $service = app(TenantAppBootstrapService::class);

        $method = new ReflectionMethod($service, 'buildWindowsCommandLine');
        $method->setAccessible(true);

        $commandLine = $method->invoke($service, [
            '"D:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe"',
            'artisan',
            'tenant:bootstrap-database',
            'tenant_demo',
            'Owner Demo',
            'owner@example.com',
            'secret12345',
            '--driver=mysql',
            '--branch=Cabang 1',
        ]);

        $this->assertStringStartsWith('"D:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe" "artisan"', $commandLine);
        $this->assertStringNotContainsString('"""D:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe"""', $commandLine);
        $this->assertStringNotContainsString('\'\\"D:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe\\"\'', $commandLine);
    }

    public function test_httpd_binary_is_swapped_to_cli_php_binary_on_windows(): void
    {
        if (DIRECTORY_SEPARATOR !== '\\') {
            $this->markTestSkipped('Swap binary httpd/php hanya berlaku di Windows.');
        }

        $service = app(TenantAppBootstrapService::class);

        $method = new ReflectionMethod($service, 'resolvePhpBinary');
        $method->setAccessible(true);

        $resolved = $method->invoke($service, 'D:\laragon\bin\apache\httpd-2.4.62-240904-win64-VS17\bin\httpd.exe');

        $this->assertStringEndsWith('php.exe', strtolower($resolved));
        $this->assertStringNotContainsString('httpd.exe', strtolower($resolved));
    }

    public function test_windows_cli_php_candidates_include_laragon_php_installations(): void
    {
        if (DIRECTORY_SEPARATOR !== '\\') {
            $this->markTestSkipped('Deteksi instalasi PHP Laragon hanya berlaku di Windows.');
        }

        $service = app(TenantAppBootstrapService::class);

        $method = new ReflectionMethod($service, 'windowsCliPhpCandidates');
        $method->setAccessible(true);

        $candidates = $method->invoke($service);

        $this->assertContains('D:\\laragon\\bin\\php\\php-8.3.16-Win32-vs16-x64\\php.exe', $candidates);
    }

    public function test_retry_detection_covers_partial_bootstrap_conflicts(): void
    {
        $service = app(TenantAppBootstrapService::class);

        $method = new ReflectionMethod($service, 'shouldRetryBootstrap');
        $method->setAccessible(true);

        $process = $this->createMock(Process::class);
        $process->method('getErrorOutput')->willReturn('');
        $process->method('getOutput')->willReturn("SQLSTATE[42S01]: Base table or view already exists: 1050 Table 'transaction_items' already exists");

        $this->assertTrue($method->invoke($service, $process, 1));
    }

    public function test_child_process_probe_delay_grows_for_later_attempts(): void
    {
        $service = app(TenantAppBootstrapService::class);

        $method = new ReflectionMethod($service, 'childProcessProbeDelay');
        $method->setAccessible(true);

        $this->assertSame(1200000, $method->invoke($service, 1));
        $this->assertSame(4800000, $method->invoke($service, 5));
        $this->assertSame(6000000, $method->invoke($service, 9));
    }

    public function test_migration_snapshot_exclusions_only_skip_platform_tables(): void
    {
        $reflection = new \ReflectionClass(TenantAppBootstrapService::class);
        $constant = $reflection->getConstant('PLATFORM_MIGRATION_EXCLUSIONS');

        $this->assertIsArray($constant);
        $this->assertNotContains('0001_01_01_000001_create_cache_table.php', $constant);
        $this->assertNotContains('0001_01_01_000002_create_jobs_table.php', $constant);
        $this->assertContains('2026_04_15_220000_create_platform_saas_tables.php', $constant);
    }
}
