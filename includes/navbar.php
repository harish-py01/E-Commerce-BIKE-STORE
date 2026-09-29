<?php
// includes/navbar.php
require_once __DIR__ . '/functions.php';

// Fetch active categories for dropdown
$cat_query = "SELECT name, slug FROM categories WHERE status = 1 ORDER BY name ASC";
$cat_result = $conn->query($cat_query);
$categories_nav = [];
if ($cat_result) {
    while ($row = $cat_result->fetch_assoc()) {
        $categories_nav[] = $row;
    }
}
$cart_count = get_cart_count();
?>

<!-- Top Announcement Bar -->
<div class="bg-dark text-light py-1 font-sans small d-none d-md-block">
    <div class="container d-flex justify-content-between align-items-center">
        <div>
            <i class="fas fa-truck me-2 text-danger"></i> Free Shipping on orders over ₹2,999 | <i class="fas fa-headset ms-2 me-1 text-danger"></i> Support: +91 98765 43210
        </div>
        <div>
            <?php if (is_admin_logged_in()): ?>
                <a href="<?php echo base_url('admin/dashboard.php'); ?>" class="text-warning text-decoration-none me-3"><i class="fas fa-user-shield me-1"></i> Admin Panel</a>
            <?php endif; ?>
            <a href="<?php echo base_url('contact.php'); ?>" class="text-light text-decoration-none me-3"><i class="fas fa-envelope me-1"></i> Help</a>
        </div>
    </div>
</div>

<!-- Main Header Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark sticky-top shadow-sm py-2 glassmorphism">
    <div class="container">
        <!-- Back Navigation Icon -->
        <a href="javascript:history.back()" class="text-light me-3 text-decoration-none nav-back-btn" title="Go Back">
            <i class="fas fa-arrow-left fa-lg"></i>
        </a>

        <!-- Brand Logo -->
        <a class="navbar-brand d-flex align-items-center" href="<?php echo base_url('index.php'); ?>">
            <img src="<?php echo base_url('assets/images/logo.svg'); ?>" alt="Bike Store Logo" height="42">
        </a>

        <!-- Mobile Toggler -->
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMain">
            <!-- Nav Links -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo base_url('index.php'); ?>"><i class="fas fa-home me-1"></i> Home</a>
                </li>
                
                <!-- Category Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="catDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-th-large me-1"></i> Categories
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark shadow" aria-labelledby="catDropdown">
                        <li><a class="dropdown-item" href="<?php echo base_url('shop.php'); ?>">All Categories</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <?php foreach ($categories_nav as $cat): ?>
                            <li><a class="dropdown-item" href="<?php echo base_url('shop.php?category=' . $cat['slug']); ?>"><?php echo e($cat['name']); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="<?php echo base_url('shop.php'); ?>"><i class="fas fa-store me-1"></i> Shop Parts</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo base_url('about.php'); ?>"><i class="fas fa-info-circle me-1"></i> About Us</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo base_url('contact.php'); ?>"><i class="fas fa-phone-alt me-1"></i> Contact</a>
                </li>
            </ul>

            <!-- Search Form -->
            <form class="d-flex me-3 position-relative my-2 my-lg-0" action="<?php echo base_url('search.php'); ?>" method="GET">
                <input class="form-control form-control-sm rounded-pill px-3 pe-5" type="search" name="q" placeholder="Search brake pads, oil..." aria-label="Search" value="<?php echo e($_GET['q'] ?? ''); ?>" required>
                <button class="btn btn-sm text-danger position-absolute end-0 top-50 translate-middle-y me-2 border-0 bg-transparent" type="submit">
                    <i class="fas fa-search"></i>
                </button>
            </form>

            <!-- Actions (Cart & User) -->
            <div class="d-flex align-items-center">
                <!-- Cart Button -->
                <a href="<?php echo base_url('cart.php'); ?>" class="btn btn-outline-light btn-sm rounded-pill me-3 position-relative px-3 py-1">
                    <i class="fas fa-shopping-cart text-danger me-1"></i> Cart
                    <span class="badge bg-danger rounded-pill cart-badge" id="cartCountBadge"><?php echo $cart_count; ?></span>
                </a>

                <!-- User Account Dropdown -->
                <?php if (is_customer_logged_in()): ?>
                    <div class="dropdown">
                        <a class="btn btn-danger btn-sm dropdown-toggle rounded-pill px-3 py-1" href="#" role="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle me-1"></i> Account
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow" aria-labelledby="userMenu">
                            <li class="dropdown-header text-muted">Hello, <?php echo e($_SESSION['customer_name'] ?? 'Rider'); ?></li>
                            <li><a class="dropdown-item" href="<?php echo base_url('profile.php'); ?>"><i class="fas fa-user-edit me-2"></i> My Profile</a></li>
                            <li><a class="dropdown-item" href="<?php echo base_url('my_orders.php'); ?>"><i class="fas fa-box me-2"></i> My Orders</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?php echo base_url('logout.php'); ?>"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="<?php echo base_url('login.php'); ?>" class="btn btn-outline-danger btn-sm rounded-pill px-3 me-2">Login</a>
                    <a href="<?php echo base_url('register.php'); ?>" class="btn btn-danger btn-sm rounded-pill px-3">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
