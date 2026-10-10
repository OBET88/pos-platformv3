<?php

namespace App\Support\Accounting;

final class AccountingEventKey
{
    public const SHIFT_OPEN = 'shift_open';
    public const CASH_IN_DP = 'cash_in_dp';
    public const SALE_CASH = 'sale_cash';
    public const SALE_QRIS = 'sale_qris';
    public const SALE_SPLIT_AMOUNT = 'sale_split_amount';
    public const SALE_SPLIT_ITEM = 'sale_split_item';
    public const SALE_DISCOUNT = 'sale_discount';
    public const SALE_REDEEM = 'sale_redeem';
    public const COGS_FOOD = 'cogs_food';
    public const COGS_DRINK = 'cogs_drink';
    public const SALE_REFUND_CASH = 'sale_refund_cash';
    public const SALE_REFUND_QRIS = 'sale_refund_qris';
    public const CASH_OUT_EXPENSE = 'cash_out_expense';
    public const QRIS_SETTLEMENT = 'qris_settlement';
    public const SHIFT_CLOSE_DIFF_PLUS = 'shift_close_diff_plus';
    public const SHIFT_CLOSE_DIFF_MINUS = 'shift_close_diff_minus';
    public const MANUAL_JOURNAL = 'manual_journal';
}
