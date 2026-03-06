<?php
// Database Configuration
$db_host = getenv('DB_HOST') ?: 'db';
$db_name = getenv('DB_NAME') ?: 'leaguesofcode_db';
$db_user = getenv('DB_USER') ?: 'locadmin';
$db_pass = getenv('DB_PASS') ?: 'locpass123';

// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

// Check connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Set charset
mysqli_set_charset($conn, "utf8mb4");

// JWT Secret (for jwt labs)
define('JWT_SECRET', getenv('JWT_SECRET') ?: 'secret123');
?>
