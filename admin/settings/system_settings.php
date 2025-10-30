<?php

/**
 * หน้าตั้งค่าระบบ
 * NC-Admission - Nakhon Pathom College Admission System
 */

// ตรวจสอบสิทธิ์การเข้าถึง
if (!check_page_permission('system_settings', $admin_role)) {
    header('Location: index.php?page=dashboard');
    exit();
}

// ดึงการตั้งค่าทั้งหมด
$settings_sql = "SELECT * FROM settings ORDER BY id ASC";
$settings_result = $conn->query($settings_sql);

// แปลงเป็น array สำหรับใช้งาน
$settings = [];
if ($settings_result && $settings_result->num_rows > 0) {
    while ($row = $settings_result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row;
    }
}
?>

<!-- Page Header -->
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-2">
                        <i class="bi bi-gear text-primary me-2"></i>
                        ตั้งค่าระบบ
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="index.php?page=dashboard">หน้าแรก</a></li>
                            <li class="breadcrumb-item active">ตั้งค่าระบบ</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- ด้านซ้าย: เมนูหมวดหมู่ -->
        <div class="col-md-3">
            <div class="card shadow-sm" id="categoryMenu">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-list-ul me-2"></i>
                        หมวดหมู่
                    </h6>
                </div>
                <div class="list-group list-group-flush">
                    <a href="#general" class="list-group-item list-group-item-action active" data-category="general">
                        <i class="bi bi-info-circle me-2"></i>
                        ข้อมูลทั่วไป
                    </a>
                    <a href="#contact" class="list-group-item list-group-item-action" data-category="contact">
                        <i class="bi bi-telephone me-2"></i>
                        ข้อมูลติดต่อ
                    </a>
                    <a href="#social" class="list-group-item list-group-item-action" data-category="social">
                        <i class="bi bi-share me-2"></i>
                        โซเชียลมีเดีย
                    </a>
                    <a href="#admission" class="list-group-item list-group-item-action" data-category="admission">
                        <i class="bi bi-calendar-check me-2"></i>
                        การรับสมัคร
                    </a>
                    <a href="#admission-schedule" class="list-group-item list-group-item-action" data-category="admission-schedule">
                        <i class="bi bi-calendar2-event me-2"></i>
                        กำหนดการรับสมัคร
                    </a>
                    <a href="#google" class="list-group-item list-group-item-action" data-category="google">
                        <i class="bi bi-google me-2"></i>
                        Google Drive
                    </a>
                    <a href="#statistics" class="list-group-item list-group-item-action" data-category="statistics">
                        <i class="bi bi-bar-chart me-2"></i>
                        สถิติ
                    </a>
                </div>
            </div>
        </div>

        <!-- ด้านขวา: ฟอร์มการตั้งค่า -->
        <div class="col-md-9">
            <form id="settingsForm">
                <!-- ข้อมูลทั่วไป -->
                <div class="card shadow-sm mb-4 setting-section" id="general">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="bi bi-info-circle text-primary me-2"></i>
                            ข้อมูลทั่วไป
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="site_name" class="form-label">
                                    ชื่อเว็บไซต์ <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                    class="form-control"
                                    id="site_name"
                                    name="site_name"
                                    value="<?php echo htmlspecialchars($settings['site_name']['setting_value'] ?? ''); ?>"
                                    required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="google_map_url" class="form-label">
                                    Google Map Embed URL
                                </label>
                                <input type="text"
                                    class="form-control"
                                    id="google_map_url"
                                    name="google_map_url"
                                    value="<?php echo htmlspecialchars($settings['google_map_url']['setting_value'] ?? ''); ?>">
                            </div>

                            <div class="col-12 mb-3">
                                <label for="site_description" class="form-label">
                                    คำอธิบายเว็บไซต์
                                </label>
                                <textarea class="form-control"
                                    id="site_description"
                                    name="site_description"
                                    rows="3"><?php echo htmlspecialchars($settings['site_description']['setting_value'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ข้อมูลติดต่อ -->
                <div class="card shadow-sm mb-4 setting-section" id="contact" style="display: none;">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="bi bi-telephone text-primary me-2"></i>
                            ข้อมูลติดต่อ
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="contact_phone" class="form-label">
                                    เบอร์โทรศัพท์
                                </label>
                                <input type="text"
                                    class="form-control"
                                    id="contact_phone"
                                    name="contact_phone"
                                    value="<?php echo htmlspecialchars($settings['contact_phone']['setting_value'] ?? ''); ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="contact_email" class="form-label">
                                    อีเมล
                                </label>
                                <input type="email"
                                    class="form-control"
                                    id="contact_email"
                                    name="contact_email"
                                    value="<?php echo htmlspecialchars($settings['contact_email']['setting_value'] ?? ''); ?>">
                            </div>

                            <div class="col-12 mb-3">
                                <label for="contact_address" class="form-label">
                                    ที่อยู่
                                </label>
                                <textarea class="form-control"
                                    id="contact_address"
                                    name="contact_address"
                                    rows="3"><?php echo htmlspecialchars($settings['contact_address']['setting_value'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- โซเชียลมีเดีย -->
                <div class="card shadow-sm mb-4 setting-section" id="social" style="display: none;">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="bi bi-share text-primary me-2"></i>
                            โซเชียลมีเดีย
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="facebook_url" class="form-label">
                                    <i class="bi bi-facebook text-primary"></i> Facebook
                                </label>
                                <input type="url"
                                    class="form-control"
                                    id="facebook_url"
                                    name="facebook_url"
                                    value="<?php echo htmlspecialchars($settings['facebook_url']['setting_value'] ?? ''); ?>"
                                    placeholder="https://facebook.com/...">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="youtube_url" class="form-label">
                                    <i class="bi bi-youtube text-danger"></i> YouTube
                                </label>
                                <input type="url"
                                    class="form-control"
                                    id="youtube_url"
                                    name="youtube_url"
                                    value="<?php echo htmlspecialchars($settings['youtube_url']['setting_value'] ?? ''); ?>"
                                    placeholder="https://youtube.com/...">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="tiktok_url" class="form-label">
                                    <i class="bi bi-tiktok text-dark"></i> TikTok
                                </label>
                                <input type="url"
                                    class="form-control"
                                    id="tiktok_url"
                                    name="tiktok_url"
                                    value="<?php echo htmlspecialchars($settings['tiktok_url']['setting_value'] ?? ''); ?>"
                                    placeholder="https://tiktok.com/@...">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- การรับสมัคร -->
                <div class="card shadow-sm mb-4 setting-section" id="admission" style="display: none;">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="bi bi-calendar-check text-primary me-2"></i>
                            การรับสมัคร
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>คำแนะนำ:</strong> เปิด/ปิดการรับสมัครในแต่ละรอบ
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="card border-primary">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <i class="bi bi-calendar-star me-2"></i>
                                            รอบโควต้า (Quota)
                                        </h6>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input"
                                                type="checkbox"
                                                id="quota_open"
                                                name="quota_open"
                                                value="1"
                                                <?php echo ($settings['quota_open']['setting_value'] ?? '0') == '1' ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="quota_open">
                                                <span class="badge bg-success" id="quota_status">
                                                    <?php echo ($settings['quota_open']['setting_value'] ?? '0') == '1' ? 'เปิดรับสมัคร' : 'ปิดรับสมัคร'; ?>
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="card border-warning">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <i class="bi bi-calendar me-2"></i>
                                            รอบปกติ (Regular)
                                        </h6>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input"
                                                type="checkbox"
                                                id="regular_open"
                                                name="regular_open"
                                                value="1"
                                                <?php echo ($settings['regular_open']['setting_value'] ?? '0') == '1' ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="regular_open">
                                                <span class="badge bg-success" id="regular_status">
                                                    <?php echo ($settings['regular_open']['setting_value'] ?? '0') == '1' ? 'เปิดรับสมัคร' : 'ปิดรับสมัคร'; ?>
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- กำหนดการรับสมัคร (Timeline) -->
                <div class="card shadow-sm mb-4 setting-section" id="admission-schedule" style="display: none;">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="bi bi-calendar2-event text-primary me-2"></i>
                            กำหนดการรับสมัคร
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>คำแนะนำ:</strong> กำหนดวันที่สำคัญในแต่ละขั้นตอนการรับสมัคร (จะแสดงบนหน้าแรกอัตโนมัติ)
                        </div>

                        <!-- รอบโควต้า -->
                        <div class="card border-primary mb-4">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">
                                    <i class="bi bi-calendar-star me-2"></i>
                                    รอบโควต้า (Quota)
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <!-- เปิดรับสมัคร -->
                                    <div class="col-12">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="timeline-dot bg-primary"></div>
                                            <h6 class="mb-0 ms-2">
                                                <i class="bi bi-calendar-check text-primary"></i>
                                                เปิดรับสมัคร
                                            </h6>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="quota_start_date" class="form-label">
                                            วันเริ่มต้น
                                        </label>
                                        <input type="date"
                                            class="form-control"
                                            id="quota_start_date"
                                            name="quota_start_date"
                                            value="<?php echo htmlspecialchars($settings['quota_start_date']['setting_value'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="quota_end_date" class="form-label">
                                            วันสิ้นสุด
                                        </label>
                                        <input type="date"
                                            class="form-control"
                                            id="quota_end_date"
                                            name="quota_end_date"
                                            value="<?php echo htmlspecialchars($settings['quota_end_date']['setting_value'] ?? ''); ?>">
                                    </div>

                                    <!-- ตรวจสอบคุณสมบัติ -->
                                    <div class="col-12 mt-4">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="timeline-dot bg-success"></div>
                                            <h6 class="mb-0 ms-2">
                                                <i class="bi bi-clipboard-check text-success"></i>
                                                ตรวจสอบคุณสมบัติ
                                            </h6>
                                        </div>
                                        <small class="text-muted ms-4">ระบบจะตรวจสอบอัตโนมัติหลังปิดรับสมัคร</small>
                                    </div>

                                    <!-- ประกาศผล -->
                                    <div class="col-12 mt-4">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="timeline-dot bg-info"></div>
                                            <h6 class="mb-0 ms-2">
                                                <i class="bi bi-megaphone text-info"></i>
                                                ประกาศผล
                                            </h6>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="quota_announce_date" class="form-label">
                                            วันประกาศผล
                                        </label>
                                        <input type="date"
                                            class="form-control"
                                            id="quota_announce_date"
                                            name="quota_announce_date"
                                            value="<?php echo htmlspecialchars($settings['quota_announce_date']['setting_value'] ?? ''); ?>">
                                    </div>

                                    <!-- ยืนยันสิทธิ์ -->
                                    <div class="col-12 mt-4">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="timeline-dot bg-warning"></div>
                                            <h6 class="mb-0 ms-2">
                                                <i class="bi bi-hand-thumbs-up text-warning"></i>
                                                ยืนยันสิทธิ์
                                            </h6>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="quota_confirm_start" class="form-label">
                                            วันเริ่มต้น
                                        </label>
                                        <input type="date"
                                            class="form-control"
                                            id="quota_confirm_start"
                                            name="quota_confirm_start"
                                            value="<?php echo htmlspecialchars($settings['quota_confirm_start']['setting_value'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="quota_confirm_end" class="form-label">
                                            วันสิ้นสุด
                                        </label>
                                        <input type="date"
                                            class="form-control"
                                            id="quota_confirm_end"
                                            name="quota_confirm_end"
                                            value="<?php echo htmlspecialchars($settings['quota_confirm_end']['setting_value'] ?? ''); ?>">
                                    </div>

                                    <!-- รายงานตัว -->
                                    <div class="col-12 mt-4">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="timeline-dot bg-danger"></div>
                                            <h6 class="mb-0 ms-2">
                                                <i class="bi bi-person-check text-danger"></i>
                                                รายงานตัว
                                            </h6>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="quota_report_date" class="form-label">
                                            วันรายงานตัว
                                        </label>
                                        <input type="date"
                                            class="form-control"
                                            id="quota_report_date"
                                            name="quota_report_date"
                                            value="<?php echo htmlspecialchars($settings['quota_report_date']['setting_value'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- รอบปกติ -->
                        <div class="card border-warning mb-4">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="mb-0">
                                    <i class="bi bi-calendar me-2"></i>
                                    รอบปกติ (Regular)
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <!-- เปิดรับสมัคร -->
                                    <div class="col-12">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="timeline-dot bg-primary"></div>
                                            <h6 class="mb-0 ms-2">
                                                <i class="bi bi-calendar-check text-primary"></i>
                                                เปิดรับสมัคร
                                            </h6>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="regular_start_date" class="form-label">
                                            วันเริ่มต้น
                                        </label>
                                        <input type="date"
                                            class="form-control"
                                            id="regular_start_date"
                                            name="regular_start_date"
                                            value="<?php echo htmlspecialchars($settings['regular_start_date']['setting_value'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="regular_end_date" class="form-label">
                                            วันสิ้นสุด
                                        </label>
                                        <input type="date"
                                            class="form-control"
                                            id="regular_end_date"
                                            name="regular_end_date"
                                            value="<?php echo htmlspecialchars($settings['regular_end_date']['setting_value'] ?? ''); ?>">
                                    </div>

                                    <!-- ตรวจสอบคุณสมบัติ -->
                                    <div class="col-12 mt-4">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="timeline-dot bg-success"></div>
                                            <h6 class="mb-0 ms-2">
                                                <i class="bi bi-clipboard-check text-success"></i>
                                                ตรวจสอบคุณสมบัติ
                                            </h6>
                                        </div>
                                        <small class="text-muted ms-4">ระบบจะตรวจสอบอัตโนมัติหลังปิดรับสมัคร</small>
                                    </div>

                                    <!-- ประกาศผล -->
                                    <div class="col-12 mt-4">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="timeline-dot bg-info"></div>
                                            <h6 class="mb-0 ms-2">
                                                <i class="bi bi-megaphone text-info"></i>
                                                ประกาศผล
                                            </h6>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="regular_announce_date" class="form-label">
                                            วันประกาศผล
                                        </label>
                                        <input type="date"
                                            class="form-control"
                                            id="regular_announce_date"
                                            name="regular_announce_date"
                                            value="<?php echo htmlspecialchars($settings['regular_announce_date']['setting_value'] ?? ''); ?>">
                                    </div>

                                    <!-- ยืนยันสิทธิ์ -->
                                    <div class="col-12 mt-4">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="timeline-dot bg-warning"></div>
                                            <h6 class="mb-0 ms-2">
                                                <i class="bi bi-hand-thumbs-up text-warning"></i>
                                                ยืนยันสิทธิ์
                                            </h6>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="regular_confirm_start" class="form-label">
                                            วันเริ่มต้น
                                        </label>
                                        <input type="date"
                                            class="form-control"
                                            id="regular_confirm_start"
                                            name="regular_confirm_start"
                                            value="<?php echo htmlspecialchars($settings['regular_confirm_start']['setting_value'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="regular_confirm_end" class="form-label">
                                            วันสิ้นสุด
                                        </label>
                                        <input type="date"
                                            class="form-control"
                                            id="regular_confirm_end"
                                            name="regular_confirm_end"
                                            value="<?php echo htmlspecialchars($settings['regular_confirm_end']['setting_value'] ?? ''); ?>">
                                    </div>

                                    <!-- รายงานตัว -->
                                    <div class="col-12 mt-4">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="timeline-dot bg-danger"></div>
                                            <h6 class="mb-0 ms-2">
                                                <i class="bi bi-person-check text-danger"></i>
                                                รายงานตัว
                                            </h6>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="regular_report_date" class="form-label">
                                            วันรายงานตัว
                                        </label>
                                        <input type="date"
                                            class="form-control"
                                            id="regular_report_date"
                                            name="regular_report_date"
                                            value="<?php echo htmlspecialchars($settings['regular_report_date']['setting_value'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Preview Timeline -->
                        <div class="card border-info">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">
                                    <i class="bi bi-eye me-2"></i>
                                    ตัวอย่างการแสดงผล (หน้าแรก)
                                </h6>
                            </div>
                            <div class="card-body">
                                <button type="button" class="btn btn-info" id="previewTimeline">
                                    <i class="bi bi-search me-2"></i>
                                    ดูตัวอย่าง Timeline
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Google Drive -->
                <div class="card shadow-sm mb-4 setting-section" id="google" style="display: none;">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="bi bi-google text-primary me-2"></i>
                            Google Drive Settings
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>คำเตือน:</strong> ต้องตั้งค่า Google API Credentials ก่อนใช้งาน
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="google_drive_folder_quota" class="form-label">
                                    Folder ID - รอบโควต้า
                                </label>
                                <input type="text"
                                    class="form-control font-monospace"
                                    id="google_drive_folder_quota"
                                    name="google_drive_folder_quota"
                                    value="<?php echo htmlspecialchars($settings['google_drive_folder_quota']['setting_value'] ?? ''); ?>"
                                    placeholder="1A2B3C4D5E6F7G8H9I0J">
                                <small class="text-muted">
                                    หา Folder ID จาก URL: drive.google.com/drive/folders/<strong>[ID นี่แหละ]</strong>
                                </small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="google_drive_folder_regular" class="form-label">
                                    Folder ID - รอบปกติ
                                </label>
                                <input type="text"
                                    class="form-control font-monospace"
                                    id="google_drive_folder_regular"
                                    name="google_drive_folder_regular"
                                    value="<?php echo htmlspecialchars($settings['google_drive_folder_regular']['setting_value'] ?? ''); ?>"
                                    placeholder="1A2B3C4D5E6F7G8H9I0J">
                                <small class="text-muted">
                                    หา Folder ID จาก URL: drive.google.com/drive/folders/<strong>[ID นี่แหละ]</strong>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- สถิติ -->
                <div class="card shadow-sm mb-4 setting-section" id="statistics" style="display: none;">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="bi bi-bar-chart text-primary me-2"></i>
                            สถิติสำหรับแสดงหน้าแรก
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>คำแนะนำ:</strong> ตัวเลขเหล่านี้จะแสดงบนหน้าแรกของเว็บไซต์
                        </div>

                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="stat_students" class="form-label">
                                    <i class="bi bi-people text-primary me-1"></i>
                                    จำนวนนักเรียน/นักศึกษา
                                </label>
                                <input type="number"
                                    class="form-control"
                                    id="stat_students"
                                    name="stat_students"
                                    value="<?php echo htmlspecialchars($settings['stat_students']['setting_value'] ?? '0'); ?>"
                                    min="0">
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="stat_departments" class="form-label">
                                    <i class="bi bi-book text-success me-1"></i>
                                    จำนวนสาขาวิชา
                                </label>
                                <input type="number"
                                    class="form-control"
                                    id="stat_departments"
                                    name="stat_departments"
                                    value="<?php echo htmlspecialchars($settings['stat_departments']['setting_value'] ?? '0'); ?>"
                                    min="0">
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="stat_teachers" class="form-label">
                                    <i class="bi bi-person-badge text-warning me-1"></i>
                                    จำนวนคณาจารย์
                                </label>
                                <input type="number"
                                    class="form-control"
                                    id="stat_teachers"
                                    name="stat_teachers"
                                    value="<?php echo htmlspecialchars($settings['stat_teachers']['setting_value'] ?? '0'); ?>"
                                    min="0">
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="stat_employment" class="form-label">
                                    <i class="bi bi-briefcase text-info me-1"></i>
                                    เปอร์เซ็นต์มีงานทำ (%)
                                </label>
                                <input type="number"
                                    class="form-control"
                                    id="stat_employment"
                                    name="stat_employment"
                                    value="<?php echo htmlspecialchars($settings['stat_employment']['setting_value'] ?? '0'); ?>"
                                    min="0"
                                    max="100">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ปุ่มบันทึก (Sticky) -->
                <div class="card shadow-sm border-primary sticky-bottom" style="bottom: 20px;">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-info-circle text-muted me-2"></i>
                                <small class="text-muted">บันทึกการตั้งค่าทั้งหมด</small>
                            </div>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-2"></i>
                                    บันทึกการตั้งค่า
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .setting-section {
        transition: all 0.3s ease;
    }

    .list-group-item {
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .list-group-item:hover {
        background-color: #f8f9fa;
    }

    .list-group-item.active {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }

    /* ✅ Make sidebar sticky and full height */
    #categoryMenu {
        position: sticky;
        top: 20px;
        max-height: calc(100vh - 40px);
        overflow-y: auto;
    }

    /* Custom scrollbar for sidebar */
    #categoryMenu::-webkit-scrollbar {
        width: 6px;
    }

    #categoryMenu::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    #categoryMenu::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 10px;
    }

    #categoryMenu::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    /* ✅ Make content area smooth scroll */
    .col-md-9 {
        scroll-behavior: smooth;
    }

    /* ✅ Adjust sticky save button */
    .sticky-bottom {
        position: sticky;
        bottom: 20px;
        z-index: 1000;
        background: white;
        border: 2px solid #0d6efd;
    }

    /* ✅ Add animation to section transitions */
    .setting-section {
        animation: fadeIn 0.3s ease-in;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* ✅ Highlight active section */
    .setting-section.active {
        border-left: 4px solid #0d6efd;
        padding-left: 16px;
    }

    /* ✅ Mobile responsive */
    @media (max-width: 768px) {
        #categoryMenu {
            position: relative;
            max-height: none;
            margin-bottom: 20px;
        }

        .sticky-bottom {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            border-radius: 0;
            margin: 0;
        }
    }

    .timeline-dot {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        display: inline-block;
        border: 3px solid white;
        box-shadow: 0 0 0 2px currentColor;
    }

    .timeline-dot.bg-primary {
        background-color: #0d6efd !important;
    }

    .timeline-dot.bg-success {
        background-color: #198754 !important;
    }

    .timeline-dot.bg-info {
        background-color: #0dcaf0 !important;
    }

    .timeline-dot.bg-warning {
        background-color: #ffc107 !important;
    }

    .timeline-dot.bg-danger {
        background-color: #dc3545 !important;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('System Settings Loaded');

        // ==================== Category Navigation ====================
        const categoryLinks = document.querySelectorAll('[data-category]');
        const sections = document.querySelectorAll('.setting-section');

        categoryLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();

                const category = this.getAttribute('data-category');

                // Update active link
                categoryLinks.forEach(l => l.classList.remove('active'));
                this.classList.add('active');

                // Show/hide sections with animation
                sections.forEach(section => {
                    section.classList.remove('active');
                    if (section.id === category) {
                        section.style.display = 'block';
                        section.classList.add('active');

                        // Smooth scroll to section
                        setTimeout(() => {
                            section.scrollIntoView({
                                behavior: 'smooth',
                                block: 'start',
                                inline: 'nearest'
                            });
                        }, 50);
                    } else {
                        section.style.display = 'none';
                    }
                });
            });
        });

        // ✅ Highlight menu based on scroll position
        function highlightMenuOnScroll() {
            let currentSection = '';
            const scrollPosition = window.scrollY + 100;

            sections.forEach(section => {
                if (section.style.display !== 'none') {
                    const sectionTop = section.offsetTop;
                    const sectionHeight = section.offsetHeight;

                    if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight) {
                        currentSection = section.id;
                    }
                }
            });

            if (currentSection) {
                categoryLinks.forEach(link => {
                    link.classList.remove('active');
                    if (link.getAttribute('data-category') === currentSection) {
                        link.classList.add('active');
                    }
                });
            }
        }

        // ✅ Add scroll event listener
        let scrollTimeout;
        window.addEventListener('scroll', function() {
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(highlightMenuOnScroll, 100);
        });

        // ==================== Toggle Status Display ====================
        const quotaSwitch = document.getElementById('quota_open');
        const regularSwitch = document.getElementById('regular_open');

        if (quotaSwitch) {
            quotaSwitch.addEventListener('change', function() {
                const status = document.getElementById('quota_status');
                if (this.checked) {
                    status.textContent = 'เปิดรับสมัคร';
                    status.className = 'badge bg-success';
                } else {
                    status.textContent = 'ปิดรับสมัคร';
                    status.className = 'badge bg-secondary';
                }
            });
        }

        if (regularSwitch) {
            regularSwitch.addEventListener('change', function() {
                const status = document.getElementById('regular_status');
                if (this.checked) {
                    status.textContent = 'เปิดรับสมัคร';
                    status.className = 'badge bg-success';
                } else {
                    status.textContent = 'ปิดรับสมัคร';
                    status.className = 'badge bg-secondary';
                }
            });
        }

        // ==================== Save Settings ====================
        const settingsForm = document.getElementById('settingsForm');
        if (settingsForm) {
            settingsForm.addEventListener('submit', function(e) {
                e.preventDefault();
                console.log('Saving settings');

                if (!settingsForm.checkValidity()) {
                    e.stopPropagation();
                    settingsForm.classList.add('was-validated');

                    // Scroll to first invalid field
                    const firstInvalid = settingsForm.querySelector(':invalid');
                    if (firstInvalid) {
                        firstInvalid.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                        firstInvalid.focus();
                    }

                    Swal.fire({
                        icon: 'warning',
                        title: 'กรุณาตรวจสอบข้อมูล',
                        text: 'มีข้อมูลบางช่องที่ยังไม่ถูกต้อง'
                    });

                    return;
                }

                Swal.fire({
                    title: 'กำลังบันทึกการตั้งค่า...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                const formData = new FormData(settingsForm);

                fetch('api/settings_update.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        return response.text().then(text => {
                            try {
                                return JSON.parse(text);
                            } catch (e) {
                                console.error('JSON Parse Error:', e);
                                console.error('Response text:', text);
                                throw new Error('Server ตอบกลับไม่ถูกต้อง');
                            }
                        });
                    })
                    .then(data => {
                        console.log('Save Response:', data);

                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'สำเร็จ!',
                                text: data.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                // Reload page to show updated values
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'เกิดข้อผิดพลาด',
                                html: data.message
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'เกิดข้อผิดพลาด',
                            html: error.message || 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้',
                            footer: '<small>กรุณาเปิด Console (F12) เพื่อดูรายละเอียด</small>'
                        });
                    });
            });
        }

        // ✅ Keyboard navigation (Arrow keys)
        document.addEventListener('keydown', function(e) {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
                return; // Don't interfere with form inputs
            }

            const activeLink = document.querySelector('.list-group-item.active');
            if (!activeLink) return;

            let nextLink = null;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                nextLink = activeLink.nextElementSibling;
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                nextLink = activeLink.previousElementSibling;
            }

            if (nextLink && nextLink.classList.contains('list-group-item')) {
                nextLink.click();
            }
        });

        // ✅ Show tooltip for keyboard shortcuts
        const categoryMenu = document.getElementById('categoryMenu');
        if (categoryMenu) {
            const tooltip = document.createElement('div');
            tooltip.className = 'text-muted text-center p-2 border-top';
            tooltip.style.fontSize = '0.75rem';
            tooltip.innerHTML = '<i class="bi bi-keyboard me-1"></i> ใช้ ↑↓ เพื่อเปลี่ยนหมวด';
            categoryMenu.appendChild(tooltip);
        }
    });

    document.getElementById('previewTimeline')?.addEventListener('click', function() {
        const quotaSchedule = {
            start: document.getElementById('quota_start_date').value,
            end: document.getElementById('quota_end_date').value,
            announce: document.getElementById('quota_announce_date').value,
            confirm_start: document.getElementById('quota_confirm_start').value,
            confirm_end: document.getElementById('quota_confirm_end').value,
            report: document.getElementById('quota_report_date').value
        };

        const regularSchedule = {
            start: document.getElementById('regular_start_date').value,
            end: document.getElementById('regular_end_date').value,
            announce: document.getElementById('regular_announce_date').value,
            confirm_start: document.getElementById('regular_confirm_start').value,
            confirm_end: document.getElementById('regular_confirm_end').value,
            report: document.getElementById('regular_report_date').value
        };

        function formatThaiDate(dateStr) {
            if (!dateStr) return '-';
            const date = new Date(dateStr);
            const thaiMonths = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.',
                'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'
            ];
            return `${date.getDate()} ${thaiMonths[date.getMonth()]} ${date.getFullYear() + 543}`;
        }

        const html = `
        <div style="text-align: left; max-height: 500px; overflow-y: auto;">
            <h5 class="text-primary mb-3"><i class="bi bi-calendar-star me-2"></i>รอบโควต้า</h5>
            <div class="timeline-preview mb-4">
                <div class="d-flex mb-2">
                    <div class="timeline-dot bg-primary me-3" style="margin-top: 3px;"></div>
                    <div>
                        <strong>เปิดรับสมัคร</strong><br>
                        <small class="text-muted">${formatThaiDate(quotaSchedule.start)} - ${formatThaiDate(quotaSchedule.end)}</small>
                    </div>
                </div>
                <div class="d-flex mb-2">
                    <div class="timeline-dot bg-success me-3" style="margin-top: 3px;"></div>
                    <div>
                        <strong>ตรวจสอบคุณสมบัติ</strong><br>
                        <small class="text-muted">ตรวจสอบอัตโนมัติ</small>
                    </div>
                </div>
                <div class="d-flex mb-2">
                    <div class="timeline-dot bg-info me-3" style="margin-top: 3px;"></div>
                    <div>
                        <strong>ประกาศผล</strong><br>
                        <small class="text-muted">${formatThaiDate(quotaSchedule.announce)}</small>
                    </div>
                </div>
                <div class="d-flex mb-2">
                    <div class="timeline-dot bg-warning me-3" style="margin-top: 3px;"></div>
                    <div>
                        <strong>ยืนยันสิทธิ์</strong><br>
                        <small class="text-muted">${formatThaiDate(quotaSchedule.confirm_start)} - ${formatThaiDate(quotaSchedule.confirm_end)}</small>
                    </div>
                </div>
                <div class="d-flex mb-2">
                    <div class="timeline-dot bg-danger me-3" style="margin-top: 3px;"></div>
                    <div>
                        <strong>รายงานตัว</strong><br>
                        <small class="text-muted">${formatThaiDate(quotaSchedule.report)}</small>
                    </div>
                </div>
            </div>
            
            <h5 class="text-warning mb-3"><i class="bi bi-calendar me-2"></i>รอบปกติ</h5>
            <div class="timeline-preview">
                <div class="d-flex mb-2">
                    <div class="timeline-dot bg-primary me-3" style="margin-top: 3px;"></div>
                    <div>
                        <strong>เปิดรับสมัคร</strong><br>
                        <small class="text-muted">${formatThaiDate(regularSchedule.start)} - ${formatThaiDate(regularSchedule.end)}</small>
                    </div>
                </div>
                <div class="d-flex mb-2">
                    <div class="timeline-dot bg-success me-3" style="margin-top: 3px;"></div>
                    <div>
                        <strong>ตรวจสอบคุณสมบัติ</strong><br>
                        <small class="text-muted">ตรวจสอบอัตโนมัติ</small>
                    </div>
                </div>
                <div class="d-flex mb-2">
                    <div class="timeline-dot bg-info me-3" style="margin-top: 3px;"></div>
                    <div>
                        <strong>ประกาศผล</strong><br>
                        <small class="text-muted">${formatThaiDate(regularSchedule.announce)}</small>
                    </div>
                </div>
                <div class="d-flex mb-2">
                    <div class="timeline-dot bg-warning me-3" style="margin-top: 3px;"></div>
                    <div>
                        <strong>ยืนยันสิทธิ์</strong><br>
                        <small class="text-muted">${formatThaiDate(regularSchedule.confirm_start)} - ${formatThaiDate(regularSchedule.confirm_end)}</small>
                    </div>
                </div>
                <div class="d-flex mb-2">
                    <div class="timeline-dot bg-danger me-3" style="margin-top: 3px;"></div>
                    <div>
                        <strong>รายงานตัว</strong><br>
                        <small class="text-muted">${formatThaiDate(regularSchedule.report)}</small>
                    </div>
                </div>
            </div>
        </div>
    `;

        Swal.fire({
            title: 'ตัวอย่างกำหนดการรับสมัคร',
            html: html,
            width: 700,
            confirmButtonText: 'ปิด'
        });
    });
</script>