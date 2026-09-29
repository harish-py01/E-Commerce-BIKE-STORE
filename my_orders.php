<?php
// my_orders.php - Customer Order History
require_once __DIR__ . '/includes/functions.php';

require_customer();

$customer_id = get_customer_id();

// Fetch Orders
$stmt = $conn->prepare("SELECT * FROM orders WHERE customer_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$page_title = "My Orders - Bike Store";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb p-3 rounded-3 shadow-sm" style="background-color: var(--dark-elevated);">
            <li class="breadcrumb-item"><a href="<?php echo base_url('index.php'); ?>" class="text-decoration-none text-muted"><i class="fas fa-home"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="<?php echo base_url('profile.php'); ?>" class="text-decoration-none text-muted">Profile</a></li>
            <li class="breadcrumb-item active text-danger fw-semibold" aria-current="page">My Orders</li>
        </ol>
    </nav>

    <div class="card border-0 shadow-sm rounded-4 p-4 text-light" style="background-color: var(--dark-surface);">
        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary pb-3">
            <h4 class="fw-bold text-white mb-0"><i class="fas fa-box text-danger me-2"></i> My Order History</h4>
            <span class="badge bg-secondary px-3 py-2 fs-6">Total Orders: <?php echo count($orders); ?></span>
        </div>

        <?php if (empty($orders)): ?>
            <div class="text-center py-5">
                <i class="fas fa-box-open text-muted fa-4x mb-3"></i>
                <h5 class="fw-bold">No Orders Placed Yet</h5>
                <p class="text-muted">You haven't placed any orders with us yet. Start exploring our spare parts catalog!</p>
                <a href="<?php echo base_url('shop.php'); ?>" class="btn btn-danger rounded-pill px-4 mt-2">Explore Shop</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-dark align-middle table-hover border border-secondary">
                    <thead>
                        <tr>
                            <th scope="col">Order #</th>
                            <th scope="col">Date</th>
                            <th scope="col">Total Amount</th>
                            <th scope="col">Payment</th>
                            <th scope="col">Order Status</th>
                            <th scope="col" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $o): ?>
                            <tr>
                                <td class="fw-bold text-white"><?php echo e($o['order_number']); ?></td>
                                <td><span class="small text-muted"><?php echo date('d M Y, h:i A', strtotime($o['created_at'])); ?></span></td>
                                <td class="fw-bold text-danger"><?php echo format_price($o['total_amount']); ?></td>
                                <td>
                                    <div class="mb-1">
                                        <span class="badge bg-light text-dark border me-1"><?php echo e($o['payment_method']); ?></span>
                                        <?php if ($o['payment_status'] === 'Paid'): ?>
                                            <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Paid</span>
                                        <?php elseif ($o['payment_status'] === 'Failed'): ?>
                                            <span class="badge bg-danger"><i class="fas fa-exclamation-triangle me-1"></i> Failed</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i> <?php echo e($o['payment_status']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($o['transaction_id'])): ?>
                                        <small class="d-block text-muted font-monospace" style="font-size: 11px;">Txn: <?php echo e($o['transaction_id']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                    $st = $o['order_status'];
                                    $badge_cls = 'bg-secondary';
                                    if ($st === 'Pending') $badge_cls = 'bg-warning text-dark';
                                    elseif ($st === 'Processing') $badge_cls = 'bg-primary';
                                    elseif ($st === 'Delivered') $badge_cls = 'bg-success';
                                    elseif ($st === 'Cancelled') $badge_cls = 'bg-danger';
                                    ?>
                                    <span class="badge <?php echo $badge_cls; ?> px-2.5 py-1.5"><?php echo e($st); ?></span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <?php if ($o['payment_status'] !== 'Paid' && strpos($o['payment_method'], 'ONLINE') !== false): ?>
                                            <a href="<?php echo base_url('payment_gateway.php?id=' . $o['id']); ?>" class="btn btn-sm btn-danger rounded-pill px-3 fw-bold">
                                                <i class="fas fa-credit-card me-1"></i> Pay Now
                                            </a>
                                        <?php endif; ?>
                                        <a href="<?php echo base_url('order_success.php?id=' . $o['id']); ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                            <i class="fas fa-eye me-1"></i> Details
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
