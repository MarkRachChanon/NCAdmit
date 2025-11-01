<!-- jQuery (Local) -->
<script src="../assets/js/jquery-3.7.0.min.js"></script>

<!-- Bootstrap 5 JS Bundle (Local) -->
<script src="../assets/js/bootstrap.bundle.min.js"></script>

<!-- DataTables JS (Local) -->
<script src="../assets/js/jquery.dataTables.min.js"></script>
<script src="../assets/js/dataTables.bootstrap5.min.js"></script>
<script src="../assets/js/dataTables.responsive.min.js"></script>
<script src="../assets/js/responsive.bootstrap5.min.js"></script>

<!-- SweetAlert2 JS (Local) -->
<script src="../assets/js/sweetalert2.all.min.js"></script>

<!-- Chart.js (Local) -->
<script src="../assets/js/chart.umd.min.js"></script>

<!-- Admin Custom JS -->
<script src="assets/js/admin.js"></script>

<script>
// Sidebar Toggle
document.getElementById('sidebarToggle')?.addEventListener('click', function() {
    document.getElementById('adminSidebar').classList.toggle('collapsed');
    document.querySelector('.admin-content').classList.toggle('expanded');
});

// Auto-hide alerts after 5 seconds
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(alert => {
        // ต้องสร้าง instance ของ Bootstrap Alert ด้วย Element นั้นๆ
        const bsAlert = new bootstrap.Alert(alert); 
        bsAlert.close();
    });
}, 5000);
</script>

</body>
</html>