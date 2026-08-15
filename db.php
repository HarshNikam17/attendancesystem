<?php
// db.php - Central Database Configuration and PDO / MySQLi connection
require_once __DIR__ . '/cors.php';

$host = getenv('DB_HOST') ?: 'localhost';
$port = getenv('DB_PORT') ?: '3306';
$db   = getenv('DB_NAME') ?: 'eduprom_db';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : (getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '');
$charset = 'utf8mb4';

// PDO Connection
$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // If DB doesn't exist yet, PDO connection might fail until created
    $pdo = null;
}

// MySQLi Connection Helper
function get_mysqli_conn() {
    global $host, $user, $pass, $db, $port;
    $conn = @new mysqli($host, $user, $pass, $db, (int)$port);
    return $conn;
}

// Global default mysqli instance
$conn = @new mysqli($host, $user, $pass, $db, (int)$port);
?>