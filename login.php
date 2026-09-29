<?php
// login.php - Customer Account Login
require_once __DIR__ . '/includes/functions.php';

if (is_customer_logged_in()) {
    header('Location: ' . base_url('index.php'));
    exit();
}

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf_token)) {
        header('Location: ' . base_url('login.php'));
        exit();
    }

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $errors[] = "Please fill in all credentials.";
    } else {
        $stmt = $conn->prepare("SELECT id, name, email, password FROM customers WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user && (password_verify($password, $user['password']) || $password === $user['password'])) {
            $_SESSION['customer_id'] = $user['id'];
            $_SESSION['customer_name'] = $user['name'];
            $_SESSION['customer_email'] = $user['email'];

            // Sync session cart items
            sync_cart_after_login($user['id']);

            set_flash('success', 'Welcome back, ' . e($user['name']) . '!');
            header('Location: ' . base_url('index.php'));
            exit();
        } else {
            $errors[] = "Invalid email or password combination.";
        }
    }
}

$page_title = "Customer Login - Bike Store";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
            <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 text-light tilt-card" style="background-color: var(--dark-surface);">
                <div class="text-center mb-4">
                    <div class="mb-2">
                        <i class="fas fa-user-circle text-danger" style="font-size: 3.5rem;"></i>
                    </div>
                    <h3 class="fw-bold text-white">Customer Login</h3>
                    <p class="text-muted small">Sign in to manage your orders & profile.</p>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger shadow-sm rounded-3 mb-4">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $err): ?>
                                <li><?php echo e($err); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="<?php echo base_url('login.php'); ?>" method="POST">
                    <?php echo csrf_field(); ?>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text border-secondary" style="background-color: var(--dark-elevated);"><i class="fas fa-envelope text-muted"></i></span>
                            <input type="email" name="email" class="form-control text-light border-secondary" style="background-color: var(--dark-color);" value="<?php echo e($email); ?>" placeholder="rahul@example.com" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-semibold text-secondary mb-0">Password</label>
                            <a href="<?php echo base_url('forgot_password.php'); ?>" class="small text-danger text-decoration-none">Forgot password?</a>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text border-secondary" style="background-color: var(--dark-elevated);"><i class="fas fa-lock text-muted"></i></span>
                            <input type="password" name="password" class="form-control text-light border-secondary" style="background-color: var(--dark-color);" placeholder="••••••••" required>
                        </div>
                    </div>

                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-danger btn-lg rounded-pill fw-bold">
                            <i class="fas fa-sign-in-alt me-2"></i> Log In
                        </button>
                    </div>

                    <div class="text-center small text-muted">
                        Don't have an account? <a href="<?php echo base_url('register.php'); ?>" class="text-danger fw-semibold">Register here</a>
                    </div>
                </form>

                <div class="mt-4 pt-3 border-top text-center small text-muted">
                    <p class="mb-1"><strong>Demo Customer Login:</strong></p>
                    <p class="mb-0">Email: <code>rahul@example.com</code> | Password: <code>user123</code></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
