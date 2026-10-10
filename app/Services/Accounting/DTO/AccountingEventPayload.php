<?php

namespace App\Services\Accounting\DTO;

use InvalidArgumentException;

class AccountingEventPayload
{
    /**
     * @param  array<int, AccountingEventLinePayload>  $lines
     */
    public function __construct(
        public readonly int $tenantId,
        public readonly string $journalDate,
        public readonly string $sourceType,
        public readonly string $eventKey,
        public readonly string $description,
        public readonly array $lines,
        public readonly ?int $sourceId = null,
        public readonly ?string $referenceNo = null,
        public readonly ?int $businessId = null,
        public readonly ?int $branchId = null,
        public readonly ?string $journalNo = null,
        public readonly ?int $postedBy = null,
        public readonly ?int $createdBy = null,
        public readonly ?int $updatedBy = null,
        public readonly ?array $meta = null,
    ) {
        if ($this->tenantId <= 0) {
            throw new InvalidArgumentException('tenant_id wajib valid.');
        }

        if ($this->description === '') {
            throw new InvalidArgumentException('description wajib diisi.');
        }

        if ($this->lines === []) {
            throw new InvalidArgumentException('Minimal harus ada satu line jurnal.');
        }
    }

    public static function fromArray(array $payload): self
    {
        return new self(
            tenantId: (int) ($payload['tenant_id'] ?? 0),
            journalDate: (string) ($payload['journal_date'] ?? ''),
            sourceType: (string) ($payload['source_type'] ?? ''),
            eventKey: (string) ($payload['event_key'] ?? ''),
            description: (string) ($payload['description'] ?? ''),
            lines: array_map(
                fn (array $line) => AccountingEventLinePayload::fromArray($line),
                array_values($payload['lines'] ?? []),
            ),
            sourceId: isset($payload['source_id']) ? (int) $payload['source_id'] : null,
            referenceNo: isset($payload['reference_no']) ? (string) $payload['reference_no'] : null,
            businessId: isset($payload['business_id']) ? (int) $payload['business_id'] : null,
            branchId: isset($payload['branch_id']) ? (int) $payload['branch_id'] : null,
            journalNo: isset($payload['journal_no']) ? (string) $payload['journal_no'] : null,
            postedBy: isset($payload['posted_by']) ? (int) $payload['posted_by'] : null,
            createdBy: isset($payload['created_by']) ? (int) $payload['created_by'] : null,
            updatedBy: isset($payload['updated_by']) ? (int) $payload['updated_by'] : null,
            meta: isset($payload['meta']) && is_array($payload['meta']) ? $payload['meta'] : null,
        );
    }

    public function toJournalPayload(): array
    {
        return array_filter([
            'tenant_id' => $this->tenantId,
            'journal_date' => $this->journalDate,
            'source_type' => $this->sourceType,
            'source_id' => $this->sourceId,
            'event_key' => $this->eventKey,
            'reference_no' => $this->referenceNo,
            'description' => $this->description,
            'business_id' => $this->businessId,
            'branch_id' => $this->branchId,
            'journal_no' => $this->journalNo,
            'posted_by' => $this->postedBy,
            'created_by' => $this->createdBy,
            'updated_by' => $this->updatedBy,
            'meta' => $this->meta,
            'lines' => array_map(
                fn (AccountingEventLinePayload $line) => $line->toArray(),
                $this->lines,
            ),
        ], fn ($value) => $value !== null);
    }
}
