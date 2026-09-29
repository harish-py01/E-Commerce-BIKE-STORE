<?php
// admin/customer_details.php - Customer Details & History View
$page_title = "Customer Details";
require_once __DIR__ . '/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $conn->prepare("SELECT * FROM customers WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$customer = $stmt->get_result()->fetch_assoc();

if (!$customer) {
    set_flash('danger', 'Customer not found.');
    header('Location: ' . base_url('admin/customers.php'));
    exit();
}

// Fetch Customer Orders
$ord_stmt = $conn->prepare("SELECT * FROM orders WHERE customer_id = ? ORDER BY id DESC");
$ord_stmt->bind_param("i", $id);
$ord_stmt->execute();
$orders = $ord_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-white mb-0"><i class="fas fa-user-circle text-danger me-2"></i> Customer Profile: <?php echo e($customer['name']); ?></h3>
    <a href="<?php echo base_url('admin/customers.php'); ?>" class="btn btn-outline-secondary rounded-pill">
        <i class="fas fa-arrow-left me-1"></i> Back to Customers
    </a>
</div>

<div class="row g-4 mb-4">
    <!-- Account Info Card -->
    <div class="col-lg-4">
        <div class="admin-card p-4 h-100">
            <h5 class="fw-bold text-white border-bottom border-secondary pb-3 mb-3">Account Details</h5>
            <div class="small">
                <p class="mb-2"><strong class="text-muted">Full Name:</strong> <span class="text-white ms-2"><?php echo e($customer['name']); ?></span></p>
                <p class="mb-2"><strong class="text-muted">Email:</strong> <span class="text-white ms-2"><?php echo e($customer['email']); ?></span></p>
                <p class="mb-2"><strong class="text-muted">Phone:</strong> <span class="text-white ms-2"><?php echo e($customer['phone'] ?? 'N/A'); ?></span></p>
                <p class="mb-2"><strong class="text-muted">Address:</strong> <span class="text-white ms-2"><?php echo e($customer['address'] ?? 'N/A'); ?></span></p>
                <p class="mb-2"><strong class="text-muted">City / State:</strong> <span class="text-white ms-2"><?php echo e($customer['city'] ?? ''); ?>, <?php echo e($customer['state'] ?? ''); ?> - <?php echo e($customer['zip_code'] ?? ''); ?></span></p>
                <p class="mb-0"><strong class="text-muted">Registered On:</strong> <span class="text-white ms-2"><?php echo date('d M Y, h:i A', strtotime($customer['created_at'])); ?></span></p>
            </div>
        </div>
    </div>

    <!-- Orders History -->
    <div class="col-lg-8">
        <div class="admin-card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Order History (<?php echo count($orders); ?>)</span>
            </div>
            <div class="table-responsive">
                <table class="table table-dark-custom align-middle">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">No orders placed by this customer yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($orders as $o): ?>
                                <tr>
                                    <td class="fw-bold text-white"><?php echo e($o['order_number']); ?></td>
                                    <td><span class="small text-muted"><?php echo date('d M Y', strtotime($o['created_at'])); ?></span></td>
                                    <td class="fw-bold text-danger"><?php echo format_price($o['total_amount']); ?></td>
                                    <td><span class="badge bg-secondary"><?php echo e($o['payment_method']); ?></span></td>
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
                                        <a href="<?php echo base_url('admin/order_details.php?id=' . $o['id']); ?>" class="btn btn-sm btn-outline-light rounded-pill">
                                            <i class="fas fa-eye"></i> Details
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
