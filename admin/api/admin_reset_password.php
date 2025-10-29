<?php
/**
 * API: รีเซ็ตรหัสผ่านผู้ดูแลระบบ
 * สำหรับ Superadmin เท่านั้น
 */

session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_logged_in'])) {
    echo json_encode([
        'success' => false,
        'message' => 'กรุณาเข้าสู่ระบบ'
    ]);
    exit();
}

// ตรวจสอบสิทธิ์ (เฉพาะ superadmin)
if ($_SESSION['admin_role'] != 'superadmin') {
    echo json_encode([
        'success' => false,
        'message' => 'คุณไม่มีสิทธิ์ในการรีเซ็ตรหัสผ่าน'
    ]);
    exit();
}

// ตรวจสอบ Method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit();
}

// รับข้อมูล JSON
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

$admin_id = intval($data['admin_id'] ?? 0);
$new_password = $data['new_password'] ?? '';

// Validate ข้อมูล
$errors = [];

// ตรวจสอบ Admin ID
if ($admin_id <= 0) {
    $errors[] = 'ไม่พบข้อมูลผู้ดูแลระบบ';
}

// ตรวจสอบว่ามี Admin นี้จริง
if ($admin_id > 0) {
    $check_sql = "SELECT username FROM admin_users WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $admin_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        $errors[] = 'ไม่พบข้อมูลผู้ดูแลระบบ';
    } else {
        $admin_data = $check_result->fetch_assoc();
    }
    $check_stmt->close();
}

// ตรวจสอบ Password
if (empty($new_password)) {
    $errors[] = 'กรุณากรอกรหัสผ่านใหม่';
} elseif (strlen($new_password) < 6) {
    $errors[] = 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร';
}

// ถ้ามี Error
if (!empty($errors)) {
    echo json_encode([
        'success' => false,
        'message' => implode('<br>', $errors)
    ]);
    exit();
}

// เริ่ม Transaction
$conn->begin_transaction();

try {
    // เข้ารหัส Password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Update รหัสผ่าน
    $sql = "UPDATE admin_users SET password = ?, updated_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $hashed_password, $admin_id);

    if (!$stmt->execute()) {
        throw new Exception('ไม่สามารถรีเซ็ตรหัสผ่านได้');
    }

    $stmt->close();

    // บันทึก Activity Log
    $log_action = "รีเซ็ตรหัสผ่านผู้ดูแลระบบ: {$admin_data['username']}";
    
    log_activity(
        $_SESSION['admin_id'],
        $log_action,
        'admin_users',
        $admin_id,
        null,
        'Password Reset'
    );

    // Commit Transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'รีเซ็ตรหัสผ่านเรียบร้อยแล้ว'
    ]);

} catch (Exception $e) {
    // Rollback Transaction
    $conn->rollback();
    
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
    ]);
}
?>