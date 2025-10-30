<?php

/**
 * Admin Panel Router
 * NC-Admission - Nakhon Pathom College Admission System
 */

// เริ่ม Session
session_start();

// Include Config & Functions
require_once '../config/database.php';
require_once '../includes/functions.php';

// ตรวจสอบ Authentication & Permission
require_once 'includes/auth_check.php';

// Get Current Page
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Include Header & Sidebar
include 'includes/admin_header.php';
include 'includes/admin_sidebar.php';
?>

<!-- Main Content -->
<div class="admin-content">
    <?php
    if (isset($_SESSION['permission_error'])):
    ?>
        <div class="container-fluid mt-3">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>ไม่มีสิทธิ์เข้าถึง:</strong> <?php echo $_SESSION['permission_error']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php
        unset($_SESSION['permission_error']);
    endif;
    ?>

    <?php
    // Route Pages
    switch ($page) {
        // ==================== Dashboard ====================
        case 'dashboard':
            include 'dashboard.php';
            break;

        // ==================== Students Management ====================
        case 'quota_list':
            if (can_show_menu('quota_list', $admin_role)) {
                include 'students/quota_list.php';
            } else {
                include 'includes/403.php';
            }
            break;

        case 'regular_list':
            if (can_show_menu('regular_list', $admin_role)) {
                include 'students/regular_list.php';
            } else {
                include 'includes/403.php';
            }
            break;

        case 'student_detail':
            if (!can_show_menu('student_detail', $admin_role)) {
                include 'includes/403.php';
                exit();
            }

            $student_type = isset($_GET['type']) ? clean_input($_GET['type']) : 'quota';

            $is_allowed = true;
            if ($admin_role === 'quota' && $student_type !== 'quota') {
                $is_allowed = false;
                $required_type = 'รอบโควต้า';
            } elseif ($admin_role === 'regular' && $student_type !== 'regular') {
                $is_allowed = false;
                $required_type = 'รอบปกติ';
            }

            if ($is_allowed) {
                include 'students/student_detail.php';
            } else {
                $_SESSION['permission_error'] = 'คุณมีสิทธิ์เข้าถึงรายละเอียดผู้สมัครเฉพาะ **' . $required_type . '** เท่านั้น';
                include 'includes/403.php';
                exit();
            }
            break;

        // ==================== Export ====================
        case 'export_excel':
            if (can_show_menu('export_excel', $admin_role)) {
                include 'students/export_excel.php';
            } else {
                include 'includes/403.php';
            }
            break;

        case 'export_pdf':
            if (can_show_menu('export_pdf', $admin_role)) {
                include 'students/export_pdf.php';
            } else {
                include 'includes/403.php';
            }
            break;

        // ==================== News Management ====================
        case 'news_manage':
            if (can_show_menu('news_manage', $admin_role)) {
                include 'cms/news_manage.php';
            } else {
                include 'includes/403.php';
            }
            break;

        case 'news_add':
            if (can_show_menu('news_add', $admin_role)) {
                include 'cms/news_add.php';
            } else {
                include 'includes/403.php';
            }
            break;

        case 'news_edit':
            if (can_show_menu('news_edit', $admin_role)) {
                include 'cms/news_edit.php';
            } else {
                include 'includes/403.php';
            }
            break;

        // ==================== Gallery Management ====================
        case 'gallery_manage':
            if (can_show_menu('gallery_manage', $admin_role)) {
                include 'cms/gallery_manage.php';
            } else {
                include 'includes/403.php';
            }
            break;

        // ==================== Departments Management ====================
        case 'departments_manage':
            if (can_show_menu('departments_manage', $admin_role)) {
                include 'cms/departments_manage.php';
            } else {
                include 'includes/403.php';
            }
            break;

        case 'departments_add':
            if (can_show_menu('departments_add', $admin_role)) {
                include 'cms/departments_add.php';
            } else {
                include 'includes/403.php';
            }
            break;

        case 'departments_edit':
            if (can_show_menu('departments_edit', $admin_role)) {
                include 'cms/departments_edit.php';
            } else {
                include 'includes/403.php';
            }
            break;

        // ==================== Categories Management ====================
        case 'categories_manage':
            if (in_array($admin_role, ['superadmin', 'admin'])) {
                include 'cms/categories_manage.php';
            } else {
                include 'includes/403.php';
            }
            break;

        // ==================== Admin Management ====================
        case 'admin_manage':
            if (can_show_menu('admin_manage', $admin_role)) {
                include 'settings/admin_manage.php';
            } else {
                include 'includes/403.php';
            }
            break;

        // ==================== System Settings ====================
        case 'system_settings':
            if (can_show_menu('system_settings', $admin_role)) {
                include 'settings/system_settings.php';
            } else {
                include 'includes/403.php';
            }
            break;

        // ==================== Clear Data ====================
        case 'clear_data':
            if (can_show_menu('clear_data', $admin_role)) {
                include 'settings/clear_data.php';
            } else {
                include 'includes/403.php';
            }
            break;

        // ==================== 404 Not Found ====================
        default:
            include 'includes/404.php';
            break;
    }
    ?>
</div>

<?php include 'includes/admin_footer.php'; ?>