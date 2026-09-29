<?php
// logout.php - Customer Logout Handler
require_once __DIR__ . '/includes/functions.php';

unset($_SESSION['customer_id']);
unset($_SESSION['customer_name']);
unset($_SESSION['customer_email']);

set_flash('info', 'You have been logged out.');
header('Location: ' . base_url('login.php'));
exit();
?>
