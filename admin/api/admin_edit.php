<?php
/**
 * API: แก้ไขข้อมูลผู้ดูแลระบบ
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
        'message' => 'คุณไม่มีสิทธิ์ในการแก้ไขผู้ดูแลระบบ'
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

// รับข้อมูลจาก POST (FormData)
$admin_id = intval($_POST['admin_id'] ?? 0);
$fullname = trim($_POST['fullname'] ?? '');
$email = trim($_POST['email'] ?? '');
$role = $_POST['role'] ?? '';

// Validate ข้อมูล
$errors = [];

// ตรวจสอบ Admin ID
if ($admin_id <= 0) {
    $errors[] = 'ไม่พบข้อมูลผู้ดูแลระบบ';
}

// ตรวจสอบว่ามี Admin นี้จริง
$old_data = null;
if ($admin_id > 0) {
    $check_sql = "SELECT * FROM admin_users WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $admin_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        $errors[] = 'ไม่พบข้อมูลผู้ดูแลระบบ';
    } else {
        $old_data = $check_result->fetch_assoc();
    }
    $check_stmt->close();
}

// ตรวจสอบชื่อ-นามสกุล
if (empty($fullname)) {
    $errors[] = 'กรุณากรอกชื่อ-นามสกุล';
}

// ตรวจสอบสิทธิ์การใช้งาน
$valid_roles = ['superadmin', 'admin', 'staff', 'quota', 'regular'];
if (!in_array($role, $valid_roles)) {
    $errors[] = 'สิทธิ์การใช้งานไม่ถูกต้อง';
}

// ตรวจสอบ Email (ถ้ามี)
if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'รูปแบบอีเมลไม่ถูกต้อง';
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
    // Update ข้อมูล
    $sql = "UPDATE admin_users 
            SET fullname = ?, email = ?, role = ?, updated_at = NOW() 
            WHERE id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $fullname, $email, $role, $admin_id);

    if (!$stmt->execute()) {
        throw new Exception('ไม่สามารถบันทึกข้อมูลได้');
    }

    $stmt->close();

    // บันทึก Activity Log
    $log_action = "แก้ไขข้อมูลผู้ดูแลระบบ: {$old_data['username']}";
    $old_value = json_encode([
        'fullname' => $old_data['fullname'],
        'email' => $old_data['email'],
        'role' => $old_data['role']
    ], JSON_UNESCAPED_UNICODE);
    
    $new_value = json_encode([
        'fullname' => $fullname,
        'email' => $email,
        'role' => $role
    ], JSON_UNESCAPED_UNICODE);

    log_activity(
        $_SESSION['admin_id'],
        $log_action,
        'admin_users',
        $admin_id,
        $old_value,
        $new_value
    );

    // Commit Transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'แก้ไขข้อมูลเรียบร้อยแล้ว'
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