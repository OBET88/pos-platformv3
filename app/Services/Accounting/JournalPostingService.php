<?php

namespace App\Services\Accounting;

use App\Models\Platform\AccountingJournal;
use App\Models\Platform\AccountingJournalLine;
use App\Models\Platform\AccountingPostingQueue;
use App\Support\Accounting\AccountingPostingStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class JournalPostingService
{
    public function __construct(
        private readonly AccountingMappingResolver $mappingResolver,
    ) {
    }

    /**
     * @param  array<int, array<string, mixed>>  $lines
     */
    public function post(array $payload): AccountingJournal
    {
        return DB::transaction(function () use ($payload) {
            $resolvedLines = [];
            $missingMappings = [];

            foreach ($payload['lines'] as $index => $line) {
                $accountId = $line['account_id'] ?? null;

                if ($accountId === null && isset($line['mapping_key'])) {
                    $account = $this->mappingResolver->resolveAccount(
                        $payload['tenant_id'],
                        $line['mapping_key'],
                        $payload['business_id'] ?? null,
                        $payload['branch_id'] ?? null,
                    );

                    if ($account === null) {
                        $missingMappings[] = $line['mapping_key'];
                    } else {
                        $accountId = $account->id;
                    }
                }

                $resolvedLines[] = [
                    'line_no' => $index + 1,
                    'account_id' => $accountId,
                    'description' => $line['description'] ?? null,
                    'debit' => (float) ($line['debit'] ?? 0),
                    'credit' => (float) ($line['credit'] ?? 0),
                    'source_item_type' => $line['source_item_type'] ?? null,
                    'source_item_id' => $line['source_item_id'] ?? null,
                    'meta' => $line['meta'] ?? null,
                ];
            }

            $status = empty($missingMappings)
                ? AccountingPostingStatus::POSTED
                : AccountingPostingStatus::PENDING_MAPPING;

            $journal = AccountingJournal::query()->create([
                'tenant_id' => $payload['tenant_id'],
                'business_id' => $payload['business_id'] ?? null,
                'branch_id' => $payload['branch_id'] ?? null,
                'journal_no' => $payload['journal_no'] ?? $this->generateJournalNumber(),
                'journal_date' => $payload['journal_date'],
                'source_type' => $payload['source_type'],
                'source_id' => $payload['source_id'] ?? null,
                'event_key' => $payload['event_key'],
                'reference_no' => $payload['reference_no'] ?? null,
                'description' => $payload['description'],
                'posting_status' => $status,
                'posted_at' => $status === AccountingPostingStatus::POSTED ? now() : null,
                'posted_by' => $payload['posted_by'] ?? null,
                'meta' => [
                    'missing_mappings' => array_values(array_unique($missingMappings)),
                    'source_payload' => $payload['meta'] ?? null,
                ],
                'created_by' => $payload['created_by'] ?? null,
                'updated_by' => $payload['updated_by'] ?? null,
            ]);

            if ($status === AccountingPostingStatus::POSTED) {
                $this->assertBalanced($resolvedLines);

                foreach ($resolvedLines as $line) {
                    AccountingJournalLine::query()->create([
                        'journal_id' => $journal->id,
                        ...$line,
                    ]);
                }
            } else {
                AccountingPostingQueue::query()->create([
                    'tenant_id' => $payload['tenant_id'],
                    'event_key' => $payload['event_key'],
                    'source_type' => $payload['source_type'],
                    'source_id' => $payload['source_id'] ?? null,
                    'status' => 'pending_mapping',
                    'error_message' => 'Mapping akun belum lengkap.',
                    'attempts' => 0,
                    'payload' => $payload,
                ]);
            }

            return $journal->load('lines');
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $lines
     */
    private function assertBalanced(array $lines): void
    {
        $debit = collect($lines)->sum(fn (array $line) => (float) $line['debit']);
        $credit = collect($lines)->sum(fn (array $line) => (float) $line['credit']);

        if (round($debit, 2) !== round($credit, 2)) {
            throw new RuntimeException('Jurnal tidak balance. Total debit harus sama dengan total credit.');
        }
    }

    private function generateJournalNumber(): string
    {
        return 'JRN-'.now()->format('YmdHis').'-'.Str::upper(Str::random(6));
    }
}
