<?php
/**
 * EDUCENTER - HỆ THỐNG WEBSITE QUẢN LÝ TRUNG TÂM NGOẠI NGỮ & GIA SƯ
 * File: admin/index.php
 * Phụ trách (Tuần 1 & Tuần 3 - Nguyễn Anh Khoa & Lý Hạo Danh):
 * Bảng điều khiển Tổng quan Quản trị (Admin Dashboard) - Đồng bộ KPI động, Ca trực & Hồ sơ mới
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

$admin_title = 'Bảng Điều Khiển Tổng Quan';
$active_menu = 'dashboard';

// Khởi tạo số liệu KPI từ Mock Data Fallback chuẩn
$kpiStats = [
    'total_students'        => $sample_dashboard_stats['total_students'] ?? 1250,
    'total_teachers'        => $sample_dashboard_stats['total_teachers'] ?? 48,
    'active_classes'        => $sample_dashboard_stats['active_classes'] ?? 32,
    'pending_registrations' => $sample_dashboard_stats['pending_registrations'] ?? 15
];

// Khởi tạo dữ liệu hiển thị lịch dạy hôm nay và đăng ký mới
$todayClasses     = $sample_today_classes ?? [];
$newRegistrations = $sample_registrations ?? [];

// Đồng bộ các hồ sơ đăng ký vừa gửi từ form register.php trong phiên làm việc (Session)
if (!empty($_SESSION['new_registrations'])) {
    $newRegistrations = array_merge($_SESSION['new_registrations'], $newRegistrations);
    $kpiStats['pending_registrations'] += count($_SESSION['new_registrations']);
}

// Nếu MySQL đang kết nối, ưu tiên truy vấn dữ liệu thực tế từ CSDL
if ($db_connected && $pdo) {
    try {
        $stmtReg = $pdo->query("SELECT ticket_code, full_name, phone, email, course_name, status FROM Course_Registrations ORDER BY registration_id DESC LIMIT 4");
        $dbRegs = $stmtReg->fetchAll();
        if (!empty($dbRegs)) {
            $newRegistrations = [];
            foreach ($dbRegs as $reg) {
                $statusText = ($reg['status'] === 'Chờ liên hệ') ? 'Chờ gọi' : $reg['status'];
                if ($statusText === 'Chờ gọi') {
                    $badgeClass = 'bg-warning text-dark';
                } elseif ($statusText === 'Đã tư vấn') {
                    $badgeClass = 'bg-success text-white';
                } else {
                    $badgeClass = 'bg-primary text-white';
                }
                $newRegistrations[] = [
                    'ticket_code' => $reg['ticket_code'],
                    'full_name'   => $reg['full_name'],
                    'phone'       => $reg['phone'],
                    'course_name' => $reg['course_name'],
                    'status'      => $statusText,
                    'badge_class' => $badgeClass
                ];
            }
        }
    } catch (Exception $e) {
        // Giữ nguyên dữ liệu mẫu dự phòng nếu bảng chưa khởi tạo
    }
}

// Giới hạn hiển thị tối đa 3 hồ sơ mới nhất trên widget Dashboard
$newRegistrations = array_slice($newRegistrations, 0, 3);

// Lọc ca học hôm nay nếu người dùng chọn trên Dashboard
$selectedShift = isset($_GET['shift']) ? trim($_GET['shift']) : 'all';

require_once __DIR__ . '/includes/admin_header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h3 class="fw-bold mb-1">Bảng Điều Khiển (Dashboard)</h3>
        <p class="text-muted small mb-0">Hệ thống quản lý trung tâm ngoại ngữ &amp; điều phối gia sư EduCenter</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="students.php" class="btn btn-sm btn-outline-primary rounded-pill px-3">
            <i class="fa-solid fa-user-plus me-1"></i> Thêm học viên
        </a>
        <span class="badge bg-success-subtle text-success p-2 px-3 rounded-pill fw-semibold">
            <i class="fa-solid fa-circle-dot me-1"></i> Hệ thống hoạt động bình thường
        </span>
    </div>
</div>

<!-- 1. THẺ THỐNG KÊ TỔNG QUAN (STAT CARDS) -->
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white admin-kpi-card">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">TỔNG HỌC VIÊN</span>
                    <h2 class="fw-bold my-1 text-primary"><?php echo number_format($kpiStats['total_students']); ?></h2>
                    <small class="text-success"><i class="fa-solid fa-arrow-up me-1"></i>+12% so với tháng trước</small>
                </div>
                <div class="bg-primary-subtle text-primary admin-stat-icon">
                    <i class="fa-solid fa-users fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white admin-kpi-card">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">GIẢNG VIÊN &amp; GIA SƯ</span>
                    <h2 class="fw-bold my-1 text-success"><?php echo number_format($kpiStats['total_teachers']); ?></h2>
                    <small class="text-muted">35 Giảng viên - 13 Gia sư 1-1</small>
                </div>
                <div class="bg-success-subtle text-success admin-stat-icon">
                    <i class="fa-solid fa-chalkboard-user fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white admin-kpi-card">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">LỚP ĐANG HOẠT ĐỘNG</span>
                    <h2 class="fw-bold my-1 text-warning"><?php echo number_format($kpiStats['active_classes']); ?></h2>
                    <small class="text-primary"><i class="fa-solid fa-calendar-check me-1"></i>8 lớp mới khai giảng</small>
                </div>
                <div class="bg-warning-subtle text-warning admin-stat-icon">
                    <i class="fa-solid fa-book-open fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white admin-kpi-card">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">ĐĂNG KÝ MỚI CHỜ DUYỆT</span>
                    <h2 class="fw-bold my-1 text-danger"><?php echo number_format($kpiStats['pending_registrations']); ?></h2>
                    <small class="text-danger fw-semibold">Cần liên hệ trong ngày</small>
                </div>
                <div class="bg-danger-subtle text-danger admin-stat-icon">
                    <i class="fa-solid fa-bell fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 2. LỊCH GIẢNG DẠY HÔM NAY & ĐĂNG KÝ MỚI -->
<div class="row g-4">
    <!-- Cột trái: Lịch học hôm nay & Thông báo điều hành -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-0 py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="fa-solid fa-calendar-day text-primary me-2"></i> Lịch Giảng Dạy &amp; Điểm Danh Hôm Nay
                </h5>
                <div class="d-flex align-items-center gap-2">
                    <a href="schedule.php" class="btn btn-sm btn-outline-primary rounded-pill">Vào bảng điểm danh</a>
                </div>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Ca học</th>
                            <th>Tên lớp</th>
                            <th>Giảng viên / Gia sư</th>
                            <th>Phòng</th>
                            <th>Sĩ số</th>
                            <th class="pe-3">Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($todayClasses as $cls): ?>
                            <?php
                                if ($selectedShift !== 'all' && $cls['study_time'] !== $selectedShift) {
                                    continue;
                                }
                            ?>
                            <tr>
                                <td class="ps-3">
                                    <span class="badge <?php echo htmlspecialchars($cls['time_badge']); ?>">
                                        <?php echo htmlspecialchars($cls['study_time']); ?>
                                    </span>
                                </td>
                                <td><strong><?php echo htmlspecialchars($cls['class_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($cls['teacher_name']); ?></td>
                                <td><?php echo htmlspecialchars($cls['room']); ?></td>
                                <td><?php echo htmlspecialchars($cls['capacity']); ?></td>
                                <td class="pe-3">
                                    <span class="badge <?php echo htmlspecialchars($cls['status_class']); ?>">
                                        <?php echo htmlspecialchars($cls['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="fa-solid fa-bullhorn text-warning me-2"></i> Thông Báo Điều Hành Nội Bộ
                </h5>
                <span class="badge bg-light text-secondary border">Cập nhật hôm nay</span>
            </div>
            <div class="card-body pt-0">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item px-0 py-3 d-flex gap-3 align-items-start">
                        <i class="fa-solid fa-circle-check text-success fs-5 mt-1"></i>
                        <div>
                            <strong class="d-block">Hoàn tất kiểm tra kỳ thi giữa khóa đợt 1</strong>
                            <small class="text-muted">Đã cập nhật toàn bộ bảng điểm 4 kỹ năng cho 120 học viên lên cổng tra cứu online.</small>
                        </div>
                    </li>
                    <li class="list-group-item px-0 py-3 d-flex gap-3 align-items-start">
                        <i class="fa-solid fa-triangle-exclamation text-warning fs-5 mt-1"></i>
                        <div>
                            <strong class="d-block">Nhắc nhở nộp kế hoạch bài giảng tuần tới</strong>
                            <small class="text-muted">Các thầy cô phụ trách khối IELTS và TOEIC vui lòng gửi giáo trình trước 17h thứ 6.</small>
                        </div>
                    </li>
                    <li class="list-group-item px-0 py-3 d-flex gap-3 align-items-start">
                        <i class="fa-solid fa-bolt text-primary fs-5 mt-1"></i>
                        <div>
                            <strong class="d-block">Khai giảng lớp IELTS Intensive K42 &amp; Gia sư 1-1 mới</strong>
                            <small class="text-muted">Bộ phận học vụ hoàn tất xếp phòng P.201 và gửi thông báo lịch học cho học viên.</small>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Cột phải: Đăng ký trực tuyến gần đây -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="fa-solid fa-user-plus text-success me-2"></i> Đăng Ký Mới
                </h5>
                <a href="registrations.php" class="btn btn-sm btn-link text-decoration-none">Xem tất cả</a>
            </div>
            <div class="card-body p-3">
                <?php foreach ($newRegistrations as $index => $reg): ?>
                    <div class="p-3 bg-light rounded-3 <?php echo ($index < count($newRegistrations) - 1) ? 'mb-3' : ''; ?> border">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <strong class="text-primary"><?php echo htmlspecialchars($reg['full_name']); ?></strong>
                            <span class="badge <?php echo htmlspecialchars($reg['badge_class']); ?>">
                                <?php echo htmlspecialchars($reg['status']); ?>
                            </span>
                        </div>
                        <small class="text-muted d-block">SĐT: <?php echo htmlspecialchars($reg['phone']); ?></small>
                        <small class="text-secondary d-block">Khóa: <?php echo htmlspecialchars($reg['course_name']); ?></small>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
