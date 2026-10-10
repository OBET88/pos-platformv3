<?php

namespace App\Services\Accounting\DTO;

use InvalidArgumentException;

class AccountingEventLinePayload
{
    public function __construct(
        public readonly ?string $mappingKey,
        public readonly ?int $accountId,
        public readonly float $debit,
        public readonly float $credit,
        public readonly ?string $description = null,
        public readonly ?string $sourceItemType = null,
        public readonly ?int $sourceItemId = null,
        public readonly ?array $meta = null,
    ) {
        if ($this->mappingKey === null && $this->accountId === null) {
            throw new InvalidArgumentException('Setiap line jurnal harus punya mapping_key atau account_id.');
        }

        if ($this->debit < 0 || $this->credit < 0) {
            throw new InvalidArgumentException('Debit dan credit tidak boleh negatif.');
        }

        if ($this->debit === 0.0 && $this->credit === 0.0) {
            throw new InvalidArgumentException('Setiap line jurnal harus punya nilai debit atau credit.');
        }
    }

    public static function fromArray(array $payload): self
    {
        return new self(
            mappingKey: isset($payload['mapping_key']) ? (string) $payload['mapping_key'] : null,
            accountId: isset($payload['account_id']) ? (int) $payload['account_id'] : null,
            debit: (float) ($payload['debit'] ?? 0),
            credit: (float) ($payload['credit'] ?? 0),
            description: isset($payload['description']) ? (string) $payload['description'] : null,
            sourceItemType: isset($payload['source_item_type']) ? (string) $payload['source_item_type'] : null,
            sourceItemId: isset($payload['source_item_id']) ? (int) $payload['source_item_id'] : null,
            meta: isset($payload['meta']) && is_array($payload['meta']) ? $payload['meta'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'mapping_key' => $this->mappingKey,
            'account_id' => $this->accountId,
            'description' => $this->description,
            'debit' => $this->debit,
            'credit' => $this->credit,
            'source_item_type' => $this->sourceItemType,
            'source_item_id' => $this->sourceItemId,
            'meta' => $this->meta,
        ], fn ($value) => $value !== null);
    }
}
