<?php
/**
 * EDUCENTER - HỆ THỐNG WEBSITE QUẢN LÝ TRUNG TÂM NGOẠI NGỮ & GIA SƯ
 * File: register.php
 * Phụ trách (Tuần 2 - Nguyễn Anh Khoa - 52400051):
 * Trang Đăng ký Khóa học & Đặt lịch Kiểm tra Trình độ 24/7 (kèm Validation & Lưu hồ sơ)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = 'Đăng Ký Khóa Học & Tư Vấn Lộ Trình - EduCenter';
$currentPage = 'register.php';

require_once __DIR__ . '/config/db.php';

// Lấy danh sách khóa học để hiển thị trong select box
$courseOptions = [];
if ($db_connected && $pdo) {
    try {
        $stmt = $pdo->query("SELECT course_name FROM Courses ORDER BY course_id ASC");
        $courseOptions = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {
        $courseOptions = [];
    }
}
if (empty($courseOptions)) {
    foreach ($sample_courses as $c) {
        $courseOptions[] = $c['course_name'];
    }
}

// Khởi tạo giá trị mặc định cho form
$formData = [
    'full_name'      => '',
    'phone'          => '',
    'email'          => '',
    'course_name'    => isset($_GET['course']) ? trim($_GET['course']) : ($courseOptions[0] ?? 'Luyện thi IELTS Intensive (Mục tiêu 6.5 - 7.5+)'),
    'preferred_time' => 'Ca Tối (18:00 - 19:30)',
    'notes'          => ''
];

$errors = [];
$successTicket = null;

// Xử lý khi người dùng nhấn Submit form đăng ký
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['full_name']      = trim($_POST['full_name'] ?? '');
    $formData['phone']          = trim($_POST['phone'] ?? '');
    $formData['email']          = trim($_POST['email'] ?? '');
    $formData['course_name']    = trim($_POST['course_name'] ?? '');
    $formData['preferred_time'] = trim($_POST['preferred_time'] ?? 'Ca Tối (18:00 - 19:30)');
    $formData['notes']          = trim($_POST['notes'] ?? '');

    // 1. Server-side Validation
    if ($formData['full_name'] === '' || mb_strlen($formData['full_name'], 'UTF-8') < 2) {
        $errors['full_name'] = 'Vui lòng nhập đầy đủ họ và tên học viên (tối thiểu 2 ký tự).';
    }

    $cleanPhone = preg_replace('/[\s\.\-]+/', '', $formData['phone']);
    if ($cleanPhone === '' || !preg_match('/^(0|\+84)[0-9]{8,10}$/', $cleanPhone)) {
        $errors['phone'] = 'Số điện thoại không hợp lệ (VD hợp lệ: 0987654321).';
    }

    if ($formData['email'] !== '' && !filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Địa chỉ email không đúng định dạng (VD: email@gmail.com).';
    }

    if ($formData['course_name'] === '') {
        $errors['course_name'] = 'Vui lòng chọn khóa học hoặc dịch vụ gia sư bạn quan tâm.';
    }

    // 2. Nếu dữ liệu hợp lệ -> Lưu vào CSDL MySQL & Session Fallback
    if (empty($errors)) {
        $ticketCode = 'DK' . random_int(102000, 999999);

        if ($db_connected && $pdo) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO Course_Registrations (ticket_code, full_name, phone, email, course_name, preferred_time, notes, status)
                    VALUES (:ticket_code, :full_name, :phone, :email, :course_name, :preferred_time, :notes, 'Chờ liên hệ')
                ");
                $stmt->execute([
                    ':ticket_code'    => $ticketCode,
                    ':full_name'      => $formData['full_name'],
                    ':phone'          => $formData['phone'],
                    ':email'          => $formData['email'],
                    ':course_name'    => $formData['course_name'],
                    ':preferred_time' => $formData['preferred_time'],
                    ':notes'          => $formData['notes']
                ]);
            } catch (Exception $e) {
                // Nếu chưa tạo bảng hoặc lỗi DB vẫn tiếp tục lưu vào Session Fallback
            }
        }

        // Lưu đồng thời vào Session để đồng bộ ngay với trang Admin khi chạy chế độ Fallback
        if (!isset($_SESSION['new_registrations'])) {
            $_SESSION['new_registrations'] = [];
        }
        array_unshift($_SESSION['new_registrations'], [
            'ticket_code'    => $ticketCode,
            'full_name'      => $formData['full_name'],
            'phone'          => $formData['phone'],
            'email'          => $formData['email'],
            'course_name'    => $formData['course_name'],
            'preferred_time' => $formData['preferred_time'],
            'notes'          => $formData['notes'],
            'status'         => 'Chờ liên hệ',
            'badge_class'    => 'bg-warning text-dark',
            'created_at'     => date('d/m/Y H:i')
        ]);

        $successTicket = [
            'ticket_code'    => $ticketCode,
            'full_name'      => $formData['full_name'],
            'phone'          => $formData['phone'],
            'course_name'    => $formData['course_name'],
            'preferred_time' => $formData['preferred_time']
        ];

        // Reset form sau khi đăng ký thành công
        $formData['full_name'] = '';
        $formData['phone']     = '';
        $formData['email']     = '';
        $formData['notes']     = '';
    }
}

require_once __DIR__ . '/includes/header.php';
?>

    <!-- 1. SUBPAGE HERO BANNER -->
    <section class="edu-page-banner">
        <div class="container text-center">
            <span class="edu-page-badge">ĐĂNG KÝ TRỰC TUYẾN 24/7</span>
            <h1 class="edu-page-title">Đăng Ký Khóa Học &amp; Kiểm Tra Trình Độ</h1>
            <p class="edu-page-subtitle">
                Nhận ngay học bổng giảm 20% học phí, tặng tài khoản học online độc quyền
                và bộ đề dự đoán sát thực tế.
            </p>
        </div>
    </section>

    <!-- 2. BIỂU MẪU ĐĂNG KÝ & QUYỀN LỢI HỌC VIÊN -->
    <section class="edu-section pt-5">
        <div class="container">
            <?php if ($successTicket !== null): ?>
                <!-- Thông báo đăng ký thành công -->
                <div class="alert alert-success border-0 shadow-sm rounded-4 p-4 mb-4">
                    <div class="d-flex align-items-start gap-3">
                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:44px;height:44px;">
                            <i class="fa-solid fa-check fs-5"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="fw-bold text-success mb-1">
                                Đăng ký giữ chỗ thành công! Mã hồ sơ của bạn: <span class="badge bg-success"><?php echo htmlspecialchars($successTicket['ticket_code']); ?></span>
                            </h5>
                            <p class="mb-2 text-dark">
                                Cảm ơn học viên <strong><?php echo htmlspecialchars($successTicket['full_name']); ?></strong> (SĐT: <strong><?php echo htmlspecialchars($successTicket['phone']); ?></strong>) đã đăng ký chương trình
                                <strong><?php echo htmlspecialchars($successTicket['course_name']); ?></strong> (<?php echo htmlspecialchars($successTicket['preferred_time']); ?>).
                            </p>
                            <div class="d-flex flex-wrap gap-2 mt-2">
                                <span class="small text-muted">
                                    <i class="fa-solid fa-bolt text-warning me-1"></i>Hồ sơ đã được chuyển trực tiếp về Ban Quản Trị để xếp lịch Test đầu vào miễn phí.
                                </span>
                                <a href="admin/index.php" class="btn btn-sm btn-outline-success ms-auto">
                                    <i class="fa-solid fa-gauge-high me-1"></i>Xem trên Admin Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="row g-4 align-items-start">
                <!-- Cột trái: Biểu mẫu Thông Tin Học Viên -->
                <div class="col-lg-7">
                    <div class="edu-form-card">
                        <div class="mb-4">
                            <h4 class="fw-bold text-dark mb-1">
                                <i class="fa-solid fa-user-tag text-primary me-2"></i>Thông Tin Học Viên
                            </h4>
                            <p class="text-muted small mb-0">
                                Điền đầy đủ thông tin để nhận tư vấn lộ trình học phù hợp nhất.
                            </p>
                        </div>

                        <form id="courseRegistrationForm" method="POST" action="register.php" novalidate>
                            <div class="row g-3">
                                <!-- Họ và tên học viên -->
                                <div class="col-12">
                                    <label for="full_name" class="form-label fw-semibold">
                                        Họ và tên học viên <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           class="form-control edu-input <?php echo isset($errors['full_name']) ? 'is-invalid' : ''; ?>"
                                           id="full_name"
                                           name="full_name"
                                           placeholder="Nguyễn Văn A"
                                           value="<?php echo htmlspecialchars($formData['full_name']); ?>"
                                           required>
                                    <div class="invalid-feedback" id="fullNameFeedback">
                                        <?php echo htmlspecialchars($errors['full_name'] ?? 'Vui lòng nhập họ và tên học viên.'); ?>
                                    </div>
                                </div>

                                <!-- Số điện thoại -->
                                <div class="col-md-6">
                                    <label for="phone" class="form-label fw-semibold">
                                        Số điện thoại <span class="text-danger">*</span>
                                    </label>
                                    <input type="tel"
                                           class="form-control edu-input <?php echo isset($errors['phone']) ? 'is-invalid' : ''; ?>"
                                           id="phone"
                                           name="phone"
                                           placeholder="0987xxxxxx"
                                           value="<?php echo htmlspecialchars($formData['phone']); ?>"
                                           required>
                                    <div class="invalid-feedback" id="phoneFeedback">
                                        <?php echo htmlspecialchars($errors['phone'] ?? 'Vui lòng nhập số điện thoại hợp lệ (10 số).'); ?>
                                    </div>
                                </div>

                                <!-- Email liên hệ -->
                                <div class="col-md-6">
                                    <label for="email" class="form-label fw-semibold">
                                        Email liên hệ
                                    </label>
                                    <input type="email"
                                           class="form-control edu-input <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>"
                                           id="email"
                                           name="email"
                                           placeholder="email@gmail.com"
                                           value="<?php echo htmlspecialchars($formData['email']); ?>">
                                    <div class="invalid-feedback" id="emailFeedback">
                                        <?php echo htmlspecialchars($errors['email'] ?? 'Địa chỉ email không đúng định dạng.'); ?>
                                    </div>
                                </div>

                                <!-- Khóa học đăng ký -->
                                <div class="col-md-7">
                                    <label for="course_name" class="form-label fw-semibold">
                                        Khóa học / Dịch vụ quan tâm <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select edu-input <?php echo isset($errors['course_name']) ? 'is-invalid' : ''; ?>"
                                            id="course_name"
                                            name="course_name"
                                            required>
                                        <?php foreach ($courseOptions as $option): ?>
                                            <option value="<?php echo htmlspecialchars($option); ?>"
                                                <?php echo ($formData['course_name'] === $option) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($option); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Ca học mong muốn -->
                                <div class="col-md-5">
                                    <label for="preferred_time" class="form-label fw-semibold">
                                        Khung giờ học mong muốn <span class="text-danger">*</span>
                                    </label>
                                    <?php
                                        $timeSlots = [
                                            'Ca Tối (18:00 - 19:30)',
                                            'Ca Tối (19:45 - 21:15)',
                                            'Cuối tuần (Thứ 7 - CN)',
                                            'Linh hoạt theo lịch cá nhân (1-1)'
                                        ];
                                    ?>
                                    <select class="form-select edu-input" id="preferred_time" name="preferred_time">
                                        <?php foreach ($timeSlots as $slot): ?>
                                            <option value="<?php echo htmlspecialchars($slot); ?>"
                                                <?php echo ($formData['preferred_time'] === $slot) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($slot); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Mục tiêu học tập / Ghi chú -->
                                <div class="col-12">
                                    <label for="notes" class="form-label fw-semibold">
                                        Mục tiêu đầu ra &amp; Yêu cầu đặc biệt (nếu có)
                                    </label>
                                    <textarea class="form-control edu-input"
                                              id="notes"
                                              name="notes"
                                              rows="3"
                                              placeholder="VD: Cần đạt IELTS 7.0 trước tháng 12 để nộp hồ sơ du học, hoặc muốn học gia sư kèm riêng 1-1..."><?php echo htmlspecialchars($formData['notes']); ?></textarea>
                                </div>

                                <!-- Nút gửi đăng ký -->
                                <div class="col-12 pt-2">
                                    <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-3 shadow-sm">
                                        <i class="fa-solid fa-paper-plane me-2"></i>Gửi Đăng Ký &amp; Đặt Lịch Test Trình Độ Miễn Phí
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Cột phải: Quyền Lợi Độc Quyền Khi Đăng Ký -->
                <div class="col-lg-5">
                    <div class="edu-benefit-card">
                        <h5 class="fw-bold text-dark mb-4">
                            <i class="fa-solid fa-gift text-danger me-2"></i>Quyền Lợi Độc Quyền Khi Đăng Ký
                        </h5>

                        <div class="edu-benefit-item">
                            <div class="edu-benefit-check">
                                <i class="fa-solid fa-circle-check"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Test trình độ 4 kỹ năng miễn phí</h6>
                                <p class="text-muted small mb-0">
                                    Được đánh giá trực tiếp cùng giám khảo có chuyên môn cao.
                                </p>
                            </div>
                        </div>

                        <div class="edu-benefit-item">
                            <div class="edu-benefit-check">
                                <i class="fa-solid fa-circle-check"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Học bổng giảm 20% học phí</h6>
                                <p class="text-muted small mb-0">
                                    Áp dụng cho học viên đăng ký giữ chỗ trực tuyến trong tháng.
                                </p>
                            </div>
                        </div>

                        <div class="edu-benefit-item">
                            <div class="edu-benefit-check">
                                <i class="fa-solid fa-circle-check"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Cam kết đầu ra bằng văn bản pháp lý</h6>
                                <p class="text-muted small mb-0">
                                    Học lại miễn phí 100% nếu không đạt điểm cam kết.
                                </p>
                            </div>
                        </div>

                        <div class="edu-benefit-item mb-0">
                            <div class="edu-benefit-check">
                                <i class="fa-solid fa-circle-check"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Tài khoản tra cứu tiến độ trọn đời</h6>
                                <p class="text-muted small mb-0">
                                    Theo dõi điểm thi 4 kỹ năng, nhật ký điểm danh và lịch học minh bạch 24/7.
                                </p>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="p-3 rounded-3 bg-primary bg-opacity-10 border border-primary border-opacity-25">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:42px;height:42px;">
                                    <i class="fa-solid fa-headset"></i>
                                </div>
                                <div>
                                    <div class="small text-muted">Cần tư vấn nhanh qua Hotline 24/7?</div>
                                    <div class="fw-bold text-primary fs-6">1900 6868 - 0988 123 456</div>
                                </div>
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
