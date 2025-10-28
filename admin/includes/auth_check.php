<?php
/**
 * Authentication Check
 * ตรวจสอบว่า User Login แล้วหรือยัง
 */

// เริ่ม Session ถ้ายังไม่เริ่ม
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ตรวจสอบว่า Login แล้วหรือยัง
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // ยังไม่ Login -> Redirect ไป Login Page
    header("Location: login.php");
    exit();
}

// ตรวจสอบ Session Timeout (30 นาที)
$inactive_timeout = 1800; // 30 minutes
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $inactive_timeout)) {
    // Session หมดอายุ
    session_unset();
    session_destroy();
    header("Location: login.php?timeout=1");
    exit();
}

// อัพเดท Last Activity Time
$_SESSION['last_activity'] = time();

// ดึงข้อมูล Admin User
$admin_id = $_SESSION['admin_id'];
$admin_username = $_SESSION['admin_username'];
$admin_fullname = $_SESSION['admin_fullname'];
$admin_role = $_SESSION['admin_role'];
?>