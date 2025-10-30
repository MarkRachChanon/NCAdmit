<?php

/**
 * Student Detail - Admin Panel
 * รายละเอียดผู้สมัคร (Quota & Regular)
 */

// รับค่าจาก URL
$student_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$type = isset($_GET['type']) ? clean_input($_GET['type']) : 'quota';

// Validate
if ($student_id == 0) {
    echo '<div class="alert alert-danger m-4">
            <i class="bi bi-exclamation-triangle me-2"></i>
            ไม่พบข้อมูลผู้สมัคร
          </div>';
    exit();
}

// เลือกตาราง
$table = ($type == 'quota') ? 'students_quota' : 'students_regular';
$type_text = ($type == 'quota') ? 'รอบโควต้า' : 'รอบปกติ';
$type_icon = ($type == 'quota') ? 'person-lines-fill' : 'people-fill';
$type_color = ($type == 'quota') ? 'primary' : 'success';

// ดึงข้อมูลนักเรียน
$sql = "SELECT 
    s.*,
    d.name_th as department_name,
    d.code as department_code,
    d.level,
    d.study_type
FROM $table s
LEFT JOIN departments d ON s.department_id = d.id
WHERE s.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo '<div class="alert alert-danger m-4">
            <i class="bi bi-exclamation-triangle me-2"></i>
            ไม่พบข้อมูลผู้สมัคร
          </div>';
    exit();
}

$student = $result->fetch_assoc();

// คำนวณอายุ (ถ้ายังไม่มี)
if (empty($student['age']) && !empty($student['birth_date'])) {
    $birth = new DateTime($student['birth_date']);
    $today = new DateTime();
    $student['age'] = $today->diff($birth)->y;
}
?>

<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item">
                        <a href="index.php?page=dashboard">
                            <i class="bi bi-house-door"></i> หน้าแรก
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="index.php?page=<?php echo $type; ?>_list">
                            รายชื่อ<?php echo $type_text; ?>
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        รายละเอียด
                    </li>
                </ol>
            </nav>
            <h2 class="mb-0">
                <i class="bi bi-<?php echo $type_icon; ?> text-<?php echo $type_color; ?>"></i>
                รายละเอียดผู้สมัคร - <?php echo $type_text; ?>
            </h2>
        </div>
        <div>
            <a href="index.php?page=<?php echo $type; ?>_list" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>
                กลับ
            </a>
            <a href="../pages/download_<?php echo ($type == 'quota') ? 'application' : 'regular_application'; ?>_pdf.php?app_no=<?php echo $student['id']; ?>&type=<?php echo $type; ?>"
                class="btn btn-danger"
                target="_blank">
                <i class="bi bi-file-pdf me-2"></i>
                ดาวน์โหลด PDF
            </a>
        </div>
    </div>

    <!-- Status Alert -->
    <div class="alert alert-<?php echo get_status_color($student['status']); ?> alert-dismissible fade show" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-info-circle fs-4 me-3"></i>
            <div>
                <h5 class="alert-heading mb-1">
                    สถานะการสมัคร: <?php echo get_status_badge($student['status']); ?>
                </h5>
                <p class="mb-0">
                    <small>
                        <i class="bi bi-calendar me-1"></i>
                        สมัครเมื่อ: <?php echo thai_date($student['created_at'], 'full'); ?>
                        <?php if ($student['updated_at'] != $student['created_at']): ?>
                            | <i class="bi bi-pencil me-1"></i>
                            อัพเดทล่าสุด: <?php echo thai_date($student['updated_at'], 'full'); ?>
                        <?php endif; ?>
                    </small>
                </p>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>

    <!-- Quick Actions -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">
                    <i class="bi bi-lightning-fill text-warning me-2"></i>
                    การจัดการด่วน
                </h6>
                <span class="badge bg-<?php echo get_status_color($student['status']); ?>">
                    สถานะปัจจุบัน: <?php echo get_status_text($student['status']); ?>
                </span>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <?php
                $current_status = $student['status'];

                // กำหนดตัวเลือกสถานะทั้งหมด
                $status_options = [
                    'pending' => [
                        'label' => 'เปลี่ยนเป็นรอตรวจสอบ',
                        'icon' => 'bi-hourglass-split',
                        'class' => 'btn-warning'
                    ],
                    'approved' => [
                        'label' => 'อนุมัติ',
                        'icon' => 'bi-check-circle',
                        'class' => 'btn-success'
                    ],
                    'rejected' => [
                        'label' => 'ไม่อนุมัติ',
                        'icon' => 'bi-x-circle',
                        'class' => 'btn-danger'
                    ],
                    'cancelled' => [
                        'label' => 'ยกเลิก',
                        'icon' => 'bi-slash-circle',
                        'class' => 'btn-secondary'
                    ]
                ];

                // แสดงปุ่มสำหรับสถานะที่ไม่ใช่สถานะปัจจุบัน
                foreach ($status_options as $status_key => $option) {
                    if ($status_key != $current_status) {
                        echo '<button type="button" ';
                        echo "class=\"btn {$option['class']}\" ";
                        echo "onclick=\"updateStatus({$student_id}, '{$status_key}', '{$type}')\">";
                        echo "<i class=\"bi {$option['icon']} me-2\"></i>";
                        echo "{$option['label']}";
                        echo '</button>';
                    }
                }
                ?>

                <!-- ปุ่มเพิ่มหมายเหตุ (แสดงเสมอ) -->
                <button type="button"
                    class="btn btn-outline-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#noteModal">
                    <i class="bi bi-pencil me-2"></i>
                    <?php echo !empty($student['status_note']) ? 'แก้ไขหมายเหตุ' : 'เพิ่มหมายเหตุ'; ?>
                </button>

                <!-- ปุ่มลบ (เฉพาะ superadmin/admin) -->
                <?php if (in_array($_SESSION['admin_role'], ['superadmin', 'admin'])): ?>
                    <button type="button"
                        class="btn btn-outline-danger"
                        onclick="deleteStudent(<?php echo $student_id; ?>, '<?php echo $type; ?>')">
                        <i class="bi bi-trash me-2"></i>
                        ลบข้อมูล
                    </button>
                <?php endif; ?>
            </div>

            <!-- แสดงสถานะล่าสุด -->
            <div class="mt-3 pt-3 border-top">
                <small class="text-muted">
                    <i class="bi bi-info-circle me-1"></i>
                    <strong>หมายเหตุ:</strong> การเปลี่ยนสถานะจะถูกบันทึกลงในระบบทันที
                </small>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column - Personal Info -->
        <div class="col-lg-4">
            <!-- Profile Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <?php if (!empty($student['photo_path'])): ?>
                            <?php
                            $photo_path = get_upload_path($student['photo_path']);
                            if (file_exists('../' . $photo_path)):
                            ?>
                                <img src="../<?php echo htmlspecialchars($photo_path); ?>"
                                    alt="Photo"
                                    class="img-fluid rounded-circle"
                                    style="width: 150px; height: 150px; object-fit: cover; border: 5px solid #f0f0f0;">
                            <?php else: ?>
                                <div class="alert alert-warning small">ไม่พบรูปภาพ</div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="bg-<?php echo $type_color; ?> bg-opacity-10 rounded-circle mx-auto d-flex align-items-center justify-content-center"
                                style="width: 150px; height: 150px;">
                                <i class="bi bi-person fs-1 text-<?php echo $type_color; ?>"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <h4 class="mb-1">
                        <?php echo htmlspecialchars($student['prefix'] . ' ' . $student['firstname_th'] . ' ' . $student['lastname_th']); ?>
                    </h4>
                    <?php if (!empty($student['nickname'])): ?>
                        <p class="text-muted mb-3">
                            (<?php echo htmlspecialchars($student['nickname']); ?>)
                        </p>
                    <?php endif; ?>
                    <div class="d-flex justify-content-center gap-2 mb-3">
                        <?php echo get_status_badge($student['status']); ?>
                        <span class="badge bg-info">
                            <?php echo htmlspecialchars($student['level']); ?>
                        </span>
                    </div>
                    <hr>
                    <div class="text-start">
                        <p class="mb-2">
                            <i class="bi bi-card-text text-primary me-2"></i>
                            <strong>เลขที่ใบสมัคร:</strong><br>
                            <code class="ms-4"><?php echo htmlspecialchars($student['application_no']); ?></code>
                        </p>
                        <p class="mb-2">
                            <i class="bi bi-credit-card text-primary me-2"></i>
                            <strong>เลขบัตรประชาชน:</strong><br>
                            <code class="ms-4"><?php echo htmlspecialchars($student['id_card']); ?></code>
                        </p>
                        <p class="mb-2">
                            <i class="bi bi-mortarboard text-primary me-2"></i>
                            <strong>ระดับชั้น/สาขาวิชา:</strong><br>
                            <span class="ms-4">
                                <?php echo htmlspecialchars($student['apply_level']); ?> |
                                <?php echo htmlspecialchars($student['department_name']); ?>
                                [<?php echo htmlspecialchars($student['department_code']); ?>]
                            </span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Contact Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0">
                        <i class="bi bi-telephone text-primary me-2"></i>
                        ข้อมูลติดต่อ
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($student['phone'])): ?>
                        <p class="mb-2">
                            <i class="bi bi-phone me-2 text-muted"></i>
                            <strong>เบอร์มือถือ:</strong><br>
                            <a href="tel:<?php echo $student['phone']; ?>" class="ms-4">
                                <?php echo htmlspecialchars($student['phone']); ?>
                            </a>
                        </p>
                    <?php endif; ?>

                    <?php if (!empty($student['email'])): ?>
                        <p class="mb-2">
                            <i class="bi bi-envelope me-2 text-muted"></i>
                            <strong>อีเมล:</strong><br>
                            <a href="mailto:<?php echo $student['email']; ?>" class="ms-4">
                                <?php echo htmlspecialchars($student['email']); ?>
                            </a>
                        </p>
                    <?php endif; ?>

                    <?php if ($type == 'regular' && !empty($student['line_id'])): ?>
                        <p class="mb-2">
                            <i class="bi bi-line me-2 text-muted"></i>
                            <strong>Line ID:</strong><br>
                            <span class="ms-4"><?php echo htmlspecialchars($student['line_id']); ?></span>
                        </p>
                    <?php endif; ?>

                    <hr>

                    <p class="mb-0">
                        <i class="bi bi-geo-alt me-2 text-muted"></i>
                        <strong>ที่อยู่:</strong><br>
                        <span class="ms-4">
                            <?php
                            $address = trim($student['address_no']);
                            if (!empty($student['village_no'])) $address .= ' ม.' . $student['village_no'];
                            if (!empty($student['road'])) $address .= ' ถ.' . $student['road'];
                            if (!empty($student['subdistrict'])) $address .= ' ต.' . $student['subdistrict'];
                            if (!empty($student['district'])) $address .= ' อ.' . $student['district'];
                            if (!empty($student['province'])) $address .= ' จ.' . $student['province'];
                            if (!empty($student['postcode'])) $address .= ' ' . $student['postcode'];
                            echo htmlspecialchars($address);
                            ?>
                        </span>
                    </p>
                </div>
            </div>

            <!-- Documents Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0">
                        <i class="bi bi-paperclip text-primary me-2"></i>
                        เอกสารแนบ
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($student['photo_path'])): ?>
                        <div class="mb-3">
                            <strong>
                                <i class="bi bi-image text-primary me-2"></i>
                                รูปถ่าย:
                            </strong>
                            <div class="mt-2">
                                <a href="../uploads/<?php echo htmlspecialchars($student['photo_path']); ?>"
                                    target="_blank"
                                    class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye me-1"></i> ดูรูป
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($student['transcript_path'])): ?>
                        <div class="mb-3">
                            <strong>
                                <i class="bi bi-file-pdf text-danger me-2"></i>
                                ผลการเรียน:
                            </strong>
                            <div class="mt-2">
                                <a href="../uploads/<?php echo htmlspecialchars($student['transcript_path']); ?>"
                                    target="_blank"
                                    class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-download me-1"></i> ดาวน์โหลด PDF
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right Column - Detailed Info -->
        <div class="col-lg-8">
            <!-- Personal Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-person-badge text-primary me-2"></i>
                        ข้อมูลส่วนตัว
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">วันเกิด</label>
                            <p class="fw-bold mb-0">
                                <?php echo thai_date($student['birth_date'], 'full'); ?>
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">อายุ</label>
                            <p class="fw-bold mb-0">
                                <?php echo htmlspecialchars($student['age']); ?> ปี
                            </p>
                        </div>
                        <?php if ($type == 'regular' && !empty($student['birth_province'])): ?>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small">จังหวัดที่เกิด</label>
                                <p class="fw-bold mb-0">
                                    <?php echo htmlspecialchars($student['birth_province']); ?>
                                </p>
                            </div>
                        <?php endif; ?>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">สัญชาติ</label>
                            <p class="fw-bold mb-0">
                                <?php echo htmlspecialchars($student['nationality']); ?>
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">เชื้อชาติ</label>
                            <p class="fw-bold mb-0">
                                <?php echo htmlspecialchars($student['ethnicity']); ?>
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">ศาสนา</label>
                            <p class="fw-bold mb-0">
                                <?php echo htmlspecialchars($student['religion']); ?>
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">หมู่เลือด</label>
                            <p class="fw-bold mb-0">
                                <?php echo htmlspecialchars($student['blood_group'] ?? '-'); ?>
                            </p>
                        </div>
                        <?php if ($type == 'regular'): ?>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small">ส่วนสูง</label>
                                <p class="fw-bold mb-0">
                                    <?php echo htmlspecialchars($student['height'] ?? '-'); ?> ซม.
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small">น้ำหนัก</label>
                                <p class="fw-bold mb-0">
                                    <?php echo htmlspecialchars($student['weight'] ?? '-'); ?> กก.
                                </p>
                            </div>
                            <div class="col-md-12">
                                <label class="text-muted small">ความพิการ</label>
                                <p class="fw-bold mb-3">
                                    <?php
                                    if ($student['disability'] == 'มี') {
                                        echo '<span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle me-1"></i>มีความพิการ</span>';
                                        if (!empty($student['disability_type'])) {
                                            echo '<div class="col-md-12 mb-3">';
                                            echo '    <label class="text-muted small">ประเภทความพิการ</label>';
                                            echo '    <p class="fw-bold mb-0">' . htmlspecialchars($student['disability_type']) . '</p>';
                                            echo '</div>';
                                        }
                                    } else {
                                        echo '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>ไม่มีความพิการ</span>';
                                    }
                                    ?>
                                </p>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($student['talents'])): ?>
                            <div class="col-md-12">
                                <label class="text-muted small">ความสามารถพิเศษ</label>
                                <p class="fw-bold mb-0">
                                    <?php echo nl2br(htmlspecialchars($student['talents'])); ?>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Education Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-book text-primary me-2"></i>
                        ประวัติการศึกษา
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">ระดับชั้นปัจจุบัน</label>
                            <p class="fw-bold mb-0">
                                <?php echo htmlspecialchars($student['current_level'] ?? '-'); ?>
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">ชั้น/ห้อง</label>
                            <p class="fw-bold mb-0">
                                <?php echo htmlspecialchars($student['current_class'] ?? '-'); ?>
                            </p>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="text-muted small">โรงเรียน/วิทยาลัย</label>
                            <p class="fw-bold mb-0">
                                <?php echo htmlspecialchars($student['current_school'] ?? '-'); ?>
                            </p>
                            <?php if (!empty($student['school_address'])): ?>
                                <small class="text-muted">
                                    <?php echo htmlspecialchars($student['school_address']); ?>
                                </small>
                            <?php endif; ?>
                        </div>
                        <?php if ($type == 'quota' && !empty($student['current_major'])): ?>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small">สาขางาน</label>
                                <p class="fw-bold mb-0">
                                    <?php echo htmlspecialchars($student['current_major']); ?>
                                </p>
                            </div>
                        <?php endif; ?>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">เกรดเฉลี่ย (GPA)</label>
                            <p class="fw-bold mb-0">
                                <span class="badge bg-primary fs-6">
                                    <?php echo number_format($student['gpa'], 2); ?>
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">ปีการศึกษาที่จบ</label>
                            <p class="fw-bold mb-0">
                                <?php echo htmlspecialchars($student['graduation_year'] ?? '-'); ?>
                            </p>
                        </div>
                        <?php if ($type == 'quota' && !empty($student['awards'])): ?>
                            <div class="col-md-12">
                                <label class="text-muted small">รางวัลที่ได้รับ</label>
                                <p class="fw-bold mb-0">
                                    <?php echo nl2br(htmlspecialchars($student['awards'])); ?>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Parent Information (Regular only) -->
            <?php if ($type == 'regular'): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="bi bi-people text-primary me-2"></i>
                            ข้อมูลบิดา-มารดา
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Father Info -->
                        <h6 class="text-primary mb-3">
                            <i class="bi bi-person me-2"></i>ข้อมูลบิดา
                        </h6>
                        <div class="row mb-4">
                            <div class="col-md-12 mb-2">
                                <label class="text-muted small">ชื่อ-นามสกุล</label>
                                <p class="fw-bold mb-0">
                                    <?php
                                    if (!empty($student['father_firstname'])) {
                                        echo htmlspecialchars($student['father_prefix'] . ' ' . $student['father_firstname'] . ' ' . $student['father_lastname']);
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </p>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="text-muted small">สถานะ</label>
                                <p class="mb-0">
                                    <?php
                                    $status_class = [
                                        'มีชีวิต' => 'success',
                                        'เสียชีวิต' => 'danger',
                                        'ไม่ทราบ' => 'secondary'
                                    ];
                                    $class = $status_class[$student['father_status']] ?? 'secondary';
                                    echo '<span class="badge bg-' . $class . '">' . htmlspecialchars($student['father_status']) . '</span>';
                                    ?>
                                </p>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="text-muted small">อาชีพ</label>
                                <p class="fw-bold mb-0">
                                    <?php echo htmlspecialchars($student['father_occupation'] ?? '-'); ?>
                                </p>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="text-muted small">รายได้/ปี</label>
                                <p class="fw-bold mb-0">
                                    <?php
                                    if (!empty($student['father_income'])) {
                                        echo number_format($student['father_income'], 2) . ' บาท';
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </p>
                            </div>
                            <?php if (!empty($student['father_phone'])): ?>
                                <div class="col-md-12 mb-2">
                                    <label class="text-muted small">เบอร์โทรศัพท์</label>
                                    <p class="fw-bold mb-0">
                                        <a href="tel:<?php echo $student['father_phone']; ?>">
                                            <?php echo htmlspecialchars($student['father_phone']); ?>
                                        </a>
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <hr>

                        <!-- Mother Info -->
                        <h6 class="text-primary mb-3 mt-4">
                            <i class="bi bi-person me-2"></i>ข้อมูลมารดา
                        </h6>
                        <div class="row mb-4">
                            <div class="col-md-12 mb-2">
                                <label class="text-muted small">ชื่อ-นามสกุล</label>
                                <p class="fw-bold mb-0">
                                    <?php
                                    if (!empty($student['mother_firstname'])) {
                                        echo htmlspecialchars($student['mother_prefix'] . ' ' . $student['mother_firstname'] . ' ' . $student['mother_lastname']);
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </p>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="text-muted small">สถานะ</label>
                                <p class="mb-0">
                                    <?php
                                    $class = $status_class[$student['mother_status']] ?? 'secondary';
                                    echo '<span class="badge bg-' . $class . '">' . htmlspecialchars($student['mother_status']) . '</span>';
                                    ?>
                                </p>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="text-muted small">อาชีพ</label>
                                <p class="fw-bold mb-0">
                                    <?php echo htmlspecialchars($student['mother_occupation'] ?? '-'); ?>
                                </p>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="text-muted small">รายได้/ปี</label>
                                <p class="fw-bold mb-0">
                                    <?php
                                    if (!empty($student['mother_income'])) {
                                        echo number_format($student['mother_income'], 2) . ' บาท';
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </p>
                            </div>
                            <?php if (!empty($student['mother_phone'])): ?>
                                <div class="col-md-12 mb-2">
                                    <label class="text-muted small">เบอร์โทรศัพท์</label>
                                    <p class="fw-bold mb-0">
                                        <a href="tel:<?php echo $student['mother_phone']; ?>">
                                            <?php echo htmlspecialchars($student['mother_phone']); ?>
                                        </a>
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <hr>

                        <!-- Parents Status -->
                        <div class="row">
                            <div class="col-md-12">
                                <label class="text-muted small">สถานะบิดา-มารดา</label>
                                <p class="fw-bold mb-0">
                                    <?php
                                    $parents_status_class = [
                                        'อยู่ด้วยกัน' => 'success',
                                        'แยกกันอยู่' => 'warning',
                                        'หย่าร้าง' => 'danger'
                                    ];
                                    $class = $parents_status_class[$student['parents_status']] ?? 'secondary';
                                    echo '<span class="badge bg-' . $class . '">' . htmlspecialchars($student['parents_status']) . '</span>';
                                    ?>
                                </p>
                            </div>
                        </div>

                        <!-- Guardian Info (if exists) -->
                        <?php if (!empty($student['guardian_firstname'])): ?>
                            <hr>
                            <h6 class="text-primary mb-3 mt-4">
                                <i class="bi bi-person-badge me-2"></i>ข้อมูลผู้ปกครอง
                            </h6>
                            <div class="row">
                                <div class="col-md-12 mb-2">
                                    <label class="text-muted small">ชื่อ-นามสกุล</label>
                                    <p class="fw-bold mb-0">
                                        <?php echo htmlspecialchars($student['guardian_prefix'] . ' ' . $student['guardian_firstname'] . ' ' . $student['guardian_lastname']); ?>
                                    </p>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="text-muted small">ความสัมพันธ์</label>
                                    <p class="fw-bold mb-0">
                                        <?php echo htmlspecialchars($student['guardian_relation'] ?? '-'); ?>
                                    </p>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="text-muted small">อาชีพ</label>
                                    <p class="fw-bold mb-0">
                                        <?php echo htmlspecialchars($student['guardian_occupation'] ?? '-'); ?>
                                    </p>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="text-muted small">รายได้/ปี</label>
                                    <p class="fw-bold mb-0">
                                        <?php
                                        if (!empty($student['guardian_income'])) {
                                            echo number_format($student['guardian_income'], 2) . ' บาท';
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </p>
                                </div>
                                <?php if (!empty($student['guardian_phone'])): ?>
                                    <div class="col-md-12 mb-2">
                                        <label class="text-muted small">เบอร์โทรศัพท์</label>
                                        <p class="fw-bold mb-0">
                                            <a href="tel:<?php echo $student['guardian_phone']; ?>">
                                                <?php echo htmlspecialchars($student['guardian_phone']); ?>
                                            </a>
                                        </p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Status Note -->
            <?php if (!empty($student['status_note'])): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="bi bi-sticky text-warning me-2"></i>
                            หมายเหตุ
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            <?php echo nl2br(htmlspecialchars($student['status_note'])); ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Activity Timeline (if activity logs exist) -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history text-primary me-2"></i>
                        ประวัติการดำเนินการ
                    </h5>
                </div>
                <div class="card-body">
                    <?php
                    // ดึง Activity Logs
                    $logs_sql = "SELECT al.*, au.fullname as admin_name 
                                FROM activity_logs al 
                                LEFT JOIN admin_users au ON al.admin_id = au.id 
                                WHERE al.table_name = ? AND al.record_id = ? 
                                ORDER BY al.created_at DESC 
                                LIMIT 10";
                    $logs_stmt = $conn->prepare($logs_sql);
                    $logs_stmt->bind_param("si", $table, $student_id);
                    $logs_stmt->execute();
                    $logs_result = $logs_stmt->get_result();
                    ?>

                    <?php if ($logs_result && $logs_result->num_rows > 0): ?>
                        <div class="timeline">
                            <?php while ($log = $logs_result->fetch_assoc()): ?>
                                <div class="timeline-item mb-3">
                                    <div class="d-flex">
                                        <div class="timeline-icon bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                                            <i class="bi bi-circle-fill text-primary"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <p class="mb-1 fw-bold">
                                                <?php echo htmlspecialchars($log['action']); ?>
                                            </p>
                                            <small class="text-muted">
                                                <i class="bi bi-person me-1"></i>
                                                <?php echo htmlspecialchars($log['admin_name'] ?? 'ระบบ'); ?>
                                                |
                                                <i class="bi bi-clock me-1"></i>
                                                <?php echo thai_date($log['created_at'], 'full'); ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-inbox display-4 text-muted"></i>
                            <p class="text-muted mt-3 mb-0">ยังไม่มีประวัติการดำเนินการ</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Note Modal -->
<div class="modal fade" id="noteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-pencil me-2"></i>
                    เพิ่มหมายเหตุ
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="noteForm">
                    <div class="mb-3">
                        <label class="form-label">หมายเหตุ</label>
                        <textarea class="form-control"
                            id="statusNote"
                            rows="4"
                            placeholder="กรอกหมายเหตุ..."><?php echo htmlspecialchars($student['status_note'] ?? ''); ?></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    ยกเลิก
                </button>
                <button type="button" class="btn btn-primary" onclick="saveNote()">
                    <i class="bi bi-save me-2"></i>
                    บันทึก
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .timeline {
        position: relative;
    }

    .timeline-item {
        position: relative;
        padding-left: 0;
    }

    .timeline-item:not(:last-child)::before {
        content: '';
        position: absolute;
        left: 19px;
        top: 40px;
        height: calc(100% - 20px);
        width: 2px;
        background: #e9ecef;
    }

    .timeline-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
</style>

<script>
    // Update Status with Note
    async function updateStatus(id, status, type) {
        const statusText = {
            'approved': 'อนุมัติ',
            'rejected': 'ไม่อนุมัติ',
            'cancelled': 'ยกเลิก'
        };

        let note = null;

        // ถ้าไม่อนุมัติ ให้กรอกเหตุผล
        if (status === 'rejected') {
            const {
                value: reason
            } = await Swal.fire({
                title: 'เหตุผลที่ไม่อนุมัติ',
                input: 'textarea',
                inputPlaceholder: 'กรอกเหตุผล...',
                inputAttributes: {
                    'aria-label': 'เหตุผล'
                },
                showCancelButton: true,
                confirmButtonText: 'ยืนยัน',
                cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#dc3545'
            });

            if (!reason) return;
            note = reason;
        }

        const result = await Swal.fire({
            title: 'ยืนยันการเปลี่ยนสถานะ',
            text: `คุณต้องการ${statusText[status]}ใบสมัครนี้ใช่หรือไม่?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: status === 'approved' ? '#28a745' : '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'ยืนยัน',
            cancelButtonText: 'ยกเลิก'
        });

        if (result.isConfirmed) {
            showLoading('กำลังอัพเดทสถานะ...');

            try {
                const response = await fetch('api/update_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id: id,
                        status: status,
                        type: type,
                        note: note
                    })
                });

                const data = await response.json();

                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'สำเร็จ',
                        text: 'อัพเดทสถานะเรียบร้อยแล้ว',
                        confirmButtonText: 'ตกลง'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    showError('เกิดข้อผิดพลาด', data.message || 'ไม่สามารถอัพเดทสถานะได้');
                }
            } catch (error) {
                showError('เกิดข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์');
            }
        }
    }

    // Save Note
    async function saveNote() {
        const note = document.getElementById('statusNote').value;

        if (!note.trim()) {
            showWarning('กรุณากรอกหมายเหตุ', 'กรุณากรอกข้อความหมายเหตุ');
            return;
        }

        showLoading('กำลังบันทึก...');

        try {
            const response = await fetch('api/update_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id: <?php echo $student_id; ?>,
                    status: '<?php echo $student['status']; ?>',
                    type: '<?php echo $type; ?>',
                    note: note
                })
            });

            const data = await response.json();

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'สำเร็จ',
                    text: 'บันทึกหมายเหตุเรียบร้อยแล้ว',
                    confirmButtonText: 'ตกลง'
                }).then(() => {
                    location.reload();
                });
            } else {
                showError('เกิดข้อผิดพลาด', data.message || 'ไม่สามารถบันทึกหมายเหตุได้');
            }
        } catch (error) {
            showError('เกิดข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์');
        }
    }

    // Delete Student Function
    async function deleteStudent(id, type) {
        const result = await Swal.fire({
            title: 'ยืนยันการลบ',
            text: 'คุณแน่ใจหรือไม่ว่าต้องการลบข้อมูลนี้? การดำเนินการนี้ไม่สามารถย้อนกลับได้',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'ลบเลย',
            cancelButtonText: 'ยกเลิก'
        });

        if (result.isConfirmed) {
            showLoading('กำลังลบข้อมูล...');

            try {
                const response = await fetch('api/delete_student.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id: id,
                        type: type
                    })
                });

                const data = await response.json();

                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'สำเร็จ',
                        text: 'ลบข้อมูลเรียบร้อยแล้ว',
                        confirmButtonText: 'ตกลง'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    showError('เกิดข้อผิดพลาด', data.message || 'ไม่สามารถลบข้อมูลได้');
                }
            } catch (error) {
                showError('เกิดข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์');
            }
        }
    }

    // Print Page
    function printPage() {
        window.print();
    }
</script>

<style media="print">
    /* Print Styles */
    .admin-navbar,
    .admin-sidebar,
    .breadcrumb,
    .btn,
    .dropdown,
    .modal,
    .card-footer {
        display: none !important;
    }

    .card {
        box-shadow: none !important;
        border: 1px solid #dee2e6 !important;
        page-break-inside: avoid;
    }

    .admin-content {
        margin-left: 0 !important;
    }
</style>