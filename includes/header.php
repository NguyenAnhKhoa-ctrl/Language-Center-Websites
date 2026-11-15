<?php
/**
 * EDUCENTER - HỆ THỐNG WEBSITE QUẢN LÝ TRUNG TÂM NGOẠI NGỮ & GIA SƯ
 * File: includes/header.php
 * Phụ trách (Tuần 1 - Nguyễn Anh Khoa - 52400051):
 * Header dùng chung cho phân hệ Khách hàng (Client UI) - Sử dụng Relative URLs
 */
if (!isset($pageTitle)) {
    $pageTitle = 'Trang Chủ - Hệ Thống Đào Tạo Ngoại Ngữ & Gia Sư EduCenter';
}
if (!isset($currentPage)) {
    $currentPage = basename($_SERVER['PHP_SELF'] ?? 'index.php');
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6.4 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <!-- Custom Style CSS (Relative Path) -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>

    <!-- 1. Thanh Thông Báo Trên Cùng (Topbar) -->
    <div class="edu-topbar">
        <div class="container d-flex flex-wrap justify-content-between align-items-center">
            <div class="d-flex flex-wrap align-items-center">
                <span class="edu-topbar-item">
                    <i class="fa-solid fa-phone-volume"></i>
                    <span>Hotline: <strong>1900 6868 - 0988 123 456</strong></span>
                </span>
                <span class="edu-topbar-item d-none d-md-inline-flex">
                    <i class="fa-solid fa-envelope"></i>
                    <span>tuyensinh@educenter.edu.vn</span>
                </span>
                <span class="edu-topbar-item d-none d-lg-inline-flex">
                    <i class="fa-solid fa-location-dot"></i>
                    <span>128 Nguyễn Trãi, Thanh Xuân, Hà Nội</span>
                </span>
            </div>
            <div class="d-flex align-items-center gap-3 mt-1 mt-sm-0">
                <a href="lookup.php" class="d-inline-flex align-items-center gap-1 fw-medium">
                    <i class="fa-solid fa-magnifying-glass text-info"></i>
                    <span>Tra cứu điểm &amp; Lịch học</span>
                </a>
                <a href="admin/index.php" class="btn-admin-portal">
                    <i class="fa-solid fa-user-shield"></i>
                    <span>Quản trị viên</span>
                </a>
            </div>
        </div>
    </div>

    <!-- 2. Thanh Điều Hướng Chính (Main Navbar) -->
    <nav class="navbar navbar-expand-lg edu-navbar">
        <div class="container">
            <a class="navbar-brand edu-brand" href="index.php">
                <span class="edu-brand-icon">
                    <i class="fa-solid fa-graduation-cap"></i>
                </span>
                <span>EduCenter</span>
            </a>

            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#eduMainNavbar" aria-controls="eduMainNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="eduMainNavbar">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0 gap-1">
                    <li class="nav-item">
                        <a class="nav-link edu-nav-link <?php echo ($currentPage === 'index.php') ? 'active' : ''; ?>" href="index.php">
                            <i class="fa-solid fa-house"></i>
                            <span>Trang chủ</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link edu-nav-link <?php echo ($currentPage === 'courses.php') ? 'active' : ''; ?>" href="courses.php">
                            <i class="fa-solid fa-book-open"></i>
                            <span>Khóa học &amp; Gia sư</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link edu-nav-link <?php echo ($currentPage === 'teachers.php') ? 'active' : ''; ?>" href="teachers.php">
                            <i class="fa-solid fa-chalkboard-user"></i>
                            <span>Giảng viên</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link edu-nav-link <?php echo ($currentPage === 'lookup.php') ? 'active' : ''; ?>" href="lookup.php">
                            <i class="fa-solid fa-medal"></i>
                            <span>Tra cứu Điểm &amp; Lịch</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link edu-nav-link <?php echo ($currentPage === 'contact.php') ? 'active' : ''; ?>" href="contact.php">
                            <i class="fa-solid fa-headset"></i>
                            <span>Liên hệ</span>
                        </a>
                    </li>
                </ul>

                <div class="d-flex">
                    <a href="register.php" class="btn-trial-cta">
                        <i class="fa-solid fa-pen-to-square"></i>
                        <span>Đăng ký học thử</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>
