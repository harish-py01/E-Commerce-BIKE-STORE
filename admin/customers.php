<?php
// admin/customers.php - Customer List Management
$page_title = "Manage Customers";
require_once __DIR__ . '/header.php';

$query = "SELECT c.*, COUNT(o.id) as total_orders, IFNULL(SUM(o.total_amount), 0.00) as total_spent 
          FROM customers c 
          LEFT JOIN orders o ON c.id = o.customer_id AND o.order_status != 'Cancelled' 
          GROUP BY c.id 
          ORDER BY c.id DESC";
$customers = $conn->query($query)->fetch_all(MYSQLI_ASSOC);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-white mb-0"><i class="fas fa-users text-danger me-2"></i> Registered Customers</h3>
</div>

<div class="admin-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Customer Directory (<?php echo count($customers); ?>)</span>
        <input type="text" class="form-control form-control-sm w-auto table-search-input" data-table-target="customerTable" placeholder="Search customers...">
    </div>

    <div class="table-responsive">
        <table class="table table-dark-custom align-middle" id="customerTable">
            <thead>
                <tr>
                    <th>Customer Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>City / State</th>
                    <th>Total Orders</th>
                    <th>Total Spent</th>
                    <th>Joined Date</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($customers)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">No registered customers found.</td></tr>
                <?php else: ?>
                    <?php foreach ($customers as $c): ?>
                        <tr>
                            <td class="fw-bold text-dark"><?php echo e($c['name']); ?></td>
                            <td><?php echo e($c['email']); ?></td>
                            <td><?php echo e($c['phone'] ?? 'N/A'); ?></td>
                            <td><?php echo e($c['city'] ?? 'N/A'); ?>, <?php echo e($c['state'] ?? ''); ?></td>
                            <td><span class="badge bg-primary"><?php echo $c['total_orders']; ?> orders</span></td>
                            <td class="fw-bold text-success"><?php echo format_price($c['total_spent']); ?></td>
                            <td><span class="small text-muted"><?php echo date('d M Y', strtotime($c['created_at'])); ?></span></td>
                            <td class="text-center">
                                <a href="<?php echo base_url('admin/customer_details.php?id=' . $c['id']); ?>" class="btn btn-sm btn-outline-info rounded-pill">
                                    <i class="fas fa-user"></i> View Profile
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
