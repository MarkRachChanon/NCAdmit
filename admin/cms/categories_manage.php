<?php
/**
 * หน้าจัดการประเภทวิชา (Department Categories)
 * NC-Admission - Nakhon Pathom College Admission System
 */

// ตรวจสอบสิทธิ์การเข้าถึง
if (!in_array($admin_role, ['superadmin', 'admin'])) {
    header('Location: index.php?page=dashboard');
    exit();
}

// ดึงข้อมูล Categories พร้อมนับจำนวนสาขา
$categories_sql = "SELECT 
                    dc.*,
                    COUNT(d.id) as department_count
                   FROM department_categories dc
                   LEFT JOIN departments d ON dc.id = d.category_id
                   GROUP BY dc.id
                   ORDER BY dc.sort_order ASC";
$categories_result = $conn->query($categories_sql);

// สถิติ
$stats_sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive
              FROM department_categories";
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
                        <i class="bi bi-folder text-primary me-2"></i>
                        จัดการประเภทวิชา
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="index.php?page=dashboard">หน้าแรก</a></li>
                            <li class="breadcrumb-item active">จัดการประเภทวิชา</li>
                        </ol>
                    </nav>
                </div>
                
                <div class="d-flex gap-2">
                    <a href="index.php?page=departments_manage" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>
                        กลับ
                    </a>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                        <i class="bi bi-plus-circle me-2"></i>
                        เพิ่มประเภทวิชา
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- สถิติ -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card stat-card bg-gradient-primary text-white border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">ประเภทวิชาทั้งหมด</h6>
                            <h2 class="mb-0"><?php echo number_format($stats['total']); ?></h2>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-folder"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="card stat-card bg-gradient-success text-white border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">เปิดใช้งาน</h6>
                            <h2 class="mb-0"><?php echo number_format($stats['active']); ?></h2>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="card stat-card bg-gradient-warning text-white border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">ปิดใช้งาน</h6>
                            <h2 class="mb-0"><?php echo number_format($stats['inactive']); ?></h2>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-x-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ตารางข้อมูล -->
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">
                <i class="bi bi-list-ul me-2"></i>
                รายการประเภทวิชา
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="categoriesTable" class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 80px;">ลำดับ</th>
                            <th>ชื่อประเภทวิชา</th>
                            <th>คำอธิบาย</th>
                            <th style="width: 120px;" class="text-center">จำนวนสาขา</th>
                            <th style="width: 100px;" class="text-center">สถานะ</th>
                            <th style="width: 150px;" class="text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($categories_result && $categories_result->num_rows > 0):
                            while ($cat = $categories_result->fetch_assoc()):
                        ?>
                        <tr>
                            <td class="text-center">
                                <span class="badge bg-secondary"><?php echo $cat['sort_order']; ?></span>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($cat['name']); ?></strong>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?php echo htmlspecialchars($cat['description']); ?>
                                </small>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info">
                                    <?php echo number_format($cat['department_count']); ?> สาขา
                                </span>
                            </td>
                            <td class="text-center">
                                <?php if ($cat['is_active']): ?>
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle me-1"></i>เปิด
                                </span>
                                <?php else: ?>
                                <span class="badge bg-secondary">
                                    <i class="bi bi-x-circle me-1"></i>ปิด
                                </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" 
                                            class="btn btn-outline-primary btn-edit-category" 
                                            data-id="<?php echo $cat['id']; ?>"
                                            data-name="<?php echo htmlspecialchars($cat['name']); ?>"
                                            data-description="<?php echo htmlspecialchars($cat['description']); ?>"
                                            data-sort="<?php echo $cat['sort_order']; ?>"
                                            data-active="<?php echo $cat['is_active']; ?>"
                                            title="แก้ไข">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    
                                    <button type="button" 
                                            class="btn btn-outline-danger btn-delete-category" 
                                            data-id="<?php echo $cat['id']; ?>" 
                                            data-name="<?php echo htmlspecialchars($cat['name']); ?>"
                                            data-count="<?php echo $cat['department_count']; ?>"
                                            title="ลบ"
                                            <?php echo ($cat['department_count'] > 0) ? 'disabled' : ''; ?>>
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
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                <p class="mb-0">ยังไม่มีข้อมูลประเภทวิชา</p>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal เพิ่มประเภทวิชา -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-plus-circle me-2"></i>
                    เพิ่มประเภทวิชาใหม่
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="addCategoryForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="add_name" class="form-label">
                            ชื่อประเภทวิชา <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="add_name" 
                               name="name" 
                               required
                               placeholder="เช่น บริหารธุรกิจ">
                    </div>
                    
                    <div class="mb-3">
                        <label for="add_description" class="form-label">คำอธิบาย</label>
                        <textarea class="form-control" 
                                  id="add_description" 
                                  name="description" 
                                  rows="3"
                                  placeholder="คำอธิบายประเภทวิชา (ถ้ามี)"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="add_sort_order" class="form-label">
                            ลำดับการแสดง <span class="text-danger">*</span>
                        </label>
                        <input type="number" 
                               class="form-control" 
                               id="add_sort_order" 
                               name="sort_order" 
                               min="0"
                               value="<?php echo ($categories_result ? $categories_result->num_rows + 1 : 1); ?>"
                               required>
                        <small class="text-muted">ตัวเลขน้อยจะแสดงก่อน</small>
                    </div>
                    
                    <div class="form-check form-switch">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="add_is_active" 
                               name="is_active" 
                               value="1"
                               checked>
                        <label class="form-check-label" for="add_is_active">
                            เปิดใช้งาน
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>ยกเลิก
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>บันทึก
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal แก้ไขประเภทวิชา -->
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="bi bi-pencil-square me-2"></i>
                    แก้ไขประเภทวิชา
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editCategoryForm">
                <input type="hidden" id="edit_id" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">
                            ชื่อประเภทวิชา <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="edit_name" 
                               name="name" 
                               required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">คำอธิบาย</label>
                        <textarea class="form-control" 
                                  id="edit_description" 
                                  name="description" 
                                  rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_sort_order" class="form-label">
                            ลำดับการแสดง <span class="text-danger">*</span>
                        </label>
                        <input type="number" 
                               class="form-control" 
                               id="edit_sort_order" 
                               name="sort_order" 
                               min="0"
                               required>
                        <small class="text-muted">ตัวเลขน้อยจะแสดงก่อน</small>
                    </div>
                    
                    <div class="form-check form-switch">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="edit_is_active" 
                               name="is_active" 
                               value="1">
                        <label class="form-check-label" for="edit_is_active">
                            เปิดใช้งาน
                        </label>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Categories Management Loaded');
    
    // Initialize DataTable
    const table = $('#categoriesTable').DataTable({
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/th.json'
        },
        order: [[0, 'asc']],
        pageLength: 25,
        columnDefs: [
            { orderable: false, targets: [5] }
        ]
    });
    
    // ==================== Add Category ====================
    const addForm = document.getElementById('addCategoryForm');
    if (addForm) {
        addForm.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Adding category');
            
            if (!addForm.checkValidity()) {
                e.stopPropagation();
                addForm.classList.add('was-validated');
                return;
            }
            
            const formData = new FormData(addForm);
            
            fetch('api/categories_add.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log('Add Response:', data);
                
                if (data.success) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('addCategoryModal'));
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
    
    // ==================== Edit Category ====================
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-edit-category')) {
            const btn = e.target.closest('.btn-edit-category');
            
            document.getElementById('edit_id').value = btn.dataset.id;
            document.getElementById('edit_name').value = btn.dataset.name;
            document.getElementById('edit_description').value = btn.dataset.description;
            document.getElementById('edit_sort_order').value = btn.dataset.sort;
            document.getElementById('edit_is_active').checked = btn.dataset.active == 1;
            
            const modal = new bootstrap.Modal(document.getElementById('editCategoryModal'));
            modal.show();
        }
    });
    
    const editForm = document.getElementById('editCategoryForm');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Editing category');
            
            if (!editForm.checkValidity()) {
                e.stopPropagation();
                editForm.classList.add('was-validated');
                return;
            }
            
            const formData = new FormData(editForm);
            
            fetch('api/categories_edit.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log('Edit Response:', data);
                
                if (data.success) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editCategoryModal'));
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
    
    // ==================== Delete Category ====================
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-delete-category')) {
            e.preventDefault();
            
            const btn = e.target.closest('.btn-delete-category');
            const catId = btn.dataset.id;
            const catName = btn.dataset.name;
            const deptCount = parseInt(btn.dataset.count);
            
            if (deptCount > 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'ไม่สามารถลบได้',
                    html: `ประเภทวิชา <strong>"${catName}"</strong> มีสาขาวิชาอยู่ <strong>${deptCount}</strong> สาขา<br>กรุณาย้ายหรือลบสาขาวิชาก่อน`
                });
                return;
            }
            
            Swal.fire({
                title: 'ยืนยันการลบ?',
                html: `คุณต้องการลบประเภทวิชา<br><strong class="text-danger">"${catName}"</strong><br>ใช่หรือไม่?`,
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
                    
                    fetch('api/categories_delete.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            cat_id: parseInt(catId)
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
    
    // Reset form when modal closes
    document.getElementById('addCategoryModal').addEventListener('hidden.bs.modal', function() {
        addForm.reset();
        addForm.classList.remove('was-validated');
    });
    
    document.getElementById('editCategoryModal').addEventListener('hidden.bs.modal', function() {
        editForm.reset();
        editForm.classList.remove('was-validated');
    });
});
</script>