<?php
/**
 * API: แก้ไขข่าวประชาสัมพันธ์
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
        'message' => 'คุณไม่มีสิทธิ์ในการแก้ไขข่าว'
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
$news_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$excerpt = isset($_POST['excerpt']) ? trim($_POST['excerpt']) : '';
$content = isset($_POST['content']) ? trim($_POST['content']) : '';
$old_featured_image = isset($_POST['old_featured_image']) ? trim($_POST['old_featured_image']) : '';
$new_image_url_external = isset($_POST['image_url']) ? trim($_POST['image_url']) : '';
$published_at = isset($_POST['published_at']) ? trim($_POST['published_at']) : null;
$is_published = isset($_POST['is_published']) ? 1 : 0;
$is_pinned = isset($_POST['is_pinned']) ? 1 : 0;

// Validate ข้อมูล
$errors = [];

if ($news_id <= 0) {
    $errors[] = 'ไม่พบข้อมูลข่าว';
}

if (empty($title)) {
    $errors[] = 'กรุณากรอกหัวข้อข่าว';
}

if (empty($content)) {
    $errors[] = 'กรุณากรอกเนื้อหาข่าว';
}

// ตรวจสอบว่ามีข่าวนี้จริง
$old_data = null;
if ($news_id > 0) {
    $check_sql = "SELECT * FROM news WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $news_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        $errors[] = 'ไม่พบข้อมูลข่าว';
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

// สร้าง slug ใหม่ถ้าชื่อเปลี่ยน
function create_slug($string) {
    $string = strtolower($string);
    $string = preg_replace('/\s+/', '-', $string);
    $string = preg_replace('/[^a-z0-9\-ก-๙]/', '', $string);
    $string = preg_replace('/-+/', '-', $string);
    $string = trim($string, '-');
    return $string;
}

$slug = $old_data['slug'];
if ($title !== $old_data['title']) {
    $new_slug = create_slug($title);
    
    // ตรวจสอบ slug ซ้ำ
    $check_slug = $new_slug;
    $counter = 1;
    while (true) {
        $check_sql = "SELECT id FROM news WHERE slug = ? AND id != ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("si", $check_slug, $news_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows === 0) {
            $slug = $check_slug;
            break;
        }
        
        $check_slug = $new_slug . '-' . $counter;
        $counter++;
        $check_stmt->close();
    }
}

// ถ้าไม่มี excerpt ให้ใช้ส่วนแรกของ content
if (empty($excerpt)) {
    $excerpt = mb_substr(strip_tags($content), 0, 200);
}

// แปลง datetime-local เป็น MySQL datetime
if (!empty($published_at)) {
    $published_at = date('Y-m-d H:i:s', strtotime($published_at));
} else {
    $published_at = $old_data['published_at'];
}

// ตัวแปรสำหรับเก็บ URL รูปภาพใหม่
$new_featured_image = $old_featured_image;
$file_to_delete = null;
$image_changed = false;

// ตรวจสอบว่ามีการเปลี่ยนรูปภาพหรือไม่
// 1. ตรวจสอบว่าใช้ URL ภายนอกหรือไม่
if (!empty($new_image_url_external)) {
    if (!filter_var($new_image_url_external, FILTER_VALIDATE_URL)) {
        echo json_encode([
            'success' => false,
            'message' => 'URL รูปภาพไม่ถูกต้อง'
        ]);
        exit();
    }
    
    $new_featured_image = $new_image_url_external;
    $image_changed = true;
    
    // ถ้ารูปเก่าเป็นไฟล์ local ให้เตรียมลบ
    if (!empty($old_featured_image) && strpos($old_featured_image, 'http') !== 0) {
        $file_to_delete = __DIR__ . '/../../' . $old_featured_image;
    }
}
// 2. ตรวจสอบว่ามีการอัปโหลดไฟล์ใหม่หรือไม่
elseif (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] !== UPLOAD_ERR_NO_FILE) {
    $file = $_FILES['featured_image'];
    
    // ตรวจสอบ Error
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode([
            'success' => false,
            'message' => 'เกิดข้อผิดพลาดในการอัปโหลดไฟล์'
        ]);
        exit();
    }
    
    // ตรวจสอบขนาดไฟล์
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
    $new_filename = 'news_' . time() . '_' . uniqid() . '.' . $extension;
    
    // กำหนด path สำหรับเก็บไฟล์
    $upload_dir = __DIR__ . '/../../uploads/news/';
    
    // สร้างโฟลเดอร์ถ้ายังไม่มี
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $upload_path = $upload_dir . $new_filename;
    
    // ย้ายไฟล์
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        $new_featured_image = 'uploads/news/' . $new_filename;
        $image_changed = true;
        
        // ถ้ารูปเก่าเป็นไฟล์ local ให้เตรียมลบ
        if (!empty($old_featured_image) && strpos($old_featured_image, 'http') !== 0) {
            $file_to_delete = __DIR__ . '/../../' . $old_featured_image;
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
    $sql = "UPDATE news 
            SET title = ?, slug = ?, excerpt = ?, content = ?, featured_image = ?, 
                is_published = ?, is_pinned = ?, published_at = ?, updated_at = NOW() 
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sssssiisi",
        $title,
        $slug,
        $excerpt,
        $content,
        $new_featured_image,
        $is_published,
        $is_pinned,
        $published_at,
        $news_id
    );
    
    if (!$stmt->execute()) {
        throw new Exception('ไม่สามารถบันทึกข้อมูลได้');
    }
    
    $stmt->close();
    
    // ลบไฟล์เก่า (ถ้ามี)
    if ($file_to_delete && file_exists($file_to_delete)) {
        unlink($file_to_delete);
    }
    
    // บันทึก Activity Log
    $log_action = "แก้ไขข่าวประชาสัมพันธ์: {$old_data['title']}";
    
    $changes = [];
    if ($old_data['title'] !== $title) $changes[] = "ชื่อ: {$old_data['title']} → {$title}";
    if ($old_data['slug'] !== $slug) $changes[] = "Slug: {$old_data['slug']} → {$slug}";
    if ($old_data['content'] !== $content) $changes[] = "เนื้อหา";
    if ($old_data['featured_image'] !== $new_featured_image) $changes[] = "รูปภาพ";
    if ($old_data['is_published'] != $is_published) $changes[] = "สถานะ";
    if ($old_data['is_pinned'] != $is_pinned) $changes[] = "ปักหมุด";
    
    $old_value = json_encode([
        'title' => $old_data['title'],
        'slug' => $old_data['slug'],
        'is_published' => $old_data['is_published'],
        'is_pinned' => $old_data['is_pinned']
    ], JSON_UNESCAPED_UNICODE);
    
    $new_value = json_encode([
        'title' => $title,
        'slug' => $slug,
        'is_published' => $is_published,
        'is_pinned' => $is_pinned,
        'changes' => $changes
    ], JSON_UNESCAPED_UNICODE);
    
    log_activity(
        $_SESSION['admin_id'],
        $log_action,
        'news',
        $news_id,
        $old_value,
        $new_value
    );
    
    // Commit Transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'แก้ไขข่าวเรียบร้อยแล้ว' . ($image_changed ? ' (รวมรูปภาพใหม่)' : ''),
        'data' => [
            'id' => $news_id,
            'title' => $title,
            'slug' => $slug
        ]
    ]);
    
} catch (Exception $e) {
    // Rollback Transaction
    $conn->rollback();
    
    // ลบไฟล์ที่อัปโหลดใหม่ (ถ้ามี)
    if ($image_changed && !empty($new_featured_image) && strpos($new_featured_image, 'http') !== 0) {
        $new_file = __DIR__ . '/../../' . $new_featured_image;
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