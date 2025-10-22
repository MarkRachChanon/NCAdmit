<?php
$current_page = isset($_GET['page']) ? $_GET['page'] : 'home';
?>
<nav class="navbar navbar-expand-lg navbar-dark sticky-top shadow">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="index.php">
            <img src="assets/images/logo.png" alt="Logo" height="40" class="me-2">
            <div class="d-flex flex-column lh-sm">
                <span style="font-size: 1.2rem;">NCAdmit</span>
                <small style="font-size: 0.7rem; font-weight: 300;">ระบบรับสมัครนักเรียนออนไลน์</small>
            </div>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'home') ? 'active' : ''; ?>" 
                       href="index.php?page=home">
                        <i class="bi bi-house-door me-1"></i> หน้าแรก
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'about') ? 'active' : ''; ?>" 
                       href="index.php?page=about">
                        <i class="bi bi-info-circle me-1"></i> เกี่ยวกับเรา
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'news') ? 'active' : ''; ?>" 
                       href="index.php?page=news">
                        <i class="bi bi-newspaper me-1"></i> ข่าวสาร
                    </a>
                </li>
                
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?php echo (in_array($current_page, ['admission_info', 'quota_form', 'regular_form'])) ? 'active' : ''; ?>" 
                       href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-file-earmark-text me-1"></i> รับสมัคร
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="index.php?page=admission_info">
                                <i class="bi bi-info-circle me-2"></i> ข้อมูลการรับสมัคร
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="index.php?page=quota_form">
                                <i class="bi bi-pencil-square me-2"></i> สมัครรอบโควต้า
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="index.php?page=regular_form">
                                <i class="bi bi-pencil-square me-2"></i> สมัครรอบปกติ
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="index.php?page=check_status">
                                <i class="bi bi-search me-2"></i> ตรวจสอบสถานะ
                            </a>
                        </li>
                    </ul>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'gallery') ? 'active' : ''; ?>" 
                       href="index.php?page=gallery">
                        <i class="bi bi-images me-1"></i> แกลเลอรี่
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'contact') ? 'active' : ''; ?>" 
                       href="index.php?page=contact">
                        <i class="bi bi-envelope me-1"></i> ติดต่อเรา
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="admin/login.php">
                        <i class="bi bi-lock me-1"></i> Admin
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>