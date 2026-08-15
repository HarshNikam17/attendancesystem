<?php
// admin_data.php
error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/db.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$data = json_decode(file_get_contents("php://input"), true);
if (isset($data['action'])) {
    $action = $data['action'];
}

if (!$conn || $conn->connect_error) {
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
    $masterPass = getenv('ADMIN_MASTER_PASSWORD') ?: 'admin123';
    if ($adminPassword !== $masterPass) { 
        echo json_encode(["success" => false, "message" => "Incorrect Admin Password! Deletion cancelled."]);
        exit;
    }
    // ----------------------------------------------

    // Delete associated students first, then the class
    $stmtDelStudents = $conn->prepare("DELETE FROM students WHERE class_id = ?");
    if ($stmtDelStudents) {
        $stmtDelStudents->bind_param("i", $classId);
        $stmtDelStudents->execute();
        $stmtDelStudents->close();
    }

    $stmt = $conn->prepare("DELETE FROM classes WHERE class_id = ?");
    $stmt->bind_param("i", $classId);
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Group deleted successfully!"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to delete group."]);
    }
    $stmt->close();
}
?>