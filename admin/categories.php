<?php
// admin/categories.php - Category Management
$page_title = "Manage Categories";
require_once __DIR__ . '/header.php';

$query = "SELECT * FROM categories ORDER BY id DESC";
$categories = $conn->query($query)->fetch_all(MYSQLI_ASSOC);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-white mb-0"><i class="fas fa-tags text-danger me-2"></i> Product Categories</h3>
    <a href="<?php echo base_url('admin/add_category.php'); ?>" class="btn btn-danger rounded-pill fw-bold">
        <i class="fas fa-plus me-1"></i> Add New Category
    </a>
</div>

<div class="admin-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Category List (<?php echo count($categories); ?>)</span>
        <input type="text" class="form-control form-control-sm w-auto table-search-input" data-table-target="categoryTable" placeholder="Search categories...">
    </div>

    <div class="table-responsive">
        <table class="table table-dark-custom align-middle" id="categoryTable">
            <thead>
                <tr>
                    <th style="width: 80px;">Image</th>
                    <th>Category Name</th>
                    <th>Slug</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th class="text-center" style="width: 140px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($categories)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No categories found.</td></tr>
                <?php else: ?>
                    <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td>
                                <img src="<?php echo base_url('uploads/' . e($cat['image'])); ?>" alt="cat" class="rounded" style="width: 45px; height: 45px; object-fit: cover;">
                            </td>
                            <td class="fw-bold text-dark"><?php echo e($cat['name']); ?></td>
                            <td><code><?php echo e($cat['slug']); ?></code></td>
                            <td><span class="small text-muted text-truncate d-inline-block" style="max-width: 250px;"><?php echo e($cat['description']); ?></span></td>
                            <td>
                                <?php if ($cat['status'] == 1): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <a href="<?php echo base_url('admin/edit_category.php?id=' . $cat['id']); ?>" class="btn btn-sm btn-outline-warning me-1">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="<?php echo base_url('admin/delete_category.php?id=' . $cat['id'] . '&token=' . generate_csrf_token()); ?>" 
                                   class="btn btn-sm btn-outline-danger admin-delete-btn" 
                                   data-name="<?php echo e($cat['name']); ?>">
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
