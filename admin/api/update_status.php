<?php
/**
 * Update Status API
 * อัพเดทสถานะผู้สมัคร (Quota & Regular)
 */

session_start();

// ตรวจสอบ Admin Login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized - กรุณาเข้าสู่ระบบ'
    ]);
    exit();
}

// Include Database
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Set JSON Header
header('Content-Type: application/json');

// รับข้อมูลจาก Request
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Validate Input
if (!isset($data['id']) || !isset($data['status']) || !isset($data['type'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ข้อมูลไม่ครบถ้วน'
    ]);
    exit();
}

$id = (int)$data['id'];
$status = clean_input($data['status']);
$type = clean_input($data['type']);
$note = isset($data['note']) ? clean_input($data['note']) : null;

// Validate Status
$valid_statuses = ['pending', 'approved', 'rejected', 'cancelled'];
if (!in_array($status, $valid_statuses)) {
    echo json_encode([
        'success' => false,
        'message' => 'สถานะไม่ถูกต้อง'
    ]);
    exit();
}

// Validate Type
$valid_types = ['quota', 'regular'];
if (!in_array($type, $valid_types)) {
    echo json_encode([
        'success' => false,
        'message' => 'ประเภทไม่ถูกต้อง'
    ]);
    exit();
}

// เลือกตารางที่จะอัพเดท
$table = ($type == 'quota') ? 'students_quota' : 'students_regular';

// เริ่ม Transaction
$conn->begin_transaction();

try {
    // ตรวจสอบว่ามีข้อมูลอยู่จริง
    $check_sql = "SELECT id, application_no, firstname_th, lastname_th, status FROM $table WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows == 0) {
        throw new Exception('ไม่พบข้อมูลที่ต้องการอัพเดท');
    }
    
    $student = $check_result->fetch_assoc();
    $old_status = $student['status'];
    
    // อัพเดทสถานะ
    $update_sql = "UPDATE $table SET 
        status = ?,
        status_note = ?,
        updated_at = NOW()
        WHERE id = ?";
    
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ssi", $status, $note, $id);
    
    if (!$update_stmt->execute()) {
        throw new Exception('ไม่สามารถอัพเดทข้อมูลได้');
    }
    
    // บันทึก Log (ถ้ามีตาราง logs)
    $admin_id = $_SESSION['admin_id'];
    $admin_username = $_SESSION['admin_username'];
    $action = "อัพเดทสถานะจาก $old_status เป็น $status";
    $log_sql = "INSERT INTO activity_logs (admin_id, action, table_name, record_id, old_value, new_value, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
    
    if ($log_stmt = $conn->prepare($log_sql)) {
        $log_stmt->bind_param("ississ", $admin_id, $action, $table, $id, $old_status, $status);
        $log_stmt->execute();
    }
    
    // Commit Transaction
    $conn->commit();
    
    // ส่ง Email แจ้งเตือน (ถ้าต้องการ)
    // sendStatusUpdateEmail($student['email'], $status);
    
    echo json_encode([
        'success' => true,
        'message' => 'อัพเดทสถานะเรียบร้อยแล้ว',
        'data' => [
            'id' => $id,
            'old_status' => $old_status,
            'new_status' => $status,
            'application_no' => $student['application_no'],
            'updated_by' => $admin_username,
            'updated_at' => date('Y-m-d H:i:s')
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