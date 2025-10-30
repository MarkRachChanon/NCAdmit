<?php
/**
 * Home Page
 */

// Get Latest News
$sql_news = "SELECT * FROM news WHERE is_published = 1 ORDER BY published_at DESC LIMIT 3";
$news_result = $conn->query($sql_news);
?>

<!-- Hero Section - Full Screen -->
<section class="hero-section">
    <div class="container hero-content">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div data-aos="fade-right">
                    <h1 class="hero-title text-white mb-3">
                        <?php echo $site_name; ?>
                    </h1>
                    <h2 class="hero-subtitle text-white mb-4">
                        ระบบรับสมัครนักเรียนออนไลน์ ปีการศึกษา <?php echo (date('Y') + 543 + 1); ?><br class="d-none d-md-inline"> 
                        วิทยาลัยอาชีวศึกษานครปฐม
                    </h2>
                    <p class="hero-text text-white mb-5">
                        สะดวก รวดเร็ว ปลอดภัย<br class="d-none d-md-inline"> 
                        ตลอด 24 ชั่วโมง
                    </p>
                    <div class="hero-buttons d-flex flex-column flex-md-row gap-3">
                        <a href="index.php?page=quota_form" class="btn btn-light btn-lg px-4 px-md-5 shadow">
                            <i class="bi bi-pencil-square me-2"></i> สมัครรอบโควตา
                        </a>
                        <a href="index.php?page=regular_form" class="btn btn-outline-light btn-lg px-4 px-md-5">
                            <i class="bi bi-pencil-square me-2"></i> สมัครรอบปกติ
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6 text-center" data-aos="fade-left" data-aos-delay="200">
                <div class="hero-image">
                    <img src="https://cdn-icons-png.flaticon.com/512/3976/3976625.png" alt="Education" class="img-fluid" style="filter: drop-shadow(0 20px 40px rgba(0,0,0,0.3));">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Quick Info Cards -->
<section id="quick-info" class="py-5 bg-light">
    <div class="container">
        <div class="row mb-5 text-center">
            <div class="col-12" data-aos="fade-up">
                <h2 class="section-title text-gradient">บริการของเรา</h2>
                <p class="section-subtitle">เลือกบริการที่คุณต้องการ</p>
            </div>
        </div>

        <div class="row g-4">
            <!-- Card 1: รับสมัคร -->
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card h-100">
                    <div class="card-body text-center p-4">
                        <div class="icon-circle bg-gradient-primary text-white mb-4">
                            <i class="bi bi-file-earmark-text display-6"></i>
                        </div>
                        <h5 class="card-title fw-bold mb-3">รับสมัครออนไลน์</h5>
                        <p class="card-text text-muted mb-4">
                            สมัครเรียนออนไลน์ได้ตลอด 24 ชั่วโมง<br>
                            สะดวก รวดเร็ว ปลอดภัย
                        </p>
                        <a href="index.php?page=admission_info" class="btn btn-gradient">
                            ดูรายละเอียด <i class="bi bi-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Card 2: ตรวจสอบสถานะ -->
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="card h-100">
                    <div class="card-body text-center p-4">
                        <div class="icon-circle bg-gradient-secondary text-white mb-4">
                            <i class="bi bi-search display-6"></i>
                        </div>
                        <h5 class="card-title fw-bold mb-3">ตรวจสอบสถานะ</h5>
                        <p class="card-text text-muted mb-4">
                            ตรวจสอบผลการสมัครได้ทันที<br>
                            พร้อมข้อมูลรายละเอียด
                        </p>
                        <a href="index.php?page=check_status" class="btn btn-gradient">
                            ตรวจสอบเลย <i class="bi bi-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Card 3: ติดต่อสอบถาม -->
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                <div class="card h-100">
                    <div class="card-body text-center p-4">
                        <div class="icon-circle bg-gradient-blue text-white mb-4">
                            <i class="bi bi-telephone display-6"></i>
                        </div>
                        <h5 class="card-title fw-bold mb-3">ติดต่อสอบถาม</h5>
                        <p class="card-text text-muted mb-4">
                            สอบถามข้อมูลเพิ่มเติมได้ที่นี่<br>
                            ทีมงานพร้อมให้บริการ
                        </p>
                        <a href="index.php?page=contact" class="btn btn-gradient">
                            ติดต่อเรา <i class="bi bi-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</section>

<!-- Latest News Section -->
<section class="py-5">
    <div class="container">
        <div class="row mb-5 text-center">
            <div class="col-12" data-aos="fade-up">
                <h2 class="section-title text-gradient">ข่าวสารล่าสุด</h2>
                <p class="section-subtitle">ติดตามข่าวสารและกิจกรรมของเรา</p>
            </div>
        </div>
        
        <div class="row g-4">
            <?php if ($news_result && $news_result->num_rows > 0): ?>
                <?php $delay = 100; ?>
                <?php while ($news = $news_result->fetch_assoc()): ?>
                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                        <div class="card h-100">
                            <?php if ($news['featured_image']): ?>
                                <img src="<?php echo $news['featured_image']; ?>" 
                                     class="card-img-top" 
                                     alt="<?php echo htmlspecialchars($news['title']); ?>"
                                     style="height: 200px; object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-gradient-primary text-white d-flex align-items-center justify-content-center" 
                                     style="height: 200px;">
                                    <i class="bi bi-image display-1"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="card-body d-flex flex-column">
                                <div class="text-muted small mb-2">
                                    <i class="bi bi-calendar3 me-1"></i>
                                    <?php echo thai_date($news['published_at'], 'short'); ?>
                                </div>
                                <h5 class="card-title fw-bold">
                                    <?php echo htmlspecialchars($news['title']); ?>
                                </h5>
                                <p class="card-text text-muted flex-grow-1">
                                    <?php echo mb_substr(strip_tags($news['excerpt']), 0, 100, 'UTF-8'); ?>...
                                </p>
                                <a href="index.php?page=news_detail&id=<?php echo $news['id']; ?>" 
                                   class="btn btn-gradient btn-sm mt-3">
                                    อ่านเพิ่มเติม <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php $delay += 100; ?>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5" data-aos="fade-up">
                    <i class="bi bi-newspaper display-1 text-muted"></i>
                    <p class="text-muted mt-3">ยังไม่มีข่าวสาร</p>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if ($news_result && $news_result->num_rows > 0): ?>
        <div class="text-center mt-5" data-aos="fade-up" data-aos-delay="400">
            <a href="index.php?page=news" class="btn btn-gradient btn-lg px-5">
                ดูข่าวสารทั้งหมด <i class="bi bi-arrow-right ms-2"></i>
            </a>
        </div>
        <?php endif; ?>
    </div>
</section>