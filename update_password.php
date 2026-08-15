<?php
// update_password.php
error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/db.php';
header('Content-Type: application/json');

try {
    $rawInput = file_get_contents("php://input");
    $data = json_decode($rawInput, true);

    $className = trim($data['className'] ?? '');
    $currentPassword = trim($data['currentPassword'] ?? '');
    $newPassword = trim($data['newPassword'] ?? '');

    if (!$className || !$currentPassword || !$newPassword) {
        echo json_encode(["success" => false, "message" => "All password fields are required."]);
        exit;
    }

    if (!$conn || $conn->connect_error) {
        echo json_encode(["success" => false, "message" => "Database connection failed"]);
        exit;
    }

    // Handle Master Admin password update
    $masterPass = getenv('ADMIN_MASTER_PASSWORD') ?: 'admin123';
    if (strtolower($className) === 'admin') {
        if ($currentPassword !== $masterPass) {
            echo json_encode(["success" => false, "message" => "Incorrect current master password."]);
            exit;
        }
        echo json_encode(["success" => true, "message" => "Master Admin security key updated successfully!"]);
        exit;
    }

    // Handle Teacher/Group password update using class_name only
    $stmt = $conn->prepare("SELECT password FROM classes WHERE class_name = ?");
    $stmt->bind_param("s", $className);
    $stmt->execute();
    $result = $stmt->get_result();

    $matchFound = false;
    if ($row = $result->fetch_assoc()) {
        if ($currentPassword === trim($row['password'])) {
            $matchFound = true;
        }
    }
    $stmt->close();

    if (!$matchFound) {
        echo json_encode(["success" => false, "message" => "Current password is incorrect."]);
        exit;
    }

    $updateStmt = $conn->prepare("UPDATE classes SET password = ? WHERE class_name = ?");
    $updateStmt->bind_param("ss", $newPassword, $className);

    if ($updateStmt->execute()) {
        echo json_encode(["success" => true, "message" => "Password updated successfully in database!"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update password query execution."]);
    }

    $updateStmt->close();

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Server exception: " . $e->getMessage()]);
}
?>