<?php
/**
 * Database Configuration
 * NCAdmit - Nakhon Pathom College Admission System
 */

// Database Configuration
define('DB_HOST', 'thsv25.hostatom.com');
define('DB_USER', 'ncitproj_mark');
define('DB_PASS', 'th5GD3$hqN_sbah8');
define('DB_NAME', 'ncadmit_db');
define('DB_CHARSET', 'utf8mb4');

// Create Connection
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check Connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Set Charset
    $conn->set_charset(DB_CHARSET);
    
} catch (Exception $e) {
    die("Database Error: " . $e->getMessage());
}

// Timezone
date_default_timezone_set('Asia/Bangkok');

// Error Reporting (เปิดตอน Development, ปิดตอน Production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>