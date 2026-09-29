<?php
require_once __DIR__ . '/includes/db.php';
header('Content-Type: application/json');

$brand_slug = isset($_GET['brand']) ? trim($_GET['brand']) : '';

if (empty($brand_slug)) {
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare("SELECT m.name, m.slug, m.variant, m.start_year, m.end_year FROM models m JOIN brands b ON m.brand_id = b.id WHERE b.slug = ? AND m.status = 1 ORDER BY m.name ASC, m.start_year DESC");
$stmt->bind_param("s", $brand_slug);
$stmt->execute();
$result = $stmt->get_result();

$models = [];
while ($row = $result->fetch_assoc()) {
    $displayName = $row['name'];
    if (!empty($row['variant'])) {
        $displayName .= ' ' . $row['variant'];
    }
    if (!empty($row['start_year'])) {
        $end = !empty($row['end_year']) ? $row['end_year'] : 'Present';
        $displayName .= ' (' . $row['start_year'] . ' - ' . $end . ')';
    }
    
    $models[] = [
        'slug' => $row['slug'],
        'display_name' => $displayName
    ];
}

echo json_encode($models);
