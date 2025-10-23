<?php
/**
 * Admission Info Page - ข้อมูลการรับสมัคร
 */

// ตรวจสอบสถานะการเปิด-ปิดรับสมัคร
$quota_open = is_admission_open('quota');
$regular_open = is_admission_open('regular');

// ดึงข้อมูลสาขาวิชาทั้งหมดแบ่งตามระดับ
$levels = ['ปวช.', 'ปวส.', 'ปริญญาตรี'];
?>

<!-- Page Header -->
<section class="page-header bg-gradient-primary text-white py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center" data-aos="fade-down">
                <h1 class="display-6 fw-bold mb-3">
                    <i class="bi bi-info-circle-fill me-3"></i>
                    ข้อมูลการรับสมัคร
                </h1>
                <p class="lead mb-0">
                    รายละเอียดการรับสมัครนักเรียน นักศึกษา ประจำปีการศึกษา <?php echo (date('Y') + 543 + 1); ?>
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Admission Status -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row g-4">
            <!-- รอบโควต้า -->
            <div class="col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-4">
                            <div class="icon-circle bg-gradient-primary text-white me-3" style="width: 60px; height: 60px;">
                                <i class="bi bi-award display-6"></i>
                            </div>
                            <div>
                                <h4 class="mb-0 fw-bold">รอบโควต้า</h4>
                                <?php if ($quota_open): ?>
                                    <span class="badge bg-success">เปิดรับสมัคร</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">ยังไม่เปิดรับสมัคร</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <p class="text-muted mb-4">
                            รับสมัครนักเรียน นักศึกษาที่มีผลการเรียนดี มีความประพฤติดี 
                            และมีคุณสมบัติตามที่กำหนด
                        </p>
                        <div class="d-grid">
                            <?php if ($quota_open): ?>
                                <a href="index.php?page=quota_form" class="btn btn-gradient btn-lg">
                                    <i class="bi bi-pencil-square me-2"></i> สมัครเลย
                                </a>
                            <?php else: ?>
                                <button class="btn btn-secondary btn-lg" disabled>
                                    <i class="bi bi-x-circle me-2"></i> ยังไม่เปิดรับสมัคร
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- รอบปกติ -->
            <div class="col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-4">
                            <div class="icon-circle bg-gradient-secondary text-white me-3" style="width: 60px; height: 60px;">
                                <i class="bi bi-people display-6"></i>
                            </div>
                            <div>
                                <h4 class="mb-0 fw-bold">รอบปกติ</h4>
                                <?php if ($regular_open): ?>
                                    <span class="badge bg-success">เปิดรับสมัคร</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">ปิดรับสมัคร</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <p class="text-muted mb-4">
                            รับสมัครนักเรียน นักศึกษาทั่วไป ที่สำเร็จการศึกษาหรือ
                            กำลังศึกษาอยู่ตามระดับที่กำหนด
                        </p>
                        <div class="d-grid">
                            <?php if ($regular_open): ?>
                                <a href="index.php?page=regular_form" class="btn btn-gradient btn-lg">
                                    <i class="bi bi-pencil-square me-2"></i> สมัครเลย
                                </a>
                            <?php else: ?>
                                <button class="btn btn-secondary btn-lg" disabled>
                                    <i class="bi bi-x-circle me-2"></i> ปิดรับสมัคร
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Timeline -->
<section class="py-5">
    <div class="container">
        <div class="row mb-5">
            <div class="col-12 text-center" data-aos="fade-up">
                <h2 class="section-title text-gradient">กำหนดการรับสมัคร</h2>
                <p class="section-subtitle">ตารางเวลาสำคัญที่ต้องจำไว้</p>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-10 offset-lg-1">
                <div class="timeline" data-aos="fade-up" data-aos-delay="100">
                    <!-- Timeline Item 1 -->
                    <div class="timeline-item">
                        <div class="timeline-marker bg-primary"></div>
                        <div class="timeline-content">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h5 class="fw-bold mb-0">
                                            <i class="bi bi-calendar-check text-primary me-2"></i>
                                            เปิดรับสมัคร
                                        </h5>
                                        <span class="badge bg-primary">ธันวาคม 2567</span>
                                    </div>
                                    <p class="text-muted mb-0">
                                        เปิดรับสมัครนักเรียน นักศึกษา ทั้งรอบโควต้าและรอบปกติ
                                        ผ่านระบบออนไลน์
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Timeline Item 2 -->
                    <div class="timeline-item">
                        <div class="timeline-marker bg-success"></div>
                        <div class="timeline-content">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h5 class="fw-bold mb-0">
                                            <i class="bi bi-clipboard-check text-success me-2"></i>
                                            ตรวจสอบคุณสมบัติ
                                        </h5>
                                        <span class="badge bg-success">มกราคม 2568</span>
                                    </div>
                                    <p class="text-muted mb-0">
                                        ตรวจสอบเอกสารและคุณสมบัติผู้สมัคร 
                                        ประกาศรายชื่อผู้มีสิทธิ์สอบสัมภาษณ์
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Timeline Item 3 -->
                    <div class="timeline-item">
                        <div class="timeline-marker bg-info"></div>
                        <div class="timeline-content">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h5 class="fw-bold mb-0">
                                            <i class="bi bi-chat-dots text-info me-2"></i>
                                            สอบสัมภาษณ์
                                        </h5>
                                        <span class="badge bg-info">กุมภาพันธ์ 2568</span>
                                    </div>
                                    <p class="text-muted mb-0">
                                        สอบสัมภาษณ์ผู้สมัคร พร้อมตรวจสอบเอกสารต้นฉบับ
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Timeline Item 4 -->
                    <div class="timeline-item">
                        <div class="timeline-marker bg-warning"></div>
                        <div class="timeline-content">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h5 class="fw-bold mb-0">
                                            <i class="bi bi-megaphone text-warning me-2"></i>
                                            ประกาศผล
                                        </h5>
                                        <span class="badge bg-warning">มีนาคม 2568</span>
                                    </div>
                                    <p class="text-muted mb-0">
                                        ประกาศรายชื่อผู้ผ่านการคัดเลือก พร้อมรายละเอียดการมอบตัว
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Timeline Item 5 -->
                    <div class="timeline-item">
                        <div class="timeline-marker bg-danger"></div>
                        <div class="timeline-content">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h5 class="fw-bold mb-0">
                                            <i class="bi bi-person-check text-danger me-2"></i>
                                            มอบตัวและรายงานตัว
                                        </h5>
                                        <span class="badge bg-danger">เมษายน 2568</span>
                                    </div>
                                    <p class="text-muted mb-0">
                                        ผู้ผ่านการคัดเลือกมอบตัวและรายงานตัวเข้าเป็นนักเรียน นักศึกษา
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Qualifications -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row mb-5">
            <div class="col-12 text-center" data-aos="fade-up">
                <h2 class="section-title text-gradient">คุณสมบัติผู้สมัคร</h2>
                <p class="section-subtitle">ข้อกำหนดสำหรับผู้สมัครแต่ละระดับ</p>
            </div>
        </div>

        <div class="row g-4">
            <!-- ปวช. -->
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-header bg-gradient-primary text-white text-center py-3">
                        <h4 class="mb-0 fw-bold">
                            <i class="bi bi-bookmark-star me-2"></i>
                            ระดับ ปวช.
                        </h4>
                    </div>
                    <div class="card-body p-4">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-3">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                สำเร็จการศึกษาระดับ ม.3
                            </li>
                            <li class="mb-3">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                มีผลการเรียนเฉลี่ย 2.00 ขึ้นไป
                            </li>
                            <li class="mb-3">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                มีความประพฤติดี
                            </li>
                            <li class="mb-3">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                ไม่มีโรคติดต่อร้ายแรง
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- ปวส. -->
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-header bg-gradient-secondary text-white text-center py-3">
                        <h4 class="mb-0 fw-bold">
                            <i class="bi bi-bookmark-star me-2"></i>
                            ระดับ ปวส.
                        </h4>
                    </div>
                    <div class="card-body p-4">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-3">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                สำเร็จการศึกษาระดับ ม.6 หรือ ปวช.
                            </li>
                            <li class="mb-3">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                มีผลการเรียนเฉลี่ย 2.00 ขึ้นไป
                            </li>
                            <li class="mb-3">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                มีความประพฤติดี
                            </li>
                            <li class="mb-3">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                ไม่มีโรคติดต่อร้ายแรง
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- ปริญญาตรี -->
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-header bg-gradient-blue text-white text-center py-3">
                        <h4 class="mb-0 fw-bold">
                            <i class="bi bi-bookmark-star me-2"></i>
                            ระดับ ปริญญาตรี
                        </h4>
                    </div>
                    <div class="card-body p-4">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-3">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                สำเร็จการศึกษาระดับ ปวส.
                            </li>
                            <li class="mb-3">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                มีผลการเรียนเฉลี่ย 2.00 ขึ้นไป
                            </li>
                            <li class="mb-3">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                มีความประพฤติดี
                            </li>
                            <li class="mb-3">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                ไม่มีโรคติดต่อร้ายแรง
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Documents Required -->
<section class="py-5">
    <div class="container">
        <div class="row mb-5">
            <div class="col-12 text-center" data-aos="fade-up">
                <h2 class="section-title text-gradient">เอกสารที่ต้องใช้</h2>
                <p class="section-subtitle">เตรียมเอกสารให้พร้อมก่อนสมัคร</p>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 offset-lg-2">
                <div class="card border-0 shadow-sm" data-aos="fade-up" data-aos-delay="100">
                    <div class="card-body p-4 p-md-5">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="d-flex mb-3">
                                    <div class="flex-shrink-0">
                                        <div class="icon-circle bg-primary bg-opacity-10 text-primary" 
                                             style="width: 65px; height: 65px;">
                                            <i class="bi bi-file-earmark-person display-6"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="fw-bold mb-1">รูปถ่ายหน้าตรง</h6>
                                        <p class="text-muted small mb-0">
                                            ขนาด 1 นิ้ว หรือ 2 นิ้ว<br>
                                            ไฟล์ JPG/PNG ไม่เกิน 5 MB
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="d-flex mb-3">
                                    <div class="flex-shrink-0">
                                        <div class="icon-circle bg-success bg-opacity-10 text-success" 
                                             style="width: 65px; height: 65px;">
                                            <i class="bi bi-file-earmark-text display-6"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="fw-bold mb-1">ใบผลการเรียน</h6>
                                        <p class="text-muted small mb-0">
                                            Transcript (PDF)<br>
                                            ไฟล์ PDF ไม่เกิน 5 MB
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="d-flex mb-3">
                                    <div class="flex-shrink-0">
                                        <div class="icon-circle bg-info bg-opacity-10 text-info" 
                                             style="width: 65px; height: 65px;">
                                            <i class="bi bi-person-badge display-6"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="fw-bold mb-1">สำเนาบัตรประชาชน</h6>
                                        <p class="text-muted small mb-0">
                                            (สำหรับยืนยันตัวตน)
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="d-flex mb-3">
                                    <div class="flex-shrink-0">
                                        <div class="icon-circle bg-warning bg-opacity-10 text-warning" 
                                             style="width: 65px; height: 65px;">
                                            <i class="bi bi-file-earmark-medical display-6"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="fw-bold mb-1">ใบรับรองแพทย์</h6>
                                        <p class="text-muted small mb-0">
                                            (นำมาในวันมอบตัว)
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info mt-4 mb-0" role="alert">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>หมายเหตุ:</strong> เอกสารทั้งหมดต้องชัดเจน อ่านได้ และเป็นปัจจุบัน
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Departments -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row mb-5">
            <div class="col-12 text-center" data-aos="fade-up">
                <h2 class="section-title text-gradient">สาขาวิชาที่เปิดรับ</h2>
                <p class="section-subtitle">เลือกสาขาที่ใช่สำหรับคุณ</p>
            </div>
        </div>

        <?php foreach ($levels as $index => $level): ?>
            <?php 
            $departments = get_departments($level);
            if (count($departments) > 0):
            ?>
            <div class="mb-5" data-aos="fade-up" data-aos-delay="<?php echo ($index * 100); ?>">
                <h4 class="fw-bold mb-4">
                    <span class="badge bg-gradient-primary me-2"><?php echo $level; ?></span>
                    <?php echo count($departments); ?> สาขาวิชา
                </h4>
                <div class="row g-3">
                    <?php foreach ($departments as $dept): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-mortarboard-fill text-primary fs-3 me-3"></i>
                                        <div>
                                            <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($dept['name_th']); ?></h6>
                                            <small class="text-muted"><?php echo htmlspecialchars($dept['category_name']); ?></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</section>

<!-- Contact CTA -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 offset-lg-2">
                <div class="card border-0 shadow-lg bg-gradient-primary text-white" data-aos="zoom-in">
                    <div class="card-body p-5 text-center">
                        <i class="bi bi-question-circle display-1 mb-4"></i>
                        <h3 class="fw-bold mb-3">มีคำถามเพิ่มเติม?</h3>
                        <p class="lead mb-4">
                            ติดต่อสอบถามข้อมูลเพิ่มเติมได้ที่งานรับสมัคร
                        </p>
                        <div class="d-flex flex-column flex-md-row gap-3 justify-content-center">
                            <a href="tel:<?php echo str_replace('-', '', get_setting('contact_phone', '0342510810')); ?>" 
                               class="btn btn-light btn-lg px-5">
                                <i class="bi bi-telephone me-2"></i>
                                <?php echo get_setting('contact_phone', '034-251-081'); ?>
                            </a>
                            <a href="index.php?page=contact" class="btn btn-outline-light btn-lg px-5">
                                <i class="bi bi-envelope me-2"></i> ส่งข้อความถึงเรา
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Timeline Styles */
.timeline {
    position: relative;
    padding: 0;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 30px;
    top: 0;
    bottom: 0;
    width: 3px;
    background: linear-gradient(to bottom, #4facfe, #00f2fe);
}

.timeline-item {
    position: relative;
    padding-left: 80px;
    margin-bottom: 30px;
}

.timeline-marker {
    position: absolute;
    left: 18px;
    top: 10px;
    width: 25px;
    height: 25px;
    border-radius: 50%;
    border: 4px solid white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.timeline-content {
    position: relative;
}

.timeline-item:last-child {
    margin-bottom: 0;
}

@media (max-width: 768px) {
    .timeline::before {
        left: 15px;
    }
    
    .timeline-item {
        padding-left: 50px;
    }
    
    .timeline-marker {
        left: 7px;
        width: 18px;
        height: 18px;
    }
}
</style>