<?php
// admin/brands.php - Brand Management
$page_title = "Manage Brands";
require_once __DIR__ . '/header.php';

$query = "SELECT * FROM brands ORDER BY id DESC";
$brands = $conn->query($query)->fetch_all(MYSQLI_ASSOC);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-white mb-0"><i class="fas fa-copyright text-danger me-2"></i> Official Brands</h3>
    <a href="<?php echo base_url('admin/add_brand.php'); ?>" class="btn btn-danger rounded-pill fw-bold">
        <i class="fas fa-plus me-1"></i> Add New Brand
    </a>
</div>

<div class="admin-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Brand List (<?php echo count($brands); ?>)</span>
        <input type="text" class="form-control form-control-sm w-auto table-search-input" data-table-target="brandTable" placeholder="Search brands...">
    </div>

    <div class="table-responsive">
        <table class="table table-dark-custom align-middle" id="brandTable">
            <thead>
                <tr>
                    <th style="width: 80px;">Logo</th>
                    <th>Brand Name</th>
                    <th>Slug</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th class="text-center" style="width: 140px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($brands)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No brands found.</td></tr>
                <?php else: ?>
                    <?php foreach ($brands as $b): ?>
                        <tr>
                            <td>
                                <img src="<?php echo base_url('uploads/' . e($b['logo'])); ?>" alt="logo" class="rounded bg-white p-1" style="width: 45px; height: 45px; object-fit: contain;">
                            </td>
                            <td class="fw-bold text-dark"><?php echo e($b['name']); ?></td>
                            <td><code><?php echo e($b['slug']); ?></code></td>
                            <td><span class="small text-muted text-truncate d-inline-block" style="max-width: 250px;"><?php echo e($b['description']); ?></span></td>
                            <td>
                                <?php if ($b['status'] == 1): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <a href="<?php echo base_url('admin/edit_brand.php?id=' . $b['id']); ?>" class="btn btn-sm btn-outline-warning me-1">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="<?php echo base_url('admin/delete_brand.php?id=' . $b['id'] . '&token=' . generate_csrf_token()); ?>" 
                                   class="btn btn-sm btn-outline-danger admin-delete-btn" 
                                   data-name="<?php echo e($b['name']); ?>">
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
