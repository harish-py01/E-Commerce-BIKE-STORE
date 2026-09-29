<?php
// includes/db.php
// Database Connection file using mysqli

if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');
if (!defined('DB_NAME')) define('DB_NAME', 'bike_store');
if (!defined('DB_PORT')) define('DB_PORT', 3306);

// Report all mysqli errors as exceptions for easier debugging
mysqli_report(MYSQLI_REPORT_OFF);

$conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

if ($conn->connect_error) {
    die("
    <div style='font-family: Arial, sans-serif; background: #fff5f5; color: #c53030; padding: 25px; border-radius: 8px; max-width: 600px; margin: 50px auto; border: 1px solid #feb2b2; box-shadow: 0 4px 12px rgba(0,0,0,0.1);'>
        <h2 style='margin-top:0;'>⚠️ Database Connection Failed</h2>
        <p><strong>Error:</strong> " . htmlspecialchars($conn->connect_error) . "</p>
        <hr style='border: none; border-top: 1px solid #feb2b2; margin: 15px 0;'>
        <h4>Quick Fix Instructions for XAMPP:</h4>
        <ol style='line-height: 1.6;'>
            <li>Open <strong>XAMPP Control Panel</strong> and click <strong>Start</strong> next to <em>Apache</em> and <em>MySQL</em>.</li>
            <li>Go to <a href='http://localhost/phpmyadmin' target='_blank' style='color: #2b6cb0;'>http://localhost/phpmyadmin</a>.</li>
            <li>Create a database named <code>bike_store</code>.</li>
            <li>Import the <code>bike_store.sql</code> file provided in the project root.</li>
        </ol>
    </div>
    ");
}

$conn->set_charset("utf8mb4");
?>
