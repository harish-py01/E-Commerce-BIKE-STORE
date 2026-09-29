<?php
// search.php - Dedicated search query handler
require_once __DIR__ . '/includes/functions.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

// Redirect directly to shop catalog with query parameter
header('Location: ' . base_url('shop.php?q=' . urlencode($q)));
exit();
?>
