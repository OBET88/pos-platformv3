<?php

namespace Tests\Feature;

use App\Models\Platform\Tenant;
use App\Services\Accounting\AccountingDefaultChartService;
use App\Services\Accounting\DTO\AccountingEventLinePayload;
use App\Services\Accounting\DTO\AccountingEventPayload;
use App\Services\Accounting\TenantAccountingBridge;
use App\Support\Accounting\AccountingEventKey;
use App\Support\Accounting\AccountingMappingKey;
use App\Support\Accounting\AccountingSourceType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformAccountingIntegrationContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_accounting_bridge_can_post_discount_event_payload(): void
    {
        $tenant = Tenant::query()->create([
            'code' => 'tenant-contract-discount',
            'name' => 'Tenant Contract Discount',
            'owner_name' => 'Owner Contract',
            'owner_email' => 'owner-contract@example.com',
            'status' => 'active',
        ]);

        app(AccountingDefaultChartService::class)->provisionForTenant($tenant);

        $payload = new AccountingEventPayload(
            tenantId: $tenant->id,
            journalDate: now()->toDateString(),
            sourceType: AccountingSourceType::SALE,
            eventKey: AccountingEventKey::SALE_DISCOUNT,
            description: 'Payload kontrak diskon minimum pembelian',
            sourceId: 3001,
            referenceNo: 'INV-CONTRACT-001',
            meta: [
                'transaction_code' => 'TRX-CONTRACT-001',
                'order_type' => 'dine_in',
            ],
            lines: [
                new AccountingEventLinePayload(
                    mappingKey: AccountingMappingKey::DEFAULT_CASH_ACCOUNT,
                    accountId: null,
                    debit: 36000,
                    credit: 0,
                ),
                new AccountingEventLinePayload(
                    mappingKey: AccountingMappingKey::DEFAULT_SALES_DISCOUNT_ACCOUNT,
                    accountId: null,
                    debit: 4000,
                    credit: 0,
                ),
                new AccountingEventLinePayload(
                    mappingKey: AccountingMappingKey::DEFAULT_FOOD_SALES_ACCOUNT,
                    accountId: null,
                    debit: 0,
                    credit: 40000,
                ),
            ],
        );

        $journal = app(TenantAccountingBridge::class)->postEvent($payload);

        $this->assertSame('posted', $journal->posting_status);
        $this->assertSame('INV-CONTRACT-001', $journal->reference_no);
        $this->assertSame('TRX-CONTRACT-001', $journal->meta['source_payload']['transaction_code'] ?? null);
        $this->assertCount(3, $journal->lines);
    }

    public function test_accounting_event_payload_can_be_built_from_array(): void
    {
        $payload = AccountingEventPayload::fromArray([
            'tenant_id' => 99,
            'journal_date' => '2026-05-06',
            'source_type' => AccountingSourceType::SALE,
            'source_id' => 4001,
            'event_key' => AccountingEventKey::SALE_REDEEM,
            'reference_no' => 'INV-CONTRACT-002',
            'description' => 'Payload redeem dari tenant app',
            'meta' => [
                'transaction_code' => 'TRX-CONTRACT-002',
            ],
            'lines' => [
                [
                    'mapping_key' => AccountingMappingKey::DEFAULT_QRIS_CLEARING_ACCOUNT,
                    'debit' => 25000,
                    'credit' => 0,
                ],
                [
                    'mapping_key' => AccountingMappingKey::DEFAULT_PROMO_EXPENSE_ACCOUNT,
                    'debit' => 5000,
                    'credit' => 0,
                ],
                [
                    'mapping_key' => AccountingMappingKey::DEFAULT_FOOD_SALES_ACCOUNT,
                    'debit' => 0,
                    'credit' => 30000,
                ],
            ],
        ]);

        $journalPayload = $payload->toJournalPayload();

        $this->assertSame(99, $journalPayload['tenant_id']);
        $this->assertSame('INV-CONTRACT-002', $journalPayload['reference_no']);
        $this->assertSame('TRX-CONTRACT-002', $journalPayload['meta']['transaction_code']);
        $this->assertCount(3, $journalPayload['lines']);
    }
}
