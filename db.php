<?php
// ============================================================
// config/db.php — Database Connection
// Uses MySQLi with error handling.
// Change DB_USER / DB_PASS to match your XAMPP setup.
// ============================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // default XAMPP user
define('DB_PASS', '');           // default XAMPP password (empty)
define('DB_NAME', 'period_tracker');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    // In production, log this error instead of displaying it
    die('<div style="font-family:sans-serif;color:#c0392b;padding:20px;">
        <h3>⚠️ Database Connection Failed</h3>
        <p>Error: ' . htmlspecialchars($conn->connect_error) . '</p>
        <p>Please ensure XAMPP MySQL is running and the database <strong>period_tracker</strong> exists.</p>
    </div>');
}

// Set charset to utf8mb4 for full unicode support
$conn->set_charset('utf8mb4');
