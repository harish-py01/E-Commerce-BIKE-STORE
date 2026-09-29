<?php
// about.php - About Us Page
$page_title = "About Us - Bike Spare Parts E-Commerce";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-5">
    <!-- Hero Banner -->
    <div class="card border-0 shadow-lg rounded-4 p-5 bg-dark text-white mb-5 position-relative overflow-hidden">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <span class="badge bg-danger mb-2 px-3 py-2 text-uppercase">Driven By Passion</span>
                <h1 class="display-4 fw-bold mb-3">Your Trusted Partner For Premium Bike Spare Parts</h1>
                <p class="lead text-light opacity-75">We provide riders, mechanics, and motorcycle enthusiasts with 100% authentic OEM components, high performance braking systems, and racing accessories.</p>
            </div>
            <div class="col-lg-4 text-center d-none d-lg-block">
                <i class="fas fa-motorcycle text-danger fa-8x opacity-75"></i>
            </div>
        </div>
    </div>

    <!-- Core Values -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 text-center h-100">
                <div class="mb-3"><i class="fas fa-certificate text-danger fa-3x"></i></div>
                <h4 class="fw-bold">Guaranteed OEM Quality</h4>
                <p class="text-muted small">Every part in our inventory undergoes strict quality assurance tests to ensure maximum performance and longevity on the road.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 text-center h-100">
                <div class="mb-3"><i class="fas fa-warehouse text-danger fa-3x"></i></div>
                <h4 class="fw-bold">Extensive Inventory</h4>
                <p class="text-muted small">From engine pistons to drive chain sprockets, brake discs, and synthetic lubricants, we stock thousands of active SKUs.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 text-center h-100">
                <div class="mb-3"><i class="fas fa-truck-monster text-danger fa-3x"></i></div>
                <h4 class="fw-bold">Nationwide Shipping</h4>
                <p class="text-muted small">We partner with top logistics providers to ensure fast, reliable delivery right to your doorstep or garage.</p>
            </div>
        </div>
    </div>

    <!-- Project Information -->
    <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white mb-5">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h3 class="fw-bold text-dark mb-3">Project Metadata</h3>
                <table class="table table-borderless small">
                    <tr><td class="fw-bold text-secondary" style="width: 180px;">Project Title:</td> <td>Bike Spare Parts E-Commerce Website</td></tr>
                    <tr><td class="fw-bold text-secondary">Project Type:</td> <td>MCA Mini Project</td></tr>
                    <tr><td class="fw-bold text-secondary">Author:</td> <td>Grish A</td></tr>
                    <tr><td class="fw-bold text-secondary">Technology Stack:</td> <td>Core PHP 8 (mysqli), MySQL, Bootstrap 5.3, JavaScript, HTML5, CSS3</td></tr>
                    <tr><td class="fw-bold text-secondary">Server Compatibility:</td> <td>Apache & MySQL (XAMPP Compatible)</td></tr>
                </table>
            </div>
            <div class="col-md-4 text-center">
                <div class="p-4 bg-light rounded-4 border">
                    <i class="fas fa-graduation-cap text-danger fa-4x mb-2"></i>
                    <h5 class="fw-bold mb-1">Academic Submission</h5>
                    <p class="text-muted small mb-0">Master of Computer Applications</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
