<?php

namespace App\Support\Accounting;

final class AccountingMappingKey
{
    public const DEFAULT_CASH_ACCOUNT = 'default_cash_account';
    public const DEFAULT_BANK_ACCOUNT = 'default_bank_account';
    public const DEFAULT_QRIS_CLEARING_ACCOUNT = 'default_qris_clearing_account';
    public const DEFAULT_CUSTOMER_DEPOSIT_ACCOUNT = 'default_customer_deposit_account';
    public const DEFAULT_FOOD_SALES_ACCOUNT = 'default_food_sales_account';
    public const DEFAULT_DRINK_SALES_ACCOUNT = 'default_drink_sales_account';
    public const DEFAULT_SALES_DISCOUNT_ACCOUNT = 'default_sales_discount_account';
    public const DEFAULT_SALES_RETURN_ACCOUNT = 'default_sales_return_account';
    public const DEFAULT_PROMO_EXPENSE_ACCOUNT = 'default_promo_expense_account';
    public const DEFAULT_FOOD_COGS_ACCOUNT = 'default_food_cogs_account';
    public const DEFAULT_DRINK_COGS_ACCOUNT = 'default_drink_cogs_account';
    public const DEFAULT_FINISHED_GOODS_INVENTORY_ACCOUNT = 'default_finished_goods_inventory_account';
    public const DEFAULT_MERCHANDISE_INVENTORY_ACCOUNT = 'default_merchandise_inventory_account';
    public const DEFAULT_SHIFT_OPENING_BALANCE_ACCOUNT = 'default_shift_opening_balance_account';
    public const DEFAULT_CASH_OVER_SHORT_GAIN_ACCOUNT = 'default_cash_over_short_gain_account';
    public const DEFAULT_CASH_OVER_SHORT_LOSS_ACCOUNT = 'default_cash_over_short_loss_account';

    public static function all(): array
    {
        return [
            self::DEFAULT_CASH_ACCOUNT,
            self::DEFAULT_BANK_ACCOUNT,
            self::DEFAULT_QRIS_CLEARING_ACCOUNT,
            self::DEFAULT_CUSTOMER_DEPOSIT_ACCOUNT,
            self::DEFAULT_FOOD_SALES_ACCOUNT,
            self::DEFAULT_DRINK_SALES_ACCOUNT,
            self::DEFAULT_SALES_DISCOUNT_ACCOUNT,
            self::DEFAULT_SALES_RETURN_ACCOUNT,
            self::DEFAULT_PROMO_EXPENSE_ACCOUNT,
            self::DEFAULT_FOOD_COGS_ACCOUNT,
            self::DEFAULT_DRINK_COGS_ACCOUNT,
            self::DEFAULT_FINISHED_GOODS_INVENTORY_ACCOUNT,
            self::DEFAULT_MERCHANDISE_INVENTORY_ACCOUNT,
            self::DEFAULT_SHIFT_OPENING_BALANCE_ACCOUNT,
            self::DEFAULT_CASH_OVER_SHORT_GAIN_ACCOUNT,
            self::DEFAULT_CASH_OVER_SHORT_LOSS_ACCOUNT,
        ];
    }
}
