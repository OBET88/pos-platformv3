<?php

namespace App\Livewire\Platform\Admin;

use App\Models\Platform\AccountingAccount;
use App\Models\Platform\AccountingAccountMapping;
use App\Models\Platform\Tenant;
use App\Services\Accounting\AccountingDefaultChartService;
use App\Support\Accounting\AccountingMappingKey;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.platform-admin')]
class AccountingSettingsPage extends Component
{
    #[Url(as: 'tenant', except: '')]
    public string $selectedTenantId = '';

    public array $mappingInputs = [];

    public array $accountForm = [
        'code' => '',
        'name' => '',
        'category' => 'expense',
        'normal_balance' => 'debit',
        'allow_manual_entry' => true,
    ];

    public function mount(): void
    {
        $firstTenant = Tenant::query()->latest('id')->first();

        if ($firstTenant && $this->selectedTenantId === '') {
            $this->selectedTenantId = (string) $firstTenant->getKey();
        }

        $this->fillMappingInputs();
    }

    public function selectTenant(int $tenantId): void
    {
        $this->selectedTenantId = (string) $tenantId;
        $this->resetValidation();
        $this->fillMappingInputs();
    }

    public function provisionDefaultChart(AccountingDefaultChartService $defaultChartService): void
    {
        $tenant = $this->selectedTenant();

        if (! $tenant) {
            session()->flash('platform_admin_error', 'Tenant belum dipilih.');

            return;
        }

        $defaultChartService->provisionForTenant($tenant, auth()->id());
        $this->fillMappingInputs();

        session()->flash('platform_admin_message', 'COA default dan mapping dasar berhasil dipastikan untuk tenant '.$tenant->name.'.');
    }

    public function saveMappings(): void
    {
        $tenant = $this->selectedTenant();

        if (! $tenant) {
            session()->flash('platform_admin_error', 'Tenant belum dipilih.');

            return;
        }

        $rules = [];
        foreach (array_keys($this->mappingLabels()) as $mappingKey) {
            $rules['mappingInputs.'.$mappingKey] = ['nullable', 'integer', Rule::exists('accounting_accounts', 'id')->where('tenant_id', $tenant->id)];
        }

        $validated = $this->validate($rules);

        foreach ($this->mappingLabels() as $mappingKey => $label) {
            $accountId = $validated['mappingInputs'][$mappingKey] ?? null;

            if (blank($accountId)) {
                AccountingAccountMapping::query()
                    ->where('tenant_id', $tenant->id)
                    ->whereNull('business_id')
                    ->whereNull('branch_id')
                    ->where('mapping_key', $mappingKey)
                    ->delete();

                continue;
            }

            AccountingAccountMapping::query()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'business_id' => null,
                    'branch_id' => null,
                    'mapping_key' => $mappingKey,
                ],
                [
                    'account_id' => $accountId,
                    'is_required' => true,
                    'updated_by' => auth()->id(),
                    'created_by' => auth()->id(),
                ],
            );
        }

        $this->fillMappingInputs();

        session()->flash('platform_admin_message', 'Mapping akun tenant '.$tenant->name.' berhasil diperbarui.');
    }

    public function createAccount(): void
    {
        $tenant = $this->selectedTenant();

        if (! $tenant) {
            session()->flash('platform_admin_error', 'Tenant belum dipilih.');

            return;
        }

        $validated = $this->validate([
            'accountForm.code' => ['required', 'string', 'max:50', Rule::unique('accounting_accounts', 'code')->where('tenant_id', $tenant->id)],
            'accountForm.name' => ['required', 'string', 'max:255'],
            'accountForm.category' => ['required', 'string', Rule::in(['asset', 'liability', 'equity', 'revenue', 'contra_revenue', 'cogs', 'expense'])],
            'accountForm.normal_balance' => ['required', 'string', Rule::in(['debit', 'credit'])],
            'accountForm.allow_manual_entry' => ['boolean'],
        ])['accountForm'];

        AccountingAccount::query()->create([
            'tenant_id' => $tenant->id,
            'code' => trim((string) $validated['code']),
            'name' => trim((string) $validated['name']),
            'category' => $validated['category'],
            'normal_balance' => $validated['normal_balance'],
            'is_active' => true,
            'allow_manual_entry' => (bool) ($validated['allow_manual_entry'] ?? true),
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        $this->accountForm = [
            'code' => '',
            'name' => '',
            'category' => 'expense',
            'normal_balance' => 'debit',
            'allow_manual_entry' => true,
        ];

        session()->flash('platform_admin_message', 'Akun baru berhasil ditambahkan untuk tenant '.$tenant->name.'.');
    }

    public function selectedTenant(): ?Tenant
    {
        if ($this->selectedTenantId === '') {
            return null;
        }

        return Tenant::query()->find((int) $this->selectedTenantId);
    }

    public function mappingLabels(): array
    {
        return [
            AccountingMappingKey::DEFAULT_CASH_ACCOUNT => 'Kas Kecil / Laci Kas',
            AccountingMappingKey::DEFAULT_BANK_ACCOUNT => 'Bank',
            AccountingMappingKey::DEFAULT_QRIS_CLEARING_ACCOUNT => 'QRIS Clearing / Piutang Gateway',
            AccountingMappingKey::DEFAULT_CUSTOMER_DEPOSIT_ACCOUNT => 'Uang Muka Pelanggan',
            AccountingMappingKey::DEFAULT_FOOD_SALES_ACCOUNT => 'Penjualan Makanan',
            AccountingMappingKey::DEFAULT_DRINK_SALES_ACCOUNT => 'Penjualan Minuman',
            AccountingMappingKey::DEFAULT_SALES_DISCOUNT_ACCOUNT => 'Diskon Penjualan',
            AccountingMappingKey::DEFAULT_SALES_RETURN_ACCOUNT => 'Retur Penjualan',
            AccountingMappingKey::DEFAULT_PROMO_EXPENSE_ACCOUNT => 'Beban Promosi / Redeem',
            AccountingMappingKey::DEFAULT_FOOD_COGS_ACCOUNT => 'HPP Makanan',
            AccountingMappingKey::DEFAULT_DRINK_COGS_ACCOUNT => 'HPP Minuman',
            AccountingMappingKey::DEFAULT_FINISHED_GOODS_INVENTORY_ACCOUNT => 'Persediaan Barang Jadi',
            AccountingMappingKey::DEFAULT_MERCHANDISE_INVENTORY_ACCOUNT => 'Persediaan Barang Dagang',
            AccountingMappingKey::DEFAULT_SHIFT_OPENING_BALANCE_ACCOUNT => 'Saldo Awal / Selisih Pembukaan',
            AccountingMappingKey::DEFAULT_CASH_OVER_SHORT_GAIN_ACCOUNT => 'Selisih Kas Lebih',
            AccountingMappingKey::DEFAULT_CASH_OVER_SHORT_LOSS_ACCOUNT => 'Selisih Kas Kurang',
        ];
    }

    public function render()
    {
        $tenants = Tenant::query()->latest('id')->get();
        $selectedTenant = $this->selectedTenant();
        $accounts = $selectedTenant
            ? $selectedTenant->accountingAccounts()->orderBy('code')->get()
            : collect();
        $mappingSummary = collect($this->mappingLabels())->map(fn (string $label, string $key) => [
            'key' => $key,
            'label' => $label,
            'account_id' => $this->mappingInputs[$key] ?? null,
        ]);
        $missingMappingsCount = $mappingSummary->filter(fn (array $item) => blank($item['account_id']))->count();

        return view('livewire.platform.admin.accounting-settings-page', [
            'tenants' => $tenants,
            'selectedTenant' => $selectedTenant,
            'accounts' => $accounts,
            'mappingSummary' => $mappingSummary,
            'missingMappingsCount' => $missingMappingsCount,
            'title' => 'Platform Admin - Setting Akuntansi / COA',
        ]);
    }

    private function fillMappingInputs(): void
    {
        $tenant = $this->selectedTenant();

        $this->mappingInputs = collect($this->mappingLabels())
            ->mapWithKeys(fn (string $label, string $key) => [$key => null])
            ->all();

        if (! $tenant) {
            return;
        }

        $mappings = $tenant->accountingAccountMappings()
            ->whereNull('business_id')
            ->whereNull('branch_id')
            ->get()
            ->keyBy('mapping_key');

        foreach (array_keys($this->mappingLabels()) as $mappingKey) {
            $this->mappingInputs[$mappingKey] = $mappings->get($mappingKey)?->account_id;
        }
    }
}
