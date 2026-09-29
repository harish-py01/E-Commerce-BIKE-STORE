<?php
// includes/functions.php
// Common Helper Functions, Security, Sessions & Cart Utilities

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';

// Base URL helper
function base_url($path = '') {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $script_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    
    // Trim trailing admin if inside admin subdirectory
    $base = preg_replace('/\/admin$/', '', $script_dir);
    $base = rtrim($base, '/');
    
    return $protocol . "://" . $host . $base . '/' . ltrim($path, '/');
}

// XSS Sanitization function
function e($data) {
    return htmlspecialchars(trim($data ?? ''), ENT_QUOTES, 'UTF-8');
}

// Generate CSRF Token
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// CSRF Field Input HTML
function csrf_field() {
    $token = generate_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

// Verify CSRF Token
function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        set_flash('danger', 'Invalid session or CSRF token match failed. Please try again.');
        return false;
    }
    return true;
}

// Flash Messages
function set_flash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type, // success, danger, warning, info
        'message' => $message
    ];
}

function display_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        
        $type = isset($flash['type']) ? $flash['type'] : 'info';
        $message = isset($flash['message']) ? $flash['message'] : (is_string($flash) ? $flash : '');
        
        if ($message !== '') {
            echo '<div class="alert alert-' . e($type) . ' alert-dismissible fade show shadow-sm border-0 my-3" role="alert">
                    <i class="fas ' . ($type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle') . ' me-2"></i>' . e($message) . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>';
        }
    }
}

// Customer Auth Helpers
function is_customer_logged_in() {
    return isset($_SESSION['customer_id']) && !empty($_SESSION['customer_id']);
}

function require_customer() {
    if (!is_customer_logged_in()) {
        set_flash('warning', 'Please login to access your account.');
        header('Location: ' . base_url('login.php'));
        exit();
    }
}

function get_customer_id() {
    return $_SESSION['customer_id'] ?? null;
}

// Admin Auth Helpers
function is_admin_logged_in() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function require_admin() {
    if (!is_admin_logged_in()) {
        set_flash('danger', 'Access denied. Please login to access the Admin Panel.');
        header('Location: ' . base_url('admin/login.php'));
        exit();
    }
}

// Currency Symbol Configuration
if (!defined('CURRENCY_SYMBOL')) define('CURRENCY_SYMBOL', '₹'); // Set to '$' or '₹'

// Price Formatter
function format_price($amount) {
    return CURRENCY_SYMBOL . number_format((float)$amount, 2);
}

// Slugify helper
function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'n-a' : $text;
}

// Image Upload Helper
function upload_image($file, $target_dir = null) {
    if (!$target_dir) {
        $target_dir = __DIR__ . '/../uploads/';
    }
    
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    if (!isset($file['error']) || is_array($file['error'])) {
        return ['status' => false, 'message' => 'Invalid parameters.'];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['status' => false, 'message' => 'File upload failed with error code ' . $file['error']];
    }

    if ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
        return ['status' => false, 'message' => 'Exceeded file size limit (Max 5MB).'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($ext, $allowed)) {
        return ['status' => false, 'message' => 'Invalid file extension. Only JPG, PNG, WEBP allowed.'];
    }

    $filename = uniqid('part_', true) . '.' . $ext;
    $destination = $target_dir . $filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return ['status' => true, 'file_name' => $filename];
    }

    return ['status' => false, 'message' => 'Failed to move uploaded file.'];
}

// 3D Model Upload Helper
function upload_3d_model($file, $target_dir = null) {
    if (!$target_dir) {
        $target_dir = __DIR__ . '/../uploads/3d/';
    }
    
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    if (!isset($file['error']) || is_array($file['error'])) {
        return ['status' => false, 'message' => 'Invalid parameters.'];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['status' => false, 'message' => 'File upload failed with error code ' . $file['error']];
    }

    if ($file['size'] > 50 * 1024 * 1024) { // 50MB limit
        return ['status' => false, 'message' => 'Exceeded file size limit (Max 50MB).'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['glb', 'gltf'];

    if (!in_array($ext, $allowed)) {
        return ['status' => false, 'message' => 'Invalid file extension. Only GLB and GLTF allowed.'];
    }

    $filename = uniqid('model_', true) . '.' . $ext;
    $destination = $target_dir . $filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return ['status' => true, 'file_name' => $filename];
    }

    return ['status' => false, 'message' => 'Failed to move uploaded 3D model.'];
}

// Session cart key / DB sync
function get_session_id() {
    return session_id();
}

// Get Cart Item Count
function get_cart_count() {
    global $conn;
    $count = 0;
    
    if (is_customer_logged_in()) {
        $customer_id = $_SESSION['customer_id'];
        $stmt = $conn->prepare("SELECT SUM(quantity) as total FROM cart WHERE customer_id = ?");
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $count = $res['total'] ?? 0;
    } else {
        $session_id = get_session_id();
        $stmt = $conn->prepare("SELECT SUM(quantity) as total FROM cart WHERE session_id = ? AND customer_id IS NULL");
        $stmt->bind_param("s", $session_id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $count = $res['total'] ?? 0;
    }
    
    return (int)$count;
}

// Get Cart Subtotal
function get_cart_subtotal() {
    global $conn;
    $total = 0.00;
    
    if (is_customer_logged_in()) {
        $customer_id = $_SESSION['customer_id'];
        $stmt = $conn->prepare("SELECT c.quantity, p.price FROM cart c JOIN products p ON c.product_id = p.id WHERE c.customer_id = ?");
        $stmt->bind_param("i", $customer_id);
    } else {
        $session_id = get_session_id();
        $stmt = $conn->prepare("SELECT c.quantity, p.price FROM cart c JOIN products p ON c.product_id = p.id WHERE c.session_id = ? AND c.customer_id IS NULL");
        $stmt->bind_param("s", $session_id);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $total += $row['quantity'] * $row['price'];
    }
    
    return $total;
}

// Merge session cart into user database cart upon login
function sync_cart_after_login($customer_id) {
    global $conn;
    $session_id = get_session_id();
    
    $stmt = $conn->prepare("SELECT product_id, quantity FROM cart WHERE session_id = ? AND customer_id IS NULL");
    $stmt->bind_param("s", $session_id);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    foreach ($items as $item) {
        $p_id = $item['product_id'];
        $qty = $item['quantity'];

        // Check if customer already has item
        $check = $conn->prepare("SELECT id, quantity FROM cart WHERE customer_id = ? AND product_id = ?");
        $check->bind_param("ii", $customer_id, $p_id);
        $check->execute();
        $existing = $check->get_result()->fetch_assoc();

        if ($existing) {
            $new_qty = $existing['quantity'] + $qty;
            $update = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            $update->bind_param("ii", $new_qty, $existing['id']);
            $update->execute();
        } else {
            $insert = $conn->prepare("INSERT INTO cart (customer_id, session_id, product_id, quantity) VALUES (?, ?, ?, ?)");
            $insert->bind_param("isii", $customer_id, $session_id, $p_id, $qty);
            $insert->execute();
        }
    }

    // Delete temporary session items
    $del = $conn->prepare("DELETE FROM cart WHERE session_id = ? AND customer_id IS NULL");
    $del->bind_param("s", $session_id);
    $del->execute();
}

// Auto Migration Check for Online Payment Columns
function ensure_database_schema() {
    global $conn;
    if (!$conn) return;
    
    // Check transaction_id column in orders table
    $chk_txn = $conn->query("SHOW COLUMNS FROM orders LIKE 'transaction_id'");
    if ($chk_txn && $chk_txn->num_rows === 0) {
        $conn->query("ALTER TABLE orders ADD COLUMN transaction_id VARCHAR(100) DEFAULT NULL AFTER payment_status");
    }
    
    // Check payment_details column in orders table
    $chk_dtl = $conn->query("SHOW COLUMNS FROM orders LIKE 'payment_details'");
    if ($chk_dtl && $chk_dtl->num_rows === 0) {
        $conn->query("ALTER TABLE orders ADD COLUMN payment_details TEXT DEFAULT NULL AFTER transaction_id");
    }

    // Check product_3d_model column in products table
    $chk_3d = $conn->query("SHOW COLUMNS FROM products LIKE 'product_3d_model'");
    if ($chk_3d && $chk_3d->num_rows === 0) {
        $conn->query("ALTER TABLE products ADD COLUMN product_3d_model VARCHAR(255) DEFAULT NULL AFTER gallery");
    }
}
ensure_database_schema();
?>
