<?php
/**
 * EDUCENTER - HỆ THỐNG WEBSITE QUẢN LÝ TRUNG TÂM NGOẠI NGỮ & GIA SƯ
 * File: courses.php
 * Phụ trách (Tuần 2 - Nguyễn Anh Khoa - 52400051):
 * Trang Danh sách Khóa học & Dịch vụ Gia sư 1-1 kèm bộ lọc tương tác trực quan
 */

$pageTitle = 'Khóa Học & Dịch Vụ Gia Sư 1-1 - EduCenter';
$currentPage = 'courses.php';

require_once __DIR__ . '/config/db.php';

// Lấy danh sách khóa học từ CSDL MySQL (nếu kết nối) hoặc dùng Mock Data Fallback
$courses = [];
if ($db_connected && $pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM Courses ORDER BY course_id ASC");
        $rows = $stmt->fetchAll();
        if (!empty($rows)) {
            foreach ($rows as $row) {
                $cat = $row['category'] ?? 'IELTS';
                $iconMap = [
                    'IELTS'      => 'fa-graduation-cap',
                    'TOEIC'      => 'fa-certificate',
                    'Giao tiếp'  => 'fa-comments',
                    'Gia sư 1-1' => 'fa-user-tie',
                    'Thiếu nhi'  => 'fa-shapes'
                ];
                $courses[] = [
                    'course_id'   => $row['course_id'],
                    'course_code' => $row['course_code'],
                    'course_name' => $row['course_name'],
                    'category'    => $cat,
                    'badge'       => $row['badge'] ?? 'Phổ biến',
                    'level'       => $row['level_range'] ?? 'Mọi trình độ',
                    'target_goal' => $row['target_goal'] ?? 'Cam kết chuẩn đầu ra',
                    'duration'    => $row['duration'] ?? '3 tháng (36 buổi)',
                    'description' => $row['description'] ?? '',
                    'fee'         => (float)$row['fee'],
                    'icon'        => $iconMap[$cat] ?? 'fa-book-open-reader'
                ];
            }
        }
    } catch (Exception $e) {
        $courses = [];
    }
}

if (empty($courses)) {
    $courses = $sample_courses;
}

// Lọc theo tham số URL (nếu có)
$selectedCategory = isset($_GET['category']) ? trim($_GET['category']) : 'all';

// Hàm chọn class màu cho nhãn độ nóng (badge)
function getCourseBadgeClass($badge) {
    $badgeLower = mb_strtolower($badge, 'UTF-8');
    if (strpos($badgeLower, 'hot') !== false) {
        return 'edu-badge-hot';
    }
    if (strpos($badgeLower, 'cấp tốc') !== false) {
        return 'edu-badge-fast';
    }
    return 'edu-badge-popular';
}

require_once __DIR__ . '/includes/header.php';
?>

    <!-- 1. SUBPAGE HERO BANNER -->
    <section class="edu-page-banner">
        <div class="container text-center">
            <span class="edu-page-badge">CHƯƠNG TRÌNH ĐÀO TẠO QUỐC TẾ</span>
            <h1 class="edu-page-title">Danh Sách Khóa Học &amp; Gia Sư 1-1</h1>
            <p class="edu-page-subtitle">
                Từ mất gốc đến thành thạo giao tiếp, chinh phục các chứng chỉ quốc tế
                và các lớp gia sư kèm sát từng học viên.
            </p>
        </div>
    </section>

    <!-- 2. BỘ LỌC DANH MỤC & DANH SÁCH KHÓA HỌC -->
    <section class="edu-section pt-5">
        <div class="container">
            <!-- Thanh nút lọc theo danh mục (Category Filter Bar) -->
            <div class="d-flex flex-wrap justify-content-center gap-2 mb-5" id="courseFilterBar">
                <button type="button"
                        class="btn rounded-pill px-4 py-2 fw-semibold shadow-sm <?php echo ($selectedCategory === 'all') ? 'btn-primary text-white' : 'btn-light border'; ?>"
                        data-course-filter="all">
                    <i class="fa-solid fa-border-all me-1"></i> Tất cả khóa học
                </button>
                <button type="button"
                        class="btn rounded-pill px-4 py-2 fw-semibold shadow-sm <?php echo ($selectedCategory === 'IELTS') ? 'btn-primary text-white' : 'btn-light border'; ?>"
                        data-course-filter="IELTS">
                    <i class="fa-solid fa-circle-dot text-primary me-1 small"></i> Luyện thi IELTS
                </button>
                <button type="button"
                        class="btn rounded-pill px-4 py-2 fw-semibold shadow-sm <?php echo ($selectedCategory === 'TOEIC') ? 'btn-primary text-white' : 'btn-light border'; ?>"
                        data-course-filter="TOEIC">
                    <i class="fa-solid fa-circle-dot text-info me-1 small"></i> Luyện thi TOEIC
                </button>
                <button type="button"
                        class="btn rounded-pill px-4 py-2 fw-semibold shadow-sm <?php echo ($selectedCategory === 'Giao tiếp') ? 'btn-primary text-white' : 'btn-light border'; ?>"
                        data-course-filter="Giao tiếp">
                    <i class="fa-solid fa-comments text-primary me-1"></i> Tiếng Anh Giao tiếp
                </button>
                <button type="button"
                        class="btn rounded-pill px-4 py-2 fw-semibold shadow-sm <?php echo ($selectedCategory === 'Gia sư 1-1') ? 'btn-primary text-white' : 'btn-light border'; ?>"
                        data-course-filter="Gia sư 1-1">
                    <i class="fa-solid fa-user-graduate text-primary me-1"></i> Gia sư 1 kèm 1
                </button>
                <button type="button"
                        class="btn rounded-pill px-4 py-2 fw-semibold shadow-sm <?php echo ($selectedCategory === 'Thiếu nhi') ? 'btn-primary text-white' : 'btn-light border'; ?>"
                        data-course-filter="Thiếu nhi">
                    <i class="fa-solid fa-child-reaching text-primary me-1"></i> Tiếng Anh Thiếu nhi
                </button>
            </div>

            <!-- Lưới hiển thị các thẻ khóa học -->
            <div class="row g-4" id="courseGridContainer">
                <?php foreach ($courses as $course): ?>
                    <?php
                        $cat = $course['category'] ?? 'IELTS';
                        $isHidden = ($selectedCategory !== 'all' && $selectedCategory !== $cat);
                        $badgeClass = getCourseBadgeClass($course['badge'] ?? 'Phổ biến');
                        $levelText = $course['level'] ?? ($course['level_range'] ?? 'Mọi trình độ');
                        $formattedFee = number_format((float)$course['fee'], 0, ',', '.') . 'đ';
                    ?>
                    <div class="col-lg-4 col-md-6 course-card-item"
                         data-course-category="<?php echo htmlspecialchars($cat); ?>"
                         style="<?php echo $isHidden ? 'display:none;' : ''; ?>">
                        <div class="edu-course-card">
                            <div>
                                <!-- Hàng trên cùng: Icon + Nhãn độ nóng -->
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="edu-course-icon">
                                        <i class="fa-solid <?php echo htmlspecialchars($course['icon'] ?? 'fa-graduation-cap'); ?>"></i>
                                    </span>
                                    <span class="<?php echo htmlspecialchars($badgeClass); ?>">
                                        <?php echo htmlspecialchars($course['badge'] ?? 'Phổ biến'); ?>
                                    </span>
                                </div>

                                <!-- Nhãn danh mục & Tên khóa học -->
                                <span class="edu-course-cat text-primary bg-primary bg-opacity-10">
                                    <?php echo htmlspecialchars($cat); ?>
                                </span>
                                <h3 class="edu-course-title">
                                    <?php echo htmlspecialchars($course['course_name']); ?>
                                </h3>

                                <p class="text-muted small mb-3">
                                    <?php echo htmlspecialchars($course['description']); ?>
                                </p>

                                <!-- Thông số chi tiết khóa học -->
                                <ul class="edu-course-meta">
                                    <li>
                                        <i class="fa-solid fa-layer-group"></i>
                                        <span>Đối tượng: <strong><?php echo htmlspecialchars($levelText); ?></strong></span>
                                    </li>
                                    <li>
                                        <i class="fa-regular fa-clock"></i>
                                        <span>Thời lượng: <strong><?php echo htmlspecialchars($course['duration']); ?></strong></span>
                                    </li>
                                    <li>
                                        <i class="fa-solid fa-shield-halved"></i>
                                        <span>Cam kết chuẩn đầu ra bằng văn bản</span>
                                    </li>
                                </ul>
                            </div>

                            <!-- Hàng dưới cùng: Học phí & Nút đăng ký -->
                            <div class="pt-3 border-top d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-muted d-block" style="font-size:0.75rem;">Học phí trọn gói</span>
                                    <span class="edu-course-price"><?php echo $formattedFee; ?></span>
                                </div>
                                <a href="register.php?course=<?php echo urlencode($course['course_name']); ?>"
                                   class="btn btn-primary fw-semibold px-3 py-2 rounded-3">
                                    <i class="fa-solid fa-paper-plane me-1 small"></i> Đăng ký học
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- 3. DỊCH VỤ ĐẶC BIỆT: GHÉP NỐI GIA SƯ 1 KÈM 1 THEO YÊU CẦU -->
    <section class="edu-section bg-white border-top">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <span class="edu-section-tag">Dịch vụ Gia sư VIP 1-1</span>
                    <h2 class="edu-section-title">Cần Gia Sư Kèm Riêng 1-1 Tại Nhà Hoặc Online?</h2>
                    <p class="text-muted mb-4">
                        Hệ thống EduCenter tự động ghép nối học viên với Gia sư/Giảng viên phù hợp nhất dựa trên mục tiêu điểm số, khu vực sinh sống và lịch trống cá nhân.
                    </p>

                    <div class="d-flex gap-3 mb-3">
                        <div class="edu-step-badge">1</div>
                        <div>
                            <h6 class="fw-bold mb-1">Gửi yêu cầu &amp; Test năng lực đầu vào</h6>
                            <p class="text-muted small mb-0">Xác định chính xác điểm mạnh, điểm yếu 4 kỹ năng hoàn toàn miễn phí.</p>
                        </div>
                    </div>

                    <div class="d-flex gap-3 mb-3">
                        <div class="edu-step-badge">2</div>
                        <div>
                            <h6 class="fw-bold mb-1">Chọn Gia sư &amp; Học thử trải nghiệm</h6>
                            <p class="text-muted small mb-0">Lựa chọn từ hơn 50+ giảng viên IELTS 8.0+, TOEIC 990 và gia sư sư phạm giỏi.</p>
                        </div>
                    </div>

                    <div class="d-flex gap-3 mb-4">
                        <div class="edu-step-badge">3</div>
                        <div>
                            <h6 class="fw-bold mb-1">Theo dõi tiến độ minh bạch 24/7</h6>
                            <p class="text-muted small mb-0">Cập nhật nhật ký điểm danh, nhận xét sau mỗi buổi học và bảng điểm định kỳ.</p>
                        </div>
                    </div>

                    <a href="register.php?course=<?php echo urlencode('Gia sư Tiếng Anh 1 Kèm 1 Theo Yêu Cầu'); ?>"
                       class="btn-trial-cta px-4 py-3">
                        <i class="fa-solid fa-user-plus"></i>
                        <span>Đăng ký tìm Gia sư 1-1 ngay</span>
                    </a>
                </div>

                <div class="col-lg-6">
                    <div class="p-4 rounded-4 bg-light border shadow-sm">
                        <h5 class="fw-bold text-primary mb-3">
                            <i class="fa-solid fa-award text-warning me-2"></i>Bảng So Sánh Quyền Lợi Học Viên
                        </h5>
                        <div class="table-responsive">
                            <table class="table table-bordered bg-white align-middle mb-0 small">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tiêu chí đào tạo</th>
                                        <th class="text-center">Lớp Nhóm Nhỏ</th>
                                        <th class="text-center text-primary">Gia Sư VIP 1-1</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="fw-medium">Sĩ số lớp học</td>
                                        <td class="text-center">8 - 12 học viên</td>
                                        <td class="text-center fw-bold text-primary">1 Thầy - 1 Trò</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-medium">Lịch học hàng tuần</td>
                                        <td class="text-center">Cố định theo ca</td>
                                        <td class="text-center fw-bold text-success">Linh hoạt 100%</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-medium">Chữa bài Writing &amp; Speaking</td>
                                        <td class="text-center">Hàng tuần</td>
                                        <td class="text-center fw-bold text-primary">Trực tiếp mỗi buổi</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-medium">Cổng tra cứu Điểm &amp; Điểm danh</td>
                                        <td class="text-center"><i class="fa-solid fa-circle-check text-success"></i></td>
                                        <td class="text-center"><i class="fa-solid fa-circle-check text-success"></i></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-medium">Cam kết đầu ra hợp đồng</td>
                                        <td class="text-center"><i class="fa-solid fa-circle-check text-success"></i></td>
                                        <td class="text-center"><i class="fa-solid fa-circle-check text-success"></i></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
