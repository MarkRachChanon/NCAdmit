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

// ดึงข้อมูลข่าวทั้งหมด (ปรับให้ตรงกับฟิลด์ในตาราง)
$news_sql = "SELECT * FROM news 
             ORDER BY is_pinned DESC, published_at DESC, created_at DESC";
$news_result = $conn->query($news_sql);

// สถิติ (ปรับให้ใช้ is_published แทน status)
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
                    <a href="index.php?page=news_add" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>
                        เพิ่มข่าวใหม่
                    </a>
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
                <table id="newsTable" class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 80px;" class="text-center">รูปภาพ</th>
                            <th>หัวข้อข่าว</th>
                            <th style="width: 120px;" class="text-center">วันที่เผยแพร่</th>
                            <th style="width: 100px;" class="text-center">ยอดอ่าน</th>
                            <th style="width: 100px;" class="text-center">สถานะ</th>
                            <th style="width: 80px;" class="text-center">ปักหมุด</th>
                            <th style="width: 180px;" class="text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($news_result && $news_result->num_rows > 0):
                            while ($news = $news_result->fetch_assoc()):
                                // กำหนด path รูปภาพ (ใช้ featured_image แทน image_url)
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
                                        <?php if (!empty($news['category_name'])): ?>
                                        <span class="badge bg-secondary ms-2" style="font-size: 0.7rem;">
                                            <?php echo htmlspecialchars($news['category_name']); ?>
                                        </span>
                                        <?php endif; ?>
                                        <br>
                                        <small class="text-muted news-content">
                                            <?php 
                                            // ใช้ excerpt ถ้ามี ไม่งั้นใช้ content
                                            $display_text = !empty($news['excerpt']) ? $news['excerpt'] : strip_tags($news['content']);
                                            echo mb_strlen($display_text) > 80 ? mb_substr($display_text, 0, 80) . '...' : $display_text;
                                            ?>
                                        </small>
                                    </div>
                                </div>
                            </td>
                            
                            <!-- วันที่เผยแพร่ -->
                            <td class="text-center">
                                <small class="text-muted">
                                    <?php 
                                    if (!empty($news['published_at'])) {
                                        echo thai_date($news['published_at'], 'short');
                                    } else {
                                        echo '<span class="text-danger">ยังไม่เผยแพร่</span>';
                                    }
                                    ?>
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
                                    <a href="index.php?page=news_edit&id=<?php echo $news['id']; ?>" 
                                       class="btn btn-outline-primary"
                                       title="แก้ไข">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    
                                    <a href="/ncadmission/index.php?page=news_detail&id=<?php echo $news['id']; ?>" 
                                       class="btn btn-outline-info"
                                       target="_blank"
                                       title="ดูข่าว">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    
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
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                <p class="mb-0">ยังไม่มีข่าวประชาสัมพันธ์</p>
                                <a href="index.php?page=news_add" class="btn btn-primary mt-3">
                                    <i class="bi bi-plus-circle me-2"></i>เพิ่มข่าวแรก
                                </a>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
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
    
    // Initialize DataTable
    const table = $('#newsTable').DataTable({
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/th.json'
        },
        order: [[2, 'desc']], // เรียงตามวันที่
        pageLength: 25,
        columnDefs: [
            { orderable: false, targets: [0, 6] } // ไม่ให้เรียงคอลัมน์รูปและปุ่ม
        ]
    });
    
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
});
</script>