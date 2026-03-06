<?php
// LeaguesOfCode Lab Portal Configuration
// Cybersecurity Bootcamp 2026

// Database Configuration
define('DB_HOST', getenv('DB_HOST') ?: 'db');
define('DB_NAME', getenv('DB_NAME') ?: 'leaguesofcode_db');
define('DB_USER', getenv('DB_USER') ?: 'locadmin');
define('DB_PASS', getenv('DB_PASS') ?: 'locpass123');

// JWT Configuration
if (!defined('JWT_SECRET')) {
    define('JWT_SECRET', getenv('JWT_SECRET') ?: 'secret123');
}

// Application Settings
define('APP_NAME', 'LeaguesOfCode Lab Portal');
define('APP_VERSION', '1.0.0');

// Create global database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Database Connection Function
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die('Connection failed: ' . $conn->connect_error);
    }
    return $conn;
}

// PDO Connection Function
function getPDOConnection() {
    try {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME,
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        return $pdo;
    } catch (PDOException $e) {
        die('Connection failed: ' . $e->getMessage());
    }
}
?>
