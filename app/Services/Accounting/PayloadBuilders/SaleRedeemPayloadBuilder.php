<?php

namespace App\Services\Accounting\PayloadBuilders;

use App\Services\Accounting\DTO\AccountingEventLinePayload;
use App\Services\Accounting\DTO\AccountingEventPayload;
use App\Support\Accounting\AccountingEventKey;
use App\Support\Accounting\AccountingMappingKey;
use App\Support\Accounting\AccountingSourceType;

class SaleRedeemPayloadBuilder
{
    public function build(array $data): AccountingEventPayload
    {
        return new AccountingEventPayload(
            tenantId: (int) $data['tenant_id'],
            journalDate: (string) $data['journal_date'],
            sourceType: AccountingSourceType::SALE,
            eventKey: AccountingEventKey::SALE_REDEEM,
            description: (string) ($data['description'] ?? 'Redeem voucher / poin transaksi POS'),
            sourceId: isset($data['source_id']) ? (int) $data['source_id'] : null,
            referenceNo: isset($data['reference_no']) ? (string) $data['reference_no'] : null,
            businessId: isset($data['business_id']) ? (int) $data['business_id'] : null,
            branchId: isset($data['branch_id']) ? (int) $data['branch_id'] : null,
            createdBy: isset($data['created_by']) ? (int) $data['created_by'] : null,
            updatedBy: isset($data['updated_by']) ? (int) $data['updated_by'] : null,
            meta: $data['meta'] ?? null,
            lines: [
                new AccountingEventLinePayload(
                    mappingKey: $this->paymentMappingKey((string) $data['payment_method']),
                    accountId: null,
                    debit: (float) $data['amount_paid'],
                    credit: 0,
                ),
                new AccountingEventLinePayload(
                    mappingKey: AccountingMappingKey::DEFAULT_PROMO_EXPENSE_ACCOUNT,
                    accountId: null,
                    debit: (float) $data['redeem_amount'],
                    credit: 0,
                ),
                new AccountingEventLinePayload(
                    mappingKey: AccountingMappingKey::DEFAULT_FOOD_SALES_ACCOUNT,
                    accountId: null,
                    debit: 0,
                    credit: (float) $data['food_sales_amount'],
                ),
                new AccountingEventLinePayload(
                    mappingKey: AccountingMappingKey::DEFAULT_DRINK_SALES_ACCOUNT,
                    accountId: null,
                    debit: 0,
                    credit: (float) $data['drink_sales_amount'],
                ),
            ],
        );
    }

    private function paymentMappingKey(string $paymentMethod): string
    {
        return match ($paymentMethod) {
            'cash' => AccountingMappingKey::DEFAULT_CASH_ACCOUNT,
            default => AccountingMappingKey::DEFAULT_QRIS_CLEARING_ACCOUNT,
        };
    }
}
