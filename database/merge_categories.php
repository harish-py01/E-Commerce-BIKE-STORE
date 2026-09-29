<?php
require_once __DIR__ . '/../includes/db.php';

echo "<h2>Starting Category Merge Process</h2>";

// 1. Get Category IDs
$categories = [];
$res = $conn->query("SELECT id, slug FROM categories");
while ($row = $res->fetch_assoc()) {
    $categories[$row['slug']] = $row['id'];
}

$cables_keep_id = $categories['controls-cables'] ?? null;
$cables_remove_id = $categories['transmission-cables'] ?? null;

$brakes_keep_id = $categories['brakes'] ?? null;
$brakes_front_id = $categories['brakes-front-brake'] ?? null;
$brakes_rear_id = $categories['brakes-rear-brake'] ?? null;

// 2. Merge Cables
if ($cables_keep_id && $cables_remove_id) {
    $stmt = $conn->prepare("UPDATE products SET category_id = ? WHERE category_id = ?");
    $stmt->bind_param("ii", $cables_keep_id, $cables_remove_id);
    $stmt->execute();
    echo "Merged " . $stmt->affected_rows . " products from Transmission Cables to Controls Cables.<br>";
    
    // Delete duplicate category
    $conn->query("DELETE FROM categories WHERE id = $cables_remove_id");
    echo "Deleted duplicate 'transmission-cables' category.<br>";
}

// 3. Merge Brakes
if ($brakes_keep_id) {
    if ($brakes_front_id) {
        $stmt = $conn->prepare("UPDATE products SET category_id = ? WHERE category_id = ?");
        $stmt->bind_param("ii", $brakes_keep_id, $brakes_front_id);
        $stmt->execute();
        echo "Merged " . $stmt->affected_rows . " products from Front Brake to Brakes.<br>";
        
        $conn->query("DELETE FROM categories WHERE id = $brakes_front_id");
        echo "Deleted redundant 'brakes-front-brake' category.<br>";
    }
    
    if ($brakes_rear_id) {
        $stmt = $conn->prepare("UPDATE products SET category_id = ? WHERE category_id = ?");
        $stmt->bind_param("ii", $brakes_keep_id, $brakes_rear_id);
        $stmt->execute();
        echo "Merged " . $stmt->affected_rows . " products from Rear Brake to Brakes.<br>";
        
        $conn->query("DELETE FROM categories WHERE id = $brakes_rear_id");
        echo "Deleted redundant 'brakes-rear-brake' category.<br>";
    }
}

echo "<h2>Merge Process Complete</h2>";
?>
