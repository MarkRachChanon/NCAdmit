<?php
/**
 * News Detail Page - รายละเอียดข่าว
 */

// Get News ID
$news_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($news_id <= 0) {
    header("Location: index.php?page=news");
    exit;
}

// Get News Detail
$sql = "SELECT n.*, c.name as category_name, c.color as category_color
        FROM news n 
        LEFT JOIN categories c ON n.category_id = c.id 
        WHERE n.id = ? AND n.is_published = 1 
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $news_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: index.php?page=news");
    exit;
}

$news = $result->fetch_assoc();

// Update View Count
$update_sql = "UPDATE news SET views = views + 1 WHERE id = ?";
$update_stmt = $conn->prepare($update_sql);
$update_stmt->bind_param('i', $news_id);
$update_stmt->execute();

// Get Related News
$related_sql = "SELECT * FROM news 
                WHERE category_id = ? AND id != ? AND is_published = 1 
                ORDER BY created_at DESC 
                LIMIT 3";
$related_stmt = $conn->prepare($related_sql);
$related_stmt->bind_param('ii', $news['category_id'], $news_id);
$related_stmt->execute();
$related_result = $related_stmt->get_result();

// Get Latest News
$latest_sql = "SELECT * FROM news 
               WHERE is_published = 1 AND id != ? 
               ORDER BY created_at DESC 
               LIMIT 5";
$latest_stmt = $conn->prepare($latest_sql);
$latest_stmt->bind_param('i', $news_id);
$latest_stmt->execute();
$latest_result = $latest_stmt->get_result();
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="bg-light py-3">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="index.php">หน้าแรก</a></li>
            <li class="breadcrumb-item"><a href="index.php?page=news">ข่าวสาร</a></li>
            <?php if ($news['category_name']): ?>
                <li class="breadcrumb-item">
                    <a href="index.php?page=news&cat=<?php echo $news['category_id']; ?>">
                        <?php echo htmlspecialchars($news['category_name']); ?>
                    </a>
                </li>
            <?php endif; ?>
            <li class="breadcrumb-item active" aria-current="page">
                <?php echo mb_substr(htmlspecialchars($news['title']), 0, 50, 'UTF-8'); ?>...
            </li>
        </ol>
    </div>
</nav>

<!-- News Content -->
<article class="py-5">
    <div class="container">
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8 mb-4 mb-lg-0">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4 p-md-5">
                        <!-- Category & Date -->
                        <div class="d-flex flex-wrap align-items-center gap-3 mb-4">
                            <?php if ($news['category_name']): ?>
                                <span class="badge px-3 py-2" 
                                      style="background-color: <?php echo $news['category_color'] ?? '#0d6efd'; ?>">
                                    <?php echo htmlspecialchars($news['category_name']); ?>
                                </span>
                            <?php endif; ?>
                            
                            <?php if ($news['is_pinned']): ?>
                                <span class="badge bg-danger px-3 py-2">
                                    <i class="bi bi-pin-angle-fill me-1"></i> ปักหมุด
                                </span>
                            <?php endif; ?>
                            
                            <span class="text-muted">
                                <i class="bi bi-calendar3 me-2"></i>
                                <?php echo thai_date($news['created_at']); ?>
                            </span>
                            
                            <span class="text-muted">
                                <i class="bi bi-eye me-2"></i>
                                <?php echo number_format($news['views']); ?> ครั้ง
                            </span>
                        </div>

                        <!-- Title -->
                        <h1 class="display-6 fw-bold mb-4" data-aos="fade-up">
                            <?php echo htmlspecialchars($news['title']); ?>
                        </h1>

                        <!-- Featured Image -->
                        <?php if ($news['featured_image']): ?>
                            <div class="mb-4" data-aos="fade-up" data-aos-delay="100">
                                <img src="<?php echo htmlspecialchars($news['featured_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($news['title']); ?>"
                                     class="img-fluid rounded-3 shadow-sm w-100"
                                     style="max-height: 500px; object-fit: cover;">
                            </div>
                        <?php endif; ?>

                        <!-- Excerpt -->
                        <?php if ($news['excerpt']): ?>
                            <div class="alert alert-info border-0 mb-4" data-aos="fade-up" data-aos-delay="200">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>สรุป:</strong> <?php echo htmlspecialchars($news['excerpt']); ?>
                            </div>
                        <?php endif; ?>

                        <!-- Content -->
                        <div class="news-content" data-aos="fade-up" data-aos-delay="300">
                            <?php echo $news['content']; ?>
                        </div>

                        <!-- Share Buttons -->
                        <div class="mt-5 pt-4 border-top">
                            <h6 class="fw-bold mb-3">
                                <i class="bi bi-share me-2"></i> แชร์ข่าวนี้
                            </h6>
                            <div class="d-flex gap-2 flex-wrap">
                                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode((isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" 
                                   target="_blank" 
                                   class="btn btn-primary">
                                    <i class="bi bi-facebook me-2"></i> Facebook
                                </a>
                                <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode((isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>&text=<?php echo urlencode($news['title']); ?>" 
                                   target="_blank" 
                                   class="btn btn-info text-white">
                                    <i class="bi bi-twitter me-2"></i> Twitter
                                </a>
                                <a href="https://social-plugins.line.me/lineit/share?url=<?php echo urlencode((isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" 
                                   target="_blank" 
                                   class="btn btn-success">
                                    <i class="bi bi-line me-2"></i> Line
                                </a>
                                <button onclick="copyToClipboard('<?php echo addslashes((isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>')" 
                                        class="btn btn-secondary">
                                    <i class="bi bi-link-45deg me-2"></i> คัดลอกลิงก์
                                </button>
                            </div>
                        </div>

                        <!-- Navigation -->
                        <div class="mt-4 pt-4 border-top">
                            <div class="d-flex justify-content-between flex-wrap gap-3">
                                <a href="index.php?page=news" class="btn btn-outline-primary">
                                    <i class="bi bi-arrow-left me-2"></i> กลับหน้าข่าวสาร
                                </a>
                                <?php if ($news['category_id']): ?>
                                    <a href="index.php?page=news&cat=<?php echo $news['category_id']; ?>" 
                                       class="btn btn-outline-primary">
                                        ดูข่าวใน <?php echo htmlspecialchars($news['category_name']); ?>
                                        <i class="bi bi-arrow-right ms-2"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Related News -->
                <?php if ($related_result->num_rows > 0): ?>
                    <div class="card border-0 shadow-sm mb-4" data-aos="fade-left">
                        <div class="card-header bg-gradient-primary text-white py-3">
                            <h5 class="mb-0 fw-bold">
                                <i class="bi bi-newspaper me-2"></i>
                                ข่าวที่เกี่ยวข้อง
                            </h5>
                        </div>
                        <div class="card-body p-3">
                            <?php while ($related = $related_result->fetch_assoc()): ?>
                                <a href="index.php?page=news_detail&id=<?php echo $related['id']; ?>" 
                                   class="text-decoration-none">
                                    <div class="d-flex gap-3 mb-3 pb-3 border-bottom">
                                        <?php if ($related['featured_image']): ?>
                                            <img src="<?php echo htmlspecialchars($related['featured_image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($related['title']); ?>"
                                                 class="rounded"
                                                 style="width: 80px; height: 80px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                                 style="width: 80px; height: 80px;">
                                                <i class="bi bi-image text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-2 text-dark">
                                                <?php echo htmlspecialchars($related['title']); ?>
                                            </h6>
                                            <small class="text-muted">
                                                <i class="bi bi-calendar3 me-1"></i>
                                                <?php echo thai_date($related['created_at'], 'short'); ?>
                                            </small>
                                        </div>
                                    </div>
                                </a>
                            <?php endwhile; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Latest News -->
                <?php if ($latest_result->num_rows > 0): ?>
                    <div class="card border-0 shadow-sm mb-4" data-aos="fade-left" data-aos-delay="100">
                        <div class="card-header bg-gradient-secondary text-white py-3">
                            <h5 class="mb-0 fw-bold">
                                <i class="bi bi-clock-history me-2"></i>
                                ข่าวล่าสุด
                            </h5>
                        </div>
                        <div class="card-body p-3">
                            <?php while ($latest = $latest_result->fetch_assoc()): ?>
                                <a href="index.php?page=news_detail&id=<?php echo $latest['id']; ?>" 
                                   class="text-decoration-none d-block mb-3 pb-3 border-bottom">
                                    <h6 class="mb-2 text-dark">
                                        <?php echo htmlspecialchars($latest['title']); ?>
                                    </h6>
                                    <small class="text-muted">
                                        <i class="bi bi-calendar3 me-1"></i>
                                        <?php echo thai_date($latest['created_at'], 'short'); ?>
                                    </small>
                                </a>
                            <?php endwhile; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- CTA Card -->
                <div class="card border-0 bg-gradient-primary text-white shadow-sm" data-aos="fade-left" data-aos-delay="200">
                    <div class="card-body p-4 text-center">
                        <i class="bi bi-megaphone display-1 mb-3"></i>
                        <h5 class="fw-bold mb-3">ติดตามข่าวสารจากเรา</h5>
                        <p class="mb-4">
                            อย่าพลาดข่าวสารและกิจกรรมสำคัญ
                        </p>
                        <a href="index.php?page=news" class="btn btn-light btn-lg">
                            <i class="bi bi-newspaper me-2"></i> ดูข่าวทั้งหมด
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</article>

<style>
.news-content {
    font-size: 1.1rem;
    line-height: 1.8;
    color: #333;
}

.news-content p {
    margin-bottom: 1.5rem;
}

.news-content img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    margin: 1.5rem 0;
}

.news-content h2,
.news-content h3,
.news-content h4 {
    margin-top: 2rem;
    margin-bottom: 1rem;
    font-weight: 700;
}

.news-content ul,
.news-content ol {
    margin-bottom: 1.5rem;
    padding-left: 2rem;
}

.news-content li {
    margin-bottom: 0.5rem;
}

.news-content blockquote {
    border-left: 4px solid #4facfe;
    padding-left: 1.5rem;
    margin: 1.5rem 0;
    font-style: italic;
    color: #666;
}

.news-content a {
    color: #4facfe;
    text-decoration: underline;
}

.news-content a:hover {
    color: #0061ff;
}
</style>