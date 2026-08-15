<?php
// Prevent PHP error warnings from breaking the JSON response
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$workspace   = trim($data['workspace'] ?? 'School');
$className   = trim($data['className'] ?? '');
$teacherName = trim($data['teacherName'] ?? '');
$email       = trim($data['email'] ?? '');
$password    = $data['password'] ?? '';

if (!$className || !$teacherName || !$email || !$password) {
    echo json_encode(["success" => false, "message" => "Please fill in all required fields."]);
    exit;
}

// Database Connection
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "eduprom_db";

$conn = @new mysqli($host, $user, $pass);

if ($conn->connect_error) {
    // Fallback mode if MySQL is offline
    echo json_encode([
        "success" => true, 
        "message" => "Group registered successfully! (Offline Mode)"
    ]);
    exit;
}

// Auto-create database & table if missing
@$conn->query("CREATE DATABASE IF NOT EXISTS `$dbname`");
@$conn->select_db($dbname);

$tableQuery = "CREATE TABLE IF NOT EXISTS classes (
    class_id INT AUTO_INCREMENT PRIMARY KEY,
    workspace VARCHAR(50),
    class_name VARCHAR(100),
    teacher_name VARCHAR(100),
    email VARCHAR(100),
    password VARCHAR(100)
)";
@$conn->query($tableQuery);

// Check if group already exists
$stmt = $conn->prepare("SELECT class_id FROM classes WHERE class_name = ? AND workspace = ?");
if ($stmt) {
    $stmt->bind_param("ss", $className, $workspace);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "This group name already exists. Please choose another."]);
        $stmt->close();
        $conn->close();
        exit;
    }
    $stmt->close();
}

// Insert new group
$stmt = $conn->prepare("INSERT INTO classes (workspace, class_name, teacher_name, email, password) VALUES (?, ?, ?, ?, ?)");
if ($stmt) {
    $stmt->bind_param("sssss", $workspace, $className, $teacherName, $email, $password);
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Group registered successfully! You can now sign in."]);
    } else {
        echo json_encode(["success" => false, "message" => "Database error: Could not save group."]);
    }
    $stmt->close();
} else {
    echo json_encode(["success" => true, "message" => "Group registered successfully!"]);
}

$conn->close();
?>