<?php
/**
 * API: อัปโหลดรูปภาพแกลเลอรี่
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
        'message' => 'คุณไม่มีสิทธิ์ในการอัปโหลดรูปภาพ'
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
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$image_url_external = isset($_POST['image_url']) ? trim($_POST['image_url']) : '';
$sort_order = isset($_POST['sort_order']) ? intval($_POST['sort_order']) : 0;
$is_published = isset($_POST['is_published']) ? 1 : 0;

// Validate ข้อมูล
$errors = [];

if (empty($title)) {
    $errors[] = 'กรุณากรอกชื่อรูปภาพ';
}

// ตัวแปรสำหรับเก็บ URL รูปภาพ
$image_url = '';

// ตรวจสอบว่าใช้ URL ภายนอกหรืออัปโหลดไฟล์
if (!empty($image_url_external)) {
    // ใช้ URL ภายนอก
    if (!filter_var($image_url_external, FILTER_VALIDATE_URL)) {
        $errors[] = 'URL รูปภาพไม่ถูกต้อง';
    } else {
        $image_url = $image_url_external;
    }
} else {
    // อัปโหลดไฟล์
    if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
        $errors[] = 'กรุณาเลือกรูปภาพหรือระบุ URL';
    } else {
        $file = $_FILES['image'];
        
        // ตรวจสอบ Error
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'เกิดข้อผิดพลาดในการอัปโหลดไฟล์';
        }
        
        // ตรวจสอบขนาดไฟล์ (ไม่เกิน 5MB)
        $max_size = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $max_size) {
            $errors[] = 'ขนาดไฟล์ต้องไม่เกิน 5MB';
        }
        
        // ตรวจสอบชนิดไฟล์
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $file_type = mime_content_type($file['tmp_name']);
        
        if (!in_array($file_type, $allowed_types)) {
            $errors[] = 'รองรับเฉพาะไฟล์ JPG, PNG, GIF เท่านั้น';
        }
        
        // ถ้าไม่มี Error ให้อัปโหลดไฟล์
        if (empty($errors)) {
            // สร้างชื่อไฟล์ใหม่
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $new_filename = 'gallery_' . time() . '_' . uniqid() . '.' . $extension;
            
            // ✅ กำหนด path สำหรับเก็บไฟล์ (ใช้ __DIR__ จาก admin/api/)
            $upload_dir = __DIR__ . '/../../uploads/gallery/';
            
            // สร้างโฟลเดอร์ถ้ายังไม่มี
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $upload_path = $upload_dir . $new_filename;
            
            // ย้ายไฟล์
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                // ✅ เก็บ path แบบ relative จาก root (ไม่มี ../)
                $image_url = 'uploads/gallery/' . $new_filename;
            } else {
                $errors[] = 'ไม่สามารถอัปโหลดไฟล์ได้';
            }
        }
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
    // Insert ข้อมูล
    $sql = "INSERT INTO gallery (title, description, image_url, sort_order, is_published, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssii", $title, $description, $image_url, $sort_order, $is_published);
    
    if (!$stmt->execute()) {
        throw new Exception('ไม่สามารถบันทึกข้อมูลได้');
    }
    
    $new_gallery_id = $conn->insert_id;
    $stmt->close();
    
    // บันทึก Activity Log
    log_activity(
        $_SESSION['admin_id'],
        "อัปโหลดรูปภาพแกลเลอรี่: $title",
        'gallery',
        $new_gallery_id,
        null,
        json_encode([
            'title' => $title,
            'description' => $description,
            'image_url' => $image_url,
            'sort_order' => $sort_order,
            'is_published' => $is_published
        ], JSON_UNESCAPED_UNICODE)
    );
    
    // Commit Transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'อัปโหลดรูปภาพเรียบร้อยแล้ว',
        'data' => [
            'id' => $new_gallery_id,
            'title' => $title,
            'image_url' => $image_url
        ]
    ]);
    
} catch (Exception $e) {
    // Rollback Transaction
    $conn->rollback();
    
    // ลบไฟล์ที่อัปโหลดไปแล้ว (ถ้ามี)
    if (!empty($image_url) && strpos($image_url, 'http') !== 0) {
        $file_to_delete = __DIR__ . '/../../' . $image_url;
        if (file_exists($file_to_delete)) {
            unlink($file_to_delete);
        }
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
    ]);
}

$conn->close();
?>