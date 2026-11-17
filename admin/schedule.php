<?php
/**
 * EDUCENTER - HỆ THỐNG WEBSITE QUẢN LÝ TRUNG TÂM NGOẠI NGỮ & GIA SƯ
 * File: admin/schedule.php
 * Phụ trách (Tuần 3 - Lý Hạo Danh - 52400175):
 * Ma trận Thời khóa biểu tuần, Điều phối phòng học & Bảng Điểm Danh Trực Tiếp
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

$admin_title = 'Xếp Lịch Học & Điểm Danh';
$active_menu = 'schedule';

$daysOfWeek = ['Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy', 'Chủ Nhật'];

// 1. Khởi tạo Ma trận Thời khóa biểu tuần chuẩn theo Báo cáo (Hình 4.4)
$defaultMatrix = [
    'ca1' => [
        'label' => 'Ca 1',
        'time'  => '18:00 - 19:30',
        'slots' => [
            'Thứ Hai'  => ['title' => 'IELTS Intensive K42', 'sub' => 'Cô Thảo My (P.201)', 'color' => 'bg-primary text-white'],
            'Thứ Ba'   => null,
            'Thứ Tư'   => ['title' => 'IELTS Intensive K42', 'sub' => 'Cô Thảo My (P.201)', 'color' => 'bg-primary text-white'],
            'Thứ Năm'  => null,
            'Thứ Sáu'  => ['title' => 'IELTS Intensive K42', 'sub' => 'Cô Thảo My (P.201)', 'color' => 'bg-primary text-white'],
            'Thứ Bảy'  => ['title' => 'Gia Sư VIP 1-1', 'sub' => 'Cô Bảo Trâm (P.VIP 3)', 'color' => 'bg-warning text-dark'],
            'Chủ Nhật' => ['title' => 'Gia Sư VIP 1-1', 'sub' => 'Cô Bảo Trâm (P.VIP 3)', 'color' => 'bg-warning text-dark']
        ]
    ],
    'ca2' => [
        'label' => 'Ca 2',
        'time'  => '19:45 - 21:15',
        'slots' => [
            'Thứ Hai'  => null,
            'Thứ Ba'   => ['title' => 'TOEIC 750+ Cấp Tốc', 'sub' => 'Thầy Đình Khôi (P.102)', 'color' => 'bg-success text-white'],
            'Thứ Tư'   => null,
            'Thứ Năm'  => ['title' => 'TOEIC 750+ Cấp Tốc', 'sub' => 'Thầy Đình Khôi (P.102)', 'color' => 'bg-success text-white'],
            'Thứ Sáu'  => null,
            'Thứ Bảy'  => ['title' => 'CLB Speaking Club', 'sub' => 'Thầy Michael (Hội trường)', 'color' => 'bg-info text-white'],
            'Chủ Nhật' => null
        ]
    ]
];

if (!isset($_SESSION['admin_schedule_matrix'])) {
    $_SESSION['admin_schedule_matrix'] = $defaultMatrix;
}

// 2. Khởi tạo Danh sách Điểm danh Học viên trực tiếp theo lớp
$defaultAttendanceByClass = [
    'IELTS Intensive K42' => [
        [
            'student_id'   => 1,
            'student_code' => 'HV001',
            'full_name'    => 'Nguyễn Văn An',
            'status'       => 'Có mặt',
            'note'         => 'Làm bài tập về nhà đầy đủ, phát biểu tốt'
        ],
        [
            'student_id'   => 4,
            'student_code' => 'HV004',
            'full_name'    => 'Hoàng Minh Quân',
            'status'       => 'Có phép',
            'note'         => 'Báo bận lịch thi giữa kỳ tại trường'
        ],
        [
            'student_id'   => 5,
            'student_code' => 'HV005',
            'full_name'    => 'Đỗ Phương Linh',
            'status'       => 'Có mặt',
            'note'         => 'Đến đúng giờ, phát âm tiến bộ'
        ]
    ],
    'TOEIC 750+ Cấp Tốc' => [
        [
            'student_id'   => 2,
            'student_code' => 'HV002',
            'full_name'    => 'Trần Thị Bích Ngọc',
            'status'       => 'Có mặt',
            'note'         => 'Hoàn thành tốt đề thi thử Reading & Listening'
        ]
    ],
    'Gia Sư 1-1 Thi Đại Học' => [
        [
            'student_id'   => 3,
            'student_code' => 'HV003',
            'full_name'    => 'Lê Gia Huy',
            'status'       => 'Có mặt',
            'note'         => 'Nắm vững chuyên đề mệnh đề quan hệ & từ vựng'
        ]
    ]
];

if (!isset($_SESSION['admin_attendance_by_class'])) {
    $_SESSION['admin_attendance_by_class'] = $defaultAttendanceByClass;
}

$selectedClass  = isset($_GET['class_name']) ? trim($_GET['class_name']) : 'IELTS Intensive K42';
if (!isset($_SESSION['admin_attendance_by_class'][$selectedClass])) {
    $selectedClass = 'IELTS Intensive K42';
}

$attendanceDate = isset($_GET['att_date']) ? trim($_GET['att_date']) : date('Y-m-d');
$lessonTopic    = 'Buổi học chuyên đề & Kiểm tra phản xạ trên lớp';
$flashMessage   = '';
$activeTab      = isset($_GET['tab']) && $_GET['tab'] === 'attendance' ? 'attendance' : 'matrix';

// ============================================================================
// XỬ LÝ FORM POST (XẾP LỊCH MỚI & LƯU BẢNG ĐIỂM DANH TRỰC TIẾP)
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // A. Xử lý xếp lịch ca học mới
    if ($action === 'add_schedule') {
        $shiftKey  = ($_POST['shift'] ?? 'ca1') === 'ca2' ? 'ca2' : 'ca1';
        $studyDay  = trim($_POST['study_day'] ?? 'Thứ Hai');
        $className = trim($_POST['class_name'] ?? 'IELTS Intensive K42');
        $teacher   = trim($_POST['teacher_name'] ?? 'Cô Thảo My');
        $room      = trim($_POST['room'] ?? 'P.201');
        $color     = trim($_POST['color_theme'] ?? 'bg-primary text-white');

        if (in_array($studyDay, $daysOfWeek, true)) {
            $_SESSION['admin_schedule_matrix'][$shiftKey]['slots'][$studyDay] = [
                'title' => $className,
                'sub'   => $teacher . ' (' . $room . ')',
                'color' => $color
            ];

            if ($db_connected && $pdo) {
                try {
                    $studyTime = $_SESSION['admin_schedule_matrix'][$shiftKey]['time'];
                    $stmt = $pdo->prepare("
                        INSERT INTO Schedules (class_id, study_day, study_time, room)
                        VALUES (1, :study_day, :study_time, :room)
                    ");
                    $stmt->execute([
                        ':study_day'  => $studyDay,
                        ':study_time' => $studyTime,
                        ':room'       => $room
                    ]);
                } catch (Exception $e) {
                    // Fallback Session
                }
            }

            $flashMessage = "Đã phân bổ ca học <strong>" . htmlspecialchars($className) . "</strong> vào <strong>" . htmlspecialchars($studyDay) . "</strong> (" . htmlspecialchars($room) . ") thành công!";
            $activeTab    = 'matrix';
        }
    }

    // B. Xử lý lưu bảng điểm danh trực tiếp
    elseif ($action === 'save_attendance') {
        $selectedClass  = trim($_POST['class_name'] ?? 'IELTS Intensive K42');
        $attendanceDate = trim($_POST['attendance_date'] ?? date('Y-m-d'));
        $lessonTopic    = trim($_POST['lesson_topic'] ?? 'Buổi học định kỳ');
        $statuses       = $_POST['att_status'] ?? [];
        $notes          = $_POST['att_note'] ?? [];

        if (isset($_SESSION['admin_attendance_by_class'][$selectedClass])) {
            foreach ($_SESSION['admin_attendance_by_class'][$selectedClass] as &$stRow) {
                $sid = (int)$stRow['student_id'];
                if (isset($statuses[$sid])) {
                    $stRow['status'] = $statuses[$sid];
                }
                if (isset($notes[$sid])) {
                    $stRow['note'] = trim($notes[$sid]);
                }

                // Lưu xuống bảng Attendance trong CSDL MySQL nếu đang kết nối
                if ($db_connected && $pdo) {
                    try {
                        $stmt = $pdo->prepare("
                            INSERT INTO Attendance (class_id, student_id, attendance_date, lesson_name, status, note)
                            VALUES (1, :student_id, :attendance_date, :lesson_name, :status, :note)
                        ");
                        $stmt->execute([
                            ':student_id'      => $sid,
                            ':attendance_date' => $attendanceDate,
                            ':lesson_name'     => $lessonTopic,
                            ':status'          => $stRow['status'],
                            ':note'            => $stRow['note']
                        ]);
                    } catch (Exception $e) {
                        // Fallback Session
                    }
                }
            }
            unset($stRow);
        }

        $flashMessage = "<strong>Thành công!</strong> Đã lưu dữ liệu điểm danh lớp <strong>" . htmlspecialchars($selectedClass) . "</strong> (Ngày " . htmlspecialchars(date('d/m/Y', strtotime($attendanceDate))) . ") và đồng bộ ngay lên cổng tra cứu của học viên.";
        $activeTab    = 'attendance';
    }
}

$scheduleMatrix     = $_SESSION['admin_schedule_matrix'];
$currentClassRoster = $_SESSION['admin_attendance_by_class'][$selectedClass] ?? [];

// Tính toán nhanh thống kê chuyên cần của lớp đang chọn
$countPresent = 0;
$countExcused = 0;
$countAbsent  = 0;
foreach ($currentClassRoster as $item) {
    if ($item['status'] === 'Có mặt') {
        $countPresent++;
    } elseif ($item['status'] === 'Có phép') {
        $countExcused++;
    } else {
        $countAbsent++;
    }
}

require_once __DIR__ . '/includes/admin_header.php';
?>

<!-- TIÊU ĐỀ TRANG & NÚT XẾP LỊCH MỚI -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h3 class="fw-bold mb-1">Xếp Lịch Học &amp; Điểm Danh Học Viên</h3>
        <p class="text-muted small mb-0">Điều phối phòng học, ca giảng dạy và quản lý chuyên cần học viên theo ngày</p>
    </div>
    <div>
        <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#addScheduleModal">
            <i class="fa-solid fa-calendar-plus me-2"></i>Xếp Lịch Mới
        </button>
    </div>
</div>

<?php if ($flashMessage !== ''): ?>
    <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i><?php echo $flashMessage; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- TAB ĐIỀU HƯỚNG -->
<ul class="nav nav-pills mb-4 bg-white p-2 rounded-4 shadow-sm d-inline-flex gap-1" id="scheduleTab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link <?php echo ($activeTab === 'matrix') ? 'active' : ''; ?> px-4 py-2 rounded-pill fw-bold"
                id="tab-matrix-btn"
                data-bs-toggle="pill"
                data-bs-target="#tab-matrix"
                type="button"
                role="tab">
            <i class="fa-solid fa-calendar-week me-2"></i>Thời Khóa Biểu Tuần
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link <?php echo ($activeTab === 'attendance') ? 'active' : ''; ?> px-4 py-2 rounded-pill fw-bold"
                id="tab-attendance-btn"
                data-bs-toggle="pill"
                data-bs-target="#tab-attendance"
                type="button"
                role="tab">
            <i class="fa-solid fa-user-check me-2"></i>Bảng Điểm Danh Trực Tiếp
        </button>
    </li>
</ul>

<div class="tab-content" id="scheduleTabContent">
    <!-- TAB 1: MA TRẬN THỜI KHÓA BIỂU TUẦN -->
    <div class="tab-pane fade <?php echo ($activeTab === 'matrix') ? 'show active' : ''; ?>" id="tab-matrix" role="tabpanel">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h5 class="fw-bold mb-0 text-dark">Lịch Phân Bổ Phòng Học &amp; Ca Dạy</h5>
                <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fw-semibold">
                    Tuần hiện tại (T10/2026)
                </span>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-bordered text-center align-middle mb-0 schedule-matrix-table">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 130px;">Khung Giờ</th>
                            <?php foreach ($daysOfWeek as $dayName): ?>
                                <th><?php echo htmlspecialchars($dayName); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($scheduleMatrix as $shift): ?>
                            <tr>
                                <td class="fw-bold bg-light">
                                    <?php echo htmlspecialchars($shift['label']); ?><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($shift['time']); ?></small>
                                </td>
                                <?php foreach ($daysOfWeek as $dayName): ?>
                                    <?php $slot = $shift['slots'][$dayName] ?? null; ?>
                                    <td>
                                        <?php if ($slot !== null): ?>
                                            <div class="<?php echo htmlspecialchars($slot['color']); ?> p-2 rounded-3 shadow-sm text-start small schedule-slot-box">
                                                <strong><?php echo htmlspecialchars($slot['title']); ?></strong><br>
                                                <span><?php echo htmlspecialchars($slot['sub']); ?></span>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted small">- Trống -</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 2: BẢNG ĐIỂM DANH TRỰC TIẾP -->
    <div class="tab-pane fade <?php echo ($activeTab === 'attendance') ? 'show active' : ''; ?>" id="tab-attendance" role="tabpanel">
        <div class="card border-0 shadow-sm rounded-4">
            <!-- Thanh công cụ chọn lớp & ngày điểm danh -->
            <div class="card-header bg-white border-bottom py-3">
                <form action="schedule.php" method="GET" class="row g-3 align-items-center">
                    <input type="hidden" name="tab" value="attendance">
                    <div class="col-auto">
                        <label class="fw-bold col-form-label small">Chọn lớp học:</label>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <select name="class_name" class="form-select" onchange="this.form.submit()">
                            <option value="IELTS Intensive K42" <?php echo ($selectedClass === 'IELTS Intensive K42') ? 'selected' : ''; ?>>
                                IELTS Intensive K42 (Cô Thảo My - P.201)
                            </option>
                            <option value="TOEIC 750+ Cấp Tốc" <?php echo ($selectedClass === 'TOEIC 750+ Cấp Tốc') ? 'selected' : ''; ?>>
                                TOEIC 750+ Cấp Tốc (Thầy Đình Khôi - P.102)
                            </option>
                            <option value="Gia Sư 1-1 Thi Đại Học" <?php echo ($selectedClass === 'Gia Sư 1-1 Thi Đại Học') ? 'selected' : ''; ?>>
                                Gia Sư 1-1 Thi Đại Học (Cô Bảo Trâm - P.VIP 3)
                            </option>
                        </select>
                    </div>

                    <div class="col-auto">
                        <label class="fw-bold col-form-label small">Ngày học:</label>
                    </div>
                    <div class="col-auto">
                        <input type="date" name="att_date" class="form-control" value="<?php echo htmlspecialchars($attendanceDate); ?>">
                    </div>

                    <div class="col-auto ms-auto d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-semibold" id="btnMarkAllPresent">
                            <i class="fa-solid fa-check-double me-1"></i>Tất cả Có mặt
                        </button>
                        <button type="submit" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                            <i class="fa-solid fa-arrows-rotate me-1"></i>Tải danh sách
                        </button>
                    </div>
                </form>
            </div>

            <!-- Biểu mẫu lưu điểm danh -->
            <form action="schedule.php?tab=attendance" method="POST">
                <input type="hidden" name="action" value="save_attendance">
                <input type="hidden" name="class_name" value="<?php echo htmlspecialchars($selectedClass); ?>">
                <input type="hidden" name="attendance_date" value="<?php echo htmlspecialchars($attendanceDate); ?>">

                <div class="px-4 py-3 bg-light border-bottom d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div class="d-flex align-items-center gap-2 flex-grow-1" style="max-width: 520px;">
                        <span class="small fw-semibold text-secondary text-nowrap">Nội dung buổi học:</span>
                        <input type="text" name="lesson_topic" class="form-control form-control-sm bg-white" value="<?php echo htmlspecialchars($lessonTopic); ?>">
                    </div>
                    <div class="d-flex flex-wrap gap-2 small">
                        <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill">
                            Có mặt: <strong id="summaryPresent"><?php echo $countPresent; ?></strong>
                        </span>
                        <span class="badge bg-warning-subtle text-warning px-3 py-2 rounded-pill">
                            Có phép: <strong id="summaryExcused"><?php echo $countExcused; ?></strong>
                        </span>
                        <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill">
                            Vắng mặt: <strong id="summaryAbsent"><?php echo $countAbsent; ?></strong>
                        </span>
                    </div>
                </div>

                <div class="card-body p-0 table-responsive">
                    <table class="table table-hover align-middle mb-0" id="attendanceTable">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Mã HV</th>
                                <th>Họ và Tên</th>
                                <th class="text-center text-success"><i class="fa-solid fa-check me-1"></i>Có mặt</th>
                                <th class="text-center text-danger"><i class="fa-solid fa-xmark me-1"></i>Vắng mặt</th>
                                <th class="text-center text-warning"><i class="fa-solid fa-clock me-1"></i>Có phép</th>
                                <th style="min-width: 280px;">Ghi chú thái độ / Bài tập</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($currentClassRoster as $st): ?>
                                <?php $sid = (int)$st['student_id']; ?>
                                <tr>
                                    <td class="ps-4"><strong class="text-primary"><?php echo htmlspecialchars($st['student_code']); ?></strong></td>
                                    <td class="fw-semibold"><?php echo htmlspecialchars($st['full_name']); ?></td>
                                    <td class="text-center">
                                        <input class="form-check-input att-radio"
                                               type="radio"
                                               name="att_status[<?php echo $sid; ?>]"
                                               value="Có mặt"
                                               <?php echo ($st['status'] === 'Có mặt') ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input class="form-check-input att-radio"
                                               type="radio"
                                               name="att_status[<?php echo $sid; ?>]"
                                               value="Vắng mặt"
                                               <?php echo ($st['status'] === 'Vắng mặt') ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input class="form-check-input att-radio"
                                               type="radio"
                                               name="att_status[<?php echo $sid; ?>]"
                                               value="Có phép"
                                               <?php echo ($st['status'] === 'Có phép') ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="pe-4">
                                        <input type="text"
                                               class="form-control form-control-sm"
                                               name="att_note[<?php echo $sid; ?>]"
                                               placeholder="Nhập nhận xét buổi học..."
                                               value="<?php echo htmlspecialchars($st['note']); ?>">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="card-footer bg-white py-3 border-0 text-end">
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                        <i class="fa-solid fa-floppy-disk me-2"></i>Lưu Bảng Điểm Danh Hôm Nay
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL XẾP LỊCH CA HỌC MỚI -->
<div class="modal fade" id="addScheduleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form action="schedule.php" method="POST">
                <input type="hidden" name="action" value="add_schedule">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark">
                        <i class="fa-solid fa-calendar-plus text-primary me-2"></i>Phân Bổ Phòng Học &amp; Ca Dạy Mới
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold small">Thứ trong tuần <span class="text-danger">*</span></label>
                            <select name="study_day" class="form-select">
                                <?php foreach ($daysOfWeek as $d): ?>
                                    <option value="<?php echo htmlspecialchars($d); ?>"><?php echo htmlspecialchars($d); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold small">Ca học <span class="text-danger">*</span></label>
                            <select name="shift" class="form-select">
                                <option value="ca1">Ca 1 (18:00 - 19:30)</option>
                                <option value="ca2">Ca 2 (19:45 - 21:15)</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Lớp học phân bổ <span class="text-danger">*</span></label>
                        <select name="class_name" class="form-select">
                            <option value="IELTS Intensive K42">IELTS Intensive K42</option>
                            <option value="TOEIC 750+ Cấp Tốc">TOEIC 750+ Cấp Tốc</option>
                            <option value="Tiếng Anh Giao Tiếp VIP">Tiếng Anh Giao Tiếp VIP</option>
                            <option value="Gia Sư VIP 1-1">Gia Sư VIP 1-1</option>
                            <option value="CLB Speaking Club">CLB Speaking Club</option>
                        </select>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold small">Giảng viên phụ trách</label>
                            <select name="teacher_name" class="form-select">
                                <option value="Cô Thảo My">Cô Thảo My</option>
                                <option value="Thầy Đình Khôi">Thầy Đình Khôi</option>
                                <option value="Thầy Michael">Thầy Michael</option>
                                <option value="Cô Bảo Trâm">Cô Bảo Trâm</option>
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold small">Phòng học</label>
                            <select name="room" class="form-select">
                                <option value="P.201">P.201</option>
                                <option value="P.102">P.102</option>
                                <option value="P.101">P.101</option>
                                <option value="P.VIP 3">P.VIP 3</option>
                                <option value="Hội trường">Hội trường</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold small">Nhãn màu hiển thị</label>
                        <select name="color_theme" class="form-select">
                            <option value="bg-primary text-white">Xanh dương (IELTS)</option>
                            <option value="bg-success text-white">Xanh lá (TOEIC)</option>
                            <option value="bg-warning text-dark">Vàng cam (Gia sư VIP 1-1)</option>
                            <option value="bg-info text-white">Xanh ngọc (Giao tiếp / CLB)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">
                        <i class="fa-solid fa-check me-1"></i>Cập Nhật Lịch
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var radios = document.querySelectorAll('.att-radio');
    var markAllBtn = document.getElementById('btnMarkAllPresent');

    function updateAttendanceSummary() {
        var present = 0;
        var excused = 0;
        var absent  = 0;
        document.querySelectorAll('.att-radio:checked').forEach(function (r) {
            if (r.value === 'Có mặt') present++;
            else if (r.value === 'Có phép') excused++;
            else if (r.value === 'Vắng mặt') absent++;
        });
        var elP = document.getElementById('summaryPresent');
        var elE = document.getElementById('summaryExcused');
        var elA = document.getElementById('summaryAbsent');
        if (elP) elP.textContent = present;
        if (elE) elE.textContent = excused;
        if (elA) elA.textContent = absent;
    }

    radios.forEach(function (radio) {
        radio.addEventListener('change', updateAttendanceSummary);
    });

    if (markAllBtn) {
        markAllBtn.addEventListener('click', function () {
            document.querySelectorAll('.att-radio[value="Có mặt"]').forEach(function (r) {
                r.checked = true;
            });
            updateAttendanceSummary();
        });
    }
});
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
