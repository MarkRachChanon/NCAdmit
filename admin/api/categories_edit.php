<?php
/**
 * API: เพิ่มประเภทวิชาใหม่
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
        'message' => 'คุณไม่มีสิทธิ์ในการเพิ่มประเภทวิชา'
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
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$sort_order = isset($_POST['sort_order']) ? intval($_POST['sort_order']) : 0;
$is_active = isset($_POST['is_active']) ? 1 : 0;

// Validate ข้อมูล
$errors = [];

if (empty($name)) {
    $errors[] = 'กรุณากรอกชื่อประเภทวิชา';
}

if ($sort_order < 0) {
    $errors[] = 'ลำดับการแสดงต้องเป็นตัวเลข 0 หรือมากกว่า';
}

// ตรวจสอบชื่อซ้ำ
if (!empty($name)) {
    $check_sql = "SELECT id FROM department_categories WHERE name = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $name);
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
    // Insert ข้อมูล
    $sql = "INSERT INTO department_categories (name, description, sort_order, is_active, created_at) 
            VALUES (?, ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssii", $name, $description, $sort_order, $is_active);
    
    if (!$stmt->execute()) {
        throw new Exception('ไม่สามารถบันทึกข้อมูลได้');
    }
    
    $new_cat_id = $conn->insert_id;
    $stmt->close();
    
    // บันทึก Activity Log
    log_activity(
        $_SESSION['admin_id'],
        "เพิ่มประเภทวิชา: $name",
        'department_categories',
        $new_cat_id,
        null,
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
        'message' => 'เพิ่มประเภทวิชาเรียบร้อยแล้ว',
        'data' => [
            'id' => $new_cat_id,
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