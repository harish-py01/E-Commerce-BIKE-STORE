<?php
require_once __DIR__ . '/../includes/db.php';

$slugs = [
    'electrical-batteries', 'brakes', 'controls-cables', 'chains-sprockets', 
    'transmission-clutch-plates', 'controls', 'drivetrain-drive-belts', 
    'drivetrain', 'electrical', 'engine', 'engine-filters', 
    'suspension-fork-seals', 'oils-lubricants', 'rider-accessories', 
    'engine-spark-plugs', 'suspension', 'tires-wheels', 'transmission'
];

$results = [];

foreach ($slugs as $slug) {
    $stmt = $conn->prepare("SELECT COUNT(*) as c FROM products p JOIN categories c ON p.category_id = c.id WHERE c.slug = ?");
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    $res = $stmt->get_result();
    $count = $res->fetch_assoc()['c'];
    $results[$slug] = $count;
}

echo json_encode($results, JSON_PRETTY_PRINT);
?>
