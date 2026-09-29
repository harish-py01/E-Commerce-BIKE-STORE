<?php
// checkout.php - Checkout & Order Processing
require_once __DIR__ . '/includes/functions.php';

// Customer requirement guard
require_customer();

$customer_id = get_customer_id();

// Fetch Customer Profile for pre-filling details
$cust_stmt = $conn->prepare("SELECT * FROM customers WHERE id = ? LIMIT 1");
$cust_stmt->bind_param("i", $customer_id);
$cust_stmt->execute();
$customer = $cust_stmt->get_result()->fetch_assoc();

// Fetch Cart Items
$cart_stmt = $conn->prepare("SELECT c.id as cart_id, c.quantity, p.id as product_id, p.name, p.price, p.stock, p.image 
                            FROM cart c 
                            JOIN products p ON c.product_id = p.id 
                            WHERE c.customer_id = ?");
$cart_stmt->bind_param("i", $customer_id);
$cart_stmt->execute();
$cart_items = $cart_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if (empty($cart_items)) {
    set_flash('warning', 'Your shopping cart is empty.');
    header('Location: ' . base_url('shop.php'));
    exit();
}

$subtotal = 0.00;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$shipping = ($subtotal >= 2999) ? 0.00 : 150.00;
$grand_total = $subtotal + $shipping;

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf_token)) {
        header('Location: ' . base_url('checkout.php'));
        exit();
    }

    $name = trim($_POST['shipping_name'] ?? '');
    $phone = trim($_POST['shipping_phone'] ?? '');
    $address = trim($_POST['shipping_address'] ?? '');
    $city = trim($_POST['shipping_city'] ?? '');
    $state = trim($_POST['shipping_state'] ?? '');
    $zip = trim($_POST['shipping_zip'] ?? '');
    $payment_method = trim($_POST['payment_method'] ?? 'COD');

    // Validation
    if (empty($name)) $errors[] = "Full Name is required.";
    if (empty($phone) || !preg_match('/^[0-9]{10}$/', $phone)) $errors[] = "Valid 10-digit Phone Number is required.";
    if (empty($address)) $errors[] = "Shipping Address is required.";
    if (empty($city)) $errors[] = "City is required.";
    if (empty($state)) $errors[] = "State is required.";
    if (empty($zip)) $errors[] = "Zip / Pincode is required.";

    if (empty($errors)) {
        $conn->begin_transaction();

        try {
            // Generate Order Number
            $order_number = 'ORD-' . date('Ymd') . '-' . rand(1000, 9999);
            $payment_status = ($payment_method === 'COD') ? 'Pending' : 'Unpaid';
            $order_status = 'Pending';

            // Insert into orders
            $order_sql = "INSERT INTO orders (order_number, customer_id, total_amount, shipping_name, shipping_phone, shipping_address, shipping_city, shipping_state, shipping_zip, payment_method, payment_status, order_status) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_ord = $conn->prepare($order_sql);
            $stmt_ord->bind_param("siddssssssss", $order_number, $customer_id, $grand_total, $name, $phone, $address, $city, $state, $zip, $payment_method, $payment_status, $order_status);
            $stmt_ord->execute();
            $order_id = $stmt_ord->insert_id;

            // Insert items into order_items & update product stock
            $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity, total) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_stock = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");

            foreach ($cart_items as $ci) {
                $item_tot = $ci['price'] * $ci['quantity'];
                $stmt_item->bind_param("iisdid", $order_id, $ci['product_id'], $ci['name'], $ci['price'], $ci['quantity'], $item_tot);
                $stmt_item->execute();

                // Update stock
                $stmt_stock->bind_param("ii", $ci['quantity'], $ci['product_id']);
                $stmt_stock->execute();
            }

            // Clear Cart
            $stmt_del_cart = $conn->prepare("DELETE FROM cart WHERE customer_id = ?");
            $stmt_del_cart->bind_param("i", $customer_id);
            $stmt_del_cart->execute();

            $conn->commit();

            // Send Confirmation Email
            require_once __DIR__ . '/includes/mailer.php';
            send_order_confirmation($order_id, $email, $name, $order_number, $grand_total);

            if ($payment_method === 'ONLINE') {
                header('Location: ' . base_url('payment_gateway.php?id=' . $order_id));
                exit();
            } else {
                set_flash('success', 'Order placed successfully! Pay cash upon delivery.');
                header('Location: ' . base_url('order_success.php?id=' . $order_id));
                exit();
            }

        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = "Order placement failed: " . $e->getMessage();
        }
    }
}

$page_title = "Checkout - Bike Store";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb p-3 rounded-3 shadow-sm" style="background-color: var(--dark-elevated);">
            <li class="breadcrumb-item"><a href="<?php echo base_url('index.php'); ?>" class="text-decoration-none text-muted"><i class="fas fa-home"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="<?php echo base_url('cart.php'); ?>" class="text-decoration-none text-muted">Cart</a></li>
            <li class="breadcrumb-item active text-danger fw-semibold" aria-current="page">Checkout</li>
        </ol>
    </nav>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger shadow-sm rounded-3">
            <ul class="mb-0 ps-3">
                <?php foreach ($errors as $err): ?>
                    <li><?php echo e($err); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="<?php echo base_url('checkout.php'); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <div class="row g-4">
            <!-- Shipping Details Column -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 text-light" style="background-color: var(--dark-surface);">
                    <h5 class="fw-bold text-white border-bottom border-secondary pb-3 mb-3"><i class="fas fa-truck text-danger me-2"></i> Shipping Address</h5>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Recipient Full Name *</label>
                            <input type="text" name="shipping_name" class="form-control" value="<?php echo e($_POST['shipping_name'] ?? $customer['name']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Phone Number *</label>
                            <input type="text" name="shipping_phone" class="form-control" value="<?php echo e($_POST['shipping_phone'] ?? $customer['phone']); ?>" placeholder="10 digit mobile number" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold text-secondary">Street Address *</label>
                            <textarea name="shipping_address" class="form-control" rows="3" required><?php echo e($_POST['shipping_address'] ?? $customer['address']); ?></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-secondary">City *</label>
                            <input type="text" name="shipping_city" class="form-control" value="<?php echo e($_POST['shipping_city'] ?? $customer['city']); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-secondary">State *</label>
                            <input type="text" name="shipping_state" class="form-control" value="<?php echo e($_POST['shipping_state'] ?? $customer['state']); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-secondary">Pincode / Zip *</label>
                            <input type="text" name="shipping_zip" class="form-control" value="<?php echo e($_POST['shipping_zip'] ?? $customer['zip_code']); ?>" required>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 p-4 text-light" style="background-color: var(--dark-surface);">
                    <h5 class="fw-bold text-white border-bottom border-secondary pb-3 mb-3"><i class="fas fa-credit-card text-danger me-2"></i> Select Payment Method</h5>

                    <div class="form-check mb-3 p-3 border rounded-3 shadow-xs border-primary-subtle" style="background-color: var(--dark-elevated);">
                        <input class="form-check-input ms-0 me-3" type="radio" name="payment_method" id="payONLINE" value="ONLINE" checked>
                        <label class="form-check-label fw-semibold text-dark w-100" for="payONLINE">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-shield-alt text-success me-2 fs-5"></i> <strong>Online Payment Gateway</strong>
                                    <span class="badge bg-success ms-2">Recommended</span>
                                </div>
                                <div class="d-none d-sm-block">
                                    <i class="fab fa-cc-visa text-primary fs-5 me-1"></i>
                                    <i class="fab fa-cc-mastercard text-danger fs-5 me-1"></i>
                                    <i class="fas fa-qrcode text-dark fs-5 me-1"></i>
                                    <i class="fas fa-university text-secondary fs-5"></i>
                                </div>
                            </div>
                            <small class="d-block text-muted fw-normal ms-4 mt-1">Instant Payment via Credit/Debit Cards, UPI (GPay, PhonePe, Paytm), Net Banking & Digital Wallets.</small>
                        </label>
                    </div>

                    <div class="form-check p-3 border rounded-3 border-secondary" style="background-color: var(--dark-elevated);">
                        <input class="form-check-input ms-0 me-3" type="radio" name="payment_method" id="payCOD" value="COD">
                        <label class="form-check-label fw-semibold text-white" for="payCOD">
                            <i class="fas fa-money-bill-wave text-success me-2 fs-5"></i> Cash on Delivery (COD)
                            <small class="d-block text-muted fw-normal ms-4 mt-1">Pay cash upon delivery at your doorstep.</small>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Order Items Summary Column -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 p-4 order-summary-card text-light" style="background-color: var(--dark-surface);">
                    <h5 class="fw-bold text-white border-bottom border-secondary pb-3 mb-3">Order Items</h5>

                    <div class="pe-2 mb-3" style="max-height: 280px; overflow-y: auto;">
                        <?php foreach ($cart_items as $ci): ?>
                            <div class="d-flex align-items-center mb-3 pb-2 border-bottom border-secondary">
                                <img src="<?php echo base_url('uploads/' . e($ci['image'])); ?>" alt="img" class="img-thumbnail me-3 bg-dark border-secondary" style="width: 50px; height: 50px; object-fit: contain;">
                                <div class="flex-grow-1">
                                    <h6 class="mb-0 small fw-semibold text-white"><?php echo e($ci['name']); ?></h6>
                                    <small class="text-muted">Qty: <?php echo $ci['quantity']; ?> × <?php echo format_price($ci['price']); ?></small>
                                </div>
                                <span class="fw-bold text-danger small"><?php echo format_price($ci['price'] * $ci['quantity']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal</span>
                        <span class="fw-semibold"><?php echo format_price($subtotal); ?></span>
                    </div>

                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Shipping Fee</span>
                        <?php if ($shipping == 0): ?>
                            <span class="text-success fw-bold">FREE</span>
                        <?php else: ?>
                            <span class="fw-semibold"><?php echo format_price($shipping); ?></span>
                        <?php endif; ?>
                    </div>

                    <hr class="my-3">

                    <div class="d-flex justify-content-between fs-4 mb-4">
                        <span class="fw-bold text-white">Total Pay</span>
                        <span class="fw-bold text-danger"><?php echo format_price($grand_total); ?></span>
                    </div>

                    <button type="submit" class="btn btn-danger btn-lg w-100 rounded-pill fw-bold py-3">
                        <i class="fas fa-check-circle me-2"></i> Place Order Now
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
