<?php
/**
 * News Page - ข่าวสาร
 */

// Pagination
$page_num = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$per_page = 9; // แสดง 9 ข่าวต่อหน้า
$offset = ($page_num - 1) * $per_page;

// Search & Category Filter
$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';
$category_id = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;

// Build Query
$where = ["is_published = 1"];
$params = [];

if (!empty($search)) {
    $where[] = "(title LIKE ? OR excerpt LIKE ? OR content LIKE ?)";
    $search_term = "%$search%";
    $params = array_merge($params, [$search_term, $search_term, $search_term]);
}

if ($category_id > 0) {
    $where[] = "category_id = ?";
    $params[] = $category_id;
}

$where_sql = implode(" AND ", $where);

// Count Total
$count_sql = "SELECT COUNT(*) as total FROM news WHERE $where_sql";
$count_stmt = $conn->prepare($count_sql);
if (!empty($params)) {
    $types = str_repeat('s', count($params));
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$total_result = $count_stmt->get_result()->fetch_assoc();
$total_news = $total_result['total'];
$total_pages = ceil($total_news / $per_page);

// Get News
$sql = "SELECT n.*, c.name as category_name, c.color as category_color 
        FROM news n 
        LEFT JOIN categories c ON n.category_id = c.id 
        WHERE $where_sql 
        ORDER BY is_pinned DESC, published_at DESC 
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
$params[] = $per_page;
$params[] = $offset;
$types = str_repeat('s', count($params) - 2) . 'ii';
$stmt->bind_param($types, ...$params);
$stmt->execute();
$news_result = $stmt->get_result();

// Get Categories
$cat_sql = "SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order, name";
$cat_result = $conn->query($cat_sql);
?>

<!-- Page Header -->
<section class="page-header bg-gradient-primary text-white py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center" data-aos="fade-down">
                <h1 class="display-5 fw-bold mb-3">
                    <i class="bi bi-newspaper me-3"></i>
                    ข่าวสาร
                </h1>
                <p class="lead mb-0">
                    ติดตามข่าวสารและกิจกรรมของเรา
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Search & Filter -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center mb-4">
            <div class="col-lg-8">
                <form action="index.php" method="GET">
                    <input type="hidden" name="page" value="news">
                    <?php if ($category_id > 0): ?>
                        <input type="hidden" name="cat" value="<?php echo $category_id; ?>">
                    <?php endif; ?>
                    
                    <div class="input-group input-group-lg shadow-sm" data-aos="fade-up">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search text-primary"></i>
                        </span>
                        <input type="text" 
                               name="search" 
                               class="form-control border-start-0 border-end-0 ps-0" 
                               placeholder="ค้นหาข่าวสาร..."
                               value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-search me-2"></i> ค้นหา
                        </button>
                        <?php if (!empty($search)): ?>
                            <a href="index.php?page=news<?php echo $category_id > 0 ? '&cat=' . $category_id : ''; ?>" 
                               class="btn btn-outline-secondary"
                               title="ล้างการค้นหา">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Category Filter -->
        <?php 
        // Reset pointer for category buttons
        $cat_result->data_seek(0);
        ?>
        <div class="row">
            <div class="col-12">
                <div class="d-flex gap-2 flex-wrap justify-content-center" data-aos="fade-up">
                    <a href="index.php?page=news<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                       class="btn <?php echo ($category_id == 0) ? 'btn-gradient' : 'btn-outline-primary'; ?> btn-sm px-3">
                        <i class="bi bi-grid-3x3-gap-fill me-2"></i> ทั้งหมด
                    </a>
                    <?php while ($cat = $cat_result->fetch_assoc()): ?>
                        <a href="index.php?page=news&cat=<?php echo $cat['id']; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                           class="btn <?php echo ($category_id == $cat['id']) ? 'btn-gradient' : 'btn-outline-primary'; ?> btn-sm px-3">
                            <?php if ($cat['icon']): ?>
                                <i class="bi bi-<?php echo $cat['icon']; ?> me-2"></i>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </a>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

        <!-- Active Filter Info -->
        <?php if (!empty($search) || $category_id > 0): ?>
            <div class="row mt-4">
                <div class="col-12">
                    <div class="alert alert-info d-flex align-items-center justify-content-between" role="alert">
                        <div>
                            <i class="bi bi-funnel-fill me-2"></i>
                            <strong>กำลังแสดง:</strong>
                            <?php if (!empty($search)): ?>
                                ผลการค้นหา "<span class="text-primary"><?php echo htmlspecialchars($search); ?></span>"
                            <?php endif; ?>
                            <?php if ($category_id > 0): ?>
                                <?php
                                // Get category name
                                $cat_name_sql = "SELECT name FROM categories WHERE id = $category_id";
                                $cat_name_result = $conn->query($cat_name_sql);
                                if ($cat_name_result && $cat_name_result->num_rows > 0) {
                                    $cat_data = $cat_name_result->fetch_assoc();
                                    echo (!empty($search) ? ' ในหมวดหมู่ ' : 'หมวดหมู่ ') . '<span class="text-primary">' . htmlspecialchars($cat_data['name']) . '</span>';
                                }
                                ?>
                            <?php endif; ?>
                            <span class="ms-2 badge bg-primary"><?php echo number_format($total_news); ?> รายการ</span>
                        </div>
                        <a href="index.php?page=news" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-x-circle me-1"></i> ล้างตัวกรอง
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
                </div>
            </div>
        </div>

        <!-- Active Filters -->
        <?php if (!empty($search) || $category_id > 0): ?>
            <div class="mt-3">
                <small class="text-muted">
                    <i class="bi bi-funnel me-2"></i> กำลังกรอง:
                    <?php if (!empty($search)): ?>
                        <span class="badge bg-info me-2">
                            ค้นหา: "<?php echo htmlspecialchars($search); ?>"
                            <a href="index.php?page=news<?php echo $category_id > 0 ? '&cat='.$category_id : ''; ?>" 
                               class="text-white ms-2">×</a>
                        </span>
                    <?php endif; ?>
                    <?php if ($category_id > 0): ?>
                        <span class="badge bg-info me-2">
                            หมวดหมู่
                            <a href="index.php?page=news<?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>" 
                               class="text-white ms-2">×</a>
                        </span>
                    <?php endif; ?>
                </small>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- News Grid -->
<section class="py-5">
    <div class="container">
        <?php if ($total_news > 0): ?>
            <!-- Results Info -->
            <div class="row mb-4">
                <div class="col-12">
                    <p class="text-muted mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        พบ <strong><?php echo number_format($total_news); ?></strong> ข่าว
                        <?php if ($total_pages > 1): ?>
                            (หน้า <?php echo $page_num; ?> จาก <?php echo $total_pages; ?>)
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <!-- News Cards -->
            <div class="row g-4">
                <?php 
                $delay = 100;
                while ($news = $news_result->fetch_assoc()): 
                ?>
                    <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                        <article class="card border-0 shadow-sm h-100 news-card">
                            <!-- Image -->
                            <?php if ($news['featured_image']): ?>
                                <div class="position-relative overflow-hidden" style="height: 220px;">
                                    <img src="<?php echo htmlspecialchars($news['featured_image']); ?>" 
                                         class="card-img-top" 
                                         alt="<?php echo htmlspecialchars($news['title']); ?>"
                                         style="height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                                    
                                    <!-- Pinned Badge -->
                                    <?php if ($news['is_pinned']): ?>
                                        <span class="position-absolute top-0 start-0 m-3">
                                            <span class="badge bg-danger">
                                                <i class="bi bi-pin-angle-fill me-1"></i> ปักหมุด
                                            </span>
                                        </span>
                                    <?php endif; ?>

                                    <!-- Category Badge -->
                                    <?php if ($news['category_name']): ?>
                                        <span class="position-absolute top-0 end-0 m-3">
                                            <span class="badge" style="background-color: <?php echo $news['category_color'] ?? '#0d6efd'; ?>">
                                                <?php echo htmlspecialchars($news['category_name']); ?>
                                            </span>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="bg-gradient-primary text-white d-flex align-items-center justify-content-center" 
                                     style="height: 220px;">
                                    <i class="bi bi-image display-1"></i>
                                </div>
                            <?php endif; ?>

                            <!-- Card Body -->
                            <div class="card-body d-flex flex-column p-4">
                                <!-- Meta Info -->
                                <div class="d-flex align-items-center gap-3 mb-3 text-muted small">
                                    <span>
                                        <i class="bi bi-calendar3 me-1"></i>
                                        <?php echo thai_date($news['published_at'], 'short'); ?>
                                    </span>
                                    <span>
                                        <i class="bi bi-eye me-1"></i>
                                        <?php echo number_format($news['views']); ?> ครั้ง
                                    </span>
                                </div>

                                <!-- Title -->
                                <h5 class="card-title fw-bold mb-3">
                                    <a href="index.php?page=news_detail&id=<?php echo $news['id']; ?>" 
                                       class="text-decoration-none text-dark stretched-link">
                                        <?php echo htmlspecialchars($news['title']); ?>
                                    </a>
                                </h5>

                                <!-- Excerpt -->
                                <p class="card-text text-muted flex-grow-1 mb-3">
                                    <?php echo mb_substr(strip_tags($news['excerpt']), 0, 120, 'UTF-8'); ?>...
                                </p>

                                <!-- Read More -->
                                <div class="mt-auto">
                                    <span class="text-primary small">
                                        อ่านเพิ่มเติม <i class="bi bi-arrow-right ms-1"></i>
                                    </span>
                                </div>
                            </div>
                        </article>
                    </div>
                    <?php 
                    $delay += 100;
                    if ($delay > 300) $delay = 100;
                    ?>
                <?php endwhile; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="row mt-5">
                    <div class="col-12">
                        <nav aria-label="News pagination">
                            <ul class="pagination justify-content-center">
                                <!-- Previous -->
                                <?php if ($page_num > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="index.php?page=news&p=<?php echo ($page_num - 1); ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?><?php echo $category_id > 0 ? '&cat='.$category_id : ''; ?>">
                                            <i class="bi bi-chevron-left"></i> ก่อนหน้า
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <!-- Pages -->
                                <?php
                                $start = max(1, $page_num - 2);
                                $end = min($total_pages, $page_num + 2);

                                if ($start > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="index.php?page=news&p=1<?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?><?php echo $category_id > 0 ? '&cat='.$category_id : ''; ?>">1</a>
                                    </li>
                                    <?php if ($start > 2): ?>
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php for ($i = $start; $i <= $end; $i++): ?>
                                    <li class="page-item <?php echo ($i == $page_num) ? 'active' : ''; ?>">
                                        <a class="page-link" href="index.php?page=news&p=<?php echo $i; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?><?php echo $category_id > 0 ? '&cat='.$category_id : ''; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($end < $total_pages): ?>
                                    <?php if ($end < $total_pages - 1): ?>
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                    <?php endif; ?>
                                    <li class="page-item">
                                        <a class="page-link" href="index.php?page=news&p=<?php echo $total_pages; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?><?php echo $category_id > 0 ? '&cat='.$category_id : ''; ?>">
                                            <?php echo $total_pages; ?>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <!-- Next -->
                                <?php if ($page_num < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="index.php?page=news&p=<?php echo ($page_num + 1); ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?><?php echo $category_id > 0 ? '&cat='.$category_id : ''; ?>">
                                            ถัดไป <i class="bi bi-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <!-- No Results -->
            <div class="row">
                <div class="col-lg-6 offset-lg-3">
                    <div class="text-center py-5" data-aos="fade-up">
                        <i class="bi bi-inbox display-1 text-muted mb-4"></i>
                        <h4 class="fw-bold mb-3">ไม่พบข่าวสาร</h4>
                        <p class="text-muted mb-4">
                            <?php if (!empty($search) || $category_id > 0): ?>
                                ไม่พบข่าวสารที่ตรงกับเงื่อนไขการค้นหา
                            <?php else: ?>
                                ยังไม่มีข่าวสารในขณะนี้
                            <?php endif; ?>
                        </p>
                        <?php if (!empty($search) || $category_id > 0): ?>
                            <a href="index.php?page=news" class="btn btn-primary">
                                <i class="bi bi-arrow-left me-2"></i> ดูข่าวสารทั้งหมด
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<style>
.news-card {
    transition: all 0.3s ease;
}

.news-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15) !important;
}

.news-card:hover img {
    transform: scale(1.1);
}

.news-card .stretched-link::after {
    z-index: 1;
}

.page-link {
    color: #4facfe;
    border-color: #dee2e6;
}

.page-link:hover {
    color: #0061ff;
    background-color: #e9f5ff;
    border-color: #4facfe;
}

.page-item.active .page-link {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    border-color: #4facfe;
}

.page-item.disabled .page-link {
    background-color: transparent;
    border-color: #dee2e6;
}
</style>