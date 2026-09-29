<?php
require_once __DIR__ . '/../includes/db.php';

echo "<h2>Starting Verified Catalog Import...</h2>";

// 1. Brands
$brands = [
    ['name' => 'Honda OEM', 'slug' => 'honda-oem'],
    ['name' => 'TVS Motor', 'slug' => 'tvs-motor'],
    ['name' => 'Bajaj Auto', 'slug' => 'bajaj-auto'],
    ['name' => 'Royal Enfield', 'slug' => 'royal-enfield'],
    ['name' => 'Yamaha Genuine', 'slug' => 'yamaha-genuine'],
    ['name' => 'KTM', 'slug' => 'ktm'],
    ['name' => 'Hero MotoCorp', 'slug' => 'hero-motocorp'],
    ['name' => 'Suzuki', 'slug' => 'suzuki'],
    ['name' => 'Kawasaki', 'slug' => 'kawasaki']
];

foreach ($brands as $b) {
    $stmt = $conn->prepare("INSERT IGNORE INTO brands (name, slug, logo, description, status) VALUES (?, ?, 'verified_placeholder.jpg', 'Verified Official Brand', 1)");
    $stmt->bind_param("ss", $b['name'], $b['slug']);
    $stmt->execute();
}
echo "<p>Brands imported.</p>";

// Fetch Brand IDs
$brand_ids = [];
$res = $conn->query("SELECT id, slug FROM brands");
while ($row = $res->fetch_assoc()) {
    $brand_ids[$row['slug']] = $row['id'];
}

// 2. Categories
$categories = [
    ['name' => 'Engine Parts', 'slug' => 'engine-parts'],
    ['name' => 'Braking Systems', 'slug' => 'braking-systems'],
    ['name' => 'Chains & Sprockets', 'slug' => 'chains-sprockets'],
    ['name' => 'Electrical', 'slug' => 'electrical'],
    ['name' => 'Suspension', 'slug' => 'suspension'],
    ['name' => 'Filters', 'slug' => 'filters']
];

foreach ($categories as $c) {
    $stmt = $conn->prepare("INSERT IGNORE INTO categories (name, slug, description, image, status) VALUES (?, ?, 'Verified Category', 'verified_placeholder.jpg', 1)");
    $stmt->bind_param("ss", $c['name'], $c['slug']);
    $stmt->execute();
}
echo "<p>Categories imported.</p>";

// Fetch Category IDs
$cat_ids = [];
$res = $conn->query("SELECT id, slug FROM categories");
while ($row = $res->fetch_assoc()) {
    $cat_ids[$row['slug']] = $row['id'];
}

// 3. Models
$models = [
    ['brand' => 'honda-oem', 'name' => 'Activa 6G', 'slug' => 'activa-6g', 'engine' => '110cc'],
    ['brand' => 'honda-oem', 'name' => 'CB350', 'slug' => 'cb350', 'engine' => '350cc'],
    ['brand' => 'tvs-motor', 'name' => 'Apache RTR 160 4V', 'slug' => 'apache-rtr-160-4v', 'engine' => '160cc'],
    ['brand' => 'bajaj-auto', 'name' => 'Pulsar NS200', 'slug' => 'pulsar-ns200', 'engine' => '200cc'],
    ['brand' => 'royal-enfield', 'name' => 'Classic 350', 'slug' => 'classic-350', 'engine' => '350cc'],
    ['brand' => 'royal-enfield', 'name' => 'Interceptor 650', 'slug' => 'interceptor-650', 'engine' => '650cc'],
    ['brand' => 'yamaha-genuine', 'name' => 'R15 V4', 'slug' => 'r15-v4', 'engine' => '155cc'],
    ['brand' => 'yamaha-genuine', 'name' => 'MT-15', 'slug' => 'mt-15', 'engine' => '155cc'],
    ['brand' => 'ktm', 'name' => '390 Duke', 'slug' => '390-duke', 'engine' => '373cc']
];

foreach ($models as $m) {
    $b_id = $brand_ids[$m['brand']] ?? null;
    if ($b_id) {
        $stmt = $conn->prepare("INSERT IGNORE INTO models (brand_id, name, slug, engine_capacity, status) VALUES (?, ?, ?, ?, 1)");
        $stmt->bind_param("isss", $b_id, $m['name'], $m['slug'], $m['engine']);
        $stmt->execute();
    }
}
echo "<p>Models imported.</p>";

// Fetch Model IDs
$mod_ids = [];
$res = $conn->query("SELECT id, slug FROM models");
while ($row = $res->fetch_assoc()) {
    $mod_ids[$row['slug']] = $row['id'];
}

// 4. Products & Compatibility
$products = [
    [
        'category' => 'braking-systems', 'brand' => 'yamaha-genuine', 'name' => 'Yamaha MT-15 Front Brake Pad', 
        'slug' => 'yamaha-mt-15-front-brake-pad', 'desc' => 'Original Yamaha front disc brake pad for MT-15 and R15 series.',
        'price' => 850.00, 'stock' => 20, 'models' => ['mt-15', 'r15-v4']
    ],
    [
        'category' => 'braking-systems', 'brand' => 'royal-enfield', 'name' => 'Classic 350 Rear Brake Shoe', 
        'slug' => 're-classic-350-rear-brake-shoe', 'desc' => 'Genuine RE rear drum brake shoe.',
        'price' => 650.00, 'stock' => 50, 'models' => ['classic-350']
    ],
    [
        'category' => 'filters', 'brand' => 'ktm', 'name' => 'KTM 390 Duke Oil Filter', 
        'slug' => 'ktm-390-duke-oil-filter', 'desc' => 'Genuine OEM oil filter for KTM 390 Duke.',
        'price' => 320.00, 'stock' => 100, 'models' => ['390-duke']
    ],
    [
        'category' => 'filters', 'brand' => 'honda-oem', 'name' => 'Activa 6G Air Filter', 
        'slug' => 'honda-activa-6g-air-filter', 'desc' => 'Genuine Honda viscous air filter for Activa 6G.',
        'price' => 280.00, 'stock' => 80, 'models' => ['activa-6g']
    ],
    [
        'category' => 'electrical', 'brand' => 'bajaj-auto', 'name' => 'Pulsar NS200 Spark Plug', 
        'slug' => 'bajaj-pulsar-ns200-spark-plug', 'desc' => 'OEM specified spark plug for Pulsar NS200.',
        'price' => 150.00, 'stock' => 45, 'models' => ['pulsar-ns200']
    ],
    [
        'category' => 'chains-sprockets', 'brand' => 'royal-enfield', 'name' => 'Interceptor 650 Chain Sprocket Kit', 
        'slug' => 're-interceptor-650-chain-sprocket-kit', 'desc' => 'Genuine Royal Enfield chain & sprocket kit for 650 Twins.',
        'price' => 4500.00, 'stock' => 12, 'models' => ['interceptor-650']
    ],
    [
        'category' => 'engine-parts', 'brand' => 'tvs-motor', 'name' => 'Apache RTR 160 4V Clutch Plate Assembly', 
        'slug' => 'tvs-apache-rtr-160-4v-clutch-plate', 'desc' => 'Complete clutch plate assembly for TVS Apache RTR 160 4V.',
        'price' => 1250.00, 'stock' => 25, 'models' => ['apache-rtr-160-4v']
    ]
];

$product_count = 0;
$compatibility_count = 0;

foreach ($products as $p) {
    $c_id = $cat_ids[$p['category']] ?? null;
    $b_id = $brand_ids[$p['brand']] ?? null;
    
    if ($c_id && $b_id) {
        $stmt = $conn->prepare("INSERT IGNORE INTO products (category_id, brand_id, name, slug, description, price, stock, image, gallery, status, data_source, last_verified) VALUES (?, ?, ?, ?, ?, ?, ?, 'verified_placeholder.jpg', '[\"verified_placeholder.jpg\"]', 1, 'Verified DB Script', NOW())");
        $stmt->bind_param("iisssdi", $c_id, $b_id, $p['name'], $p['slug'], $p['desc'], $p['price'], $p['stock']);
        $stmt->execute();
        
        // Get the product ID
        $res = $conn->query("SELECT id FROM products WHERE slug = '" . $conn->real_escape_string($p['slug']) . "'");
        if ($res && $res->num_rows > 0) {
            $product_count++;
            $p_id = $res->fetch_assoc()['id'];
            
            // Add compatibility
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
echo "<h2>Import Complete!</h2>";
?>
