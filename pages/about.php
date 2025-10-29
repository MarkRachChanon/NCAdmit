<?php
/**
 * About Page - เกี่ยวกับเรา
 */

$site_name = get_setting('site_name', 'NC-Admission');
$site_description = get_setting('site_description', 'ระบบรับสมัครนักเรียนออนไลน์ วิทยาลัยอาชีวศึกษานครปฐม');
?>

<!-- Page Header -->
<section class="page-header bg-gradient-primary text-white py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center" data-aos="fade-down">
                <h1 class="display-5 fw-bold mb-3">
                    <i class="bi bi-info-circle-fill me-3"></i>
                    เกี่ยวกับเรา
                </h1>
                <p class="lead mb-0">
                    ทำความรู้จักกับวิทยาลัยอาชีวศึกษานครปฐมและระบบ <?php echo $site_name; ?>
                </p>
            </div>
        </div>
    </div>
</section>

<!-- About NC-Admission -->
<section class="py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0" data-aos="fade-right">
                <div class="position-relative">
                    <img src="/ncadmission/assets/images/admission.png" 
                         alt="NC-Admission System" 
                         class="img-fluid rounded-4 shadow-lg">
                    <div class="position-absolute bottom-0 start-0 m-4 p-3 bg-white rounded-3 shadow">
                        <h5 class="mb-0 fw-bold text-primary">
                            <i class="bi bi-people-fill me-2"></i>
                            <?php echo get_setting('stat_students', '2000'); ?> นักเรียน
                        </h5>
                    </div>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <div class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill mb-3">
                    <i class="bi bi-star-fill me-2"></i>
                    ระบบรับสมัครออนไลน์
                </div>
                <h2 class="display-6 fw-bold mb-4">
                    <?php echo $site_name; ?>
                </h2>
                <p class="lead text-muted mb-4">
                    <?php echo $site_description; ?>
                </p>
                <p class="text-muted mb-4">
                    ระบบรับสมัครนักเรียนออนไลน์ที่ทันสมัย สะดวก รวดเร็ว และปลอดภัย 
                    พัฒนาขึ้นเพื่ออำนวยความสะดวกให้กับผู้สมัครและเจ้าหน้าที่ 
                    ลดขั้นตอนการทำงานที่ซับซ้อน และเพิ่มประสิทธิภาพในการจัดการข้อมูล
                </p>
                <div class="row g-4">
                    <div class="col-6">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle-fill text-success fs-4 me-3"></i>
                            <div>
                                <h6 class="mb-0 fw-bold">สมัครง่าย</h6>
                                <small class="text-muted">24 ชั่วโมง</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle-fill text-success fs-4 me-3"></i>
                            <div>
                                <h6 class="mb-0 fw-bold">ปลอดภัย</h6>
                                <small class="text-muted">เข้ารหัสข้อมูล</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle-fill text-success fs-4 me-3"></i>
                            <div>
                                <h6 class="mb-0 fw-bold">ตรวจสอบได้</h6>
                                <small class="text-muted">Real-time</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle-fill text-success fs-4 me-3"></i>
                            <div>
                                <h6 class="mb-0 fw-bold">ไร้กระดาษ</h6>
                                <small class="text-muted">เป็นมิตรสิ่งแวดล้อม</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- About College -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 order-lg-2 mb-4 mb-lg-0" data-aos="fade-left">
                <div class="position-relative">
                    <img src="/ncadmission/assets/images/nvc.jpg" 
                         alt="วิทยาลัยอาชีวศึกษานครปฐม" 
                         class="img-fluid rounded-4 shadow-lg">
                    <div class="position-absolute top-0 end-0 m-4 p-3 bg-white rounded-3 shadow">
                        <h5 class="mb-0 fw-bold text-primary">
                            <i class="bi bi-award-fill me-2"></i>
                            89+ ปี
                        </h5>
                        <small class="text-muted">ประสบการณ์</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 order-lg-1" data-aos="fade-right">
                <div class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill mb-3">
                    <i class="bi bi-mortarboard-fill me-2"></i>
                    สถาบันการศึกษา
                </div>
                <h2 class="display-6 fw-bold mb-4">
                    วิทยาลัยอาชีวศึกษานครปฐม
                </h2>
                <p class="lead text-muted mb-4">
                    สถาบันการศึกษาชั้นนำด้านอาชีวศึกษา ที่มุ่งเน้นการพัฒนาทักษะและความรู้
                    เพื่อสร้างบุคลากรที่มีคุณภาพสู่ตลาดแรงงาน
                </p>
                <p class="text-muted mb-4">
                    ด้วยประสบการณ์กว่า 50 ปีในการผลิตบุคลากรที่มีคุณภาพ 
                    มีหลักสูตรการเรียนการสอนที่ทันสมัย ครูผู้สอนที่มีความเชี่ยวชาญ 
                    และอุปกรณ์การเรียนการสอนที่ครบครัน พร้อมส่งเสริมให้นักเรียน
                    นักศึกษาพัฒนาตนเองอย่างเต็มศักยภาพ
                </p>
                <a href="index.php?page=admission_info" class="btn btn-gradient btn-lg">
                    <i class="bi bi-arrow-right-circle me-2"></i>
                    ข้อมูลการรับสมัคร
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Statistics -->
<section class="py-5">
    <div class="container">
        <div class="row mb-5">
            <div class="col-12 text-center" data-aos="fade-up">
                <h2 class="section-title text-gradient">ตัวเลขที่น่าภาคภูมิใจ</h2>
                <p class="section-subtitle">ความสำเร็จที่เราร่วมสร้าง</p>
            </div>
        </div>

        <div class="row g-4">
            <!-- Stat 1 -->
            <div class="col-md-3 col-sm-6" data-aos="fade-up" data-aos-delay="100">
                <div class="card border-0 shadow-sm text-center h-100">
                    <div class="card-body p-4">
                        <div class="icon-circle bg-primary bg-opacity-10 text-primary mx-auto mb-3" 
                             style="width: 80px; height: 80px;">
                            <i class="bi bi-people-fill display-6"></i>
                        </div>
                        <h3 class="fw-bold mb-2 counter" data-target="<?php echo get_setting('stat_students', '2000'); ?>">0</h3>
                        <p class="text-muted mb-0">นักเรียน นักศึกษา</p>
                    </div>
                </div>
            </div>

            <!-- Stat 2 -->
            <div class="col-md-3 col-sm-6" data-aos="fade-up" data-aos-delay="200">
                <div class="card border-0 shadow-sm text-center h-100">
                    <div class="card-body p-4">
                        <div class="icon-circle bg-success bg-opacity-10 text-success mx-auto mb-3" 
                             style="width: 80px; height: 80px;">
                            <i class="bi bi-book-fill display-6"></i>
                        </div>
                        <h3 class="fw-bold mb-2 counter" data-target="<?php echo get_setting('stat_departments', '23'); ?>">0</h3>
                        <p class="text-muted mb-0">สาขาวิชา</p>
                    </div>
                </div>
            </div>

            <!-- Stat 3 -->
            <div class="col-md-3 col-sm-6" data-aos="fade-up" data-aos-delay="300">
                <div class="card border-0 shadow-sm text-center h-100">
                    <div class="card-body p-4">
                        <div class="icon-circle bg-info bg-opacity-10 text-info mx-auto mb-3" 
                             style="width: 80px; height: 80px;">
                            <i class="bi bi-person-badge-fill display-6"></i>
                        </div>
                        <h3 class="fw-bold mb-2 counter" data-target="<?php echo get_setting('stat_teachers', '150'); ?>">0</h3>
                        <p class="text-muted mb-0">คณาจารย์</p>
                    </div>
                </div>
            </div>

            <!-- Stat 4 -->
            <div class="col-md-3 col-sm-6" data-aos="fade-up" data-aos-delay="400">
                <div class="card border-0 shadow-sm text-center h-100">
                    <div class="card-body p-4">
                        <div class="icon-circle bg-warning bg-opacity-10 text-warning mx-auto mb-3" 
                             style="width: 80px; height: 80px;">
                            <i class="bi bi-trophy-fill display-6"></i>
                        </div>
                        <h3 class="fw-bold mb-2 counter" data-target="<?php echo get_setting('stat_employment', '95'); ?>">0</h3>
                        <p class="text-muted mb-0">% มีงานทำ</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Vision & Mission -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row mb-5">
            <div class="col-12 text-center" data-aos="fade-up">
                <h2 class="section-title text-gradient">วิสัยทัศน์และพันธกิจ</h2>
                <p class="section-subtitle">เป้าหมายที่เรามุ่งมั่น</p>
            </div>
        </div>

        <div class="row g-4">
            <!-- Vision -->
            <div class="col-lg-6" data-aos="fade-up" data-aos-delay="100">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4 p-md-5">
                        <div class="d-flex align-items-center mb-4">
                            <div class="icon-circle bg-primary bg-opacity-10 text-primary me-3" 
                                 style="width: 60px; height: 60px;">
                                <i class="bi bi-eye-fill display-6"></i>
                            </div>
                            <h3 class="mb-0 fw-bold">วิสัยทัศน์</h3>
                        </div>
                        <p class="text-muted mb-0 lead">
                            "มุ่งมั่นผลิตและพัฒนากำลังคนด้านอาชีวศึกษาให้มีคุณภาพ 
                            มาตรฐานสากล สามารถแข่งขันได้ในระดับสากล 
                            เป็นที่ยอมรับของสถานประกอบการและชุมชน"
                        </p>
                    </div>
                </div>
            </div>

            <!-- Mission -->
            <div class="col-lg-6" data-aos="fade-up" data-aos-delay="200">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4 p-md-5">
                        <div class="d-flex align-items-center mb-4">
                            <div class="icon-circle bg-success bg-opacity-10 text-success me-3" 
                                 style="width: 60px; height: 60px;">
                                <i class="bi bi-bullseye display-6"></i>
                            </div>
                            <h3 class="mb-0 fw-bold">พันธกิจ</h3>
                        </div>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-3 d-flex">
                                <i class="bi bi-check-circle-fill text-success me-3 mt-1"></i>
                                <span>จัดการศึกษาอาชีวศึกษาที่มีคุณภาพตามมาตรฐาน</span>
                            </li>
                            <li class="mb-3 d-flex">
                                <i class="bi bi-check-circle-fill text-success me-3 mt-1"></i>
                                <span>พัฒนาทักษะวิชาชีพและคุณธรรมจริยธรรม</span>
                            </li>
                            <li class="mb-3 d-flex">
                                <i class="bi bi-check-circle-fill text-success me-3 mt-1"></i>
                                <span>ส่งเสริมการวิจัยและนวัตกรรม</span>
                            </li>
                            <li class="mb-0 d-flex">
                                <i class="bi bi-check-circle-fill text-success me-3 mt-1"></i>
                                <span>สร้างเครือข่ายความร่วมมือกับสถานประกอบการ</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Core Values -->
<section class="py-5">
    <div class="container">
        <div class="row mb-5">
            <div class="col-12 text-center" data-aos="fade-up">
                <h2 class="section-title text-gradient">ค่านิยมหลัก</h2>
                <p class="section-subtitle">หลักการที่เรายึดถือ</p>
            </div>
        </div>

        <div class="row g-4">
            <!-- Value 1 -->
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card border-0 shadow-sm h-100 text-center">
                    <div class="card-body p-4">
                        <div class="icon-circle bg-gradient-primary text-white mx-auto mb-3" 
                             style="width: 80px; height: 80px;">
                            <i class="bi bi-lightbulb-fill display-6"></i>
                        </div>
                        <h5 class="fw-bold mb-3">นวัตกรรม</h5>
                        <p class="text-muted mb-0">
                            ส่งเสริมความคิดสร้างสรรค์และการพัฒนานวัตกรรมใหม่ๆ 
                            เพื่อก้าวทันเทคโนโลยี
                        </p>
                    </div>
                </div>
            </div>

            <!-- Value 2 -->
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="card border-0 shadow-sm h-100 text-center">
                    <div class="card-body p-4">
                        <div class="icon-circle bg-gradient-secondary text-white mx-auto mb-3" 
                             style="width: 80px; height: 80px;">
                            <i class="bi bi-heart-fill display-6"></i>
                        </div>
                        <h5 class="fw-bold mb-3">คุณธรรม</h5>
                        <p class="text-muted mb-0">
                            ปลูกฝังคุณธรรม จริยธรรม และความรับผิดชอบต่อสังคม
                            เพื่อเป็นพลเมืองดี
                        </p>
                    </div>
                </div>
            </div>

            <!-- Value 3 -->
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                <div class="card border-0 shadow-sm h-100 text-center">
                    <div class="card-body p-4">
                        <div class="icon-circle bg-gradient-blue text-white mx-auto mb-3" 
                             style="width: 80px; height: 80px;">
                            <i class="bi bi-star-fill display-6"></i>
                        </div>
                        <h5 class="fw-bold mb-3">ความเป็นเลิศ</h5>
                        <p class="text-muted mb-0">
                            มุ่งมั่นพัฒนาคุณภาพการศึกษาอย่างต่อเนื่อง
                            เพื่อความเป็นเลิศทางวิชาการและวิชาชีพ
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row mb-5">
            <div class="col-12 text-center" data-aos="fade-up">
                <h2 class="section-title text-gradient">ทำไมต้องเลือกเรา</h2>
                <p class="section-subtitle">จุดเด่นที่ทำให้เราแตกต่าง</p>
            </div>
        </div>

        <div class="row g-4">
            <!-- Feature 1 -->
            <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="100">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4 text-center">
                        <i class="bi bi-mortarboard-fill text-primary display-1 mb-3"></i>
                        <h5 class="fw-bold mb-3">หลักสูตรทันสมัย</h5>
                        <p class="text-muted mb-0">
                            หลักสูตรการเรียนการสอนที่ทันสมัย 
                            ตอบโจทย์ตลาดแรงงาน
                        </p>
                    </div>
                </div>
            </div>

            <!-- Feature 2 -->
            <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="200">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4 text-center">
                        <i class="bi bi-people-fill text-success display-1 mb-3"></i>
                        <h5 class="fw-bold mb-3">คณาจารย์มืออาชีพ</h5>
                        <p class="text-muted mb-0">
                            ครูผู้สอนที่มีความเชี่ยวชาญ
                            และประสบการณ์สูง
                        </p>
                    </div>
                </div>
            </div>

            <!-- Feature 3 -->
            <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="300">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4 text-center">
                        <i class="bi bi-building-fill text-info display-1 mb-3"></i>
                        <h5 class="fw-bold mb-3">อุปกรณ์ครบครัน</h5>
                        <p class="text-muted mb-0">
                            ห้องปฏิบัติการและอุปกรณ์
                            ที่ทันสมัยครบครัน
                        </p>
                    </div>
                </div>
            </div>

            <!-- Feature 4 -->
            <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="400">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4 text-center">
                        <i class="bi bi-briefcase-fill text-warning display-1 mb-3"></i>
                        <h5 class="fw-bold mb-3">โอกาสทำงาน</h5>
                        <p class="text-muted mb-0">
                            เครือข่ายสถานประกอบการ
                            พร้อมรับบัณฑิต
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-10 offset-lg-1">
                <div class="card border-0 shadow-lg bg-gradient-primary text-white" data-aos="zoom-in">
                    <div class="card-body p-5 text-center">
                        <i class="bi bi-rocket-takeoff display-1 mb-4"></i>
                        <h3 class="fw-bold mb-3">พร้อมเริ่มต้นกับเราแล้วหรือยัง?</h3>
                        <p class="lead mb-4">
                            เข้าร่วมเป็นส่วนหนึ่งของวิทยาลัยอาชีวศึกษานครปฐม
                            และสร้างอนาคตที่สดใสไปด้วยกัน
                        </p>
                        <div class="d-flex flex-column flex-md-row gap-3 justify-content-center">
                            <a href="index.php?page=admission_info" class="btn btn-light btn-lg px-5">
                                <i class="bi bi-info-circle me-2"></i> ข้อมูลการรับสมัคร
                            </a>
                            <a href="index.php?page=contact" class="btn btn-outline-light btn-lg px-5">
                                <i class="bi bi-envelope me-2"></i> ติดต่อสอบถาม
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Counter Animation
document.addEventListener('DOMContentLoaded', function() {
    const counters = document.querySelectorAll('.counter');
    const speed = 200;

    const animateCounter = (counter) => {
        const target = +counter.getAttribute('data-target');
        const count = +counter.innerText;
        const increment = target / speed;

        if (count < target) {
            counter.innerText = Math.ceil(count + increment);
            setTimeout(() => animateCounter(counter), 1);
        } else {
            counter.innerText = target;
            if (counter.parentElement.querySelector('.text-muted').innerText.includes('%')) {
                counter.innerText = target + '%';
            } else {
                counter.innerText = target.toLocaleString();
            }
        }
    };

    // Intersection Observer for counter animation
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const counter = entry.target;
                animateCounter(counter);
                observer.unobserve(counter);
            }
        });
    }, { threshold: 0.5 });

    counters.forEach(counter => {
        observer.observe(counter);
    });
});
</script>