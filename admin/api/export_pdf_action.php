<?php
/**
 * Export PDF Action - ประมวลผลและสร้างไฟล์ PDF
 * NC-Admission - Nakhon Pathom College Admission System
 */

session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../vendor/autoload.php'; // mPDF

// ตรวจสอบ Authentication
if (!isset($_SESSION['admin_logged_in'])) {
    die('Unauthorized');
}

// รับค่าจาก Form
$type = isset($_POST['type']) ? $_POST['type'] : (isset($_GET['type']) ? $_GET['type'] : '');
$academic_year = isset($_POST['academic_year']) ? $_POST['academic_year'] : (isset($_GET['academic_year']) ? $_GET['academic_year'] : '');
$level = isset($_POST['level']) ? $_POST['level'] : (isset($_GET['level']) ? $_GET['level'] : '');
$department_id = isset($_POST['department_id']) ? $_POST['department_id'] : (isset($_GET['department_id']) ? $_GET['department_id'] : '');
$status = isset($_POST['status']) ? $_POST['status'] : (isset($_GET['status']) ? $_GET['status'] : '');
$format = isset($_POST['format']) ? $_POST['format'] : (isset($_GET['format']) ? $_GET['format'] : 'list');
$is_preview = isset($_POST['preview']) || isset($_GET['preview']);

// Validate
if (empty($type) || empty($academic_year)) {
    die('กรุณาเลือกประเภทการสมัครและปีการศึกษา');
}

// ==================== Build SQL Query ====================
function buildQuery($type, $academic_year, $level, $department_id, $status, $conn) {
    $conditions = [];
    $params = [];
    $types = '';
    
    // Academic Year
    if ($academic_year !== 'all') {
        $conditions[] = "academic_year = ?";
        $params[] = $academic_year;
        $types .= 's';
    }
    
    // Level - กรองตามระดับชั้น
    if (!empty($level)) {
        $conditions[] = "d.level = ?";
        $params[] = $level;
        $types .= 's';
    }
    
    // Department
    if (!empty($department_id)) {
        $conditions[] = "department_id = ?";
        $params[] = $department_id;
        $types .= 'i';
    }
    
    // Status
    if (!empty($status)) {
        $conditions[] = "status = ?";
        $params[] = $status;
        $types .= 's';
    }
    
    $where = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
    
    $queries = [];
    
    // Query for Quota
    if ($type === 'quota' || $type === 'all') {
        $sql = "SELECT 
                    sq.*,
                    d.code as dept_code,
                    d.name_th as dept_name,
                    d.level as dept_level,
                    'quota' as student_type
                FROM students_quota sq
                LEFT JOIN departments d ON sq.department_id = d.id
                {$where}
                ORDER BY d.level, d.name_th, sq.application_no";
        
        $queries[] = ['sql' => $sql, 'params' => $params, 'types' => $types];
    }
    
    // Query for Regular
    if ($type === 'regular' || $type === 'all') {
        $sql = "SELECT 
                    sr.*,
                    d.code as dept_code,
                    d.name_th as dept_name,
                    d.level as dept_level,
                    'regular' as student_type
                FROM students_regular sr
                LEFT JOIN departments d ON sr.department_id = d.id
                {$where}
                ORDER BY d.level, d.name_th, sr.application_no";
        
        $queries[] = ['sql' => $sql, 'params' => $params, 'types' => $types];
    }
    
    return $queries;
}

// ==================== Fetch Data ====================
$all_students = [];
$queries = buildQuery($type, $academic_year, $level, $department_id, $status, $conn);

foreach ($queries as $query) {
    $stmt = $conn->prepare($query['sql']);
    
    if (!empty($query['params'])) {
        $stmt->bind_param($query['types'], ...$query['params']);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $all_students[] = $row;
    }
}

// ==================== Generate PDF ====================
try {
    // กำหนด Config สำหรับ mPDF พร้อมฟอนต์ภาษาไทย
    $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
    $fontDirs = $defaultConfig['fontDir'];
    
    $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
    $fontData = $defaultFontConfig['fontdata'];
    
    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'orientation' => ($format === 'list' ? 'L' : 'P'),
        'margin_left' => 8,
        'margin_right' => 8,
        'margin_top' => 8,
        'margin_bottom' => 5,
        'margin_header' => 5,
        'margin_footer' => 10,
        'fontDir' => array_merge($fontDirs, [
            __DIR__ . '/../../fonts',
        ]),
        'fontdata' => $fontData + [
            'thsarabun' => [
                'R' => 'THSarabunNew.ttf',
                'B' => 'THSarabunNew Bold.ttf',
                'I' => 'THSarabunNew Italic.ttf',
                'BI' => 'THSarabunNew BoldItalic.ttf',
            ]
        ],
        'default_font' => 'thsarabun'
    ]);
    
    // Set Document Properties
    $mpdf->SetTitle('รายชื่อผู้สมัคร - วิทยาลัยอาชีวศึกษานครปฐม');
    $mpdf->SetAuthor('NC-Admission System');
    $mpdf->SetCreator('NC-Admission System');
    
    // ==================== Generate Content by Format ====================
    if ($format === 'list') {
        $html = generateListFormat($mpdf, $all_students, $type, $academic_year);
        $mpdf->WriteHTML($html);
    } else {
        $html = generateListFormat($mpdf, $all_students, $type, $academic_year);
        $mpdf->WriteHTML($html);
    }
    
    // ==================== Output PDF ====================
    $filename = 'รายชื่อผู้สมัคร_' . ($type === 'all' ? 'ทั้งหมด' : ($type === 'quota' ? 'โควตา' : 'ปกติ')) . '_' . date('Y-m-d_His') . '.pdf';
    
    if ($is_preview) {
        $mpdf->Output($filename, 'I'); // Display in browser
    } else {
        $mpdf->Output($filename, 'D'); // Download
        
        // Log Activity
        log_activity(
            $_SESSION['admin_id'],
            "Export PDF รายชื่อผู้สมัคร ({$type}) - {$academic_year}",
            null,
            null,
            null,
            null
        );
    }
    
} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}

// ==================== Format Functions ====================

/**
 * สร้าง PDF แบบรายชื่อตาราง พร้อมเลขหน้า
 */
function generateListFormat($mpdf, $students, $type, $academic_year) {
    $total = count($students);
    $type_text = ($type === 'all' ? 'ทั้งหมด' : ($type === 'quota' ? 'รอบโควตา' : 'รอบปกติ'));
    $year_text = ($academic_year === 'all' ? 'ทุกปีการศึกษา' : 'ปีการศึกษา ' . $academic_year);
    
    // ==================== Set mPDF Footer เท่านั้น (แสดงเลขหน้า) ====================
    $mpdf->SetHTMLFooter('
    <table width="100%" style="font-family: thsarabun; font-size: 13px; color: #666; border-top: 1px solid #ddd; padding-top: 5px;">
        <tr>
            <td width="50%" style="text-align: left;">
                พิมพ์โดย: ' . htmlspecialchars($_SESSION['admin_fullname']) . ' | ระบบ NC-Admission
            </td>
            <td width="50%" style="text-align: right;">
                หน้า {PAGENO} / {nbpg}
            </td>
        </tr>
    </table>
    ');
    
    // ==================== HTML Header (แสดงเฉพาะหน้าแรก) ====================
    $header_html = '
    <div style="text-align: center; margin-bottom: 15px; border-bottom: 2px solid #333; padding-bottom: 10px;">
        <h1 style="font-size: 22px; margin: 5px 0; color: #2c3e50; font-weight: bold;">รายชื่อผู้สมัครเข้าเรียน</h1>
        <h2 style="font-size: 18px; margin: 5px 0; color: #555;">วิทยาลัยอาชีวศึกษานครปฐม ' . $year_text . '</h2>
    </div>';
    
    // ==================== Info Bar (จะซ้ำทุกหน้าผ่าน thead) ====================
    $info_bar = '
    <tr>
        <td colspan="7" style="background-color: #f8f9fa; padding: 10px; border: 1px solid #ddd; font-size: 14px; color: #333;">
            <strong>ประเภท:</strong> ' . $type_text . ' | 
            <strong>ปีการศึกษา:</strong> ' . $year_text . ' | 
            <strong>จำนวนทั้งหมด:</strong> ' . number_format($total) . ' คน | 
            <strong>วันที่พิมพ์:</strong> ' . date('d/m/Y H:i:s') . '
        </td>
    </tr>';
    
    // ==================== Main Content ====================
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <style>
            body {
                font-family: "thsarabun", sans-serif;
                font-size: 16px;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 10px;
            }
            thead tr th {
                background-color: #34495e;
                color: white;
                padding: 10px 5px;
                text-align: center;
                font-size: 14px;
                border: 1px solid #2c3e50;
                font-weight: bold;
            }
            tbody tr td {
                padding: 8px 5px;
                border: 1px solid #ddd;
                font-size: 13px;
                vertical-align: middle;
            }
            tbody tr:nth-child(even) {
                background-color: #f8f9fa;
            }
            .text-center { text-align: center; }
            .text-right { text-align: right; }
            .status-badge {
                padding: 4px 10px;
                border-radius: 4px;
                font-size: 12px;
                font-weight: bold;
                display: inline-block;
            }
            .status-pending { background-color: #fff3cd; color: #856404; }
            .status-approved { background-color: #d1e7dd; color: #0f5132; }
            .status-rejected { background-color: #f8d7da; color: #842029; }
            .status-cancelled { background-color: #e2e3e5; color: #41464b; }
        </style>
    </head>
    <body>';
    
    // เพิ่ม Header เฉพาะหน้าแรก
    $html .= $header_html;
    
    // เริ่มตาราง - ใส่ info bar ใน thead เพื่อให้ซ้ำทุกหน้า
    $html .= '
        <table>
            <thead>
                ' . $info_bar . '
                <tr>
                    <th width="5%">ลำดับ</th>
                    <th width="12%">เลขที่สมัคร</th>
                    <th width="23%">ชื่อ-สกุล</th>
                    <th width="10%">ระดับ</th>
                    <th width="25%">สาขาวิชา</th>
                    <th width="10%">เกรด</th>
                    <th width="15%">สถานะ</th>
                </tr>
            </thead>
            <tbody>';
    
    $no = 1;
    foreach ($students as $student) {
        $fullname = ($student['prefix'] ?? '') . ' ' . ($student['firstname_th'] ?? '') . ' ' . ($student['lastname_th'] ?? '');
        $status_class = 'status-' . ($student['status'] ?? 'pending');
        $status_text = [
            'pending' => 'รอตรวจสอบ',
            'approved' => 'อนุมัติ',
            'rejected' => 'ไม่อนุมัติ',
            'cancelled' => 'ยกเลิก'
        ];
        
        $current_status = $student['status'] ?? 'pending';
        
        $html .= '
                <tr>
                    <td class="text-center">' . $no++ . '</td>
                    <td class="text-center">' . htmlspecialchars($student['application_no'] ?? '') . '</td>
                    <td>' . htmlspecialchars($fullname) . '</td>
                    <td class="text-center">' . htmlspecialchars($student['apply_level'] ?? '') . '</td>
                    <td>' . htmlspecialchars(($student['dept_code'] ?? '') . ' - ' . ($student['dept_name'] ?? '')) . '</td>
                    <td class="text-center">' . number_format($student['gpa'] ?? 0, 2) . '</td>
                    <td class="text-center">
                        <span class="status-badge ' . $status_class . '">
                            ' . ($status_text[$current_status] ?? 'ไม่ระบุ') . '
                        </span>
                    </td>
                </tr>';
    }
    
    $html .= '
            </tbody>
        </table>
    </body>
    </html>';
    
    return $html;
}