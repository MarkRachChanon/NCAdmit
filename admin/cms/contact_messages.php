<?php

/**
 * หน้าจัดการข้อความติดต่อ
 * NC-Admission - Nakhon Pathom College Admission System
 */

// ตรวจสอบสิทธิ์การเข้าถึง
if (!check_page_permission('contact_messages', $admin_role)) {
    header('Location: index.php?page=dashboard');
    exit();
}

// ดึงข้อมูลทั้งหมดโดยไม่มี pagination (สำหรับ realtime filter)
$sql = "SELECT * FROM contact_messages ORDER BY created_at DESC";
$result = $conn->query($sql);

// สถิติ
$stats_sql = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread,
    SUM(CASE WHEN is_read = 1 THEN 1 ELSE 0 END) as read_count
    FROM contact_messages";
$stats_result = $conn->query($stats_sql);
$stats = $stats_result->fetch_assoc();
?>

<!-- Page Header -->
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-2">
                        <i class="bi bi-envelope-open text-primary me-2"></i>
                        ข้อความติดต่อ
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="index.php?page=dashboard">หน้าแรก</a></li>
                            <li class="breadcrumb-item active">ข้อความติดต่อ</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- สถิติ -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card stat-card bg-gradient-primary text-white border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">ข้อความทั้งหมด</h6>
                            <h2 class="mb-0"><?php echo number_format($stats['total']); ?></h2>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-envelope-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card stat-card bg-gradient-warning text-white border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">ยังไม่ได้อ่าน</h6>
                            <h2 class="mb-0"><?php echo number_format($stats['unread']); ?></h2>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-envelope-exclamation-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card stat-card bg-gradient-success text-white border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">อ่านแล้ว</h6>
                            <h2 class="mb-0"><?php echo number_format($stats['read_count']); ?></h2>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-envelope-check-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-bold">
                        <i class="bi bi-funnel"></i> สถานะ
                    </label>
                    <select id="filterStatus" class="form-select">
                        <option value="">ทั้งหมด</option>
                        <option value="0">ยังไม่ได้อ่าน</option>
                        <option value="1">อ่านแล้ว</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">
                        <i class="bi bi-search"></i> ค้นหา
                    </label>
                    <input type="text"
                        id="searchInput"
                        class="form-control"
                        placeholder="ชื่อ, อีเมล, หัวข้อ หรือข้อความ...">
                </div>

                <div class="col-md-3">
                    <button type="button" class="btn btn-outline-secondary w-100" id="clearFilters">
                        <i class="bi bi-x-circle me-2"></i>ล้างตัวกรอง
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Messages List -->
    <div class="card shadow-sm">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-list-ul me-2"></i>รายการข้อความ
            </h5>
            <span class="badge bg-primary" id="messageCount"><?php echo number_format($stats['total']); ?> รายการ</span>
        </div>
        <div class="card-body p-0">
            <?php if ($result && $result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="50" class="text-center">สถานะ</th>
                                <th>ชื่อผู้ติดต่อ</th>
                                <th>อีเมล / เบอร์โทร</th>
                                <th>หัวข้อ</th>
                                <th width="150" class="text-center">วันที่ส่ง</th>
                                <th width="180" class="text-center">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody id="messageTableBody">
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr class="message-item <?php echo ($row['is_read'] == 0) ? 'table-warning' : ''; ?>"
                                    data-status="<?php echo $row['is_read']; ?>"
                                    data-name="<?php echo htmlspecialchars(strtolower($row['name'])); ?>"
                                    data-email="<?php echo htmlspecialchars(strtolower($row['email'])); ?>"
                                    data-phone="<?php echo htmlspecialchars(strtolower($row['phone'] ?? '')); ?>"
                                    data-subject="<?php echo htmlspecialchars(strtolower($row['subject'])); ?>"
                                    data-message="<?php echo htmlspecialchars(strtolower(strip_tags($row['message']))); ?>">
                                    <td class="text-center">
                                        <?php if ($row['is_read'] == 0): ?>
                                            <span class="badge bg-warning">
                                                <i class="bi bi-envelope-fill"></i>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-success">
                                                <i class="bi bi-envelope-open-fill"></i>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($row['name']); ?></strong>
                                    </td>
                                    <td>
                                        <div class="small mb-1">
                                            <a href="mailto:<?php echo htmlspecialchars($row['email']); ?>"
                                                class="text-decoration-none text-primary"
                                                title="ส่งอีเมลถึง <?php echo htmlspecialchars($row['name']); ?>">
                                                <i class="bi bi-envelope me-1"></i>
                                                <?php echo htmlspecialchars($row['email']); ?>
                                            </a>
                                        </div>
                                        <?php if (!empty($row['phone'])): ?>
                                            <div class="small">
                                                <a href="tel:<?php echo str_replace('-', '', $row['phone']); ?>"
                                                    class="text-decoration-none text-success"
                                                    title="โทรหา <?php echo htmlspecialchars($row['name']); ?>">
                                                    <i class="bi bi-telephone me-1"></i>
                                                    <?php echo htmlspecialchars($row['phone']); ?>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?php echo htmlspecialchars($row['subject']); ?></div>
                                        <div class="small text-muted">
                                            <?php echo mb_substr(strip_tags($row['message']), 0, 50, 'UTF-8'); ?>...
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <small><?php echo thai_date($row['created_at'], 'full'); ?></small>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <!-- ปุ่มส่งอีเมล -->
                                            <a href="mailto:<?php echo htmlspecialchars($row['email']); ?>"
                                                class="btn btn-sm btn-outline-primary"
                                                title="ส่งอีเมล"
                                                data-bs-toggle="tooltip">
                                                <i class="bi bi-envelope"></i>
                                            </a>

                                            <!-- ปุ่มโทรศัพท์ -->
                                            <?php if (!empty($row['phone'])): ?>
                                                <a href="tel:<?php echo str_replace('-', '', $row['phone']); ?>"
                                                    class="btn btn-sm btn-outline-success"
                                                    title="โทรศัพท์"
                                                    data-bs-toggle="tooltip">
                                                    <i class="bi bi-telephone"></i>
                                                </a>
                                            <?php endif; ?>

                                            <!-- ปุ่มดูรายละเอียด -->
                                            <button type="button"
                                                class="btn btn-sm btn-outline-info"
                                                onclick="viewMessage(<?php echo $row['id']; ?>)"
                                                title="ดูรายละเอียด"
                                                data-bs-toggle="tooltip">
                                                <i class="bi bi-eye"></i>
                                            </button>

                                            <!-- ปุ่มลบ -->
                                            <button type="button"
                                                class="btn btn-sm btn-outline-danger"
                                                onclick="deleteMessage(<?php echo $row['id']; ?>)"
                                                title="ลบ"
                                                data-bs-toggle="tooltip">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-envelope-x display-1 text-muted"></i>
                    <p class="text-muted mt-3">ไม่พบข้อความติดต่อ</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal: View Message -->
<div class="modal fade" id="viewMessageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-envelope-open me-2"></i>รายละเอียดข้อความ
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="messageContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-lift {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .hover-lift:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }

    .gallery-item {
        transition: opacity 0.3s ease;
    }

    .gallery-item.hidden {
        display: none;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });

    function viewMessage(id) {
        const modal = new bootstrap.Modal(document.getElementById('viewMessageModal'));
        modal.show();

        // Load message details
        fetch(`api/contact_message_detail.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const msg = data.data;
                    const phoneDisplay = msg.phone ? `
                    <a href="tel:${msg.phone.replace(/-/g, '')}" class="text-decoration-none text-success">
                        <i class="bi bi-telephone me-1"></i> ${msg.phone}
                    </a>
                ` : '-';

                    document.getElementById('messageContent').innerHTML = `
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong><i class="bi bi-person me-2"></i>ชื่อ:</strong> ${msg.name}
                        </div>
                        <div class="col-md-6">
                            <strong><i class="bi bi-calendar me-2"></i>วันที่ส่ง:</strong> ${msg.created_at_thai}
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong><i class="bi bi-envelope me-2"></i>อีเมล:</strong> 
                            <a href="mailto:${msg.email}" class="text-decoration-none text-primary">
                                ${msg.email}
                            </a>
                        </div>
                        <div class="col-md-6">
                            <strong><i class="bi bi-telephone me-2"></i>เบอร์โทร:</strong> ${phoneDisplay}
                        </div>
                    </div>
                    <div class="mb-3">
                        <strong><i class="bi bi-chat-left-text me-2"></i>หัวข้อ:</strong> ${msg.subject}
                    </div>
                    <div class="mb-3">
                        <strong><i class="bi bi-file-text me-2"></i>ข้อความ:</strong>
                        <div class="border rounded p-3 mt-2 bg-light">
                            ${msg.message.replace(/\n/g, '<br>')}
                        </div>
                    </div>
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>ติดต่อกลับ:</strong>
                        <div class="mt-2">
                            <a href="mailto:${msg.email}" class="btn btn-sm btn-primary me-2">
                                <i class="bi bi-envelope me-1"></i> ส่งอีเมล
                            </a>
                            ${msg.phone ? `
                                <a href="tel:${msg.phone.replace(/-/g, '')}" class="btn btn-sm btn-success">
                                    <i class="bi bi-telephone me-1"></i> โทรศัพท์
                                </a>
                            ` : ''}
                        </div>
                    </div>
                `;

                    // Reload page to update unread count
                    setTimeout(() => location.reload(), 10000);
                } else {
                    document.getElementById('messageContent').innerHTML = `
                    <div class="alert alert-danger">${data.message}</div>
                `;
                }
            })
            .catch(error => {
                document.getElementById('messageContent').innerHTML = `
                <div class="alert alert-danger">เกิดข้อผิดพลาด: ${error.message}</div>
            `;
            });
    }

    function deleteMessage(id) {
        Swal.fire({
            title: 'ยืนยันการลบ?',
            text: 'คุณต้องการลบข้อความนี้หรือไม่?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'ใช่, ลบเลย!',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('api/contact_message_delete.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            id: id
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'สำเร็จ!',
                                text: data.message,
                                timer: 1500
                            }).then(() => location.reload());
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'ผิดพลาด!',
                                text: data.message
                            });
                        }
                    });
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        console.log('Contact Messages Management Loaded');

        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // ==================== Realtime Search & Filter ====================
        const searchInput = document.getElementById('searchInput');
        const filterStatus = document.getElementById('filterStatus');
        const messageItems = document.querySelectorAll('.message-item');
        const messageCount = document.getElementById('messageCount');
        const tableBody = document.getElementById('messageTableBody');
        const noResultsMessage = document.getElementById('noResultsMessage');
        const clearFiltersBtn = document.getElementById('clearFilters');
        const clearFiltersFromNoResults = document.getElementById('clearFiltersFromNoResults');

        function filterMessages() {
            const searchTerm = searchInput.value.toLowerCase().trim();
            const statusValue = filterStatus.value;

            let visibleCount = 0;

            messageItems.forEach(item => {
                const name = item.dataset.name || '';
                const email = item.dataset.email || '';
                const phone = item.dataset.phone || '';
                const subject = item.dataset.subject || '';
                const message = item.dataset.message || '';
                const status = item.dataset.status;

                // Check search match
                const matchSearch = !searchTerm ||
                    name.includes(searchTerm) ||
                    email.includes(searchTerm) ||
                    phone.includes(searchTerm) ||
                    subject.includes(searchTerm) ||
                    message.includes(searchTerm);

                // Check status match
                const matchStatus = statusValue === '' || status === statusValue;

                // Show or hide item
                if (matchSearch && matchStatus) {
                    item.classList.remove('hidden');
                    item.classList.add('fade-in');
                    visibleCount++;
                } else {
                    item.classList.add('hidden');
                    item.classList.remove('fade-in');
                }
            });

            // Update count badge
            updateCountBadge(visibleCount);

            // Show/hide no results message
            if (visibleCount === 0 && messageItems.length > 0) {
                tableBody.style.display = 'none';
                noResultsMessage.style.display = 'block';
            } else {
                tableBody.style.display = '';
                noResultsMessage.style.display = 'none';
            }
        }

        function updateCountBadge(count) {
            if (messageCount) {
                messageCount.textContent = `${count.toLocaleString('th-TH')} รายการ`;

                // Add animation
                messageCount.style.transform = 'scale(1.1)';
                setTimeout(() => {
                    messageCount.style.transform = 'scale(1)';
                }, 200);
            }
        }

        function clearAllFilters() {
            searchInput.value = '';
            filterStatus.value = '';
            filterMessages();
            searchInput.focus();
        }

        // Event listeners
        if (searchInput) {
            searchInput.addEventListener('keyup', filterMessages);
            searchInput.addEventListener('search', filterMessages);
        }

        if (filterStatus) {
            filterStatus.addEventListener('change', filterMessages);
        }

        if (clearFiltersBtn) {
            clearFiltersBtn.addEventListener('click', clearAllFilters);
        }

        if (clearFiltersFromNoResults) {
            clearFiltersFromNoResults.addEventListener('click', clearAllFilters);
        }

        // Add smooth transition to count badge
        if (messageCount) {
            messageCount.style.transition = 'transform 0.2s ease';
        }
    });
</script>

<?php
if (isset($result)) {
    $result->close();
}
?>