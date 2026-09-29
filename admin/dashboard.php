<?php
// admin/dashboard.php - Professional Admin Dashboard
$page_title = "Dashboard Overview";
require_once __DIR__ . '/header.php';

// Fetch Dashboard Metrics
$total_products = $conn->query("SELECT COUNT(*) as c FROM products")->fetch_assoc()['c'];
$total_categories = $conn->query("SELECT COUNT(*) as c FROM categories")->fetch_assoc()['c'];
$total_brands = $conn->query("SELECT COUNT(*) as c FROM brands")->fetch_assoc()['c'];
$total_customers = $conn->query("SELECT COUNT(*) as c FROM customers")->fetch_assoc()['c'];
$total_orders = $conn->query("SELECT COUNT(*) as c FROM orders")->fetch_assoc()['c'];
$total_revenue = $conn->query("SELECT SUM(total_amount) as s FROM orders WHERE order_status != 'Cancelled'")->fetch_assoc()['s'] ?? 0.00;

// Fetch Recent Orders
$recent_orders_query = "SELECT o.*, c.name as customer_name FROM orders o JOIN customers c ON o.customer_id = c.id ORDER BY o.id DESC LIMIT 5";
$recent_orders = $conn->query($recent_orders_query)->fetch_all(MYSQLI_ASSOC);

// Fetch Recent Customers
$recent_customers_query = "SELECT * FROM customers ORDER BY id DESC LIMIT 5";
$recent_customers = $conn->query($recent_customers_query)->fetch_all(MYSQLI_ASSOC);
?>

<div class="row g-4 mb-4">
    <!-- Stat 1: Products -->
    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="stat-card d-flex align-items-center justify-content-between">
            <div>
                <span class="text-muted small d-block">Products</span>
                <h3 class="fw-bold text-white mb-0 mt-1"><?php echo number_format($total_products); ?></h3>
            </div>
            <div class="stat-icon bg-primary bg-opacity-25 text-primary">
                <i class="fas fa-cubes"></i>
            </div>
        </div>
    </div>

    <!-- Stat 2: Categories -->
    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="stat-card d-flex align-items-center justify-content-between">
            <div>
                <span class="text-muted small d-block">Categories</span>
                <h3 class="fw-bold text-white mb-0 mt-1"><?php echo number_format($total_categories); ?></h3>
            </div>
            <div class="stat-icon bg-success bg-opacity-25 text-success">
                <i class="fas fa-tags"></i>
            </div>
        </div>
    </div>

    <!-- Stat 3: Brands -->
    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="stat-card d-flex align-items-center justify-content-between">
            <div>
                <span class="text-muted small d-block">Brands</span>
                <h3 class="fw-bold text-white mb-0 mt-1"><?php echo number_format($total_brands); ?></h3>
            </div>
            <div class="stat-icon bg-info bg-opacity-25 text-info">
                <i class="fas fa-copyright"></i>
            </div>
        </div>
    </div>

    <!-- Stat 4: Customers -->
    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="stat-card d-flex align-items-center justify-content-between">
            <div>
                <span class="text-muted small d-block">Customers</span>
                <h3 class="fw-bold text-white mb-0 mt-1"><?php echo number_format($total_customers); ?></h3>
            </div>
            <div class="stat-icon bg-warning bg-opacity-25 text-warning">
                <i class="fas fa-users"></i>
            </div>
        </div>
    </div>

    <!-- Stat 5: Orders -->
    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="stat-card d-flex align-items-center justify-content-between">
            <div>
                <span class="text-muted small d-block">Total Orders</span>
                <h3 class="fw-bold text-white mb-0 mt-1"><?php echo number_format($total_orders); ?></h3>
            </div>
            <div class="stat-icon bg-danger bg-opacity-25 text-danger">
                <i class="fas fa-shopping-bag"></i>
            </div>
        </div>
    </div>

    <!-- Stat 6: Revenue -->
    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="stat-card d-flex align-items-center justify-content-between">
            <div>
                <span class="text-muted small d-block">Total Revenue</span>
                <h3 class="fw-bold text-success mb-0 mt-1"><?php echo format_price($total_revenue); ?></h3>
            </div>
            <div class="stat-icon bg-success bg-opacity-25 text-success">
                <i class="fas fa-rupee-sign"></i>
            </div>
        </div>
    </div>
</div>

<!-- Sales Analytics Chart -->
<div class="row mb-4">
    <div class="col-12">
        <div class="admin-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-chart-line text-danger me-2"></i> Monthly Sales & Revenue Overview</span>
                <span class="badge bg-secondary">Year 2026</span>
            </div>
            <div class="card-body p-4">
                <canvas id="salesChart" style="max-height: 280px;"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent Orders & Recent Customers Tables -->
<div class="row g-4">
    <!-- Recent Orders -->
    <div class="col-lg-8">
        <div class="admin-card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-shopping-cart text-danger me-2"></i> Recent Orders</span>
                <a href="<?php echo base_url('admin/orders.php'); ?>" class="btn btn-outline-danger btn-sm rounded-pill">View All Orders</a>
            </div>
            <div class="table-responsive">
                <table class="table table-dark-custom align-middle">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent_orders)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">No recent orders found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($recent_orders as $ro): ?>
                                <tr>
                                    <td class="fw-bold text-white"><?php echo e($ro['order_number']); ?></td>
                                    <td><?php echo e($ro['customer_name']); ?></td>
                                    <td class="fw-bold text-danger"><?php echo format_price($ro['total_amount']); ?></td>
                                    <td>
                                        <?php 
                                        $st = $ro['order_status'];
                                        $cls = 'bg-secondary';
                                        if ($st === 'Pending') $cls = 'bg-warning text-dark';
                                        elseif ($st === 'Processing') $cls = 'bg-primary';
                                        elseif ($st === 'Delivered') $cls = 'bg-success';
                                        elseif ($st === 'Cancelled') $cls = 'bg-danger';
                                        ?>
                                        <span class="badge <?php echo $cls; ?>"><?php echo e($st); ?></span>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?php echo base_url('admin/order_details.php?id=' . $ro['id']); ?>" class="btn btn-sm btn-outline-light rounded-pill">
                                            <i class="fas fa-eye"></i> View
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

    <!-- Recent Customers -->
    <div class="col-lg-4">
        <div class="admin-card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-user-plus text-danger me-2"></i> New Customers</span>
                <a href="<?php echo base_url('admin/customers.php'); ?>" class="btn btn-outline-danger btn-sm rounded-pill">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush bg-transparent">
                    <?php if (empty($recent_customers)): ?>
                        <div class="p-3 text-muted text-center">No customers registered yet.</div>
                    <?php else: ?>
                        <?php foreach ($recent_customers as $rc): ?>
                            <div class="list-group-item bg-transparent text-white border-bottom border-secondary d-flex align-items-center p-3">
                                <i class="fas fa-user-circle fs-3 text-secondary me-3"></i>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0 fw-semibold text-white small"><?php echo e($rc['name']); ?></h6>
                                    <small class="text-muted"><?php echo e($rc['email']); ?></small>
                                </div>
                                <span class="small text-muted"><?php echo date('d M', strtotime($rc['created_at'])); ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Render Chart Script -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    const ctx = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Monthly Revenue (₹)',
                data: [12000, 19000, 15000, 22000, 28000, 31000, 38000, 42000, 39000, 45000, 48000, 55000],
                backgroundColor: 'rgba(220, 53, 69, 0.7)',
                borderColor: '#dc3545',
                borderWidth: 1.5,
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#2d3448' },
                    ticks: { color: '#94a3b8' }
                },
                x: {
                    grid: { color: '#2d3448' },
                    ticks: { color: '#94a3b8' }
                }
            },
            plugins: {
                legend: { labels: { color: '#e2e8f0' } }
            }
        }
    });
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
