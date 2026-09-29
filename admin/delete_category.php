<?php
// admin/delete_category.php - Category Deletion
require_once __DIR__ . '/../includes/functions.php';

require_admin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$token = $_GET['token'] ?? '';

if (!verify_csrf_token($token)) {
    header('Location: ' . base_url('admin/categories.php'));
    exit();
}

if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        set_flash('success', 'Category deleted successfully.');
    } else {
        set_flash('danger', 'Cannot delete category containing active products.');
    }
}

header('Location: ' . base_url('admin/categories.php'));
exit();
?>
