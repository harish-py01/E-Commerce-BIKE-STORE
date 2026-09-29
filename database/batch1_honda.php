<?php
require_once __DIR__ . '/../includes/db.php';
echo "<h2>Batch 1: Honda - Starting Import</h2>";

// 1. Ensure Brand Exists
$brand_slug = 'honda-oem';
$conn->query("INSERT IGNORE INTO brands (name, slug, logo, description, status) VALUES ('Honda OEM', '$brand_slug', 'verified_placeholder.jpg', 'Authentic Honda spare parts.', 1)");
$brand_id = $conn->query("SELECT id FROM brands WHERE slug='$brand_slug'")->fetch_assoc()['id'];

// 2. Categories Structure
$categories = [
    'Engine' => ['Filters', 'Spark Plugs', 'Gaskets'],
    'Brakes' => ['Front Brake', 'Rear Brake'],
    'Controls' => ['Cables', 'Levers']
];

$cat_ids = [];
foreach ($categories as $parent => $children) {
    $p_slug = strtolower(str_replace(' ', '-', $parent));
    $conn->query("INSERT IGNORE INTO categories (name, slug, description, image, status) VALUES ('$parent', '$p_slug', 'Verified Category', 'verified_placeholder.jpg', 1)");
    $p_id = $conn->query("SELECT id FROM categories WHERE slug='$p_slug'")->fetch_assoc()['id'];
    $cat_ids[$p_slug] = $p_id;

    foreach ($children as $child) {
        $c_slug = $p_slug . '-' . strtolower(str_replace([' ', '&'], ['-', ''], $child));
        $c_slug = str_replace('--', '-', $c_slug);
        $conn->query("INSERT IGNORE INTO categories (name, slug, parent_id, description, image, status) VALUES ('$child', '$c_slug', $p_id, 'Verified Subcategory', 'verified_placeholder.jpg', 1)");
        $c_id = $conn->query("SELECT id FROM categories WHERE slug='$c_slug'")->fetch_assoc()['id'];
        $cat_ids[$c_slug] = $c_id;
    }
}

// 3. Honda Models
$models = [
    ['name' => 'Activa 6G', 'slug' => 'honda-activa-6g', 'variant' => 'Standard', 'start_year' => 2020, 'end_year' => null, 'engine_type' => '4-stroke, SI Engine', 'fuel_type' => 'Petrol', 'transmission' => 'CVT'],
    ['name' => 'Activa 125', 'slug' => 'honda-activa-125-bs6', 'variant' => 'BS6', 'start_year' => 2019, 'end_year' => null, 'engine_type' => '4-stroke, SI Engine', 'fuel_type' => 'Petrol', 'transmission' => 'CVT'],
    ['name' => 'Shine', 'slug' => 'honda-shine-125', 'variant' => '125cc', 'start_year' => 2006, 'end_year' => null, 'engine_type' => 'Air Cooled, 4 Stroke', 'fuel_type' => 'Petrol', 'transmission' => '5-speed'],
    ['name' => 'H\'ness CB350', 'slug' => 'honda-hness-cb350', 'variant' => 'DLX', 'start_year' => 2020, 'end_year' => null, 'engine_type' => 'Air Cooled, 4 Stroke OHC', 'fuel_type' => 'Petrol', 'transmission' => '5-speed']
];

$mod_ids = [];
foreach ($models as $m) {
    $end = $m['end_year'] ? $m['end_year'] : 'NULL';
    $stmt = $conn->prepare("INSERT IGNORE INTO models (brand_id, name, slug, variant, start_year, end_year, engine_type, fuel_type, transmission, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
    $stmt->bind_param("isssiisss", $brand_id, $m['name'], $m['slug'], $m['variant'], $m['start_year'], $m['end_year'], $m['engine_type'], $m['fuel_type'], $m['transmission']);
    $stmt->execute();
    $mod_ids[$m['slug']] = $conn->query("SELECT id FROM models WHERE slug='{$m['slug']}'")->fetch_assoc()['id'];
}

// 4. Products & Compatibility
$products = [
    [
        'category' => 'engine-filters', 'name' => 'Honda Activa 6G Viscous Air Filter', 
        'slug' => 'honda-activa-6g-air-filter', 'desc' => 'Genuine OEM viscous air filter for Honda Activa 6G.',
        'price' => 280.00, 'stock' => 150, 'models' => ['honda-activa-6g']
    ],
    [
        'category' => 'brakes-front-brake', 'name' => 'Honda CB350 Front Brake Pad', 
        'slug' => 'honda-cb350-front-brake-pad', 'desc' => 'Sintered front brake pad for Honda H\'ness CB350.',
        'price' => 1250.00, 'stock' => 40, 'models' => ['honda-hness-cb350']
    ],
    [
        'category' => 'controls-cables', 'name' => 'Honda Shine 125 Clutch Cable', 
        'slug' => 'honda-shine-125-clutch-cable', 'desc' => 'OEM clutch cable for Honda Shine 125.',
        'price' => 180.00, 'stock' => 60, 'models' => ['honda-shine-125']
    ],
    [
        'category' => 'engine-spark-plugs', 'name' => 'NGK Spark Plug CPR7EA-9', 
        'slug' => 'ngk-cpr7ea-9-activa-125', 'desc' => 'OEM specified spark plug for Honda Activa 125 BS6.',
        'price' => 140.00, 'stock' => 100, 'models' => ['honda-activa-125-bs6']
    ]
];

$product_count = 0;
$compatibility_count = 0;

foreach ($products as $p) {
    $c_id = $cat_ids[$p['category']] ?? null;
    
    if ($c_id) {
        $stmt = $conn->prepare("INSERT IGNORE INTO products (category_id, brand_id, name, slug, description, price, stock, image, gallery, status, data_source, last_verified) VALUES (?, ?, ?, ?, ?, ?, ?, 'verified_placeholder.jpg', '[\"verified_placeholder.jpg\"]', 1, 'Verified DB Script - Batch 1', NOW())");
        $stmt->bind_param("iisssdi", $c_id, $brand_id, $p['name'], $p['slug'], $p['desc'], $p['price'], $p['stock']);
        $stmt->execute();
        
        $res = $conn->query("SELECT id FROM products WHERE slug = '" . $conn->real_escape_string($p['slug']) . "'");
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
    }
}
echo "<p>Products imported: $product_count</p>";
echo "<p>Compatibility records added: $compatibility_count</p>";
echo "<h2>Batch 1 Complete!</h2>";
?>
