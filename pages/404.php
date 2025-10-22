<!-- 404 Error Page -->
<section class="error-page d-flex align-items-center justify-content-center min-vh-100 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <!-- Animated 404 -->
                <div class="error-animation mb-5" data-aos="zoom-in">
                    <h1 class="error-code text-gradient mb-0" style="font-size: 10rem; font-weight: 900; line-height: 1;">
                        404
                    </h1>
                    <div class="error-emoji">
                        <i class="bi bi-emoji-frown display-1 text-muted"></i>
                    </div>
                </div>

                <!-- Error Message -->
                <div data-aos="fade-up" data-aos-delay="100">
                    <h2 class="fw-bold mb-3">ไม่พบหน้าที่คุณต้องการ</h2>
                    <p class="lead text-muted mb-4">
                        ขออภัย! หน้าที่คุณกำลังมองหาอาจถูกย้าย ลบ หรือไม่เคยมีอยู่จริง
                    </p>
                </div>

                <!-- Error Code Display -->
                <div class="alert alert-light border shadow-sm d-inline-block px-5 py-3 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <strong>Error Code:</strong> 404 - Page Not Found
                </div>

                <!-- Action Buttons -->
                <div class="d-flex flex-column flex-md-row gap-3 justify-content-center mb-5" data-aos="fade-up" data-aos-delay="300">
                    <a href="index.php?page=home" class="btn btn-gradient btn-lg px-5">
                        <i class="bi bi-house-door-fill me-2"></i> กลับหน้าแรก
                    </a>
                    <button onclick="history.back()" class="btn btn-outline-primary btn-lg px-5">
                        <i class="bi bi-arrow-left me-2"></i> ย้อนกลับ
                    </button>
                </div>

                <!-- Quick Links -->
                <div class="card border-0 shadow-sm" data-aos="fade-up" data-aos-delay="400">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4">
                            <i class="bi bi-signpost-2-fill text-primary me-2"></i>
                            หน้าที่คุณอาจกำลังมองหา
                        </h5>
                        <div class="row g-3">
                            <div class="col-md-3 col-6">
                                <a href="index.php?page=admission_info" class="text-decoration-none">
                                    <div class="quick-link-card p-3 rounded-3 h-100 text-center">
                                        <i class="bi bi-info-circle-fill text-primary display-6 mb-2"></i>
                                        <div class="small fw-bold">ข้อมูลการรับสมัคร</div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-3 col-6">
                                <a href="index.php?page=about" class="text-decoration-none">
                                    <div class="quick-link-card p-3 rounded-3 h-100 text-center">
                                        <i class="bi bi-building-fill text-success display-6 mb-2"></i>
                                        <div class="small fw-bold">เกี่ยวกับเรา</div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-3 col-6">
                                <a href="index.php?page=news" class="text-decoration-none">
                                    <div class="quick-link-card p-3 rounded-3 h-100 text-center">
                                        <i class="bi bi-newspaper text-info display-6 mb-2"></i>
                                        <div class="small fw-bold">ข่าวสาร</div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-3 col-6">
                                <a href="index.php?page=contact" class="text-decoration-none">
                                    <div class="quick-link-card p-3 rounded-3 h-100 text-center">
                                        <i class="bi bi-envelope-fill text-warning display-6 mb-2"></i>
                                        <div class="small fw-bold">ติดต่อเรา</div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Search Box -->
                <div class="mt-4" data-aos="fade-up" data-aos-delay="500">
                    <p class="text-muted mb-3">หรือลองค้นหาสิ่งที่คุณต้องการ</p>
                    <form action="index.php" method="GET" class="search-form">
                        <input type="hidden" name="page" value="news">
                        <div class="input-group input-group-lg shadow-sm">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-search text-primary"></i>
                            </span>
                            <input type="text" 
                                   name="search" 
                                   class="form-control border-start-0 border-end-0 ps-0" 
                                   placeholder="ค้นหาข่าวสาร...">
                            <button type="submit" class="btn btn-primary px-4">
                                ค้นหา
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Help Text -->
                <div class="mt-5 pt-4 border-top" data-aos="fade-up" data-aos-delay="600">
                    <p class="text-muted mb-2">
                        <i class="bi bi-lightbulb-fill text-warning me-2"></i>
                        หากคุณคิดว่านี่คือข้อผิดพลาด กรุณา
                        <a href="index.php?page=contact" class="text-decoration-none fw-bold">แจ้งให้เราทราบ</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Error Page Styles */
.error-page {
    position: relative;
    overflow: hidden;
}

.error-page::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(79, 172, 254, 0.05) 0%, transparent 70%);
    animation: rotate 20s linear infinite;
}

@keyframes rotate {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

.error-code {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    text-shadow: 0 10px 30px rgba(79, 172, 254, 0.3);
    animation: float 3s ease-in-out infinite;
}

@keyframes float {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-20px);
    }
}

.error-emoji {
    animation: swing 2s ease-in-out infinite;
}

@keyframes swing {
    0%, 100% {
        transform: rotate(-5deg);
    }
    50% {
        transform: rotate(5deg);
    }
}

/* Quick Link Cards */
.quick-link-card {
    background: #f8f9fa;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.quick-link-card:hover {
    background: white;
    border-color: #4facfe;
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
}

.quick-link-card i {
    transition: transform 0.3s ease;
}

.quick-link-card:hover i {
    transform: scale(1.2);
}

/* Search Form */
.search-form .input-group {
    max-width: 600px;
    margin: 0 auto;
}

/* Responsive */
@media (max-width: 768px) {
    .error-code {
        font-size: 6rem !important;
    }
    
    .error-page {
        padding: 2rem 0;
    }
}
</style>

<script>
// Add particles effect (optional)
document.addEventListener('DOMContentLoaded', function() {
    // Create floating particles
    const errorPage = document.querySelector('.error-page');
    
    for (let i = 0; i < 20; i++) {
        const particle = document.createElement('div');
        particle.style.cssText = `
            position: absolute;
            width: ${Math.random() * 10 + 5}px;
            height: ${Math.random() * 10 + 5}px;
            background: rgba(79, 172, 254, 0.3);
            border-radius: 50%;
            left: ${Math.random() * 100}%;
            top: ${Math.random() * 100}%;
            animation: float ${Math.random() * 3 + 2}s ease-in-out infinite;
            animation-delay: ${Math.random() * 2}s;
        `;
        errorPage.appendChild(particle);
    }
});

// Easter egg - Konami code
let konamiCode = [];
const pattern = ['ArrowUp', 'ArrowUp', 'ArrowDown', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'ArrowLeft', 'ArrowRight', 'b', 'a'];

document.addEventListener('keydown', function(e) {
    konamiCode.push(e.key);
    konamiCode = konamiCode.slice(-10);
    
    if (konamiCode.join(',') === pattern.join(',')) {
        document.querySelector('.error-emoji i').classList.add('bi-emoji-sunglasses');
        document.querySelector('.error-emoji i').classList.remove('bi-emoji-frown');
        showSuccess('🎉 Easter Egg!', 'คุณพบรหัสลับแล้ว!');
    }
});
</script>