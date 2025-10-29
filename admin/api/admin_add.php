<?php
/**
 * Admin Add API
 * เพิ่มผู้ดูแลระบบ (เฉพาะ Superadmin)
 */
session_start();

// ตรวจสอบ Admin Login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// ตรวจสอบสิทธิ์ (เฉพาะ superadmin)
if ($_SESSION['admin_role'] != 'superadmin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden - คุณไม่มีสิทธิ์']);
    exit();
}

// Include Database
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Set JSON Header
header('Content-Type: application/json');

// รับข้อมูลจาก POST
$username = isset($_POST['username']) ? clean_input($_POST['username']) : '';
$fullname = isset($_POST['fullname']) ? clean_input($_POST['fullname']) : '';
$email = isset($_POST['email']) ? clean_input($_POST['email']) : '';
$role = isset($_POST['role']) ? clean_input($_POST['role']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

// Validate Input
if (empty($username) || empty($fullname) || empty($role) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน']);
    exit();
}

// Validate Username (ภาษาอังกฤษและตัวเลขเท่านั้น 4-20 ตัวอักษร)
if (!preg_match('/^[a-zA-Z0-9_]{4,20}$/', $username)) {
    echo json_encode(['success' => false, 'message' => 'ชื่อผู้ใช้ไม่ถูกต้อง (ใช้ได้เฉพาะ a-z, A-Z, 0-9, _ ความยาว 4-20 ตัวอักษร)']);
    exit();
}

// Validate Role
$valid_roles = ['superadmin', 'admin', 'staff', 'quota', 'regular'];
if (!in_array($role, $valid_roles)) {
    echo json_encode(['success' => false, 'message' => 'สิทธิ์การใช้งานไม่ถูกต้อง']);
    exit();
}

// Validate Password
if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร']);
    exit();
}

// Validate Email (ถ้ามี)
if (!empty($email) && !validate_email($email)) {
    echo json_encode(['success' => false, 'message' => 'อีเมลไม่ถูกต้อง']);
    exit();
}

// เริ่ม Transaction
$conn->begin_transaction();

try {
    // ตรวจสอบว่า Username ซ้ำหรือไม่
    $check_sql = "SELECT id FROM admin_users WHERE username = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $username);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        throw new Exception('ชื่อผู้ใช้นี้มีอยู่ในระบบแล้ว');
    }
    
    // Hash Password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert Admin
    $insert_sql = "INSERT INTO admin_users (username, password, fullname, email, role, is_active, created_at) 
                   VALUES (?, ?, ?, ?, ?, 1, NOW())";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("sssss", $username, $hashed_password, $fullname, $email, $role);
    
    if (!$insert_stmt->execute()) {
        throw new Exception('ไม่สามารถเพิ่มข้อมูลได้');
    }
    
    $new_admin_id = $conn->insert_id;
    
    // บันทึก Activity Log
    log_activity(
        $_SESSION['admin_id'],
        "เพิ่มผู้ดูแลระบบใหม่: $username (Role: $role)",
        'admin_users',
        $new_admin_id,
        null,
        json_encode(['username' => $username, 'role' => $role], JSON_UNESCAPED_UNICODE)
    );
    
    // Commit Transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'เพิ่มผู้ดูแลระบบเรียบร้อยแล้ว',
        'data' => [
            'id' => $new_admin_id,
            'username' => $username,
            'role' => $role
        ]
    ]);
    
} catch (Exception $e) {
    // Rollback Transaction
    $conn->rollback();
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>