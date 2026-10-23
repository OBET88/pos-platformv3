<?php

namespace App\Livewire\Platform\Admin;

use App\Livewire\Platform\Admin\Concerns\AuthorizesPlatformAdmin;
use App\Mail\PlatformRegistrationVerification;
use App\Models\Platform\InboxItem;
use App\Models\Platform\PendingRegistration;
use App\Models\Platform\Plan;
use App\Models\Platform\PlatformPaymentTransaction;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;
use App\Models\Platform\TenantAudit;
use App\Models\Platform\TenantDomain;
use App\Models\Platform\TenantProvisionLog;
use App\Services\Platform\PaymentActivationService;
use App\Services\Platform\PlatformSettings;
use App\Services\Platform\TenantAppBootstrapService;
use App\Services\Platform\TenantAuditLogger;
use App\Services\Platform\TenantLifecycleSyncService;
use App\Services\Platform\TenantProvisioningService;
use App\Services\Platform\TenantStatusGuard;
use App\Support\Billing\SubscriptionBilling;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use RuntimeException;

#[Layout('layouts.platform-admin', ['title' => 'Platform Admin - Tenant Manager'])]
class TenantManager extends Component
{
    use AuthorizesPlatformAdmin;
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'status', except: '')]
    public string $statusFilter = '';

    #[Url(as: 'tenant', except: '')]
    public string $selectedTenantId = '';

    #[Url(as: 'inbox', except: 'all')]
    public string $inboxFilter = 'all';

    #[Url(as: 'lifecycle', except: 'all')]
    public string $lifecycleFilter = 'all';

    #[Url(as: 'focus', except: '')]
    public string $focusPanel = '';

    #[Url(as: 'tab', except: 'summary')]
    public string $activeTab = 'summary';

    /**
     * Daftar tab detail tenant yang valid.
     */
    public const DETAIL_TABS = ['summary', 'profile', 'recovery', 'subscription', 'billing', 'access', 'audit'];

    public string $statusUpdate = '';

    public string $provisionNote = '';

    public string $selectedPlanId = '';

    public int $trialPlanDays = 7;

    public int $renewDays = 30;

    public string $paymentType = 'renewal';

    public string $paymentStatusFilter = 'all';

    public string $paymentBillingCycle = 'monthly';

    public int $paymentDueDays = 3;

    public string $paymentAmount = '';

    public string $paymentNotes = '';

    public string $primaryDomain = '';

    public string $tenantName = '';

    public string $ownerName = '';

    public string $ownerPhone = '';

    public string $tenantNotes = '';

    public string $ownerUserName = '';

    public string $ownerUserEmail = '';

    public string $ownerUserPassword = '';

    public string $businessName = 'Usaha Utama';

    public string $branchName = 'Cabang 1';

    public string $ownerRole = 'owner';

    public function resendPendingRegistrationVerification(int $pendingRegistrationId): void
    {
        if (! $this->hasPendingRegistrationsTable()) {
            session()->flash('platform_admin_error', 'Tabel pending registration belum tersedia. Jalankan migration platform terbaru lebih dulu.');

            return;
        }

        $pendingRegistration = PendingRegistration::query()->find($pendingRegistrationId);

        if (! $pendingRegistration) {
            session()->flash('platform_admin_error', 'Request verifikasi yang dipilih tidak ditemukan.');

            return;
        }

        if ($pendingRegistration->consumed_at !== null || $pendingRegistration->tenant_id !== null) {
            session()->flash('platform_admin_error', 'Request ini sudah selesai diproses menjadi tenant.');

            return;
        }

        $payload = (array) ($pendingRegistration->payload ?? []);

        try {
            Mail::to($pendingRegistration->owner_email)->send(new PlatformRegistrationVerification(
                ownerName: (string) ($payload['owner_name'] ?? $pendingRegistration->owner_email),
                companyName: (string) ($payload['company_name'] ?? 'Tenant Baru'),
                verificationUrl: route('platform.register.verify', ['token' => $pendingRegistration->verification_token]),
                expiresAtLabel: optional($pendingRegistration->expires_at?->timezone(config('app.timezone')))->format('d M Y H:i') ?? '-',
            ));

            $pendingRegistration->forceFill([
                'verification_sent_at' => now(),
                'expires_at' => now()->addHours(24),
            ])->save();

            session()->flash('platform_admin_message', 'Email verifikasi berhasil dikirim ulang ke '.$pendingRegistration->owner_email.'.');
        } catch (RuntimeException $exception) {
            session()->flash('platform_admin_error', $exception->getMessage());
        } catch (\Throwable $exception) {
            Log::warning('Platform registration verification resend failed from admin.', [
                'pending_registration_id' => $pendingRegistration->getKey(),
                'owner_email' => $pendingRegistration->owner_email,
                'error' => $exception->getMessage(),
            ]);

            session()->flash('platform_admin_error', 'Email verifikasi belum berhasil dikirim ulang. Coba lagi sebentar lagi.');
        }
    }

    public function mount(): void
    {
        $firstTenant = Tenant::query()->latest('id')->first();
        $this->trialPlanDays = app(PlatformSettings::class)->defaultTrialDays();

        if ($firstTenant && $this->selectedTenantId === '') {
            $this->selectedTenantId = (string) $firstTenant->getKey();
        }

        $this->syncTabFromFocus();
    }

    /**
     * Pindah tab detail tenant. Mengosongkan focusPanel agar tidak menimpa tab pilihan admin.
     */
    public function setTab(string $tab): void
    {
        if (! in_array($tab, self::DETAIL_TABS, true)) {
            return;
        }

        $this->activeTab = $tab;
        $this->focusPanel = '';
    }

    /**
     * Selaraskan tab aktif dengan focusPanel yang dikirim dari inbox.
     */
    private function syncTabFromFocus(): void
    {
        $map = [
            'profile' => 'profile',
            'recovery' => 'recovery',
            'subscription' => 'subscription',
            'billing' => 'billing',
            'domain' => 'access',
            'status' => 'access',
            'audit' => 'audit',
        ];

        if ($this->focusPanel !== '' && isset($map[$this->focusPanel])) {
            $this->activeTab = $map[$this->focusPanel];
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingInboxFilter(): void
    {
        $this->resetPage();
    }

    public function updatingLifecycleFilter(): void
    {
        $this->resetPage();
    }

    public function selectTenant(int $tenantId): void
    {
        $this->selectedTenantId = (string) $tenantId;
        $this->provisionNote = '';
        $this->fillTenantFormState($this->selectedTenant());
    }

    public function hydrate(): void
    {
        $this->syncTabFromFocus();
    }

    public function resetTenantFilters(): void
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->lifecycleFilter = 'all';
        $this->resetPage();
    }

    public function updateTenantProfile(TenantAuditLogger $auditLogger): void
    {
        $tenant = $this->selectedTenant();

        if (! $tenant) {
            return;
        }

        $validated = $this->validate([
            'tenantName' => ['required', 'string', 'max:255'],
            'ownerName' => ['required', 'string', 'max:255'],
            'ownerPhone' => ['nullable', 'string', 'max:50'],
            'tenantNotes' => ['nullable', 'string', 'max:1000'],
        ]);

        $before = [
            'name' => $tenant->name,
            'owner_name' => $tenant->owner_name,
            'owner_phone' => $tenant->owner_phone,
            'notes' => $tenant->notes,
        ];

        $tenant->forceFill([
            'name' => $validated['tenantName'],
            'owner_name' => $validated['ownerName'],
            'owner_phone' => $validated['ownerPhone'] !== '' ? $validated['ownerPhone'] : null,
            'notes' => $validated['tenantNotes'] !== '' ? $validated['tenantNotes'] : null,
        ])->save();

        $this->ownerUserName = $tenant->owner_name ?? '';
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

        session()->flash('platform_admin_message', 'Profil tenant berhasil diperbarui.');
    }

    public function updateTenantStatus(TenantAuditLogger $auditLogger, TenantStatusGuard $statusGuard): void
    {
        $tenant = $this->selectedTenant();

        if (! $tenant) {
            return;
        }

        $previousStatus = $tenant->status;
        $validated = $this->validate([
            'statusUpdate' => ['required', 'in:pending_setup,trial,active,suspended,expired'],
        ]);

        try {
            $statusGuard->ensureCanChangeStatus($tenant, $validated['statusUpdate']);
        } catch (RuntimeException $exception) {
            session()->flash('platform_admin_error', $exception->getMessage());

            return;
        }

        $tenant->forceFill([
            'status' => $validated['statusUpdate'],
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

        session()->flash('platform_admin_message', 'Status tenant berhasil diperbarui.');
    }

    public function provisionTenant(TenantProvisioningService $provisioningService, TenantAuditLogger $auditLogger): void
    {
        $tenant = $this->selectedTenant();

        if (! $tenant) {
            return;
        }

        $tenant = $provisioningService->provision($tenant);
        $this->statusUpdate = (string) $tenant->status;
        $this->primaryDomain = (string) optional($tenant->domains->firstWhere('is_primary', true))->domain;
        $auditLogger->log(
            $tenant,
            'tenant.provision.recovered',
            'Recovery provisioning tenant berhasil dijalankan dari panel admin.'
        );

        session()->flash('platform_admin_message', 'Recovery provisioning tenant selesai. Database tenant sudah diverifikasi ulang.');
    }

    public function bootstrapTenantApp(TenantAppBootstrapService $bootstrapService, \App\Services\Platform\TenantOnboardingDispatcher $onboardingDispatcher, TenantAuditLogger $auditLogger): void
    {
        $tenant = $this->selectedTenant();

        if (! $tenant) {
            return;
        }

        $validated = $this->validate([
            'ownerUserName' => ['required', 'string', 'max:255'],
            'ownerUserEmail' => ['required', 'email', 'max:255'],
            'ownerUserPassword' => ['required', 'string', 'min:8', 'max:255'],
            'businessName' => ['required', 'string', 'max:255'],
            'branchName' => ['required', 'string', 'max:255'],
            'ownerRole' => ['required', Rule::in(['owner', 'administrator', 'admin'])],
        ]);

        try {
            $tenant = $bootstrapService->bootstrap($tenant, [
                'owner_name' => $validated['ownerUserName'],
                'owner_email' => $validated['ownerUserEmail'],
                'owner_password' => $validated['ownerUserPassword'],
                'business_name' => $validated['businessName'],
                'branch_name' => $validated['branchName'],
                'owner_role' => $validated['ownerRole'],
            ]);
            $onboardingDispatcher->finalizeAdminRecovery($tenant, [
                'owner_name' => $validated['ownerUserName'],
                'owner_email' => $validated['ownerUserEmail'],
                'business_name' => $validated['businessName'],
                'branch_name' => $validated['branchName'],
                'owner_role' => $validated['ownerRole'],
            ]);

            $this->ownerUserPassword = '';
            $this->statusUpdate = (string) $tenant->status;
            $auditLogger->log(
                $tenant,
                'tenant.bootstrap.recovered',
                'Recovery bootstrap tenant app berhasil dijalankan dari panel admin.',
                [
                    'after' => [
                        'owner_name' => $validated['ownerUserName'],
                        'owner_email' => $validated['ownerUserEmail'],
                        'business_name' => $validated['businessName'],
                        'branch_name' => $validated['branchName'],
                        'owner_role' => $validated['ownerRole'],
                    ],
                ]
            );

            session()->flash('platform_admin_message', 'Recovery bootstrap tenant app selesai. Owner awal dan schema tenant sudah diverifikasi ulang.');
        } catch (RuntimeException $exception) {
            $this->addError('ownerUserPassword', $exception->getMessage());
        }
    }

    public function updateSubscriptionPlan(TenantAuditLogger $auditLogger): void
    {
        $tenant = $this->selectedTenant();

        if (! $tenant) {
            return;
        }

        $validated = $this->validate([
            'selectedPlanId' => ['required', Rule::exists('plans', 'id')],
            'trialPlanDays' => ['required', 'integer', 'min:1', 'max:90'],
        ]);

        $subscription = $tenant->subscriptions()->with('plan')->latest('id')->first();
        $previousPlanName = $subscription?->plan?->name;
        $previousSubscriptionStatus = $subscription?->status;
        $trialEndsAt = now()->addDays((int) $validated['trialPlanDays']);
        $isTrialLifecycle = $tenant->status === 'trial' || $subscription?->status === 'trial' || $this->statusUpdate === 'trial';

        if (! $subscription) {
            Subscription::query()->create([
                'tenant_id' => $tenant->getKey(),
                'plan_id' => (int) $validated['selectedPlanId'],
                'status' => 'trial',
                'started_at' => now(),
                'trial_ends_at' => $trialEndsAt,
                'expired_at' => $trialEndsAt,
            ]);
        } else {
            $payload = [
                'plan_id' => (int) $validated['selectedPlanId'],
            ];

            if ($isTrialLifecycle) {
                $payload['status'] = 'trial';
                $payload['trial_ends_at'] = $trialEndsAt;
                $payload['expired_at'] = $trialEndsAt;
            }

            $subscription->forceFill($payload)->save();
        }

        if ($isTrialLifecycle && $tenant->status !== 'trial') {
            $tenant->forceFill([
                'status' => 'trial',
            ])->save();

            $this->statusUpdate = 'trial';
        }

        $plan = Plan::query()->find((int) $validated['selectedPlanId']);
        $latestSubscription = $tenant->subscriptions()->with('plan')->latest('id')->first();
        $message = $isTrialLifecycle
            ? 'Paket tenant diubah menjadi '.$plan?->name.' dengan promo trial '.$validated['trialPlanDays'].' hari.'
            : 'Paket tenant diubah menjadi '.$plan?->name.'.';

        $this->writeProvisionLog($tenant, 'subscription-plan-update', 'done', $message);
        $auditLogger->log(
            $tenant,
            'tenant.plan.updated',
            'Paket tenant diperbarui dari panel admin.',
            [
                'before' => [
                    'plan_name' => $previousPlanName,
                    'subscription_status' => $previousSubscriptionStatus,
                ],
                'after' => [
                    'plan_name' => $plan?->name,
                    'subscription_status' => $latestSubscription?->status,
                    'trial_ends_at' => optional($latestSubscription?->trial_ends_at)->toISOString(),
                ],
            ],
            $isTrialLifecycle ? ['trial_days' => (int) $validated['trialPlanDays']] : []
        );

        session()->flash('platform_admin_message', $message);
    }

    public function renewSubscription(TenantAuditLogger $auditLogger): void
    {
        $tenant = $this->selectedTenant();

        if (! $tenant) {
            return;
        }

        $auditLogger->log(
            $tenant,
            'tenant.subscription.renewal_blocked',
            'Perpanjangan langsung ditolak karena renewal wajib melalui invoice pembayaran.',
            [],
            ['enforced_policy' => 'renewal_requires_paid_invoice']
        );

        $this->focusPanel = 'billing';
        $this->addError('renewDays', 'Perpanjangan langsung dinonaktifkan. Buat invoice renewal lalu validasi pembayarannya.');
        session()->flash('platform_admin_error', 'Perpanjangan langsung dinonaktifkan. Gunakan panel invoice pembayaran untuk renewal tenant.');
    }

    public function createManualPayment(TenantAuditLogger $auditLogger): void
    {
        $tenant = $this->selectedTenant();

        if (! $tenant) {
            return;
        }

        $validated = $this->validate([
            'selectedPlanId' => ['required', Rule::exists('plans', 'id')],
            'paymentType' => ['required', Rule::in(['new_subscription', 'renewal', 'upgrade', 'downgrade'])],
            'paymentBillingCycle' => ['required', Rule::in(['monthly', 'quarterly', 'yearly', 'custom'])],
            'paymentDueDays' => ['required', 'integer', 'min:1', 'max:30'],
            'paymentAmount' => ['required', 'numeric', 'min:0'],
            'paymentNotes' => ['nullable', 'string', 'max:1000'],
        ]);

        $plan = Plan::query()->find((int) $validated['selectedPlanId']);

        if (! $plan) {
            $this->addError('selectedPlanId', 'Paket yang dipilih tidak ditemukan.');

            return;
        }

        $latestSubscription = $tenant->subscriptions()->latest('id')->first();
        $pricing = $this->resolvePaymentAmount($validated['paymentType'], $validated['paymentBillingCycle'], $plan, $latestSubscription);
        $amount = (float) $pricing['amount'];
        $invoiceNumber = $this->generateInvoiceNumber($tenant);

        $isUpgrade = $validated['paymentType'] === 'upgrade';
        $isDowngrade = $validated['paymentType'] === 'downgrade';

        // Downgrade mempertahankan masa aktif; upgrade mulai siklus baru; renewal menambah.
        $resultingExpiresAt = match (true) {
            $isUpgrade => SubscriptionBilling::changeExpiresAt(now(), (string) $validated['paymentBillingCycle']),
            $isDowngrade => $latestSubscription?->expiresAt() ?? now(),
            default => SubscriptionBilling::renewalExpiresAt(
                $latestSubscription?->expiresAt(),
                now(),
                (string) $validated['paymentBillingCycle'],
            ),
        };

        $transaction = PlatformPaymentTransaction::query()->create([
            'tenant_id' => $tenant->getKey(),
            'subscription_id' => $latestSubscription?->getKey(),
            'plan_id' => $plan->getKey(),
            'invoice_number' => $invoiceNumber,
            'type' => $validated['paymentType'],
            'payment_method' => 'manual_transfer',
            'payment_channel' => 'bank_transfer',
            'amount_subtotal' => (float) $pricing['target'],
            'amount_discount' => (float) $pricing['credit'],
            'amount_total' => $amount,
            'billing_cycle' => $validated['paymentBillingCycle'],
            'status' => 'pending_payment',
            'due_at' => now()->addDays((int) $validated['paymentDueDays']),
            'notes' => $validated['paymentNotes'] !== '' ? $validated['paymentNotes'] : null,
            'meta_json' => [
                'created_via' => 'platform_admin',
                'plan_name' => $plan->name,
                'pricing_mode' => $isUpgrade ? 'plan_difference_prorata' : 'manual_or_standard',
                'selected_plan_id' => $plan->getKey(),
                'current_plan_id' => $latestSubscription?->plan_id,
                'upgrade_credit' => $pricing['credit'] > 0 ? (float) $pricing['credit'] : null,
                'resulting_expires_at' => $resultingExpiresAt->toDateTimeString(),
            ],
        ]);

        $summary = $validated['paymentType'] === 'upgrade'
            ? 'Invoice upgrade '.$invoiceNumber.' dibuat dengan nominal selisih paket.'
            : 'Invoice manual '.$invoiceNumber.' dibuat untuk tenant.';
        $this->writeProvisionLog($tenant, 'payment-invoice-created', 'done', $summary);
        $auditLogger->log(
            $tenant,
            'tenant.payment.invoice_created',
            'Invoice pembayaran manual dibuat dari panel admin.',
            [
                'after' => [
                    'invoice_number' => $transaction->invoice_number,
                    'payment_type' => $transaction->type,
                    'billing_cycle' => $transaction->billing_cycle,
                    'amount_total' => $transaction->amount_total,
                    'status' => $transaction->status,
                ],
            ]
        );

        $this->paymentNotes = '';
        $this->paymentAmount = number_format($amount, 0, '.', '');
        session()->flash('platform_admin_message', $summary);
    }

    public function updatedPaymentType(): void
    {
        $this->refreshSuggestedPaymentAmount();
    }

    public function updatedSelectedPlanId(): void
    {
        $this->refreshSuggestedPaymentAmount();
    }

    public function updatedPaymentBillingCycle(): void
    {
        $this->refreshSuggestedPaymentAmount();
    }

    public function approvePayment(int $paymentTransactionId, PaymentActivationService $paymentActivationService): void
    {
        $tenant = $this->selectedTenant();

        if (! $tenant) {
            return;
        }

        $transaction = PlatformPaymentTransaction::query()
            ->where('tenant_id', $tenant->getKey())
            ->find($paymentTransactionId);

        if (! $transaction) {
            session()->flash('platform_admin_error', 'Invoice pembayaran yang dipilih tidak ditemukan.');

            return;
        }

        if (in_array($transaction->status, ['paid', 'cancelled', 'refunded', 'expired'], true)) {
            session()->flash('platform_admin_error', 'Invoice ini tidak bisa divalidasi lagi dari panel admin.');

            return;
        }

        $transaction = $paymentActivationService->markAsPaid($transaction, now(), [
            'approved_via' => 'platform_admin',
            'approved_by_email' => auth()->user()?->email,
        ]);

        $transaction->proofs()
            ->where('review_status', 'pending')
            ->update([
                'review_status' => 'approved',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'review_notes' => 'Bukti bayar divalidasi dari panel admin.',
            ]);

        $this->statusUpdate = 'active';
        $this->writeProvisionLog(
            $tenant,
            'payment-approved',
            'done',
            'Pembayaran invoice '.$transaction->invoice_number.' divalidasi dan subscription tenant diaktifkan.'
        );

        session()->flash('platform_admin_message', 'Pembayaran invoice '.$transaction->invoice_number.' berhasil divalidasi.');
    }

    public function updatePrimaryDomain(TenantAuditLogger $auditLogger): void
    {
        $tenant = $this->selectedTenant();

        if (! $tenant) {
            return;
        }

        $primaryDomainRecord = TenantDomain::query()
            ->where('tenant_id', $tenant->getKey())
            ->where('is_primary', true)
            ->latest('id')
            ->first();

        $validated = $this->validate([
            'primaryDomain' => [
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
                'domain' => $validated['primaryDomain'],
                'type' => 'subdomain',
                'is_primary' => true,
            ]
        );

        $this->writeProvisionLog($tenant, 'primary-domain-update', 'done', 'Primary domain tenant diubah menjadi '.$validated['primaryDomain'].'.');
        $auditLogger->log(
            $tenant,
            'tenant.domain.updated',
            'Primary domain tenant diperbarui dari panel admin.',
            [
                'before' => ['primary_domain' => $primaryDomainRecord?->domain],
                'after' => ['primary_domain' => $validated['primaryDomain']],
            ]
        );

        session()->flash('platform_admin_message', 'Primary domain tenant berhasil diperbarui.');
    }

    public function addProvisionNote(TenantAuditLogger $auditLogger): void
    {
        $tenant = $this->selectedTenant();

        if (! $tenant) {
            return;
        }

        $validated = $this->validate([
            'provisionNote' => ['required', 'string', 'max:1000'],
        ]);

        $this->writeProvisionLog($tenant, 'admin-note', 'info', $validated['provisionNote']);
        $auditLogger->log(
            $tenant,
            'tenant.note.added',
            'Catatan monitoring tenant ditambahkan dari panel admin.',
            [
                'after' => ['note' => $validated['provisionNote']],
            ]
        );

        $this->provisionNote = '';
        session()->flash('platform_admin_message', 'Catatan monitoring tenant ditambahkan.');
    }

    public function selectedTenant(): ?Tenant
    {
        if ($this->selectedTenantId === '') {
            return null;
        }

        return Tenant::query()
            ->with([
                'subscriptions.plan',
                'paymentTransactions' => fn ($query) => $query->with(['plan', 'proofs'])->latest('id'),
                'domains',
                'databaseConfigs',
                'provisionLogs' => fn ($query) => $query->latest('id'),
                'audits' => fn ($query) => $query->with('actor')->latest('id'),
            ])
            ->find((int) $this->selectedTenantId);
    }

    private function fillTenantFormState(?Tenant $tenant): void
    {
        $latestSubscription = $tenant?->subscriptions()->with('plan')->latest('id')->first();

        $this->statusUpdate = (string) optional($tenant)->status;
        $this->selectedPlanId = (string) optional(optional($latestSubscription)->plan)->getKey();
        $remainingTrialDays = $latestSubscription?->trial_ends_at?->isFuture()
            ? max(1, now()->diffInDays($latestSubscription->trial_ends_at, false))
            : app(PlatformSettings::class)->defaultTrialDays();
        $this->trialPlanDays = (int) $remainingTrialDays;
        $this->paymentBillingCycle = (string) ($latestSubscription?->plan?->billing_cycle ?? 'monthly');
        $this->paymentAmount = $latestSubscription?->plan !== null
            ? number_format((float) $latestSubscription->plan->price, 0, '.', '')
            : '';
        $this->primaryDomain = (string) optional($tenant?->domains->firstWhere('is_primary', true))->domain;
        $this->tenantName = (string) ($tenant?->name ?? '');
        $this->ownerName = (string) ($tenant?->owner_name ?? '');
        $this->ownerPhone = (string) ($tenant?->owner_phone ?? '');
        $this->tenantNotes = (string) ($tenant?->notes ?? '');
        $this->ownerUserName = (string) ($tenant?->owner_name ?? '');
        $this->ownerUserEmail = (string) ($tenant?->owner_email ?? '');
        $this->refreshSuggestedPaymentAmount();
    }

    private function refreshSuggestedPaymentAmount(): void
    {
        $tenant = $this->selectedTenant();

        if (! $tenant || $this->selectedPlanId === '') {
            return;
        }

        $plan = Plan::query()->find((int) $this->selectedPlanId);

        if (! $plan) {
            return;
        }

        $latestSubscription = $tenant->subscriptions()->with('plan')->latest('id')->first();
        $pricing = $this->resolvePaymentAmount(
            $this->paymentType,
            $this->paymentBillingCycle,
            $plan,
            $latestSubscription
        );

        $this->paymentAmount = number_format((float) $pricing['amount'], 0, '.', '');
    }

    /**
     * @return array{amount: float, credit: float, target: float}
     */
    private function resolvePaymentAmount(string $paymentType, string $billingCycle, Plan $targetPlan, ?Subscription $latestSubscription): array
    {
        $multiplier = match ($billingCycle) {
            'quarterly' => 3,
            'yearly' => 12,
            default => 1,
        };

        $target = (float) $targetPlan->price * $multiplier;

        // Downgrade: tanpa biaya (pelanggan hanya pindah ke paket lebih murah).
        if ($paymentType === 'downgrade') {
            return ['amount' => 0.0, 'credit' => 0.0, 'target' => 0.0];
        }

        if ($paymentType !== 'upgrade') {
            return ['amount' => $target, 'credit' => 0.0, 'target' => $target];
        }

        return SubscriptionBilling::upgradeAmount(
            (float) ($latestSubscription?->plan?->price ?? 0),
            (float) $targetPlan->price,
            $latestSubscription?->expiresAt(),
            now(),
            $billingCycle,
        );
    }

    public function filteredPaymentTransactions(?Tenant $tenant): Collection
    {
        if (! $tenant) {
            return collect();
        }

        return $tenant->paymentTransactions
            ->when($this->paymentStatusFilter !== 'all', fn (Collection $items) => $items->where('status', $this->paymentStatusFilter))
            ->take(6)
            ->values();
    }

    public function tenantRecommendedAction(?Tenant $tenant): array
    {
        $btn = fn (string $label, ?string $tab = null, ?string $url = null): array => [
            'label' => $label,
            'tab' => $tab,
            'url' => $url,
        ];

        if (! $tenant) {
            return [
                'tone' => 'slate',
                'label' => 'Belum Ada Tenant',
                'headline' => 'Pilih tenant dari daftar untuk melihat rekomendasi langkah berikutnya.',
                'description' => 'Setelah tenant dipilih, panel ini akan memberi arahan operasional paling relevan berdasarkan status onboarding, langganan, dan penagihan.',
                'primary' => $btn('Lihat Daftar Tenant', 'summary'),
                'secondary' => null,
            ];
        }

        $latestSubscription = $tenant->subscriptions->sortByDesc('id')->first();
        $databaseConfig = $tenant->databaseConfigs->sortByDesc('id')->first();
        $primaryDomain = $tenant->domains->firstWhere('is_primary', true)?->domain;
        $bootstrapLog = $tenant->provisionLogs->firstWhere('step', 'tenant_app_bootstrap');
        $latestPayment = $tenant->paymentTransactions->sortByDesc('id')->first();
        $expiresAt = $latestSubscription ? $this->subscriptionExpiresAt($latestSubscription) : null;
        $trialEndsAt = $latestSubscription?->trial_ends_at;
        $trialEndsSoon = $tenant->status === 'trial'
            && $trialEndsAt !== null
            && $trialEndsAt->isFuture()
            && $trialEndsAt->lessThanOrEqualTo(now()->addDays(3));

        if (($databaseConfig?->status ?? 'pending') !== 'provisioned') {
            return [
                'tone' => 'amber',
                'label' => 'Prioritas Recovery',
                'headline' => 'Tenant ini masih perlu recovery provisioning database sebelum lanjut ke langkah lain.',
                'description' => 'Jalankan recovery provisioning lebih dulu agar fondasi tenant siap. Setelah database siap, barulah domain dan bootstrap aplikasi bisa diverifikasi.',
                'primary' => $btn('Buka Recovery', 'recovery'),
                'secondary' => $btn('Lihat Audit', 'audit'),
            ];
        }

        if (($bootstrapLog?->status !== 'completed')) {
            return [
                'tone' => 'amber',
                'label' => 'Bootstrap Belum Selesai',
                'headline' => 'Database tenant sudah siap, tetapi bootstrap aplikasi tenant belum tuntas.',
                'description' => 'Lanjutkan recovery bootstrap untuk memastikan owner awal, usaha, cabang, dan schema tenant siap dipakai login pertama.',
                'primary' => $btn('Lanjutkan Bootstrap', 'recovery'),
                'secondary' => $btn('Cek Ringkasan Login', 'summary'),
            ];
        }

        if (! filled($primaryDomain)) {
            return [
                'tone' => 'sky',
                'label' => 'Domain Belum Siap',
                'headline' => 'Tenant sudah terbentuk, tetapi domain utama belum ditetapkan.',
                'description' => 'Tetapkan domain utama agar aplikasi tenant dan URL login bisa langsung dipakai tim customer.',
                'primary' => $btn('Atur Domain', 'access'),
                'secondary' => $btn('Lihat Ringkasan', 'summary'),
            ];
        }

        if (in_array($latestPayment?->status, ['waiting_validation', 'pending_payment'], true)) {
            return [
                'tone' => $latestPayment?->status === 'waiting_validation' ? 'violet' : 'sky',
                'label' => $latestPayment?->status === 'waiting_validation' ? 'Validasi Pembayaran' : 'Tindak Lanjut Invoice',
                'headline' => $latestPayment?->status === 'waiting_validation'
                    ? 'Ada pembayaran yang menunggu validasi untuk tenant ini.'
                    : 'Tenant ini sudah punya invoice aktif yang masih menunggu pembayaran.',
                'description' => $latestPayment?->status === 'waiting_validation'
                    ? 'Buka panel penagihan untuk cek bukti bayar dan validasi invoice agar subscription aktif tanpa jeda.'
                    : 'Pantau invoice aktif, kirim follow-up bila perlu, atau validasi segera saat bukti bayar sudah masuk.',
                'primary' => $btn('Buka Penagihan', 'billing'),
                'secondary' => $btn('Cari di Pusat Pembayaran', null, route('platform.admin.payments.index', ['q' => $tenant->code ?: $tenant->owner_email])),
            ];
        }

        if ($expiresAt !== null && $expiresAt->isPast()) {
            return [
                'tone' => 'rose',
                'label' => 'Langganan Berakhir',
                'headline' => 'Masa aktif tenant ini sudah berakhir dan perlu ditindaklanjuti.',
                'description' => 'Buat invoice renewal lalu aktifkan tenant hanya setelah pembayaran tervalidasi agar alur langganan tetap konsisten dan aman.',
                'primary' => $btn('Buka Langganan', 'subscription'),
                'secondary' => $btn('Buka Penagihan', 'billing'),
            ];
        }

        if ($trialEndsSoon) {
            return [
                'tone' => 'amber',
                'label' => 'Trial Segera Berakhir',
                'headline' => 'Masa trial tenant ini akan segera selesai dalam 3 hari ke depan.',
                'description' => 'Saat yang tepat untuk follow-up paket akhir, buat invoice jika tenant siap lanjut, atau sesuaikan durasi promo bila ada keputusan bisnis khusus.',
                'primary' => $btn('Buka Langganan', 'subscription'),
                'secondary' => $btn('Buka Penagihan', 'billing'),
            ];
        }

        if ($expiresAt !== null && $expiresAt->isFuture() && $expiresAt->lessThanOrEqualTo(now()->addDays(3))) {
            return [
                'tone' => 'amber',
                'label' => 'Segera Perpanjang',
                'headline' => 'Langganan tenant ini akan berakhir dalam 3 hari ke depan.',
                'description' => 'Siapkan invoice renewal sekarang agar tidak ada jeda operasional, lalu validasi pembayaran sebelum masa aktif tenant diperbarui.',
                'primary' => $btn('Buka Langganan', 'subscription'),
                'secondary' => $btn('Buka Penagihan', 'billing'),
            ];
        }

        if ($tenant->status === 'suspended') {
            return [
                'tone' => 'indigo',
                'label' => 'Status Ditangguhkan',
                'headline' => 'Tenant ini sedang ditangguhkan dan perlu keputusan operasional berikutnya.',
                'description' => 'Tinjau alasan penangguhan, audit terakhir, dan kesiapan subscription sebelum mengubah status tenant kembali aktif.',
                'primary' => $btn('Buka Status', 'access'),
                'secondary' => $btn('Buka Audit', 'audit'),
            ];
        }

        return [
            'tone' => 'emerald',
            'label' => 'Kondisi Sehat',
            'headline' => 'Tenant ini terlihat siap dipakai dan tidak ada tindak lanjut mendesak saat ini.',
            'description' => 'Gunakan panel audit, penagihan, atau langganan hanya bila ada request baru dari tenant atau kebutuhan monitoring tambahan dari tim internal.',
            'primary' => $btn('Lihat Audit', 'audit'),
            'secondary' => $btn('Buka Langganan', 'subscription'),
        ];
    }

    public function tenantStats(): array
    {
        $latestSubscriptions = Subscription::query()
            ->with('tenant')
            ->latest('id')
            ->get()
            ->unique('tenant_id')
            ->values();
        $pendingRegistrations = $this->pendingRegistrationsBaseQuery();

        return [
            'total' => Tenant::query()->count(),
            'pending_setup' => Tenant::query()->where('status', 'pending_setup')->count(),
            'pending_email_verification' => $pendingRegistrations?->count() ?? 0,
            'trial' => Tenant::query()->where('status', 'trial')->count(),
            'active' => Tenant::query()->where('status', 'active')->count(),
            'new_active' => Tenant::query()
                ->where('status', 'active')
                ->where(function ($query) {
                    $query->whereDate('onboarded_at', today())
                        ->orWhereDate('updated_at', today());
                })
                ->count(),
            'expiring_soon' => $latestSubscriptions
                ->filter(fn (Subscription $subscription) => $this->expiresWithinDays($subscription, 3))
                ->count(),
            'expired' => $latestSubscriptions
                ->filter(fn (Subscription $subscription) => $this->isExpired($subscription))
                ->count(),
            'renewed_recent' => $latestSubscriptions
                ->filter(fn (Subscription $subscription) => $this->isRenewedRecently($subscription))
                ->count(),
            'attention_count' => $latestSubscriptions
                ->filter(fn (Subscription $subscription) => $this->expiresWithinDays($subscription, 3) || $this->isExpired($subscription))
                ->count(),
        ];
    }

    public function activityInbox(): Collection
    {
        $events = collect();

        $this->pendingRegistrationsBaseQuery()
            ?->latest('updated_at')
            ->take(6)
            ->get()
            ->each(function (PendingRegistration $pendingRegistration) use ($events): void {
                $payload = (array) ($pendingRegistration->payload ?? []);
                $events->push([
                    'type' => 'pending_registration',
                    'tone' => $this->pendingRegistrationTone($pendingRegistration),
                    'title' => 'Menunggu verifikasi email',
                    'context_label' => 'Registrasi Publik',
                    'message' => (($payload['company_name'] ?? 'Request tenant baru').' masih menunggu klik verifikasi dari '.$pendingRegistration->owner_email.'.'),
                    'tenant_id' => null,
                    'tenant_name' => $payload['company_name'] ?? null,
                    'action_label' => 'Lihat Verifikasi',
                    'focus' => '',
                    'anchor' => '#pending-verification',
                    'timestamp' => $pendingRegistration->updated_at ?? $pendingRegistration->created_at,
                ]);
            });

        Tenant::query()
            ->latest('id')
            ->take(6)
            ->get()
            ->each(function (Tenant $tenant) use ($events): void {
                $events->push([
                    'type' => 'tenant_registered',
                    'tone' => 'sky',
                    'title' => 'Tenant baru mendaftar',
                    'context_label' => 'Tenant Baru',
                    'message' => $tenant->name.' baru masuk ke platform.',
                    'tenant_id' => $tenant->getKey(),
                    'tenant_name' => $tenant->name,
                    'action_label' => 'Buka Ringkasan',
                    'focus' => '',
                    'anchor' => '#tenant-detail',
                    'timestamp' => $tenant->created_at,
                ]);
            });

        Tenant::query()
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereDate('onboarded_at', today())
                    ->orWhereDate('updated_at', today());
            })
            ->latest('updated_at')
            ->take(6)
            ->get()
            ->each(function (Tenant $tenant) use ($events): void {
                $events->push([
                    'type' => 'tenant_active',
                    'tone' => 'emerald',
                    'title' => 'Tenant baru aktif',
                    'context_label' => 'Aktivasi',
                    'message' => $tenant->name.' tercatat aktif dan siap dipantau.',
                    'tenant_id' => $tenant->getKey(),
                    'tenant_name' => $tenant->name,
                    'action_label' => 'Buka Ringkasan',
                    'focus' => '',
                    'anchor' => '#tenant-detail',
                    'timestamp' => $tenant->onboarded_at ?? $tenant->updated_at,
                ]);
            });

        Subscription::query()
            ->with(['tenant', 'plan'])
            ->latest('id')
            ->get()
            ->unique('tenant_id')
            ->filter(fn (Subscription $subscription) => $this->expiresWithinDays($subscription, 3))
            ->take(6)
            ->each(function (Subscription $subscription) use ($events): void {
                $expiresAt = $this->subscriptionExpiresAt($subscription);

                $events->push([
                    'type' => 'subscription_expiring',
                    'tone' => 'amber',
                    'title' => 'Tenant mau expire',
                    'context_label' => 'Lifecycle Langganan',
                    'message' => $subscription->tenant?->name.' akan expire pada '.$expiresAt?->format('d M Y H:i').'.',
                    'tenant_id' => $subscription->tenant?->getKey(),
                    'tenant_name' => $subscription->tenant?->name,
                    'action_label' => 'Buka Langganan',
                    'focus' => 'subscription',
                    'anchor' => '#subscription-panel',
                    'timestamp' => $expiresAt,
                ]);
            });

        Subscription::query()
            ->with(['tenant', 'plan'])
            ->latest('id')
            ->get()
            ->unique('tenant_id')
            ->filter(fn (Subscription $subscription) => $this->isExpired($subscription))
            ->take(6)
            ->each(function (Subscription $subscription) use ($events): void {
                $expiresAt = $this->subscriptionExpiresAt($subscription);

                $events->push([
                    'type' => 'subscription_expired',
                    'tone' => 'rose',
                    'title' => 'Tenant expired',
                    'context_label' => 'Lifecycle Langganan',
                    'message' => $subscription->tenant?->name.' sudah melewati masa aktif/langganan.',
                    'tenant_id' => $subscription->tenant?->getKey(),
                    'tenant_name' => $subscription->tenant?->name,
                    'action_label' => 'Buka Langganan',
                    'focus' => 'subscription',
                    'anchor' => '#subscription-panel',
                    'timestamp' => $expiresAt,
                ]);
            });

        InboxItem::query()
            ->with('tenant')
            ->whereIn('event_key', [
                'tenant.subscription.upgrade_requested',
                'tenant.subscription.renewal_requested',
            ])
            ->latest('occurred_at')
            ->latest('id')
            ->take(6)
            ->get()
            ->each(function (InboxItem $item) use ($events): void {
                $payload = (array) ($item->payload ?? []);

                if ($item->event_key === 'tenant.subscription.upgrade_requested') {
                    $requestedPlan = $payload['requested_plan_name']
                        ?? $payload['target_plan_name']
                        ?? $payload['requested_plan_code']
                        ?? $payload['target_plan_code']
                        ?? 'paket baru';

                    $events->push([
                        'type' => 'subscription_upgrade_request',
                        'tone' => 'sky',
                        'title' => 'Request Upgrade',
                        'context_label' => 'Permintaan Paket',
                        'message' => ($item->tenant?->name ?? 'Tenant').' meminta upgrade ke '.$requestedPlan.'.',
                        'tenant_id' => $item->tenant?->getKey(),
                        'tenant_name' => $item->tenant?->name,
                        'action_label' => 'Buka Langganan',
                        'focus' => 'subscription',
                        'anchor' => '#subscription-panel',
                        'timestamp' => $item->occurred_at ?? $item->created_at,
                    ]);

                    return;
                }

                $requestedTerm = $payload['requested_term_label']
                    ?? (! empty($payload['requested_term_months']) ? $payload['requested_term_months'].' bulan' : 'durasi baru');

                $events->push([
                    'type' => 'subscription_renewal_request',
                    'tone' => 'emerald',
                    'title' => 'Request Perpanjangan',
                    'context_label' => 'Permintaan Perpanjangan',
                    'message' => ($item->tenant?->name ?? 'Tenant').' meminta perpanjangan '.$requestedTerm.'.',
                    'tenant_id' => $item->tenant?->getKey(),
                    'tenant_name' => $item->tenant?->name,
                    'action_label' => 'Buka Langganan',
                    'focus' => 'subscription',
                    'anchor' => '#subscription-panel',
                    'timestamp' => $item->occurred_at ?? $item->created_at,
                ]);
            });

        TenantAudit::query()
            ->with(['tenant', 'actor'])
            ->latest('id')
            ->take(10)
            ->get()
            ->each(function (TenantAudit $audit) use ($events): void {
                [$title, $tone] = match ($audit->action) {
                    'tenant.subscription.expiring' => ['Tenant mau expire', 'amber'],
                    'tenant.subscription.expired' => ['Tenant expired', 'rose'],
                    'tenant.subscription.renewed' => ['Perpanjangan terbaru', 'emerald'],
                    default => ['Update admin terbaru', 'slate'],
                };

                [$actionLabel, $focus, $anchor] = match ($audit->action) {
                    'tenant.subscription.expiring',
                    'tenant.subscription.expired',
                    'tenant.subscription.renewed',
                    'tenant.plan.updated' => ['Buka Langganan', 'subscription', '#subscription-panel'],
                    'tenant.profile.updated' => ['Buka Profil', 'profile', '#profile-panel'],
                    'tenant.provision.failed',
                    'tenant.provision.recovered',
                    'tenant.bootstrap.failed',
                    'tenant.bootstrap.recovered' => ['Buka Recovery', 'recovery', '#recovery-panel'],
                    'tenant.domain.updated' => ['Buka Domain', 'domain', '#domain-panel'],
                    'tenant.status.updated' => ['Buka Status', 'status', '#status-panel'],
                    'tenant.payment.awaiting_validation',
                    'tenant.payment.rejected',
                    'tenant.payment.approved',
                    'tenant.payment.invoice_created' => ['Buka Penagihan', 'billing', '#billing-panel'],
                    'tenant.note.added' => ['Buka Audit', 'audit', '#audit-panel'],
                    default => ['Buka Audit', 'audit', '#audit-panel'],
                };

                $events->push([
                    'type' => 'audit',
                    'tone' => $tone,
                    'title' => $title,
                    'context_label' => match ($focus) {
                        'subscription' => 'Update Langganan',
                        'profile' => 'Update Profil',
                        'recovery' => 'Recovery Tenant',
                        'domain' => 'Update Domain',
                        'status' => 'Update Status',
                        'billing' => 'Update Penagihan',
                        default => 'Catatan Admin',
                    },
                    'message' => $audit->summary,
                    'tenant_id' => $audit->tenant?->getKey(),
                    'tenant_name' => $audit->tenant?->name,
                    'action_label' => $actionLabel,
                    'focus' => $focus,
                    'anchor' => $anchor,
                    'timestamp' => $audit->created_at,
                ]);
            });

        return $events
            ->filter(fn (array $event) => $event['timestamp'] !== null)
            ->filter(function (array $event): bool {
                return match ($this->inboxFilter) {
                    'attention' => in_array($event['type'], ['subscription_expiring', 'subscription_expired', 'pending_registration'], true)
                        || in_array($event['title'], ['Tenant mau expire', 'Tenant expired', 'Menunggu verifikasi email'], true),
                    'renewal' => in_array($event['type'], ['subscription_renewed', 'subscription_upgrade_request', 'subscription_renewal_request'], true)
                        || in_array($event['title'], ['Perpanjangan terbaru', 'Request Upgrade', 'Request Perpanjangan'], true),
                    'admin' => $event['type'] === 'audit' && $event['title'] === 'Update admin terbaru',
                    default => true,
                };
            })
            ->sortByDesc('timestamp')
            ->take(10)
            ->values();
    }

    private function subscriptionExpiresAt(Subscription $subscription)
    {
        return $subscription->expired_at ?? $subscription->trial_ends_at;
    }

    private function latestSubscriptions(): Collection
    {
        return Subscription::query()
            ->with(['tenant', 'plan'])
            ->latest('id')
            ->get()
            ->unique('tenant_id')
            ->values();
    }

    private function expiresWithinDays(Subscription $subscription, int $days): bool
    {
        $expiresAt = $this->subscriptionExpiresAt($subscription);

        if (! $expiresAt) {
            return false;
        }

        return $expiresAt->isFuture() && $expiresAt->lessThanOrEqualTo(now()->addDays($days));
    }

    private function isExpired(Subscription $subscription): bool
    {
        $expiresAt = $this->subscriptionExpiresAt($subscription);

        return $expiresAt !== null && $expiresAt->isPast();
    }

    private function isRenewedRecently(Subscription $subscription): bool
    {
        $expiresAt = $this->subscriptionExpiresAt($subscription);

        if (! $expiresAt) {
            return false;
        }

        return $subscription->updated_at !== null
            && $subscription->created_at !== null
            && $subscription->updated_at->isToday()
            && $subscription->created_at->isBefore(today())
            && $subscription->updated_at->greaterThan($subscription->created_at)
            && $expiresAt->isFuture()
            && ($subscription->started_at?->isBefore(today()) ?? true);
    }

    private function writeProvisionLog(Tenant $tenant, string $step, string $status, string $message): void
    {
        TenantProvisionLog::query()->create([
            'tenant_id' => $tenant->getKey(),
            'step' => $step,
            'status' => $status,
            'message' => $message,
            'payload' => [
                'actor_id' => auth()->id(),
                'actor_email' => auth()->user()?->email,
            ],
        ]);
    }

    public function render()
    {
        app(TenantLifecycleSyncService::class)->syncExpiredTenants();

        $latestSubscriptions = $this->latestSubscriptions();
        $expiringTenantIds = $latestSubscriptions
            ->filter(fn (Subscription $subscription) => $this->expiresWithinDays($subscription, 3))
            ->pluck('tenant_id')
            ->filter()
            ->values()
            ->all();
        $expiredTenantIds = $latestSubscriptions
            ->filter(fn (Subscription $subscription) => $this->isExpired($subscription))
            ->pluck('tenant_id')
            ->filter()
            ->values()
            ->all();
        $renewedTenantIds = $latestSubscriptions
            ->filter(fn (Subscription $subscription) => $this->isRenewedRecently($subscription))
            ->pluck('tenant_id')
            ->filter()
            ->values()
            ->all();
        $pendingRegistrations = $this->pendingRegistrationsBaseQuery();

        if ($pendingRegistrations) {
            $pendingRegistrations = $pendingRegistrations
                ->when($this->search !== '', function ($query) {
                    $query->where(function ($inner) {
                        $inner->where('owner_email', 'like', '%'.$this->search.'%')
                            ->orWhere('payload->company_name', 'like', '%'.$this->search.'%')
                            ->orWhere('payload->owner_name', 'like', '%'.$this->search.'%');
                    });
                })
                ->latest('updated_at')
                ->take(10)
                ->get();
        } else {
            $pendingRegistrations = collect();
        }

        $tenants = Tenant::query()
            ->with(['subscriptions.plan'])
            ->when($this->search !== '', function ($query) {
                $query->where(function ($inner) {
                    $inner->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('code', 'like', '%'.$this->search.'%')
                        ->orWhere('owner_email', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->statusFilter !== '', fn ($query) => $query->where('status', $this->statusFilter))
            ->when($this->lifecycleFilter === 'expiring', fn ($query) => $query->whereIn('id', $expiringTenantIds))
            ->when($this->lifecycleFilter === 'expired', fn ($query) => $query->whereIn('id', $expiredTenantIds))
            ->when($this->lifecycleFilter === 'renewed', fn ($query) => $query->whereIn('id', $renewedTenantIds))
            ->latest('id')
            ->paginate(10);

        $selectedTenant = $this->selectedTenant();

        if ($selectedTenant) {
            if ($this->tenantName === '' && $this->ownerName === '' && $this->ownerUserEmail === '') {
                $this->fillTenantFormState($selectedTenant);
            }
        }

        return view('livewire.platform.admin.tenant-manager', [
            'tenants' => $tenants,
            'pendingRegistrations' => $pendingRegistrations,
            'selectedTenant' => $selectedTenant,
            'stats' => $this->tenantStats(),
            'activityInbox' => $this->activityInbox(),
            'attentionCount' => count($expiringTenantIds) + count($expiredTenantIds) + $pendingRegistrations->count(),
            'plans' => Plan::query()->where('is_active', true)->orderBy('price')->get(),
            'title' => 'Platform Admin - Tenant Manager',
        ]);
    }

    public function pendingRegistrationState(?PendingRegistration $pendingRegistration): string
    {
        if (! $pendingRegistration) {
            return 'unknown';
        }

        if ($pendingRegistration->expires_at?->isPast()) {
            return 'expired';
        }

        if ($pendingRegistration->expires_at?->lessThanOrEqualTo(now()->addHours(6))) {
            return 'expiring_soon';
        }

        return 'active';
    }

    private function pendingRegistrationTone(PendingRegistration $pendingRegistration): string
    {
        return match ($this->pendingRegistrationState($pendingRegistration)) {
            'expired' => 'rose',
            'expiring_soon' => 'amber',
            default => 'sky',
        };
    }

    private function pendingRegistrationsBaseQuery()
    {
        if (! $this->hasPendingRegistrationsTable()) {
            return null;
        }

        return PendingRegistration::query()
            ->whereNull('consumed_at')
            ->whereNull('tenant_id');
    }

    private function hasPendingRegistrationsTable(): bool
    {
        return Schema::hasTable('platform_pending_registrations');
    }

    private function generateInvoiceNumber(Tenant $tenant): string
    {
        do {
            $candidate = 'INV-'.now()->format('Ymd').'-'.Str::upper(Str::padLeft((string) random_int(1, 9999), 4, '0')).'-'.$tenant->getKey();
        } while (PlatformPaymentTransaction::query()->where('invoice_number', $candidate)->exists());

        return $candidate;
    }
}
