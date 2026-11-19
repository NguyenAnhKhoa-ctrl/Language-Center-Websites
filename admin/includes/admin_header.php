<?php
/**
 * EDUCENTER - HỆ THỐNG WEBSITE QUẢN LÝ TRUNG TÂM NGOẠI NGỮ & GIA SƯ
 * File: admin/includes/admin_header.php
 * Phụ trách (Tuần 1 & Bổ sung Gói A2 - Lý Hạo Danh & Nguyễn Anh Khoa):
 * Header & Sidebar điều hướng dùng chung cho Phân hệ Quản trị (Admin Dashboard)
 */

require_once __DIR__ . '/../../includes/auth_check.php';

if (!isset($admin_title)) {
    $admin_title = 'Quản Trị Hệ Thống - EduCenter';
}
if (!isset($active_menu)) {
    $active_menu = 'dashboard';
}

$adminUser  = getCurrentUser();
$adminName  = $adminUser['display_name'] ?? 'Quản Trị Viên';
$adminEmail = $adminUser['email'] ?? 'admin@educenter.edu.vn';
$adminAva   = $adminUser['avatar'] ?? 'https://ui-avatars.com/api/?name=Admin+Manager&background=1E40AF&color=fff';
$adminTheme = $adminUser['theme'] ?? 'light';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($admin_title); ?> | EduCenter Admin</title>
    <!-- Google Fonts Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6.4 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <!-- Custom Style CSS (Relative Path) -->
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light" data-edu-theme="<?php echo htmlspecialchars($adminTheme); ?>">
<div class="d-flex" id="wrapper">
    <!-- SIDEBAR QUẢN TRỊ -->
    <div class="bg-dark text-white shadow" id="sidebar-wrapper">
        <div class="sidebar-heading text-center py-4 fs-4 fw-bold border-bottom border-secondary">
            <i class="fa-solid fa-graduation-cap me-2 text-primary"></i>EduCenter
            <div class="small text-secondary fw-normal fs-6">Admin Management</div>
        </div>

        <div class="list-group list-group-flush my-3">
            <a href="index.php" class="list-group-item list-group-item-action bg-transparent text-white <?php echo ($active_menu === 'dashboard') ? 'active' : ''; ?>">
                <i class="fa-solid fa-chart-pie me-2 text-info"></i> Bảng điều khiển
            </a>
            <a href="students.php" class="list-group-item list-group-item-action bg-transparent text-white <?php echo ($active_menu === 'students') ? 'active' : ''; ?>">
                <i class="fa-solid fa-users me-2 text-warning"></i> Quản lý Học viên
            </a>
            <a href="teachers.php" class="list-group-item list-group-item-action bg-transparent text-white <?php echo ($active_menu === 'teachers') ? 'active' : ''; ?>">
                <i class="fa-solid fa-chalkboard-user me-2 text-success"></i> Quản lý Giảng viên
            </a>
            <a href="courses.php" class="list-group-item list-group-item-action bg-transparent text-white <?php echo ($active_menu === 'courses') ? 'active' : ''; ?>">
                <i class="fa-solid fa-book-open-reader me-2 text-info"></i> Khóa học &amp; Lớp 1-1
            </a>
            <a href="schedule.php" class="list-group-item list-group-item-action bg-transparent text-white <?php echo ($active_menu === 'schedule') ? 'active' : ''; ?>">
                <i class="fa-solid fa-calendar-days me-2 text-primary"></i> Xếp lịch &amp; Điểm danh
            </a>
            <a href="grades.php" class="list-group-item list-group-item-action bg-transparent text-white <?php echo ($active_menu === 'grades') ? 'active' : ''; ?>">
                <i class="fa-solid fa-star me-2 text-warning"></i> Quản lý Điểm số
            </a>
            <a href="registrations.php" class="list-group-item list-group-item-action bg-transparent text-white <?php echo ($active_menu === 'registrations') ? 'active' : ''; ?>">
                <i class="fa-solid fa-envelope-open-text me-2 text-danger"></i> Đăng ký trực tuyến
                <span class="badge bg-danger rounded-pill ms-2">Mới</span>
            </a>
        </div>

        <div class="mt-auto p-3 border-top border-secondary">
            <a href="../index.php" class="btn btn-sm btn-outline-light w-100 mb-2">
                <i class="fa-solid fa-globe me-1"></i> Xem trang Client UI
            </a>
            <small class="text-secondary d-block text-center">&copy; <?php echo date('Y'); ?> EduCenter Portal</small>
        </div>
    </div>

    <!-- KHUNG NỘI DUNG CHÍNH (PAGE CONTENT WRAPPER) -->
    <div id="page-content-wrapper" class="w-100">
        <!-- THANH ĐIỀU HƯỚNG TRÊN CÙNG ADMIN (TOP NAVBAR) -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm px-4 py-3">
            <div class="container-fluid p-0">
                <button class="btn btn-outline-primary" id="menu-toggle" type="button" aria-label="Thu phóng menu quản trị">
                    <i class="fa-solid fa-bars"></i>
                </button>

                <div class="ms-auto d-flex align-items-center gap-3">
                    <a href="../lookup.php" class="btn btn-sm btn-light border text-secondary d-none d-md-inline-block">
                        <i class="fa-solid fa-magnifying-glass me-1"></i> Cổng tra cứu học viên
                    </a>

                    <div class="dropdown">
                        <a href="#" class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle" id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="<?php echo htmlspecialchars($adminAva); ?>" alt="Admin" width="36" height="36" class="rounded-circle me-2 object-fit-cover">
                            <div class="d-none d-sm-block text-start">
                                <strong class="d-block small"><?php echo htmlspecialchars($adminName); ?></strong>
                                <span class="text-muted" style="font-size: 0.75rem;"><?php echo htmlspecialchars($adminEmail); ?></span>
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="dropdownUser">
                            <li><a class="dropdown-item" href="index.php"><i class="fa-solid fa-gauge-high me-2"></i>Tổng quan hệ thống</a></li>
                            <li><a class="dropdown-item" href="../profile.php"><i class="fa-solid fa-user-gear me-2"></i>Hồ sơ &amp; Tùy chỉnh</a></li>
                            <li><a class="dropdown-item" href="../index.php"><i class="fa-solid fa-house me-2"></i>Trang chủ Client</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="../logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i>Đăng xuất</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

        <div class="container-fluid p-4">
