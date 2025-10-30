<?php
/**
 * หน้าล้างข้อมูลระบบ
 * NC-Admission - Nakhon Pathom College Admission System
 */

// ตรวจสอบสิทธิ์การเข้าถึง (เฉพาะ superadmin)
if (!check_page_permission('clear_data', $admin_role) || $admin_role !== 'superadmin') {
    header('Location: index.php?page=dashboard');
    exit();
}

// นับจำนวนข้อมูลในแต่ละตาราง
$tables_info = [
    'students_quota' => [
        'name' => 'ใบสมัครรอบโควตา',
        'icon' => 'bi-file-earmark-person',
        'color' => 'primary',
        'description' => 'ข้อมูลการสมัครเข้าเรียนรอบโควตาทั้งหมด'
    ],
    'students_regular' => [
        'name' => 'ใบสมัครรอบปกติ',
        'icon' => 'bi-file-earmark-text',
        'color' => 'info',
        'description' => 'ข้อมูลการสมัครเข้าเรียนรอบปกติทั้งหมด'
    ],
    'news' => [
        'name' => 'ข่าวประชาสัมพันธ์',
        'icon' => 'bi-newspaper',
        'color' => 'success',
        'description' => 'ข่าวสารและประกาศต่างๆ'
    ],
    'gallery' => [
        'name' => 'คลังภาพ',
        'icon' => 'bi-images',
        'color' => 'warning',
        'description' => 'รูปภาพกิจกรรมและผลงาน'
    ],
    'activity_logs' => [
        'name' => 'ประวัติการใช้งาน',
        'icon' => 'bi-clock-history',
        'color' => 'secondary',
        'description' => 'บันทึกการทำงานของผู้ดูแลระบบ'
    ]
];

// นับจำนวนข้อมูล
foreach ($tables_info as $table => &$info) {
    $count_sql = "SELECT COUNT(*) as count FROM {$table}";
    $count_result = $conn->query($count_sql);
    $info['count'] = $count_result ? $count_result->fetch_assoc()['count'] : 0;
}
?>

<!-- Page Header -->
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-2">
                        <i class="bi bi-trash text-danger me-2"></i>
                        ล้างข้อมูลระบบ
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="index.php?page=dashboard">หน้าแรก</a></li>
                            <li class="breadcrumb-item active">ล้างข้อมูล</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Warning Alert -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-start">
                    <i class="bi bi-exclamation-triangle-fill fs-2 me-3"></i>
                    <div>
                        <h5 class="alert-heading mb-2">
                            <i class="bi bi-shield-exclamation me-2"></i>
                            คำเตือนสำคัญ!
                        </h5>
                        <p class="mb-2">
                            หน้านี้สำหรับ <strong>ผู้ดูแลระบบระดับสูงสุด (Superadmin)</strong> เท่านั้น
                        </p>
                        <ul class="mb-0">
                            <li>การลบข้อมูลจะ <strong class="text-danger">ลบถาวร</strong> และไม่สามารถกู้คืนได้</li>
                            <li>ควร <strong>สำรองข้อมูล (Backup)</strong> ก่อนทำการลบทุกครั้ง</li>
                            <li>ระบบจะบันทึกการกระทำทั้งหมดไว้ใน Activity Log</li>
                        </ul>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">รอบโควตา</p>
                            <h3 class="mb-0"><?php echo number_format($tables_info['students_quota']['count']); ?></h3>
                        </div>
                        <div class="text-primary">
                            <i class="bi bi-file-earmark-person fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">รอบปกติ</p>
                            <h3 class="mb-0"><?php echo number_format($tables_info['students_regular']['count']); ?></h3>
                        </div>
                        <div class="text-info">
                            <i class="bi bi-file-earmark-text fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">ข่าวสาร</p>
                            <h3 class="mb-0"><?php echo number_format($tables_info['news']['count']); ?></h3>
                        </div>
                        <div class="text-success">
                            <i class="bi bi-newspaper fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">คลังภาพ</p>
                            <h3 class="mb-0"><?php echo number_format($tables_info['gallery']['count']); ?></h3>
                        </div>
                        <div class="text-warning">
                            <i class="bi bi-images fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Clear Data Cards -->
    <div class="row g-4">
        <?php foreach ($tables_info as $table => $info): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm hover-shadow">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start mb-3">
                        <div class="icon-circle bg-<?php echo $info['color']; ?> bg-opacity-10 text-<?php echo $info['color']; ?> me-3"
                             style="width: 60px; height: 60px; flex-shrink: 0;">
                            <i class="bi <?php echo $info['icon']; ?> fs-2"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="mb-1 fw-bold"><?php echo $info['name']; ?></h5>
                            <p class="text-muted small mb-0"><?php echo $info['description']; ?></p>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">จำนวนข้อมูล</span>
                        <span class="badge bg-<?php echo $info['color']; ?> fs-6">
                            <?php echo number_format($info['count']); ?> รายการ
                        </span>
                    </div>

                    <?php if ($info['count'] > 0): ?>
                    <button type="button" 
                            class="btn btn-outline-danger w-100 btn-clear-data"
                            data-table="<?php echo $table; ?>"
                            data-name="<?php echo $info['name']; ?>"
                            data-count="<?php echo $info['count']; ?>">
                        <i class="bi bi-trash me-2"></i>
                        ล้างข้อมูล
                    </button>
                    <?php else: ?>
                    <button type="button" class="btn btn-secondary w-100" disabled>
                        <i class="bi bi-check-circle me-2"></i>
                        ไม่มีข้อมูล
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Danger Zone -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card border-danger shadow-sm">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-exclamation-diamond me-2"></i>
                        Danger Zone - ล้างข้อมูลทั้งหมด
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h6 class="fw-bold text-danger mb-2">
                                <i class="bi bi-radioactive me-2"></i>
                                ล้างข้อมูลทั้งระบบ
                            </h6>
                            <p class="text-muted mb-0">
                                ลบข้อมูลทั้งหมดในระบบ (ยกเว้นข้อมูลผู้ดูแลระบบ, สาขาวิชา, และการตั้งค่า)
                                <br>
                                <strong class="text-danger">⚠️ การกระทำนี้ไม่สามารถย้อนกลับได้!</strong>
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                            <button type="button" 
                                    class="btn btn-danger btn-lg"
                                    id="btnClearAll">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                ล้างทั้งหมด
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Backup Recommendation -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-info shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-info-circle-fill text-info fs-3 me-3"></i>
                        <div>
                            <h6 class="fw-bold mb-2">
                                <i class="bi bi-database-down me-2"></i>
                                คำแนะนำการสำรองข้อมูล
                            </h6>
                            <p class="mb-2">ก่อนล้างข้อมูล ควรสำรองข้อมูลโดยใช้วิธีใดวิธีหนึ่งต่อไปนี้:</p>
                            <ol class="mb-0">
                                <li><strong>phpMyAdmin</strong> - Export ฐานข้อมูล ncadmit_db</li>
                                <li><strong>Command Line</strong> - <code class="bg-light px-2 py-1">mysqldump -u root -p ncadmit_db &gt; backup.sql</code></li>
                                <li><strong>Control Panel</strong> - ใช้ระบบ Backup ของ Hosting</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.icon-circle {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.hover-shadow {
    transition: all 0.3s ease;
}

.hover-shadow:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

code {
    font-family: 'Courier New', monospace;
    font-size: 0.9em;
    border-radius: 4px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Clear Data Page Loaded');
    
    // ==================== Clear Single Table ====================
    document.querySelectorAll('.btn-clear-data').forEach(button => {
        button.addEventListener('click', function() {
            const table = this.dataset.table;
            const name = this.dataset.name;
            const count = parseInt(this.dataset.count);
            
            Swal.fire({
                title: 'ยืนยันการลบข้อมูล?',
                html: `
                    <div class="text-start">
                        <p class="mb-3">คุณต้องการลบข้อมูล <strong class="text-danger">${name}</strong> ทั้งหมด?</p>
                        <div class="alert alert-warning mb-3">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>จำนวนที่จะถูกลบ: ${count.toLocaleString()} รายการ</strong>
                        </div>
                        <p class="text-muted small mb-2">กรุณาพิมพ์ <code class="text-danger">DELETE</code> เพื่อยืนยัน</p>
                        <input type="text" id="confirmDelete" class="form-control" placeholder="พิมพ์ DELETE">
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'ยืนยันการลบ',
                cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                preConfirm: () => {
                    const input = document.getElementById('confirmDelete').value;
                    if (input !== 'DELETE') {
                        Swal.showValidationMessage('กรุณาพิมพ์ DELETE เพื่อยืนยัน');
                        return false;
                    }
                    return true;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    clearTableData(table, name);
                }
            });
        });
    });
    
    // ==================== Clear All Data ====================
    document.getElementById('btnClearAll')?.addEventListener('click', function() {
        Swal.fire({
            title: '⚠️ คำเตือนร้ายแรง!',
            html: `
                <div class="text-start">
                    <p class="mb-3 text-danger fw-bold">คุณกำลังจะลบข้อมูลทั้งหมดในระบบ!</p>
                    <div class="alert alert-danger mb-3">
                        <strong>ข้อมูลที่จะถูกลบ:</strong>
                        <ul class="mb-0 mt-2">
                            <li>ใบสมัครรอบโควตา (<?php echo number_format($tables_info['students_quota']['count']); ?> รายการ)</li>
                            <li>ใบสมัครรอบปกติ (<?php echo number_format($tables_info['students_regular']['count']); ?> รายการ)</li>
                            <li>ข่าวประชาสัมพันธ์ (<?php echo number_format($tables_info['news']['count']); ?> รายการ)</li>
                            <li>คลังภาพ (<?php echo number_format($tables_info['gallery']['count']); ?> รายการ)</li>
                            <li>ประวัติการใช้งาน (<?php echo number_format($tables_info['activity_logs']['count']); ?> รายการ)</li>
                        </ul>
                    </div>
                    <p class="text-danger mb-2">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        การกระทำนี้ไม่สามารถย้อนกลับได้!
                    </p>
                    <p class="text-muted small mb-2">กรุณาพิมพ์ <code class="text-danger">DELETE ALL DATA</code> เพื่อยืนยัน</p>
                    <input type="text" id="confirmDeleteAll" class="form-control" placeholder="พิมพ์ DELETE ALL DATA">
                </div>
            `,
            icon: 'error',
            showCancelButton: true,
            confirmButtonText: 'ยืนยันการลบทั้งหมด',
            cancelButtonText: 'ยกเลิก',
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            width: 600,
            preConfirm: () => {
                const input = document.getElementById('confirmDeleteAll').value;
                if (input !== 'DELETE ALL DATA') {
                    Swal.showValidationMessage('กรุณาพิมพ์ DELETE ALL DATA เพื่อยืนยัน');
                    return false;
                }
                return true;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                clearAllData();
            }
        });
    });
    
    // ==================== Clear Table Function ====================
    function clearTableData(table, name) {
        Swal.fire({
            title: 'กำลังลบข้อมูล...',
            html: `กำลังลบข้อมูล ${name}`,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        fetch('api/clear_data.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'clear_table',
                table: table
            })
        })
        .then(response => response.json())
        .then(data => {
            console.log('Clear Response:', data);
            
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'ลบสำเร็จ!',
                    html: data.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
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
                text: 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้'
            });
        });
    }
    
    // ==================== Clear All Function ====================
    function clearAllData() {
        Swal.fire({
            title: 'กำลังลบข้อมูลทั้งหมด...',
            html: 'กรุณารอสักครู่...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        fetch('api/clear_data.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'clear_all'
            })
        })
        .then(response => response.json())
        .then(data => {
            console.log('Clear All Response:', data);
            
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'ลบสำเร็จ!',
                    html: data.message,
                    timer: 3000,
                    showConfirmButton: false
                }).then(() => {
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
                text: 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้'
            });
        });
    }
});
</script>