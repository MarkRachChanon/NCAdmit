<?php
/**
 * หน้าแก้ไขสาขาวิชา
 * NC-Admission - Nakhon Pathom College Admission System
 */

// ตรวจสอบสิทธิ์การเข้าถึง
if (!check_page_permission('departments_edit', $admin_role)) {
    header('Location: index.php?page=dashboard');
    exit();
}

// ดึง ID จาก URL
$dept_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($dept_id <= 0) {
    $_SESSION['error_message'] = 'ไม่พบข้อมูลสาขาวิชา';
    header('Location: index.php?page=departments_manage');
    exit();
}

// ดึงข้อมูลสาขาวิชา
$dept_sql = "SELECT * FROM departments WHERE id = ?";
$dept_stmt = $conn->prepare($dept_sql);
$dept_stmt->bind_param("i", $dept_id);
$dept_stmt->execute();
$dept_result = $dept_stmt->get_result();

if ($dept_result->num_rows == 0) {
    $_SESSION['error_message'] = 'ไม่พบข้อมูลสาขาวิชา';
    header('Location: index.php?page=departments_manage');
    exit();
}

$dept = $dept_result->fetch_assoc();

// ดึงข้อมูล Categories
$categories_sql = "SELECT * FROM department_categories WHERE is_active = 1 ORDER BY sort_order ASC";
$categories_result = $conn->query($categories_sql);

// นับจำนวนผู้สมัคร
$applicants_sql = "SELECT 
                    (SELECT COUNT(*) FROM students_quota WHERE department_id = ?) as quota_count,
                    (SELECT COUNT(*) FROM students_regular WHERE department_id = ?) as regular_count";
$applicants_stmt = $conn->prepare($applicants_sql);
$applicants_stmt->bind_param("ii", $dept_id, $dept_id);
$applicants_stmt->execute();
$applicants_result = $applicants_stmt->get_result();
$applicants = $applicants_result->fetch_assoc();
$total_applicants = $applicants['quota_count'] + $applicants['regular_count'];
?>

<!-- Page Header -->
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-2">
                        <i class="bi bi-pencil-square text-warning me-2"></i>
                        แก้ไขสาขาวิชา
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="index.php?page=dashboard">หน้าแรก</a></li>
                            <li class="breadcrumb-item"><a href="index.php?page=departments_manage">จัดการสาขาวิชา</a></li>
                            <li class="breadcrumb-item active">แก้ไขสาขาวิชา</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="index.php?page=departments_manage" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>
                        กลับ
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- แจ้งเตือนมีผู้สมัคร -->
    <?php if ($total_applicants > 0): ?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <strong>คำเตือน:</strong> สาขาวิชานี้มีผู้สมัครแล้ว <strong><?php echo number_format($total_applicants); ?></strong> คน 
        (โควต้า: <?php echo $applicants['quota_count']; ?> / ปกติ: <?php echo $applicants['regular_count']; ?>)
        <br>
        <small>การแก้ไขข้อมูลอาจส่งผลกระทบต่อผู้สมัคร โปรดดำเนินการด้วยความระมัดระวัง</small>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- ฟอร์มแก้ไขสาขาวิชา -->
    <div class="row">
        <div class="col-lg-12 mx-auto">
            <form id="editDepartmentForm" method="POST" class="needs-validation" novalidate>
                <input type="hidden" name="dept_id" value="<?php echo $dept_id; ?>">
                
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            ข้อมูลพื้นฐาน
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- รหัสสาขา -->
                            <div class="col-md-6 mb-3">
                                <label for="code" class="form-label">
                                    รหัสสาขาวิชา <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control text-uppercase" 
                                       id="code" 
                                       name="code" 
                                       value="<?php echo htmlspecialchars($dept['code']); ?>"
                                       required
                                       pattern="[A-Z0-9\-]+"
                                       title="ใช้ตัวอักษรพิมพ์ใหญ่ ตัวเลข และเครื่องหมาย - เท่านั้น">
                                <small class="text-muted">ตัวอย่าง: PVC-IT, PVS-ACC, DEG-MKT</small>
                            </div>

                            <!-- ประเภทวิชา -->
                            <div class="col-md-6 mb-3">
                                <label for="category_id" class="form-label">
                                    ประเภทวิชา <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="category_id" name="category_id" required>
                                    <option value="">-- เลือกประเภทวิชา --</option>
                                    <?php
                                    if ($categories_result && $categories_result->num_rows > 0):
                                        $categories_result->data_seek(0);
                                        while ($category = $categories_result->fetch_assoc()):
                                            $selected = ($category['id'] == $dept['category_id']) ? 'selected' : '';
                                    ?>
                                    <option value="<?php echo $category['id']; ?>" <?php echo $selected; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                    <?php
                                        endwhile;
                                    endif;
                                    ?>
                                </select>
                            </div>

                            <!-- ชื่อสาขา (ไทย) -->
                            <div class="col-md-6 mb-3">
                                <label for="name_th" class="form-label">
                                    ชื่อสาขาวิชา (ภาษาไทย) <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="name_th" 
                                       name="name_th" 
                                       value="<?php echo htmlspecialchars($dept['name_th']); ?>"
                                       required>
                            </div>

                            <!-- ชื่อสาขา (อังกฤษ) -->
                            <div class="col-md-6 mb-3">
                                <label for="name_en" class="form-label">
                                    ชื่อสาขาวิชา (ภาษาอังกฤษ) <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="name_en" 
                                       name="name_en" 
                                       value="<?php echo htmlspecialchars($dept['name_en']); ?>"
                                       required>
                            </div>

                            <!-- ระดับการศึกษา -->
                            <div class="col-md-6 mb-3">
                                <label for="level" class="form-label">
                                    ระดับการศึกษา <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="level" name="level" required>
                                    <option value="">-- เลือกระดับ --</option>
                                    <option value="ปวช." <?php echo ($dept['level'] == 'ปวช.') ? 'selected' : ''; ?>>ปวช. (ประกาศนียบัตรวิชาชีพ)</option>
                                    <option value="ปวส." <?php echo ($dept['level'] == 'ปวส.') ? 'selected' : ''; ?>>ปวส. (ประกาศนียบัตรวิชาชีพชั้นสูง)</option>
                                    <option value="ปริญญาตรี" <?php echo ($dept['level'] == 'ปริญญาตรี') ? 'selected' : ''; ?>>ปริญญาตรี (เทคโนโลยีบัณฑิต)</option>
                                </select>
                            </div>

                            <!-- ประเภทการเรียน -->
                            <div class="col-md-6 mb-3">
                                <label for="study_type" class="form-label">
                                    ประเภทการเรียน <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="study_type" name="study_type" required>
                                    <option value="">-- เลือกประเภทการเรียน --</option>
                                    <option value="ปกติ" <?php echo ($dept['study_type'] == 'ปกติ') ? 'selected' : ''; ?>>ปกติ</option>
                                    <option value="ทวิภาคี" <?php echo ($dept['study_type'] == 'ทวิภาคี') ? 'selected' : ''; ?>>ทวิภาคี</option>
                                    <option value="ปกติ+ทวิภาคี" <?php echo ($dept['study_type'] == 'ปกติ+ทวิภาคี') ? 'selected' : ''; ?>>ปกติ+ทวิภาคี</option>
                                </select>
                            </div>

                            <!-- คำอธิบาย -->
                            <div class="col-12 mb-3">
                                <label for="description" class="form-label">
                                    คำอธิบายสาขาวิชา
                                </label>
                                <textarea class="form-control" 
                                          id="description" 
                                          name="description" 
                                          rows="3"><?php echo htmlspecialchars($dept['description']); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- การรับสมัคร -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-people me-2"></i>
                            ข้อมูลการรับสมัคร
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- รอบโควตา -->
                            <div class="col-md-6">
                                <div class="card border-primary mb-3">
                                    <div class="card-header bg-primary bg-opacity-10">
                                        <h6 class="mb-0">
                                            <i class="bi bi-star me-2"></i>
                                            รอบโควตา (Quota)
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   id="open_quota" 
                                                   name="open_quota" 
                                                   value="1"
                                                   <?php echo ($dept['open_quota']) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="open_quota">
                                                <strong>เปิดรับสมัครรอบโควตา</strong>
                                            </label>
                                        </div>
                                        <div class="mb-3">
                                            <label for="seats_quota" class="form-label">
                                                จำนวนที่รับ <span class="text-danger">*</span>
                                            </label>
                                            <input type="number" 
                                                   class="form-control" 
                                                   id="seats_quota" 
                                                   name="seats_quota" 
                                                   min="0" 
                                                   max="999"
                                                   value="<?php echo $dept['seats_quota']; ?>" 
                                                   required>
                                        </div>
                                        <?php if ($applicants['quota_count'] > 0): ?>
                                        <div class="alert alert-info mb-0">
                                            <small>
                                                <i class="bi bi-info-circle me-1"></i>
                                                มีผู้สมัครแล้ว: <strong><?php echo $applicants['quota_count']; ?></strong> คน
                                            </small>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- รอบปกติ -->
                            <div class="col-md-6">
                                <div class="card border-success mb-3">
                                    <div class="card-header bg-success bg-opacity-10">
                                        <h6 class="mb-0">
                                            <i class="bi bi-people me-2"></i>
                                            รอบปกติ (Regular)
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   id="open_regular" 
                                                   name="open_regular" 
                                                   value="1"
                                                   <?php echo ($dept['open_regular']) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="open_regular">
                                                <strong>เปิดรับสมัครรอบปกติ</strong>
                                            </label>
                                        </div>
                                        <div class="mb-3">
                                            <label for="seats_regular" class="form-label">
                                                จำนวนที่รับ <span class="text-danger">*</span>
                                            </label>
                                            <input type="number" 
                                                   class="form-control" 
                                                   id="seats_regular" 
                                                   name="seats_regular" 
                                                   min="0" 
                                                   max="999"
                                                   value="<?php echo $dept['seats_regular']; ?>" 
                                                   required>
                                        </div>
                                        <?php if ($applicants['regular_count'] > 0): ?>
                                        <div class="alert alert-info mb-0">
                                            <small>
                                                <i class="bi bi-info-circle me-1"></i>
                                                มีผู้สมัครแล้ว: <strong><?php echo $applicants['regular_count']; ?></strong> คน
                                            </small>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- การตั้งค่าเพิ่มเติม -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-gear me-2"></i>
                            การตั้งค่าเพิ่มเติม
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- สถานะการใช้งาน -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">สถานะการใช้งาน</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="is_active" 
                                           name="is_active" 
                                           value="1"
                                           <?php echo ($dept['is_active']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_active">
                                        เปิดใช้งาน
                                    </label>
                                </div>
                            </div>

                            <!-- สาขาใหม่ -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">สาขาใหม่</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="is_new" 
                                           name="is_new" 
                                           value="1"
                                           <?php echo ($dept['is_new']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_new">
                                        ทำเครื่องหมายเป็นสาขาใหม่
                                    </label>
                                </div>
                            </div>

                            <!-- Highlight -->
                            <div class="col-md-4 mb-3">
                                <label for="highlight" class="form-label">
                                    ไฮไลท์
                                </label>
                                <select class="form-select" id="highlight" name="highlight">
                                    <option value="" <?php echo empty($dept['highlight']) ? 'selected' : ''; ?>>-- ไม่มี --</option>
                                    <option value="NEW" <?php echo ($dept['highlight'] == 'NEW') ? 'selected' : ''; ?>>NEW (สาขาใหม่)</option>
                                    <option value="HOT" <?php echo ($dept['highlight'] == 'HOT') ? 'selected' : ''; ?>>HOT (ยอดนิยม)</option>
                                    <option value="POPULAR" <?php echo ($dept['highlight'] == 'POPULAR') ? 'selected' : ''; ?>>POPULAR (แนะนำ)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ข้อมูลเพิ่มเติม -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-clock-history me-2"></i>
                            ข้อมูลเพิ่มเติม
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <p class="mb-2"><strong>วันที่สร้าง:</strong></p>
                                <p class="text-muted"><?php echo thai_date($dept['created_at']); ?></p>
                            </div>
                            <div class="col-md-4">
                                <p class="mb-2"><strong>แก้ไขล่าสุด:</strong></p>
                                <p class="text-muted"><?php echo thai_date($dept['updated_at']); ?></p>
                            </div>
                            <div class="col-md-4">
                                <p class="mb-2"><strong>จำนวนผู้สมัครทั้งหมด:</strong></p>
                                <p class="text-primary fs-5 mb-0">
                                    <strong><?php echo number_format($total_applicants); ?></strong> คน
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ปุ่ม Submit -->
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <a href="index.php?page=departments_manage" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-2"></i>
                                ยกเลิก
                            </a>
                            <button type="submit" class="btn btn-warning" id="btnSubmit">
                                <i class="bi bi-save me-2"></i>
                                บันทึกการแก้ไข
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Department Edit Form Loaded');
    
    const form = document.getElementById('editDepartmentForm');
    const codeInput = document.getElementById('code');
    const openQuota = document.getElementById('open_quota');
    const seatsQuota = document.getElementById('seats_quota');
    const openRegular = document.getElementById('open_regular');
    const seatsRegular = document.getElementById('seats_regular');
    
    // ==================== Auto Uppercase for Code ====================
    if (codeInput) {
        codeInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    }
    
    // ==================== Toggle Seats Quota ====================
    if (openQuota && seatsQuota) {
        openQuota.addEventListener('change', function() {
            if (this.checked) {
                seatsQuota.disabled = false;
                seatsQuota.required = true;
            } else {
                seatsQuota.disabled = true;
                seatsQuota.required = false;
                seatsQuota.value = 0;
            }
        });
    }
    
    // ==================== Toggle Seats Regular ====================
    if (openRegular && seatsRegular) {
        openRegular.addEventListener('change', function() {
            if (this.checked) {
                seatsRegular.disabled = false;
                seatsRegular.required = true;
            } else {
                seatsRegular.disabled = true;
                seatsRegular.required = false;
                seatsRegular.value = 0;
            }
        });
    }
    
    // ==================== Form Submit ====================
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Form submitted');
            
            // Validate Form
            if (!form.checkValidity()) {
                e.stopPropagation();
                form.classList.add('was-validated');
                
                Swal.fire({
                    icon: 'error',
                    title: 'กรุณากรอกข้อมูลให้ครบถ้วน',
                    text: 'โปรดตรวจสอบข้อมูลที่กรอกให้ถูกต้อง'
                });
                return;
            }
            
            // ตรวจสอบว่าเปิดรับสมัครอย่างน้อย 1 รอบ
            if (!openQuota.checked && !openRegular.checked) {
                Swal.fire({
                    icon: 'warning',
                    title: 'กรุณาเลือกรอบรับสมัคร',
                    text: 'กรุณาเปิดรับสมัครอย่างน้อย 1 รอบ (โควตาหรือปกติ)'
                });
                return;
            }
            
            // ยืนยันการแก้ไข
            Swal.fire({
                title: 'ยืนยันการแก้ไข?',
                text: 'คุณต้องการบันทึกการแก้ไขหรือไม่?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'บันทึก',
                cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#ffc107',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (result.isConfirmed) {
                    // แสดง Loading
                    Swal.fire({
                        title: 'กำลังบันทึกข้อมูล...',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // เตรียมข้อมูล FormData
                    const formData = new FormData(form);
                    
                    // Debug: แสดงข้อมูลที่จะส่ง
                    console.log('Form Data:');
                    for (let [key, value] of formData.entries()) {
                        console.log(key + ': ' + value);
                    }
                    
                    // ส่งข้อมูลด้วย Fetch API
                    fetch('api/department_edit.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        console.log('Response status:', response.status);
                        console.log('Response headers:', response.headers);
                        
                        // ตรวจสอบ Content-Type
                        const contentType = response.headers.get('content-type');
                        if (!contentType || !contentType.includes('application/json')) {
                            return response.text().then(text => {
                                console.error('Response is not JSON:', text);
                                throw new Error('Server did not return JSON response');
                            });
                        }
                        
                        return response.json();
                    })
                    .then(data => {
                        console.log('Response data:', data);
                        
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'สำเร็จ!',
                                text: data.message,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                // Redirect ไปหน้า manage
                                window.location.href = 'index.php?page=departments_manage';
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'เกิดข้อผิดพลาด',
                                html: data.message || 'ไม่สามารถบันทึกข้อมูลได้'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Fetch Error:', error);
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'เกิดข้อผิดพลาด',
                            html: 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้<br><small>' + error.message + '</small>'
                        });
                    });
                }
            });
        });
    }
});
</script>