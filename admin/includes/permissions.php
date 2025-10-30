<?php
/**
 * Permissions System
 * กำหนดสิทธิ์การเข้าถึงเมนูต่างๆ ตาม Role
 */

// กำหนดสิทธิ์สำหรับแต่ละ Role
$role_permissions = [
    'superadmin' => [
        'dashboard',
        'quota_list',
        'regular_list',
        'student_detail',
        'export_excel',
        'export_pdf',
        'news_manage',
        'news_add',
        'news_edit',
        'gallery_manage',
        'departments_manage',
        'departments_add',
        'departments_edit',
        'categories_manage',
        'admin_manage',
        'system_settings',
        'clear_data'
    ],
    'admin' => [
        'dashboard',
        'quota_list',
        'regular_list',
        'student_detail',
        'export_excel',
        'export_pdf',
        'news_manage',
        'news_add',
        'news_edit',
        'gallery_manage',
        'departments_manage',
        'departments_add',
        'departments_edit',
        'categories_manage'
    ],
    'quota' => [
        'dashboard',
        'quota_list',
        'student_detail',
        'export_excel',
        'export_pdf',
        'news_manage',
        'news_add',
        'news_edit',
        'gallery_manage',
        'departments_manage',
        'departments_add',
        'departments_edit',
        'categories_manage'
    ],
    'regular' => [
        'dashboard',
        'regular_list',
        'student_detail',
        'export_excel',
        'export_pdf',
        'news_manage',
        'news_add',
        'news_edit',
        'gallery_manage'
    ],
    'staff' => [
        'dashboard',
        'news_manage',
        'news_add',
        'news_edit',
        'gallery_manage'
    ]
];

/**
 * ตรวจสอบว่า User มีสิทธิ์เข้าถึงหน้านั้นหรือไม่
 * 
 * @param string $page หน้าที่ต้องการตรวจสอบ
 * @param string $user_role Role ของ User
 * @return bool true = มีสิทธิ์, false = ไม่มีสิทธิ์
 */
function check_page_permission($page, $user_role) {
    global $role_permissions;
    
    // ตรวจสอบว่ามี Role นี้ในระบบหรือไม่
    if (!isset($role_permissions[$user_role])) {
        return false;
    }
    
    // ตรวจสอบว่าหน้านี้อยู่ในรายการสิทธิ์หรือไม่
    return in_array($page, $role_permissions[$user_role]);
}

/**
 * ตรวจสอบและ Redirect ถ้าไม่มีสิทธิ์
 * 
 * @param string $page หน้าปัจจุบัน
 * @param string $user_role Role ของ User
 */
function require_permission($page, $user_role) {
    if (!check_page_permission($page, $user_role)) {
        // ไม่มีสิทธิ์ -> Redirect ไป Dashboard พร้อม Alert
        $_SESSION['error_message'] = 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้';
        header('Location: index.php?page=dashboard');
        exit();
    }
}

/**
 * ตรวจสอบว่าควรแสดงเมนูหรือไม่
 * 
 * @param string $page หน้าที่ต้องการตรวจสอบ
 * @param string $user_role Role ของ User
 * @return bool
 */
function can_show_menu($page, $user_role) {
    return check_page_permission($page, $user_role);
}

/**
 * ดึงรายการเมนูที่ User สามารถเข้าถึงได้
 * 
 * @param string $user_role Role ของ User
 * @return array
 */
function get_accessible_pages($user_role) {
    global $role_permissions;
    
    if (!isset($role_permissions[$user_role])) {
        return [];
    }
    
    return $role_permissions[$user_role];
}

/**
 * ข้อความอธิบายสิทธิ์สำหรับแต่ละ Role
 */
function get_role_description($role) {
    $descriptions = [
        'superadmin' => 'เข้าถึงได้ทุกเมนู (สิทธิ์เต็มรูปแบบ)',
        'admin' => 'เข้าถึงได้ทุกเมนู ยกเว้น จัดการ Admin, ตั้งค่าระบบ และล้างข้อมูล',
        'quota' => 'จัดการรอบโควตา, ข่าว, แกลเลอรี่ และ Export ข้อมูล',
        'regular' => 'จัดการรอบปกติ, ข่าว, แกลเลอรี่ และ Export ข้อมูล',
        'staff' => 'จัดการข่าวและแกลเลอรี่เท่านั้น'
    ];
    
    return $descriptions[$role] ?? 'ไม่มีข้อมูล';
}

/**
 * สี Badge สำหรับแต่ละ Role
 */
function get_role_color($role) {
    $colors = [
        'superadmin' => 'danger',
        'admin' => 'primary',
        'quota' => 'success',
        'regular' => 'warning',
        'staff' => 'info'
    ];
    
    return $colors[$role] ?? 'secondary';
}

/**
 * Icon สำหรับแต่ละ Role
 */
function get_role_icon($role) {
    $icons = [
        'superadmin' => 'bi-shield-fill-exclamation',
        'admin' => 'bi-shield-fill-check',
        'quota' => 'bi-person-lines-fill',
        'regular' => 'bi-people-fill',
        'staff' => 'bi-person-badge'
    ];
    
    return $icons[$role] ?? 'bi-person';
}