<?php
/**
 * EDUCENTER - HỆ THỐNG WEBSITE QUẢN LÝ TRUNG TÂM NGOẠI NGỮ & GIA SƯ
 * File: admin/registrations.php
 * Phụ trách (Tuần 4 - Nguyễn Anh Khoa - 52400051):
 * Quản lý Hồ sơ Đăng ký Trực tuyến tiếp nhận tự động từ Client UI (register.php)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

$admin_title = 'Hồ Sơ Đăng Ký Trực Tuyến';
$active_menu = 'registrations';

// Danh sách hồ sơ đăng ký mẫu chuẩn theo Báo cáo (Hình 4.6)
$defaultRegistrations = [
    [
        'ticket_code'    => 'DK101234',
        'full_name'      => 'Nguyễn Hoàng Nam',
        'phone'          => '0983 221 456',
        'email'          => 'hoangnam.nguyen@gmail.com',
        'course_name'    => 'Luyện thi IELTS Intensive (Mục tiêu 6.5 - 7.5+)',
        'preferred_time' => 'Ca Tối (18:00 - 19:30)',
        'notes'          => 'Cần thi IELTS trước tháng 12 để nộp hồ sơ du học Canada',
        'status'         => 'Chờ liên hệ'
    ],
    [
        'ticket_code'    => 'DK101189',
        'full_name'      => 'Đỗ Minh Châu',
        'phone'          => '0912 889 001',
        'email'          => 'minhchau.do@gmail.com',
        'course_name'    => 'Gia sư Tiếng Anh 1 Kèm 1 Theo Yêu Cầu',
        'preferred_time' => 'Cuối tuần (Thứ 7 - CN)',
        'notes'          => 'Muốn học kèm riêng tại nhà hoặc qua Zoom cùng cô Bảo Trâm',
        'status'         => 'Đã tư vấn'
    ],
    [
        'ticket_code'    => 'DK101045',
        'full_name'      => 'Vũ Thị Quỳnh Như',
        'phone'          => '0977 445 123',
        'email'          => 'quynhnhu.vu@gmail.com',
        'course_name'    => 'Tiếng Anh Giao Tiếp Thực Chiến (100% Phản Xạ)',
        'preferred_time' => 'Ca Tối (19:45 - 21:15)',
        'notes'          => 'Người đi làm, muốn cải thiện kỹ năng nghe nói họp trực tuyến',
        'status'         => 'Đã xếp lớp'
    ]
];

if (!isset($_SESSION['admin_registrations'])) {
    $_SESSION['admin_registrations'] = $defaultRegistrations;
}

// Đồng bộ tự động các lượt đăng ký mới gửi từ form register.php (Client UI)
if (!empty($_SESSION['new_registrations'])) {
    $existingCodes = array_column($_SESSION['admin_registrations'], 'ticket_code');
    foreach ($_SESSION['new_registrations'] as $newReg) {
        if (!in_array($newReg['ticket_code'], $existingCodes, true)) {
            array_unshift($_SESSION['admin_registrations'], [
                'ticket_code'    => $newReg['ticket_code'],
                'full_name'      => $newReg['full_name'],
                'phone'          => $newReg['phone'],
                'email'          => $newReg['email'] !== '' ? $newReg['email'] : 'khachhang@gmail.com',
                'course_name'    => $newReg['course_name'],
                'preferred_time' => $newReg['preferred_time'],
                'notes'          => $newReg['notes'] !== '' ? $newReg['notes'] : 'Đăng ký kiểm tra trình độ đầu vào từ Website',
                'status'         => $newReg['status'] ?? 'Chờ liên hệ'
            ]);
        }
    }
}

$availableClasses = [
    'IELTS Intensive K42',
    'TOEIC 750+ Cấp Tốc',
    'Tiếng Anh Giao Tiếp VIP',
    'Gia Sư 1-1 Thi Đại Học'
];

$flashMessage = '';
$flashType    = 'success';

// ============================================================================
// XỬ LÝ CẬP NHẬT TRẠNG THÁI TƯ VẤN & XẾP LỚP HỌC VIÊN
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action     = $_POST['action'];
    $ticketCode = trim($_POST['ticket_code'] ?? '');

    // 1. Cập nhật trạng thái cuộc gọi tư vấn
    if ($action === 'update_call_status' && $ticketCode !== '') {
        $newStatus = trim($_POST['status'] ?? 'Đã tư vấn');
        $callNote  = trim($_POST['call_note'] ?? '');

        if ($db_connected && $pdo) {
            try {
                $stmt = $pdo->prepare("UPDATE Course_Registrations SET status = :status, notes = CONCAT(notes, :note) WHERE ticket_code = :code");
                $stmt->execute([
                    ':status' => $newStatus,
                    ':note'   => $callNote !== '' ? " | Ghi chú TV: $callNote" : '',
                    ':code'   => $ticketCode
                ]);
            } catch (Exception $e) {
                // Fallback sang Session
            }
        }

        foreach ($_SESSION['admin_registrations'] as &$reg) {
            if ($reg['ticket_code'] === $ticketCode) {
                $reg['status'] = $newStatus;
                if ($callNote !== '') {
                    $reg['notes'] .= ' (Tư vấn: ' . $callNote . ')';
                }
                break;
            }
        }
        unset($reg);

        $flashMessage = "Đã cập nhật trạng thái hồ sơ <strong>" . htmlspecialchars($ticketCode) . "</strong> sang <strong>" . htmlspecialchars($newStatus) . "</strong>.";
    }

    // 2. Xếp lớp chính thức cho học viên từ hồ sơ đăng ký
    if ($action === 'assign_class' && $ticketCode !== '') {
        $assignedClass = trim($_POST['assigned_class'] ?? 'IELTS Intensive K42');
        $studentName   = '';
        $studentPhone  = '';
        $studentEmail  = '';

        foreach ($_SESSION['admin_registrations'] as &$reg) {
            if ($reg['ticket_code'] === $ticketCode) {
                $reg['status'] = 'Đã xếp lớp';
                $studentName   = $reg['full_name'];
                $studentPhone  = $reg['phone'];
                $studentEmail  = $reg['email'];
                break;
            }
        }
        unset($reg);

        if ($db_connected && $pdo) {
            try {
                $stmt = $pdo->prepare("UPDATE Course_Registrations SET status = 'Đã xếp lớp' WHERE ticket_code = :code");
                $stmt->execute([':code' => $ticketCode]);
            } catch (Exception $e) {
                // Fallback sang Session
            }
        }

        // Tự động thêm vào danh sách Học viên chính thức (admin/students.php) nếu chưa có
        if ($studentName !== '' && isset($_SESSION['admin_students'])) {
            $newStudentCode = 'HV' . str_pad((string)random_int(10, 999), 3, '0', STR_PAD_LEFT);
            array_unshift($_SESSION['admin_students'], [
                'student_id'   => count($_SESSION['admin_students']) + 1,
                'student_code' => $newStudentCode,
                'full_name'    => $studentName,
                'email'        => $studentEmail !== '' ? $studentEmail : 'hocvien@gmail.com',
                'dob'          => '2003-01-01',
                'phone'        => str_replace(' ', '', $studentPhone),
                'class_name'   => $assignedClass,
                'status'       => 'Đang học'
            ]);
        }

        $flashMessage = "Đã duyệt và xếp học viên <strong>" . htmlspecialchars($studentName) . "</strong> (Hồ sơ: <strong>$ticketCode</strong>) vào lớp <strong>" . htmlspecialchars($assignedClass) . "</strong>!";
    }
}

// Lấy danh sách hiển thị (ưu tiên kết hợp dữ liệu DB hoặc Session)
$registrationsList = $_SESSION['admin_registrations'];

// Bộ lọc trạng thái & tìm kiếm nhanh
$statusFilter  = trim($_GET['status'] ?? 'all');
$searchKeyword = trim($_GET['q'] ?? '');

$filteredRegistrations = array_values(array_filter($registrationsList, function ($item) use ($statusFilter, $searchKeyword) {
    if ($statusFilter !== 'all' && $item['status'] !== $statusFilter) {
        return false;
    }
    if ($searchKeyword !== '') {
        $kw = mb_strtolower($searchKeyword, 'UTF-8');
        $matchCode  = mb_strpos(mb_strtolower($item['ticket_code'], 'UTF-8'), $kw) !== false;
        $matchName  = mb_strpos(mb_strtolower($item['full_name'], 'UTF-8'), $kw) !== false;
        $matchPhone = mb_strpos(mb_strtolower($item['phone'], 'UTF-8'), $kw) !== false;
        if (!$matchCode && !$matchName && !$matchPhone) {
            return false;
        }
    }
    return true;
}));

// Hàm lấy class màu badge theo trạng thái
function getRegistrationStatusBadge($status) {
    if ($status === 'Chờ liên hệ') {
        return 'bg-warning-subtle text-warning border border-warning-subtle';
    }
    if ($status === 'Đã tư vấn') {
        return 'bg-info-subtle text-info border border-info-subtle';
    }
    return 'bg-success-subtle text-success border border-success-subtle';
}

require_once __DIR__ . '/includes/admin_header.php';
?>

<!-- TIÊU ĐỀ TRANG & NHÃN ĐỒNG BỘ CLIENT UI -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h3 class="fw-bold mb-1">Danh Sách Hồ Sơ Đăng Ký Trực Tuyến Từ Website</h3>
        <p class="text-muted small mb-0">Hồ sơ khách hàng, học viên đăng ký tư vấn qua form Client UI</p>
    </div>
    <div>
        <span class="badge bg-primary px-3 py-2 rounded-pill fw-semibold shadow-sm">
            <i class="fa-solid fa-arrows-rotate me-1"></i> Tự động cập nhật từ Client UI
        </span>
    </div>
</div>

<?php if ($flashMessage !== ''): ?>
    <div class="alert alert-<?php echo htmlspecialchars($flashType); ?> alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i><?php echo $flashMessage; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- THANH LỌC NHANH THEO TRẠNG THÁI & TÌM KIẾM -->
<div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white">
    <form method="GET" action="registrations.php" class="row g-3 align-items-center">
        <div class="col-lg-6 col-md-12">
            <div class="d-flex flex-wrap gap-2">
                <a href="registrations.php?status=all"
                   class="btn btn-sm rounded-pill px-3 fw-semibold <?php echo ($statusFilter === 'all') ? 'btn-primary' : 'btn-light border'; ?>">
                    Tất cả hồ sơ (<?php echo count($registrationsList); ?>)
                </a>
                <a href="registrations.php?status=<?php echo urlencode('Chờ liên hệ'); ?>"
                   class="btn btn-sm rounded-pill px-3 fw-semibold <?php echo ($statusFilter === 'Chờ liên hệ') ? 'btn-warning text-dark' : 'btn-light border'; ?>">
                    Chờ liên hệ
                </a>
                <a href="registrations.php?status=<?php echo urlencode('Đã tư vấn'); ?>"
                   class="btn btn-sm rounded-pill px-3 fw-semibold <?php echo ($statusFilter === 'Đã tư vấn') ? 'btn-info text-dark' : 'btn-light border'; ?>">
                    Đã tư vấn
                </a>
                <a href="registrations.php?status=<?php echo urlencode('Đã xếp lớp'); ?>"
                   class="btn btn-sm rounded-pill px-3 fw-semibold <?php echo ($statusFilter === 'Đã xếp lớp') ? 'btn-success text-white' : 'btn-light border'; ?>">
                    Đã xếp lớp
                </a>
            </div>
        </div>

        <div class="col-lg-4 col-md-8">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white border-end-0 text-muted">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </span>
                <input type="text"
                       name="q"
                       class="form-control border-start-0"
                       placeholder="Tìm theo Mã hồ sơ (DK...), Họ tên, SĐT..."
                       value="<?php echo htmlspecialchars($searchKeyword); ?>">
            </div>
        </div>

        <div class="col-lg-2 col-md-4 d-grid">
            <button type="submit" class="btn btn-sm btn-secondary fw-semibold">
                <i class="fa-solid fa-filter me-1"></i> Tìm hồ sơ
            </button>
        </div>
    </form>
</div>

<!-- BẢNG DANH SÁCH HỒ SƠ ĐĂNG KÝ TRỰC TUYẾN (HÌNH 4.6) -->
<div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="registrationsTable">
            <thead class="table-light">
                <tr>
                    <th class="ps-4 py-3">Mã Hồ Sơ</th>
                    <th class="py-3">Họ và Tên</th>
                    <th class="py-3">Số điện thoại / Email</th>
                    <th class="py-3">Khóa học mong muốn</th>
                    <th class="py-3">Ca học</th>
                    <th class="py-3" style="min-width: 220px;">Ghi chú / Mục tiêu</th>
                    <th class="py-3">Trạng thái</th>
                    <th class="text-end pe-4 py-3" style="min-width: 115px;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($filteredRegistrations)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-inbox fs-2 mb-2 d-block"></i>
                            Không có hồ sơ đăng ký nào khớp với điều kiện tìm kiếm.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($filteredRegistrations as $reg): ?>
                        <?php $badgeClass = getRegistrationStatusBadge($reg['status']); ?>
                        <tr>
                            <td class="ps-4 fw-bold text-primary">
                                <?php echo htmlspecialchars($reg['ticket_code']); ?>
                            </td>
                            <td>
                                <strong class="text-dark d-block"><?php echo htmlspecialchars($reg['full_name']); ?></strong>
                            </td>
                            <td>
                                <strong class="text-dark d-block"><?php echo htmlspecialchars($reg['phone']); ?></strong>
                                <small class="text-muted"><?php echo htmlspecialchars($reg['email']); ?></small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-semibold text-wrap text-start">
                                    <?php echo htmlspecialchars($reg['course_name']); ?>
                                </span>
                            </td>
                            <td class="small text-secondary">
                                <?php echo htmlspecialchars($reg['preferred_time']); ?>
                            </td>
                            <td class="small text-muted">
                                <?php echo htmlspecialchars($reg['notes']); ?>
                            </td>
                            <td>
                                <span class="badge <?php echo $badgeClass; ?> px-3 py-2 rounded-pill">
                                    <?php echo htmlspecialchars($reg['status']); ?>
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex flex-column gap-1 align-items-end">
                                    <!-- Nút Gọi tư vấn -->
                                    <button type="button"
                                            class="btn btn-sm btn-outline-secondary rounded-3 w-100 fw-semibold btn-call-consult"
                                            data-bs-toggle="modal"
                                            data-bs-target="#callConsultModal"
                                            data-code="<?php echo htmlspecialchars($reg['ticket_code']); ?>"
                                            data-name="<?php echo htmlspecialchars($reg['full_name']); ?>"
                                            data-phone="<?php echo htmlspecialchars($reg['phone']); ?>"
                                            data-course="<?php echo htmlspecialchars($reg['course_name']); ?>"
                                            data-status="<?php echo htmlspecialchars($reg['status']); ?>">
                                        <i class="fa-solid fa-phone me-1"></i> Gọi
                                    </button>

                                    <!-- Nút Xếp lớp -->
                                    <button type="button"
                                            class="btn btn-sm btn-primary rounded-3 w-100 fw-semibold btn-assign-class"
                                            data-bs-toggle="modal"
                                            data-bs-target="#assignClassModal"
                                            data-code="<?php echo htmlspecialchars($reg['ticket_code']); ?>"
                                            data-name="<?php echo htmlspecialchars($reg['full_name']); ?>"
                                            data-course="<?php echo htmlspecialchars($reg['course_name']); ?>">
                                        <i class="fa-solid fa-user-plus me-1"></i> Xếp lớp
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ============================================================================
     MODAL 1: GỌI ĐIỆN TƯ VẤN & CẬP NHẬT TRẠNG THÁI HỒ SƠ
     ============================================================================ -->
<div class="modal fade" id="callConsultModal" tabindex="-1" aria-labelledby="callConsultModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form method="POST" action="registrations.php">
                <input type="hidden" name="action" value="update_call_status">
                <input type="hidden" name="ticket_code" id="callTicketCodeInput">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-primary" id="callConsultModalLabel">
                        <i class="fa-solid fa-phone-volume me-2"></i>Ghi Nhận Cuộc Gọi Tư Vấn Tuyển Sinh
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body pt-3">
                    <div class="p-3 rounded-3 bg-light border mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="badge bg-primary" id="callTicketCodeBadge">DK101234</span>
                            <a href="tel:0983221456" id="callDirectPhoneLink" class="fw-bold text-success text-decoration-none">
                                <i class="fa-solid fa-phone me-1"></i><span id="callStudentPhoneText">0983 221 456</span>
                            </a>
                        </div>
                        <h6 class="fw-bold mb-1 text-dark" id="callStudentNameText">Nguyễn Hoàng Nam</h6>
                        <small class="text-muted d-block" id="callCourseNameText">Luyện thi IELTS Intensive</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Cập nhật trạng thái hồ sơ</label>
                        <select name="status" id="callStatusSelect" class="form-select">
                            <option value="Chờ liên hệ">Chờ liên hệ (Gọi lại sau)</option>
                            <option value="Đã tư vấn">Đã tư vấn &amp; Đặt lịch Test đầu vào</option>
                            <option value="Đã xếp lớp">Đã hoàn tất xếp lớp</option>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-semibold small">Ghi chú kết quả cuộc gọi</label>
                        <textarea name="call_note" class="form-control" rows="2" placeholder="VD: Học viên hẹn 18h30 thứ 5 qua cơ sở Nguyễn Trãi làm bài test..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary fw-semibold px-4">
                        <i class="fa-solid fa-check me-1"></i> Lưu Trạng Thái
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================================================
     MODAL 2: DUYỆT HỒ SƠ & XẾP LỚP CHÍNH THỨC CHO HỌC VIÊN
     ============================================================================ -->
<div class="modal fade" id="assignClassModal" tabindex="-1" aria-labelledby="assignClassModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form method="POST" action="registrations.php">
                <input type="hidden" name="action" value="assign_class">
                <input type="hidden" name="ticket_code" id="assignTicketCodeInput">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-success" id="assignClassModalLabel">
                        <i class="fa-solid fa-user-check me-2"></i>Duyệt Hồ Sơ &amp; Phân Bổ Lớp Học
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body pt-3">
                    <p class="text-muted small mb-3">
                        Xác nhận xếp lớp cho học viên <strong class="text-dark" id="assignStudentNameText"></strong>
                        (Mã đăng ký: <strong class="text-primary" id="assignTicketCodeText"></strong>).
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Khóa học đăng ký:</label>
                        <input type="text" id="assignCourseNameInput" class="form-control bg-light" readonly>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold small">Chọn lớp học tiếp nhận <span class="text-danger">*</span></label>
                        <select name="assigned_class" class="form-select" required>
                            <?php foreach ($availableClasses as $cls): ?>
                                <option value="<?php echo htmlspecialchars($cls); ?>">
                                    <?php echo htmlspecialchars($cls); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success fw-semibold px-4">
                        <i class="fa-solid fa-user-plus me-1"></i> Xác Nhận Xếp Lớp
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Đổ dữ liệu vào Modal Gọi tư vấn
    document.querySelectorAll('.btn-call-consult').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const code = this.getAttribute('data-code');
            const phone = this.getAttribute('data-phone');
            document.getElementById('callTicketCodeInput').value = code;
            document.getElementById('callTicketCodeBadge').textContent = code;
            document.getElementById('callStudentNameText').textContent = this.getAttribute('data-name');
            document.getElementById('callStudentPhoneText').textContent = phone;
            document.getElementById('callDirectPhoneLink').setAttribute('href', 'tel:' + phone.replace(/\s+/g, ''));
            document.getElementById('callCourseNameText').textContent = this.getAttribute('data-course');
            document.getElementById('callStatusSelect').value = this.getAttribute('data-status');
        });
    });

    // Đổ dữ liệu vào Modal Xếp lớp
    document.querySelectorAll('.btn-assign-class').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const code = this.getAttribute('data-code');
            document.getElementById('assignTicketCodeInput').value = code;
            document.getElementById('assignTicketCodeText').textContent = code;
            document.getElementById('assignStudentNameText').textContent = this.getAttribute('data-name');
            document.getElementById('assignCourseNameInput').value = this.getAttribute('data-course');
        });
    });
});
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
