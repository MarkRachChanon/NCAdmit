<?php
/**
 * API: ลบข่าวประชาสัมพันธ์
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
        'message' => 'คุณไม่มีสิทธิ์ในการลบข่าว'
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

$news_id = intval($data['news_id'] ?? 0);

// Validate ข้อมูล
$errors = [];

if ($news_id <= 0) {
    $errors[] = 'ไม่พบข้อมูลข่าว';
}

// ตรวจสอบว่ามีข่าวนี้จริง
$news_data = null;
if ($news_id > 0) {
    $check_sql = "SELECT * FROM news WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $news_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        $errors[] = 'ไม่พบข้อมูลข่าว';
    } else {
        $news_data = $check_result->fetch_assoc();
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
    $sql = "DELETE FROM news WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $news_id);
    
    if (!$stmt->execute()) {
        throw new Exception('ไม่สามารถลบข้อมูลได้');
    }
    
    $stmt->close();
    
    // ลบไฟล์รูปภาพ (ถ้าเป็นไฟล์ที่อัปโหลดเอง ไม่ใช่ URL ภายนอก)
    $featured_image = $news_data['featured_image'];
    if (!empty($featured_image) && strpos($featured_image, 'http') !== 0) {
        $file_path = __DIR__ . '/../../' . $featured_image;
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }
    
    // บันทึก Activity Log
    $log_action = "ลบข่าวประชาสัมพันธ์: {$news_data['title']}";
    $backup_data = json_encode($news_data, JSON_UNESCAPED_UNICODE);
    
    log_activity(
        $_SESSION['admin_id'],
        $log_action,
        'news',
        $news_id,
        $backup_data,
        null
    );
    
    // Commit Transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'ลบข่าวเรียบร้อยแล้ว'
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