<?php
/**
 * Admin Management - จัดการผู้ดูแลระบบ
 * เฉพาะ Superadmin เท่านั้น
 */

// ตรวจสอบสิทธิ์ (เฉพาะ superadmin)
if ($_SESSION['admin_role'] != 'superadmin') {
    include 'includes/403.php';
    exit();
}

// ดึงข้อมูล Admin ทั้งหมด
$sql = "SELECT * FROM admin_users ORDER BY 
        FIELD(role, 'superadmin', 'admin', 'staff', 'quota', 'regular'),
        created_at DESC";
$result = $conn->query($sql);

// นับจำนวนตามสิทธิ์
$stats_sql = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN role = 'superadmin' THEN 1 ELSE 0 END) as superadmin,
    SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admin,
    SUM(CASE WHEN role = 'staff' THEN 1 ELSE 0 END) as staff,
    SUM(CASE WHEN role = 'quota' THEN 1 ELSE 0 END) as quota,
    SUM(CASE WHEN role = 'regular' THEN 1 ELSE 0 END) as regular,
    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
    SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive
FROM admin_users";
$stats_result = $conn->query($stats_sql);
$stats = $stats_result->fetch_assoc();

// ข้อมูลสิทธิ์การใช้งาน
$roles_info = [
    'superadmin' => [
        'label' => 'Super Admin',
        'color' => 'danger',
        'icon' => 'bi-shield-fill-exclamation',
        'description' => 'สิทธิ์เต็มรูปแบบ จัดการทุกอย่างในระบบ'
    ],
    'admin' => [
        'label' => 'Admin',
        'color' => 'primary',
        'icon' => 'bi-shield-fill-check',
        'description' => 'จัดการข้อมูลนักเรียน, CMS, ตั้งค่าระบบ'
    ],
    'staff' => [
        'label' => 'Staff',
        'color' => 'info',
        'icon' => 'bi-person-badge',
        'description' => 'ดูข้อมูลและตรวจสอบเอกสาร'
    ],
    'quota' => [
        'label' => 'Quota Manager',
        'color' => 'success',
        'icon' => 'bi-person-lines-fill',
        'description' => 'จัดการเฉพาะรอบโควตา'
    ],
    'regular' => [
        'label' => 'Regular Manager',
        'color' => 'warning',
        'icon' => 'bi-people-fill',
        'description' => 'จัดการเฉพาะรอบปกติ'
    ]
];
?>

<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">
                <i class="bi bi-shield-lock text-danger"></i>
                จัดการผู้ดูแลระบบ
            </h2>
            <p class="text-muted mb-0">
                จัดการบัญชีผู้ดูแลและกำหนดสิทธิ์การใช้งาน
            </p>
        </div>
        <div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAdminModal">
                <i class="bi bi-plus-circle me-2"></i>
                เพิ่มผู้ดูแลระบบ
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">ทั้งหมด</h6>
                            <h3 class="mb-0 fw-bold"><?php echo $stats['total']; ?></h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 rounded-circle">
                            <i class="bi bi-people fs-4 text-primary icon-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">ใช้งานอยู่</h6>
                            <h3 class="mb-0 fw-bold text-success"><?php echo $stats['active']; ?></h3>
                        </div>
                        <div class="bg-success bg-opacity-10 rounded-circle">
                            <i class="bi bi-check-circle fs-4 text-success icon-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">ระงับการใช้งาน</h6>
                            <h3 class="mb-0 fw-bold text-danger"><?php echo $stats['inactive']; ?></h3>
                        </div>
                        <div class="bg-danger bg-opacity-10 rounded-circle">
                            <i class="bi bi-x-circle fs-4 text-danger icon-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Super Admin</h6>
                            <h3 class="mb-0 fw-bold text-danger"><?php echo $stats['superadmin']; ?></h3>
                        </div>
                        <div class="bg-danger bg-opacity-10 rounded-circle">
                            <i class="bi bi-shield-fill-exclamation fs-4 text-danger icon-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Permission Matrix -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">
                <i class="bi bi-grid-3x3-gap text-primary me-2"></i>
                ตารางสิทธิ์การเข้าถึง (Permission Matrix)
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle table-hover">
                    <thead class="table-light">
                        <tr>
                            <th width="30%">เมนู / ฟังก์ชัน</th>
                            <th class="text-center" width="14%">
                                <i class="bi bi-shield-fill-exclamation text-danger"></i>
                                <br>Super Admin
                            </th>
                            <th class="text-center" width="14%">
                                <i class="bi bi-shield-fill-check text-primary"></i>
                                <br>Admin
                            </th>
                            <th class="text-center" width="14%">
                                <i class="bi bi-person-badge text-info"></i>
                                <br>Staff
                            </th>
                            <th class="text-center" width="14%">
                                <i class="bi bi-person-lines-fill text-success"></i>
                                <br>Quota
                            </th>
                            <th class="text-center" width="14%">
                                <i class="bi bi-people-fill text-warning"></i>
                                <br>Regular
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $permission_matrix = [
                            'Dashboard' => ['dashboard'],
                            'รายชื่อรอบโควตา' => ['quota_list'],
                            'รายชื่อรอบปกติ' => ['regular_list'],
                            'ดูรายละเอียดนักเรียน' => ['student_detail'],
                            'Export Excel' => ['export_excel'],
                            'Export PDF' => ['export_pdf'],
                            'จัดการข่าว' => ['news_manage', 'news_add', 'news_edit'],
                            'จัดการแกลเลอรี่' => ['gallery_manage'],
                            'จัดการสาขาวิชา' => ['departments_manage', 'departments_add', 'departments_edit'],
                            'จัดการประเภทวิชา' => ['categories_manage'],
                            'ข้อความติดต่อ' => ['contact_messages'],
                            'จัดการ Admin' => ['admin_manage'],
                            'ตั้งค่าระบบ' => ['system_settings'],
                            'ล้างข้อมูล' => ['clear_data']
                        ];
                        
                        $roles = ['superadmin', 'admin', 'staff', 'quota', 'regular'];
                        
                        foreach ($permission_matrix as $menu_name => $pages):
                        ?>
                        <tr>
                            <td><strong><?php echo $menu_name; ?></strong></td>
                            <?php foreach ($roles as $role): ?>
                                <?php
                                $has_permission = false;
                                foreach ($pages as $page) {
                                    if (check_page_permission($page, $role)) {
                                        $has_permission = true;
                                        break;
                                    }
                                }
                                ?>
                                <td class="text-center">
                                    <?php if ($has_permission): ?>
                                        <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                    <?php else: ?>
                                        <i class="bi bi-x-circle text-muted"></i>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="alert alert-info mt-3 mb-0">
                <i class="bi bi-info-circle me-2"></i>
                <strong>หมายเหตุ:</strong> 
                <i class="bi bi-check-circle-fill text-success"></i> = มีสิทธิ์เข้าถึง | 
                <i class="bi bi-x-circle text-muted"></i> = ไม่มีสิทธิ์เข้าถึง
            </div>
        </div>
    </div>

    <!-- Role Info Cards -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">
                <i class="bi bi-info-circle text-primary me-2"></i>
                คำอธิบายระดับสิทธิ์การใช้งาน
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <?php foreach ($roles_info as $role => $info): ?>
                <div class="col-md-4">
                    <div class="d-flex align-items-start border rounded p-3 h-100">
                        <div class="bg-<?php echo $info['color']; ?> bg-opacity-10 rounded p-2 me-3">
                            <i class="bi <?php echo $info['icon']; ?> fs-4 text-<?php echo $info['color']; ?>"></i>
                        </div>
                        <div>
                            <h6 class="mb-1">
                                <span class="badge bg-<?php echo $info['color']; ?>">
                                    <?php echo $info['label']; ?>
                                </span>
                                <span class="badge bg-secondary ms-1">
                                    <?php echo $stats[$role] ?? 0; ?>
                                </span>
                            </h6>
                            <small class="text-muted"><?php echo $info['description']; ?></small>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Admin List Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">
                <i class="bi bi-table me-2"></i>
                รายชื่อผู้ดูแลระบบ
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="adminTable">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">ลำดับ</th>
                            <th width="20%">ชื่อผู้ใช้</th>
                            <th width="20%">ชื่อ-นามสกุล</th>
                            <th width="20%">อีเมล</th>
                            <th width="15%">สิทธิ์</th>
                            <th width="10%">สถานะ</th>
                            <th width="10%" class="text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php $no = 1; ?>
                            <?php while ($admin = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle me-2 d-flex align-items-center justify-content-center">
                                                <i class="bi <?php echo $roles_info[$admin['role']]['icon']; ?> text-<?php echo $roles_info[$admin['role']]['color']; ?>"></i>
                                            </div>
                                            <strong><?php echo htmlspecialchars($admin['username']); ?></strong>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($admin['fullname']); ?></td>
                                    <td>
                                        <?php if (!empty($admin['email'])): ?>
                                            <a href="mailto:<?php echo $admin['email']; ?>">
                                                <?php echo htmlspecialchars($admin['email']); ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $role_info = $roles_info[$admin['role']] ?? ['label' => $admin['role'], 'color' => 'secondary', 'icon' => 'bi-person'];
                                        ?>
                                        <span class="badge bg-<?php echo $role_info['color']; ?>">
                                            <i class="bi <?php echo $role_info['icon']; ?> me-1"></i>
                                            <?php echo $role_info['label']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($admin['is_active'] == 1): ?>
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle me-1"></i>ใช้งานอยู่
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">
                                                <i class="bi bi-x-circle me-1"></i>ระงับ
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0)" 
                                                       onclick="editAdmin(<?php echo htmlspecialchars(json_encode($admin)); ?>)">
                                                        <i class="bi bi-pencil me-2 text-primary"></i>
                                                        แก้ไข
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0)" 
                                                       onclick="resetPassword(<?php echo $admin['id']; ?>, '<?php echo htmlspecialchars($admin['username']); ?>')">
                                                        <i class="bi bi-key me-2 text-warning"></i>
                                                        รีเซ็ตรหัสผ่าน
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <?php if ($admin['id'] != $_SESSION['admin_id']): ?>
                                                    <li>
                                                        <a class="dropdown-item" href="javascript:void(0)" 
                                                           onclick="toggleStatus(<?php echo $admin['id']; ?>, <?php echo $admin['is_active']; ?>)">
                                                            <?php if ($admin['is_active'] == 1): ?>
                                                                <i class="bi bi-x-circle me-2 text-danger"></i>
                                                                ระงับการใช้งาน
                                                            <?php else: ?>
                                                                <i class="bi bi-check-circle me-2 text-success"></i>
                                                                เปิดใช้งาน
                                                            <?php endif; ?>
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item text-danger" href="javascript:void(0)" 
                                                           onclick="deleteAdmin(<?php echo $admin['id']; ?>, '<?php echo htmlspecialchars($admin['username']); ?>')">
                                                            <i class="bi bi-trash me-2"></i>
                                                            ลบ
                                                        </a>
                                                    </li>
                                                <?php else: ?>
                                                    <li>
                                                        <span class="dropdown-item text-muted disabled">
                                                            <i class="bi bi-info-circle me-2"></i>
                                                            ไม่สามารถลบตัวเองได้
                                                        </span>
                                                    </li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="bi bi-inbox display-1 text-muted"></i>
                                    <p class="text-muted mt-3 mb-0">ไม่พบข้อมูลผู้ดูแลระบบ</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Admin Modal -->
<div class="modal fade" id="addAdminModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-plus-circle me-2"></i>
                    เพิ่มผู้ดูแลระบบ
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addAdminForm" onsubmit="return handleAddAdmin(event);">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                <i class="bi bi-person me-1"></i>
                                ชื่อผู้ใช้ <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" name="username" required 
                                   pattern="[a-zA-Z0-9_]{4,20}" 
                                   placeholder="ภาษาอังกฤษและตัวเลขเท่านั้น 4-20 ตัวอักษร">
                            <small class="text-muted">ใช้สำหรับเข้าสู่ระบบ</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                <i class="bi bi-person-badge me-1"></i>
                                ชื่อ-นามสกุล <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" name="fullname" required 
                                   placeholder="ชื่อจริง นามสกุล">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                <i class="bi bi-envelope me-1"></i>
                                อีเมล
                            </label>
                            <input type="email" class="form-control" name="email" 
                                   placeholder="example@nc.ac.th">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                <i class="bi bi-shield me-1"></i>
                                สิทธิ์การใช้งาน <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" name="role" required id="roleSelect">
                                <option value="">-- เลือกสิทธิ์ --</option>
                                <?php foreach ($roles_info as $role => $info): ?>
                                    <option value="<?php echo $role; ?>">
                                        <?php echo $info['label']; ?> - <?php echo $info['description']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                <i class="bi bi-key me-1"></i>
                                รหัสผ่าน <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control" name="password" id="password" required 
                                       minlength="6" placeholder="อย่างน้อย 6 ตัวอักษร">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                    <i class="bi bi-eye" id="password-icon"></i>
                                </button>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                <i class="bi bi-key-fill me-1"></i>
                                ยืนยันรหัสผ่าน <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control" name="confirm_password" id="confirm_password" required 
                                       minlength="6" placeholder="กรอกรหัสผ่านอีกครั้ง">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirm_password')">
                                    <i class="bi bi-eye" id="confirm_password-icon"></i>
                                </button>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>หมายเหตุ:</strong> รหัสผ่านจะถูกเข้ารหัสอัตโนมัติก่อนบันทึกลงระบบ
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>ยกเลิก
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>บันทึก
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Admin Modal -->
<div class="modal fade" id="editAdminModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-pencil me-2"></i>
                    แก้ไขผู้ดูแลระบบ
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editAdminForm" onsubmit="return handleEditAdmin(event);">
                <input type="hidden" name="admin_id" id="edit_admin_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                <i class="bi bi-person me-1"></i>
                                ชื่อผู้ใช้
                            </label>
                            <input type="text" class="form-control" id="edit_username" readonly>
                            <small class="text-muted">ไม่สามารถเปลี่ยนชื่อผู้ใช้ได้</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                <i class="bi bi-person-badge me-1"></i>
                                ชื่อ-นามสกุล <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" name="fullname" id="edit_fullname" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                <i class="bi bi-envelope me-1"></i>
                                อีเมล
                            </label>
                            <input type="email" class="form-control" name="email" id="edit_email">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                <i class="bi bi-shield me-1"></i>
                                สิทธิ์การใช้งาน <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" name="role" id="edit_role" required>
                                <?php foreach ($roles_info as $role => $info): ?>
                                    <option value="<?php echo $role; ?>">
                                        <?php echo $info['label']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>ยกเลิก
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>บันทึกการแก้ไข
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.avatar-sm {
    width: 40px;
    height: 40px;
    font-size: 1.2rem;
}

.table-bordered th,
.table-bordered td {
    border-color: #dee2e6;
}

.table-bordered thead th {
    font-weight: 600;
}
</style>

<script>
// Initialize DataTable
$(document).ready(function() {
    $('#adminTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/th.json'
        },
        responsive: true,
        pageLength: 25,
        order: [[0, 'asc']]
    });
});

// Toggle Password Visibility
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '-icon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}

function handleAddAdmin(e) {
    e.preventDefault();
    e.stopPropagation();
    
    const form = e.target;
    const formData = new FormData(form);
    const password = formData.get('password');
    const confirm_password = formData.get('confirm_password');
    
    // เช็ครหัสผ่าน
    if (password !== confirm_password) {
        alert('รหัสผ่านไม่ตรงกัน');
        return false;
    }
    
    // แสดง Loading
    if (typeof showLoading === 'function') {
        showLoading('กำลังเพิ่มผู้ดูแลระบบ...');
    }
    
    // ส่งข้อมูล
    fetch('api/admin_add.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'สำเร็จ',
                    text: 'เพิ่มผู้ดูแลระบบเรียบร้อยแล้ว',
                    confirmButtonText: 'ตกลง'
                }).then(() => {
                    location.reload();
                });
            } else {
                alert('เพิ่มผู้ดูแลระบบเรียบร้อยแล้ว');
                location.reload();
            }
        } else {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด',
                    text: data.message
                });
            } else {
                alert('เกิดข้อผิดพลาด: ' + data.message);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('เกิดข้อผิดพลาด: ' + error.message);
    });
    
    return false;
}

// Edit Admin
function editAdmin(admin) {
    document.getElementById('edit_admin_id').value = admin.id;
    document.getElementById('edit_username').value = admin.username;
    document.getElementById('edit_fullname').value = admin.fullname;
    document.getElementById('edit_email').value = admin.email || '';
    document.getElementById('edit_role').value = admin.role;
    
    new bootstrap.Modal(document.getElementById('editAdminModal')).show();
}

function handleEditAdmin(e) {
    e.preventDefault();
    e.stopPropagation();
    
    const form = e.target;
    const formData = new FormData(form);
    
    if (typeof showLoading === 'function') {
        showLoading('กำลังบันทึกการแก้ไข...');
    }
    
    fetch('api/admin_edit.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'สำเร็จ',
                    text: 'แก้ไขข้อมูลเรียบร้อยแล้ว',
                    confirmButtonText: 'ตกลง'
                }).then(() => {
                    location.reload();
                });
            } else {
                alert('แก้ไขข้อมูลเรียบร้อยแล้ว');
                location.reload();
            }
        } else {
            alert('เกิดข้อผิดพลาด: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('เกิดข้อผิดพลาด: ' + error.message);
    });
    
    return false;
}

// Reset Password
async function resetPassword(id, username) {
    const { value: newPassword } = await Swal.fire({
        title: 'รีเซ็ตรหัสผ่าน',
        text: `กำหนดรหัสผ่านใหม่สำหรับ: ${username}`,
        input: 'password',
        inputPlaceholder: 'กรอกรหัสผ่านใหม่ (อย่างน้อย 6 ตัวอักษร)',
        inputAttributes: {
            minlength: 6,
            autocomplete: 'new-password'
        },
        showCancelButton: true,
        confirmButtonText: 'รีเซ็ต',
        cancelButtonText: 'ยกเลิก',
        confirmButtonColor: '#dc3545',
        inputValidator: (value) => {
            if (!value) {
                return 'กรุณากรอกรหัสผ่าน';
            }
            if (value.length < 6) {
                return 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร';
            }
        }
    });
    
    if (newPassword) {
        showLoading('กำลังรีเซ็ตรหัสผ่าน...');
        
        try {
            const response = await fetch('api/admin_reset_password.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    admin_id: id,
                    new_password: newPassword
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'สำเร็จ',
                    text: 'รีเซ็ตรหัสผ่านเรียบร้อยแล้ว',
                    confirmButtonText: 'ตกลง'
                });
            } else {
                showError('เกิดข้อผิดพลาด', data.message);
            }
        } catch (error) {
            showError('เกิดข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์');
        }
    }
}

// Toggle Status
async function toggleStatus(id, currentStatus) {
    const newStatus = currentStatus == 1 ? 0 : 1;
    const action = newStatus == 1 ? 'เปิดใช้งาน' : 'ระงับการใช้งาน';
    
    const result = await Swal.fire({
        title: 'ยืนยันการดำเนินการ',
        text: `คุณต้องการ${action}บัญชีนี้ใช่หรือไม่?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'ยืนยัน',
        cancelButtonText: 'ยกเลิก',
        confirmButtonColor: newStatus == 1 ? '#28a745' : '#dc3545'
    });
    
    if (result.isConfirmed) {
        showLoading('กำลังดำเนินการ...');
        
        try {
            const response = await fetch('api/admin_toggle_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    admin_id: id,
                    is_active: newStatus
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'สำเร็จ',
                    text: `${action}เรียบร้อยแล้ว`,
                    confirmButtonText: 'ตกลง'
                }).then(() => {
                    location.reload();
                });
            } else {
                showError('เกิดข้อผิดพลาด', data.message);
            }
        } catch (error) {
            showError('เกิดข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์');
        }
    }
}

// Delete Admin
async function deleteAdmin(id, username) {
    const result = await Swal.fire({
        title: 'ยืนยันการลบ',
        html: `คุณแน่ใจหรือไม่ว่าต้องการลบบัญชี <strong>${username}</strong>?<br><br>
               <span class="text-danger">การดำเนินการนี้ไม่สามารถย้อนกลับได้</span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'ลบเลย',
        cancelButtonText: 'ยกเลิก',
        confirmButtonColor: '#dc3545'
    });
    
    if (result.isConfirmed) {
        showLoading('กำลังลบข้อมูล...');
        
        try {
            const response = await fetch('api/admin_delete.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    admin_id: id
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'สำเร็จ',
                    text: 'ลบข้อมูลเรียบร้อยแล้ว',
                    confirmButtonText: 'ตกลง'
                }).then(() => {
                    location.reload();
                });
            } else {
                showError('เกิดข้อผิดพลาด', data.message);
            }
        } catch (error) {
            showError('เกิดข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์');
        }
    }
}
</script>