<?php
error_reporting(0);
header('Content-Type: application/json');

$file = 'maintenance_status.json';
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $status = $data['maintenance'] ?? false;
    file_put_contents($file, json_encode(["maintenance" => $status]));
    echo json_encode(["success" => true, "maintenance" => $status]);
    exit;
}

// GET request to check status
if (file_exists($file)) {
    $status = json_decode(file_get_contents($file), true);
    echo json_encode(["success" => true, "maintenance" => $status['maintenance'] ?? false]);
} else {
    echo json_encode(["success" => true, "maintenance" => false]);
}
?>