<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';

try {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (!$data) {
        throw new Exception('ข้อมูลไม่ถูกต้อง');
    }
    
    $id_card = isset($data['id_card']) ? trim($data['id_card']) : '';
    $phone = isset($data['phone']) ? trim($data['phone']) : '';
    
    if (empty($id_card) || empty($phone)) {
        throw new Exception('กรุณากรอกข้อมูลให้ครบถ้วน');
    }
    
    $id_card_clean = preg_replace('/\D/', '', $id_card);
    $phone_clean = preg_replace('/\D/', '', $phone);
    
    error_log("========== CHECK STATUS ==========");
    error_log("Input id_card: $id_card → Clean: $id_card_clean");
    error_log("Input phone: $phone → Clean: $phone_clean");
    
    $applications = []; // 🎯 เก็บผลลัพธ์ทั้งหมด
    
    // ✅ ตรวจสอบใน students_quota
    $stmt = $conn->prepare("
        SELECT 
            sq.*,
            d.name_th as department_name,
            d.level as department_level,
            dc.name as category_name,
            'quota' as form_type
        FROM students_quota sq
        LEFT JOIN departments d ON sq.department_id = d.id
        LEFT JOIN department_categories dc ON d.category_id = dc.id
        WHERE REPLACE(REPLACE(REPLACE(sq.id_card, '-', ''), ' ', ''), '_', '') = ? 
          AND REPLACE(REPLACE(REPLACE(sq.phone, '-', ''), ' ', ''), '_', '') = ?
        LIMIT 1
    ");
    
    if (!$stmt) {
        error_log("❌ Prepare failed: " . $conn->error);
        throw new Exception('Database error');
    }
    
    $stmt->bind_param("ss", $id_card_clean, $phone_clean);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $applications[] = $result->fetch_assoc();
        error_log("✅ Found in students_quota");
    }
    $stmt->close();
    
    // ✅ ตรวจสอบใน students_regular
    $stmt = $conn->prepare("
        SELECT 
            sr.*,
            d.name_th as department_name,
            d.level as department_level,
            dc.name as category_name,
            'regular' as form_type
        FROM students_regular sr
        LEFT JOIN departments d ON sr.department_id = d.id
        LEFT JOIN department_categories dc ON d.category_id = dc.id
        WHERE REPLACE(REPLACE(REPLACE(sr.id_card, '-', ''), ' ', ''), '_', '') = ? 
          AND REPLACE(REPLACE(REPLACE(sr.phone, '-', ''), ' ', ''), '_', '') = ?
        LIMIT 1
    ");
    
    if (!$stmt) {
        error_log("❌ Prepare failed: " . $conn->error);
        throw new Exception('Database error');
    }
    
    $stmt->bind_param("ss", $id_card_clean, $phone_clean);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $applications[] = $result->fetch_assoc();
        error_log("✅ Found in students_regular");
    }
    $stmt->close();
    
    if (count($applications) > 0) {
        error_log("✅ Total found: " . count($applications));
        error_log("==================================");
        
        echo json_encode([
            'success' => true,
            'count' => count($applications),
            'data' => $applications
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    error_log("❌ Not found in both tables");
    error_log("==================================");
    
    echo json_encode([
        'success' => false,
        'message' => 'ไม่พบข้อมูลการสมัคร'
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    error_log("❌ Exception: " . $e->getMessage());
    error_log("==================================");
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

if (isset($conn)) {
    $conn->close();
}
?>