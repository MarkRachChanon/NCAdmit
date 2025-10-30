<?php
/**
 * API: แก้ไขข้อมูลสาขาวิชา
 * สำหรับ Superadmin และ Admin เท่านั้น
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

// ตรวจสอบสิทธิ์ (เฉพาะ superadmin และ admin)
if (!in_array($_SESSION['admin_role'], ['superadmin', 'admin'])) {
    echo json_encode([
        'success' => false,
        'message' => 'คุณไม่มีสิทธิ์ในการแก้ไขสาขาวิชา'
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
$dept_id = intval($_POST['dept_id'] ?? 0);
$code = strtoupper(trim($_POST['code'] ?? ''));
$category_id = intval($_POST['category_id'] ?? 0);
$name_th = trim($_POST['name_th'] ?? '');
$name_en = trim($_POST['name_en'] ?? '');
$level = trim($_POST['level'] ?? '');
$study_type = trim($_POST['study_type'] ?? '');
$description = trim($_POST['description'] ?? '');

$open_quota = isset($_POST['open_quota']) ? 1 : 0;
$seats_quota = intval($_POST['seats_quota'] ?? 0);
$open_regular = isset($_POST['open_regular']) ? 1 : 0;
$seats_regular = intval($_POST['seats_regular'] ?? 0);

$is_active = isset($_POST['is_active']) ? 1 : 0;
$is_new = isset($_POST['is_new']) ? 1 : 0;
$highlight = trim($_POST['highlight'] ?? '');

// Validate ข้อมูล
$errors = [];

// ตรวจสอบ Department ID
if ($dept_id <= 0) {
    $errors[] = 'ไม่พบข้อมูลสาขาวิชา';
}

// ตรวจสอบว่ามีสาขานี้จริง
$old_data = null;
if ($dept_id > 0) {
    $check_sql = "SELECT * FROM departments WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $dept_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        $errors[] = 'ไม่พบข้อมูลสาขาวิชา';
    } else {
        $old_data = $check_result->fetch_assoc();
    }
    $check_stmt->close();
}

// ตรวจสอบรหัสสาขา
if (empty($code)) {
    $errors[] = 'กรุณากรอกรหัสสาขาวิชา';
} elseif (!preg_match('/^[A-Z0-9\-]+$/', $code)) {
    $errors[] = 'รหัสสาขาวิชาไม่ถูกต้อง (ใช้ได้เฉพาะ A-Z, 0-9, -)';
}

// ตรวจสอบว่ารหัสสาขาซ้ำหรือไม่ (ยกเว้นตัวเอง)
if (!empty($code) && $dept_id > 0) {
    $check_sql = "SELECT id FROM departments WHERE code = ? AND id != ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("si", $code, $dept_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $errors[] = 'รหัสสาขาวิชานี้มีอยู่ในระบบแล้ว';
    }
    $check_stmt->close();
}

// ตรวจสอบประเภทวิชา
if ($category_id <= 0) {
    $errors[] = 'กรุณาเลือกประเภทวิชา';
}

// ตรวจสอบชื่อสาขา
if (empty($name_th)) {
    $errors[] = 'กรุณากรอกชื่อสาขาวิชา (ภาษาไทย)';
}

if (empty($name_en)) {
    $errors[] = 'กรุณากรอกชื่อสาขาวิชา (ภาษาอังกฤษ)';
}

// ตรวจสอบระดับการศึกษา
$valid_levels = ['ปวช.', 'ปวส.', 'ปริญญาตรี'];
if (!in_array($level, $valid_levels)) {
    $errors[] = 'กรุณาเลือกระดับการศึกษา';
}

// ตรวจสอบประเภทการเรียน
$valid_study_types = ['ปกติ', 'ทวิภาคี', 'ปกติ+ทวิภาคี'];
if (!in_array($study_type, $valid_study_types)) {
    $errors[] = 'กรุณาเลือกประเภทการเรียน';
}

// ตรวจสอบจำนวนที่รับ
if ($open_quota && $seats_quota <= 0) {
    $errors[] = 'กรุณากรอกจำนวนที่รับรอบโควต้า';
}

if ($open_regular && $seats_regular <= 0) {
    $errors[] = 'กรุณากรอกจำนวนที่รับรอบปกติ';
}

if (!$open_quota && !$open_regular) {
    $errors[] = 'กรุณาเปิดรับสมัครอย่างน้อย 1 รอบ';
}

// ตรวจสอบ Highlight
$valid_highlights = ['', 'NEW', 'HOT', 'POPULAR'];
if (!in_array($highlight, $valid_highlights)) {
    $highlight = '';
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
    // Update ข้อมูล
    $sql = "UPDATE departments SET 
            category_id = ?,
            code = ?,
            name_th = ?,
            name_en = ?,
            level = ?,
            study_type = ?,
            seats_quota = ?,
            open_quota = ?,
            seats_regular = ?,
            open_regular = ?,
            description = ?,
            is_active = ?,
            is_new = ?,
            highlight = ?,
            updated_at = NOW()
            WHERE id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "isssssiiisisssi",
        $category_id,
        $code,
        $name_th,
        $name_en,
        $level,
        $study_type,
        $seats_quota,
        $open_quota,
        $seats_regular,
        $open_regular,
        $description,
        $is_active,
        $is_new,
        $highlight,
        $dept_id
    );

    if (!$stmt->execute()) {
        throw new Exception('ไม่สามารถบันทึกข้อมูลได้');
    }

    $stmt->close();

    // บันทึก Activity Log
    $log_action = "แก้ไขข้อมูลสาขาวิชา: $code - $name_th";
    
    $old_value = json_encode([
        'code' => $old_data['code'],
        'name_th' => $old_data['name_th'],
        'name_en' => $old_data['name_en'],
        'level' => $old_data['level'],
        'seats_quota' => $old_data['seats_quota'],
        'seats_regular' => $old_data['seats_regular']
    ], JSON_UNESCAPED_UNICODE);
    
    $new_value = json_encode([
        'code' => $code,
        'name_th' => $name_th,
        'name_en' => $name_en,
        'level' => $level,
        'seats_quota' => $seats_quota,
        'seats_regular' => $seats_regular
    ], JSON_UNESCAPED_UNICODE);

    log_activity(
        $_SESSION['admin_id'],
        $log_action,
        'departments',
        $dept_id,
        $old_value,
        $new_value
    );

    // Commit Transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'แก้ไขข้อมูลเรียบร้อยแล้ว',
        'data' => [
            'id' => $dept_id,
            'code' => $code,
            'name_th' => $name_th
        ]
    ]);

} catch (Exception $e) {
    // Rollback Transaction
    $conn->rollback();
    
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
    ]);
}
?>