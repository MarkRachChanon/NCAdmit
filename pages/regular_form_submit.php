<?php
@ob_start();
@ini_set('display_errors', 'Off');
@ini_set('log_errors', 'On');
@error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
@header('Content-Type: application/json; charset=utf-8');

function sendJSON($data)
{
    @ob_clean();
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

register_shutdown_function(function () {
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
        throw new Exception('Database Error: เชื่อมต่อ Database ไม่ได้');
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
    error_log(print_r($data, true));
    error_log("=======================================");

    $conn->begin_transaction();

    $id_card = isset($data['id_card']) ? trim($data['id_card']) : '';
    if (empty($id_card)) {
        throw new Exception('กรุณากรอกเลขบัตรประชาชน');
    }

    // ตรวจสอบซ้ำ
    $stmt = $conn->prepare("SELECT application_no FROM students_regular WHERE id_card = ? LIMIT 1");
    $stmt->bind_param("s", $id_card);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $stmt->close();
        $conn->rollback();
        sendJSON([
            'success' => false,
            'message' => 'เลขบัตรประชาชนนี้เคยสมัครแล้ว'
        ]);
    }
    $stmt->close();

    // ดึงปีการศึกษาจาก settings
    $year_query = $conn->query("SELECT setting_value FROM settings WHERE setting_key = 'academic_year' LIMIT 1");
    if ($year_query && $year_query->num_rows > 0) {
        $year = $year_query->fetch_assoc()['setting_value'];
    } else {
        $year = (date('Y') + 543 + 1); // fallback
    }
    error_log("📅 Using academic_year from settings: $year");

    $uploads = isset($data['uploaded_files']) ? $data['uploaded_files'] : [];

    $photo_path = isset($uploads['photo']['path']) ? $uploads['photo']['path'] : null;
    $transcript_path = isset($uploads['transcript']['path']) ? $uploads['transcript']['path'] : null;

    // 🔧 ปรับ Type Casting ให้ถูกต้อง
    $age = (isset($data['age']) && is_numeric($data['age']) && (int)$data['age'] > 0) ? (int)$data['age'] : null;
    $height = (isset($data['height']) && is_numeric($data['height']) && (int)$data['height'] > 0) ? (int)$data['height'] : null;
    $weight = (isset($data['weight']) && is_numeric($data['weight']) && (int)$data['weight'] > 0) ? (int)$data['weight'] : null;

    $father_income = (isset($data['father_income']) && is_numeric($data['father_income']) && (float)$data['father_income'] >= 0) ? (float)$data['father_income'] : null;
    $mother_income = (isset($data['mother_income']) && is_numeric($data['mother_income']) && (float)$data['mother_income'] >= 0) ? (float)$data['mother_income'] : null;
    $guardian_income = (isset($data['guardian_income']) && is_numeric($data['guardian_income']) && (float)$data['guardian_income'] >= 0) ? (float)$data['guardian_income'] : null;

    $gpa = (isset($data['gpa']) && is_numeric($data['gpa']) && (float)$data['gpa'] >= 0) ? (float)$data['gpa'] : null;

    $department_id = (isset($data['department_id']) && is_numeric($data['department_id']) && (int)$data['department_id'] > 0) ? (int)$data['department_id'] : null;

    $graduation_year = $data['graduation_year'] ?? (string)$year;
    $apply_level = $data['education_level_apply'] ?? null;

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
        ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?
    )";

    // 🎯 FIX: Bind string ที่ถูกต้อง 100%
    // s=string, i=integer, d=double(decimal)
    $bind_string =
        "ss"      . // 1-2: application_no, academic_year
        "ssss"    . // 3-6: prefix, firstname_th, lastname_th, nickname
        "ss"      . // 7-8: birth_date, birth_province
        "iii"     . // 9-11: age, height, weight (integer)
        "sss"     . // 12-14: nationality, ethnicity, religion
        "sss"     . // 15-17: blood_group, id_card, disability
        "ss"      . // 18-19: disability_type, talents
        "sssssss" . // 20-26: address_no, village_no, village_name, soi, road, subdistrict, district
        "sss"     . // 27-29: province, postcode, phone_home
        "sss"     . // 30-32: phone, line_id, email
        "sss"     . // 33-35: father_prefix, father_firstname, father_lastname
        "s"       . // 36: father_occupation
        "d"       . // 37: father_income (decimal)
        "sss"     . // 38-40: father_phone, father_status, father_disability
        "s"       . // 41: father_disability_type
        "sss"     . // 42-44: mother_prefix, mother_firstname, mother_lastname
        "s"       . // 45: mother_occupation
        "d"       . // 46: mother_income (decimal)
        "sss"     . // 47-49: mother_phone, mother_status, mother_disability
        "ss"      . // 50-51: mother_disability_type, parents_status
        "ssss"    . // 52-55: guardian_prefix, guardian_firstname, guardian_lastname, guardian_relation
        "s"       . // 56: guardian_occupation
        "d"       . // 57: guardian_income (decimal)
        "s"       . // 58: guardian_phone
        "ssss"    . // 59-62: current_class, current_level, current_school, school_address
        "d"       . // 63: gpa (decimal)
        "ss"      . // 64-65: graduation_year, apply_level
        "i"       . // 66: department_id (integer)
        "ssss";     // 67-70: photo_path, transcript_path, status, status_note

    $variables = [
        null,                                   // ✅ 1 - application_no จะ UPDATE ทีหลัง
        (string)$year,                          // 2
        $data['prefix'] ?? null,                // 3
        $data['firstname_th'] ?? null,          // 4
        $data['lastname_th'] ?? null,           // 5
        $data['nickname'] ?? null,              // 6
        $data['birth_date'] ?? null,            // 7
        $data['birth_province'] ?? null,        // 8
        $age,                                   // 9 - integer
        $height,                                // 10 - integer
        $weight,                                // 11 - integer
        $data['nationality'] ?? 'ไทย',          // 12
        $data['ethnicity'] ?? 'ไทย',            // 13
        $data['religion'] ?? 'พุทธ',            // 14
        $data['blood_group'] ?? null,           // 15
        $id_card,                               // 16
        $data['disability'] ?? 'ไม่มี',         // 17
        $data['disability_type'] ?? null,       // 18
        $data['talents'] ?? null,               // 19
        $data['address_no'] ?? null,            // 20
        $data['village_no'] ?? null,            // 21
        $data['village_name'] ?? null,          // 22
        $data['soi'] ?? null,                   // 23
        $data['road'] ?? null,                  // 24
        $data['subdistrict'] ?? null,           // 25
        $data['district'] ?? null,              // 26
        $data['province'] ?? null,              // 27
        $data['postcode'] ?? null,              // 28
        $data['phone_home'] ?? null,            // 29
        $data['phone'] ?? null,                 // 30
        $data['line_id'] ?? null,               // 31
        $data['email'] ?? null,                 // 32
        $data['father_prefix'] ?? null,         // 33
        $data['father_firstname'] ?? null,      // 34
        $data['father_lastname'] ?? null,       // 35
        $data['father_occupation'] ?? null,     // 36
        $father_income,                         // 37 - decimal
        $data['father_phone'] ?? null,          // 38
        $data['father_status'] ?? 'มีชีวิต',    // 39
        $data['father_disability'] ?? 'ไม่มี',  // 40
        $data['father_disability_type'] ?? null, // 41
        $data['mother_prefix'] ?? null,         // 42
        $data['mother_firstname'] ?? null,      // 43
        $data['mother_lastname'] ?? null,       // 44
        $data['mother_occupation'] ?? null,     // 45
        $mother_income,                         // 46 - decimal
        $data['mother_phone'] ?? null,          // 47
        $data['mother_status'] ?? 'มีชีวิต',    // 48
        $data['mother_disability'] ?? 'ไม่มี',  // 49
        $data['mother_disability_type'] ?? null, // 50
        $data['parents_status'] ?? null,        // 51
        $data['guardian_prefix'] ?? null,       // 52
        $data['guardian_firstname'] ?? null,    // 53
        $data['guardian_lastname'] ?? null,     // 54
        $data['guardian_relation'] ?? null,     // 55
        $data['guardian_occupation'] ?? null,   // 56
        $guardian_income,                       // 57 - decimal
        $data['guardian_phone'] ?? null,        // 58
        $data['current_class'] ?? null,         // 59
        $data['current_level'] ?? null,         // 60
        $data['current_school'] ?? null,        // 61
        $data['school_address'] ?? null,        // 62
        $gpa,                                   // 63 - decimal
        $graduation_year,                       // 64
        $apply_level,                           // 65
        $department_id,                         // 66 - integer
        $photo_path,                            // 67
        $transcript_path,                       // 68
        'pending',                              // 69
        null                                    // 70
    ];

    if (strlen($bind_string) !== count($variables)) {
        throw new Exception("Bind error: " . strlen($bind_string) . " types vs " . count($variables) . " values");
    }

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }

    $bind_args = [$bind_string];
    foreach ($variables as &$value) {
        $bind_args[] = &$value;
    }

    if (!call_user_func_array([$stmt, 'bind_param'], $bind_args)) {
        throw new Exception('Bind failed: ' . $stmt->error);
    }

    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }

    // ✅ ดึง ID ที่เพิ่งสร้าง
    $inserted_id = $conn->insert_id;
    $stmt->close();

    // ✅ สร้าง application_no จาก ID (ใช้ R สำหรับ Regular)
    $year_suffix = substr($year, -2);
    $id_part = str_pad($inserted_id, 5, '0', STR_PAD_LEFT);
    $application_no = 'R' . $year_suffix . $id_part;

    error_log("✅ Generated application_no from ID: '$application_no' (ID: $inserted_id)");

    // ✅ UPDATE application_no
    $update_stmt = $conn->prepare("UPDATE students_regular SET application_no = ? WHERE id = ?");
    if (!$update_stmt) {
        throw new Exception('Update prepare failed: ' . $conn->error);
    }

    $update_stmt->bind_param("si", $application_no, $inserted_id);
    if (!$update_stmt->execute()) {
        throw new Exception('Update execute failed: ' . $update_stmt->error);
    }
    $update_stmt->close();

    $conn->commit();

    error_log("✅ SUCCESS - ID: $inserted_id, Application: $application_no");

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
        'message' => $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine()
    ]);
}

if (isset($conn)) {
    $conn->close();
}
