<?php

/**
 * Admin Router - NC-Admission
 * Main Router for Admin Panel
 */

session_start();

// ตรวจสอบการ Login
require_once 'includes/auth_check.php';

// Include Database
require_once '../config/database.php';
require_once '../includes/functions.php';

// Get Page Parameter
$page = isset($_GET['page']) ? clean_input($_GET['page']) : 'dashboard';

// Include Admin Header & Sidebar
include 'includes/admin_header.php';
include 'includes/admin_sidebar.php';
?>

<!-- Main Content Wrapper -->
<div class="admin-content">
    <?php
    // Router - Switch Case
    switch ($page) {
        // Dashboard
        case 'dashboard':
            include 'dashboard.php';
            break;

        // จัดการนักเรียน
        case 'quota_list':
            include 'students/quota_list.php';
            break;

        case 'regular_list':
            include 'students/regular_list.php';
            break;

        case 'student_detail':
            include 'students/student_detail.php';
            break;

        case 'export_excel':
            include 'students/export_excel.php';
            break;

        case 'export_pdf':
            include 'students/export_pdf.php';
            break;

        // CMS
        case 'news_manage':
            include 'cms/news_manage.php';
            break;

        case 'news_add':
            include 'cms/news_add.php';
            break;

        case 'news_edit':
            include 'cms/news_edit.php';
            break;

        case 'departments_manage':
            include 'cms/departments_manage.php';
            break;

        case 'departments_add':
            include 'cms/departments_add.php';
            break;

        case 'departments_edit':
            include 'cms/departments_edit.php';
            break;

        case 'gallery_manage':
            include 'cms/gallery_manage.php';
            break;

        // Settings
        case 'admin_manage':
            include 'settings/admin_manage.php';
            break;

        case 'system_settings':
            include 'settings/system_settings.php';
            break;

        case 'clear_data':
            include 'students/clear_data.php';
            break;

        // Default - 404
        default:
            include '../pages/404.php';
            break;
    }
    ?>
</div>

<?php
// Include Admin Footer
include 'includes/admin_footer.php';
?>