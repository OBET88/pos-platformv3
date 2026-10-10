<?php

namespace App\Support\Accounting;

final class AccountingSourceType
{
    public const SHIFT = 'shift';
    public const CASH_IN = 'cash_in';
    public const CASH_OUT = 'cash_out';
    public const SALE = 'sale';
    public const REFUND = 'refund';
    public const MANUAL_JOURNAL = 'manual_journal';
    public const QRIS_SETTLEMENT = 'qris_settlement';
}
