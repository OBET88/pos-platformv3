<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Mail;
use App\Models\Platform\TenantOnboardingJob;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Endpoint publik pendaftaran mengirim email & menjalankan provisioning mahal.
 * Tanpa throttle, endpoint ini bisa dipakai untuk email-bombing dan
 * menguras resource server. Semua harus dibatasi per email+IP.
 */
class PlatformRegistrationThrottleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        RateLimiter::clear('registration-store:corban@example.com|127.0.0.1');
        RateLimiter::clear('registration-resend:corban@example.com|127.0.0.1');
    }

    private function validPayload(): array
    {
        return [
            'company_name' => 'Resto Uji Throttle',
            'owner_name' => 'Pemilik Uji',
            'owner_email' => 'corban@example.com',
            'owner_phone' => '081200000001',
            'plan_code' => 'basic',
            'business_name' => 'Cabang Uji',
            'branch_name' => 'Cabang Uji',
            'owner_password' => 'rahasia123',
            'owner_password_confirmation' => 'rahasia123',
        ];
    }

    public function test_store_blocks_after_too_many_submissions_from_same_ip_and_email(): void
    {
        $this->withoutExceptionHandling();

        $lastResponse = null;

        for ($i = 0; $i < 5; $i++) {
            $lastResponse = $this->post(route('platform.register.store'), $this->validPayload());
            $lastResponse->assertRedirect(); // 5 pertama lolos (bukan 429)
        }

        $lastResponse = $this->post(route('platform.register.store'), $this->validPayload());

        $lastResponse->assertStatus(429);
    }

    public function test_store_returns_429_when_lockout_active(): void
    {
        RateLimiter::hit('registration-store:corban@example.com|127.0.0.1', 300);
        RateLimiter::hit('registration-store:corban@example.com|127.0.0.1', 300);
        RateLimiter::hit('registration-store:corban@example.com|127.0.0.1', 300);
        RateLimiter::hit('registration-store:corban@example.com|127.0.0.1', 300);
        RateLimiter::hit('registration-store:corban@example.com|127.0.0.1', 300);

        $response = $this->post(route('platform.register.store'), $this->validPayload());

        $response->assertStatus(429);
    }

    public function test_resend_verification_is_throttled_tighter_than_store(): void
    {
        // resend dibatasi 3x / 10 menit
        $key = 'registration-resend:corban@example.com|127.0.0.1';

        for ($i = 0; $i < 3; $i++) {
            RateLimiter::hit($key, 600);
        }

        $response = $this->post(route('platform.register.resend'), [
            'owner_email' => 'corban@example.com',
        ]);

        $response->assertStatus(429);
    }

    public function test_bootstrap_endpoint_is_rate_limited(): void
    {
        $tenant = \App\Models\Platform\Tenant::query()->create([
            'code' => 'throttle-tenant',
            'name' => 'Throttle Tenant',
            'owner_name' => 'Owner Throttle',
            'owner_email' => 'owner@throttle.test',
            'status' => 'pending_provisioning',
        ]);
        $accessToken = Str::random(80);
        TenantOnboardingJob::query()->create([
            'tenant_id' => $tenant->id,
            'status' => 'queued',
            'payload' => [],
            'access_token_hash' => hash('sha256', $accessToken),
            'access_expires_at' => now()->addHour(),
            'available_at' => now(),
        ]);
        RateLimiter::clear('registration-bootstrap:'.$tenant->id.':127.0.0.1');

        $lastResponse = null;

        for ($i = 0; $i < 10; $i++) {
            $lastResponse = $this->postJson(route('platform.register.bootstrap', ['tenant' => $tenant->code]), ['access' => $accessToken]);
        }

        $lastResponse = $this->postJson(route('platform.register.bootstrap', ['tenant' => $tenant->code]), ['access' => $accessToken]);

        $lastResponse->assertStatus(429);
        $lastResponse->assertJsonPath('status', 'throttled');
    }
}
