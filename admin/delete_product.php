<?php
// admin/delete_product.php - Product Deletion
require_once __DIR__ . '/../includes/functions.php';

require_admin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$token = $_GET['token'] ?? '';

if (!verify_csrf_token($token)) {
    header('Location: ' . base_url('admin/products.php'));
    exit();
}

if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        set_flash('success', 'Product deleted from inventory.');
    } else {
        set_flash('danger', 'Failed to delete product.');
    }
}

header('Location: ' . base_url('admin/products.php'));
exit();
?>
