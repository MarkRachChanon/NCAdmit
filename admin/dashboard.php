<?php

/**
 * Dashboard - Admin Panel
 * แสดงภาพรวมและสถิติ
 */

// ดึงข้อมูลสถิติ
$current_year = get_setting('academic_year');

// นับจำนวนผู้สมัครรอบโควตา
$sql_quota = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
FROM students_quota 
WHERE academic_year = '$current_year'";
$quota_result = $conn->query($sql_quota);
$quota_stats = $quota_result ? $quota_result->fetch_assoc() : ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0];

// แปลง NULL เป็น 0
$quota_stats['total'] = (int)($quota_stats['total'] ?? 0);
$quota_stats['pending'] = (int)($quota_stats['pending'] ?? 0);
$quota_stats['approved'] = (int)($quota_stats['approved'] ?? 0);
$quota_stats['rejected'] = (int)($quota_stats['rejected'] ?? 0);

// นับจำนวนผู้สมัครรอบปกติ
$sql_regular = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
FROM students_regular 
WHERE academic_year = '$current_year'";
$regular_result = $conn->query($sql_regular);
$regular_stats = $regular_result ? $regular_result->fetch_assoc() : ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0];

// แปลง NULL เป็น 0
$regular_stats['total'] = (int)($regular_stats['total'] ?? 0);
$regular_stats['pending'] = (int)($regular_stats['pending'] ?? 0);
$regular_stats['approved'] = (int)($regular_stats['approved'] ?? 0);
$regular_stats['rejected'] = (int)($regular_stats['rejected'] ?? 0);

// นับจำนวนสาขาวิชาและแยกตามระดับ
$sql_departments_by_level = "
    SELECT 
        level, 
        COUNT(*) as count 
    FROM departments 
    WHERE is_active = 1 
    GROUP BY level
    -- จัดลำดับการแสดงผลให้ถูกต้อง (ปวช., ปวส., ปริญญาตรี)
    ORDER BY FIELD(level, 'ปวช.', 'ปวส.', 'ปริญญาตรี')
";
$departments_by_level_result = $conn->query($sql_departments_by_level);

$departments_level_stats = [];
$departments_count = 0; // ยอดรวมสาขาที่เปิดรับ

if ($departments_by_level_result) {
    while ($row = $departments_by_level_result->fetch_assoc()) {
        // เก็บจำนวนสาขาแยกตามระดับการศึกษา
        $departments_level_stats[$row['level']] = (int)$row['count'];
        // คำนวณยอดรวมสาขา
        $departments_count += (int)$row['count'];
    }
}

// ดึงผู้สมัครล่าสุด (5 คน)
$sql_recent_quota = "SELECT * FROM students_quota ORDER BY created_at DESC LIMIT 5";
$recent_quota = $conn->query($sql_recent_quota);

$sql_recent_regular = "SELECT * FROM students_regular ORDER BY created_at DESC LIMIT 5";
$recent_regular = $conn->query($sql_recent_regular);

// นับจำนวนสาขาวิชา
$sql_departments = "SELECT COUNT(*) as total FROM departments WHERE is_active = 1";
$departments_result = $conn->query($sql_departments);
$departments_count = 0;
if ($departments_result && $departments_result->num_rows > 0) {
    $departments_count = (int)$departments_result->fetch_assoc()['total'];
}

// ข้อมูลสำหรับกราฟ - การสมัครแยกตามสาขา (Top 5)
$sql_dept_stats = "SELECT 
    d.name_th, 
    COALESCE(COUNT(DISTINCT sq.id), 0) + COALESCE(COUNT(DISTINCT sr.id), 0) as total_applications
FROM departments d
LEFT JOIN students_quota sq ON d.id = sq.department_id AND sq.academic_year = '$current_year'
LEFT JOIN students_regular sr ON d.id = sr.department_id AND sr.academic_year = '$current_year'
WHERE d.is_active = 1
GROUP BY d.id, d.name_th
HAVING total_applications > 0
ORDER BY total_applications DESC
LIMIT 5";
$dept_stats = $conn->query($sql_dept_stats);

// ถ้าไม่มีข้อมูล ให้แสดงสาขาทั้งหมด
if (!$dept_stats || $dept_stats->num_rows == 0) {
    $sql_dept_stats = "SELECT name_th, 0 as total_applications FROM departments WHERE is_active = 1 LIMIT 5";
    $dept_stats = $conn->query($sql_dept_stats);
}

// ข้อมูลสำหรับแสดงรายละเอียดทุกสาขา (สำหรับ Modal) - แยกตามสถานะ
$sql_all_departments = "SELECT 
    d.id,
    d.code,
    d.name_th,
    d.level,
    -- รอบโควตา
    COALESCE(COUNT(DISTINCT CASE WHEN sq.status = 'approved' THEN sq.id END), 0) as quota_approved,
    COALESCE(COUNT(DISTINCT CASE WHEN sq.status = 'pending' THEN sq.id END), 0) as quota_pending,
    COALESCE(COUNT(DISTINCT CASE WHEN sq.status = 'rejected' THEN sq.id END), 0) as quota_rejected,
    COALESCE(COUNT(DISTINCT CASE WHEN sq.status = 'cancelled' THEN sq.id END), 0) as quota_cancelled,
    COALESCE(COUNT(DISTINCT sq.id), 0) as quota_count,
    -- รอบปกติ
    COALESCE(COUNT(DISTINCT CASE WHEN sr.status = 'approved' THEN sr.id END), 0) as regular_approved,
    COALESCE(COUNT(DISTINCT CASE WHEN sr.status = 'pending' THEN sr.id END), 0) as regular_pending,
    COALESCE(COUNT(DISTINCT CASE WHEN sr.status = 'rejected' THEN sr.id END), 0) as regular_rejected,
    COALESCE(COUNT(DISTINCT CASE WHEN sr.status = 'cancelled' THEN sr.id END), 0) as regular_cancelled,
    COALESCE(COUNT(DISTINCT sr.id), 0) as regular_count,
    -- รวมทั้งหมด
    COALESCE(COUNT(DISTINCT sq.id), 0) + COALESCE(COUNT(DISTINCT sr.id), 0) as total_count,
    COALESCE(COUNT(DISTINCT CASE WHEN sq.status = 'approved' THEN sq.id END), 0) + 
    COALESCE(COUNT(DISTINCT CASE WHEN sr.status = 'approved' THEN sr.id END), 0) as total_approved
FROM departments d
LEFT JOIN students_quota sq ON d.id = sq.department_id AND sq.academic_year = '$current_year'
LEFT JOIN students_regular sr ON d.id = sr.department_id AND sr.academic_year = '$current_year'
WHERE d.is_active = 1
GROUP BY d.id, d.code, d.name_th, d.level
ORDER BY d.level, total_count DESC, d.name_th";
$all_departments_result = $conn->query($sql_all_departments);

// จัดกลุ่มข้อมูลตามระดับชั้น
$departments_by_level = [
    'ปวช.' => [],
    'ปวส.' => [],
    'ปริญญาตรี' => []
];

if ($all_departments_result && $all_departments_result->num_rows > 0) {
    while ($dept = $all_departments_result->fetch_assoc()) {
        $departments_by_level[$dept['level']][] = $dept;
    }
}
?>

<style>
    /* เพิ่ม CSS สำหรับ Tab ให้มองเห็นชัดเจน */
    #departmentTabs .nav-link {
        color: #495057 !important;
        background-color: #f8f9fa;
        border-color: #dee2e6;
        margin-right: 5px;
    }

    #departmentTabs .nav-link:hover {
        color: #0056b3 !important;
        background-color: #e9ecef;
    }

    #departmentTabs .nav-link.active {
        color: #fff !important;
        background-color: #0d6efd;
        border-color: #0d6efd;
    }

    .modal-header-gradient {
        background: linear-gradient(135deg, #0061ff 0%, #764ba2 100%);
    }
</style>

<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0"><i class="bi bi-speedometer2 text-primary"></i> แดชบอร์ด</h2>
            <p class="text-muted mb-0">ภาพรวมระบบรับสมัคร ปีการศึกษา <?php echo $current_year; ?></p>
        </div>
        <div>
            <span class="badge bg-primary fs-6">
                <i class="bi bi-calendar-check me-1"></i>
                <?php echo thai_date(date('Y-m-d'), 'full'); ?>
            </span>
        </div>
    </div>

    <!-- Welcome Alert -->
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="bi bi-info-circle me-2"></i>
        <strong>ยินดีต้อนรับ, <?php echo htmlspecialchars($admin_fullname); ?>!</strong>
        <span class="ms-2">คุณเข้าสู่ระบบในฐานะ <strong><?php echo $admin_role; ?></strong></span>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>

    <!-- Stats Cards Row 1 -->
    <div class="row g-3 mb-4">
        <!-- Card 1: รอบโควตา -->
        <div class="col-md-3">
            <div class="card stat-card bg-gradient-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-white-50 mb-2">ผู้สมัครรอบโควตา</h6>
                            <h2 class="mb-0 fw-bold"><?php echo number_format($quota_stats['total']); ?></h2>
                            <small class="text-white-50">คน</small>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-person-lines-fill"></i>
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-top border-white-25">
                        <small>
                            <i class="bi bi-clock me-1"></i> รอตรวจสอบ: <?php echo number_format($quota_stats['pending']); ?> คน<br>
                            <i class="bi bi-check-circle me-1 text-info"></i> อนุมัติ: <?php echo number_format($quota_stats['approved']); ?> คน<br>
                            <i class="bi bi-x-circle me-1 text-danger"></i> ไม่อนุมัติ: <?php echo number_format($quota_stats['rejected']); ?> คน
                        </small>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <a href="index.php?page=quota_list" class="text-white text-decoration-none">
                        ดูรายละเอียด <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Card 2: รอบปกติ -->
        <div class="col-md-3">
            <div class="card stat-card bg-gradient-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-white-50 mb-2">ผู้สมัครรอบปกติ</h6>
                            <h2 class="mb-0 fw-bold"><?php echo number_format($regular_stats['total']); ?></h2>
                            <small class="text-white-50">คน</small>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-people-fill"></i>
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-top border-white-25">
                        <small>
                            <i class="bi bi-clock me-1"></i> รอตรวจสอบ: <?php echo number_format($regular_stats['pending']); ?> คน<br>
                            <i class="bi bi-check-circle me-1 text-info"></i> อนุมัติ: <?php echo number_format($regular_stats['approved']); ?> คน<br>
                            <i class="bi bi-x-circle me-1 text-danger"></i> ไม่อนุมัติ: <?php echo number_format($regular_stats['rejected']); ?> คน
                        </small>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <a href="index.php?page=regular_list" class="text-white text-decoration-none">
                        ดูรายละเอียด <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Card 3: อนุมัติแล้ว -->
        <div class="col-md-3">
            <div class="card stat-card bg-gradient-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-white-50 mb-2">อนุมัติแล้ว</h6>
                            <h2 class="mb-0 fw-bold"><?php echo number_format($quota_stats['approved'] + $regular_stats['approved']); ?></h2>
                            <small class="text-white-50">คน</small>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-top border-white-25">
                        <small>
                            โควตา: <?php echo number_format($quota_stats['approved']); ?> | ปกติ: <?php echo number_format($regular_stats['approved']); ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 4: สาขาวิชา -->
        <div class="col-md-3">
            <div class="card stat-card bg-gradient-warning text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-white-50 mb-2">สาขาวิชาที่เปิดรับ</h6>
                            <h2 class="mb-0 fw-bold"><?php echo number_format($departments_count); ?></h2>
                            <small class="text-white-50">สาขา</small>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-mortarboard-fill"></i>
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-top border-white-25">
                        <small>
                            <?php
                            $level_output = [];
                            // กำหนดลำดับการแสดงผลตาม Level (ต้องตรงกับ Enum ใน DB: ปวช., ปวส., ปริญญาตรี)
                            $levels_order = ['ปวช.', 'ปวส.', 'ปริญญาตรี'];

                            foreach ($levels_order as $level) {
                                $count = $departments_level_stats[$level] ?? 0;
                                if ($count > 0) {
                                    // เก็บผลลัพธ์ในรูปแบบ 'ระดับ: จำนวน'
                                    $level_output[] = "{$level}: " . number_format($count);
                                }
                            }

                            if (!empty($level_output)) {
                                // แสดงผลลัพธ์ที่จัดกลุ่มแล้ว เช่น 'ปวช.: 12 | ปวส.: 15 | ปริญญาตรี: 10'
                                echo '<i class="bi bi-building me-1"></i> แยกตามระดับ: ' . implode(' | ', $level_output);
                            } else {
                                // กรณีไม่มีสาขาเปิดรับเลย
                                echo '<i class="bi bi-building me-1"></i> ไม่มีสาขาวิชาที่เปิดรับ';
                            }
                            ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts & Recent Applications Row -->
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-pie-chart text-primary"></i> สถิติการสมัคร</h5>
                </div>
                <div class="card-body">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-bar-chart text-success"></i> สาขาที่มีผู้สมัครมากที่สุด (Top 5)</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#allDepartmentsModal">
                        <i class="bi bi-list-ul me-1"></i> ดูทุกสาขา
                    </button>
                </div>
                <div class="card-body">
                    <canvas id="departmentChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-clock-history text-primary"></i> ผู้สมัครล่าสุด (โควตา)</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php if ($recent_quota && $recent_quota->num_rows > 0): ?>
                            <?php while ($student = $recent_quota->fetch_assoc()): ?>
                                <a href="index.php?page=student_detail&type=quota&id=<?php echo $student['id']; ?>"
                                    class="list-group-item list-group-item-action">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0"><?php echo htmlspecialchars($student['firstname_th'] . ' ' . $student['lastname_th']); ?></h6>
                                            <small class="text-muted"><?php echo $student['application_no']; ?></small>
                                        </div>
                                        <?php echo get_status_badge($student['status']); ?>
                                    </div>
                                    <small class="text-muted">
                                        <i class="bi bi-clock me-1"></i>
                                        <?php echo thai_date($student['created_at'], 'short'); ?>
                                    </small>
                                </a>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="text-center py-4 text-muted">
                                <i class="bi bi-inbox display-4"></i>
                                <p class="mb-0 mt-2">ยังไม่มีผู้สมัคร</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-footer bg-white text-center">
                    <a href="index.php?page=quota_list" class="text-decoration-none">
                        ดูทั้งหมด <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-clock-history text-success"></i> ผู้สมัครล่าสุด (ปกติ)</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php if ($recent_regular && $recent_regular->num_rows > 0): ?>
                            <?php while ($student = $recent_regular->fetch_assoc()): ?>
                                <a href="index.php?page=student_detail&type=regular&id=<?php echo $student['id']; ?>"
                                    class="list-group-item list-group-item-action">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0"><?php echo htmlspecialchars($student['firstname_th'] . ' ' . $student['lastname_th']); ?></h6>
                                            <small class="text-muted"><?php echo $student['application_no']; ?></small>
                                        </div>
                                        <?php echo get_status_badge($student['status']); ?>
                                    </div>
                                    <small class="text-muted">
                                        <i class="bi bi-clock me-1"></i>
                                        <?php echo thai_date($student['created_at'], 'short'); ?>
                                    </small>
                                </a>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="text-center py-4 text-muted">
                                <i class="bi bi-inbox display-4"></i>
                                <p class="mb-0 mt-2">ยังไม่มีผู้สมัคร</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-footer bg-white text-center">
                    <a href="index.php?page=regular_list" class="text-decoration-none">
                        ดูทั้งหมด <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-3 mt-2">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-lightning text-warning"></i> เมนูด่วน</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <a href="index.php?page=quota_list&status=pending" class="btn btn-outline-primary w-100">
                                <i class="bi bi-hourglass-split me-2"></i>
                                ตรวจสอบใบสมัคร (โควตา)
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="index.php?page=regular_list&status=pending" class="btn btn-outline-success w-100">
                                <i class="bi bi-hourglass-split me-2"></i>
                                ตรวจสอบใบสมัคร (ปกติ)
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="index.php?page=export_excel" class="btn btn-outline-info w-100">
                                <i class="bi bi-file-earmark-excel me-2"></i>
                                Export Excel
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="index.php?page=system_settings" class="btn btn-outline-warning w-100">
                                <i class="bi bi-gear me-2"></i>
                                ตั้งค่าระบบ
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: แสดงข้อมูลทุกสาขาวิชา -->
<div class="modal fade" id="allDepartmentsModal" tabindex="-1" aria-labelledby="allDepartmentsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" style="max-width: 95%;">
        <div class="modal-content">
            <div class="modal-header text-white modal-header-gradient">
                <h5 class="modal-title" id="allDepartmentsModalLabel">
                    <i class="bi bi-list-ul me-2"></i>ข้อมูลจำนวนผู้สมัครทุกสาขาวิชา
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>ข้อมูล ณ ปีการศึกษา <?php echo $current_year; ?></strong>
                    <span class="ms-2">แสดงจำนวนผู้สมัครแยกตามระดับชั้น รอบการสมัคร และสถานะ</span>
                </div>

                <!-- Tab Navigation -->
                <ul class="nav nav-tabs mb-3" id="departmentTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="table-tab" data-bs-toggle="tab" data-bs-target="#table-view" type="button" role="tab">
                            <i class="bi bi-table me-1"></i> ตารางข้อมูล
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="chart-tab" data-bs-toggle="tab" data-bs-target="#chart-view" type="button" role="tab">
                            <i class="bi bi-bar-chart-fill me-1"></i> กราฟแสดงผล
                        </button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="departmentTabContent">

                    <!-- Tab 1: ตารางข้อมูล -->
                    <div class="tab-pane fade show active" id="table-view" role="tabpanel">
                        <?php foreach ($departments_by_level as $level => $departments): ?>
                            <?php if (!empty($departments)): ?>
                                <div class="mb-4">
                                    <h5 class="border-bottom pb-2 mb-3">
                                        <i class="bi bi-mortarboard-fill text-primary me-2"></i>
                                        ระดับ <?php echo $level; ?>
                                        <span class="badge bg-primary ms-2"><?php echo count($departments); ?> สาขา</span>
                                    </h5>

                                    <div class="table-responsive">
                                        <table class="table table-hover table-bordered table-sm">
                                            <thead class="table-light">
                                                <tr>
                                                    <th width="5%" class="text-center align-middle" rowspan="2">#</th>
                                                    <th width="12%" class="align-middle" rowspan="2">รหัสสาขา</th>
                                                    <th width="23%" class="align-middle" rowspan="2">ชื่อสาขาวิชา</th>
                                                    <th colspan="5" class="text-center bg-primary text-white">รอบโควตา</th>
                                                    <th colspan="5" class="text-center bg-success text-white">รอบปกติ</th>
                                                    <th width="8%" class="text-center align-middle" rowspan="2">รวมทั้งหมด</th>
                                                </tr>
                                                <tr>
                                                    <!-- รอบโควตา -->
                                                    <th width="5%" class="text-center bg-success text-white">อนุมัติ</th>
                                                    <th width="5%" class="text-center bg-warning">รอตรวจ</th>
                                                    <th width="5%" class="text-center bg-danger text-white">ไม่อนุมัติ</th>
                                                    <th width="5%" class="text-center bg-secondary text-white">ยกเลิก</th>
                                                    <th width="5%" class="text-center bg-primary text-white">รวม</th>
                                                    <!-- รอบปกติ -->
                                                    <th width="5%" class="text-center bg-success text-white">อนุมัติ</th>
                                                    <th width="5%" class="text-center bg-warning">รอตรวจ</th>
                                                    <th width="5%" class="text-center bg-danger text-white">ไม่อนุมัติ</th>
                                                    <th width="5%" class="text-center bg-secondary text-white">ยกเลิก</th>
                                                    <th width="5%" class="text-center bg-success text-white">รวม</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $no = 1;
                                                foreach ($departments as $dept):
                                                ?>
                                                    <tr>
                                                        <td class="text-center"><?php echo $no++; ?></td>
                                                        <td><code class="text-primary"><?php echo htmlspecialchars($dept['code']); ?></code></td>
                                                        <td><?php echo htmlspecialchars($dept['name_th']); ?></td>

                                                        <!-- รอบโควตา -->
                                                        <td class="text-center">
                                                            <?php echo $dept['quota_approved'] > 0 ? '<span class="badge bg-success">' . $dept['quota_approved'] . '</span>' : '-'; ?>
                                                        </td>
                                                        <td class="text-center">
                                                            <?php echo $dept['quota_pending'] > 0 ? '<span class="badge bg-warning text-dark">' . $dept['quota_pending'] . '</span>' : '-'; ?>
                                                        </td>
                                                        <td class="text-center">
                                                            <?php echo $dept['quota_rejected'] > 0 ? '<span class="badge bg-danger">' . $dept['quota_rejected'] . '</span>' : '-'; ?>
                                                        </td>
                                                        <td class="text-center">
                                                            <?php echo $dept['quota_cancelled'] > 0 ? '<span class="badge bg-secondary">' . $dept['quota_cancelled'] . '</span>' : '-'; ?>
                                                        </td>
                                                        <td class="text-center fw-bold">
                                                            <?php echo $dept['quota_count'] > 0 ? '<span class="badge bg-primary">' . $dept['quota_count'] . '</span>' : '-'; ?>
                                                        </td>

                                                        <!-- รอบปกติ -->
                                                        <td class="text-center">
                                                            <?php echo $dept['regular_approved'] > 0 ? '<span class="badge bg-success">' . $dept['regular_approved'] . '</span>' : '-'; ?>
                                                        </td>
                                                        <td class="text-center">
                                                            <?php echo $dept['regular_pending'] > 0 ? '<span class="badge bg-warning text-dark">' . $dept['regular_pending'] . '</span>' : '-'; ?>
                                                        </td>
                                                        <td class="text-center">
                                                            <?php echo $dept['regular_rejected'] > 0 ? '<span class="badge bg-danger">' . $dept['regular_rejected'] . '</span>' : '-'; ?>
                                                        </td>
                                                        <td class="text-center">
                                                            <?php echo $dept['regular_cancelled'] > 0 ? '<span class="badge bg-secondary">' . $dept['regular_cancelled'] . '</span>' : '-'; ?>
                                                        </td>
                                                        <td class="text-center fw-bold">
                                                            <?php echo $dept['regular_count'] > 0 ? '<span class="badge bg-success">' . $dept['regular_count'] . '</span>' : '-'; ?>
                                                        </td>

                                                        <!-- รวมทั้งหมด -->
                                                        <td class="text-center fw-bold">
                                                            <?php echo $dept['total_count'] > 0 ? '<span class="badge bg-dark fs-6">' . $dept['total_count'] . '</span>' : '<span class="text-muted">-</span>'; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>

                                                <!-- แถวสรุปยอดรวมในแต่ละระดับ -->
                                                <tr class="table-info fw-bold">
                                                    <td colspan="3" class="text-end">รวม <?php echo $level; ?>:</td>
                                                    <!-- โควตา -->
                                                    <td class="text-center"><?php echo number_format(array_sum(array_column($departments, 'quota_approved'))); ?></td>
                                                    <td class="text-center"><?php echo number_format(array_sum(array_column($departments, 'quota_pending'))); ?></td>
                                                    <td class="text-center"><?php echo number_format(array_sum(array_column($departments, 'quota_rejected'))); ?></td>
                                                    <td class="text-center"><?php echo number_format(array_sum(array_column($departments, 'quota_cancelled'))); ?></td>
                                                    <td class="text-center"><?php echo number_format(array_sum(array_column($departments, 'quota_count'))); ?></td>
                                                    <!-- ปกติ -->
                                                    <td class="text-center"><?php echo number_format(array_sum(array_column($departments, 'regular_approved'))); ?></td>
                                                    <td class="text-center"><?php echo number_format(array_sum(array_column($departments, 'regular_pending'))); ?></td>
                                                    <td class="text-center"><?php echo number_format(array_sum(array_column($departments, 'regular_rejected'))); ?></td>
                                                    <td class="text-center"><?php echo number_format(array_sum(array_column($departments, 'regular_cancelled'))); ?></td>
                                                    <td class="text-center"><?php echo number_format(array_sum(array_column($departments, 'regular_count'))); ?></td>
                                                    <!-- รวม -->
                                                    <td class="text-center"><?php echo number_format(array_sum(array_column($departments, 'total_count'))); ?></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>

                        <!-- สรุปยอดรวมทั้งหมด -->
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title mb-3"><i class="bi bi-calculator me-2"></i>สรุปยอดรวมทั้งระบบ</h6>
                                <div class="row text-center g-3">
                                    <div class="col-md-3">
                                        <div class="p-3 bg-white rounded shadow-sm">
                                            <h6 class="text-muted mb-2">รวมรอบโควตา</h6>
                                            <h3 class="text-primary mb-0">
                                                <?php
                                                $total_quota = 0;
                                                foreach ($departments_by_level as $depts) {
                                                    $total_quota += array_sum(array_column($depts, 'quota_count'));
                                                }
                                                echo number_format($total_quota);
                                                ?>
                                                <small class="text-muted">คน</small>
                                            </h3>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-3 bg-white rounded shadow-sm">
                                            <h6 class="text-muted mb-2">รวมรอบปกติ</h6>
                                            <h3 class="text-success mb-0">
                                                <?php
                                                $total_regular = 0;
                                                foreach ($departments_by_level as $depts) {
                                                    $total_regular += array_sum(array_column($depts, 'regular_count'));
                                                }
                                                echo number_format($total_regular);
                                                ?>
                                                <small class="text-muted">คน</small>
                                            </h3>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-3 bg-white rounded shadow-sm">
                                            <h6 class="text-muted mb-2">รวมที่อนุมัติ</h6>
                                            <h3 class="text-success mb-0">
                                                <?php
                                                $total_approved = 0;
                                                foreach ($departments_by_level as $depts) {
                                                    $total_approved += array_sum(array_column($depts, 'quota_approved'));
                                                    $total_approved += array_sum(array_column($depts, 'regular_approved'));
                                                }
                                                echo number_format($total_approved);
                                                ?>
                                                <small class="text-muted">คน</small>
                                            </h3>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-3 bg-white rounded shadow-sm">
                                            <h6 class="text-muted mb-2">รวมทั้งหมด</h6>
                                            <h3 class="text-dark mb-0">
                                                <?php echo number_format($total_quota + $total_regular); ?>
                                                <small class="text-muted">คน</small>
                                            </h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 2: กราฟแสดงผล -->
                    <div class="tab-pane fade" id="chart-view" role="tabpanel">
                        <div class="row g-3">
                            <!-- กราฟแท่งเปรียบเทียบ -->
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-white">
                                        <h6 class="mb-0"><i class="bi bi-bar-chart-fill text-primary me-2"></i>เปรียบเทียบจำนวนผู้สมัครแต่ละระดับ</h6>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="levelComparisonChart"></canvas>
                                    </div>
                                </div>
                            </div>

                            <!-- กราฟวงกลมสถานะ -->
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-white">
                                        <h6 class="mb-0"><i class="bi bi-pie-chart-fill text-success me-2"></i>สัดส่วนสถานะการสมัคร</h6>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="statusPieChart"></canvas>
                                    </div>
                                </div>
                            </div>

                            <!-- กราฟแท่งแนวนอน Top 10 -->
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header bg-white">
                                        <h6 class="mb-0"><i class="bi bi-bar-chart-line-fill text-warning me-2"></i>10 สาขาที่มีผู้สมัครมากที่สุด</h6>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="top10DepartmentChart"></canvas>
                                    </div>
                                </div>
                            </div>

                            <!-- กราฟเส้น Stacked -->
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header bg-white">
                                        <h6 class="mb-0"><i class="bi bi-graph-up text-info me-2"></i>สัดส่วนรอบโควตา vs รอบปกติ แยกตามระดับ</h6>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="roundComparisonChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> ปิด
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Scripts -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Status Pie Chart
        const statusCtx = document.getElementById('statusChart');
        if (statusCtx) {
            new Chart(statusCtx, {
                type: 'pie',
                data: {
                    labels: ['รอตรวจสอบ', 'อนุมัติ', 'ไม่อนุมัติ'],
                    datasets: [{
                        data: [
                            <?php echo ($quota_stats['pending'] + $regular_stats['pending']); ?>,
                            <?php echo ($quota_stats['approved'] + $regular_stats['approved']); ?>,
                            <?php echo ($quota_stats['rejected'] + $regular_stats['rejected']); ?>
                        ],
                        backgroundColor: ['#ffc107', '#28a745', '#dc3545'],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    aspectRatio: 1.5,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.label + ': ' + context.parsed + ' คน';
                                }
                            }
                        }
                    }
                }
            });
        }

        // Department Bar Chart
        const deptCtx = document.getElementById('departmentChart');
        if (deptCtx) {
            new Chart(deptCtx, {
                type: 'bar',
                data: {
                    labels: [
                        <?php
                        if ($dept_stats && $dept_stats->num_rows > 0) {
                            $dept_stats->data_seek(0);
                            $labels = [];
                            while ($dept = $dept_stats->fetch_assoc()) {
                                $labels[] = "'" . addslashes($dept['name_th']) . "'";
                            }
                            echo implode(',', $labels);
                        } else {
                            echo "'ยังไม่มีข้อมูล'";
                        }
                        ?>
                    ],
                    datasets: [{
                        label: 'จำนวนผู้สมัคร',
                        data: [
                            <?php
                            if ($dept_stats && $dept_stats->num_rows > 0) {
                                $dept_stats->data_seek(0);
                                $data = [];
                                while ($dept = $dept_stats->fetch_assoc()) {
                                    $data[] = (int)$dept['total_applications'];
                                }
                                echo implode(',', $data);
                            } else {
                                echo "0";
                            }
                            ?>
                        ],
                        backgroundColor: 'rgba(54, 162, 235, 0.8)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    aspectRatio: 2.5,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'ผู้สมัคร: ' + context.parsed.y + ' คน';
                                }
                            }
                        }
                    }
                }
            });
        }

        // ================== กราฟใน Modal ==================

        // เตรียมข้อมูล PHP สำหรับ JavaScript
        const departmentsData = <?php
                                $all_data = [];
                                foreach ($departments_by_level as $level => $depts) {
                                    $all_data[$level] = $depts;
                                }
                                echo json_encode($all_data, JSON_UNESCAPED_UNICODE);
                                ?>;

        // เมื่อเปิด Modal ให้สร้างกราฟ
        const modalElement = document.getElementById('allDepartmentsModal');
        if (modalElement) {
            modalElement.addEventListener('shown.bs.modal', function() {
                initializeModalCharts();
            });
        }

        function initializeModalCharts() {
            // 1. กราฟเปรียบเทียบระดับชั้น
            const levelCtx = document.getElementById('levelComparisonChart');
            if (levelCtx) {
                const levels = ['ปวช.', 'ปวส.', 'ปริญญาตรี'];
                const quotaData = [];
                const regularData = [];

                levels.forEach(level => {
                    const depts = departmentsData[level] || [];
                    quotaData.push(depts.reduce((sum, d) => sum + parseInt(d.quota_count), 0));
                    regularData.push(depts.reduce((sum, d) => sum + parseInt(d.regular_count), 0));
                });

                new Chart(levelCtx, {
                    type: 'bar',
                    data: {
                        labels: levels,
                        datasets: [{
                                label: 'รอบโควตา',
                                data: quotaData,
                                backgroundColor: 'rgba(54, 162, 235, 0.8)',
                                borderColor: 'rgba(54, 162, 235, 1)',
                                borderWidth: 1
                            },
                            {
                                label: 'รอบปกติ',
                                data: regularData,
                                backgroundColor: 'rgba(75, 192, 192, 0.8)',
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 1
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'top'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': ' + context.parsed.y + ' คน';
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // 2. กราฟวงกลมสถานะ
            const statusCtx = document.getElementById('statusPieChart');
            if (statusCtx) {
                let totalApproved = 0,
                    totalPending = 0,
                    totalRejected = 0,
                    totalCancelled = 0;

                Object.values(departmentsData).forEach(levelDepts => {
                    levelDepts.forEach(d => {
                        totalApproved += parseInt(d.quota_approved) + parseInt(d.regular_approved);
                        totalPending += parseInt(d.quota_pending) + parseInt(d.regular_pending);
                        totalRejected += parseInt(d.quota_rejected) + parseInt(d.regular_rejected);
                        totalCancelled += parseInt(d.quota_cancelled) + parseInt(d.regular_cancelled);
                    });
                });

                new Chart(statusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['อนุมัติ', 'รอตรวจสอบ', 'ไม่อนุมัติ', 'ยกเลิก'],
                        datasets: [{
                            data: [totalApproved, totalPending, totalRejected, totalCancelled],
                            backgroundColor: [
                                'rgba(40, 167, 69, 0.8)',
                                'rgba(255, 193, 7, 0.8)',
                                'rgba(220, 53, 69, 0.8)',
                                'rgba(108, 117, 125, 0.8)'
                            ],
                            borderColor: '#fff',
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = ((context.parsed / total) * 100).toFixed(1);
                                        return context.label + ': ' + context.parsed + ' คน (' + percentage + '%)';
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // 3. กราฟ Top 10 สาขา
            const top10Ctx = document.getElementById('top10DepartmentChart');
            if (top10Ctx) {
                const allDepts = [];
                Object.values(departmentsData).forEach(levelDepts => {
                    levelDepts.forEach(d => {
                        allDepts.push({
                            name: d.name_th,
                            total: parseInt(d.total_count),
                            quota: parseInt(d.quota_count),
                            regular: parseInt(d.regular_count)
                        });
                    });
                });

                // เรียงและเอา 10 อันดับแรก
                allDepts.sort((a, b) => b.total - a.total);
                const top10 = allDepts.slice(0, 10);

                new Chart(top10Ctx, {
                    type: 'bar',
                    data: {
                        labels: top10.map(d => d.name),
                        datasets: [{
                                label: 'รอบโควตา',
                                data: top10.map(d => d.quota),
                                backgroundColor: 'rgba(54, 162, 235, 0.8)',
                                borderWidth: 1
                            },
                            {
                                label: 'รอบปกติ',
                                data: top10.map(d => d.regular),
                                backgroundColor: 'rgba(75, 192, 192, 0.8)',
                                borderWidth: 1
                            }
                        ]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                beginAtZero: true,
                                stacked: true
                            },
                            y: {
                                stacked: true
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'top'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': ' + context.parsed.x + ' คน';
                                    }
                                }
                            }
                        }
                    }
                });

                // ปรับความสูงของ canvas ตามจำนวนข้อมูล
                top10Ctx.style.height = (top10.length * 60) + 'px';
            }

            // 4. กราฟเปรียบเทียบรอบ
            const roundCtx = document.getElementById('roundComparisonChart');
            if (roundCtx) {
                const levels = ['ปวช.', 'ปวส.', 'ปริญญาตรี'];
                const quotaData = [];
                const regularData = [];

                levels.forEach(level => {
                    const depts = departmentsData[level] || [];
                    quotaData.push(depts.reduce((sum, d) => sum + parseInt(d.quota_count), 0));
                    regularData.push(depts.reduce((sum, d) => sum + parseInt(d.regular_count), 0));
                });

                new Chart(roundCtx, {
                    type: 'line',
                    data: {
                        labels: levels,
                        datasets: [{
                                label: 'รอบโควตา',
                                data: quotaData,
                                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                                borderColor: 'rgba(54, 162, 235, 1)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.4
                            },
                            {
                                label: 'รอบปกติ',
                                data: regularData,
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.4
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'top'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': ' + context.parsed.y + ' คน';
                                    }
                                }
                            }
                        }
                    }
                });
            }
        }
    });
</script>