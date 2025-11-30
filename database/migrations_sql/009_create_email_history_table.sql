-- Create email_history table for tracking all emails sent to investors
CREATE TABLE IF NOT EXISTS `email_history` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `account_id` bigint(20) unsigned NOT NULL COMMENT 'The account that received the email',
  `email_type` varchar(50) NOT NULL COMMENT 'Type: document, project_update, support_ticket, system_status, etc.',
  `subject` varchar(255) DEFAULT NULL,
  `recipient` varchar(255) NOT NULL,
  `project_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Related project if applicable',
  `related_id` bigint(20) unsigned DEFAULT NULL COMMENT 'ID of related record (update_id, ticket_id, etc.)',
  `sent_by` bigint(20) unsigned DEFAULT NULL COMMENT 'Account that triggered the email',
  `sent_at` datetime DEFAULT NULL,
  `created_on` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_account_id` (`account_id`),
  KEY `idx_email_type` (`email_type`),
  KEY `idx_project_id` (`project_id`),
  KEY `idx_sent_at` (`sent_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

