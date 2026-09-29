<?php
// add_to_cart.php - Handle Add To Cart Action
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    $buy_now = isset($_POST['buy_now']) ? (int)$_POST['buy_now'] : 0;
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!verify_csrf_token($csrf_token)) {
        header('Location: ' . base_url('cart.php'));
        exit();
    }

    if ($product_id <= 0 || $quantity <= 0) {
        set_flash('danger', 'Invalid product or quantity.');
        header('Location: ' . base_url('shop.php'));
        exit();
    }

    // Verify product stock
    $stmt = $conn->prepare("SELECT id, name, price, stock FROM products WHERE id = ? AND status = 1 LIMIT 1");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();

    if (!$product) {
        set_flash('danger', 'Product not found or unavailable.');
        header('Location: ' . base_url('shop.php'));
        exit();
    }

    if ($product['stock'] < $quantity) {
        set_flash('warning', 'Requested quantity exceeds available stock (' . $product['stock'] . ' available).');
        header('Location: ' . $_SERVER['HTTP_REFERER'] ?? base_url('cart.php'));
        exit();
    }

    $customer_id = get_customer_id();
    $session_id = get_session_id();

    if ($customer_id) {
        // Check existing cart item for logged-in user
        $check = $conn->prepare("SELECT id, quantity FROM cart WHERE customer_id = ? AND product_id = ?");
        $check->bind_param("ii", $customer_id, $product_id);
        $check->execute();
        $existing = $check->get_result()->fetch_assoc();

        if ($existing) {
            $new_qty = $existing['quantity'] + $quantity;
            if ($new_qty > $product['stock']) $new_qty = $product['stock'];

            $update = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            $update->bind_param("ii", $new_qty, $existing['id']);
            $update->execute();
        } else {
            $insert = $conn->prepare("INSERT INTO cart (customer_id, session_id, product_id, quantity) VALUES (?, ?, ?, ?)");
            $insert->bind_param("isii", $customer_id, $session_id, $product_id, $quantity);
            $insert->execute();
        }
    } else {
        // Guest user cart
        $check = $conn->prepare("SELECT id, quantity FROM cart WHERE session_id = ? AND product_id = ? AND customer_id IS NULL");
        $check->bind_param("si", $session_id, $product_id);
        $check->execute();
        $existing = $check->get_result()->fetch_assoc();

        if ($existing) {
            $new_qty = $existing['quantity'] + $quantity;
            if ($new_qty > $product['stock']) $new_qty = $product['stock'];

            $update = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            $update->bind_param("ii", $new_qty, $existing['id']);
            $update->execute();
        } else {
            $insert = $conn->prepare("INSERT INTO cart (session_id, product_id, quantity) VALUES (?, ?, ?)");
            $insert->bind_param("sii", $session_id, $product_id, $quantity);
            $insert->execute();
        }
    }

    set_flash('success', 'Added "' . e($product['name']) . '" to your shopping cart!');
    if ($buy_now === 1) {
        header('Location: ' . base_url('checkout.php'));
    } else {
        header('Location: ' . base_url('cart.php'));
    }
    exit();
} else {
    header('Location: ' . base_url('shop.php'));
    exit();
}
?>
