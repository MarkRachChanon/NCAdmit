<?php
/**
 * 404 Not Found Page
 * แสดงเมื่อไม่พบหน้าที่ต้องการ
 */
?>

<div class="container-fluid py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-lg">
                <div class="card-body text-center py-5">
                    <!-- Icon -->
                    <div class="mb-4">
                        <i class="bi bi-exclamation-triangle display-1 text-warning"></i>
                    </div>
                    
                    <!-- Title -->
                    <h1 class="display-4 fw-bold text-warning mb-3">404</h1>
                    <h3 class="mb-4">ไม่พบหน้าที่ค้นหา</h3>
                    
                    <!-- Description -->
                    <p class="text-muted mb-4">
                        ขออภัย ไม่พบหน้าที่คุณกำลังค้นหา<br>
                        หน้าที่คุณต้องการอาจถูกย้าย ลบ หรือไม่มีอยู่จริง
                    </p>
                    
                    <!-- Search Help -->
                    <div class="alert alert-light border mx-auto mb-4" style="max-width: 500px;">
                        <h6 class="mb-3">
                            <i class="bi bi-compass text-primary me-2"></i>
                            คุณกำลังมองหาอะไร?
                        </h6>
                        <div class="list-group list-group-flush">
                            <?php if (can_show_menu('quota_list', $admin_role)): ?>
                            <a href="index.php?page=quota_list" class="list-group-item list-group-item-action">
                                <i class="bi bi-person-lines-fill text-success me-2"></i>
                                รายชื่อรอบโควตา
                            </a>
                            <?php endif; ?>
                            
                            <?php if (can_show_menu('regular_list', $admin_role)): ?>
                            <a href="index.php?page=regular_list" class="list-group-item list-group-item-action">
                                <i class="bi bi-people-fill text-warning me-2"></i>
                                รายชื่อรอบปกติ
                            </a>
                            <?php endif; ?>
                            
                            <?php if (can_show_menu('news_manage', $admin_role)): ?>
                            <a href="index.php?page=news_manage" class="list-group-item list-group-item-action">
                                <i class="bi bi-newspaper text-info me-2"></i>
                                จัดการข่าว
                            </a>
                            <?php endif; ?>
                            
                            <?php if (can_show_menu('system_settings', $admin_role)): ?>
                            <a href="index.php?page=system_settings" class="list-group-item list-group-item-action">
                                <i class="bi bi-gear text-primary me-2"></i>
                                ตั้งค่าระบบ
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="d-flex gap-2 justify-content-center">
                        <a href="index.php?page=dashboard" class="btn btn-primary">
                            <i class="bi bi-house-door me-2"></i>
                            กลับหน้าหลัก
                        </a>
                        <button onclick="history.back()" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>
                            ย้อนกลับ
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Additional Help -->
            <div class="text-center mt-4">
                <small class="text-muted">
                    <i class="bi bi-question-circle me-1"></i>
                    หากคุณคิดว่านี่เป็นข้อผิดพลาด กรุณาติดต่อ 
                    <a href="mailto:<?php echo get_setting('contact_email', 'admin@nc.ac.th'); ?>">
                        ผู้ดูแลระบบ
                    </a>
                </small>
            </div>
        </div>
    </div>
</div>

<style>
.display-1 {
    font-size: 8rem;
}

.list-group-item {
    border-left: 3px solid transparent;
    transition: all 0.3s ease;
}

.list-group-item:hover {
    border-left-color: var(--bs-primary);
    background-color: var(--bs-light);
    padding-left: 1.5rem;
}
</style>