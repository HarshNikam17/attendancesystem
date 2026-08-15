<?php
// forgot_password.php
error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/db.php';
header('Content-Type: application/json');

if (!$conn || $conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$email = trim($data['email'] ?? '');
$workspace = trim($data['workspace'] ?? 'School');
$customMessage = trim($data['custom_message'] ?? '');

// Handle Email Digest Request
if (!empty($customMessage)) {
    $to = $email ?: "admin@edupro.local";
    $subject = "EduPro Attendance Daily Digest - " . date('Y-m-d');
    $headers = "From: no-reply@edupro.local\r\n";
    @mail($to, $subject, $customMessage, $headers);
    echo json_encode(['success' => true, 'message' => 'Email digest sent.']);
    exit;
}

if (empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Email is required.']);
    exit;
}

$stmt = $conn->prepare("SELECT class_name, teacher_name FROM classes WHERE email = ? AND workspace = ?");
if ($stmt) {
    $stmt->bind_param("ss", $email, $workspace);
    $stmt->execute();
    $result = $stmt->get_result();
    $account = $result->fetch_assoc();
    $stmt->close();

    if ($account) {
        // Generate random 8-char temporary password
        $new_password = substr(str_shuffle('abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789'), 0, 8);
        
        $update = $conn->prepare("UPDATE classes SET password = ? WHERE email = ? AND workspace = ?");
        if ($update) {
            $update->bind_param("sss", $new_password, $email, $workspace);
            $update->execute();
            $update->close();
        }

        $to = $email;
        $subject = "EduPro Security - Password Recovery";
        $message = "Hello " . $account['teacher_name'] . ",\n\n"
                 . "A password reset was requested for your EduPro group: " . $account['class_name'] . ".\n\n"
                 . "Your new temporary password is: " . $new_password . "\n\n"
                 . "Please log in and change this password from the Settings Hub immediately.\n\n"
                 . "Regards,\nEduPro Security System";
                 
        $headers = "From: no-reply@edupro.local\r\n";

        if (@mail($to, $subject, $message, $headers)) {
            echo json_encode(['success' => true, 'message' => 'Password reset instructions sent.']);
        } else {
            // Even if server mail() is not configured in PHP, return temporary password or success message
            echo json_encode([
                'success' => true, 
                'message' => "Password reset to '$new_password'. (Server mail not configured; use this password to log in)"
            ]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No account found matching this email.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Database query failed.']);
}
?>