/**
 * Quota Form - Multi-Step Navigation
 * ฟอร์มรับสมัครรอบโควต้า
 * * Updated to match new form structure (aligned with students_quota table)
 */

window.addEventListener('load', function() {
  // รีเซ็ต dropdown เมื่อโหลดหน้า
  if (document.getElementById('education_level_apply')) {
    document.getElementById('education_level_apply').selectedIndex = 0;
  }
});

let currentStep = 1;
const totalSteps = 6;
const form = document.getElementById('quotaForm');

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    setupNavigation();
    setupFormInputs();
    // setupAddressCheckbox(); // REMOVED
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
        // ตรวจสอบเฉพาะ input ที่แสดงผล (ไม่นับรวมที่ซ่อนโดย 'display: none')
        if (input.offsetParent !== null) {
            if (!input.value.trim()) {
                isValid = false;
                input.classList.add('is-invalid');
                
                if (!firstInvalidField) {
                    firstInvalidField = input;
                }
            } else {
                input.classList.remove('is-invalid');
            }
        }
    });
    
    if (!isValid) {
        Swal.fire({
            icon: 'error',
            title: 'ข้อมูลไม่ครบถ้วน',
            text: 'กรุณากรอกข้อมูลที่มีเครื่องหมาย * ให้ครบถ้วน',
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
        // case 5: 
        //     return validateFiles(); // (Optional) Add file validation if needed
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
    // (REMOVED logic for 'same_address')
    // Add zipcode validation
    const postcode = document.getElementById('postcode').value;
    if (postcode.length !== 5 || !/^\d+$/.test(postcode)) {
         Swal.fire({
            icon: 'error',
            title: 'รหัสไปรษณีย์ไม่ถูกต้อง',
            text: 'กรุณากรอกรหัสไปรษณีย์ 5 หลัก',
            confirmButtonColor: '#4facfe'
        });
        return false;
    }
    return true;
}

function validateEducation() {
    // *** FIELD ID UPDATED ***
    const gpa = parseFloat(document.getElementById('gpa').value);
    
    // *** REMOVED parent_phone validation ***
    
    // Validate GPAX
    if (isNaN(gpa) || gpa < 0 || gpa > 4) {
        Swal.fire({
            icon: 'error',
            title: 'เกรดเฉลี่ยไม่ถูกต้อง',
            text: 'กรุณากรอกเกรดเฉลี่ย (GPAX) ระหว่าง 0.00 - 4.00',
            confirmButtonColor: '#4facfe'
        });
        return false;
    }
    
    return true;
}

function validateDepartment() {
    // *** FIELD ID UPDATED ***
    const department = document.getElementById('department_id').value;
    
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

// ========================================
// Form Input Helpers
// ========================================

function setupFormInputs() {
    // ID Card Format (X-XXXX-XXXXX-XX-X)
    const idCardInput = document.getElementById('id_card');
    if (idCardInput) {
        idCardInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 13) value = value.slice(0, 13);
            
            let formatted = '';
            if (value.length > 0) formatted += value.substr(0, 1);
            if (value.length > 1) formatted += '-' + value.substr(1, 4);
            if (value.length > 5) formatted += '-' + value.substr(5, 5);
            if (value.length > 10) formatted += '-' + value.substr(10, 2);
            if (value.length > 12) formatted += '-' + value.substr(12, 1);
            e.target.value = formatted;
        });
    }
    
    // Phone Format (0XX-XXX-XXXX) - Student
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 10) value = value.slice(0, 10);
            
            let formatted = '';
            if (value.length > 0) formatted += value.substr(0, 3);
            if (value.length > 3) formatted += '-' + value.substr(3, 3);
            if (value.length > 6) formatted += '-' + value.substr(6, 4);
            e.target.value = formatted;
        });
    }
    
    // *** REMOVED Parent Phone Formatting ***
    
    // GPAX Format (0.00 - 4.00)
    // *** FIELD ID UPDATED ***
    const gpaInput = document.getElementById('gpa');
    if (gpaInput) {
        gpaInput.addEventListener('input', function(e) {
            let value = parseFloat(e.target.value);
            if (value > 4) {
                e.target.value = '4.00';
            } else if (value < 0) {
                e.target.value = '0.00';
            }
        });
        
        gpaInput.addEventListener('blur', function(e) {
            let value = parseFloat(e.target.value);
            if (!isNaN(value)) {
                e.target.value = value.toFixed(2);
            }
        });
    }
    
    // Zipcode Format (เฉพาะตัวเลข 5 หลัก)
    // *** FIELD NAME UPDATED ***
    const zipcodeInputs = document.querySelectorAll('[name="postcode"]');
    zipcodeInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 5) value = value.slice(0, 5);
            e.target.value = value;
        });
    });
    
    // Auto-save on input
    document.querySelectorAll('input, select, textarea').forEach(input => {
        input.addEventListener('change', function() {
            clearTimeout(window.saveTimeout);
            window.saveTimeout = setTimeout(() => {
                saveStepData();
            }, 1000);
        });
    });
    
    // Setup Department Selection
    setupDepartmentSelection();

    // Setup File Previews
    setupFilePreview('photo', 'photo_preview', 'image', 2);
    // setupFilePreview('id_card_file', 'id_card_preview'); // REMOVED
    // setupFilePreview('house_registration', 'house_registration_preview'); // REMOVED
    setupFilePreview('transcript', 'transcript_preview', 'pdf', 5);
}

// ใน assets/js/quota-form.js

function setupFilePreview(inputId, previewId, type = 'pdf', maxMB = 2) {
    const input = document.getElementById(inputId);
    const previewContainer = document.getElementById(previewId);
    const maxBytes = maxMB * 1024 * 1024; // คำนวณขนาดเป็น Bytes

    if (input && previewContainer) {
        input.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                // --- START: ส่วนที่เพิ่มเข้ามา ---
                // ตรวจสอบขนาดไฟล์
                if (file.size > maxBytes) {
                    Toast.fire({
                        icon: 'error',
                        title: 'ไฟล์มีขนาดใหญ่เกินไป',
                        text: `กรุณาเลือกไฟล์ขนาดไม่เกิน ${maxMB} MB`
                    });
                    
                    // ล้างค่าไฟล์ที่เลือก
                    this.value = ''; 
                    
                    // รีเซ็ต Preview
                    if (type === 'image') {
                        previewContainer.src = 'https://placehold.co/150x200/4facfe/ffffff?text=Photo'; // URL Placeholder
                    } else {
                        previewContainer.innerHTML = `
                            <i class="bi bi-file-earmark-pdf text-muted" style="font-size: 3rem;"></i>
                            <p class="small text-muted mb-0 mt-2">ยังไม่ได้เลือกไฟล์</p>
                        `;
                    }
                    return; // หยุดการทำงาน
                }
                // --- END: ส่วนที่เพิ่มเข้ามา ---

                const reader = new FileReader();
                reader.onload = function(e) {
                    if (type === 'image') {
                        previewContainer.src = e.target.result;
                    } else {
                        previewContainer.innerHTML = `
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                            <p class="small text-dark mb-0 mt-2" style="overflow-wrap: break-word;">${file.name}</p>
                        `;
                    }
                }
                reader.readAsDataURL(file);
            }
        });
    }
}

// ========================================
// Address Functions
// ========================================

// *** REMOVED setupAddressCheckbox() ***
// *** REMOVED copyCurrentToRegister() ***


// ========================================
// Session Storage
// ========================================

function saveStepData() {
    const currentStepElement = document.querySelector(`.form-step[data-step="${currentStep}"]`);
    if (!currentStepElement) return;

    const inputs = currentStepElement.querySelectorAll('input, select, textarea');
    
    const formData = {};
    inputs.forEach(input => {
        if (input.name) { // Ensure the input has a name
            if (input.type === 'checkbox') {
                formData[input.name] = input.checked;
            } else if (input.type === 'file') {
                // ไม่เก็บ file ใน session
            } else {
                formData[input.name] = input.value;
            }
        }
    });
    
    // Save to session (Assuming you have these backend files)
    fetch('includes/save_session.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            step: currentStep,
            data: formData
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Session saved for step ' + currentStep);
        }
    })
    .catch(error => {
        console.error('Error saving session:', error);
    });
}

function loadSavedData() {
    // (Assuming you have these backend files)
    fetch('includes/load_session.php')
    .then(response => response.json())
    .then(data => {
        if (data && data.quota_form_data) {
            Object.keys(data.quota_form_data).forEach(key => {
                const input = document.querySelector(`[name="${key}"]`);
                if (input) {
                    if (input.type === 'checkbox') {
                        input.checked = data.quota_form_data[key];
                    } else {
                        input.value = data.quota_form_data[key];
                    }

                    // Trigger change event for dependent logic
                    if (key === 'birth_date') {
                         input.dispatchEvent(new Event('change'));
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
                // *** FIELD ID UPDATED ***
                document.getElementById('department_id').value = '';
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
    // *** FIELD ID UPDATED ***
    document.getElementById('department_id').value = '';
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
    
    // *** FIELD ID UPDATED ***
    document.getElementById('department_id').value = deptId;
    
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
    
    // *** FIELD ID UPDATED ***
    document.getElementById('department_id').value = '';
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

console.log('Quota Form (v2) Loaded ✓');