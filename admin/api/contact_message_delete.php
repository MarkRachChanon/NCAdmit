<?php
/**
 * API: ลบข้อความติดต่อ
 * NC-Admission - Nakhon Pathom College Admission System
 */

session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

try {
    // ตรวจสอบการเข้าสู่ระบบ
    if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_logged_in'])) {
        throw new Exception('กรุณาเข้าสู่ระบบ');
    }

    // ตรวจสอบ Method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }

    // รับข้อมูล JSON
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);

    $id = isset($data['id']) ? (int)$data['id'] : 0;
    
    if ($id <= 0) {
        throw new Exception('ข้อมูลไม่ถูกต้อง');
    }

    // ดึงข้อมูลเก่าก่อนลบ
    $sql = "SELECT * FROM contact_messages WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('ไม่พบข้อความที่ต้องการลบ');
    }

    $old_data = $result->fetch_assoc();
    $stmt->close();

    // ลบข้อความ
    $delete_sql = "DELETE FROM contact_messages WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $id);
    
    if (!$delete_stmt->execute()) {
        throw new Exception('ไม่สามารถลบข้อความได้');
    }

    $delete_stmt->close();

    // บันทึก Activity Log
    log_activity(
        $_SESSION['admin_id'],
        "ลบข้อความติดต่อจาก {$old_data['name']} (หัวข้อ: {$old_data['subject']})",
        'contact_message',
        $id,
        json_encode($old_data, JSON_UNESCAPED_UNICODE),
        null
    );

    echo json_encode([
        'success' => true,
        'message' => 'ลบข้อความเรียบร้อยแล้ว'
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

if (isset($conn)) {
    $conn->close();
}