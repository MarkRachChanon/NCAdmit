<?php
/**
 * 403 Forbidden Page
 * แสดงเมื่อผู้ใช้พยายามเข้าถึงหน้าที่ไม่มีสิทธิ์
 */
?>

<div class="container-fluid py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-lg">
                <div class="card-body text-center py-5">
                    <!-- Icon -->
                    <div class="mb-4">
                        <i class="bi bi-shield-lock display-1 text-danger"></i>
                    </div>
                    
                    <!-- Title -->
                    <h1 class="display-4 fw-bold text-danger mb-3">403</h1>
                    <h3 class="mb-4">ไม่มีสิทธิ์เข้าถึง</h3>
                    
                    <!-- Description -->
                    <p class="text-muted mb-4">
                        คุณไม่มีสิทธิ์เข้าถึงหน้านี้<br>
                        กรุณาติดต่อผู้ดูแลระบบเพื่อขอสิทธิ์การใช้งาน
                    </p>
                    
                    <!-- User Info -->
                    <div class="alert alert-info mx-auto" style="max-width: 400px;">
                        <div class="d-flex align-items-center justify-content-center">
                            <i class="bi <?php echo get_role_icon($admin_role); ?> fs-3 me-3 text-<?php echo get_role_color($admin_role); ?>"></i>
                            <div class="text-start">
                                <strong>บัญชีของคุณ:</strong><br>
                                <small class="text-muted">
                                    <?php echo htmlspecialchars($admin_fullname); ?> 
                                    (<span class="badge bg-<?php echo get_role_color($admin_role); ?>">
                                        <?php echo ucfirst($admin_role); ?>
                                    </span>)
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Permission Info -->
                    <div class="alert alert-light border mx-auto mb-4" style="max-width: 500px;">
                        <h6 class="mb-2">
                            <i class="bi bi-info-circle text-primary me-2"></i>
                            สิทธิ์การใช้งานของคุณ:
                        </h6>
                        <small class="text-muted">
                            <?php echo get_role_description($admin_role); ?>
                        </small>
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
</style>