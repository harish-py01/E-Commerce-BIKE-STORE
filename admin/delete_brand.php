<?php
// admin/delete_brand.php - Brand Deletion
require_once __DIR__ . '/../includes/functions.php';

require_admin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$token = $_GET['token'] ?? '';

if (!verify_csrf_token($token)) {
    header('Location: ' . base_url('admin/brands.php'));
    exit();
}

if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM brands WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        set_flash('success', 'Brand deleted successfully.');
    } else {
        set_flash('danger', 'Cannot delete brand containing active products.');
    }
}

header('Location: ' . base_url('admin/brands.php'));
exit();
?>
