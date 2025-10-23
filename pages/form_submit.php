<?php
/**
 * Form Submit Handler - รับข้อมูลและบันทึกลงฐานข้อมูล
 */

// เริ่ม session
session_start();

// เชื่อมต่อฐานข้อมูล
require_once '../includes/db_connect.php';

// ตั้งค่า response เป็น JSON
header('Content-Type: application/json');

// ตรวจสอบว่าเป็น POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

try {
    // ===========================================
    // 1. รับข้อมูลจากฟอร์ม
    // ===========================================
    
    // ข้อมูลส่วนตัว
    $prefix = $_POST['prefix'] ?? '';
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $id_card_number = $_POST['id_card_number'] ?? '';
    $birth_date = $_POST['birth_date'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $nationality = $_POST['nationality'] ?? '';
    $religion = $_POST['religion'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $line_id = $_POST['line_id'] ?? '';

    // ที่อยู่ปัจจุบัน
    $current_address = $_POST['current_address'] ?? '';
    $current_subdistrict = $_POST['current_subdistrict'] ?? '';
    $current_district = $_POST['current_district'] ?? '';
    $current_province = $_POST['current_province'] ?? '';
    $current_postal_code = $_POST['current_postal_code'] ?? '';

    // การศึกษา
    $previous_school = $_POST['previous_school'] ?? '';
    $education_level = $_POST['education_level'] ?? '';
    $gpax = $_POST['gpax'] ?? 0;

    // ข้อมูลผู้ปกครอง
    $parent_name = $_POST['parent_name'] ?? '';
    $parent_relation = $_POST['parent_relation'] ?? '';
    $parent_phone = $_POST['parent_phone'] ?? '';
    $parent_occupation = $_POST['parent_occupation'] ?? '';

    // สาขาที่สมัคร
    $department_1 = $_POST['department_1'] ?? 0;

    // ===========================================
    // 2. Validate ข้อมูล
    // ===========================================
    
    if (empty($first_name) || empty($last_name) || empty($id_card_number)) {
        throw new Exception('กรุณากรอกข้อมูลให้ครบถ้วน');
    }

    if (empty($department_1) || $department_1 == 0) {
        throw new Exception('กรุณาเลือกสาขาวิชาที่ต้องการสมัคร');
    }

    // ตรวจสอบว่ามีการสมัครซ้ำหรือไม่
    $check_stmt = $conn->prepare("SELECT id FROM students_quota WHERE id_card_number = ? AND academic_year = ?");
    $current_year = date('Y') + 543;
    $check_stmt->bind_param("si", $id_card_number, $current_year);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        throw new Exception('เลขบัตรประชาชนนี้ได้สมัครไปแล้วในปีการศึกษานี้');
    }

    // ===========================================
    // 3. Upload ไฟล์
    // ===========================================
    
    $upload_dir = '../uploads/';
    $uploaded_files = [];

    // กำหนด directory สำหรับแต่ละประเภทไฟล์
    $file_types = [
        'photo' => 'photos/',
        'id_card_file' => 'id_cards/',
        'house_registration' => 'house_registrations/',
        'transcript' => 'transcripts/'
    ];

    foreach ($file_types as $field_name => $sub_dir) {
        if (isset($_FILES[$field_name]) && $_FILES[$field_name]['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES[$field_name];
            
            // สร้างชื่อไฟล์ใหม่
            $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $timestamp = time();
            $random = bin2hex(random_bytes(8));
            $new_filename = "{$field_name}_{$timestamp}_{$random}.{$file_ext}";
            
            // กำหนด path
            $target_dir = $upload_dir . $sub_dir;
            
            // สร้าง directory ถ้ายังไม่มี
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }
            
            $target_path = $target_dir . $new_filename;
            
            // ย้ายไฟล์
            if (move_uploaded_file($file['tmp_name'], $target_path)) {
                $uploaded_files[$field_name] = $sub_dir . $new_filename;
            } else {
                throw new Exception("ไม่สามารถอัปโหลดไฟล์ {$field_name} ได้");
            }
        }
    }

    // ===========================================
    // 4. Generate เลขที่ใบสมัคร
    // ===========================================
    
    // Format: Q{ปี}{เดือน}{วัน}-{running number 4 หลัก}
    // ตัวอย่าง: Q680101-0001
    
    $date_prefix = date('ymd'); // yymmdd
    
    // หา running number วันนี้
    $sql_count = "SELECT COUNT(*) as count FROM students_quota 
                  WHERE DATE(created_at) = CURDATE()";
    $result_count = $conn->query($sql_count);
    $row_count = $result_count->fetch_assoc();
    $running_number = str_pad($row_count['count'] + 1, 4, '0', STR_PAD_LEFT);
    
    $application_number = "Q{$date_prefix}-{$running_number}";

    // ===========================================
    // 5. บันทึกข้อมูลลงฐานข้อมูล
    // ===========================================
    
    $sql = "INSERT INTO students_quota (
        application_number,
        academic_year,
        prefix,
        first_name,
        last_name,
        id_card_number,
        birth_date,
        gender,
        nationality,
        religion,
        phone,
        email,
        line_id,
        current_address,
        current_subdistrict,
        current_district,
        current_province,
        current_postal_code,
        previous_school,
        education_level,
        gpax,
        parent_name,
        parent_relation,
        parent_phone,
        parent_occupation,
        department_1,
        photo_url,
        id_card_url,
        house_registration_url,
        transcript_url,
        status,
        created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
    
    $stmt = $conn->prepare($sql);
    
    $stmt->bind_param(
        "sisssssssssssssssssdssssississs",
        $application_number,
        $current_year,
        $prefix,
        $first_name,
        $last_name,
        $id_card_number,
        $birth_date,
        $gender,
        $nationality,
        $religion,
        $phone,
        $email,
        $line_id,
        $current_address,
        $current_subdistrict,
        $current_district,
        $current_province,
        $current_postal_code,
        $previous_school,
        $education_level,
        $gpax,
        $parent_name,
        $parent_relation,
        $parent_phone,
        $parent_occupation,
        $department_1,
        $uploaded_files['photo'] ?? null,
        $uploaded_files['id_card_file'] ?? null,
        $uploaded_files['house_registration'] ?? null,
        $uploaded_files['transcript'] ?? null
    );
    
    if (!$stmt->execute()) {
        throw new Exception('ไม่สามารถบันทึกข้อมูลได้: ' . $stmt->error);
    }

    $student_id = $conn->insert_id;

    // ===========================================
    // 6. ส่ง Response กลับ
    // ===========================================
    
    echo json_encode([
        'success' => true,
        'message' => 'บันทึกข้อมูลสำเร็จ',
        'application_number' => $application_number,
        'student_id' => $student_id
    ]);

    // Log การสมัคร
    $log_sql = "INSERT INTO application_logs (student_id, action, details, created_at) 
                VALUES (?, 'submitted', 'สมัครเรียนรอบโควต้า', NOW())";
    $log_stmt = $conn->prepare($log_sql);
    $log_stmt->bind_param("i", $student_id);
    $log_stmt->execute();

} catch (Exception $e) {
    // ถ้าเกิด error ลบไฟล์ที่อัปโหลดไปแล้ว
    if (!empty($uploaded_files)) {
        foreach ($uploaded_files as $file_path) {
            $full_path = $upload_dir . $file_path;
            if (file_exists($full_path)) {
                unlink($full_path);
            }
        }
    }

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

// ปิดการเชื่อมต่อ
if (isset($stmt)) $stmt->close();
if (isset($check_stmt)) $check_stmt->close();
$conn->close();
?>