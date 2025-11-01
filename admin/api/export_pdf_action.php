<?php
/**
 * Export PDF Action - ประมวลผลและสร้างไฟล์ PDF
 * NC-Admission - Nakhon Pathom College Admission System
 * 
 * รองรับ 2 รูปแบบ:
 * 1. list - รายชื่อผู้สมัครแบบตาราง
 * 2. summary - สรุปจำนวนผู้สมัครแยกตามสาขาวิชา
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

// ==================== สร้าง PDF ตามรูปแบบที่เลือก ====================
try {
    if ($format === 'summary') {
        // สร้าง PDF สรุปจำนวน
        generateSummaryPDF($conn, $type, $academic_year, $is_preview);
    } else {
        // สร้าง PDF รายชื่อ
        generateListPDF($conn, $type, $academic_year, $level, $department_id, $status, $is_preview);
    }
} catch (Exception $e) {
    error_log("PDF Generation Error: " . $e->getMessage());
    die('เกิดข้อผิดพลาด: ' . $e->getMessage());
}

// ==================== ฟังก์ชันสร้าง PDF รายชื่อ ====================
function generateListPDF($conn, $type, $academic_year, $level, $department_id, $status, $is_preview) {
    // ดึงข้อมูลผู้สมัคร
    $all_students = [];
    $queries = buildQuery($type, $academic_year, $level, $department_id, $status, $conn);
    
    // Debug Log
    error_log("=== Export PDF List Debug ===");
    error_log("Type: $type | Year: $academic_year | Level: " . ($level ?: 'ทุกระดับ'));
    
    foreach ($queries as $index => $query) {
        $stmt = $conn->prepare($query['sql']);
        if (!empty($query['params'])) {
            $stmt->bind_param($query['types'], ...$query['params']);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $all_students[] = $row;
        }
        error_log("Query " . ($index + 1) . " records: " . $result->num_rows);
    }
    
    // เรียงลำดับ
    if ($type === 'all') {
        usort($all_students, function($a, $b) {
            $type_order = ['quota' => 1, 'regular' => 2];
            $type_compare = $type_order[$a['student_type']] - $type_order[$b['student_type']];
            if ($type_compare !== 0) return $type_compare;
            if ($a['level_order'] !== $b['level_order']) return $a['level_order'] - $b['level_order'];
            $dept_compare = strcmp($a['dept_name'], $b['dept_name']);
            if ($dept_compare !== 0) return $dept_compare;
            return strcmp($a['application_no'], $b['application_no']);
        });
    }
    
    if (empty($all_students)) {
        die('ไม่พบข้อมูลตามเงื่อนไขที่เลือก');
    }
    
    // สร้าง PDF
    $mpdf = createMpdfInstance('L');
    $mpdf->SetTitle('รายชื่อผู้สมัคร - วิทยาลัยอาชีวศึกษานครปฐม');
    setFooter($mpdf);
    
    $html = generateListHTML($conn, $all_students, $type, $academic_year, $level, $department_id);
    $mpdf->WriteHTML($html);
    
    // Output
    $type_text = ($type === 'all' ? 'ทั้งหมด' : ($type === 'quota' ? 'โควตา' : 'ปกติ'));
    $filename = 'รายชื่อผู้สมัคร_' . $type_text . '_' . date('Y-m-d_His') . '.pdf';
    
    if ($is_preview) {
        $mpdf->Output($filename, 'I');
    } else {
        $mpdf->Output($filename, 'D');
        logActivity($type, $academic_year, $level, $department_id, $status, 'list');
    }
}

// ==================== ฟังก์ชันสร้าง PDF สรุปจำนวน ====================
function generateSummaryPDF($conn, $type, $academic_year, $is_preview) {
    // ดึงข้อมูลสรุป
    $summary_data = getSummaryData($conn, $type, $academic_year);
    
    error_log("=== Export PDF Summary Debug ===");
    error_log("Type: $type | Year: $academic_year");
    error_log("Levels found: " . count($summary_data));
    
    if (empty($summary_data)) {
        die('ไม่พบข้อมูลตามเงื่อนไขที่เลือก');
    }
    
    // สร้าง PDF
    $mpdf = createMpdfInstance('L');
    $mpdf->SetTitle('สรุปจำนวนผู้สมัคร - วิทยาลัยอาชีวศึกษานครปฐม');
    setFooter($mpdf);
    
    $html = generateSummaryHTML($summary_data, $type, $academic_year);
    $mpdf->WriteHTML($html);
    
    // Output
    $type_text = ($type === 'all' ? 'ทั้งหมด' : ($type === 'quota' ? 'โควตา' : 'ปกติ'));
    $filename = 'สรุปจำนวนผู้สมัคร_' . $type_text . '_' . date('Y-m-d_His') . '.pdf';
    
    if ($is_preview) {
        $mpdf->Output($filename, 'I');
    } else {
        $mpdf->Output($filename, 'D');
        log_activity(
            $_SESSION['admin_id'],
            "Export สรุปจำนวนผู้สมัคร",
            null, null,
            "ประเภท: $type_text, ปีการศึกษา: $academic_year",
            null
        );
    }
}

// ==================== Helper Functions ====================

function createMpdfInstance($orientation = 'L') {
    $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
    $fontDirs = $defaultConfig['fontDir'];
    $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
    $fontData = $defaultFontConfig['fontdata'];
    
    return new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'orientation' => $orientation,
        'margin_left' => 8,
        'margin_right' => 8,
        'margin_top' => 8,
        'margin_bottom' => 5,
        'margin_header' => 5,
        'margin_footer' => 10,
        'fontDir' => array_merge($fontDirs, [__DIR__ . '/../../fonts']),
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
}

function setFooter($mpdf) {
    $mpdf->SetHTMLFooter('
    <table width="100%" style="font-family: thsarabun; font-size: 13px; color: #666; border-top: 1px solid #ddd; padding-top: 5px;">
        <tr>
            <td width="50%" style="text-align: left;">
                พิมพ์โดย: ' . htmlspecialchars($_SESSION['admin_fullname']) . ' | วันที่: ' . date('d/m/Y H:i:s') . '
            </td>
            <td width="50%" style="text-align: right;">
                หน้า {PAGENO} / {nbpg}
            </td>
        </tr>
    </table>
    ');
}

function buildQuery($type, $academic_year, $level, $department_id, $status, $conn) {
    $conditions = [];
    $params = [];
    $types = '';
    
    if ($academic_year !== 'all') {
        $conditions[] = "academic_year = ?";
        $params[] = $academic_year;
        $types .= 's';
    }
    
    if (!empty($department_id)) {
        $conditions[] = "department_id = ?";
        $params[] = $department_id;
        $types .= 'i';
    } else if (!empty($level)) {
        $conditions[] = "d.level = ?";
        $params[] = $level;
        $types .= 's';
    }
    
    if (!empty($status)) {
        $conditions[] = "status = ?";
        $params[] = $status;
        $types .= 's';
    }
    
    $where = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
    $queries = [];
    
    if ($type === 'quota' || $type === 'all') {
        $sql = "SELECT sq.*, d.code as dept_code, d.name_th as dept_name, d.level as dept_level,
                'quota' as student_type,
                CASE WHEN d.level = 'ปวช.' THEN 1 WHEN d.level = 'ปวส.' THEN 2 
                     WHEN d.level = 'ปริญญาตรี' THEN 3 ELSE 4 END as level_order
                FROM students_quota sq
                LEFT JOIN departments d ON sq.department_id = d.id
                {$where} ORDER BY level_order, d.name_th, sq.application_no";
        $queries[] = ['sql' => $sql, 'params' => $params, 'types' => $types];
    }
    
    if ($type === 'regular' || $type === 'all') {
        $sql = "SELECT sr.*, d.code as dept_code, d.name_th as dept_name, d.level as dept_level,
                'regular' as student_type,
                CASE WHEN d.level = 'ปวช.' THEN 1 WHEN d.level = 'ปวส.' THEN 2 
                     WHEN d.level = 'ปริญญาตรี' THEN 3 ELSE 4 END as level_order
                FROM students_regular sr
                LEFT JOIN departments d ON sr.department_id = d.id
                {$where} ORDER BY level_order, d.name_th, sr.application_no";
        $queries[] = ['sql' => $sql, 'params' => $params, 'types' => $types];
    }
    
    return $queries;
}

function logActivity($type, $academic_year, $level, $department_id, $status, $format) {
    global $conn;
    $type_text = ($type === 'all' ? 'ทั้งหมด' : ($type === 'quota' ? 'โควตา' : 'ปกติ'));
    $filter_info = ["ประเภท: $type_text", "ปีการศึกษา: " . ($academic_year === 'all' ? 'ทั้งหมด' : $academic_year)];
    
    if (!empty($level)) $filter_info[] = "ระดับ: $level";
    if (!empty($department_id)) {
        $dept_sql = "SELECT name_th FROM departments WHERE id = ?";
        $dept_stmt = $conn->prepare($dept_sql);
        $dept_stmt->bind_param('i', $department_id);
        $dept_stmt->execute();
        if ($dept_row = $dept_stmt->get_result()->fetch_assoc()) {
            $filter_info[] = "สาขา: " . $dept_row['name_th'];
        }
    }
    if (!empty($status)) $filter_info[] = "สถานะ: $status";
    
    log_activity($_SESSION['admin_id'], "Export PDF รายชื่อผู้สมัคร", null, null, implode(', ', $filter_info), null);
}

// ==================== HTML Generation Functions ====================

function generateListHTML($conn, $students, $type, $academic_year, $level, $department_id) {
    $total = count($students);
    $type_text = ($type === 'all' ? 'ทั้งหมด' : ($type === 'quota' ? 'รอบโควตา' : 'รอบปกติ'));
    $year_text = ($academic_year === 'all' ? 'ทุกปีการศึกษา' : 'ปีการศึกษา ' . $academic_year);
    
    $level_text = !empty($level) ? " | <strong>ระดับ:</strong> $level" : '';
    $dept_text = '';
    if (!empty($department_id)) {
        $dept_sql = "SELECT code, name_th FROM departments WHERE id = ?";
        $dept_stmt = $conn->prepare($dept_sql);
        $dept_stmt->bind_param('i', $department_id);
        $dept_stmt->execute();
        if ($dept_row = $dept_stmt->get_result()->fetch_assoc()) {
            $dept_text = " | <strong>สาขา:</strong> " . $dept_row['code'] . ' - ' . $dept_row['name_th'];
        }
    }
    
    $show_type = ($type === 'all');
    $colspan = $show_type ? 8 : 7;
    
    $html = '<!DOCTYPE html><html><head><meta charset="utf-8"><style>
        body { font-family: "thsarabun", sans-serif; font-size: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        thead tr th { background-color: #34495e; color: white; padding: 10px 5px; text-align: center; 
                      font-size: 14px; border: 1px solid #2c3e50; font-weight: bold; }
        tbody tr td { padding: 8px 5px; border: 1px solid #ddd; font-size: 13px; vertical-align: middle; }
        tbody tr:nth-child(even) { background-color: #f8f9fa; }
        .text-center { text-align: center; }
        .type-badge, .status-badge { padding: 3px 8px; border-radius: 3px; font-size: 11px; 
                                     font-weight: bold; display: inline-block; }
        .type-quota { background-color: #e3f2fd; color: #1565c0; }
        .type-regular { background-color: #f3e5f5; color: #6a1b9a; }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-approved { background-color: #d1e7dd; color: #0f5132; }
        .status-rejected { background-color: #f8d7da; color: #842029; }
        .status-cancelled { background-color: #e2e3e5; color: #41464b; }
    </style></head><body>';
    
    $html .= '<div style="text-align: center; margin-bottom: 15px; border-bottom: 2px solid #333; padding-bottom: 10px;">
        <h1 style="font-size: 22px; margin: 5px 0; color: #2c3e50; font-weight: bold;">รายชื่อผู้สมัครเข้าเรียน</h1>
        <h2 style="font-size: 18px; margin: 5px 0; color: #555;">วิทยาลัยอาชีวศึกษานครปฐม ' . $year_text . '</h2>
    </div>';
    
    $html .= '<table><thead><tr><td colspan="' . $colspan . '" style="background-color: #f8f9fa; padding: 10px; border: 1px solid #ddd; font-size: 14px; color: #333;">
        <strong>ประเภท:</strong> ' . $type_text . ' | <strong>ปีการศึกษา:</strong> ' . $year_text . $level_text . $dept_text . ' | 
        <strong>จำนวนทั้งหมด:</strong> ' . number_format($total) . ' คน | <strong>วันที่พิมพ์:</strong> ' . date('d/m/Y H:i:s') . '
    </td></tr>';
    
    $html .= '<tr><th width="4%">ลำดับ</th>';
    if ($show_type) $html .= '<th width="8%">ประเภท</th>';
    $html .= '<th width="10%">เลขที่สมัคร</th>
              <th width="' . ($show_type ? '20%' : '23%') . '">ชื่อ-สกุล</th>
              <th width="8%">ระดับ</th>
              <th width="' . ($show_type ? '23%' : '25%') . '">สาขาวิชา</th>
              <th width="8%">เกรด</th>
              <th width="12%">สถานะ</th></tr></thead><tbody>';
    
    $no = 1;
    $type_labels = ['quota' => 'โควตา', 'regular' => 'ปกติ'];
    $status_labels = ['pending' => 'รอตรวจสอบ', 'approved' => 'อนุมัติ', 'rejected' => 'ไม่อนุมัติ', 'cancelled' => 'ยกเลิก'];
    
    foreach ($students as $s) {
        $fullname = ($s['prefix'] ?? '') . ' ' . ($s['firstname_th'] ?? '') . ' ' . ($s['lastname_th'] ?? '');
        $st = $s['student_type'] ?? 'quota';
        $status = $s['status'] ?? 'pending';
        
        $html .= '<tr><td class="text-center">' . $no++ . '</td>';
        if ($show_type) {
            $html .= '<td class="text-center"><span class="type-badge type-' . $st . '">' . $type_labels[$st] . '</span></td>';
        }
        $html .= '<td class="text-center">' . htmlspecialchars($s['application_no'] ?? '') . '</td>
                  <td>' . htmlspecialchars($fullname) . '</td>
                  <td class="text-center">' . htmlspecialchars($s['dept_level'] ?? '') . '</td>
                  <td>' . htmlspecialchars(($s['dept_code'] ?? '') . ' - ' . ($s['dept_name'] ?? '')) . '</td>
                  <td class="text-center">' . number_format($s['gpa'] ?? 0, 2) . '</td>
                  <td class="text-center"><span class="status-badge status-' . $status . '">' . $status_labels[$status] . '</span></td>
                  </tr>';
    }
    
    $html .= '</tbody></table></body></html>';
    return $html;
}

function getSummaryData($conn, $type, $academic_year) {
    $summary = [];
    $levels = ['ปวช.', 'ปวส.', 'ปริญญาตรี'];
    
    foreach ($levels as $level) {
        $dept_sql = "SELECT id, code, name_th FROM departments WHERE level = ? ORDER BY code";
        $dept_stmt = $conn->prepare($dept_sql);
        $dept_stmt->bind_param('s', $level);
        $dept_stmt->execute();
        $dept_result = $dept_stmt->get_result();
        
        $level_data = ['level' => $level, 'departments' => []];
        
        while ($dept = $dept_result->fetch_assoc()) {
            $dept_id = $dept['id'];
            $dept_info = [
                'code' => $dept['code'],
                'name' => $dept['name_th'],
                'quota' => ['approved' => 0, 'pending' => 0, 'rejected' => 0, 'cancelled' => 0, 'total' => 0],
                'regular' => ['approved' => 0, 'pending' => 0, 'rejected' => 0, 'cancelled' => 0, 'total' => 0],
                'grand_total' => 0
            ];
            
            if ($type === 'quota' || $type === 'all') {
                $quota_sql = "SELECT status, COUNT(*) as count FROM students_quota 
                             WHERE department_id = ? AND academic_year = ? GROUP BY status";
                $quota_stmt = $conn->prepare($quota_sql);
                $quota_stmt->bind_param('is', $dept_id, $academic_year);
                $quota_stmt->execute();
                $quota_result = $quota_stmt->get_result();
                while ($row = $quota_result->fetch_assoc()) {
                    $dept_info['quota'][$row['status']] = $row['count'];
                    $dept_info['quota']['total'] += $row['count'];
                }
            }
            
            if ($type === 'regular' || $type === 'all') {
                $regular_sql = "SELECT status, COUNT(*) as count FROM students_regular 
                               WHERE department_id = ? AND academic_year = ? GROUP BY status";
                $regular_stmt = $conn->prepare($regular_sql);
                $regular_stmt->bind_param('is', $dept_id, $academic_year);
                $regular_stmt->execute();
                $regular_result = $regular_stmt->get_result();
                while ($row = $regular_result->fetch_assoc()) {
                    $dept_info['regular'][$row['status']] = $row['count'];
                    $dept_info['regular']['total'] += $row['count'];
                }
            }
            
            $dept_info['grand_total'] = $dept_info['quota']['total'] + $dept_info['regular']['total'];
            if ($dept_info['grand_total'] > 0) {
                $level_data['departments'][] = $dept_info;
            }
        }
        
        if (!empty($level_data['departments'])) {
            $summary[] = $level_data;
        }
    }
    
    return $summary;
}

function generateSummaryHTML($summary_data, $type, $academic_year) {
    $type_text = ($type === 'all' ? 'ทั้งหมด' : ($type === 'quota' ? 'รอบโควตา' : 'รอบปกติ'));
    $year_text = ($academic_year === 'all' ? 'ทุกปีการศึกษา' : 'ปีการศึกษา ' . $academic_year);
    $show_both = ($type === 'all');
    
    $html = '<!DOCTYPE html><html><head><meta charset="utf-8"><style>
        body { font-family: "thsarabun", sans-serif; font-size: 16px; }
        .header { text-align: center; margin-bottom: 15px; border-bottom: 3px solid #2c3e50; padding-bottom: 10px; }
        .header h1 { font-size: 24px; margin: 5px 0; color: #2c3e50; font-weight: bold; }
        .header h2 { font-size: 20px; margin: 5px 0; color: #555; }
        .level-section { margin: 20px 0; }
        .level-header { background-color: #34495e; color: white; padding: 8px 15px; font-size: 18px; 
                        font-weight: bold; border-radius: 5px 5px 0 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        thead tr th { background-color: #f8f9fa; color: #333; padding: 8px 5px; text-align: center; 
                      font-size: 14px; border: 1px solid #dee2e6; font-weight: bold; }
        thead tr.subheader th { background-color: #e9ecef; color: #333; font-size: 13px; padding: 6px 3px; }
        .header-quota { background-color: #cfe2ff !important; color: #084298 !important; }
        .header-regular { background-color: #d1e7dd !important; color: #0a3622 !important; }
        tbody tr td { padding: 8px 5px; border: 1px solid #dee2e6; font-size: 14px; text-align: center; }
        tbody tr:nth-child(even) { background-color: #ffffff; }
        tbody tr:nth-child(odd) { background-color: #f8f9fa; }
        .text-left { text-align: left !important; padding-left: 10px; }
        .total-row { background-color: #e9ecef !important; font-weight: bold; color: #333 !important; }
        .grand-total-row { background-color: #dee2e6 !important; font-weight: bold; font-size: 15px; color: #333 !important; }
    </style></head><body>';
    
    $html .= '<div class="header">
        <h1 style="font-size: 22px; margin: 5px 0; color: #2c3e50; font-weight: bold;">สรุปจำนวนผู้สมัครเข้าเรียน</h1>
        <h2 style="font-size: 18px; margin: 5px 0; color: #555;">วิทยาลัยอาชีวศึกษานครปฐม ' . $year_text . '</h2>
        <p style="margin: 5px 0; font-size: 16px; color: #666;">ประเภท: ' . $type_text . '</p>
    </div>';
    
    $grand_totals = [
        'quota' => ['approved' => 0, 'pending' => 0, 'rejected' => 0, 'cancelled' => 0, 'total' => 0],
        'regular' => ['approved' => 0, 'pending' => 0, 'rejected' => 0, 'cancelled' => 0, 'total' => 0],
        'overall' => 0
    ];
    
    foreach ($summary_data as $index => $level_data) {
        $level = $level_data['level'];
        $departments = $level_data['departments'];
        
        if ($index > 0) {
            $html .= '<pagebreak />';
        }
        
        $html .= '<div class="level-section"><div class="level-header">ระดับ ' . $level . '</div><table><thead><tr>
            <th rowspan="2" width="5%">#</th>
            <th rowspan="2" width="10%">รหัสสาขา</th>
            <th rowspan="2" width="' . ($show_both ? '20%' : '30%') . '">ชื่อสาขาวิชา</th>';
        
        if ($show_both) {
            $html .= '<th colspan="5" class="header-quota">รอบโควตา</th>
                      <th colspan="5" class="header-regular">รอบปกติ</th>
                      <th rowspan="2" width="6%">รวมทั้งหมด</th>';
        } else if ($type === 'quota') {
            $html .= '<th colspan="5" class="header-quota">รอบโควตา</th>';
        } else {
            $html .= '<th colspan="5" class="header-regular">รอบปกติ</th>';
        }
        
        $html .= '</tr><tr class="subheader">';
        
        if ($show_both) {
            $html .= '<th width="5%">อนุมัติ</th><th width="5%">รอตรวจสอบ</th><th width="5%">ไม่อนุมัติ</th>
                      <th width="5%">ยกเลิก</th><th width="5%">รวม</th>
                      <th width="5%">อนุมัติ</th><th width="5%">รอตรวจสอบ</th><th width="5%">ไม่อนุมัติ</th>
                      <th width="5%">ยกเลิก</th><th width="5%">รวม</th>';
        } else {
            $html .= '<th width="8%">อนุมัติ</th><th width="8%">รอตรวจสอบ</th><th width="8%">ไม่อนุมัติ</th>
                      <th width="8%">ยกเลิก</th><th width="8%">รวม</th>';
        }
        
        $html .= '</tr></thead><tbody>';
        
        $no = 1;
        $level_totals = [
            'quota' => ['approved' => 0, 'pending' => 0, 'rejected' => 0, 'cancelled' => 0, 'total' => 0],
            'regular' => ['approved' => 0, 'pending' => 0, 'rejected' => 0, 'cancelled' => 0, 'total' => 0],
            'overall' => 0
        ];
        
        foreach ($departments as $dept) {
            $html .= '<tr><td>' . $no++ . '</td>
                      <td>' . htmlspecialchars($dept['code']) . '</td>
                      <td class="text-left">' . htmlspecialchars($dept['name']) . '</td>';
            
            if ($show_both || $type === 'quota') {
                $html .= '<td>' . number_format($dept['quota']['approved']) . '</td>
                          <td>' . number_format($dept['quota']['pending']) . '</td>
                          <td>' . number_format($dept['quota']['rejected']) . '</td>
                          <td>' . number_format($dept['quota']['cancelled']) . '</td>
                          <td><strong>' . number_format($dept['quota']['total']) . '</strong></td>';
                
                $level_totals['quota']['approved'] += $dept['quota']['approved'];
                $level_totals['quota']['pending'] += $dept['quota']['pending'];
                $level_totals['quota']['rejected'] += $dept['quota']['rejected'];
                $level_totals['quota']['cancelled'] += $dept['quota']['cancelled'];
                $level_totals['quota']['total'] += $dept['quota']['total'];
            }
            
            if ($show_both || $type === 'regular') {
                $html .= '<td>' . number_format($dept['regular']['approved']) . '</td>
                          <td>' . number_format($dept['regular']['pending']) . '</td>
                          <td>' . number_format($dept['regular']['rejected']) . '</td>
                          <td>' . number_format($dept['regular']['cancelled']) . '</td>
                          <td><strong>' . number_format($dept['regular']['total']) . '</strong></td>';
                
                $level_totals['regular']['approved'] += $dept['regular']['approved'];
                $level_totals['regular']['pending'] += $dept['regular']['pending'];
                $level_totals['regular']['rejected'] += $dept['regular']['rejected'];
                $level_totals['regular']['cancelled'] += $dept['regular']['cancelled'];
                $level_totals['regular']['total'] += $dept['regular']['total'];
            }
            
            if ($show_both) {
                $html .= '<td><strong>' . number_format($dept['grand_total']) . '</strong></td>';
                $level_totals['overall'] += $dept['grand_total'];
            }
            
            $html .= '</tr>';
        }
        
        // รวมแต่ละระดับ
        $html .= '<tr class="total-row"><td colspan="3" class="text-left">รวม ' . $level . '</td>';
        
        if ($show_both || $type === 'quota') {
            $html .= '<td>' . number_format($level_totals['quota']['approved']) . '</td>
                      <td>' . number_format($level_totals['quota']['pending']) . '</td>
                      <td>' . number_format($level_totals['quota']['rejected']) . '</td>
                      <td>' . number_format($level_totals['quota']['cancelled']) . '</td>
                      <td><strong>' . number_format($level_totals['quota']['total']) . '</strong></td>';
        }
        
        if ($show_both || $type === 'regular') {
            $html .= '<td>' . number_format($level_totals['regular']['approved']) . '</td>
                      <td>' . number_format($level_totals['regular']['pending']) . '</td>
                      <td>' . number_format($level_totals['regular']['rejected']) . '</td>
                      <td>' . number_format($level_totals['regular']['cancelled']) . '</td>
                      <td><strong>' . number_format($level_totals['regular']['total']) . '</strong></td>';
        }
        
        if ($show_both) {
            $html .= '<td><strong>' . number_format($level_totals['overall']) . '</strong></td>';
        }
        
        $html .= '</tr></tbody></table></div>';
        
        // สะสมยอดรวมทั้งหมด
        $grand_totals['quota']['approved'] += $level_totals['quota']['approved'];
        $grand_totals['quota']['pending'] += $level_totals['quota']['pending'];
        $grand_totals['quota']['rejected'] += $level_totals['quota']['rejected'];
        $grand_totals['quota']['cancelled'] += $level_totals['quota']['cancelled'];
        $grand_totals['quota']['total'] += $level_totals['quota']['total'];
        
        $grand_totals['regular']['approved'] += $level_totals['regular']['approved'];
        $grand_totals['regular']['pending'] += $level_totals['regular']['pending'];
        $grand_totals['regular']['rejected'] += $level_totals['regular']['rejected'];
        $grand_totals['regular']['cancelled'] += $level_totals['regular']['cancelled'];
        $grand_totals['regular']['total'] += $level_totals['regular']['total'];
        
        $grand_totals['overall'] += $level_totals['overall'];
    }

    // สรุปยอดรวมทั้งหมด - บังคับให้อยู่หน้าสุดท้าย
    $html .= '<pagebreak />';

    $html .= '<div class="header">
        <h1 style="font-size: 22px; margin: 5px 0; color: #2c3e50; font-weight: bold;">สรุปยอดรวมการสมัครในระบบออนไลน์ทั้งหมด</h1>
        <h2 style="font-size: 18px; margin: 5px 0; color: #555;">วิทยาลัยอาชีวศึกษานครปฐม ' . $year_text . '</h2>
    </div>';
    
    $html .= '<table><thead><tr>
        <th colspan="3" style="background-color: #d5d5d5ff; font-size: 18px; padding: 10px;">
            สรุปยอดรวมทั้งหมด
        </th>';
    
    if ($show_both || $type === 'quota') {
        $html .= '<th colspan="5" class="header-quota">รอบโควตา</th>';
    }
    
    if ($show_both || $type === 'regular') {
        $html .= '<th colspan="5" class="header-regular">รอบปกติ</th>';
    }
    
    if ($show_both) {
        $html .= '<th rowspan="2" style="background-color: #d5d5d5ff;">รวมทั้งหมด</th>';
    }
    
    $html .= '</tr><tr class="subheader">';
    
    if ($show_both || $type === 'quota') {
        $html .= '<th colspan="3"></th>
                  <th>อนุมัติ</th><th>รอตรวจสอบ</th><th>ไม่อนุมัติ</th><th>ยกเลิก</th><th>รวม</th>';
    }
    
    if ($show_both || $type === 'regular') {
        if (!($show_both || $type === 'quota')) {
            $html .= '<th colspan="3"></th>';
        }
        $html .= '<th>อนุมัติ</th><th>รอตรวจสอบ</th><th>ไม่อนุมัติ</th><th>ยกเลิก</th><th>รวม</th>';
    }
    
    $html .= '</tr></thead><tbody><tr style="background-color: #ffffff !important;">
        <td colspan="3" class="text-left" style="padding-left: 20px; font-weight: bold; font-size: 15px;">รวมทุกระดับ</td>';
    
    if ($show_both || $type === 'quota') {
        $html .= '<td style="font-weight: bold;">' . number_format($grand_totals['quota']['approved']) . '</td>
                  <td style="font-weight: bold;">' . number_format($grand_totals['quota']['pending']) . '</td>
                  <td style="font-weight: bold;">' . number_format($grand_totals['quota']['rejected']) . '</td>
                  <td style="font-weight: bold;">' . number_format($grand_totals['quota']['cancelled']) . '</td>
                  <td style="font-weight: bold; font-size: 15px;">' . number_format($grand_totals['quota']['total']) . '</td>';
    }
    
    if ($show_both || $type === 'regular') {
        $html .= '<td style="font-weight: bold;">' . number_format($grand_totals['regular']['approved']) . '</td>
                  <td style="font-weight: bold;">' . number_format($grand_totals['regular']['pending']) . '</td>
                  <td style="font-weight: bold;">' . number_format($grand_totals['regular']['rejected']) . '</td>
                  <td style="font-weight: bold;">' . number_format($grand_totals['regular']['cancelled']) . '</td>
                  <td style="font-weight: bold; font-size: 15px;">' . number_format($grand_totals['regular']['total']) . '</td>';
    }
    
    if ($show_both) {
        $html .= '<td style="font-weight: bold; font-size: 16px;">' . number_format($grand_totals['overall']) . '</td>';
    }
    
    $html .= '</tr></tbody></table></body></html>';
    
    return $html;
}
?>