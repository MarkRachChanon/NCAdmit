/**
 * NC-Admission - Admin Panel JavaScript
 */

// Initialize DataTables
$(document).ready(function() {
    // Default DataTable Configuration
    if ($.fn.DataTable) {
        $('.data-table').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/th.json'
            },
            responsive: true,
            pageLength: 25,
            order: [[0, 'desc']],
            columnDefs: [
                { orderable: false, targets: 'no-sort' }
            ]
        });
    }
});

// Sidebar Toggle for Mobile
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('adminSidebar');
    const content = document.querySelector('.admin-content');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            sidebar.classList.toggle('show');
            if (content) {
                content.classList.toggle('expanded');
            }
        });
    }
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 991) {
            if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                sidebar.classList.add('collapsed');
                sidebar.classList.remove('show');
            }
        }
    });
});

// Confirm Delete
function confirmDelete(message = 'คุณแน่ใจหรือไม่ว่าต้องการลบข้อมูลนี้?') {
    return Swal.fire({
        title: 'ยืนยันการลบ',
        text: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'ลบเลย',
        cancelButtonText: 'ยกเลิก'
    });
}

// Show Loading
function showLoading(message = 'กำลังประมวลผล...') {
    Swal.fire({
        title: message,
        html: '<div class="spinner-admin"></div>',
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
        confirmButtonColor: '#28a745'
    });
}

// Error Alert
function showError(title, message) {
    Swal.fire({
        icon: 'error',
        title: title,
        text: message,
        confirmButtonText: 'ตกลง',
        confirmButtonColor: '#dc3545'
    });
}

// Warning Alert
function showWarning(title, message) {
    Swal.fire({
        icon: 'warning',
        title: title,
        text: message,
        confirmButtonText: 'ตกลง',
        confirmButtonColor: '#ffc107'
    });
}

// Confirm Action
async function confirmAction(title, message, confirmText = 'ยืนยัน') {
    const result = await Swal.fire({
        title: title,
        text: message,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3498db',
        cancelButtonColor: '#6c757d',
        confirmButtonText: confirmText,
        cancelButtonText: 'ยกเลิก'
    });
    return result.isConfirmed;
}

// Update Status
async function updateStatus(id, status, type = 'quota') {
    const statusText = {
        'approved': 'อนุมัติ',
        'rejected': 'ไม่อนุมัติ',
        'cancelled': 'ยกเลิก'
    };
    
    const confirmed = await confirmAction(
        'ยืนยันการเปลี่ยนสถานะ',
        `คุณต้องการเปลี่ยนสถานะเป็น "${statusText[status]}" ใช่หรือไม่?`,
        'ยืนยัน'
    );
    
    if (confirmed) {
        showLoading('กำลังอัพเดทสถานะ...');
        
        try {
            const response = await fetch('api/update_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id: id,
                    status: status,
                    type: type
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                showSuccess('สำเร็จ', 'อัพเดทสถานะเรียบร้อยแล้ว');
                setTimeout(() => location.reload(), 1500);
            } else {
                showError('เกิดข้อผิดพลาด', data.message || 'ไม่สามารถอัพเดทสถานะได้');
            }
        } catch (error) {
            showError('เกิดข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์');
        }
    }
}

// Export to Excel
function exportToExcel(tableId, filename = 'export') {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    const wb = XLSX.utils.table_to_book(table);
    XLSX.writeFile(wb, filename + '.xlsx');
}

// Print Element
function printElement(elementId) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    const printWindow = window.open('', '', 'height=600,width=800');
    printWindow.document.write('<html><head><title>Print</title>');
    printWindow.document.write('<link href="../assets/css/bootstrap.min.css" rel="stylesheet">');
    printWindow.document.write('<style>@media print { body { margin: 20px; } }</style>');
    printWindow.document.write('</head><body>');
    printWindow.document.write(element.innerHTML);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    
    setTimeout(() => {
        printWindow.print();
        printWindow.close();
    }, 250);
}

// Format Number
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// Format Date to Thai
function formatThaiDate(dateString) {
    const date = new Date(dateString);
    const thaiMonths = [
        'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
        'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'
    ];
    
    const day = date.getDate();
    const month = thaiMonths[date.getMonth()];
    const year = date.getFullYear() + 543;
    
    return `${day} ${month} ${year}`;
}

// Copy to Clipboard
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        Swal.fire({
            icon: 'success',
            title: 'คัดลอกแล้ว',
            text: 'คัดลอกข้อมูลไปยังคลิปบอร์ดเรียบร้อย',
            timer: 1500,
            showConfirmButton: false
        });
    }).catch(() => {
        showError('เกิดข้อผิดพลาด', 'ไม่สามารถคัดลอกข้อมูลได้');
    });
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

// Check File Size
function checkFileSize(input, maxSizeMB = 5) {
    const file = input.files[0];
    if (file) {
        const fileSize = file.size / 1024 / 1024;
        if (fileSize > maxSizeMB) {
            showError('ไฟล์ใหญ่เกินไป', `ไฟล์ต้องมีขนาดไม่เกิน ${maxSizeMB} MB`);
            input.value = '';
            return false;
        }
    }
    return true;
}

// Auto-dismiss Alerts
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert-dismissible:not(.alert-permanent)');
    alerts.forEach(alert => {
        const bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
    });
}, 5000);

// Tooltips
const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

// Popovers
const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));

// Session Timeout Warning
let sessionTimeout;
let sessionWarningTimeout;

function resetSessionTimer() {
    clearTimeout(sessionTimeout);
    clearTimeout(sessionWarningTimeout);
    
    // Warning at 25 minutes
    sessionWarningTimeout = setTimeout(() => {
        Swal.fire({
            title: 'เซสชันใกล้หมดอายุ',
            text: 'เซสชันของคุณจะหมดอายุในอีก 5 นาที',
            icon: 'warning',
            confirmButtonText: 'ต่ออายุเซสชัน',
            confirmButtonColor: '#3498db'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('api/refresh_session.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            resetSessionTimer();
                        }
                    });
            }
        });
    }, 25 * 60 * 1000); // 25 minutes
    
    // Logout at 30 minutes
    sessionTimeout = setTimeout(() => {
        Swal.fire({
            title: 'เซสชันหมดอายุ',
            text: 'กรุณาเข้าสู่ระบบใหม่',
            icon: 'error',
            confirmButtonText: 'ตกลง',
            allowOutsideClick: false
        }).then(() => {
            window.location.href = 'logout.php';
        });
    }, 30 * 60 * 1000); // 30 minutes
}

// Reset timer on user activity
document.addEventListener('mousemove', resetSessionTimer);
document.addEventListener('keypress', resetSessionTimer);
document.addEventListener('click', resetSessionTimer);

// Initialize session timer
resetSessionTimer();

// Form Validation
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        showError('ข้อมูลไม่ครบถ้วน', 'กรุณากรอกข้อมูลให้ครบถ้วน');
        return false;
    }
    
    return true;
}

// Bulk Actions
function selectAllCheckboxes(selectAllCheckbox) {
    const checkboxes = document.querySelectorAll('.row-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });
    updateBulkActions();
}

function updateBulkActions() {
    const checkboxes = document.querySelectorAll('.row-checkbox:checked');
    const bulkActionsBtn = document.getElementById('bulkActionsBtn');
    
    if (bulkActionsBtn) {
        if (checkboxes.length > 0) {
            bulkActionsBtn.disabled = false;
            bulkActionsBtn.textContent = `ดำเนินการ (${checkboxes.length})`;
        } else {
            bulkActionsBtn.disabled = true;
            bulkActionsBtn.textContent = 'เลือกรายการ';
        }
    }
}

// Get Selected IDs
function getSelectedIds() {
    const checkboxes = document.querySelectorAll('.row-checkbox:checked');
    return Array.from(checkboxes).map(cb => cb.value);
}

// Bulk Delete
async function bulkDelete(type = 'quota') {
    const ids = getSelectedIds();
    
    if (ids.length === 0) {
        showWarning('ไม่มีรายการที่เลือก', 'กรุณาเลือกรายการที่ต้องการลบ');
        return;
    }
    
    const confirmed = await confirmAction(
        'ยืนยันการลบ',
        `คุณต้องการลบข้อมูล ${ids.length} รายการใช่หรือไม่?`,
        'ลบทั้งหมด'
    );
    
    if (confirmed) {
        showLoading('กำลังลบข้อมูล...');
        
        try {
            const response = await fetch('api/bulk_delete.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    ids: ids,
                    type: type
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                showSuccess('สำเร็จ', `ลบข้อมูล ${ids.length} รายการเรียบร้อยแล้ว`);
                setTimeout(() => location.reload(), 1500);
            } else {
                showError('เกิดข้อผิดพลาด', data.message || 'ไม่สามารถลบข้อมูลได้');
            }
        } catch (error) {
            showError('เกิดข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์');
        }
    }
}

// AJAX Form Submit
async function submitFormAjax(formId, successCallback) {
    const form = document.getElementById(formId);
    if (!form || !validateForm(formId)) return;
    
    const formData = new FormData(form);
    showLoading('กำลังบันทึกข้อมูล...');
    
    try {
        const response = await fetch(form.action, {
            method: form.method,
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showSuccess('สำเร็จ', data.message || 'บันทึกข้อมูลเรียบร้อยแล้ว');
            if (successCallback) {
                successCallback(data);
            }
        } else {
            showError('เกิดข้อผิดพลาด', data.message || 'ไม่สามารถบันทึกข้อมูลได้');
        }
    } catch (error) {
        showError('เกิดข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์');
    }
}

console.log('NC-Admission Admin System Loaded ✓');