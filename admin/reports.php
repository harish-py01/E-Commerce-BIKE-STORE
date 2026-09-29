<?php
// admin/reports.php - Sales, Product & Revenue Reports
$page_title = "Analytics & Reports";
require_once __DIR__ . '/header.php';

// Revenue Summary
$total_revenue = $conn->query("SELECT SUM(total_amount) as s FROM orders WHERE order_status != 'Cancelled'")->fetch_assoc()['s'] ?? 0.00;
$delivered_orders = $conn->query("SELECT COUNT(*) as c FROM orders WHERE order_status = 'Delivered'")->fetch_assoc()['c'];
$pending_orders = $conn->query("SELECT COUNT(*) as c FROM orders WHERE order_status = 'Pending'")->fetch_assoc()['c'];

// Top Selling Products
$top_products_query = "SELECT oi.product_name, SUM(oi.quantity) as total_qty, SUM(oi.total) as total_revenue 
                       FROM order_items oi 
                       JOIN orders o ON oi.order_id = o.id 
                       WHERE o.order_status != 'Cancelled' 
                       GROUP BY oi.product_id 
                       ORDER BY total_qty DESC LIMIT 5";
$top_products = $conn->query($top_products_query)->fetch_all(MYSQLI_ASSOC);

// Category Revenue Distribution
$cat_revenue_query = "SELECT c.name as category_name, SUM(oi.total) as category_revenue 
                      FROM order_items oi 
                      JOIN products p ON oi.product_id = p.id 
                      JOIN categories c ON p.category_id = c.id 
                      JOIN orders o ON oi.order_id = o.id 
                      WHERE o.order_status != 'Cancelled' 
                      GROUP BY c.id ORDER BY category_revenue DESC";
$cat_revenue = $conn->query($cat_revenue_query)->fetch_all(MYSQLI_ASSOC);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-white mb-0"><i class="fas fa-chart-line text-danger me-2"></i> Business Performance Reports</h3>
    <button onclick="window.print()" class="btn btn-outline-light rounded-pill">
        <i class="fas fa-print me-1"></i> Print Analytics Summary
    </button>
</div>

<!-- Key Revenue Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <span class="text-muted small">Total Delivered Revenue</span>
            <h2 class="fw-bold text-success mt-1 mb-0"><?php echo format_price($total_revenue); ?></h2>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <span class="text-muted small">Delivered Orders</span>
            <h2 class="fw-bold text-primary mt-1 mb-0"><?php echo number_format($delivered_orders); ?></h2>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <span class="text-muted small">Pending Fulfillment</span>
            <h2 class="fw-bold text-warning mt-1 mb-0"><?php echo number_format($pending_orders); ?></h2>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Top Selling Spare Parts Table -->
    <div class="col-lg-7">
        <div class="admin-card h-100">
            <div class="card-header">
                <i class="fas fa-fire text-danger me-2"></i> Top Selling Spare Parts
            </div>
            <div class="table-responsive">
                <table class="table table-dark-custom align-middle">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th class="text-center">Units Sold</th>
                            <th class="text-end">Revenue Generated</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($top_products)): ?>
                            <tr><td colspan="3" class="text-center text-muted py-4">No sales recorded yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($top_products as $tp): ?>
                                <tr>
                                    <td class="fw-bold text-dark"><?php echo e($tp['product_name']); ?></td>
                                    <td class="text-center"><span class="badge bg-primary fs-6"><?php echo $tp['total_qty']; ?> units</span></td>
                                    <td class="text-end fw-bold text-success"><?php echo format_price($tp['total_revenue']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Category Revenue Chart -->
    <div class="col-lg-5">
        <div class="admin-card h-100">
            <div class="card-header">
                <i class="fas fa-chart-pie text-danger me-2"></i> Revenue Share By Category
            </div>
            <div class="card-body p-4">
                <canvas id="categoryChart" style="max-height: 250px;"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const ctx = document.getElementById('categoryChart').getContext('2d');
    const catLabels = <?php echo json_encode(array_column($cat_revenue, 'category_name')); ?>;
    const catData = <?php echo json_encode(array_column($cat_revenue, 'category_revenue')); ?>;

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: catLabels.length ? catLabels : ['Braking', 'Engine', 'Chains', 'Tires', 'Oils'],
            datasets: [{
                data: catData.length ? catData : [4500, 3200, 2899, 4599, 1730],
                backgroundColor: ['#dc3545', '#0d6efd', '#ffc107', '#198754', '#0dcaf0', '#6c757d']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { labels: { color: '#e2e8f0' } }
            }
        }
    });
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
