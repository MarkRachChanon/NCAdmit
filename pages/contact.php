<?php
/**
 * Contact Page - ติดต่อเรา
 */

// Handle form submission
$success = false;
$error = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_contact'])) {
    $name = clean_input($_POST['name'] ?? '');
    $email = clean_input($_POST['email'] ?? '');
    $phone = clean_input($_POST['phone'] ?? '');
    $subject = clean_input($_POST['subject'] ?? '');
    $message = clean_input($_POST['message'] ?? '');
    
    // Validation
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = "กรุณากรอกข้อมูลให้ครบถ้วน";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "รูปแบบอีเมลไม่ถูกต้อง";
    } else {
        // Save to database (optional)
        $sql = "INSERT INTO contact_messages (name, email, phone, subject, message, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("sssss", $name, $email, $phone, $subject, $message);
            
            if ($stmt->execute()) {
                $success = true;
                
                // Send email notification (optional)
                $to = get_setting('contact_email', 'info@nc.ac.th');
                $email_subject = "ข้อความติดต่อใหม่: $subject";
                $email_body = "ชื่อ: $name\nอีเมล: $email\nโทรศัพท์: $phone\n\nข้อความ:\n$message";
                $headers = "From: $email\r\nReply-To: $email";
                
                // mail($to, $email_subject, $email_body, $headers);
            } else {
                $error = "เกิดข้อผิดพลาดในการส่งข้อความ";
            }
            $stmt->close();
        } else {
            $error = "เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล";
        }
    }
}

// Get contact info from settings
$phone = get_setting('contact_phone', '034-251-081');
$email = get_setting('contact_email', 'info@nc.ac.th');
$address = get_setting('contact_address', 'วิทยาลัยอาชีวศึกษานครปฐม จังหวัดนครปฐม');
$facebook = get_setting('facebook_url', '#');
$line = get_setting('line_url', '#');
$google_map = get_setting('google_map_url', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3875.839471779832!2d100.06037931483044!3d13.738045090357!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMTPCsDQ0JzE3LjAiTiAxMDDCsDAzJzQ1LjAiRQ!5e0!3m2!1sth!2sth!4v1234567890');
?>

<!-- Page Header -->
<section class="page-header bg-gradient-primary text-white py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center" data-aos="fade-down">
                <h1 class="display-6 fw-bold mb-3">
                    <i class="bi bi-envelope-fill me-3"></i>
                    ติดต่อเรา
                </h1>
                <p class="lead mb-0">
                    ยินดีให้บริการและตอบคำถามทุกท่าน
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Contact Info Cards -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row g-4">
            <!-- Phone -->
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card border-0 shadow-sm h-100 text-center">
                    <div class="card-body p-4">
                        <div class="icon-circle bg-primary bg-opacity-10 text-primary mx-auto mb-3" 
                             style="width: 80px; height: 80px;">
                            <i class="bi bi-telephone-fill display-6"></i>
                        </div>
                        <h5 class="fw-bold mb-3">โทรศัพท์</h5>
                        <p class="text-muted mb-2">ติดต่อสอบถามข้อมูล</p>
                        <a href="tel:<?php echo str_replace('-', '', $phone); ?>" 
                           class="h5 text-primary text-decoration-none fw-bold">
                            <?php echo $phone; ?>
                        </a>
                        <div class="mt-3">
                            <small class="text-muted">จันทร์-ศุกร์ 08:00-16:30 น.</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Email -->
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="card border-0 shadow-sm h-100 text-center">
                    <div class="card-body p-4">
                        <div class="icon-circle bg-success bg-opacity-10 text-success mx-auto mb-3" 
                             style="width: 80px; height: 80px;">
                            <i class="bi bi-envelope-fill display-6"></i>
                        </div>
                        <h5 class="fw-bold mb-3">อีเมล</h5>
                        <p class="text-muted mb-2">ส่งข้อความถึงเรา</p>
                        <a href="mailto:<?php echo $email; ?>" 
                           class="h6 text-success text-decoration-none fw-bold">
                            <?php echo $email; ?>
                        </a>
                        <div class="mt-3">
                            <small class="text-muted">ตอบภายใน 24 ชั่วโมง</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Location -->
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                <div class="card border-0 shadow-sm h-100 text-center">
                    <div class="card-body p-4">
                        <div class="icon-circle bg-info bg-opacity-10 text-info mx-auto mb-3" 
                             style="width: 80px; height: 80px;">
                            <i class="bi bi-geo-alt-fill display-6"></i>
                        </div>
                        <h5 class="fw-bold mb-3">ที่อยู่</h5>
                        <p class="text-muted mb-2">มาเยี่ยมชมเรา</p>
                        <p class="mb-0 fw-bold"><?php echo nl2br($address); ?></p>
                        <div class="mt-3">
                            <a href="#map" class="btn btn-sm btn-outline-info">
                                <i class="bi bi-map me-2"></i> ดูแผนที่
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Form & Social -->
<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <!-- Contact Form -->
            <div class="col-lg-8" data-aos="fade-right">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4 p-md-5">
                        <h3 class="fw-bold mb-4">
                            <i class="bi bi-chat-dots-fill text-primary me-2"></i>
                            ส่งข้อความถึงเรา
                        </h3>

                        <?php if ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                <strong>ส่งข้อความสำเร็จ!</strong> เราจะติดต่อกลับโดยเร็วที่สุด
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <strong>เกิดข้อผิดพลาด!</strong> <?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="" id="contactForm">
                            <div class="row g-3">
                                <!-- Name -->
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">
                                        ชื่อ-นามสกุล <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           name="name" 
                                           class="form-control form-control-lg" 
                                           placeholder="กรอกชื่อ-นามสกุล"
                                           required>
                                </div>

                                <!-- Phone -->
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">
                                        เบอร์โทรศัพท์
                                    </label>
                                    <input type="tel" 
                                           name="phone" 
                                           class="form-control form-control-lg" 
                                           placeholder="08X-XXX-XXXX"
                                           pattern="[0-9]{9,10}">
                                </div>

                                <!-- Email -->
                                <div class="col-12">
                                    <label class="form-label fw-bold">
                                        อีเมล <span class="text-danger">*</span>
                                    </label>
                                    <input type="email" 
                                           name="email" 
                                           class="form-control form-control-lg" 
                                           placeholder="example@email.com"
                                           required>
                                </div>

                                <!-- Subject -->
                                <div class="col-12">
                                    <label class="form-label fw-bold">
                                        หัวข้อ <span class="text-danger">*</span>
                                    </label>
                                    <select name="subject" class="form-select form-select-lg" required>
                                        <option value="">-- เลือกหัวข้อ --</option>
                                        <option value="สอบถามข้อมูลการรับสมัคร">สอบถามข้อมูลการรับสมัคร</option>
                                        <option value="สอบถามหลักสูตร">สอบถามหลักสูตร</option>
                                        <option value="สอบถามเรื่องทั่วไป">สอบถามเรื่องทั่วไป</option>
                                        <option value="ร้องเรียน/แจ้งปัญหา">ร้องเรียน/แจ้งปัญหา</option>
                                        <option value="อื่นๆ">อื่นๆ</option>
                                    </select>
                                </div>

                                <!-- Message -->
                                <div class="col-12">
                                    <label class="form-label fw-bold">
                                        ข้อความ <span class="text-danger">*</span>
                                    </label>
                                    <textarea name="message" 
                                              class="form-control" 
                                              rows="5" 
                                              placeholder="พิมพ์ข้อความของคุณที่นี่..."
                                              required></textarea>
                                </div>

                                <!-- Submit -->
                                <div class="col-12">
                                    <button type="submit" 
                                            name="submit_contact" 
                                            class="btn btn-gradient btn-lg w-100">
                                        <i class="bi bi-send-fill me-2"></i> ส่งข้อความ
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Social Media -->
                <div class="card border-0 shadow-sm mb-4" data-aos="fade-left">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4">
                            <i class="bi bi-share-fill text-primary me-2"></i>
                            ติดตามเรา
                        </h5>
                        <div class="d-grid gap-3">
                            <a href="<?php echo $facebook; ?>" 
                               target="_blank"
                               class="btn btn-outline-primary btn-lg text-start">
                                <i class="bi bi-facebook me-3"></i> Facebook
                            </a>
                            <a href="<?php echo $line; ?>" 
                               target="_blank"
                               class="btn btn-outline-success btn-lg text-start">
                                <i class="bi bi-line me-3"></i> Line Official
                            </a>
                            <a href="<?php echo get_setting('youtube_url', '#'); ?>" 
                               target="_blank"
                               class="btn btn-outline-danger btn-lg text-start">
                                <i class="bi bi-youtube me-3"></i> YouTube
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="card border-0 shadow-sm" data-aos="fade-left" data-aos-delay="100">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4">
                            <i class="bi bi-link-45deg text-primary me-2"></i>
                            ลิงก์ด่วน
                        </h5>
                        <div class="d-flex flex-column gap-3">
                            <a href="index.php?page=admission_info" class="text-decoration-none">
                                <i class="bi bi-chevron-right text-primary me-2"></i>
                                ข้อมูลการรับสมัคร
                            </a>
                            <a href="index.php?page=about" class="text-decoration-none">
                                <i class="bi bi-chevron-right text-primary me-2"></i>
                                เกี่ยวกับเรา
                            </a>
                            <a href="index.php?page=news" class="text-decoration-none">
                                <i class="bi bi-chevron-right text-primary me-2"></i>
                                ข่าวสาร
                            </a>
                            <a href="index.php?page=gallery" class="text-decoration-none">
                                <i class="bi bi-chevron-right text-primary me-2"></i>
                                แกลเลอรี่
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Google Map -->
<section class="py-5 bg-light" id="map">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12 text-center" data-aos="fade-up">
                <h2 class="section-title text-gradient">แผนที่</h2>
                <p class="section-subtitle">ตำแหน่งที่ตั้งวิทยาลัย</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12" data-aos="fade-up" data-aos-delay="100">
                <div class="card border-0 shadow-sm overflow-hidden">
                    <div class="ratio ratio-21x9">
                        <iframe src="<?php echo $google_map; ?>"
                                style="border:0;" 
                                allowfullscreen="" 
                                loading="lazy" 
                                referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section (Optional) -->
<section class="py-5">
    <div class="container">
        <div class="row mb-5">
            <div class="col-12 text-center" data-aos="fade-up">
                <h2 class="section-title text-gradient">คำถามที่พบบ่อย</h2>
                <p class="section-subtitle">คำตอบสำหรับคำถามยอดนิยม</p>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion" id="faqAccordion" data-aos="fade-up" data-aos-delay="100">
                    <!-- FAQ 1 -->
                    <div class="accordion-item border-0 shadow-sm mb-3">
                        <h2 class="accordion-header">
                            <button class="accordion-button fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                <i class="bi bi-question-circle-fill text-primary me-3"></i>
                                เปิดรับสมัครเมื่อไหร่?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                เปิดรับสมัครประมาณเดือนธันวาคม - มีนาคม ของทุกปี สามารถติดตามข่าวสารได้ที่เว็บไซต์หรือ Facebook Page
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 2 -->
                    <div class="accordion-item border-0 shadow-sm mb-3">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                <i class="bi bi-question-circle-fill text-primary me-3"></i>
                                มีหลักสูตรอะไรบ้าง?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                มีหลักสูตร ปวช., ปวส. และปริญญาตรี ในสาขาต่างๆ กว่า 20 สาขา ดูรายละเอียดเพิ่มเติมได้ที่หน้าข้อมูลการรับสมัคร
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 3 -->
                    <div class="accordion-item border-0 shadow-sm mb-3">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                <i class="bi bi-question-circle-fill text-primary me-3"></i>
                                ค่าเทอมเท่าไหร่?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                ค่าใช้จ่ายขึ้นอยู่กับหลักสูตรและระดับการศึกษา โดยประมาณ 3,000-8,000 บาทต่อภาคเรียน โทรสอบถามรายละเอียดได้ที่ 034-251-081
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 4 -->
                    <div class="accordion-item border-0 shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                <i class="bi bi-question-circle-fill text-primary me-3"></i>
                                มีหอพักหรือไม่?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                ไม่มีหอพักในวิทยาลัย แต่มีหอพักเอกชนรอบๆ วิทยาลัยให้เลือกมากมาย ราคาเริ่มต้น 1,500-3,000 บาท/เดือน
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Icon Circle */
.icon-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Accordion Custom */
.accordion-button:not(.collapsed) {
    background-color: #f8f9fa;
    color: #4facfe;
}

.accordion-button:focus {
    box-shadow: none;
    border-color: #dee2e6;
}

/* Map Responsive */
.ratio-21x9 {
    --bs-aspect-ratio: 42.857%;
}
</style>

<script>
// Form validation
document.getElementById('contactForm')?.addEventListener('submit', function(e) {
    const phone = this.querySelector('[name="phone"]').value;
    if (phone && !/^[0-9]{9,10}$/.test(phone.replace(/-/g, ''))) {
        e.preventDefault();
        showError('กรุณากรอกเบอร์โทรศัพท์ให้ถูกต้อง (9-10 หลัก)');
    }
});
</script>