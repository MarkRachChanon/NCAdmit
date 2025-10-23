<?php
/**
 * Load Session Data
 * โหลดข้อมูลฟอร์มจาก Session
 */

// Start Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set Header
header('Content-Type: application/json');

try {
    // Get session data
    $quota_form_data = $_SESSION['quota_form_data'] ?? null;
    $current_step = $_SESSION['current_step'] ?? 1;
    $last_update = $_SESSION['last_update'] ?? null;
    
    // Format last update time
    $last_update_formatted = null;
    if ($last_update) {
        $last_update_formatted = date('Y-m-d H:i:s', $last_update);
    }
    
    // Response
    echo json_encode([
        'success' => true,
        'quota_form_data' => $quota_form_data,
        'current_step' => $current_step,
        'last_update' => $last_update,
        'last_update_formatted' => $last_update_formatted,
        'has_data' => !empty($quota_form_data)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error loading data: ' . $e->getMessage(),
        'quota_form_data' => null,
        'current_step' => 1,
        'last_update' => null,
        'has_data' => false
    ]);
}
?>