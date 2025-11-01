<?php

/**
 * Export PDF - ส่งออกรายชื่อผู้สมัครเป็น PDF
 * NC-Admission - Nakhon Pathom College Admission System
 */

// ตรวจสอบสิทธิ์
if (!check_page_permission('export_pdf', $admin_role)) {
    header('Location: index.php?page=dashboard');
    exit();
}

// ดึงข้อมูลปีการศึกษาปัจจุบัน
$current_year = date('Y') + 543 + 1;

// ดึงข้อมูลสาขาวิชาทั้งหมด
$departments_sql = "SELECT DISTINCT d.id, d.code, d.name_th, d.level 
                    FROM departments d 
                    ORDER BY d.level, d.name_th";
$departments = $conn->query($departments_sql);

// ดึงปีการศึกษาทั้งหมด
$years_sql = "SELECT DISTINCT academic_year 
              FROM (
                  SELECT academic_year FROM students_quota 
                  UNION 
                  SELECT academic_year FROM students_regular
              ) AS years 
              ORDER BY academic_year DESC";
$years_result = $conn->query($years_sql);
$years = [];
while ($row = $years_result->fetch_assoc()) {
    $years[] = $row['academic_year'];
}

// ดึงข้อมูลสถิติ
$stats_sql = "SELECT 
    (SELECT COUNT(*) FROM students_quota WHERE academic_year = '$current_year') as quota_total,
    (SELECT COUNT(*) FROM students_quota WHERE academic_year = '$current_year' AND status = 'pending') as quota_pending,
    (SELECT COUNT(*) FROM students_quota WHERE academic_year = '$current_year' AND status = 'approved') as quota_approved,
    (SELECT COUNT(*) FROM students_quota WHERE academic_year = '$current_year' AND status = 'rejected') as quota_rejected,
    (SELECT COUNT(*) FROM students_regular WHERE academic_year = '$current_year') as regular_total,
    (SELECT COUNT(*) FROM students_regular WHERE academic_year = '$current_year' AND status = 'pending') as regular_pending,
    (SELECT COUNT(*) FROM students_regular WHERE academic_year = '$current_year' AND status = 'approved') as regular_approved,
    (SELECT COUNT(*) FROM students_regular WHERE academic_year = '$current_year' AND status = 'rejected') as regular_rejected";
$stats_result = $conn->query($stats_sql);
$stats = $stats_result->fetch_assoc();

// คำนวณรวม
$total_all = $stats['quota_total'] + $stats['regular_total'];
$total_pending = $stats['quota_pending'] + $stats['regular_pending'];
$total_approved = $stats['quota_approved'] + $stats['regular_approved'];
$total_rejected = $stats['quota_rejected'] + $stats['regular_rejected'];
?>

<!-- Page Header -->
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">
                <i class="bi bi-file-earmark-pdf text-danger"></i>
                Export PDF
            </h2>
            <p class="text-muted mb-0">
                ส่งออกรายชื่อผู้สมัครเป็น PDF ปีการศึกษา <?php echo $current_year; ?>
            </p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">ทั้งหมด</h6>
                            <h3 class="mb-0 fw-bold"><?php echo number_format($total_all); ?></h3>
                            <small class="text-muted">
                                โควตา <?php echo number_format($stats['quota_total']); ?> |
                                ปกติ <?php echo number_format($stats['regular_total']); ?>
                            </small>
                        </div>
                        <div class="bg-primary bg-opacity-10 rounded-circle">
                            <i class="bi bi-people fs-4 text-primary icon-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">รอตรวจสอบ</h6>
                            <h3 class="mb-0 fw-bold text-warning"><?php echo number_format($total_pending); ?></h3>
                            <small class="text-muted">
                                โควตา <?php echo number_format($stats['quota_pending']); ?> |
                                ปกติ <?php echo number_format($stats['regular_pending']); ?>
                            </small>
                        </div>
                        <div class="bg-warning bg-opacity-10 rounded-circle">
                            <i class="bi bi-hourglass-split fs-4 text-warning icon-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">อนุมัติแล้ว</h6>
                            <h3 class="mb-0 fw-bold text-success"><?php echo number_format($total_approved); ?></h3>
                            <small class="text-muted">
                                โควตา <?php echo number_format($stats['quota_approved']); ?> |
                                ปกติ <?php echo number_format($stats['regular_approved']); ?>
                            </small>
                        </div>
                        <div class="bg-success bg-opacity-10 rounded-circle">
                            <i class="bi bi-check-circle fs-4 text-success icon-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">ไม่อนุมัติ</h6>
                            <h3 class="mb-0 fw-bold text-danger"><?php echo number_format($total_rejected); ?></h3>
                            <small class="text-muted">
                                โควตา <?php echo number_format($stats['quota_rejected']); ?> |
                                ปกติ <?php echo number_format($stats['regular_rejected']); ?>
                            </small>
                        </div>
                        <div class="bg-danger bg-opacity-10 rounded-circle">
                            <i class="bi bi-x-circle fs-4 text-danger icon-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Options -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-funnel me-2"></i>
                        เลือกข้อมูลที่ต้องการ Export
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form id="exportPdfForm" method="post" action="api/export_pdf_action.php" target="_blank">
                        <div class="row g-4">
                            <!-- ประเภทการสมัคร -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-diagram-3 text-primary me-2"></i>
                                    ประเภทการสมัคร <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" name="type" id="type" required>
                                    <option value="">-- เลือกประเภท --</option>
                                    <?php if (in_array($admin_role, ['superadmin', 'admin', 'quota'])): ?>
                                        <option value="quota">รอบโควต้า</option>
                                    <?php endif; ?>

                                    <?php if (in_array($admin_role, ['superadmin', 'admin', 'regular'])): ?>
                                        <option value="regular">รอบปกติ</option>
                                    <?php endif; ?>

                                    <?php if (in_array($admin_role, ['superadmin', 'admin'])): ?>
                                        <option value="all">ทั้งหมด (โควต้า + ปกติ)</option>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <!-- ปีการศึกษา -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-calendar-event text-info me-2"></i>
                                    ปีการศึกษา <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" name="academic_year" required>
                                    <option value="">-- เลือกปีการศึกษา --</option>
                                    <?php foreach ($years as $year): ?>
                                        <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                                    <?php endforeach; ?>
                                    <option value="all">ทุกปีการศึกษา</option>
                                </select>
                            </div>

                            <!-- ระดับชั้น -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-mortarboard text-success me-2"></i>
                                    ระดับชั้น
                                </label>
                                <select class="form-select" name="level" id="level">
                                    <option value="">ทุกระดับ</option>
                                    <option value="ปวช.">ปวช. (ประกาศนียบัตรวิชาชีพ)</option>
                                    <option value="ปวส.">ปวส. (ประกาศนียบัตรวิชาชีพชั้นสูง)</option>
                                    <option value="ปริญญาตรี">ปริญญาตรี (ทล.บ.)</option>
                                </select>
                            </div>

                            <!-- สาขาวิชา -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-book text-warning me-2"></i>
                                    สาขาวิชา
                                </label>
                                <select class="form-select" name="department_id" id="department">
                                    <option value="">ทุกสาขา</option>
                                    <?php
                                    $departments->data_seek(0); // Reset pointer
                                    $current_level = '';
                                    while ($dept = $departments->fetch_assoc()):
                                        if ($current_level != $dept['level']) {
                                            if ($current_level != '') echo '</optgroup>';
                                            echo '<optgroup label="' . $dept['level'] . '">';
                                            $current_level = $dept['level'];
                                        }
                                    ?>
                                        <option value="<?php echo $dept['id']; ?>" data-level="<?php echo $dept['level']; ?>">
                                            <?php echo $dept['code'] . ' - ' . $dept['name_th']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                    <?php if ($current_level != '') echo '</optgroup>'; ?>
                                </select>
                                <small class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    ถ้าเลือกระดับชั้น จะแสดงเฉพาะสาขาในระดับนั้น
                                </small>
                            </div>

                            <!-- สถานะ -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-check-circle text-danger me-2"></i>
                                    สถานะการสมัคร
                                </label>
                                <select class="form-select" name="status">
                                    <option value="">ทุกสถานะ</option>
                                    <option value="pending">รอตรวจสอบ</option>
                                    <option value="approved">อนุมัติ</option>
                                    <option value="rejected">ไม่อนุมัติ</option>
                                    <option value="cancelled">ยกเลิก</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold mb-3">
                                    <i class="bi bi-file-pdf text-danger me-2"></i>
                                    รูปแบบ PDF
                                </label>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="card border-2 pdf-format-option h-100" data-format="list">
                                            <div class="card-body text-center">
                                                <i class="bi bi-list-ul display-4 text-primary mb-3"></i>
                                                <h6 class="mb-2">รายชื่อแบบตาราง</h6>
                                                <small class="text-muted">เหมาะสำหรับดูภาพรวม</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card border-2 pdf-format-option h-100" data-format="summary">
                                            <div class="card-body text-center">
                                                <i class="bi bi-bar-chart display-4 text-success mb-3"></i>
                                                <h6 class="mb-2">สรุปจำนวนผู้สมัคร</h6>
                                                <small class="text-muted">แยกตามสาขาวิชา</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card border-2 pdf-format-option h-100 opacity-50" data-format="form" style="cursor: not-allowed;">
                                            <div class="card-body text-center">
                                                <i class="bi bi-file-earmark-person display-4 text-secondary mb-3"></i>
                                                <h6 class="mb-2">ใบสมัครรายบุคคล</h6>
                                                <small class="text-muted">
                                                    <span class="badge bg-secondary">ยังไม่พร้อมใช้งาน</span>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="format" id="format" value="list" required>
                            </div>

                            <!-- ปุ่ม Export -->
                            <div class="col-12">
                                <hr class="my-4">
                                <div class="d-flex gap-3">
                                    <button type="submit" class="btn btn-danger px-4">
                                        <i class="bi bi-download me-2"></i>
                                        Export PDF
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary px-4" id="btnPreview">
                                        <i class="bi bi-eye me-2"></i>
                                        ดูตัวอย่าง
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 
==============================================================================
ไฟล์: export_pdf.php (Frontend)
คำแนะนำ: วางแทนที่ส่วน <style> และ <script> เดิมในไฟล์ export_pdf.php
==============================================================================
-->

<!-- เพิ่ม CSS สำหรับ info text -->
<style>
    .pdf-format-option {
        cursor: pointer;
        transition: all 0.3s ease;
        border-color: #dee2e6 !important;
    }

    .pdf-format-option:hover:not(.opacity-50) {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        border-color: var(--bs-primary) !important;
    }

    .pdf-format-option.active {
        border-color: var(--bs-primary) !important;
        background-color: rgba(13, 110, 253, 0.05);
        box-shadow: 0 0.5rem 1rem rgba(13, 110, 253, 0.2);
    }

    .pdf-format-option.active i {
        color: var(--bs-primary) !important;
    }

    .icon-circle {
        padding: 12px;
    }

    #department-level-info {
        display: block;
        margin-top: 0.5rem;
        font-size: 0.875rem;
        transition: all 0.3s ease;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Export PDF Page Loaded');

        // ==================== Format Selection ====================
        document.querySelectorAll('.pdf-format-option').forEach(card => {
            card.addEventListener('click', function() {
                if (this.classList.contains('opacity-50')) {
                    return;
                }

                document.querySelectorAll('.pdf-format-option').forEach(c => c.classList.remove('active'));
                this.classList.add('active');

                const format = this.dataset.format;
                document.getElementById('format').value = format;

                console.log('Selected format:', format);
            });
        });

        document.querySelector('.pdf-format-option[data-format="list"]').classList.add('active');

        // ==================== Level & Department Filter - แก้ไขใหม่ ====================
        const levelSelect = document.getElementById('level');
        const departmentSelect = document.getElementById('department');
        const allDepartmentOptions = Array.from(departmentSelect.querySelectorAll('option[data-level]'));

        /**
         * ฟังก์ชันกรองสาขาวิชาตามระดับชั้นที่เลือก
         * - ถ้าเลือก "ทุกระดับ" จะแสดงทุกสาขา
         * - ถ้าเลือกระดับชั้นเฉพาะ จะแสดงเฉพาะสาขาในระดับนั้น
         */
        function filterDepartmentsByLevel() {
            const selectedLevel = levelSelect.value;

            console.log('กรองสาขาตามระดับ:', selectedLevel || 'ทุกระดับ');

            if (selectedLevel === '') {
                // กรณีเลือก "ทุกระดับ" - แสดงทุกสาขา
                allDepartmentOptions.forEach(opt => {
                    opt.style.display = '';
                    opt.disabled = false;
                });
            } else {
                // กรณีเลือกระดับเฉพาะ - แสดงเฉพาะสาขาในระดับนั้น
                allDepartmentOptions.forEach(opt => {
                    if (opt.dataset.level === selectedLevel) {
                        opt.style.display = '';
                        opt.disabled = false;
                    } else {
                        opt.style.display = 'none';
                        opt.disabled = true;
                    }
                });

                // ตรวจสอบว่าสาขาที่เลือกอยู่ยังแสดงอยู่หรือไม่
                const currentDeptOption = departmentSelect.querySelector(`option[value="${departmentSelect.value}"]`);
                if (currentDeptOption && currentDeptOption.style.display === 'none') {
                    // ถ้าสาขาที่เลือกอยู่ถูกซ่อน ให้รีเซ็ตเป็น "ทุกสาขา"
                    departmentSelect.value = '';
                    console.log('รีเซ็ตการเลือกสาขาเพราะไม่ตรงกับระดับที่เลือก');
                }
            }

            updateDepartmentInfo();
        }

        /**
         * ฟังก์ชันอัปเดตข้อมูลระดับชั้นตามสาขาที่เลือก
         * - ถ้าเลือก "ทุกสาขา" ไม่มีผลกับระดับชั้น
         * - ถ้าเลือกสาขาเฉพาะ จะแสดงข้อมูลระดับของสาขานั้น
         */
        function updateDepartmentInfo() {
            const selectedDeptOption = departmentSelect.querySelector(`option[value="${departmentSelect.value}"]`);
            const infoElement = document.getElementById('department-level-info');

            if (selectedDeptOption && selectedDeptOption.value !== '') {
                const deptLevel = selectedDeptOption.dataset.level;
                console.log('เลือกสาขา:', selectedDeptOption.text, '| ระดับ:', deptLevel);

                if (infoElement) {
                    infoElement.innerHTML = `<i class="bi bi-info-circle me-1"></i> สาขานี้อยู่ในระดับ: <strong>${deptLevel}</strong>`;
                    infoElement.className = 'text-primary mt-2';
                }
            } else {
                if (infoElement) {
                    infoElement.innerHTML = `<i class="bi bi-info-circle me-1"></i> ถ้าเลือกระดับชั้น จะแสดงเฉพาะสาขาในระดับนั้น`;
                    infoElement.className = 'text-muted';
                }
            }
        }

        // Event: เมื่อเปลี่ยนระดับชั้น
        levelSelect.addEventListener('change', function() {
            console.log('=== เปลี่ยนระดับชั้น ===');
            filterDepartmentsByLevel();
        });

        // Event: เมื่อเปลี่ยนสาขาวิชา
        departmentSelect.addEventListener('change', function() {
            console.log('=== เปลี่ยนสาขาวิชา ===');
            updateDepartmentInfo();
        });

        // เพิ่ม info element ใต้ dropdown สาขาวิชา
        const deptFormGroup = departmentSelect.closest('.col-md-6');
        const existingSmall = deptFormGroup.querySelector('small');
        if (existingSmall) {
            existingSmall.id = 'department-level-info';
        }

        // ==================== Preview Button ====================
        document.getElementById('btnPreview').addEventListener('click', function() {
            const form = document.getElementById('exportPdfForm');
            const formData = new FormData(form);

            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            formData.append('preview', '1');

            const queryString = new URLSearchParams(formData).toString();
            window.open('api/export_pdf_action.php?' + queryString, '_blank');
        });

        // ==================== Form Submit ====================
        document.getElementById('exportPdfForm').addEventListener('submit', function(e) {
            console.log('Exporting PDF...');
            console.log('ระดับชั้น:', levelSelect.value || 'ทุกระดับ');
            console.log('สาขาวิชา:', departmentSelect.value || 'ทุกสาขา');

            Swal.fire({
                title: 'กำลังสร้าง PDF...',
                html: 'กรุณารอสักครู่',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            setTimeout(() => {
                Swal.close();
            }, 2000);
        });

        // ==================== Export Summary Button ====================
        document.getElementById('btnExportSummary').addEventListener('click', function() {
            const form = document.getElementById('exportPdfForm');
            const type = document.getElementById('type').value;
            const academic_year = form.querySelector('[name="academic_year"]').value;

            // Validate
            if (!type || !academic_year) {
                Swal.fire({
                    icon: 'warning',
                    title: 'กรุณาเลือกข้อมูล',
                    text: 'กรุณาเลือกประเภทการสมัครและปีการศึกษา',
                    confirmButtonText: 'ตรวจสอบ'
                });
                return;
            }

            Swal.fire({
                title: 'กำลังสร้างรายงานสรุป...',
                html: 'กรุณารอสักครู่',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // สร้าง URL สำหรับ Export Summary
            const params = new URLSearchParams({
                type: type,
                academic_year: academic_year,
                format: 'summary'
            });

            // Open in new window
            window.open('api/export_summary_pdf.php?' + params.toString(), '_blank');

            setTimeout(() => {
                Swal.close();
            }, 1500);
        });
    });
</script>