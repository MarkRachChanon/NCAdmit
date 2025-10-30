<?php

/**
 * API: ลบรูปภาพแกลเลอรี่
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

// ตรวจสอบสิทธิ์
$allowed_roles = ['superadmin', 'admin', 'staff', 'quota', 'regular'];
if (!in_array($_SESSION['admin_role'], $allowed_roles)) {
    echo json_encode([
        'success' => false,
        'message' => 'คุณไม่มีสิทธิ์ในการลบรูปภาพ'
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

$gallery_id = intval($data['gallery_id'] ?? 0);

// Validate ข้อมูล
$errors = [];

if ($gallery_id <= 0) {
    $errors[] = 'ไม่พบข้อมูลรูปภาพ';
}

// ตรวจสอบว่ามีรูปภาพนี้จริง
$gallery_data = null;
if ($gallery_id > 0) {
    $check_sql = "SELECT * FROM gallery WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $gallery_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows === 0) {
        $errors[] = 'ไม่พบข้อมูลรูปภาพ';
    } else {
        $gallery_data = $check_result->fetch_assoc();
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
    // ลบข้อมูลจากฐานข้อมูล
    $sql = "DELETE FROM gallery WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $gallery_id);

    if (!$stmt->execute()) {
        throw new Exception('ไม่สามารถลบข้อมูลได้');
    }

    $stmt->close();

    // ลบไฟล์รูปภาพ (ถ้าเป็นไฟล์ที่อัปโหลดเอง ไม่ใช่ URL ภายนอก)
    $image_url = $gallery_data['image_url'];
    if (!empty($image_url) && strpos($image_url, 'http') !== 0) {
        // ✅ เป็นไฟล์ local (ใช้ __DIR__ จาก admin/api/)
        $file_path = __DIR__ . '/../../' . $image_url;
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }

    // บันทึก Activity Log
    $log_action = "ลบรูปภาพแกลเลอรี่: {$gallery_data['title']}";
    $backup_data = json_encode($gallery_data, JSON_UNESCAPED_UNICODE);

    log_activity(
        $_SESSION['admin_id'],
        $log_action,
        'gallery',
        $gallery_id,
        $backup_data,
        null
    );

    // Commit Transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'ลบรูปภาพเรียบร้อยแล้ว'
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
