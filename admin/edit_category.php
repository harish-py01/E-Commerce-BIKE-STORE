<?php
// admin/edit_category.php - Edit Category Form
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$page_title = "Edit Category";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $conn->prepare("SELECT * FROM categories WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$category = $stmt->get_result()->fetch_assoc();

if (!$category) {
    set_flash('danger', 'Category not found.');
    header('Location: ' . base_url('admin/categories.php'));
    exit();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf_token)) {
        header('Location: ' . base_url('admin/edit_category.php?id=' . $id));
        exit();
    }

    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = isset($_POST['status']) ? 1 : 0;
    $slug = slugify($name);

    if (empty($name)) $errors[] = "Category name cannot be empty.";

    $image_name = $category['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload = upload_image($_FILES['image']);
        if ($upload['status']) {
            $image_name = $upload['file_name'];
        } else {
            $errors[] = $upload['message'];
        }
    }

    if (empty($errors)) {
        $stmt_upd = $conn->prepare("UPDATE categories SET name = ?, slug = ?, description = ?, image = ?, status = ? WHERE id = ?");
        $stmt_upd->bind_param("ssssii", $name, $slug, $description, $image_name, $status, $id);

        if ($stmt_upd->execute()) {
            set_flash('success', 'Category updated successfully.');
            header('Location: ' . base_url('admin/categories.php'));
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
    <h3 class="fw-bold text-white mb-0"><i class="fas fa-edit text-warning me-2"></i> Edit Category</h3>
    <a href="<?php echo base_url('admin/categories.php'); ?>" class="btn btn-outline-secondary rounded-pill">
        <i class="fas fa-arrow-left me-1"></i> Back to Categories
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

            <form action="<?php echo base_url('admin/edit_category.php?id=' . $id); ?>" method="POST" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Category Name *</label>
                    <input type="text" name="name" class="form-control" value="<?php echo e($category['name']); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea name="description" class="form-control" rows="3"><?php echo e($category['description']); ?></textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Category Image</label>
                    <input type="file" name="image" class="form-control image-preview-input" data-preview-target="catPreview" accept="image/*">
                    <div class="mt-3">
                        <p class="small text-muted mb-1">Current / Preview Image:</p>
                        <img id="catPreview" src="<?php echo base_url('uploads/' . e($category['image'])); ?>" alt="Category Image" class="rounded border" style="max-height: 120px;">
                    </div>
                </div>

                <div class="mb-4 form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="status" id="catStatus" value="1" <?php echo ($category['status'] == 1) ? 'checked' : ''; ?>>
                    <label class="form-check-label text-white" for="catStatus">Active (Visible on Storefront)</label>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-warning btn-lg rounded-pill px-5 fw-bold">
                        <i class="fas fa-save me-2"></i> Update Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
