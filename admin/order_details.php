<?php
// admin/order_details.php - Detailed Order View & Status Update
// NOTE: All PHP logic runs BEFORE header.php include to allow header() redirects.
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$page_title = "Manage Order";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $conn->prepare("SELECT o.*, c.name as customer_name, c.email as customer_email, c.phone as customer_phone 
                        FROM orders o 
                        JOIN customers c ON o.customer_id = c.id 
                        WHERE o.id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    set_flash('danger', 'Order not found.');
    header('Location: ' . base_url('admin/orders.php'));
    exit();
}

// Fetch Order Items
$items_stmt = $conn->prepare("SELECT oi.*, p.image, p.slug FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$items_stmt->bind_param("i", $id);
$items_stmt->execute();
$items = $items_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Handle Status Updates BEFORE any HTML output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf_token)) {
        header('Location: ' . base_url('admin/order_details.php?id=' . $id));
        exit();
    }

    $new_order_status = $_POST['order_status'] ?? $order['order_status'];
    $new_payment_status = $_POST['payment_status'] ?? $order['payment_status'];

    $upd_stmt = $conn->prepare("UPDATE orders SET order_status = ?, payment_status = ? WHERE id = ?");
    $upd_stmt->bind_param("ssi", $new_order_status, $new_payment_status, $id);

    if ($upd_stmt->execute()) {
        set_flash('success', 'Order status updated to "' . e($new_order_status) . '" and payment status updated to "' . e($new_payment_status) . '".');
        header('Location: ' . base_url('admin/order_details.php?id=' . $id));
        exit();
    }
}

// Now include header AFTER all redirect logic is done
require_once __DIR__ . '/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-white mb-0"><i class="fas fa-file-invoice text-danger me-2"></i> Manage Order #<?php echo e($order['order_number']); ?></h3>
    <a href="<?php echo base_url('admin/orders.php'); ?>" class="btn btn-outline-secondary rounded-pill">
        <i class="fas fa-arrow-left me-1"></i> Back to Orders
    </a>
</div>

<div class="row g-4 mb-4">
    <!-- Status Update Form Card -->
    <div class="col-lg-4">
        <div class="admin-card p-4 h-100">
            <h5 class="fw-bold text-white border-bottom border-secondary pb-3 mb-3">Update Order Status</h5>
            
            <form action="<?php echo base_url('admin/order_details.php?id=' . $id); ?>" method="POST">
                <?php echo csrf_field(); ?>

                <div class="mb-3">
                    <label class="form-label text-muted small fw-semibold">Order Fulfillment Status</label>
                    <select name="order_status" class="form-select">
                        <option value="Pending" <?php echo ($order['order_status'] === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="Processing" <?php echo ($order['order_status'] === 'Processing') ? 'selected' : ''; ?>>Processing</option>
                        <option value="Delivered" <?php echo ($order['order_status'] === 'Delivered') ? 'selected' : ''; ?>>Delivered</option>
                        <option value="Cancelled" <?php echo ($order['order_status'] === 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label text-muted small fw-semibold">Payment Status</label>
                    <select name="payment_status" class="form-select">
                        <option value="Pending" <?php echo ($order['payment_status'] === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="Paid" <?php echo ($order['payment_status'] === 'Paid') ? 'selected' : ''; ?>>Paid</option>
                        <option value="Refunded" <?php echo ($order['payment_status'] === 'Refunded') ? 'selected' : ''; ?>>Refunded</option>
                    </select>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-danger rounded-pill fw-bold">
                        <i class="fas fa-sync me-2"></i> Update Order Status
                    </button>
                </div>
            </form>

            <hr class="border-secondary my-4">

            <h6 class="fw-bold text-white mb-2">Payment Details</h6>
            <div class="small text-muted mb-4">
                <p class="mb-1"><strong class="text-white">Payment Method:</strong> <?php echo e($order['payment_method']); ?></p>
                <p class="mb-1"><strong class="text-white">Payment Status:</strong> <?php echo e($order['payment_status']); ?></p>
                <?php if (!empty($order['transaction_id'])): ?>
                    <p class="mb-1"><strong class="text-white">Transaction ID:</strong> <code class="bg-dark border border-secondary px-2 py-1 rounded text-warning"><?php echo e($order['transaction_id']); ?></code></p>
                <?php endif; ?>
                <?php if (!empty($order['payment_details'])): ?>
                    <?php $p_info = json_decode($order['payment_details'], true); ?>
                    <?php if (is_array($p_info)): ?>
                        <p class="mb-0"><strong class="text-white">Gateway Timestamp:</strong> <?php echo e($p_info['timestamp'] ?? 'N/A'); ?></p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <hr class="border-secondary my-4">

            <h6 class="fw-bold text-white mb-2">Shipping Information</h6>
            <div class="small text-muted">
                <p class="mb-1"><strong class="text-white">Recipient:</strong> <?php echo e($order['shipping_name']); ?></p>
                <p class="mb-1"><strong class="text-white">Phone:</strong> <?php echo e($order['shipping_phone']); ?></p>
                <p class="mb-1"><strong class="text-white">Address:</strong> <?php echo e($order['shipping_address']); ?></p>
                <p class="mb-0"><strong class="text-white">City:</strong> <?php echo e($order['shipping_city']); ?>, <?php echo e($order['shipping_state']); ?> - <?php echo e($order['shipping_zip']); ?></p>
            </div>
        </div>
    </div>

    <!-- Order Items List -->
    <div class="col-lg-8">
        <div class="admin-card h-100 p-4">
            <h5 class="fw-bold text-white border-bottom border-secondary pb-3 mb-3">Order Items & Summary</h5>

            <div class="table-responsive mb-4">
                <table class="table table-dark-custom align-middle">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Unit Price</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="<?php echo base_url('uploads/' . e($item['image'])); ?>" alt="img" class="rounded me-3" style="width: 45px; height: 45px; object-fit: contain;">
                                        <a href="<?php echo base_url('product.php?slug=' . $item['slug']); ?>" target="_blank" class="text-white text-decoration-none small fw-semibold">
                                            <?php echo e($item['product_name']); ?>
                                        </a>
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
                            <td colspan="3" class="text-end fw-bold text-white">Grand Total Amount:</td>
                            <td class="text-end fw-bold text-danger fs-5"><?php echo format_price($order['total_amount']); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                <button onclick="window.print()" class="btn btn-outline-light rounded-pill">
                    <i class="fas fa-print me-1"></i> Print Packing Slip
                </button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
