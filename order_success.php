<?php
// order_success.php - Order Confirmation & Invoice View
require_once __DIR__ . '/includes/functions.php';

require_customer();

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$customer_id = get_customer_id();

// Fetch Order
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND customer_id = ? LIMIT 1");
$stmt->bind_param("ii", $order_id, $customer_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header('Location: ' . base_url('my_orders.php'));
    exit();
}

// Fetch Order Items
$items_stmt = $conn->prepare("SELECT oi.*, p.image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items = $items_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$page_title = "Order Invoice #" . $order['order_number'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-5">
    <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 text-light" style="background-color: var(--dark-surface);">
        <!-- Success Banner Header -->
        <div class="text-center mb-5">
            <div class="mb-3">
                <i class="fas fa-check-circle text-success" style="font-size: 4.5rem;"></i>
            </div>
            <h2 class="fw-bold text-white mb-2">Thank You For Your Order!</h2>
            <p class="text-muted lead">Your order has been placed successfully and is being prepared for dispatch.</p>
            <span class="badge bg-danger fs-6 px-3 py-2">Order #<?php echo e($order['order_number']); ?></span>
        </div>

        <div class="row g-4 mb-4">
            <!-- Order Info -->
            <div class="col-md-6">
                <div class="p-3 rounded-3 border border-secondary" style="background-color: var(--dark-elevated);">
                    <h6 class="fw-bold text-white border-bottom border-secondary pb-2 mb-2">Order Summary</h6>
                    <p class="mb-1 text-muted small"><strong>Order Date:</strong> <?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?></p>
                    <p class="mb-1 text-muted small"><strong>Payment Method:</strong> <?php echo e($order['payment_method']); ?></p>
                    <p class="mb-1 text-muted small">
                        <strong>Payment Status:</strong> 
                        <?php if ($order['payment_status'] === 'Paid'): ?>
                            <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Paid</span>
                        <?php elseif ($order['payment_status'] === 'Failed'): ?>
                            <span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i> Payment Failed</span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i> <?php echo e($order['payment_status']); ?></span>
                        <?php endif; ?>
                    </p>
                    <?php if (!empty($order['transaction_id'])): ?>
                        <p class="mb-1 text-muted small"><strong>Transaction ID:</strong> <code class="border border-secondary px-2 py-1 rounded text-light fw-bold" style="background-color: var(--dark-color);"><?php echo e($order['transaction_id']); ?></code></p>
                    <?php endif; ?>
                    <p class="mb-3 text-muted small"><strong>Order Status:</strong></p>
                    <?php 
                        $status_list = ['Pending', 'Processing', 'Delivered'];
                        $current_idx = array_search($order['order_status'], $status_list);
                        if ($order['order_status'] === 'Cancelled') {
                            echo '<div class="alert alert-danger py-2 mb-0"><i class="fas fa-times-circle me-2"></i> Order Cancelled</div>';
                        } else {
                            echo '<div class="d-flex justify-content-between position-relative align-items-center mt-2 mb-2">';
                            echo '<div class="position-absolute w-100" style="height: 4px; background: var(--dark-color); z-index: 0; top: 12px;"></div>';
                            foreach ($status_list as $idx => $st) {
                                $is_active = ($idx <= $current_idx);
                                $color = $is_active ? 'var(--danger-color)' : 'var(--dark-color)';
                                $text_color = $is_active ? 'text-white' : 'text-muted';
                                echo '<div class="position-relative text-center" style="z-index: 1;">';
                                echo '<div class="rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 28px; height: 28px; background: '.$color.';">';
                                echo '<i class="fas fa-check text-white small" style="opacity: '.($is_active ? '1' : '0').';"></i>';
                                echo '</div>';
                                echo '<div class="'.$text_color.' small mt-1 fw-bold">'.$st.'</div>';
                                echo '</div>';
                            }
                            echo '</div>';
                        }
                    ?>

                    <?php if ($order['payment_status'] !== 'Paid' && strpos($order['payment_method'], 'ONLINE') !== false): ?>
                        <div class="mt-3">
                            <a href="<?php echo base_url('payment_gateway.php?id=' . $order['id']); ?>" class="btn btn-sm btn-danger rounded-pill fw-bold px-3">
                                <i class="fas fa-credit-card me-1"></i> Pay Now via Online Gateway
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Shipping Info -->
            <div class="col-md-6">
                <div class="p-3 rounded-3 border border-secondary" style="background-color: var(--dark-elevated);">
                    <h6 class="fw-bold text-white border-bottom border-secondary pb-2 mb-2">Shipping Details</h6>
                    <p class="mb-1 fw-bold text-white small"><?php echo e($order['shipping_name']); ?></p>
                    <p class="mb-1 text-muted small"><?php echo e($order['shipping_address']); ?>, <?php echo e($order['shipping_city']); ?>, <?php echo e($order['shipping_state']); ?> - <?php echo e($order['shipping_zip']); ?></p>
                    <p class="mb-0 text-muted small"><i class="fas fa-phone me-1"></i> <?php echo e($order['shipping_phone']); ?></p>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <h5 class="fw-bold text-white mb-3">Item Details</h5>
        <div class="table-responsive mb-4">
            <table class="table table-dark align-middle border border-secondary">
                <thead>
                    <tr>
                        <th scope="col">Product</th>
                        <th scope="col">Price</th>
                        <th scope="col" class="text-center">Quantity</th>
                        <th scope="col" class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="<?php echo base_url('uploads/' . e($item['image'])); ?>" alt="img" class="img-thumbnail me-3" style="width: 50px; height: 50px; object-fit: contain;">
                                    <span class="fw-semibold text-white small"><?php echo e($item['product_name']); ?></span>
                                </div>
                            </td>
                            <td><?php echo format_price($item['price']); ?></td>
                            <td class="text-center"><?php echo $item['quantity']; ?></td>
                            <td class="text-end fw-bold text-danger"><?php echo format_price($item['total']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end fw-bold">Grand Total:</td>
                        <td class="text-end fw-bold fs-5 text-danger"><?php echo format_price($order['total_amount']); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-4">
            <a href="<?php echo base_url('my_orders.php'); ?>" class="btn btn-outline-light rounded-pill px-4">
                <i class="fas fa-box me-2"></i> View All My Orders
            </a>
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-secondary rounded-pill px-4">
                    <i class="fas fa-print me-2"></i> Print Invoice
                </button>
                <a href="<?php echo base_url('shop.php'); ?>" class="btn btn-danger rounded-pill px-4">
                    <i class="fas fa-shopping-bag me-2"></i> Continue Shopping
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
