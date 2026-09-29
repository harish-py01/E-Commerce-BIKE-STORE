<?php
// includes/config.php
// Environment and API Configuration File
// DO NOT commit this file to version control (add to .gitignore)

// --- IMAGE SEARCH API CONFIGURATION ---
// Set to 'google' or 'serpapi' or 'duckduckgo_html'
if (!defined('IMAGE_SEARCH_PROVIDER')) define('IMAGE_SEARCH_PROVIDER', 'duckduckgo_html');

// API Keys (Leave blank if using 'duckduckgo_html')
if (!defined('IMAGE_SEARCH_API_KEY')) define('IMAGE_SEARCH_API_KEY', '');
if (!defined('IMAGE_SEARCH_ENGINE_ID')) define('IMAGE_SEARCH_ENGINE_ID', '');

// --- UPLOAD CONFIGURATION ---
if (!defined('UPLOAD_PATH')) define('UPLOAD_PATH', __DIR__ . '/../uploads/products/');
