<?php
/**
 * Clear Session Data
 * ล้างข้อมูลฟอร์มออกจาก Session
 */

// Start Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set Header
header('Content-Type: application/json');

try {
    // Clear form data
    unset($_SESSION['quota_form_data']);
    unset($_SESSION['current_step']);
    unset($_SESSION['last_update']);
    
    // Success Response
    echo json_encode([
        'success' => true,
        'message' => 'Session cleared successfully'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error clearing session: ' . $e->getMessage()
    ]);
}
?>