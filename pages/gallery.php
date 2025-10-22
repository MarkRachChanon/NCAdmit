<?php
/**
 * Gallery Page - แกลเลอรี่
 */

// Pagination
$page_num = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$per_page = 12;
$offset = ($page_num - 1) * $per_page;

// Category Filter
$category_id = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;

// Build Query
$where = ["is_published = 1"];

if ($category_id > 0) {
    $where[] = "category_id = $category_id";
}

$where_sql = implode(" AND ", $where);

// Get total count
$count_sql = "SELECT COUNT(*) as total FROM gallery WHERE $where_sql";
$count_result = $conn->query($count_sql);
$total_items = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_items / $per_page);

// Get gallery items
$sql = "SELECT g.*, c.name as category_name, c.slug as category_slug, c.color as category_color
        FROM gallery g
        LEFT JOIN categories c ON g.category_id = c.id
        WHERE $where_sql
        ORDER BY g.sort_order DESC, g.created_at DESC
        LIMIT $offset, $per_page";
$gallery_result = $conn->query($sql);

// Get categories
$cat_sql = "SELECT c.*, COUNT(g.id) as item_count 
            FROM categories c 
            LEFT JOIN gallery g ON c.id = g.category_id AND g.is_published = 1
            WHERE c.is_active = 1 
            GROUP BY c.id 
            ORDER BY c.sort_order, c.name";
$cat_result = $conn->query($cat_sql);
?>

<!-- Page Header -->
<section class="page-header bg-gradient-primary text-white py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center" data-aos="fade-down">
                <h1 class="display-5 fw-bold mb-3">
                    <i class="bi bi-images me-3"></i>
                    แกลเลอรี่
                </h1>
                <p class="lead mb-0">
                    ภาพกิจกรรมและบรรยากาศของวิทยาลัย
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Category Filter -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="d-flex gap-2 flex-wrap justify-content-center" data-aos="fade-up">
                    <a href="index.php?page=gallery" 
                       class="btn <?php echo ($category_id == 0) ? 'btn-gradient' : 'btn-outline-primary'; ?> btn-sm px-4">
                        <i class="bi bi-grid-3x3-gap-fill me-2"></i> 
                        ทั้งหมด
                        <?php if ($category_id == 0): ?>
                            <span class="badge bg-white text-primary ms-2"><?php echo number_format($total_items); ?></span>
                        <?php endif; ?>
                    </a>
                    <?php if ($cat_result && $cat_result->num_rows > 0): ?>
                        <?php while ($cat = $cat_result->fetch_assoc()): ?>
                            <?php if ($cat['item_count'] > 0): ?>
                                <a href="index.php?page=gallery&cat=<?php echo $cat['id']; ?>" 
                                   class="btn <?php echo ($category_id == $cat['id']) ? 'btn-gradient' : 'btn-outline-primary'; ?> btn-sm px-4">
                                    <?php if ($cat['icon']): ?>
                                        <i class="bi bi-<?php echo $cat['icon']; ?> me-2"></i>
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                    <?php if ($category_id == $cat['id']): ?>
                                        <span class="badge bg-white text-primary ms-2"><?php echo number_format($cat['item_count']); ?></span>
                                    <?php endif; ?>
                                </a>
                            <?php endif; ?>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Active Filter Info -->
        <?php if ($category_id > 0): ?>
            <?php
            // Get category name
            $cat_name_sql = "SELECT name FROM categories WHERE id = $category_id";
            $cat_name_result = $conn->query($cat_name_sql);
            if ($cat_name_result && $cat_name_result->num_rows > 0):
                $cat_data = $cat_name_result->fetch_assoc();
            ?>
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="alert alert-info d-flex align-items-center justify-content-between" role="alert">
                            <div>
                                <i class="bi bi-funnel-fill me-2"></i>
                                <strong>กำลังแสดง:</strong>
                                หมวดหมู่ <span class="text-primary"><?php echo htmlspecialchars($cat_data['name']); ?></span>
                                <span class="ms-2 badge bg-primary"><?php echo number_format($total_items); ?> รายการ</span>
                            </div>
                            <a href="index.php?page=gallery" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-x-circle me-1"></i> แสดงทั้งหมด
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<!-- Gallery Grid -->
<section class="py-5">
    <div class="container">
        <?php if ($gallery_result && $gallery_result->num_rows > 0): ?>
            <div class="row g-4 gallery-masonry">
                <?php 
                $delay = 100;
                while ($item = $gallery_result->fetch_assoc()): 
                ?>
                    <div class="col-md-6 col-lg-4 col-xl-3" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                        <div class="gallery-item">
                            <div class="gallery-image-wrapper">
                                <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['title']); ?>"
                                     class="gallery-image"
                                     data-lightbox="gallery"
                                     data-title="<?php echo htmlspecialchars($item['title']); ?>">
                                
                                <!-- Overlay -->
                                <div class="gallery-overlay">
                                    <div class="gallery-content">
                                        <h5 class="text-white fw-bold mb-2">
                                            <?php echo htmlspecialchars($item['title']); ?>
                                        </h5>
                                        <?php if ($item['description']): ?>
                                            <p class="text-white small mb-3">
                                                <?php echo htmlspecialchars(mb_substr($item['description'], 0, 80, 'UTF-8')); ?>...
                                            </p>
                                        <?php endif; ?>
                                        <button class="btn btn-light btn-sm" 
                                                onclick="openLightbox('<?php echo htmlspecialchars($item['image_url']); ?>', '<?php echo htmlspecialchars($item['title']); ?>', '<?php echo htmlspecialchars($item['description'] ?? ''); ?>')">
                                            <i class="bi bi-zoom-in me-2"></i> ดูภาพ
                                        </button>
                                    </div>
                                </div>

                                <!-- Category Badge -->
                                <?php if ($item['category_name']): ?>
                                    <div class="gallery-badge">
                                        <span class="badge" style="background-color: <?php echo $item['category_color'] ?? '#6c757d'; ?>">
                                            <?php echo htmlspecialchars($item['category_name']); ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php 
                    $delay += 50;
                    if ($delay > 300) $delay = 100;
                    ?>
                <?php endwhile; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="mt-5" data-aos="fade-up">
                    <nav aria-label="Gallery pagination">
                        <ul class="pagination pagination-lg justify-content-center">
                            <!-- Previous -->
                            <?php if ($page_num > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" 
                                       href="?page=gallery&p=<?php echo ($page_num - 1); ?><?php echo $category_id > 0 ? '&cat=' . $category_id : ''; ?>">
                                        <i class="bi bi-chevron-left"></i> ก่อนหน้า
                                    </a>
                                </li>
                            <?php endif; ?>

                            <!-- Page Numbers -->
                            <?php
                            $start_page = max(1, $page_num - 2);
                            $end_page = min($total_pages, $page_num + 2);
                            
                            if ($start_page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=gallery&p=1<?php echo $category_id > 0 ? '&cat=' . $category_id : ''; ?>">1</a>
                                </li>
                                <?php if ($start_page > 2): ?>
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                <li class="page-item <?php echo ($i == $page_num) ? 'active' : ''; ?>">
                                    <a class="page-link" 
                                       href="?page=gallery&p=<?php echo $i; ?><?php echo $category_id > 0 ? '&cat=' . $category_id : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($end_page < $total_pages): ?>
                                <?php if ($end_page < $total_pages - 1): ?>
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                <?php endif; ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=gallery&p=<?php echo $total_pages; ?><?php echo $category_id > 0 ? '&cat=' . $category_id : ''; ?>"><?php echo $total_pages; ?></a>
                                </li>
                            <?php endif; ?>

                            <!-- Next -->
                            <?php if ($page_num < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" 
                                       href="?page=gallery&p=<?php echo ($page_num + 1); ?><?php echo $category_id > 0 ? '&cat=' . $category_id : ''; ?>">
                                        ถัดไป <i class="bi bi-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <!-- Empty State -->
            <div class="text-center py-5" data-aos="fade-up">
                <i class="bi bi-images display-1 text-muted mb-3"></i>
                <h4 class="mb-3">ไม่พบรูปภาพ</h4>
                <p class="text-muted mb-4">
                    <?php if ($category_id > 0): ?>
                        ยังไม่มีรูปภาพในหมวดหมู่นี้
                    <?php else: ?>
                        ยังไม่มีรูปภาพในแกลเลอรี่
                    <?php endif; ?>
                </p>
                <?php if ($category_id > 0): ?>
                    <a href="index.php?page=gallery" class="btn btn-primary">
                        <i class="bi bi-arrow-left me-2"></i> กลับหน้าแกลเลอรี่ทั้งหมด
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Lightbox Modal -->
<div class="modal fade" id="lightboxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-transparent border-0">
            <div class="modal-body p-0">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" 
                        data-bs-dismiss="modal" aria-label="Close" style="z-index: 1050;"></button>
                <div class="text-center">
                    <img id="lightboxImage" src="" alt="" class="img-fluid rounded-3 shadow-lg">
                    <div class="mt-3 text-white">
                        <h4 id="lightboxTitle" class="mb-2"></h4>
                        <p id="lightboxDescription" class="mb-0"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Gallery Styles */
.gallery-item {
    height: 100%;
}

.gallery-image-wrapper {
    position: relative;
    overflow: hidden;
    border-radius: 12px;
    height: 280px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.gallery-image-wrapper:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
}

.gallery-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
    cursor: pointer;
}

.gallery-image-wrapper:hover .gallery-image {
    transform: scale(1.1);
}

/* Overlay */
.gallery-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.4) 50%, rgba(0,0,0,0) 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
    display: flex;
    align-items: flex-end;
    padding: 1.5rem;
}

.gallery-image-wrapper:hover .gallery-overlay {
    opacity: 1;
}

.gallery-content {
    width: 100%;
}

/* Category Badge */
.gallery-badge {
    position: absolute;
    top: 1rem;
    right: 1rem;
    z-index: 10;
}

/* Lightbox Modal */
#lightboxModal .modal-content {
    background-color: rgba(0, 0, 0, 0.9) !important;
}

#lightboxImage {
    max-height: 80vh;
    width: auto;
    max-width: 100%;
}

/* Pagination */
.pagination .page-link {
    border-radius: 8px;
    margin: 0 4px;
    border: none;
    background: transparent;
    color: #4facfe;
    font-weight: 500;
}

.pagination .page-item.active .page-link {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
}

.pagination .page-link:hover {
    background: rgba(79, 172, 254, 0.1);
    color: #4facfe;
}

.pagination .page-item.disabled .page-link {
    color: #6c757d;
}

/* Responsive */
@media (max-width: 768px) {
    .gallery-image-wrapper {
        height: 220px;
    }
    
    .gallery-overlay {
        opacity: 1;
    }
}
</style>

<script>
// Lightbox Function
function openLightbox(imageUrl, title, description) {
    document.getElementById('lightboxImage').src = imageUrl;
    document.getElementById('lightboxTitle').textContent = title;
    document.getElementById('lightboxDescription').textContent = description || '';
    
    const modal = new bootstrap.Modal(document.getElementById('lightboxModal'));
    modal.show();
}

// Close lightbox on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modalEl = document.getElementById('lightboxModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) {
            modal.hide();
        }
    }
});
</script>