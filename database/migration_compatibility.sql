-- --------------------------------------------------------
-- Table structure for table `models`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `models` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `brand_id` INT NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `engine_capacity` VARCHAR(50) DEFAULT NULL,
  `status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`brand_id`) REFERENCES `brands`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `product_compatibility`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `product_compatibility` (
  `product_id` INT NOT NULL,
  `model_id` INT NOT NULL,
  PRIMARY KEY (`product_id`, `model_id`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`model_id`) REFERENCES `models`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Alter table `products` for tracking data source
-- --------------------------------------------------------
-- Only add these columns if they don't exist
ALTER TABLE `products` ADD COLUMN IF NOT EXISTS `data_source` VARCHAR(100) DEFAULT NULL;
ALTER TABLE `products` ADD COLUMN IF NOT EXISTS `last_verified` TIMESTAMP NULL DEFAULT NULL;
ALTER TABLE `products` ADD COLUMN IF NOT EXISTS `part_number` VARCHAR(100) DEFAULT NULL;
ALTER TABLE `products` ADD COLUMN IF NOT EXISTS `oem_number` VARCHAR(100) DEFAULT NULL;
