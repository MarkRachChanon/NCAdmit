<?php
/**
 * API: ดึงรายละเอียดข้อความติดต่อ
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

    // รับ ID
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if ($id <= 0) {
        throw new Exception('ข้อมูลไม่ถูกต้อง');
    }

    // ดึงข้อมูล
    $sql = "SELECT * FROM contact_messages WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('ไม่พบข้อความ');
    }

    $data = $result->fetch_assoc();
    
    // อัพเดทสถานะเป็นอ่านแล้ว
    $update_sql = "UPDATE contact_messages SET is_read = 1 WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("i", $id);
    $update_stmt->execute();
    $update_stmt->close();

    // จัดรูปแบบวันที่
    $data['created_at_thai'] = thai_date($data['created_at'], 'full');

    echo json_encode([
        'success' => true,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);

    $stmt->close();
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