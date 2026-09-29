<?php
// 404.php - Page Not Found
$page_title = "404 - Page Not Found";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-5 text-center py-5">
    <div class="card border-0 shadow-sm rounded-4 p-5 max-w-600 mx-auto bg-white">
        <h1 class="display-1 fw-bold text-danger">404</h1>
        <h3 class="fw-bold text-dark mb-3">Page Not Found</h3>
        <p class="text-muted mb-4">The page or spare part product you are looking for does not exist or has been moved.</p>
        <div>
            <a href="<?php echo base_url('index.php'); ?>" class="btn btn-danger btn-lg rounded-pill px-5 fw-bold">
                <i class="fas fa-home me-2"></i> Back To Homepage
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
