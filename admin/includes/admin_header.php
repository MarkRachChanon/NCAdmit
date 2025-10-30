<?php
$current_page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

require_once __DIR__ . '/permissions.php';

$page_titles = [
    'dashboard' => 'แดชบอร์ด',
    'quota_list' => 'รายชื่อรอบโควตา',
    'regular_list' => 'รายชื่อรอบปกติ',
    'student_detail' => 'รายละเอียดนักเรียน',
    'news_manage' => 'จัดการข่าว',
    'news_add' => 'เพิ่มข่าว',
    'news_edit' => 'แก้ไขข่าว',
    'gallery_manage' => 'จัดการแกลเลอรี่',
    'departments_manage' => 'จัดการสาขาวิชา',
    'departments_add' => 'เพิ่มสาขาวิชา',
    'departments_edit' => 'แก้ไขสาขาวิชา',
    'admin_manage' => 'จัดการ Admin',
    'system_settings' => 'ตั้งค่าระบบ',
    'clear_data' => 'ล้างข้อมูล',
    'export_excel' => 'Export Excel',
    'export_pdf' => 'Export PDF'
];
$page_title = isset($page_titles[$current_page]) ? $page_titles[$current_page] : 'Admin Panel';
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - NC-Admission Admin</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../assets/images/logo.png">

    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Admin Custom CSS -->
    <link rel="stylesheet" href="assets/css/admin.css">

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
</head>

<body class="admin-body">

    <!-- Top Navbar -->
    <nav class="admin-navbar">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <!-- Left: Hamburger + Logo -->
                <div class="d-flex align-items-center">
                    <button class="btn btn-link text-white me-3" id="sidebarToggle">
                        <i class="bi bi-list fs-4"></i>
                    </button>
                    <a href="index.php" class="navbar-brand text-white fw-bold">
                        <i class="bi bi-speedometer2 me-2"></i>
                        NC-Admission
                    </a>
                </div>

                <!-- Right: User Menu -->
                <div class="dropdown">
                    <button class="btn btn-link text-white dropdown-toggle text-decoration-none" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-2"></i>
                        <span class="d-none d-sm-inline"><?php echo htmlspecialchars($admin_fullname); ?></span>
                        <span class="badge bg-<?php echo get_role_color($admin_role); ?> ms-2 d-none d-md-inline">
                            <?php echo ucfirst($admin_role); ?>
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li class="px-3 py-2 border-bottom">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-2">
                                    <i class="bi <?php echo get_role_icon($admin_role); ?> text-primary"></i>
                                </div>
                                <div>
                                    <div class="fw-bold"><?php echo htmlspecialchars($admin_fullname); ?></div>
                                    <small class="text-muted">@<?php echo htmlspecialchars($admin_username); ?></small>
                                </div>
                            </div>
                        </li>
                        <li class="px-3 py-2 bg-light">
                            <small class="text-muted d-block">สิทธิ์การใช้งาน:</small>
                            <small><?php echo get_role_description($admin_role); ?></small>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        
                        <?php if (can_show_menu('system_settings', $admin_role)): ?>
                        <li><a class="dropdown-item" href="index.php?page=system_settings">
                                <i class="bi bi-gear me-2"></i> ตั้งค่าระบบ
                            </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <?php endif; ?>
                        
                        <li><a class="dropdown-item text-danger" href="logout.php">
                                <i class="bi bi-box-arrow-right me-2"></i> ออกจากระบบ
                            </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>