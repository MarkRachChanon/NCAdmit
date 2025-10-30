<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;

// รับพารามิเตอร์
$student_id = isset($_GET['app_no']) ? trim($_GET['app_no']) : '';
$form_type = isset($_GET['type']) ? trim($_GET['type']) : '';

if (empty($student_id) || empty($form_type)) {
    die('ข้อมูลไม่ครบถ้วน');
}

try {
    // ดึงข้อมูลจาก Database
    if ($form_type === 'quota') {
        $stmt = $conn->prepare("
            SELECT 
                sq.*,
                d.name_th as department_name,
                d.level as department_level,
                d.study_type as study_type,
                dc.name as category_name
            FROM students_quota sq
            LEFT JOIN departments d ON sq.department_id = d.id
            LEFT JOIN department_categories dc ON d.category_id = dc.id
            WHERE sq.id = ?
            LIMIT 1
        ");
    }

    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        die('ไม่พบข้อมูลใบสมัคร');
    }

    $data = $result->fetch_assoc();
    $stmt->close();

    // สร้างภาพด้วย GD Library
    $image_path = createApplicationImage($data, $form_type);

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
    $filename = 'ใบสมัครโควตา_' . $data['application_no'] . '_' . date('Ymd_His') . '.pdf';

    // ส่งออก PDF
    $mpdf->Output($filename, 'D');
} catch (Exception $e) {
    error_log("PDF Error: " . $e->getMessage());
    die('เกิดข้อผิดพลาดในการสร้าง PDF: ' . $e->getMessage());
}

// ==================== Functions ====================

function createApplicationImage($data, $form_type)
{
    // เพิ่มความคมชัดเป็น 200 DPI = 1654 x 2339 pixels
    $width = 1654;
    $height = 2339;
    $margin_left = 100;
    $margin_right = 100;

    // สร้างภาพพื้นหลังสีขาว
    $image = imagecreatetruecolor($width, $height);
    $white = imagecolorallocate($image, 255, 255, 255);
    $black = imagecolorallocate($image, 0, 0, 0);
    $gray = imagecolorallocate($image, 100, 100, 100);

    imagefill($image, 0, 0, $white);

    // เปิด anti-aliasing เพื่อความคมชัด
    imageantialias($image, true);

    // โหลดฟอนต์ไทย
    $font = __DIR__ . '/../fonts/THSarabunNew.ttf';
    $fontBold = __DIR__ . '/../fonts/THSarabunNew Bold.ttf';

    if (!file_exists($font)) {
        $font = '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf'; // fallback
    }

    $y = 65; // ตำแหน่ง Y เริ่มต้น
    $academic_year = $data['academic_year'] ?? (date('Y') + 543 + 1);

    // ===== HEADER =====
    $x = $margin_left + 50;
    imagettftext($image, 24, 0, $x, $y, $black, $font, 'เอกสารหมายเลข ๑');
    $y += 40;

    $x = $margin_left;
    imagettftext($image, 21, 0, $x, $y, $black, $font, 'ส่งกลับวิทยาลัยอาชีวศึกษานครปฐม');
    $y += 0;

    $logo_path =  __DIR__ . '/../assets/images/logo.png';
    // ตรวจสอบว่าไฟล์โลโก้มีอยู่จริงหรือไม่
    if (file_exists($logo_path)) {
        $logo = imagecreatefrompng($logo_path);
        
        // ตรวจสอบว่าการโหลดภาพสำเร็จหรือไม่
        if ($logo !== false) {
            $logo_original_width = imagesx($logo);
            $logo_original_height = imagesy($logo);

            // กำหนดขนาดโลโก้ที่ต้องการแสดง (เช่น 400x400 พิกเซล สำหรับความละเอียด 450 DPI)
            $logo_target_width = 200;
            $logo_target_height = 200; 

            // คำนวณตำแหน่ง X เพื่อจัดกึ่งกลาง
            $x_logo_center = ($width - $logo_target_width) / 2;

            // วาดโลโก้พร้อมปรับขนาด
            imagecopyresampled(
                $image,
                $logo,
                $x_logo_center,
                $y,
                0, 0,
                $logo_target_width,
                $logo_target_height,
                $logo_original_width,
                $logo_original_height
            );

            // เพิ่ม $y เพื่อให้หัวข้อหลักอยู่ใต้โลโก้
            $y += $logo_target_height + 53; // 400 พิกเซล + ระยะห่าง 20 พิกเซล

            // เคลียร์หน่วยความจำของโลโก้
            imagedestroy($logo);
        }
    }

    // หัวข้อหลัก - จัดกึ่งกลาง
    $text = 'วิทยาลัยอาชีวศึกษานครปฐม';
    $bbox = imagettfbbox(37, 0, $fontBold, $text);
    $text_width = $bbox[2] - $bbox[0];
    $x_center = ($width - $text_width) / 2;
    imagettftext($image, 37, 0, $x_center, $y, $black, $fontBold, $text);
    $y += 53;

    $text = 'ใบสมัครเข้าเป็นนักเรียน-นักศึกษา สิทธิพิเศษ (โควตา) ปีการศึกษา ' . $academic_year;
    $bbox = imagettfbbox(32, 0, $fontBold, $text);
    $text_width = $bbox[2] - $bbox[0];
    $x_center = ($width - $text_width) / 2;
    imagettftext($image, 32, 0, $x_center, $y, $black, $fontBold, $text);
    $y += 46;

    $text = 'ระดับประกาศนียบัตรวิชาชีพ (ปวช.) ระดับประกาศนียบัตรวิชาชีพชั้นสูง (ปวส.)';
    $bbox = imagettfbbox(24, 0, $font, $text);
    $text_width = $bbox[2] - $bbox[0];
    $x_center = ($width - $text_width) / 2;
    imagettftext($image, 24, 0, $x_center, $y, $black, $font, $text);
    $y += 40;

    $text = 'และระดับปริญญาตรีสายเทคโนโลยีหรือสายปฏิบัติการ (ทล.บ.)';
    $bbox = imagettfbbox(24, 0, $font, $text);
    $text_width = $bbox[2] - $bbox[0];
    $x_center = ($width - $text_width) / 2;
    imagettftext($image, 24, 0, $x_center, $y, $black, $font, $text);
    $y += 53;

    // เส้นคั่น
    imageline($image, $margin_left, $y, $width - $margin_right, $y, $black);
    imagesetthickness($image, 1);
    $y += 46;

    // ===== ข้อ 1: ข้อมูลส่วนตัว =====
    $prefix = $data['prefix'] ?? '';
    $firstname = $data['firstname_th'] ?? '';
    $lastname = $data['lastname_th'] ?? '';

    // บรรทัดที่ 1: ชื่อและนามสกุล
    $line1_start_x = $margin_left;
    imagettftext($image, 27, 0, $line1_start_x, $y, $black, $font, '๑. ชื่อ');

    // วาดเส้นประและข้อมูลชื่อ
    $x_pos = $line1_start_x + 70;
    $name_data = $prefix . '' . $firstname;
    $bbox = imagettfbbox(27, 0, $font, $name_data);
    $name_width = $bbox[2] - $bbox[0];

    drawDottedLine($image, $x_pos, $y + 2, 380, $black);
    imagettftext($image, 27, 0, $x_pos, $y, $black, $font, $name_data);

    // นามสกุล
    $x_pos2 = $x_pos + 390; 
    imagettftext($image, 27, 0, $x_pos2, $y, $black, $font, 'นามสกุล');

    $x_pos3 = $x_pos2 + 95;
    drawDottedLine($image, $x_pos3, $y + 2, $width - $margin_right - $x_pos3, $black);
    imagettftext($image, 27, 0, $x_pos3, $y, $black, $font, $lastname);
    $y += 46;

    // บรรทัดที่ 2: อายุ วันเกิด เชื้อชาติ
    $age = $data['age'] ?? '';
    $birth_date = formatDateThaiFull($data['birth_date']);
    $ethnicity = $data['ethnicity'] ?? '';
    $nationality = $data['nationality'] ?? '';

    $x = $margin_left + 30;
    imagettftext($image, 27, 0, $x, $y, $black, $font, 'อายุ');

    $x += 45;
    drawDottedLine($image, $x, $y + 2, 60, $black);
    imagettftext($image, 27, 0, $x, $y, $black, $font, $age);

    $x += 65;
    imagettftext($image, 27, 0, $x, $y, $black, $font, 'ปี   วัน / เดือน / ปีเกิด');

    $x += 225;
    drawDottedLine($image, $x, $y + 2, 320, $black);
    imagettftext($image, 27, 0, $x, $y, $black, $font, $birth_date);

    $x += 335;
    imagettftext($image, 27, 0, $x, $y, $black, $font, 'เชื้อชาติ');

    $x += 85;
    drawDottedLine($image, $x, $y + 2, 320, $black);
    imagettftext($image, 27, 0, $x, $y, $black, $font, $ethnicity);

    $x += 335;
    imagettftext($image, 27, 0, $x, $y, $black, $font, 'สัญชาติ');

    $x += 85;
    drawDottedLine($image, $x, $y + 2, $width - $margin_right - $x, $black);
    imagettftext($image, 27, 0, $x, $y, $black, $font, $nationality);
    $y += 46;

    // บรรทัดที่ 3: สัญชาติ // บรรทัดที่ 4: ศาสนา และเลขบัตรประชาชน
    $religion = $data['religion'] ?? '';
    $id_card = $data['id_card'] ?? '';

    $x = $margin_left + 30;
    imagettftext($image, 27, 0, $x, $y, $black, $font, 'ศาสนา');

    $x += 75;
    drawDottedLine($image, $x, $y + 2, 150, $black);
    imagettftext($image, 27, 0, $x, $y, $black, $font, $religion);

    $x += 165;
    imagettftext($image, 27, 0, $x, $y, $black, $font, 'เลขประจำตัวประชาชน');

    $x += 230;
    drawDottedLine($image, $x, $y + 2, $width - $margin_right - $x, $black);
    imagettftext($image, 27, 0, $x, $y, $black, $font, $id_card);
    $y += 60;

    // ===== ข้อ 2: ที่อยู่ =====
    $address_no = $data['address_no'] ?? '';
    $village_no = $data['village_no'] ?? '';
    $subdistrict = $data['subdistrict'] ?? '';
    $district = $data['district'] ?? '';

    // บรรทัดที่ 1
    $x = $margin_left;
    imagettftext($image, 27, 0, $x, $y, $black, $font, '๒. ที่อยู่ปัจจุบัน  เลขที่');

    $x += 225;
    drawDottedLine($image, $x, $y + 2, 100, $black);
    imagettftext($image, 27, 0, $x, $y, $black, $font, $address_no);

    $x += 115;
    imagettftext($image, 27, 0, $x, $y, $black, $font, 'หมู่ที่');

    $x += 55;
    drawDottedLine($image, $x, $y + 2, 80, $black);
    imagettftext($image, 27, 0, $x, $y, $black, $font, $village_no);

    $x += 95;
    imagettftext($image, 27, 0, $x, $y, $black, $font, 'ตำบล');

    $x += 60;
    drawDottedLine($image, $x, $y + 2, 180, $black);
    imagettftext($image, 27, 0, $x, $y, $black, $font, $subdistrict);

    $x += 185;
    imagettftext($image, 27, 0, $x, $y, $black, $font, 'อำเภอ');

    $x += 70;
    drawDottedLine($image, $x, $y + 2, $width - $margin_right - $x, $black);
    imagettftext($image, 27, 0, $x, $y, $black, $font, $district);
    $y += 46;

    // บรรทัดที่ 2
    $province = $data['province'] ?? '';
    $postcode = $data['postcode'] ?? '';

    $x = $margin_left + 30;
    imagettftext($image, 27, 0, $x, $y, $black, $font, 'จังหวัด');

    $x += 70;
    drawDottedLine($image, $x, $y + 2, 200, $black);
    imagettftext($image, 27, 0, $x, $y, $black, $font, $province);

    $x += 200;
    imagettftext($image, 27, 0, $x, $y, $black, $font, 'รหัสไปรษณีย์');

    $x += 135;
    drawDottedLine($image, $x, $y + 2, $width - $margin_right - $x, $black);
    imagettftext($image, 27, 0, $x, $y, $black, $font, $postcode);
    $y += 46;

    // บรรทัดที่ 3
    $phone = $data['phone'] ?? '';

    $x = $margin_left + 30;
    imagettftext($image, 27, 0, $x, $y, $black, $font, 'เบอร์โทรศัพท์ที่สามารถติดต่อได้');

    $x += 305;
    drawDottedLine($image, $x, $y + 2, $width - $margin_right - $x, $black);
    imagettftext($image, 27, 0, $x, $y, $black, $font, $phone);
    $y += 60;

    // ===== ข้อ 3: การศึกษา =====
    imagettftext($image, 27, 0, $margin_left, $y, $black, $font, '๓. การศึกษา');
    $y += 46;

    $current_level = $data['current_level'] ?? '';
    $current_school = $data['current_school'] ?? '';

    // บรรทัดที่ 1
    $x = $margin_left + 30;
    imagettftext($image, 27, 0, $x, $y, $black, $font, 'กำลังศึกษาอยู่ในระดับชั้น');

    $x += 245;
    drawDottedLine($image, $x, $y + 2, 150, $black);
    imagettftext($image, 27, 0, $x, $y, $black, $font, $current_level);

    $x += 155;
    imagettftext($image, 27, 0, $x, $y, $black, $font, 'โรงเรียน/วิทยาลัย');

    $x += 175;
    drawDottedLine($image, $x, $y + 2, $width - $margin_right - $x, $black);
    imagettftext($image, 27, 0, $x, $y, $black, $font, $current_school);
    $y += 46;

    // บรรทัดที่ 2
    $school_address = getSchoolAddress($data);

    $x = $margin_left + 30;
    imagettftext($image, 27, 0, $x, $y, $black, $font, 'ที่อยู่');

    $x += 50;
    drawDottedLine($image, $x, $y + 2, $width - $margin_right - $x, $black);
    imagettftext($image, 27, 0, $x, $y, $black, $font, $school_address);
    $y += 46;

    // บรรทัดที่ 3
    $current_major = $data['current_major'] ?? '';

    $x = $margin_left + 30;
    imagettftext($image, 27, 0, $x, $y, $black, $font, 'สาขาวิชา(กรณีศึกษาอยู่ในระดับชั้น ปวช.และปวส.)');

    $x += 485;
    drawDottedLine($image, $x, $y + 2, $width - $margin_right - $x, $black);
    imagettftext($image, 27, 0, $x, $y, $black, $font, $current_major);
    $y += 60;

    // ===== ข้อ 4-6: ข้อมูลอื่นๆ =====
    $awards = getAwards($data);

    $x = $margin_left;
    imagettftext($image, 27, 0, $x, $y, $black, $font, '๔. ผลงาน/รางวัลที่ได้รับ');

    $x += 235;
    drawDottedLine($image, $x, $y + 2, $width - $margin_right - $x, $black);
    imagettftext($image, 27, 0, $x, $y, $black, $font, $awards);
    $y += 46;

    $special_ability = $data['talents'] ?? '';

    $x = $margin_left;
    imagettftext($image, 27, 0, $x, $y, $black, $font, '๕. ความสามารถพิเศษ');

    $x += 215;
    drawDottedLine($image, $x, $y + 2, $width - $margin_right - $x, $black);
    imagettftext($image, 27, 0, $x, $y, $black, $font, $special_ability);
    $y += 46;

    $gpa = $data['gpa'] ?? '';

    $x = $margin_left;
    imagettftext($image, 27, 0, $x, $y, $black, $font, '๖. เกรดเฉลี่ย');

    $x += 130;
    drawDottedLine($image, $x, $y + 2, $width - $margin_right - $x, $black);
    imagettftext($image, 27, 0, $x, $y, $black, $font, $gpa);
    $y += 60;

    // ===== ข้อ 7: มีความประสงค์ =====
    imagettftext($image, 27, 0, $margin_left, $y, $black, $font, '๗. มีความประสงค์ขอเข้าศึกษาต่อในระดับชั้น');
    $y += 53;

    $apply_level = $data['apply_level'] ?? $data['department_level'] ?? '';
    $department = $data['department_name'] ?? '';
    $study_type = $data['study_type'] ?? '';

    // Checkbox สำหรับ ปวช.
    $pvs_checked = (strpos($apply_level, 'ปวช.') !== false);
    drawCheckbox($image, $margin_left + 30, $y - 24, $pvs_checked, $black);
    imagettftext($image, 27, 0, $margin_left + 65, $y, $black, $font, 
        'ระดับประกาศนียบัตรวิชาชีพ (ปวช.) ณ วิทยาลัยอาชีวศึกษานครปฐม');
    $y += 46;

    $x = $margin_left + 65;
    imagettftext($image, 27, 0, $x, $y, $black, $font, 'สาขาวิชา');

    $x += 100;
    drawDottedLine($image, $x, $y + 2, $width - $margin_right - $x, $black);
    if ($pvs_checked) {
        $dept_text = $department;
        imagettftext($image, 27, 0, $x, $y, $black, $font, $dept_text);
    }
    $y += 46;

    // Checkbox สำหรับ ปวส.
    $pvc_checked = (strpos($apply_level, 'ปวส.') !== false);
    drawCheckbox($image, $margin_left + 30, $y - 24, $pvc_checked, $black);
    imagettftext($image, 27, 0, $margin_left + 65, $y, $black, $font, 
        'ระดับประกาศนียบัตรวิชาชีพชั้นสูง (ปวส.) ณ วิทยาลัยอาชีวศึกษานครปฐม');
    $y += 46;

    $x = $margin_left + 65;
    imagettftext($image, 27, 0, $x, $y, $black, $font, 'สาขาวิชา');

    $x += 100;
    drawDottedLine($image, $x, $y + 2, $width - $margin_right - $x, $black);
    if ($pvc_checked) {
        $dept_text = $department;
        imagettftext($image, 27, 0, $x, $y, $black, $font, $dept_text);
    }
    $y += 46;

    // Checkbox สำหรับ ปริญญาตรี
    $degree_checked = (strpos($apply_level, 'ปริญญาตรี') !== false);
    drawCheckbox($image, $margin_left + 30, $y - 24, $degree_checked, $black);
    imagettftext($image, 27, 0, $margin_left + 65, $y, $black, $font, 
        'ปริญญาตรีสายเทคโนโลยีหรือสายปฏิบัติการ (ทล.บ.) ณ สถาบันการอาชีวศึกษาภาคกลาง ๔');
    $y += 40;
    imagettftext($image, 27, 0, $margin_left + 65, $y, $black, $font, '(วิทยาลัยอาชีวศึกษานครปฐม)');
    $y += 46;

    $x = $margin_left + 65;
    imagettftext($image, 27, 0, $x, $y, $black, $font, 'สาขาวิชา');

    $x += 100;
    drawDottedLine($image, $x, $y + 2, $width - $margin_right - $x, $black);
    if ($degree_checked) {
        imagettftext($image, 27, 0, $x, $y, $black, $font, $department);
    }
    $y += 180;

    // ===== ลายเซ็น =====
    $signature_line_text = 'ลงชื่อ.......................................................ผู้สมัคร';
    $x_signature_start = $width - 650;
    imagettftext($image, 27, 0, $x_signature_start, $y, $black, $font, $signature_line_text);
    $y += 46;
    $full_name_in_parens = '( '. $prefix . $firstname . '  ' . $lastname .' )';
    $bbox_name = imagettfbbox(27, 0, $font, $full_name_in_parens);
    $name_width = $bbox_name[2] - $bbox_name[0];
    $bbox_sign = imagettfbbox(27, 0, $font, $signature_line_text);
    $sign_line_width = $bbox_sign[2] - $bbox_sign[0];
    $x_name_center = $x_signature_start + ($sign_line_width / 2) - ($name_width / 2);
    imagettftext($image, 27, 0, $x_name_center, $y, $black, $font, $full_name_in_parens);
    $y += 65;

    // ===== Footer =====
    imagesetthickness($image, 1);
    imageline($image, $margin_left, $y, $width - $margin_right, $y, $black);
    $y += 40;

    $text = 'หมายเหตุ นักเรียน - นักศึกษาที่แจ้งความประสงค์เป็นนักศึกษาสิทธิพิเศษ (โควตา) ปีการศึกษา ' . $academic_year;
    $bbox = imagettfbbox(21, 0, $font, $text);
    $text_width = $bbox[2] - $bbox[0];
    $x_center = ($width - $text_width) / 2;
    imagettftext($image, 21, 0, $x_center, $y, $black, $font, $text);
    $y += 40;

    $text = 'สามารถติดตามและตรวจสอบรายชื่อผู้มีสิทธิ์เข้าสอบ ได้ที่ www.nc.ac.th';
    $bbox = imagettfbbox(21, 0, $fontBold, $text);
    $text_width = $bbox[2] - $bbox[0];
    $x_center = ($width - $text_width) / 2;
    imagettftext($image, 21, 0, $x_center, $y, $black, $fontBold, $text);
    $y += 53;

    // กรอบข้อมูลการบันทึก
    $box_height = 65;
    imagesetthickness($image, 1);
    imagerectangle($image, $margin_left, $y, $width - $margin_right, $y + $box_height, $black);
    imagefilledrectangle($image, $margin_left + 1, $y + 1, $width - $margin_right - 1, $y + $box_height - 1, 
        imagecolorallocate($image, 245, 245, 245));

    $info_text = 'ข้อมูลการบันทึก: หมายเลขใบสมัคร ' . $data['application_no'] . 
                 ' | วันที่สมัคร: ' . formatDateThai($data['created_at']) . 
                 ' | พิมพ์เมื่อ: ' . date('d/m/Y H:i') . ' น.';
    imagettftext($image, 21, 0, $margin_left + 13, $y + 42, $black, $font, $info_text);

    // บันทึกเป็นไฟล์ชั่วคราว
    $temp_file = sys_get_temp_dir() . '/application_' . uniqid() . '.png';
    imagepng($image, $temp_file);
    imagedestroy($image);

    return $temp_file;
}

function drawCheckbox($image, $x, $y, $checked, $color)
{
    $size = 27;
    imagesetthickness($image, 1);
    imagerectangle($image, $x, $y, $x + $size, $y + $size, $color);

    if ($checked) {
        imagesetthickness($image, 4);
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
        '', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
        'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'
    ];

    $day = (int)date('j', $timestamp);
    $month = $thai_months_full[(int)date('n', $timestamp)];
    $year = date('Y', $timestamp) + 543;

    return "$day $month พ.ศ. $year";
}

function formatDateThai($date)
{
    if (empty($date)) return '';

    $timestamp = strtotime($date);
    $thai_months = [
        '', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.',
        'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'
    ];

    $day = date('j', $timestamp);
    $month = $thai_months[(int)date('n', $timestamp)];
    $year = date('Y', $timestamp) + 543;

    return "$day $month $year";
}

function getSchoolAddress($data)
{
    if (!empty($data['school_address'])) {
        return $data['school_address'];
    }
    return '';
}

function getAwards($data)
{
    if (!empty($data['awards'])) {
        return $data['awards'];
    }
    return '';
}
?>