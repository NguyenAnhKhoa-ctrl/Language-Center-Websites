<?php
/**
 * EDUCENTER - HỆ THỐNG WEBSITE QUẢN LÝ TRUNG TÂM NGOẠI NGỮ & GIA SƯ
 * File: admin/includes/admin_footer.php
 * Phụ trách (Tuần 1 & Bổ sung Auto-Resize Mobile UI - Lý Hạo Danh & Nguyễn Anh Khoa):
 * Footer dùng chung, Tự động thu phóng Sidebar & Auto-Resize UI cho Phân hệ Quản trị (Admin Dashboard)
 */
?>
        </div> <!-- /.container-fluid -->
    </div> <!-- /#page-content-wrapper -->
</div> <!-- /#wrapper -->

<!-- Lớp phủ mờ hỗ trợ chạm đóng Sidebar trên màn hình điện thoại -->
<div class="edu-sidebar-backdrop" id="adminSidebarBackdrop"></div>

<!-- Bootstrap 5.3 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Shared Auto-Resize Mobile UI & Theme Engine -->
<script src="../assets/js/main.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    var toggleBtn = document.getElementById("menu-toggle");
    var wrapper = document.getElementById("wrapper");
    var backdrop = document.getElementById("adminSidebarBackdrop");

    function syncBackdropState() {
        if (!wrapper || !backdrop) return;
        var isMobile = window.innerWidth < 768;
        if (isMobile && wrapper.classList.contains("toggled")) {
            backdrop.classList.add("active");
        } else {
            backdrop.classList.remove("active");
        }
    }

    if (toggleBtn && wrapper) {
        toggleBtn.addEventListener("click", function (e) {
            e.preventDefault();
            wrapper.classList.toggle("toggled");
            syncBackdropState();
        });
    }

    if (backdrop && wrapper) {
        backdrop.addEventListener("click", function () {
            wrapper.classList.remove("toggled");
            syncBackdropState();
        });
    }

    window.addEventListener("resize", function () {
        if (window.innerWidth >= 768 && backdrop) {
            backdrop.classList.remove("active");
        } else {
            syncBackdropState();
        }
    }, { passive: true });
});
</script>
</body>
</html>
