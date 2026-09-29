<?php
// includes/smtp_config.php
// SMTP Configuration for sending emails

// SMTP Server Settings
define('SMTP_HOST', 'smtp.example.com');     // Specify main and backup SMTP servers
define('SMTP_USER', 'your_smtp_username');   // SMTP username
define('SMTP_PASS', 'your_smtp_password');   // SMTP password
define('SMTP_PORT', 587);                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
define('SMTP_ENCRYPTION', 'tls');            // Enable TLS encryption, `ssl` also accepted

// Sender Settings
define('MAIL_FROM_ADDRESS', 'sales@bikestore.com');
define('MAIL_FROM_NAME', 'Bike Store Support');
