-- Sinkronisasi schema + default settings tenant
-- Dibuat dari analisis perbedaan:
-- - baseline/default poslive
-- - tenant aktif dkriuk
--
-- Tujuan:
-- 1. Menambah kolom sort_order pada categories
-- 2. Menambah kolom reversal pada shift_cash_movements
-- 3. Menambah default app_settings untuk membership, QR sound,
--    dan mapping akuntansi dasar
--
-- Jalankan file ini di database TENANT via phpMyAdmin.
-- Bukan untuk database platform.

SET @db_name := DATABASE();

-- =========================================================
-- 1) categories: sort_order
-- =========================================================

SET @sql := (
    SELECT IF (
        EXISTS (
            SELECT 1
            FROM information_schema.tables
            WHERE table_schema = @db_name
              AND table_name = 'categories'
        )
        AND NOT EXISTS (
            SELECT 1
            FROM information_schema.columns
            WHERE table_schema = @db_name
              AND table_name = 'categories'
              AND column_name = 'sort_order'
        ),
        'ALTER TABLE `categories` ADD COLUMN `sort_order` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `name`',
        'SELECT ''skip add categories.sort_order'' AS message'
    )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
    SELECT IF (
        EXISTS (
            SELECT 1
            FROM information_schema.tables
            WHERE table_schema = @db_name
              AND table_name = 'categories'
        )
        AND EXISTS (
            SELECT 1
            FROM information_schema.columns
            WHERE table_schema = @db_name
              AND table_name = 'categories'
              AND column_name = 'sort_order'
        ),
        'SET @rownum := 0',
        'SELECT ''skip init categories.sort_order backfill'' AS message'
    )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
    SELECT IF (
        EXISTS (
            SELECT 1
            FROM information_schema.tables
            WHERE table_schema = @db_name
              AND table_name = 'categories'
        )
        AND EXISTS (
            SELECT 1
            FROM information_schema.columns
            WHERE table_schema = @db_name
              AND table_name = 'categories'
              AND column_name = 'sort_order'
        ),
        'UPDATE `categories` SET `sort_order` = ((@rownum := @rownum + 1) * 10) ORDER BY `sort_order`, `id`',
        'SELECT ''skip backfill categories.sort_order'' AS message'
    )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- =========================================================
-- 2) shift_cash_movements: reversal fields
-- =========================================================

SET @sql := (
    SELECT IF (
        EXISTS (
            SELECT 1
            FROM information_schema.tables
            WHERE table_schema = @db_name
              AND table_name = 'shift_cash_movements'
        )
        AND NOT EXISTS (
            SELECT 1
            FROM information_schema.columns
            WHERE table_schema = @db_name
              AND table_name = 'shift_cash_movements'
              AND column_name = 'reversal_of_movement_id'
        ),
        'ALTER TABLE `shift_cash_movements` ADD COLUMN `reversal_of_movement_id` BIGINT UNSIGNED NULL AFTER `note`',
        'SELECT ''skip add reversal_of_movement_id'' AS message'
    )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
    SELECT IF (
        EXISTS (
            SELECT 1
            FROM information_schema.tables
            WHERE table_schema = @db_name
              AND table_name = 'shift_cash_movements'
        )
        AND NOT EXISTS (
            SELECT 1
            FROM information_schema.columns
            WHERE table_schema = @db_name
              AND table_name = 'shift_cash_movements'
              AND column_name = 'reversed_at'
        ),
        'ALTER TABLE `shift_cash_movements` ADD COLUMN `reversed_at` TIMESTAMP NULL DEFAULT NULL AFTER `reversal_of_movement_id`',
        'SELECT ''skip add reversed_at'' AS message'
    )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
    SELECT IF (
        EXISTS (
            SELECT 1
            FROM information_schema.tables
            WHERE table_schema = @db_name
              AND table_name = 'shift_cash_movements'
        )
        AND NOT EXISTS (
            SELECT 1
            FROM information_schema.columns
            WHERE table_schema = @db_name
              AND table_name = 'shift_cash_movements'
              AND column_name = 'reversed_by'
        ),
        'ALTER TABLE `shift_cash_movements` ADD COLUMN `reversed_by` BIGINT UNSIGNED NULL AFTER `reversed_at`',
        'SELECT ''skip add reversed_by'' AS message'
    )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
    SELECT IF (
        EXISTS (
            SELECT 1
            FROM information_schema.tables
            WHERE table_schema = @db_name
              AND table_name = 'shift_cash_movements'
        )
        AND NOT EXISTS (
            SELECT 1
            FROM information_schema.columns
            WHERE table_schema = @db_name
              AND table_name = 'shift_cash_movements'
              AND column_name = 'reversal_reason'
        ),
        'ALTER TABLE `shift_cash_movements` ADD COLUMN `reversal_reason` VARCHAR(255) NULL AFTER `reversed_by`',
        'SELECT ''skip add reversal_reason'' AS message'
    )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
    SELECT IF (
        EXISTS (
            SELECT 1
            FROM information_schema.tables
            WHERE table_schema = @db_name
              AND table_name = 'shift_cash_movements'
        )
        AND NOT EXISTS (
            SELECT 1
            FROM information_schema.table_constraints
            WHERE table_schema = @db_name
              AND table_name = 'shift_cash_movements'
              AND constraint_name = 'shift_cash_movements_reversal_of_movement_id_foreign'
        ),
        'ALTER TABLE `shift_cash_movements` ADD CONSTRAINT `shift_cash_movements_reversal_of_movement_id_foreign` FOREIGN KEY (`reversal_of_movement_id`) REFERENCES `shift_cash_movements` (`id`) ON DELETE SET NULL',
        'SELECT ''skip fk reversal_of_movement_id'' AS message'
    )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
    SELECT IF (
        EXISTS (
            SELECT 1
            FROM information_schema.tables
            WHERE table_schema = @db_name
              AND table_name = 'shift_cash_movements'
        )
        AND NOT EXISTS (
            SELECT 1
            FROM information_schema.table_constraints
            WHERE table_schema = @db_name
              AND table_name = 'shift_cash_movements'
              AND constraint_name = 'shift_cash_movements_reversed_by_foreign'
        ),
        'ALTER TABLE `shift_cash_movements` ADD CONSTRAINT `shift_cash_movements_reversed_by_foreign` FOREIGN KEY (`reversed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL',
        'SELECT ''skip fk reversed_by'' AS message'
    )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- =========================================================
-- 3) app_settings defaults
-- =========================================================

INSERT INTO `app_settings` (`key`, `value`, `created_at`, `updated_at`) VALUES
('membership_enabled', '1', NOW(), NOW()),
('membership_points_enabled', '1', NOW(), NOW()),
('membership_redeem_enabled', '0', NOW(), NOW()),
('membership_tier_enabled', '1', NOW(), NOW()),
('membership_tier_discount_enabled', '0', NOW(), NOW()),
('membership_point_earn_ratio_amount', '10000', NOW(), NOW()),
('membership_point_earn_ratio_points', '1', NOW(), NOW()),
('membership_point_redeem_ratio_amount', '500', NOW(), NOW()),
('membership_point_redeem_ratio_points', '1', NOW(), NOW()),
('membership_minimum_spend_for_points', '10000', NOW(), NOW()),
('membership_tier_silver_min_points', '50', NOW(), NOW()),
('membership_tier_gold_min_points', '150', NOW(), NOW()),
('membership_tier_platinum_min_points', '300', NOW(), NOW()),
('membership_tier_discount_silver_percent', '0', NOW(), NOW()),
('membership_tier_discount_gold_percent', '0', NOW(), NOW()),
('membership_tier_discount_platinum_percent', '0', NOW(), NOW()),
('membership_allow_points_with_promo', '0', NOW(), NOW()),
('membership_allow_points_with_manual_discount', '0', NOW(), NOW()),
('qr_order_notification_sound', 'qr_order_chime_long', NOW(), NOW()),
('qr_bill_notification_sound', 'qr_bill_ring_long', NOW(), NOW()),
('pos_validate_stock_selection', '0', NOW(), NOW()),
('acc_map_cash_difference_expense', '6101', NOW(), NOW()),
('acc_map_cash_difference_revenue', '4201', NOW(), NOW())
ON DUPLICATE KEY UPDATE
`value` = VALUES(`value`),
`updated_at` = VALUES(`updated_at`);

-- =========================================================
-- 4) Optional cek hasil
-- =========================================================

SELECT
    column_name,
    column_type,
    is_nullable
FROM information_schema.columns
WHERE table_schema = @db_name
  AND table_name = 'categories'
  AND column_name IN (
      'sort_order'
  );

SELECT `id`, `name`, `sort_order`
FROM `categories`
ORDER BY `sort_order`, `id`;

SELECT
    column_name,
    column_type,
    is_nullable
FROM information_schema.columns
WHERE table_schema = @db_name
  AND table_name = 'shift_cash_movements'
  AND column_name IN (
      'reversal_of_movement_id',
      'reversed_at',
      'reversed_by',
      'reversal_reason'
  );

SELECT `key`, `value`
FROM `app_settings`
WHERE `key` IN (
    'membership_enabled',
    'membership_points_enabled',
    'membership_redeem_enabled',
    'membership_tier_enabled',
    'membership_tier_discount_enabled',
    'membership_point_earn_ratio_amount',
    'membership_point_earn_ratio_points',
    'membership_point_redeem_ratio_amount',
    'membership_point_redeem_ratio_points',
    'membership_minimum_spend_for_points',
    'membership_tier_silver_min_points',
    'membership_tier_gold_min_points',
    'membership_tier_platinum_min_points',
    'membership_tier_discount_silver_percent',
    'membership_tier_discount_gold_percent',
    'membership_tier_discount_platinum_percent',
    'membership_allow_points_with_promo',
    'membership_allow_points_with_manual_discount',
    'qr_order_notification_sound',
    'qr_bill_notification_sound',
    'pos_validate_stock_selection',
    'acc_map_cash_difference_expense',
    'acc_map_cash_difference_revenue'
)
ORDER BY `key`;
