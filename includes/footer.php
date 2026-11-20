<?php
/**
 * EDUCENTER - HỆ THỐNG WEBSITE QUẢN LÝ TRUNG TÂM NGOẠI NGỮ & GIA SƯ
 * File: includes/footer.php
 * Phụ trách (Tuần 1 - Nguyễn Anh Khoa - 52400051):
 * Footer dùng chung cho phân hệ Khách hàng (Client UI) - Sử dụng Relative URLs
 */
?>
    <!-- Footer -->
    <footer class="edu-footer">
        <div class="container">
            <div class="row g-4">
                <!-- Cột 1: Thông tin thương hiệu -->
                <div class="col-lg-4 col-md-6">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="edu-brand-icon">
                            <i class="fa-solid fa-graduation-cap"></i>
                        </span>
                        <span class="fs-4 fw-bold text-white">EduCenter</span>
                    </div>
                    <p class="mb-0">
                        Hệ thống đào tạo Ngoại ngữ &amp; Kết nối Gia sư 1-1 chuẩn Quốc tế. Cam kết chuẩn đầu ra bằng hợp đồng pháp lý và minh bạch tiến độ học tập 24/7.
                    </p>
                </div>

                <!-- Cột 2: Liên kết nhanh -->
                <div class="col-lg-2 col-md-6">
                    <h5 class="edu-footer-title">Liên Kết Nhanh</h5>
                    <ul class="edu-footer-links">
                        <li><a href="index.php"><i class="fa-solid fa-angle-right small"></i> Trang chủ</a></li>
                        <li><a href="courses.php"><i class="fa-solid fa-angle-right small"></i> Khóa học &amp; Gia sư</a></li>
                        <li><a href="teachers.php"><i class="fa-solid fa-angle-right small"></i> Đội ngũ Giảng viên</a></li>
                        <li><a href="lookup.php"><i class="fa-solid fa-angle-right small"></i> Tra cứu Điểm &amp; Lịch</a></li>
                        <li><a href="register.php"><i class="fa-solid fa-angle-right small"></i> Đăng ký Test đầu vào</a></li>
                    </ul>
                </div>

                <!-- Cột 3: Chương trình đào tạo -->
                <div class="col-lg-3 col-md-6">
                    <h5 class="edu-footer-title">Chương Trình Đào Tạo</h5>
                    <ul class="edu-footer-links">
                        <li><a href="courses.php"><i class="fa-solid fa-check text-info small"></i> Luyện thi IELTS 6.5 - 8.0+</a></li>
                        <li><a href="courses.php"><i class="fa-solid fa-check text-info small"></i> Luyện thi TOEIC 4 Kỹ năng</a></li>
                        <li><a href="courses.php"><i class="fa-solid fa-check text-info small"></i> Tiếng Anh Giao Tiếp Phản Xạ</a></li>
                        <li><a href="courses.php"><i class="fa-solid fa-check text-info small"></i> Gia sư 1 Kèm 1 Theo Yêu Cầu</a></li>
                        <li><a href="courses.php"><i class="fa-solid fa-check text-info small"></i> Tiếng Anh Thiếu Nhi Cambridge</a></li>
                    </ul>
                </div>

                <!-- Cột 4: Hệ thống cơ sở -->
                <div class="col-lg-3 col-md-6">
                    <h5 class="edu-footer-title">Hệ Thống Cơ Sở</h5>
                    <ul class="list-unstyled small">
                        <li class="mb-2">
                            <i class="fa-solid fa-building text-warning me-2"></i>
                            <strong class="text-white">Cơ sở 1 (Hà Nội):</strong><br>
                            128 Nguyễn Trãi, Quận Thanh Xuân, Hà Nội
                        </li>
                        <li class="mb-2">
                            <i class="fa-solid fa-building text-warning me-2"></i>
                            <strong class="text-white">Cơ sở 2 (TP.HCM):</strong><br>
                            45 Võ Văn Tần, Quận 3, TP. Hồ Chí Minh
                        </li>
                        <li class="mb-2">
                            <i class="fa-solid fa-phone text-info me-2"></i>
                            Hotline: <strong class="text-white">1900 6868 - 0988 123 456</strong>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="edu-footer-bottom d-flex flex-wrap justify-content-between align-items-center">
                <div>
                    &copy; <?php echo date('Y'); ?> <strong>EduCenter Portal</strong> - Hệ thống Quản lý Trung tâm Ngoại ngữ &amp; Gia sư.
                </div>
                <div class="mt-2 mt-sm-0">
                    Thực hiện bởi: <span class="text-white">Nguyễn Anh Khoa (52400051) &amp; Lý Hạo Danh (52400175)</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5.3 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom Main JS (Relative Path) -->
    <script src="assets/js/main.js"></script>
</body>
</html>
