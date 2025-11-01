<?php
/**
 * Export PDF Action - ประมวลผลและสร้างไฟล์ PDF
 * NC-Admission - Nakhon Pathom College Admission System
 * 
 * การแก้ไข:
 * 1. แยกการทำงานของระดับชั้นและสาขาวิชาให้ชัดเจน
 * 2. เพิ่มคอลัมน์ประเภทการสมัครใน PDF
 * 3. เรียงลำดับ: โควตา (ปวช. -> ปวส. -> ปริญญาตรี) ก่อน ปกติ (ปวช. -> ปวส. -> ปริญญาตรี)
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
/**
 * สร้าง SQL Query สำหรับดึงข้อมูลผู้สมัคร
 * 
 * ลอจิกการทำงาน:
 * 1. ถ้าเลือกสาขาเฉพาะ → ใช้ department_id (ไม่สนใจ level)
 * 2. ถ้าไม่เลือกสาขา แต่เลือกระดับ → ใช้ level
 * 3. ถ้าไม่เลือกทั้ง 2 → แสดงทั้งหมด
 * 4. เพิ่ม level_order สำหรับการเรียงลำดับ (ปวช.=1, ปวส.=2, ปริญญาตรี=3)
 */
function buildQuery($type, $academic_year, $level, $department_id, $status, $conn) {
    $conditions = [];
    $params = [];
    $types = '';
    
    // 1. ปีการศึกษา
    if ($academic_year !== 'all') {
        $conditions[] = "academic_year = ?";
        $params[] = $academic_year;
        $types .= 's';
    }
    
    // 2. ลอจิกการกรองตามสาขาและระดับ
    if (!empty($department_id)) {
        // เลือกสาขาเฉพาะ → ใช้ department_id
        $conditions[] = "department_id = ?";
        $params[] = $department_id;
        $types .= 'i';
    } else if (!empty($level)) {
        // ไม่เลือกสาขา แต่เลือกระดับ → กรองตามระดับ
        $conditions[] = "d.level = ?";
        $params[] = $level;
        $types .= 's';
    }
    
    // 3. สถานะ
    if (!empty($status)) {
        $conditions[] = "status = ?";
        $params[] = $status;
        $types .= 's';
    }
    
    $where = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
    
    $queries = [];
    
    // Query for Quota (รอบโควตา)
    if ($type === 'quota' || $type === 'all') {
        $sql = "SELECT 
                    sq.*,
                    d.code as dept_code,
                    d.name_th as dept_name,
                    d.level as dept_level,
                    'quota' as student_type,
                    CASE 
                        WHEN d.level = 'ปวช.' THEN 1
                        WHEN d.level = 'ปวส.' THEN 2
                        WHEN d.level = 'ปริญญาตรี' THEN 3
                        ELSE 4
                    END as level_order
                FROM students_quota sq
                LEFT JOIN departments d ON sq.department_id = d.id
                {$where}
                ORDER BY level_order, d.name_th, sq.application_no";
        
        $queries[] = ['sql' => $sql, 'params' => $params, 'types' => $types];
    }
    
    // Query for Regular (รอบปกติ)
    if ($type === 'regular' || $type === 'all') {
        $sql = "SELECT 
                    sr.*,
                    d.code as dept_code,
                    d.name_th as dept_name,
                    d.level as dept_level,
                    'regular' as student_type,
                    CASE 
                        WHEN d.level = 'ปวช.' THEN 1
                        WHEN d.level = 'ปวส.' THEN 2
                        WHEN d.level = 'ปริญญาตรี' THEN 3
                        ELSE 4
                    END as level_order
                FROM students_regular sr
                LEFT JOIN departments d ON sr.department_id = d.id
                {$where}
                ORDER BY level_order, d.name_th, sr.application_no";
        
        $queries[] = ['sql' => $sql, 'params' => $params, 'types' => $types];
    }
    
    return $queries;
}

// ==================== Fetch Data ====================
$all_students = [];
$queries = buildQuery($type, $academic_year, $level, $department_id, $status, $conn);

// Debug Log
error_log("=== Export PDF Debug ===");
error_log("Type: $type");
error_log("Academic Year: $academic_year");
error_log("Level: " . ($level ?: 'ทุกระดับ'));
error_log("Department ID: " . ($department_id ?: 'ทุกสาขา'));
error_log("Status: " . ($status ?: 'ทุกสถานะ'));
error_log("Total Queries: " . count($queries));

foreach ($queries as $index => $query) {
    error_log("Query " . ($index + 1) . ": " . $query['sql']);
    
    $stmt = $conn->prepare($query['sql']);
    
    if (!empty($query['params'])) {
        $stmt->bind_param($query['types'], ...$query['params']);
        error_log("Params: " . json_encode($query['params']));
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $all_students[] = $row;
    }
    
    error_log("Records from query " . ($index + 1) . ": " . $result->num_rows);
}

// ==================== Sort All Students ====================
// เมื่อเลือก "ทั้งหมด" ให้เรียงลำดับ: โควตา (ปวช. -> ปวส. -> ปริญญาตรี) -> ปกติ (ปวช. -> ปวส. -> ปริญญาตรี)
if ($type === 'all') {
    usort($all_students, function($a, $b) {
        // 1. เรียงตามประเภท (quota ก่อน regular)
        $type_order = ['quota' => 1, 'regular' => 2];
        $type_compare = $type_order[$a['student_type']] - $type_order[$b['student_type']];
        if ($type_compare !== 0) return $type_compare;
        
        // 2. เรียงตามระดับชั้น (ปวช. -> ปวส. -> ปริญญาตรี)
        if ($a['level_order'] !== $b['level_order']) {
            return $a['level_order'] - $b['level_order'];
        }
        
        // 3. เรียงตามชื่อสาขา
        $dept_compare = strcmp($a['dept_name'], $b['dept_name']);
        if ($dept_compare !== 0) return $dept_compare;
        
        // 4. เรียงตามเลขที่สมัคร
        return strcmp($a['application_no'], $b['application_no']);
    });
    
    error_log("Sorted " . count($all_students) . " students by type and level");
}

error_log("Total students: " . count($all_students));
error_log("======================");

// ตรวจสอบว่ามีข้อมูลหรือไม่
if (empty($all_students)) {
    die('ไม่พบข้อมูลตามเงื่อนไขที่เลือก');
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
    
    // ==================== Generate Content ====================
    if ($format === 'list') {
        $html = generateListFormat($mpdf, $all_students, $type, $academic_year, $level, $department_id);
        $mpdf->WriteHTML($html);
    } else {
        $html = generateListFormat($mpdf, $all_students, $type, $academic_year, $level, $department_id);
        $mpdf->WriteHTML($html);
    }
    
    // ==================== Output PDF ====================
    $type_text = ($type === 'all' ? 'ทั้งหมด' : ($type === 'quota' ? 'โควตา' : 'ปกติ'));
    $filename = 'รายชื่อผู้สมัคร_' . $type_text . '_' . date('Y-m-d_His') . '.pdf';
    
    if ($is_preview) {
        $mpdf->Output($filename, 'I'); // Display in browser
    } else {
        $mpdf->Output($filename, 'D'); // Download
        
        // Log Activity
        $filter_info = [];
        $filter_info[] = "ประเภท: $type_text";
        $filter_info[] = "ปีการศึกษา: " . ($academic_year === 'all' ? 'ทั้งหมด' : $academic_year);
        if (!empty($level)) $filter_info[] = "ระดับ: $level";
        if (!empty($department_id)) {
            $dept_sql = "SELECT name_th FROM departments WHERE id = ?";
            $dept_stmt = $conn->prepare($dept_sql);
            $dept_stmt->bind_param('i', $department_id);
            $dept_stmt->execute();
            $dept_result = $dept_stmt->get_result();
            if ($dept_row = $dept_result->fetch_assoc()) {
                $filter_info[] = "สาขา: " . $dept_row['name_th'];
            }
        }
        if (!empty($status)) $filter_info[] = "สถานะ: $status";
        
        log_activity(
            $_SESSION['admin_id'],
            "Export PDF รายชื่อผู้สมัคร",
            null,
            null,
            implode(', ', $filter_info),
            null
        );
    }
    
} catch (Exception $e) {
    error_log("PDF Generation Error: " . $e->getMessage());
    die('เกิดข้อผิดพลาด: ' . $e->getMessage());
}

// ==================== Format Functions ====================

/**
 * สร้าง PDF แบบรายชื่อตาราง พร้อมเลขหน้า
 * เพิ่มคอลัมน์ประเภทการสมัคร (โควตา/ปกติ)
 */
function generateListFormat($mpdf, $students, $type, $academic_year, $level = '', $department_id = '') {
    global $conn;
    
    $total = count($students);
    $type_text = ($type === 'all' ? 'ทั้งหมด' : ($type === 'quota' ? 'รอบโควตา' : 'รอบปกติ'));
    $year_text = ($academic_year === 'all' ? 'ทุกปีการศึกษา' : 'ปีการศึกษา ' . $academic_year);
    
    // สร้างข้อความสำหรับระดับและสาขา
    $level_text = '';
    if (!empty($level)) {
        $level_text = " | <strong>ระดับ:</strong> $level";
    }
    
    $dept_text = '';
    if (!empty($department_id)) {
        $dept_sql = "SELECT code, name_th FROM departments WHERE id = ?";
        $dept_stmt = $conn->prepare($dept_sql);
        $dept_stmt->bind_param('i', $department_id);
        $dept_stmt->execute();
        $dept_result = $dept_stmt->get_result();
        if ($dept_row = $dept_result->fetch_assoc()) {
            $dept_text = " | <strong>สาขา:</strong> " . $dept_row['code'] . ' - ' . $dept_row['name_th'];
        }
    }
    
    // กำหนดจำนวนคอลัมน์ตามประเภทที่เลือก
    $show_type_column = ($type === 'all'); // แสดงคอลัมน์ประเภทเมื่อเลือก "ทั้งหมด"
    $colspan = $show_type_column ? 8 : 7;
    
    // ==================== Set mPDF Footer ====================
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
    
    // ==================== HTML Header ====================
    $header_html = '
    <div style="text-align: center; margin-bottom: 15px; border-bottom: 2px solid #333; padding-bottom: 10px;">
        <h1 style="font-size: 22px; margin: 5px 0; color: #2c3e50; font-weight: bold;">รายชื่อผู้สมัครเข้าเรียน</h1>
        <h2 style="font-size: 18px; margin: 5px 0; color: #555;">วิทยาลัยอาชีวศึกษานครปฐม ' . $year_text . '</h2>
    </div>';
    
    // ==================== Info Bar ====================
    $info_bar = '
    <tr>
        <td colspan="' . $colspan . '" style="background-color: #f8f9fa; padding: 10px; border: 1px solid #ddd; font-size: 14px; color: #333;">
            <strong>ประเภท:</strong> ' . $type_text . ' | 
            <strong>ปีการศึกษา:</strong> ' . $year_text . 
            $level_text . 
            $dept_text . ' | 
            <strong>จำนวนทั้งหมด:</strong> ' . number_format($total) . ' คน | 
            <strong>วันที่พิมพ์:</strong> ' . date('d/m/Y H:i:s') . '
        </td>
    </tr>';
    
    // ==================== Table Header ====================
    $table_header = '
    <tr>
        <th width="4%">ลำดับ</th>' .
        ($show_type_column ? '<th width="8%">ประเภท</th>' : '') . '
        <th width="10%">เลขที่สมัคร</th>
        <th width="' . ($show_type_column ? '20%' : '23%') . '">ชื่อ-สกุล</th>
        <th width="8%">ระดับ</th>
        <th width="' . ($show_type_column ? '23%' : '25%') . '">สาขาวิชา</th>
        <th width="8%">เกรด</th>
        <th width="12%">สถานะ</th>
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
            .type-badge {
                padding: 3px 8px;
                border-radius: 3px;
                font-size: 11px;
                font-weight: bold;
                display: inline-block;
            }
            .type-quota { background-color: #e3f2fd; color: #1565c0; }
            .type-regular { background-color: #f3e5f5; color: #6a1b9a; }
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
    
    // เพิ่ม Header
    $html .= $header_html;
    
    // เริ่มตาราง
    $html .= '
        <table>
            <thead>
                ' . $info_bar . 
                $table_header . '
            </thead>
            <tbody>';
    
    $no = 1;
    foreach ($students as $student) {
        $fullname = ($student['prefix'] ?? '') . ' ' . ($student['firstname_th'] ?? '') . ' ' . ($student['lastname_th'] ?? '');
        
        // ประเภท
        $type_class = 'type-' . ($student['student_type'] ?? 'quota');
        $type_text_display = [
            'quota' => 'โควตา',
            'regular' => 'ปกติ'
        ];
        $current_type = $student['student_type'] ?? 'quota';
        
        // สถานะ
        $status_class = 'status-' . ($student['status'] ?? 'pending');
        $status_text_arr = [
            'pending' => 'รอตรวจสอบ',
            'approved' => 'อนุมัติ',
            'rejected' => 'ไม่อนุมัติ',
            'cancelled' => 'ยกเลิก'
        ];
        $current_status = $student['status'] ?? 'pending';
        
        $html .= '
                <tr>
                    <td class="text-center">' . $no++ . '</td>';
        
        // แสดงคอลัมน์ประเภทเมื่อเลือก "ทั้งหมด"
        if ($show_type_column) {
            $html .= '
                    <td class="text-center">
                        <span class="type-badge ' . $type_class . '">
                            ' . ($type_text_display[$current_type] ?? 'ไม่ระบุ') . '
                        </span>
                    </td>';
        }
        
        $html .= '
                    <td class="text-center">' . htmlspecialchars($student['application_no'] ?? '') . '</td>
                    <td>' . htmlspecialchars($fullname) . '</td>
                    <td class="text-center">' . htmlspecialchars($student['dept_level'] ?? '') . '</td>
                    <td>' . htmlspecialchars(($student['dept_code'] ?? '') . ' - ' . ($student['dept_name'] ?? '')) . '</td>
                    <td class="text-center">' . number_format($student['gpa'] ?? 0, 2) . '</td>
                    <td class="text-center">
                        <span class="status-badge ' . $status_class . '">
                            ' . ($status_text_arr[$current_status] ?? 'ไม่ระบุ') . '
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
?>