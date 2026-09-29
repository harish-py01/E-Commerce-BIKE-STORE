<?php
// index.php - Home Page
$page_title = "Home - Premium Bike Spare Parts & Accessories";
require_once __DIR__ . '/includes/header.php';

// Fetch Categories
$cat_query = "SELECT * FROM categories WHERE status = 1 ORDER BY name ASC LIMIT 6";
$categories = $conn->query($cat_query)->fetch_all(MYSQLI_ASSOC);

// Fetch Featured Products
$feat_query = "SELECT p.*, c.name as category_name, b.name as brand_name 
              FROM products p 
              JOIN categories c ON p.category_id = c.id 
              JOIN brands b ON p.brand_id = b.id 
              WHERE p.is_featured = 1 AND p.status = 1 
              ORDER BY p.id DESC LIMIT 8";
$featured_products = $conn->query($feat_query)->fetch_all(MYSQLI_ASSOC);

// Fetch Popular Products
$pop_query = "SELECT p.*, c.name as category_name, b.name as brand_name 
             FROM products p 
             JOIN categories c ON p.category_id = c.id 
             JOIN brands b ON p.brand_id = b.id 
             WHERE p.is_popular = 1 AND p.status = 1 
             ORDER BY p.id DESC LIMIT 4";
$popular_products = $conn->query($pop_query)->fetch_all(MYSQLI_ASSOC);

// Fetch Top Brands
$brand_query = "SELECT * FROM brands WHERE status = 1 ORDER BY name ASC LIMIT 6";
$brands = $conn->query($brand_query)->fetch_all(MYSQLI_ASSOC);
?>

<!-- Hero Slider Section -->
<section class="mb-5">
    <div id="heroCarousel" class="carousel slide hero-slider shadow-lg" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
        </div>
        <div class="carousel-inner">
            <div class="carousel-item active py-5 px-4 px-md-5">
                <div class="row align-items-center py-4">
                    <div class="col-lg-7 text-white">
                        <span class="badge bg-danger mb-2 px-3 py-2 text-uppercase">High Performance Parts</span>
                        <h1 class="display-4 fw-bold mb-3">Upgrade Your Ride With Authentic Spare Parts</h1>
                        <p class="lead mb-4 text-light opacity-75">Engineered for endurance. Premium brake ceramic pads, Akrapovič performance exhausts, and OEM cylinder heads.</p>
                        <div class="d-flex gap-3">
                            <a href="<?php echo base_url('shop.php'); ?>" class="btn btn-primary btn-lg">Explore Shop <i class="fas fa-arrow-right ms-2"></i></a>
                            <a href="<?php echo base_url('shop.php?category=braking-systems'); ?>" class="btn btn-outline-light btn-lg rounded-pill">Braking Systems</a>
                        </div>
                    </div>
                    <div class="col-lg-5 text-center d-none d-lg-block preserve-3d">
                        <img src="<?php echo base_url('assets/images/products/brake_pad.png'); ?>" alt="Brake Pad" class="img-fluid pop-out" style="max-height: 220px; object-fit: contain; filter: drop-shadow(0px 10px 15px rgba(0,0,0,0.15)); animation: float 6s ease-in-out infinite;" onerror="this.onerror=null; this.src='<?php echo base_url('uploads/default_product.jpg'); ?>';">
                    </div>
                </div>
            </div>
            <div class="carousel-item py-5 px-4 px-md-5">
                <div class="row align-items-center py-4">
                    <div class="col-lg-7 text-white">
                        <span class="badge bg-primary mb-2 px-3 py-2 text-uppercase">100% Synthetic Oils</span>
                        <h1 class="display-4 fw-bold mb-3">Keep Your Engine Running Like Brand New</h1>
                        <p class="lead mb-4 text-light opacity-75">Discover Motul Ester oils, high flow washable air filters, and heavy duty DID drive chain kits.</p>
                        <div class="d-flex gap-3">
                            <a href="<?php echo base_url('shop.php?category=oils-lubricants'); ?>" class="btn btn-danger btn-lg rounded-pill">Shop Lubricants <i class="fas fa-oil-can ms-2"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-5 text-center d-none d-lg-block preserve-3d">
                        <img src="<?php echo base_url('assets/images/products/engine_oil.png'); ?>" alt="Engine Lubricant" class="img-fluid pop-out" style="max-height: 220px; object-fit: contain; filter: drop-shadow(0px 10px 15px rgba(0,0,0,0.15)); animation: float 6s ease-in-out infinite 1s;" onerror="this.onerror=null; this.src='<?php echo base_url('uploads/default_product.jpg'); ?>';">
                    </div>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
</section>

<!-- Features Highlights -->
<div class="container mb-5">
    <div class="row g-4">
        <div class="col-md-3 col-sm-6">
            <div class="feature-box">
                <i class="fas fa-shield-alt"></i>
                <h5 class="fw-bold">100% Genuine</h5>
                <p class="text-muted small mb-0">Directly sourced OEM & top racing brand components.</p>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="feature-box">
                <i class="fas fa-shipping-fast"></i>
                <h5 class="fw-bold">Fast Delivery</h5>
                <p class="text-muted small mb-0">Dispatched within 24 hours with express tracking.</p>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="feature-box">
                <i class="fas fa-undo"></i>
                <h5 class="fw-bold">Easy Returns</h5>
                <p class="text-muted small mb-0">14-day replacement guarantee on defective items.</p>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="feature-box">
                <i class="fas fa-user-headset"></i>
                <h5 class="fw-bold">Expert Support</h5>
                <p class="text-muted small mb-0">Mechanic advice for part compatibility.</p>
            </div>
        </div>
    </div>
</div>

<!-- Featured Categories Section -->
<section class="container mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-0">Browse By Category</h2>
            <p class="text-muted mb-0 small">Find the right spare part for your bike model</p>
        </div>
        <a href="<?php echo base_url('shop.php'); ?>" class="btn btn-outline-danger btn-sm rounded-pill">View All Categories <i class="fas fa-arrow-right ms-1"></i></a>
    </div>
    <div class="row g-4">
        <?php foreach ($categories as $cat): ?>
            <div class="col-lg-2 col-md-4 col-6">
                <a href="<?php echo base_url('shop.php?category=' . $cat['slug']); ?>" class="text-decoration-none">
                    <div class="category-card p-3">
                        <img src="<?php echo base_url('uploads/' . e($cat['image'])); ?>" alt="<?php echo e($cat['name']); ?>" class="img-fluid mb-3 w-100">
                        <h6 class="fw-bold text-dark text-truncate mb-1"><?php echo e($cat['name']); ?></h6>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Featured Products Section -->
<section class="container mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-0">Featured Spare Parts</h2>
            <p class="text-muted mb-0 small">Handpicked top performance parts for your bike</p>
        </div>
        <a href="<?php echo base_url('shop.php'); ?>" class="btn btn-danger btn-sm rounded-pill">Explore Shop</a>
    </div>

    <div class="row g-4">
        <?php foreach ($featured_products as $prod): ?>
            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="product-card tilt-card">
                    <div class="card-img-wrap preserve-3d">
                        <span class="badge bg-danger badge-overlay">Featured</span>
                        <img src="<?php echo base_url('uploads/' . e($prod['image'])); ?>" alt="<?php echo e($prod['name']); ?>" class="pop-out">
                    </div>
                    <div class="card-body">
                        <span class="text-muted small text-uppercase fw-medium"><?php echo e($prod['brand_name']); ?></span>
                        <a href="<?php echo base_url('product.php?slug=' . $prod['slug']); ?>" class="product-title mt-1"><?php echo e($prod['name']); ?></a>
                        
                        <div class="d-flex justify-content-between align-items-center mt-3 mb-3">
                            <span class="product-price"><?php echo format_price($prod['price']); ?></span>
                            <?php if ($prod['stock'] > 0): ?>
                                <span class="badge badge-in-stock px-2 py-1"><i class="fas fa-check-circle me-1"></i> In Stock</span>
                            <?php else: ?>
                                <span class="badge badge-out-stock px-2 py-1"><i class="fas fa-times-circle me-1"></i> Out of Stock</span>
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
</section>

<!-- Popular Products Grid Banner -->
<section class="py-5 mb-5 border-top border-bottom" style="background-color: var(--dark-elevated); border-color: var(--border-color) !important;">
    <div class="container">
        <div class="row align-items-center mb-4">
            <div class="col-md-8">
                <h2 class="fw-bold text-dark mb-1">Most Popular Among Riders</h2>
                <p class="text-muted mb-0">Top-rated items based on customer purchases</p>
            </div>
        </div>

        <div class="row g-4">
            <?php foreach ($popular_products as $prod): ?>
                <div class="col-lg-3 col-md-6">
                    <div class="product-card tilt-card">
                        <div class="card-img-wrap preserve-3d">
                            <span class="badge bg-warning text-dark badge-overlay"><i class="fas fa-fire me-1"></i> Popular</span>
                            <img src="<?php echo base_url('uploads/' . e($prod['image'])); ?>" alt="<?php echo e($prod['name']); ?>" class="pop-out">
                        </div>
                        <div class="card-body">
                            <span class="text-muted small text-uppercase fw-medium"><?php echo e($prod['brand_name']); ?></span>
                            <a href="<?php echo base_url('product.php?slug=' . $prod['slug']); ?>" class="product-title mt-1"><?php echo e($prod['name']); ?></a>
                            
                            <div class="d-flex justify-content-between align-items-center mt-3 mb-3">
                                <span class="product-price"><?php echo format_price($prod['price']); ?></span>
                            </div>

                            <div class="mt-auto">
                                <a href="<?php echo base_url('product.php?slug=' . $prod['slug']); ?>" class="btn btn-dark w-100 rounded-pill btn-sm">
                                    View Details <i class="fas fa-chevron-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Top Brands Banner -->
<section class="container mb-5">
    <h3 class="fw-bold text-dark text-center mb-4">Official Brand Partners</h3>
    <div class="row g-3 justify-content-center text-center">
        <?php foreach ($brands as $b): ?>
            <div class="col-md-2 col-4">
                <a href="<?php echo base_url('shop.php?brand=' . $b['slug']); ?>" class="card border-0 shadow-sm p-3 h-100 text-decoration-none text-light tilt-card" style="background-color: var(--dark-surface);">
                    <img src="<?php echo base_url('uploads/' . e($b['logo'])); ?>" alt="<?php echo e($b['name']); ?>" class="img-fluid rounded mb-2 pop-out" style="max-height: 60px; object-fit: contain;">
                    <span class="small fw-semibold text-truncate"><?php echo e($b['name']); ?></span>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
