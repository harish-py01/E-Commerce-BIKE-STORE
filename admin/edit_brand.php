<?php
// admin/edit_brand.php - Edit Brand Form
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$page_title = "Edit Brand";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $conn->prepare("SELECT * FROM brands WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$brand = $stmt->get_result()->fetch_assoc();

if (!$brand) {
    set_flash('danger', 'Brand not found.');
    header('Location: ' . base_url('admin/brands.php'));
    exit();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf_token)) {
        header('Location: ' . base_url('admin/edit_brand.php?id=' . $id));
        exit();
    }

    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = isset($_POST['status']) ? 1 : 0;
    $slug = slugify($name);

    if (empty($name)) $errors[] = "Brand name cannot be empty.";

    $logo_name = $brand['logo'];
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $upload = upload_image($_FILES['logo']);
        if ($upload['status']) {
            $logo_name = $upload['file_name'];
        } else {
            $errors[] = $upload['message'];
        }
    }

    if (empty($errors)) {
        $stmt_upd = $conn->prepare("UPDATE brands SET name = ?, slug = ?, logo = ?, description = ?, status = ? WHERE id = ?");
        $stmt_upd->bind_param("ssssii", $name, $slug, $logo_name, $description, $status, $id);

        if ($stmt_upd->execute()) {
            set_flash('success', 'Brand updated successfully.');
            header('Location: ' . base_url('admin/brands.php'));
            exit();
        } else {
            $errors[] = "Database update failed.";
        }
    }
}

// Include header AFTER all redirect logic
require_once __DIR__ . '/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-white mb-0"><i class="fas fa-edit text-warning me-2"></i> Edit Brand</h3>
    <a href="<?php echo base_url('admin/brands.php'); ?>" class="btn btn-outline-secondary rounded-pill">
        <i class="fas fa-arrow-left me-1"></i> Back to Brands
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="admin-card p-4">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger mb-4">
                    <ul class="mb-0 ps-3">
                        <?php foreach ($errors as $err): ?>
                            <li><?php echo e($err); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="<?php echo base_url('admin/edit_brand.php?id=' . $id); ?>" method="POST" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Brand Name *</label>
                    <input type="text" name="name" class="form-control" value="<?php echo e($brand['name']); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea name="description" class="form-control" rows="3"><?php echo e($brand['description']); ?></textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Brand Logo</label>
                    <input type="file" name="logo" class="form-control image-preview-input" data-preview-target="logoPreview" accept="image/*">
                    <div class="mt-3">
                        <p class="small text-muted mb-1">Current / Preview Logo:</p>
                        <img id="logoPreview" src="<?php echo base_url('uploads/' . e($brand['logo'])); ?>" alt="Logo" class="rounded border bg-white p-2" style="max-height: 120px;">
                    </div>
                </div>

                <div class="mb-4 form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="status" id="brandStatus" value="1" <?php echo ($brand['status'] == 1) ? 'checked' : ''; ?>>
                    <label class="form-check-label text-white" for="brandStatus">Active (Visible on Storefront)</label>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-warning btn-lg rounded-pill px-5 fw-bold">
                        <i class="fas fa-save me-2"></i> Update Brand
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
