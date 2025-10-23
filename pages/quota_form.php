<?php

/**
 * Quota Form - ใบสมัครรอบโควต้า
 * Multi-Step Form with Session Storage
 */

// ตรวจสอบว่าเปิดรับสมัครรอบโควต้าหรือไม่
if (!is_admission_open('quota')) {
    echo '<div class="container py-5">
            <div class="alert alert-warning text-center shadow">
                <i class="bi bi-exclamation-triangle-fill fs-1 mb-3 d-block"></i>
                <h4 class="mb-3">ปิดรับสมัครรอบโควต้า</h4>
                <p class="mb-0">ขณะนี้ยังไม่เปิดรับสมัครรอบโควต้า กรุณาติดตามข่าวสารเพิ่มเติม</p>
            </div>
          </div>';
    return;
}

// ดึงข้อมูลประเภทวิชาและสาขาวิชา
$categories_query = "SELECT * FROM department_categories WHERE is_active = 1 ORDER BY sort_order";
$categories = $conn->query($categories_query);

$departments_query = "SELECT d.*, dc.name as category_name, dc.id as cat_id
                      FROM departments d 
                      JOIN department_categories dc ON d.category_id = dc.id 
                      WHERE d.is_active = 1 AND d.open_quota = 1
                      ORDER BY dc.sort_order, d.level, d.name_th";
$departments_result = $conn->query($departments_query);

$dept_by_category = [];
while ($dept = $departments_result->fetch_assoc()) {
    $dept_by_category[$dept['cat_id']][] = $dept;
}
?>

<!-- Custom Styles -->
<style>
    .form-step {
        display: none;
    }

    .form-step.active {
        display: block;
        animation: fadeIn 0.5s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .step-indicator {
        width: 50px;
        height: 50px;
        transition: all 0.3s ease;
    }

    .step-indicator.active {
        background: var(--blue-gradient) !important;
        transform: scale(1.1);
        box-shadow: 0 4px 15px rgba(79, 172, 254, 0.4);
    }

    .step-indicator.completed {
        background: linear-gradient(135deg, #48bb78 0%, #38a169 100%) !important;
    }
</style>

<!-- Header -->
<section class="page-header bg-gradient-blue text-white py-5 mb-4">
    <div class="container">
        <div class="row align-items-center" data-aos="fade-down">
            <div class="col-md-8">
                <h2 class="mb-1">
                    <i class="bi bi-file-earmark-text me-2"></i>
                    ใบสมัครเข้าศึกษาต่อ รอบโควต้า
                </h2>
                <p class="mb-0 opacity-75">ปีการศึกษา <?php echo (date('Y') + 543 + 1); ?></p>
            </div>
            <div class="col-md-4 text-md-end">
                <div class="bg-primary bg-opacity-50 rounded px-3 py-2 d-inline-block">
                    <i class="bi bi-calendar-event me-2"></i>
                    <span><?php echo thai_date(date('Y-m-d')); ?></span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Progress Steps -->
<div class="container mb-4">
    <div class="card shadow-sm">
        <div class="card-body p-3">
            <div class="row g-2 align-items-center text-center small">
                <div class="col step-item" data-step="1">
                    <div class="step-indicator active d-inline-flex align-items-center justify-content-center rounded-circle text-white fw-bold mb-2">
                        <i class="bi bi-person-fill fs-5"></i>
                    </div>
                    <p class="mb-0 fw-medium">ข้อมูลส่วนตัว</p>
                </div>
                <div class="col-auto"><i class="bi bi-chevron-right text-muted"></i></div>

                <div class="col step-item" data-step="2">
                    <div class="step-indicator d-inline-flex align-items-center justify-content-center rounded-circle bg-secondary text-white fw-bold mb-2">
                        <i class="bi bi-house-fill fs-5"></i>
                    </div>
                    <p class="mb-0 fw-medium">ที่อยู่</p>
                </div>
                <div class="col-auto"><i class="bi bi-chevron-right text-muted"></i></div>

                <div class="col step-item" data-step="3">
                    <div class="step-indicator d-inline-flex align-items-center justify-content-center rounded-circle bg-secondary text-white fw-bold mb-2">
                        <i class="bi bi-book-fill fs-5"></i>
                    </div>
                    <p class="mb-0 fw-medium">การศึกษา</p>
                </div>
                <div class="col-auto"><i class="bi bi-chevron-right text-muted"></i></div>

                <div class="col step-item" data-step="4">
                    <div class="step-indicator d-inline-flex align-items-center justify-content-center rounded-circle bg-secondary text-white fw-bold mb-2">
                        <i class="bi bi-list-ul fs-5"></i>
                    </div>
                    <p class="mb-0 fw-medium">เลือกสาขา</p>
                </div>
                <div class="col-auto"><i class="bi bi-chevron-right text-muted"></i></div>

                <div class="col step-item" data-step="5">
                    <div class="step-indicator d-inline-flex align-items-center justify-content-center rounded-circle bg-secondary text-white fw-bold mb-2">
                        <i class="bi bi-cloud-upload-fill fs-5"></i>
                    </div>
                    <p class="mb-0 fw-medium">อัปโหลด</p>
                </div>
                <div class="col-auto"><i class="bi bi-chevron-right text-muted"></i></div>

                <div class="col step-item" data-step="6">
                    <div class="step-indicator d-inline-flex align-items-center justify-content-center rounded-circle bg-secondary text-white fw-bold mb-2">
                        <i class="bi bi-check-circle-fill fs-5"></i>
                    </div>
                    <p class="mb-0 fw-medium">ยืนยัน</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Form Container -->
<div class="container mb-5">
    <form id="quotaForm" class="card shadow">
        <div class="card-body p-4">

            <!-- Step 1: ข้อมูลส่วนตัว -->
            <div class="form-step active" data-step="1">
                <h4 class="fw-bold mb-4 text-gradient">
                    <i class="bi bi-person-fill me-2"></i> ข้อมูลส่วนตัว
                </h4>

                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">คำนำหน้า <span class="text-danger">*</span></label>
                        <select name="title" class="form-select" required>
                            <option value="">เลือก</option>
                            <option value="เด็กชาย">เด็กชาย</option>
                            <option value="เด็กหญิง">เด็กหญิง</option>
                            <option value="นาย">นาย</option>
                            <option value="นางสาว">นางสาว</option>
                            <option value="นาง">นาง</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">ชื่อ <span class="text-danger">*</span></label>
                        <input type="text" name="first_name" class="form-control" placeholder="ชื่อ" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">นามสกุล <span class="text-danger">*</span></label>
                        <input type="text" name="last_name" class="form-control" placeholder="นามสกุล" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">เลขบัตรประชาชน <span class="text-danger">*</span></label>
                        <input type="text" name="id_card" id="id_card" class="form-control"
                            placeholder="X-XXXX-XXXXX-XX-X" maxlength="17" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">วันเกิด <span class="text-danger">*</span></label>
                        <input type="date" name="birth_date" class="form-control" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">เพศ <span class="text-danger">*</span></label>
                        <select name="gender" class="form-select" required>
                            <option value="">เลือกเพศ</option>
                            <option value="ชาย">ชาย</option>
                            <option value="หญิง">หญิง</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">เบอร์โทรศัพท์ <span class="text-danger">*</span></label>
                        <input type="tel" name="phone" id="phone" class="form-control"
                            placeholder="0XX-XXX-XXXX" maxlength="12" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">อีเมล <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control"
                            placeholder="example@email.com" required>
                    </div>
                </div>

                <div class="d-flex flex-column flex-md-row justify-content-between mt-4 gap-2">
                    <button type="button" class="btn btn-gradient btn-next order-md-last px-5">
                        ถัดไป <i class="bi bi-arrow-right ms-2"></i>
                    </button>
                </div>
            </div>

            <!-- Step 2: ที่อยู่ -->
            <div class="form-step" data-step="2">
                <h4 class="fw-bold mb-4 text-gradient">
                    <i class="bi bi-house-fill me-2"></i> ที่อยู่
                </h4>

                <!-- ที่อยู่ปัจจุบัน -->
                <div class="card bg-light mb-4">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">ที่อยู่ปัจจุบัน</h5>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">บ้านเลขที่ / หมู่บ้าน <span class="text-danger">*</span></label>
                                <input type="text" name="current_address" id="current_address"
                                    class="form-control" placeholder="123/45 หมู่บ้าน..." required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">จังหวัด <span class="text-danger">*</span></label>
                                <input type="text" name="current_province" id="current_province"
                                    class="form-control" placeholder="นครปฐม" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">อำเภอ/เขต <span class="text-danger">*</span></label>
                                <input type="text" name="current_district" id="current_district"
                                    class="form-control" placeholder="เมืองนครปฐม" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">ตำบล/แขวง <span class="text-danger">*</span></label>
                                <input type="text" name="current_subdistrict" id="current_subdistrict"
                                    class="form-control" placeholder="พระปฐมเจดีย์" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">รหัสไปรษณีย์ <span class="text-danger">*</span></label>
                                <input type="text" name="current_zipcode" id="current_zipcode"
                                    class="form-control" placeholder="73000" maxlength="5" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Checkbox ที่อยู่เหมือนกัน -->
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="same_address">
                    <label class="form-check-label" for="same_address">
                        ที่อยู่ตามทะเบียนบ้านเหมือนกับที่อยู่ปัจจุบัน
                    </label>
                </div>

                <!-- ที่อยู่ตามทะเบียนบ้าน -->
                <div class="card bg-light" id="register_address_section">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">ที่อยู่ตามทะเบียนบ้าน</h5>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">บ้านเลขที่ / หมู่บ้าน <span class="text-danger">*</span></label>
                                <input type="text" name="register_address" id="register_address"
                                    class="form-control" placeholder="123/45 หมู่บ้าน..." required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">จังหวัด <span class="text-danger">*</span></label>
                                <input type="text" name="register_province" id="register_province"
                                    class="form-control" placeholder="นครปฐม" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">อำเภอ/เขต <span class="text-danger">*</span></label>
                                <input type="text" name="register_district" id="register_district"
                                    class="form-control" placeholder="เมืองนครปฐม" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">ตำบล/แขวง <span class="text-danger">*</span></label>
                                <input type="text" name="register_subdistrict" id="register_subdistrict"
                                    class="form-control" placeholder="พระปฐมเจดีย์" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">รหัสไปรษณีย์ <span class="text-danger">*</span></label>
                                <input type="text" name="register_zipcode" id="register_zipcode"
                                    class="form-control" placeholder="73000" maxlength="5" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-column flex-md-row justify-content-between mt-4 gap-2">
                    <button type="button" class="btn btn-gradient btn-next order-md-last px-5">
                        ถัดไป <i class="bi bi-arrow-right ms-2"></i>
                    </button>
                    <button type="button" class="btn btn-secondary btn-prev px-5">
                        <i class="bi bi-arrow-left me-2"></i> ย้อนกลับ
                    </button>
                </div>
            </div>

            <!-- Step 3: ข้อมูลการศึกษา + ผู้ปกครอง -->
            <div class="form-step" data-step="3">
                <h4 class="fw-bold mb-4 text-gradient">
                    <i class="bi bi-book-fill me-2"></i> ข้อมูลการศึกษา
                </h4>

                <!-- ข้อมูลการศึกษา -->
                <div class="card bg-light mb-4">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">โรงเรียน/สถาบันเดิม</h5>
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">ชื่อโรงเรียน/สถาบัน <span class="text-danger">*</span></label>
                                <input type="text" name="school_name" class="form-control"
                                    placeholder="โรงเรียน..." required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">จังหวัด <span class="text-danger">*</span></label>
                                <input type="text" name="school_province" class="form-control"
                                    placeholder="นครปฐม" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">ระดับการศึกษา <span class="text-danger">*</span></label>
                                <select name="education_level" class="form-select" required>
                                    <option value="">เลือกระดับการศึกษา</option>
                                    <option value="ม.3">มัธยมศึกษาปีที่ 3 (ม.3)</option>
                                    <option value="ม.6">มัธยมศึกษาปีที่ 6 (ม.6)</option>
                                    <option value="ปวช.">ปวช. (ประกาศนียบัตรวิชาชีพ)</option>
                                    <option value="ปวส.">ปวส. (ประกาศนียบัตรวิชาชีพชั้นสูง)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">ปีที่จบการศึกษา <span class="text-danger">*</span></label>
                                <select name="graduation_year" class="form-select" required>
                                    <option value="">เลือกปีที่จบ</option>
                                    <?php
                                    $currentYear = date('Y') + 543;
                                    for ($year = $currentYear; $year >= $currentYear - 5; $year--) {
                                        echo "<option value='$year'>$year</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">เกรดเฉลี่ย (GPAX) <span class="text-danger">*</span></label>
                                <input type="number" name="gpax" id="gpax" class="form-control"
                                    placeholder="0.00" step="0.01" min="0" max="4" required>
                                <small class="text-muted">ระบุทศนิยม 2 ตำแหน่ง (0.00 - 4.00)</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ข้อมูลผู้ปกครอง -->
                <div class="card bg-light">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">ข้อมูลผู้ปกครอง</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">ชื่อ-นามสกุลผู้ปกครอง <span class="text-danger">*</span></label>
                                <input type="text" name="parent_name" class="form-control"
                                    placeholder="นาย/นาง/นางสาว..." required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">ความสัมพันธ์ <span class="text-danger">*</span></label>
                                <select name="parent_relation" class="form-select" required>
                                    <option value="">เลือกความสัมพันธ์</option>
                                    <option value="บิดา">บิดา</option>
                                    <option value="มารดา">มารดา</option>
                                    <option value="ผู้ปกครอง">ผู้ปกครอง</option>
                                    <option value="อื่นๆ">อื่นๆ</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">เบอร์โทรศัพท์ผู้ปกครอง <span class="text-danger">*</span></label>
                                <input type="tel" name="parent_phone" id="parent_phone" class="form-control"
                                    placeholder="0XX-XXX-XXXX" maxlength="12" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">อาชีพผู้ปกครอง <span class="text-danger">*</span></label>
                                <input type="text" name="parent_occupation" class="form-control"
                                    placeholder="อาชีพ..." required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-column flex-md-row justify-content-between mt-4 gap-2">
                    <button type="button" class="btn btn-gradient btn-next order-md-last px-5">
                        ถัดไป <i class="bi bi-arrow-right ms-2"></i>
                    </button>
                    <button type="button" class="btn btn-secondary btn-prev px-5">
                        <i class="bi bi-arrow-left me-2"></i> ย้อนกลับ
                    </button>
                </div>
            </div>

            <!-- Step 4: เลือกสาขาวิชา -->
            <div class="form-step" data-step="4">
                <h4 class="fw-bold mb-4 text-gradient">
                    <i class="bi bi-list-ul me-2"></i> เลือกสาขาวิชาที่ต้องการสมัคร
                </h4>

                <!-- คำอธิบาย -->
                <div class="alert alert-info shadow-sm mb-4">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    <strong>หมายเหตุ:</strong> กรุณาเลือกระดับชั้นที่ต้องการสมัคร จากนั้นเลือกสาขาวิชา (เลือกได้เพียง 1 สาขาเท่านั้น)
                </div>

                <!-- เลือกระดับชั้น -->
                <div class="card bg-light mb-4">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">เลือกระดับชั้นที่ต้องการสมัคร</h5>
                        <div class="row g-4">
                            <div class="col-12">
                                <label class="form-label">ระดับชั้น <span class="text-danger">*</span></label>
                                <select name="education_level_apply" id="education_level_apply" class="form-select mb-2" required>
                                    <option value="">-- เลือกระดับชั้นที่ต้องการสมัคร --</option>
                                    <option value="ปวช.">ปวช. (ประกาศนียบัตรวิชาชีพ)</option>
                                    <option value="ปวส.">ปวส. (ประกาศนียบัตรวิชาชีพชั้นสูง)</option>
                                    <option value="ปริญญาตรี">ปริญญาตรี</option>
                                </select>
                                <small class="text-muted">
                                    <i class="bi bi-lightbulb me-1"></i>
                                    เลือกระดับชั้นที่คุณต้องการเข้าศึกษา
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- เลือกสาขาวิชา -->
                <div class="card bg-light" id="department_selection_card" style="display: none;">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">เลือกสาขาวิชา</h5>

                        <!-- Search Box -->
                        <div class="mb-3">
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" id="department_search" class="form-control"
                                    placeholder="ค้นหาสาขาวิชา...">
                            </div>
                        </div>

                        <div class="row g-3" id="department_list">
                            <!-- Department cards will be loaded here -->
                        </div>

                        <!-- แสดงสาขาที่เลือก -->
                        <div id="selected_department" class="mt-4" style="display: none;">
                            <div class="alert alert-success shadow-sm">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="bi bi-check-circle-fill me-2"></i>
                                        <strong>คุณเลือก:</strong> <span id="selected_dept_name"></span>
                                        <span class="badge bg-secondary ms-2" id="selected_dept_category"></span>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearDepartmentSelection()">
                                        <i class="bi bi-x-circle me-1"></i> เปลี่ยนสาขา
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="department_1" id="department_1" value="">

                <div class="d-flex flex-column flex-md-row justify-content-between mt-4 gap-2">
                    <button type="button" class="btn btn-gradient btn-next order-md-last px-5">
                        ถัดไป <i class="bi bi-arrow-right ms-2"></i>
                    </button>
                    <button type="button" class="btn btn-secondary btn-prev px-5">
                        <i class="bi bi-arrow-left me-2"></i> ย้อนกลับ
                    </button>
                </div>
            </div>

            <!-- Steps 5-6 ต่อ... -->

        </div>
    </form>
</div>

<!-- JavaScript -->
<script>
    const departmentsByCategory = <?php echo json_encode($dept_by_category); ?>;
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
</script>
<script src="assets/js/quota-form.js"></script>