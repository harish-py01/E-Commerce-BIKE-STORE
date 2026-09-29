<?php
// admin/orders.php - Order Management List
$page_title = "Manage Orders";
require_once __DIR__ . '/header.php';

$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';

$where_sql = "";
$params = [];
$param_types = "";

if (!empty($status_filter)) {
    $where_sql = " WHERE o.order_status = ?";
    $params[] = $status_filter;
    $param_types = "s";
}

$query = "SELECT o.*, c.name as customer_name, c.email as customer_email 
          FROM orders o 
          JOIN customers c ON o.customer_id = c.id" 
          . $where_sql . " ORDER BY o.id DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-white mb-0"><i class="fas fa-shopping-bag text-danger me-2"></i> Customer Orders</h3>
    
    <!-- Status Filter Buttons -->
    <div class="btn-group rounded-pill overflow-hidden shadow-sm" role="group">
        <a href="<?php echo base_url('admin/orders.php'); ?>" class="btn btn-sm <?php echo empty($status_filter) ? 'btn-danger' : 'btn-outline-secondary'; ?>">All</a>
        <a href="<?php echo base_url('admin/orders.php?status=Pending'); ?>" class="btn btn-sm <?php echo ($status_filter === 'Pending') ? 'btn-warning text-dark' : 'btn-outline-secondary'; ?>">Pending</a>
        <a href="<?php echo base_url('admin/orders.php?status=Processing'); ?>" class="btn btn-sm <?php echo ($status_filter === 'Processing') ? 'btn-primary' : 'btn-outline-secondary'; ?>">Processing</a>
        <a href="<?php echo base_url('admin/orders.php?status=Delivered'); ?>" class="btn btn-sm <?php echo ($status_filter === 'Delivered') ? 'btn-success' : 'btn-outline-secondary'; ?>">Delivered</a>
        <a href="<?php echo base_url('admin/orders.php?status=Cancelled'); ?>" class="btn btn-sm <?php echo ($status_filter === 'Cancelled') ? 'btn-danger' : 'btn-outline-secondary'; ?>">Cancelled</a>
    </div>
</div>

<div class="admin-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Order List (<?php echo count($orders); ?>)</span>
        <input type="text" class="form-control form-control-sm w-auto table-search-input" data-table-target="ordersTable" placeholder="Search orders...">
    </div>

    <div class="table-responsive">
        <table class="table table-dark-custom align-middle" id="ordersTable">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Date & Time</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No orders matching criteria.</td></tr>
                <?php else: ?>
                    <?php foreach ($orders as $o): ?>
                        <tr>
                            <td class="fw-bold text-dark"><?php echo e($o['order_number']); ?></td>
                            <td>
                                <div class="fw-semibold text-dark small"><?php echo e($o['customer_name']); ?></div>
                                <small class="text-muted"><?php echo e($o['customer_email']); ?></small>
                            </td>
                            <td><span class="small text-muted"><?php echo date('d M Y, h:i A', strtotime($o['created_at'])); ?></span></td>
                            <td class="fw-bold text-danger"><?php echo format_price($o['total_amount']); ?></td>
                            <td>
                                <span class="badge bg-dark border text-white"><?php echo e($o['payment_method']); ?></span>
                                <span class="badge bg-info text-dark ms-1"><?php echo e($o['payment_status']); ?></span>
                            </td>
                            <td>
                                <?php 
                                $st = $o['order_status'];
                                $cls = 'bg-secondary';
                                if ($st === 'Pending') $cls = 'bg-warning text-dark';
                                elseif ($st === 'Processing') $cls = 'bg-primary';
                                elseif ($st === 'Delivered') $cls = 'bg-success';
                                elseif ($st === 'Cancelled') $cls = 'bg-danger';
                                ?>
                                <span class="badge <?php echo $cls; ?>"><?php echo e($st); ?></span>
                            </td>
                            <td class="text-center">
                                <a href="<?php echo base_url('admin/order_details.php?id=' . $o['id']); ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                    <i class="fas fa-edit me-1"></i> Manage
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
