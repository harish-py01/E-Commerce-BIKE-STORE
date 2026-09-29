<?php
// process_online_payment.php - Backend Handler for Online Payment Gateway Transactions
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/json');

if (!is_customer_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access. Please login.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

$customer_id = get_customer_id();

// Verify CSRF token
$csrf_token = $_POST['csrf_token'] ?? '';
if (!verify_csrf_token($csrf_token)) {
    echo json_encode(['success' => false, 'message' => 'Security token expired. Please refresh the page and try again.']);
    exit();
}

$order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
$method = trim($_POST['method'] ?? 'Card'); // Card, UPI, NetBanking, Wallet
$simulate_action = trim($_POST['action'] ?? 'success'); // success or fail

if ($order_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid order specification.']);
    exit();
}

// Fetch order and customer details
$stmt = $conn->prepare("SELECT o.*, c.email as customer_email, c.name as customer_name FROM orders o JOIN customers c ON o.customer_id = c.id WHERE o.id = ? AND o.customer_id = ? LIMIT 1");
$stmt->bind_param("ii", $order_id, $customer_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    echo json_encode(['success' => false, 'message' => 'Order not found or access denied.']);
    exit();
}

if ($order['payment_status'] === 'Paid') {
    echo json_encode([
        'success' => true, 
        'already_paid' => true,
        'message' => 'Order is already marked as Paid.',
        'redirect_url' => base_url('order_success.php?id=' . $order_id)
    ]);
    exit();
}

if ($simulate_action === 'fail') {
    // Record failed attempt in order notes or status
    $upd_fail = $conn->prepare("UPDATE orders SET payment_status = 'Failed' WHERE id = ? AND customer_id = ?");
    $upd_fail->bind_param("ii", $order_id, $customer_id);
    $upd_fail->execute();

    echo json_encode([
        'success' => false,
        'message' => 'Transaction was declined by bank/issuer. Please try another card or UPI method.'
    ]);
    exit();
}

// Generate unique transaction ID
$transaction_id = 'TXN-GATEWAY-' . date('YmdHis') . '-' . strtoupper(bin2hex(random_bytes(4)));

// Prepare payment details JSON
$payment_info = [
    'gateway' => 'BikeStore Online Gateway',
    'method' => $method,
    'timestamp' => date('Y-m-d H:i:s'),
    'amount' => (float)$order['total_amount'],
    'currency' => 'INR',
    'card_last4' => isset($_POST['card_last4']) ? e($_POST['card_last4']) : null,
    'card_brand' => isset($_POST['card_brand']) ? e($_POST['card_brand']) : null,
    'upi_id' => isset($_POST['upi_id']) ? e($_POST['upi_id']) : null,
    'bank_name' => isset($_POST['bank_name']) ? e($_POST['bank_name']) : null,
    'wallet_name' => isset($_POST['wallet_name']) ? e($_POST['wallet_name']) : null,
    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
];

$payment_details_json = json_encode($payment_info);
$formatted_method_name = "Online Payment (" . $method . ")";

// Update Order in Database
$upd_stmt = $conn->prepare("UPDATE orders SET payment_status = 'Paid', payment_method = ?, transaction_id = ?, payment_details = ? WHERE id = ? AND customer_id = ?");
$upd_stmt->bind_param("sssii", $formatted_method_name, $transaction_id, $payment_details_json, $order_id, $customer_id);

if ($upd_stmt->execute()) {
    // Clear cart if items remain
    $stmt_del_cart = $conn->prepare("DELETE FROM cart WHERE customer_id = ?");
    $stmt_del_cart->bind_param("i", $customer_id);
    $stmt_del_cart->execute();

    // Send Confirmation Email
    require_once __DIR__ . '/includes/mailer.php';
    send_order_confirmation($order_id, $order['customer_email'], $order['customer_name'], $order['order_number'], $order['total_amount']);

    set_flash('success', 'Payment authorized and completed successfully!');
    echo json_encode([
        'success' => true,
        'transaction_id' => $transaction_id,
        'message' => 'Payment authorized successfully!',
        'redirect_url' => base_url('order_success.php?id=' . $order_id)
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database update failed: ' . $conn->error]);
}
