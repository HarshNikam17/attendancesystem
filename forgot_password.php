<?php
// Prevent HTML errors from breaking the JSON response
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

// --- 1. DATABASE CONNECTION (Using mysqli to match your signup.php) ---
$host = 'localhost';
$db   = 'eduprom_db'; 
$user = 'root';
$pass = '';

$conn = @new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'DB Connection failed: ' . $conn->connect_error]);
    exit;
}

// Get the JSON data sent from Javascript
$data = json_decode(file_get_contents("php://input"), true);
$email = trim($data['email'] ?? '');
$workspace = trim($data['workspace'] ?? '');

if (empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Email is required.']);
    exit;
}

// 2. Check if the email exists in your correct database
$stmt = $conn->prepare("SELECT * FROM classes WHERE email = ? AND workspace = ?");
if ($stmt) {
    $stmt->bind_param("ss", $email, $workspace);
    $stmt->execute();
    $result = $stmt->get_result();
    $account = $result->fetch_assoc();
    $stmt->close();

    if ($account) {
        // 3. Generate a random 8-character temporary password
        $new_password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
        
        // 4. Update the database with the new password
        $update = $conn->prepare("UPDATE classes SET password = ? WHERE email = ? AND workspace = ?");
        if ($update) {
            $update->bind_param("sss", $new_password, $email, $workspace);
            $update->execute();
            $update->close();
        }

        // 5. Draft the Email
        $to = $email;
        $subject = "EduPro Security - Password Recovery";
        $message = "Hello " . $account['teacher_name'] . ",\n\n"
                 . "A password reset was requested for your EduPro group: " . $account['class_name'] . ".\n\n"
                 . "Your new temporary password is: " . $new_password . "\n\n"
                 . "Please log in and change this password from the Settings Hub immediately.\n\n"
                 . "Regards,\nEduPro Security System";
                 
        $headers = "From: no-reply@edupro.local\r\n";

        // 6. Send the Email
        if (mail($to, $subject, $message, $headers)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Password reset in DB, but XAMPP failed to send the email.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No account found matching this email.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Database query failed.']);
}

$conn->close();
?>