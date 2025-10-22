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
?>