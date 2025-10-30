<?php
/**
 * API สำหรับล้างข้อมูลระบบ
 * NC-Admission - Nakhon Pathom College Admission System
 */

session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// ตรวจสอบ Authentication & Permission
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_role'] !== 'superadmin') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'ไม่มีสิทธิ์เข้าถึง'
    ]);
    exit();
}

// รับข้อมูล JSON
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

$response = ['success' => false, 'message' => 'Invalid action'];

// ==================== Clear Single Table ====================
if ($action === 'clear_table') {
    $table = $input['table'] ?? '';
    
    // Whitelist ตารางที่อนุญาตให้ลบ
    $allowed_tables = [
        'students_quota',
        'students_regular',
        'news',
        'gallery',
        'activity_logs'
    ];
    
    if (!in_array($table, $allowed_tables)) {
        $response = [
            'success' => false,
            'message' => 'ไม่อนุญาตให้ลบตารางนี้'
        ];
    } else {
        // นับข้อมูลก่อนลบ
        $count_sql = "SELECT COUNT(*) as count FROM {$table}";
        $count_result = $conn->query($count_sql);
        $count = $count_result ? $count_result->fetch_assoc()['count'] : 0;
        
        // เริ่ม Transaction
        $conn->begin_transaction();
        
        try {
            // สำรองข้อมูลก่อนลบ (เก็บไว้ใน deleted_records)
            if ($count > 0) {
                $backup_sql = "SELECT * FROM {$table}";
                $backup_result = $conn->query($backup_sql);
                $backup_data = [];
                
                while ($row = $backup_result->fetch_assoc()) {
                    $backup_data[] = $row;
                }
                
                // บันทึกข้อมูลลงตาราง deleted_records
                $backup_json = json_encode($backup_data, JSON_UNESCAPED_UNICODE);
                $reason = "ล้างข้อมูลโดย Superadmin ({$_SESSION['admin_username']})";
                
                $insert_sql = "INSERT INTO deleted_records 
                               (record_type, deleted_count, deleted_by, reason, backup_data) 
                               VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($insert_sql);
                
                // แปลงชื่อตารางเป็น record_type
                $record_type = ($table === 'students_quota') ? 'quota' : 
                              (($table === 'students_regular') ? 'regular' : 
                              (($table === 'news') ? 'news' : 
                              (($table === 'gallery') ? 'gallery' : $table)));
                
                $stmt->bind_param('siiss', $record_type, $count, $_SESSION['admin_id'], $reason, $backup_json);
                $stmt->execute();
            }
            
            // ลบข้อมูลไฟล์ก่อน (ถ้ามี)
            if ($table === 'students_quota' || $table === 'students_regular') {
                $files_sql = "SELECT photo_path, transcript_path FROM {$table}";
                $files_result = $conn->query($files_sql);
                
                while ($file_row = $files_result->fetch_assoc()) {
                    // ลบรูปถ่าย
                    if (!empty($file_row['photo_path'])) {
                        $photo_full_path = "../../" . $file_row['photo_path'];
                        if (file_exists($photo_full_path)) {
                            @unlink($photo_full_path);
                        }
                    }
                    
                    // ลบไฟล์ผลการเรียน
                    if (!empty($file_row['transcript_path'])) {
                        $transcript_full_path = "../../" . $file_row['transcript_path'];
                        if (file_exists($transcript_full_path)) {
                            @unlink($transcript_full_path);
                        }
                    }
                }
            }
            
            // ลบรูปภาพแกลเลอรี่
            if ($table === 'gallery') {
                $files_sql = "SELECT image_url FROM {$table} WHERE image_url LIKE 'uploads/gallery/%'";
                $files_result = $conn->query($files_sql);
                
                while ($file_row = $files_result->fetch_assoc()) {
                    $image_path = "../../" . $file_row['image_url'];
                    if (file_exists($image_path)) {
                        @unlink($image_path);
                    }
                }
            }
            
            // ลบรูปข่าว
            if ($table === 'news') {
                $files_sql = "SELECT featured_image FROM {$table} WHERE featured_image LIKE 'uploads/news/%'";
                $files_result = $conn->query($files_sql);
                
                while ($file_row = $files_result->fetch_assoc()) {
                    $image_path = "../../" . $file_row['featured_image'];
                    if (file_exists($image_path)) {
                        @unlink($image_path);
                    }
                }
            }
            
            // ลบข้อมูลในตาราง
            $delete_sql = "TRUNCATE TABLE {$table}";
            $conn->query($delete_sql);
            
            // บันทึก Activity Log
            $table_names = [
                'students_quota' => 'ใบสมัครรอบโควตา',
                'students_regular' => 'ใบสมัครรอบปกติ',
                'news' => 'ข่าวประชาสัมพันธ์',
                'gallery' => 'คลังภาพ',
                'activity_logs' => 'ประวัติการใช้งาน'
            ];
            
            log_activity(
                $_SESSION['admin_id'],
                "ล้างข้อมูล {$table_names[$table]} ทั้งหมด ({$count} รายการ)",
                'settings',
                null,
                null,
                null
            );
            
            $conn->commit();
            
            $response = [
                'success' => true,
                'message' => "ลบข้อมูล <strong>{$table_names[$table]}</strong> สำเร็จ<br><small class='text-muted'>ลบไปทั้งหมด {$count} รายการ</small>"
            ];
            
        } catch (Exception $e) {
            $conn->rollback();
            $response = [
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ];
        }
    }
}

// ==================== Clear All Data ====================
elseif ($action === 'clear_all') {
    $tables = [
        'students_quota',
        'students_regular',
        'news',
        'gallery',
        'activity_logs'
    ];
    
    $conn->begin_transaction();
    
    try {
        $total_deleted = 0;
        $details = [];
        
        foreach ($tables as $table) {
            // นับข้อมูล
            $count_sql = "SELECT COUNT(*) as count FROM {$table}";
            $count_result = $conn->query($count_sql);
            $count = $count_result ? $count_result->fetch_assoc()['count'] : 0;
            
            if ($count > 0) {
                // ลบไฟล์ (เหมือนด้านบน)
                if ($table === 'students_quota' || $table === 'students_regular') {
                    $files_sql = "SELECT photo_path, transcript_path FROM {$table}";
                    $files_result = $conn->query($files_sql);
                    
                    while ($file_row = $files_result->fetch_assoc()) {
                        if (!empty($file_row['photo_path'])) {
                            @unlink("../../" . $file_row['photo_path']);
                        }
                        if (!empty($file_row['transcript_path'])) {
                            @unlink("../../" . $file_row['transcript_path']);
                        }
                    }
                } elseif ($table === 'gallery') {
                    $files_sql = "SELECT image_url FROM {$table} WHERE image_url LIKE 'uploads/gallery/%'";
                    $files_result = $conn->query($files_sql);
                    while ($file_row = $files_result->fetch_assoc()) {
                        @unlink("../../" . $file_row['image_url']);
                    }
                } elseif ($table === 'news') {
                    $files_sql = "SELECT featured_image FROM {$table} WHERE featured_image LIKE 'uploads/news/%'";
                    $files_result = $conn->query($files_sql);
                    while ($file_row = $files_result->fetch_assoc()) {
                        @unlink("../../" . $file_row['featured_image']);
                    }
                }
                
                // ลบข้อมูล
                $conn->query("TRUNCATE TABLE {$table}");
                
                $total_deleted += $count;
                $details[] = "{$table}: {$count} รายการ";
            }
        }
        
        // บันทึก Activity Log
        log_activity(
            $_SESSION['admin_id'],
            "ล้างข้อมูลทั้งระบบ (ทั้งหมด {$total_deleted} รายการ)",
            'settings',
            null,
            null,
            null
        );
        
        $conn->commit();
        
        $response = [
            'success' => true,
            'message' => "ล้างข้อมูลทั้งระบบสำเร็จ<br><small class='text-muted'>ลบไปทั้งหมด {$total_deleted} รายการ</small>"
        ];
        
    } catch (Exception $e) {
        $conn->rollback();
        $response = [
            'success' => false,
            'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
        ];
    }
}

// ส่ง Response
header('Content-Type: application/json');
echo json_encode($response);