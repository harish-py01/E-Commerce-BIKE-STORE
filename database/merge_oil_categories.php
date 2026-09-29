<?php
require_once __DIR__ . '/../includes/db.php';

echo "<h2>Starting Oil Category Merge Process</h2>";

// 1. Get Category IDs
$categories = [];
$res = $conn->query("SELECT id, slug FROM categories");
while ($row = $res->fetch_assoc()) {
    $categories[$row['slug']] = $row['id'];
}

$oil_keep_id = $categories['oils-lubricants'] ?? null;
$oil_remove_id = $categories['engine-oil'] ?? null;

// 2. Merge Oils
if ($oil_keep_id && $oil_remove_id) {
    $stmt = $conn->prepare("UPDATE products SET category_id = ? WHERE category_id = ?");
    $stmt->bind_param("ii", $oil_keep_id, $oil_remove_id);
    $stmt->execute();
    echo "Merged " . $stmt->affected_rows . " products from 'engine-oil' to 'oils-lubricants'.<br>";
    
    // Delete duplicate category
    $conn->query("DELETE FROM categories WHERE id = $oil_remove_id");
    echo "Deleted duplicate 'engine-oil' category.<br>";
} else {
    echo "Error: One or both categories not found. It may have already been merged.<br>";
}

echo "<h2>Merge Process Complete</h2>";
?>
