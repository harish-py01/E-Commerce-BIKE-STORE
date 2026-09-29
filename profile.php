<?php
// profile.php - Customer Profile Management
require_once __DIR__ . '/includes/functions.php';

require_customer();

$customer_id = get_customer_id();

// Fetch Customer Record
$stmt = $conn->prepare("SELECT * FROM customers WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$customer = $stmt->get_result()->fetch_assoc();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf_token)) {
        header('Location: ' . base_url('profile.php'));
        exit();
    }

    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $zip_code = trim($_POST['zip_code'] ?? '');
    $new_password = $_POST['new_password'] ?? '';

    if (empty($name)) $errors[] = "Full Name cannot be empty.";

    if (empty($errors)) {
        if (!empty($new_password)) {
            if (strlen($new_password) < 6) {
                $errors[] = "New password must be at least 6 characters long.";
            } else {
                $hashed = password_hash($new_password, PASSWORD_BCRYPT);
                $upd = $conn->prepare("UPDATE customers SET name = ?, phone = ?, address = ?, city = ?, state = ?, zip_code = ?, password = ? WHERE id = ?");
                $upd->bind_param("sssssssi", $name, $phone, $address, $city, $state, $zip_code, $hashed, $customer_id);
                $upd->execute();
            }
        } else {
            $upd = $conn->prepare("UPDATE customers SET name = ?, phone = ?, address = ?, city = ?, state = ?, zip_code = ? WHERE id = ?");
            $upd->bind_param("ssssssi", $name, $phone, $address, $city, $state, $zip_code, $customer_id);
            $upd->execute();
        }

        if (empty($errors)) {
            $_SESSION['customer_name'] = $name;
            set_flash('success', 'Profile details updated successfully!');
            header('Location: ' . base_url('profile.php'));
            exit();
        }
    }
}

$page_title = "My Profile - Bike Store";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb p-3 rounded-3 shadow-sm" style="background-color: var(--dark-elevated);">
            <li class="breadcrumb-item"><a href="<?php echo base_url('index.php'); ?>" class="text-decoration-none text-muted"><i class="fas fa-home"></i> Home</a></li>
            <li class="breadcrumb-item active text-danger fw-semibold" aria-current="page">My Profile</li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Profile Navigation Sidebar -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 text-center mb-4 text-light" style="background-color: var(--dark-surface);">
                <div class="mb-3">
                    <i class="fas fa-user-circle text-danger" style="font-size: 5rem;"></i>
                </div>
                <h5 class="fw-bold text-white mb-1"><?php echo e($customer['name']); ?></h5>
                <p class="text-muted small mb-3"><?php echo e($customer['email']); ?></p>

                <div class="list-group list-group-flush text-start small border border-secondary rounded-3">
                    <a href="<?php echo base_url('profile.php'); ?>" class="list-group-item list-group-item-action active bg-danger border-danger"><i class="fas fa-user me-2"></i> Account Details</a>
                    <a href="<?php echo base_url('my_orders.php'); ?>" class="list-group-item list-group-item-action text-light" style="background-color: var(--dark-elevated);"><i class="fas fa-box me-2"></i> My Order History</a>
                    <a href="<?php echo base_url('logout.php'); ?>" class="list-group-item list-group-item-action text-danger" style="background-color: var(--dark-elevated);"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
                </div>
            </div>
        </div>

        <!-- Edit Profile Form -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 text-light" style="background-color: var(--dark-surface);">
                <h4 class="fw-bold text-white border-bottom border-secondary pb-3 mb-4"><i class="fas fa-user-edit text-danger me-2"></i> Edit Account Profile</h4>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger shadow-sm rounded-3 mb-4">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $err): ?>
                                <li><?php echo e($err); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="<?php echo base_url('profile.php'); ?>" method="POST">
                    <?php echo csrf_field(); ?>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Full Name *</label>
                            <input type="text" name="name" class="form-control text-light border-secondary" style="background-color: var(--dark-color);" value="<?php echo e($customer['name']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Email Address (Read Only)</label>
                            <input type="email" class="form-control border-secondary" style="background-color: var(--dark-elevated); color: var(--text-muted);" value="<?php echo e($customer['email']); ?>" readonly>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Phone Number</label>
                            <input type="text" name="phone" class="form-control text-light border-secondary" style="background-color: var(--dark-color);" value="<?php echo e($customer['phone']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">City</label>
                            <input type="text" name="city" class="form-control text-light border-secondary" style="background-color: var(--dark-color);" value="<?php echo e($customer['city']); ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Street Address</label>
                        <textarea name="address" class="form-control text-light border-secondary" style="background-color: var(--dark-color);" rows="2"><?php echo e($customer['address']); ?></textarea>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">State</label>
                            <input type="text" name="state" class="form-control text-light border-secondary" style="background-color: var(--dark-color);" value="<?php echo e($customer['state']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Zip / Pincode</label>
                            <input type="text" name="zip_code" class="form-control text-light border-secondary" style="background-color: var(--dark-color);" value="<?php echo e($customer['zip_code']); ?>">
                        </div>
                    </div>

                    <hr class="my-4 border-secondary">

                    <h5 class="fw-bold text-white mb-3">Change Password (Optional)</h5>
                    <div class="mb-4">
                        <label class="form-label fw-semibold text-secondary">New Password</label>
                        <input type="password" name="new_password" class="form-control text-light border-secondary" style="background-color: var(--dark-color);" placeholder="Leave blank to keep current password">
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-danger btn-lg rounded-pill px-5 fw-bold">
                            <i class="fas fa-save me-2"></i> Save Profile Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
