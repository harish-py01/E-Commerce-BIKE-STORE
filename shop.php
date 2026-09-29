<?php
// shop.php - Product Catalog with Filters, Sorting, and Pagination
$page_title = "Shop Bike Spare Parts & Accessories";
require_once __DIR__ . '/includes/header.php';

// Filter Inputs
$cat_slug = isset($_GET['category']) ? trim($_GET['category']) : '';
$brand_slug = isset($_GET['brand']) ? trim($_GET['brand']) : '';
$model_slug = isset($_GET['model']) ? trim($_GET['model']) : '';
$search_q = isset($_GET['q']) ? trim($_GET['q']) : '';
$sort_by = isset($_GET['sort']) ? trim($_GET['sort']) : 'newest';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$limit = 8;
$offset = ($page - 1) * $limit;

// Build WHERE Clause with prepared parameters
$where_clauses = ["p.status = 1"];
$params = [];
$param_types = "";

if (!empty($cat_slug)) {
    $where_clauses[] = "c.slug = ?";
    $params[] = $cat_slug;
    $param_types .= "s";
}

if (!empty($brand_slug)) {
    $where_clauses[] = "b.slug = ?";
    $params[] = $brand_slug;
    $param_types .= "s";
}

if (!empty($model_slug)) {
    $where_clauses[] = "p.id IN (SELECT product_id FROM product_compatibility pc JOIN models m ON pc.model_id = m.id WHERE m.slug = ?)";
    $params[] = $model_slug;
    $param_types .= "s";
}

if (!empty($search_q)) {
    $where_clauses[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $search_param = "%" . $search_q . "%";
    $params[] = $search_param;
    $params[] = $search_param;
    $param_types .= "ss";
}

$where_sql = " WHERE " . implode(" AND ", $where_clauses);

// Sorting
$order_sql = " ORDER BY p.id DESC";
if ($sort_by === 'price_asc') {
    $order_sql = " ORDER BY p.price ASC";
} elseif ($sort_by === 'price_desc') {
    $order_sql = " ORDER BY p.price DESC";
} elseif ($sort_by === 'name_asc') {
    $order_sql = " ORDER BY p.name ASC";
}

// Count Total Products for Pagination
$count_query = "SELECT COUNT(*) as total 
                FROM products p 
                JOIN categories c ON p.category_id = c.id 
                JOIN brands b ON p.brand_id = b.id" . $where_sql;
$stmt_count = $conn->prepare($count_query);
if (!empty($params)) {
    $stmt_count->bind_param($param_types, ...$params);
}
$stmt_count->execute();
$total_products = $stmt_count->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_products / $limit);

// Main Query
$main_query = "SELECT p.*, c.name as category_name, c.slug as category_slug, b.name as brand_name, b.slug as brand_slug 
              FROM products p 
              JOIN categories c ON p.category_id = c.id 
              JOIN brands b ON p.brand_id = b.id" 
              . $where_sql . $order_sql . " LIMIT ? OFFSET ?";

$stmt_main = $conn->prepare($main_query);

$bind_params = $params;
$bind_params[] = $limit;
$bind_params[] = $offset;
$bind_types = $param_types . "ii";

$stmt_main->bind_param($bind_types, ...$bind_params);
$stmt_main->execute();
$products = $stmt_main->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch Categories & Brands for Filters
$all_categories = $conn->query("SELECT * FROM categories WHERE status = 1 ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);
$all_brands = $conn->query("SELECT * FROM brands WHERE status = 1 ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);

$all_models = [];
if (!empty($brand_slug)) {
    $stmt_m = $conn->prepare("SELECT m.* FROM models m JOIN brands b ON m.brand_id = b.id WHERE b.slug = ? AND m.status = 1 ORDER BY m.name ASC");
    $stmt_m->bind_param("s", $brand_slug);
    $stmt_m->execute();
    $all_models = $stmt_m->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    $all_models = $conn->query("SELECT * FROM models WHERE status = 1 ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);
}
?>

<div class="container my-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb p-3 rounded-3 shadow-sm" style="background-color: var(--dark-elevated);">
            <li class="breadcrumb-item"><a href="<?php echo base_url('index.php'); ?>" class="text-decoration-none text-muted"><i class="fas fa-home"></i> Home</a></li>
            <li class="breadcrumb-item active fw-semibold text-danger" aria-current="page">Shop Spare Parts</li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Sidebar Filters -->
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 sticky-top text-white" style="background-color: var(--dark-elevated); top: 90px; z-index: 10; max-height: calc(100vh - 120px); overflow-y: auto;">
                <h5 class="fw-bold mb-3 border-bottom border-secondary pb-2"><i class="fas fa-filter text-danger me-2"></i> Filter Parts</h5>
                
                <form action="<?php echo base_url('shop.php'); ?>" method="GET" id="filterForm">
                    <!-- Keep search term if any -->
                    <?php if (!empty($search_q)): ?>
                        <input type="hidden" name="q" value="<?php echo e($search_q); ?>">
                    <?php endif; ?>

                    <!-- Categories Filter -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold text-secondary mb-2">Category</label>
                        <div class="list-group list-group-flush small">
                            <a href="<?php echo base_url('shop.php' . (!empty($brand_slug) ? '?brand='.$brand_slug : '')); ?>" class="list-group-item list-group-item-action border-0 px-2 py-1.5 rounded <?php echo empty($cat_slug) ? 'bg-danger text-white fw-bold' : 'text-light bg-transparent'; ?>">
                                All Categories
                            </a>
                            <?php foreach ($all_categories as $cat): ?>
                                <a href="<?php echo base_url('shop.php?category=' . $cat['slug'] . (!empty($brand_slug) ? '&brand='.$brand_slug : '')); ?>" 
                                   class="list-group-item list-group-item-action border-0 px-2 py-1.5 rounded <?php echo ($cat_slug === $cat['slug']) ? 'bg-danger text-white fw-bold' : 'text-light bg-transparent'; ?>">
                                    <?php echo e($cat['name']); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Brands Filter -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold text-secondary mb-2">Brand</label>
                        <select name="brand" class="form-select form-select-sm" id="brandSelect" onchange="fetchModels(this.value); document.getElementById('filterForm').submit();">
                            <option value="">All Brands</option>
                            <?php foreach ($all_brands as $b): ?>
                                <option value="<?php echo e($b['slug']); ?>" <?php echo ($brand_slug === $b['slug']) ? 'selected' : ''; ?>>
                                    <?php echo e($b['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Models Filter -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold text-secondary mb-2">Compatible Model</label>
                        <select name="model" class="form-select form-select-sm" id="modelSelect" onchange="document.getElementById('filterForm').submit();">
                            <option value="">All Models</option>
                            <?php foreach ($all_models as $m): 
                                $displayName = $m['name'];
                                if (!empty($m['variant'])) {
                                    $displayName .= ' ' . $m['variant'];
                                }
                                if (!empty($m['start_year'])) {
                                    $end = !empty($m['end_year']) ? $m['end_year'] : 'Present';
                                    $displayName .= ' (' . $m['start_year'] . ' - ' . $end . ')';
                                }
                            ?>
                                <option value="<?php echo e($m['slug']); ?>" <?php echo ($model_slug === $m['slug']) ? 'selected' : ''; ?>>
                                    <?php echo e($displayName); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Sorting -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold text-secondary mb-2">Sort By</label>
                        <select name="sort" class="form-select form-select-sm" onchange="document.getElementById('filterForm').submit();">
                            <option value="newest" <?php echo ($sort_by === 'newest') ? 'selected' : ''; ?>>Newest First</option>
                            <option value="price_asc" <?php echo ($sort_by === 'price_asc') ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price_desc" <?php echo ($sort_by === 'price_desc') ? 'selected' : ''; ?>>Price: High to Low</option>
                            <option value="name_asc" <?php echo ($sort_by === 'name_asc') ? 'selected' : ''; ?>>Product Name: A to Z</option>
                        </select>
                    </div>

                    <?php if (!empty($cat_slug) || !empty($brand_slug) || !empty($model_slug) || !empty($search_q)): ?>
                        <a href="<?php echo base_url('shop.php'); ?>" class="btn btn-outline-secondary btn-sm w-100 rounded-pill">
                            <i class="fas fa-undo me-1"></i> Reset All Filters
                        </a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Main Product Grid -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-3 p-3 rounded-3 shadow-sm text-light" style="background-color: var(--dark-elevated);">
                <span class="text-muted small">Showing <strong><?php echo count($products); ?></strong> of <strong><?php echo $total_products; ?></strong> spare parts</span>
                
                <?php if (!empty($search_q)): ?>
                    <span class="badge bg-danger fs-6 fw-normal">Search: "<?php echo e($search_q); ?>"</span>
                <?php endif; ?>
            </div>

            <?php if (empty($products)): ?>
                <div class="card border-0 shadow-sm rounded-4 p-5 text-center my-4">
                    <i class="fas fa-search-minus text-muted fa-4x mb-3"></i>
                    <h4 class="fw-bold">No Products Found</h4>
                    <p class="text-muted">We couldn't find any spare parts matching your selected filter criteria.</p>
                    <div class="mt-2">
                        <a href="<?php echo base_url('shop.php'); ?>" class="btn btn-danger btn-sm rounded-pill px-4">Browse All Parts</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($products as $prod): ?>
                        <div class="col-md-4 col-sm-6">
                            <div class="product-card tilt-card">
                                <div class="card-img-wrap preserve-3d">
                                    <?php if ($prod['is_featured']): ?>
                                        <span class="badge bg-danger badge-overlay">Featured</span>
                                    <?php endif; ?>
                                    <img src="<?php echo base_url('uploads/' . e($prod['image'])); ?>" alt="<?php echo e($prod['name']); ?>" class="pop-out">
                                </div>
                                <div class="card-body">
                                    <span class="text-muted small text-uppercase fw-medium"><?php echo e($prod['brand_name']); ?></span>
                                    <a href="<?php echo base_url('product.php?slug=' . $prod['slug']); ?>" class="product-title mt-1"><?php echo e($prod['name']); ?></a>
                                    
                                    <div class="d-flex justify-content-between align-items-center mt-3 mb-3">
                                        <span class="product-price"><?php echo format_price($prod['price']); ?></span>
                                        <?php if ($prod['stock'] > 0): ?>
                                            <span class="badge badge-in-stock px-2 py-1">In Stock</span>
                                        <?php else: ?>
                                            <span class="badge badge-out-stock px-2 py-1">Out of Stock</span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="mt-auto">
                                        <?php if ($prod['stock'] > 0): ?>
                                            <form action="<?php echo base_url('add_to_cart.php'); ?>" method="POST">
                                                <input type="hidden" name="product_id" value="<?php echo $prod['id']; ?>">
                                                <input type="hidden" name="quantity" value="1">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit" class="btn btn-outline-danger w-100 rounded-pill btn-sm fw-semibold">
                                                    <i class="fas fa-cart-plus me-1"></i> Add To Cart
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <button class="btn btn-secondary w-100 rounded-pill btn-sm disabled" disabled>Out of Stock</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination Controls -->
                <?php if ($total_pages > 1): ?>
                    <nav class="mt-5" aria-label="Product Pagination">
                        <ul class="pagination justify-content-center">
                            <!-- Prev Link -->
                            <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?php echo base_url('shop.php?page=' . ($page - 1) . (!empty($cat_slug) ? '&category='.$cat_slug : '') . (!empty($brand_slug) ? '&brand='.$brand_slug : '') . (!empty($model_slug) ? '&model='.$model_slug : '') . (!empty($sort_by) ? '&sort='.$sort_by : '')); ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>

                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo ($page === $i) ? 'active' : ''; ?>">
                                    <a class="page-link <?php echo ($page === $i) ? 'bg-danger border-danger' : ''; ?>" href="<?php echo base_url('shop.php?page=' . $i . (!empty($cat_slug) ? '&category='.$cat_slug : '') . (!empty($brand_slug) ? '&brand='.$brand_slug : '') . (!empty($model_slug) ? '&model='.$model_slug : '') . (!empty($sort_by) ? '&sort='.$sort_by : '')); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <!-- Next Link -->
                            <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?php echo base_url('shop.php?page=' . ($page + 1) . (!empty($cat_slug) ? '&category='.$cat_slug : '') . (!empty($brand_slug) ? '&brand='.$brand_slug : '') . (!empty($model_slug) ? '&model='.$model_slug : '') . (!empty($sort_by) ? '&sort='.$sort_by : '')); ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
<script>
function fetchModels(brandSlug) {
    // Handled by traditional form submission for now to keep state simple.
    // If you want pure AJAX, you'd populate #modelSelect here.
    // But since the form submits on change, the PHP re-renders it anyway.
}
</script>
