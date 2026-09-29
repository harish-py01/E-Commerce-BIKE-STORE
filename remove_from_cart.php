<?php
// remove_from_cart.php - Delete single cart item
require_once __DIR__ . '/includes/functions.php';

$cart_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$token = $_GET['token'] ?? '';

if (!verify_csrf_token($token)) {
    header('Location: ' . base_url('cart.php'));
    exit();
}

if ($cart_id > 0) {
    if (is_customer_logged_in()) {
        $customer_id = $_SESSION['customer_id'];
        $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND customer_id = ?");
        $stmt->bind_param("ii", $cart_id, $customer_id);
    } else {
        $session_id = get_session_id();
        $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND session_id = ? AND customer_id IS NULL");
        $stmt->bind_param("is", $cart_id, $session_id);
    }
    
    if ($stmt->execute()) {
        set_flash('success', 'Item removed from cart.');
    }
}

header('Location: ' . base_url('cart.php'));
exit();
?>
