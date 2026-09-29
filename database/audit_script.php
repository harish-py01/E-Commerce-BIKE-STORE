<?php
require_once __DIR__ . '/../includes/db.php';

$audit = [];

// 1. Overall Database Audit
$res = $conn->query("SELECT COUNT(*) as total FROM products");
$audit['total_products'] = $res->fetch_assoc()['total'];

$res = $conn->query("SELECT COUNT(*) as total FROM categories");
$audit['total_categories'] = $res->fetch_assoc()['total'];

$res = $conn->query("SELECT COUNT(*) as total FROM brands");
$audit['total_brands'] = $res->fetch_assoc()['total'];

$res = $conn->query("SELECT COUNT(*) as total FROM models");
$audit['total_models'] = $res->fetch_assoc()['total'];

$res = $conn->query("SELECT COUNT(*) as total FROM product_compatibility");
$audit['total_compatibilities'] = $res->fetch_assoc()['total'];


// 2. Duplicate Report
// Check for products with the same slug (should be impossible due to UNIQUE constraint, but we check name/brand)
$duplicates = [];
$res = $conn->query("
    SELECT name, brand_id, COUNT(*) as c 
    FROM products 
    GROUP BY name, brand_id 
    HAVING c > 1
");
while ($row = $res->fetch_assoc()) {
    $duplicates[] = $row;
}
$audit['duplicates'] = $duplicates;


// 3. Category Report (Products per category)
$categories = [];
$res = $conn->query("
    SELECT c.name, c.slug, COUNT(p.id) as product_count 
    FROM categories c 
    LEFT JOIN products p ON c.id = p.category_id 
    GROUP BY c.id
    ORDER BY product_count DESC
");
while ($row = $res->fetch_assoc()) {
    $categories[] = $row;
}
$audit['categories'] = $categories;


// 4. Brand Report (Products per brand)
$brands = [];
$res = $conn->query("
    SELECT b.name, b.slug, COUNT(p.id) as product_count 
    FROM brands b 
    LEFT JOIN products p ON b.id = p.brand_id 
    GROUP BY b.id
    ORDER BY product_count DESC
");
while ($row = $res->fetch_assoc()) {
    $brands[] = $row;
}
$audit['brands'] = $brands;


// 5. Cleanup suggestions (Empty categories, brands with 0 products, orphaned models)
$cleanup = [
    'empty_categories' => [],
    'empty_brands' => [],
    'orphaned_models' => []
];

foreach ($categories as $cat) {
    if ($cat['product_count'] == 0) {
        $cleanup['empty_categories'][] = $cat['name'];
    }
}

foreach ($brands as $brand) {
    if ($brand['product_count'] == 0) {
        $cleanup['empty_brands'][] = $brand['name'];
    }
}

$res = $conn->query("
    SELECT m.name 
    FROM models m 
    LEFT JOIN product_compatibility pc ON m.id = pc.model_id 
    WHERE pc.product_id IS NULL
");
while ($row = $res->fetch_assoc()) {
    $cleanup['orphaned_models'][] = $row['name'];
}
$audit['cleanup'] = $cleanup;


echo json_encode($audit, JSON_PRETTY_PRINT);
?>
