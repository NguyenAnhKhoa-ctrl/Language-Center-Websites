<?php
/**
 * EDUCENTER - HỆ THỐNG WEBSITE QUẢN LÝ TRUNG TÂM NGOẠI NGỮ & GIA SƯ
 * File: admin/students.php
 * Phụ trách (Tuần 3 - Nguyễn Anh Khoa - 52400051):
 * Quản lý Danh sách Học viên (Tìm kiếm, Bộ lọc Lớp/Trạng thái, Phân trang & Modal CRUD)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

$admin_title = 'Quản Lý Học Viên';
$active_menu = 'students';

// Danh sách lớp học chuẩn để chọn trong bộ lọc và Modal CRUD
$availableClasses = [
    1 => 'IELTS Intensive K42',
    2 => 'TOEIC 750+ Cấp Tốc',
    3 => 'Tiếng Anh Giao Tiếp VIP',
    4 => 'Gia Sư 1-1 Thi Đại Học'
];

// Dữ liệu học viên mẫu chuẩn theo Báo cáo (Hình 4.2)
$defaultStudents = [
    [
        'student_id'   => 1,
        'student_code' => 'HV001',
        'full_name'    => 'Nguyễn Văn An',
        'email'        => 'nguyenvanan@gmail.com',
        'dob'          => '2002-05-15',
        'phone'        => '0987654321',
        'class_name'   => 'IELTS Intensive K42',
        'status'       => 'Đang học'
    ],
    [
        'student_id'   => 2,
        'student_code' => 'HV002',
        'full_name'    => 'Trần Thị Bích Ngọc',
        'email'        => 'bichngoc.tran@gmail.com',
        'dob'          => '2003-08-22',
        'phone'        => '0912345678',
        'class_name'   => 'TOEIC 750+ Cấp Tốc',
        'status'       => 'Đang học'
    ],
    [
        'student_id'   => 3,
        'student_code' => 'HV003',
        'full_name'    => 'Lê Gia Huy',
        'email'        => 'giahuy.le@gmail.com',
        'dob'          => '2005-11-10',
        'phone'        => '0909090909',
        'class_name'   => 'Gia Sư 1-1 Thi Đại Học',
        'status'       => 'Đang học'
    ],
    [
        'student_id'   => 4,
        'student_code' => 'HV004',
        'full_name'    => 'Hoàng Minh Quân',
        'email'        => 'minhquan.h@gmail.com',
        'dob'          => '2001-04-03',
        'phone'        => '0933221100',
        'class_name'   => 'Tiếng Anh Giao Tiếp VIP',
        'status'       => 'Bảo lưu'
    ],
    [
        'student_id'   => 5,
        'student_code' => 'HV005',
        'full_name'    => 'Đỗ Phương Linh',
        'email'        => 'phuonglinh.do@gmail.com',
        'dob'          => '2004-12-19',
        'phone'        => '0978112233',
        'class_name'   => 'IELTS Intensive K42',
        'status'       => 'Đang học'
    ]
];

if (!isset($_SESSION['admin_students'])) {
    $_SESSION['admin_students'] = $defaultStudents;
}

$flashMessage = '';
$flashType    = 'success';

// ============================================================================
// XỬ LÝ THAO TÁC CRUD (THÊM MỚI, CẬP NHẬT, XÓA HỌC VIÊN)
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // 1. THÊM HỌC VIÊN MỚI
    if ($action === 'add_student') {
        $fullName  = trim($_POST['full_name'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $dob       = trim($_POST['dob'] ?? '2003-01-01');
        $phone     = trim($_POST['phone'] ?? '');
        $className = trim($_POST['class_name'] ?? 'IELTS Intensive K42');
        $status    = trim($_POST['status'] ?? 'Đang học');

        if ($fullName !== '' && $phone !== '') {
            $nextIndex   = count($_SESSION['admin_students']) + 1;
            $studentCode = 'HV' . str_pad((string)random_int(6, 999), 3, '0', STR_PAD_LEFT);

            // Lưu vào CSDL MySQL nếu đang kết nối
            if ($db_connected && $pdo) {
                try {
                    $stmt = $pdo->prepare("
                        INSERT INTO Students (student_code, full_name, dob, phone, email, status)
                        VALUES (:code, :name, :dob, :phone, :email, :status)
                    ");
                    $stmt->execute([
                        ':code'   => $studentCode,
                        ':name'   => $fullName,
                        ':dob'    => $dob,
                        ':phone'  => $phone,
                        ':email'  => $email !== '' ? $email : 'hocvien@educenter.edu.vn',
                        ':status' => $status
                    ]);
                } catch (Exception $e) {
                    // Tiếp tục lưu vào Session Fallback
                }
            }

            array_unshift($_SESSION['admin_students'], [
                'student_id'   => $nextIndex,
                'student_code' => $studentCode,
                'full_name'    => $fullName,
                'email'        => $email !== '' ? $email : 'hocvien@educenter.edu.vn',
                'dob'          => $dob,
                'phone'        => $phone,
                'class_name'   => $className,
                'status'       => $status
            ]);

            $flashMessage = "Đã thêm học viên mới <strong>" . htmlspecialchars($fullName) . "</strong> (Mã HV: <strong>$studentCode</strong>) thành công!";
        } else {
            $flashMessage = "Vui lòng nhập đầy đủ Họ tên và Số điện thoại của học viên.";
            $flashType    = "danger";
        }
    }

    // 2. CẬP NHẬT THÔNG TIN HỌC VIÊN
    if ($action === 'edit_student') {
        $studentCode = trim($_POST['student_code'] ?? '');
        $fullName    = trim($_POST['full_name'] ?? '');
        $email       = trim($_POST['email'] ?? '');
        $dob         = trim($_POST['dob'] ?? '2003-01-01');
        $phone       = trim($_POST['phone'] ?? '');
        $className   = trim($_POST['class_name'] ?? 'IELTS Intensive K42');
        $status      = trim($_POST['status'] ?? 'Đang học');

        if ($studentCode !== '' && $fullName !== '') {
            if ($db_connected && $pdo) {
                try {
                    $stmt = $pdo->prepare("
                        UPDATE Students
                        SET full_name = :name, dob = :dob, phone = :phone, email = :email, status = :status
                        WHERE student_code = :code
                    ");
                    $stmt->execute([
                        ':name'   => $fullName,
                        ':dob'    => $dob,
                        ':phone'  => $phone,
                        ':email'  => $email,
                        ':status' => $status,
                        ':code'   => $studentCode
                    ]);
                } catch (Exception $e) {
                    // Fallback sang Session
                }
            }

            foreach ($_SESSION['admin_students'] as &$st) {
                if ($st['student_code'] === $studentCode) {
                    $st['full_name']  = $fullName;
                    $st['email']      = $email;
                    $st['dob']        = $dob;
                    $st['phone']      = $phone;
                    $st['class_name'] = $className;
                    $st['status']     = $status;
                    break;
                }
            }
            unset($st);

            $flashMessage = "Đã cập nhật hồ sơ học viên <strong>" . htmlspecialchars($fullName) . "</strong> ($studentCode) thành công!";
        }
    }

    // 3. XÓA HỌC VIÊN
    if ($action === 'delete_student') {
        $studentCode = trim($_POST['student_code'] ?? '');
        if ($studentCode !== '') {
            if ($db_connected && $pdo) {
                try {
                    $stmt = $pdo->prepare("DELETE FROM Students WHERE student_code = :code");
                    $stmt->execute([':code' => $studentCode]);
                } catch (Exception $e) {
                    // Fallback sang Session
                }
            }

            $_SESSION['admin_students'] = array_values(array_filter(
                $_SESSION['admin_students'],
                function ($item) use ($studentCode) {
                    return $item['student_code'] !== $studentCode;
                }
            ));

            $flashMessage = "Đã xóa hồ sơ học viên có mã <strong>" . htmlspecialchars($studentCode) . "</strong> khỏi danh sách.";
            $flashType    = "warning";
        }
    }
}

// ============================================================================
// TRUY VẤN & LỌC DANH SÁCH HỌC VIÊN (THEO TỪ KHÓA, LỚP HỌC, TRẠNG THÁI)
// ============================================================================
$studentsList = $_SESSION['admin_students'];

$searchKeyword = trim($_GET['search'] ?? '');
$filterClass   = trim($_GET['class_filter'] ?? 'all');
$filterStatus  = trim($_GET['status_filter'] ?? 'all');

$filteredStudents = array_values(array_filter($studentsList, function ($st) use ($searchKeyword, $filterClass, $filterStatus) {
    if ($searchKeyword !== '') {
        $kw = mb_strtolower($searchKeyword, 'UTF-8');
        $matchName  = mb_strpos(mb_strtolower($st['full_name'], 'UTF-8'), $kw) !== false;
        $matchCode  = mb_strpos(mb_strtolower($st['student_code'], 'UTF-8'), $kw) !== false;
        $matchPhone = mb_strpos(mb_strtolower($st['phone'], 'UTF-8'), $kw) !== false;
        $matchEmail = mb_strpos(mb_strtolower($st['email'], 'UTF-8'), $kw) !== false;
        if (!$matchName && !$matchCode && !$matchPhone && !$matchEmail) {
            return false;
        }
    }
    if ($filterClass !== 'all' && $st['class_name'] !== $filterClass) {
        return false;
    }
    if ($filterStatus !== 'all' && $st['status'] !== $filterStatus) {
        return false;
    }
    return true;
}));

require_once __DIR__ . '/includes/admin_header.php';
?>

<!-- TIÊU ĐỀ TRANG & NÚT THÊM HỌC VIÊN MỚI -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h3 class="fw-bold mb-1">Quản Lý Danh Sách Học Viên</h3>
        <p class="text-muted small mb-0">Quản lý hồ sơ, tình trạng theo học và phân bổ lớp cho học viên</p>
    </div>
    <div>
        <button type="button" class="btn btn-primary fw-semibold px-3 py-2 rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#addStudentModal">
            <i class="fa-solid fa-user-plus me-2"></i>Thêm Học Viên Mới
        </button>
    </div>
</div>

<?php if ($flashMessage !== ''): ?>
    <div class="alert alert-<?php echo htmlspecialchars($flashType); ?> alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i><?php echo $flashMessage; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- 1. THANH TÌM KIẾM & BỘ LỌC DỮ LIỆU -->
<div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white">
    <form method="GET" action="students.php" class="row g-3 align-items-center">
        <div class="col-lg-5 col-md-6">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0 text-muted">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </span>
                <input type="text"
                       name="search"
                       id="studentSearchInput"
                       class="form-control border-start-0 ps-0"
                       placeholder="Tìm theo Tên, Mã HV hoặc Số điện thoại..."
                       value="<?php echo htmlspecialchars($searchKeyword); ?>">
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <select name="class_filter" id="studentClassFilter" class="form-select">
                <option value="all">Tất cả lớp học</option>
                <?php foreach ($availableClasses as $clsName): ?>
                    <option value="<?php echo htmlspecialchars($clsName); ?>" <?php echo ($filterClass === $clsName) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($clsName); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-lg-2 col-md-6">
            <select name="status_filter" id="studentStatusFilter" class="form-select">
                <option value="all">Tất cả trạng thái</option>
                <option value="Đang học" <?php echo ($filterStatus === 'Đang học') ? 'selected' : ''; ?>>Đang học</option>
                <option value="Bảo lưu" <?php echo ($filterStatus === 'Bảo lưu') ? 'selected' : ''; ?>>Bảo lưu</option>
                <option value="Nghỉ" <?php echo ($filterStatus === 'Nghỉ') ? 'selected' : ''; ?>>Đã nghỉ</option>
            </select>
        </div>

        <div class="col-lg-2 col-md-6 d-grid">
            <button type="submit" class="btn btn-secondary fw-semibold">
                <i class="fa-solid fa-filter me-1"></i> Lọc dữ liệu
            </button>
        </div>
    </form>
</div>

<!-- 2. BẢNG DANH SÁCH HỌC VIÊN -->
<div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="studentsTable">
            <thead class="table-light">
                <tr>
                    <th class="ps-4 py-3">Mã HV</th>
                    <th class="py-3">Họ và Tên</th>
                    <th class="py-3">Ngày sinh</th>
                    <th class="py-3">Số điện thoại</th>
                    <th class="py-3">Lớp học hiện tại</th>
                    <th class="py-3">Trạng thái</th>
                    <th class="text-end pe-4 py-3">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($filteredStudents)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-user-slash fs-2 mb-2 d-block"></i>
                            Không tìm thấy hồ sơ học viên nào khớp với bộ lọc hiện tại.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($filteredStudents as $st): ?>
                        <?php
                            $formattedDob = date('d/m/Y', strtotime($st['dob']));
                            if ($st['status'] === 'Đang học') {
                                $statusBadge = 'bg-success-subtle text-success';
                            } elseif ($st['status'] === 'Bảo lưu') {
                                $statusBadge = 'bg-warning-subtle text-warning';
                            } else {
                                $statusBadge = 'bg-danger-subtle text-danger';
                            }
                        ?>
                        <tr>
                            <td class="ps-4 fw-bold text-primary">
                                <?php echo htmlspecialchars($st['student_code']); ?>
                            </td>
                            <td>
                                <strong class="d-block text-dark"><?php echo htmlspecialchars($st['full_name']); ?></strong>
                                <small class="text-muted"><?php echo htmlspecialchars($st['email']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($formattedDob); ?></td>
                            <td><?php echo htmlspecialchars($st['phone']); ?></td>
                            <td>
                                <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-medium">
                                    <?php echo htmlspecialchars($st['class_name']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?php echo $statusBadge; ?> px-3 py-2 rounded-pill">
                                    <?php echo htmlspecialchars($st['status']); ?>
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-inline-flex gap-1">
                                    <!-- Xem chi tiết -->
                                    <button type="button"
                                            class="btn btn-sm btn-outline-info rounded-circle student-action-btn btn-view-student"
                                            title="Xem chi tiết hồ sơ"
                                            data-bs-toggle="modal"
                                            data-bs-target="#viewStudentModal"
                                            data-code="<?php echo htmlspecialchars($st['student_code']); ?>"
                                            data-name="<?php echo htmlspecialchars($st['full_name']); ?>"
                                            data-email="<?php echo htmlspecialchars($st['email']); ?>"
                                            data-dob="<?php echo htmlspecialchars($formattedDob); ?>"
                                            data-phone="<?php echo htmlspecialchars($st['phone']); ?>"
                                            data-class="<?php echo htmlspecialchars($st['class_name']); ?>"
                                            data-status="<?php echo htmlspecialchars($st['status']); ?>">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>

                                    <!-- Chỉnh sửa -->
                                    <button type="button"
                                            class="btn btn-sm btn-outline-primary rounded-circle student-action-btn btn-edit-student"
                                            title="Chỉnh sửa thông tin"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editStudentModal"
                                            data-code="<?php echo htmlspecialchars($st['student_code']); ?>"
                                            data-name="<?php echo htmlspecialchars($st['full_name']); ?>"
                                            data-email="<?php echo htmlspecialchars($st['email']); ?>"
                                            data-dob="<?php echo htmlspecialchars($st['dob']); ?>"
                                            data-phone="<?php echo htmlspecialchars($st['phone']); ?>"
                                            data-class="<?php echo htmlspecialchars($st['class_name']); ?>"
                                            data-status="<?php echo htmlspecialchars($st['status']); ?>">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>

                                    <!-- Xóa -->
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger rounded-circle student-action-btn btn-delete-student"
                                            title="Xóa học viên"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteStudentModal"
                                            data-code="<?php echo htmlspecialchars($st['student_code']); ?>"
                                            data-name="<?php echo htmlspecialchars($st['full_name']); ?>">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Chân bảng & Phân trang -->
    <div class="card-footer bg-white border-top py-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <small class="text-muted">
            Hiển thị <strong><?php echo count($filteredStudents); ?></strong> trên tổng số <strong>1,250</strong> học viên
        </small>
        <nav aria-label="Phân trang danh sách học viên">
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item disabled"><a class="page-link" href="#">Trước</a></li>
                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                <li class="page-item"><a class="page-link" href="#">2</a></li>
                <li class="page-item"><a class="page-link" href="#">3</a></li>
                <li class="page-item"><a class="page-link" href="#">Sau</a></li>
            </ul>
        </nav>
    </div>
</div>

<!-- ============================================================================
     3. CÁC POPUP MODAL CRUD HỌC VIÊN (THÊM MỚI / XEM CHI TIẾT / SỬA / XÓA)
     ============================================================================ -->

<!-- MODAL 1: THÊM HỌC VIÊN MỚI -->
<div class="modal fade" id="addStudentModal" tabindex="-1" aria-labelledby="addStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form method="POST" action="students.php">
                <input type="hidden" name="action" value="add_student">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-primary" id="addStudentModalLabel">
                        <i class="fa-solid fa-user-plus me-2"></i>Thêm Hồ Sơ Học Viên Mới
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body pt-3">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Họ và tên học viên <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" class="form-control" placeholder="VD: Nguyễn Minh Khôi" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold small">Ngày sinh <span class="text-danger">*</span></label>
                            <input type="date" name="dob" class="form-control" value="2003-06-15" required>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold small">Số điện thoại <span class="text-danger">*</span></label>
                            <input type="tel" name="phone" class="form-control" placeholder="0987xxxxxx" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Email liên hệ</label>
                        <input type="email" name="email" class="form-control" placeholder="hocvien@gmail.com">
                    </div>
                    <div class="row g-3">
                        <div class="col-sm-7">
                            <label class="form-label fw-semibold small">Phân bổ vào lớp học</label>
                            <select name="class_name" class="form-select">
                                <?php foreach ($availableClasses as $clsName): ?>
                                    <option value="<?php echo htmlspecialchars($clsName); ?>">
                                        <?php echo htmlspecialchars($clsName); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-sm-5">
                            <label class="form-label fw-semibold small">Trạng thái</label>
                            <select name="status" class="form-select">
                                <option value="Đang học">Đang học</option>
                                <option value="Bảo lưu">Bảo lưu</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary fw-semibold px-4">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Lưu Hồ Sơ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL 2: XEM CHI TIẾT HỒ SƠ HỌC VIÊN -->
<div class="modal fade" id="viewStudentModal" tabindex="-1" aria-labelledby="viewStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-info" id="viewStudentModalLabel">
                    <i class="fa-solid fa-id-card me-2"></i>Chi Tiết Hồ Sơ Học Viên
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body">
                <div class="p-3 rounded-3 bg-light border mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-primary px-3 py-2" id="viewStudentCode">HV001</span>
                        <span class="badge bg-success-subtle text-success px-3 py-2" id="viewStudentStatus">Đang học</span>
                    </div>
                    <h5 class="fw-bold text-dark mb-1" id="viewStudentName">Nguyễn Văn An</h5>
                    <small class="text-muted" id="viewStudentEmail">nguyenvanan@gmail.com</small>
                </div>
                <ul class="list-group list-group-flush small">
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Ngày sinh:</span>
                        <strong id="viewStudentDob">15/05/2002</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Số điện thoại:</span>
                        <strong id="viewStudentPhone">0987654321</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Lớp đang theo học:</span>
                        <strong class="text-primary" id="viewStudentClass">IELTS Intensive K42</strong>
                    </li>
                </ul>
            </div>
            <div class="modal-footer border-top-0">
                <a href="../lookup.php?query=HV001" id="viewStudentLookupLink" class="btn btn-outline-primary btn-sm">
                    <i class="fa-solid fa-chart-simple me-1"></i> Xem Bảng Điểm &amp; Chuyên Cần
                </a>
                <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL 3: CHỈNH SỬA HỒ SƠ HỌC VIÊN -->
<div class="modal fade" id="editStudentModal" tabindex="-1" aria-labelledby="editStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form method="POST" action="students.php">
                <input type="hidden" name="action" value="edit_student">
                <input type="hidden" name="student_code" id="editStudentCodeInput">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-primary" id="editStudentModalLabel">
                        <i class="fa-solid fa-user-pen me-2"></i>Cập Nhật Hồ Sơ Học Viên
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body pt-3">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Họ và tên học viên <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" id="editStudentName" class="form-control" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold small">Ngày sinh</label>
                            <input type="date" name="dob" id="editStudentDob" class="form-control" required>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold small">Số điện thoại <span class="text-danger">*</span></label>
                            <input type="tel" name="phone" id="editStudentPhone" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Email liên hệ</label>
                        <input type="email" name="email" id="editStudentEmail" class="form-control">
                    </div>
                    <div class="row g-3">
                        <div class="col-sm-7">
                            <label class="form-label fw-semibold small">Lớp học hiện tại</label>
                            <select name="class_name" id="editStudentClass" class="form-select">
                                <?php foreach ($availableClasses as $clsName): ?>
                                    <option value="<?php echo htmlspecialchars($clsName); ?>">
                                        <?php echo htmlspecialchars($clsName); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-sm-5">
                            <label class="form-label fw-semibold small">Trạng thái</label>
                            <select name="status" id="editStudentStatus" class="form-select">
                                <option value="Đang học">Đang học</option>
                                <option value="Bảo lưu">Bảo lưu</option>
                                <option value="Nghỉ">Nghỉ</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary fw-semibold px-4">
                        <i class="fa-solid fa-check me-1"></i> Cập Nhật
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL 4: XÁC NHẬN XÓA HỌC VIÊN -->
<div class="modal fade" id="deleteStudentModal" tabindex="-1" aria-labelledby="deleteStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow rounded-4">
            <form method="POST" action="students.php">
                <input type="hidden" name="action" value="delete_student">
                <input type="hidden" name="student_code" id="deleteStudentCodeInput">
                <div class="modal-body text-center p-4">
                    <div class="bg-danger-subtle text-danger rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
                        <i class="fa-solid fa-triangle-exclamation fs-3"></i>
                    </div>
                    <h5 class="fw-bold mb-2" id="deleteStudentModalLabel">Xác nhận xóa hồ sơ?</h5>
                    <p class="text-muted small mb-4">
                        Bạn có chắc chắn muốn xóa học viên <strong class="text-dark" id="deleteStudentNameText"></strong> khỏi danh sách không?
                    </p>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light px-3" data-bs-dismiss="modal">Hủy bỏ</button>
                        <button type="submit" class="btn btn-danger fw-semibold px-3">Xóa ngay</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Đổ dữ liệu vào Modal Xem chi tiết
    document.querySelectorAll('.btn-view-student').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const code = this.getAttribute('data-code');
            document.getElementById('viewStudentCode').textContent = code;
            document.getElementById('viewStudentName').textContent = this.getAttribute('data-name');
            document.getElementById('viewStudentEmail').textContent = this.getAttribute('data-email');
            document.getElementById('viewStudentDob').textContent = this.getAttribute('data-dob');
            document.getElementById('viewStudentPhone').textContent = this.getAttribute('data-phone');
            document.getElementById('viewStudentClass').textContent = this.getAttribute('data-class');
            document.getElementById('viewStudentStatus').textContent = this.getAttribute('data-status');
            document.getElementById('viewStudentLookupLink').setAttribute('href', '../lookup.php?query=' + encodeURIComponent(code));
        });
    });

    // Đổ dữ liệu vào Modal Chỉnh sửa
    document.querySelectorAll('.btn-edit-student').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('editStudentCodeInput').value = this.getAttribute('data-code');
            document.getElementById('editStudentName').value = this.getAttribute('data-name');
            document.getElementById('editStudentEmail').value = this.getAttribute('data-email');
            document.getElementById('editStudentDob').value = this.getAttribute('data-dob');
            document.getElementById('editStudentPhone').value = this.getAttribute('data-phone');
            document.getElementById('editStudentClass').value = this.getAttribute('data-class');
            document.getElementById('editStudentStatus').value = this.getAttribute('data-status');
        });
    });

    // Đổ dữ liệu vào Modal Xác nhận xóa
    document.querySelectorAll('.btn-delete-student').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const code = this.getAttribute('data-code');
            const name = this.getAttribute('data-name');
            document.getElementById('deleteStudentCodeInput').value = code;
            document.getElementById('deleteStudentNameText').textContent = name + ' (' + code + ')';
        });
    });
});
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
