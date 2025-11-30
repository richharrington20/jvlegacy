-- Create account_shares table for sharing account access
CREATE TABLE IF NOT EXISTS `account_shares` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `primary_account_id` bigint(20) unsigned NOT NULL COMMENT 'The account that owns the investments',
  `shared_account_id` bigint(20) unsigned NOT NULL COMMENT 'The account that has been granted access',
  `status` enum('pending','active','revoked') DEFAULT 'pending' COMMENT 'Status of the share',
  `invited_by` bigint(20) unsigned DEFAULT NULL COMMENT 'Account ID that sent the invitation',
  `invited_on` datetime DEFAULT NULL,
  `accepted_on` datetime DEFAULT NULL,
  `revoked_on` datetime DEFAULT NULL,
  `created_on` datetime DEFAULT NULL,
  `updated_on` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_primary_account` (`primary_account_id`),
  KEY `idx_shared_account` (`shared_account_id`),
  KEY `idx_status` (`status`),
  UNIQUE KEY `unique_share` (`primary_account_id`, `shared_account_id`, `deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

