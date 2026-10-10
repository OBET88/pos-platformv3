<?php

namespace App\Services\Accounting\PayloadBuilders;

use App\Services\Accounting\DTO\AccountingEventLinePayload;
use App\Services\Accounting\DTO\AccountingEventPayload;
use App\Support\Accounting\AccountingEventKey;
use App\Support\Accounting\AccountingMappingKey;
use App\Support\Accounting\AccountingSourceType;

class SaleDiscountPayloadBuilder
{
    public function build(array $data): AccountingEventPayload
    {
        return new AccountingEventPayload(
            tenantId: (int) $data['tenant_id'],
            journalDate: (string) $data['journal_date'],
            sourceType: AccountingSourceType::SALE,
            eventKey: AccountingEventKey::SALE_DISCOUNT,
            description: (string) ($data['description'] ?? 'Diskon minimum pembelian transaksi POS'),
            sourceId: isset($data['source_id']) ? (int) $data['source_id'] : null,
            referenceNo: isset($data['reference_no']) ? (string) $data['reference_no'] : null,
            businessId: isset($data['business_id']) ? (int) $data['business_id'] : null,
            branchId: isset($data['branch_id']) ? (int) $data['branch_id'] : null,
            createdBy: isset($data['created_by']) ? (int) $data['created_by'] : null,
            updatedBy: isset($data['updated_by']) ? (int) $data['updated_by'] : null,
            meta: $data['meta'] ?? null,
            lines: [
                new AccountingEventLinePayload(
                    mappingKey: AccountingMappingKey::DEFAULT_CASH_ACCOUNT,
                    accountId: null,
                    debit: (float) $data['amount_paid_cash'],
                    credit: 0,
                ),
                new AccountingEventLinePayload(
                    mappingKey: AccountingMappingKey::DEFAULT_SALES_DISCOUNT_ACCOUNT,
                    accountId: null,
                    debit: (float) $data['discount_amount'],
                    credit: 0,
                ),
                new AccountingEventLinePayload(
                    mappingKey: AccountingMappingKey::DEFAULT_FOOD_SALES_ACCOUNT,
                    accountId: null,
                    debit: 0,
                    credit: (float) $data['gross_sales_amount'],
                ),
            ],
        );
    }
}
