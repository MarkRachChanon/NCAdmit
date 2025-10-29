<?php
$current_page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// โหลดระบบ permissions
require_once __DIR__ . '/permissions.php';
?>

<!-- Sidebar -->
<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-header">
        <img src="../assets/images/logo.png" alt="Logo" height="50">
        <h5 class="mt-2 mb-0">NC-Admission</h5>
        <small class="text-muted">ระบบจัดการหลังบ้าน</small>
    </div>
    
    <nav class="sidebar-menu">
        <ul class="nav flex-column">
            <!-- Dashboard -->
            <?php if (can_show_menu('dashboard', $admin_role)): ?>
            <li class="nav-item">
                <a href="index.php?page=dashboard" class="nav-link <?php echo ($current_page == 'dashboard') ? 'active' : ''; ?>">
                    <i class="bi bi-speedometer2"></i>
                    <span>แดชบอร์ด</span>
                </a>
            </li>
            <?php endif; ?>
            
            <!-- เช็คว่ามีเมนูในกลุ่มจัดการนักเรียนที่แสดงได้หรือไม่ -->
            <?php if (can_show_menu('quota_list', $admin_role) || can_show_menu('regular_list', $admin_role)): ?>
            <li class="nav-divider">จัดการนักเรียน</li>
            <?php endif; ?>
            
            <!-- รอบโควต้า -->
            <?php if (can_show_menu('quota_list', $admin_role)): ?>
            <li class="nav-item">
                <a href="index.php?page=quota_list" class="nav-link <?php echo ($current_page == 'quota_list') ? 'active' : ''; ?>">
                    <i class="bi bi-person-lines-fill"></i>
                    <span>รายชื่อรอบโควต้า</span>
                </a>
            </li>
            <?php endif; ?>

            <!-- รอบปกติ -->
            <?php if (can_show_menu('regular_list', $admin_role)): ?>
            <li class="nav-item">
                <a href="index.php?page=regular_list" class="nav-link <?php echo ($current_page == 'regular_list') ? 'active' : ''; ?>">
                    <i class="bi bi-people-fill"></i>
                    <span>รายชื่อรอบปกติ</span>
                </a>
            </li>
            <?php endif; ?>
            
            <!-- Export -->
            <?php if (can_show_menu('export_excel', $admin_role) || can_show_menu('export_pdf', $admin_role)): ?>
            <li class="nav-item">
                <a href="#exportMenu" class="nav-link" data-bs-toggle="collapse">
                    <i class="bi bi-file-earmark-arrow-down"></i>
                    <span>Export ข้อมูล</span>
                    <i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul class="collapse nav flex-column ms-3" id="exportMenu">
                    <?php if (can_show_menu('export_excel', $admin_role)): ?>
                    <li class="nav-item">
                        <a href="index.php?page=export_excel" class="nav-link">
                            <i class="bi bi-file-earmark-excel"></i> Excel
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if (can_show_menu('export_pdf', $admin_role)): ?>
                    <li class="nav-item">
                        <a href="index.php?page=export_pdf" class="nav-link">
                            <i class="bi bi-file-earmark-pdf"></i> PDF
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </li>
            <?php endif; ?>
            
            <!-- เช็คว่ามีเมนูในกลุ่มจัดการเนื้อหาที่แสดงได้หรือไม่ -->
            <?php if (can_show_menu('news_manage', $admin_role) || can_show_menu('gallery_manage', $admin_role) || can_show_menu('departments_manage', $admin_role)): ?>
            <li class="nav-divider">จัดการเนื้อหา</li>
            <?php endif; ?>
            
            <!-- ข่าว -->
            <?php if (can_show_menu('news_manage', $admin_role)): ?>
            <li class="nav-item">
                <a href="index.php?page=news_manage" class="nav-link <?php echo (in_array($current_page, ['news_manage', 'news_add', 'news_edit'])) ? 'active' : ''; ?>">
                    <i class="bi bi-newspaper"></i>
                    <span>จัดการข่าว</span>
                </a>
            </li>
            <?php endif; ?>
            
            <!-- แกลเลอรี่ -->
            <?php if (can_show_menu('gallery_manage', $admin_role)): ?>
            <li class="nav-item">
                <a href="index.php?page=gallery_manage" class="nav-link <?php echo ($current_page == 'gallery_manage') ? 'active' : ''; ?>">
                    <i class="bi bi-images"></i>
                    <span>จัดการแกลเลอรี่</span>
                </a>
            </li>
            <?php endif; ?>
            
            <!-- สาขาวิชา -->
            <?php if (can_show_menu('departments_manage', $admin_role)): ?>
            <li class="nav-item">
                <a href="index.php?page=departments_manage" class="nav-link <?php echo ($current_page == 'departments_manage') ? 'active' : ''; ?>">
                    <i class="bi bi-file-earmark-text"></i>
                    <span>จัดการสาขาวิชา</span>
                </a>
            </li>
            <?php endif; ?>
            
            <!-- เช็คว่ามีเมนูตั้งค่าที่แสดงได้หรือไม่ (เฉพาะ superadmin) -->
            <?php if (can_show_menu('admin_manage', $admin_role) || can_show_menu('system_settings', $admin_role) || can_show_menu('clear_data', $admin_role)): ?>
            <li class="nav-divider">ตั้งค่า</li>
            <?php endif; ?>
            
            <!-- จัดการ Admin -->
            <?php if (can_show_menu('admin_manage', $admin_role)): ?>
            <li class="nav-item">
                <a href="index.php?page=admin_manage" class="nav-link <?php echo ($current_page == 'admin_manage') ? 'active' : ''; ?>">
                    <i class="bi bi-shield-lock"></i>
                    <span>จัดการ Admin</span>
                </a>
            </li>
            <?php endif; ?>
            
            <!-- ตั้งค่าระบบ -->
            <?php if (can_show_menu('system_settings', $admin_role)): ?>
            <li class="nav-item">
                <a href="index.php?page=system_settings" class="nav-link <?php echo ($current_page == 'system_settings') ? 'active' : ''; ?>">
                    <i class="bi bi-gear"></i>
                    <span>ตั้งค่าระบบ</span>
                </a>
            </li>
            <?php endif; ?>
            
            <!-- ล้างข้อมูล -->
            <?php if (can_show_menu('clear_data', $admin_role)): ?>
            <li class="nav-item">
                <a href="index.php?page=clear_data" class="nav-link <?php echo ($current_page == 'clear_data') ? 'active' : ''; ?>">
                    <i class="bi bi-trash"></i>
                    <span>ล้างข้อมูล</span>
                </a>
            </li>
            <?php endif; ?>
            
            <li class="nav-divider"></li>
            
            <!-- ออกจากระบบ -->
            <li class="nav-item">
                <a href="logout.php" class="nav-link text-danger">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>ออกจากระบบ</span>
                </a>
            </li>
        </ul>
    </nav>
    
    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <small class="text-muted d-flex align-items-center justify-content-center">
            <i class="bi bi-person-badge me-2"></i>
            <span><?php echo htmlspecialchars($admin_fullname); ?></span>
        </small>
        <small class="text-muted d-block text-center mt-1">
            <span class="badge bg-<?php echo get_role_color($admin_role); ?>">
                <i class="bi <?php echo get_role_icon($admin_role); ?> me-1"></i>
                <?php echo ucfirst($admin_role); ?>
            </span>
        </small>
    </div>
</aside>