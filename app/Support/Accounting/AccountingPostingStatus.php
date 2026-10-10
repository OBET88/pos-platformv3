<?php

namespace App\Support\Accounting;

final class AccountingPostingStatus
{
    public const DRAFT = 'draft';
    public const POSTED = 'posted';
    public const PENDING_MAPPING = 'pending_mapping';
    public const FAILED = 'failed';
    public const REVERSED = 'reversed';
}
