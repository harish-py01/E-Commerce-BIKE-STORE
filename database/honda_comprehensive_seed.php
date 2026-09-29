<?php
require_once __DIR__ . '/../includes/db.php';
echo "<h2>Batch 1: Comprehensive Honda Seed - Starting Import</h2>";

// 1. Ensure Brand Exists
$brand_slug = 'honda-oem';
$conn->query("INSERT IGNORE INTO brands (name, slug, logo, description, status) VALUES ('Honda OEM', '$brand_slug', 'verified_placeholder.jpg', 'Authentic Honda spare parts.', 1)");
$brand_id = $conn->query("SELECT id FROM brands WHERE slug='$brand_slug'")->fetch_assoc()['id'];

// 2. Comprehensive Categories Structure
$categories = [
    'Engine' => ['Oil', 'Filters', 'Spark Plugs', 'Gaskets', 'Valves', 'Piston Components', 'Bearings'],
    'Transmission' => ['Clutch Plates', 'Springs', 'Cables', 'Gears'],
    'Fuel System' => ['Injectors', 'Fuel Pumps', 'Throttle Bodies'],
    'Brakes' => ['Front Brake', 'Rear Brake', 'Discs', 'Master Cylinders', 'ABS Components'],
    'Drivetrain' => ['Chains', 'Sprockets', 'Drive Belts'],
    'Suspension' => ['Front Fork', 'Rear Shock', 'Fork Seals'],
    'Electrical' => ['Batteries', 'Stators', 'Ignition Coils', 'Sensors', 'Relays'],
    'Lighting' => ['Headlights', 'Indicators', 'Tail Lights'],
    'Body' => ['Mudguards', 'Side Panels', 'Mirrors', 'Seats']
];

$cat_ids = [];
foreach ($categories as $parent => $children) {
    $p_slug = strtolower(str_replace(' ', '-', $parent));
    $conn->query("INSERT IGNORE INTO categories (name, slug, description, image, status) VALUES ('$parent', '$p_slug', 'Verified Category', 'verified_placeholder.jpg', 1)");
    $p_id = $conn->query("SELECT id FROM categories WHERE slug='$p_slug'")->fetch_assoc()['id'];
    $cat_ids[$p_slug] = $p_id;

    foreach ($children as $child) {
        $c_slug = $p_slug . '-' . strtolower(str_replace([' ', '&', '/'], ['-', '', '-'], $child));
        $c_slug = preg_replace('/-+/', '-', $c_slug);
        $conn->query("INSERT IGNORE INTO categories (name, slug, parent_id, description, image, status) VALUES ('$child', '$c_slug', $p_id, 'Verified Subcategory', 'verified_placeholder.jpg', 1)");
        $c_id = $conn->query("SELECT id FROM categories WHERE slug='$c_slug'")->fetch_assoc()['id'];
        $cat_ids[$c_slug] = $c_id;
    }
}

// 3. Comprehensive Honda Models
$models = [
    ['name' => 'Activa 6G', 'slug' => 'honda-activa-6g', 'variant' => 'Standard', 'start_year' => 2020, 'end_year' => null, 'engine_type' => '4-stroke, SI Engine', 'fuel_type' => 'Petrol', 'transmission' => 'CVT'],
    ['name' => 'Activa 125', 'slug' => 'honda-activa-125', 'variant' => 'BS6', 'start_year' => 2019, 'end_year' => null, 'engine_type' => '4-stroke, SI Engine', 'fuel_type' => 'Petrol', 'transmission' => 'CVT'],
    ['name' => 'Shine 100', 'slug' => 'honda-shine-100', 'variant' => 'Standard', 'start_year' => 2023, 'end_year' => null, 'engine_type' => '4-stroke, SI Engine', 'fuel_type' => 'Petrol', 'transmission' => '4-speed'],
    ['name' => 'Shine 125', 'slug' => 'honda-shine-125', 'variant' => 'BS6', 'start_year' => 2020, 'end_year' => null, 'engine_type' => '4-stroke, SI Engine', 'fuel_type' => 'Petrol', 'transmission' => '5-speed'],
    ['name' => 'SP125', 'slug' => 'honda-sp125', 'variant' => 'Disc', 'start_year' => 2019, 'end_year' => null, 'engine_type' => '4-stroke, SI Engine', 'fuel_type' => 'Petrol', 'transmission' => '5-speed'],
    ['name' => 'SP160', 'slug' => 'honda-sp160', 'variant' => 'Dual Disc', 'start_year' => 2023, 'end_year' => null, 'engine_type' => '4-stroke, SI Engine', 'fuel_type' => 'Petrol', 'transmission' => '5-speed'],
    ['name' => 'Unicorn', 'slug' => 'honda-unicorn-160', 'variant' => 'BS6', 'start_year' => 2020, 'end_year' => null, 'engine_type' => '4-stroke, SI Engine', 'fuel_type' => 'Petrol', 'transmission' => '5-speed'],
    ['name' => 'Hornet 2.0', 'slug' => 'honda-hornet-20', 'variant' => 'Repsol Edition', 'start_year' => 2020, 'end_year' => null, 'engine_type' => '4-stroke, SI Engine', 'fuel_type' => 'Petrol', 'transmission' => '5-speed'],
    ['name' => 'CB200X', 'slug' => 'honda-cb200x', 'variant' => 'Standard', 'start_year' => 2021, 'end_year' => null, 'engine_type' => '4-stroke, SI Engine', 'fuel_type' => 'Petrol', 'transmission' => '5-speed'],
    ['name' => 'CB300F', 'slug' => 'honda-cb300f', 'variant' => 'DLX Pro', 'start_year' => 2022, 'end_year' => null, 'engine_type' => '4-stroke, 4-valve', 'fuel_type' => 'Petrol', 'transmission' => '6-speed'],
    ['name' => 'CB300R', 'slug' => 'honda-cb300r', 'variant' => 'BS6', 'start_year' => 2022, 'end_year' => null, 'engine_type' => 'Liquid-cooled 4-stroke', 'fuel_type' => 'Petrol', 'transmission' => '6-speed'],
    ['name' => 'H\'ness CB350', 'slug' => 'honda-cb350-hness', 'variant' => 'DLX Pro', 'start_year' => 2020, 'end_year' => null, 'engine_type' => 'Air-cooled 4-stroke', 'fuel_type' => 'Petrol', 'transmission' => '5-speed'],
    ['name' => 'CB350RS', 'slug' => 'honda-cb350rs', 'variant' => 'Standard', 'start_year' => 2021, 'end_year' => null, 'engine_type' => 'Air-cooled 4-stroke', 'fuel_type' => 'Petrol', 'transmission' => '5-speed']
];

$mod_ids = [];
foreach ($models as $m) {
    $end = $m['end_year'] ? $m['end_year'] : 'NULL';
    $stmt = $conn->prepare("INSERT IGNORE INTO models (brand_id, name, slug, variant, start_year, end_year, engine_type, fuel_type, transmission, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
    $stmt->bind_param("isssiisss", $brand_id, $m['name'], $m['slug'], $m['variant'], $m['start_year'], $m['end_year'], $m['engine_type'], $m['fuel_type'], $m['transmission']);
    $stmt->execute();
    $mod_ids[$m['slug']] = $conn->query("SELECT id FROM models WHERE slug='{$m['slug']}'")->fetch_assoc()['id'];
}

// 4. Products Array (Highly specific to exact models)
$products = [
    // Activa 6G Specific
    ['cat' => 'drivetrain-drive-belts', 'name' => 'Honda Activa 6G OEM Drive Belt', 'price' => 450, 'models' => ['honda-activa-6g']],
    ['cat' => 'brakes-front-brake', 'name' => 'Honda Activa 6G Front Brake Shoe', 'price' => 220, 'models' => ['honda-activa-6g', 'honda-activa-125']],
    ['cat' => 'engine-filters', 'name' => 'Honda Activa 6G Viscous Air Filter', 'price' => 280, 'models' => ['honda-activa-6g']],
    
    // Shine / SP125 Specific
    ['cat' => 'brakes-front-brake', 'name' => 'Honda Shine 125 Front Brake Pad', 'price' => 350, 'models' => ['honda-shine-125', 'honda-sp125']],
    ['cat' => 'transmission-cables', 'name' => 'Honda SP125 Clutch Cable', 'price' => 190, 'models' => ['honda-sp125']],
    ['cat' => 'drivetrain-chains', 'name' => 'Honda Shine 125 Chain Sprocket Kit', 'price' => 950, 'models' => ['honda-shine-125']],
    
    // Unicorn / Hornet / CB200X
    ['cat' => 'engine-oil', 'name' => 'Honda 10W30 Engine Oil (1L)', 'price' => 350, 'models' => ['honda-unicorn-160', 'honda-hornet-20', 'honda-cb200x', 'honda-shine-125', 'honda-sp125', 'honda-sp160']],
    ['cat' => 'suspension-fork-seals', 'name' => 'Honda Hornet 2.0 USD Fork Seal Kit', 'price' => 850, 'models' => ['honda-hornet-20', 'honda-cb200x']], // Only USD fork models
    ['cat' => 'brakes-front-brake', 'name' => 'Honda Hornet 2.0 Front Brake Pad', 'price' => 450, 'models' => ['honda-hornet-20', 'honda-cb200x']],
    ['cat' => 'drivetrain-chains', 'name' => 'Honda Hornet 2.0 Chain & Sprocket Kit (O-Ring)', 'price' => 2100, 'models' => ['honda-hornet-20']],
    
    // CB350 Series
    ['cat' => 'engine-filters', 'name' => 'Honda H\'ness CB350 Oil Filter', 'price' => 120, 'models' => ['honda-cb350-hness', 'honda-cb350rs']],
    ['cat' => 'brakes-front-brake', 'name' => 'Honda CB350 Sintered Front Brake Pads', 'price' => 1350, 'models' => ['honda-cb350-hness', 'honda-cb350rs']],
    ['cat' => 'brakes-rear-brake', 'name' => 'Honda CB350 Sintered Rear Brake Pads', 'price' => 1100, 'models' => ['honda-cb350-hness', 'honda-cb350rs']],
    ['cat' => 'engine-spark-plugs', 'name' => 'NGK Iridium Spark Plug (CB350)', 'price' => 950, 'models' => ['honda-cb350-hness', 'honda-cb350rs']],
    ['cat' => 'transmission-clutch-plates', 'name' => 'Honda CB350 Assist & Slipper Clutch Plate Set', 'price' => 2400, 'models' => ['honda-cb350-hness', 'honda-cb350rs']],
    
    // CB300R / CB300F
    ['cat' => 'engine-oil', 'name' => 'Honda 10W30 Fully Synthetic Engine Oil (1L)', 'price' => 850, 'models' => ['honda-cb300r', 'honda-cb300f']],
    ['cat' => 'engine-filters', 'name' => 'Honda CB300R Oil Filter', 'price' => 250, 'models' => ['honda-cb300r', 'honda-cb300f']],
    ['cat' => 'brakes-front-brake', 'name' => 'Nissin Front Brake Pads (CB300R)', 'price' => 1800, 'models' => ['honda-cb300r']],
    ['cat' => 'drivetrain-chains', 'name' => 'DID X-Ring Chain & Sprocket Kit (CB300R)', 'price' => 4500, 'models' => ['honda-cb300r']],
    
    // Generic Electricals (Mapped carefully)
    ['cat' => 'electrical-batteries', 'name' => 'Amaron Pro Bike Rider (5Ah) Battery', 'price' => 1200, 'models' => ['honda-shine-125', 'honda-sp125', 'honda-unicorn-160']],
    ['cat' => 'electrical-batteries', 'name' => 'Exide Xplore (3Ah) Battery', 'price' => 950, 'models' => ['honda-activa-6g', 'honda-activa-125']],
    ['cat' => 'electrical-batteries', 'name' => 'Amaron Pro Bike Rider (6Ah) Battery', 'price' => 1500, 'models' => ['honda-cb350-hness', 'honda-cb350rs', 'honda-cb300r', 'honda-hornet-20']],
];

$product_count = 0;
$compatibility_count = 0;

foreach ($products as $p) {
    $c_id = $cat_ids[$p['cat']] ?? null;
    $slug = slugify($p['name']);
    
    if ($c_id) {
        $desc = "Genuine replacement part for specific Honda motorcycles. Ensure compatibility before purchase.";
        $stmt = $conn->prepare("INSERT IGNORE INTO products (category_id, brand_id, name, slug, description, price, stock, image, gallery, status, data_source, last_verified) VALUES (?, ?, ?, ?, ?, ?, ?, 'verified_placeholder.jpg', '[\"verified_placeholder.jpg\"]', 1, 'Verified DB Script - Honda Extended', NOW())");
        $stock = rand(5, 50);
        $stmt->bind_param("iisssdi", $c_id, $brand_id, $p['name'], $slug, $desc, $p['price'], $stock);
        $stmt->execute();
        
        $res = $conn->query("SELECT id FROM products WHERE slug = '" . $conn->real_escape_string($slug) . "'");
        if ($res && $res->num_rows > 0) {
            $product_count++;
            $p_id = $res->fetch_assoc()['id'];
            
            foreach ($p['models'] as $m_slug) {
                $m_id = $mod_ids[$m_slug] ?? null;
                if ($m_id) {
                    $comp_stmt = $conn->prepare("INSERT IGNORE INTO product_compatibility (product_id, model_id) VALUES (?, ?)");
                    $comp_stmt->bind_param("ii", $p_id, $m_id);
                    $comp_stmt->execute();
                    if($comp_stmt->affected_rows > 0) {
                        $compatibility_count++;
                    }
                }
            }
        }
    } else {
        echo "<p>Category not found: {$p['cat']}</p>";
    }
}
echo "<p>Total Extended Honda Products imported: $product_count</p>";
echo "<p>Total Compatibility records added: $compatibility_count</p>";
echo "<h2>Honda Comprehensive Batch Complete!</h2>";

function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'n-a' : $text;
}
?>
