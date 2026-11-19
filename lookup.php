<?php
/**
 * EDUCENTER - HỆ THỐNG WEBSITE QUẢN LÝ TRUNG TÂM NGOẠI NGỮ & GIA SƯ
 * File: lookup.php
 * Phụ trách (Tuần 2 - Lý Hạo Danh - 52400175):
 * Cổng Tra cứu Bảng điểm 4 kỹ năng, Điểm danh & Lịch học trực tuyến
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = 'Tra Cứu Bảng Điểm & Lịch Học Trực Tuyến - EduCenter';
$currentPage = 'lookup.php';

require_once __DIR__ . '/config/db.php';

$search_query = isset($_GET['student_id']) ? trim(strip_tags((string)$_GET['student_id'])) : '';
$student_data = null;
$searched = false;

if ($search_query !== '') {
    $searched = true;
    $student_data = lookupStudentData($search_query, $pdo, $db_connected, $sample_student_lookup);
}

require_once __DIR__ . '/includes/header.php';
?>

    <!-- 1. SUBPAGE HERO BANNER -->
    <section class="edu-page-banner">
        <div class="container text-center">
            <span class="edu-page-badge">HỆ THỐNG TRA CỨU ĐIỆN TỬ</span>
            <h1 class="edu-page-title">Tra Cứu Bảng Điểm &amp; Lịch Học</h1>
            <p class="edu-page-subtitle">
                Học viên và phụ huynh dễ dàng tra cứu điểm kiểm tra định kỳ, tỷ lệ
                chuyên cần điểm danh và lịch học các buổi tiếp theo.
            </p>
        </div>
    </section>

    <!-- 2. KHUNG TÌM KIẾM & KẾT QUẢ TRA CỨU -->
    <section class="edu-section pt-5">
        <div class="container">
            <!-- Biểu mẫu nhập mã học viên / SĐT -->
            <div class="row justify-content-center mb-5">
                <div class="col-lg-8">
                    <div class="lookup-card">
                        <h4 class="fw-bold text-center mb-3 text-dark">
                            <i class="fa-solid fa-id-card-clip text-primary me-2"></i>Nhập Thông Tin Tra Cứu
                        </h4>

                        <form action="lookup.php" method="GET" id="lookupForm" class="search-input-group d-flex shadow-sm mb-3">
                            <input type="text"
                                   name="student_id"
                                   id="studentSearchInput"
                                   class="form-control"
                                   placeholder="Nhập Mã học viên (VD: HV001, HV002) hoặc Số điện thoại..."
                                   value="<?php echo htmlspecialchars($search_query); ?>"
                                   required
                                   autofocus>
                            <button class="btn btn-primary px-4" type="submit">
                                <i class="fa-solid fa-magnifying-glass me-2"></i>Tra cứu
                            </button>
                        </form>

                        <div class="d-flex flex-wrap align-items-center justify-content-center gap-2 small">
                            <span class="text-muted">
                                <i class="fa-solid fa-lightbulb text-warning me-1"></i>Bấm để thử ngay dữ liệu mẫu:
                            </span>
                            <a href="lookup.php?student_id=HV001" class="badge bg-light text-primary border demo-fill-id text-decoration-none px-2 py-1" data-id="HV001">HV001 (IELTS)</a>
                            <a href="lookup.php?student_id=HV002" class="badge bg-light text-primary border demo-fill-id text-decoration-none px-2 py-1" data-id="HV002">HV002 (TOEIC)</a>
                            <a href="lookup.php?student_id=HV003" class="badge bg-light text-primary border demo-fill-id text-decoration-none px-2 py-1" data-id="HV003">HV003 (Gia sư 1-1)</a>
                            <a href="lookup.php?student_id=0987654321" class="badge bg-light text-primary border demo-fill-id text-decoration-none px-2 py-1" data-id="0987654321">SĐT: 0987654321</a>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($searched): ?>
                <?php if ($student_data): ?>
                    <!-- KẾT QUẢ TRA CỨU HỢP LỆ -->
                    <div class="bg-white rounded-4 border shadow-sm p-4 p-md-5" id="printArea">
                        <div class="d-flex flex-wrap justify-content-between align-items-center border-bottom pb-3 mb-4">
                            <div>
                                <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-bold mb-2">
                                    <i class="fa-solid fa-circle-check me-1"></i>ĐÃ TÌM THẤY KẾT QUẢ
                                </span>
                                <h2 class="fw-bold mb-0 text-dark"><?php echo htmlspecialchars($student_data['full_name']); ?></h2>
                                <span class="text-muted small">
                                    Mã học viên: <strong><?php echo htmlspecialchars($student_data['student_id']); ?></strong> |
                                    Ngày sinh: <?php echo htmlspecialchars($student_data['dob']); ?>
                                </span>
                            </div>
                            <div class="mt-3 mt-md-0 d-flex gap-2">
                                <button type="button" class="btn btn-outline-secondary rounded-pill" id="btnPrintResult">
                                    <i class="fa-solid fa-print me-1"></i>In phiếu kết quả
                                </button>
                                <span class="badge bg-primary text-white d-flex align-items-center px-3 py-2 rounded-pill">
                                    <?php echo htmlspecialchars($student_data['status']); ?>
                                </span>
                            </div>
                        </div>

                        <!-- THÔNG TIN LỚP HỌC & GIẢNG VIÊN -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <div class="p-3 bg-light rounded-3 border h-100">
                                    <small class="text-muted d-block">Lớp học hiện tại</small>
                                    <strong class="text-primary fs-6"><?php echo htmlspecialchars($student_data['class_name']); ?></strong>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-3 bg-light rounded-3 border h-100">
                                    <small class="text-muted d-block">Giảng viên / Gia sư</small>
                                    <strong class="text-dark fs-6"><?php echo htmlspecialchars($student_data['teacher_name']); ?></strong>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-3 bg-light rounded-3 border h-100">
                                    <small class="text-muted d-block">Phòng học</small>
                                    <strong class="text-dark fs-6"><?php echo htmlspecialchars($student_data['room']); ?></strong>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-3 bg-light rounded-3 border h-100">
                                    <small class="text-muted d-block">Số điện thoại liên hệ</small>
                                    <strong class="text-dark fs-6"><?php echo htmlspecialchars($student_data['phone']); ?></strong>
                                </div>
                            </div>
                        </div>

                        <!-- TAB ĐIỂM SỐ - ĐIỂM DANH - LỊCH HỌC -->
                        <ul class="nav nav-pills mb-4 border-bottom pb-3 gap-2" id="lookupTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active px-4 py-2 fw-bold" id="grades-tab" data-bs-toggle="pill" data-bs-target="#tab-grades" type="button" role="tab">
                                    <i class="fa-solid fa-star me-2 text-warning"></i>Bảng Điểm &amp; Đánh Giá
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link px-4 py-2 fw-bold" id="attendance-tab" data-bs-toggle="pill" data-bs-target="#tab-attendance" type="button" role="tab">
                                    <i class="fa-solid fa-clipboard-check me-2 text-success"></i>Chuyên Cần &amp; Điểm Danh
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link px-4 py-2 fw-bold" id="schedule-tab" data-bs-toggle="pill" data-bs-target="#tab-schedule" type="button" role="tab">
                                    <i class="fa-solid fa-calendar-days me-2 text-primary"></i>Lịch Học Tuần Này
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content" id="lookupTabsContent">
                            <!-- TAB 1: BẢNG ĐIỂM 4 KỸ NĂNG -->
                            <div class="tab-pane fade show active" id="tab-grades" role="tabpanel">
                                <div class="row g-4 align-items-center mb-4">
                                    <div class="col-md-8">
                                        <div class="row g-3 text-center">
                                            <?php if ($student_data['grades']['listening'] !== null): ?>
                                                <div class="col-6 col-sm-3">
                                                    <div class="p-3 rounded-3 border bg-light h-100">
                                                        <span class="score-badge-circle bg-primary-subtle text-primary">
                                                            <?php echo htmlspecialchars((string)$student_data['grades']['listening']); ?>
                                                        </span>
                                                        <strong class="d-block text-secondary small">Nghe (Listening)</strong>
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                            <?php if ($student_data['grades']['speaking'] !== null): ?>
                                                <div class="col-6 col-sm-3">
                                                    <div class="p-3 rounded-3 border bg-light h-100">
                                                        <span class="score-badge-circle bg-success-subtle text-success">
                                                            <?php echo htmlspecialchars((string)$student_data['grades']['speaking']); ?>
                                                        </span>
                                                        <strong class="d-block text-secondary small">Nói (Speaking)</strong>
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                            <?php if ($student_data['grades']['reading'] !== null): ?>
                                                <div class="col-6 col-sm-3">
                                                    <div class="p-3 rounded-3 border bg-light h-100">
                                                        <span class="score-badge-circle bg-info-subtle text-info">
                                                            <?php echo htmlspecialchars((string)$student_data['grades']['reading']); ?>
                                                        </span>
                                                        <strong class="d-block text-secondary small">Đọc (Reading)</strong>
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                            <?php if ($student_data['grades']['writing'] !== null): ?>
                                                <div class="col-6 col-sm-3">
                                                    <div class="p-3 rounded-3 border bg-light h-100">
                                                        <span class="score-badge-circle bg-warning-subtle text-warning">
                                                            <?php echo htmlspecialchars((string)$student_data['grades']['writing']); ?>
                                                        </span>
                                                        <strong class="d-block text-secondary small">Viết (Writing)</strong>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-center border-start ps-md-4">
                                        <small class="text-muted text-uppercase fw-bold">Điểm Tổng Kết (Overall)</small>
                                        <div class="display-4 fw-bold text-danger my-1">
                                            <?php echo htmlspecialchars((string)$student_data['grades']['overall']); ?>
                                        </div>
                                        <span class="badge bg-danger-subtle text-danger fs-6 px-3 py-2 rounded-pill">
                                            <?php echo htmlspecialchars($student_data['grades']['rank']); ?>
                                        </span>
                                    </div>
                                </div>

                                <!-- Nhận xét của giảng viên -->
                                <div class="p-4 bg-light rounded-3 border">
                                    <h6 class="fw-bold text-dark mb-2">
                                        <i class="fa-solid fa-comment-dots text-primary me-2"></i>Nhận xét &amp; Đánh giá từ Giảng viên phụ trách:
                                    </h6>
                                    <p class="mb-0 text-secondary fst-italic">
                                        "<?php echo htmlspecialchars($student_data['grades']['comment']); ?>"
                                    </p>
                                </div>
                            </div>

                            <!-- TAB 2: ĐIỂM DANH & CHUYÊN CẦN -->
                            <div class="tab-pane fade" id="tab-attendance" role="tabpanel">
                                <div class="row g-4 mb-2">
                                    <div class="col-md-4 text-center">
                                        <div class="p-4 border rounded-4 bg-light h-100">
                                            <h6 class="text-muted mb-2">Tỷ lệ Chuyên cần</h6>
                                            <div class="display-5 fw-bold text-success mb-2">
                                                <?php echo htmlspecialchars($student_data['attendance']['rate']); ?>
                                            </div>
                                            <div class="progress" style="height: 10px;">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo htmlspecialchars($student_data['attendance']['rate']); ?>;"></div>
                                            </div>
                                            <div class="d-flex justify-content-between small text-muted mt-3">
                                                <span>Có mặt: <strong><?php echo (int)$student_data['attendance']['present']; ?></strong></span>
                                                <span>Có phép: <strong><?php echo (int)$student_data['attendance']['absent_allowed']; ?></strong></span>
                                                <span>Vắng: <strong><?php echo (int)$student_data['attendance']['absent_unallowed']; ?></strong></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-8">
                                        <h6 class="fw-bold mb-3">
                                            <i class="fa-solid fa-clock-rotate-left text-primary me-2"></i>Nhật ký điểm danh các buổi gần đây:
                                        </h6>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover align-middle mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Ngày học</th>
                                                        <th>Nội dung bài học</th>
                                                        <th>Trạng thái</th>
                                                        <th>Ghi chú</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($student_data['attendance']['recent_history'] as $att): ?>
                                                        <tr>
                                                            <td><strong><?php echo htmlspecialchars($att['date']); ?></strong></td>
                                                            <td><?php echo htmlspecialchars($att['lesson']); ?></td>
                                                            <td>
                                                                <?php if ($att['status'] === 'Có mặt'): ?>
                                                                    <span class="badge bg-success-subtle text-success"><i class="fa-solid fa-check me-1"></i>Có mặt</span>
                                                                <?php elseif ($att['status'] === 'Có phép'): ?>
                                                                    <span class="badge bg-warning-subtle text-warning"><i class="fa-solid fa-clock me-1"></i>Có phép</span>
                                                                <?php else: ?>
                                                                    <span class="badge bg-danger-subtle text-danger"><i class="fa-solid fa-xmark me-1"></i>Vắng mặt</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td class="small text-muted"><?php echo htmlspecialchars($att['note']); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- TAB 3: LỊCH HỌC TUẦN -->
                            <div class="tab-pane fade" id="tab-schedule" role="tabpanel">
                                <h6 class="fw-bold mb-3">
                                    <i class="fa-solid fa-calendar-check text-primary me-2"></i>Lịch học cố định hàng tuần:
                                </h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered text-center align-middle mb-0">
                                        <thead class="table-primary">
                                            <tr>
                                                <th>Thứ trong tuần</th>
                                                <th>Khung giờ học</th>
                                                <th>Phòng học</th>
                                                <th>Giảng viên / Gia sư</th>
                                                <th>Trạng thái</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($student_data['schedule'] as $sc): ?>
                                                <tr>
                                                    <td class="fw-bold"><?php echo htmlspecialchars($sc['day']); ?></td>
                                                    <td><span class="badge bg-light text-dark border fs-6"><?php echo htmlspecialchars($sc['time']); ?></span></td>
                                                    <td><?php echo htmlspecialchars($sc['room']); ?></td>
                                                    <td><?php echo htmlspecialchars($sc['teacher']); ?></td>
                                                    <td><span class="badge bg-success">Đang diễn ra</span></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- KHÔNG TÌM THẤY KẾT QUẢ -->
                    <div class="bg-white rounded-4 border p-5 text-center shadow-sm">
                        <div class="bg-warning-subtle text-warning rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width:75px;height:75px;">
                            <i class="fa-solid fa-triangle-exclamation fs-2"></i>
                        </div>
                        <h4 class="fw-bold mb-2">Không tìm thấy thông tin phù hợp!</h4>
                        <p class="text-muted mb-4" style="max-width: 550px; margin: 0 auto;">
                            Hệ thống không tìm thấy học viên nào với từ khóa: <strong>"<?php echo htmlspecialchars($search_query); ?>"</strong>. Vui lòng kiểm tra lại Mã học viên hoặc Số điện thoại đăng ký.
                        </p>
                        <div class="d-flex justify-content-center gap-2">
                            <a href="lookup.php?student_id=HV001" class="btn btn-primary rounded-pill px-4">
                                Thử tra cứu mã mẫu HV001
                            </a>
                            <a href="contact.php" class="btn btn-outline-secondary rounded-pill px-4">
                                Liên hệ hỗ trợ kỹ thuật
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <!-- GỢI Ý HƯỚNG DẪN KHI CHƯA TRA CỨU -->
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="p-4 bg-white rounded-4 border h-100 text-center shadow-sm">
                            <i class="fa-solid fa-qrcode text-primary fs-2 mb-3"></i>
                            <h6 class="fw-bold">Tra cứu theo Mã học viên</h6>
                            <p class="text-muted small mb-0">
                                Mã học viên được in trên thẻ học viên hoặc tin nhắn SMS tiếp nhận hồ sơ (VD: HV001, HV002).
                            </p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-4 bg-white rounded-4 border h-100 text-center shadow-sm">
                            <i class="fa-solid fa-mobile-screen-button text-success fs-2 mb-3"></i>
                            <h6 class="fw-bold">Tra cứu bằng Số điện thoại</h6>
                            <p class="text-muted small mb-0">
                                Nhập đúng số điện thoại của học viên hoặc phụ huynh đã đăng ký tại phòng đào tạo.
                            </p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-4 bg-white rounded-4 border h-100 text-center shadow-sm">
                            <i class="fa-solid fa-headset text-warning fs-2 mb-3"></i>
                            <h6 class="fw-bold">Hỗ trợ 24/7</h6>
                            <p class="text-muted small mb-0">
                                Nếu gặp lỗi không tra cứu được, vui lòng gọi hotline <strong>1900 6868</strong> để được kích hoạt ngay.
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
