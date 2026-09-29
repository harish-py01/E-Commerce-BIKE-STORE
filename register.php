<?php
// register.php - Customer Account Registration
require_once __DIR__ . '/includes/functions.php';

if (is_customer_logged_in()) {
    header('Location: ' . base_url('index.php'));
    exit();
}

$errors = [];
$name = '';
$email = '';
$phone = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf_token)) {
        header('Location: ' . base_url('register.php'));
        exit();
    }

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($name)) $errors[] = "Full Name is required.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email address is required.";
    if (empty($password) || strlen($password) < 6) $errors[] = "Password must be at least 6 characters long.";
    if ($password !== $confirm_password) $errors[] = "Passwords do not match.";

    if (empty($errors)) {
        // Check duplicate email
        $stmt_check = $conn->prepare("SELECT id FROM customers WHERE email = ? LIMIT 1");
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        if ($stmt_check->get_result()->num_rows > 0) {
            $errors[] = "An account with this email address already exists.";
        } else {
            // Hash Password
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            
            $stmt_ins = $conn->prepare("INSERT INTO customers (name, email, password, phone) VALUES (?, ?, ?, ?)");
            $stmt_ins->bind_param("ssss", $name, $email, $hashed_password, $phone);

            if ($stmt_ins->execute()) {
                $customer_id = $stmt_ins->insert_id;
                $_SESSION['customer_id'] = $customer_id;
                $_SESSION['customer_name'] = $name;
                $_SESSION['customer_email'] = $email;

                // Sync session cart items
                sync_cart_after_login($customer_id);

                set_flash('success', 'Registration successful! Welcome to Bike Store.');
                header('Location: ' . base_url('index.php'));
                exit();
            } else {
                $errors[] = "Database error: Could not register user.";
            }
        }
    }
}

$page_title = "Customer Registration - Bike Store";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
            <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 text-light tilt-card" style="background-color: var(--dark-surface);">
                <div class="text-center mb-4">
                    <h3 class="fw-bold text-white">Create An Account</h3>
                    <p class="text-muted small">Join thousands of riders getting authentic spare parts delivered fast.</p>
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

                <form action="<?php echo base_url('register.php'); ?>" method="POST">
                    <?php echo csrf_field(); ?>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Full Name *</label>
                        <div class="input-group">
                            <span class="input-group-text border-secondary" style="background-color: var(--dark-elevated);"><i class="fas fa-user text-muted"></i></span>
                            <input type="text" name="name" class="form-control text-light border-secondary" style="background-color: var(--dark-color);" value="<?php echo e($name); ?>" placeholder="e.g. Rahul Sharma" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Email Address *</label>
                        <div class="input-group">
                            <span class="input-group-text border-secondary" style="background-color: var(--dark-elevated);"><i class="fas fa-envelope text-muted"></i></span>
                            <input type="email" name="email" class="form-control text-light border-secondary" style="background-color: var(--dark-color);" value="<?php echo e($email); ?>" placeholder="name@example.com" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Phone Number</label>
                        <div class="input-group">
                            <span class="input-group-text border-secondary" style="background-color: var(--dark-elevated);"><i class="fas fa-phone text-muted"></i></span>
                            <input type="text" name="phone" class="form-control text-light border-secondary" style="background-color: var(--dark-color);" value="<?php echo e($phone); ?>" placeholder="10-digit phone number">
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Password *</label>
                            <input type="password" name="password" class="form-control text-light border-secondary" style="background-color: var(--dark-color);" placeholder="At least 6 chars" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Confirm Password *</label>
                            <input type="password" name="confirm_password" class="form-control text-light border-secondary" style="background-color: var(--dark-color);" placeholder="Re-type password" required>
                        </div>
                    </div>

                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-danger btn-lg rounded-pill fw-bold">
                            <i class="fas fa-user-plus me-2"></i> Register Account
                        </button>
                    </div>

                    <div class="text-center small text-muted">
                        Already registered? <a href="<?php echo base_url('login.php'); ?>" class="text-danger fw-semibold">Login here</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
