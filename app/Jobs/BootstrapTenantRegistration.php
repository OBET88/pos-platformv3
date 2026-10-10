<?php

namespace App\Jobs;

use App\Models\Platform\Tenant;
use App\Models\Platform\TenantProvisionLog;
use App\Services\Platform\TenantRegistrationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Throwable;

class BootstrapTenantRegistration implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public int $tries = 1;
    public int $timeout = 900;

    public function __construct(
        private readonly int $tenantId,
        private readonly array $payload,
    ) {
        $this->onQueue('tenant-provisioning');
    }

    public function handle(TenantRegistrationService $registrationService): void
    {
        $tenant = Tenant::query()->find($this->tenantId);

        if (!$tenant) {
            return;
        }

        if ($tenant->provisionLogs()->where('step', 'auto_onboarding_completed')->where('status', 'completed')->exists()) {
            Cache::forget($this->pendingCacheKey());
            return;
        }

        try {
            $registrationService->completeAutoOnboarding($tenant, $this->payload);
        } catch (Throwable $exception) {
            TenantProvisionLog::query()->create([
                'tenant_id' => $tenant->getKey(),
                'step' => 'auto_onboarding_completed',
                'status' => 'failed',
                'message' => 'Auto provisioning tenant gagal dilanjutkan otomatis.',
                'payload' => [
                    'owner_email' => $this->payload['owner_email'] ?? null,
                    'business_name' => $this->payload['business_name'] ?? null,
                    'branch_name' => $this->payload['branch_name'] ?? null,
                    'error' => $exception->getMessage(),
                ],
            ]);

            report($exception);
        } finally {
            Cache::forget($this->pendingCacheKey());
        }
    }

    private function pendingCacheKey(): string
    {
        return 'platform-register-job-pending:'.$this->tenantId;
    }
}
