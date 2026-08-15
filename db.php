<?php
$host = 'localhost';
$db   = 'edupro_attendance';
$user = 'root'; // XAMPP default username is usually 'root'
$pass = '';     // XAMPP default password is usually blank
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
     
} catch (\PDOException $e) {
     echo "Connection failed: " . $e->getMessage();
}
?>