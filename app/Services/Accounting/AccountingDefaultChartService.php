<?php

namespace App\Services\Accounting;

use App\Models\Platform\AccountingAccount;
use App\Models\Platform\AccountingAccountMapping;
use App\Models\Platform\Tenant;
use App\Support\Accounting\AccountingMappingKey;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AccountingDefaultChartService
{
    public function provisionForTenant(Tenant $tenant, ?int $userId = null): void
    {
        if (!$this->platformAccountingTablesExist()) {
            Log::warning('Platform accounting tables are missing. Default COA provisioning is skipped for tenant registration.', [
                'tenant_id' => $tenant->id,
                'tenant_code' => $tenant->code,
            ]);

            return;
        }

        $accounts = [];

        foreach ($this->defaultAccounts() as $definition) {
            $accounts[$definition['code']] = AccountingAccount::query()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'code' => $definition['code'],
                ],
                [
                    'name' => $definition['name'],
                    'category' => $definition['category'],
                    'normal_balance' => $definition['normal_balance'],
                    'is_active' => true,
                    'allow_manual_entry' => $definition['allow_manual_entry'],
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ],
            );
        }

        foreach ($this->defaultMappings() as $mappingKey => $accountCode) {
            AccountingAccountMapping::query()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'business_id' => null,
                    'branch_id' => null,
                    'mapping_key' => $mappingKey,
                ],
                [
                    'account_id' => $accounts[$accountCode]->id,
                    'is_required' => true,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ],
            );
        }
    }

    private function defaultAccounts(): array
    {
        return [
            ['code' => '1120', 'name' => 'Kas Kecil / Laci Kas', 'category' => 'asset', 'normal_balance' => 'debit', 'allow_manual_entry' => true],
            ['code' => '1130', 'name' => 'Bank', 'category' => 'asset', 'normal_balance' => 'debit', 'allow_manual_entry' => true],
            ['code' => '1140', 'name' => 'QRIS Clearing / Piutang Gateway', 'category' => 'asset', 'normal_balance' => 'debit', 'allow_manual_entry' => true],
            ['code' => '1160', 'name' => 'Persediaan Barang Dagang', 'category' => 'asset', 'normal_balance' => 'debit', 'allow_manual_entry' => true],
            ['code' => '1180', 'name' => 'Persediaan Barang Jadi', 'category' => 'asset', 'normal_balance' => 'debit', 'allow_manual_entry' => true],
            ['code' => '2120', 'name' => 'Uang Muka Pelanggan', 'category' => 'liability', 'normal_balance' => 'credit', 'allow_manual_entry' => true],
            ['code' => '2140', 'name' => 'Hutang Redeem / Voucher', 'category' => 'liability', 'normal_balance' => 'credit', 'allow_manual_entry' => true],
            ['code' => '3130', 'name' => 'Saldo Awal / Selisih Pembukaan', 'category' => 'equity', 'normal_balance' => 'credit', 'allow_manual_entry' => true],
            ['code' => '4110', 'name' => 'Penjualan Makanan', 'category' => 'revenue', 'normal_balance' => 'credit', 'allow_manual_entry' => true],
            ['code' => '4120', 'name' => 'Penjualan Minuman', 'category' => 'revenue', 'normal_balance' => 'credit', 'allow_manual_entry' => true],
            ['code' => '4130', 'name' => 'Pendapatan Lain-lain', 'category' => 'revenue', 'normal_balance' => 'credit', 'allow_manual_entry' => true],
            ['code' => '4190', 'name' => 'Diskon Penjualan', 'category' => 'contra_revenue', 'normal_balance' => 'debit', 'allow_manual_entry' => true],
            ['code' => '4191', 'name' => 'Retur Penjualan', 'category' => 'contra_revenue', 'normal_balance' => 'debit', 'allow_manual_entry' => true],
            ['code' => '5110', 'name' => 'HPP Makanan', 'category' => 'cogs', 'normal_balance' => 'debit', 'allow_manual_entry' => true],
            ['code' => '5120', 'name' => 'HPP Minuman', 'category' => 'cogs', 'normal_balance' => 'debit', 'allow_manual_entry' => true],
            ['code' => '6110', 'name' => 'Beban Gas', 'category' => 'expense', 'normal_balance' => 'debit', 'allow_manual_entry' => true],
            ['code' => '6140', 'name' => 'Beban Promosi', 'category' => 'expense', 'normal_balance' => 'debit', 'allow_manual_entry' => true],
            ['code' => '6160', 'name' => 'Beban Operasional Lain-lain', 'category' => 'expense', 'normal_balance' => 'debit', 'allow_manual_entry' => true],
        ];
    }

    private function defaultMappings(): array
    {
        return [
            AccountingMappingKey::DEFAULT_CASH_ACCOUNT => '1120',
            AccountingMappingKey::DEFAULT_BANK_ACCOUNT => '1130',
            AccountingMappingKey::DEFAULT_QRIS_CLEARING_ACCOUNT => '1140',
            AccountingMappingKey::DEFAULT_MERCHANDISE_INVENTORY_ACCOUNT => '1160',
            AccountingMappingKey::DEFAULT_FINISHED_GOODS_INVENTORY_ACCOUNT => '1180',
            AccountingMappingKey::DEFAULT_CUSTOMER_DEPOSIT_ACCOUNT => '2120',
            AccountingMappingKey::DEFAULT_SHIFT_OPENING_BALANCE_ACCOUNT => '3130',
            AccountingMappingKey::DEFAULT_FOOD_SALES_ACCOUNT => '4110',
            AccountingMappingKey::DEFAULT_DRINK_SALES_ACCOUNT => '4120',
            AccountingMappingKey::DEFAULT_CASH_OVER_SHORT_GAIN_ACCOUNT => '4130',
            AccountingMappingKey::DEFAULT_SALES_DISCOUNT_ACCOUNT => '4190',
            AccountingMappingKey::DEFAULT_SALES_RETURN_ACCOUNT => '4191',
            AccountingMappingKey::DEFAULT_FOOD_COGS_ACCOUNT => '5110',
            AccountingMappingKey::DEFAULT_DRINK_COGS_ACCOUNT => '5120',
            AccountingMappingKey::DEFAULT_PROMO_EXPENSE_ACCOUNT => '6140',
            AccountingMappingKey::DEFAULT_CASH_OVER_SHORT_LOSS_ACCOUNT => '6160',
        ];
    }

    private function platformAccountingTablesExist(): bool
    {
        return Schema::hasTable('accounting_accounts')
            && Schema::hasTable('accounting_account_mappings');
    }
}
