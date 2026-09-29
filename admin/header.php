<?php
// admin/header.php
require_once __DIR__ . '/../includes/functions.php';

require_admin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? e($page_title) . ' - Admin Panel' : 'Admin Panel - Bike Store'; ?></title>
    
    <!-- Google Fonts (Poppins) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- Admin Dark Theme CSS -->
    <link rel="stylesheet" href="<?php echo base_url('assets/css/admin.css'); ?>">
</head>
<body class="admin-body">

<div class="d-flex" id="wrapper">
    <!-- Sidebar -->
    <?php require_once __DIR__ . '/sidebar.php'; ?>

    <!-- Page Content -->
    <div id="page-content-wrapper">
        <!-- Top Navigation Bar -->
        <nav class="navbar navbar-expand-lg admin-navbar">
            <div class="container-fluid">
                <button class="btn btn-outline-light btn-sm me-3" id="menu-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                <span class="navbar-brand text-white fw-bold mb-0 me-auto">Bike Store Admin Panel</span>

                <div class="d-flex align-items-center gap-3">
                    <a href="<?php echo base_url('index.php'); ?>" target="_blank" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                        <i class="fas fa-external-link-alt me-1"></i> View Storefront
                    </a>
                    
                    <div class="dropdown">
                        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="adminUserDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-shield me-2 text-danger fs-5"></i>
                            <span class="fw-semibold small"><?php echo e($_SESSION['admin_name'] ?? 'Admin'); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow" aria-labelledby="adminUserDropdown">
                            <li><span class="dropdown-item-text text-muted small">Role: Super Admin</span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?php echo base_url('admin/logout.php'); ?>"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content Body -->
        <div class="container-fluid p-4">
            <?php display_flash(); ?>
