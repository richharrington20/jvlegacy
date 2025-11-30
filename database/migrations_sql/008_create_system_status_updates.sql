-- Create system_status_updates table for status update history
CREATE TABLE IF NOT EXISTS `system_status_updates` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `status_id` bigint(20) unsigned NOT NULL COMMENT 'The system status this update belongs to',
  `account_id` bigint(20) unsigned DEFAULT NULL COMMENT 'The account that posted the update',
  `message` text NOT NULL COMMENT 'The update message',
  `is_fixed` tinyint(1) DEFAULT 0 COMMENT 'Whether the issue has been marked as fixed',
  `fixed_by` bigint(20) unsigned DEFAULT NULL COMMENT 'Account that marked it as fixed',
  `fixed_on` datetime DEFAULT NULL,
  `created_on` datetime DEFAULT NULL,
  `updated_on` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_status_id` (`status_id`),
  KEY `idx_account_id` (`account_id`),
  KEY `idx_created_on` (`created_on`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

