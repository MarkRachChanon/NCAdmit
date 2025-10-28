<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;

// รับพารามิเตอร์
$application_no = isset($_GET['app_no']) ? trim($_GET['app_no']) : '';
$form_type = isset($_GET['type']) ? trim($_GET['type']) : '';

if (empty($application_no)) {
    die('ข้อมูลไม่ครบถ้วน: กรุณาระบุเลขที่ใบสมัคร');
}

try {
    // ดึงข้อมูลจาก Database
    if ($form_type === 'regular') {
        $stmt = $conn->prepare("
            SELECT 
                sr.*,
                d.name_th as department_name,
                d.level as department_level,
                d.study_type as study_type
            FROM students_regular sr
            LEFT JOIN departments d ON sr.department_id = d.id
            WHERE sr.application_no = ?
            LIMIT 1
        ");
    }

    $stmt->bind_param("s", $application_no);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        die('ไม่พบข้อมูลใบสมัคร');
    }

    $data = $result->fetch_assoc();
    $stmt->close();

    // สร้างภาพด้วย GD Library
    $image_path = createRegularApplicationImage($data);

    // สร้าง PDF จากภาพ
    $defaultConfig = (new ConfigVariables())->getDefaults();
    $fontDirs = $defaultConfig['fontDir'];

    $defaultFontConfig = (new FontVariables())->getDefaults();
    $fontData = $defaultFontConfig['fontdata'];

    $fontData['thsarabunnew'] = [
        'R' => 'THSarabunNew.ttf',
        'B' => 'THSarabunNew Bold.ttf',
        'I' => 'THSarabunNew Italic.ttf',
    ];

    $mpdf = new Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'margin_left' => 0,
        'margin_right' => 0,
        'margin_top' => 0,
        'margin_bottom' => 0,
        'fontDir' => array_merge($fontDirs, [
            __DIR__ . '/../fonts',
        ]),
        'fontdata' => $fontData,
        'default_font' => 'thsarabunnew'
    ]);

    // แปลงภาพเป็น base64 และใส่ใน PDF
    $imageData = base64_encode(file_get_contents($image_path));
    $html = '<img src="data:image/png;base64,' . $imageData . '" style="width: 210mm; height: 297mm;" />';

    $mpdf->WriteHTML($html);

    // ลบไฟล์ชั่วคราว
    unlink($image_path);

    // ชื่อไฟล์
    $filename = 'ใบสมัครรอบปกติ_' . $application_no . '_' . date('Ymd_His') . '.pdf';

    // ส่งออก PDF
    $mpdf->Output($filename, 'D');
} catch (Exception $e) {
    error_log("PDF Error: " . $e->getMessage());
    die('เกิดข้อผิดพลาดในการสร้าง PDF: ' . $e->getMessage());
}

// ==================== Functions ====================

function createRegularApplicationImage($data)
{
    // ขนาด A4 ที่ 200 DPI
    $width = 1654;
    $height = 2339;
    $margin_left = 100;
    $margin_right = 100;

    // สร้างภาพพื้นหลังสีขาว
    $image = imagecreatetruecolor($width, $height);
    $white = imagecolorallocate($image, 255, 255, 255);
    $black = imagecolorallocate($image, 0, 0, 0);
    $gray = imagecolorallocate($image, 150, 150, 150);

    imagefill($image, 0, 0, $white);
    imageantialias($image, true);

    // โหลดฟอนต์ไทย
    $font = __DIR__ . '/../fonts/THSarabunNew.ttf';
    $fontBold = __DIR__ . '/../fonts/THSarabunNew Bold.ttf';

    if (!file_exists($font)) {
        $font = '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf';
    }

    $y = 80;

    // ===== โลโก้ =====
    $logo_path = __DIR__ . '/../assets/images/logo.png';
    if (file_exists($logo_path)) {
        $logo = imagecreatefrompng($logo_path);
        $logo_width = 180;
        $logo_height = 180;
        $logo_x = ($width - $logo_width) / 2;
        imagecopyresampled($image, $logo, $logo_x, 60, 0, 0, $logo_width, $logo_height, imagesx($logo), imagesy($logo));
        imagedestroy($logo);
    }

    // กรอบรูปถ่าย 1 นิ้ว (ขวาบน)
    $photo_size = 200; // 1 inch = ~25.4mm at 200 DPI
    $photo_x = $width - $margin_right - $photo_size - 20;
    $photo_y = 60;
    imagesetthickness($image, 1);
    imagerectangle($image, $photo_x, $photo_y, $photo_x + $photo_size, $photo_y + $photo_size, $black);
    imagettftext($image, 20, 0, $photo_x + 30, $photo_y + $photo_size / 2, $black, $font, 'รูปถ่าย ขนาด 1 นิ้ว');

    $y = 280;

    // ===== เลขบัตรประชาชนและเลขใบสมัคร =====
    $x = $margin_left;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'เลขบัตรประจำตัวประชาชน:');
    $x += 240;
    drawDottedLine($image, $x, $y + 2, 250, $black);
    imagettftext($image, 24, 0, $x, $y, $black, $font, $data['id_card'] ?? '');

    $x += 270;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'เลขบัตรประจำตัวผู้สมัครออนไลน์:');
    $x += 295;
    drawDottedLine($image, $x, $y + 2, $width - $margin_right - $x, $black);
    imagettftext($image, 24, 0, $x, $y, $black, $font, $data['application_no'] ?? '');
    $y += 50;

    // ===== หัวข้อ =====
    $text = 'ใบสมัครเข้าศึกษาต่อวิทยาลัยอาชีวศึกษานครปฐม (Online)';
    $bbox = imagettfbbox(32, 0, $fontBold, $text);
    $text_width = $bbox[2] - $bbox[0];
    $x_center = ($width - $text_width) / 2;
    imagettftext($image, 32, 0, $x_center, $y, $black, $fontBold, $text);
    $y += 60;

    // ===== วันที่ =====
    $created_date = formatDateThaiFull($data['created_at']);
    $parts = explode(' ', $created_date);

    $x = $width - 550;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'วันที่:');
    $x += 50;
    drawDottedLine($image, $x, $y + 2, 60, $black);
    imagettftext($image, 24, 0, $x, $y, $black, $font, $parts[0] ?? '');

    $x += 75;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'เดือน:');
    $x += 60;
    drawDottedLine($image, $x, $y + 2, 120, $black);
    imagettftext($image, 24, 0, $x, $y, $black, $font, $parts[1] ?? '');

    $x += 135;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'ปี พ.ศ.');
    $x += 70;
    drawDottedLine($image, $x, $y + 2, 80, $black);
    imagettftext($image, 24, 0, $x, $y, $black, $font, $parts[3] ?? '');
    $y += 60;

    // ===== ข้อ 1: ชื่อผู้สมัคร =====
    $x = $margin_left;
    imagettftext($image, 24, 0, $x, $y, $black, $font, '1. ชื่อผู้สมัคร:');
    $x += 120;
    $fullname = ($data['prefix'] ?? '') . '' . ($data['firstname_th'] ?? '');
    drawDottedLine($image, $x, $y + 2, 350, $black);
    imagettftext($image, 24, 0, $x, $y, $black, $font, $fullname);

    $x += 365;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'นามสกุล:');
    $x += 85;
    drawDottedLine($image, $x, $y + 2, 320, $black);
    imagettftext($image, 24, 0, $x, $y, $black, $font, $data['lastname_th'] ?? '');

    $x += 335;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'ชื่อเล่น:');
    $x += 70;
    drawDottedLine($image, $x, $y + 2, $width - $margin_right - $x, $black);
    imagettftext($image, 24, 0, $x, $y, $black, $font, $data['nickname'] ?? '');
    $y += 50;

    // ===== จังหวัดที่เกิด วันเกิด อายุ ส่วนสูง น้ำหนัก (บรรทัดที่แก้ไข) =====
    $x = $margin_left + 40;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'จังหวัดที่เกิด:');
    $x += 115;
    drawDottedLine($image, $x, $y + 2, 180, $black);
    imagettftext($image, 24, 0, $x, $y, $black, $font, $data['birth_province'] ?? '');

    $birth_date = formatDateThaiFull($data['birth_date']);
    $birth_parts = explode(' ', $birth_date);

    $x += 195;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'เกิดวันที่:');
    $x += 85;
    drawDottedLine($image, $x, $y + 2, 50, $black);
    imagettftext($image, 24, 0, $x, $y, $black, $font, $birth_parts[0] ?? '');

    $x += 65;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'เดือน:');
    $x += 60;
    drawDottedLine($image, $x, $y + 2, 100, $black);
    imagettftext($image, 24, 0, $x, $y, $black, $font, $birth_parts[1] ?? '');

    $x += 115;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'พ.ศ.');
    $x += 50;
    drawDottedLine($image, $x, $y + 2, 70, $black);
    imagettftext($image, 24, 0, $x, $y, $black, $font, $birth_parts[3] ?? '');

    $x += 85;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'อายุ:');
    $x += 50;
    drawDottedLine($image, $x, $y + 2, 50, $black);
    imagettftext($image, 24, 0, $x, $y, $black, $font, $data['age'] ?? '');

    $x += 65;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'ปี ส่วนสูง:');
    $x += 85;
    drawDottedLine($image, $x, $y + 2, 60, $black);
    imagettftext($image, 24, 0, $x, $y, $black, $font, $data['height'] ?? '');

    // *** ส่วนที่แก้ไข: เพิ่ม (เซนติเมตร) เข้าไปในข้อความ และปรับตำแหน่ง $x ***
    $x += 75;
    // เพิ่ม "(เซนติเมตร)" กลับเข้าไปในข้อความ
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'ซ.ม.(เซนติเมตร) น้ำหนัก:');

    // ปรับระยะ $x$ ให้เหมาะสมกับข้อความที่ยาวขึ้น (จากเดิม 140 ปรับเป็น 250)
    $x += 215;

    drawDottedLine($image, $x, $y + 2, 60, $black);
    imagettftext($image, 24, 0, $x, $y, $black, $font, $data['weight'] ?? '');

    $x += 75;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'กิโลกรัม');
    // *** สิ้นสุดส่วนที่แก้ไข ***
    $y += 50;

    // ===== เชื้อชาติ สัญชาติ ศาสนา กรุ๊ปเลือด (บรรทัดที่แก้ไข) =====
    $x = $margin_left + 40;
    // บรรทัดนี้เริ่มต้นด้วย 'เชื้อชาติ' เหมือนเดิม
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'เชื้อชาติ:');
    $x += 78;
    drawDottedLine($image, $x, $y + 2, 100, $black);
    imagettftext($image, 24, 0, $x, $y, $black, $font, $data['ethnicity'] ?? '');

    $x += 115;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'สัญชาติ:');
    $x += 75;
    drawDottedLine($image, $x, $y + 2, 100, $black);
    imagettftext($image, 24, 0, $x, $y, $black, $font, $data['nationality'] ?? '');

    $x += 115;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'ศาสนา:');
    $x += 70;
    drawDottedLine($image, $x, $y + 2, 110, $black);
    imagettftext($image, 24, 0, $x, $y, $black, $font, $data['religion'] ?? '');

    $x += 125;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'กรุ๊ปเลือด:');
    $x += 90;
    drawDottedLine($image, $x, $y + 2, $width - $margin_right - $x, $black);
    imagettftext($image, 24, 0, $x, $y, $black, $font, $data['blood_group'] ?? '');
    // *** สิ้นสุดส่วนที่แก้ไข 2 ***
    $y += 50;

    // ===== ความสามารถพิเศษ =====
    $x = $margin_left + 40;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'ความสามารถพิเศษ:');
    $x += 170;
    drawDottedLine($image, $x, $y + 2, $width - $margin_right - $x, $black);
    imagettftext($image, 24, 0, $x, $y, $black, $font, $data['talents'] ?? '');
    $y += 50;

    // ===== ความพิการ =====
    $x = $margin_left + 40;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'ความพิการ:');
    $x += 110;

    $disability = $data['disability'] ?? 'ไม่มี';
    drawCheckbox($image, $x, $y - 20, $disability === 'ไม่มี', $black);
    $x += 35;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'ไม่มี');

    $x += 60;
    drawCheckbox($image, $x, $y - 20, $disability === 'มี', $black);
    $x += 35;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'มี');

    $x += 35;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'ประเภท:');
    $x += 80;
    drawDottedLine($image, $x, $y + 2, $width - $margin_right - $x, $black);
    imagettftext($image, 24, 0, $x, $y, $black, $font, $data['disability_type'] ?? '');
    $y += 60;

    // ===== ข้อ 2: ที่อยู่ =====
    $x = $margin_left;
    imagettftext($image, 24, 0, $x, $y, $black, $font, '2. ที่อยู่ปัจจุบัน');
    $y += 45;

    $x = $margin_left + 40;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'บ้านเลขที่:');
    $x += 100;
    drawDottedLine($image, $x, $y + 2, 80, $black);
    imagettftext($image, 24, 0, $x, $y, $black, $font, $data['address_no'] ?? '');

    $x += 95;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'หมู่ที่:');
    $x += 60;
    drawDottedLine($image, $x, $y + 2, 60, $black);
    imagettftext($image, 24, 0, $x, $y, $black, $font, $data['village_no'] ?? '');

    $x += 75;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'ชื่อหมู่บ้าน:');
    $x += 105;
    drawDottedLine($image, $x, $y + 2, 400, $black);
    imagettftext($image, 24, 0, $x, $y, $black, $font, $data['village_name'] ?? '');

    $x += 415;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'ซอย:');
    $x += 45;
    drawDottedLine($image, $x, $y + 2, $width - $margin_right - $x, $black);
    imagettftext($image, 24, 0, $x, $y, $black, $font, $data['soi'] ?? '');
    $y += 45;

    $x = $margin_left + 40;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'ถนน:');
    $x += 50;
    drawDottedLine($image, $x, $y + 2, 200, $black);
    imagettftext($image, 24, 0, $x, $y, $black, $font, $data['road'] ?? '');

    $x += 215;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'ตำบล/แขวง:');
    $x += 110;
    drawDottedLine($image, $x, $y + 2, 220, $black);
    imagettftext($image, 24, 0, $x, $y, $black, $font, $data['subdistrict'] ?? '');

    $x += 235;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'อำเภอ/เขต:');
    $x += 105;
    drawDottedLine($image, $x, $y + 2, $width - $margin_right - $x, $black);
    imagettftext($image, 24, 0, $x, $y, $black, $font, $data['district'] ?? '');
    $y += 45;

    $x = $margin_left + 40;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'จังหวัด:');
    $x += 70;
    drawDottedLine($image, $x, $y + 2, 200, $black);
    imagettftext($image, 24, 0, $x, $y, $black, $font, $data['province'] ?? '');

    $x += 215;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'รหัสไปรษณีย์:');
    $x += 125;
    drawDottedLine($image, $x, $y + 2, 100, $black);
    imagettftext($image, 24, 0, $x, $y, $black, $font, $data['postcode'] ?? '');

    $x += 115;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'โทรศัพท์บ้าน:');
    $x += 125;
    drawDottedLine($image, $x, $y + 2, $width - $margin_right - $x, $black);
    imagettftext($image, 24, 0, $x, $y, $black, $font, $data['phone_home'] ?? '');
    $y += 45;

    $x = $margin_left + 40;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'โทรศัพท์มือถือ:');
    $x += 130;
    drawDottedLine($image, $x, $y + 2, 180, $black);
    imagettftext($image, 24, 0, $x, $y, $black, $font, $data['phone'] ?? '');

    $x += 195;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'Line ID:');
    $x += 82;
    drawDottedLine($image, $x, $y + 2, 180, $black);
    imagettftext($image, 24, 0, $x, $y, $black, $font, $data['line_id'] ?? '');

    $x += 195;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'Email:');
    $x += 70;
    drawDottedLine($image, $x, $y + 2, $width - $margin_right - $x, $black);
    imagettftext($image, 24, 0, $x, $y, $black, $font, $data['email'] ?? '');
    $y += 60;

    // ===== ข้อ 3: ข้อมูลบิดา =====
    $x = $margin_left;
    imagettftext($image, 24, 0, $x, $y, $black, $font, '3. ข้อมูลครอบครัว');
    $y += 45;

    $x = $margin_left + 40;
    $father_fullname = ($data['father_prefix'] ?? '') . '' . ($data['father_firstname'] ?? '') . ' ' . ($data['father_lastname'] ?? '');
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'ชื่อ-นามสกุล:');
    $x += 115;
    drawDottedLine($image, $x, $y + 2, 500, $black); // ปรับจาก 300 เป็น 500
    imagettftext($image, 24, 0, $x, $y, $black, $font, $father_fullname);

    $x += 515; // ปรับจาก 315 เป็น 515 (เพื่อให้ระยะห่างเท่าเดิม)
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'อาชีพ:');
    $x += 60;
    drawDottedLine($image, $x, $y + 2, 430, $black);
    imagettftext($image, 24, 0, $x, $y, $black, $font, $data['father_occupation'] ?? '');

    $x += 439;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'รายได้ต่อปี:');
    $x += 110;
    drawDottedLine($image, $x, $y + 2, $width - $margin_right - $x, $black);
    imagettftext($image, 24, 0, $x, $y, $black, $font, number_format($data['father_income'] ?? 0, 2));
    $y += 45;

    $x = $margin_left + 40;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'บาท โทรศัพท์:');
    $x += 125;
    drawDottedLine($image, $x, $y + 2, 180, $black);
    imagettftext($image, 24, 0, $x, $y, $black, $font, $data['father_phone'] ?? '');

    $x += 195;
    $father_status = $data['father_status'] ?? 'มีชีวิต';
    drawCheckbox($image, $x, $y - 20, $father_status === 'มีชีวิต', $black);
    $x += 35;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'มีชีวิต');

    $x += 75;
    drawCheckbox($image, $x, $y - 20, $father_status === 'เสียชีวิต', $black);
    $x += 35;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'เสียชีวิต');

    $x += 85;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'ความพิการ:');
    $x += 105;
    $father_disability = $data['father_disability'] ?? 'ไม่มี';
    drawCheckbox($image, $x, $y - 20, $father_disability === 'ไม่มี', $black);
    $x += 35;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'ไม่มี');

    $x += 60;
    drawCheckbox($image, $x, $y - 20, $father_disability === 'มี', $black);
    $x += 35;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'มี');

    $x += 40;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'ประเภท:');
    $x += 80;
    drawDottedLine($image, $x, $y + 2, $width - $margin_right - $x, $black);
    imagettftext($image, 24, 0, $x, $y, $black, $font, $data['father_disability_type'] ?? '');
    $y += 60;

    // ===== ข้อมูลมารดา =====
    $x = $margin_left + 40;
    $mother_fullname = ($data['mother_prefix'] ?? '') . '' . ($data['mother_firstname'] ?? '') . ' ' . ($data['mother_lastname'] ?? '');
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'ชื่อ-นามสกุล มารดา:');
    $x += 178;
    drawDottedLine($image, $x, $y + 2, 500, $black); // ปรับจาก 300 เป็น 500
    imagettftext($image, 24, 0, $x, $y, $black, $font, $mother_fullname);

    $x += 515; // ปรับจาก 315 เป็น 515 (เพื่อให้ระยะห่างเท่าเดิม)
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'อาชีพ:');
    $x += 60;
    drawDottedLine($image, $x, $y + 2, 390, $black);
    imagettftext($image, 24, 0, $x, $y, $black, $font, $data['mother_occupation'] ?? '');

    $x += 399;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'รายได้ต่อปี:');
    $x += 110;
    drawDottedLine($image, $x, $y + 2, $width - $margin_right - $x, $black);
    imagettftext($image, 24, 0, $x, $y, $black, $font, number_format($data['mother_income'] ?? 0, 2));
    $y += 45;

    $x = $margin_left + 40;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'บาท โทรศัพท์:');
    $x += 125;
    drawDottedLine($image, $x, $y + 2, 180, $black);
    imagettftext($image, 24, 0, $x, $y, $black, $font, $data['mother_phone'] ?? '');

    $x += 195;
    $mother_status = $data['mother_status'] ?? 'มีชีวิต';
    drawCheckbox($image, $x, $y - 20, $mother_status === 'มีชีวิต', $black);
    $x += 35;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'มีชีวิต');

    $x += 75;
    drawCheckbox($image, $x, $y - 20, $mother_status === 'เสียชีวิต', $black);
    $x += 35;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'เสียชีวิต');

    $x += 85;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'ความพิการ:');
    $x += 105;
    $mother_disability = $data['mother_disability'] ?? 'ไม่มี';
    drawCheckbox($image, $x, $y - 20, $mother_disability === 'ไม่มี', $black);
    $x += 35;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'ไม่มี');

    $x += 60;
    drawCheckbox($image, $x, $y - 20, $mother_disability === 'มี', $black);
    $x += 35;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'มี');

    $x += 40;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'ประเภท:');
    $x += 80;
    drawDottedLine($image, $x, $y + 2, $width - $margin_right - $x, $black);
    imagettftext($image, 24, 0, $x, $y, $black, $font, $data['mother_disability_type'] ?? '');
    $y += 45;

    // สถานะบิดา-มารดา
    $x = $margin_left + 40;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'บิดา-มารดา:');
    $x += 115;
    $parents_status = $data['parents_status'] ?? 'อยู่ด้วยกัน';
    drawCheckbox($image, $x, $y - 20, $parents_status === 'อยู่ด้วยกัน', $black);
    $x += 35;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'อยู่ด้วยกัน');

    $x += 105;
    drawCheckbox($image, $x, $y - 20, $parents_status === 'แยกกันอยู่', $black);
    $x += 35;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'แยกกันอยู่');

    $x += 105;
    drawCheckbox($image, $x, $y - 20, $parents_status === 'หย่าร้าง', $black);
    $x += 35;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'หย่าร้าง');
    $y += 60;

    // ===== ข้อมูลผู้ปกครอง =====
    $x = $margin_left + 40;
    $guardian_fullname = ($data['guardian_prefix'] ?? '') . '' . ($data['guardian_firstname'] ?? '') . ' ' . ($data['guardian_lastname'] ?? '');
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'ชื่อ-นามสกุล ผู้ปกครอง:');
    $x += 200;
    drawDottedLine($image, $x, $y + 2, 450, $black);
    imagettftext($image, 24, 0, $x, $y, $black, $font, $guardian_fullname);

    $x += 465;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'ความสัมพันธ์:');
    $x += 125;
    drawDottedLine($image, $x, $y + 2, $width - $margin_right - $x, $black);
    imagettftext($image, 24, 0, $x, $y, $black, $font, $data['guardian_relation'] ?? '');
    $y += 45;

    $x = $margin_left + 40;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'อาชีพ:');
    $x += 60;
    drawDottedLine($image, $x, $y + 2, 400, $black);
    imagettftext($image, 24, 0, $x, $y, $black, $font, $data['guardian_occupation'] ?? '');

    $x += 415;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'รายได้ต่อปี:');
    $x += 110;
    drawDottedLine($image, $x, $y + 2, 180, $black);
    imagettftext($image, 24, 0, $x, $y, $black, $font, number_format($data['guardian_income'] ?? 0, 2));

    $x += 195;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'บาท โทรศัพท์:');
    $x += 125;
    drawDottedLine($image, $x, $y + 2, $width - $margin_right - $x, $black);
    imagettftext($image, 24, 0, $x, $y, $black, $font, $data['guardian_phone'] ?? '');
    $y += 60;

    // ===== ข้อ 4: ข้อมูลการศึกษา =====
    $x = $margin_left;
    imagettftext($image, 24, 0, $x, $y, $black, $font, '4. ข้อมูลด้านการศึกษา:');
    $x += 205;
    drawDottedLine($image, $x, $y + 2, $width - $margin_right - $x, $black);
    $full_level_name = ($data['current_level'] ?? '') . getLevelFullName($data['current_level'] ?? '');
    imagettftext($image, 24, 0, $x, $y, $black, $font, $full_level_name);
    $y += 45;

    $x = $margin_left + 40;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'สถานศึกษา:');
    $x += 110;
    drawDottedLine($image, $x, $y + 2, $width - $margin_right - $x, $black);
    imagettftext($image, 24, 0, $x, $y, $black, $font, $data['current_school'] ?? '');
    $y += 45;

    $x = $margin_left + 40;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'ที่อยู่สถานศึกษา:');
    $x += 145;
    $school_address = $data['school_address'] ?? '';
    drawDottedLine($image, $x, $y + 2, $width - $margin_right - $x, $black);
    imagettftext($image, 24, 0, $x, $y, $black, $font, $school_address);
    $y += 45;

    $x = $margin_left + 40;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'เกรดเฉลี่ยสะสม (GPA):');
    $x += 200;
    drawDottedLine($image, $x, $y + 2, 140, $black);
    imagettftext($image, 24, 0, $x, $y, $black, $font, $data['gpa'] ?? '');

    $x += 145;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'ปีการศึกษา:');
    $x += 110;
    drawDottedLine($image, $x, $y + 2, $width - $margin_right - $x, $black);
    imagettftext($image, 24, 0, $x, $y, $black, $font, $data['graduation_year'] ?? '');
    $y += 60;

    // ===== ข้อ 5: ความประสงค์ =====
    $x = $margin_left;
    imagettftext($image, 24, 0, $x, $y, $black, $font, '5. ข้าพเจ้าประสงค์จะสมัครศึกษาต่อในระดับ:');
    $x += 380;
    drawDottedLine($image, $x, $y + 2, 370, $black);
    $full_apply_name = ($data['apply_level'] ?? '') . getLevelFullName($data['apply_level'] ?? '');
    imagettftext($image, 24, 0, $x, $y, $black, $font, $full_apply_name);

    $x += 385;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'สาขาวิชา/สาขางาน:');
    $x += 175;
    drawDottedLine($image, $x, $y + 2, $width - $margin_right - $x, $black);
    imagettftext($image, 24, 0, $x, $y, $black, $font, $data['department_name'] ?? '');
    $y += 60;

    // ===== ข้อรับรอง =====
    $x = $margin_left;
    drawCheckbox($image, $x, $y - 20, true, $black);
    $x += 35;
    $text = 'ข้าพเจ้าขอรับรองว่าข้อความดังกล่าวข้างต้นเป็นความจริงทุกประการ';
    imagettftext($image, 22, 0, $x, $y, $black, $fontBold, $text);
    $y += 60;

    // ===== ลายเซ็น =====
    $signature_line_text = 'ลงชื่อ.......................................................ผู้สมัคร';
    $x_signature_start = $width - 650;
    imagettftext($image, 22, 0, $x_signature_start, $y, $black, $font, $signature_line_text);
    $y += 45;

    $fullname = ($data['prefix'] ?? '') . '' . ($data['firstname_th'] ?? '') . ' ' . ($data['lastname_th'] ?? '');
    $full_name_in_parens = '( ' . $fullname . ' )';

    $bbox_name = imagettfbbox(22, 0, $font, $full_name_in_parens);
    $name_width = $bbox_name[2] - $bbox_name[0];
    $bbox_sign = imagettfbbox(22, 0, $font, $signature_line_text);
    $sign_line_width = $bbox_sign[2] - $bbox_sign[0];

    $x_name_center = $x_signature_start + ($sign_line_width / 2) - ($name_width / 2);
    imagettftext($image, 22, 0, $x_name_center, $y, $black, $font, $full_name_in_parens);
    $y += 45;

    $x = $width - 550;
    imagettftext($image, 22, 0, $x, $y, $black, $font, '.......... / .......... / ..........');
    $y += 35;

    // ===== ข้อตกลง =====
    $x = $margin_left + 40;
    $agreement_text = 'หากตรวจสอบพบว่า คุณวุฒิและคุณสมบัติของข้าพเจ้าไม่ตรงตามที่สถานศึกษากำหนดหรือตรวจสอบพบว่ามีการปลอมแปลงเอกสาร ข้าพเจ้ายินยอมให้ทาง สถานศึกษาเพิกถอนสิทธิ์การสมัครสอบคัดเลือกและการมอบตัว';
    imagettftext($image, 18, 0, $x, $y, $black, $font, $agreement_text);
    $y += 30;

    $agreement_text2 = 'ทุกประการและไม่ขอรับเงินค่าสอบคัดเลือกและค่ามอบตัวคืนไม่ว่ากรณีใด และข้าพเจ้ายินยอมให้ สถานศึกษานำข้อมูลในใบสมัครของข้าพเจ้าไปใช้ประโยชน์ตามที่สถานศึกษาพิจารณาเห็นสมควร';
    imagettftext($image, 18, 0, $x, $y, $black, $font, $agreement_text2);
    $y += 20;

    // ===== ตารางส่วนเจ้าหน้าที่ =====
    imagesetthickness($image, 1);
    $table_y = $y;
    $table_width = $width - $margin_left - $margin_right;
    $col_width = intval($table_width / 2);
    $row_height = 40;

    // Header - ส่วนที่ 1 และ 2 (วาดเฉพาะเส้นบนและข้าง ไม่มีเส้นล่าง)
    imageline($image, $margin_left, $table_y, $width - $margin_right, $table_y, $black);
    imageline($image, $margin_left, $table_y, $margin_left, $table_y + $row_height, $black);
    imageline($image, $margin_left + $col_width, $table_y, $margin_left + $col_width, $table_y + $row_height, $black);
    imageline($image, $width - $margin_right, $table_y, $width - $margin_right, $table_y + $row_height, $black);

    // ข้อความชิดซ้าย
    imagettftext($image, 22, 0, $margin_left + 10, $table_y + 28, $black, $font, 'ส่วนที่ 1 เฉพาะเจ้าหน้าที่');
    imagettftext($image, 22, 0, $margin_left + $col_width + 10, $table_y + 28, $black, $font, 'ส่วนที่ 2 เฉพาะเจ้าหน้าที่');

    // ลายเซ็นกรรมการ
    $table_y += $row_height;
    imageline($image, $margin_left, $table_y, $margin_left, $table_y + $row_height, $black);
    imageline($image, $margin_left + $col_width, $table_y, $margin_left + $col_width, $table_y + $row_height, $black);
    imageline($image, $width - $margin_right, $table_y, $width - $margin_right, $table_y + $row_height, $black);

    $text = 'ลงชื่อ .......................................... กรรมการรับเงิน';
    $bbox = imagettfbbox(20, 0, $font, $text);
    $text_width = $bbox[2] - $bbox[0];
    $x_center = intval($margin_left + ($col_width - $text_width) / 2);
    imagettftext($image, 20, 0, $x_center, $table_y + 28, $black, $font, $text);

    $text = 'ลงชื่อ .......................................... กรรมการรับสมัคร';
    $bbox = imagettfbbox(20, 0, $font, $text);
    $text_width = $bbox[2] - $bbox[0];
    $x_center = intval($margin_left + $col_width + ($col_width - $text_width) / 2);
    imagettftext($image, 20, 0, $x_center, $table_y + 28, $black, $font, $text);

    $table_y += $row_height;
    imageline($image, $margin_left, $table_y, $margin_left, $table_y + $row_height, $black);
    imageline($image, $margin_left + $col_width, $table_y, $margin_left + $col_width, $table_y + $row_height, $black);
    imageline($image, $width - $margin_right, $table_y, $width - $margin_right, $table_y + $row_height, $black);

    $text = '(.....................................................)';
    $bbox = imagettfbbox(20, 0, $font, $text);
    $text_width = $bbox[2] - $bbox[0];
    $x_center = intval($margin_left + ($col_width - $text_width) / 2);
    imagettftext($image, 20, 0, $x_center, $table_y + 28, $black, $font, $text);

    $x_center = intval($margin_left + $col_width + ($col_width - $text_width) / 2);
    imagettftext($image, 20, 0, $x_center, $table_y + 28, $black, $font, $text);

    $table_y += $row_height;
    imageline($image, $margin_left, $table_y, $margin_left, $table_y + $row_height, $black);
    imageline($image, $margin_left + $col_width, $table_y, $margin_left + $col_width, $table_y + $row_height, $black);
    imageline($image, $width - $margin_right, $table_y, $width - $margin_right, $table_y + $row_height, $black);
    imageline($image, $margin_left, $table_y + $row_height, $width - $margin_right, $table_y + $row_height, $black);

    $text = '.......... / .......... / ..........';
    $bbox = imagettfbbox(20, 0, $font, $text);
    $text_width = $bbox[2] - $bbox[0];
    $x_center = intval($margin_left + ($col_width - $text_width) / 2);
    imagettftext($image, 20, 0, $x_center, $table_y + 28, $black, $font, $text);

    $x_center = intval($margin_left + $col_width + ($col_width - $text_width) / 2);
    imagettftext($image, 20, 0, $x_center, $table_y + 28, $black, $font, $text);

    // บัตรประจำตัวผู้สมัครสอบ - Header
    $table_y += $row_height;
    $card_row_height = 35;

    // วาดเฉพาะเส้นบน ซ้าย กลาง ขวา (ไม่มีเส้นล่าง)
    imageline($image, $margin_left, $table_y, $width - $margin_right, $table_y, $black);
    imageline($image, $margin_left, $table_y, $margin_left, $table_y + $card_row_height, $black);
    imageline($image, $margin_left + $col_width, $table_y, $margin_left + $col_width, $table_y + $card_row_height, $black);
    imageline($image, $width - $margin_right, $table_y, $width - $margin_right, $table_y + $card_row_height, $black);

    $text = 'บัตรประจำตัวผู้สมัครสอบ (ส่วนของสถานศึกษา)';
    $bbox = imagettfbbox(20, 0, $font, $text);
    $text_width = $bbox[2] - $bbox[0];
    $x_center = intval($margin_left + ($col_width - $text_width) / 2);
    imagettftext($image, 20, 0, $x_center, $table_y + 25, $black, $font, $text);

    $text = 'บัตรประจำตัวผู้สมัครสอบ (ส่วนของผู้สมัคร)';
    $bbox = imagettfbbox(20, 0, $font, $text);
    $text_width = $bbox[2] - $bbox[0];
    $x_center = intval($margin_left + $col_width + ($col_width - $text_width) / 2);
    imagettftext($image, 20, 0, $x_center, $table_y + 25, $black, $font, $text);

    // รายละเอียดผู้สมัคร + รูปถ่าย
    $table_y += $card_row_height;
    $detail_height = 145;
    $half_col = intval($col_width / 2);

    // วาดเฉพาะเส้นซ้าย กลาง ขวา (ไม่มีเส้นบนและล่าง)
    imageline($image, $margin_left, $table_y, $margin_left, $table_y + $detail_height, $black);
    imageline($image, $margin_left + $col_width, $table_y, $margin_left + $col_width, $table_y + $detail_height, $black);
    imageline($image, $width - $margin_right, $table_y, $width - $margin_right, $table_y + $detail_height, $black);

    // ส่วนซ้าย - รายละเอียด + รูปถ่าย
    $detail_y = $table_y + 25;
    $line_height = 24;

    // รายละเอียดฝั่งซ้าย
    $details = [
        'เลขที่ผู้สมัครสอบ : ' . ($data['application_no'] ?? ''),
        'ชื่อ-สกุล : ' . ($data['prefix'] ?? '') . ($data['firstname_th'] ?? '') . ' ' . ($data['lastname_th'] ?? ''),
        'ระดับชั้น : ' . ($data['apply_level'] ?? ''),
        'สาขาวิชา/สาขางาน :',
        ($data['department_name'] ?? ''),
    ];

    foreach ($details as $detail) {
        imagettftext($image, 18, 0, $margin_left + 10, $detail_y, $black, $font, $detail);
        $detail_y += $line_height;
    }

    // กรอบรูปถ่าย ฝั่งซ้าย
    $photo_size = 130;
    $photo_x = intval($margin_left + $half_col + ($half_col - $photo_size) / 2);
    $photo_y = intval($table_y + ($detail_height - $photo_size) / 2);
    imagerectangle($image, $photo_x, $photo_y, $photo_x + $photo_size, $photo_y + $photo_size, $black);
    imagettftext($image, 16, 0, $photo_x + 15, intval($photo_y + $photo_size / 2), $black, $font, 'รูปถ่าย ขนาด 1 นิ้ว');

    // รายละเอียดฝั่งขวา
    $detail_y = $table_y + 25;
    foreach ($details as $detail) {
        imagettftext($image, 18, 0, $margin_left + $col_width + 10, $detail_y, $black, $font, $detail);
        $detail_y += $line_height;
    }

    // กรอบรูปถ่าย ฝั่งขวา
    $photo_x = intval($margin_left + $col_width + $half_col + ($half_col - $photo_size) / 2);
    imagerectangle($image, $photo_x, $photo_y, $photo_x + $photo_size, $photo_y + $photo_size, $black);
    imagettftext($image, 16, 0, $photo_x + 15, intval($photo_y + $photo_size / 2), $black, $font, 'รูปถ่าย ขนาด 1 นิ้ว');

    // ส่วนลายเซ็น (เพิ่มความสูงให้มากขึ้น)
    $table_y += $detail_height;
    $sign_section_height = 60; // เพิ่มความสูงจาก 50 เป็น 60

    // วาดเฉพาะเส้นซ้าย กลาง ขวา (ไม่มีเส้นบนและล่าง)
    imageline($image, $margin_left, $table_y, $margin_left, $table_y + $sign_section_height, $black);
    imageline($image, $margin_left + $col_width, $table_y, $margin_left + $col_width, $table_y + $sign_section_height, $black);
    imageline($image, $width - $margin_right, $table_y, $width - $margin_right, $table_y + $sign_section_height, $black);

    // วาดเส้นประสำหรับลายเซ็น (ห่างจากด้านบน)
    $sign_line_y = $table_y + 50;
    $dot_line_length = 180; // ความยาวเส้นประ

    // เส้นประฝั่งซ้าย - ผู้สมัคร
    $line_start = intval($margin_left + ($half_col - $dot_line_length) / 2);
    drawDottedLine($image, $line_start, $sign_line_y, $dot_line_length, $black);

    // เส้นประฝั่งซ้าย - กรรมการ
    $line_start = intval($margin_left + $half_col + ($half_col - $dot_line_length) / 2);
    drawDottedLine($image, $line_start, $sign_line_y, $dot_line_length, $black);

    // เส้นประฝั่งขวา - ผู้สมัคร
    $line_start = intval($margin_left + $col_width + ($half_col - $dot_line_length) / 2);
    drawDottedLine($image, $line_start, $sign_line_y, $dot_line_length, $black);

    // เส้นประฝั่งขวา - กรรมการ
    $line_start = intval($margin_left + $col_width + $half_col + ($half_col - $dot_line_length) / 2);
    drawDottedLine($image, $line_start, $sign_line_y, $dot_line_length, $black);

    // ข้อความผู้สมัคร/กรรมการ
    $table_y += $sign_section_height;
    $label_row_height = 50;

    // วาดเฉพาะเส้นซ้าย กลาง ขวา และล่าง (ไม่มีเส้นบน)
    imageline($image, $margin_left, $table_y, $margin_left, $table_y + $label_row_height, $black);
    imageline($image, $margin_left + $col_width, $table_y, $margin_left + $col_width, $table_y + $label_row_height, $black);
    imageline($image, $width - $margin_right, $table_y, $width - $margin_right, $table_y + $label_row_height, $black);
    imageline($image, $margin_left, $table_y + $label_row_height, $width - $margin_right, $table_y + $label_row_height, $black);

    // ข้อความผู้สมัครและกรรมการ (ไม่มีเส้นใต้)
    $text = 'ผู้สมัคร';
    $bbox = imagettfbbox(20, 0, $font, $text);
    $text_width = $bbox[2] - $bbox[0];
    $x_center = intval($margin_left + ($half_col - $text_width) / 2);
    imagettftext($image, 20, 0, $x_center, $table_y + 22, $black, $font, $text);

    $text = 'กรรมการรับสมัคร';
    $bbox = imagettfbbox(20, 0, $font, $text);
    $text_width = $bbox[2] - $bbox[0];
    $x_center = intval($margin_left + $half_col + ($half_col - $text_width) / 2);
    imagettftext($image, 20, 0, $x_center, $table_y + 22, $black, $font, $text);

    $text = 'ผู้สมัคร';
    $bbox = imagettfbbox(20, 0, $font, $text);
    $text_width = $bbox[2] - $bbox[0];
    $x_center = intval($margin_left + $col_width + ($half_col - $text_width) / 2);
    imagettftext($image, 20, 0, $x_center, $table_y + 22, $black, $font, $text);

    $text = 'กรรมการรับสมัคร';
    $bbox = imagettfbbox(20, 0, $font, $text);
    $text_width = $bbox[2] - $bbox[0];
    $x_center = intval($margin_left + $col_width + $half_col + ($half_col - $text_width) / 2);
    imagettftext($image, 20, 0, $x_center, $table_y + 22, $black, $font, $text);

    // บันทึกเป็นไฟล์ชั่วคราว
    $temp_file = sys_get_temp_dir() . '/regular_application_' . uniqid() . '.png';
    imagepng($image, $temp_file);
    imagedestroy($image);

    return $temp_file;
}

function parseSchoolAddress($address)
{
    // แยกที่อยู่ออกเป็นอำเภอและจังหวัด
    $district = '';
    $province = '';

    if (!empty($address)) {
        // ตัวอย่าง: "อ.เมือง จ.นครปฐม"
        if (preg_match('/อ\.([^\s]+)/', $address, $matches)) {
            $district = $matches[1];
        }
        if (preg_match('/จ\.([^\s]+)/', $address, $matches)) {
            $province = $matches[1];
        }
    }

    return ['district' => $district, 'province' => $province];
}

function drawCheckbox($image, $x, $y, $checked, $color)
{
    $size = 27;
    imagesetthickness($image, 1);
    imagerectangle($image, $x, $y, $x + $size, $y + $size, $color);

    if ($checked) {
        imagesetthickness($image, 2);
        imageline($image, $x + 5, $y + 13, $x + 11, $y + 21, $color);
        imageline($image, $x + 11, $y + 21, $x + 22, $y + 5, $color);
        imagesetthickness($image, 1);
    }
}

function drawDottedLine($image, $x_start, $y, $length, $color)
{
    imagesetthickness($image, 1);
    $dot_length = 4;
    $gap_length = 4;

    for ($i = 0; $i < $length; $i += ($dot_length + $gap_length)) {
        imageline($image, $x_start + $i, $y, $x_start + $i + $dot_length, $y, $color);
    }
}

function formatDateThaiFull($date)
{
    if (empty($date)) return '';

    $timestamp = strtotime($date);
    $thai_months_full = [
        '',
        'มกราคม',
        'กุมภาพันธ์',
        'มีนาคม',
        'เมษายน',
        'พฤษภาคม',
        'มิถุนายน',
        'กรกฎาคม',
        'สิงหาคม',
        'กันยายน',
        'ตุลาคม',
        'พฤศจิกายน',
        'ธันวาคม'
    ];

    $day = (int)date('j', $timestamp);
    $month = $thai_months_full[(int)date('n', $timestamp)];
    $year = date('Y', $timestamp) + 543;

    return "$day $month พ.ศ. $year";
}

/**
 * Maps an educational level abbreviation to its full Thai name.
 * @param string|null $level
 * @return string The full name in parentheses, or an empty string if no mapping is found.
 */

function getLevelFullName($level)
{
    if (empty($level)) {
        return '';
    }
    $level_map = [
        'ม.3' => 'มัธยมศึกษาปีที่ 3',
        'ม.6.' => 'มัธยมศึกษาปีที่ 6',
        'ปวช.' => 'ประกาศนียบัตรวิชาชีพ',
        'ปวส.' => 'ประกาศนียบัตรวิชาชีพชั้นสูง',
        'ปริญญาตรี' => 'ปริญญาตรี',
    ];

    $full_name = $level_map[$level] ?? null;

    if (!empty($full_name)) {
        return " ({$full_name})";
    }
    return '';
}
