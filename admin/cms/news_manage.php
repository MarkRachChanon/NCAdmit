<?php

/**
 * หน้าจัดการข่าวประชาสัมพันธ์
 * NC-Admission - Nakhon Pathom College Admission System
 */

// ตรวจสอบสิทธิ์การเข้าถึง
if (!check_page_permission('news_manage', $admin_role)) {
    header('Location: index.php?page=dashboard');
    exit();
}

// ดึงข้อมูลข่าวทั้งหมด
$news_sql = "SELECT n.*, a.username as author_name 
             FROM news n 
             LEFT JOIN admin_users a ON n.author_id = a.id 
             ORDER BY n.is_pinned DESC, n.published_at DESC, n.created_at DESC";
$news_result = $conn->query($news_sql);

// สถิติ
$stats_sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN is_published = 1 THEN 1 ELSE 0 END) as published,
                SUM(CASE WHEN is_published = 0 THEN 1 ELSE 0 END) as draft,
                SUM(views) as total_views
              FROM news";
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
                        <i class="bi bi-newspaper text-primary me-2"></i>
                        จัดการข่าวประชาสัมพันธ์
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="index.php?page=dashboard">หน้าแรก</a></li>
                            <li class="breadcrumb-item active">จัดการข่าว</li>
                        </ol>
                    </nav>
                </div>

                <div>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addNewsModal">
                        <i class="bi bi-plus-circle me-2"></i>
                        เพิ่มข่าวใหม่
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- สถิติ -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card stat-card bg-gradient-primary text-white border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">ข่าวทั้งหมด</h6>
                            <h2 class="mb-0"><?php echo number_format($stats['total']); ?></h2>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-newspaper"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card stat-card bg-gradient-success text-white border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">เผยแพร่แล้ว</h6>
                            <h2 class="mb-0"><?php echo number_format($stats['published']); ?></h2>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card stat-card bg-gradient-warning text-white border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">แบบร่าง</h6>
                            <h2 class="mb-0"><?php echo number_format($stats['draft']); ?></h2>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card stat-card bg-gradient-info text-white border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">ยอดอ่านรวม</h6>
                            <h2 class="mb-0"><?php echo number_format($stats['total_views']); ?></h2>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-eye"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search -->
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-bold">
                        <i class="bi bi-search"></i> ค้นหา
                    </label>
                    <input type="text" class="form-control" id="searchInput"
                        placeholder="ค้นหาหัวข้อข่าว, เนื้อหา...">
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">
                        <i class="bi bi-funnel"></i> สถานะ
                    </label>
                    <select class="form-select" id="filterStatus">
                        <option value="">ทั้งหมด</option>
                        <option value="1">เผยแพร่</option>
                        <option value="0">แบบร่าง</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-bold">
                        <i class="bi bi-pin-angle"></i> ปักหมุด
                    </label>
                    <select class="form-select" id="filterPinned">
                        <option value="">ทั้งหมด</option>
                        <option value="1">ปักหมุด</option>
                        <option value="0">ไม่ปักหมุด</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <button type="button" class="btn btn-outline-secondary w-100" id="clearFilters">
                        <i class="bi bi-x-circle me-2"></i>ล้าง
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ตารางข่าว -->
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">
                <i class="bi bi-list-ul me-2"></i>
                รายการข่าวทั้งหมด
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 80px;" class="text-center">รูปภาพ</th>
                            <th>หัวข้อข่าว</th>
                            <th style="width: 120px;" class="text-center">ผู้เขียน</th>
                            <th style="width: 120px;" class="text-center">วันที่เผยแพร่</th>
                            <th style="width: 100px;" class="text-center">ยอดอ่าน</th>
                            <th style="width: 100px;" class="text-center">สถานะ</th>
                            <th style="width: 80px;" class="text-center">ปักหมุด</th>
                            <th style="width: 180px;" class="text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody id="newsTableBody">
                        <?php
                        if ($news_result && $news_result->num_rows > 0):
                            while ($news = $news_result->fetch_assoc()):
                                // กำหนด path รูปภาพ
                                $featured_image = $news['featured_image'];
                                if (!empty($featured_image)) {
                                    if (strpos($featured_image, 'http://') === 0 || strpos($featured_image, 'https://') === 0) {
                                        $img_src = $featured_image;
                                    } else {
                                        $img_src = '/ncadmission/' . $featured_image;
                                    }
                                } else {
                                    $img_src = 'https://via.placeholder.com/100x60/e9ecef/6c757d?text=No+Image';
                                }
                        ?>
                                <tr class="news-item"
                                    data-status="<?php echo $news['is_published']; ?>"
                                    data-pinned="<?php echo $news['is_pinned']; ?>">
                                    <!-- รูปภาพ -->
                                    <td class="text-center">
                                        <img src="<?php echo htmlspecialchars($img_src); ?>"
                                            alt="<?php echo htmlspecialchars($news['title']); ?>"
                                            class="rounded"
                                            style="width: 60px; height: 40px; object-fit: cover;"
                                            onerror="this.src='https://via.placeholder.com/100x60/e9ecef/6c757d?text=No+Image';">
                                    </td>

                                    <!-- หัวข้อข่าว -->
                                    <td>
                                        <div class="d-flex align-items-start">
                                            <?php if ($news['is_pinned']): ?>
                                                <i class="bi bi-pin-angle-fill text-danger me-2 mt-1"></i>
                                            <?php endif; ?>
                                            <div>
                                                <strong class="news-title">
                                                    <?php echo htmlspecialchars($news['title']); ?>
                                                </strong>
                                                <br>
                                                <small class="text-muted news-content">
                                                    <?php
                                                    // ใช้ excerpt ถ้ามี ไม่งั้นใช้ content
                                                    $excerpt = !empty($news['excerpt']) ? $news['excerpt'] : strip_tags($news['content']);
                                                    echo mb_strlen($excerpt) > 80 ? mb_substr($excerpt, 0, 80) . '...' : $excerpt;
                                                    ?>
                                                </small>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- ผู้เขียน -->
                                    <td class="text-center">
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars($news['author_name'] ?? 'ไม่ระบุ'); ?>
                                        </small>
                                    </td>

                                    <!-- วันที่เผยแพร่ -->
                                    <td class="text-center">
                                        <small class="text-muted">
                                            <?php echo $news['published_at'] ? thai_date($news['published_at'], 'short') : '-'; ?>
                                        </small>
                                    </td>

                                    <!-- ยอดอ่าน -->
                                    <td class="text-center">
                                        <span class="badge bg-info">
                                            <i class="bi bi-eye me-1"></i>
                                            <?php echo number_format($news['views']); ?>
                                        </span>
                                    </td>

                                    <!-- สถานะ -->
                                    <td class="text-center">
                                        <?php if ($news['is_published'] == 1): ?>
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle me-1"></i>เผยแพร่
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">
                                                <i class="bi bi-file-earmark-text me-1"></i>แบบร่าง
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- ปักหมุด -->
                                    <td class="text-center">
                                        <?php if ($news['is_pinned']): ?>
                                            <span class="badge bg-danger">
                                                <i class="bi bi-pin-angle-fill"></i>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- จัดการ -->
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button"
                                                class="btn btn-outline-primary btn-edit-news"
                                                data-id="<?php echo $news['id']; ?>"
                                                data-title="<?php echo htmlspecialchars($news['title']); ?>"
                                                data-slug="<?php echo htmlspecialchars($news['slug']); ?>"
                                                data-excerpt="<?php echo htmlspecialchars($news['excerpt']); ?>"
                                                data-content="<?php echo htmlspecialchars($news['content']); ?>"
                                                data-featured-image="<?php echo htmlspecialchars($news['featured_image']); ?>"
                                                data-published-at="<?php echo $news['published_at']; ?>"
                                                data-is-published="<?php echo $news['is_published']; ?>"
                                                data-pinned="<?php echo $news['is_pinned']; ?>"
                                                title="แก้ไข">
                                                <i class="bi bi-pencil"></i>
                                            </button>

                                            <button type="button"
                                                class="btn btn-outline-danger btn-delete-news"
                                                data-id="<?php echo $news['id']; ?>"
                                                data-title="<?php echo htmlspecialchars($news['title']); ?>"
                                                title="ลบ">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php
                            endwhile;
                        else:
                            ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                    <p class="mb-0">ยังไม่มีข่าวประชาสัมพันธ์</p>
                                    <button type="button" class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#addNewsModal">
                                        <i class="bi bi-plus-circle me-2"></i>เพิ่มข่าวแรก
                                    </button>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal เพิ่มข่าว -->
<div class="modal fade" id="addNewsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-plus-circle me-2"></i>
                    เพิ่มข่าวใหม่
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="addNewsForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row">
                        <!-- ด้านซ้าย -->
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="add_title" class="form-label">
                                    หัวข้อข่าว <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                    class="form-control"
                                    id="add_title"
                                    name="title"
                                    required
                                    placeholder="เช่น ประกาศรับสมัครนักเรียนใหม่">
                            </div>

                            <div class="mb-3">
                                <label for="add_excerpt" class="form-label">
                                    สรุปข่าว (Excerpt)
                                </label>
                                <textarea class="form-control"
                                    id="add_excerpt"
                                    name="excerpt"
                                    rows="2"
                                    placeholder="สรุปข่าวสั้นๆ สำหรับแสดงในหน้ารายการ (ถ้าไม่ระบุจะใช้ส่วนแรกของเนื้อหา)"></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="add_content" class="form-label">
                                    เนื้อหาข่าว <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control"
                                    id="add_content"
                                    name="content"
                                    rows="10"
                                    required
                                    placeholder="เขียนเนื้อหาข่าวที่นี่..."></textarea>
                                <div class="form-text">
                                    <i class="bi bi-info-circle"></i> รองรับ HTML สำหรับจัดรูปแบบข้อความ
                                </div>
                            </div>
                        </div>

                        <!-- ด้านขวา -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="add_featured_image" class="form-label">
                                    รูปภาพข่าว
                                </label>
                                <input type="file"
                                    class="form-control"
                                    id="add_featured_image"
                                    name="featured_image"
                                    accept="image/jpeg,image/png,image/gif">
                                <div class="form-text">ขนาดแนะนำ: 800x600 px</div>
                            </div>

                            <div class="mb-3">
                                <label for="add_image_url" class="form-label">หรือใช้ URL รูปภาพ</label>
                                <input type="url"
                                    class="form-control"
                                    id="add_image_url"
                                    name="image_url"
                                    placeholder="https://example.com/image.jpg">
                            </div>

                            <div class="mb-3">
                                <label for="add_published_at" class="form-label">
                                    วันที่เผยแพร่
                                </label>
                                <input type="datetime-local"
                                    class="form-control"
                                    id="add_published_at"
                                    name="published_at"
                                    value="<?php echo date('Y-m-d\TH:i'); ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">สถานะ</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input"
                                        type="checkbox"
                                        id="add_is_published"
                                        name="is_published"
                                        value="1"
                                        checked>
                                    <label class="form-check-label" for="add_is_published">
                                        <i class="bi bi-check-circle"></i> เผยแพร่ทันที
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input"
                                        type="checkbox"
                                        id="add_is_pinned"
                                        name="is_pinned"
                                        value="1">
                                    <label class="form-check-label" for="add_is_pinned">
                                        <i class="bi bi-pin-angle"></i> ปักหมุดข่าวนี้
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>ยกเลิก
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>บันทึกข่าว
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal แก้ไขข่าว -->
<div class="modal fade" id="editNewsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="bi bi-pencil-square me-2"></i>
                    แก้ไขข่าว
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editNewsForm" enctype="multipart/form-data">
                <input type="hidden" id="edit_id" name="id">
                <input type="hidden" id="edit_old_featured_image" name="old_featured_image">

                <div class="modal-body">
                    <div class="row">
                        <!-- ด้านซ้าย -->
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="edit_title" class="form-label">
                                    หัวข้อข่าว <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                    class="form-control"
                                    id="edit_title"
                                    name="title"
                                    required>
                            </div>

                            <div class="mb-3">
                                <label for="edit_excerpt" class="form-label">
                                    สรุปข่าว (Excerpt)
                                </label>
                                <textarea class="form-control"
                                    id="edit_excerpt"
                                    name="excerpt"
                                    rows="2"></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="edit_content" class="form-label">
                                    เนื้อหาข่าว <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control"
                                    id="edit_content"
                                    name="content"
                                    rows="10"
                                    required></textarea>
                            </div>
                        </div>

                        <!-- ด้านขวา -->
                        <div class="col-md-4">
                            <!-- แสดงรูปภาพปัจจุบัน -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">รูปภาพปัจจุบัน</label>
                                <div class="border rounded p-2 bg-light text-center">
                                    <img id="edit_current_image"
                                        src=""
                                        alt="Current Image"
                                        style="max-width: 100%; max-height: 150px; object-fit: contain;">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="edit_featured_image" class="form-label">
                                    เปลี่ยนรูปภาพ (ถ้าต้องการ)
                                </label>
                                <input type="file"
                                    class="form-control"
                                    id="edit_featured_image"
                                    name="featured_image"
                                    accept="image/jpeg,image/png,image/gif">
                            </div>

                            <div class="mb-3">
                                <label for="edit_image_url" class="form-label">หรือใช้ URL</label>
                                <input type="url"
                                    class="form-control"
                                    id="edit_image_url"
                                    name="image_url">
                            </div>

                            <div class="mb-3">
                                <label for="edit_published_at" class="form-label">
                                    วันที่เผยแพร่
                                </label>
                                <input type="datetime-local"
                                    class="form-control"
                                    id="edit_published_at"
                                    name="published_at">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">สถานะ</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input"
                                        type="checkbox"
                                        id="edit_is_published"
                                        name="is_published"
                                        value="1">
                                    <label class="form-check-label" for="edit_is_published">
                                        <i class="bi bi-check-circle"></i> เผยแพร่
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input"
                                        type="checkbox"
                                        id="edit_is_pinned"
                                        name="is_pinned"
                                        value="1">
                                    <label class="form-check-label" for="edit_is_pinned">
                                        <i class="bi bi-pin-angle"></i> ปักหมุด
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>ยกเลิก
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-save me-2"></i>บันทึกการแก้ไข
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .news-item {
        transition: background-color 0.2s ease;
    }

    .news-item:hover {
        background-color: #f8f9fa;
    }

    .news-item.hidden {
        display: none;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('News Management Loaded');

        // ==================== Search & Filter ====================
        const searchInput = document.getElementById('searchInput');
        const filterStatus = document.getElementById('filterStatus');
        const filterPinned = document.getElementById('filterPinned');
        const newsItems = document.querySelectorAll('.news-item');

        function filterNews() {
            const searchTerm = searchInput.value.toLowerCase();
            const statusValue = filterStatus.value;
            const pinnedValue = filterPinned.value;

            newsItems.forEach(item => {
                const title = item.querySelector('.news-title').textContent.toLowerCase();
                const content = item.querySelector('.news-content').textContent.toLowerCase();
                const status = item.dataset.status;
                const pinned = item.dataset.pinned;

                const matchSearch = title.includes(searchTerm) || content.includes(searchTerm);
                const matchStatus = statusValue === '' || status === statusValue;
                const matchPinned = pinnedValue === '' || pinned === pinnedValue;

                if (matchSearch && matchStatus && matchPinned) {
                    item.classList.remove('hidden');
                } else {
                    item.classList.add('hidden');
                }
            });
        }

        if (searchInput) {
            searchInput.addEventListener('keyup', filterNews);
        }

        if (filterStatus) {
            filterStatus.addEventListener('change', filterNews);
        }

        if (filterPinned) {
            filterPinned.addEventListener('change', filterNews);
        }

        // Clear Filters
        document.getElementById('clearFilters')?.addEventListener('click', function() {
            searchInput.value = '';
            filterStatus.value = '';
            filterPinned.value = '';
            filterNews();
        });

        // ==================== Add News ====================
        const addForm = document.getElementById('addNewsForm');
        if (addForm) {
            addForm.addEventListener('submit', function(e) {
                e.preventDefault();
                console.log('Adding news');

                if (!addForm.checkValidity()) {
                    e.stopPropagation();
                    addForm.classList.add('was-validated');
                    return;
                }

                Swal.fire({
                    title: 'กำลังบันทึกข้อมูล...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                const formData = new FormData(addForm);

                fetch('api/news_add.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        console.log('Response status:', response.status);
                        console.log('Response headers:', response.headers);

                        // อ่าน response เป็น text ก่อน
                        return response.text().then(text => {
                            console.log('Raw response:', text);

                            // ตรวจสอบว่าเป็น JSON หรือไม่
                            if (!text) {
                                throw new Error('Server ไม่ตอบกลับ (Empty response)');
                            }

                            try {
                                return JSON.parse(text);
                            } catch (e) {
                                console.error('JSON Parse Error:', e);
                                console.error('Response text:', text.substring(0, 500));
                                throw new Error('Server ตอบกลับไม่ถูกต้อง:\n\n' + text.substring(0, 300));
                            }
                        });
                    })
                    .then(data => {
                        console.log('Add Response:', data);

                        if (data.success) {
                            const modal = bootstrap.Modal.getInstance(document.getElementById('addNewsModal'));
                            modal.hide();

                            Swal.fire({
                                icon: 'success',
                                title: 'สำเร็จ!',
                                text: data.message,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'เกิดข้อผิดพลาด',
                                html: data.message || 'ไม่สามารถบันทึกข้อมูลได้'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Fetch Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'เกิดข้อผิดพลาด',
                            html: '<div style="text-align: left; max-height: 300px; overflow-y: auto;">' +
                                '<strong>Error:</strong><br>' +
                                error.message +
                                '</div>',
                            width: 700,
                            footer: '<small>กรุณาเปิด Console (F12) เพื่อดูรายละเอียดเพิ่มเติม</small>'
                        });
                    });
            });
        }

        // ==================== Edit News ====================
        document.addEventListener('click', function(e) {
            if (e.target.closest('.btn-edit-news')) {
                const btn = e.target.closest('.btn-edit-news');

                document.getElementById('edit_id').value = btn.dataset.id;
                document.getElementById('edit_title').value = btn.dataset.title;
                document.getElementById('edit_excerpt').value = btn.dataset.excerpt;
                document.getElementById('edit_content').value = btn.dataset.content;

                // แปลง published_at จาก MySQL datetime เป็น datetime-local format
                const publishedAt = btn.dataset.publishedAt;
                if (publishedAt) {
                    const dateObj = new Date(publishedAt);
                    const year = dateObj.getFullYear();
                    const month = String(dateObj.getMonth() + 1).padStart(2, '0');
                    const day = String(dateObj.getDate()).padStart(2, '0');
                    const hours = String(dateObj.getHours()).padStart(2, '0');
                    const minutes = String(dateObj.getMinutes()).padStart(2, '0');
                    document.getElementById('edit_published_at').value = `${year}-${month}-${day}T${hours}:${minutes}`;
                }

                document.getElementById('edit_is_published').checked = btn.dataset.isPublished == 1;
                document.getElementById('edit_is_pinned').checked = btn.dataset.pinned == 1;

                // แสดงรูปภาพปัจจุบัน
                const featuredImage = btn.dataset.featuredImage;
                document.getElementById('edit_old_featured_image').value = featuredImage;

                if (featuredImage) {
                    let imgSrc;
                    if (featuredImage.indexOf('http://') === 0 || featuredImage.indexOf('https://') === 0) {
                        imgSrc = featuredImage;
                    } else {
                        imgSrc = '/ncadmission/' + featuredImage;
                    }
                    document.getElementById('edit_current_image').src = imgSrc;
                } else {
                    document.getElementById('edit_current_image').src = 'https://via.placeholder.com/300x200?text=No+Image';
                }

                // Reset input
                document.getElementById('edit_featured_image').value = '';
                document.getElementById('edit_image_url').value = '';

                const modal = new bootstrap.Modal(document.getElementById('editNewsModal'));
                modal.show();
            }
        });

        const editForm = document.getElementById('editNewsForm');
        if (editForm) {
            editForm.addEventListener('submit', function(e) {
                e.preventDefault();
                console.log('Editing news');

                if (!editForm.checkValidity()) {
                    e.stopPropagation();
                    editForm.classList.add('was-validated');
                    return;
                }

                Swal.fire({
                    title: 'กำลังบันทึกข้อมูล...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                const formData = new FormData(editForm);

                fetch('api/news_edit.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Edit Response:', data);

                        if (data.success) {
                            const modal = bootstrap.Modal.getInstance(document.getElementById('editNewsModal'));
                            modal.hide();

                            Swal.fire({
                                icon: 'success',
                                title: 'สำเร็จ!',
                                text: data.message,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'เกิดข้อผิดพลาด',
                                html: data.message
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'เกิดข้อผิดพลาด',
                            text: 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้'
                        });
                    });
            });
        }

        // ==================== Delete News ====================
        document.addEventListener('click', function(e) {
            if (e.target.closest('.btn-delete-news')) {
                e.preventDefault();

                const btn = e.target.closest('.btn-delete-news');
                const newsId = btn.dataset.id;
                const newsTitle = btn.dataset.title;

                Swal.fire({
                    title: 'ยืนยันการลบ?',
                    html: `คุณต้องการลบข่าว<br><strong class="text-danger">"${newsTitle}"</strong><br>ใช่หรือไม่?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'ใช่, ลบเลย!',
                    cancelButtonText: 'ยกเลิก',
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'กำลังลบข้อมูล...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        fetch('api/news_delete.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    news_id: parseInt(newsId)
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                console.log('Delete Response:', data);

                                if (data.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'ลบสำเร็จ!',
                                        text: data.message,
                                        timer: 1500,
                                        showConfirmButton: false
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'เกิดข้อผิดพลาด',
                                        text: data.message
                                    });
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'เกิดข้อผิดพลาด',
                                    text: 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้'
                                });
                            });
                    }
                });
            }
        });

        // Reset forms when modals close
        document.getElementById('addNewsModal')?.addEventListener('hidden.bs.modal', function() {
            addForm.reset();
            addForm.classList.remove('was-validated');
        });

        document.getElementById('editNewsModal')?.addEventListener('hidden.bs.modal', function() {
            editForm.reset();
            editForm.classList.remove('was-validated');
        });
    });
</script>