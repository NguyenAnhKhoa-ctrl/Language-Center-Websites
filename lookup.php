<?php
/**
 * EDUCENTER - HỆ THỐNG WEBSITE QUẢN LÝ TRUNG TÂM NGOẠI NGỮ & GIA SƯ
 * File: lookup.php
 * Phụ trách (Tuần 2 & Bổ sung Gói B3 - Lý Hạo Danh - 52400175):
 * Cổng Tra cứu Bảng điểm 4 kỹ năng, Điểm danh, Lịch học, Nộp bài tập về nhà & Thanh toán Học phí QR
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = 'Tra Cứu Bảng Điểm, Bài Tập & Học Phí Trực Tuyến - EduCenter';
$currentPage = 'lookup.php';

require_once __DIR__ . '/config/db.php';

$search_query = isset($_GET['student_id']) ? trim(strip_tags((string)$_GET['student_id'])) : '';
if ($search_query === '' && isset($_POST['student_id'])) {
    $search_query = trim(strip_tags((string)$_POST['student_id']));
}

// Khởi tạo dữ liệu bài tập & trạng thái học phí mặc định trong Session
if (!isset($_SESSION['student_homework'])) {
    $_SESSION['student_homework'] = [
        'HV001' => [
            ['title' => 'Bài tập Writing Task 2 - Chủ đề Education & Technology', 'date' => '12/10/2026', 'link' => 'writing_task2_nguyenvanan.pdf', 'status' => 'Đã chấm: 8.0/9.0'],
            ['title' => 'Bài thu âm Speaking Part 2 - Describe a book you read', 'date' => '09/10/2026', 'link' => 'speaking_part2_audio.mp3', 'status' => 'Đã chấm: 7.5/9.0']
        ],
        'HV002' => [
            ['title' => 'Giải đề TOEIC Reading Part 5, 6, 7 - Test 04', 'date' => '11/10/2026', 'link' => 'toeic_test04_answers.pdf', 'status' => 'Đã chấm: 425/495']
        ],
        'HV003' => [
            ['title' => 'Chuyên đề Đọc hiểu & Từ vựng THPT Quốc Gia', 'date' => '12/10/2026', 'link' => 'thpt_reading_huy.docx', 'status' => 'Đã chấm: 9.0/10']
        ]
    ];
}

if (!isset($_SESSION['student_tuition_status'])) {
    $_SESSION['student_tuition_status'] = [
        'HV001' => ['invoice_code' => 'HD202601', 'amount' => 8900000, 'status' => 'Chờ thanh toán kỳ 2', 'paid' => false],
        'HV002' => ['invoice_code' => 'HD202602', 'amount' => 4500000, 'status' => 'Đã hoàn tất học phí', 'paid' => true],
        'HV003' => ['invoice_code' => 'HD202603', 'amount' => 6500000, 'status' => 'Chờ thanh toán khóa 1-1', 'paid' => false]
    ];
}

$portalFlash = '';
$activePortalTab = isset($_GET['ptab']) ? trim($_GET['ptab']) : 'grades';

// Xử lý Nộp bài tập về nhà & Thanh toán học phí QR trực tuyến
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['portal_action'])) {
    $portalAction = $_POST['portal_action'];
    $stCode       = strtoupper(trim($_POST['student_code'] ?? 'HV001'));

    if ($portalAction === 'submit_homework') {
        $activePortalTab = 'homework';
        $hwTitle = trim($_POST['hw_title'] ?? '');
        $hwLink  = trim($_POST['hw_link'] ?? '');

        if ($hwTitle !== '') {
            if (!isset($_SESSION['student_homework'][$stCode])) {
                $_SESSION['student_homework'][$stCode] = [];
            }
            array_unshift($_SESSION['student_homework'][$stCode], [
                'title'  => $hwTitle,
                'date'   => date('d/m/Y H:i'),
                'link'   => $hwLink !== '' ? $hwLink : 'baitap_hocvien_upload.pdf',
                'status' => 'Đã nộp - Chờ giảng viên chấm'
            ]);
            $portalFlash = 'Đã nộp bài tập <strong>' . htmlspecialchars($hwTitle) . '</strong> lên hệ thống cho giảng viên chấm điểm!';
        }
    } elseif ($portalAction === 'pay_tuition_qr') {
        $activePortalTab = 'tuition';
        $payMethod       = trim($_POST['payment_channel'] ?? 'VietQR Ngân hàng');

        $_SESSION['student_tuition_status'][$stCode] = [
            'invoice_code' => $_SESSION['student_tuition_status'][$stCode]['invoice_code'] ?? ('HD' . random_int(202610, 202999)),
            'amount'       => $_SESSION['student_tuition_status'][$stCode]['amount'] ?? 8900000,
            'status'       => 'Đã thanh toán qua ' . $payMethod,
            'paid'         => true
        ];

        // Đồng bộ trạng thái sang danh sách hóa đơn của Admin (admin/finance.php)
        if (isset($_SESSION['admin_tuition_invoices'])) {
            foreach ($_SESSION['admin_tuition_invoices'] as &$inv) {
                if ($inv['student_code'] === $stCode) {
                    $inv['status']         = 'Đã thanh toán';
                    $inv['payment_method'] = $payMethod;
                }
            }
            unset($inv);
        }

        $portalFlash = 'Xác nhận thanh toán học phí trực tuyến qua <strong>' . htmlspecialchars($payMethod) . '</strong> thành công! Hóa đơn đã được cập nhật.';
    }
}

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
            <span class="edu-page-badge">HỆ THỐNG TRA CỨU &amp; HỌC TẬP ĐIỆN TỬ</span>
            <h1 class="edu-page-title">Tra Cứu Bảng Điểm, Bài Tập &amp; Học Phí</h1>
            <p class="edu-page-subtitle">
                Học viên và phụ huynh dễ dàng tra cứu điểm kiểm tra định kỳ, tỷ lệ chuyên cần,
                nộp bài tập về nhà và thanh toán học phí trực tuyến qua mã QR.
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

            <?php if ($portalFlash !== ''): ?>
                <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
                    <i class="fa-solid fa-circle-check me-2"></i><?php echo $portalFlash; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if ($searched): ?>
                <?php if ($student_data): ?>
                    <?php
                        $stCode = $student_data['student_id'];
                        $hwList = $_SESSION['student_homework'][$stCode] ?? [];
                        $tuitionInfo = $_SESSION['student_tuition_status'][$stCode] ?? [
                            'invoice_code' => 'HD202609',
                            'amount'       => 8900000,
                            'status'       => 'Chờ thanh toán học phí',
                            'paid'         => false
                        ];
                    ?>
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

                        <!-- 5 TAB: ĐIỂM SỐ - ĐIỂM DANH - LỊCH HỌC - BÀI TẬP & TÀI LIỆU - HỌC PHÍ QR -->
                        <ul class="nav nav-pills mb-4 border-bottom pb-3 gap-2" id="lookupTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link <?php echo ($activePortalTab === 'grades') ? 'active' : ''; ?> px-3 py-2 fw-bold" id="grades-tab" data-bs-toggle="pill" data-bs-target="#tab-grades" type="button" role="tab">
                                    <i class="fa-solid fa-star me-1 text-warning"></i>Bảng Điểm &amp; Đánh Giá
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link <?php echo ($activePortalTab === 'attendance') ? 'active' : ''; ?> px-3 py-2 fw-bold" id="attendance-tab" data-bs-toggle="pill" data-bs-target="#tab-attendance" type="button" role="tab">
                                    <i class="fa-solid fa-clipboard-check me-1 text-success"></i>Chuyên Cần &amp; Điểm Danh
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link <?php echo ($activePortalTab === 'schedule') ? 'active' : ''; ?> px-3 py-2 fw-bold" id="schedule-tab" data-bs-toggle="pill" data-bs-target="#tab-schedule" type="button" role="tab">
                                    <i class="fa-solid fa-calendar-days me-1 text-primary"></i>Lịch Học Tuần
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link <?php echo ($activePortalTab === 'homework') ? 'active' : ''; ?> px-3 py-2 fw-bold" id="homework-tab" data-bs-toggle="pill" data-bs-target="#tab-homework" type="button" role="tab">
                                    <i class="fa-solid fa-book-open-reader me-1 text-info"></i>Tài Liệu &amp; Nộp Bài Tập
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link <?php echo ($activePortalTab === 'tuition') ? 'active' : ''; ?> px-3 py-2 fw-bold" id="tuition-tab" data-bs-toggle="pill" data-bs-target="#tab-tuition" type="button" role="tab">
                                    <i class="fa-solid fa-qrcode me-1 text-danger"></i>Học Phí &amp; Thanh Toán QR
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content" id="lookupTabsContent">
                            <!-- TAB 1: BẢNG ĐIỂM 4 KỸ NĂNG -->
                            <div class="tab-pane fade <?php echo ($activePortalTab === 'grades') ? 'show active' : ''; ?>" id="tab-grades" role="tabpanel">
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
                            <div class="tab-pane fade <?php echo ($activePortalTab === 'attendance') ? 'show active' : ''; ?>" id="tab-attendance" role="tabpanel">
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
                            <div class="tab-pane fade <?php echo ($activePortalTab === 'schedule') ? 'show active' : ''; ?>" id="tab-schedule" role="tabpanel">
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

                            <!-- TAB 4: TÀI LIỆU BÀI GIẢNG & NỘP BÀI TẬP VỀ NHÀ (GÓI B3) -->
                            <div class="tab-pane fade <?php echo ($activePortalTab === 'homework') ? 'show active' : ''; ?>" id="tab-homework" role="tabpanel">
                                <div class="row g-4">
                                    <!-- Kho tài liệu học tập -->
                                    <div class="col-lg-5">
                                        <div class="p-4 rounded-4 bg-light border h-100">
                                            <h6 class="fw-bold text-dark mb-3">
                                                <i class="fa-solid fa-folder-open text-primary me-2"></i>Tài Liệu Bài Giảng Lớp <?php echo htmlspecialchars($student_data['class_name']); ?>
                                            </h6>
                                            <div class="list-group list-group-flush rounded-3 border bg-white">
                                                <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                                                    <div>
                                                        <i class="fa-solid fa-file-pdf text-danger me-2"></i>
                                                        <strong class="small">Đề cương &amp; Chiến thuật giải đề trọng tâm.pdf</strong>
                                                        <small class="d-block text-muted">Dung lượng: 4.2 MB</small>
                                                    </div>
                                                    <span class="badge bg-primary-subtle text-primary">PDF</span>
                                                </div>
                                                <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                                                    <div>
                                                        <i class="fa-solid fa-file-audio text-info me-2"></i>
                                                        <strong class="small">Bộ Audio Luyện Nghe Phản Xạ Tuần 4.zip</strong>
                                                        <small class="d-block text-muted">Dung lượng: 18.5 MB</small>
                                                    </div>
                                                    <span class="badge bg-info-subtle text-info">Audio</span>
                                                </div>
                                                <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                                                    <div>
                                                        <i class="fa-solid fa-file-lines text-success me-2"></i>
                                                        <strong class="small">Bộ đề Mock Test Giữa Khóa kèm Đáp án.pdf</strong>
                                                        <small class="d-block text-muted">Dung lượng: 6.1 MB</small>
                                                    </div>
                                                    <span class="badge bg-success-subtle text-success">Mock Test</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Biểu mẫu nộp bài tập & lịch sử chấm bài -->
                                    <div class="col-lg-7">
                                        <div class="p-4 rounded-4 border bg-white mb-3">
                                            <h6 class="fw-bold text-dark mb-3">
                                                <i class="fa-solid fa-cloud-arrow-up text-success me-2"></i>Nộp Bài Tập Về Nhà Trực Tuyến
                                            </h6>
                                            <form action="lookup.php?student_id=<?php echo urlencode($stCode); ?>&amp;ptab=homework" method="POST" class="row g-2">
                                                <input type="hidden" name="portal_action" value="submit_homework">
                                                <input type="hidden" name="student_code" value="<?php echo htmlspecialchars($stCode); ?>">
                                                <div class="col-md-6">
                                                    <input type="text" name="hw_title" class="form-control form-control-sm" placeholder="Tên bài tập (VD: Writing Task 2 Unit 5)..." required>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="text" name="hw_link" class="form-control form-control-sm" placeholder="Link Google Drive / Tên file...">
                                                </div>
                                                <div class="col-md-2 d-grid">
                                                    <button type="submit" class="btn btn-sm btn-success fw-semibold">
                                                        <i class="fa-solid fa-paper-plane me-1"></i>Nộp bài
                                                    </button>
                                                </div>
                                            </form>
                                        </div>

                                        <div class="table-responsive">
                                            <table class="table table-bordered align-middle small mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Bài tập đã nộp</th>
                                                        <th>Tệp / Liên kết</th>
                                                        <th>Ngày nộp</th>
                                                        <th>Kết quả chấm</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($hwList as $hw): ?>
                                                        <tr>
                                                            <td class="fw-semibold"><?php echo htmlspecialchars($hw['title']); ?></td>
                                                            <td class="text-primary"><i class="fa-solid fa-paperclip me-1"></i><?php echo htmlspecialchars($hw['link']); ?></td>
                                                            <td class="text-muted"><?php echo htmlspecialchars($hw['date']); ?></td>
                                                            <td>
                                                                <span class="badge bg-success-subtle text-success"><?php echo htmlspecialchars($hw['status']); ?></span>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- TAB 5: HỌC PHÍ & THANH TOÁN TRỰC TUYẾN QR (GÓI B3) -->
                            <div class="tab-pane fade <?php echo ($activePortalTab === 'tuition') ? 'show active' : ''; ?>" id="tab-tuition" role="tabpanel">
                                <div class="row g-4 align-items-center">
                                    <div class="col-lg-7">
                                        <div class="p-4 rounded-4 border bg-light">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <span class="badge bg-primary px-3 py-2 rounded-pill">
                                                    Mã hóa đơn: <?php echo htmlspecialchars($tuitionInfo['invoice_code']); ?>
                                                </span>
                                                <?php if (!empty($tuitionInfo['paid'])): ?>
                                                    <span class="badge bg-success px-3 py-2 rounded-pill">
                                                        <i class="fa-solid fa-circle-check me-1"></i><?php echo htmlspecialchars($tuitionInfo['status']); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">
                                                        <i class="fa-solid fa-clock me-1"></i><?php echo htmlspecialchars($tuitionInfo['status']); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>

                                            <h5 class="fw-bold text-dark mb-1">
                                                Học phí chương trình: <?php echo htmlspecialchars($student_data['class_name']); ?>
                                            </h5>
                                            <p class="text-muted small mb-3">
                                                Học viên: <strong><?php echo htmlspecialchars($student_data['full_name']); ?></strong> (<?php echo htmlspecialchars($stCode); ?>)
                                            </p>

                                            <div class="d-flex justify-content-between align-items-center p-3 bg-white rounded-3 border mb-3">
                                                <span class="text-secondary fw-semibold">Tổng mức học phí niêm yết:</span>
                                                <span class="fs-4 fw-bold text-danger"><?php echo number_format((float)$tuitionInfo['amount'], 0, ',', '.'); ?>đ</span>
                                            </div>

                                            <?php if (empty($tuitionInfo['paid'])): ?>
                                                <button type="button" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#qrPaymentModal">
                                                    <i class="fa-solid fa-qrcode me-2"></i>Thanh Toán Trực Tuyến Bằng Mã QR (VietQR / MoMo / VNPay)
                                                </button>
                                            <?php else: ?>
                                                <div class="alert alert-success mb-0 small border-0">
                                                    <i class="fa-solid fa-shield-check me-1"></i>Học viên đã hoàn thành nghĩa vụ học phí cho khóa học hiện tại.
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="col-lg-5 text-center">
                                        <div class="p-4 rounded-4 border bg-white shadow-sm">
                                            <h6 class="fw-bold text-dark mb-2">
                                                <i class="fa-solid fa-building-columns text-primary me-1"></i>Mã VietQR Chuyển Khoản Nhanh
                                            </h6>
                                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&amp;data=<?php echo urlencode('EDUCENTER_TUITION_' . $stCode . '_' . $tuitionInfo['amount']); ?>"
                                                 alt="VietQR EduCenter"
                                                 class="img-fluid rounded-3 border p-2 mb-2"
                                                 style="width: 170px; height: 170px;">
                                            <div class="small text-muted">
                                                Nội dung CK: <strong class="text-primary">EDUCENTER <?php echo htmlspecialchars($stCode); ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- MODAL THANH TOÁN HỌC PHÍ TRỰC TUYẾN QR -->
                    <div class="modal fade" id="qrPaymentModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow rounded-4">
                                <form action="lookup.php?student_id=<?php echo urlencode($stCode); ?>&amp;ptab=tuition" method="POST">
                                    <input type="hidden" name="portal_action" value="pay_tuition_qr">
                                    <input type="hidden" name="student_code" value="<?php echo htmlspecialchars($stCode); ?>">
                                    <div class="modal-header border-bottom-0 pb-0">
                                        <h5 class="modal-title fw-bold text-dark">
                                            <i class="fa-solid fa-qrcode text-primary me-2"></i>Cổng Thanh Toán Học Phí Trực Tuyến QR
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body text-center">
                                        <p class="small text-muted mb-3">
                                            Quét mã QR bên dưới bằng ứng dụng Ngân hàng hoặc Ví điện tử để thanh toán học phí cho mã hồ sơ <strong><?php echo htmlspecialchars($stCode); ?></strong>
                                        </p>
                                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&amp;data=<?php echo urlencode('EDUCENTER_PAY_' . $stCode . '_' . $tuitionInfo['amount']); ?>"
                                             alt="QR Payment"
                                             class="img-fluid rounded-3 border p-2 mb-3"
                                             style="width: 190px; height: 190px;">

                                        <div class="p-3 rounded-3 bg-light border text-start small mb-3">
                                            <div class="d-flex justify-content-between mb-1">
                                                <span class="text-muted">Đơn vị thụ hưởng:</span>
                                                <strong>TRUNG TÂM NGOẠI NGỮ EDUCENTER</strong>
                                            </div>
                                            <div class="d-flex justify-content-between mb-1">
                                                <span class="text-muted">Số tiền thanh toán:</span>
                                                <strong class="text-danger"><?php echo number_format((float)$tuitionInfo['amount'], 0, ',', '.'); ?>đ</strong>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span class="text-muted">Nội dung chuyển khoản:</span>
                                                <strong class="text-primary">HP <?php echo htmlspecialchars($stCode); ?></strong>
                                            </div>
                                        </div>

                                        <div class="text-start">
                                            <label class="form-label fw-semibold small">Chọn cổng thanh toán xác nhận:</label>
                                            <select name="payment_channel" class="form-select">
                                                <option value="VietQR Ngân hàng">Chuyển khoản nhanh VietQR 24/7</option>
                                                <option value="Ví điện tử MoMo">Ví điện tử MoMo QR</option>
                                                <option value="Cổng VNPay QR">Cổng thanh toán VNPay QR</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-top-0 pt-0">
                                        <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Đóng</button>
                                        <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold">
                                            <i class="fa-solid fa-circle-check me-1"></i>Xác Nhận Đã Chuyển Khoản
                                        </button>
                                    </div>
                                </form>
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
