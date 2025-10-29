<?php
/**
 * API: ลบผู้ดูแลระบบ
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
        'message' => 'คุณไม่มีสิทธิ์ในการลบผู้ดูแลระบบ'
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

// Validate ข้อมูล
$errors = [];

// ตรวจสอบ Admin ID
if ($admin_id <= 0) {
    $errors[] = 'ไม่พบข้อมูลผู้ดูแลระบบ';
}

// ห้ามลบตัวเอง
if ($admin_id == $_SESSION['admin_id']) {
    $errors[] = 'ไม่สามารถลบบัญชีของตัวเองได้';
}

// ตรวจสอบว่ามี Admin นี้จริง
if ($admin_id > 0) {
    $check_sql = "SELECT * FROM admin_users WHERE id = ?";
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
    // Backup ข้อมูลก่อนลบ
    $backup_data = json_encode($admin_data, JSON_UNESCAPED_UNICODE);
    
    $backup_sql = "INSERT INTO admin_users_deleted 
                   (id, username, password, fullname, email, role, is_active, 
                    last_login, created_at, updated_at, deleted_by, backup_data) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $backup_stmt = $conn->prepare($backup_sql);
    $backup_stmt->bind_param(
        "isssssssssss",
        $admin_data['id'],
        $admin_data['username'],
        $admin_data['password'],
        $admin_data['fullname'],
        $admin_data['email'],
        $admin_data['role'],
        $admin_data['is_active'],
        $admin_data['last_login'],
        $admin_data['created_at'],
        $admin_data['updated_at'],
        $_SESSION['admin_id'],
        $backup_data
    );
    
    if (!$backup_stmt->execute()) {
        throw new Exception('ไม่สามารถสำรองข้อมูลได้');
    }
    $backup_stmt->close();

    // ลบข้อมูล
    $delete_sql = "DELETE FROM admin_users WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $admin_id);

    if (!$delete_stmt->execute()) {
        throw new Exception('ไม่สามารถลบข้อมูลได้');
    }
    $delete_stmt->close();

    // บันทึก Activity Log
    $log_action = "ลบผู้ดูแลระบบ: {$admin_data['username']} (Role: {$admin_data['role']})";
    
    log_activity(
        $_SESSION['admin_id'],
        $log_action,
        'admin_users',
        $admin_id,
        $backup_data,
        null
    );

    // Commit Transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'ลบข้อมูลเรียบร้อยแล้ว'
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