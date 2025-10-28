<?php

/**
 * Dashboard - Admin Panel
 * แสดงภาพรวมและสถิติ
 */

// ดึงข้อมูลสถิติ
$current_year = date('Y') + 543 + 1;

// นับจำนวนผู้สมัครรอบโควต้า
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
?>

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
        <!-- Card 1: รอบโควต้า -->
        <div class="col-md-3">
            <div class="card stat-card bg-gradient-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-white-50 mb-2">ผู้สมัครรอบโควต้า</h6>
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
                            โควต้า: <?php echo number_format($quota_stats['approved']); ?> | ปกติ: <?php echo number_format($regular_stats['approved']); ?>
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
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-bar-chart text-success"></i> สาขาที่มีผู้สมัครมากที่สุด (Top 5)</h5>
                </div>
                <div class="card-body">
                    <canvas id="departmentChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-clock-history text-primary"></i> ผู้สมัครล่าสุด (โควต้า)</h6>
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
                                ตรวจสอบใบสมัคร (โควต้า)
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
    });
</script>