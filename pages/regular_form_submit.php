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
    
    error_log("========== REGULAR FORM DATA ==========");
    error_log("JSON: " . $json);
    error_log("=======================================");
    
    $conn->begin_transaction();
    
    $id_card = isset($data['id_card']) ? trim($data['id_card']) : '';
    if (empty($id_card)) {
        throw new Exception('กรุณากรอกเลขบัตรประชาชน');
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
        sendJSON(['success' => false, 'message' => 'เลขบัตรประชาชนนี้เคยสมัครแล้ว']);
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
    
    // age
    $age_value = null;
    if (isset($data['age']) && (int)$data['age'] > 0) {
        $age_value = (int)$data['age'];
    }
    
    // graduation_year
    $graduation_year_value = $data['graduation_year'] ?? '';
    if ($graduation_year_value === '' || $graduation_year_value === null) {
        $graduation_year_value = (string)((int)$year + 3);
    }
    
    // apply_level - กำหนดจาก department ที่เลือก หรือจาก form
    $apply_level = null;
    if (isset($data['apply_level'])) {
        $apply_level = $data['apply_level'];
    }

    // เตรียมข้อมูลสำหรับ INSERT (เฉพาะคอลัมน์ที่มีข้อมูลจากฟอร์ม)
    $sql = "INSERT INTO students_regular (
        application_no, 
        academic_year,
        prefix, 
        firstname_th, 
        lastname_th, 
        nickname, 
        birth_date, 
        age, 
        nationality, 
        ethnicity, 
        religion, 
        blood_group, 
        id_card, 
        talents,
        address_no, 
        village_no, 
        road, 
        subdistrict, 
        district, 
        province, 
        postcode, 
        phone, 
        email, 
        current_class, 
        current_level, 
        current_school, 
        school_address, 
        gpa, 
        graduation_year, 
        apply_level,
        department_id, 
        photo_path, 
        transcript_path, 
        status, 
        status_note
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
    )";
    
    $variables_to_bind = [
        $application_no,                    // 1
        $academic_year_val,                 // 2
        $data['prefix'] ?? null,            // 3
        $data['firstname_th'] ?? null,      // 4
        $data['lastname_th'] ?? null,       // 5
        $data['nickname'] ?? null,          // 6
        $data['birth_date'] ?? null,        // 7
        $age_value,                         // 8
        $data['nationality'] ?? 'ไทย',      // 9
        $data['ethnicity'] ?? 'ไทย',        // 10
        $data['religion'] ?? 'พุทธ',        // 11
        $data['blood_group'] ?? null,       // 12
        $id_card,                           // 13
        $data['talents'] ?? null,           // 14
        $data['address_no'] ?? null,        // 15
        $data['village_no'] ?? null,        // 16
        $data['road'] ?? null,              // 17
        $data['subdistrict'] ?? null,       // 18
        $data['district'] ?? null,          // 19
        $data['province'] ?? null,          // 20
        $data['postcode'] ?? null,          // 21
        $data['phone'] ?? null,             // 22
        $data['email'] ?? null,             // 23
        $data['current_class'] ?? null,     // 24
        $data['current_level'] ?? null,     // 25
        $data['current_school'] ?? null,    // 26
        $data['school_address'] ?? null,    // 27
        (string)($data['gpa'] ?? null),     // 28
        $graduation_year_value,             // 29
        $apply_level,                       // 30
        (string)($data['department_id'] ?? null), // 31
        $photo_path,                        // 32
        $transcript_path,                   // 33
        $status,                            // 34
        $status_note                        // 35
    ];
    
    if (count($variables_to_bind) !== 35) {
        throw new Exception("Code error: " . count($variables_to_bind) . " values (expected 35)");
    }

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }

    $bind_string = str_repeat('s', 35);
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
        'message' => $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine()
    ]);
}

if (isset($conn)) {
    $conn->close();
}
?>