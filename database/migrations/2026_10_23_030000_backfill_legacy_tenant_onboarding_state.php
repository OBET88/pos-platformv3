<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('tenants')
            ->orderBy('id')
            ->each(function (object $tenant): void {
                $completed = DB::table('tenant_provision_logs')
                    ->where('tenant_id', $tenant->id)
                    ->where('step', 'auto_onboarding_completed')
                    ->where('status', 'completed')
                    ->exists();

                if ($completed) {
                    return;
                }

                $log = DB::table('tenant_provision_logs')
                    ->where('tenant_id', $tenant->id)
                    ->where('step', 'auto_onboarding_requested')
                    ->orderByDesc('id')
                    ->first();

                if ($log === null && ! in_array($tenant->status, ['pending_payment', 'pending_provisioning', 'pending_setup', 'provisioning'], true)) {
                    return;
                }

                $payload = $log === null ? [] : json_decode((string) $log->payload, true);
                $payload = is_array($payload) ? $payload : [];
                $encryptedPassword = $payload['owner_password_encrypted'] ?? null;

                if (! is_string($encryptedPassword) || $encryptedPassword === '') {
                    DB::table('tenant_onboarding_jobs')->updateOrInsert(
                        ['tenant_id' => $tenant->id],
                        [
                            'status' => 'failed',
                            'payload' => json_encode([]),
                            'attempts' => 0,
                            'last_error' => 'Data rahasia bootstrap legacy tidak tersedia. Recovery admin diperlukan.',
                            'failed_at' => now(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                    );

                    DB::table('tenant_provision_logs')->insert([
                        'tenant_id' => $tenant->id,
                        'step' => 'auto_onboarding_backfill',
                        'status' => 'failed',
                        'message' => 'Onboarding legacy memerlukan recovery admin karena secret bootstrap tidak tersedia.',
                        'payload' => json_encode([]),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    return;
                }

                DB::table('tenant_bootstrap_secrets')->updateOrInsert(
                    ['tenant_id' => $tenant->id],
                    [
                        'owner_password_encrypted' => $encryptedPassword,
                        'consumed_at' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                );

                unset($payload['owner_password_encrypted']);
                if ($log !== null) {
                    DB::table('tenant_provision_logs')->where('id', $log->id)->update([
                        'payload' => json_encode($payload),
                        'updated_at' => now(),
                    ]);
                }

                DB::table('tenant_onboarding_jobs')->updateOrInsert(
                    ['tenant_id' => $tenant->id],
                    [
                        'status' => $tenant->status === 'pending_payment' ? 'blocked' : 'queued',
                        'payload' => json_encode([
                            'owner_name' => $payload['owner_name'] ?? $tenant->owner_name,
                            'owner_email' => $payload['owner_email'] ?? $tenant->owner_email,
                            'owner_email_verified_at' => $payload['owner_email_verified_at'] ?? null,
                            'business_name' => $payload['business_name'] ?? $tenant->name,
                            'branch_name' => $payload['branch_name'] ?? 'Cabang 1',
                            'owner_role' => $payload['owner_role'] ?? 'owner',
                        ]),
                        'attempts' => 0,
                        'available_at' => $tenant->status === 'pending_payment' ? null : now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                );
            });
    }

    public function down(): void
    {
        // Historical secret migration is intentionally non-destructive.
    }
};
