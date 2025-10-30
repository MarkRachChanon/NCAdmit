<?php

/**
 * หน้าจัดการแกลเลอรี่
 * NC-Admission - Nakhon Pathom College Admission System
 */

// ตรวจสอบสิทธิ์การเข้าถึง
if (!check_page_permission('gallery_manage', $admin_role)) {
    header('Location: index.php?page=dashboard');
    exit();
}

// ดึงข้อมูลแกลเลอรี่ทั้งหมด
$gallery_sql = "SELECT * FROM gallery ORDER BY sort_order DESC, created_at DESC";
$gallery_result = $conn->query($gallery_sql);

// สถิติ
$stats_sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN is_published = 1 THEN 1 ELSE 0 END) as published,
                SUM(CASE WHEN is_published = 0 THEN 1 ELSE 0 END) as unpublished,
                SUM(views) as total_views
              FROM gallery";
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
                        <i class="bi bi-images text-primary me-2"></i>
                        จัดการแกลเลอรี่
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="index.php?page=dashboard">หน้าแรก</a></li>
                            <li class="breadcrumb-item active">จัดการแกลเลอรี่</li>
                        </ol>
                    </nav>
                </div>

                <div>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadGalleryModal">
                        <i class="bi bi-cloud-upload me-2"></i>
                        อัปโหลดรูปภาพ
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
                            <h6 class="text-white-50 mb-1">รูปภาพทั้งหมด</h6>
                            <h2 class="mb-0"><?php echo number_format($stats['total']); ?></h2>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-images"></i>
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
                            <h6 class="text-white-50 mb-1">ซ่อนไว้</h6>
                            <h2 class="mb-0"><?php echo number_format($stats['unpublished']); ?></h2>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-eye-slash"></i>
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
                            <h6 class="text-white-50 mb-1">ยอดเข้าชมรวม</h6>
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
                <div class="col-md-6">
                    <label class="form-label fw-bold">
                        <i class="bi bi-search"></i> ค้นหา
                    </label>
                    <input type="text" class="form-control" id="searchInput"
                        placeholder="ค้นหาชื่อรูปภาพ, คำอธิบาย...">
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">
                        <i class="bi bi-funnel"></i> สถานะ
                    </label>
                    <select class="form-select" id="filterStatus">
                        <option value="">ทั้งหมด</option>
                        <option value="1">เผยแพร่</option>
                        <option value="0">ซ่อน</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <button type="button" class="btn btn-outline-secondary w-100" id="clearFilters">
                        <i class="bi bi-x-circle me-2"></i>ล้างตัวกรอง
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Gallery Grid -->
    <div class="row" id="galleryGrid">
        <?php
        if ($gallery_result && $gallery_result->num_rows > 0):
            while ($item = $gallery_result->fetch_assoc()):
        ?>
                <div class="col-md-4 col-lg-3 mb-4 gallery-item" data-status="<?php echo $item['is_published']; ?>">
                    <div class="card h-100 shadow-sm hover-lift">
                        <!-- รูปภาพ -->
                        <div class="position-relative" style="height: 200px; overflow: hidden;">
                            <?php
                            $image_url = $item['image_url'];

                            // ตรวจสอบว่าเป็น URL ภายนอกหรือไม่
                            if (strpos($image_url, 'http://') === 0 || strpos($image_url, 'https://') === 0) {
                                // เป็น URL ภายนอก
                                $img_src = $image_url;
                            } else {
                                // เป็นไฟล์ local - ใช้ absolute path จาก root
                                $img_src = '/ncadmission/' . $image_url;
                            }
                            ?>
                            <img src="<?php echo htmlspecialchars($img_src); ?>"
                                class="card-img-top h-100 w-100"
                                style="object-fit: cover;"
                                alt="<?php echo htmlspecialchars($item['title']); ?>"
                                loading="lazy"
                                onerror="this.onerror=null; this.src='https://via.placeholder.com/800x600/e9ecef/6c757d?text=No+Image';">

                            <!-- Badge สถานะ -->
                            <div class="position-absolute top-0 start-0 m-2">
                                <?php if ($item['is_published']): ?>
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle me-1"></i>เผยแพร่
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">
                                        <i class="bi bi-eye-slash me-1"></i>ซ่อน
                                    </span>
                                <?php endif; ?>
                            </div>

                            <!-- จำนวนการดู -->
                            <div class="position-absolute top-0 end-0 m-2">
                                <span class="badge bg-dark bg-opacity-75">
                                    <i class="bi bi-eye me-1"></i><?php echo number_format($item['views']); ?>
                                </span>
                            </div>
                        </div>

                        <!-- เนื้อหา -->
                        <div class="card-body">
                            <h6 class="card-title mb-2">
                                <?php echo htmlspecialchars($item['title']); ?>
                            </h6>
                            <p class="card-text text-muted small mb-2">
                                <?php
                                $desc = htmlspecialchars($item['description']);
                                echo mb_strlen($desc) > 60 ? mb_substr($desc, 0, 60) . '...' : $desc;
                                ?>
                            </p>
                            <small class="text-muted">
                                <i class="bi bi-calendar me-1"></i>
                                <?php echo thai_date($item['created_at']); ?>
                            </small>
                        </div>

                        <!-- ปุ่มจัดการ -->
                        <div class="card-footer bg-white border-top-0">
                            <div class="d-flex gap-2">
                                <button type="button"
                                    class="btn btn-sm btn-outline-primary flex-fill btn-edit-gallery"
                                    data-id="<?php echo $item['id']; ?>"
                                    data-title="<?php echo htmlspecialchars($item['title']); ?>"
                                    data-description="<?php echo htmlspecialchars($item['description']); ?>"
                                    data-sort="<?php echo $item['sort_order']; ?>"
                                    data-published="<?php echo $item['is_published']; ?>"
                                    data-image-url="<?php echo htmlspecialchars($item['image_url']); ?>">
                                    <i class="bi bi-pencil"></i> แก้ไข
                                </button>

                                <button type="button"
                                    class="btn btn-sm btn-outline-danger btn-delete-gallery"
                                    data-id="<?php echo $item['id']; ?>"
                                    data-title="<?php echo htmlspecialchars($item['title']); ?>">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php
            endwhile;
        else:
            ?>
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body text-center text-muted py-5">
                        <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                        <p class="mb-0">ยังไม่มีรูปภาพในแกลเลอรี่</p>
                        <button type="button" class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#uploadGalleryModal">
                            <i class="bi bi-cloud-upload me-2"></i>อัปโหลดรูปภาพแรก
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal อัปโหลดรูปภาพ -->
<div class="modal fade" id="uploadGalleryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-cloud-upload me-2"></i>
                    อัปโหลดรูปภาพ
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="uploadGalleryForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>คำแนะนำ:</strong> ขนาดรูปภาพแนะนำ 1200x800 px, รองรับไฟล์ JPG, PNG, GIF (ไม่เกิน 5MB)
                    </div>

                    <div class="mb-3">
                        <label for="upload_image" class="form-label">
                            เลือกรูปภาพ <span class="text-danger">*</span>
                        </label>
                        <input type="file"
                            class="form-control"
                            id="upload_image"
                            name="image"
                            accept="image/jpeg,image/png,image/gif">
                        <div class="form-text">หรือใช้ URL รูปภาพภายนอก</div>
                    </div>

                    <div class="mb-3">
                        <label for="upload_image_url" class="form-label">URL รูปภาพ (ถ้ามี)</label>
                        <input type="url"
                            class="form-control"
                            id="upload_image_url"
                            name="image_url"
                            placeholder="https://example.com/image.jpg">
                    </div>

                    <div class="mb-3">
                        <label for="upload_title" class="form-label">
                            ชื่อรูปภาพ <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                            class="form-control"
                            id="upload_title"
                            name="title"
                            required
                            placeholder="เช่น งานวันสถาปนาวิทยาลัย">
                    </div>

                    <div class="mb-3">
                        <label for="upload_description" class="form-label">คำอธิบาย</label>
                        <textarea class="form-control"
                            id="upload_description"
                            name="description"
                            rows="3"
                            placeholder="คำอธิบายรูปภาพ (ถ้ามี)"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="upload_sort_order" class="form-label">ลำดับการแสดง</label>
                            <input type="number"
                                class="form-control"
                                id="upload_sort_order"
                                name="sort_order"
                                min="0"
                                value="0">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">สถานะ</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input"
                                    type="checkbox"
                                    id="upload_is_published"
                                    name="is_published"
                                    value="1"
                                    checked>
                                <label class="form-check-label" for="upload_is_published">
                                    เผยแพร่
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>ยกเลิก
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-cloud-upload me-2"></i>อัปโหลด
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal แก้ไขรูปภาพ -->
<div class="modal fade" id="editGalleryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="bi bi-pencil-square me-2"></i>
                    แก้ไขรูปภาพ
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editGalleryForm" enctype="multipart/form-data">
                <input type="hidden" id="edit_id" name="id">
                <input type="hidden" id="edit_old_image_url" name="old_image_url">

                <div class="modal-body">
                    <!-- แสดงรูปภาพปัจจุบัน -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">รูปภาพปัจจุบัน</label>
                        <div class="border rounded p-3 bg-light text-center">
                            <img id="edit_current_image"
                                src=""
                                alt="Current Image"
                                style="max-width: 100%; max-height: 200px; object-fit: contain;"
                                class="mb-2">
                            <div>
                                <small id="edit_current_url" class="text-muted"></small>
                            </div>
                        </div>
                    </div>

                    <!-- เลือกรูปภาพใหม่ -->
                    <div class="mb-3">
                        <label for="edit_image" class="form-label">
                            <i class="bi bi-image"></i> เปลี่ยนรูปภาพ (ถ้าต้องการ)
                        </label>
                        <input type="file"
                            class="form-control"
                            id="edit_image"
                            name="image"
                            accept="image/jpeg,image/png,image/gif">
                        <div class="form-text">
                            <i class="bi bi-info-circle"></i> เลือกไฟล์ใหม่เพื่อเปลี่ยนรูปภาพ หรือใช้ URL ด้านล่าง
                        </div>
                    </div>

                    <!-- หรือใช้ URL -->
                    <div class="mb-3">
                        <label for="edit_image_url" class="form-label">
                            <i class="bi bi-link-45deg"></i> หรือใช้ URL รูปภาพ
                        </label>
                        <input type="url"
                            class="form-control"
                            id="edit_image_url"
                            name="image_url"
                            placeholder="https://example.com/image.jpg">
                        <div class="form-text">
                            <i class="bi bi-info-circle"></i> ใส่ URL เพื่อใช้รูปภาพจากภายนอก (จะแทนที่รูปภาพปัจจุบัน)
                        </div>
                    </div>

                    <hr>

                    <!-- ชื่อรูปภาพ -->
                    <div class="mb-3">
                        <label for="edit_title" class="form-label">
                            ชื่อรูปภาพ <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                            class="form-control"
                            id="edit_title"
                            name="title"
                            required>
                    </div>

                    <!-- คำอธิบาย -->
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">คำอธิบาย</label>
                        <textarea class="form-control"
                            id="edit_description"
                            name="description"
                            rows="3"></textarea>
                    </div>

                    <!-- ลำดับการแสดง -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_sort_order" class="form-label">ลำดับการแสดง</label>
                            <input type="number"
                                class="form-control"
                                id="edit_sort_order"
                                name="sort_order"
                                min="0">
                        </div>

                        <!-- สถานะ -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">สถานะ</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input"
                                    type="checkbox"
                                    id="edit_is_published"
                                    name="is_published"
                                    value="1">
                                <label class="form-check-label" for="edit_is_published">
                                    เผยแพร่
                                </label>
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
    .hover-lift {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .hover-lift:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }

    .gallery-item {
        transition: opacity 0.3s ease;
    }

    .gallery-item.hidden {
        display: none;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Gallery Management Loaded');

        // ==================== Search & Filter ====================
        const searchInput = document.getElementById('searchInput');
        const filterStatus = document.getElementById('filterStatus');
        const galleryItems = document.querySelectorAll('.gallery-item');

        function filterGallery() {
            const searchTerm = searchInput.value.toLowerCase();
            const statusValue = filterStatus.value;

            galleryItems.forEach(item => {
                const title = item.querySelector('.card-title').textContent.toLowerCase();
                const description = item.querySelector('.card-text').textContent.toLowerCase();
                const status = item.dataset.status;

                const matchSearch = title.includes(searchTerm) || description.includes(searchTerm);
                const matchStatus = statusValue === '' || status === statusValue;

                if (matchSearch && matchStatus) {
                    item.classList.remove('hidden');
                } else {
                    item.classList.add('hidden');
                }
            });
        }

        if (searchInput) {
            searchInput.addEventListener('keyup', filterGallery);
        }

        if (filterStatus) {
            filterStatus.addEventListener('change', filterGallery);
        }

        // Clear Filters
        document.getElementById('clearFilters')?.addEventListener('click', function() {
            searchInput.value = '';
            filterStatus.value = '';
            filterGallery();
        });

        // ==================== Upload Gallery ====================
        const uploadForm = document.getElementById('uploadGalleryForm');
        if (uploadForm) {
            uploadForm.addEventListener('submit', function(e) {
                e.preventDefault();
                console.log('Uploading gallery');

                // ตรวจสอบว่ามีรูปหรือ URL อย่างน้อย 1 อย่าง
                const fileInput = document.getElementById('upload_image');
                const urlInput = document.getElementById('upload_image_url');

                if (!fileInput.files.length && !urlInput.value) {
                    Swal.fire({
                        icon: 'error',
                        title: 'กรุณาเลือกรูปภาพ',
                        text: 'กรุณาเลือกรูปภาพหรือระบุ URL'
                    });
                    return;
                }

                if (!uploadForm.checkValidity()) {
                    e.stopPropagation();
                    uploadForm.classList.add('was-validated');
                    return;
                }

                Swal.fire({
                    title: 'กำลังอัปโหลด...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                const formData = new FormData(uploadForm);

                fetch('api/gallery_upload.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Upload Response:', data);

                        if (data.success) {
                            const modal = bootstrap.Modal.getInstance(document.getElementById('uploadGalleryModal'));
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

        // ==================== Edit Gallery ====================
        document.addEventListener('click', function(e) {
            if (e.target.closest('.btn-edit-gallery')) {
                const btn = e.target.closest('.btn-edit-gallery');

                // ข้อมูลพื้นฐาน
                document.getElementById('edit_id').value = btn.dataset.id;
                document.getElementById('edit_title').value = btn.dataset.title;
                document.getElementById('edit_description').value = btn.dataset.description;
                document.getElementById('edit_sort_order').value = btn.dataset.sort;
                document.getElementById('edit_is_published').checked = btn.dataset.published == 1;

                // แสดงรูปภาพปัจจุบัน
                const imageUrl = btn.dataset.imageUrl;
                document.getElementById('edit_old_image_url').value = imageUrl;

                // กำหนด src ของรูปภาพ
                let imgSrc;
                if (imageUrl.indexOf('http://') === 0 || imageUrl.indexOf('https://') === 0) {
                    imgSrc = imageUrl;
                } else {
                    imgSrc = '/ncadmission/' + imageUrl;
                }

                document.getElementById('edit_current_image').src = imgSrc;
                document.getElementById('edit_current_url').textContent = imageUrl;

                // Reset input fields
                document.getElementById('edit_image').value = '';
                document.getElementById('edit_image_url').value = '';

                const modal = new bootstrap.Modal(document.getElementById('editGalleryModal'));
                modal.show();
            }
        });

        const editForm = document.getElementById('editGalleryForm');
        if (editForm) {
            editForm.addEventListener('submit', function(e) {
                e.preventDefault();
                console.log('Editing gallery');

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

                fetch('api/gallery_edit.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Edit Response:', data);

                        if (data.success) {
                            const modal = bootstrap.Modal.getInstance(document.getElementById('editGalleryModal'));
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

        // ==================== Delete Gallery ====================
        document.addEventListener('click', function(e) {
            if (e.target.closest('.btn-delete-gallery')) {
                e.preventDefault();

                const btn = e.target.closest('.btn-delete-gallery');
                const galleryId = btn.dataset.id;
                const galleryTitle = btn.dataset.title;

                Swal.fire({
                    title: 'ยืนยันการลบ?',
                    html: `คุณต้องการลบรูปภาพ<br><strong class="text-danger">"${galleryTitle}"</strong><br>ใช่หรือไม่?`,
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

                        fetch('api/gallery_delete.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    gallery_id: parseInt(galleryId)
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
        document.getElementById('uploadGalleryModal')?.addEventListener('hidden.bs.modal', function() {
            uploadForm.reset();
            uploadForm.classList.remove('was-validated');
        });

        document.getElementById('editGalleryModal')?.addEventListener('hidden.bs.modal', function() {
            editForm.reset();
            editForm.classList.remove('was-validated');
        });
    });
</script>