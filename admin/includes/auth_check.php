<?php

/**
 * Authentication Check
 * ตรวจสอบว่า User Login แล้วหรือยัง + ตรวจสอบสิทธิ์
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

// โหลดระบบสิทธิ์
require_once __DIR__ . '/permissions.php';

// ตรวจสอบสิทธิ์การเข้าถึงหน้าปัจจุบัน
$current_page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// ถ้าไม่มีสิทธิ์เข้าถึงหน้านี้
if (!check_page_permission($current_page, $admin_role)) {
    // โหลด database connection สำหรับ log
    require_once __DIR__ . '/../../config/database.php';

    // โหลด functions สำหรับ log_activity
    require_once __DIR__ . '/../../includes/functions.php';

    // บันทึก Log การพยายามเข้าถึงโดยไม่มีสิทธิ์
    log_activity(
        $admin_id,
        "พยายามเข้าถึงหน้าที่ไม่มีสิทธิ์: $current_page",
        null,
        null,
        null,
        null
    );

    // เก็บข้อความแจ้งเตือน
    $_SESSION['permission_error'] = 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้';

    // Redirect กลับไป Dashboard
    header("Location: index.php?page=dashboard");
    exit();
}
