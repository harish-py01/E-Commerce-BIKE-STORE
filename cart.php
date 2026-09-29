<?php
// cart.php - Shopping Cart Page
$page_title = "Shopping Cart - Bike Store";
require_once __DIR__ . '/includes/header.php';

// Fetch Cart Items
$cart_items = [];
if (is_customer_logged_in()) {
    $customer_id = $_SESSION['customer_id'];
    $stmt = $conn->prepare("SELECT c.id as cart_id, c.quantity, p.id as product_id, p.name, p.slug, p.price, p.stock, p.image, b.name as brand_name 
                            FROM cart c 
                            JOIN products p ON c.product_id = p.id 
                            JOIN brands b ON p.brand_id = b.id 
                            WHERE c.customer_id = ? ORDER BY c.id DESC");
    $stmt->bind_param("i", $customer_id);
} else {
    $session_id = get_session_id();
    $stmt = $conn->prepare("SELECT c.id as cart_id, c.quantity, p.id as product_id, p.name, p.slug, p.price, p.stock, p.image, b.name as brand_name 
                            FROM cart c 
                            JOIN products p ON c.product_id = p.id 
                            JOIN brands b ON p.brand_id = b.id 
                            WHERE c.session_id = ? AND c.customer_id IS NULL ORDER BY c.id DESC");
    $stmt->bind_param("s", $session_id);
}

$stmt->execute();
$cart_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$subtotal = 0.00;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$shipping = ($subtotal >= 2999 || $subtotal == 0) ? 0.00 : 150.00;
$grand_total = $subtotal + $shipping;
?>

<div class="container my-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb p-3 rounded-3 shadow-sm" style="background-color: var(--dark-elevated);">
            <li class="breadcrumb-item"><a href="<?php echo base_url('index.php'); ?>" class="text-decoration-none text-muted"><i class="fas fa-home"></i> Home</a></li>
            <li class="breadcrumb-item active text-danger fw-semibold" aria-current="page">Shopping Cart</li>
        </ol>
    </nav>

    <?php if (empty($cart_items)): ?>
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center my-4 text-light" style="background-color: var(--dark-surface);">
            <div class="mb-3">
                <i class="fas fa-shopping-basket text-muted fa-5x"></i>
            </div>
            <h3 class="fw-bold text-white mb-2">Your Shopping Cart is Empty</h3>
            <p class="text-muted mb-4">Looks like you haven't added any spare parts or accessories to your cart yet.</p>
            <div>
                <a href="<?php echo base_url('shop.php'); ?>" class="btn btn-danger btn-lg rounded-pill px-5">
                    <i class="fas fa-store me-2"></i> Start Shopping Now
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <!-- Cart Items Table Column -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 text-light" style="background-color: var(--dark-surface);">
                    <div class="card-header p-3 border-bottom border-secondary d-flex justify-content-between align-items-center" style="background-color: var(--dark-elevated);">
                        <h5 class="fw-bold text-white mb-0"><i class="fas fa-shopping-cart text-danger me-2"></i> Cart Items (<?php echo count($cart_items); ?>)</h5>
                        <a href="<?php echo base_url('shop.php'); ?>" class="btn btn-sm btn-outline-secondary rounded-pill">
                            <i class="fas fa-arrow-left me-1"></i> Continue Shopping
                        </a>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-dark align-middle mb-0">
                            <thead>
                                <tr>
                                    <th scope="col" class="ps-4">Product</th>
                                    <th scope="col">Price</th>
                                    <th scope="col" style="width: 140px;">Quantity</th>
                                    <th scope="col">Total</th>
                                    <th scope="col" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cart_items as $item): ?>
                                    <?php $item_total = $item['price'] * $item['quantity']; ?>
                                    <tr>
                                        <td class="ps-4 py-3">
                                            <div class="d-flex align-items-center">
                                                <img src="<?php echo base_url('uploads/' . e($item['image'])); ?>" alt="<?php echo e($item['name']); ?>" class="img-thumbnail me-3" style="width: 65px; height: 65px; object-fit: contain;">
                                                <div>
                                                    <span class="text-muted small text-uppercase d-block"><?php echo e($item['brand_name']); ?></span>
                                                    <a href="<?php echo base_url('product.php?slug=' . $item['slug']); ?>" class="fw-semibold text-white text-decoration-none small">
                                                        <?php echo e($item['name']); ?>
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                        <td><span class="fw-semibold"><?php echo format_price($item['price']); ?></span></td>
                                        <td>
                                            <form action="<?php echo base_url('update_cart.php'); ?>" method="POST" class="auto-submit-qty">
                                                <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                                <?php echo csrf_field(); ?>
                                                <div class="input-group input-group-sm quantity-control">
                                                    <button type="button" class="btn btn-outline-secondary qty-btn-minus">-</button>
                                                    <input type="number" name="quantity" class="form-control text-center qty-input" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock']; ?>" readonly>
                                                    <button type="button" class="btn btn-outline-secondary qty-btn-plus">+</button>
                                                </div>
                                            </form>
                                        </td>
                                        <td><span class="fw-bold text-danger"><?php echo format_price($item_total); ?></span></td>
                                        <td class="text-center">
                                            <a href="<?php echo base_url('remove_from_cart.php?id=' . $item['cart_id'] . '&token=' . generate_csrf_token()); ?>" 
                                               class="btn btn-sm btn-outline-danger border-0 rounded-circle btn-confirm-delete" 
                                               data-message="Remove <?php echo e($item['name']); ?> from cart?">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Order Summary Column -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 order-summary-card text-light" style="background-color: var(--dark-surface);">
                    <h5 class="fw-bold text-white border-bottom border-secondary pb-3 mb-3">Order Summary</h5>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal</span>
                        <span class="fw-semibold"><?php echo format_price($subtotal); ?></span>
                    </div>

                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Shipping</span>
                        <?php if ($shipping == 0): ?>
                            <span class="text-success fw-bold">FREE</span>
                        <?php else: ?>
                            <span class="fw-semibold"><?php echo format_price($shipping); ?></span>
                        <?php endif; ?>
                    </div>

                    <?php if ($shipping > 0): ?>
                        <div class="alert alert-warning p-2 small mb-3">
                            <i class="fas fa-truck me-1"></i> Add <strong><?php echo format_price(2999 - $subtotal); ?></strong> more items for FREE shipping!
                        </div>
                    <?php endif; ?>

                    <hr class="my-3">

                    <div class="d-flex justify-content-between fs-5 mb-4">
                        <span class="fw-bold text-white">Grand Total</span>
                        <span class="fw-bold text-danger"><?php echo format_price($grand_total); ?></span>
                    </div>

                    <div class="d-grid">
                        <a href="<?php echo base_url('checkout.php'); ?>" class="btn btn-danger btn-lg rounded-pill fw-bold py-3">
                            Proceed To Checkout <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    </div>

                    <div class="text-center mt-3 small text-muted">
                        <i class="fas fa-lock me-1"></i> 256-bit Encrypted Checkout
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
