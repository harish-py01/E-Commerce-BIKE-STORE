-- Bike Spare Parts E-Commerce Database Dump
-- Project: Bike Spare Parts E-Commerce Website (MCA Mini Project)
-- Author: Grish A
-- Compatibility: MySQL / MariaDB (XAMPP)

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS cart;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS brands;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS customers;
DROP TABLE IF EXISTS admin;
SET FOREIGN_KEY_CHECKS = 1;

-- --------------------------------------------------------
-- Table structure for table `admin`
-- --------------------------------------------------------
CREATE TABLE `admin` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `role` VARCHAR(20) DEFAULT 'Super Admin',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `customers`
-- --------------------------------------------------------
CREATE TABLE `customers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `city` VARCHAR(50) DEFAULT NULL,
  `state` VARCHAR(50) DEFAULT NULL,
  `zip_code` VARCHAR(15) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `categories`
-- --------------------------------------------------------
CREATE TABLE `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `description` TEXT DEFAULT NULL,
  `image` VARCHAR(255) DEFAULT 'default_cat.jpg',
  `status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `brands`
-- --------------------------------------------------------
CREATE TABLE `brands` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `logo` VARCHAR(255) DEFAULT 'default_brand.jpg',
  `description` TEXT DEFAULT NULL,
  `status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `products`
-- --------------------------------------------------------
CREATE TABLE `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NOT NULL,
  `brand_id` INT NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `description` TEXT DEFAULT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `stock` INT NOT NULL DEFAULT 0,
  `image` VARCHAR(255) DEFAULT 'default_product.jpg',
  `gallery` TEXT DEFAULT NULL,
  `is_featured` TINYINT(1) DEFAULT 0,
  `is_popular` TINYINT(1) DEFAULT 0,
  `status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`brand_id`) REFERENCES `brands`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `cart`
-- --------------------------------------------------------
CREATE TABLE `cart` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `customer_id` INT DEFAULT NULL,
  `session_id` VARCHAR(255) NOT NULL,
  `product_id` INT NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `orders`
-- --------------------------------------------------------
CREATE TABLE `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_number` VARCHAR(50) NOT NULL UNIQUE,
  `customer_id` INT NOT NULL,
  `total_amount` DECIMAL(10,2) NOT NULL,
  `shipping_name` VARCHAR(100) NOT NULL,
  `shipping_phone` VARCHAR(20) NOT NULL,
  `shipping_address` TEXT NOT NULL,
  `shipping_city` VARCHAR(50) NOT NULL,
  `shipping_state` VARCHAR(50) NOT NULL,
  `shipping_zip` VARCHAR(15) NOT NULL,
  `payment_method` VARCHAR(50) NOT NULL DEFAULT 'COD',
  `payment_status` VARCHAR(20) NOT NULL DEFAULT 'Pending',
  `transaction_id` VARCHAR(100) DEFAULT NULL,
  `payment_details` TEXT DEFAULT NULL,
  `order_status` ENUM('Pending', 'Processing', 'Delivered', 'Cancelled') NOT NULL DEFAULT 'Pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `order_items`
-- --------------------------------------------------------
CREATE TABLE `order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `product_name` VARCHAR(255) NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `quantity` INT NOT NULL,
  `total` DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- SAMPLE DATA INSERTION
-- --------------------------------------------------------

-- Admin (Password: admin123)
INSERT INTO `admin` (`id`, `username`, `email`, `password`, `full_name`, `role`) VALUES
(1, 'admin', 'admin@bikestore.com', '$2y$10$fcUl1xtm.4JN4BgtEE3slOt33X6vMryYbGNgMWtETd3rYoEe0SEV6', 'System Administrator', 'Super Admin');

-- Customers (Password: user123)
INSERT INTO `customers` (`id`, `name`, `email`, `password`, `phone`, `address`, `city`, `state`, `zip_code`) VALUES
(1, 'Rahul Sharma', 'rahul@example.com', '$2y$10$fcUl1xtm.4JN4BgtEE3slOt33X6vMryYbGNgMWtETd3rYoEe0SEV6', '9876543210', '123 MG Road, Indiranagar', 'Bengaluru', 'Karnataka', '560038'),
(2, 'Priya Patel', 'priya@example.com', '$2y$10$fcUl1xtm.4JN4BgtEE3slOt33X6vMryYbGNgMWtETd3rYoEe0SEV6', '9812345678', '45 Park Street, Connaught Place', 'New Delhi', 'Delhi', '110001');

-- Categories
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image`, `status`) VALUES
(1, 'Braking Systems', 'braking-systems', 'High performance ceramic and metallic brake pads, rotors, and calipers.', 'cat_braking.jpg', 1),
(2, 'Engine Parts', 'engine-parts', 'Pistons, spark plugs, cylinder heads, and high flow air filters.', 'cat_engine.jpg', 1),
(3, 'Chains & Sprockets', 'chains-sprockets', 'Heavy duty drive chains, front and rear sprockets for all bike models.', 'cat_chains.jpg', 1),
(4, 'Tires & Wheels', 'tires-wheels', 'Tubeless radial tires, alloy rims, and inner tubes.', 'cat_tires.jpg', 1),
(5, 'Oils & Lubricants', 'oils-lubricants', 'Synthetic engine oils, chain lubes, brake fluids, and coolants.', 'cat_oils.jpg', 1),
(6, 'Rider Accessories', 'rider-accessories', 'Helmets, riding gloves, mirrors, LED lights, and luggage racks.', 'cat_accessories.jpg', 1);

-- Brands
INSERT INTO `brands` (`id`, `name`, `slug`, `logo`, `description`, `status`) VALUES
(1, 'Brembo', 'brembo', 'brand_brembo.jpg', 'World renowned high performance braking systems.', 1),
(2, 'Yamaha Genuine', 'yamaha-genuine', 'brand_yamaha.jpg', 'Original OEM spare parts for Yamaha motorcycles.', 1),
(3, 'Honda OEM', 'honda-oem', 'brand_honda.jpg', 'Authentic spare parts engineered for Honda bikes.', 1),
(4, 'Akrapovič', 'akrapovic', 'brand_akrapovic.jpg', 'Premium exhaust systems for sports & racing motorcycles.', 1),
(5, 'Motul', 'motul', 'brand_motul.jpg', 'High quality synthetic engine lubricants and additives.', 1),
(6, 'Dunlop Tires', 'dunlop-tires', 'brand_dunlop.jpg', 'Ultra grip sports radial and off-road tires.', 1);

-- Products
INSERT INTO `products` (`id`, `category_id`, `brand_id`, `name`, `slug`, `description`, `price`, `stock`, `image`, `gallery`, `is_featured`, `is_popular`, `status`) VALUES
(1, 1, 1, 'Brembo Ceramic Front Brake Pads', 'brembo-ceramic-front-brake-pads', 'Premium sintered ceramic front brake pads providing extreme stopping power, zero fade under high heat, and low dust emission. Compatible with 200cc-400cc sports bikes.', 1499.00, 35, 'brake_pad.jpg', '["brake_pad.jpg", "disc_rotor.jpg"]', 1, 1, 1),
(2, 1, 1, 'Brembo Floating Disc Rotor 300mm', 'brembo-floating-disc-rotor-300mm', 'Laser-cut stainless steel floating brake rotor for maximum heat dissipation and crisp feedback. Ideal for high speed braking.', 3299.00, 18, 'disc_rotor.jpg', '["disc_rotor.jpg"]', 1, 0, 1),
(3, 2, 2, 'NGK Iridium IX Spark Plug (Pair)', 'ngk-iridium-ix-spark-plug-pair', 'High performance iridium spark plugs ensuring superior ignitability, faster throttle response, and improved fuel efficiency.', 850.00, 50, 'spark_plug.jpg', '["spark_plug.jpg"]', 0, 1, 1),
(4, 2, 3, 'High Flow Performance Air Filter', 'high-flow-performance-air-filter', 'Washable and reusable multi-layer cotton gauze air filter. Increases horsepower and torque while delivering maximum air filtration.', 1199.00, 25, 'air_filter.jpg', '["air_filter.jpg"]', 1, 0, 1),
(5, 3, 2, 'DID 520 Pitch O-Ring Drive Chain Kit', 'did-520-pitch-o-ring-drive-chain-kit', 'Heavy duty gold X-ring drive chain complete with front 14T and rear 45T steel sprockets. Rated for high torque street and race bikes.', 2899.00, 15, 'chain_kit.jpg', '["chain_kit.jpg"]', 1, 1, 1),
(6, 4, 6, 'Dunlop Sportmax Radial Rear Tire 140/70-17', 'dunlop-sportmax-radial-rear-tire-140-70-17', 'All-weather dual compound sport radial tire featuring deep tread grooves for superior wet grip and high cornering stability.', 4599.00, 12, 'tires.jpg', '["tires.jpg"]', 0, 1, 1),
(7, 5, 5, 'Motul 7100 4T 10W40 Synthetic Engine Oil (1L)', 'motul-7100-4t-10w40-synthetic-engine-oil-1l', '100% Synthetic 4-stroke engine oil formulated with Ester technology. Smooth gear shifts and outstanding wear protection.', 980.00, 60, 'engine_oil.jpg', '["engine_oil.jpg"]', 1, 1, 1),
(8, 6, 4, 'Akrapovič Slip-On Racing Exhaust Carbon', 'akrapovic-slip-on-racing-exhaust-carbon', 'Lightweight carbon fiber slip-on exhaust muffler delivering a deep resonance exhaust note and weight savings of up to 3.2 kg.', 12499.00, 6, 'exhaust.jpg', '["exhaust.jpg"]', 1, 0, 1),
(9, 6, 2, 'Full Face Aerodynamic Sports Helmet', 'full-face-aerodynamic-sports-helmet', 'ECE 22.05 certified polycarbonate outer shell with anti-scratch pinlock visor and breathable removable cheek pads.', 3499.00, 20, 'helmet.jpg', '["helmet.jpg"]', 0, 1, 1),
(10, 5, 5, 'Motul Chain Lube Road & Clean Combo Pack', 'motul-chain-lube-road-clean-combo-pack', 'Complete chain maintenance kit featuring 400ml aerosol spray lube and 400ml chain cleaner with specialized brush.', 750.00, 40, 'oil_filter.jpg', '["oil_filter.jpg"]', 0, 0, 1);

-- Sample Orders
INSERT INTO `orders` (`id`, `order_number`, `customer_id`, `total_amount`, `shipping_name`, `shipping_phone`, `shipping_address`, `shipping_city`, `shipping_state`, `shipping_zip`, `payment_method`, `payment_status`, `order_status`, `created_at`) VALUES
(1, 'ORD-20260730-1001', 1, 2479.00, 'Rahul Sharma', '9876543210', '123 MG Road, Indiranagar', 'Bengaluru', 'Karnataka', '560038', 'COD', 'Pending', 'Delivered', '2026-07-28 10:30:00'),
(2, 'ORD-20260730-1002', 2, 4599.00, 'Priya Patel', '9812345678', '45 Park Street, Connaught Place', 'New Delhi', 'Delhi', '110001', 'UPI', 'Paid', 'Processing', '2026-07-29 14:15:00');

-- Order Items
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `price`, `quantity`, `total`) VALUES
(1, 1, 1, 'Brembo Ceramic Front Brake Pads', 1499.00, 1, 1499.00),
(2, 1, 7, 'Motul 7100 4T 10W40 Synthetic Engine Oil (1L)', 980.00, 1, 980.00),
(3, 2, 6, 'Dunlop Sportmax Radial Rear Tire 140/70-17', 4599.00, 1, 4599.00);
