<?php

/**
 * Delete Student API
 * ลบข้อมูลผู้สมัคร (Quota & Regular)
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

// ตรวจสอบสิทธิ์ (เฉพาะ superadmin และ admin เท่านั้น)
if (!in_array($_SESSION['admin_role'], ['superadmin', 'admin'])) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Forbidden - คุณไม่มีสิทธิ์ลบข้อมูล'
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
if (!isset($data['id']) || !isset($data['type'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ข้อมูลไม่ครบถ้วน'
    ]);
    exit();
}

$id = (int)$data['id'];
$type = clean_input($data['type']);

// Validate Type
$valid_types = ['quota', 'regular'];
if (!in_array($type, $valid_types)) {
    echo json_encode([
        'success' => false,
        'message' => 'ประเภทไม่ถูกต้อง'
    ]);
    exit();
}

// เลือกตารางที่จะลบ
$table = ($type == 'quota') ? 'students_quota' : 'students_regular';

// เริ่ม Transaction
$conn->begin_transaction();

try {
    // ดึงข้อมูลก่อนลบ (เพื่อเก็บ backup)
    $select_sql = "SELECT * FROM $table WHERE id = ?";
    $select_stmt = $conn->prepare($select_sql);
    $select_stmt->bind_param("i", $id);
    $select_stmt->execute();
    $result = $select_stmt->get_result();

    if ($result->num_rows == 0) {
        throw new Exception('ไม่พบข้อมูลที่ต้องการลบ');
    }

    $student = $result->fetch_assoc();
    $backup_data = json_encode($student, JSON_UNESCAPED_UNICODE);

    // ลบไฟล์ที่เกี่ยวข้อง (รูปภาพ, ไฟล์แนบ)
    $files_to_delete = [];

    if (!empty($student['photo_path'])) {
        $full_path = '../../uploads/' . $student['photo_path'];
        if (file_exists($full_path)) {
            $files_to_delete[] = $full_path;
        }
    }

    if (!empty($student['transcript_path']) && file_exists('../../' . $student['transcript_path'])) {
        $files_to_delete[] = '../../' . $student['transcript_path'];
    }

    if (!empty($student['id_card_path']) && file_exists('../../' . $student['id_card_path'])) {
        $files_to_delete[] = '../../' . $student['id_card_path'];
    }

    if (!empty($student['house_registration_path']) && file_exists('../../' . $student['house_registration_path'])) {
        $files_to_delete[] = '../../' . $student['house_registration_path'];
    }

    // บันทึกข้อมูลลงตาราง deleted_records (สำหรับ backup)
    $admin_id = $_SESSION['admin_id'];
    $backup_sql = "INSERT INTO deleted_records (record_type, deleted_count, deleted_by, backup_data, deleted_at) 
                   VALUES (?, 1, ?, ?, NOW())";
    $backup_stmt = $conn->prepare($backup_sql);
    $backup_stmt->bind_param("sis", $type, $admin_id, $backup_data);
    $backup_stmt->execute();

    // ลบข้อมูลจากฐานข้อมูล
    $delete_sql = "DELETE FROM $table WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $id);

    if (!$delete_stmt->execute()) {
        throw new Exception('ไม่สามารถลบข้อมูลได้');
    }

    // ลบไฟล์จริง
    foreach ($files_to_delete as $file) {
        if (file_exists($file)) {
            @unlink($file);
        }
    }

    // บันทึก Activity Log
    $action = "ลบข้อมูลผู้สมัคร: {$student['application_no']} - {$student['firstname_th']} {$student['lastname_th']}";
    $log_sql = "INSERT INTO activity_logs (admin_id, action, table_name, record_id, old_value, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())";

    if ($log_stmt = $conn->prepare($log_sql)) {
        $log_stmt->bind_param("issis", $admin_id, $action, $table, $id, $backup_data);
        $log_stmt->execute();
    }

    // Commit Transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'ลบข้อมูลเรียบร้อยแล้ว',
        'data' => [
            'id' => $id,
            'application_no' => $student['application_no'],
            'name' => $student['firstname_th'] . ' ' . $student['lastname_th'],
            'deleted_by' => $_SESSION['admin_username'],
            'deleted_at' => date('Y-m-d H:i:s'),
            'files_deleted' => count($files_to_delete)
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
