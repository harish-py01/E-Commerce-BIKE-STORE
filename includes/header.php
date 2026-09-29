<?php
// includes/header.php
require_once __DIR__ . '/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? e($page_title) . ' - Bike Store' : 'Bike Spare Parts E-Commerce Website'; ?></title>
    
    <!-- Meta tags for SEO -->
    <meta name="description" content="High performance motorcycle spare parts, engine components, braking systems, and genuine accessories online.">
    <meta name="author" content="Grish A">

    <!-- Google Fonts (Poppins) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link href="<?php echo base_url('assets/css/vendor/bootstrap.min.css'); ?>" rel="stylesheet">

    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="<?php echo base_url('assets/css/vendor/all.min.css'); ?>">

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="<?php echo base_url('assets/css/vendor/sweetalert2.min.css'); ?>">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo base_url('assets/css/style.css'); ?>">
</head>
<body>

<?php require_once __DIR__ . '/navbar.php'; ?>

<!-- Main Container / Flash Alerts -->
<div class="container mt-3">
    <?php display_flash(); ?>
</div>
