<?php
/**
 * API: เพิ่มข่าวประชาสัมพันธ์
 * NC-Admission - Nakhon Pathom College Admission System
 */

// ✅ ป้องกัน output ที่ไม่ต้องการ
ob_start();

session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// ✅ Clear output buffer และส่ง JSON เท่านั้น
ob_end_clean();

header('Content-Type: application/json; charset=utf-8');

// ✅ Catch all errors
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาดของระบบ: ' . $errstr,
        'debug' => [
            'file' => basename($errfile),
            'line' => $errline
        ]
    ]);
    exit();
});

// ✅ Catch fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'เกิดข้อผิดพลาดร้ายแรง: ' . $error['message']
        ]);
    }
});

try {
    // ตรวจสอบการเข้าสู่ระบบ
    if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_logged_in'])) {
        throw new Exception('กรุณาเข้าสู่ระบบ');
    }

    // ตรวจสอบสิทธิ์
    $allowed_roles = ['superadmin', 'admin', 'staff', 'quota', 'regular'];
    if (!in_array($_SESSION['admin_role'], $allowed_roles)) {
        throw new Exception('คุณไม่มีสิทธิ์ในการเพิ่มข่าว');
    }

    // ตรวจสอบ Method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }

    // รับข้อมูลจาก POST
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $excerpt = isset($_POST['excerpt']) ? trim($_POST['excerpt']) : '';
    $content = isset($_POST['content']) ? trim($_POST['content']) : '';
    $image_url_external = isset($_POST['image_url']) ? trim($_POST['image_url']) : '';
    $published_at = isset($_POST['published_at']) ? trim($_POST['published_at']) : null;
    $is_published = isset($_POST['is_published']) ? 1 : 0;
    $is_pinned = isset($_POST['is_pinned']) ? 1 : 0;
    $author_id = $_SESSION['admin_id'];

    // Validate ข้อมูล
    $errors = [];

    if (empty($title)) {
        $errors[] = 'กรุณากรอกหัวข้อข่าว';
    }

    if (empty($content)) {
        $errors[] = 'กรุณากรอกเนื้อหาข่าว';
    }

    // สร้าง slug จาก title
    function create_slug($string) {
        $string = strtolower($string);
        $string = preg_replace('/\s+/', '-', $string);
        $string = preg_replace('/[^a-z0-9\-ก-๙]/', '', $string);
        $string = preg_replace('/-+/', '-', $string);
        $string = trim($string, '-');
        
        // ถ้า slug ว่างเปล่า (เช่น ภาษาที่ไม่รองรับ)
        if (empty($string)) {
            $string = 'news-' . time();
        }
        
        return $string;
    }

    $slug = create_slug($title);

    // ตรวจสอบ slug ซ้ำ
    $check_slug = $slug;
    $counter = 1;
    $max_attempts = 100; // ป้องกัน infinite loop
    
    while ($counter < $max_attempts) {
        $check_sql = "SELECT id FROM news WHERE slug = ?";
        $check_stmt = $conn->prepare($check_sql);
        
        if (!$check_stmt) {
            throw new Exception('ไม่สามารถเตรียม statement ได้: ' . $conn->error);
        }
        
        $check_stmt->bind_param("s", $check_slug);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows === 0) {
            $slug = $check_slug;
            $check_stmt->close();
            break;
        }
        
        $check_slug = $slug . '-' . $counter;
        $counter++;
        $check_stmt->close();
    }

    // ถ้าไม่มี excerpt ให้ใช้ส่วนแรกของ content
    if (empty($excerpt)) {
        $excerpt = mb_substr(strip_tags($content), 0, 200);
    }

    // แปลง datetime-local เป็น MySQL datetime
    if (!empty($published_at)) {
        $published_at = date('Y-m-d H:i:s', strtotime($published_at));
    } else {
        $published_at = date('Y-m-d H:i:s');
    }

    // ตัวแปรสำหรับเก็บ URL รูปภาพ
    $featured_image = '';

    // ตรวจสอบว่าใช้ URL ภายนอกหรืออัปโหลดไฟล์
    if (!empty($image_url_external)) {
        // ใช้ URL ภายนอก
        if (!filter_var($image_url_external, FILTER_VALIDATE_URL)) {
            $errors[] = 'URL รูปภาพไม่ถูกต้อง';
        } else {
            $featured_image = $image_url_external;
        }
    } elseif (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        // อัปโหลดไฟล์
        $file = $_FILES['featured_image'];
        
        // ตรวจสอบ Error
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $upload_errors = [
                UPLOAD_ERR_INI_SIZE => 'ไฟล์ใหญ่เกินกำหนด (php.ini)',
                UPLOAD_ERR_FORM_SIZE => 'ไฟล์ใหญ่เกินกำหนด (form)',
                UPLOAD_ERR_PARTIAL => 'อัปโหลดไม่สมบูรณ์',
                UPLOAD_ERR_NO_TMP_DIR => 'ไม่มีโฟลเดอร์ชั่วคราว',
                UPLOAD_ERR_CANT_WRITE => 'เขียนไฟล์ไม่ได้',
                UPLOAD_ERR_EXTENSION => 'PHP extension หยุดการอัปโหลด'
            ];
            
            $error_msg = isset($upload_errors[$file['error']]) 
                ? $upload_errors[$file['error']] 
                : 'เกิดข้อผิดพลาดในการอัปโหลดไฟล์ (Error: ' . $file['error'] . ')';
            
            $errors[] = $error_msg;
        } else {
            // ตรวจสอบขนาดไฟล์ (ไม่เกิน 5MB)
            $max_size = 5 * 1024 * 1024; // 5MB
            if ($file['size'] > $max_size) {
                $errors[] = 'ขนาดไฟล์ต้องไม่เกิน 5MB (ปัจจุบัน: ' . round($file['size'] / 1024 / 1024, 2) . ' MB)';
            }
            
            // ตรวจสอบชนิดไฟล์
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            $file_type = mime_content_type($file['tmp_name']);
            
            if (!in_array($file_type, $allowed_types)) {
                $errors[] = 'รองรับเฉพาะไฟล์ JPG, PNG, GIF เท่านั้น (ปัจจุบัน: ' . $file_type . ')';
            }
            
            // ถ้าไม่มี Error ให้อัปโหลดไฟล์
            if (empty($errors)) {
                // สร้างชื่อไฟล์ใหม่
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $new_filename = 'news_' . time() . '_' . uniqid() . '.' . $extension;
                
                // กำหนด path สำหรับเก็บไฟล์
                $upload_dir = __DIR__ . '/../../uploads/news/';
                
                // สร้างโฟลเดอร์ถ้ายังไม่มี
                if (!is_dir($upload_dir)) {
                    if (!mkdir($upload_dir, 0755, true)) {
                        $errors[] = 'ไม่สามารถสร้างโฟลเดอร์ได้';
                    }
                }
                
                if (empty($errors)) {
                    $upload_path = $upload_dir . $new_filename;
                    
                    // ย้ายไฟล์
                    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                        $featured_image = 'uploads/news/' . $new_filename;
                    } else {
                        $errors[] = 'ไม่สามารถอัปโหลดไฟล์ได้ (ตรวจสอบ permissions)';
                    }
                }
            }
        }
    }

    // ถ้ามี Error
    if (!empty($errors)) {
        throw new Exception(implode('<br>', $errors));
    }

    // เริ่ม Transaction
    $conn->begin_transaction();

    // Insert ข้อมูล
    $sql = "INSERT INTO news (
                title, slug, excerpt, content, featured_image, 
                author_id, is_published, is_pinned, published_at, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception('ไม่สามารถเตรียม statement ได้: ' . $conn->error);
    }
    
    $stmt->bind_param(
        "sssssiiis",
        $title,
        $slug,
        $excerpt,
        $content,
        $featured_image,
        $author_id,
        $is_published,
        $is_pinned,
        $published_at
    );
    
    if (!$stmt->execute()) {
        throw new Exception('ไม่สามารถบันทึกข้อมูลได้: ' . $stmt->error);
    }
    
    $new_news_id = $conn->insert_id;
    $stmt->close();
    
    // บันทึก Activity Log
    log_activity(
        $_SESSION['admin_id'],
        "เพิ่มข่าวประชาสัมพันธ์: $title",
        'news',
        $new_news_id,
        null,
        json_encode([
            'title' => $title,
            'slug' => $slug,
            'is_published' => $is_published,
            'is_pinned' => $is_pinned,
            'published_at' => $published_at
        ], JSON_UNESCAPED_UNICODE)
    );
    
    // Commit Transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'เพิ่มข่าวเรียบร้อยแล้ว',
        'data' => [
            'id' => $new_news_id,
            'title' => $title,
            'slug' => $slug
        ]
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    // Rollback Transaction
    if (isset($conn) && $conn->ping()) {
        $conn->rollback();
    }
    
    // ลบไฟล์ที่อัปโหลดไปแล้ว (ถ้ามี)
    if (isset($featured_image) && !empty($featured_image) && strpos($featured_image, 'http') !== 0) {
        $file_to_delete = __DIR__ . '/../../' . $featured_image;
        if (file_exists($file_to_delete)) {
            @unlink($file_to_delete);
        }
    }
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

if (isset($conn)) {
    $conn->close();
}
?>