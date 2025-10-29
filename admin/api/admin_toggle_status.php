<?php
/**
 * API: เปิด/ปิดการใช้งานผู้ดูแลระบบ
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
        'message' => 'คุณไม่มีสิทธิ์ในการเปลี่ยนสถานะ'
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
$is_active = intval($data['is_active'] ?? 0);

// Validate ข้อมูล
$errors = [];

// ตรวจสอบ Admin ID
if ($admin_id <= 0) {
    $errors[] = 'ไม่พบข้อมูลผู้ดูแลระบบ';
}

// ห้ามเปลี่ยนสถานะตัวเอง
if ($admin_id == $_SESSION['admin_id']) {
    $errors[] = 'ไม่สามารถเปลี่ยนสถานะของตัวเองได้';
}

// ตรวจสอบว่ามี Admin นี้จริง
if ($admin_id > 0) {
    $check_sql = "SELECT username, is_active FROM admin_users WHERE id = ?";
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

// ตรวจสอบค่า is_active
if (!in_array($is_active, [0, 1])) {
    $errors[] = 'ค่าสถานะไม่ถูกต้อง';
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
    // Update สถานะ
    $sql = "UPDATE admin_users SET is_active = ?, updated_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $is_active, $admin_id);

    if (!$stmt->execute()) {
        throw new Exception('ไม่สามารถเปลี่ยนสถานะได้');
    }

    $stmt->close();

    // บันทึก Activity Log
    $status_text = $is_active == 1 ? 'เปิดใช้งาน' : 'ระงับการใช้งาน';
    $log_action = "{$status_text}บัญชีผู้ดูแลระบบ: {$admin_data['username']}";
    
    log_activity(
        $_SESSION['admin_id'],
        $log_action,
        'admin_users',
        $admin_id,
        $admin_data['is_active'],
        $is_active
    );

    // Commit Transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => $status_text . 'เรียบร้อยแล้ว'
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