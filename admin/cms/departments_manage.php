<?php
/**
 * หน้าจัดการสาขาวิชา
 * NC-Admission - Nakhon Pathom College Admission System
 */

// ตรวจสอบสิทธิ์การเข้าถึง
if (!check_page_permission('departments_manage', $admin_role)) {
    header('Location: index.php?page=dashboard');
    exit();
}

// ดึงข้อมูล Categories
$categories_sql = "SELECT * FROM department_categories WHERE is_active = 1 ORDER BY sort_order ASC";
$categories_result = $conn->query($categories_sql);
$categories = [];
if ($categories_result && $categories_result->num_rows > 0) {
    while ($row = $categories_result->fetch_assoc()) {
        $categories[$row['id']] = $row['name'];
    }
}

// ดึงข้อมูลสาขาวิชาทั้งหมด
$departments_sql = "SELECT d.*, 
                    dc.name as category_name,
                    (SELECT COUNT(*) FROM students_quota WHERE department_id = d.id) as quota_count,
                    (SELECT COUNT(*) FROM students_regular WHERE department_id = d.id) as regular_count
                    FROM departments d 
                    LEFT JOIN department_categories dc ON d.category_id = dc.id 
                    ORDER BY dc.sort_order, d.level, d.name_th";
$departments_result = $conn->query($departments_sql);
?>

<!-- Page Header -->
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-2">
                        <i class="bi bi-book-half text-primary me-2"></i>
                        จัดการสาขาวิชา
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="index.php?page=dashboard">หน้าแรก</a></li>
                            <li class="breadcrumb-item active">จัดการสาขาวิชา</li>
                        </ol>
                    </nav>
                </div>
                
                <div class="d-flex gap-2">
                    <?php if (in_array($admin_role, ['superadmin', 'admin'])): ?>
                    <a href="index.php?page=categories_manage" class="btn btn-outline-secondary">
                        <i class="bi bi-folder me-2"></i>
                        จัดการประเภทวิชา
                    </a>
                    <?php endif; ?>
                    
                    <?php if (can_show_menu('departments_add', $admin_role)): ?>
                    <a href="index.php?page=departments_add" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>
                        เพิ่มสาขาวิชา
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- สถิติสาขาวิชา -->
    <div class="row mb-4">
        <?php
        // นับสถิติ
        $stats_sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
                        SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive,
                        SUM(CASE WHEN is_new = 1 THEN 1 ELSE 0 END) as new_count
                      FROM departments";
        $stats_result = $conn->query($stats_sql);
        
        $total_departments = 0;
        $active_departments = 0;
        $inactive_departments = 0;
        $new_departments = 0;
        
        if ($stats_result && $stats_result->num_rows > 0) {
            $stats = $stats_result->fetch_assoc();
            $total_departments = $stats['total'];
            $active_departments = $stats['active'];
            $inactive_departments = $stats['inactive'];
            $new_departments = $stats['new_count'];
        }
        ?>
        
        <div class="col-md-3 mb-3">
            <div class="card stat-card bg-gradient-primary text-white border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">สาขาวิชาทั้งหมด</h6>
                            <h2 class="mb-0"><?php echo number_format($total_departments); ?></h2>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-book"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card stat-card bg-gradient-success text-white border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">เปิดรับสมัคร</h6>
                            <h2 class="mb-0"><?php echo number_format($active_departments); ?></h2>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card stat-card bg-gradient-warning text-white border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">ปิดรับสมัคร</h6>
                            <h2 class="mb-0"><?php echo number_format($inactive_departments); ?></h2>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-x-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card stat-card bg-gradient-info text-white border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">สาขาใหม่</h6>
                            <h2 class="mb-0"><?php echo number_format($new_departments); ?></h2>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-star"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Section -->
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-bold">
                        <i class="bi bi-search"></i> ค้นหา
                    </label>
                    <input type="text" class="form-control" id="searchInput" 
                           placeholder="ค้นหารหัส, ชื่อสาขาวิชา...">
                </div>
                
                <div class="col-md-2">
                    <label class="form-label fw-bold">
                        <i class="bi bi-funnel"></i> ระดับ
                    </label>
                    <select class="form-select" id="filterLevel">
                        <option value="">ทั้งหมด</option>
                        <option value="ปวช.">ปวช.</option>
                        <option value="ปวส.">ปวส.</option>
                        <option value="ปริญญาตรี">ปริญญาตรี</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label fw-bold">
                        <i class="bi bi-funnel"></i> ประเภทวิชา
                    </label>
                    <select class="form-select" id="filterCategory">
                        <option value="">ทั้งหมด</option>
                        <?php foreach ($categories as $cat_id => $cat_name): ?>
                        <option value="<?php echo $cat_id; ?>">
                            <?php echo htmlspecialchars($cat_name); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-1">
                    <label class="form-label fw-bold">
                        <i class="bi bi-funnel"></i> สถานะ
                    </label>
                    <select class="form-select" id="filterStatus">
                        <option value="">ทั้งหมด</option>
                        <option value="1">เปิดรับสมัคร</option>
                        <option value="0">ปิดรับสมัคร</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <button type="button" class="btn btn-outline-secondary w-100" id="clearFilters" title="ล้างตัวกรอง">
                        <i class="bi bi-x-circle me-2"></i>ล้างตัวกรอง
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ตารางข้อมูลสาขาวิชา -->
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">
                <i class="bi bi-list-ul me-2"></i>
                รายการสาขาวิชา
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="departmentsTable" class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th style="width: 100px;">รหัส</th>
                            <th>ชื่อสาขาวิชา</th>
                            <th style="width: 180px;">ประเภทวิชา</th>
                            <th style="width: 100px;">ระดับ</th>
                            <th style="width: 120px;">ประเภทการเรียน</th>
                            <th style="width: 100px;" class="text-center">จำนวนที่รับ</th>
                            <th style="width: 100px;" class="text-center">ผู้สมัคร</th>
                            <th style="width: 100px;" class="text-center">สถานะ</th>
                            <th style="width: 150px;" class="text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($departments_result && $departments_result->num_rows > 0):
                            // Reset pointer
                            $departments_result->data_seek(0);
                            $no = 1;
                            while ($dept = $departments_result->fetch_assoc()):
                                $total_applicants = $dept['quota_count'] + $dept['regular_count'];
                                $total_seats = 0;
                                if ($dept['open_quota']) $total_seats += $dept['seats_quota'];
                                if ($dept['open_regular']) $total_seats += $dept['seats_regular'];
                        ?>
                        <tr data-category="<?php echo $dept['category_id']; ?>" 
                            data-level="<?php echo htmlspecialchars($dept['level']); ?>" 
                            data-status="<?php echo $dept['is_active']; ?>">
                            <td><?php echo $no++; ?></td>
                            <td>
                                <span class="badge bg-secondary"><?php echo htmlspecialchars($dept['code']); ?></span>
                            </td>
                            <td>
                                <div>
                                    <strong><?php echo htmlspecialchars($dept['name_th']); ?></strong>
                                    <br>
                                    <small class="text-muted"><?php echo htmlspecialchars($dept['name_en']); ?></small>
                                    <?php if ($dept['is_new']): ?>
                                    <span class="badge bg-success ms-2">NEW</span>
                                    <?php endif; ?>
                                    <?php if (!empty($dept['highlight'])): ?>
                                    <span class="badge bg-danger ms-1"><?php echo htmlspecialchars($dept['highlight']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <small><?php echo htmlspecialchars($dept['category_name']); ?></small>
                            </td>
                            <td>
                                <span class="badge bg-info"><?php echo htmlspecialchars($dept['level']); ?></span>
                            </td>
                            <td>
                                <small class="text-muted"><?php echo htmlspecialchars($dept['study_type']); ?></small>
                            </td>
                            <td class="text-center">
                                <div class="d-flex flex-column gap-1">
                                    <?php if ($dept['open_quota']): ?>
                                    <span class="badge bg-primary">โควตา: <?php echo $dept['seats_quota']; ?></span>
                                    <?php endif; ?>
                                    <?php if ($dept['open_regular']): ?>
                                    <span class="badge bg-success">ปกติ: <?php echo $dept['seats_regular']; ?></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="text-center">
                                <strong class="<?php echo $total_applicants > 0 ? 'text-primary' : 'text-muted'; ?>">
                                    <?php echo number_format($total_applicants); ?>
                                </strong>
                                <br>
                                <small class="text-muted">
                                    Q:<?php echo $dept['quota_count']; ?> / 
                                    R:<?php echo $dept['regular_count']; ?>
                                </small>
                            </td>
                            <td class="text-center">
                                <?php if ($dept['is_active']): ?>
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle me-1"></i>เปิด
                                </span>
                                <?php else: ?>
                                <span class="badge bg-secondary">
                                    <i class="bi bi-x-circle me-1"></i>ปิด
                                </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <?php if (can_show_menu('departments_edit', $admin_role)): ?>
                                    <a href="index.php?page=departments_edit&id=<?php echo $dept['id']; ?>" 
                                       class="btn btn-outline-primary" 
                                       title="แก้ไข">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php endif; ?>
                                    
                                    <?php if (in_array($admin_role, ['superadmin', 'admin'])): ?>
                                    <button type="button" 
                                            class="btn btn-outline-warning btn-toggle-status" 
                                            data-id="<?php echo $dept['id']; ?>" 
                                            data-status="<?php echo $dept['is_active']; ?>"
                                            title="<?php echo $dept['is_active'] ? 'ปิดสาขา' : 'เปิดสาขา'; ?>">
                                        <i class="bi bi-toggle-<?php echo $dept['is_active'] ? 'on' : 'off'; ?>"></i>
                                    </button>
                                    
                                    <button type="button" 
                                            class="btn btn-outline-danger btn-delete-dept" 
                                            data-id="<?php echo $dept['id']; ?>" 
                                            data-code="<?php echo htmlspecialchars($dept['code']); ?>"
                                            data-name="<?php echo htmlspecialchars($dept['name_th']); ?>"
                                            data-applicants="<?php echo $total_applicants; ?>"
                                            title="ลบ">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="10" class="text-center text-muted py-5">
                                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                <p class="mb-0">ยังไม่มีข้อมูลสาขาวิชา</p>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// รอให้ DOM โหลดเสร็จก่อน
document.addEventListener('DOMContentLoaded', function() {
    console.log('Department Management Script Loaded');
    
    // ==================== Initialize DataTable (ยังใช้ jQuery สำหรับ DataTables) ====================
    var table = $('#departmentsTable').DataTable({
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/th.json'
        },
        order: [[2, 'asc']],
        pageLength: 25,
        columnDefs: [
            { orderable: false, targets: [9] }
        ]
    });
    
    console.log('DataTable initialized');

    // ==================== Real-time Search ====================
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            console.log('Searching:', this.value);
            table.search(this.value).draw();
        });
    }

    // ==================== Filter by Level ====================
    const filterLevel = document.getElementById('filterLevel');
    if (filterLevel) {
        filterLevel.addEventListener('change', function() {
            const value = this.value;
            console.log('Filter Level:', value);
            
            if (value) {
                table.column(4).search('^' + value + '$', true, false).draw();
            } else {
                table.column(4).search('').draw();
            }
        });
    }

    // ==================== Filter by Category ====================
    const filterCategory = document.getElementById('filterCategory');
    if (filterCategory) {
        filterCategory.addEventListener('change', function() {
            const value = this.value;
            console.log('Filter Category:', value);
            
            if (value) {
                const rows = document.querySelectorAll('#departmentsTable tbody tr');
                rows.forEach(row => {
                    if (row.dataset.category == value) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
                table.draw();
            } else {
                const rows = document.querySelectorAll('#departmentsTable tbody tr');
                rows.forEach(row => {
                    row.style.display = '';
                });
                table.draw();
            }
        });
    }

    // ==================== Filter by Status ====================
    const filterStatus = document.getElementById('filterStatus');
    if (filterStatus) {
        filterStatus.addEventListener('change', function() {
            const value = this.value;
            console.log('Filter Status:', value);
            
            if (value !== '') {
                const rows = document.querySelectorAll('#departmentsTable tbody tr');
                rows.forEach(row => {
                    if (row.dataset.status == value) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
                table.draw();
            } else {
                const rows = document.querySelectorAll('#departmentsTable tbody tr');
                rows.forEach(row => {
                    row.style.display = '';
                });
                table.draw();
            }
        });
    }

    // ==================== Clear All Filters ====================
    const clearFilters = document.getElementById('clearFilters');
    if (clearFilters) {
        clearFilters.addEventListener('click', function() {
            console.log('Clearing filters');
            
            if (searchInput) searchInput.value = '';
            if (filterLevel) filterLevel.value = '';
            if (filterCategory) filterCategory.value = '';
            if (filterStatus) filterStatus.value = '';
            
            table.search('').columns().search('').draw();
            
            const rows = document.querySelectorAll('#departmentsTable tbody tr');
            rows.forEach(row => {
                row.style.display = '';
            });
            table.draw();
        });
    }

    // ==================== Toggle Status ====================
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-toggle-status')) {
            e.preventDefault();
            
            const btn = e.target.closest('.btn-toggle-status');
            const deptId = btn.dataset.id;
            const currentStatus = parseInt(btn.dataset.status);
            const newStatus = currentStatus == 1 ? 0 : 1;
            const statusText = newStatus == 1 ? 'เปิด' : 'ปิด';
            
            console.log('Toggle Status - Dept ID:', deptId, 'Current:', currentStatus, 'New:', newStatus);

            Swal.fire({
                title: `ยืนยันการ${statusText}สาขาวิชา?`,
                text: `คุณต้องการ${statusText}การรับสมัครสาขาวิชานี้หรือไม่?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'ยืนยัน',
                cancelButtonText: 'ยกเลิก',
                confirmButtonColor: newStatus == 1 ? '#28a745' : '#ffc107',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (result.isConfirmed) {
                    // แสดง Loading
                    Swal.fire({
                        title: 'กำลังประมวลผล...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // ส่งข้อมูลด้วย Fetch API
                    fetch('api/department_toggle_status.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            dept_id: parseInt(deptId),
                            is_active: newStatus
                        })
                    })
                    .then(response => {
                        console.log('Response status:', response.status);
                        return response.json();
                    })
                    .then(data => {
                        console.log('Toggle Response:', data);
                        
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'สำเร็จ!',
                                text: data.message,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'เกิดข้อผิดพลาด',
                                text: data.message
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Toggle Error:', error);
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'เกิดข้อผิดพลาด',
                            text: 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้: ' + error.message
                        });
                    });
                }
            });
        }
    });

    // ==================== Delete Department ====================
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-delete-dept')) {
            e.preventDefault();
            
            const btn = e.target.closest('.btn-delete-dept');
            const deptId = btn.dataset.id;
            const deptCode = btn.dataset.code;
            const deptName = btn.dataset.name;
            const applicants = parseInt(btn.dataset.applicants) || 0;
            
            console.log('Delete - Dept ID:', deptId, 'Applicants:', applicants);

            let warningHtml = `คุณต้องการลบสาขาวิชา<br><strong class="text-danger">"${deptCode} - ${deptName}"</strong><br>ใช่หรือไม่?`;
            
            if (applicants > 0) {
                warningHtml += `<br><br><div class="alert alert-warning mt-2 mb-0">
                    <i class="bi bi-exclamation-triangle"></i> 
                    สาขานี้มีผู้สมัครแล้ว <strong>${applicants}</strong> คน
                </div>`;
            }

            Swal.fire({
                title: 'ยืนยันการลบ?',
                html: warningHtml,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'ใช่, ลบเลย!',
                cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                input: 'checkbox',
                inputValue: 0,
                inputPlaceholder: 'ฉันเข้าใจว่าการลบนี้ไม่สามารถย้อนกลับได้'
            }).then((result) => {
                if (result.isConfirmed) {
                    if (!result.value) {
                        Swal.fire({
                            icon: 'error',
                            title: 'กรุณายืนยัน',
                            text: 'กรุณาทำเครื่องหมายยืนยันการลบ'
                        });
                        return;
                    }

                    // แสดง Loading
                    Swal.fire({
                        title: 'กำลังลบข้อมูล...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // ส่งข้อมูลด้วย Fetch API
                    fetch('api/department_delete.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            dept_id: parseInt(deptId)
                        })
                    })
                    .then(response => {
                        console.log('Response status:', response.status);
                        return response.json();
                    })
                    .then(data => {
                        console.log('Delete Response:', data);
                        
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'ลบสำเร็จ!',
                                text: data.message,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'เกิดข้อผิดพลาด',
                                text: data.message
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Delete Error:', error);
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'เกิดข้อผิดพลาด',
                            text: 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้: ' + error.message
                        });
                    });
                }
            });
        }
    });
});
</script>