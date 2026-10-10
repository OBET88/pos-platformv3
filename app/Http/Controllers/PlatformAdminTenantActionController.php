<?php

namespace App\Http\Controllers;

use App\Models\Platform\Plan;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;
use App\Models\Platform\TenantDomain;
use App\Models\Platform\TenantProvisionLog;
use App\Services\Platform\TenantAuditLogger;
use App\Services\Platform\TenantAppBootstrapService;
use App\Services\Platform\TenantProvisioningService;
use App\Services\Platform\TenantStatusGuard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RuntimeException;
use Throwable;
use Illuminate\Support\Facades\Auth;

class PlatformAdminTenantActionController extends Controller
{
    public function updateProfile(Request $request, Tenant $tenant, TenantAuditLogger $auditLogger): RedirectResponse
    {
        $validated = $request->validate([
            'tenant_name' => ['required', 'string', 'max:255'],
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_phone' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $before = [
            'name' => $tenant->name,
            'owner_name' => $tenant->owner_name,
            'owner_phone' => $tenant->owner_phone,
            'notes' => $tenant->notes,
        ];

        $tenant->forceFill([
            'name' => $validated['tenant_name'],
            'owner_name' => $validated['owner_name'],
            'owner_phone' => $validated['owner_phone'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ])->save();

        $this->writeProvisionLog($tenant, 'tenant-profile-update', 'done', 'Profil tenant diperbarui dari panel admin platform.');
        $auditLogger->log(
            $tenant,
            'tenant.profile.updated',
            'Profil tenant diperbarui dari panel admin.',
            [
                'before' => $before,
                'after' => [
                    'name' => $tenant->name,
                    'owner_name' => $tenant->owner_name,
                    'owner_phone' => $tenant->owner_phone,
                    'notes' => $tenant->notes,
                ],
            ]
        );

        return $this->redirectToTenantIndex($request, $tenant, 'Profil tenant berhasil diperbarui.');
    }

    public function provision(Request $request, Tenant $tenant, TenantProvisioningService $provisioningService, TenantAuditLogger $auditLogger): RedirectResponse
    {
        try {
            $provisioningService->provision($tenant);
        } catch (RuntimeException $exception) {
            $auditLogger->log(
                $tenant,
                'tenant.provision.failed',
                'Recovery provisioning tenant gagal dari panel admin.',
                [],
                ['error' => $exception->getMessage()]
            );

            return $this->redirectToTenantIndex($request, $tenant)
                ->with('platform_admin_error', $exception->getMessage());
        }

        $auditLogger->log(
            $tenant,
            'tenant.provision.recovered',
            'Recovery provisioning tenant berhasil dijalankan dari panel admin.'
        );

        return $this->redirectToTenantIndex($request, $tenant, 'Recovery provisioning tenant selesai. Database tenant sudah diverifikasi ulang.');
    }

    public function bootstrap(
        Request $request,
        Tenant $tenant,
        TenantProvisioningService $provisioningService,
        TenantAppBootstrapService $bootstrapService,
        TenantAuditLogger $auditLogger
    ): RedirectResponse
    {
        $this->prepareLongRunningExecution();

        $validated = $request->validate([
            'owner_user_name' => ['required', 'string', 'max:255'],
            'owner_user_email' => ['required', 'email', 'max:255'],
            'owner_user_password' => ['required', 'string', 'min:8', 'max:255'],
            'business_name' => ['required', 'string', 'max:255'],
            'branch_name' => ['required', 'string', 'max:255'],
            'owner_role' => ['required', Rule::in(['owner', 'administrator', 'admin'])],
        ]);

        $lastException = null;
        $attemptDelaysInMicroseconds = [
            2000000,
            4000000,
            7000000,
        ];

        for ($attempt = 1; $attempt <= count($attemptDelaysInMicroseconds) + 1; $attempt++) {
            try {
                $tenant = $provisioningService->provision($tenant);
                $this->waitForFreshTenantDatabaseStabilization($tenant);

                $bootstrapService->bootstrap($tenant, [
                    'owner_name' => $validated['owner_user_name'],
                    'owner_email' => $validated['owner_user_email'],
                    'owner_password' => $validated['owner_user_password'],
                    'business_name' => $validated['business_name'],
                    'branch_name' => $validated['branch_name'],
                    'owner_role' => $validated['owner_role'],
                ]);

                $message = $attempt === 1
                    ? 'Recovery bootstrap tenant app selesai. Database tenant juga diverifikasi ulang otomatis sebelum setup owner awal.'
                    : 'Recovery bootstrap tenant app selesai setelah sistem memulihkan percobaan awal yang sempat gagal.';

                $auditLogger->log(
                    $tenant,
                    'tenant.bootstrap.recovered',
                    'Recovery bootstrap tenant app berhasil dijalankan dari panel admin.',
                    [
                        'after' => [
                            'owner_name' => $validated['owner_user_name'],
                            'owner_email' => $validated['owner_user_email'],
                            'business_name' => $validated['business_name'],
                            'branch_name' => $validated['branch_name'],
                            'owner_role' => $validated['owner_role'],
                        ],
                    ],
                    ['attempt' => $attempt]
                );

                return $this->redirectToTenantIndex($request, $tenant, $message);
            } catch (Throwable $exception) {
                $lastException = $exception;

                if (
                    !$exception instanceof RuntimeException
                    || $attempt > count($attemptDelaysInMicroseconds)
                    || !$this->shouldRetryBootstrapFromUi($exception)
                ) {
                    break;
                }

                usleep($attemptDelaysInMicroseconds[$attempt - 1]);
            }
        }

        $auditLogger->log(
            $tenant,
            'tenant.bootstrap.failed',
            'Recovery bootstrap tenant app gagal dari panel admin.',
            [],
            ['error' => $lastException?->getMessage()]
        );

        return $this->redirectToTenantIndex($request, $tenant)
            ->with('platform_admin_error', $lastException?->getMessage() ?? 'Bootstrap tenant app gagal dijalankan.')
            ->withErrors(['owner_user_password' => $lastException?->getMessage() ?? 'Bootstrap tenant app gagal dijalankan.'])
            ->withInput();
    }

    private function prepareLongRunningExecution(): void
    {
        if (PHP_SAPI === 'cli') {
            return;
        }

        if (function_exists('set_time_limit')) {
            try {
                set_time_limit(0);
            } catch (\Throwable) {
                // Ignore hosts that disable runtime time-limit changes.
            }
        }

        if (function_exists('ignore_user_abort')) {
            ignore_user_abort(true);
        }
    }

    private function shouldRetryBootstrapFromUi(Throwable $exception): bool
    {
        $message = strtolower($exception->getMessage());

        return str_contains($message, 'sqlstate[hy000] [2002]')
            || str_contains($message, 'unknown error while connecting')
            || str_contains($message, 'connection refused')
            || str_contains($message, 'base table or view already exists')
            || str_contains($message, 'duplicate column name')
            || str_contains($message, 'duplicate key name')
            || str_contains($message, 'duplicate entry');
    }

    private function waitForFreshTenantDatabaseStabilization(Tenant $tenant): void
    {
        $databaseConfig = $tenant->fresh(['databaseConfigs'])?->databaseConfigs?->sortByDesc('id')->first();

        if (!$databaseConfig || $databaseConfig->db_host === 'sqlite') {
            return;
        }

        usleep(1500000);
    }

    public function updatePlan(Request $request, Tenant $tenant, TenantAuditLogger $auditLogger): RedirectResponse
    {
        $validated = $request->validate([
            'selected_plan_id' => ['required', Rule::exists('plans', 'id')],
        ]);

        $subscription = $tenant->subscriptions()->latest('id')->first();
        $previousPlanName = $subscription?->plan?->name;

        if (!$subscription) {
            Subscription::query()->create([
                'tenant_id' => $tenant->getKey(),
                'plan_id' => (int) $validated['selected_plan_id'],
                'status' => 'trial',
                'started_at' => now(),
                'trial_ends_at' => now()->addDays(14),
                'expired_at' => now()->addDays(14),
            ]);
        } else {
            $subscription->forceFill([
                'plan_id' => (int) $validated['selected_plan_id'],
            ])->save();
        }

        $plan = Plan::query()->find((int) $validated['selected_plan_id']);
        $this->writeProvisionLog($tenant, 'subscription-plan-update', 'done', 'Paket tenant diubah menjadi '.$plan?->name.'.');
        $auditLogger->log(
            $tenant,
            'tenant.plan.updated',
            'Paket tenant diperbarui dari panel admin.',
            [
                'before' => ['plan_name' => $previousPlanName],
                'after' => ['plan_name' => $plan?->name],
            ]
        );

        return $this->redirectToTenantIndex($request, $tenant, 'Paket tenant berhasil diperbarui.');
    }

    public function renewSubscription(Request $request, Tenant $tenant, TenantAuditLogger $auditLogger): RedirectResponse
    {
        $auditLogger->log(
            $tenant,
            'tenant.subscription.renewal_blocked',
            'Perpanjangan langsung ditolak karena renewal wajib melalui invoice pembayaran.',
            [],
            ['enforced_policy' => 'renewal_requires_paid_invoice']
        );

        return $this->redirectToTenantIndex($request, $tenant)
            ->with('platform_admin_error', 'Perpanjangan langsung dinonaktifkan. Buat invoice renewal dan aktifkan tenant setelah pembayaran tervalidasi.');
    }

    public function updateDomain(Request $request, Tenant $tenant, TenantAuditLogger $auditLogger): RedirectResponse
    {
        $primaryDomainRecord = TenantDomain::query()
            ->where('tenant_id', $tenant->getKey())
            ->where('is_primary', true)
            ->latest('id')
            ->first();

        $validated = $request->validate([
            'primary_domain' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tenant_domains', 'domain')->ignore($primaryDomainRecord?->getKey()),
            ],
        ]);

        TenantDomain::query()
            ->where('tenant_id', $tenant->getKey())
            ->update(['is_primary' => false]);

        TenantDomain::query()->updateOrCreate(
            ['id' => $primaryDomainRecord?->getKey()],
            [
                'tenant_id' => $tenant->getKey(),
                'domain' => $validated['primary_domain'],
                'type' => 'subdomain',
                'is_primary' => true,
            ]
        );

        $this->writeProvisionLog($tenant, 'primary-domain-update', 'done', 'Primary domain tenant diubah menjadi '.$validated['primary_domain'].'.');
        $auditLogger->log(
            $tenant,
            'tenant.domain.updated',
            'Primary domain tenant diperbarui dari panel admin.',
            [
                'before' => ['primary_domain' => $primaryDomainRecord?->domain],
                'after' => ['primary_domain' => $validated['primary_domain']],
            ]
        );

        return $this->redirectToTenantIndex($request, $tenant, 'Primary domain tenant berhasil diperbarui.');
    }

    public function updateStatus(Request $request, Tenant $tenant, TenantAuditLogger $auditLogger, TenantStatusGuard $statusGuard): RedirectResponse
    {
        $validated = $request->validate([
            'status_update' => ['required', 'in:pending_setup,trial,active,suspended,expired'],
        ]);

        try {
            $statusGuard->ensureCanChangeStatus($tenant, $validated['status_update']);
        } catch (RuntimeException $exception) {
            return $this->redirectToTenantIndex($request, $tenant)
                ->with('platform_admin_error', $exception->getMessage());
        }

        $previousStatus = $tenant->status;
        $tenant->forceFill([
            'status' => $validated['status_update'],
        ])->save();

        $this->writeProvisionLog($tenant, 'admin-status-update', 'done', 'Status tenant diubah menjadi '.$tenant->status);
        $auditLogger->log(
            $tenant,
            'tenant.status.updated',
            'Status tenant diperbarui dari panel admin.',
            [
                'before' => ['status' => $previousStatus],
                'after' => ['status' => $tenant->status],
            ]
        );

        return $this->redirectToTenantIndex($request, $tenant, 'Status tenant berhasil diperbarui.');
    }

    public function addNote(Request $request, Tenant $tenant, TenantAuditLogger $auditLogger): RedirectResponse
    {
        $validated = $request->validate([
            'provision_note' => ['required', 'string', 'max:1000'],
        ]);

        $this->writeProvisionLog($tenant, 'admin-note', 'info', $validated['provision_note']);
        $auditLogger->log(
            $tenant,
            'tenant.note.added',
            'Catatan monitoring tenant ditambahkan dari panel admin.',
            [
                'after' => ['note' => $validated['provision_note']],
            ]
        );

        return $this->redirectToTenantIndex($request, $tenant, 'Catatan monitoring tenant ditambahkan.');
    }

    private function writeProvisionLog(Tenant $tenant, string $step, string $status, string $message): void
    {
        TenantProvisionLog::query()->create([
            'tenant_id' => $tenant->getKey(),
            'step' => $step,
            'status' => $status,
            'message' => $message,
            'payload' => [
                'actor_id' => Auth::id(),
                'actor_email' => Auth::user()?->email,
            ],
        ]);
    }

    private function redirectToTenantIndex(Request $request, Tenant $tenant, ?string $message = null): RedirectResponse
    {
        $redirect = redirect()->to(route('platform.admin.tenants.index', [
            'tenant' => $tenant->getKey(),
            'q' => $request->string('q')->toString() !== '' ? $request->string('q')->toString() : null,
            'status' => $request->string('status')->toString() !== '' ? $request->string('status')->toString() : null,
            'page' => $request->integer('page') > 1 ? $request->integer('page') : null,
        ]).'#tenant-detail');

        return $message ? $redirect->with('platform_admin_message', $message) : $redirect;
    }
}
