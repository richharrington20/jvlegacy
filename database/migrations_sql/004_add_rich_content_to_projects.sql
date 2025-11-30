-- Add rich content fields to projects table
ALTER TABLE `projects` 
  ADD COLUMN IF NOT EXISTS `map_embed_code` text DEFAULT NULL AFTER `description`,
  ADD COLUMN IF NOT EXISTS `latitude` decimal(10,8) DEFAULT NULL AFTER `map_embed_code`,
  ADD COLUMN IF NOT EXISTS `longitude` decimal(11,8) DEFAULT NULL AFTER `latitude`,
  ADD COLUMN IF NOT EXISTS `surrounding_area` text DEFAULT NULL AFTER `longitude`,
  ADD COLUMN IF NOT EXISTS `proposed_designs` text DEFAULT NULL AFTER `surrounding_area`,
  ADD COLUMN IF NOT EXISTS `drawings` text DEFAULT NULL AFTER `proposed_designs`,
  ADD COLUMN IF NOT EXISTS `location_details` text DEFAULT NULL AFTER `drawings`,
  ADD COLUMN IF NOT EXISTS `neighborhood_info` text DEFAULT NULL AFTER `location_details`,
  ADD COLUMN IF NOT EXISTS `development_plans` text DEFAULT NULL AFTER `neighborhood_info`,
  ADD COLUMN IF NOT EXISTS `show_to_investors` tinyint(1) DEFAULT 1 AFTER `development_plans`,
  ADD COLUMN IF NOT EXISTS `show_map` tinyint(1) DEFAULT 1 AFTER `show_to_investors`,
  ADD COLUMN IF NOT EXISTS `show_surrounding_area` tinyint(1) DEFAULT 1 AFTER `show_map`,
  ADD COLUMN IF NOT EXISTS `show_designs` tinyint(1) DEFAULT 1 AFTER `show_surrounding_area`,
  ADD COLUMN IF NOT EXISTS `show_drawings` tinyint(1) DEFAULT 1 AFTER `show_designs`,
  ADD COLUMN IF NOT EXISTS `show_location_details` tinyint(1) DEFAULT 1 AFTER `show_drawings`,
  ADD COLUMN IF NOT EXISTS `show_neighborhood_info` tinyint(1) DEFAULT 1 AFTER `show_location_details`,
  ADD COLUMN IF NOT EXISTS `show_development_plans` tinyint(1) DEFAULT 1 AFTER `show_neighborhood_info`;

