<?php
/**
 * EDUCENTER - HỆ THỐNG WEBSITE QUẢN LÝ TRUNG TÂM NGOẠI NGỮ & GIA SƯ
 * File: admin/includes/admin_footer.php
 * Phụ trách (Tuần 1 - Lý Hạo Danh - 52400175):
 * Footer dùng chung & Script xử lý đóng/mở Sidebar cho Phân hệ Quản trị (Admin Dashboard)
 */
?>
        </div> <!-- /.container-fluid -->
    </div> <!-- /#page-content-wrapper -->
</div> <!-- /#wrapper -->

<!-- Bootstrap 5.3 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    var toggleBtn = document.getElementById("menu-toggle");
    var wrapper = document.getElementById("wrapper");
    if (toggleBtn && wrapper) {
        toggleBtn.addEventListener("click", function (e) {
            e.preventDefault();
            wrapper.classList.toggle("toggled");
        });
    }
});
</script>
</body>
</html>
