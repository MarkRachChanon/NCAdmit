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
            'message' => 'เกิดข้อผิดพลาดร้ายแรงบนเซิร์ฟเวอร์',
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
        throw new Exception('เชื่อมต่อ Database ไม่ได้');
    }
    
    $json = @file_get_contents('php://input');
    if (!$json) {
        throw new Exception('ไม่ได้รับข้อมูล JSON');
    }
    $data = @json_decode($json, true);
    if (!$data) {
        throw new Exception('JSON ไม่ถูกต้อง: ' . json_last_error_msg());
    }
    
    // ✅ LOG ข้อมูลที่ได้รับ
    error_log("========== FORM DATA ==========");
    error_log("graduation_year: '" . ($data['graduation_year'] ?? 'NOT_SET') . "'");
    error_log("age: '" . ($data['age'] ?? 'NOT_SET') . "'");
    error_log("JSON: " . $json);
    error_log("===============================");
    
    $conn->begin_transaction();
    
    $id_card = isset($data['id_card']) ? trim($data['id_card']) : '';
    if (empty($id_card)) {
        throw new Exception('กรุณากรอกเลขบัตรประชาชน');
    }
    
    $stmt = $conn->prepare("SELECT application_no FROM students_quota WHERE id_card = ? LIMIT 1");
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    $stmt->bind_param("s", $id_card);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $stmt->close();
        $conn->rollback();
        sendJSON(['success' => false, 'message' => 'เลขบัตรประชาชนนี้เคยสมัครแล้ว']);
    }
    $stmt->close();
    
    $year = isset($data['academic_year']) ? $data['academic_year'] : (date('Y') + 543 + 1);
    $last_id_stmt = $conn->query("SELECT MAX(id) AS max_id FROM students_quota");
    $max_id = $last_id_stmt->fetch_assoc()['max_id'] ?? 0;
    $new_id_part = str_pad($max_id + 1, 4, '0', STR_PAD_LEFT);
    $application_no = 'Q' . substr($year, -2) . $new_id_part;

    $uploads = isset($data['uploaded_files']) ? $data['uploaded_files'] : [];
    $academic_year_val = (string)$year;
    $status = 'pending'; 
    $status_note = null;
    
    $photo_path = isset($uploads['photo']['path']) ? $uploads['photo']['path'] : null;
    $transcript_path = isset($uploads['transcript']['path']) ? $uploads['transcript']['path'] : null;
    
    // ✅ age
    $age_value = null;
    if (isset($data['age']) && (int)$data['age'] > 0) {
        $age_value = (int)$data['age'];
    }
    
    // ✅✅✅ graduation_year - เก็บค่าที่ส่งมาตรงๆ (ไม่แปลง ไม่เช็ค)
    $graduation_year_value = $data['graduation_year'] ?? '';
    
    // ถ้าเป็น empty string ให้ใช้ปีปัจจุบัน + 3 (เพื่อไม่ให้เป็น 0000)
    if ($graduation_year_value === '' || $graduation_year_value === null) {
        $graduation_year_value = (string)((int)$year + 3);
        error_log("⚠️ graduation_year empty! Using fallback: $graduation_year_value");
    }
    
    error_log("✅ FINAL graduation_year: '$graduation_year_value'");

    $variables_to_bind = [
        $application_no, 
        $data['prefix'] ?? null, 
        $data['firstname_th'] ?? null, 
        $data['lastname_th'] ?? null, 
        $data['nickname'] ?? null, 
        $data['birth_date'] ?? null, 
        $age_value,
        $data['nationality'] ?? 'ไทย', 
        $data['ethnicity'] ?? 'ไทย', 
        $data['religion'] ?? 'พุทธ', 
        $data['blood_group'] ?? null, 
        $id_card, 
        $data['address_no'] ?? null, 
        $data['village_no'] ?? null, 
        $data['road'] ?? null, 
        $data['subdistrict'] ?? null, 
        $data['district'] ?? null, 
        $data['province'] ?? null, 
        $data['postcode'] ?? null, 
        $data['phone'] ?? null, 
        $data['email'] ?? null, 
        $data['current_class'] ?? null, 
        $data['current_level'] ?? null, 
        $data['current_school'] ?? null, 
        $data['school_address'] ?? null, 
        $data['current_major'] ?? null, 
        $graduation_year_value,
        (string)($data['gpa'] ?? 0), 
        $data['awards'] ?? null, 
        $data['talents'] ?? null, 
        (string)($data['department_id'] ?? null), 
        $academic_year_val, 
        $photo_path,
        $transcript_path,
        $status, 
        $status_note
    ];
    
    if (count($variables_to_bind) !== 36) {
        throw new Exception("Code error: " . count($variables_to_bind) . " values");
    }

    $sql = "INSERT INTO students_quota (
        application_no, prefix, firstname_th, lastname_th, nickname, 
        birth_date, age, nationality, ethnicity, religion, blood_group, 
        id_card, address_no, village_no, road, subdistrict, district, 
        province, postcode, phone, email, current_class, current_level, 
        current_school, school_address, current_major, graduation_year, gpa, 
        awards, talents, department_id, academic_year, 
        photo_path, transcript_path, status, status_note
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
    )";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }

    $bind_string = str_repeat('s', 36);
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
    
    error_log("✅ SUCCESS - Application: $application_no, graduation_year saved: $graduation_year_value");
    
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
?>