<?php

namespace App\Services\Accounting;

use App\Models\Platform\AccountingAccount;
use App\Models\Platform\AccountingAccountMapping;
use App\Models\Platform\Tenant;
use RuntimeException;

class AccountingMappingResolver
{
    public function resolveAccount(
        Tenant|int $tenant,
        string $mappingKey,
        ?int $businessId = null,
        ?int $branchId = null,
    ): ?AccountingAccount {
        $tenantId = $tenant instanceof Tenant ? $tenant->getKey() : $tenant;

        $mapping = AccountingAccountMapping::query()
            ->forTenant($tenantId)
            ->where('mapping_key', $mappingKey)
            ->where('business_id', $businessId)
            ->where('branch_id', $branchId)
            ->with('account')
            ->first();

        if ($mapping !== null) {
            return $mapping->account;
        }

        if ($branchId !== null) {
            return $this->resolveAccount($tenantId, $mappingKey, $businessId, null);
        }

        if ($businessId !== null) {
            return $this->resolveAccount($tenantId, $mappingKey, null, null);
        }

        return null;
    }

    public function resolveRequiredAccount(
        Tenant|int $tenant,
        string $mappingKey,
        ?int $businessId = null,
        ?int $branchId = null,
    ): AccountingAccount {
        $account = $this->resolveAccount($tenant, $mappingKey, $businessId, $branchId);

        if ($account === null) {
            throw new RuntimeException('Mapping akun belum diatur untuk key: '.$mappingKey);
        }

        return $account;
    }
}
