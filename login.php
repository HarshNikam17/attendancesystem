<?php
// login.php
error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$workspace = trim($data['workspace'] ?? 'School');
$username  = trim($data['username'] ?? '');
$password  = $data['password'] ?? '';

if (!$username || !$password) {
    echo json_encode(["success" => false, "message" => "Please enter both group name and password."]);
    exit;
}

if (!$conn || $conn->connect_error) {
    // Fallback mode if offline or database unreachable
    echo json_encode([
        "success" => true,
        "role" => "teacher",
        "className" => $username,
        "teacherName" => "Instructor",
        "message" => "Logged in successfully! (Offline Mode)"
    ]);
    exit;
}

// Check database for matching class/group & password
$stmt = $conn->prepare("SELECT teacher_name, password FROM classes WHERE class_name = ? AND workspace = ?");
if ($stmt) {
    $stmt->bind_param("ss", $username, $workspace);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($teacherName, $dbPassword);
        $stmt->fetch();

        // Direct password match check
        if ($password === $dbPassword) {
            echo json_encode([
                "success" => true,
                "role" => "teacher",
                "className" => $username,
                "teacherName" => $teacherName
            ]);
        } else {
            echo json_encode(["success" => false, "message" => "Incorrect password for this group."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Group name not found in $workspace workspace."]);
    }
    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Database query error: " . $conn->error]);
}
?>