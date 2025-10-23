<?php
/**
 * Save Session Data
 * บันทึกข้อมูลฟอร์มลง Session
 */

// Start Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set Header
header('Content-Type: application/json');

// Get POST Data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validate Data
if (!$data) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid data format'
    ]);
    exit;
}

$step = $data['step'] ?? null;
$formData = $data['data'] ?? null;

if (!$step || !$formData) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing step or data'
    ]);
    exit;
}

try {
    // Initialize session array if not exists
    if (!isset($_SESSION['quota_form_data'])) {
        $_SESSION['quota_form_data'] = [];
    }
    
    // Merge new data with existing data
    $_SESSION['quota_form_data'] = array_merge($_SESSION['quota_form_data'], $formData);
    
    // Save current step
    $_SESSION['current_step'] = $step;
    
    // Save timestamp
    $_SESSION['last_update'] = time();
    
    // Success Response
    echo json_encode([
        'success' => true,
        'message' => 'Data saved successfully',
        'step' => $step,
        'timestamp' => $_SESSION['last_update']
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error saving data: ' . $e->getMessage()
    ]);
}
?>