<?php

namespace App\Services\Accounting;

use App\Models\Platform\AccountingJournal;
use App\Services\Accounting\Contracts\PostsAccountingEvents;
use App\Services\Accounting\DTO\AccountingEventPayload;

class TenantAccountingBridge implements PostsAccountingEvents
{
    public function __construct(
        private readonly JournalPostingService $journalPostingService,
    ) {
    }

    public function postEvent(AccountingEventPayload $payload): AccountingJournal
    {
        return $this->journalPostingService->post($payload->toJournalPayload());
    }
}
