/**
 * NCAdmit - Custom JavaScript
 */

// Smooth Scroll
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Navbar Background Change on Scroll
window.addEventListener('scroll', function() {
    const navbar = document.querySelector('.navbar');
    if (window.scrollY > 50) {
        navbar.style.backdropFilter = 'blur(20px)';
        navbar.style.boxShadow = '0 4px 20px rgba(79, 172, 254, 0.3)';
    } else {
        navbar.style.backdropFilter = 'blur(10px)';
        navbar.style.boxShadow = '0 4px 16px rgba(79, 172, 254, 0.25)';
    }
});

// Form Validation Helper
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    return form.checkValidity();
}

// Show Loading Spinner
function showLoading() {
    Swal.fire({
        title: 'กำลังประมวลผล...',
        html: '<div class="spinner-gradient mx-auto"></div>',
        showConfirmButton: false,
        allowOutsideClick: false
    });
}

// Hide Loading
function hideLoading() {
    Swal.close();
}

// Success Alert
function showSuccess(title, message) {
    Swal.fire({
        icon: 'success',
        title: title,
        text: message,
        confirmButtonText: 'ตกลง',
        confirmButtonColor: '#4facfe'
    });
}

// Error Alert
function showError(title, message) {
    Swal.fire({
        icon: 'error',
        title: title,
        text: message,
        confirmButtonText: 'ตกลง',
        confirmButtonColor: '#4facfe'
    });
}

// Confirm Dialog
function showConfirm(title, message, callback) {
    Swal.fire({
        title: title,
        text: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'ยืนยัน',
        cancelButtonText: 'ยกเลิก',
        confirmButtonColor: '#4facfe',
        cancelButtonColor: '#d33'
    }).then((result) => {
        if (result.isConfirmed && callback) {
            callback();
        }
    });
}

// Format Phone Number
function formatPhone(input) {
    let value = input.value.replace(/\D/g, '');
    if (value.length > 10) {
        value = value.slice(0, 10);
    }
    input.value = value;
}

// Format ID Card
function formatIDCard(input) {
    let value = input.value.replace(/\D/g, '');
    if (value.length > 13) {
        value = value.slice(0, 13);
    }
    input.value = value;
}

// Check File Size
function checkFileSize(input, maxSize = 5) {
    const file = input.files[0];
    if (file) {
        const fileSize = file.size / 1024 / 1024; // MB
        if (fileSize > maxSize) {
            showError('ไฟล์ใหญ่เกินไป', `ไฟล์ต้องมีขนาดไม่เกิน ${maxSize} MB`);
            input.value = '';
            return false;
        }
    }
    return true;
}

// Preview Image
function previewImage(input, previewId) {
    const file = input.files[0];
    const preview = document.getElementById(previewId);
    
    if (file && preview) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
}

// Auto-save Form Data to LocalStorage
function autoSaveForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return;
    
    const inputs = form.querySelectorAll('input, textarea, select');
    inputs.forEach(input => {
        input.addEventListener('change', function() {
            const key = `${formId}_${this.name}`;
            localStorage.setItem(key, this.value);
        });
    });
}

// Load Saved Form Data
function loadSavedForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return;
    
    const inputs = form.querySelectorAll('input, textarea, select');
    inputs.forEach(input => {
        const key = `${formId}_${input.name}`;
        const savedValue = localStorage.getItem(key);
        if (savedValue) {
            input.value = savedValue;
        }
    });
}

// Clear Saved Form Data
function clearSavedForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return;
    
    const inputs = form.querySelectorAll('input, textarea, select');
    inputs.forEach(input => {
        const key = `${formId}_${input.name}`;
        localStorage.removeItem(key);
    });
}

// Print Element
function printElement(elementId) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    const printWindow = window.open('', '', 'height=600,width=800');
    printWindow.document.write('<html><head><title>Print</title>');
    printWindow.document.write('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">');
    printWindow.document.write('</head><body>');
    printWindow.document.write(element.innerHTML);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.print();
}

// Copy to Clipboard
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showSuccess('คัดลอกแล้ว', 'คัดลอกข้อมูลไปยังคลิปบอร์ดเรียบร้อย');
    }).catch(() => {
        showError('เกิดข้อผิดพลาด', 'ไม่สามารถคัดลอกข้อมูลได้');
    });
}

console.log('NCAdmit System Loaded ✓');