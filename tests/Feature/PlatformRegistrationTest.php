<?php

namespace Tests\Feature;

use App\Mail\PlatformRegistrationReceived;
use App\Mail\PlatformRegistrationVerification;
use App\Mail\PlatformTenantReady;
use App\Models\Platform\PendingRegistration;
use App\Models\Platform\Plan;
use App\Models\Platform\PlatformSetting;
use App\Models\Platform\RegistrationDraft;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;
use App\Models\Platform\TenantDatabaseConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use PDO;
use Tests\TestCase;

class PlatformRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_shows_the_public_platform_registration_form(): void
    {
        $this->get(route('platform.register.create'))
            ->assertOk()
            ->assertSee('Daftar Tenant / Usaha Baru')
            ->assertSeeText('Ini bukan form daftar akun karyawan, admin, atau kasir tenant yang sudah aktif.')
            ->assertSeeText('Trial - Gratis 14 hari');
    }

    public function test_legacy_tenant_registration_host_redirects_to_official_platform_registration_url(): void
    {
        $this->get('https://dkriuk.jajajuju.my.id/daftar?plan=pro')
            ->assertRedirect('https://rwp.jajajuju.my.id/daftar?plan=pro');
    }

    public function test_it_can_prefill_plan_selection_from_landing_cta(): void
    {
        $this->get(route('platform.register.create', ['plan' => 'pro']))
            ->assertOk()
            ->assertSee('Paket rekomendasi sudah disiapkan.')
            ->assertSee('PRO');
    }

    public function test_public_registration_form_uses_configured_visible_plans_and_default_choice(): void
    {
        PlatformSetting::query()->updateOrCreate(
            ['key' => 'public_registration_plan_codes'],
            ['value' => json_encode(['basic', 'pro'])]
        );
        PlatformSetting::query()->updateOrCreate(
            ['key' => 'public_registration_default_plan_code'],
            ['value' => 'pro']
        );

        $this->get(route('platform.register.create'))
            ->assertOk()
            ->assertSee('Paket rekomendasi sudah disiapkan.')
            ->assertSee('PRO')
            ->assertSee('value="basic"', false)
            ->assertSee('value="pro"', false)
            ->assertDontSee('value="trial"', false);
    }

    public function test_default_public_support_pages_are_available(): void
    {
        $this->get(route('platform.help'))
            ->assertOk()
            ->assertSee('Help Center');

        $this->get(route('platform.contact'))
            ->assertOk()
            ->assertSee('Hubungi tim RWP POS');

        $this->get(route('platform.blog'))
            ->assertOk()
            ->assertSeeText('Blog & Update');
    }

    public function test_it_shows_active_subscription_plans_on_the_public_landing_page(): void
    {
        Plan::query()->create([
            'code' => 'growth',
            'name' => 'Growth',
            'price' => 299000,
            'billing_cycle' => 'bulan',
            'max_businesses' => 2,
            'max_branches' => 5,
            'max_users' => 20,
            'features_json' => ['menu_reports', 'menu_inventory', 'menu_customers'],
            'is_active' => true,
        ]);

        Plan::query()->create([
            'code' => 'legacy-hidden',
            'name' => 'Legacy Hidden',
            'price' => 999000,
            'billing_cycle' => 'bulan',
            'max_businesses' => 9,
            'max_branches' => 9,
            'max_users' => 99,
            'features_json' => ['menu_reports'],
            'is_active' => false,
        ]);

        $this->get(route('platform.landing'))
            ->assertOk()
            ->assertSee('Fitur Utama Aplikasi')
            ->assertSee('Paket Langganan')
            ->assertSee('Trial')
            ->assertSee('Growth')
            ->assertDontSee('Legacy Hidden')
            ->assertSee('Pilih Paket Ini');
    }

    public function test_trial_plan_is_available_by_default(): void
    {
        $trialPlan = Plan::query()->where('code', 'trial')->first();

        $this->assertNotNull($trialPlan);
        $this->assertSame('Trial', $trialPlan->name);
        $this->assertTrue($trialPlan->is_active);
        $this->assertContains('menu_accounting_settings', $trialPlan->features_json);
        $this->assertContains('accounting_coa', $trialPlan->features_json);
    }

    public function test_it_creates_a_tenant_registration_from_the_public_platform_form(): void
    {
        config()->set('platform.tenant_base_domain', 'poslive.test');
        config()->set('platform.tenant_database_driver', 'sqlite');
        $directory = storage_path('framework/testing/platform-registration-tenants');
        File::deleteDirectory($directory);
        config()->set('platform.tenant_database_directory', $directory);
        Mail::fake();

        $plan = Plan::query()->where('code', 'trial')->firstOrFail();

        $this->post(route('platform.register.store'), [
            'company_name' => 'PT Platform Test',
            'owner_name' => 'Platform Owner',
            'owner_email' => 'platform-owner@example.com',
            'owner_password' => 'secret12345',
            'owner_phone' => '0812000000',
            'plan_code' => $plan->code,
            'business_name' => 'Usaha Platform Test',
            'branch_name' => 'Cabang Platform Test',
            'notes' => 'Tenant dari test app platform',
        ])
            ->assertRedirect(route('platform.register.pending'));

        $this->assertDatabaseHas('platform_pending_registrations', [
            'owner_email' => 'platform-owner@example.com',
        ]);
        $this->assertDatabaseMissing('tenants', [
            'owner_email' => 'platform-owner@example.com',
        ]);

        Mail::assertSent(PlatformRegistrationVerification::class, function (PlatformRegistrationVerification $mail) {
            return $mail->hasTo('platform-owner@example.com');
        });

        $pendingRegistration = PendingRegistration::query()->where('owner_email', 'platform-owner@example.com')->firstOrFail();

        $this->get(route('platform.register.verify', ['token' => $pendingRegistration->verification_token]))
            ->assertRedirect();

        $tenant = Tenant::query()->where('owner_email', 'platform-owner@example.com')->first();

        $this->assertNotNull($tenant);
        app(\App\Services\Platform\TenantOnboardingWorker::class)->processNext($tenant->id);
        $tenant->refresh();
        $this->assertSame('trial', $tenant->status);
        $this->assertSame($tenant->code.'.poslive.test', $tenant->domains()->where('is_primary', true)->value('domain'));
        $this->assertSame(1, $tenant->subscriptions()->count());
        $this->assertSame($plan->id, Subscription::query()->where('tenant_id', $tenant->id)->value('plan_id'));
        $this->assertSame('provisioned', TenantDatabaseConfig::query()->where('tenant_id', $tenant->id)->value('status'));

        $databasePath = TenantDatabaseConfig::query()->where('tenant_id', $tenant->id)->value('db_name');
        $this->assertNotFalse($databasePath);
        $this->assertTrue(File::exists($databasePath));

        $pdo = new PDO('sqlite:'.$databasePath);
        $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type = 'table' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);

        $this->assertContains('users', $tables);
        $this->assertContains('businesses', $tables);
        $this->assertContains('branches', $tables);

        $ownerEmail = $pdo->query("SELECT email FROM users WHERE email = 'platform-owner@example.com' LIMIT 1")->fetchColumn();
        $this->assertSame('platform-owner@example.com', $ownerEmail);
        $ownerEmailVerifiedAt = $pdo->query("SELECT email_verified_at FROM users WHERE email = 'platform-owner@example.com' LIMIT 1")->fetchColumn();
        $this->assertNotEmpty($ownerEmailVerifiedAt);

        $this->assertNotNull($pendingRegistration->fresh()->email_verified_at);
        $this->assertNotNull($pendingRegistration->fresh()->consumed_at);
        $this->assertSame($tenant->id, $pendingRegistration->fresh()->tenant_id);

        $this->assertDatabaseHas('tenant_provision_logs', [
            'tenant_id' => $tenant->id,
            'step' => 'tenant_app_bootstrap',
            'status' => 'completed',
        ]);
        $this->assertDatabaseHas('tenant_provision_logs', [
            'tenant_id' => $tenant->id,
            'step' => 'auto_onboarding_completed',
            'status' => 'completed',
        ]);

        Mail::assertSent(PlatformRegistrationReceived::class, function (PlatformRegistrationReceived $mail) use ($tenant) {
            return $mail->hasTo('platform-owner@example.com')
                && $mail->tenantCode === $tenant->code;
        });
        Mail::assertSent(PlatformTenantReady::class, function (PlatformTenantReady $mail) use ($tenant) {
            return $mail->hasTo('platform-owner@example.com')
                && $mail->tenantCode === $tenant->code
                && $mail->username === 'platform-owner@example.com'
                && $mail->forgotPasswordUrl === 'http://'.$tenant->code.'.poslive.test/forgot-password';
        });

        File::deleteDirectory($directory);
    }

    public function test_paid_plan_registration_waits_for_payment_without_provisioning(): void
    {
        config()->set('platform.tenant_base_domain', 'poslive.test');
        $directory = storage_path('framework/testing/platform-registration-paid-plan-trial');
        File::deleteDirectory($directory);
        config()->set('platform.tenant_database_directory', $directory);
        Mail::fake();

        $plan = Plan::query()->where('code', 'basic')->firstOrFail();

        $this->post(route('platform.register.store'), [
            'company_name' => 'PT Registrasi Berbayar',
            'owner_name' => 'Owner Berbayar',
            'owner_email' => 'paid-registration@example.com',
            'owner_password' => 'secret12345',
            'owner_phone' => '0812000044',
            'plan_code' => $plan->code,
            'business_name' => 'Usaha Registrasi Berbayar',
            'branch_name' => 'Cabang Registrasi Berbayar',
            'notes' => 'Perlu invoice dulu sebelum tenant aktif',
        ])->assertRedirect(route('platform.register.pending'));

        $pendingRegistration = PendingRegistration::query()->where('owner_email', 'paid-registration@example.com')->firstOrFail();

        $verifyResponse = $this->get(route('platform.register.verify', ['token' => $pendingRegistration->verification_token]));

        $tenant = Tenant::query()->where('owner_email', 'paid-registration@example.com')->firstOrFail();
        $subscription = Subscription::query()->where('tenant_id', $tenant->id)->firstOrFail();
        $databaseConfig = TenantDatabaseConfig::query()->where('tenant_id', $tenant->id)->firstOrFail();

        $registrationInvoice = \App\Models\Platform\PlatformPaymentTransaction::query()
            ->where('tenant_id', $tenant->id)
            ->where('type', 'registration')
            ->firstOrFail();
        $verifyResponse->assertRedirectContains(route('platform.payment.show', ['invoiceNumber' => $registrationInvoice->invoice_number]));

        $this->assertSame('pending_payment', $tenant->status);
        $this->assertNull($tenant->trial_ends_at);
        $this->assertSame('pending_payment', $subscription->status);
        $this->assertNull($subscription->started_at);
        $this->assertNull($subscription->trial_ends_at);
        $this->assertNull($subscription->expired_at);
        $this->assertSame($plan->id, $subscription->plan_id);
        $this->assertSame('pending', $databaseConfig->status);
        $this->assertDatabaseHas('platform_payment_transactions', [
            'tenant_id' => $tenant->id,
            'type' => 'registration',
            'status' => 'pending_payment',
        ]);

        $this->assertNotNull($pendingRegistration->fresh()->email_verified_at);
        $this->assertNotNull($pendingRegistration->fresh()->consumed_at);
        $this->assertSame($tenant->id, $pendingRegistration->fresh()->tenant_id);

        $this->get(route('platform.register.verify', ['token' => $pendingRegistration->verification_token]))
            ->assertRedirectContains(route('platform.payment.show', ['invoiceNumber' => $registrationInvoice->invoice_number]));

        $this->assertDatabaseMissing('tenant_provision_logs', [
            'tenant_id' => $tenant->id,
            'step' => 'database_provisioning',
        ]);
        $this->assertDatabaseMissing('tenant_provision_logs', [
            'tenant_id' => $tenant->id,
            'step' => 'auto_onboarding_completed',
            'status' => 'completed',
        ]);

        Mail::assertSent(PlatformRegistrationVerification::class, function (PlatformRegistrationVerification $mail) {
            return $mail->hasTo('paid-registration@example.com');
        });
        Mail::assertSent(PlatformRegistrationReceived::class, function (PlatformRegistrationReceived $mail) use ($tenant) {
            return $mail->hasTo('paid-registration@example.com')
                && $mail->tenantCode === $tenant->code;
        });
        Mail::assertNotSent(PlatformTenantReady::class);

        File::deleteDirectory($directory);
    }

    public function test_it_can_render_the_pending_registration_page_with_session_context(): void
    {
        $response = $this->withSession([
            'pending_registration_email' => 'pending-owner@example.com',
            'registration_email_sent' => true,
        ])->get(route('platform.register.pending'));

        $response->assertOk()
            ->assertSee('pending-owner@example.com')
            ->assertSee('Link verifikasi sudah kami kirim.');
    }

    public function test_it_reports_owner_email_status_as_available_for_new_public_registration(): void
    {
        $this->getJson(route('platform.register.owner-email-status', [
            'owner_email' => 'fresh-owner@example.com',
        ]))
            ->assertOk()
            ->assertJson([
                'status' => 'available',
            ]);
    }

    public function test_it_reports_owner_email_status_as_pending_for_existing_pending_registration(): void
    {
        PendingRegistration::query()->create([
            'owner_email' => 'pending-status@example.com',
            'verification_token' => 'pending-status-token',
            'payload' => [
                'company_name' => 'PT Pending Status',
                'owner_name' => 'Owner Pending Status',
                'owner_email' => 'pending-status@example.com',
            ],
            'verification_sent_at' => now()->subMinutes(2),
            'expires_at' => now()->addHours(24),
        ]);

        $this->getJson(route('platform.register.owner-email-status', [
            'owner_email' => ' PENDING-STATUS@EXAMPLE.COM ',
        ]))
            ->assertOk()
            ->assertJson([
                'status' => 'pending',
            ]);
    }

    public function test_it_reports_owner_email_status_as_registered_for_existing_tenant(): void
    {
        Tenant::query()->create([
            'code' => 'registered-status-tenant',
            'name' => 'Registered Status Tenant',
            'owner_name' => 'Registered Owner',
            'owner_email' => 'registered-status@example.com',
            'status' => 'pending_setup',
            'trial_ends_at' => now()->addDays(14),
        ]);

        $this->getJson(route('platform.register.owner-email-status', [
            'owner_email' => ' REGISTERED-STATUS@EXAMPLE.COM ',
        ]))
            ->assertOk()
            ->assertJson([
                'status' => 'registered',
            ])
            ->assertJsonStructure([
                'status',
                'message',
                'action_label',
                'action_url',
            ]);
    }

    public function test_it_redirects_pending_page_gracefully_when_session_context_is_missing(): void
    {
        $this->from(route('platform.register.create'))
            ->get(route('platform.register.pending'))
            ->assertRedirect(route('platform.register.create'))
            ->assertSessionHasErrors([
                'owner_email' => 'Status verifikasi tidak ditemukan di sesi browser ini. Silakan kirim ulang pendaftaran untuk mendapatkan link verifikasi terbaru.',
            ]);
    }

    public function test_it_can_resend_pending_registration_verification_email_from_the_public_pending_page(): void
    {
        Mail::fake();

        $plan = Plan::query()->where('code', 'trial')->firstOrFail();

        $this->post(route('platform.register.store'), [
            'company_name' => 'PT Resend Publik',
            'owner_name' => 'Owner Resend',
            'owner_email' => 'resend-public@example.com',
            'owner_password' => 'secret12345',
            'owner_phone' => '0812000012',
            'plan_code' => $plan->code,
            'business_name' => 'Usaha Resend Publik',
            'branch_name' => 'Cabang Resend Publik',
        ])->assertRedirect(route('platform.register.pending'));

        Mail::assertSent(PlatformRegistrationVerification::class, 1);

        $sentAtBefore = PendingRegistration::query()
            ->where('owner_email', 'resend-public@example.com')
            ->value('verification_sent_at');

        $this->travel(5)->minutes();

        $this->withSession([
            'pending_registration_email' => 'resend-public@example.com',
        ])->post(route('platform.register.resend'))
            ->assertRedirect(route('platform.register.pending'))
            ->assertSessionHas('status');

        Mail::assertSent(PlatformRegistrationVerification::class, 2);

        $pendingRegistration = PendingRegistration::query()
            ->where('owner_email', 'resend-public@example.com')
            ->firstOrFail();

        $this->assertNotNull($pendingRegistration->verification_sent_at);
        $this->assertTrue($pendingRegistration->verification_sent_at->gt($sentAtBefore));
    }

    public function test_it_blocks_public_verification_resend_requests_during_cooldown(): void
    {
        Mail::fake();

        $plan = Plan::query()->where('code', 'trial')->firstOrFail();

        $this->post(route('platform.register.store'), [
            'company_name' => 'PT Cooldown Publik',
            'owner_name' => 'Owner Cooldown',
            'owner_email' => 'cooldown-public@example.com',
            'owner_password' => 'secret12345',
            'owner_phone' => '0812000013',
            'plan_code' => $plan->code,
            'business_name' => 'Usaha Cooldown Publik',
            'branch_name' => 'Cabang Cooldown Publik',
        ])->assertRedirect(route('platform.register.pending'));

        Mail::assertSent(PlatformRegistrationVerification::class, 1);

        $this->withSession([
            'pending_registration_email' => 'cooldown-public@example.com',
        ])->post(route('platform.register.resend'))
            ->assertRedirect(route('platform.register.pending'))
            ->assertSessionHas('warning');

        Mail::assertSent(PlatformRegistrationVerification::class, 1);
    }

    public function test_it_redirects_resend_request_back_to_registration_when_session_context_is_missing(): void
    {
        $this->from(route('platform.register.create'))
            ->post(route('platform.register.resend'))
            ->assertRedirect(route('platform.register.create'))
            ->assertSessionHasErrors([
                'owner_email' => 'Status verifikasi tidak ditemukan di sesi browser ini. Silakan isi ulang form pendaftaran untuk mendapatkan link verifikasi terbaru.',
            ]);
    }

    public function test_it_can_resend_verification_using_owner_email_fallback_when_session_context_is_missing(): void
    {
        Mail::fake();

        $plan = Plan::query()->where('code', 'trial')->firstOrFail();

        $this->post(route('platform.register.store'), [
            'company_name' => 'PT Resend Fallback',
            'owner_name' => 'Owner Resend Fallback',
            'owner_email' => 'resend-fallback@example.com',
            'owner_password' => 'secret12345',
            'owner_phone' => '0812000017',
            'plan_code' => $plan->code,
            'business_name' => 'Usaha Resend Fallback',
            'branch_name' => 'Cabang Resend Fallback',
        ])->assertRedirect(route('platform.register.pending'));

        Mail::assertSent(PlatformRegistrationVerification::class, 1);

        $this->travel(61)->seconds();

        $this->post(route('platform.register.resend'), [
            'owner_email' => ' RESEND-FALLBACK@EXAMPLE.COM ',
        ])
            ->assertRedirect(route('platform.register.pending'))
            ->assertSessionHas('status');

        Mail::assertSent(PlatformRegistrationVerification::class, 2);
    }

    public function test_it_redirects_gracefully_when_verification_token_is_not_found(): void
    {
        $this->from(route('platform.register.create'))
            ->get(route('platform.register.verify', ['token' => 'missing-token']))
            ->assertRedirect(route('platform.register.create'))
            ->assertSessionHasErrors([
                'owner_email' => 'Link verifikasi tidak ditemukan atau sudah tidak berlaku. Silakan kirim ulang pendaftaran untuk mendapatkan link verifikasi terbaru.',
            ]);
    }

    public function test_it_redirects_gracefully_when_success_page_tenant_is_missing(): void
    {
        $this->from(route('platform.register.create'))
            ->get(route('platform.register.success', ['tenant' => 'tenant-sudah-dihapus']))
            ->assertRedirect(route('platform.register.create'))
            ->assertSessionHasErrors([
                'owner_email' => 'Status pendaftaran tenant tidak ditemukan. Kemungkinan link ini sudah lama, tenant pernah dibersihkan, atau proses pendaftaran perlu diulang dari awal.',
            ]);
    }

    public function test_it_returns_json_message_when_bootstrap_endpoint_tenant_is_missing(): void
    {
        $this->postJson(route('platform.register.bootstrap', ['tenant' => 'tenant-sudah-dihapus']))
            ->assertNotFound()
            ->assertJson([
                'status' => 'missing',
                'message' => 'Tenant untuk status pendaftaran ini sudah tidak ditemukan. Silakan ulangi pendaftaran dari awal.',
            ]);
    }

    public function test_it_returns_json_message_when_status_endpoint_tenant_is_missing(): void
    {
        $this->getJson(route('platform.register.status', ['tenant' => 'tenant-sudah-dihapus']))
            ->assertNotFound()
            ->assertJson([
                'status' => 'missing',
                'logs' => [],
                'message' => 'Tenant untuk status pendaftaran ini sudah tidak ditemukan. Silakan ulangi pendaftaran dari awal.',
            ]);
    }

    public function test_public_onboarding_pages_require_the_opaque_access_token(): void
    {
        $tenant = Tenant::query()->create([
            'code' => 'protected-onboarding',
            'name' => 'Protected Onboarding',
            'owner_name' => 'Protected Owner',
            'owner_email' => 'protected-onboarding@example.com',
            'status' => 'pending_setup',
        ]);
        $dispatcher = app(\App\Services\Platform\TenantOnboardingDispatcher::class);
        $dispatcher->enqueue($tenant, [
            'owner_name' => $tenant->owner_name,
            'owner_email' => $tenant->owner_email,
        ]);
        $accessToken = $dispatcher->issuePublicAccessToken($tenant);

        $this->get(route('platform.register.success', ['tenant' => $tenant->code]))->assertNotFound();
        $this->getJson(route('platform.register.status', ['tenant' => $tenant->code]))->assertNotFound();
        $this->postJson(route('platform.register.bootstrap', ['tenant' => $tenant->code]))->assertNotFound();

        $this->get(route('platform.register.success', ['tenant' => $tenant->code, 'access' => $accessToken]))
            ->assertOk();
        $this->getJson(route('platform.register.status', ['tenant' => $tenant->code, 'access' => $accessToken]))
            ->assertOk();
    }

    public function test_it_reuses_the_same_verification_token_for_an_existing_pending_registration(): void
    {
        Mail::fake();

        $plan = Plan::query()->where('code', 'trial')->firstOrFail();

        $this->post(route('platform.register.store'), [
            'company_name' => 'PT Pending Token',
            'owner_name' => 'Pending Owner',
            'owner_email' => 'pending-token@example.com',
            'owner_password' => 'secret12345',
            'owner_phone' => '0812000011',
            'plan_code' => $plan->code,
            'business_name' => 'Usaha Pending Token',
            'branch_name' => 'Cabang Pending Token',
        ])->assertRedirect(route('platform.register.pending'));

        $firstPending = PendingRegistration::query()->where('owner_email', 'pending-token@example.com')->firstOrFail();
        $firstToken = $firstPending->verification_token;

        $this->post(route('platform.register.store'), [
            'company_name' => 'PT Pending Token Update',
            'owner_name' => 'Pending Owner Update',
            'owner_email' => 'pending-token@example.com',
            'owner_password' => 'secret12345',
            'owner_phone' => '0812000099',
            'plan_code' => $plan->code,
            'business_name' => 'Usaha Pending Token Update',
            'branch_name' => 'Cabang Pending Token Update',
        ])->assertRedirect(route('platform.register.pending'));

        $updatedPending = PendingRegistration::query()->where('owner_email', 'pending-token@example.com')->firstOrFail();

        $this->assertSame($firstToken, $updatedPending->verification_token);
        $this->assertSame('PT Pending Token Update', $updatedPending->payload['company_name']);
        $this->assertSame('Pending Owner Update', $updatedPending->payload['owner_name']);
    }

    public function test_it_reuses_pending_registration_when_same_email_is_submitted_with_different_casing(): void
    {
        Mail::fake();

        $plan = Plan::query()->where('code', 'trial')->firstOrFail();

        $this->post(route('platform.register.store'), [
            'company_name' => 'PT Pending Normalize',
            'owner_name' => 'Pending Normalize',
            'owner_email' => 'pending-normalize@example.com',
            'owner_password' => 'secret12345',
            'owner_phone' => '0812000015',
            'plan_code' => $plan->code,
            'business_name' => 'Usaha Pending Normalize',
            'branch_name' => 'Cabang Pending Normalize',
        ])->assertRedirect(route('platform.register.pending'));

        $firstPending = PendingRegistration::query()->where('owner_email', 'pending-normalize@example.com')->firstOrFail();
        $firstToken = $firstPending->verification_token;

        $this->travel(61)->seconds();

        $this->post(route('platform.register.store'), [
            'company_name' => 'PT Pending Normalize Update',
            'owner_name' => 'Pending Normalize Update',
            'owner_email' => '  PENDING-NORMALIZE@EXAMPLE.COM ',
            'owner_password' => 'secret12345',
            'owner_phone' => '0812000097',
            'plan_code' => $plan->code,
            'business_name' => 'Usaha Pending Normalize Update',
            'branch_name' => 'Cabang Pending Normalize Update',
        ])->assertRedirect(route('platform.register.pending'));

        $updatedPending = PendingRegistration::query()->where('owner_email', 'pending-normalize@example.com')->firstOrFail();

        $this->assertSame($firstToken, $updatedPending->verification_token);
        $this->assertSame('PT Pending Normalize Update', $updatedPending->payload['company_name']);
        $this->assertSame('Pending Normalize Update', $updatedPending->payload['owner_name']);
    }

    public function test_it_reuses_pending_registration_without_sending_duplicate_email_during_cooldown(): void
    {
        Mail::fake();

        $plan = Plan::query()->where('code', 'basic')->firstOrFail();

        $this->post(route('platform.register.store'), [
            'company_name' => 'PT Pending Cooldown',
            'owner_name' => 'Pending Cooldown',
            'owner_email' => 'pending-cooldown@example.com',
            'owner_password' => 'secret12345',
            'owner_phone' => '0812000014',
            'plan_code' => $plan->code,
            'business_name' => 'Usaha Pending Cooldown',
            'branch_name' => 'Cabang Pending Cooldown',
        ])->assertRedirect(route('platform.register.pending'));

        Mail::assertSent(PlatformRegistrationVerification::class, 1);

        $firstPending = PendingRegistration::query()->where('owner_email', 'pending-cooldown@example.com')->firstOrFail();
        $firstToken = $firstPending->verification_token;

        $this->post(route('platform.register.store'), [
            'company_name' => 'PT Pending Cooldown Update',
            'owner_name' => 'Pending Cooldown Update',
            'owner_email' => 'pending-cooldown@example.com',
            'owner_password' => 'secret12345',
            'owner_phone' => '0812000098',
            'plan_code' => $plan->code,
            'business_name' => 'Usaha Pending Cooldown Update',
            'branch_name' => 'Cabang Pending Cooldown Update',
        ])->assertRedirect(route('platform.register.pending'))
            ->assertSessionHas('warning');

        Mail::assertSent(PlatformRegistrationVerification::class, 1);

        $updatedPending = PendingRegistration::query()->where('owner_email', 'pending-cooldown@example.com')->firstOrFail();

        $this->assertSame($firstToken, $updatedPending->verification_token);
        $this->assertSame('PT Pending Cooldown Update', $updatedPending->payload['company_name']);
        $this->assertSame('Pending Cooldown Update', $updatedPending->payload['owner_name']);
    }

    public function test_it_explains_that_existing_pending_email_is_updated_instead_of_creating_a_new_registration(): void
    {
        Mail::fake();

        $plan = Plan::query()->where('code', 'basic')->firstOrFail();

        $this->post(route('platform.register.store'), [
            'company_name' => 'PT Pending Explain',
            'owner_name' => 'Pending Explain',
            'owner_email' => 'pending-explain@example.com',
            'owner_password' => 'secret12345',
            'owner_phone' => '0812000016',
            'plan_code' => $plan->code,
            'business_name' => 'Usaha Pending Explain',
            'branch_name' => 'Cabang Pending Explain',
        ])->assertRedirect(route('platform.register.pending'));

        $this->travel(61)->seconds();

        $this->post(route('platform.register.store'), [
            'company_name' => 'PT Pending Explain Update',
            'owner_name' => 'Pending Explain Update',
            'owner_email' => 'pending-explain@example.com',
            'owner_password' => 'secret12345',
            'owner_phone' => '0812000096',
            'plan_code' => $plan->code,
            'business_name' => 'Usaha Pending Explain Update',
            'branch_name' => 'Cabang Pending Explain Update',
        ])
            ->assertRedirect(route('platform.register.pending'))
            ->assertSessionHas('status', function (string $message): bool {
                return str_contains($message, 'masih menunggu verifikasi')
                    && str_contains($message, 'Data pendaftaran sebelumnya kami perbarui');
            });
    }

    public function test_public_registration_uses_configurable_default_trial_duration(): void
    {
        config()->set('platform.tenant_base_domain', 'poslive.test');
        config()->set('platform.tenant_database_driver', 'sqlite');
        $directory = storage_path('framework/testing/platform-registration-custom-trial');
        File::deleteDirectory($directory);
        config()->set('platform.tenant_database_directory', $directory);
        Mail::fake();

        PlatformSetting::query()->updateOrCreate(
            ['key' => 'default_trial_days'],
            ['value' => '21']
        );

        $plan = Plan::query()->where('code', 'trial')->firstOrFail();

        $this->post(route('platform.register.store'), [
            'company_name' => 'PT Trial 21 Hari',
            'owner_name' => 'Owner Trial 21',
            'owner_email' => 'owner-trial-21@example.com',
            'owner_password' => 'secret12345',
            'owner_phone' => '0812000021',
            'plan_code' => $plan->code,
            'business_name' => 'Usaha Trial 21',
            'branch_name' => 'Cabang Trial 21',
        ])->assertRedirect(route('platform.register.pending'));

        $pendingRegistration = PendingRegistration::query()->where('owner_email', 'owner-trial-21@example.com')->firstOrFail();

        $this->get(route('platform.register.verify', ['token' => $pendingRegistration->verification_token]))
            ->assertRedirect();

        $tenant = Tenant::query()->where('owner_email', 'owner-trial-21@example.com')->firstOrFail();
        $subscription = Subscription::query()->where('tenant_id', $tenant->id)->firstOrFail();

        $this->assertTrue($subscription->trial_ends_at->between(now()->addDays(20), now()->addDays(22)));
        $this->assertTrue($subscription->expired_at->between(now()->addDays(20), now()->addDays(22)));
        $this->assertTrue($tenant->trial_ends_at->between(now()->addDays(20), now()->addDays(22)));
    }

    public function test_it_rejects_duplicate_owner_email_for_public_registration(): void
    {
        $plan = Plan::query()->where('code', 'basic')->firstOrFail();

        Tenant::query()->create([
            'code' => 'existing-tenant',
            'name' => 'Tenant Existing',
            'owner_name' => 'Existing Owner',
            'owner_email' => 'platform-owner@example.com',
            'status' => 'pending_setup',
            'trial_ends_at' => now()->addDays(14),
        ]);

        $this->from(route('platform.register.create'))
            ->post(route('platform.register.store'), [
                'company_name' => 'PT Platform Test 2',
                'owner_name' => 'Platform Owner 2',
                'owner_email' => 'platform-owner@example.com',
                'owner_password' => 'secret12345',
                'owner_phone' => '0812000001',
                'plan_code' => $plan->code,
                'business_name' => 'Usaha Platform Test 2',
                'branch_name' => 'Cabang Platform Test 2',
                'notes' => 'Tenant duplicate email test',
            ])
            ->assertRedirect(route('platform.register.create'))
            ->assertSessionHasErrors([
                'owner_email' => 'Email owner ini sudah dipakai oleh tenant yang sudah terdaftar. Gunakan email lain atau masuk ke tenant yang sudah ada.',
            ]);

        $this->assertSame(1, Tenant::query()->where('owner_email', 'platform-owner@example.com')->count());
    }

    public function test_it_rejects_duplicate_owner_email_even_when_public_input_uses_spaces_and_uppercase(): void
    {
        $plan = Plan::query()->where('code', 'basic')->firstOrFail();

        Tenant::query()->create([
            'code' => 'existing-tenant-normalized',
            'name' => 'Tenant Existing Normalized',
            'owner_name' => 'Existing Owner',
            'owner_email' => 'normalized-owner@example.com',
            'status' => 'pending_setup',
            'trial_ends_at' => now()->addDays(14),
        ]);

        $this->from(route('platform.register.create'))
            ->post(route('platform.register.store'), [
                'company_name' => 'PT Platform Test Normalized',
                'owner_name' => 'Platform Owner Normalized',
                'owner_email' => '  NORMALIZED-OWNER@EXAMPLE.COM  ',
                'owner_password' => 'secret12345',
                'owner_phone' => '0812000002',
                'plan_code' => $plan->code,
                'business_name' => 'Usaha Platform Test Normalized',
                'branch_name' => 'Cabang Platform Test Normalized',
            ])
            ->assertRedirect(route('platform.register.create'))
            ->assertSessionHasErrors([
                'owner_email' => 'Email owner ini sudah dipakai oleh tenant yang sudah terdaftar. Gunakan email lain atau masuk ke tenant yang sudah ada.',
            ]);

        $this->assertSame(1, Tenant::query()->where('owner_email', 'normalized-owner@example.com')->count());
    }

    public function test_it_rejects_inactive_plan_for_public_registration(): void
    {
        Plan::query()->create([
            'code' => 'inactive-special',
            'name' => 'Inactive Special',
            'price' => 199000,
            'billing_cycle' => 'bulan',
            'max_businesses' => 1,
            'max_branches' => 1,
            'max_users' => 2,
            'features_json' => ['menu_dashboard'],
            'is_active' => false,
        ]);

        $this->from(route('platform.register.create'))
            ->post(route('platform.register.store'), [
                'company_name' => 'PT Plan Nonaktif',
                'owner_name' => 'Owner Nonaktif',
                'owner_email' => 'owner-nonaktif@example.com',
                'owner_password' => 'secret12345',
                'owner_phone' => '0812333444',
                'plan_code' => 'inactive-special',
                'business_name' => 'Usaha Nonaktif',
                'branch_name' => 'Cabang Nonaktif',
            ])
            ->assertRedirect(route('platform.register.create'))
            ->assertSessionHasErrors([
                'plan_code' => 'Paket yang dipilih tidak tersedia lagi. Silakan pilih paket aktif lainnya.',
            ]);
    }

    public function test_it_rejects_active_plan_that_is_not_open_for_public_registration(): void
    {
        PlatformSetting::query()->updateOrCreate(
            ['key' => 'public_registration_plan_codes'],
            ['value' => json_encode(['pro'])]
        );
        PlatformSetting::query()->updateOrCreate(
            ['key' => 'public_registration_default_plan_code'],
            ['value' => 'pro']
        );

        $this->from(route('platform.register.create'))
            ->post(route('platform.register.store'), [
                'company_name' => 'PT Paket Ditutup',
                'owner_name' => 'Owner Paket Ditutup',
                'owner_email' => 'owner-paket-ditutup@example.com',
                'owner_password' => 'secret12345',
                'owner_phone' => '0812333555',
                'plan_code' => 'basic',
                'business_name' => 'Usaha Paket Ditutup',
                'branch_name' => 'Cabang Paket Ditutup',
            ])
            ->assertRedirect(route('platform.register.create'))
            ->assertSessionHasErrors([
                'plan_code' => 'Paket ini sedang tidak dibuka untuk pendaftaran baru. Silakan pilih paket lain yang tersedia.',
            ]);
    }

    public function test_it_can_store_and_reload_a_registration_draft(): void
    {
        $plan = Plan::query()->where('code', 'basic')->firstOrFail();

        $response = $this->postJson(route('platform.register.draft.store'), [
            'company_name' => 'PT Draft Lintas Perangkat',
            'owner_name' => 'Draft Owner',
            'owner_email' => 'draft-owner@example.com',
            'owner_phone' => '08125550000',
            'plan_code' => $plan->code,
            'business_name' => 'Usaha Draft',
            'branch_name' => 'Cabang Draft',
            'notes' => 'Draft onboarding awal',
        ])->assertOk();

        $draftToken = $response->json('draft_token');

        $this->assertNotEmpty($draftToken);
        $this->assertDatabaseHas('platform_registration_drafts', [
            'resume_token' => $draftToken,
            'owner_email' => 'draft-owner@example.com',
        ]);

        $this->getJson(route('platform.register.draft.show', ['token' => $draftToken]))
            ->assertOk()
            ->assertJsonPath('payload.company_name', 'PT Draft Lintas Perangkat')
            ->assertJsonPath('payload.plan_code', $plan->code);
    }

    public function test_it_deletes_registration_draft_after_successful_registration(): void
    {
        config()->set('platform.tenant_base_domain', 'poslive.test');
        config()->set('platform.tenant_database_driver', 'sqlite');
        $directory = storage_path('framework/testing/platform-registration-drafts');
        File::deleteDirectory($directory);
        config()->set('platform.tenant_database_directory', $directory);
        Mail::fake();

        $plan = Plan::query()->where('code', 'basic')->firstOrFail();

        $draft = RegistrationDraft::query()->create([
            'resume_token' => 'draft-token-cleanup',
            'owner_email' => 'cleanup@example.com',
            'payload' => [
                'company_name' => 'PT Cleanup Draft',
                'owner_name' => 'Cleanup Owner',
            ],
            'last_seen_at' => now(),
        ]);

        $this->post(route('platform.register.store'), [
            'registration_draft_token' => $draft->resume_token,
            'company_name' => 'PT Cleanup Draft',
            'owner_name' => 'Cleanup Owner',
            'owner_email' => 'cleanup@example.com',
            'owner_password' => 'secret12345',
            'owner_phone' => '0812111222',
            'plan_code' => $plan->code,
            'business_name' => 'Usaha Cleanup Draft',
            'branch_name' => 'Cabang Cleanup',
            'notes' => 'Draft ini harus hilang setelah submit',
        ])->assertRedirect(route('platform.register.pending'));

        $this->assertDatabaseHas('platform_registration_drafts', [
            'resume_token' => 'draft-token-cleanup',
        ]);

        $pendingRegistration = PendingRegistration::query()->where('owner_email', 'cleanup@example.com')->firstOrFail();

        $this->get(route('platform.register.verify', ['token' => $pendingRegistration->verification_token]))
            ->assertRedirect();

        $this->assertDatabaseMissing('platform_registration_drafts', [
            'resume_token' => 'draft-token-cleanup',
        ]);

        File::deleteDirectory($directory);
    }

    public function test_it_prunes_only_expired_unused_pending_registrations(): void
    {
        $expired = PendingRegistration::query()->create([
            'owner_email' => 'expired-pending@example.com',
            'verification_token' => 'expired-token',
            'payload' => [
                'company_name' => 'Expired Pending',
                'owner_name' => 'Expired Owner',
            ],
            'verification_sent_at' => now()->subDays(5),
            'expires_at' => now()->subDays(4),
        ]);

        $fresh = PendingRegistration::query()->create([
            'owner_email' => 'fresh-pending@example.com',
            'verification_token' => 'fresh-token',
            'payload' => [
                'company_name' => 'Fresh Pending',
                'owner_name' => 'Fresh Owner',
            ],
            'verification_sent_at' => now()->subHours(2),
            'expires_at' => now()->addHours(20),
        ]);

        $consumed = PendingRegistration::query()->create([
            'owner_email' => 'consumed-pending@example.com',
            'verification_token' => 'consumed-token',
            'payload' => [
                'company_name' => 'Consumed Pending',
                'owner_name' => 'Consumed Owner',
            ],
            'verification_sent_at' => now()->subDays(5),
            'email_verified_at' => now()->subDays(4),
            'consumed_at' => now()->subDays(4),
            'expires_at' => now()->subDays(4),
        ]);

        $this->artisan('platform:prune-pending-registrations', ['--hours' => 72])
            ->expectsOutput('Pending registration yang dibersihkan: 1')
            ->assertSuccessful();

        $this->assertDatabaseMissing('platform_pending_registrations', [
            'id' => $expired->id,
        ]);
        $this->assertDatabaseHas('platform_pending_registrations', [
            'id' => $fresh->id,
        ]);
        $this->assertDatabaseHas('platform_pending_registrations', [
            'id' => $consumed->id,
        ]);
    }

    public function test_it_fails_gracefully_when_pending_registration_table_is_missing(): void
    {
        Schema::dropIfExists('platform_pending_registrations');
        $plan = Plan::query()->where('code', 'basic')->firstOrFail();

        $this->from(route('platform.register.create'))
            ->post(route('platform.register.store'), [
                'company_name' => 'PT Table Missing',
                'owner_name' => 'Owner Missing',
                'owner_email' => 'missing-table@example.com',
                'owner_password' => 'secret12345',
                'owner_phone' => '0812000000',
                'plan_code' => $plan->code,
                'business_name' => 'Usaha Missing',
                'branch_name' => 'Cabang Missing',
                'notes' => 'Should not crash',
            ])
            ->assertRedirect(route('platform.register.create'))
            ->assertSessionHasErrors([
                'owner_email' => 'Fitur verifikasi email pendaftaran belum aktif karena migration terbaru belum dijalankan. Jalankan migration platform terlebih dahulu.',
            ]);
    }
}
