<?php

/**
 * Regular List - Admin Panel
 * รายชื่อผู้สมัครรอบปกติ
 */
if ($_SESSION['admin_role'] != 'regular' && $_SESSION['admin_role'] != 'superadmin') {
    echo '<div class="alert alert-danger m-4">
            <i class="bi bi-exclamation-triangle me-2"></i>
            คุณไม่มีสิทธิ์เข้าถึงหน้านี้
          </div>';
    exit();
}

// ดึงข้อมูลสถิติ
$current_year = date('Y') + 543 + 1;

// กรองตาม Status
$status_filter = isset($_GET['status']) ? clean_input($_GET['status']) : '';
$department_filter = isset($_GET['department']) ? (int)$_GET['department'] : 0;
$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';

// สร้าง Query
$sql = "SELECT 
    sr.*,
    d.name_th as department_name,
    d.level,
    d.code as department_code
FROM students_regular sr
LEFT JOIN departments d ON sr.department_id = d.id
WHERE sr.academic_year = '$current_year'";

// เพิ่มเงื่อนไขการกรอง
if (!empty($status_filter)) {
    $sql .= " AND sr.status = '$status_filter'";
}

if ($department_filter > 0) {
    $sql .= " AND sr.department_id = $department_filter";
}

if (!empty($search)) {
    $sql .= " AND (sr.application_no LIKE '%$search%' 
              OR sr.firstname_th LIKE '%$search%' 
              OR sr.lastname_th LIKE '%$search%'
              OR sr.id_card LIKE '%$search%')";
}

$sql .= " ORDER BY sr.created_at DESC";

$result = $conn->query($sql);

// ดึงรายชื่อสาขาวิชาสำหรับ Filter
$departments_sql = "SELECT id, code, name_th, level FROM departments WHERE is_active = 1 ORDER BY level, name_th";
$departments = $conn->query($departments_sql);

// นับจำนวนตาม Status
$stats_sql = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
FROM students_regular 
WHERE academic_year = '$current_year'";
$stats_result = $conn->query($stats_sql);
$stats = $stats_result->fetch_assoc();
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">
                <i class="bi bi-people-fill text-success"></i>
                รายชื่อผู้สมัครรอบปกติ
            </h2>
            <p class="text-muted mb-0">
                จัดการข้อมูลผู้สมัครรอบปกติ ปีการศึกษา <?php echo $current_year; ?>
            </p>
        </div>
        <div>
            <a href="index.php?page=export_excel&type=regular" class="btn btn-success">
                <i class="bi bi-file-earmark-excel me-2"></i>
                Export Excel
            </a>
            <a href="index.php?page=export_pdf&type=regular" class="btn btn-danger">
                <i class="bi bi-file-earmark-pdf me-2"></i>
                Export PDF
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">ทั้งหมด</h6>
                            <h3 class="mb-0 fw-bold"><?php echo number_format($stats['total']); ?></h3>
                        </div>
                        <div class="bg-success bg-opacity-10 rounded-circle">
                            <i class="bi bi-people fs-4 text-success icon-circle"></i>
                        </div>
                    </div>
                    <a href="index.php?page=regular_list" class="btn btn-sm btn-outline-success w-100 mt-3">
                        ดูทั้งหมด
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">รอตรวจสอบ</h6>
                            <h3 class="mb-0 fw-bold text-warning"><?php echo number_format($stats['pending']); ?></h3>
                        </div>
                        <div class="bg-warning bg-opacity-10 rounded-circle">
                            <i class="bi bi-hourglass-split fs-4 text-warning icon-circle"></i>
                        </div>
                    </div>
                    <a href="index.php?page=regular_list&status=pending" class="btn btn-sm btn-outline-warning w-100 mt-3">
                        ตรวจสอบ
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">อนุมัติแล้ว</h6>
                            <h3 class="mb-0 fw-bold text-success"><?php echo number_format($stats['approved']); ?></h3>
                        </div>
                        <div class="bg-success bg-opacity-10 rounded-circle">
                            <i class="bi bi-check-circle fs-4 text-success icon-circle"></i>
                        </div>
                    </div>
                    <a href="index.php?page=regular_list&status=approved" class="btn btn-sm btn-outline-success w-100 mt-3">
                        ดูรายชื่อ
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">ไม่อนุมัติ</h6>
                            <h3 class="mb-0 fw-bold text-danger"><?php echo number_format($stats['rejected']); ?></h3>
                        </div>
                        <div class="bg-danger bg-opacity-10 rounded-circle">
                            <i class="bi bi-x-circle fs-4 text-danger icon-circle"></i>
                        </div>
                    </div>
                    <a href="index.php?page=regular_list&status=rejected" class="btn btn-sm btn-outline-danger w-100 mt-3">
                        ดูรายชื่อ
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="index.php" class="row g-3">
                <input type="hidden" name="page" value="regular_list">

                <div class="col-md-4">
                    <label class="form-label fw-bold">
                        <i class="bi bi-search me-1"></i> ค้นหา
                    </label>
                    <input type="text"
                        name="search"
                        class="form-control"
                        placeholder="เลขที่ใบสมัคร, ชื่อ, นามสกุล, เลขบัตร"
                        value="<?php echo htmlspecialchars($search); ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">
                        <i class="bi bi-filter me-1"></i> สถานะ
                    </label>
                    <select name="status" class="form-select">
                        <option value="">ทั้งหมด</option>
                        <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>
                            รอตรวจสอบ
                        </option>
                        <option value="approved" <?php echo $status_filter == 'approved' ? 'selected' : ''; ?>>
                            อนุมัติแล้ว
                        </option>
                        <option value="rejected" <?php echo $status_filter == 'rejected' ? 'selected' : ''; ?>>
                            ไม่อนุมัติ
                        </option>
                        <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>
                            ยกเลิก
                        </option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">
                        <i class="bi bi-mortarboard me-1"></i> สาขาวิชา
                    </label>
                    <select name="department" class="form-select">
                        <option value="">ทุกสาขา</option>
                        <?php while ($dept = $departments->fetch_assoc()): ?>
                            <option value="<?php echo $dept['id']; ?>"
                                <?php echo $department_filter == $dept['id'] ? 'selected' : ''; ?>>
                                [<?php echo $dept['level']; ?>] <?php echo $dept['name_th']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-success w-100 me-2">
                        <i class="bi bi-search me-1"></i> ค้นหา
                    </button>
                    <a href="index.php?page=regular_list" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">
                <i class="bi bi-table me-2"></i>
                รายชื่อผู้สมัคร
                <?php if (!empty($status_filter)): ?>
                    <span class="badge <?php echo get_status_class($status_filter); ?>">
                        <?php echo get_status_text($status_filter); ?>
                    </span>
                <?php endif; ?>
                <span class="badge bg-secondary">
                    <?php echo $result ? $result->num_rows : 0; ?> รายการ
                </span>
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover data-table" id="regularTable">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">ลำดับ</th>
                            <th width="10%">เลขที่ใบสมัคร</th>
                            <th width="20%">ชื่อ-นามสกุล</th>
                            <th width="15%">เลขบัตรประชาชน</th>
                            <th width="15%">สาขาวิชา</th>
                            <th width="10%">ระดับ</th>
                            <th width="10%">สถานะ</th>
                            <th width="10%">วันที่สมัคร</th>
                            <th width="5%" class="text-center no-sort">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php $no = 1; ?>
                            <?php while ($student = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td>
                                        <strong class="text-success">
                                            <?php echo htmlspecialchars($student['application_no']); ?>
                                        </strong>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-success bg-opacity-10 rounded-circle me-2 d-flex align-items-center justify-content-center">
                                                <i class="bi bi-person text-success"></i>
                                            </div>
                                            <div>
                                                <strong>
                                                    <?php echo htmlspecialchars($student['prefix'] . ' ' . $student['firstname_th'] . ' ' . $student['lastname_th']); ?>
                                                </strong>
                                                <?php if (!empty($student['nickname'])): ?>
                                                    <br><small class="text-muted">
                                                        (<?php echo htmlspecialchars($student['nickname']); ?>)
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <code><?php echo htmlspecialchars($student['id_card']); ?></code>
                                    </td>
                                    <td>
                                        <small class="text-muted d-block">
                                            [<?php echo htmlspecialchars($student['department_code']); ?>]
                                        </small>
                                        <?php echo htmlspecialchars($student['department_name']); ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo htmlspecialchars($student['level']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo get_status_badge($student['status']); ?>
                                    </td>
                                    <td>
                                        <small>
                                            <i class="bi bi-calendar me-1"></i>
                                            <?php echo thai_date($student['created_at'], 'short'); ?>
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light"
                                                type="button"
                                                data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item"
                                                        href="index.php?page=student_detail&type=regular&id=<?php echo $student['id']; ?>">
                                                        <i class="bi bi-eye me-2 text-primary"></i>
                                                        ดูรายละเอียด
                                                    </a>
                                                </li>
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <?php
                                                $current_status = $student['status'];

                                                // กำหนดตัวเลือกสถานะทั้งหมด
                                                $status_options = [
                                                    'pending' => ['label' => 'รอตรวจสอบ', 'icon' => 'bi-hourglass-split', 'color' => 'text-warning'],
                                                    'approved' => ['label' => 'อนุมัติ', 'icon' => 'bi-check-circle', 'color' => 'text-success'],
                                                    'rejected' => ['label' => 'ไม่อนุมัติ', 'icon' => 'bi-x-circle', 'color' => 'text-danger'],
                                                    'cancelled' => ['label' => 'ยกเลิก', 'icon' => 'bi-slash-circle', 'color' => 'text-secondary'],
                                                ];

                                                // วนลูปสร้างตัวเลือก ยกเว้นสถานะปัจจุบัน
                                                foreach ($status_options as $status_key => $option) {
                                                    if ($status_key != $current_status) {
                                                        // สร้างลิงก์สำหรับเปลี่ยนสถานะด้วยฟังก์ชัน updateStatus()
                                                        echo '<li>';
                                                        echo '<a class="dropdown-item" ';
                                                        echo 'href="javascript:void(0)" ';
                                                        echo "onclick=\"updateStatus({$student['id']}, '{$status_key}', 'regular')\">";
                                                        echo "<i class=\"bi {$option['icon']} me-2 {$option['color']}\"></i>";
                                                        echo "{$option['label']}";
                                                        echo '</a>';
                                                        echo '</li>';
                                                    }
                                                }
                                                ?>

                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li>
                                                    <a class="dropdown-item"
                                                        href="../pages/download_regular_application_pdf.php?app_no=<?php echo $student['application_no']; ?>&type=regular"
                                                        target="_blank">
                                                        <i class="bi bi-file-pdf me-2 text-danger"></i>
                                                        ดาวน์โหลด PDF
                                                    </a>
                                                </li>
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li>
                                                    <a class="dropdown-item text-danger"
                                                        href="javascript:void(0)"
                                                        onclick="deleteStudent(<?php echo $student['id']; ?>, 'regular')">
                                                        <i class="bi bi-trash me-2"></i>
                                                        ลบ
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <i class="bi bi-inbox display-1 text-muted"></i>
                                    <p class="text-muted mt-3 mb-0">ไม่พบข้อมูลผู้สมัคร</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .avatar-sm {
        width: 40px;
        height: 40px;
        font-size: 1.2rem;
    }
</style>

<script>
    // Initialize DataTable
    $(document).ready(function() {
        $('#regularTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/th.json'
            },
            responsive: true,
            pageLength: 25,
            order: [
                [0, 'asc']
            ],
            columnDefs: [{
                orderable: false,
                targets: 'no-sort'
            }]
        });
    });

    // Update Status Function
    async function updateStatus(id, status, type) {
        const statusText = {
            'approved': 'อนุมัติ',
            'rejected': 'ไม่อนุมัติ',
            'cancelled': 'ยกเลิก'
        };

        const result = await Swal.fire({
            title: 'ยืนยันการเปลี่ยนสถานะ',
            text: `คุณต้องการ${statusText[status]}ใบสมัครนี้ใช่หรือไม่?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: status === 'approved' ? '#28a745' : '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'ยืนยัน',
            cancelButtonText: 'ยกเลิก'
        });

        if (result.isConfirmed) {
            showLoading('กำลังอัพเดทสถานะ...');

            try {
                const response = await fetch('api/update_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id: id,
                        status: status,
                        type: type
                    })
                });

                const data = await response.json();

                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'สำเร็จ',
                        text: 'อัพเดทสถานะเรียบร้อยแล้ว',
                        confirmButtonText: 'ตกลง'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    showError('เกิดข้อผิดพลาด', data.message || 'ไม่สามารถอัพเดทสถานะได้');
                }
            } catch (error) {
                showError('เกิดข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์');
            }
        }
    }

    // Delete Student Function
    async function deleteStudent(id, type) {
        const result = await Swal.fire({
            title: 'ยืนยันการลบ',
            text: 'คุณแน่ใจหรือไม่ว่าต้องการลบข้อมูลนี้? การดำเนินการนี้ไม่สามารถย้อนกลับได้',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'ลบเลย',
            cancelButtonText: 'ยกเลิก'
        });

        if (result.isConfirmed) {
            showLoading('กำลังลบข้อมูล...');

            try {
                const response = await fetch('api/delete_student.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id: id,
                        type: type
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
                    showError('เกิดข้อผิดพลาด', data.message || 'ไม่สามารถลบข้อมูลได้');
                }
            } catch (error) {
                showError('เกิดข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์');
            }
        }
    }
</script>