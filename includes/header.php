<?php
/**
 * EDUCENTER - HỆ THỐNG WEBSITE QUẢN LÝ TRUNG TÂM NGOẠI NGỮ & GIA SƯ
 * File: includes/header.php
 * Phụ trách (Tuần 1 & Bổ sung Gói A1/A3 - Nguyễn Anh Khoa - 52400051):
 * Header dùng chung cho phân hệ Khách hàng (Client UI) - Tích hợp Auth, Kích hoạt Email, Dark Mode & PWA
 */

require_once __DIR__ . '/auth_check.php';

if (!isset($pageTitle)) {
    $pageTitle = 'Trang Chủ - Hệ Thống Đào Tạo Ngoại Ngữ & Gia Sư EduCenter';
}
if (!isset($currentPage)) {
    $currentPage = basename($_SERVER['PHP_SELF'] ?? 'index.php');
}

$currentUser = getCurrentUser();
$userTheme   = $currentUser['theme'] ?? 'light';
$userFont    = $currentUser['font_size'] ?? 'medium';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1d4ed8">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <!-- PWA Web App Manifest (Relative Path) -->
    <link rel="manifest" href="manifest.json">
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6.4 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <!-- Custom Style CSS (Relative Path) -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body data-edu-theme="<?php echo htmlspecialchars($userTheme); ?>" data-edu-font="<?php echo htmlspecialchars($userFont); ?>">

    <!-- 0. Thanh trạng thái Offline PWA (Tự động hiển thị khi mất kết nối mạng) -->
    <div id="offlineStatusBanner" class="bg-danger text-white text-center py-1 small fw-semibold d-none">
        <i class="fa-solid fa-wifi-slash me-1"></i> Bạn đang ở chế độ Ngoại tuyến (Offline Mode) — Dữ liệu đang được hiển thị từ bộ nhớ đệm PWA &amp; LocalStorage.
    </div>

    <!-- 0.1. Thanh thông báo tài khoản chưa kích hoạt Email (Account Activation Notification) -->
    <?php if ($currentUser !== null && empty($currentUser['is_activated'])): ?>
        <div class="bg-warning text-dark py-2 px-3 border-bottom border-warning-subtle shadow-sm">
            <div class="container d-flex flex-wrap justify-content-between align-items-center gap-2 small">
                <div>
                    <i class="fa-solid fa-envelope-circle-check me-2 text-danger"></i>
                    <strong>Tài khoản chưa được xác thực!</strong> Một email chứa liên kết kích hoạt đã được gửi tới
                    <strong><?php echo htmlspecialchars($currentUser['email']); ?></strong>. Vui lòng kiểm tra hộp thư để hoàn tất kích hoạt.
                </div>
                <a href="<?php echo htmlspecialchars($currentPage); ?>?activate_account=1" class="btn btn-sm btn-dark fw-semibold rounded-pill px-3">
                    <i class="fa-solid fa-check-double me-1 text-warning"></i> Bấm để kích hoạt ngay (Link Email)
                </a>
            </div>
        </div>
    <?php endif; ?>

    <!-- 0.2. Thông báo xác nhận khi vừa kích hoạt tài khoản thành công -->
    <?php if (!empty($_SESSION['activation_flash'])): ?>
        <div class="bg-success text-white py-2 px-3 text-center small fw-semibold">
            <i class="fa-solid fa-circle-check me-1"></i>
            <?php
                echo htmlspecialchars($_SESSION['activation_flash']);
                unset($_SESSION['activation_flash']);
            ?>
        </div>
    <?php endif; ?>

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
                <span class="edu-topbar-item d-none d-xl-inline-flex">
                    <i class="fa-solid fa-location-dot"></i>
                    <span>128 Nguyễn Trãi, Thanh Xuân, Hà Nội</span>
                </span>
            </div>
            <div class="d-flex align-items-center gap-2 mt-1 mt-sm-0">
                <!-- Nút chuyển đổi giao diện Sáng / Tối (User Preferences) -->
                <button type="button" id="btnThemeToggle" class="btn btn-sm btn-outline-secondary text-light border-secondary py-0 px-2" title="Chuyển đổi giao diện Sáng / Tối">
                    <i class="fa-solid fa-moon" id="themeToggleIcon"></i>
                </button>

                <?php if ($currentUser !== null): ?>
                    <div class="dropdown">
                        <a href="#" class="d-inline-flex align-items-center gap-2 text-white dropdown-toggle fw-semibold" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="<?php echo htmlspecialchars($currentUser['avatar'] ?? 'https://ui-avatars.com/api/?name=User&background=2563EB&color=fff'); ?>"
                                 alt="Avatar"
                                 width="24"
                                 height="24"
                                 class="rounded-circle object-fit-cover">
                            <span><?php echo htmlspecialchars($currentUser['display_name']); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm small">
                            <li>
                                <a class="dropdown-item" href="profile.php">
                                    <i class="fa-solid fa-user-gear me-2 text-primary"></i>Hồ sơ &amp; Tùy chỉnh
                                </a>
                            </li>
                            <?php if (($currentUser['role'] ?? '') === 'admin'): ?>
                                <li>
                                    <a class="dropdown-item" href="admin/index.php">
                                        <i class="fa-solid fa-gauge-high me-2 text-info"></i>Bảng điều khiển Admin
                                    </a>
                                </li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="logout.php">
                                    <i class="fa-solid fa-right-from-bracket me-2"></i>Đăng xuất
                                </a>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="d-inline-flex align-items-center gap-1 fw-medium me-2">
                        <i class="fa-solid fa-right-to-bracket text-info"></i>
                        <span>Đăng nhập</span>
                    </a>
                    <a href="signup.php" class="d-none d-sm-inline-flex align-items-center gap-1 fw-medium me-2">
                        <i class="fa-solid fa-user-plus text-warning"></i>
                        <span>Đăng ký</span>
                    </a>
                <?php endif; ?>

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
