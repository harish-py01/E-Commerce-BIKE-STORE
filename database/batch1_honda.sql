-- Batch 1: Honda
-- Verified Data Insertion

INSERT IGNORE INTO brands (name, slug, logo, description, status) VALUES 
('Honda OEM', 'honda-oem', 'verified_placeholder.jpg', 'Authentic spare parts engineered for Honda bikes.', 1);

-- Note: We assume parent categories exist or will be created. Let's create subcategories.
INSERT IGNORE INTO categories (name, slug, description, status) VALUES 
('Filters', 'filters', 'Air, Oil, and Fuel Filters', 1),
('Brake Pads & Shoes', 'brake-pads', 'Braking components', 1),
('Spark Plugs', 'spark-plugs', 'Ignition Spark Plugs', 1),
('Cables', 'cables', 'Clutch, Throttle, and Brake Cables', 1);

-- We fetch the brand ID and Category IDs in a structured PHP script or assume they are mapped.
-- To avoid complex SQL variable issues across different environments, I will create a PHP script `batch1_honda.php` that performs the insertion safely.
