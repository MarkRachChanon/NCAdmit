<?php

/**
 * API: บันทึกการตั้งค่าระบบ
 * NC-Admission - Nakhon Pathom College Admission System
 */

// ✅ ป้องกัน output ที่ไม่ต้องการ
ob_start();

session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Clear output buffer
ob_end_clean();

header('Content-Type: application/json; charset=utf-8');

// ✅ Error Handler
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาดของระบบ: ' . $errstr
    ], JSON_UNESCAPED_UNICODE);
    exit();
});

try {
    // ตรวจสอบการเข้าสู่ระบบ
    if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_logged_in'])) {
        throw new Exception('กรุณาเข้าสู่ระบบ');
    }

    // ตรวจสอบสิทธิ์ (เฉพาะ superadmin และ admin)
    $allowed_roles = ['superadmin', 'admin'];
    if (!in_array($_SESSION['admin_role'], $allowed_roles)) {
        throw new Exception('คุณไม่มีสิทธิ์ในการแก้ไขการตั้งค่าระบบ');
    }

    // ตรวจสอบ Method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }

    // กำหนด settings ที่อนุญาตให้แก้ไข
    $allowed_settings = [
        // ข้อมูลทั่วไป
        'site_name' => ['type' => 'text', 'required' => true],
        'site_description' => ['type' => 'text', 'required' => false],
        'google_map_url' => ['type' => 'text', 'required' => false],

        // ข้อมูลติดต่อ
        'contact_phone' => ['type' => 'text', 'required' => false],
        'contact_email' => ['type' => 'email', 'required' => false],
        'contact_address' => ['type' => 'textarea', 'required' => false],

        // โซเชียลมีเดีย
        'facebook_url' => ['type' => 'text', 'required' => false],
        'youtube_url' => ['type' => 'text', 'required' => false],
        'tiktok_url' => ['type' => 'text', 'required' => false],

        // การรับสมัคร
        'quota_open' => ['type' => 'boolean', 'required' => false],
        'regular_open' => ['type' => 'boolean', 'required' => false],
        'academic_year' => ['type' => 'text', 'required' => true],

        // กำหนดการรับสมัคร - รอบโควต้า
        'quota_start_date' => ['type' => 'date', 'required' => false],
        'quota_end_date' => ['type' => 'date', 'required' => false],
        'quota_announce_date' => ['type' => 'date', 'required' => false],
        'quota_confirm_start' => ['type' => 'date', 'required' => false],
        'quota_confirm_end' => ['type' => 'date', 'required' => false],
        'quota_report_date' => ['type' => 'date', 'required' => false],

        // กำหนดการรับสมัคร - รอบปกติ
        'regular_start_date' => ['type' => 'date', 'required' => false],
        'regular_end_date' => ['type' => 'date', 'required' => false],
        'regular_announce_date' => ['type' => 'date', 'required' => false],
        'regular_confirm_start' => ['type' => 'date', 'required' => false],
        'regular_confirm_end' => ['type' => 'date', 'required' => false],
        'regular_report_date' => ['type' => 'date', 'required' => false],

        // Google Drive
        'google_drive_folder_quota' => ['type' => 'text', 'required' => false],
        'google_drive_folder_regular' => ['type' => 'text', 'required' => false],

        // สถิติ
        'stat_students' => ['type' => 'number', 'required' => false],
        'stat_departments' => ['type' => 'number', 'required' => false],
        'stat_teachers' => ['type' => 'number', 'required' => false],
        'stat_employment' => ['type' => 'number', 'required' => false]
    ];

    // Validate ข้อมูล
    $errors = [];
    $settings_to_update = [];

    foreach ($allowed_settings as $key => $config) {
        $value = isset($_POST[$key]) ? trim($_POST[$key]) : '';

        // ตรวจสอบ required
        if ($config['required'] && empty($value)) {
            $errors[] = "กรุณากรอก {$key}";
            continue;
        }

        // แปลงค่าตาม type
        switch ($config['type']) {
            case 'boolean':
                $value = ($value === '1' || $value === 'on') ? '1' : '0';
                break;

            case 'number':
                $value = intval($value);
                if ($value < 0) $value = 0;
                break;

            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = "อีเมลไม่ถูกต้อง: {$key}";
                    continue 2;
                }
                break;

            case 'date':
                if (!empty($value)) {
                    $date_obj = DateTime::createFromFormat('Y-m-d', $value);
                    if (!$date_obj || $date_obj->format('Y-m-d') !== $value) {
                        $errors[] = "วันที่ไม่ถูกต้อง: {$key}";
                        continue 2;
                    }
                }
                break;
        }

        $settings_to_update[$key] = $value;
    }

    // ถ้ามี Error
    if (!empty($errors)) {
        throw new Exception(implode('<br>', $errors));
    }

    // เริ่ม Transaction
    $conn->begin_transaction();

    $updated_count = 0;
    $changes_log = [];

    foreach ($settings_to_update as $key => $value) {
        // ดึงค่าเก่า
        $old_sql = "SELECT setting_value FROM settings WHERE setting_key = ?";
        $old_stmt = $conn->prepare($old_sql);
        $old_stmt->bind_param("s", $key);
        $old_stmt->execute();
        $old_result = $old_stmt->get_result();
        $old_value = '';

        if ($old_result->num_rows > 0) {
            $old_row = $old_result->fetch_assoc();
            $old_value = $old_row['setting_value'];
        }
        $old_stmt->close();

        // อัพเดทค่าใหม่
        $update_sql = "UPDATE settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?";
        $update_stmt = $conn->prepare($update_sql);

        if (!$update_stmt) {
            throw new Exception('ไม่สามารถเตรียม statement ได้: ' . $conn->error);
        }

        $update_stmt->bind_param("ss", $value, $key);

        if ($update_stmt->execute()) {
            if ($update_stmt->affected_rows > 0) {
                $updated_count++;

                // บันทึกการเปลี่ยนแปลง
                if ($old_value !== $value) {
                    $changes_log[] = [
                        'key' => $key,
                        'old' => $old_value,
                        'new' => $value
                    ];
                }
            }
        } else {
            throw new Exception("ไม่สามารถอัพเดท {$key} ได้: " . $update_stmt->error);
        }

        $update_stmt->close();
    }

    // บันทึก Activity Log
    if (!empty($changes_log)) {
        $log_message = "อัพเดทการตั้งค่าระบบ (" . count($changes_log) . " รายการ)";

        log_activity(
            $_SESSION['admin_id'],
            $log_message,
            'settings',
            null,
            json_encode($changes_log, JSON_UNESCAPED_UNICODE),
            json_encode($settings_to_update, JSON_UNESCAPED_UNICODE)
        );
    }

    // Commit Transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => "บันทึกการตั้งค่าเรียบร้อยแล้ว ({$updated_count} รายการ)",
        'data' => [
            'updated_count' => $updated_count,
            'changes_count' => count($changes_log)
        ]
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    // Rollback Transaction
    if (isset($conn) && $conn->ping()) {
        $conn->rollback();
    }

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

if (isset($conn)) {
    $conn->close();
}
