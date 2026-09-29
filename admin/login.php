<?php
// admin/login.php - Admin Authentication Page
require_once __DIR__ . '/../includes/functions.php';

if (is_admin_logged_in()) {
    header('Location: ' . base_url('admin/dashboard.php'));
    exit();
}

$errors = [];
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf_token)) {
        header('Location: ' . base_url('admin/login.php'));
        exit();
    }

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $errors[] = "Username and password are required.";
    } else {
        $stmt = $conn->prepare("SELECT id, username, full_name, password FROM admin WHERE username = ? OR email = ? LIMIT 1");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $admin = $stmt->get_result()->fetch_assoc();

        if ($admin && (password_verify($password, $admin['password']) || $password === $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_name'] = $admin['full_name'];

            set_flash('success', 'Logged in successfully as Administrator.');
            header('Location: ' . base_url('admin/dashboard.php'));
            exit();
        } else {
            $errors[] = "Invalid administrator credentials.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Bike Store</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom Admin CSS -->
    <link rel="stylesheet" href="<?php echo base_url('assets/css/admin.css'); ?>">
</head>
<body class="admin-login-wrapper">

<div class="admin-login-card">
    <div class="text-center mb-4">
        <i class="fas fa-user-shield text-danger fa-3x mb-3"></i>
        <h4 class="fw-bold text-white mb-1">Admin Portal</h4>
        <p class="text-muted small">Bike Spare Parts Management System</p>
    </div>

    <?php display_flash(); ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger p-2.5 rounded-3 mb-3 small">
            <ul class="mb-0 ps-3">
                <?php foreach ($errors as $err): ?>
                    <li><?php echo e($err); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="<?php echo base_url('admin/login.php'); ?>" method="POST" class="admin-card p-0 border-0">
        <?php echo csrf_field(); ?>

        <div class="mb-3">
            <label class="form-label text-muted small fw-semibold">Username or Email</label>
            <div class="input-group">
                <span class="input-group-text bg-dark border-secondary text-secondary"><i class="fas fa-user"></i></span>
                <input type="text" name="username" class="form-control" value="<?php echo e($username); ?>" placeholder="admin" required autocomplete="username">
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label text-muted small fw-semibold">Password</label>
            <div class="input-group">
                <span class="input-group-text bg-dark border-secondary text-secondary"><i class="fas fa-lock"></i></span>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required autocomplete="current-password">
            </div>
        </div>

        <div class="d-grid mb-3">
            <button type="submit" class="btn btn-danger btn-lg rounded-pill fw-bold">
                <i class="fas fa-sign-in-alt me-2"></i> Log In to Dashboard
            </button>
        </div>

        <div class="text-center">
            <a href="<?php echo base_url('index.php'); ?>" class="text-muted small text-decoration-none"><i class="fas fa-arrow-left me-1"></i> Return to Main Storefront</a>
        </div>
    </form>

    <div class="mt-4 pt-3 border-top border-secondary text-center text-muted small">
        <p class="mb-1"><strong>Default Admin Credentials:</strong></p>
        <p class="mb-0">Username: <code>admin</code> | Password: <code>admin123</code></p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
