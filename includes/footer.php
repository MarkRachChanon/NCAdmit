<?php
$contact_phone = get_setting('contact_phone', '034-251-081');
$contact_email = get_setting('contact_email', 'info@nc.ac.th');
$current_year = date('Y') + 543;
?>

<footer class="bg-dark text-white mt-5 pt-5 pb-3">
    <div class="container">
        <div class="row">
            <!-- Column 1: About -->
            <div class="col-md-4 mb-4">
                <div class="d-flex align-items-center mb-3">
                    <img src="assets/images/logo.png" alt="Logo" height="50" class="me-3">
                    <div>
                        <h5 class="mb-0 fw-bold">NCAdmit</h5>
                        <small class="text-white-50">ระบบรับสมัครนักเรียนออนไลน์</small>
                    </div>
                </div>
                <p class="text-white-50">
                    <strong>วิทยาลัยอาชีวศึกษานครปฐม</strong><br>
                    สะดวก รวดเร็ว ปลอดภัย ตลอด 24 ชั่วโมง
                </p>
                <div class="social-links mt-3">
                    <a href="#" class="text-white me-3" title="Facebook">
                        <i class="bi bi-facebook fs-4"></i>
                    </a>
                    <a href="#" class="text-white me-3" title="YouTube">
                        <i class="bi bi-youtube fs-4"></i>
                    </a>
                    <a href="#" class="text-white me-3" title="Line">
                        <i class="bi bi-line fs-4"></i>
                    </a>
                </div>
            </div>
            
            <!-- Column 2: Quick Links -->
            <div class="col-md-4 mb-4">
                <h5 class="mb-3">เมนูหลัก</h5>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <a href="index.php?page=home" class="text-white-50 text-decoration-none">
                            <i class="bi bi-chevron-right me-2"></i> หน้าแรก
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="index.php?page=about" class="text-white-50 text-decoration-none">
                            <i class="bi bi-chevron-right me-2"></i> เกี่ยวกับเรา
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="index.php?page=news" class="text-white-50 text-decoration-none">
                            <i class="bi bi-chevron-right me-2"></i> ข่าวสาร
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="index.php?page=admission_info" class="text-white-50 text-decoration-none">
                            <i class="bi bi-chevron-right me-2"></i> ข้อมูลการรับสมัคร
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="index.php?page=check_status" class="text-white-50 text-decoration-none">
                            <i class="bi bi-chevron-right me-2"></i> ตรวจสอบสถานะ
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Column 3: Contact -->
            <div class="col-md-4 mb-4">
                <h5 class="mb-3">ติดต่อเรา</h5>
                <ul class="list-unstyled text-white-50">
                    <li class="mb-2">
                        <i class="bi bi-telephone-fill me-2"></i>
                        <?php echo $contact_phone; ?>
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-envelope-fill me-2"></i>
                        <?php echo $contact_email; ?>
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-geo-alt-fill me-2"></i>
                        จังหวัดนครปฐม
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-clock-fill me-2"></i>
                        จันทร์ - ศุกร์ 08:00 - 16:30 น.
                    </li>
                </ul>
            </div>
        </div>
        
        <hr class="bg-white">
        
        <div class="row">
            <div class="col-md-12 text-center">
                <p class="mb-0 text-white-50">
                    &copy; <?php echo $current_year; ?> วิทยาลัยอาชีวศึกษานครปฐม. All rights reserved.
                    <br>
                    <small>Powered by <strong>NCAdmit</strong> ||  Dev by <strong>Mark Chanon</strong></small>
                </p>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap 5 JS Bundle (Local) -->
<script src="assets/js/bootstrap.bundle.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- AOS Animation JS -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<!-- Custom JS -->
<script src="assets/js/custom.js"></script>

<!-- Initialize AOS -->
<script>
    AOS.init({
        duration: 1000,
        easing: 'ease-out-cubic',
        once: true,
        offset: 100
    });
</script>

</body>
</html>