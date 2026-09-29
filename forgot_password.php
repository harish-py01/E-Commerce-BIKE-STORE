<?php
// forgot_password.php - Forgot Password Request
require_once __DIR__ . '/includes/functions.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "If an account exists for " . e($email) . ", a password reset link has been sent to your inbox.";
    } else {
        $message = "Please enter a valid email address.";
    }
}

$page_title = "Forgot Password - Bike Store";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">
            <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 bg-white text-center">
                <i class="fas fa-key text-danger fa-3x mb-3"></i>
                <h3 class="fw-bold text-dark mb-2">Forgot Password?</h3>
                <p class="text-muted small mb-4">Enter your registered email address to receive password reset instructions.</p>

                <?php if (!empty($message)): ?>
                    <div class="alert alert-info shadow-sm rounded-3 mb-4">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <form action="<?php echo base_url('forgot_password.php'); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="mb-4 text-start">
                        <label class="form-label fw-semibold text-secondary">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="name@example.com" required>
                    </div>
                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-danger btn-lg rounded-pill fw-bold">Send Reset Link</button>
                    </div>
                    <a href="<?php echo base_url('login.php'); ?>" class="text-decoration-none small text-muted"><i class="fas fa-arrow-left me-1"></i> Back to Login</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
