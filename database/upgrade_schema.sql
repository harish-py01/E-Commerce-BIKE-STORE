-- Add parent_id to categories for nested hierarchy
ALTER TABLE `categories` ADD COLUMN IF NOT EXISTS `parent_id` INT DEFAULT NULL;
ALTER TABLE `categories` ADD CONSTRAINT `fk_category_parent` FOREIGN KEY (`parent_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE;

-- Expand models table
ALTER TABLE `models` ADD COLUMN IF NOT EXISTS `variant` VARCHAR(100) DEFAULT NULL;
ALTER TABLE `models` ADD COLUMN IF NOT EXISTS `start_year` INT DEFAULT NULL;
ALTER TABLE `models` ADD COLUMN IF NOT EXISTS `end_year` INT DEFAULT NULL;
ALTER TABLE `models` ADD COLUMN IF NOT EXISTS `engine_type` VARCHAR(100) DEFAULT NULL;
ALTER TABLE `models` ADD COLUMN IF NOT EXISTS `fuel_type` VARCHAR(50) DEFAULT NULL;
ALTER TABLE `models` ADD COLUMN IF NOT EXISTS `transmission` VARCHAR(50) DEFAULT NULL;
ALTER TABLE `models` ADD COLUMN IF NOT EXISTS `vehicle_category` VARCHAR(50) DEFAULT NULL;

-- Update existing models to avoid null constraint issues if any, but they are DEFAULT NULL so it's fine.
