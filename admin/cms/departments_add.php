<?php
/**
 * หน้าเพิ่มสาขาวิชาใหม่
 * NC-Admission - Nakhon Pathom College Admission System
 */

// ตรวจสอบสิทธิ์การเข้าถึง
if (!check_page_permission('departments_add', $admin_role)) {
    header('Location: index.php?page=dashboard');
    exit();
}

// ดึงข้อมูล Categories
$categories_sql = "SELECT * FROM department_categories WHERE is_active = 1 ORDER BY sort_order ASC";
$categories_result = $conn->query($categories_sql);
?>

<!-- Page Header -->
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-2">
                        <i class="bi bi-plus-circle text-primary me-2"></i>
                        เพิ่มสาขาวิชาใหม่
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="index.php?page=dashboard">หน้าแรก</a></li>
                            <li class="breadcrumb-item"><a href="index.php?page=departments_manage">จัดการสาขาวิชา</a></li>
                            <li class="breadcrumb-item active">เพิ่มสาขาวิชา</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="index.php?page=departments_manage" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>
                        กลับ
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- ฟอร์มเพิ่มสาขาวิชา -->
    <div class="row">
        <div class="col-lg-12 mx-auto">
            <form id="addDepartmentForm" method="POST" class="needs-validation" novalidate>
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            ข้อมูลพื้นฐาน
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- รหัสสาขา -->
                            <div class="col-md-6 mb-3">
                                <label for="code" class="form-label">
                                    รหัสสาขาวิชา <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control text-uppercase" 
                                       id="code" 
                                       name="code" 
                                       placeholder="เช่น PVC-IT" 
                                       required
                                       pattern="[A-Z0-9\-]+"
                                       title="ใช้ตัวอักษรพิมพ์ใหญ่ ตัวเลข และเครื่องหมาย - เท่านั้น">
                                <small class="text-muted">ตัวอย่าง: PVC-IT, PVS-ACC, DEG-MKT</small>
                            </div>

                            <!-- ประเภทวิชา -->
                            <div class="col-md-6 mb-3">
                                <label for="category_id" class="form-label">
                                    ประเภทวิชา <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="category_id" name="category_id" required>
                                    <option value="">-- เลือกประเภทวิชา --</option>
                                    <?php
                                    if ($categories_result && $categories_result->num_rows > 0):
                                        while ($category = $categories_result->fetch_assoc()):
                                    ?>
                                    <option value="<?php echo $category['id']; ?>">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                    <?php
                                        endwhile;
                                    endif;
                                    ?>
                                </select>
                            </div>

                            <!-- ชื่อสาขา (ไทย) -->
                            <div class="col-md-6 mb-3">
                                <label for="name_th" class="form-label">
                                    ชื่อสาขาวิชา (ภาษาไทย) <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="name_th" 
                                       name="name_th" 
                                       placeholder="เช่น สาขาวิชาเทคโนโลยีสารสนเทศ" 
                                       required>
                            </div>

                            <!-- ชื่อสาขา (อังกฤษ) -->
                            <div class="col-md-6 mb-3">
                                <label for="name_en" class="form-label">
                                    ชื่อสาขาวิชา (ภาษาอังกฤษ) <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="name_en" 
                                       name="name_en" 
                                       placeholder="เช่น Information Technology" 
                                       required>
                            </div>

                            <!-- ระดับการศึกษา -->
                            <div class="col-md-6 mb-3">
                                <label for="level" class="form-label">
                                    ระดับการศึกษา <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="level" name="level" required>
                                    <option value="">-- เลือกระดับ --</option>
                                    <option value="ปวช.">ปวช. (ประกาศนียบัตรวิชาชีพ)</option>
                                    <option value="ปวส.">ปวส. (ประกาศนียบัตรวิชาชีพชั้นสูง)</option>
                                    <option value="ปริญญาตรี">ปริญญาตรี (เทคโนโลยีบัณฑิต)</option>
                                </select>
                            </div>

                            <!-- ประเภทการเรียน -->
                            <div class="col-md-6 mb-3">
                                <label for="study_type" class="form-label">
                                    ประเภทการเรียน <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="study_type" name="study_type" required>
                                    <option value="">-- เลือกประเภทการเรียน --</option>
                                    <option value="ปกติ">ปกติ</option>
                                    <option value="ทวิภาคี">ทวิภาคี</option>
                                    <option value="ปกติ+ทวิภาคี">ปกติ+ทวิภาคี</option>
                                </select>
                            </div>

                            <!-- คำอธิบาย -->
                            <div class="col-12 mb-3">
                                <label for="description" class="form-label">
                                    คำอธิบายสาขาวิชา
                                </label>
                                <textarea class="form-control" 
                                          id="description" 
                                          name="description" 
                                          rows="3" 
                                          placeholder="อธิบายรายละเอียดสาขาวิชา เนื้อหาการเรียน และอาชีพที่เกี่ยวข้อง"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- การรับสมัคร -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-people me-2"></i>
                            ข้อมูลการรับสมัคร
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- รอบโควตา -->
                            <div class="col-md-6">
                                <div class="card border-primary mb-3">
                                    <div class="card-header bg-primary bg-opacity-10">
                                        <h6 class="mb-0">
                                            <i class="bi bi-star me-2"></i>
                                            รอบโควตา (Quota)
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   id="open_quota" 
                                                   name="open_quota" 
                                                   value="1"
                                                   checked>
                                            <label class="form-check-label" for="open_quota">
                                                <strong>เปิดรับสมัครรอบโควตา</strong>
                                            </label>
                                        </div>
                                        <div class="mb-3">
                                            <label for="seats_quota" class="form-label">
                                                จำนวนที่รับ <span class="text-danger">*</span>
                                            </label>
                                            <input type="number" 
                                                   class="form-control" 
                                                   id="seats_quota" 
                                                   name="seats_quota" 
                                                   min="0" 
                                                   max="999"
                                                   value="40" 
                                                   required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- รอบปกติ -->
                            <div class="col-md-6">
                                <div class="card border-success mb-3">
                                    <div class="card-header bg-success bg-opacity-10">
                                        <h6 class="mb-0">
                                            <i class="bi bi-people me-2"></i>
                                            รอบปกติ (Regular)
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   id="open_regular" 
                                                   name="open_regular" 
                                                   value="1"
                                                   checked>
                                            <label class="form-check-label" for="open_regular">
                                                <strong>เปิดรับสมัครรอบปกติ</strong>
                                            </label>
                                        </div>
                                        <div class="mb-3">
                                            <label for="seats_regular" class="form-label">
                                                จำนวนที่รับ <span class="text-danger">*</span>
                                            </label>
                                            <input type="number" 
                                                   class="form-control" 
                                                   id="seats_regular" 
                                                   name="seats_regular" 
                                                   min="0" 
                                                   max="999"
                                                   value="40" 
                                                   required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- การตั้งค่าเพิ่มเติม -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-gear me-2"></i>
                            การตั้งค่าเพิ่มเติม
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- สถานะการใช้งาน -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">สถานะการใช้งาน</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="is_active" 
                                           name="is_active" 
                                           value="1"
                                           checked>
                                    <label class="form-check-label" for="is_active">
                                        เปิดใช้งาน
                                    </label>
                                </div>
                            </div>

                            <!-- สาขาใหม่ -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">สาขาใหม่</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="is_new" 
                                           name="is_new" 
                                           value="1">
                                    <label class="form-check-label" for="is_new">
                                        ทำเครื่องหมายเป็นสาขาใหม่
                                    </label>
                                </div>
                            </div>

                            <!-- Highlight -->
                            <div class="col-md-4 mb-3">
                                <label for="highlight" class="form-label">
                                    ไฮไลท์
                                </label>
                                <select class="form-select" id="highlight" name="highlight">
                                    <option value="">-- ไม่มี --</option>
                                    <option value="NEW">NEW (สาขาใหม่)</option>
                                    <option value="HOT">HOT (ยอดนิยม)</option>
                                    <option value="POPULAR">POPULAR (แนะนำ)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ปุ่ม Submit -->
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <a href="index.php?page=departments_manage" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-2"></i>
                                ยกเลิก
                            </a>
                            <button type="submit" class="btn btn-primary" id="btnSubmit">
                                <i class="bi bi-save me-2"></i>
                                บันทึกข้อมูล
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Department Add Form Loaded');
    
    const form = document.getElementById('addDepartmentForm');
    const codeInput = document.getElementById('code');
    const openQuota = document.getElementById('open_quota');
    const seatsQuota = document.getElementById('seats_quota');
    const openRegular = document.getElementById('open_regular');
    const seatsRegular = document.getElementById('seats_regular');
    
    // ==================== Auto Uppercase for Code ====================
    if (codeInput) {
        codeInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    }
    
    // ==================== Toggle Seats Quota ====================
    if (openQuota && seatsQuota) {
        openQuota.addEventListener('change', function() {
            if (this.checked) {
                seatsQuota.disabled = false;
                seatsQuota.required = true;
            } else {
                seatsQuota.disabled = true;
                seatsQuota.required = false;
                seatsQuota.value = 0;
            }
        });
    }
    
    // ==================== Toggle Seats Regular ====================
    if (openRegular && seatsRegular) {
        openRegular.addEventListener('change', function() {
            if (this.checked) {
                seatsRegular.disabled = false;
                seatsRegular.required = true;
            } else {
                seatsRegular.disabled = true;
                seatsRegular.required = false;
                seatsRegular.value = 0;
            }
        });
    }
    
    // ==================== Form Submit ====================
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Form submitted');
            
            // Validate Form
            if (!form.checkValidity()) {
                e.stopPropagation();
                form.classList.add('was-validated');
                
                Swal.fire({
                    icon: 'error',
                    title: 'กรุณากรอกข้อมูลให้ครบถ้วน',
                    text: 'โปรดตรวจสอบข้อมูลที่กรอกให้ถูกต้อง'
                });
                return;
            }
            
            // ตรวจสอบว่าเปิดรับสมัครอย่างน้อย 1 รอบ
            if (!openQuota.checked && !openRegular.checked) {
                Swal.fire({
                    icon: 'warning',
                    title: 'กรุณาเลือกรอบรับสมัคร',
                    text: 'กรุณาเปิดรับสมัครอย่างน้อย 1 รอบ (โควตาหรือปกติ)'
                });
                return;
            }
            
            // แสดง Loading
            Swal.fire({
                title: 'กำลังบันทึกข้อมูล...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // เตรียมข้อมูล FormData
            const formData = new FormData(form);
            
            // Debug: แสดงข้อมูลที่จะส่ง
            console.log('Form Data:');
            for (let [key, value] of formData.entries()) {
                console.log(key + ': ' + value);
            }
            
            // ส่งข้อมูลด้วย Fetch API
            fetch('api/department_add.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                
                // ตรวจสอบ Content-Type
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    return response.text().then(text => {
                        console.error('Response is not JSON:', text);
                        throw new Error('Server did not return JSON response');
                    });
                }
                
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'สำเร็จ!',
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        // Redirect ไปหน้า manage
                        window.location.href = 'index.php?page=departments_manage';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาด',
                        html: data.message || 'ไม่สามารถบันทึกข้อมูลได้'
                    });
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด',
                    html: 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้<br><small>' + error.message + '</small>'
                });
            });
        });
    }
});
</script>