<?php
/**
 * API: แก้ไขรูปภาพแกลเลอรี่
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
        'message' => 'คุณไม่มีสิทธิ์ในการแก้ไขรูปภาพ'
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
$gallery_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$sort_order = isset($_POST['sort_order']) ? intval($_POST['sort_order']) : 0;
$is_published = isset($_POST['is_published']) ? 1 : 0;
$old_image_url = isset($_POST['old_image_url']) ? trim($_POST['old_image_url']) : '';
$new_image_url_external = isset($_POST['image_url']) ? trim($_POST['image_url']) : '';

// Validate ข้อมูล
$errors = [];

if ($gallery_id <= 0) {
    $errors[] = 'ไม่พบข้อมูลรูปภาพ';
}

if (empty($title)) {
    $errors[] = 'กรุณากรอกชื่อรูปภาพ';
}

// ตรวจสอบว่ามีรูปภาพนี้จริง
$old_data = null;
if ($gallery_id > 0) {
    $check_sql = "SELECT * FROM gallery WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $gallery_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        $errors[] = 'ไม่พบข้อมูลรูปภาพ';
    } else {
        $old_data = $check_result->fetch_assoc();
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

// ตัวแปรสำหรับเก็บ URL รูปภาพใหม่
$new_image_url = $old_image_url; // ใช้รูปเดิมก่อน
$file_to_delete = null; // เก็บไฟล์เก่าที่จะลบ

// ตรวจสอบว่ามีการเปลี่ยนรูปภาพหรือไม่
$image_changed = false;

// 1. ตรวจสอบว่าใช้ URL ภายนอกหรือไม่
if (!empty($new_image_url_external)) {
    if (!filter_var($new_image_url_external, FILTER_VALIDATE_URL)) {
        echo json_encode([
            'success' => false,
            'message' => 'URL รูปภาพไม่ถูกต้อง'
        ]);
        exit();
    }
    
    $new_image_url = $new_image_url_external;
    $image_changed = true;
    
    // ถ้ารูปเก่าเป็นไฟล์ local ให้เตรียมลบ
    if (!empty($old_image_url) && strpos($old_image_url, 'http') !== 0) {
        $file_to_delete = __DIR__ . '/../../' . $old_image_url;
    }
}
// 2. ตรวจสอบว่ามีการอัปโหลดไฟล์ใหม่หรือไม่
elseif (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
    $file = $_FILES['image'];
    
    // ตรวจสอบ Error
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode([
            'success' => false,
            'message' => 'เกิดข้อผิดพลาดในการอัปโหลดไฟล์'
        ]);
        exit();
    }
    
    // ตรวจสอบขนาดไฟล์ (ไม่เกิน 5MB)
    $max_size = 5 * 1024 * 1024;
    if ($file['size'] > $max_size) {
        echo json_encode([
            'success' => false,
            'message' => 'ขนาดไฟล์ต้องไม่เกิน 5MB'
        ]);
        exit();
    }
    
    // ตรวจสอบชนิดไฟล์
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $file_type = mime_content_type($file['tmp_name']);
    
    if (!in_array($file_type, $allowed_types)) {
        echo json_encode([
            'success' => false,
            'message' => 'รองรับเฉพาะไฟล์ JPG, PNG, GIF เท่านั้น'
        ]);
        exit();
    }
    
    // สร้างชื่อไฟล์ใหม่
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = 'gallery_' . time() . '_' . uniqid() . '.' . $extension;
    
    // กำหนด path สำหรับเก็บไฟล์
    $upload_dir = __DIR__ . '/../../uploads/gallery/';
    
    // สร้างโฟลเดอร์ถ้ายังไม่มี
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $upload_path = $upload_dir . $new_filename;
    
    // ย้ายไฟล์
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        $new_image_url = 'uploads/gallery/' . $new_filename;
        $image_changed = true;
        
        // ถ้ารูปเก่าเป็นไฟล์ local ให้เตรียมลบ
        if (!empty($old_image_url) && strpos($old_image_url, 'http') !== 0) {
            $file_to_delete = __DIR__ . '/../../' . $old_image_url;
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'ไม่สามารถอัปโหลดไฟล์ได้'
        ]);
        exit();
    }
}

// เริ่ม Transaction
$conn->begin_transaction();

try {
    // Update ข้อมูล
    $sql = "UPDATE gallery 
            SET title = ?, description = ?, image_url = ?, sort_order = ?, is_published = ?, updated_at = NOW() 
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssiii", $title, $description, $new_image_url, $sort_order, $is_published, $gallery_id);
    
    if (!$stmt->execute()) {
        throw new Exception('ไม่สามารถบันทึกข้อมูลได้');
    }
    
    $stmt->close();
    
    // ลบไฟล์เก่า (ถ้ามี)
    if ($file_to_delete && file_exists($file_to_delete)) {
        unlink($file_to_delete);
    }
    
    // บันทึก Activity Log
    $log_action = "แก้ไขรูปภาพแกลเลอรี่: {$old_data['title']}";
    
    $changes = [];
    if ($old_data['title'] !== $title) $changes[] = "ชื่อ: {$old_data['title']} → {$title}";
    if ($old_data['description'] !== $description) $changes[] = "คำอธิบาย";
    if ($old_data['image_url'] !== $new_image_url) $changes[] = "รูปภาพ: {$old_data['image_url']} → {$new_image_url}";
    if ($old_data['sort_order'] != $sort_order) $changes[] = "ลำดับ: {$old_data['sort_order']} → {$sort_order}";
    if ($old_data['is_published'] != $is_published) $changes[] = "สถานะ";
    
    $old_value = json_encode([
        'title' => $old_data['title'],
        'description' => $old_data['description'],
        'image_url' => $old_data['image_url'],
        'sort_order' => $old_data['sort_order'],
        'is_published' => $old_data['is_published']
    ], JSON_UNESCAPED_UNICODE);
    
    $new_value = json_encode([
        'title' => $title,
        'description' => $description,
        'image_url' => $new_image_url,
        'sort_order' => $sort_order,
        'is_published' => $is_published,
        'changes' => $changes
    ], JSON_UNESCAPED_UNICODE);
    
    log_activity(
        $_SESSION['admin_id'],
        $log_action,
        'gallery',
        $gallery_id,
        $old_value,
        $new_value
    );
    
    // Commit Transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'แก้ไขรูปภาพเรียบร้อยแล้ว' . ($image_changed ? ' (รวมรูปภาพใหม่)' : ''),
        'data' => [
            'id' => $gallery_id,
            'title' => $title,
            'image_url' => $new_image_url
        ]
    ]);
    
} catch (Exception $e) {
    // Rollback Transaction
    $conn->rollback();
    
    // ลบไฟล์ที่อัปโหลดใหม่ (ถ้ามี)
    if ($image_changed && !empty($new_image_url) && strpos($new_image_url, 'http') !== 0) {
        $new_file = __DIR__ . '/../../' . $new_image_url;
        if (file_exists($new_file)) {
            unlink($new_file);
        }
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
    ]);
}

$conn->close();
?>