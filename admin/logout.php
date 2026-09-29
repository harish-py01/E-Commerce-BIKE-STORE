<?php
// admin/logout.php - Admin Logout Handler
require_once __DIR__ . '/../includes/functions.php';

unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);
unset($_SESSION['admin_name']);

set_flash('info', 'Admin logged out safely.');
header('Location: ' . base_url('admin/login.php'));
exit();
?>
