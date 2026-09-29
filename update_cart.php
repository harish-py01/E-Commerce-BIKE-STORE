<?php
// update_cart.php - Handle Cart Quantity Update
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cart_id = isset($_POST['cart_id']) ? (int)$_POST['cart_id'] : 0;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!verify_csrf_token($csrf_token)) {
        header('Location: ' . base_url('cart.php'));
        exit();
    }

    if ($cart_id <= 0 || $quantity <= 0) {
        set_flash('danger', 'Invalid cart request.');
        header('Location: ' . base_url('cart.php'));
        exit();
    }

    // Verify ownership & stock limit
    $stmt = $conn->prepare("SELECT c.id, p.stock, p.name FROM cart c JOIN products p ON c.product_id = p.id WHERE c.id = ?");
    $stmt->bind_param("i", $cart_id);
    $stmt->execute();
    $item = $stmt->get_result()->fetch_assoc();

    if ($item) {
        if ($quantity > $item['stock']) {
            $quantity = $item['stock'];
            set_flash('warning', 'Quantity updated to maximum available stock (' . $item['stock'] . ').');
        } else {
            set_flash('success', 'Cart updated successfully.');
        }

        $update = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $update->bind_param("ii", $quantity, $cart_id);
        $update->execute();
    }

    header('Location: ' . base_url('cart.php'));
    exit();
} else {
    header('Location: ' . base_url('cart.php'));
    exit();
}
?>
