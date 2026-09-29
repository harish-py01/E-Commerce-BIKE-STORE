<?php
// admin/sidebar.php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div id="sidebar-wrapper">
    <div class="sidebar-heading">
        <i class="fas fa-motorcycle text-danger me-2"></i> BIKE<span class="text-danger">STORE</span>
    </div>
    
    <div class="list-group list-group-flush mt-2">
        <a href="<?php echo base_url('admin/dashboard.php'); ?>" class="list-group-item <?php echo ($current_page === 'dashboard.php') ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        
        <a href="<?php echo base_url('admin/products.php'); ?>" class="list-group-item <?php echo (in_array($current_page, ['products.php', 'add_product.php', 'edit_product.php'])) ? 'active' : ''; ?>">
            <i class="fas fa-cubes"></i> Products
        </a>
        
        <a href="<?php echo base_url('admin/categories.php'); ?>" class="list-group-item <?php echo (in_array($current_page, ['categories.php', 'add_category.php', 'edit_category.php'])) ? 'active' : ''; ?>">
            <i class="fas fa-tags"></i> Categories
        </a>
        
        <a href="<?php echo base_url('admin/brands.php'); ?>" class="list-group-item <?php echo (in_array($current_page, ['brands.php', 'add_brand.php', 'edit_brand.php'])) ? 'active' : ''; ?>">
            <i class="fas fa-copyright"></i> Brands
        </a>
        
        <a href="<?php echo base_url('admin/orders.php'); ?>" class="list-group-item <?php echo (in_array($current_page, ['orders.php', 'order_details.php'])) ? 'active' : ''; ?>">
            <i class="fas fa-shopping-bag"></i> Orders
        </a>
        
        <a href="<?php echo base_url('admin/customers.php'); ?>" class="list-group-item <?php echo (in_array($current_page, ['customers.php', 'customer_details.php'])) ? 'active' : ''; ?>">
            <i class="fas fa-users"></i> Customers
        </a>
        
        <a href="<?php echo base_url('admin/reports.php'); ?>" class="list-group-item <?php echo ($current_page === 'reports.php') ? 'active' : ''; ?>">
            <i class="fas fa-chart-line"></i> Analytics & Reports
        </a>
        
        <a href="<?php echo base_url('admin/product_images.php'); ?>" class="list-group-item <?php echo ($current_page === 'product_images.php') ? 'active' : ''; ?>">
            <i class="fas fa-image"></i> Product Image Manager
        </a>
        
        <hr class="border-secondary my-3">
        
        <a href="<?php echo base_url('admin/logout.php'); ?>" class="list-group-item text-danger">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>
