<?php
/**
 * API: แก้ไขประเภทวิชา
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
        'message' => 'คุณไม่มีสิทธิ์ในการแก้ไขประเภทวิชา'
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

// รับข้อมูลจาก POST
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$sort_order = isset($_POST['sort_order']) ? intval($_POST['sort_order']) : 0;
$is_active = isset($_POST['is_active']) ? 1 : 0;

// Validate ข้อมูล
$errors = [];

if ($id <= 0) {
    $errors[] = 'ไม่พบรหัสประเภทวิชา';
}

if (empty($name)) {
    $errors[] = 'กรุณากรอกชื่อประเภทวิชา';
}

if ($sort_order < 0) {
    $errors[] = 'ลำดับการแสดงต้องเป็นตัวเลข 0 หรือมากกว่า';
}

// ตรวจสอบว่าประเภทวิชานี้มีอยู่ในระบบหรือไม่
if ($id > 0) {
    $check_exist_sql = "SELECT id, name, description, sort_order, is_active FROM department_categories WHERE id = ?";
    $check_exist_stmt = $conn->prepare($check_exist_sql);
    $check_exist_stmt->bind_param("i", $id);
    $check_exist_stmt->execute();
    $check_exist_result = $check_exist_stmt->get_result();
    
    if ($check_exist_result->num_rows === 0) {
        $errors[] = 'ไม่พบประเภทวิชาที่ต้องการแก้ไข';
    } else {
        $old_data = $check_exist_result->fetch_assoc();
    }
    $check_exist_stmt->close();
}

// ตรวจสอบชื่อซ้ำ (ยกเว้นตัวเอง)
if (!empty($name) && $id > 0) {
    $check_sql = "SELECT id FROM department_categories WHERE name = ? AND id != ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("si", $name, $id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $errors[] = 'ชื่อประเภทวิชานี้มีอยู่ในระบบแล้ว';
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
    // Update ข้อมูล
    $sql = "UPDATE department_categories 
            SET name = ?, 
                description = ?, 
                sort_order = ?, 
                is_active = ?,
                updated_at = NOW()
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssiii", $name, $description, $sort_order, $is_active, $id);
    
    if (!$stmt->execute()) {
        throw new Exception('ไม่สามารถบันทึกข้อมูลได้');
    }
    
    $stmt->close();
    
    // บันทึก Activity Log
    log_activity(
        $_SESSION['admin_id'],
        "แก้ไขประเภทวิชา: $name",
        'department_categories',
        $id,
        json_encode([
            'name' => $old_data['name'],
            'description' => $old_data['description'],
            'sort_order' => $old_data['sort_order'],
            'is_active' => $old_data['is_active']
        ], JSON_UNESCAPED_UNICODE),
        json_encode([
            'name' => $name,
            'description' => $description,
            'sort_order' => $sort_order,
            'is_active' => $is_active
        ], JSON_UNESCAPED_UNICODE)
    );
    
    // Commit Transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'แก้ไขประเภทวิชาเรียบร้อยแล้ว',
        'data' => [
            'id' => $id,
            'name' => $name
        ]
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