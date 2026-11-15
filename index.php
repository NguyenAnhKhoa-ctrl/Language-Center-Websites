<?php
/**
 * EDUCENTER - HỆ THỐNG WEBSITE QUẢN LÝ TRUNG TÂM NGOẠI NGỮ & GIA SƯ
 * File: index.php
 * Phụ trách (Tuần 1 - Nguyễn Anh Khoa - 52400051):
 * Trang chủ Phân hệ Khách hàng (Client UI) - Hero Banner, Thống kê & Định hướng Khóa học
 */

$pageTitle = 'Trang Chủ - Hệ Thống Đào Tạo Ngoại Ngữ & Gia Sư EduCenter';
$currentPage = 'index.php';

// Tích hợp config/db.php nếu đã có, đồng thời hỗ trợ dữ liệu mặc định an toàn
if (file_exists(__DIR__ . '/config/db.php')) {
    require_once __DIR__ . '/config/db.php';
}

// Danh sách khóa học nổi bật hiển thị trên trang chủ
$featuredCourses = [
    [
        'category'    => 'IELTS',
        'badge'       => 'Hot nhất',
        'badge_class' => 'edu-badge-hot',
        'icon'        => 'fa-graduation-cap',
        'title'       => 'Luyện thi IELTS Intensive (Mục tiêu 6.5 - 7.5+)',
        'target'      => 'Cam kết đầu ra 6.5 - 7.5+ bằng văn bản',
        'duration'    => '3 tháng (36 buổi chuyên sâu)',
        'mode'        => 'Lớp nhóm nhỏ 8-12 HV / Hỗ trợ 1-1',
        'fee'         => '8.900.000đ'
    ],
    [
        'category'    => 'TOEIC',
        'badge'       => 'Cấp tốc',
        'badge_class' => 'edu-badge-fast',
        'icon'        => 'fa-certificate',
        'title'       => 'Luyện thi TOEIC Cấp Tốc (Cam kết 750+)',
        'target'      => 'Nắm trọn chiến thuật giải đề IIG mới nhất',
        'duration'    => '2 tháng (24 buổi thực chiến)',
        'mode'        => 'Offline tại trung tâm / Hybrid linh hoạt',
        'fee'         => '4.500.000đ'
    ],
    [
        'category'    => 'Giao tiếp',
        'badge'       => 'Phổ biến',
        'badge_class' => 'edu-badge-popular',
        'icon'        => 'fa-comments',
        'title'       => 'Tiếng Anh Giao Tiếp Thực Chiến (100% Phản Xạ)',
        'target'      => 'Phản xạ tự nhiên cùng GV Bản xứ & Thạc sĩ',
        'duration'    => '3 tháng (30 buổi tương tác)',
        'mode'        => 'Thực hành thuyết trình & tình huống thực tế',
        'fee'         => '5.200.000đ'
    ]
];

require_once __DIR__ . '/includes/header.php';
?>

    <!-- 1. HERO SECTION -->
    <section class="edu-hero">
        <div class="container">
            <div class="row align-items-center g-5">
                <!-- Cột nội dung bên trái -->
                <div class="col-lg-7">
                    <div class="edu-hero-badge">
                        <i class="fa-solid fa-star text-warning"></i>
                        <span>Trung tâm Ngoại ngữ &amp; Gia sư chuẩn Quốc tế</span>
                    </div>

                    <h1 class="edu-hero-title">
                        Bứt Phá Tiếng Anh,
                        <span class="highlight-amber">Chinh Phục Tương Lai</span>
                    </h1>

                    <p class="edu-hero-desc">
                        Lộ trình đào tạo cá nhân hóa 1-1 và theo nhóm nhỏ. Đội ngũ giảng viên 8.5+ IELTS, bản xứ và gia sư sư phạm tận tâm. Cam kết chuẩn đầu ra, hỗ trợ tra cứu tiến độ học tập và điểm thi 24/7.
                    </p>

                    <div class="d-flex flex-wrap gap-3 edu-hero-actions">
                        <a href="courses.php" class="btn-hero-primary">
                            <i class="fa-solid fa-compass"></i>
                            <span>Khám phá Khóa học</span>
                        </a>
                        <a href="lookup.php" class="btn-hero-outline">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <span>Tra cứu Điểm &amp; Lịch học</span>
                        </a>
                    </div>
                </div>

                <!-- Cột hình ảnh bên phải -->
                <div class="col-lg-5">
                    <div class="edu-hero-image-wrap">
                        <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=900&q=80"
                             alt="Học viên EduCenter"
                             class="edu-hero-image">

                        <!-- Nhãn nổi cam kết đầu ra -->
                        <div class="edu-floating-card">
                            <div class="edu-floating-icon">
                                <i class="fa-solid fa-check"></i>
                            </div>
                            <div class="text-start">
                                <div class="fw-bold text-dark" style="font-size:0.95rem;">98.5% Đạt Target</div>
                                <div class="text-muted" style="font-size:0.78rem;">Ngay trong lần thi đầu tiên</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 2. THANH THỐNG KÊ SỐ LIỆU (STATS BAR) -->
    <section class="edu-stats-wrapper">
        <div class="container">
            <div class="edu-stats-box">
                <div class="row g-0">
                    <div class="col-lg-3 col-6 edu-stat-item">
                        <div class="edu-stat-number">1,500+</div>
                        <div class="edu-stat-label">Học viên xuất sắc</div>
                    </div>
                    <div class="col-lg-3 col-6 edu-stat-item">
                        <div class="edu-stat-number">50+</div>
                        <div class="edu-stat-label">Giảng viên &amp; Gia sư</div>
                    </div>
                    <div class="col-lg-3 col-6 edu-stat-item">
                        <div class="edu-stat-number">100%</div>
                        <div class="edu-stat-label">Cam kết đầu ra hợp đồng</div>
                    </div>
                    <div class="col-lg-3 col-6 edu-stat-item">
                        <div class="edu-stat-number">10+</div>
                        <div class="edu-stat-label">Năm kinh nghiệm uy tín</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 3. CHƯƠNG TRÌNH ĐÀO TẠO & GIA SƯ TIÊU BIỂU -->
    <section class="edu-section">
        <div class="container">
            <div class="text-center">
                <span class="edu-section-tag">Chương trình đào tạo chuẩn quốc tế</span>
                <h2 class="edu-section-title">Các Khóa Học &amp; Dịch Vụ Gia Sư Nổi Bật</h2>
                <p class="edu-section-subtitle">
                    Thiết kế chuyên biệt cho từng mục tiêu học thuật: Luyện thi chứng chỉ quốc tế IELTS, TOEIC, Tiếng Anh Giao tiếp phản xạ và Gia sư 1 kèm 1.
                </p>
            </div>

            <div class="row g-4">
                <?php foreach ($featuredCourses as $course): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="edu-course-card">
                            <div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="edu-course-icon">
                                        <i class="fa-solid <?php echo htmlspecialchars($course['icon']); ?>"></i>
                                    </span>
                                    <span class="<?php echo htmlspecialchars($course['badge_class']); ?>">
                                        <?php echo htmlspecialchars($course['badge']); ?>
                                    </span>
                                </div>

                                <span class="edu-course-cat"><?php echo htmlspecialchars($course['category']); ?></span>
                                <h3 class="edu-course-title"><?php echo htmlspecialchars($course['title']); ?></h3>

                                <ul class="edu-course-meta">
                                    <li>
                                        <i class="fa-solid fa-bullseye"></i>
                                        <span><?php echo htmlspecialchars($course['target']); ?></span>
                                    </li>
                                    <li>
                                        <i class="fa-regular fa-clock"></i>
                                        <span>Thời lượng: <?php echo htmlspecialchars($course['duration']); ?></span>
                                    </li>
                                    <li>
                                        <i class="fa-solid fa-users-rectangle"></i>
                                        <span><?php echo htmlspecialchars($course['mode']); ?></span>
                                    </li>
                                </ul>
                            </div>

                            <div class="pt-3 border-top d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-muted d-block" style="font-size:0.75rem;">Học phí trọn khóa</span>
                                    <span class="edu-course-price"><?php echo htmlspecialchars($course['fee']); ?></span>
                                </div>
                                <a href="register.php" class="btn btn-primary fw-semibold px-3 py-2 rounded-3">
                                    Đăng ký ngay <i class="fa-solid fa-arrow-right ms-1 small"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="text-center mt-5">
                <a href="courses.php" class="btn btn-outline-primary fw-bold px-4 py-2 rounded-pill">
                    Xem tất cả khóa học &amp; Gia sư 1-1 <i class="fa-solid fa-angles-right ms-1"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- 4. GIÁ TRỊ KHÁC BIỆT CỦA EDUCENTER -->
    <section class="edu-section bg-white border-top border-bottom">
        <div class="container">
            <div class="text-center">
                <span class="edu-section-tag">Ưu thế vượt trội</span>
                <h2 class="edu-section-title">Vì Sao Phụ Huynh &amp; Học Viên Chọn EduCenter?</h2>
                <p class="edu-section-subtitle">
                    Hệ thống quản lý đào tạo số hóa toàn diện giúp kết nối chặt chẽ giữa Trung tâm - Giảng viên - Học viên và Phụ huynh.
                </p>
            </div>

            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="edu-feature-box">
                        <div class="edu-feature-icon bg-primary bg-opacity-10 text-primary">
                            <i class="fa-solid fa-user-check"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Đội Ngũ 8.5+ IELTS</h5>
                        <p class="text-muted small mb-0">
                            100% giảng viên và gia sư sở hữu chứng chỉ TESOL, CELTA, IELTS 8.0 - 8.5+ và TOEIC 990 tuyệt đối.
                        </p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="edu-feature-box">
                        <div class="edu-feature-icon bg-warning bg-opacity-10 text-warning">
                            <i class="fa-solid fa-route"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Cá Nhân Hóa Lộ Trình</h5>
                        <p class="text-muted small mb-0">
                            Kiểm tra trình độ 4 kỹ năng đầu vào miễn phí, thiết kế lộ trình học tập sát với năng lực và mục tiêu.
                        </p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="edu-feature-box">
                        <div class="edu-feature-icon bg-success bg-opacity-10 text-success">
                            <i class="fa-solid fa-chart-line"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Tra Cứu Điểm &amp; Lịch 24/7</h5>
                        <p class="text-muted small mb-0">
                            Phụ huynh và học viên dễ dàng tra cứu bảng điểm 4 kỹ năng, tỷ lệ chuyên cần và lịch học trực tuyến.
                        </p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="edu-feature-box">
                        <div class="edu-feature-icon bg-info bg-opacity-10 text-info">
                            <i class="fa-solid fa-file-shield"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Cam Kết Đầu Ra Pháp Lý</h5>
                        <p class="text-muted small mb-0">
                            Cam kết chuẩn đầu ra bằng văn bản hợp đồng. Hỗ trợ học lại hoàn toàn miễn phí nếu chưa đạt target.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 5. BANNER KÊU GỌI HÀNH ĐỘNG (CTA) -->
    <section class="py-5">
        <div class="container">
            <div class="p-4 p-md-5 rounded-4 text-white shadow-lg" style="background: linear-gradient(135deg, #0f296b 0%, #1d4ed8 100%);">
                <div class="row align-items-center g-4">
                    <div class="col-lg-8">
                        <span class="badge bg-warning text-dark fw-bold mb-2 px-3 py-2 rounded-pill">
                            ƯU ĐÃI HỌC BỔNG THÁNG NÀY
                        </span>
                        <h3 class="fw-bold mb-2">Nhận Ngay Học Bổng 20% &amp; Kiểm Tra Trình Độ 4 Kỹ Năng Miễn Phí!</h3>
                        <p class="text-white-50 mb-0">
                            Đăng ký giữ chỗ trực tuyến chỉ trong 30 giây để nhận tư vấn lộ trình cá nhân hóa cùng chuyên gia học thuật.
                        </p>
                    </div>
                    <div class="col-lg-4 text-lg-end">
                        <a href="register.php" class="btn-hero-primary">
                            <i class="fa-solid fa-paper-plane"></i>
                            <span>Đăng ký Test trình độ ngay</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
