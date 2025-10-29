<?php
/**
 * Upload Handler - จัดเก็บไฟล์แยกตามปีการศึกษา
 * ตำแหน่ง: includes/upload_handler.php
 * โครงสร้าง: uploads/[type]/[academic_year]/[filename]
 */

@ini_set('display_errors', 0);
@error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

define('UPLOAD_BASE_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024);

$allowed_types = [
    'photo' => ['image/jpeg', 'image/png', 'image/jpg'],
    'transcript' => ['application/pdf'],
    'id_card_file' => ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'],
    'house_registration' => ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf']
];

$allowed_extensions = [
    'photo' => ['jpg', 'jpeg', 'png'],
    'transcript' => ['pdf'],
    'id_card_file' => ['jpg', 'jpeg', 'png', 'pdf'],
    'house_registration' => ['jpg', 'jpeg', 'png', 'pdf']
];

$max_sizes = [
    'photo' => 2,
    'transcript' => 5,
    'id_card_file' => 3,
    'house_registration' => 3
];

$folder_names = [
    'photo' => 'photos',
    'transcript' => 'transcripts',
    'id_card_file' => 'id_cards',
    'house_registration' => 'house_registrations'
];

function createDirectoryStructure($academic_year, $type) {
    global $folder_names;
    
    $folder_name = $folder_names[$type] ?? 'others';
    $upload_dir = UPLOAD_BASE_DIR . $folder_name . '/' . $academic_year . '/';
    
    if (!file_exists($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            throw new Exception('ไม่สามารถสร้างโฟลเดอร์ได้');
        }
        
        $htaccess = "Options -Indexes\n# Allow image and PDF files\n<FilesMatch \"\\.(jpg|jpeg|png|pdf)$\">\n    Require all granted\n</FilesMatch>";
        @file_put_contents($upload_dir . '.htaccess', $htaccess);
        @file_put_contents($upload_dir . 'index.php', '<?php http_response_code(403); ?>');
    }
    
    return $upload_dir;
}

function generateUniqueFileName($original_name) {
    $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
    $unique_id = bin2hex(random_bytes(16));
    $timestamp = time();
    return $unique_id . '_' . $timestamp . '.' . $extension;
}

function validateFileExtension($filename, $type, $allowed_extensions) {
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return isset($allowed_extensions[$type]) && in_array($extension, $allowed_extensions[$type]);
}

function validateMimeType($tmp_file, $type, $allowed_types) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $tmp_file);
    finfo_close($finfo);
    return isset($allowed_types[$type]) && in_array($mime_type, $allowed_types[$type]);
}

function scanForMalware($file_path) {
    $content = @file_get_contents($file_path, false, null, 0, 1024);
    if ($content === false) return true;
    
    $dangerous_patterns = [
        '/<\?php/i', '/<\?=/i', '/eval\s*\(/i', '/base64_decode\s*\(/i',
        '/system\s*\(/i', '/exec\s*\(/i', '/shell_exec\s*\(/i', '/passthru\s*\(/i'
    ];
    
    foreach ($dangerous_patterns as $pattern) {
        if (preg_match($pattern, $content)) return false;
    }
    return true;
}

function uploadFile($file, $type, $academic_year) {
    global $allowed_types, $allowed_extensions, $max_sizes, $folder_names;
    
    try {
        if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            throw new Exception('ไม่พบไฟล์ที่อัปโหลด');
        }
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors = [
                UPLOAD_ERR_INI_SIZE => 'ไฟล์ใหญ่เกินกำหนด',
                UPLOAD_ERR_PARTIAL => 'อัปโหลดไม่สมบูรณ์'
            ];
            throw new Exception($errors[$file['error']] ?? 'เกิดข้อผิดพลาด');
        }
        
        if (!validateFileExtension($file['name'], $type, $allowed_extensions)) {
            throw new Exception('นามสกุลไฟล์ไม่ถูกต้อง');
        }
        
        if (!validateMimeType($file['tmp_name'], $type, $allowed_types)) {
            throw new Exception('ประเภทไฟล์ไม่ถูกต้อง');
        }
        
        $max_size = $max_sizes[$type] * 1024 * 1024;
        if ($file['size'] > $max_size) {
            throw new Exception('ไฟล์ใหญ่เกิน ' . $max_sizes[$type] . ' MB');
        }
        
        if ($type === 'photo' || $type === 'id_card_file') {
            $image_info = @getimagesize($file['tmp_name']);
            if ($image_info === false) {
                throw new Exception('ไฟล์ไม่ใช่รูปภาพ');
            }
        }
        
        if (!scanForMalware($file['tmp_name'])) {
            throw new Exception('ตรวจพบเนื้อหาอันตราย');
        }
        
        $upload_dir = createDirectoryStructure($academic_year, $type);
        $new_filename = generateUniqueFileName($file['name']);
        $destination = $upload_dir . $new_filename;
        
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new Exception('บันทึกไฟล์ไม่ได้');
        }
        
        @chmod($destination, 0644);
        
        $folder_name = $folder_names[$type];
        $relative_path = $folder_name . '/' . $academic_year . '/' . $new_filename;
        
        return [
            'success' => true,
            'path' => $relative_path,
            'filename' => $new_filename,
            'original_name' => $file['name'],
            'size' => $file['size']
        ];
        
    } catch (Exception $e) {
        if (isset($file['tmp_name']) && file_exists($file['tmp_name'])) {
            @unlink($file['tmp_name']);
        }
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request');
    }
    
    $type = $_POST['type'] ?? '';
    $academic_year = $_POST['academic_year'] ?? '';
    
    if (empty($type)) throw new Exception('ไม่ได้ระบุประเภทไฟล์');
    if (empty($academic_year)) throw new Exception('ไม่ได้ระบุปีการศึกษา');
    if (!preg_match('/^25\d{2}$/', $academic_year)) throw new Exception('ปีการศึกษาไม่ถูกต้อง');
    if (!isset($allowed_types[$type])) throw new Exception('ประเภทไฟล์ไม่ถูกต้อง');
    if (!isset($_FILES['file'])) throw new Exception('ไม่พบไฟล์');
    
    $result = uploadFile($_FILES['file'], $type, $academic_year);
    
    if ($result['success']) {
        @error_log("[UPLOAD] {$type}/{$academic_year}: {$result['filename']}");
    }
    
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>