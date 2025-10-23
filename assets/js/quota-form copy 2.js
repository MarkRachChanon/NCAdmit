/**
 * Quota Form - Multi-Step Navigation
 * ฟอร์มรับสมัครรอบโควต้า
 * รองรับ Step 1-6 (เพิ่ม Step 5: Upload Files, Step 6: Summary)
 */

window.addEventListener('load', function() {
  document.getElementById('education_level_apply').selectedIndex = 0;
});

let currentStep = 1;
const totalSteps = 6;

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    setupNavigation();
    setupFormInputs();
    setupAddressCheckbox();
    setupFileUploads(); // เพิ่มใหม่
    loadSavedData();
    
    Toast.fire({
        icon: 'info',
        title: 'กรุณากรอกข้อมูลให้ครบถ้วน'
    });
});

// ========================================
// Navigation Functions
// ========================================

function setupNavigation() {
    // Next buttons
    document.querySelectorAll('.btn-next').forEach(btn => {
        btn.addEventListener('click', function() {
            if (validateCurrentStep()) {
                saveStepData();
                nextStep();
            }
        });
    });
    
    // Previous buttons
    document.querySelectorAll('.btn-prev').forEach(btn => {
        btn.addEventListener('click', function() {
            saveStepData();
            previousStep();
        });
    });
}

function nextStep() {
    if (currentStep < totalSteps) {
        // Hide current step
        document.querySelector(`.form-step[data-step="${currentStep}"]`).classList.remove('active');
        
        // Update indicator
        updateStepIndicator(currentStep, 'completed');
        
        // Show next step
        currentStep++;
        document.querySelector(`.form-step[data-step="${currentStep}"]`).classList.add('active');
        
        // Update indicator
        updateStepIndicator(currentStep, 'active');
        
        // ถ้าเป็น Step 6 ให้ Generate Summary
        if (currentStep === 6) {
            generateSummary();
        }
        
        // Scroll to top
        window.scrollTo({ top: 0, behavior: 'smooth' });
        
        Toast.fire({
            icon: 'success',
            title: 'บันทึกข้อมูลสำเร็จ'
        });
    }
}

function previousStep() {
    if (currentStep > 1) {
        document.querySelector(`.form-step[data-step="${currentStep}"]`).classList.remove('active');
        updateStepIndicator(currentStep, '');
        
        currentStep--;
        document.querySelector(`.form-step[data-step="${currentStep}"]`).classList.add('active');
        updateStepIndicator(currentStep, 'active');
        
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

function updateStepIndicator(step, status) {
    const indicator = document.querySelector(`.step-item[data-step="${step}"] .step-indicator`);
    
    if (!indicator) return;
    
    indicator.classList.remove('active', 'completed', 'bg-secondary');
    
    if (status === 'active') {
        indicator.classList.add('active');
    } else if (status === 'completed') {
        indicator.classList.add('completed');
    } else {
        indicator.classList.add('bg-secondary');
    }
}

// ========================================
// Validation Functions
// ========================================

function validateCurrentStep() {
    const currentStepElement = document.querySelector(`.form-step[data-step="${currentStep}"]`);
    const requiredInputs = currentStepElement.querySelectorAll('[required]');
    
    let isValid = true;
    let firstInvalidField = null;
    
    requiredInputs.forEach(input => {
        // สำหรับ file input ตรวจสอบว่ามีไฟล์หรือไม่
        if (input.type === 'file') {
            if (!input.files || input.files.length === 0) {
                isValid = false;
                input.classList.add('is-invalid');
                if (!firstInvalidField) firstInvalidField = input;
            } else {
                input.classList.remove('is-invalid');
            }
        } else {
            // สำหรับ input อื่นๆ
            if (!input.value.trim()) {
                isValid = false;
                input.classList.add('is-invalid');
                if (!firstInvalidField) firstInvalidField = input;
            } else {
                input.classList.remove('is-invalid');
            }
        }
    });
    
    if (!isValid) {
        Swal.fire({
            icon: 'error',
            title: 'ข้อมูลไม่ครบถ้วน',
            text: 'กรุณากรอกข้อมูลและอัปโหลดเอกสารที่มีเครื่องหมาย * ให้ครบถ้วน',
            confirmButtonColor: '#4facfe'
        });
        
        if (firstInvalidField) {
            firstInvalidField.focus();
        }
        
        return false;
    }
    
    // Validation เพิ่มเติมตาม Step
    switch(currentStep) {
        case 1:
            return validatePersonalInfo();
        case 2:
            return validateAddress();
        case 3:
            return validateEducation();
        case 4:
            return validateDepartment();
        case 5:
            return validateFiles();
        default:
            return true;
    }
}

function validatePersonalInfo() {
    const idCard = document.getElementById('id_card').value.replace(/-/g, '');
    const phone = document.getElementById('phone').value.replace(/-/g, '');
    const email = document.querySelector('[name="email"]').value;
    
    // Validate ID Card
    if (idCard.length !== 13 || !/^\d+$/.test(idCard)) {
        Swal.fire({
            icon: 'error',
            title: 'เลขบัตรประชาชนไม่ถูกต้อง',
            text: 'กรุณากรอกเลขบัตรประชาชน 13 หลัก',
            confirmButtonColor: '#4facfe'
        });
        return false;
    }
    
    // Validate Phone
    if (phone.length !== 10 || !/^0\d{9}$/.test(phone)) {
        Swal.fire({
            icon: 'error',
            title: 'เบอร์โทรศัพท์ไม่ถูกต้อง',
            text: 'กรุณากรอกเบอร์โทรศัพท์ 10 หลัก เริ่มต้นด้วย 0',
            confirmButtonColor: '#4facfe'
        });
        return false;
    }
    
    // Validate Email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        Swal.fire({
            icon: 'error',
            title: 'อีเมลไม่ถูกต้อง',
            text: 'กรุณากรอกอีเมลในรูปแบบที่ถูกต้อง',
            confirmButtonColor: '#4facfe'
        });
        return false;
    }
    
    return true;
}

function validateAddress() {
    return true;
}

function validateEducation() {
    const gpax = parseFloat(document.getElementById('gpax').value);
    const parentPhone = document.getElementById('parent_phone').value.replace(/-/g, '');
    
    // Validate GPAX
    if (isNaN(gpax) || gpax < 0 || gpax > 4) {
        Swal.fire({
            icon: 'error',
            title: 'เกรดเฉลี่ยไม่ถูกต้อง',
            text: 'กรุณากรอกเกรดเฉลี่ย (GPAX) ระหว่าง 0.00 - 4.00',
            confirmButtonColor: '#4facfe'
        });
        return false;
    }
    
    // Validate Parent Phone
    if (parentPhone.length !== 10 || !/^0\d{9}$/.test(parentPhone)) {
        Swal.fire({
            icon: 'error',
            title: 'เบอร์โทรศัพท์ผู้ปกครองไม่ถูกต้อง',
            text: 'กรุณากรอกเบอร์โทรศัพท์ 10 หลัก เริ่มต้นด้วย 0',
            confirmButtonColor: '#4facfe'
        });
        return false;
    }
    
    return true;
}

function validateDepartment() {
    const department = document.getElementById('department_1').value;
    
    if (!department) {
        Swal.fire({
            icon: 'error',
            title: 'ยังไม่ได้เลือกสาขาวิชา',
            text: 'กรุณาเลือกสาขาวิชาที่ต้องการสมัคร',
            confirmButtonColor: '#4facfe'
        });
        return false;
    }
    
    return true;
}

function validateFiles() {
    const requiredFiles = ['photo', 'id_card_file', 'house_registration', 'transcript'];
    let allValid = true;
    
    requiredFiles.forEach(fieldName => {
        const input = document.getElementById(fieldName);
        if (!input || !input.files || input.files.length === 0) {
            allValid = false;
        }
    });
    
    if (!allValid) {
        Swal.fire({
            icon: 'error',
            title: 'กรุณาอัปโหลดเอกสารให้ครบถ้วน',
            text: 'ต้องอัปโหลดเอกสารทั้ง 4 รายการ',
            confirmButtonColor: '#4facfe'
        });
        return false;
    }
    
    return true;
}

// ========================================
// Form Input Helpers
// ========================================

function setupFormInputs() {
    // ID Card Format (X-XXXX-XXXXX-XX-X)
    const idCardInput = document.getElementById('id_card');
    if (idCardInput) {
        idCardInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            if (value.length > 13) {
                value = value.substring(0, 13);
            }
            
            if (value.length > 1) {
                value = value.substring(0, 1) + '-' + value.substring(1);
            }
            if (value.length > 6) {
                value = value.substring(0, 6) + '-' + value.substring(6);
            }
            if (value.length > 12) {
                value = value.substring(0, 12) + '-' + value.substring(12);
            }
            if (value.length > 15) {
                value = value.substring(0, 15) + '-' + value.substring(15);
            }
            
            e.target.value = value;
        });
    }
    
    // Phone Format (0XX-XXX-XXXX)
    const phoneInputs = document.querySelectorAll('input[type="tel"]');
    phoneInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            if (value.length > 10) {
                value = value.substring(0, 10);
            }
            
            if (value.length > 3) {
                value = value.substring(0, 3) + '-' + value.substring(3);
            }
            if (value.length > 7) {
                value = value.substring(0, 7) + '-' + value.substring(7);
            }
            
            e.target.value = value;
        });
    });
    
    // Setup Department Selection
    setupDepartmentSelection();
}

// ========================================
// Address Functions
// ========================================

function setupAddressCheckbox() {
    const checkbox = document.getElementById('same_address');
    
    if (checkbox) {
        checkbox.addEventListener('change', function() {
            if (this.checked) {
                copyAddress();
            } else {
                clearCurrentAddress();
            }
        });
    }
}

function copyAddress() {
    document.getElementById('current_address').value = document.getElementById('permanent_address').value;
    document.getElementById('current_subdistrict').value = document.getElementById('permanent_subdistrict').value;
    document.getElementById('current_district').value = document.getElementById('permanent_district').value;
    document.getElementById('current_province').value = document.getElementById('permanent_province').value;
    document.getElementById('current_postal_code').value = document.getElementById('permanent_postal_code').value;
    
    Toast.fire({
        icon: 'success',
        title: 'คัดลอกที่อยู่สำเร็จ'
    });
}

function clearCurrentAddress() {
    document.getElementById('current_address').value = '';
    document.getElementById('current_subdistrict').value = '';
    document.getElementById('current_district').value = '';
    document.getElementById('current_province').value = '';
    document.getElementById('current_postal_code').value = '';
}

// ========================================
// Session Save/Load Functions
// ========================================

function saveStepData() {
    const form = document.getElementById('quotaForm');
    const formData = new FormData(form);
    const data = {};
    
    formData.forEach((value, key) => {
        data[key] = value;
    });
    
    fetch('includes/save_session.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .catch(error => {
        console.error('Error saving session:', error);
    });
}

function loadSavedData() {
    fetch('includes/get_session.php')
    .then(response => response.json())
    .then(data => {
        if (data.quota_form_data) {
            Object.keys(data.quota_form_data).forEach(key => {
                const input = document.querySelector(`[name="${key}"]`);
                if (input) {
                    if (input.type === 'checkbox' || input.type === 'radio') {
                        input.checked = data.quota_form_data[key];
                    } else {
                        input.value = data.quota_form_data[key];
                    }
                }
            });
        }
    })
    .catch(error => {
        console.error('Error loading session:', error);
    });
}

// ========================================
// Department Selection Functions
// ========================================

function setupDepartmentSelection() {
    const levelSelect = document.getElementById('education_level_apply');
    const searchInput = document.getElementById('department_search');
    
    if (levelSelect) {
        levelSelect.addEventListener('change', function() {
            const level = this.value;
            
            if (level) {
                loadDepartmentsByLevel(level);
            } else {
                document.getElementById('department_selection_card').style.display = 'none';
                document.getElementById('department_1').value = '';
            }
        });
    }
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            filterDepartments(this.value);
        });
    }
}

function loadDepartmentsByLevel(level) {
    const departmentList = document.getElementById('department_list');
    const selectionCard = document.getElementById('department_selection_card');
    const searchInput = document.getElementById('department_search');
    
    // Clear previous selections
    departmentList.innerHTML = '';
    document.getElementById('department_1').value = '';
    document.getElementById('selected_department').style.display = 'none';
    if (searchInput) searchInput.value = '';
    
    // Collect all departments with matching level
    let allDepartments = [];
    Object.keys(departmentsByCategory).forEach(catId => {
        const depts = departmentsByCategory[catId];
        depts.forEach(dept => {
            if (dept.level === level) {
                allDepartments.push(dept);
            }
        });
    });
    
    if (allDepartments.length === 0) {
        departmentList.innerHTML = `
            <div class="col-12 text-center py-5">
                <i class="bi bi-inbox display-4 text-muted"></i>
                <p class="text-muted mt-3">ไม่มีสาขาวิชาสำหรับระดับชั้นนี้</p>
            </div>
        `;
        selectionCard.style.display = 'block';
        return;
    }
    
    // Group by category for display
    const groupedDepts = {};
    allDepartments.forEach(dept => {
        const catName = dept.category_name || 'อื่นๆ';
        if (!groupedDepts[catName]) {
            groupedDepts[catName] = [];
        }
        groupedDepts[catName].push(dept);
    });
    
    // Display departments grouped by category
    let html = '';
    Object.keys(groupedDepts).sort().forEach(catName => {
        html += `
            <div class="col-12">
                <h6 class="fw-bold text-primary mb-3 mt-3">
                    <i class="bi bi-folder-fill me-2"></i>${catName}
                </h6>
            </div>
        `;
        
        groupedDepts[catName].forEach(dept => {
            html += createDepartmentCard(dept);
        });
    });
    
    departmentList.innerHTML = html;
    selectionCard.style.display = 'block';
    
    // Add click listeners
    setTimeout(() => {
        document.querySelectorAll('.department-card').forEach(card => {
            card.addEventListener('click', function() {
                selectDepartment(this);
            });
        });
    }, 100);
    
    Toast.fire({
        icon: 'info',
        title: `พบ ${allDepartments.length} สาขาวิชา`
    });
}

function createDepartmentCard(dept) {
    return `
        <div class="col-md-6">
            <div class="department-card card h-100 shadow-sm" 
                 data-dept-id="${dept.id}" 
                 data-dept-name="${dept.name_th}"
                 data-dept-category="${dept.category_name || ''}"
                 style="cursor: pointer; transition: all 0.3s ease;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="fw-bold mb-0 flex-grow-1">${dept.name_th}</h6>
                        <span class="badge bg-primary ms-2">${dept.level || 'ไม่ระบุ'}</span>
                    </div>
                    ${dept.name_en ? `<p class="text-muted small mb-2">${dept.name_en}</p>` : ''}
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="bi bi-people-fill me-1"></i>
                            รับ: ${dept.seats_quota || 'ไม่ระบุ'} คน
                        </small>
                        <i class="bi bi-check-circle-fill text-success fs-4" style="display: none;"></i>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function selectDepartment(cardElement) {
    // Remove previous selection
    document.querySelectorAll('.department-card').forEach(card => {
        card.classList.remove('border-primary', 'bg-light');
        card.style.borderWidth = '1px';
        card.querySelector('.bi-check-circle-fill').style.display = 'none';
    });
    
    // Add selection to clicked card
    cardElement.classList.add('border-primary', 'bg-light');
    cardElement.style.borderWidth = '3px';
    cardElement.querySelector('.bi-check-circle-fill').style.display = 'block';
    
    // Set hidden input value
    const deptId = cardElement.dataset.deptId;
    const deptName = cardElement.dataset.deptName;
    const deptCategory = cardElement.dataset.deptCategory;
    
    document.getElementById('department_1').value = deptId;
    
    // Show selected department
    document.getElementById('selected_dept_name').textContent = deptName;
    document.getElementById('selected_dept_category').textContent = deptCategory;
    document.getElementById('selected_department').style.display = 'block';
    
    // Scroll to selection
    document.getElementById('selected_department').scrollIntoView({ 
        behavior: 'smooth', 
        block: 'nearest' 
    });
    
    Toast.fire({
        icon: 'success',
        title: 'เลือกสาขา: ' + deptName
    });
}

function clearDepartmentSelection() {
    // Clear selection
    document.querySelectorAll('.department-card').forEach(card => {
        card.classList.remove('border-primary', 'bg-light');
        card.style.borderWidth = '1px';
        card.querySelector('.bi-check-circle-fill').style.display = 'none';
    });
    
    document.getElementById('department_1').value = '';
    document.getElementById('selected_department').style.display = 'none';
    
    Toast.fire({
        icon: 'info',
        title: 'ยกเลิกการเลือกสาขา'
    });
}

function filterDepartments(searchTerm) {
    const cards = document.querySelectorAll('.department-card');
    const term = searchTerm.toLowerCase().trim();
    
    let visibleCount = 0;
    
    cards.forEach(card => {
        const deptName = card.dataset.deptName.toLowerCase();
        const deptCategory = card.dataset.deptCategory.toLowerCase();
        
        if (term === '' || deptName.includes(term) || deptCategory.includes(term)) {
            card.parentElement.style.display = 'block';
            visibleCount++;
        } else {
            card.parentElement.style.display = 'none';
        }
    });
    
    // Show/hide category headers
    document.querySelectorAll('#department_list > .col-12').forEach(header => {
        const nextCards = [];
        let sibling = header.nextElementSibling;
        
        while (sibling && !sibling.classList.contains('col-12')) {
            if (sibling.style.display !== 'none') {
                nextCards.push(sibling);
            }
            sibling = sibling.nextElementSibling;
        }
        
        header.style.display = nextCards.length > 0 ? 'block' : 'none';
    });
}

// ===========================================
// STEP 5: FILE UPLOAD & VALIDATION
// ===========================================

// File size limits (in bytes)
const FILE_LIMITS = {
    photo: 2 * 1024 * 1024,        // 2 MB
    id_card: 2 * 1024 * 1024,      // 2 MB
    house_reg: 2 * 1024 * 1024,    // 2 MB
    transcript: 5 * 1024 * 1024    // 5 MB
};

// Allowed file types
const ALLOWED_TYPES = {
    photo: ['image/jpeg', 'image/png', 'image/jpg'],
    document: ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'],
    pdf: ['application/pdf']
};

function setupFileUploads() {
    // Photo upload
    const photoInput = document.getElementById('photo');
    if (photoInput) {
        photoInput.addEventListener('change', function() {
            previewImage(this, 'photo_preview');
        });
    }

    // ID Card upload
    const idCardInput = document.getElementById('id_card_file');
    if (idCardInput) {
        idCardInput.addEventListener('change', function() {
            previewDocument(this, 'id_card_preview');
        });
    }

    // House Registration upload
    const houseRegInput = document.getElementById('house_registration');
    if (houseRegInput) {
        houseRegInput.addEventListener('change', function() {
            previewDocument(this, 'house_registration_preview');
        });
    }

    // Transcript upload
    const transcriptInput = document.getElementById('transcript');
    if (transcriptInput) {
        transcriptInput.addEventListener('change', function() {
            previewDocument(this, 'transcript_preview');
        });
    }
}

/**
 * Validate file size
 */
function validateFileSize(file, maxSize) {
    if (file.size > maxSize) {
        const maxMB = (maxSize / (1024 * 1024)).toFixed(1);
        return {
            valid: false,
            message: `ไฟล์มีขนาดใหญ่เกินไป (สูงสุด ${maxMB} MB)`
        };
    }
    return { valid: true };
}

/**
 * Validate file type
 */
function validateFileType(file, allowedTypes) {
    if (!allowedTypes.includes(file.type)) {
        return {
            valid: false,
            message: 'ประเภทไฟล์ไม่ถูกต้อง'
        };
    }
    return { valid: true };
}

/**
 * Preview uploaded image
 */
function previewImage(input, previewId) {
    const file = input.files[0];
    if (!file) return;

    // Validate
    const sizeCheck = validateFileSize(file, FILE_LIMITS.photo);
    if (!sizeCheck.valid) {
        Toast.fire({ icon: 'error', title: sizeCheck.message });
        input.value = '';
        return;
    }

    const typeCheck = validateFileType(file, ALLOWED_TYPES.photo);
    if (!typeCheck.valid) {
        Toast.fire({ icon: 'error', title: 'กรุณาเลือกไฟล์ JPG หรือ PNG' });
        input.value = '';
        return;
    }

    // Show preview
    const reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById(previewId).src = e.target.result;
    };
    reader.readAsDataURL(file);

    Toast.fire({ 
        icon: 'success', 
        title: 'เลือกไฟล์เรียบร้อย' 
    });
}

/**
 * Preview uploaded document (PDF or Image)
 */
function previewDocument(input, previewId) {
    const file = input.files[0];
    if (!file) return;

    // Validate size
    let maxSize = FILE_LIMITS.id_card;
    if (input.id === 'transcript') {
        maxSize = FILE_LIMITS.transcript;
    }

    const sizeCheck = validateFileSize(file, maxSize);
    if (!sizeCheck.valid) {
        Toast.fire({ icon: 'error', title: sizeCheck.message });
        input.value = '';
        return;
    }

    // Validate type
    const allowedTypes = (input.id === 'transcript') ? ALLOWED_TYPES.pdf : ALLOWED_TYPES.document;
    const typeCheck = validateFileType(file, allowedTypes);
    if (!typeCheck.valid) {
        Toast.fire({ icon: 'error', title: 'ประเภทไฟล์ไม่ถูกต้อง' });
        input.value = '';
        return;
    }

    // Show preview
    const preview = document.getElementById(previewId);
    const fileSize = (file.size / 1024).toFixed(1);

    if (file.type === 'application/pdf') {
        // PDF file
        preview.innerHTML = `
            <i class="bi bi-file-earmark-pdf-fill text-danger" style="font-size: 3rem;"></i>
            <p class="small mb-0 mt-2"><strong>${file.name}</strong></p>
            <p class="small text-muted mb-0">(${fileSize} KB)</p>
        `;
    } else {
        // Image file
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `
                <img src="${e.target.result}" class="img-fluid rounded" style="max-height: 100px; object-fit: cover;" alt="Preview">
                <p class="small mb-0 mt-2">${file.name}</p>
            `;
        };
        reader.readAsDataURL(file);
    }

    Toast.fire({ 
        icon: 'success', 
        title: 'เลือกไฟล์เรียบร้อย' 
    });
}

// ===========================================
// STEP 6: SUMMARY GENERATION
// ===========================================

/**
 * Generate summary for Step 6
 */
function generateSummary() {
    const form = document.getElementById('quotaForm');
    const formData = new FormData(form);

    // ข้อมูลส่วนตัว
    const prefix = formData.get('prefix') || '';
    const firstName = formData.get('first_name') || '';
    const lastName = formData.get('last_name') || '';
    const fullName = `${prefix}${firstName} ${lastName}`;
    
    document.getElementById('summary_name').textContent = fullName;
    document.getElementById('summary_id_card').textContent = formData.get('id_card_number') || '-';
    document.getElementById('summary_birth_date').textContent = formatThaiDate(formData.get('birth_date')) || '-';
    document.getElementById('summary_phone').textContent = formData.get('phone') || '-';

    // ที่อยู่
    const address = `${formData.get('current_address') || ''} ต.${formData.get('current_subdistrict') || ''} อ.${formData.get('current_district') || ''} จ.${formData.get('current_province') || ''} ${formData.get('current_postal_code') || ''}`;
    document.getElementById('summary_current_address').textContent = address;

    // การศึกษา
    document.getElementById('summary_school').textContent = formData.get('previous_school') || '-';
    document.getElementById('summary_gpax').textContent = formData.get('gpax') || '-';

    // สาขาที่เลือก
    const deptId = formData.get('department_1');
    if (deptId) {
        const selectedCard = document.querySelector(`.department-card[data-dept-id="${deptId}"]`);
        if (selectedCard) {
            const deptName = selectedCard.dataset.deptName;
            document.getElementById('summary_department').textContent = deptName;
        } else {
            document.getElementById('summary_department').textContent = '-';
        }
    } else {
        document.getElementById('summary_department').textContent = '-';
    }
}

/**
 * Format date to Thai format
 */
function formatThaiDate(dateString) {
    if (!dateString) return '-';
    
    const date = new Date(dateString);
    const thaiMonths = [
        'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน',
        'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม',
        'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'
    ];
    
    const day = date.getDate();
    const month = thaiMonths[date.getMonth()];
    const year = date.getFullYear() + 543;
    
    return `${day} ${month} ${year}`;
}

// ===========================================
// FORM SUBMISSION
// ===========================================

/**
 * Handle form submission
 */
const quotaForm = document.getElementById('quotaForm');
if (quotaForm) {
    quotaForm.addEventListener('submit', function(e) {
        e.preventDefault();

        // Check if terms accepted
        const termsCheckbox = document.getElementById('accept_terms');
        if (!termsCheckbox.checked) {
            Swal.fire({
                icon: 'warning',
                title: 'กรุณายอมรับเงื่อนไข',
                text: 'คุณต้องยอมรับเงื่อนไขและข้อตกลงก่อนส่งใบสมัคร',
                confirmButtonText: 'ตกลง',
                confirmButtonColor: '#4facfe'
            });
            return;
        }

        // Show loading
        Swal.fire({
            title: 'กำลังส่งใบสมัคร...',
            html: 'กรุณารอสักครู่',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Create FormData
        const formData = new FormData(this);

        // Submit via AJAX
        fetch('pages/form_submit.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'สมัครสำเร็จ!',
                    html: `
                        <p class="mb-3">ส่งใบสมัครเรียบร้อยแล้ว</p>
                        <div class="alert alert-info">
                            <strong>เลขที่ใบสมัคร:</strong><br>
                            <h3 class="text-primary mb-0">${data.application_number}</h3>
                        </div>
                        <p class="small text-muted">กรุณาจดบันทึกเลขที่ใบสมัครไว้เพื่อตรวจสอบสถานะ</p>
                    `,
                    confirmButtonText: 'ตรวจสอบสถานะ',
                    showCancelButton: true,
                    cancelButtonText: 'กลับหน้าหลัก',
                    confirmButtonColor: '#4facfe',
                    cancelButtonColor: '#6c757d'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'index.php?page=check_status';
                    } else {
                        window.location.href = 'index.php?page=home';
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด',
                    text: data.message || 'ไม่สามารถส่งใบสมัครได้ กรุณาลองใหม่อีกครั้ง',
                    confirmButtonText: 'ตกลง',
                    confirmButtonColor: '#4facfe'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'เกิดข้อผิดพลาด',
                text: 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้',
                confirmButtonText: 'ตกลง',
                confirmButtonColor: '#4facfe'
            });
        });
    });
}

// ========================================
// Utility Functions
// ========================================

function showLoading(text = 'กำลังโหลด...') {
    Swal.fire({
        title: text,
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
}

function hideLoading() {
    Swal.close();
}

console.log('✅ Quota Form Step 1-6 Loaded Successfully!');