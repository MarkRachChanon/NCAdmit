<?php
/**
 * API: ลบประเภทวิชา
 * NC-Admission - Nakhon Pathom College Admission System
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

// ตรวจสอบสิทธิ์ (เฉพาะ superadmin และ admin)
if (!in_array($_SESSION['admin_role'], ['superadmin', 'admin'])) {
    echo json_encode([
        'success' => false,
        'message' => 'คุณไม่มีสิทธิ์ในการลบประเภทวิชา'
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

$cat_id = intval($data['cat_id'] ?? 0);

// Validate ข้อมูล
$errors = [];

if ($cat_id <= 0) {
    $errors[] = 'ไม่พบข้อมูลประเภทวิชา';
}

// ตรวจสอบว่ามีประเภทวิชานี้จริง
$cat_data = null;
if ($cat_id > 0) {
    $check_sql = "SELECT * FROM department_categories WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $cat_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        $errors[] = 'ไม่พบข้อมูลประเภทวิชา';
    } else {
        $cat_data = $check_result->fetch_assoc();
    }
    $check_stmt->close();
}

// ตรวจสอบว่ามีสาขาวิชาในประเภทนี้หรือไม่
if ($cat_id > 0) {
    $dept_sql = "SELECT COUNT(*) as count FROM departments WHERE category_id = ?";
    $dept_stmt = $conn->prepare($dept_sql);
    $dept_stmt->bind_param("i", $cat_id);
    $dept_stmt->execute();
    $dept_result = $dept_stmt->get_result();
    $dept_count = $dept_result->fetch_assoc()['count'];
    $dept_stmt->close();
    
    if ($dept_count > 0) {
        $errors[] = "ไม่สามารถลบได้ เนื่องจากมีสาขาวิชาในประเภทนี้อยู่ {$dept_count} สาขา<br>กรุณาย้ายหรือลบสาขาวิชาก่อน";
    }
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
    // ลบข้อมูล
    $sql = "DELETE FROM department_categories WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cat_id);
    
    if (!$stmt->execute()) {
        throw new Exception('ไม่สามารถลบข้อมูลได้');
    }
    
    $stmt->close();
    
    // บันทึก Activity Log
    $log_action = "ลบประเภทวิชา: {$cat_data['name']}";
    $backup_data = json_encode($cat_data, JSON_UNESCAPED_UNICODE);
    
    log_activity(
        $_SESSION['admin_id'],
        $log_action,
        'department_categories',
        $cat_id,
        $backup_data,
        null
    );
    
    // Commit Transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'ลบประเภทวิชาเรียบร้อยแล้ว'
    ]);
    
} catch (Exception $e) {
    // Rollback Transaction
    $conn->rollback();
    
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
    ]);
}

$conn->close();
?>