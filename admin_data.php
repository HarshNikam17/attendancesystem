<?php
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$data = json_decode(file_get_contents("php://input"), true);
if (isset($data['action'])) {
    $action = $data['action'];
}

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "eduprom_db";

$conn = @new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit;
}

if ($action === 'get_all_groups') {
    $workspace = $_GET['workspace'] ?? 'School';
    $stmt = $conn->prepare("SELECT class_id, class_name, teacher_name, email FROM classes WHERE workspace = ?");
    $stmt->bind_param("s", $workspace);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $groups = [];
    while ($row = $result->fetch_assoc()) {
        $groups[] = $row;
    }
    
    echo json_encode(["success" => true, "groups" => $groups]);
    $stmt->close();
} 
else if ($action === 'delete_group') {
    $classId = $data['class_id'] ?? '';
    $adminPassword = $data['admin_password'] ?? ''; 

    if (!$classId) {
        echo json_encode(["success" => false, "message" => "Invalid group ID."]);
        exit;
    }

    if (!$adminPassword) {
        echo json_encode(["success" => false, "message" => "Admin password is required."]);
        exit;
    }

    // --- SECURITY CHECK: VERIFY MASTER PASSWORD ---
    if ($adminPassword !== 'admin123') { 
        echo json_encode(["success" => false, "message" => "Incorrect Admin Password! Deletion cancelled."]);
        exit;
    }
    // ----------------------------------------------

    // Password matches! Proceed with deletion.
    $stmt = $conn->prepare("DELETE FROM classes WHERE class_id = ?");
    $stmt->bind_param("i", $classId);
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Group deleted successfully!"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to delete group."]);
    }
    $stmt->close();
}

$conn->close();
?>