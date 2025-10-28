<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<script src="../assets/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

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