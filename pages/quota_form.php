<?php

/**
 * Quota Form - ใบสมัครรอบโควต้า
 * Multi-Step Form with Session Storage
 * * Updated to match 'students_quota' table structure.
 */

// ตรวจสอบว่าเปิดรับสมัครรอบโควต้าหรือไม่
if (!is_admission_open('quota')) {
    echo '<section class="py-5">
                <div class="container">
                    <div class="row mb-5 text-center">
                        <div class="col-12" data-aos="fade-up">
                            <h2 class="section-title text-gradient">ตอนยังไม่เปิดรับสมัคร</h2>
                            <p class="section-subtitle">ขณะนี้ยังไม่เปิดรับสมัครรอบโควต้า</p>
                        </div>
                    </div>
                    <div class="alert text-center shadow text-black" data-aos="fade-up">
                        <i class="bi bi-exclamation-triangle-fill fs-1 mb-3 d-block text-primary"></i>
                        <h4 class="mb-3">ยังไม่เปิดรับสมัครรอบโควต้า</h4>
                        <p class="mb-4 text-muted">ขณะนี้ยังไม่เปิดรับสมัครรอบโควต้า กรุณาติดตามข่าวสารเพิ่มเติม</p>
                        <a href="index.php?page=home" class="btn btn-gradient">
                            กลับหน้าหลัก <i class="bi bi-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </section>
            <section id="quick-info" class="py-5 bg-light">
                <div class="container">
                    <div class="row mb-5 text-center">
                        <div class="col-12" data-aos="fade-up">
                            <h2 class="section-title text-gradient">บริการของเรา</h2>
                            <p class="section-subtitle">เลือกบริการที่คุณต้องการ</p>
                        </div>
                    </div>
                    <div class="row g-4">
                        <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                            <div class="card h-100">
                                <div class="card-body text-center p-4">
                                    <div class="icon-circle bg-gradient-primary text-white mb-4">
                                        <i class="bi bi-file-earmark-text display-6"></i>
                                    </div>
                                    <h5 class="card-title fw-bold mb-3">รับสมัครออนไลน์</h5>
                                    <p class="card-text text-muted mb-4">
                                        สมัครเรียนออนไลน์ได้ตลอด 24 ชั่วโมง<br>
                                        สะดวก รวดเร็ว ปลอดภัย
                                    </p>
                                    <a href="index.php?page=admission_info" class="btn btn-gradient">
                                        ดูรายละเอียด <i class="bi bi-arrow-right ms-2"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                            <div class="card h-100">
                                <div class="card-body text-center p-4">
                                    <div class="icon-circle bg-gradient-secondary text-white mb-4">
                                        <i class="bi bi-search display-6"></i>
                                    </div>
                                    <h5 class="card-title fw-bold mb-3">ตรวจสอบสถานะ</h5>
                                    <p class="card-text text-muted mb-4">
                                        ตรวจสอบผลการสมัครได้ทันที<br>
                                        พร้อมข้อมูลรายละเอียด
                                    </p>
                                    <a href="index.php?page=check_status" class="btn btn-gradient">
                                        ตรวจสอบเลย <i class="bi bi-arrow-right ms-2"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                            <div class="card h-100">
                                <div class="card-body text-center p-4">
                                    <div class="icon-circle bg-gradient-blue text-white mb-4">
                                        <i class="bi bi-telephone display-6"></i>
                                    </div>
                                    <h5 class="card-title fw-bold mb-3">ติดต่อสอบถาม</h5>
                                    <p class="card-text text-muted mb-4">
                                        สอบถามข้อมูลเพิ่มเติมได้ที่นี่<br>
                                        ทีมงานพร้อมให้บริการ
                                    </p>
                                    <a href="index.php?page=contact" class="btn btn-gradient">
                                        ติดต่อเรา <i class="bi bi-arrow-right ms-2"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>';
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

<section class="page-header bg-gradient-blue text-white py-5 mb-4">
    <div class="container">
        <div class="row align-items-center" data-aos="fade-down">
            <div class="col-md-8">
                <h2 class="mb-1">
                    <i class="bi bi-file-earmark-text me-2"></i>
                    สมัครเข้าศึกษาต่อ รอบโควต้า (วิทยาลัยอาชีวศึกษานครปฐม)
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

<div class="container mb-4">
    <div class="card shadow-sm">
        <div class="card-body p-3">
            <h4 class="fw-bold mb-4 text-gradient">
                <i class="bi bi-check-circle-fill me-2"></i> กระบวนการสมัคร
            </h4>
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

<div class="container mb-5">
    <form id="quotaForm" class="card shadow">
        <div class="card-body p-4">

            <div class="form-step active" data-step="1">
                <h4 class="fw-bold mb-4 text-gradient">
                    <i class="bi bi-person-fill me-2"></i> ข้อมูลส่วนตัว
                </h4>

                <div class="card bg-light mb-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-2">
                                <label class="form-label">คำนำหน้า <span class="text-danger">*</span></label>
                                <select name="prefix" class="form-select" required>
                                    <option value="">เลือก</option>
                                    <option value="เด็กชาย">เด็กชาย</option>
                                    <option value="เด็กหญิง">เด็กหญิง</option>
                                    <option value="นาย">นาย</option>
                                    <option value="นางสาว">นางสาว</option>
                                    <option value="นาง">นาง</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">ชื่อ <span class="text-danger">*</span></label>
                                <input type="text" name="firstname_th" class="form-control" placeholder="ชื่อ" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">นามสกุล <span class="text-danger">*</span></label>
                                <input type="text" name="lastname_th" class="form-control" placeholder="นามสกุล" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">ชื่อเล่น</label>
                                <input type="text" name="nickname" class="form-control" placeholder="ชื่อเล่น">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">เลขบัตรประชาชน <span class="text-danger">*</span></label>
                                <input type="text" name="id_card" id="id_card" class="form-control"
                                    placeholder="X-XXXX-XXXXX-XX-X" maxlength="17" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">วันเกิด <span class="text-danger">*</span></label>
                                <input type="date" name="birth_date" id="birth_date" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">อายุ</label>
                                <input type="number" name="age" id="age" class="form-control" placeholder="ปี" min="0">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">กรุ๊ปเลือด</label>
                                <select name="blood_group" class="form-select">
                                    <option value="">ไม่ระบุ</option>
                                    <option value="A">A</option>
                                    <option value="B">B</option>
                                    <option value="O">O</option>
                                    <option value="AB">AB</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">เชื้อชาติ</label>
                                <input type="text" name="ethnicity" class="form-control" placeholder="เชื้อชาติ" value="ไทย">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">สัญชาติ</label>
                                <input type="text" name="nationality" class="form-control" placeholder="สัญชาติ" value="ไทย">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">ศาสนา</label>
                                <input type="text" name="religion" class="form-control" placeholder="ศาสนา" value="พุทธ">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">เบอร์โทรศัพท์ <span class="text-danger">*</span></label>
                                <input type="tel" name="phone" id="phone" class="form-control"
                                    placeholder="0XX-XXX-XXXX" maxlength="12" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">อีเมล <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control"
                                    placeholder="example@email.com" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-column flex-md-row justify-content-end mt-4 gap-2">
                    <button type="button" class="btn btn-gradient btn-next order-md-last px-5">
                        ถัดไป <i class="bi bi-arrow-right ms-2"></i>
                    </button>
                </div>
            </div>

            <div class="form-step" data-step="2">
                <h4 class="fw-bold mb-4 text-gradient">
                    <i class="bi bi-house-fill me-2"></i> ที่อยู่ (ตามทะเบียนบ้าน)
                </h4>

                <div class="card bg-light mb-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-9">
                                <label class="form-label">บ้านเลขที่ <span class="text-danger">*</span></label>
                                <input type="text" name="address_no" id="address_no"
                                    class="form-control" placeholder="123/45" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">หมู่ที่</label>
                                <input type="text" name="village_no" id="village_no"
                                    class="form-control" placeholder="เช่น 1">
                            </div>
                            <div class="col-12">
                                <label class="form-label">ถนน</label>
                                <input type="text" name="road" id="road"
                                    class="form-control" placeholder="ถนน...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">จังหวัด <span class="text-danger">*</span></label>
                                <input type="text" name="province" id="province"
                                    class="form-control" placeholder="นครปฐม" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">อำเภอ/เขต <span class="text-danger">*</span></label>
                                <input type="text" name="district" id="district"
                                    class="form-control" placeholder="เมืองนครปฐม" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">ตำบล/แขวง <span class="text-danger">*</span></label>
                                <input type="text" name="subdistrict" id="subdistrict"
                                    class="form-control" placeholder="พระปฐมเจดีย์" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">รหัสไปรษณีย์ <span class="text-danger">*</span></label>
                                <input type="text" name="postcode" id="postcode"
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

            <div class="form-step" data-step="3">
                <h4 class="fw-bold mb-4 text-gradient">
                    <i class="bi bi-book-fill me-2"></i> ข้อมูลการศึกษา
                </h4>

                <div class="card bg-light mb-4">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">โรงเรียน/สถาบันเดิม</h5>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">ชื่อโรงเรียน/สถาบัน <span class="text-danger">*</span></label>
                                <input type="text" name="current_school" class="form-control"
                                    placeholder="โรงเรียน..." required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">ที่อยู่สถานศึกษา <span class="text-danger">*</span></label>
                                <textarea name="school_address" class="form-control" rows="2" placeholder="ที่อยู่โรงเรียน/สถาบัน..." required></textarea>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">ระดับการศึกษาเดิม <span class="text-danger">*</span></label>
                                <select name="current_level" class="form-select" required>
                                    <option value="">เลือกระดับการศึกษา</option>
                                    <option value="ม.3">มัธยมศึกษาปีที่ 3 (ม.3)</option>
                                    <option value="ม.6">มัธยมศึกษาปีที่ 6 (ม.6)</option>
                                    <option value="ปวช.">ปวช. (ประกาศนียบัตรวิชาชีพ)</option>
                                    <option value="ปวส.">ปวส. (ประกาศนียบัตรวิชาชีพชั้นสูง)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">กำลังศึกษาอยู่ชั้น <span class="text-danger">*</span></label>
                                <input type="text" name="current_class" class="form-control"
                                    placeholder="เช่น ม.3/1" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">ปีที่จบการศึกษา <span class="text-danger">*</span></label>
                                <select name="graduation_year" class="form-select" required>
                                    <option value="">เลือกปีที่จบ</option>
                                    <?php
                                    $currentYearBE = date('Y') + 543;
                                    $startYear = $currentYearBE + 1; // ปีปัจจุบัน + 1 (สำหรับคนที่กำลังจะจบ)
                                    $endYear = $currentYearBE - 5;   // ย้อนหลัง 5 ปี
                                    for ($year = $startYear; $year >= $endYear; $year--) {
                                        echo "<option value='$year'>$year</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">เกรดเฉลี่ย (GPAX) <span class="text-danger">*</span></label>
                                <input type="number" name="gpa" id="gpa" class="form-control"
                                    placeholder="0.00" step="0.01" min="0" max="4" required>
                                <small class="text-muted">ระบุทศนิยม 2 ตำแหน่ง (0.00 - 4.00)</small>
                            </div>

                            <div class="col-12">
                                <label class="form-label">สาขางาน (กรณีจบ ปวช./ปวส.)</label>
                                <input type="text" name="current_major" class="form-control"
                                    placeholder="เช่น ช่างยนต์, บัญชี (ถ้ามี)">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card bg-light">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">ข้อมูลเพิ่มเติม (ถ้ามี)</h5>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">ผลงาน/รางวัลที่ได้รับ</label>
                                <textarea name="awards" class="form-control" rows="3" placeholder="ระบุผลงาน หรือรางวัลที่เคยได้รับ..."></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">ความสามารถพิเศษ</label>
                                <textarea name="talents" class="form-control" rows="3" placeholder="เช่น ดนตรี, กีฬา, คอมพิวเตอร์..."></textarea>
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

            <div class="form-step" data-step="4">
                <h4 class="fw-bold mb-4 text-gradient">
                    <i class="bi bi-list-ul me-2"></i> เลือกสาขาวิชาที่ต้องการสมัคร
                </h4>

                <div class="alert alert-info shadow-sm mb-4">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    <strong>หมายเหตุ:</strong> กรุณาเลือกระดับชั้นที่ต้องการสมัคร จากนั้นเลือกสาขาวิชา (เลือกได้เพียง 1 สาขาเท่านั้น)
                </div>

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

                <div class="card bg-light" id="department_selection_card" style="display: none;">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">เลือกสาขาวิชา</h5>

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
                        </div>

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

                <input type="hidden" name="department_id" id="department_id" value="">

                <div class="d-flex flex-column flex-md-row justify-content-between mt-4 gap-2">
                    <button type="button" class="btn btn-gradient btn-next order-md-last px-5">
                        ถัดไป <i class="bi bi-arrow-right ms-2"></i>
                    </button>
                    <button type="button" class="btn btn-secondary btn-prev px-5">
                        <i class="bi bi-arrow-left me-2"></i> ย้อนกลับ
                    </button>
                </div>
            </div>

            <div class="form-step" data-step="5">
                <h4 class="fw-bold mb-4 text-gradient">
                    <i class="bi bi-cloud-upload-fill me-2"></i> อัปโหลดเอกสารประกอบการสมัคร
                </h4>

                <div class="alert alert-warning shadow-sm mb-4" data-aos="fade-up">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>หมายเหตุ:</strong> กรุณาเตรียมเอกสารให้พร้อม ไฟล์รูปภาพต้องเป็น JPG/PNG และ PDF
                </div>

                <div class="card bg-light mb-4">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">
                            <i class="bi bi-person-square me-2 text-primary"></i>
                            1. รูปถ่าย 1 นิ้ว (หน้าตรง)
                        </h5>
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">เลือกไฟล์รูปถ่าย <span class="text-danger">*</span></label>
                                <input type="file" name="photo" id="photo" class="form-control"
                                    accept="image/jpeg,image/png,image/jpg" required>
                                <small class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    รองรับ: JPG, PNG | ขนาดไม่เกิน 2 MB
                                </small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">ตัวอย่าง</label>
                                <div class="border rounded p-2 text-center bg-white shadow-sm">
                                    <img id="photo_preview" src="https://placehold.co/150x200/4facfe/ffffff?text=Photo"
                                        class="img-fluid rounded" style="max-height: 150px; object-fit: cover;" alt="Preview">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card bg-light mb-4">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">
                            <i class="bi bi-file-earmark-text me-2 text-warning"></i>
                            2. ผลการเรียน (Transcript)
                        </h5>
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">เลือกไฟล์ผลการเรียน <span class="text-danger">*</span></label>
                                <input type="file" name="transcript" id="transcript" class="form-control"
                                    accept="application/pdf" required>
                                <small class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    รองรับ: PDF เท่านั้น | ขนาดไม่เกิน 5 MB
                                </small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">ตัวอย่าง</label>
                                <div class="border rounded p-3 text-center bg-white shadow-sm">
                                    <div id="transcript_preview">
                                        <i class="bi bi-file-earmark-pdf text-muted" style="font-size: 3rem;"></i>
                                        <p class="small text-muted mb-0 mt-2">ยังไม่ได้เลือกไฟล์</p>
                                    </div>
                                </div>
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

            <div class="form-step" data-step="6">
                <h4 class="fw-bold mb-4 text-gradient">
                    <i class="bi bi-check-circle-fill me-2"></i> ยืนยันข้อมูลการสมัคร
                </h4>

                <div class="card bg-light mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4">
                            <i class="bi bi-clipboard-check me-2 text-primary"></i>
                            สรุปข้อมูลการสมัคร
                        </h5>

                        <div class="mb-4 pb-4 border-bottom">
                            <h6 class="fw-bold text-primary mb-3">
                                <i class="bi bi-person-fill me-2"></i>ข้อมูลส่วนตัว
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <small class="text-muted d-block">ชื่อ-นามสกุล:</small>
                                    <p class="mb-0 fw-medium" id="summary_name">-</p>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted d-block">ชื่อเล่น:</small>
                                    <p class="mb-0 fw-medium" id="summary_nickname">-</p>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted d-block">เลขบัตรประชาชน:</small>
                                    <p class="mb-0 fw-medium" id="summary_id_card">-</p>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted d-block">วันเกิด:</small>
                                    <p class="mb-0 fw-medium" id="summary_birth_date">-</p>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted d-block">อายุ:</small>
                                    <p class="mb-0 fw-medium" id="summary_age">-</p>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted d-block">กรุ๊ปเลือด:</small>
                                    <p class="mb-0 fw-medium" id="summary_blood_group">-</p>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block">เชื้อชาติ:</small>
                                    <p class="mb-0 fw-medium" id="summary_ethnicity">-</p>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block">สัญชาติ:</small>
                                    <p class="mb-0 fw-medium" id="summary_nationality">-</p>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block">ศาสนา:</small>
                                    <p class="mb-0 fw-medium" id="summary_religion">-</p>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted d-block">เบอร์โทรศัพท์:</small>
                                    <p class="mb-0 fw-medium" id="summary_phone">-</p>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted d-block">อีเมล:</small>
                                    <p class="mb-0 fw-medium" id="summary_email">-</p>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4 pb-4 border-bottom">
                            <h6 class="fw-bold text-primary mb-3">
                                <i class="bi bi-house-fill me-2"></i>ที่อยู่ (ตามทะเบียนบ้าน)
                            </h6>
                            <small class="text-muted d-block">ที่อยู่:</small>
                            <p class="mb-0 fw-medium" id="summary_address">-</p>
                        </div>

                        <div class="mb-4 pb-4 border-bottom">
                            <h6 class="fw-bold text-primary mb-3">
                                <i class="bi bi-book-fill me-2"></i>ข้อมูลการศึกษา
                            </h6>
                            <div class="row g-3">
                                <div class="col-12">
                                    <small class="text-muted d-block">โรงเรียน/สถาบัน:</small>
                                    <p class="mb-0 fw-medium" id="summary_school">-</p>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted d-block">ที่อยู่สถานศึกษา:</small>
                                    <p class="mb-0 fw-medium" id="summary_school_address">-</p>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted d-block">ระดับ/ชั้น:</small>
                                    <p class="mb-0 fw-medium" id="summary_education_level">-</p>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted d-block">ปีที่จบการศึกษา:</small>
                                    <p class="mb-0 fw-medium" id="summary_graduation_year">-</p>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted d-block">เกรดเฉลี่ย (GPAX):</small>
                                    <p class="mb-0 fw-medium" id="summary_gpa">-</p>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted d-block">สาขางาน (เดิม):</small>
                                    <p class="mb-0 fw-medium" id="summary_current_major">-</p>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted d-block">ผลงาน/รางวัล:</small>
                                    <p class="mb-0 fw-medium" id="summary_awards">-</p>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted d-block">ความสามารถพิเศษ:</small>
                                    <p class="mb-0 fw-medium" id="summary_talents">-</p>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4 pb-4 border-bottom">
                            <h6 class="fw-bold text-primary mb-3">
                                <i class="bi bi-list-ul me-2"></i>สาขาที่สมัคร
                            </h6>
                            <div class="alert alert-info mb-0 shadow-sm">
                                <i class="bi bi-star-fill me-2"></i>
                                <strong id="summary_department_name">-</strong>
                                <span class="badge bg-secondary ms-2" id="summary_department_category"></span>
                            </div>
                        </div>

                        <div>
                            <h6 class="fw-bold text-primary mb-3">
                                <i class="bi bi-cloud-upload-fill me-2"></i>เอกสารที่อัปโหลด
                            </h6>
                            <div class="row g-3">
                                <div class="col-6 col-md-4">
                                    <div class="text-center p-3 border rounded bg-white shadow-sm">
                                        <i id="summary_photo_status" class="bi bi-check-circle-fill text-success fs-3"></i>
                                        <p class="small mb-0 mt-2">รูปถ่าย</p>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <div class="text-center p-3 border rounded bg-white shadow-sm">
                                        <i id="summary_transcript_status" class="bi bi-check-circle-fill text-success fs-3"></i>
                                        <p class="small mb-0 mt-2">Transcript</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card bg-light mb-4">
                    <div class="card-body">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" value="" id="accept_terms">
                            <label class="form-check-label fw-bold" for="accept_terms">
                                ข้าพเจ้ายอมรับเงื่อนไขและข้อตกลงในการสมัคร
                            </label>
                        </div>
                        <ul class="small text-muted mb-0 ps-4">
                            <li class="mb-2">ข้าพเจ้ารับรองว่าข้อมูลที่กรอกทั้งหมดเป็นความจริง</li>
                            <li class="mb-2">หากตรวจสอบพบว่าข้อมูลไม่ตรงความจริง ข้าพเจ้ายินยอมให้ยกเลิกการสมัคร</li>
                            <li class="mb-0">ข้าพเจ้ายินยอมให้ประมวลผลข้อมูลส่วนบุคคลตาม พ.ร.บ. คุ้มครองข้อมูลส่วนบุคคล</li>
                        </ul>
                    </div>
                </div>

                <div class="d-flex flex-column flex-md-row justify-content-between mt-4 gap-2">
                    <button type="submit" class="btn btn-success btn-lg order-md-last px-5 shadow" id="submit_btn">
                        <i class="bi bi-send-fill me-2"></i> ส่งใบสมัคร
                    </button>
                    <button type="button" class="btn btn-secondary btn-prev px-5">
                        <i class="bi bi-arrow-left me-2"></i> ย้อนกลับ
                    </button>
                </div>
            </div>

        </div>
    </form>
</div>

<script>
    // ส่งข้อมูล PHP ไปยัง JavaScript
    const departmentsByCategory = <?php echo json_encode($dept_by_category); ?>;

    // SweetAlert Toast Mixin
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });

    /**
     * อัปเดตหน้าสรุปข้อมูล (Step 6)
     */
    function updateSummary() {
        const form = document.getElementById('quotaForm');
        // ฟังก์ชันช่วยสำหรับแสดงค่าว่าง
        const val = (value) => (value && value.trim() !== '') ? value.trim() : '-';
        // ฟังก์ชันช่วยสำหรับ GPA
        const gpaVal = (value) => {
            const num = parseFloat(value);
            return (!isNaN(num) && num > 0) ? num.toFixed(2) : '-';
        }

        // Step 1: Personal Info
        document.getElementById('summary_name').textContent = val(`${form.prefix.value} ${form.firstname_th.value} ${form.lastname_th.value}`);
        document.getElementById('summary_nickname').textContent = val(form.nickname.value);
        document.getElementById('summary_id_card').textContent = val(form.id_card.value);
        const birthDate = form.birth_date.value;
        document.getElementById('summary_birth_date').textContent = birthDate ? new Date(birthDate).toLocaleDateString('th-TH', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        }) : '-';
        document.getElementById('summary_age').textContent = val(form.age.value ? `${form.age.value} ปี` : '');
        document.getElementById('summary_blood_group').textContent = val(form.blood_group.value);
        document.getElementById('summary_ethnicity').textContent = val(form.ethnicity.value);
        document.getElementById('summary_nationality').textContent = val(form.nationality.value);
        document.getElementById('summary_religion').textContent = val(form.religion.value);
        document.getElementById('summary_phone').textContent = val(form.phone.value);
        document.getElementById('summary_email').textContent = val(form.email.value);

        // Step 2: Address
        let fullAddress = [
            'บ้านเลขที่ ' + val(form.address_no.value),
            form.village_no.value ? 'หมู่ ' + val(form.village_no.value) : null,
            form.road.value ? 'ถนน ' + val(form.road.value) : null,
            'ต/แขวง ' + val(form.subdistrict.value),
            'อ/เขต ' + val(form.district.value),
            'จ. ' + val(form.province.value),
            val(form.postcode.value)
        ].filter(Boolean).join(', '); // filter(Boolean)
        document.getElementById('summary_address').textContent = fullAddress;

        // Step 3: Education
        document.getElementById('summary_school').textContent = val(form.current_school.value);
        document.getElementById('summary_school_address').textContent = val(form.school_address.value);
        document.getElementById('summary_education_level').textContent = val(`ระดับ ${form.current_level.value} (ชั้น ${form.current_class.value})`);
        document.getElementById('summary_graduation_year').textContent = val(form.graduation_year.value);
        document.getElementById('summary_gpa').textContent = gpaVal(form.gpa.value);
        document.getElementById('summary_current_major').textContent = val(form.current_major.value);
        document.getElementById('summary_awards').textContent = val(form.awards.value);
        document.getElementById('summary_talents').textContent = val(form.talents.value);

        // Step 4: Department
        const selectedDeptName = document.getElementById('selected_dept_name').textContent;
        const selectedDeptCategory = document.getElementById('selected_dept_category').textContent;
        document.getElementById('summary_department_name').textContent = val(selectedDeptName);
        document.getElementById('summary_department_category').textContent = val(selectedDeptCategory);

        // Step 5: Files Status
        const uploads = JSON.parse(sessionStorage.getItem('quotaFormUploads')) || {};
        document.getElementById('summary_photo_status').className =
            (uploads.photo && uploads.photo.path) 
            ? 'bi bi-check-circle-fill text-success fs-3' 
            : 'bi bi-x-circle-fill text-danger fs-3';
            
        document.getElementById('summary_transcript_status').className =
            (uploads.transcript && uploads.transcript.path)
            ? 'bi bi-check-circle-fill text-success fs-3'
            : 'bi bi-x-circle-fill text-danger fs-3';
    }



    document.addEventListener('DOMContentLoaded', () => {
        const birthDateInput = document.getElementById('birth_date');
        const ageInput = document.getElementById('age');

        // Auto calculate age
        if (birthDateInput && ageInput) {
            birthDateInput.addEventListener('change', () => {
                if (birthDateInput.value) {
                    const birthDate = new Date(birthDateInput.value);
                    const today = new Date();
                    let age = today.getFullYear() - birthDate.getFullYear();
                    const m = today.getMonth() - birthDate.getMonth();
                    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                        age--;
                    }
                    ageInput.value = age > 0 ? age : 0;
                } else {
                    ageInput.value = '';
                }
            });
        }

        // Form Submit Handler - *** แก้ไขใหม่: เพิ่ม Modal ยืนยันก่อนส่ง ***
        const form = document.getElementById('quotaForm');
        form.addEventListener('submit', function(e) {
             e.preventDefault(); // 1. หยุดการส่งฟอร์มตามปกติ
             
             // 2. ตรวจสอบ Checkbox ยอมรับเงื่อนไข
             if (!document.getElementById('accept_terms').checked) {
                Swal.fire({
                    icon: 'warning',
                    title: 'ยังไม่ยอมรับเงื่อนไข',
                    text: 'กรุณากดยอมรับเงื่อนไขและข้อตกลงก่อนส่งใบสมัคร'
                });
                return; // หยุดการทำงานถ้ายังไม่ติ๊ก
             }

             // 3. 🚀 แสดง Modal ยืนยันการส่ง
             Swal.fire({
                title: 'ยืนยันการส่งใบสมัคร',
                text: "กรุณาตรวจสอบข้อมูลให้ถูกต้องครบถ้วนก่อนส่งใบสมัคร",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745', // สีเขียว
                cancelButtonColor: '#6c757d',  // สีเทา
                confirmButtonText: '<i class="bi bi-send-fill me-2"></i> ยืนยันการส่ง',
                cancelButtonText: 'ยกเลิก'
             }).then((result) => {
                
                // 4. ถ้าผู้ใช้กด "ยืนยันการส่ง"
                if (result.isConfirmed) {
                    
                    // 5. เรียกฟังก์ชัน submitForm() จาก quota-form.js
                    if (typeof submitForm === 'function') {
                        submitForm(); // <--- เรียกใช้ฟังก์ชันส่งข้อมูลจริง
                    } else {
                        console.error('❌ ฟังก์ชัน submitForm() ไม่พบ');
                        Swal.fire({
                            icon: 'error',
                            title: 'เกิดข้อผิดพลาด',
                            text: 'ไม่พบฟังก์ชันส่งข้อมูล กรุณาโหลดหน้าใหม่'
                        });
                    }
                }
             });
        });

        // Update Summary Step
        document.querySelectorAll('.btn-next').forEach(btn => {
            btn.addEventListener('click', () => {
                if (typeof currentStep !== 'undefined' && currentStep === 5) {
                    if (validateCurrentStep()) {
                        updateSummary();
                    }
                }
            });
        });
    });
</script>

<script src="assets/js/quota-form.js"></script>