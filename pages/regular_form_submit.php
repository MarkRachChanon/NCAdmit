<?php
@ob_start(); 
@ini_set('display_errors', 'Off');
@ini_set('log_errors', 'On');
@error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING); 
@header('Content-Type: application/json; charset=utf-8');

function sendJSON($data) {
    @ob_clean(); 
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_RECOVERABLE_ERROR])) {
        @ob_clean();
        sendJSON([
            'success' => false,
            'message' => 'เกิดข้อผิดพลาดร้ายแรงบนเซิร์ฟเวอร์ (Fatal Error)',
            'error_details' => $error['message'],
            'file' => basename($error['file']),
            'line' => $error['line']
        ]);
    }
});

@session_start();

try {
    $config_file = __DIR__ . '/../config/database.php';
    if (!file_exists($config_file)) {
        throw new Exception('ไม่พบไฟล์ config/database.php');
    }
    @require_once $config_file;
    @require_once __DIR__ . '/../includes/functions.php'; 

    if (!isset($conn) || $conn->connect_error) {
        throw new Exception('Database Error: เชื่อมต่อ Database ไม่ได้ กรุณาตรวจสอบไฟล์ config/database.php');
    }
    
    $json = @file_get_contents('php://input');
    if (!$json) {
        throw new Exception('ไม่ได้รับข้อมูล JSON');
    }
    $data = @json_decode($json, true);
    if (!$data) {
        throw new Exception('JSON ไม่ถูกต้อง: ' . json_last_error_msg());
    }
    
    error_log("========== REGULAR FORM DATA ==========");
    error_log("JSON: " . $json);
    error_log("=======================================");
    
    $conn->begin_transaction();
    
    $id_card = isset($data['id_card']) ? trim($data['id_card']) : '';
    if (empty($id_card) || !preg_match('/^\d{13}$/', str_replace('-', '', $id_card))) {
        throw new Exception('กรุณากรอกเลขบัตรประชาชนให้ถูกต้อง');
    }
    
    // ตรวจสอบบัตรประชาชนซ้ำ
    $stmt = $conn->prepare("SELECT application_no FROM students_regular WHERE id_card = ? LIMIT 1");
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    $stmt->bind_param("s", $id_card);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $stmt->close();
        $conn->rollback();
        sendJSON([
            'success' => false, 
            'message' => 'DUPLICATE_ID_CARD',
            'user_message' => 'เลขบัตรประชาชน ' . $id_card . ' นี้ เคยใช้สมัครในระบบแล้ว'
        ]);
    }
    $stmt->close();
    
    // สร้างเลขที่ใบสมัคร
    $year = isset($data['academic_year']) ? $data['academic_year'] : (date('Y') + 543 + 1);
    $last_id_stmt = $conn->query("SELECT MAX(id) AS max_id FROM students_regular");
    $max_id = $last_id_stmt->fetch_assoc()['max_id'] ?? 0;
    $new_id_part = str_pad($max_id + 1, 4, '0', STR_PAD_LEFT);
    $application_no = 'R' . substr($year, -2) . $new_id_part;

    $uploads = isset($data['uploaded_files']) ? $data['uploaded_files'] : [];
    $academic_year_val = (string)$year;
    $status = 'pending'; 
    $status_note = null;
    
    $photo_path = isset($uploads['photo']['path']) ? $uploads['photo']['path'] : null;
    $transcript_path = isset($uploads['transcript']['path']) ? $uploads['transcript']['path'] : null;
    
    // Type Casting สำหรับตัวเลข
    $age_value = is_numeric($data['age'] ?? null) && (int)$data['age'] > 0 ? (int)$data['age'] : null;
    $height_value = is_numeric($data['height'] ?? null) && (int)$data['height'] > 0 ? (int)$data['height'] : null;
    $weight_value = is_numeric($data['weight'] ?? null) && (int)$data['weight'] > 0 ? (int)$data['weight'] : null;
    $father_income_value = is_numeric($data['father_income'] ?? null) && (float)$data['father_income'] >= 0 ? (float)$data['father_income'] : null;
    $mother_income_value = is_numeric($data['mother_income'] ?? null) && (float)$data['mother_income'] >= 0 ? (float)$data['mother_income'] : null;
    $guardian_income_value = is_numeric($data['guardian_income'] ?? null) && (float)$data['guardian_income'] >= 0 ? (float)$data['guardian_income'] : null;
    $gpa_value = is_numeric($data['gpa'] ?? null) && (float)$data['gpa'] >= 0 ? (float)$data['gpa'] : null;
    $department_id_value = is_numeric($data['department_id'] ?? null) && (int)$data['department_id'] > 0 ? (int)$data['department_id'] : null;
    
    // ค่าอื่นๆ
    $graduation_year_value = $data['graduation_year'] ?? '';
    if ($graduation_year_value === '' || $graduation_year_value === null) {
        $graduation_year_value = (string)((int)$year); 
    }
    
    $apply_level = $data['education_level_apply'] ?? null;

    // 🚀 FIX: เพิ่ม 'ss' ด้านหลังสุดเพื่อให้ครบ 70 ตัว (เดิม 68)
    // s=string, i=integer, d=double (decimal/float)
    $bind_string = "sssiisssiidsisssississssssssssssssssiisisssisssisssissssisiidsssiissss"; 

    $sql = "INSERT INTO students_regular (
        application_no, academic_year, prefix, firstname_th, lastname_th, nickname, 
        birth_date, birth_province, age, height, weight, nationality, ethnicity, 
        religion, blood_group, id_card, disability, disability_type, talents,
        address_no, village_no, village_name, soi, road, subdistrict, district, 
        province, postcode, phone_home, phone, line_id, email, 
        father_prefix, father_firstname, father_lastname, father_occupation, 
        father_income, father_phone, father_status, father_disability, 
        father_disability_type, mother_prefix, mother_firstname, mother_lastname, 
        mother_occupation, mother_income, mother_phone, mother_status, 
        mother_disability, mother_disability_type, parents_status, 
        guardian_prefix, guardian_firstname, guardian_lastname, guardian_relation, 
        guardian_occupation, guardian_income, guardian_phone, 
        current_class, current_level, current_school, school_address, 
        gpa, graduation_year, apply_level, department_id, 
        photo_path, transcript_path, status, status_note
    ) VALUES (
        " . rtrim(str_repeat('?,', 70), ',') . "
    )";
    
    $variables_to_bind = [
        $application_no, $academic_year_val, $data['prefix'] ?? null, $data['firstname_th'] ?? null, 
        $data['lastname_th'] ?? null, $data['nickname'] ?? null, $data['birth_date'] ?? null, 
        $data['birth_province'] ?? null, $age_value, $height_value, 
        $weight_value, $data['nationality'] ?? 'ไทย', $data['ethnicity'] ?? 'ไทย', 
        $data['religion'] ?? 'พุทธ', $data['blood_group'] ?? null, $id_card, 
        $data['disability'] ?? 'ไม่มี', $data['disability_type'] ?? null, $data['talents'] ?? null,
        $data['address_no'] ?? null, $data['village_no'] ?? null, $data['village_name'] ?? null, 
        $data['soi'] ?? null, $data['road'] ?? null, $data['subdistrict'] ?? null, $data['district'] ?? null, 
        $data['province'] ?? null, $data['postcode'] ?? null, $data['phone_home'] ?? null, 
        $data['phone'] ?? null, $data['line_id'] ?? null, $data['email'] ?? null, 
        $data['father_prefix'] ?? null, $data['father_firstname'] ?? null, $data['father_lastname'] ?? null, 
        $data['father_occupation'] ?? null, $father_income_value, $data['father_phone'] ?? null, 
        $data['father_status'] ?? 'มีชีวิต', $data['father_disability'] ?? 'ไม่มี', 
        $data['father_disability_type'] ?? null, $data['mother_prefix'] ?? null, $data['mother_firstname'] ?? null, 
        $data['mother_lastname'] ?? null, $data['mother_occupation'] ?? null, $mother_income_value, 
        $data['mother_phone'] ?? null, $data['mother_status'] ?? 'มีชีวิต', $data['mother_disability'] ?? 'ไม่มี', 
        $data['mother_disability_type'] ?? null, $data['parents_status'] ?? null, 
        $data['guardian_prefix'] ?? null, $data['guardian_firstname'] ?? null, $data['guardian_lastname'] ?? null, 
        $data['guardian_relation'] ?? null, $data['guardian_occupation'] ?? null, $guardian_income_value, 
        $data['guardian_phone'] ?? null, $data['current_class'] ?? null, $data['current_level'] ?? null, 
        $data['current_school'] ?? null, $data['school_address'] ?? null, 
        $gpa_value, $graduation_year_value, $apply_level, $department_id_value, 
        $photo_path, $transcript_path, $status, $status_note // 🚨 สองตัวสุดท้าย: $status, $status_note
    ];
    
    // บรรทัดนี้จะถูกตรวจสอบว่าถูกต้อง (70 == 70)
    if (strlen($bind_string) !== count($variables_to_bind)) {
        throw new Exception("Code error: Bind string length (" . strlen($bind_string) . ") does not match value count (" . count($variables_to_bind) . ")");
    }

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }

    $bind_args = [$bind_string];
    foreach ($variables_to_bind as &$value) {
        $bind_args[] = &$value;
    }
    
    if (!call_user_func_array([$stmt, 'bind_param'], $bind_args)) {
        throw new Exception('Bind failed: ' . $stmt->error);
    }
    
    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }
    
    $stmt->close();
    $conn->commit();
    
    error_log("✅ SUCCESS - Application: $application_no");
    
    sendJSON([
        'success' => true,
        'application_no' => $application_no,
        'name' => trim(($data['firstname_th'] ?? '') . ' ' . ($data['lastname_th'] ?? ''))
    ]);
    
} catch (Exception $e) {
    if (isset($conn) && property_exists($conn, 'in_transaction') && $conn->in_transaction) {
        $conn->rollback();
    }
    
    error_log("❌ ERROR: " . $e->getMessage());
    
    sendJSON([
        'success' => false,
        'message' => 'Database operation failed: ' . $e->getMessage(), 
        'file' => basename($e->getFile()),
        'line' => $e->getLine()
    ]);
}

if (isset($conn)) {
    $conn->close();
}
?>