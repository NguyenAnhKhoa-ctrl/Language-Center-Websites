<?php
/**
 * EDUCENTER - HỆ THỐNG WEBSITE QUẢN LÝ TRUNG TÂM NGOẠI NGỮ & GIA SƯ
 * File: admin/courses.php
 * Phụ trách (Bổ sung Gói A2 - Nguyễn Anh Khoa - 52400051):
 * Quản lý Khóa học, Mở Lớp học & Điều phối Ghép nối Gia sư 1-1 (Theo SRS Mục 2.1)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

$admin_title = 'Quản Lý Khóa Học & Lớp Học';
$active_menu = 'courses';

// Khởi tạo danh sách Khóa học trong Session từ Mock Data Fallback
if (!isset($_SESSION['admin_courses'])) {
    $_SESSION['admin_courses'] = $sample_courses;
}

// Khởi tạo danh sách Lớp học mở theo khóa
if (!isset($_SESSION['admin_classes'])) {
    $_SESSION['admin_classes'] = [
        [
            'class_code'   => 'IELTS_K42',
            'class_name'   => 'IELTS Intensive K42',
            'course_name'  => 'Luyện thi IELTS Intensive (Mục tiêu 6.5 - 7.5+)',
            'teacher_name' => 'Cô Nguyễn Thảo My',
            'room'         => 'P.201 (Offline)',
            'start_date'   => '15/09/2026',
            'capacity'     => '12/12 HV',
            'status'       => 'Đang mở'
        ],
        [
            'class_code'   => 'TOEIC_22',
            'class_name'   => 'TOEIC 750+ Cấp Tốc',
            'course_name'  => 'Luyện thi TOEIC Cấp Tốc (Cam kết 750+)',
            'teacher_name' => 'Thầy Trần Đình Khôi',
            'room'         => 'P.102 (Offline)',
            'start_date'   => '20/09/2026',
            'capacity'     => '15/15 HV',
            'status'       => 'Đang mở'
        ],
        [
            'class_code'   => 'GT_05',
            'class_name'   => 'Tiếng Anh Giao Tiếp VIP',
            'course_name'  => 'Tiếng Anh Giao Tiếp Thực Chiến (100% Phản Xạ)',
            'teacher_name' => 'Thầy Michael Roberts',
            'room'         => 'P.101 / Hybrid Zoom',
            'start_date'   => '01/10/2026',
            'capacity'     => '8/10 HV',
            'status'       => 'Đang mở'
        ],
        [
            'class_code'   => 'TUTOR_VIP3',
            'class_name'   => 'Gia sư 1-1 Luyện thi THPT',
            'course_name'  => 'Gia sư Tiếng Anh 1 Kèm 1 Theo Yêu Cầu',
            'teacher_name' => 'Cô Lê Hoàng Bảo Trâm',
            'room'         => 'Phòng VIP 3 / Zoom 1-1',
            'start_date'   => '05/10/2026',
            'capacity'     => '1/1 HV',
            'status'       => 'Đang kèm 1-1'
        ]
    ];
}

// Khởi tạo danh sách Ghép nối Gia sư 1-1 (SRS Mục 2.1 - Quản lý dịch vụ gia sư 1-1)
if (!isset($_SESSION['tutor_matchings'])) {
    $_SESSION['tutor_matchings'] = [
        [
            'match_code'   => 'GS101',
            'student_name' => 'Lê Gia Huy (HV003)',
            'subject'      => 'Luyện thi THPT Quốc Gia mục tiêu 9+',
            'area_mode'    => 'Phòng VIP 3 / Kèm 1-1 trực tiếp',
            'schedule'     => 'Thứ 7 & Chủ Nhật (18:00 - 19:30)',
            'tutor_name'   => 'Cô Lê Hoàng Bảo Trâm',
            'status'       => 'Đang học ổn định'
        ],
        [
            'match_code'   => 'GS102',
            'student_name' => 'Đỗ Minh Châu (DK101189)',
            'subject'      => 'IELTS Speaking & Writing 1-1 Cấp tốc',
            'area_mode'    => 'Trực tuyến qua Zoom 1-1',
            'schedule'     => 'Thứ 3 - Thứ 5 (20:00 - 21:30)',
            'tutor_name'   => 'Cô Nguyễn Thảo My',
            'status'       => 'Vừa ghép nối'
        ]
    ];
}

$flashMessage = '';
$flashType    = 'success';

// ============================================================================
// XỬ LÝ POST CRUD KHÓA HỌC, LỚP HỌC & GHÉP NỐI GIA SƯ 1-1
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // 1. Thêm khóa học mới
    if ($action === 'add_course') {
        $courseCode  = strtoupper(trim($_POST['course_code'] ?? 'EDU-NEW'));
        $courseName  = trim($_POST['course_name'] ?? '');
        $category    = trim($_POST['category'] ?? 'IELTS');
        $level       = trim($_POST['level'] ?? 'Mọi trình độ');
        $duration    = trim($_POST['duration'] ?? '3 tháng (36 buổi)');
        $fee         = (float)($_POST['fee'] ?? 5000000);
        $badge       = trim($_POST['badge'] ?? 'Phổ biến');
        $description = trim($_POST['description'] ?? '');

        if ($courseName !== '') {
            if ($db_connected && $pdo) {
                try {
                    $stmt = $pdo->prepare("
                        INSERT INTO Courses (course_code, course_name, category, badge, level_range, duration, description, fee)
                        VALUES (:code, :name, :cat, :badge, :level, :dur, :desc, :fee)
                    ");
                    $stmt->execute([
                        ':code'  => $courseCode,
                        ':name'  => $courseName,
                        ':cat'   => $category,
                        ':badge' => $badge,
                        ':level' => $level,
                        ':dur'   => $duration,
                        ':desc'  => $description,
                        ':fee'   => $fee
                    ]);
                } catch (Exception $e) {
                    // Fallback sang Session
                }
            }

            array_unshift($_SESSION['admin_courses'], [
                'course_id'   => count($_SESSION['admin_courses']) + 1,
                'course_code' => $courseCode,
                'course_name' => $courseName,
                'category'    => $category,
                'description' => $description !== '' ? $description : 'Chương trình đào tạo chuẩn đầu ra quốc tế tại EduCenter.',
                'fee'         => $fee,
                'duration'    => $duration,
                'level'       => $level,
                'badge'       => $badge,
                'icon'        => 'fa-graduation-cap'
            ]);

            $flashMessage = "Đã tạo khóa học mới <strong>" . htmlspecialchars($courseName) . "</strong> ($courseCode) thành công!";
        }
    }

    // 2. Xóa khóa học
    if ($action === 'delete_course') {
        $courseCode = trim($_POST['course_code'] ?? '');
        if ($courseCode !== '') {
            if ($db_connected && $pdo) {
                try {
                    $stmt = $pdo->prepare("DELETE FROM Courses WHERE course_code = :code");
                    $stmt->execute([':code' => $courseCode]);
                } catch (Exception $e) {
                    // Fallback
                }
            }
            $_SESSION['admin_courses'] = array_values(array_filter(
                $_SESSION['admin_courses'],
                function ($c) use ($courseCode) {
                    return $c['course_code'] !== $courseCode;
                }
            ));
            $flashMessage = "Đã xóa khóa học mã <strong>" . htmlspecialchars($courseCode) . "</strong>.";
            $flashType    = "warning";
        }
    }

    // 3. Mở lớp học mới & Gán phòng/link học trực tuyến
    if ($action === 'add_class') {
        $classCode   = strtoupper(trim($_POST['class_code'] ?? 'CLS_' . random_int(10, 99)));
        $className   = trim($_POST['class_name'] ?? '');
        $courseName  = trim($_POST['course_name'] ?? '');
        $teacherName = trim($_POST['teacher_name'] ?? 'Cô Nguyễn Thảo My');
        $room        = trim($_POST['room'] ?? 'P.202');
        $startDate   = trim($_POST['start_date'] ?? date('Y-m-d'));
        $maxStudents = (int)($_POST['max_students'] ?? 12);

        if ($className !== '') {
            array_unshift($_SESSION['admin_classes'], [
                'class_code'   => $classCode,
                'class_name'   => $className,
                'course_name'  => $courseName,
                'teacher_name' => $teacherName,
                'room'         => $room,
                'start_date'   => date('d/m/Y', strtotime($startDate)),
                'capacity'     => '0/' . $maxStudents . ' HV',
                'status'       => 'Mới khai giảng'
            ]);
            $flashMessage = "Đã mở lớp học mới <strong>" . htmlspecialchars($className) . "</strong> tại phòng/link <strong>" . htmlspecialchars($room) . "</strong>!";
        }
    }

    // 4. Ghép nối Gia sư 1-1 mới với Học viên
    if ($action === 'match_tutor') {
        $studentName = trim($_POST['student_name'] ?? '');
        $subject     = trim($_POST['subject'] ?? '');
        $areaMode    = trim($_POST['area_mode'] ?? 'Trực tuyến qua Zoom 1-1');
        $schedule    = trim($_POST['schedule'] ?? '');
        $tutorName   = trim($_POST['tutor_name'] ?? 'Cô Lê Hoàng Bảo Trâm');

        if ($studentName !== '' && $subject !== '') {
            $matchCode = 'GS' . random_int(103, 999);
            array_unshift($_SESSION['tutor_matchings'], [
                'match_code'   => $matchCode,
                'student_name' => $studentName,
                'subject'      => $subject,
                'area_mode'    => $areaMode,
                'schedule'     => $schedule,
                'tutor_name'   => $tutorName,
                'status'       => 'Vừa ghép nối'
            ]);
            $flashMessage = "Đã ghép nối thành công Gia sư <strong>" . htmlspecialchars($tutorName) . "</strong> kèm 1-1 cho học viên <strong>" . htmlspecialchars($studentName) . "</strong> (Mã: $matchCode)!";
        }
    }
}

$coursesList  = $_SESSION['admin_courses'];
$classesList  = $_SESSION['admin_classes'];
$matchingList = $_SESSION['tutor_matchings'];

require_once __DIR__ . '/includes/admin_header.php';
?>

<!-- TIÊU ĐỀ TRANG & CÁC NÚT THAO TÁC NHANH -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h3 class="fw-bold mb-1">Quản Lý Khóa Học, Lớp Học &amp; Ghép Nối Gia Sư 1-1</h3>
        <p class="text-muted small mb-0">Định nghĩa chương trình đào tạo, xếp phòng/link học trực tuyến và điều phối gia sư kèm riêng</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-outline-primary fw-semibold rounded-3" data-bs-toggle="modal" data-bs-target="#addClassModal">
            <i class="fa-solid fa-door-open me-1"></i> Mở Lớp Học Mới
        </button>
        <button type="button" class="btn btn-primary fw-semibold rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#addCourseModal">
            <i class="fa-solid fa-plus me-1"></i> Tạo Khóa Học Mới
        </button>
    </div>
</div>

<?php if ($flashMessage !== ''): ?>
    <div class="alert alert-<?php echo htmlspecialchars($flashType); ?> alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i><?php echo $flashMessage; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- NAVIGATION TABS (3 PHÂN HỆ QUẢN LÝ THEO SRS MỤC 2.1) -->
<ul class="nav nav-pills mb-4 gap-2" id="courseAdminTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active fw-semibold rounded-pill px-4" id="tab-courses-btn" data-bs-toggle="pill" data-bs-target="#tab-courses" type="button" role="tab">
            <i class="fa-solid fa-book-open me-1"></i> 1. Danh mục Khóa học (<?php echo count($coursesList); ?>)
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-semibold rounded-pill px-4" id="tab-classes-btn" data-bs-toggle="pill" data-bs-target="#tab-classes" type="button" role="tab">
            <i class="fa-solid fa-chalkboard me-1"></i> 2. Lớp học &amp; Phòng học/Link Online (<?php echo count($classesList); ?>)
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-semibold rounded-pill px-4" id="tab-tutor1on1-btn" data-bs-toggle="pill" data-bs-target="#tab-tutor1on1" type="button" role="tab">
            <i class="fa-solid fa-people-arrows me-1"></i> 3. Ghép nối Gia sư 1-1 (<?php echo count($matchingList); ?>)
        </button>
    </li>
</ul>

<div class="tab-content" id="courseAdminTabsContent">
    <!-- TAB 1: DANH MỤC KHÓA HỌC -->
    <div class="tab-pane fade show active" id="tab-courses" role="tabpanel">
        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 py-3">Mã Khóa</th>
                            <th class="py-3">Tên Khóa Học &amp; Chương Trình</th>
                            <th class="py-3">Danh mục</th>
                            <th class="py-3">Đầu vào / Đối tượng</th>
                            <th class="py-3">Thời lượng</th>
                            <th class="py-3">Học phí</th>
                            <th class="text-end pe-4 py-3">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($coursesList as $c): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-primary"><?php echo htmlspecialchars($c['course_code']); ?></td>
                                <td>
                                    <strong class="text-dark d-block"><?php echo htmlspecialchars($c['course_name']); ?></strong>
                                    <small class="text-muted"><?php echo htmlspecialchars($c['badge'] ?? 'Phổ biến'); ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill">
                                        <?php echo htmlspecialchars($c['category']); ?>
                                    </span>
                                </td>
                                <td class="small"><?php echo htmlspecialchars($c['level'] ?? 'Mọi trình độ'); ?></td>
                                <td class="small"><?php echo htmlspecialchars($c['duration']); ?></td>
                                <td class="fw-bold text-success"><?php echo number_format((float)$c['fee'], 0, ',', '.'); ?>đ</td>
                                <td class="text-end pe-4">
                                    <form method="POST" action="courses.php" class="d-inline" onsubmit="return confirm('Xác nhận xóa khóa học này?');">
                                        <input type="hidden" name="action" value="delete_course">
                                        <input type="hidden" name="course_code" value="<?php echo htmlspecialchars($c['course_code']); ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle student-action-btn" title="Xóa khóa học">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 2: QUẢN LÝ LỚP HỌC & PHÒNG HỌC / LINK ONLINE -->
    <div class="tab-pane fade" id="tab-classes" role="tabpanel">
        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 py-3">Mã Lớp</th>
                            <th class="py-3">Tên Lớp Học</th>
                            <th class="py-3">Thuộc Khóa Học</th>
                            <th class="py-3">Giảng Viên Phụ Trách</th>
                            <th class="py-3">Phòng Học / Link Online</th>
                            <th class="py-3">Khai Giảng</th>
                            <th class="py-3">Sĩ Số</th>
                            <th class="pe-4 py-3">Trạng Thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($classesList as $cls): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-primary"><?php echo htmlspecialchars($cls['class_code']); ?></td>
                                <td><strong><?php echo htmlspecialchars($cls['class_name']); ?></strong></td>
                                <td class="small text-muted"><?php echo htmlspecialchars($cls['course_name']); ?></td>
                                <td><?php echo htmlspecialchars($cls['teacher_name']); ?></td>
                                <td>
                                    <span class="badge bg-light text-dark border px-3 py-2 rounded-pill">
                                        <i class="fa-solid fa-location-dot text-primary me-1"></i><?php echo htmlspecialchars($cls['room']); ?>
                                    </span>
                                </td>
                                <td class="small"><?php echo htmlspecialchars($cls['start_date']); ?></td>
                                <td><strong><?php echo htmlspecialchars($cls['capacity']); ?></strong></td>
                                <td class="pe-4">
                                    <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill">
                                        <?php echo htmlspecialchars($cls['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 3: ĐIỀU PHỐI & GHÉP NỐI GIA SƯ 1-1 -->
    <div class="tab-pane fade" id="tab-tutor1on1" role="tabpanel">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="fa-solid fa-handshake-angle text-primary me-2"></i>Danh sách cặp Gia sư - Học viên 1 kèm 1 đang hoạt động
            </h6>
            <button type="button" class="btn btn-sm btn-success fw-semibold rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#matchTutorModal">
                <i class="fa-solid fa-user-plus me-1"></i> Ghép nối Gia sư 1-1 mới
            </button>
        </div>

        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 py-3">Mã Ghép</th>
                            <th class="py-3">Học Viên</th>
                            <th class="py-3">Mục Tiêu / Môn Học Yêu Cầu</th>
                            <th class="py-3">Gia Sư Phụ Trách</th>
                            <th class="py-3">Khu Vực / Hình Thức</th>
                            <th class="py-3">Lịch Học Thống Nhất</th>
                            <th class="pe-4 py-3">Trạng Thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($matchingList as $m): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-primary"><?php echo htmlspecialchars($m['match_code']); ?></td>
                                <td><strong><?php echo htmlspecialchars($m['student_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($m['subject']); ?></td>
                                <td class="fw-semibold text-success"><?php echo htmlspecialchars($m['tutor_name']); ?></td>
                                <td class="small"><?php echo htmlspecialchars($m['area_mode']); ?></td>
                                <td class="small text-secondary"><?php echo htmlspecialchars($m['schedule']); ?></td>
                                <td class="pe-4">
                                    <span class="badge bg-info-subtle text-info px-3 py-2 rounded-pill">
                                        <?php echo htmlspecialchars($m['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL 1: TẠO KHÓA HỌC MỚI -->
<div class="modal fade" id="addCourseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form method="POST" action="courses.php">
                <input type="hidden" name="action" value="add_course">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-primary">
                        <i class="fa-solid fa-book-medical me-2"></i>Tạo Khóa Học Mới
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3">
                    <div class="row g-3 mb-3">
                        <div class="col-sm-4">
                            <label class="form-label fw-semibold small">Mã khóa học *</label>
                            <input type="text" name="course_code" class="form-control" placeholder="IELTS-ADV" required>
                        </div>
                        <div class="col-sm-8">
                            <label class="form-label fw-semibold small">Tên khóa học *</label>
                            <input type="text" name="course_name" class="form-control" placeholder="VD: IELTS Master 7.5 - 8.5+" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold small">Danh mục</label>
                            <select name="category" class="form-select">
                                <option value="IELTS">IELTS</option>
                                <option value="TOEIC">TOEIC</option>
                                <option value="Giao tiếp">Giao tiếp</option>
                                <option value="Gia sư 1-1">Gia sư 1-1</option>
                                <option value="Thiếu nhi">Thiếu nhi</option>
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold small">Học phí trọn khóa (VNĐ) *</label>
                            <input type="number" name="fee" class="form-control" value="7500000" step="100000" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold small">Trình độ đầu vào</label>
                            <input type="text" name="level" class="form-control" value="Đầu vào 6.0+">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold small">Thời lượng (Số buổi)</label>
                            <input type="text" name="duration" class="form-control" value="3 tháng (36 buổi)">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold small">Mô tả chương trình học</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Tóm tắt nội dung và cam kết đầu ra..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary fw-semibold px-4">Lưu Khóa Học</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL 2: MỞ LỚP HỌC MỚI -->
<div class="modal fade" id="addClassModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form method="POST" action="courses.php">
                <input type="hidden" name="action" value="add_class">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-primary">
                        <i class="fa-solid fa-door-open me-2"></i>Mở Lớp Học Mới &amp; Gán Phòng/Link Online
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3">
                    <div class="row g-3 mb-3">
                        <div class="col-sm-4">
                            <label class="form-label fw-semibold small">Mã lớp *</label>
                            <input type="text" name="class_code" class="form-control" placeholder="IELTS_K43" required>
                        </div>
                        <div class="col-sm-8">
                            <label class="form-label fw-semibold small">Tên lớp học *</label>
                            <input type="text" name="class_name" class="form-control" placeholder="IELTS Intensive K43" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Thuộc khóa học</label>
                        <select name="course_name" class="form-select">
                            <?php foreach ($coursesList as $c): ?>
                                <option value="<?php echo htmlspecialchars($c['course_name']); ?>">
                                    <?php echo htmlspecialchars($c['course_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold small">Giảng viên phụ trách</label>
                            <select name="teacher_name" class="form-select">
                                <option value="Cô Nguyễn Thảo My">Cô Nguyễn Thảo My</option>
                                <option value="Thầy Michael Roberts">Thầy Michael Roberts</option>
                                <option value="Thầy Trần Đình Khôi">Thầy Trần Đình Khôi</option>
                                <option value="Cô Lê Hoàng Bảo Trâm">Cô Lê Hoàng Bảo Trâm</option>
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold small">Phòng học / Link Online *</label>
                            <input type="text" name="room" class="form-control" placeholder="P.203 hoặc Zoom ID" required>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold small">Ngày khai giảng</label>
                            <input type="date" name="start_date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold small">Sĩ số tối đa</label>
                            <input type="number" name="max_students" class="form-control" value="12">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary fw-semibold px-4">Mở Lớp</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL 3: GHÉP NỐI GIA SƯ 1-1 -->
<div class="modal fade" id="matchTutorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form method="POST" action="courses.php">
                <input type="hidden" name="action" value="match_tutor">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-success">
                        <i class="fa-solid fa-people-arrows me-2"></i>Ghép Nối Gia Sư 1 Kèm 1 Theo Yêu Cầu
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Học viên yêu cầu *</label>
                        <input type="text" name="student_name" class="form-control" placeholder="VD: Nguyễn Hoàng Nam (HV006)" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Môn học &amp; Mục tiêu điểm số *</label>
                        <input type="text" name="subject" class="form-control" placeholder="VD: Gia sư IELTS Writing 7.0+ 1-1" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold small">Chọn Gia sư phù hợp</label>
                            <select name="tutor_name" class="form-select">
                                <option value="Cô Lê Hoàng Bảo Trâm">Cô Lê Hoàng Bảo Trâm</option>
                                <option value="Cô Nguyễn Thảo My">Cô Nguyễn Thảo My</option>
                                <option value="Thầy Michael Roberts">Thầy Michael Roberts</option>
                                <option value="Thầy Trần Đình Khôi">Thầy Trần Đình Khôi</option>
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold small">Khu vực / Hình thức học</label>
                            <input type="text" name="area_mode" class="form-control" value="Online qua Zoom / Phòng VIP">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold small">Lịch trống thống nhất</label>
                        <input type="text" name="schedule" class="form-control" placeholder="VD: Thứ 2 - 4 - 6 (19:45 - 21:15)" required>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success fw-semibold px-4">Xác Nhận Ghép Nối</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
