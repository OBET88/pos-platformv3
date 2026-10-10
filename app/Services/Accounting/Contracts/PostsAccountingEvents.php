<?php

namespace App\Services\Accounting\Contracts;

use App\Models\Platform\AccountingJournal;
use App\Services\Accounting\DTO\AccountingEventPayload;

interface PostsAccountingEvents
{
    public function postEvent(AccountingEventPayload $payload): AccountingJournal;
}
