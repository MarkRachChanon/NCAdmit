<?php
/**
 * Check Status Page - ตรวจสอบสถานะการสมัคร
 */
?>

<!-- Page Header -->
<section class="page-header bg-gradient-primary text-white py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center" data-aos="fade-down">
                <h1 class="display-6 fw-bold mb-3">
                    <i class="bi bi-search me-3"></i>
                    ตรวจสอบสถานะการสมัคร
                </h1>
                <p class="lead mb-0">
                    ตรวจสอบสถานะการสมัครนักเรียน นักศึกษา ประจำปีการศึกษา <?php echo (date('Y') + 543 + 1); ?>
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Search Form Section -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                
                <!-- Search Form Card -->
                <div id="searchFormCard" class="card border-0 shadow-sm mb-4" data-aos="fade-up">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <div class="icon-circle bg-gradient-primary text-white mx-auto mb-3" 
                                 style="width: 80px; height: 80px;">
                                <i class="bi bi-person-circle display-4"></i>
                            </div>
                            <h4 class="fw-bold mb-2">กรอกข้อมูลเพื่อตรวจสอบ</h4>
                            <p class="text-muted mb-0">
                                กรุณากรอกเลขบัตรประชาชนและเบอร์โทรศัพท์ที่ใช้สมัคร
                            </p>
                        </div>

                        <form id="statusCheckForm">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">
                                        <i class="bi bi-person-vcard me-2 text-primary"></i>
                                        เลขบัตรประชาชน
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control form-control-lg" 
                                           id="id_card" 
                                           name="id_card" 
                                           placeholder="X-XXXX-XXXXX-XX-X"
                                           maxlength="17"
                                           >
                                    <small class="text-muted">
                                        <i class="bi bi-info-circle me-1"></i>กรอก 13 หลัก
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">
                                        <i class="bi bi-telephone me-2 text-primary"></i>
                                        เบอร์โทรศัพท์
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control form-control-lg" 
                                           id="phone" 
                                           name="phone" 
                                           placeholder="0XX-XXX-XXXX"
                                           maxlength="12"
                                           >
                                    <small class="text-muted">
                                        <i class="bi bi-info-circle me-1"></i>กรอก 10 หลัก
                                    </small>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-gradient btn-lg shadow">
                                    <i class="bi bi-search me-2"></i>ตรวจสอบสถานะ
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Result Section -->
                <div id="resultSection" style="display: none;">
                    <!-- จะถูกเติมด้วย JavaScript -->
                </div>

                <!-- Not Found Section -->
                <div id="notFoundSection" style="display: none;">
                    <div class="card border-0 shadow-sm" data-aos="fade-up">
                        <div class="card-body p-5 text-center">
                            <div class="icon-circle bg-warning bg-opacity-10 text-warning mx-auto mb-4" 
                                 style="width: 100px; height: 100px;">
                                <i class="bi bi-exclamation-triangle display-3"></i>
                            </div>
                            <h4 class="fw-bold mb-3">ไม่พบข้อมูลการสมัคร</h4>
                            <p class="text-muted mb-4">
                                กรุณาตรวจสอบเลขบัตรประชาชนและเบอร์โทรศัพท์ที่ใช้สมัครอีกครั้ง
                                <br>หรือติดต่อสอบถามเจ้าหน้าที่
                            </p>
                            <div class="d-grid gap-2 col-md-6 mx-auto">
                                <button type="button" class="btn btn-gradient btn-lg" onclick="searchAgain()">
                                    <i class="bi bi-arrow-left me-2"></i>ลองอีกครั้ง
                                </button>
                                <a href="index.php?page=contact" class="btn btn-outline-primary btn-lg">
                                    <i class="bi bi-telephone me-2"></i>ติดต่อเจ้าหน้าที่
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<!-- Help Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row mb-5">
            <div class="col-12 text-center" data-aos="fade-up">
                <h2 class="section-title text-gradient">สถานะการรับสมัคร</h2>
                <p class="section-subtitle">สถานะการรับสมัครสำคัญที่ต้องจำไว้</p>
            </div>
        </div>
        <div class="row justify-content-center mb-5">
            <div class="col-12">
                <div class="card border-0 shadow-sm" data-aos="fade-up">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4 text-center">
                            <i class="bi bi-info-circle-fill text-primary me-2"></i>
                            คำอธิบายสถานะการสมัคร
                        </h5>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="status-info-card p-3 rounded-3" style="background: linear-gradient(135deg, #fff3cd 0%, #fff9e6 100%); border-left: 4px solid #ffc107;">
                                    <div class="d-flex align-items-start">
                                        <div class="status-icon me-3">
                                            <div class="icon-circle bg-warning text-white" style="width: 50px; height: 50px;">
                                                <i class="bi bi-hourglass-split fs-4"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-2 text-warning">
                                                <i class="bi bi-dot"></i>รอตรวจสอบเอกสาร
                                            </h6>
                                            <p class="text-muted small mb-0">
                                                ใบสมัครของคุณอยู่ระหว่างการตรวจสอบเอกสารและคุณสมบัติโดยเจ้าหน้าที่ 
                                                กรุณารอการประกาศผลภายใน 7-14 วันทำการ
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="status-info-card p-3 rounded-3" style="background: linear-gradient(135deg, #d1e7dd 0%, #e8f5e9 100%); border-left: 4px solid #28a745;">
                                    <div class="d-flex align-items-start">
                                        <div class="status-icon me-3">
                                            <div class="icon-circle bg-success text-white" style="width: 50px; height: 50px;">
                                                <i class="bi bi-check-circle-fill fs-4"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-2 text-success">
                                                <i class="bi bi-dot"></i>ผ่านการคัดเลือก
                                            </h6>
                                            <p class="text-muted small mb-0">
                                                ยินดีด้วย! คุณผ่านการคัดเลือกเรียบร้อยแล้ว 
                                                กรุณารอรับการติดต่อเพื่อดำเนินการมอบตัวต่อไป
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="status-info-card p-3 rounded-3" style="background: linear-gradient(135deg, #f8d7da 0%, #ffe6e6 100%); border-left: 4px solid #dc3545;">
                                    <div class="d-flex align-items-start">
                                        <div class="status-icon me-3">
                                            <div class="icon-circle bg-danger text-white" style="width: 50px; height: 50px;">
                                                <i class="bi bi-x-circle-fill fs-4"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-2 text-danger">
                                                <i class="bi bi-dot"></i>ไม่ผ่านการคัดเลือก
                                            </h6>
                                            <p class="text-muted small mb-0">
                                                ขออภัย คุณไม่ผ่านการคัดเลือกในครั้งนี้ 
                                                สามารถสมัครในรอบถัดไปหรือติดต่อสอบถามเจ้าหน้าที่เพื่อขอคำแนะนำ
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="status-info-card p-3 rounded-3" style="background: linear-gradient(135deg, #e2e3e5 0%, #f0f0f0 100%); border-left: 4px solid #6c757d;">
                                    <div class="d-flex align-items-start">
                                        <div class="status-icon me-3">
                                            <div class="icon-circle bg-secondary text-white" style="width: 50px; height: 50px;">
                                                <i class="bi bi-dash-circle-fill fs-4"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-2 text-secondary">
                                                <i class="bi bi-dot"></i>ยกเลิกการสมัคร
                                            </h6>
                                            <p class="text-muted small mb-0">
                                                การสมัครของคุณถูกยกเลิกแล้ว 
                                                หากต้องการสมัครใหม่กรุณาติดต่อเจ้าหน้าที่
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.status-info-card {
    transition: all 0.3s ease;
}

.status-info-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
}

.application-card {
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.application-card:hover {
    border-color: #667eea;
    transform: translateY(-3px);
}

.status-badge-large {
    font-size: 1.1rem;
    padding: 0.75rem 2rem;
    border-radius: 50px;
    font-weight: 600;
    letter-spacing: 0.5px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
}

.info-item {
    padding: 1rem;
    border-radius: 10px;
    background: #f8f9fa;
    transition: all 0.2s ease;
}

.info-item:hover {
    background: #e9ecef;
}
</style>

<script>
// Auto-format ID Card
document.getElementById('id_card').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 13) value = value.substring(0, 13);
    
    if (value.length > 1) {
        value = value.substring(0, 1) + '-' + value.substring(1);
    }
    if (value.length > 6) {
        value = value.substring(0, 6) + '-' + value.substring(6);
    }
    if (value.length > 12) {
        value = value.substring(0, 12) + '-' + value.substring(12);
    }
    
    e.target.value = value;
});

// Auto-format Phone
document.getElementById('phone').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 10) value = value.substring(0, 10);
    
    if (value.length > 3) {
        value = value.substring(0, 3) + '-' + value.substring(3);
    }
    if (value.length > 7) {
        value = value.substring(0, 7) + '-' + value.substring(7);
    }
    
    e.target.value = value;
});

// Form Submit
document.getElementById('statusCheckForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const idCard = document.getElementById('id_card').value.replace(/\D/g, '');
    const phone = document.getElementById('phone').value.replace(/\D/g, '');
    
    if (idCard.length !== 13) {
        Swal.fire({
            icon: 'error',
            title: 'เลขบัตรประชาชนไม่ถูกต้อง',
            text: 'กรุณากรอกเลขบัตรประชาชน 13 หลัก',
            confirmButtonColor: '#667eea'
        });
        return;
    }
    
    if (phone.length !== 10) {
        Swal.fire({
            icon: 'error',
            title: 'เบอร์โทรศัพท์ไม่ถูกต้อง',
            text: 'กรุณากรอกเบอร์โทรศัพท์ 10 หลัก',
            confirmButtonColor: '#667eea'
        });
        return;
    }
    
    Swal.fire({
        title: 'กำลังค้นหา...',
        html: '<div class="spinner-border text-primary" role="status"></div>',
        allowOutsideClick: false,
        showConfirmButton: false
    });
    
    try {
        const response = await fetch('pages/check_status_api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id_card: idCard,
                phone: phone
            })
        });
        
        const result = await response.json();
        
        Swal.close();
        
        if (result.success) {
            displayResults(result.data, result.count);
        } else {
            showNotFound();
        }
        
    } catch (error) {
        Swal.close();
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'เกิดข้อผิดพลาด',
            text: 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้',
            confirmButtonColor: '#667eea'
        });
    }
});

function displayResults(applications, count) {
    document.getElementById('searchFormCard').style.display = 'none';
    document.getElementById('notFoundSection').style.display = 'none';
    
    const resultSection = document.getElementById('resultSection');
    resultSection.style.display = 'block';
    
    let html = '';
    
    // Header
    if (count > 1) {
        html += `
        <div class="alert alert-info mb-4" data-aos="fade-up">
            <div class="d-flex align-items-center">
                <i class="bi bi-info-circle-fill fs-3 me-3"></i>
                <div>
                    <h6 class="mb-0 fw-bold">พบข้อมูลการสมัคร ${count} รอบ</h6>
                    <small>คุณได้สมัครทั้งรอบโควต้าและรอบปกติ</small>
                </div>
            </div>
        </div>
        `;
    }
    
    // Loop through applications
    applications.forEach((data, index) => {
        const statusConfig = getStatusConfig(data.status);
        const formTypeBadge = data.form_type === 'quota' 
            ? '<span class="badge bg-primary me-2 fs-6"><i class="bi bi-award me-1"></i>รอบโควต้า</span>' 
            : '<span class="badge bg-primary me-2 fs-6"><i class="bi bi-people me-1"></i>รอบปกติ</span>';
        
        html += `
        <div class="card border-0 shadow-sm application-card mb-4" data-aos="fade-up" data-aos-delay="${index * 100}">
            <div class="card-header bg-gradient-primary text-white p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">
                        <span class="me-3"><i class="bi bi-person-badge me-2"></i>ข้อมูลการสมัคร</span> ${formTypeBadge}
                    </h5>
                    <a href="pages/download_application_pdf.php?app_no=${data.application_no}&type=${data.form_type}" 
                        class="btn btn-light btn-sm ms-2 fs-6"
                        target="_blank">
                        <i class="bi bi-download me-2"></i>ดาวน์โหลดใบสมัครโควต้า
                    </a>
                </div>
            </div>
            <div class="card-body p-4">
                <!-- Status Badge -->
                <div class="text-center mb-4 pb-4 border-bottom">
                    <h5 class="text-secondary mb-3">
                        สถานะการสมัคร
                    </h5> 
                    <div class="mb-3">
                        <span class="status-badge-large badge ${statusConfig.badge}">
                            <i class="bi bi-${statusConfig.icon} me-2"></i>${statusConfig.text}
                        </span>
                    </div>
                    
                    ${data.status_note ? `
                        <div class="alert alert-${statusConfig.alertType} mt-3 mb-0">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            <strong>หมายเหตุ:</strong> ${data.status_note}
                        </div>
                    ` : ''}
                </div>

                <!-- Application Details -->
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="info-item">
                            <small class="text-muted d-block mb-1">
                                <i class="bi bi-file-earmark-text text-primary me-1"></i>
                                หมายเลขใบสมัคร
                            </small>
                            <p class="mb-0 fw-bold fs-5 text-primary">${data.application_no}</p>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="info-item">
                            <small class="text-muted d-block mb-1">
                                <i class="bi bi-person text-success me-1"></i>
                                ชื่อ-นามสกุล
                            </small>
                            <p class="mb-0 fw-bold">${data.prefix || ''} ${data.firstname_th} ${data.lastname_th}</p>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="info-item">
                            <small class="text-muted d-block mb-1">
                                <i class="bi bi-mortarboard text-warning me-1"></i>
                                ระดับที่สมัคร
                            </small>
                            <p class="mb-0 fw-bold">${data.apply_level || data.department_level || '-'}</p>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="info-item">
                            <small class="text-muted d-block mb-1">
                                <i class="bi bi-building text-danger me-1"></i>
                                สาขาวิชา
                            </small>
                            <p class="mb-0 fw-bold">${data.department_name || '-'}</p>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="info-item">
                            <small class="text-muted d-block mb-1">
                                <i class="bi bi-calendar-check text-info me-1"></i>
                                วันที่สมัคร
                            </small>
                            <p class="mb-0">${formatDateTime(data.created_at)}</p>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="info-item">
                            <small class="text-muted d-block mb-1">
                                <i class="bi bi-clock-history text-secondary me-1"></i>
                                อัปเดตล่าสุด
                            </small>
                            <p class="mb-0">${formatDateTime(data.updated_at)}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        `;
    });
    
    // Back Button
    html += `
    <div class="d-grid gap-2 mt-4" data-aos="fade-up">
        <button type="button" class="btn btn-outline-primary btn-lg" onclick="searchAgain()">
            <i class="bi bi-arrow-left me-2"></i>ค้นหาอีกครั้ง
        </button>
    </div>
    `;
    
    resultSection.innerHTML = html;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function getStatusConfig(status) {
    const configs = {
        'pending': { 
            badge: 'bg-warning text-dark', 
            icon: 'hourglass-split', 
            text: 'รอตรวจสอบเอกสาร',
            alertType: 'warning'
        },
        'approved': { 
            badge: 'bg-success', 
            icon: 'check-circle-fill', 
            text: 'ผ่านการคัดเลือก',
            alertType: 'success'
        },
        'rejected': { 
            badge: 'bg-danger', 
            icon: 'x-circle-fill', 
            text: 'ไม่ผ่านการคัดเลือก',
            alertType: 'danger'
        },
        'cancelled': { 
            badge: 'bg-secondary', 
            icon: 'dash-circle-fill', 
            text: 'ยกเลิกการสมัคร',
            alertType: 'secondary'
        }
    };
    
    return configs[status] || configs['pending'];
}

function showNotFound() {
    document.getElementById('searchFormCard').style.display = 'none';
    document.getElementById('resultSection').style.display = 'none';
    document.getElementById('notFoundSection').style.display = 'block';
    
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function searchAgain() {
    document.getElementById('searchFormCard').style.display = 'block';
    document.getElementById('resultSection').style.display = 'none';
    document.getElementById('notFoundSection').style.display = 'none';
    
    document.getElementById('statusCheckForm').reset();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function formatDateTime(datetime) {
    if (!datetime) return '-';
    
    const date = new Date(datetime);
    const options = {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    
    return date.toLocaleDateString('th-TH', options);
}
</script>