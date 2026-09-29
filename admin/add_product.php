<?php
// admin/add_product.php - Add Product Form
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$page_title = "Add Product";

// Fetch Categories & Brands for dropdowns
$categories = $conn->query("SELECT id, name FROM categories WHERE status = 1 ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);
$brands = $conn->query("SELECT id, name FROM brands WHERE status = 1 ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);

$errors = [];
$name = '';
$category_id = '';
$brand_id = '';
$description = '';
$price = '';
$stock = '';
$is_featured = 0;
$is_popular = 0;
$status = 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf_token)) {
        header('Location: ' . base_url('admin/add_product.php'));
        exit();
    }

    $name = trim($_POST['name'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $brand_id = (int)($_POST['brand_id'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_popular = isset($_POST['is_popular']) ? 1 : 0;
    $status = isset($_POST['status']) ? 1 : 0;
    $slug = slugify($name);

    if (empty($name)) $errors[] = "Product Name is required.";
    if ($category_id <= 0) $errors[] = "Category must be selected.";
    if ($brand_id <= 0) $errors[] = "Brand must be selected.";
    if ($price <= 0) $errors[] = "Price must be greater than zero.";

    $image_name = 'default_product.jpg';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload = upload_image($_FILES['image']);
        if ($upload['status']) {
            $image_name = $upload['file_name'];
        } else {
            $errors[] = $upload['message'];
        }
    }

    $model_3d_name = null;
    if (isset($_FILES['model_3d']) && $_FILES['model_3d']['error'] === UPLOAD_ERR_OK) {
        $upload_3d = upload_3d_model($_FILES['model_3d']);
        if ($upload_3d['status']) {
            $model_3d_name = $upload_3d['file_name'];
        } else {
            $errors[] = $upload_3d['message'];
        }
    }

    if (empty($errors)) {
        $chk = $conn->prepare("SELECT id FROM products WHERE slug = ? LIMIT 1");
        $chk->bind_param("s", $slug);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            $slug .= '-' . rand(100, 999);
        }

        $gallery_json = json_encode([$image_name]);

        $stmt = $conn->prepare("INSERT INTO products (category_id, brand_id, name, slug, description, price, stock, image, gallery, product_3d_model, is_featured, is_popular, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iissd disssiii", $category_id, $brand_id, $name, $slug, $description, $price, $stock, $image_name, $gallery_json, $model_3d_name, $is_featured, $is_popular, $status);

        if ($stmt->execute()) {
            set_flash('success', 'Product "' . e($name) . '" added to inventory.');
            header('Location: ' . base_url('admin/products.php'));
            exit();
        } else {
            $errors[] = "Database insertion failed: " . $conn->error;
        }
    }
}

// Include header AFTER all redirect logic
require_once __DIR__ . '/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-white mb-0"><i class="fas fa-plus-circle text-danger me-2"></i> Add New Spare Part</h3>
    <a href="<?php echo base_url('admin/products.php'); ?>" class="btn btn-outline-secondary rounded-pill">
        <i class="fas fa-arrow-left me-1"></i> Back to Products
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-9">
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

            <form action="<?php echo base_url('admin/add_product.php'); ?>" method="POST" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>

                <div class="row g-3 mb-3">
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Product Name *</label>
                        <input type="text" name="name" class="form-control" value="<?php echo e($name); ?>" required placeholder="e.g. Brembo Ceramic Front Brake Pads">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Price (₹) *</label>
                        <input type="number" step="0.01" name="price" class="form-control" value="<?php echo e($price); ?>" required placeholder="1499.00">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Category *</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo ($category_id == $cat['id']) ? 'selected' : ''; ?>><?php echo e($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Brand *</label>
                        <select name="brand_id" class="form-select" required>
                            <option value="">Select Brand</option>
                            <?php foreach ($brands as $b): ?>
                                <option value="<?php echo $b['id']; ?>" <?php echo ($brand_id == $b['id']) ? 'selected' : ''; ?>><?php echo e($b['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Stock Qty *</label>
                        <input type="number" name="stock" class="form-control" value="<?php echo e($stock); ?>" required min="0" placeholder="25">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Product Description</label>
                    <textarea name="description" class="form-control" rows="4" placeholder="Detailed product specifications, compatibility, material details..."><?php echo e($description); ?></textarea>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Primary Image</label>
                        <input type="file" name="image" class="form-control image-preview-input" data-preview-target="prodPreview" accept="image/*">
                        <div class="mt-3">
                            <img id="prodPreview" src="#" alt="Preview" class="rounded d-none border" style="max-height: 140px;">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">3D Model (GLB/GLTF) <span class="badge bg-secondary ms-2">Optional</span></label>
                        <input type="file" name="model_3d" class="form-control" accept=".glb,.gltf">
                        <small class="text-muted d-block mt-2">Upload a true 3D model for this spare part. Max 50MB.</small>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_featured" id="isFeat" value="1">
                            <label class="form-check-label text-white" for="isFeat">Featured Product</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_popular" id="isPop" value="1">
                            <label class="form-check-label text-white" for="isPop">Popular Demand</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="status" id="prodStatus" value="1" checked>
                            <label class="form-check-label text-white" for="prodStatus">Active (Visible)</label>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-danger btn-lg rounded-pill px-5 fw-bold">
                        <i class="fas fa-save me-2"></i> Save Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
