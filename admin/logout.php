<?php
/**
 * Logout Handler
 * ทำลาย Session และ Redirect ไป Login Page
 */

session_start();

// ทำลาย Session ทั้งหมด
session_unset();
session_destroy();

// ลบ Cookie (ถ้ามี)
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Redirect ไป Login Page
header("Location: login.php");
exit();
?>