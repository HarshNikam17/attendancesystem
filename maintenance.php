<?php
// maintenance.php
error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/cors.php';
header('Content-Type: application/json');

$file = __DIR__ . '/maintenance_status.json';
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $status = (bool)($data['maintenance'] ?? false);
    @file_put_contents($file, json_encode(["maintenance" => $status]));
    echo json_encode(["success" => true, "maintenance" => $status]);
    exit;
}

// GET request to check status
if (file_exists($file)) {
    $status = json_decode(file_get_contents($file), true);
    echo json_encode(["success" => true, "maintenance" => (bool)($status['maintenance'] ?? false)]);
} else {
    echo json_encode(["success" => true, "maintenance" => false]);
}
?>