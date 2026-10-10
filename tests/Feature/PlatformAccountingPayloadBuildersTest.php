<?php

namespace Tests\Feature;

use App\Services\Accounting\PayloadBuilders\SaleDiscountPayloadBuilder;
use App\Services\Accounting\PayloadBuilders\SaleRedeemPayloadBuilder;
use App\Services\Accounting\PayloadBuilders\SaleRefundCashPayloadBuilder;
use App\Services\Accounting\PayloadBuilders\SaleSplitAmountPayloadBuilder;
use App\Support\Accounting\AccountingEventKey;
use App\Support\Accounting\AccountingMappingKey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformAccountingPayloadBuildersTest extends TestCase
{
    use RefreshDatabase;

    public function test_sale_discount_payload_builder_builds_expected_payload(): void
    {
        $payload = app(SaleDiscountPayloadBuilder::class)->build([
            'tenant_id' => 1,
            'journal_date' => '2026-05-06',
            'source_id' => 10,
            'reference_no' => 'INV-DISC-001',
            'amount_paid_cash' => 36000,
            'discount_amount' => 4000,
            'gross_sales_amount' => 40000,
        ]);

        $journalPayload = $payload->toJournalPayload();

        $this->assertSame(AccountingEventKey::SALE_DISCOUNT, $journalPayload['event_key']);
        $this->assertSame(AccountingMappingKey::DEFAULT_SALES_DISCOUNT_ACCOUNT, $journalPayload['lines'][1]['mapping_key']);
    }

    public function test_sale_redeem_payload_builder_builds_expected_payload(): void
    {
        $payload = app(SaleRedeemPayloadBuilder::class)->build([
            'tenant_id' => 1,
            'journal_date' => '2026-05-06',
            'source_id' => 11,
            'reference_no' => 'INV-RED-001',
            'payment_method' => 'qris',
            'amount_paid' => 25000,
            'redeem_amount' => 5000,
            'food_sales_amount' => 22000,
            'drink_sales_amount' => 8000,
        ]);

        $journalPayload = $payload->toJournalPayload();

        $this->assertSame(AccountingEventKey::SALE_REDEEM, $journalPayload['event_key']);
        $this->assertSame(AccountingMappingKey::DEFAULT_PROMO_EXPENSE_ACCOUNT, $journalPayload['lines'][1]['mapping_key']);
    }

    public function test_sale_refund_cash_payload_builder_builds_expected_payload(): void
    {
        $payload = app(SaleRefundCashPayloadBuilder::class)->build([
            'tenant_id' => 1,
            'journal_date' => '2026-05-06',
            'source_id' => 12,
            'reference_no' => 'INV-REF-001',
            'refund_amount' => 10000,
            'inventory_return_amount' => 5000,
        ]);

        $journalPayload = $payload->toJournalPayload();

        $this->assertSame(AccountingEventKey::SALE_REFUND_CASH, $journalPayload['event_key']);
        $this->assertSame(AccountingMappingKey::DEFAULT_SALES_RETURN_ACCOUNT, $journalPayload['lines'][0]['mapping_key']);
    }

    public function test_sale_split_amount_payload_builder_builds_expected_payload(): void
    {
        $payload = app(SaleSplitAmountPayloadBuilder::class)->build([
            'tenant_id' => 1,
            'journal_date' => '2026-05-06',
            'source_id' => 13,
            'reference_no' => 'INV-SPLIT-001',
            'cash_amount' => 20000,
            'qris_amount' => 15000,
            'gross_sales_amount' => 35000,
        ]);

        $journalPayload = $payload->toJournalPayload();

        $this->assertSame(AccountingEventKey::SALE_SPLIT_AMOUNT, $journalPayload['event_key']);
        $this->assertCount(3, $journalPayload['lines']);
        $this->assertSame(35000.0, $journalPayload['lines'][2]['credit']);
    }
}
