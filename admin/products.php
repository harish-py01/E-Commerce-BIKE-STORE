<?php
// admin/products.php - Product Inventory Management
$page_title = "Manage Products";
require_once __DIR__ . '/header.php';

$query = "SELECT p.*, c.name as category_name, b.name as brand_name 
          FROM products p 
          JOIN categories c ON p.category_id = c.id 
          JOIN brands b ON p.brand_id = b.id 
          ORDER BY p.id DESC";
$products = $conn->query($query)->fetch_all(MYSQLI_ASSOC);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-white mb-0"><i class="fas fa-cubes text-danger me-2"></i> Spare Parts Catalog</h3>
    <a href="<?php echo base_url('admin/add_product.php'); ?>" class="btn btn-danger rounded-pill fw-bold">
        <i class="fas fa-plus me-1"></i> Add New Product
    </a>
</div>

<div class="admin-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Total Products (<?php echo count($products); ?>)</span>
        <input type="text" class="form-control form-control-sm w-auto table-search-input" data-table-target="productTable" placeholder="Search products...">
    </div>

    <div class="table-responsive">
        <table class="table table-dark-custom align-middle" id="productTable">
            <thead>
                <tr>
                    <th style="width: 70px;">Image</th>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Brand</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Flags</th>
                    <th class="text-center" style="width: 140px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">No products found in inventory.</td></tr>
                <?php else: ?>
                    <?php foreach ($products as $p): ?>
                        <tr>
                            <td>
                                <img src="<?php echo base_url('uploads/' . e($p['image'])); ?>" alt="img" class="rounded" style="width: 45px; height: 45px; object-fit: contain;">
                            </td>
                            <td class="fw-bold text-dark">
                                <a href="<?php echo base_url('product.php?slug=' . $p['slug']); ?>" target="_blank" class="text-dark text-decoration-none">
                                    <?php echo e($p['name']); ?>
                                </a>
                            </td>
                            <td><span class="badge bg-secondary"><?php echo e($p['category_name']); ?></span></td>
                            <td><span class="badge bg-dark border"><?php echo e($p['brand_name']); ?></span></td>
                            <td class="fw-bold text-danger"><?php echo format_price($p['price']); ?></td>
                            <td>
                                <?php if ($p['stock'] > 10): ?>
                                    <span class="badge bg-success"><?php echo $p['stock']; ?> in stock</span>
                                <?php elseif ($p['stock'] > 0): ?>
                                    <span class="badge bg-warning text-dark"><?php echo $p['stock']; ?> low</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Out of stock</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($p['is_featured']): ?><span class="badge bg-danger me-1">Featured</span><?php endif; ?>
                                <?php if ($p['is_popular']): ?><span class="badge bg-warning text-dark">Popular</span><?php endif; ?>
                            </td>
                            <td class="text-center">
                                <a href="<?php echo base_url('admin/edit_product.php?id=' . $p['id']); ?>" class="btn btn-sm btn-outline-warning me-1">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="<?php echo base_url('admin/delete_product.php?id=' . $p['id'] . '&token=' . generate_csrf_token()); ?>" 
                                   class="btn btn-sm btn-outline-danger admin-delete-btn" 
                                   data-name="<?php echo e($p['name']); ?>">
                                    <i class="fas fa-trash"></i>
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
