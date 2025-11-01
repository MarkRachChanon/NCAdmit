/**
 * Regular Form - Multi-Step Navigation
 * ฟอร์มรับสมัครรอบปกติ
 * * Updated to match new form structure (aligned with students_regular table)
 */

let currentStep = 1;
const totalSteps = 7;
const form = document.getElementById('regularForm');

// Initialize
document.addEventListener('DOMContentLoaded', function () {
    setupNavigation();
    setupFormInputs();
    loadSavedData();
    setupDepartmentSelection();

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
        // 🚀 ทำให้เป็น async function
        btn.addEventListener('click', async function () {

            // 1. ตรวจสอบความถูกต้องก่อน
            if (!validateCurrentStep()) {
                return;
            }

            // 2. 🚀 ตรรกะพิเศษสำหรับ Step 6 (อัปโหลดไฟล์)
            if (currentStep === 6) {
                // *** NEW LOGIC: VALIDATE FILES ONLY, DO NOT UPLOAD ***
                if (!validateFileInputs()) { // ตรวจสอบว่าเลือกไฟล์ครบหรือไม่
                    return;
                }

                // ไม่ต้องเรียก handleFileUploads() ที่นี่แล้ว
                saveStepData();
                updateSummary();
                nextStep();

            } else {
                // 3. ตรรกะปกติสำหรับ Step อื่นๆ
                saveStepData();

                if (currentStep === 5) { // Step 6 คือ Step ก่อน Step สรุป (7)
                    updateSummary();
                }

                nextStep();
            }
        });
    });

    // Previous buttons
    document.querySelectorAll('.btn-prev').forEach(btn => {
        btn.addEventListener('click', function () {
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
    switch (currentStep) {
        case 1:
            return validatePersonalInfo();
        case 2:
            return validateAddress();
        case 3:
            return validateEducation();
        case 4:
            return validateFamily();
        case 5:
            return validateDepartment();
        default:
            return true;
    }

    function validateFamily() {
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
            text: 'กรุณากรอกอีเมลที่ถูกต้อง',
            confirmButtonColor: '#4facfe'
        });
        return false;
    }

    return true;
}

function validateAddress() {
    const postcode = document.querySelector('[name="postcode"]').value;

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
    return true; // Basic validation only
}

function validateDepartment() {
    const educationLevel = document.getElementById('education_level_apply').value;
    const departmentId = document.getElementById('department_id').value;

    if (!educationLevel) {
        Swal.fire({
            icon: 'error',
            title: 'กรุณาเลือกระดับชั้น',
            text: 'กรุณาเลือกระดับชั้นที่ต้องการสมัคร',
            confirmButtonColor: '#4facfe'
        });
        return false;
    }

    if (!departmentId) {
        Swal.fire({
            icon: 'error',
            title: 'กรุณาเลือกสาขาวิชา',
            text: 'กรุณาเลือกสาขาวิชาที่ต้องการสมัคร',
            confirmButtonColor: '#4facfe'
        });
        return false;
    }

    return true;
}

function validateFileInputs() {
    // ตรวจสอบว่าไฟล์ถูกเลือกแล้วหรือไม่
    const photoFile = document.getElementById('photo').files[0];
    const transcriptFile = document.getElementById('transcript').files[0];

    // ===== เช็ครูปภาพ =====
    if (!photoFile) {
        Swal.fire({
            icon: 'error',
            title: 'ขาดไฟล์ที่จำเป็น',
            text: 'กรุณาอัปโหลดรูปถ่ายหน้าตรง',
            confirmButtonColor: '#4facfe'
        });
        return false;
    }

    // 🚀 ตรวจสอบว่ารูปภาพเป็น JPG/PNG จริงๆ
    const validImageTypes = ['image/jpeg', 'image/jpg', 'image/png'];
    if (!validImageTypes.includes(photoFile.type)) {
        Swal.fire({
            icon: 'error',
            title: 'ไฟล์รูปภาพไม่ถูกต้อง',
            text: 'กรุณาอัปโหลดไฟล์รูปภาพ JPG หรือ PNG เท่านั้น\n(ไฟล์ที่คุณเลือก: ' + photoFile.type + ')',
            confirmButtonColor: '#4facfe'
        });
        document.getElementById('photo').value = ''; // เคลียร์ไฟล์
        return false;
    }

    // ===== เช็ค Transcript =====
    if (!transcriptFile) {
        Swal.fire({
            icon: 'error',
            title: 'ขาดไฟล์ที่จำเป็น',
            text: 'กรุณาอัปโหลดใบรับรองผลการเรียน',
            confirmButtonColor: '#4facfe'
        });
        return false;
    }

    // 🚀 ตรวจสอบว่า Transcript เป็น PDF จริงๆ (CRITICAL!)
    if (transcriptFile.type !== 'application/pdf') {
        Swal.fire({
            icon: 'error',
            title: 'ไฟล์ PDF ไม่ถูกต้อง',
            html: `
                <p>ไฟล์ที่คุณเลือกไม่ใช่ PDF จริง</p>
                <small class="text-muted">ประเภทไฟล์ที่ตรวจพบ: ${transcriptFile.type || 'ไม่ทราบ'}</small>
                <br><br>
                <p class="text-start">
                    <strong>💡 วิธีแก้ไข:</strong><br>
                    1. เปิดไฟล์ต้นฉบับ (เช่น Word, รูปภาพ)<br>
                    2. เลือก File → Save As / Export to PDF<br>
                    3. อัปโหลดไฟล์ PDF ที่ได้ใหม่
                </p>
            `,
            confirmButtonColor: '#4facfe',
            width: '500px'
        });
        document.getElementById('transcript').value = ''; // เคลียร์ไฟล์
        return false;
    }

    return true;
}

// ========================================
// Form Input Setup
// ========================================

function setupFormInputs() {
    setupPhoneInputFormat('phone');
    setupPhoneInputFormat('father_phone');
    setupPhoneInputFormat('mother_phone');
    setupPhoneInputFormat('guardian_phone');

    // ID Card Format (X-XXXX-XXXXX-XX-X)
    const idCardInput = document.getElementById('id_card');
    if (idCardInput) {
        idCardInput.addEventListener('input', function (e) {
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
    }

    // Auto-calculate Age
    const birthDateInput = document.querySelector('[name="birth_date"]');
    const ageInput = document.querySelector('[name="age"]');
    if (birthDateInput && ageInput) {
        birthDateInput.addEventListener('change', function () {
            const birthDate = new Date(this.value);
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();

            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }

            ageInput.value = age > 0 ? age : '';
        });
    }

    const gpaInput = document.getElementById('gpa');
    if (gpaInput) {
        gpaInput.addEventListener('input', function (e) {
            let value = parseFloat(e.target.value);
            if (value > 4) {
                e.target.value = '4.00';
            } else if (value < 0) {
                e.target.value = '0.00';
            }
        });

        gpaInput.addEventListener('blur', function (e) {
            let value = parseFloat(e.target.value);
            if (!isNaN(value)) {
                e.target.value = value.toFixed(2);
            }
        });
    }

    // 🎯 Education Level Apply - บันทึกค่า
    const educationLevelApply = document.getElementById('education_level_apply');
    if (educationLevelApply) {
        educationLevelApply.addEventListener('change', function () {
            const selectedLevel = this.value;

            // บันทึกลง sessionStorage
            sessionStorage.setItem('regular_apply_level', selectedLevel);

            // โหลดสาขาตามระดับ
            if (selectedLevel && typeof loadDepartmentsByLevel === 'function') {
                loadDepartmentsByLevel(selectedLevel);
            }
        });

        // โหลดค่าจาก sessionStorage
        const savedLevel = sessionStorage.getItem('regular_apply_level');
        if (savedLevel) {
            educationLevelApply.value = savedLevel;

            if (typeof loadDepartmentsByLevel === 'function') {
                loadDepartmentsByLevel(savedLevel);
            }
        }
    }

    // Zipcode Auto-format
    const zipcodeInput = document.querySelector('[name="postcode"]');
    if (zipcodeInput) {
        zipcodeInput.addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 5) value = value.substring(0, 5);
            e.target.value = value;
        });
    }

    // Disability Toggle
    const disabilitySelect = document.getElementById('disability');
    const disabilityTypeWrapper = document.getElementById('disability_type_wrapper');
    if (disabilitySelect && disabilityTypeWrapper) {
        disabilitySelect.addEventListener('change', function () {
            if (this.value === 'มี') {
                disabilityTypeWrapper.style.display = 'block';
            } else {
                disabilityTypeWrapper.style.display = 'none';
            }
        });
    }

    // Auto-save on input
    document.querySelectorAll('input, select, textarea').forEach(input => {
        input.addEventListener('change', function () {
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
    setupFilePreview('transcript', 'transcript_preview', 'pdf', 5);
}

function setupPhoneInputFormat(inputId) {
    const phoneInput = document.getElementById(inputId);
    if (phoneInput) {
        phoneInput.addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');
            // จำกัดความยาว
            if (value.length > 10) value = value.substring(0, 10);

            // จัดรูปแบบ XXX-XXX-XXXX
            if (value.length > 3) {
                value = value.substring(0, 3) + '-' + value.substring(3);
            }
            if (value.length > 7) {
                value = value.substring(0, 7) + '-' + value.substring(7);
            }

            e.target.value = value;
        });
    }
}

function setupFilePreview(inputId, previewId, type = 'pdf', maxMB = 2) {
    const input = document.getElementById(inputId);
    const previewContainer = document.getElementById(previewId);
    const maxBytes = maxMB * 1024 * 1024;

    if (input && previewContainer) {
        input.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                if (file.size > maxBytes) {
                    Toast.fire({
                        icon: 'error',
                        title: 'ไฟล์มีขนาดใหญ่เกินไป',
                        text: `กรุณาเลือกไฟล์ขนาดไม่เกิน ${maxMB} MB`
                    });
                    this.value = '';

                    if (type === 'image') {
                        previewContainer.src = 'https://placehold.co/150x200/4facfe/ffffff?text=Photo';
                    } else {
                        previewContainer.innerHTML = `
                            <i class="bi bi-file-earmark-pdf text-muted" style="font-size: 3rem;"></i>
                            <p class="small text-muted mb-0 mt-2">ยังไม่ได้เลือกไฟล์</p>
                        `;
                    }
                    return;
                }

                const reader = new FileReader();
                reader.onload = function (e) {
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
// Data Persistence
// ========================================

function saveStepData() {
    const form = document.getElementById('regularForm');
    const formData = new FormData(form);
    const stepData = {};

    formData.forEach((value, key) => {
        stepData[key] = value;
    });

    // 🎯 บันทึก apply_level ถ้ามี
    const applyLevel = document.getElementById('education_level_apply')?.value;
    if (applyLevel) {
        stepData.apply_level = applyLevel;
        sessionStorage.setItem('regular_apply_level', applyLevel);
    }

    sessionStorage.setItem(`regularFormStep${currentStep}`, JSON.stringify(stepData));
}

function loadSavedData() {
    for (let step = 1; step <= totalSteps; step++) {
        const savedData = sessionStorage.getItem(`regularFormStep${step}`);

        if (savedData) {
            try {
                const stepData = JSON.parse(savedData);

                Object.keys(stepData).forEach(key => {
                    const input = document.querySelector(`[name="${key}"]`);
                    if (input) {
                        input.value = stepData[key];
                    }
                });
            } catch (e) {
                console.error(`Error loading step ${step}:`, e);
            }
        }
    }

    // 🚀 โหลดระดับชั้นและสาขาวิชา
    loadDepartmentSelection();
}

/**
 * 🚀 ฟังก์ชันใหม่: โหลดข้อมูลระดับชั้นและสาขาวิชา
 */
function loadDepartmentSelection() {
    // 1. โหลดระดับชั้นที่เลือก
    const savedLevel = sessionStorage.getItem('regular_apply_level');
    const educationLevelApply = document.getElementById('education_level_apply');
    
    if (savedLevel && educationLevelApply) {
        educationLevelApply.value = savedLevel;
        
        // 2. โหลดสาขาวิชาตามระดับชั้น
        if (typeof loadDepartmentsByLevel === 'function') {
            loadDepartmentsByLevel(savedLevel);
        }
        
        // 3. รอให้สาขาวิชาโหลดเสร็จ แล้วค่อยเลือกสาขาที่เคยเลือกไว้
        setTimeout(() => {
            const savedDeptId = sessionStorage.getItem('regular_selected_dept_id');
            const savedDeptName = sessionStorage.getItem('regular_selected_dept_name');
            const savedDeptCategory = sessionStorage.getItem('regular_selected_dept_category');
            
            if (savedDeptId) {
                // หาการ์ดสาขาที่ตรงกับ ID ที่บันทึกไว้
                const deptCard = document.querySelector(`.department-card[data-dept-id="${savedDeptId}"]`);
                
                if (deptCard) {
                    // เลือกสาขานั้น (โดยไม่บันทึกซ้ำ)
                    document.querySelectorAll('.department-card').forEach(card => {
                        card.classList.remove('border-primary', 'bg-light');
                        card.style.borderWidth = '1px';
                        card.querySelector('.bi-check-circle-fill').style.display = 'none';
                    });
                    
                    deptCard.classList.add('border-primary', 'bg-light');
                    deptCard.style.borderWidth = '3px';
                    deptCard.querySelector('.bi-check-circle-fill').style.display = 'block';
                    
                    // Set hidden input
                    document.getElementById('department_id').value = savedDeptId;
                    
                    // Show selected department
                    if (savedDeptName && savedDeptCategory) {
                        document.getElementById('selected_dept_name').textContent = savedDeptName;
                        document.getElementById('selected_dept_category').textContent = savedDeptCategory;
                        document.getElementById('selected_department').style.display = 'block';
                    }
                    
                    // แสดง Department Selection Card
                    const selectionCard = document.getElementById('department_selection_card');
                    if (selectionCard) {
                        selectionCard.style.display = 'block';
                    }
                }
            }
        }, 500); // รอ 500ms ให้สาขาโหลดเสร็จ
    }
}

// ========================================
// Department Selection Functions
// ========================================

function setupDepartmentSelection() {
    const levelSelect = document.getElementById('education_level_apply');
    const searchInput = document.getElementById('department_search');

    if (levelSelect) {
        levelSelect.addEventListener('change', function () {
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
        searchInput.addEventListener('input', function () {
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
            card.addEventListener('click', function () {
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

    document.getElementById('department_id').value = deptId;

    // 🚀 บันทึก Department ID และข้อมูลลง sessionStorage
    sessionStorage.setItem('regular_selected_dept_id', deptId);
    sessionStorage.setItem('regular_selected_dept_name', deptName);
    sessionStorage.setItem('regular_selected_dept_category', deptCategory);

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

    document.getElementById('department_id').value = '';
    document.getElementById('selected_department').style.display = 'none';

    // 🚀 ลบข้อมูลสาขาออกจาก sessionStorage
    sessionStorage.removeItem('regular_selected_dept_id');
    sessionStorage.removeItem('regular_selected_dept_name');
    sessionStorage.removeItem('regular_selected_dept_category');

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
// File Upload Functions
// ========================================

/**
 * จัดการอัปโหลดไฟล์ทั้งหมด (photo, transcript)
 * ถูกเรียกใช้ใน submitForm() เท่านั้น
 * @param {string} academicYear - ปีการศึกษา
 * @returns {Promise<{success: boolean, data: object|null, error: string|null}>} 
 */
async function handleFileUploads(academicYear) {
    const uploadPromises = [];
    const uploadedFilesData = {}; // เก็บ path ของไฟล์ที่อัปโหลดสำเร็จแล้ว

    const filesToProcess = [
        { input: document.getElementById('photo'), type: 'photo' },
        { input: document.getElementById('transcript'), type: 'transcript' }
        // 🚨 เพิ่ม Input File อื่น ๆ ที่มี
    ];

    let allRequiredFilesPresent = true;

    for (const item of filesToProcess) {
        const { input, type } = item;
        const file = input.files.length > 0 ? input.files[0] : null;

        if (file) {
            uploadPromises.push(uploadFile(file, type, academicYear));
        } else {
            allRequiredFilesPresent = false;
        }
    }

    if (!allRequiredFilesPresent) {
        return { success: false, data: null, error: 'File inputs are empty. (Validation failed or inputs were cleared incorrectly)' };
    }

    const results = await Promise.all(uploadPromises);
    let allSuccess = true;

    const errorMessages = [];

    results.forEach(res => {
        if (res.success) {
            uploadedFilesData[res.type] = {
                path: res.path,
                filename: res.filename,
                original_name: res.original_name
            };
        } else {
            allSuccess = false;

            // 🚀 เก็บ Error Message พร้อมประเภทไฟล์
            const fileTypeName = res.type === 'photo' ? 'รูปถ่าย' :
                res.type === 'transcript' ? 'ใบรับรองผลการเรียน' :
                    res.type;
            errorMessages.push(`${fileTypeName}: ${res.message}`);

            console.error('Upload failed for', res.type, res.message);
        }
    });

    if (!allSuccess) {
        return {
            success: false,
            data: null,
            error: errorMessages.join('\n') || 'เกิดข้อผิดพลาดในการอัปโหลดไฟล์'
        };
    }

    // บันทึก path ที่อัปโหลดสำเร็จลง Session Storage เพื่อให้ getFormData() นำไปใช้
    sessionStorage.setItem('regularFormUploads', JSON.stringify(uploadedFilesData));

    // 🚀 ส่วนสำคัญ: เคลียร์ Input File Field ทันทีหลังอัปโหลดสำเร็จ
    document.getElementById('photo').value = '';
    document.getElementById('transcript').value = '';

    return { success: true, data: uploadedFilesData };
}

/**
 * ฟังก์ชันช่วย: อัปโหลดไฟล์เดียวไปยัง upload_handler.php
 * @param {File} file - ไฟล์ที่ต้องการอัปโหลด
 * @param {string} type - ประเภทไฟล์ (เช่น 'photo', 'transcript')
 * @param {string} academicYear - ปีการศึกษา
 * @returns {Promise<object>} - ผลลัพธ์ JSON จาก server
 */
async function uploadFile(file, type, academicYear) {
    const formData = new FormData();
    formData.append('file', file);
    formData.append('type', type);
    // formData.append('academic_year', academicYear);

    try {
        const response = await fetch('includes/upload_handler.php', {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            throw new Error('Server error: ' + response.statusText);
        }

        const result = await response.json();

        // เพิ่ม 'type' กลับเข้าไปในผลลัพธ์ เพื่อให้ Promise.all จัดการได้ง่าย
        return { ...result, type: type };

    } catch (error) {
        console.error('Fetch error for', type, error);
        return { success: false, message: error.message, type: type };
    }
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

// ==========================================
// ฟังก์ชันส่งฟอร์ม (เพิ่มใหม่)
// ==========================================

/**
 * ส่งข้อมูลทั้งหมดไปยัง Server
 */
async function submitForm() {
    showLoading('กำลังอัปโหลดเอกสารและส่งใบสมัคร...');

    const academicYear = (new Date().getFullYear() + 543 + 1).toString();

    // 1. UPLOAD FILES FIRST
    const uploadResult = await handleFileUploads(academicYear);

    if (!uploadResult.success) {
        hideLoading();

        // 🚀 แสดง Error Message จาก Server
        Swal.fire({
            icon: 'error',
            title: 'อัปโหลดเอกสารไม่สำเร็จ',
            html: `
                <div class="text-start">
                    <p class="mb-3">${uploadResult.error}</p>
                    <hr>
                    <p class="small text-muted mb-2"><strong>คำแนะนำ:</strong></p>
                    <ul class="small text-muted">
                        <li>ตรวจสอบว่าไฟล์เป็น PDF จริง (ไม่ใช่การเปลี่ยนนามสกุล)</li>
                        <li>ตรวจสอบว่ารูปภาพเป็น JPG หรือ PNG</li>
                        <li>ตรวจสอบขนาดไฟล์ไม่เกินที่กำหนด</li>
                    </ul>
                </div>
            `,
            confirmButtonColor: '#4facfe',
            width: '600px'
        });
        return;
    }

    // 2. GET FORM DATA
    const formData = getFormData();
    try {
        const response = await fetch('pages/regular_form_submit.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });

        const result = await response.json();

        hideLoading();
        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: 'ส่งใบสมัครสำเร็จ!',
                html: `หมายเลขใบสมัคร: <b>${result.application_no}</b><br>
                       ผู้สมัคร: <b>${result.name}</b>`,
                showCancelButton: true,
                confirmButtonText: 'ตรวจสอบสถานะ',
                cancelButtonText: 'ปิด'
            }).then((result) => {
                clearAllData();
                if (result.isConfirmed) {
                    window.location.href = 'index.php?page=check_status';
                } else {
                    window.location.reload();
                }
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'ส่งใบสมัครล้มเหลว',
                text: result.message
            });
        }
    } catch (error) {
        hideLoading();
        console.error('❌ Submission Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'เกิดข้อผิดพลาด',
            text: 'ไม่สามารถติดต่อเซิร์ฟเวอร์ได้'
        });
    }
}

// ในไฟล์ regular-form.js (ฟังก์ชัน getFormData)
function getFormData() {
    const form = document.getElementById('regularForm');
    const educationLevelApply = document.getElementById('education_level_apply');

    const formData = {
        id_card: form.id_card?.value || '',
        prefix: form.prefix?.value || '',
        firstname_th: form.firstname_th?.value || '',
        lastname_th: form.lastname_th?.value || '',
        nickname: form.nickname?.value || '',
        birth_date: form.birth_date?.value || '',
        age: form.age?.value || '',
        nationality: form.nationality?.value || '',
        ethnicity: form.ethnicity?.value || '',
        religion: form.religion?.value || '',
        blood_group: form.blood_group?.value || '',
        phone: form.phone?.value || '',
        email: form.email?.value || '',

        // ฟิลด์ใหม่ - Step 1
        birth_province: form.birth_province?.value || '',
        height: form.height?.value || '',
        weight: form.weight?.value || '',
        disability: form.disability?.value || 'ไม่มี',
        disability_type: form.disability_type?.value || '',
        line_id: form.line_id?.value || '',

        // ที่อยู่ + ฟิลด์ใหม่
        address_no: form.address_no?.value || '',
        village_no: form.village_no?.value || '',
        village_name: form.village_name?.value || '',
        soi: form.soi?.value || '',
        road: form.road?.value || '',
        subdistrict: form.subdistrict?.value || '',
        district: form.district?.value || '',
        province: form.province?.value || '',
        postcode: form.postcode?.value || '',
        phone_home: form.phone_home?.value || '',

        // การศึกษา
        current_school: form.current_school?.value || '',
        school_address: form.school_address?.value || '',
        current_class: form.current_class?.value || '',
        current_level: form.current_level?.value || '',
        graduation_year: form.graduation_year?.value || '',
        gpa: form.gpa?.value || '',
        talents: form.talents?.value || '',

        // ข้อมูลครอบครัว - Step 4
        father_prefix: form.father_prefix?.value || '',
        father_firstname: form.father_firstname?.value || '',
        father_lastname: form.father_lastname?.value || '',
        father_status: form.father_status?.value || 'มีชีวิต',
        father_occupation: form.father_occupation?.value || '',
        father_income: form.father_income?.value || '',
        father_phone: form.father_phone?.value || '',
        father_disability: form.father_disability?.value || 'ไม่มี',
        father_disability_type: form.father_disability_type?.value || '',

        mother_prefix: form.mother_prefix?.value || '',
        mother_firstname: form.mother_firstname?.value || '',
        mother_lastname: form.mother_lastname?.value || '',
        mother_status: form.mother_status?.value || 'มีชีวิต',
        mother_occupation: form.mother_occupation?.value || '',
        mother_income: form.mother_income?.value || '',
        mother_phone: form.mother_phone?.value || '',
        mother_disability: form.mother_disability?.value || 'ไม่มี',
        mother_disability_type: form.mother_disability_type?.value || '',

        parents_status: form.parents_status?.value || '',

        guardian_prefix: form.guardian_prefix?.value || '',
        guardian_firstname: form.guardian_firstname?.value || '',
        guardian_lastname: form.guardian_lastname?.value || '',
        guardian_relation: form.guardian_relation?.value || '',
        guardian_occupation: form.guardian_occupation?.value || '',
        guardian_income: form.guardian_income?.value || '',
        guardian_phone: form.guardian_phone?.value || '',

        // 🎯 สาขา - Step 5 (ใช้ sessionStorage เป็น fallback)
        education_level_apply: educationLevelApply?.value ||
            sessionStorage.getItem('regular_apply_level') || '',
        department_id: document.getElementById('department_id')?.value || '',
        department_name: document.getElementById('selected_dept_name')?.textContent || '',

        // ไฟล์
        uploaded_files: JSON.parse(sessionStorage.getItem('regularFormUploads')) || {},
        form_type: 'regular',
        // academic_year: (new Date().getFullYear() + 543 + 1).toString()
    };

    return formData;
}

function clearAllData() {
    [
        'regularFormStep1', 'regularFormStep2', 'regularFormStep3', 
        'regularFormStep4', 'regularFormStep5', 'regularFormStep6', 
        'regularFormStep7', 'regularFormUploads', 'regularFormProgress',
        'regular_apply_level',
        'regular_selected_dept_id',
        'regular_selected_dept_name',
        'regular_selected_dept_category'
    ].forEach(key => sessionStorage.removeItem(key));
}