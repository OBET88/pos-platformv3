-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.4.3 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.11.0.7065
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for pos_platform
DROP DATABASE IF EXISTS `pos_platform`;
CREATE DATABASE IF NOT EXISTS `pos_platform` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `pos_platform`;

-- Dumping structure for table pos_platform.accounting_accounts
DROP TABLE IF EXISTS `accounting_accounts`;
CREATE TABLE IF NOT EXISTS `accounting_accounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint unsigned NOT NULL,
  `business_id` bigint unsigned DEFAULT NULL,
  `branch_id` bigint unsigned DEFAULT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `normal_balance` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `allow_manual_entry` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `accounting_accounts_tenant_id_code_unique` (`tenant_id`,`code`),
  KEY `accounting_accounts_created_by_foreign` (`created_by`),
  KEY `accounting_accounts_updated_by_foreign` (`updated_by`),
  KEY `accounting_accounts_business_id_index` (`business_id`),
  KEY `accounting_accounts_branch_id_index` (`branch_id`),
  KEY `accounting_accounts_category_index` (`category`),
  CONSTRAINT `accounting_accounts_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `accounting_accounts_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `accounting_accounts_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pos_platform.accounting_accounts: ~0 rows (approximately)
DELETE FROM `accounting_accounts`;

-- Dumping structure for table pos_platform.accounting_account_mappings
DROP TABLE IF EXISTS `accounting_account_mappings`;
CREATE TABLE IF NOT EXISTS `accounting_account_mappings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint unsigned NOT NULL,
  `business_id` bigint unsigned DEFAULT NULL,
  `branch_id` bigint unsigned DEFAULT NULL,
  `mapping_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_id` bigint unsigned NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `is_required` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `acct_map_unique` (`tenant_id`,`business_id`,`branch_id`,`mapping_key`),
  KEY `accounting_account_mappings_account_id_foreign` (`account_id`),
  KEY `accounting_account_mappings_created_by_foreign` (`created_by`),
  KEY `accounting_account_mappings_updated_by_foreign` (`updated_by`),
  KEY `accounting_account_mappings_business_id_index` (`business_id`),
  KEY `accounting_account_mappings_branch_id_index` (`branch_id`),
  KEY `accounting_account_mappings_mapping_key_index` (`mapping_key`),
  CONSTRAINT `accounting_account_mappings_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounting_accounts` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `accounting_account_mappings_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `accounting_account_mappings_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `accounting_account_mappings_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pos_platform.accounting_account_mappings: ~0 rows (approximately)
DELETE FROM `accounting_account_mappings`;

-- Dumping structure for table pos_platform.accounting_journals
DROP TABLE IF EXISTS `accounting_journals`;
CREATE TABLE IF NOT EXISTS `accounting_journals` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint unsigned NOT NULL,
  `business_id` bigint unsigned DEFAULT NULL,
  `branch_id` bigint unsigned DEFAULT NULL,
  `journal_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `journal_date` date NOT NULL,
  `source_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `source_id` bigint unsigned DEFAULT NULL,
  `event_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reference_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `posting_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `posted_at` timestamp NULL DEFAULT NULL,
  `posted_by` bigint unsigned DEFAULT NULL,
  `meta` json DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `accounting_journals_journal_no_unique` (`journal_no`),
  KEY `accounting_journals_tenant_id_foreign` (`tenant_id`),
  KEY `accounting_journals_posted_by_foreign` (`posted_by`),
  KEY `accounting_journals_created_by_foreign` (`created_by`),
  KEY `accounting_journals_updated_by_foreign` (`updated_by`),
  KEY `accounting_journals_source_type_source_id_index` (`source_type`,`source_id`),
  KEY `accounting_journals_business_id_index` (`business_id`),
  KEY `accounting_journals_branch_id_index` (`branch_id`),
  KEY `accounting_journals_journal_date_index` (`journal_date`),
  KEY `accounting_journals_source_type_index` (`source_type`),
  KEY `accounting_journals_event_key_index` (`event_key`),
  KEY `accounting_journals_reference_no_index` (`reference_no`),
  KEY `accounting_journals_posting_status_index` (`posting_status`),
  CONSTRAINT `accounting_journals_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `accounting_journals_posted_by_foreign` FOREIGN KEY (`posted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `accounting_journals_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `accounting_journals_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pos_platform.accounting_journals: ~0 rows (approximately)
DELETE FROM `accounting_journals`;

-- Dumping structure for table pos_platform.accounting_journal_lines
DROP TABLE IF EXISTS `accounting_journal_lines`;
CREATE TABLE IF NOT EXISTS `accounting_journal_lines` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `journal_id` bigint unsigned NOT NULL,
  `account_id` bigint unsigned NOT NULL,
  `line_no` int unsigned NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `debit` decimal(15,2) NOT NULL DEFAULT '0.00',
  `credit` decimal(15,2) NOT NULL DEFAULT '0.00',
  `source_item_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source_item_id` bigint unsigned DEFAULT NULL,
  `meta` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `accounting_journal_lines_journal_id_line_no_unique` (`journal_id`,`line_no`),
  KEY `accounting_journal_lines_account_id_foreign` (`account_id`),
  CONSTRAINT `accounting_journal_lines_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounting_accounts` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `accounting_journal_lines_journal_id_foreign` FOREIGN KEY (`journal_id`) REFERENCES `accounting_journals` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pos_platform.accounting_journal_lines: ~0 rows (approximately)
DELETE FROM `accounting_journal_lines`;

-- Dumping structure for table pos_platform.accounting_posting_queues
DROP TABLE IF EXISTS `accounting_posting_queues`;
CREATE TABLE IF NOT EXISTS `accounting_posting_queues` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint unsigned NOT NULL,
  `event_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `source_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `source_id` bigint unsigned DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `attempts` int unsigned NOT NULL DEFAULT '0',
  `payload` json NOT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `accounting_posting_queues_tenant_id_foreign` (`tenant_id`),
  KEY `accounting_posting_queues_source_type_source_id_index` (`source_type`,`source_id`),
  KEY `accounting_posting_queues_event_key_index` (`event_key`),
  KEY `accounting_posting_queues_source_type_index` (`source_type`),
  KEY `accounting_posting_queues_status_index` (`status`),
  CONSTRAINT `accounting_posting_queues_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pos_platform.accounting_posting_queues: ~0 rows (approximately)
DELETE FROM `accounting_posting_queues`;

-- Dumping structure for table pos_platform.cache
DROP TABLE IF EXISTS `cache`;
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pos_platform.cache: ~0 rows (approximately)
DELETE FROM `cache`;

-- Dumping structure for table pos_platform.cache_locks
DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pos_platform.cache_locks: ~0 rows (approximately)
DELETE FROM `cache_locks`;

-- Dumping structure for table pos_platform.failed_jobs
DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pos_platform.failed_jobs: ~0 rows (approximately)
DELETE FROM `failed_jobs`;

-- Dumping structure for table pos_platform.jobs
DROP TABLE IF EXISTS `jobs`;
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pos_platform.jobs: ~0 rows (approximately)
DELETE FROM `jobs`;

-- Dumping structure for table pos_platform.job_batches
DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pos_platform.job_batches: ~0 rows (approximately)
DELETE FROM `job_batches`;

-- Dumping structure for table pos_platform.migrations
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pos_platform.migrations: ~13 rows (approximately)
DELETE FROM `migrations`;
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
	(1, '0001_01_01_000000_create_users_table', 1),
	(2, '0001_01_01_000001_create_cache_table', 1),
	(3, '0001_01_01_000002_create_jobs_table', 1),
	(4, '2026_04_15_220000_create_platform_saas_tables', 1),
	(5, '2026_04_15_230000_add_is_platform_admin_to_users_table', 1),
	(6, '2026_04_19_000000_create_platform_registration_drafts_table', 1),
	(7, '2026_04_19_010000_create_platform_tenant_audits_table', 1),
	(8, '2026_04_19_020000_create_platform_inbox_items_table', 1),
	(9, '2026_04_19_020100_create_platform_inbox_item_reads_table', 1),
	(10, '2026_04_19_030000_create_platform_pending_registrations_table', 1),
	(11, '2026_04_19_210000_add_follow_up_columns_to_platform_inbox_items_table', 1),
	(12, '2026_04_20_090000_add_trial_plan_to_platform_plans', 1),
	(13, '2026_04_24_120000_create_platform_payment_tables', 1),
	(14, '2026_04_26_090000_sync_trial_plan_with_plus_plus_features', 1),
	(15, '2026_04_26_100000_create_platform_settings_table', 1),
	(16, '2026_04_26_120000_create_platform_announcements_tables', 1),
	(17, '2026_05_06_150000_create_platform_accounting_tables', 1);

-- Dumping structure for table pos_platform.password_reset_tokens
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pos_platform.password_reset_tokens: ~0 rows (approximately)
DELETE FROM `password_reset_tokens`;

-- Dumping structure for table pos_platform.plans
DROP TABLE IF EXISTS `plans`;
CREATE TABLE IF NOT EXISTS `plans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(15,2) NOT NULL DEFAULT '0.00',
  `billing_cycle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'monthly',
  `max_businesses` int unsigned NOT NULL DEFAULT '1',
  `max_branches` int unsigned NOT NULL DEFAULT '1',
  `max_users` int unsigned NOT NULL DEFAULT '3',
  `features_json` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `plans_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pos_platform.plans: ~2 rows (approximately)
DELETE FROM `plans`;
INSERT INTO `plans` (`id`, `code`, `name`, `price`, `billing_cycle`, `max_businesses`, `max_branches`, `max_users`, `features_json`, `is_active`, `created_at`, `updated_at`) VALUES
	(1, 'basic', 'Basic', 149000.00, 'monthly', 1, 1, 3, '["pos", "stock", "basic_reports"]', 1, '2026-05-08 01:57:46', '2026-05-08 01:57:46'),
	(2, 'pro', 'Pro', 399000.00, 'monthly', 3, 5, 15, '["pos", "stock", "production", "accounting", "advanced_reports"]', 1, '2026-05-08 01:57:46', '2026-05-08 01:57:46'),
	(3, 'enterprise', 'Enterprise', 0.00, 'custom', 999, 999, 999, '["pos", "stock", "production", "accounting", "advanced_reports", "priority_support"]', 1, '2026-05-08 01:57:46', '2026-05-08 01:57:46'),
	(4, 'trial', 'Trial', 0.00, 'monthly', 999, 999, 999, '["menu_dashboard", "menu_subscription_center", "menu_reports", "menu_product_reports", "menu_stock_in", "menu_stock_out", "menu_raw_stock_reports", "menu_production_make", "menu_shift_history_self", "menu_daily_report_self", "menu_products", "menu_categories", "menu_floor_map_manager", "menu_users", "menu_finished_vs_sold", "menu_recipes", "menu_roles", "menu_owner_cashier_recap", "menu_shift_history_admin", "menu_daily_report_admin", "menu_audit_resume", "menu_accounting_report", "menu_accounting_admin_entry", "menu_accounting_manual_journal", "menu_accounting_settings", "menu_branding", "menu_promo_settings", "menu_promo_vouchers", "subscription_center", "pos", "stock", "basic_reports", "advanced_reports", "production", "accounting", "accounting_report", "accounting_coa", "accounting_admin_entry", "accounting_manual_journal", "audit", "promotions", "priority_support"]', 1, '2026-05-08 01:57:49', '2026-05-08 01:57:51');

-- Dumping structure for table pos_platform.platform_announcements
DROP TABLE IF EXISTS `platform_announcements`;
CREATE TABLE IF NOT EXISTS `platform_announcements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `tone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'info',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `show_as_modal` tinyint(1) NOT NULL DEFAULT '1',
  `starts_at` timestamp NULL DEFAULT NULL,
  `ends_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `platform_announcements_created_by_foreign` (`created_by`),
  KEY `platform_announcements_updated_by_foreign` (`updated_by`),
  KEY `platform_announcements_tone_index` (`tone`),
  KEY `platform_announcements_is_active_index` (`is_active`),
  KEY `platform_announcements_show_as_modal_index` (`show_as_modal`),
  KEY `platform_announcements_starts_at_index` (`starts_at`),
  KEY `platform_announcements_ends_at_index` (`ends_at`),
  CONSTRAINT `platform_announcements_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `platform_announcements_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pos_platform.platform_announcements: ~0 rows (approximately)
DELETE FROM `platform_announcements`;

-- Dumping structure for table pos_platform.platform_announcement_dismissals
DROP TABLE IF EXISTS `platform_announcement_dismissals`;
CREATE TABLE IF NOT EXISTS `platform_announcement_dismissals` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `announcement_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `dismissed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `platform_announcement_dismissals_announcement_id_user_id_unique` (`announcement_id`,`user_id`),
  KEY `platform_announcement_dismissals_user_id_foreign` (`user_id`),
  KEY `platform_announcement_dismissals_dismissed_at_index` (`dismissed_at`),
  CONSTRAINT `platform_announcement_dismissals_announcement_id_foreign` FOREIGN KEY (`announcement_id`) REFERENCES `platform_announcements` (`id`) ON DELETE CASCADE,
  CONSTRAINT `platform_announcement_dismissals_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pos_platform.platform_announcement_dismissals: ~0 rows (approximately)
DELETE FROM `platform_announcement_dismissals`;

-- Dumping structure for table pos_platform.platform_inbox_items
DROP TABLE IF EXISTS `platform_inbox_items`;
CREATE TABLE IF NOT EXISTS `platform_inbox_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint unsigned DEFAULT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `tone` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'slate',
  `follow_up_status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `source_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source_id` bigint unsigned DEFAULT NULL,
  `fingerprint` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` json DEFAULT NULL,
  `follow_up_note` text COLLATE utf8mb4_unicode_ci,
  `follow_up_user_id` bigint unsigned DEFAULT NULL,
  `follow_up_at` timestamp NULL DEFAULT NULL,
  `occurred_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `platform_inbox_items_fingerprint_unique` (`fingerprint`),
  KEY `platform_inbox_items_tenant_id_foreign` (`tenant_id`),
  KEY `platform_inbox_items_category_index` (`category`),
  KEY `platform_inbox_items_event_key_index` (`event_key`),
  KEY `platform_inbox_items_occurred_at_index` (`occurred_at`),
  KEY `platform_inbox_items_follow_up_user_id_foreign` (`follow_up_user_id`),
  CONSTRAINT `platform_inbox_items_follow_up_user_id_foreign` FOREIGN KEY (`follow_up_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `platform_inbox_items_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pos_platform.platform_inbox_items: ~0 rows (approximately)
DELETE FROM `platform_inbox_items`;

-- Dumping structure for table pos_platform.platform_inbox_item_reads
DROP TABLE IF EXISTS `platform_inbox_item_reads`;
CREATE TABLE IF NOT EXISTS `platform_inbox_item_reads` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `inbox_item_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `read_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `platform_inbox_item_reads_inbox_item_id_user_id_unique` (`inbox_item_id`,`user_id`),
  KEY `platform_inbox_item_reads_user_id_foreign` (`user_id`),
  CONSTRAINT `platform_inbox_item_reads_inbox_item_id_foreign` FOREIGN KEY (`inbox_item_id`) REFERENCES `platform_inbox_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `platform_inbox_item_reads_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pos_platform.platform_inbox_item_reads: ~0 rows (approximately)
DELETE FROM `platform_inbox_item_reads`;

-- Dumping structure for table pos_platform.platform_payment_events
DROP TABLE IF EXISTS `platform_payment_events`;
CREATE TABLE IF NOT EXISTS `platform_payment_events` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `payment_transaction_id` bigint unsigned DEFAULT NULL,
  `payment_provider` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider_reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `signature_valid` tinyint(1) NOT NULL DEFAULT '0',
  `payload_json` json DEFAULT NULL,
  `process_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'received',
  `received_at` timestamp NULL DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `failure_reason` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `platform_payment_events_payment_transaction_id_foreign` (`payment_transaction_id`),
  KEY `platform_payment_events_payment_provider_index` (`payment_provider`),
  KEY `platform_payment_events_event_key_index` (`event_key`),
  KEY `platform_payment_events_provider_reference_index` (`provider_reference`),
  KEY `platform_payment_events_process_status_index` (`process_status`),
  CONSTRAINT `platform_payment_events_payment_transaction_id_foreign` FOREIGN KEY (`payment_transaction_id`) REFERENCES `platform_payment_transactions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pos_platform.platform_payment_events: ~0 rows (approximately)
DELETE FROM `platform_payment_events`;

-- Dumping structure for table pos_platform.platform_payment_proofs
DROP TABLE IF EXISTS `platform_payment_proofs`;
CREATE TABLE IF NOT EXISTS `platform_payment_proofs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `payment_transaction_id` bigint unsigned NOT NULL,
  `bank_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_number_masked` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transfer_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `transfer_date` timestamp NULL DEFAULT NULL,
  `proof_file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `submitted_by_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `submitted_by_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `review_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `reviewed_by` bigint unsigned DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `review_notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `platform_payment_proofs_payment_transaction_id_foreign` (`payment_transaction_id`),
  KEY `platform_payment_proofs_reviewed_by_foreign` (`reviewed_by`),
  KEY `platform_payment_proofs_review_status_index` (`review_status`),
  CONSTRAINT `platform_payment_proofs_payment_transaction_id_foreign` FOREIGN KEY (`payment_transaction_id`) REFERENCES `platform_payment_transactions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `platform_payment_proofs_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pos_platform.platform_payment_proofs: ~0 rows (approximately)
DELETE FROM `platform_payment_proofs`;

-- Dumping structure for table pos_platform.platform_payment_transactions
DROP TABLE IF EXISTS `platform_payment_transactions`;
CREATE TABLE IF NOT EXISTS `platform_payment_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint unsigned NOT NULL,
  `subscription_id` bigint unsigned DEFAULT NULL,
  `plan_id` bigint unsigned NOT NULL,
  `invoice_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'renewal',
  `payment_method` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'manual_transfer',
  `payment_channel` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_provider` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider_reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount_subtotal` decimal(15,2) NOT NULL DEFAULT '0.00',
  `amount_discount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `amount_total` decimal(15,2) NOT NULL DEFAULT '0.00',
  `billing_cycle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'monthly',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `due_at` timestamp NULL DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `meta_json` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `platform_payment_transactions_invoice_number_unique` (`invoice_number`),
  KEY `platform_payment_transactions_tenant_id_foreign` (`tenant_id`),
  KEY `platform_payment_transactions_subscription_id_foreign` (`subscription_id`),
  KEY `platform_payment_transactions_plan_id_foreign` (`plan_id`),
  KEY `platform_payment_transactions_type_index` (`type`),
  KEY `platform_payment_transactions_payment_method_index` (`payment_method`),
  KEY `platform_payment_transactions_payment_provider_index` (`payment_provider`),
  KEY `platform_payment_transactions_provider_reference_index` (`provider_reference`),
  KEY `platform_payment_transactions_status_index` (`status`),
  CONSTRAINT `platform_payment_transactions_plan_id_foreign` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `platform_payment_transactions_subscription_id_foreign` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `platform_payment_transactions_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pos_platform.platform_payment_transactions: ~0 rows (approximately)
DELETE FROM `platform_payment_transactions`;

-- Dumping structure for table pos_platform.platform_pending_registrations
DROP TABLE IF EXISTS `platform_pending_registrations`;
CREATE TABLE IF NOT EXISTS `platform_pending_registrations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `owner_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `verification_token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` json NOT NULL,
  `verification_sent_at` timestamp NULL DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `consumed_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `tenant_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `platform_pending_registrations_owner_email_unique` (`owner_email`),
  UNIQUE KEY `platform_pending_registrations_verification_token_unique` (`verification_token`),
  KEY `platform_pending_registrations_tenant_id_foreign` (`tenant_id`),
  CONSTRAINT `platform_pending_registrations_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pos_platform.platform_pending_registrations: ~0 rows (approximately)
DELETE FROM `platform_pending_registrations`;

-- Dumping structure for table pos_platform.platform_registration_drafts
DROP TABLE IF EXISTS `platform_registration_drafts`;
CREATE TABLE IF NOT EXISTS `platform_registration_drafts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `resume_token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payload` json NOT NULL,
  `last_seen_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `platform_registration_drafts_resume_token_unique` (`resume_token`),
  KEY `platform_registration_drafts_owner_email_index` (`owner_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pos_platform.platform_registration_drafts: ~0 rows (approximately)
DELETE FROM `platform_registration_drafts`;

-- Dumping structure for table pos_platform.platform_settings
DROP TABLE IF EXISTS `platform_settings`;
CREATE TABLE IF NOT EXISTS `platform_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `platform_settings_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pos_platform.platform_settings: ~1 rows (approximately)
DELETE FROM `platform_settings`;
INSERT INTO `platform_settings` (`id`, `key`, `value`, `created_at`, `updated_at`) VALUES
	(1, 'default_trial_days', '14', '2026-05-08 01:57:51', '2026-05-08 01:57:51');

-- Dumping structure for table pos_platform.platform_tenant_audits
DROP TABLE IF EXISTS `platform_tenant_audits`;
CREATE TABLE IF NOT EXISTS `platform_tenant_audits` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint unsigned NOT NULL,
  `actor_id` bigint unsigned DEFAULT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `summary` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `changes` json DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `platform_tenant_audits_tenant_id_foreign` (`tenant_id`),
  KEY `platform_tenant_audits_actor_id_foreign` (`actor_id`),
  CONSTRAINT `platform_tenant_audits_actor_id_foreign` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `platform_tenant_audits_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pos_platform.platform_tenant_audits: ~0 rows (approximately)
DELETE FROM `platform_tenant_audits`;

-- Dumping structure for table pos_platform.sessions
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pos_platform.sessions: ~0 rows (approximately)
DELETE FROM `sessions`;

-- Dumping structure for table pos_platform.subscriptions
DROP TABLE IF EXISTS `subscriptions`;
CREATE TABLE IF NOT EXISTS `subscriptions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint unsigned NOT NULL,
  `plan_id` bigint unsigned NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'trial',
  `started_at` timestamp NULL DEFAULT NULL,
  `expired_at` timestamp NULL DEFAULT NULL,
  `trial_ends_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subscriptions_tenant_id_foreign` (`tenant_id`),
  KEY `subscriptions_plan_id_foreign` (`plan_id`),
  KEY `subscriptions_status_index` (`status`),
  CONSTRAINT `subscriptions_plan_id_foreign` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `subscriptions_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pos_platform.subscriptions: ~0 rows (approximately)
DELETE FROM `subscriptions`;

-- Dumping structure for table pos_platform.tenants
DROP TABLE IF EXISTS `tenants`;
CREATE TABLE IF NOT EXISTS `tenants` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending_setup',
  `trial_ends_at` timestamp NULL DEFAULT NULL,
  `onboarded_at` timestamp NULL DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tenants_code_unique` (`code`),
  KEY `tenants_owner_email_index` (`owner_email`),
  KEY `tenants_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pos_platform.tenants: ~0 rows (approximately)
DELETE FROM `tenants`;

-- Dumping structure for table pos_platform.tenant_database_configs
DROP TABLE IF EXISTS `tenant_database_configs`;
CREATE TABLE IF NOT EXISTS `tenant_database_configs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint unsigned NOT NULL,
  `connection_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `db_host` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `db_port` int unsigned DEFAULT NULL,
  `db_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `db_username` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `db_password_encrypted` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tenant_database_configs_tenant_id_foreign` (`tenant_id`),
  CONSTRAINT `tenant_database_configs_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pos_platform.tenant_database_configs: ~0 rows (approximately)
DELETE FROM `tenant_database_configs`;

-- Dumping structure for table pos_platform.tenant_domains
DROP TABLE IF EXISTS `tenant_domains`;
CREATE TABLE IF NOT EXISTS `tenant_domains` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint unsigned NOT NULL,
  `domain` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'subdomain',
  `is_primary` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tenant_domains_domain_unique` (`domain`),
  KEY `tenant_domains_tenant_id_foreign` (`tenant_id`),
  CONSTRAINT `tenant_domains_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pos_platform.tenant_domains: ~0 rows (approximately)
DELETE FROM `tenant_domains`;

-- Dumping structure for table pos_platform.tenant_provision_logs
DROP TABLE IF EXISTS `tenant_provision_logs`;
CREATE TABLE IF NOT EXISTS `tenant_provision_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint unsigned NOT NULL,
  `step` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `message` text COLLATE utf8mb4_unicode_ci,
  `payload` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tenant_provision_logs_tenant_id_foreign` (`tenant_id`),
  CONSTRAINT `tenant_provision_logs_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pos_platform.tenant_provision_logs: ~0 rows (approximately)
DELETE FROM `tenant_provision_logs`;

-- Dumping structure for table pos_platform.users
DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_platform_admin` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table pos_platform.users: ~1 rows (approximately)
DELETE FROM `users`;
INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `is_platform_admin`, `created_at`, `updated_at`) VALUES
	(1, 'Platform Owner', 'owner@platform.test', NULL, '$2y$12$B0KyUR5/AdFXrS9M4R/Yiu6RqLdM7rfqpDyCwc/wQaxmRcuD4xmS.', NULL, 1, '2026-05-08 01:58:27', '2026-05-08 01:58:27');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
