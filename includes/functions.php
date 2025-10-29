<?php
/**
 * Common Functions
 * NC-Admission - Nakhon Pathom College Admission System
 */

/**
 * Sanitize Input
 */
function clean_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return mysqli_real_escape_string($conn, $data);
}

/**
 * Get Page Title
 */
function get_page_title($page) {
    $titles = [
        'home' => 'หน้าแรก',
        'about' => 'เกี่ยวกับเรา',
        'news' => 'ข่าวสาร',
        'news_detail' => 'รายละเอียดข่าว',
        'gallery' => 'แกลเลอรี่',
        'contact' => 'ติดต่อเรา',
        'admission_info' => 'ข้อมูลการรับสมัคร',
        'quota_form' => 'สมัครรอบโควต้า',
        'regular_form' => 'สมัครรอบปกติ',
        'check_status' => 'ตรวจสอบสถานะการสมัคร'
    ];
    
    return isset($titles[$page]) ? $titles[$page] : 'NC-Admission';
}

/**
 * Get Setting Value
 */
function get_setting($key, $default = '') {
    global $conn;
    $key = clean_input($key);
    $sql = "SELECT setting_value FROM settings WHERE setting_key = '$key' LIMIT 1";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['setting_value'];
    }
    
    return $default;
}

/**
 * Check if Admission is Open
 */
function is_admission_open($type = 'quota') {
    $setting_key = ($type == 'quota') ? 'quota_open' : 'regular_open';
    return (get_setting($setting_key, '0') == '1');
}

/**
 * Get Active Departments by Level
 */
function get_departments($level = null) {
    global $conn;
    
    $sql = "SELECT d.*, dc.name as category_name 
            FROM departments d 
            LEFT JOIN department_categories dc ON d.category_id = dc.id 
            WHERE d.is_active = 1";
    
    if ($level) {
        $level = clean_input($level);
        $sql .= " AND d.level = '$level'";
    }
    
    $sql .= " ORDER BY dc.sort_order, d.name_th";
    
    $result = $conn->query($sql);
    $departments = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $departments[] = $row;
        }
    }
    
    return $departments;
}

/**
 * Generate Application Number
 */
function generate_application_no($type = 'quota') {
    $prefix = ($type == 'quota') ? 'Q' : 'R';
    $year = date('Y') + 543; // Buddhist Year
    $random = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    return $prefix . $year . $random;
}

/**
 * Format Thai Date
 */
function thai_date($date, $format = 'full') {
    if (!$date) return '-';
    
    $thai_months = [
        'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน',
        'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม',
        'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'
    ];
    
    $thai_months_short = [
        'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.',
        'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.',
        'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'
    ];
    
    $timestamp = strtotime($date);
    $day = date('j', $timestamp);
    $month = date('n', $timestamp) - 1;
    $year = date('Y', $timestamp) + 543;
    
    if ($format == 'short') {
        return $day . ' ' . $thai_months_short[$month] . ' ' . $year;
    }
    
    return $day . ' ' . $thai_months[$month] . ' ' . $year;
}

/**
 * Get Status Badge HTML
 */
function get_status_badge($status) {
    $badges = [
        'pending' => '<span class="badge bg-warning">รอตรวจสอบ</span>',
        'approved' => '<span class="badge bg-success">อนุมัติ</span>',
        'rejected' => '<span class="badge bg-danger">ไม่อนุมัติ</span>',
        'cancelled' => '<span class="badge bg-secondary">ยกเลิก</span>'
    ];
    
    return isset($badges[$status]) ? $badges[$status] : $status;
}

function get_status_class($status) {
    switch ($status) {
        case 'pending': return 'bg-warning text-dark';
        case 'approved': return 'bg-success';
        case 'rejected': return 'bg-danger';
        case 'cancelled': return 'bg-secondary';
        default: return 'bg-info';
    }
}

/**
 * Get Status Text
 */
function get_status_text($status) {
    switch ($status) {
        case 'pending': return 'รอตรวจสอบ';
        case 'approved': return 'อนุมัติ';
        case 'rejected': return 'ไม่อนุมัติ';
        case 'cancelled': return 'ยกเลิก';
        default: return 'ไม่ทราบสถานะ';
    }
}

/**
 * Get Status Color
 */
function get_status_color($status) {
    $colors = [
        'pending' => 'warning',
        'approved' => 'success',
        'rejected' => 'danger',
        'cancelled' => 'secondary'
    ];
    
    return $colors[$status] ?? 'secondary';
}

/**
 * Upload File
 */
function upload_file($file, $target_dir, $allowed_types = []) {
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return ['success' => false, 'message' => 'ไม่มีไฟล์ที่อัปโหลด'];
    }
    
    // ตรวจสอบ Error
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'เกิดข้อผิดพลาดในการอัปโหลดไฟล์'];
    }
    
    // ตรวจสอบขนาดไฟล์ (5MB)
    $max_size = 5 * 1024 * 1024;
    if ($file['size'] > $max_size) {
        return ['success' => false, 'message' => 'ไฟล์มีขนาดใหญ่เกิน 5MB'];
    }
    
    // ตรวจสอบประเภทไฟล์
    $file_info = pathinfo($file['name']);
    $extension = strtolower($file_info['extension']);
    
    if (!empty($allowed_types) && !in_array($extension, $allowed_types)) {
        return ['success' => false, 'message' => 'ประเภทไฟล์ไม่ถูกต้อง'];
    }
    
    // สร้างชื่อไฟล์ใหม่
    $new_filename = md5(uniqid() . time()) . '_' . time() . '.' . $extension;
    $target_path = $target_dir . '/' . $new_filename;
    
    // สร้างโฟลเดอร์ถ้ายังไม่มี
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0755, true);
    }
    
    // อัปโหลดไฟล์
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        return [
            'success' => true,
            'filename' => $new_filename,
            'path' => $target_path,
            'message' => 'อัปโหลดไฟล์สำเร็จ'
        ];
    }
    
    return ['success' => false, 'message' => 'ไม่สามารถอัปโหลดไฟล์ได้'];
}

/**
 * Delete File
 */
function delete_file($file_path) {
    if (file_exists($file_path)) {
        return unlink($file_path);
    }
    return false;
}

/**
 * Format Phone Number
 */
function format_phone($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    if (strlen($phone) == 10) {
        return substr($phone, 0, 3) . '-' . substr($phone, 3, 3) . '-' . substr($phone, 6);
    }
    
    return $phone;
}

/**
 * Format ID Card
 */
function format_id_card($id_card) {
    $id_card = preg_replace('/[^0-9]/', '', $id_card);
    
    if (strlen($id_card) == 13) {
        return substr($id_card, 0, 1) . '-' . 
               substr($id_card, 1, 4) . '-' . 
               substr($id_card, 5, 5) . '-' . 
               substr($id_card, 10, 2) . '-' . 
               substr($id_card, 12, 1);
    }
    
    return $id_card;
}

/**
 * Validate ID Card
 */
function validate_id_card($id_card) {
    $id_card = preg_replace('/[^0-9]/', '', $id_card);
    
    if (strlen($id_card) != 13) {
        return false;
    }
    
    $sum = 0;
    for ($i = 0; $i < 12; $i++) {
        $sum += (int)$id_card[$i] * (13 - $i);
    }
    
    $check = (11 - ($sum % 11)) % 10;
    
    return $check == (int)$id_card[12];
}

/**
 * Validate Email
 */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate Phone
 */
function validate_phone($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    return strlen($phone) == 10 && substr($phone, 0, 1) == '0';
}

/**
 * Send Email (Simple)
 */
function send_email($to, $subject, $message, $from_name = 'NC-Admission') {
    $from_email = get_setting('contact_email', 'noreply@nc.ac.th');
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: $from_name <$from_email>" . "\r\n";
    
    return mail($to, $subject, $message, $headers);
}

/**
 * Generate Random String
 */
function generate_random_string($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $random_string = '';
    
    for ($i = 0; $i < $length; $i++) {
        $random_string .= $characters[rand(0, strlen($characters) - 1)];
    }
    
    return $random_string;
}

/**
 * Redirect Function
 */
function redirect($page) {
    header("Location: index.php?page=" . $page);
    exit();
}

/**
 * Check if File is Image
 */
function is_image($file) {
    $allowed = ['image/jpeg', 'image/png', 'image/jpg'];
    return in_array($file['type'], $allowed);
}

/**
 * Check if File is PDF
 */
function is_pdf($file) {
    return $file['type'] === 'application/pdf';
}

/**
 * Get Client IP
 */
function get_client_ip() {
    $ip = '';
    
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    
    return $ip;
}

/**
 * Get User Agent
 */
function get_user_agent() {
    return $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
}

/**
 * Log Activity
 */
function log_activity($admin_id, $action, $table_name = null, $record_id = null, $old_value = null, $new_value = null) {
    global $conn;
    
    $ip = get_client_ip();
    $user_agent = get_user_agent();
    
    $sql = "INSERT INTO activity_logs 
            (admin_id, action, table_name, record_id, old_value, new_value, ip_address, user_agent, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ississss", 
        $admin_id, 
        $action, 
        $table_name, 
        $record_id, 
        $old_value, 
        $new_value, 
        $ip, 
        $user_agent
    );
    
    return $stmt->execute();
}

/**
 * Format Currency
 */
function format_currency($amount, $decimals = 2) {
    return number_format($amount, $decimals, '.', ',') . ' บาท';
}

/**
 * Time Ago
 */
function time_ago($datetime) {
    $timestamp = strtotime($datetime);
    $difference = time() - $timestamp;
    
    $periods = [
        'ปี' => 31536000,
        'เดือน' => 2592000,
        'สัปดาห์' => 604800,
        'วัน' => 86400,
        'ชั่วโมง' => 3600,
        'นาที' => 60,
        'วินาที' => 1
    ];
    
    foreach ($periods as $unit => $value) {
        if ($difference >= $value) {
            $time = floor($difference / $value);
            return $time . ' ' . $unit . 'ที่แล้ว';
        }
    }
    
    return 'เมื่อสักครู่';
}

/**
 * Truncate Text
 */
function truncate_text($text, $length = 100, $suffix = '...') {
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    
    return mb_substr($text, 0, $length) . $suffix;
}

/**
 * Slug Generate (Thai Support)
 */
function generate_slug($text) {
    // แปลงเป็นตัวพิมพ์เล็ก
    $text = mb_strtolower($text, 'UTF-8');
    
    // แทนที่ช่องว่างด้วย -
    $text = str_replace(' ', '-', $text);
    
    // เอาอักขระพิเศษออก (เว้นไทย, อังกฤษ, ตัวเลข, -)
    $text = preg_replace('/[^ก-๙a-z0-9\-]/', '', $text);
    
    // เอา - ซ้ำออก
    $text = preg_replace('/-+/', '-', $text);
    
    // ตัด - ข้างหน้าและหลังออก
    $text = trim($text, '-');
    
    return $text;
}

/**
 * Check Admin Permission
 */
function check_permission($required_role = 'admin') {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        return false;
    }
    
    $role_hierarchy = [
        'superadmin' => 3,
        'admin' => 2,
        'staff' => 1
    ];
    
    $user_level = $role_hierarchy[$_SESSION['admin_role']] ?? 0;
    $required_level = $role_hierarchy[$required_role] ?? 0;
    
    return $user_level >= $required_level;
}

/**
 * Get Academic Year
 */
function get_academic_year() {
    $current_month = date('n');
    $current_year = date('Y');
    
    // ถ้าเดือนมกราคม-เมษายน ให้ใช้ปีปัจจุบัน
    // ถ้าเดือนพฤษภาคม-ธันวาคม ให้ใช้ปีถัดไป
    if ($current_month >= 5) {
        $academic_year = $current_year + 543 + 1;
    } else {
        $academic_year = $current_year + 543;
    }
    
    return $academic_year;
}

/**
 * Get Upload Path with uploads/ prefix
 */
function get_upload_path($path) {
    if (empty($path)) return '';
    
    // ถ้ามี uploads/ อยู่แล้ว ไม่ต้องเพิ่ม
    if (strpos($path, 'uploads/') === 0) {
        return $path;
    }
    
    // เพิ่ม uploads/ ข้างหน้า
    return 'uploads/' . $path;
}

/**
 * Check File Exists
 */
function file_exists_in_uploads($path) {
    if (empty($path)) return false;
    $full_path = '../uploads/' . $path;
    return file_exists($full_path);
}
?>