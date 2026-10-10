<?php

namespace Tests\Feature;

use App\Models\Platform\AccountingJournal;
use App\Models\Platform\AccountingPostingQueue;
use App\Models\Platform\Plan;
use App\Models\Platform\Tenant;
use App\Services\Accounting\JournalPostingService;
use App\Services\Platform\TenantRegistrationService;
use App\Support\Accounting\AccountingEventKey;
use App\Support\Accounting\AccountingMappingKey;
use App\Support\Accounting\AccountingPostingStatus;
use App\Support\Accounting\AccountingSourceType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformAccountingFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registering_tenant_provisions_default_accounting_accounts_and_mappings(): void
    {
        $plan = Plan::query()->where('code', 'basic')->firstOrFail();

        $tenant = app(TenantRegistrationService::class)->register([
            'company_name' => 'PT Accounting Setup',
            'owner_name' => 'Owner Accounting',
            'owner_email' => 'owner-accounting@example.com',
            'owner_phone' => '081200000001',
            'plan_code' => $plan->code,
        ], completeOnboarding: false);

        $this->assertGreaterThanOrEqual(10, $tenant->accountingAccounts()->count());
        $this->assertDatabaseHas('accounting_accounts', [
            'tenant_id' => $tenant->id,
            'code' => '4190',
            'name' => 'Diskon Penjualan',
        ]);
        $this->assertDatabaseHas('accounting_accounts', [
            'tenant_id' => $tenant->id,
            'code' => '6140',
            'name' => 'Beban Promosi',
        ]);
        $this->assertDatabaseHas('accounting_account_mappings', [
            'tenant_id' => $tenant->id,
            'mapping_key' => AccountingMappingKey::DEFAULT_SALES_DISCOUNT_ACCOUNT,
        ]);
        $this->assertDatabaseHas('accounting_account_mappings', [
            'tenant_id' => $tenant->id,
            'mapping_key' => AccountingMappingKey::DEFAULT_PROMO_EXPENSE_ACCOUNT,
        ]);
    }

    public function test_journal_posting_service_posts_balanced_journal_when_mapping_exists(): void
    {
        $tenant = Tenant::query()->create([
            'code' => 'tenant-accounting-posted',
            'name' => 'Tenant Accounting Posted',
            'owner_name' => 'Owner Posted',
            'owner_email' => 'owner-posted@example.com',
            'status' => 'active',
        ]);

        app(\App\Services\Accounting\AccountingDefaultChartService::class)->provisionForTenant($tenant);

        $journal = app(JournalPostingService::class)->post([
            'tenant_id' => $tenant->id,
            'journal_date' => now()->toDateString(),
            'source_type' => AccountingSourceType::SALE,
            'source_id' => 1001,
            'event_key' => AccountingEventKey::SALE_DISCOUNT,
            'reference_no' => 'INV-UAT-001',
            'description' => 'UAT diskon minimum pembelian',
            'lines' => [
                [
                    'mapping_key' => AccountingMappingKey::DEFAULT_CASH_ACCOUNT,
                    'debit' => 36_000,
                    'credit' => 0,
                ],
                [
                    'mapping_key' => AccountingMappingKey::DEFAULT_SALES_DISCOUNT_ACCOUNT,
                    'debit' => 4_000,
                    'credit' => 0,
                ],
                [
                    'mapping_key' => AccountingMappingKey::DEFAULT_FOOD_SALES_ACCOUNT,
                    'debit' => 0,
                    'credit' => 40_000,
                ],
            ],
        ]);

        $this->assertSame(AccountingPostingStatus::POSTED, $journal->posting_status);
        $this->assertCount(3, $journal->lines);
        $this->assertDatabaseHas('accounting_journals', [
            'id' => $journal->id,
            'tenant_id' => $tenant->id,
            'posting_status' => AccountingPostingStatus::POSTED,
            'event_key' => AccountingEventKey::SALE_DISCOUNT,
        ]);
    }

    public function test_journal_posting_service_marks_pending_mapping_when_required_mapping_is_missing(): void
    {
        $tenant = Tenant::query()->create([
            'code' => 'tenant-accounting-pending',
            'name' => 'Tenant Accounting Pending',
            'owner_name' => 'Owner Pending',
            'owner_email' => 'owner-pending@example.com',
            'status' => 'active',
        ]);

        app(\App\Services\Accounting\AccountingDefaultChartService::class)->provisionForTenant($tenant);

        $tenant->accountingAccountMappings()
            ->where('mapping_key', AccountingMappingKey::DEFAULT_PROMO_EXPENSE_ACCOUNT)
            ->delete();

        $journal = app(JournalPostingService::class)->post([
            'tenant_id' => $tenant->id,
            'journal_date' => now()->toDateString(),
            'source_type' => AccountingSourceType::SALE,
            'source_id' => 1002,
            'event_key' => AccountingEventKey::SALE_REDEEM,
            'reference_no' => 'INV-UAT-002',
            'description' => 'UAT redeem promo',
            'lines' => [
                [
                    'mapping_key' => AccountingMappingKey::DEFAULT_QRIS_CLEARING_ACCOUNT,
                    'debit' => 25_000,
                    'credit' => 0,
                ],
                [
                    'mapping_key' => AccountingMappingKey::DEFAULT_PROMO_EXPENSE_ACCOUNT,
                    'debit' => 5_000,
                    'credit' => 0,
                ],
                [
                    'mapping_key' => AccountingMappingKey::DEFAULT_FOOD_SALES_ACCOUNT,
                    'debit' => 0,
                    'credit' => 22_000,
                ],
                [
                    'mapping_key' => AccountingMappingKey::DEFAULT_DRINK_SALES_ACCOUNT,
                    'debit' => 0,
                    'credit' => 8_000,
                ],
            ],
        ]);

        $journal->refresh();

        $this->assertSame(AccountingPostingStatus::PENDING_MAPPING, $journal->posting_status);
        $this->assertSame(0, AccountingJournal::query()->findOrFail($journal->id)->lines()->count());
        $this->assertDatabaseHas('accounting_posting_queues', [
            'tenant_id' => $tenant->id,
            'event_key' => AccountingEventKey::SALE_REDEEM,
            'source_type' => AccountingSourceType::SALE,
            'source_id' => 1002,
            'status' => 'pending_mapping',
        ]);

        $queue = AccountingPostingQueue::query()->where('tenant_id', $tenant->id)->latest('id')->firstOrFail();

        $this->assertContains(
            AccountingMappingKey::DEFAULT_PROMO_EXPENSE_ACCOUNT,
            $journal->meta['missing_mappings'] ?? [],
        );
        $this->assertSame(AccountingEventKey::SALE_REDEEM, $queue->event_key);
    }
}
