<?php
/**
 * API: ลบสาขาวิชา
 * สำหรับ Superadmin และ Admin เท่านั้น
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
        'message' => 'คุณไม่มีสิทธิ์ในการลบสาขาวิชา'
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

$dept_id = intval($data['dept_id'] ?? 0);

// Validate ข้อมูล
$errors = [];

// ตรวจสอบ Department ID
if ($dept_id <= 0) {
    $errors[] = 'ไม่พบข้อมูลสาขาวิชา';
}

// ตรวจสอบว่ามีสาขานี้จริง
$dept_data = null;
if ($dept_id > 0) {
    $check_sql = "SELECT d.*, 
                  (SELECT COUNT(*) FROM students_quota WHERE department_id = d.id) as quota_count,
                  (SELECT COUNT(*) FROM students_regular WHERE department_id = d.id) as regular_count
                  FROM departments d WHERE d.id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $dept_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        $errors[] = 'ไม่พบข้อมูลสาขาวิชา';
    } else {
        $dept_data = $check_result->fetch_assoc();
    }
    $check_stmt->close();
}

// ตรวจสอบว่ามีผู้สมัครหรือไม่ (ถ้า superadmin อนุญาตให้ลบได้แม้มีผู้สมัคร)
if ($dept_data && $_SESSION['admin_role'] !== 'superadmin') {
    $total_applicants = $dept_data['quota_count'] + $dept_data['regular_count'];
    if ($total_applicants > 0) {
        $errors[] = 'ไม่สามารถลบได้ เนื่องจากมีผู้สมัครในสาขานี้แล้ว ' . $total_applicants . ' คน';
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
    // Backup ข้อมูลก่อนลบ
    $backup_data = json_encode($dept_data, JSON_UNESCAPED_UNICODE);
    
    // สร้างตาราง departments_deleted ถ้ายังไม่มี
    $create_deleted_table = "CREATE TABLE IF NOT EXISTS departments_deleted (
        id INT PRIMARY KEY,
        code VARCHAR(20),
        name_th VARCHAR(255),
        name_en VARCHAR(255),
        level VARCHAR(50),
        category_id INT,
        deleted_by INT,
        deleted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        backup_data TEXT,
        quota_count INT DEFAULT 0,
        regular_count INT DEFAULT 0
    )";
    $conn->query($create_deleted_table);
    
    // Backup ข้อมูล
    $backup_sql = "INSERT INTO departments_deleted 
                   (id, code, name_th, name_en, level, category_id, deleted_by, backup_data, quota_count, regular_count) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $backup_stmt = $conn->prepare($backup_sql);
    $backup_stmt->bind_param(
        "issssiisii",
        $dept_data['id'],
        $dept_data['code'],
        $dept_data['name_th'],
        $dept_data['name_en'],
        $dept_data['level'],
        $dept_data['category_id'],
        $_SESSION['admin_id'],
        $backup_data,
        $dept_data['quota_count'],
        $dept_data['regular_count']
    );
    
    if (!$backup_stmt->execute()) {
        throw new Exception('ไม่สามารถสำรองข้อมูลได้');
    }
    $backup_stmt->close();

    // ลบข้อมูล
    $delete_sql = "DELETE FROM departments WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $dept_id);

    if (!$delete_stmt->execute()) {
        throw new Exception('ไม่สามารถลบข้อมูลได้');
    }
    $delete_stmt->close();

    // บันทึก Activity Log
    $log_action = "ลบสาขาวิชา: {$dept_data['code']} - {$dept_data['name_th']} ({$dept_data['level']})";
    
    log_activity(
        $_SESSION['admin_id'],
        $log_action,
        'departments',
        $dept_id,
        $backup_data,
        null
    );

    // Commit Transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'ลบสาขาวิชาเรียบร้อยแล้ว'
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