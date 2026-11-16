<?php
/**
 * EDUCENTER - HỆ THỐNG WEBSITE QUẢN LÝ TRUNG TÂM NGOẠI NGỮ & GIA SƯ
 * File: teachers.php
 * Phụ trách (Tuần 2 - Lý Hạo Danh - 52400175):
 * Trang Đội ngũ Giảng viên & Gia sư tiêu biểu (Hồ sơ chứng chỉ quốc tế, kinh nghiệm & đánh giá sao)
 */

$pageTitle = 'Đội Ngũ Giảng Viên & Gia Sư Uy Tín - EduCenter';
$currentPage = 'teachers.php';

require_once __DIR__ . '/config/db.php';

// Lấy danh sách giảng viên từ CSDL MySQL (nếu kết nối) hoặc dùng Mock Data Fallback
$teachers = [];
if ($db_connected && $pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM Teachers ORDER BY teacher_id ASC");
        $rows = $stmt->fetchAll();
        if (!empty($rows)) {
            $defaultAvatars = [
                1 => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=400&q=80',
                2 => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=400&q=80',
                3 => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=400&q=80',
                4 => 'https://images.unsplash.com/photo-1580489944761-15a19d654956?auto=format&fit=crop&w=400&q=80'
            ];
            foreach ($rows as $row) {
                $tid = (int)$row['teacher_id'];
                $teachers[] = [
                    'teacher_id'     => $tid,
                    'full_name'      => $row['full_name'],
                    'avatar'         => !empty($row['avatar']) ? $row['avatar'] : ($defaultAvatars[$tid] ?? $defaultAvatars[1]),
                    'specialization' => $row['specialization'] ?? 'Giảng viên Ngoại ngữ Quốc tế',
                    'degree'         => $row['degree'] ?? 'Chứng chỉ TESOL / IELTS 8.0+',
                    'experience'     => $row['experience'] ?? 'Trên 5 năm kinh nghiệm giảng dạy chuyên sâu',
                    'rating'         => (float)($row['rating'] ?? 4.9),
                    'phone'          => $row['phone'] ?? '0988 123 456',
                    'email'          => $row['email'] ?? 'teacher@educenter.edu.vn'
                ];
            }
        }
    } catch (Exception $e) {
        $teachers = [];
    }
}

if (empty($teachers)) {
    $teachers = $sample_teachers;
}

require_once __DIR__ . '/includes/header.php';
?>

    <!-- 1. SUBPAGE HERO BANNER -->
    <section class="edu-page-banner">
        <div class="container text-center">
            <span class="edu-page-badge">ĐỘI NGŨ CHUYÊN GIA SƯ PHẠM</span>
            <h1 class="edu-page-title">Giảng Viên &amp; Gia Sư Tiêu Biểu</h1>
            <p class="edu-page-subtitle">
                100% Giảng viên sở hữu chứng chỉ quốc tế (IELTS 8.0 - 8.5+, TOEIC 990,
                TESOL, CELTA) và nhiều năm kinh nghiệm giảng dạy thực chiến.
            </p>
        </div>
    </section>

    <!-- 2. DANH SÁCH HỒ SƠ GIẢNG VIÊN & GIA SƯ -->
    <section class="edu-section pt-5">
        <div class="container">
            <div class="row g-4">
                <?php foreach ($teachers as $tc): ?>
                    <div class="col-md-6 col-lg-3">
                        <div class="teacher-card">
                            <div>
                                <img src="<?php echo htmlspecialchars($tc['avatar']); ?>"
                                     alt="<?php echo htmlspecialchars($tc['full_name']); ?>"
                                     class="teacher-img">

                                <h5 class="fw-bold mb-1 text-dark">
                                    <?php echo htmlspecialchars($tc['full_name']); ?>
                                </h5>
                                <p class="text-primary small fw-bold mb-2">
                                    <?php echo htmlspecialchars($tc['specialization']); ?>
                                </p>

                                <!-- Đánh giá 5 sao -->
                                <div class="d-flex justify-content-center align-items-center gap-1 text-warning small mb-3">
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                    <span class="text-dark ms-1 fw-bold"><?php echo htmlspecialchars((string)$tc['rating']); ?>/5.0</span>
                                </div>

                                <!-- Thông tin bằng cấp, kinh nghiệm, email -->
                                <div class="text-start small text-muted mb-3">
                                    <p class="mb-2">
                                        <i class="fa-solid fa-award text-warning me-1"></i>
                                        <strong>Bằng cấp:</strong> <?php echo htmlspecialchars($tc['degree']); ?>
                                    </p>
                                    <p class="mb-2">
                                        <i class="fa-solid fa-briefcase text-info me-1"></i>
                                        <strong>Kinh nghiệm:</strong> <?php echo htmlspecialchars($tc['experience']); ?>
                                    </p>
                                    <p class="mb-0">
                                        <i class="fa-solid fa-envelope text-secondary me-1"></i>
                                        <?php echo htmlspecialchars($tc['email']); ?>
                                    </p>
                                </div>
                            </div>

                            <div class="pt-2">
                                <a href="register.php?course=<?php echo urlencode('Gia sư Tiếng Anh 1 Kèm 1 Theo Yêu Cầu'); ?>&amp;tutor_id=<?php echo (int)$tc['teacher_id']; ?>"
                                   class="btn btn-outline-primary w-100 rounded-pill fw-semibold">
                                    <i class="fa-solid fa-calendar-check me-1"></i> Đặt lịch học thử 1-1
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- 3. TIÊU CHUẨN TUYỂN CHỌN GIẢNG VIÊN -->
            <div class="mt-5 p-4 p-md-5 bg-white rounded-4 border shadow-sm">
                <h3 class="fw-bold text-center mb-4 text-dark">
                    Tiêu Chuẩn Tuyển Chọn Giảng Viên Nghiêm Ngặt Tại EduCenter
                </h3>
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="d-flex gap-3">
                            <div class="bg-primary text-white p-3 rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:54px;height:54px;">
                                <i class="fa-solid fa-graduation-cap fs-4"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Chứng chỉ chuẩn Quốc tế</h6>
                                <p class="text-muted small mb-0">
                                    Tất cả giáo viên đều có bằng đại học chuyên ngành ngôn ngữ và chứng chỉ nghiệp vụ sư phạm quốc tế CELTA, TESOL.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="d-flex gap-3">
                            <div class="bg-success text-white p-3 rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:54px;height:54px;">
                                <i class="fa-solid fa-heart-pulse fs-4"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Phương pháp truyền cảm hứng</h6>
                                <p class="text-muted small mb-0">
                                    Không dạy lý thuyết suông, chú trọng phản xạ tự nhiên, tương tác 2 chiều và sửa lỗi tỉ mỉ từng chi tiết cho học viên.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="d-flex gap-3">
                            <div class="bg-warning text-dark p-3 rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:54px;height:54px;">
                                <i class="fa-solid fa-users-viewfinder fs-4"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Kèm sát và Báo cáo định kỳ</h6>
                                <p class="text-muted small mb-0">
                                    Giảng viên và gia sư chấm điểm hàng tuần, ghi chú nhận xét chi tiết sau mỗi buổi học trực tiếp trên hệ thống.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
