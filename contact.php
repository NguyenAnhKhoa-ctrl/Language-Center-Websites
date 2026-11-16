<?php
/**
 * EDUCENTER - HỆ THỐNG WEBSITE QUẢN LÝ TRUNG TÂM NGOẠI NGỮ & GIA SƯ
 * File: contact.php
 * Phụ trách (Tuần 2 - Lý Hạo Danh - 52400175):
 * Trang Liên hệ, Hệ thống Chi nhánh Hà Nội/TP.HCM, Biểu mẫu Đặt lịch hẹn & Chuyên mục FAQ
 */

$pageTitle = 'Liên Hệ & Hệ Thống Cơ Sở - EduCenter';
$currentPage = 'contact.php';

require_once __DIR__ . '/config/db.php';

$formData = [
    'name'    => '',
    'phone'   => '',
    'email'   => '',
    'topic'   => 'Tư vấn khóa học & Học phí',
    'message' => ''
];

$errors = [];
$sentSuccess = false;
$submittedName = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['name']    = trim($_POST['name'] ?? '');
    $formData['phone']   = trim($_POST['phone'] ?? '');
    $formData['email']   = trim($_POST['email'] ?? '');
    $formData['topic']   = trim($_POST['topic'] ?? 'Tư vấn khóa học & Học phí');
    $formData['message'] = trim($_POST['message'] ?? '');

    if ($formData['name'] === '' || mb_strlen($formData['name'], 'UTF-8') < 2) {
        $errors['name'] = 'Vui lòng nhập họ và tên của bạn.';
    }

    $cleanPhone = preg_replace('/[\s\.\-]+/', '', $formData['phone']);
    if ($cleanPhone === '' || !preg_match('/^(0|\+84)[0-9]{8,10}$/', $cleanPhone)) {
        $errors['phone'] = 'Số điện thoại không hợp lệ (VD: 0987654321).';
    }

    if ($formData['email'] !== '' && !filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Địa chỉ email không đúng định dạng.';
    }

    if (empty($errors)) {
        $sentSuccess = true;
        $submittedName = $formData['name'];
        $formData['name']    = '';
        $formData['phone']   = '';
        $formData['email']   = '';
        $formData['message'] = '';
    }
}

require_once __DIR__ . '/includes/header.php';
?>

    <!-- 1. SUBPAGE HERO BANNER -->
    <section class="edu-page-banner">
        <div class="container text-center">
            <span class="edu-page-badge">HỆ THỐNG CƠ SỞ ĐÀO TẠO</span>
            <h1 class="edu-page-title">Liên Hệ &amp; Đặt Lịch Tư Vấn Trực Tiếp</h1>
            <p class="edu-page-subtitle">
                EduCenter luôn sẵn sàng lắng nghe và giải đáp mọi thắc mắc của quý
                phụ huynh và các bạn học viên.
            </p>
        </div>
    </section>

    <!-- 2. THÔNG TIN CHI NHÁNH & BIỂU MẪU GỬI TIN NHẮN -->
    <section class="edu-section pt-5">
        <div class="container">
            <div class="row g-4 align-items-start">
                <!-- Cột trái: Trụ Sở & Chi Nhánh -->
                <div class="col-lg-5">
                    <div class="p-4 bg-white rounded-4 border shadow-sm">
                        <h4 class="fw-bold mb-4 text-dark">
                            <i class="fa-solid fa-map-location-dot text-primary me-2"></i>Trụ Sở &amp; Chi Nhánh
                        </h4>

                        <div class="d-flex gap-3 mb-4">
                            <div class="bg-primary-subtle text-primary p-3 rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:50px;height:50px;">
                                <i class="fa-solid fa-building fs-5"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Cơ sở 1 (Trụ sở chính Hà Nội)</h6>
                                <p class="text-muted small mb-1">128 Nguyễn Trãi, Thanh Xuân, Hà Nội</p>
                                <p class="text-muted small mb-0">
                                    <i class="fa-solid fa-phone me-1 text-primary"></i>024.3858.6868
                                </p>
                            </div>
                        </div>

                        <div class="d-flex gap-3 mb-4">
                            <div class="bg-success-subtle text-success p-3 rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:50px;height:50px;">
                                <i class="fa-solid fa-building fs-5"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Cơ sở 2 (Chi nhánh TP.HCM)</h6>
                                <p class="text-muted small mb-1">45 Võ Văn Tần, Phường 6, Quận 3, TP.HCM</p>
                                <p class="text-muted small mb-0">
                                    <i class="fa-solid fa-phone me-1 text-success"></i>028.3930.8888
                                </p>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex gap-3 mb-3">
                            <i class="fa-solid fa-clock text-warning fs-5 mt-1"></i>
                            <div>
                                <strong class="d-block small">Thời gian làm việc:</strong>
                                <small class="text-muted">Thứ Hai - Chủ Nhật: 08:00 - 21:30 (Mở cửa tất cả các ngày trong tuần)</small>
                            </div>
                        </div>

                        <div class="d-flex gap-3">
                            <i class="fa-solid fa-envelope text-info fs-5 mt-1"></i>
                            <div>
                                <strong class="d-block small">Email hỗ trợ:</strong>
                                <small class="text-muted">contact@educenter.edu.vn / tuyensinh@educenter.edu.vn</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cột phải: Gửi Tin Nhắn / Đặt Lịch Hẹn -->
                <div class="col-lg-7">
                    <div class="p-4 p-md-5 bg-white rounded-4 border shadow-sm">
                        <h4 class="fw-bold mb-2 text-dark">
                            <i class="fa-solid fa-paper-plane text-primary me-2"></i>Gửi Tin Nhắn / Đặt Lịch Hẹn
                        </h4>
                        <p class="text-muted small mb-4">
                            Bộ phận chăm sóc học viên sẽ phản hồi bạn qua điện thoại hoặc email trong vòng 2 giờ làm việc.
                        </p>

                        <?php if ($sentSuccess): ?>
                            <div class="alert alert-success p-4 text-center rounded-4 border-0 shadow-sm mb-4">
                                <i class="fa-solid fa-circle-check fs-2 text-success mb-2 d-block"></i>
                                <h5 class="fw-bold mb-1">Tin nhắn của bạn đã được gửi thành công!</h5>
                                <p class="mb-0 small text-muted">
                                    Cảm ơn <strong><?php echo htmlspecialchars($submittedName); ?></strong>, EduCenter đã tiếp nhận yêu cầu và sẽ liên hệ hỗ trợ bạn sớm nhất.
                                </p>
                            </div>
                        <?php endif; ?>

                        <form action="contact.php" method="POST" novalidate>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="contact_name" class="form-label fw-bold">
                                        Họ và tên <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           id="contact_name"
                                           name="name"
                                           class="form-control edu-input <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>"
                                           placeholder="Nguyễn Văn A"
                                           value="<?php echo htmlspecialchars($formData['name']); ?>"
                                           required>
                                    <?php if (isset($errors['name'])): ?>
                                        <div class="invalid-feedback"><?php echo htmlspecialchars($errors['name']); ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-md-6">
                                    <label for="contact_phone" class="form-label fw-bold">
                                        Số điện thoại <span class="text-danger">*</span>
                                    </label>
                                    <input type="tel"
                                           id="contact_phone"
                                           name="phone"
                                           class="form-control edu-input <?php echo isset($errors['phone']) ? 'is-invalid' : ''; ?>"
                                           placeholder="0987xxxxxx"
                                           value="<?php echo htmlspecialchars($formData['phone']); ?>"
                                           required>
                                    <?php if (isset($errors['phone'])): ?>
                                        <div class="invalid-feedback"><?php echo htmlspecialchars($errors['phone']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="contact_email" class="form-label fw-bold">Email của bạn</label>
                                <input type="email"
                                       id="contact_email"
                                       name="email"
                                       class="form-control edu-input <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>"
                                       placeholder="email@gmail.com"
                                       value="<?php echo htmlspecialchars($formData['email']); ?>">
                                <?php if (isset($errors['email'])): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['email']); ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label for="contact_topic" class="form-label fw-bold">Vấn đề cần hỗ trợ</label>
                                <?php
                                    $topics = [
                                        'Tư vấn khóa học & Học phí',
                                        'Đăng ký gia sư 1 kèm 1 theo giờ',
                                        'Tra cứu kết quả học tập & Đổi ca học',
                                        'Hợp tác giảng dạy & Đăng ký làm gia sư',
                                        'Khác'
                                    ];
                                ?>
                                <select id="contact_topic" name="topic" class="form-select edu-input">
                                    <?php foreach ($topics as $tp): ?>
                                        <option value="<?php echo htmlspecialchars($tp); ?>"
                                            <?php echo ($formData['topic'] === $tp) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($tp); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label for="contact_message" class="form-label fw-bold">Nội dung chi tiết</label>
                                <textarea id="contact_message"
                                          name="message"
                                          rows="4"
                                          class="form-control edu-input"
                                          placeholder="Nhập câu hỏi hoặc yêu cầu cụ thể của bạn..."><?php echo htmlspecialchars($formData['message']); ?></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 fw-semibold shadow-sm">
                                <i class="fa-solid fa-paper-plane me-2"></i>Gửi Tin Nhắn Ngay
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- 3. CÂU HỎI THƯỜNG GẶP (FAQ ACCORDION) -->
            <div class="mt-5 p-4 p-md-5 bg-white rounded-4 border shadow-sm">
                <h3 class="fw-bold text-center mb-4 text-dark">Câu Hỏi Thường Gặp (FAQ)</h3>
                <div class="accordion" id="accordionFaq">
                    <div class="accordion-item border-0 border-bottom">
                        <h2 class="accordion-header" id="headingOne">
                            <button class="accordion-button fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true">
                                1. Nếu tôi có việc bận đột xuất thì có được học bù không?
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#accordionFaq">
                            <div class="accordion-body text-secondary small">
                                Có. Học viên được hỗ trợ học bù sang các lớp song song cùng trình độ hoặc xem lại video bài giảng được ghi hình chất lượng cao trên hệ thống portal. Đối với lớp gia sư 1-1, học viên chỉ cần báo trước 4 tiếng để dời lịch mà không mất phí.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item border-0 border-bottom">
                        <h2 class="accordion-header" id="headingTwo">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                                2. Hệ thống tra cứu điểm và lịch học hoạt động như thế nào?
                            </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#accordionFaq">
                            <div class="accordion-body text-secondary small">
                                Học viên chỉ cần vào trang <strong>Tra cứu Điểm &amp; Lịch</strong> trên website, nhập Mã học viên (ví dụ HV001) hoặc Số điện thoại là có thể xem bảng điểm 4 kỹ năng, tỷ lệ điểm danh các buổi và lịch học cố định hàng tuần một cách tức thì.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item border-0">
                        <h2 class="accordion-header" id="headingThree">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree">
                                3. Trung tâm có chính sách hoàn tiền hoặc học lại miễn phí không?
                            </button>
                        </h2>
                        <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#accordionFaq">
                            <div class="accordion-body text-secondary small">
                                Tất cả học viên theo học đủ 90% số buổi và hoàn thành bài tập về nhà đều được ký hợp đồng cam kết đầu ra bằng văn bản. Nếu không đạt chứng chỉ cam kết, học viên được học lại miễn phí 100% cho đến khi đạt mục tiêu.
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
