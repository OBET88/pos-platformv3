<?php

namespace App\Http\Controllers;

use App\Mail\PlatformRegistrationReceived;
use App\Mail\PlatformRegistrationVerification;
use App\Models\Platform\PendingRegistration;
use App\Models\Platform\Plan;
use App\Models\Platform\RegistrationDraft;
use App\Models\Platform\Tenant;
use App\Services\Platform\PendingRegistrationService;
use App\Services\Platform\PlatformInboxPublisher;
use App\Services\Platform\PlatformSettings;
use App\Services\Platform\TenantRegistrationService;
use App\Services\Platform\TenantOnboardingDispatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class PlatformRegistrationController extends Controller
{
    private const REGISTRATION_VERIFICATION_COOLDOWN_SECONDS = 60;

    private const OFFICIAL_REGISTRATION_HOST = 'rwp.jajajuju.my.id';

    private const LEGACY_REGISTRATION_HOSTS = [
        'dkriuk.jajajuju.my.id',
    ];

    public function landing(): View
    {
        return view('platform.landingnew', [
            'plans' => $this->activePlans(),
        ]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        if ($this->shouldRedirectToOfficialRegistrationHost($request)) {
            return redirect()->away($this->officialRegistrationUrl($request));
        }

        $draftToken = $request->query('draft');
        $preferredPlanCode = $request->query('plan');
        $plans = $this->activePlans();
        $defaultPlanCode = app(PlatformSettings::class)->publicRegistrationDefaultPlanCode();
        $preferredPlanCode = is_string($preferredPlanCode) && $plans->contains('code', $preferredPlanCode)
            ? $preferredPlanCode
            : ($plans->contains('code', $defaultPlanCode) ? $defaultPlanCode : null);

        return view('platform.registernew', [
            'plans' => $plans,
            'preferredPlanCode' => $preferredPlanCode,
            'initialDraftToken' => is_string($draftToken) ? $draftToken : null,
            'draftSaveUrl' => route('platform.register.draft.store'),
            'draftLoadUrlTemplate' => route('platform.register.draft.show', ['token' => '__TOKEN__']),
        ]);
    }

    private function shouldRedirectToOfficialRegistrationHost(Request $request): bool
    {
        return in_array($request->getHost(), self::LEGACY_REGISTRATION_HOSTS, true);
    }

    private function officialRegistrationUrl(Request $request): string
    {
        $query = $request->getQueryString();

        return 'https://'.self::OFFICIAL_REGISTRATION_HOST.'/daftar'.($query ? '?'.$query : '');
    }

    public function help(): View
    {
        return view('platform.help');
    }

    public function ownerEmailStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'owner_email' => ['nullable', 'email', 'max:255'],
        ]);

        $ownerEmail = $this->normalizeEmail((string) ($validated['owner_email'] ?? ''));

        if ($ownerEmail === '') {
            return response()->json([
                'status' => 'empty',
                'message' => 'Masukkan email owner untuk cek statusnya.',
            ]);
        }

        $tenant = Tenant::query()
            ->where('owner_email', $ownerEmail)
            ->with('domains')
            ->first(['id', 'code', 'owner_email']);

        if ($tenant) {
            $loginUrl = $tenant->domains->firstWhere('is_primary', true)?->domain
                ? 'http://'.$tenant->domains->firstWhere('is_primary', true)?->domain.'/login'
                : null;

            return response()->json([
                'status' => 'registered',
                'message' => 'Email ini sudah dipakai oleh tenant yang terdaftar. Gunakan email lain atau masuk ke tenant yang sudah ada.',
                'action_label' => $loginUrl ? 'Buka Login Tenant' : 'Butuh Bantuan Login?',
                'action_url' => $loginUrl ?: route('platform.help'),
            ]);
        }

        if ($this->hasPendingRegistrationsTable()) {
            $pendingRegistration = $this->findActivePendingRegistrationByEmail($ownerEmail);

            if ($pendingRegistration) {
                return response()->json([
                    'status' => 'pending',
                    'message' => 'Email ini masih menunggu verifikasi. Jika Anda lanjut daftar dengan email yang sama, sistem akan memperbarui request sebelumnya.',
                    'cooldown_seconds_remaining' => $this->verificationCooldownSecondsRemaining($pendingRegistration),
                    'action_label' => 'Butuh Bantuan?',
                    'action_url' => route('platform.help'),
                ]);
            }
        }

        return response()->json([
            'status' => 'available',
            'message' => 'Email ini siap dipakai untuk pendaftaran tenant baru.',
        ]);
    }

    public function contact(): View
    {
        return view('platform.contact');
    }

    public function blog(): View
    {
        return view('platform.blog');
    }

    protected function activePlans()
    {
        return app(PlatformSettings::class)->publicRegistrationPlans();
    }

    public function store(
        Request $request,
        TenantRegistrationService $registrationService,
        PlatformInboxPublisher $inboxPublisher
    ): RedirectResponse {
        $tooManyAttempts = $this->ensureRegistrationThrottled($request, 'registration-store', 5, 300);
        if ($tooManyAttempts !== null) {
            return $tooManyAttempts;
        }

        $this->prepareRegistrationRequest();
        $this->normalizeRegistrationInput($request);

        if (! $this->hasPendingRegistrationsTable()) {
            return back()
                ->withInput($request->except('owner_password'))
                ->withErrors([
                    'owner_email' => 'Fitur verifikasi email pendaftaran belum aktif karena migration terbaru belum dijalankan. Jalankan migration platform terlebih dahulu.',
                ]);
        }

        $validated = $this->validateRegistration($request);
        $validated['owner_email'] = $this->normalizeEmail((string) $validated['owner_email']);
        $hadPendingRegistration = $this->findActivePendingRegistrationByEmail($validated['owner_email']) !== null;
        $pendingRegistration = $this->storePendingRegistration($validated);
        $dispatchResult = $this->dispatchPendingRegistrationVerification(
            $pendingRegistration,
            $validated['owner_name'],
            $validated['company_name'],
        );

        $redirect = redirect()
            ->route('platform.register.pending')
            ->with('pending_registration_email', $validated['owner_email'])
            ->with('registration_email_sent', $dispatchResult['sent']);

        if ($hadPendingRegistration) {
            $statusMessage = $dispatchResult['sent']
                ? 'Email ini masih menunggu verifikasi. Data pendaftaran sebelumnya kami perbarui dan link verifikasi terbaru sudah dikirim ke '.$validated['owner_email'].'.'
                : null;
            $warningMessage = $dispatchResult['warning'] !== null
                ? 'Email ini masih menunggu verifikasi. Data pendaftaran sebelumnya kami perbarui, tetapi '.$dispatchResult['warning']
                : null;

            if ($statusMessage !== null) {
                $redirect->with('status', $statusMessage);
            }

            if ($warningMessage !== null) {
                $redirect->with('warning', $warningMessage);
            }
        } else {
            if ($dispatchResult['status'] !== null) {
                $redirect->with('status', $dispatchResult['status']);
            }

            if ($dispatchResult['warning'] !== null) {
                $redirect->with('warning', $dispatchResult['warning']);
            }
        }

        return $redirect;
    }

    public function pending(Request $request, PendingRegistrationService $pending): View|RedirectResponse
    {
        if (! $pending->hasPendingRegistrationsTable()) {
            return redirect()
                ->route('platform.register.create')
                ->withErrors([
                    'owner_email' => 'Halaman status verifikasi belum tersedia karena migration platform terbaru belum dijalankan.',
                ]);
        }

        $ownerEmail = (string) session('pending_registration_email', '');

        if ($ownerEmail === '') {
            return redirect()
                ->route('platform.register.create')
                ->withErrors([
                    'owner_email' => 'Status verifikasi tidak ditemukan di sesi browser ini. Silakan kirim ulang pendaftaran untuk mendapatkan link verifikasi terbaru.',
                ]);
        }

        return view('platform.register-pending');
    }

    public function resendVerification(Request $request, PendingRegistrationService $pending): RedirectResponse
    {
        if (! $pending->hasPendingRegistrationsTable()) {
            return redirect()
                ->route('platform.register.create')
                ->withErrors([
                    'owner_email' => 'Fitur verifikasi email pendaftaran belum aktif karena migration terbaru belum dijalankan.',
                ]);
        }

        $this->prepareRegistrationRequest();
        $this->normalizeRegistrationInput($request);

        $ownerEmail = $pending->normalizeEmail(
            (string) ($request->input('owner_email') ?: session('pending_registration_email', ''))
        );

        if ($ownerEmail === '') {
            return redirect()
                ->route('platform.register.create')
                ->withErrors([
                    'owner_email' => 'Status verifikasi tidak ditemukan di sesi browser ini. Silakan isi ulang form pendaftaran untuk mendapatkan link verifikasi terbaru.',
                ]);
        }

        $result = $pending->resend($ownerEmail, $request->ip());

        if ($result['throttled']) {
            $minutes = (int) ceil(((int) $result['retry_after']) / 60);

            return back()->withErrors([
                'owner_email' => 'Terlalu banyak permintaan dalam waktu singkat. Silakan coba lagi dalam '
                    .$minutes
                    .' menit.',
            ])->setStatusCode(429);
        }

        if ($result['missing']) {
            return redirect()
                ->route('platform.register.create')
                ->withErrors([
                    'owner_email' => 'Request verifikasi aktif untuk email ini tidak ditemukan. Silakan isi ulang form pendaftaran.',
                ]);
        }

        $redirect = redirect()
            ->route('platform.register.pending')
            ->with('pending_registration_email', $ownerEmail)
            ->with('registration_email_sent', $result['sent']);

        if ($result['status'] !== null) {
            $redirect->with('status', $result['status']);
        }

        if ($result['warning'] !== null) {
            $redirect->with('warning', $result['warning']);
        }

        return $redirect;
    }

    public function verify(
        Request $request,
        string $token,
        TenantRegistrationService $registrationService,
        PlatformInboxPublisher $inboxPublisher,
        TenantOnboardingDispatcher $onboardingDispatcher,
    ): RedirectResponse {
        $this->prepareRegistrationRequest();

        if (! $this->hasPendingRegistrationsTable()) {
            return redirect()
                ->route('platform.register.create')
                ->withErrors([
                    'owner_email' => 'Fitur verifikasi email pendaftaran belum aktif karena migration terbaru belum dijalankan.',
                ]);
        }

        $pendingRegistration = PendingRegistration::query()
            ->where('verification_token', $token)
            ->first();

        if (! $pendingRegistration) {
            return redirect()
                ->route('platform.register.create')
                ->withErrors([
                    'owner_email' => 'Link verifikasi tidak ditemukan atau sudah tidak berlaku. Silakan kirim ulang pendaftaran untuk mendapatkan link verifikasi terbaru.',
                ]);
        }

        if ($pendingRegistration->tenant_id) {
            $tenant = Tenant::query()->findOrFail($pendingRegistration->tenant_id);

            $invoice = \App\Models\Platform\PlatformPaymentTransaction::query()
                ->where('tenant_id', $tenant->getKey())
                ->where('type', 'registration')
                ->whereNotIn('status', ['paid', 'cancelled', 'refunded'])
                ->latest('id')
                ->first();

            if ($invoice !== null) {
                return redirect()
                    ->route('platform.payment.show', ['invoiceNumber' => $invoice->invoice_number]);
            }

            return redirect()
                ->route('platform.register.pending')
                ->with('status', 'Email ini sudah diverifikasi sebelumnya. Gunakan tautan status pada email konfirmasi untuk memantau onboarding tenant.');
        }

        if ($pendingRegistration->expires_at?->isPast()) {
            return redirect()
                ->route('platform.register.create')
                ->withErrors([
                    'owner_email' => 'Link verifikasi sudah kedaluwarsa. Silakan kirim ulang pendaftaran untuk mendapatkan link baru.',
                ]);
        }

        $validated = (array) ($pendingRegistration->payload ?? []);
        $validated['owner_email'] = $this->normalizeEmail((string) ($validated['owner_email'] ?? $pendingRegistration->owner_email));
        $validated['owner_password'] = Crypt::decryptString((string) ($validated['owner_password_encrypted'] ?? ''));
        $validated['owner_email_verified_at'] = ($pendingRegistration->email_verified_at ?? now())->toDateTimeString();

        if (! empty($validated['registration_draft_token'])) {
            RegistrationDraft::query()
                ->where('resume_token', (string) $validated['registration_draft_token'])
                ->delete();
        }

        $selectedPlan = Plan::query()
            ->where('code', (string) $validated['plan_code'])
            ->where('is_active', true)
            ->firstOrFail();
        $requiresRegistrationPayment = (float) $selectedPlan->price > 0;
        $runSynchronously = false;

        if ($requiresRegistrationPayment) {
            $registrationInvoice = $registrationService->registerWithPendingPayment($validated);
            $tenant = $registrationInvoice->tenant()->with('domains')->firstOrFail();
            $bootstrapDispatched = false;
        } else {
            $tenant = $registrationService->register($validated, $runSynchronously);
            $bootstrapDispatched = $runSynchronously;
        }

        $pendingRegistration->forceFill([
            'email_verified_at' => $pendingRegistration->email_verified_at ?? now(),
            'consumed_at' => now(),
            'tenant_id' => $tenant->getKey(),
        ])->save();

        $accessToken = $onboardingDispatcher->issuePublicAccessToken($tenant);

        $inboxPublisher->publish(
            tenant: $tenant,
            eventKey: 'tenant.registered',
            title: 'Tenant baru mendaftar',
            message: $tenant->name.' baru masuk ke platform dan menunggu monitoring onboarding.',
            category: 'registration',
            tone: 'sky',
            payload: [
                'tenant_code' => $tenant->code,
                'owner_email' => $tenant->owner_email,
            ],
            occurredAt: $tenant->created_at ?? now(),
            fingerprint: 'tenant.registered:'.$tenant->getKey(),
        );

        $tenantLoginUrl = $tenant->domains->firstWhere('is_primary', true)?->domain
            ? 'http://'.$tenant->domains->firstWhere('is_primary', true)?->domain.'/login'
            : null;
        $statusUrl = $onboardingDispatcher->accessUrl($tenant, $accessToken);
        $registrationEmailSent = false;

        if ($this->shouldSendPlatformMailNow()) {
            try {
                Mail::to($validated['owner_email'])->send(new PlatformRegistrationReceived(
                    ownerName: (string) $validated['owner_name'],
                    companyName: (string) $validated['company_name'],
                    tenantCode: $tenant->code,
                    statusUrl: $statusUrl,
                    loginUrl: $tenantLoginUrl,
                ));
                $registrationEmailSent = true;
            } catch (Throwable $exception) {
                Log::warning('Platform registration confirmation email failed to send.', [
                    'tenant_code' => $tenant->code,
                    'owner_email' => $validated['owner_email'],
                    'error' => $exception->getMessage(),
                ]);
            }
        } else {
            Log::info('Platform registration confirmation email skipped in local environment.', [
                'tenant_code' => $tenant->code,
                'owner_email' => $validated['owner_email'],
            ]);
        }

        $redirect = $requiresRegistrationPayment
            ? redirect()->route('platform.payment.show', ['invoiceNumber' => $registrationInvoice->invoice_number, 'access' => $accessToken])
            : redirect()->route('platform.register.success', ['tenant' => $tenant->code, 'access' => $accessToken]);

        return $redirect
            ->with('registration_success', true)
            ->with('tenant_bootstrap_dispatched', $bootstrapDispatched)
            ->with('tenant_login_url', $tenantLoginUrl)
            ->with('registration_email_sent', $registrationEmailSent);
    }

    public function storeDraft(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'draft_token' => ['nullable', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'owner_name' => ['nullable', 'string', 'max:255'],
            'owner_email' => ['nullable', 'email', 'max:255'],
            'owner_phone' => ['nullable', 'string', 'max:50'],
            'plan_code' => ['nullable', 'exists:plans,code'],
            'business_name' => ['nullable', 'string', 'max:255'],
            'branch_name' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $payload = collect($validated)
            ->except('draft_token')
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->all();

        $token = isset($validated['draft_token']) && is_string($validated['draft_token']) && $validated['draft_token'] !== ''
            ? $validated['draft_token']
            : (string) Str::uuid();

        $draft = RegistrationDraft::query()->firstOrNew([
            'resume_token' => $token,
        ]);

        $draft->owner_email = isset($payload['owner_email'])
            ? strtolower(trim((string) $payload['owner_email']))
            : $draft->owner_email;
        $draft->payload = $payload;
        $draft->last_seen_at = now();
        $draft->save();

        return response()->json([
            'draft_token' => $draft->resume_token,
            'resume_url' => route('platform.register.create', ['draft' => $draft->resume_token]),
            'payload' => $draft->payload,
            'updated_at' => optional($draft->updated_at)->toISOString(),
        ]);
    }

    public function showDraft(string $token): JsonResponse
    {
        $draft = RegistrationDraft::query()
            ->where('resume_token', $token)
            ->firstOrFail();

        $draft->forceFill([
            'last_seen_at' => now(),
        ])->save();

        return response()->json([
            'draft_token' => $draft->resume_token,
            'payload' => $draft->payload ?? [],
            'resume_url' => route('platform.register.create', ['draft' => $draft->resume_token]),
            'updated_at' => optional($draft->updated_at)->toISOString(),
        ]);
    }

    public function success(Request $request, string $tenant, TenantOnboardingDispatcher $onboardingDispatcher): View|RedirectResponse
    {
        $tenantModel = Tenant::query()->with('onboardingJob')->where('code', $tenant)->first();

        if (! $tenantModel) {
            return redirect()
                ->route('platform.register.create')
                ->withErrors([
                    'owner_email' => 'Status pendaftaran tenant tidak ditemukan. Kemungkinan link ini sudah lama, tenant pernah dibersihkan, atau proses pendaftaran perlu diulang dari awal.',
                ]);
        }

        if (! $onboardingDispatcher->hasValidPublicAccess($tenantModel, $request->query('access'))) {
            abort(404);
        }

        $initialLogs = $this->resolveProvisionLogs($tenantModel);
        $bootstrapStatus = $this->resolveBootstrapStatus($tenantModel);

        return view('platform.register-success', [
            'tenantCode' => $tenant,
            'bootstrapStatus' => $bootstrapStatus,
            'bootstrapUrl' => route('platform.register.bootstrap', ['tenant' => $tenant, 'access' => $request->query('access')]),
            'statusUrl' => route('platform.register.status', ['tenant' => $tenant, 'access' => $request->query('access')]),
            'bootstrapDispatched' => app()->environment('local')
                ? false
                : (bool) session('tenant_bootstrap_dispatched', false),
            'initialLogs' => $initialLogs,
            'tenantLoginUrl' => $tenantModel->domains->firstWhere('is_primary', true)?->domain
                ? 'http://'.$tenantModel->domains->firstWhere('is_primary', true)?->domain.'/login'
                : session('tenant_login_url'),
        ]);
    }

    public function bootstrap(Request $request, string $tenant, TenantRegistrationService $registrationService, TenantOnboardingDispatcher $onboardingDispatcher): JsonResponse
    {
        $tenantModel = Tenant::query()->where('code', $tenant)->first();

        if (! $tenantModel) {
            return response()->json([
                'status' => 'missing',
                'message' => 'Tenant untuk status pendaftaran ini sudah tidak ditemukan. Silakan ulangi pendaftaran dari awal.',
            ], 404);
        }

        if (! $onboardingDispatcher->hasValidPublicAccess($tenantModel, $request->input('access'))) {
            return response()->json(['status' => 'missing'], 404);
        }

        $throttleKey = 'registration-bootstrap:'.$tenantModel->getKey().':'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 10)) {
            return response()->json([
                'status' => 'throttled',
                'message' => 'Terlalu banyak permintaan bootstrap. Silakan coba lagi dalam '.max(RateLimiter::availableIn($throttleKey), 1).' detik.',
            ], 429);
        }

        RateLimiter::hit($throttleKey, 300);

        if ($tenantModel->provisionLogs()->where('step', 'auto_onboarding_completed')->where('status', 'completed')->exists()) {
            return response()->json([
                'status' => 'completed',
            ]);
        }

        try {
            if ($onboardingDispatcher->retry($tenantModel)) {
                return response()->json(['status' => 'processing'], 202);
            }

            $registrationService->startAutoOnboarding($tenantModel);
            $completed = $tenantModel->provisionLogs()
                ->where('step', 'auto_onboarding_completed')
                ->where('status', 'completed')
                ->exists();

            if (! $completed) {
                return response()->json([
                    'status' => 'processing',
                ], 202);
            }

            return response()->json([
                'status' => 'completed',
                'tenant_code' => $tenantModel->code,
            ]);
        } catch (Throwable $exception) {
            return response()->json([
                'status' => 'failed',
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    public function status(Request $request, string $tenant, TenantOnboardingDispatcher $onboardingDispatcher): JsonResponse
    {
        $tenantModel = Tenant::query()->with('onboardingJob')->where('code', $tenant)->first();

        if (! $tenantModel) {
            return response()->json([
                'status' => 'missing',
                'logs' => [],
                'message' => 'Tenant untuk status pendaftaran ini sudah tidak ditemukan. Silakan ulangi pendaftaran dari awal.',
            ], 404);
        }

        if (! $onboardingDispatcher->hasValidPublicAccess($tenantModel, $request->query('access'))) {
            return response()->json(['status' => 'missing', 'logs' => []], 404);
        }

        return response()->json([
            'status' => $this->resolveBootstrapStatus($tenantModel),
            'logs' => $this->resolveProvisionLogs($tenantModel),
        ]);
    }

    private function resolveProvisionLogs(Tenant $tenant): array
    {
        return $tenant->provisionLogs()
            ->latest('id')
            ->take(6)
            ->get(['step', 'status', 'message', 'created_at'])
            ->reverse()
            ->values()
            ->toArray();
    }

    private function resolveBootstrapStatus(Tenant $tenant): string
    {
        $jobStatus = $tenant->onboardingJob?->status;

        if (in_array($jobStatus, ['failed', 'blocked', 'retry_wait'], true)) {
            return $jobStatus;
        }

        if ($tenant->provisionLogs()
            ->where('step', 'auto_onboarding_completed')
            ->where('status', 'completed')
            ->exists()) {
            return 'completed';
        }

        if ($tenant->provisionLogs()
            ->where('step', 'auto_onboarding_completed')
            ->where('status', 'failed')
            ->exists()) {
            return 'failed';
        }

        return 'processing';
    }

    private function prepareRegistrationRequest(): void
    {
        if (PHP_SAPI === 'cli') {
            return;
        }

        if (function_exists('set_time_limit')) {
            try {
                set_time_limit(0);
            } catch (Throwable) {
                // Ignore hosts that block runtime time-limit changes.
            }
        }

        if (function_exists('ignore_user_abort')) {
            ignore_user_abort(true);
        }
    }

    private function validateRegistration(Request $request): array
    {
        $publicPlanCodes = app(PlatformSettings::class)->publicRegistrationPlanCodes();

        return $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_email' => ['required', 'email', 'max:255', Rule::unique('tenants', 'owner_email')],
            'owner_password' => ['required', 'string', 'min:8', 'max:255'],
            'owner_phone' => ['nullable', 'string', 'max:50'],
            'plan_code' => [
                'required',
                Rule::exists('plans', 'code')->where('is_active', true),
                Rule::in($publicPlanCodes),
            ],
            'business_name' => ['required', 'string', 'max:255'],
            'branch_name' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'registration_draft_token' => ['nullable', 'string', 'max:255'],
        ], [
            'owner_email.unique' => 'Email owner ini sudah dipakai oleh tenant yang sudah terdaftar. Gunakan email lain atau masuk ke tenant yang sudah ada.',
            'plan_code.exists' => 'Paket yang dipilih tidak tersedia lagi. Silakan pilih paket aktif lainnya.',
            'plan_code.in' => 'Paket ini sedang tidak dibuka untuk pendaftaran baru. Silakan pilih paket lain yang tersedia.',
        ]);
    }

    private function shouldAttemptAutoOnboardingRefresh(Tenant $tenant): bool
    {
        if ($tenant->provisionLogs()
            ->where('step', 'auto_onboarding_completed')
            ->where('status', 'completed')
            ->exists()) {
            return false;
        }

        $latestBootstrapLog = $tenant->provisionLogs()
            ->where('step', 'tenant_app_bootstrap')
            ->latest('id')
            ->first(['status', 'updated_at']);

        if ($latestBootstrapLog === null) {
            return false;
        }

        if ($latestBootstrapLog->status === 'failed') {
            return true;
        }

        return $latestBootstrapLog->status === 'pending'
            && $latestBootstrapLog->updated_at !== null
            && $latestBootstrapLog->updated_at->lt(now()->subMinute());
    }

    private function normalizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }

    /**
     * Throttle anti email-bombing untuk endpoint publik pendaftaran.
     * Dihitung per kombinasi email tujuan + IP supaya satu penyerang tidak
     * bisa membanjiri satu alamat email korban dari banyak form, dan satu IP
     * tidak bisa menyerang banyak korban sekaligus tanpa batas.
     *
     * Return null bila boleh lanjut, atau RedirectResponse 429 bila melebihi batas.
     */
    private function ensureRegistrationThrottled(Request $request, string $bucket, int $maxAttempts, int $decaySeconds): ?RedirectResponse
    {
        $targetEmail = $this->normalizeEmail((string) $request->input('owner_email', (string) session('pending_registration_email', '')));
        $key = $bucket.':'.($targetEmail !== '' ? $targetEmail.'|' : '').$request->ip();

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = max(RateLimiter::availableIn($key), 1);
            $minutes = (int) ceil($seconds / 60);

            return back()->withErrors([
                'owner_email' => 'Terlalu banyak permintaan dalam waktu singkat. Silakan coba lagi dalam '
                    .$minutes
                    .' menit.',
            ])->setStatusCode(429);
        }

        RateLimiter::hit($key, $decaySeconds);

        return null;
    }

    private function normalizeRegistrationInput(Request $request): void
    {
        if ($request->has('owner_email')) {
            $request->merge([
                'owner_email' => $this->normalizeEmail((string) $request->input('owner_email')),
            ]);
        }
    }

    private function shouldSendPlatformMailNow(): bool
    {
        if (app()->runningUnitTests()) {
            return true;
        }

        if (! app()->environment('local')) {
            return true;
        }

        $defaultMailer = (string) config('mail.default', 'log');
        $transport = (string) config("mail.mailers.{$defaultMailer}.transport", $defaultMailer);

        if (in_array($transport, ['array', 'log'], true)) {
            return false;
        }

        return filled(config('mail.from.address'))
            && filled(config("mail.mailers.{$defaultMailer}.host"));
    }

    private function storePendingRegistration(array $validated): PendingRegistration
    {
        $pendingRegistration = PendingRegistration::query()->firstOrNew([
            'owner_email' => $validated['owner_email'],
        ]);

        $shouldReuseExistingToken = $pendingRegistration->exists
            && $pendingRegistration->tenant_id === null
            && $pendingRegistration->consumed_at === null
            && filled($pendingRegistration->verification_token);

        $payload = $validated;
        $payload['owner_password_encrypted'] = Crypt::encryptString((string) $validated['owner_password']);
        unset($payload['owner_password']);

        $pendingRegistration->fill([
            'verification_token' => $shouldReuseExistingToken
                ? (string) $pendingRegistration->verification_token
                : (string) Str::uuid(),
            'payload' => $payload,
            'verification_sent_at' => $pendingRegistration->verification_sent_at,
            'email_verified_at' => null,
            'consumed_at' => null,
            'expires_at' => now()->addHours(24),
            'tenant_id' => null,
        ]);
        $pendingRegistration->save();

        return $pendingRegistration;
    }

    private function dispatchPendingRegistrationVerification(
        PendingRegistration $pendingRegistration,
        string $ownerName,
        string $companyName,
    ): array {
        $ownerEmail = $this->normalizeEmail((string) $pendingRegistration->owner_email);

        if ($this->hasActiveVerificationCooldown($pendingRegistration)) {
            $secondsRemaining = $this->verificationCooldownSecondsRemaining($pendingRegistration);

            return [
                'sent' => false,
                'status' => null,
                'warning' => 'Link verifikasi untuk '.$ownerEmail.' baru saja dikirim. Tunggu sekitar '.$secondsRemaining.' detik sebelum mencoba lagi.',
            ];
        }

        if ($this->shouldSendPlatformMailNow()) {
            try {
                Mail::to($ownerEmail)->send(new PlatformRegistrationVerification(
                    ownerName: $ownerName,
                    companyName: $companyName,
                    verificationUrl: route('platform.register.verify', ['token' => $pendingRegistration->verification_token]),
                    expiresAtLabel: $pendingRegistration->expires_at?->timezone(config('app.timezone'))->format('d M Y H:i'),
                ));

                $pendingRegistration->forceFill([
                    'verification_sent_at' => now(),
                    'expires_at' => now()->addHours(24),
                ])->save();

                return [
                    'sent' => true,
                    'status' => 'Link verifikasi sudah dikirim ke '.$ownerEmail.'.',
                    'warning' => null,
                ];
            } catch (Throwable $exception) {
                Log::warning('Platform registration verification email failed to send.', [
                    'owner_email' => $ownerEmail,
                    'error' => $exception->getMessage(),
                ]);
            }
        } else {
            Log::info('Platform registration verification email skipped in local environment.', [
                'owner_email' => $ownerEmail,
                'verification_url' => route('platform.register.verify', ['token' => $pendingRegistration->verification_token]),
            ]);
        }

        return [
            'sent' => false,
            'status' => null,
            'warning' => 'Percobaan pengiriman untuk '.$ownerEmail.' tercatat, tetapi email belum terkonfirmasi terkirim dari aplikasi ini.',
        ];
    }

    private function hasActiveVerificationCooldown(PendingRegistration $pendingRegistration): bool
    {
        return $pendingRegistration->verification_sent_at !== null
            && $pendingRegistration->verification_sent_at->gt(now()->subSeconds(self::REGISTRATION_VERIFICATION_COOLDOWN_SECONDS));
    }

    private function verificationCooldownSecondsRemaining(PendingRegistration $pendingRegistration): int
    {
        if ($pendingRegistration->verification_sent_at === null) {
            return 0;
        }

        return max(
            1,
            self::REGISTRATION_VERIFICATION_COOLDOWN_SECONDS - $pendingRegistration->verification_sent_at->diffInSeconds(now())
        );
    }

    private function hasPendingRegistrationsTable(): bool
    {
        return Schema::hasTable('platform_pending_registrations');
    }

    private function findActivePendingRegistrationByEmail(string $ownerEmail): ?PendingRegistration
    {
        return PendingRegistration::query()
            ->where('owner_email', $ownerEmail)
            ->whereNull('tenant_id')
            ->whereNull('consumed_at')
            ->latest('id')
            ->first();
    }
}
