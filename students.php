<?php
// students.php
error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$action = $data['action'] ?? $_GET['action'] ?? '';

if (!$pdo) {
    echo json_encode(["success" => false, "message" => "Database connection unavailable."]);
    exit;
}

try {
    // Auto-create tables if missing
    $pdo->exec("CREATE TABLE IF NOT EXISTS classes (
        class_id INT AUTO_INCREMENT PRIMARY KEY,
        workspace VARCHAR(50) DEFAULT 'School',
        class_name VARCHAR(100),
        teacher_name VARCHAR(100),
        email VARCHAR(100),
        password VARCHAR(100)
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS students (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id VARCHAR(50) NOT NULL,
        name VARCHAR(100) NOT NULL,
        phone VARCHAR(50) DEFAULT '',
        class_id INT NOT NULL,
        status VARCHAR(20) DEFAULT 'present',
        remark TEXT DEFAULT NULL,
        attendance_rate INT DEFAULT 100
    )");

    // Helper to find class ID by Name
    function resolveClassId($pdo, $identifier) {
        if (empty($identifier) || !$pdo) return null;

        // 1. Exact match by class_name
        $stmt = $pdo->prepare("SELECT class_id FROM classes WHERE class_name = :identifier");
        $stmt->execute(['identifier' => $identifier]);
        $class = $stmt->fetch();
        if ($class) return $class['class_id'];

        // 2. Loose match (case-insensitive or trimmed)
        $stmt = $pdo->prepare("SELECT class_id FROM classes WHERE TRIM(LOWER(class_name)) = TRIM(LOWER(:identifier))");
        $stmt->execute(['identifier' => $identifier]);
        $class = $stmt->fetch();
        if ($class) return $class['class_id'];

        return null;
    }

    // 1. Fetch students for a specific class
    if ($action === 'get') {
        $className = $_GET['className'] ?? '';
        $classId = resolveClassId($pdo, $className);
        
        if (!$classId) {
            $stmt = $pdo->prepare("INSERT INTO classes (class_name) VALUES (:cName)");
            $stmt->execute(['cName' => $className]);
            $classId = $pdo->lastInsertId();
        }
        
        $stmt = $pdo->prepare("
            SELECT students.student_id as id, students.name, students.phone, students.status, students.remark, students.attendance_rate as attendanceRate 
            FROM students 
            WHERE students.class_id = :classId
        ");
        $stmt->execute(['classId' => $classId]);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(["success" => true, "students" => $students]);
        exit;
    }

    // 2. Add or Update a student
    if ($action === 'save') {
        $className = trim($data['className'] ?? '');
        $studentId = trim($data['studentId'] ?? '');
        $name = trim($data['name'] ?? '');
        $phone = trim($data['phone'] ?? '');

        if (!$className || !$studentId || !$name) {
            echo json_encode(["success" => false, "message" => "Please fill in all required fields."]);
            exit;
        }

        $classId = resolveClassId($pdo, $className);
        if (!$classId) {
            $stmt = $pdo->prepare("INSERT INTO classes (class_name) VALUES (:cName)");
            $stmt->execute(['cName' => $className]);
            $classId = $pdo->lastInsertId();
        }

        // Check if student already exists IN THIS SPECIFIC CLASS
        $check = $pdo->prepare("SELECT id FROM students WHERE student_id = :studentId AND class_id = :classId");
        $check->execute(['studentId' => $studentId, 'classId' => $classId]);
        
        if ($check->fetch()) {
            // Update existing student safely isolated to this group
            $stmt = $pdo->prepare("UPDATE students SET name = :name, phone = :phone WHERE student_id = :studentId AND class_id = :classId");
            $stmt->execute(['name' => $name, 'phone' => $phone, 'studentId' => $studentId, 'classId' => $classId]);
            echo json_encode(["success" => true, "message" => "Student profile updated successfully!"]);
        } else {
            // Insert entirely new student isolated to this group
            $stmt = $pdo->prepare("INSERT INTO students (student_id, name, phone, class_id, status, remark, attendance_rate) VALUES (:studentId, :name, :phone, :classId, 'present', '', 100)");
            $stmt->execute(['studentId' => $studentId, 'name' => $name, 'phone' => $phone, 'classId' => $classId]);
            echo json_encode(["success" => true, "message" => "New member added successfully!"]);
        }
        exit;
    }

    // 3. Delete a student
    if ($action === 'delete') {
        $studentId = $data['studentId'] ?? '';
        $className = $data['className'] ?? '';

        $classId = resolveClassId($pdo, $className);

        if ($classId) {
            $stmt = $pdo->prepare("DELETE FROM students WHERE student_id = :studentId AND class_id = :classId");
            $stmt->execute(['studentId' => $studentId, 'classId' => $classId]);
            echo json_encode(["success" => true, "message" => "Record removed."]);
        } else {
            echo json_encode(["success" => false, "message" => "Class not found."]);
        }
        exit;
    }

    // 4. Update Remark
    if ($action === 'update_remark') {
        $studentId = trim($data['studentId'] ?? '');
        $className = trim($data['className'] ?? '');
        $remark = trim($data['remark'] ?? '');

        $classId = resolveClassId($pdo, $className);

        if ($classId) {
            $stmt = $pdo->prepare("UPDATE students SET remark = :remark WHERE student_id = :studentId AND class_id = :classId");
            $stmt->execute(['remark' => $remark, 'studentId' => $studentId, 'classId' => $classId]);
            echo json_encode(["success" => true, "message" => "Remark updated."]);
        } else {
            echo json_encode(["success" => false, "message" => "Class not found."]);
        }
        exit;
    }

    echo json_encode(["success" => false, "message" => "Invalid action."]);

} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
?>