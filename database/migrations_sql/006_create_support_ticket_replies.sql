-- Create support_ticket_replies table
CREATE TABLE IF NOT EXISTS `support_ticket_replies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ticket_id` bigint(20) unsigned NOT NULL,
  `account_id` bigint(20) unsigned DEFAULT NULL,
  `message` text NOT NULL,
  `is_from_support` tinyint(1) DEFAULT 0,
  `is_system` tinyint(1) DEFAULT 0,
  `created_on` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_ticket_id` (`ticket_id`),
  KEY `idx_account_id` (`account_id`),
  KEY `idx_created_on` (`created_on`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add ticket_id and other fields to support_tickets if they don't exist
ALTER TABLE `support_tickets` 
  ADD COLUMN IF NOT EXISTS `ticket_id` varchar(20) DEFAULT NULL AFTER `id`,
  ADD COLUMN IF NOT EXISTS `created_on` datetime DEFAULT NULL AFTER `status`,
  ADD COLUMN IF NOT EXISTS `updated_on` datetime DEFAULT NULL AFTER `created_on`,
  ADD COLUMN IF NOT EXISTS `deleted` tinyint(1) DEFAULT 0 AFTER `updated_on`,
  ADD INDEX IF NOT EXISTS `idx_ticket_id` (`ticket_id`),
  ADD INDEX IF NOT EXISTS `idx_status` (`status`);

