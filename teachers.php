<?php
/**
 * EDUCENTER - HỆ THỐNG WEBSITE QUẢN LÝ TRUNG TÂM NGOẠI NGỮ & GIA SƯ
 * File: teachers.php
 * Phụ trách (Tuần 2 & Bổ sung Gói B3 - Lý Hạo Danh - 52400175):
 * Trang Đội ngũ Giảng viên & Gia sư tiêu biểu (Tích hợp Chấm điểm Rating 5 sao & Nhận xét từ Học viên)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = 'Đội Ngũ Giảng Viên & Gia Sư Uy Tín - EduCenter';
$currentPage = 'teachers.php';

require_once __DIR__ . '/config/db.php';

// Khởi tạo danh sách nhận xét & đánh giá 5 sao của học viên dành cho giảng viên
if (!isset($_SESSION['teacher_reviews'])) {
    $_SESSION['teacher_reviews'] = [
        [
            'teacher_id'   => 2,
            'teacher_name' => 'Cô Nguyễn Thảo My',
            'student_name' => 'Nguyễn Văn An (HV001)',
            'stars'        => 5,
            'comment'      => 'Cô Thảo My sửa bài Writing cực kỳ chi tiết, chỉ sau 2 tháng mình đã tăng từ 5.5 lên 7.5 IELTS!',
            'date'         => '14/10/2026'
        ],
        [
            'teacher_id'   => 3,
            'teacher_name' => 'Thầy Trần Đình Khôi',
            'student_name' => 'Trần Thị Bích Ngọc (HV002)',
            'stars'        => 5,
            'comment'      => 'Chiến thuật giải đề TOEIC của thầy Khôi rất dễ áp dụng, mình đạt 840/990 ngay lần thi đầu.',
            'date'         => '12/10/2026'
        ],
        [
            'teacher_id'   => 4,
            'teacher_name' => 'Cô Lê Hoàng Bảo Trâm',
            'student_name' => 'Lê Gia Huy (HV003)',
            'stars'        => 5,
            'comment'      => 'Học gia sư 1-1 với cô Bảo Trâm rất thoải mái, cô theo sát từng buổi và giải đáp tận tình.',
            'date'         => '11/10/2026'
        ]
    ];
}

if (!isset($_SESSION['teacher_ratings_map'])) {
    $_SESSION['teacher_ratings_map'] = [];
}

$ratingFlash = '';

// Xử lý khi Học viên / Phụ huynh gửi đánh giá Rating 5 sao cho Giảng viên
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'rate_teacher') {
    $teacherId   = (int)($_POST['teacher_id'] ?? 1);
    $teacherName = trim($_POST['teacher_name'] ?? 'Giảng viên EduCenter');
    $studentName = trim($_POST['student_name'] ?? 'Học viên EduCenter');
    $stars       = max(1, min(5, (int)($_POST['stars'] ?? 5)));
    $comment     = trim($_POST['review_comment'] ?? '');

    if ($studentName !== '' && $comment !== '') {
        array_unshift($_SESSION['teacher_reviews'], [
            'teacher_id'   => $teacherId,
            'teacher_name' => $teacherName,
            'student_name' => $studentName,
            'stars'        => $stars,
            'comment'      => $comment,
            'date'         => date('d/m/Y')
        ]);

        // Cập nhật điểm trung bình Rating
        $newRating = round((4.9 + $stars) / 2, 1);
        $_SESSION['teacher_ratings_map'][$teacherId] = $newRating;

        if (isset($_SESSION['admin_teachers'])) {
            foreach ($_SESSION['admin_teachers'] as &$atc) {
                if ((int)$atc['teacher_id'] === $teacherId) {
                    $atc['rating'] = $newRating;
                }
            }
            unset($atc);
        }

        if ($db_connected && $pdo) {
            try {
                $stmt = $pdo->prepare("UPDATE Teachers SET rating = :rt WHERE teacher_id = :tid");
                $stmt->execute([':rt' => $newRating, ':tid' => $teacherId]);
            } catch (Exception $e) {
                // Fallback Session
            }
        }

        $ratingFlash = 'Cảm ơn <strong>' . htmlspecialchars($studentName) . '</strong> đã gửi đánh giá <strong>' . $stars . '/5 sao</strong> cho <strong>' . htmlspecialchars($teacherName) . '</strong>!';
    }
}

// Lấy danh sách giảng viên từ CSDL MySQL hoặc Mock Data Fallback
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
                    'rating'         => $_SESSION['teacher_ratings_map'][$tid] ?? (float)($row['rating'] ?? 4.9),
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
    $teachers = $_SESSION['admin_teachers'] ?? $sample_teachers;
    foreach ($teachers as &$tItem) {
        $tid = (int)$tItem['teacher_id'];
        if (isset($_SESSION['teacher_ratings_map'][$tid])) {
            $tItem['rating'] = $_SESSION['teacher_ratings_map'][$tid];
        }
    }
    unset($tItem);
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
            <?php if ($ratingFlash !== ''): ?>
                <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
                    <i class="fa-solid fa-star text-warning me-2"></i><?php echo $ratingFlash; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

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

                            <div class="pt-2 d-grid gap-2">
                                <a href="register.php?course=<?php echo urlencode('Gia sư Tiếng Anh 1 Kèm 1 Theo Yêu Cầu'); ?>&amp;tutor_id=<?php echo (int)$tc['teacher_id']; ?>"
                                   class="btn btn-outline-primary w-100 rounded-pill fw-semibold btn-sm py-2">
                                    <i class="fa-solid fa-calendar-check me-1"></i> Đặt lịch học thử 1-1
                                </a>
                                <button type="button"
                                        class="btn btn-warning btn-sm rounded-pill fw-semibold text-dark btn-open-rate-modal"
                                        data-bs-toggle="modal"
                                        data-bs-target="#rateTeacherModal"
                                        data-id="<?php echo (int)$tc['teacher_id']; ?>"
                                        data-name="<?php echo htmlspecialchars($tc['full_name']); ?>">
                                    <i class="fa-solid fa-star me-1"></i> Đánh giá 5 sao
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- 3. ĐÁNH GIÁ & PHẢN HỒI THỰC TẾ TỪ HỌC VIÊN (VERIFIED REVIEWS) -->
            <div class="mt-5 p-4 p-md-5 bg-white rounded-4 border shadow-sm">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
                    <div>
                        <h4 class="fw-bold text-dark mb-1">
                            <i class="fa-solid fa-comments text-primary me-2"></i>Đánh Giá 5 Sao Từ Học Viên &amp; Phụ Huynh
                        </h4>
                        <p class="text-muted small mb-0">Phản hồi minh bạch từ các học viên đã và đang theo học tại EduCenter</p>
                    </div>
                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold">
                        <i class="fa-solid fa-star me-1"></i>4.9/5.0 Điểm Hài Lòng Trung Bình
                    </span>
                </div>

                <div class="row g-3">
                    <?php foreach (array_slice($_SESSION['teacher_reviews'], 0, 3) as $rev): ?>
                        <div class="col-md-4">
                            <div class="p-3 rounded-4 bg-light border h-100 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge bg-primary-subtle text-primary"><?php echo htmlspecialchars($rev['teacher_name']); ?></span>
                                        <span class="text-warning small">
                                            <?php for ($i = 0; $i < (int)$rev['stars']; $i++): ?>
                                                <i class="fa-solid fa-star"></i>
                                            <?php endfor; ?>
                                        </span>
                                    </div>
                                    <p class="small text-secondary fst-italic mb-3">
                                        "<?php echo htmlspecialchars($rev['comment']); ?>"
                                    </p>
                                </div>
                                <div class="d-flex justify-content-between align-items-center border-top pt-2 small">
                                    <strong class="text-dark"><?php echo htmlspecialchars($rev['student_name']); ?></strong>
                                    <span class="text-muted"><?php echo htmlspecialchars($rev['date']); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- 4. TIÊU CHUẨN TUYỂN CHỌN GIẢNG VIÊN -->
            <div class="mt-4 p-4 p-md-5 bg-white rounded-4 border shadow-sm">
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

    <!-- MODAL CHẤM ĐIỂM ĐÁNH GIÁ GIẢNG VIÊN 5 SAO -->
    <div class="modal fade" id="rateTeacherModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow rounded-4">
                <form action="teachers.php" method="POST">
                    <input type="hidden" name="action" value="rate_teacher">
                    <input type="hidden" name="teacher_id" id="rate_teacher_id" value="1">
                    <input type="hidden" name="teacher_name" id="rate_teacher_name_input" value="Cô Nguyễn Thảo My">
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="modal-title fw-bold text-dark">
                            <i class="fa-solid fa-star text-warning me-2"></i>Đánh Giá Chất Lượng Giảng Viên
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="small text-muted mb-3">
                            Gửi đánh giá của bạn dành cho giảng viên <strong class="text-primary" id="rate_teacher_display"></strong>
                        </p>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Họ tên &amp; Mã học viên của bạn <span class="text-danger">*</span></label>
                            <input type="text" name="student_name" class="form-control" placeholder="VD: Nguyễn Văn An (HV001)" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Mức độ hài lòng (Số sao) <span class="text-danger">*</span></label>
                            <select name="stars" class="form-select">
                                <option value="5" selected>5 Sao — Xuất sắc, giảng dạy tận tâm &amp; dễ hiểu</option>
                                <option value="4">4 Sao — Rất tốt, nhiệt tình hỗ trợ học viên</option>
                                <option value="3">3 Sao — Khá, đáp ứng mục tiêu môn học</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-semibold small">Nhận xét chi tiết <span class="text-danger">*</span></label>
                            <textarea name="review_comment" rows="3" class="form-control" placeholder="Chia sẻ cảm nhận của bạn về phương pháp giảng dạy và sự tiến bộ..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold text-dark">
                            <i class="fa-solid fa-paper-plane me-1"></i>Gửi Đánh Giá 5 Sao
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.btn-open-rate-modal').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var tid = this.getAttribute('data-id');
                var tname = this.getAttribute('data-name');
                document.getElementById('rate_teacher_id').value = tid;
                document.getElementById('rate_teacher_name_input').value = tname;
                document.getElementById('rate_teacher_display').textContent = tname;
            });
        });
    });
    </script>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
