<?php
/**
 * EDUCENTER - HỆ THỐNG WEBSITE QUẢN LÝ TRUNG TÂM NGOẠI NGỮ & GIA SƯ
 * File: admin/teachers.php
 * Phụ trách (Tuần 3 - Lý Hạo Danh - 52400175):
 * Quản lý Đội ngũ Giảng viên & Gia sư (Card Layout, Hồ sơ chứng chỉ & Modal CRUD Thêm/Sửa/Xóa)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

$admin_title = 'Quản Lý Giảng Viên & Gia Sư';
$active_menu = 'teachers';

// Khởi tạo danh sách giảng viên trong Session từ dữ liệu chuẩn
if (!isset($_SESSION['admin_teachers'])) {
    $_SESSION['admin_teachers'] = $sample_teachers;
}

$flashMessage = '';
$flashType    = 'success';

// ============================================================================
// XỬ LÝ THAO TÁC CRUD GIẢNG VIÊN / GIA SƯ (THÊM MỚI, CẬP NHẬT, XÓA)
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // 1. THÊM GIẢNG VIÊN / GIA SƯ MỚI
    if ($action === 'add_teacher') {
        $fullName       = trim($_POST['full_name'] ?? '');
        $specialization = trim($_POST['specialization'] ?? 'Chuyên gia Luyện thi IELTS 8.5+');
        $degree         = trim($_POST['degree'] ?? '');
        $phone          = trim($_POST['phone'] ?? '');
        $email          = trim($_POST['email'] ?? '');
        $rating         = (float)($_POST['rating'] ?? 4.9);
        $avatar         = trim($_POST['avatar'] ?? '');

        if ($fullName !== '' && $phone !== '') {
            if ($avatar === '') {
                $avatar = 'https://ui-avatars.com/api/?name=' . urlencode($fullName) . '&background=1D4ED8&color=fff&size=256';
            }
            $newId = count($_SESSION['admin_teachers']) + random_int(5, 999);

            if ($db_connected && $pdo) {
                try {
                    $stmt = $pdo->prepare("
                        INSERT INTO Teachers (full_name, avatar, phone, email, specialization, degree, experience, rating)
                        VALUES (:full_name, :avatar, :phone, :email, :specialization, :degree, :experience, :rating)
                    ");
                    $stmt->execute([
                        ':full_name'      => $fullName,
                        ':avatar'         => $avatar,
                        ':phone'          => $phone,
                        ':email'          => $email !== '' ? $email : 'teacher@educenter.edu.vn',
                        ':specialization' => $specialization,
                        ':degree'         => $degree !== '' ? $degree : 'Chứng chỉ TESOL Quốc tế',
                        ':experience'     => '5 năm kinh nghiệm giảng dạy chuyên sâu',
                        ':rating'         => $rating
                    ]);
                    $newId = (int)$pdo->lastInsertId() ?: $newId;
                } catch (Exception $e) {
                    // Fallback về Session nếu lỗi DB
                }
            }

            $_SESSION['admin_teachers'][] = [
                'teacher_id'     => $newId,
                'full_name'      => $fullName,
                'avatar'         => $avatar,
                'specialization' => $specialization,
                'degree'         => $degree !== '' ? $degree : 'Chứng chỉ TESOL Quốc tế',
                'experience'     => '5 năm kinh nghiệm giảng dạy chuyên sâu',
                'rating'         => $rating,
                'phone'          => $phone,
                'email'          => $email !== '' ? $email : 'teacher@educenter.edu.vn'
            ];

            $flashMessage = "Đã thêm hồ sơ giảng viên <strong>" . htmlspecialchars($fullName) . "</strong> thành công!";
        } else {
            $flashMessage = 'Vui lòng nhập đầy đủ Họ tên và Số điện thoại của giảng viên.';
            $flashType    = 'danger';
        }
    }

    // 2. CẬP NHẬT HỒ SƠ GIẢNG VIÊN
    elseif ($action === 'edit_teacher') {
        $teacherId      = (int)($_POST['teacher_id'] ?? 0);
        $fullName       = trim($_POST['full_name'] ?? '');
        $specialization = trim($_POST['specialization'] ?? '');
        $degree         = trim($_POST['degree'] ?? '');
        $phone          = trim($_POST['phone'] ?? '');
        $email          = trim($_POST['email'] ?? '');
        $rating         = (float)($_POST['rating'] ?? 4.9);

        if ($teacherId > 0 && $fullName !== '') {
            if ($db_connected && $pdo) {
                try {
                    $stmt = $pdo->prepare("
                        UPDATE Teachers
                        SET full_name = :full_name, specialization = :specialization, degree = :degree,
                            phone = :phone, email = :email, rating = :rating
                        WHERE teacher_id = :id
                    ");
                    $stmt->execute([
                        ':full_name'      => $fullName,
                        ':specialization' => $specialization,
                        ':degree'         => $degree,
                        ':phone'          => $phone,
                        ':email'          => $email,
                        ':rating'         => $rating,
                        ':id'             => $teacherId
                    ]);
                } catch (Exception $e) {
                    // Fallback Session
                }
            }

            foreach ($_SESSION['admin_teachers'] as &$tc) {
                if ((int)$tc['teacher_id'] === $teacherId) {
                    $tc['full_name']      = $fullName;
                    $tc['specialization'] = $specialization;
                    $tc['degree']         = $degree;
                    $tc['phone']          = $phone;
                    $tc['email']          = $email;
                    $tc['rating']         = $rating;
                    break;
                }
            }
            unset($tc);

            $flashMessage = "Đã cập nhật thông tin giảng viên <strong>" . htmlspecialchars($fullName) . "</strong> thành công!";
        }
    }

    // 3. XÓA HỒ SƠ GIẢNG VIÊN
    elseif ($action === 'delete_teacher') {
        $teacherId = (int)($_POST['teacher_id'] ?? 0);
        $deletedName = '';

        if ($teacherId > 0) {
            if ($db_connected && $pdo) {
                try {
                    $stmt = $pdo->prepare("DELETE FROM Teachers WHERE teacher_id = :id");
                    $stmt->execute([':id' => $teacherId]);
                } catch (Exception $e) {
                    // Fallback Session
                }
            }

            foreach ($_SESSION['admin_teachers'] as $idx => $tc) {
                if ((int)$tc['teacher_id'] === $teacherId) {
                    $deletedName = $tc['full_name'];
                    unset($_SESSION['admin_teachers'][$idx]);
                    break;
                }
            }
            $_SESSION['admin_teachers'] = array_values($_SESSION['admin_teachers']);

            $flashMessage = "Đã xóa hồ sơ giảng viên <strong>" . htmlspecialchars($deletedName ?: ('#' . $teacherId)) . "</strong> khỏi danh sách.";
            $flashType    = 'warning';
        }
    }
}

$teachersList = $_SESSION['admin_teachers'];

require_once __DIR__ . '/includes/admin_header.php';
?>

<!-- TIÊU ĐỀ TRANG & NÚT THÊM GIẢNG VIÊN MỚI -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h3 class="fw-bold mb-1">Quản Lý Đội Ngũ Giảng Viên &amp; Gia Sư</h3>
        <p class="text-muted small mb-0">Theo dõi hợp đồng giảng dạy, phân công lớp và đánh giá chất lượng</p>
    </div>
    <div>
        <button type="button" class="btn btn-success rounded-pill px-4 shadow-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#addTeacherModal">
            <i class="fa-solid fa-plus me-2"></i>Thêm Giảng Viên Mới
        </button>
    </div>
</div>

<?php if ($flashMessage !== ''): ?>
    <div class="alert alert-<?php echo htmlspecialchars($flashType); ?> alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i><?php echo $flashMessage; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- DANH SÁCH THẺ HỒ SƠ GIẢNG VIÊN & GIA SƯ (CARD LAYOUT) -->
<div class="row g-4">
    <?php foreach ($teachersList as $t): ?>
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white text-center admin-teacher-card d-flex flex-column">
                <img src="<?php echo htmlspecialchars($t['avatar']); ?>"
                     alt="<?php echo htmlspecialchars($t['full_name']); ?>"
                     class="rounded-circle mx-auto mb-3 shadow-sm admin-teacher-avatar">

                <h5 class="fw-bold mb-1 text-dark"><?php echo htmlspecialchars($t['full_name']); ?></h5>
                <span class="badge bg-primary-subtle text-primary mb-2 px-3 py-1 rounded-pill mx-auto">
                    <?php echo htmlspecialchars($t['specialization']); ?>
                </span>

                <p class="text-muted small mb-3 text-start bg-light p-2 rounded-3 border">
                    <i class="fa-solid fa-graduation-cap text-warning me-1"></i>
                    <?php echo htmlspecialchars($t['degree']); ?>
                </p>

                <div class="text-start small text-secondary mb-3">
                    <div class="mb-1">
                        <i class="fa-solid fa-phone me-2 text-primary"></i><?php echo htmlspecialchars($t['phone']); ?>
                    </div>
                    <div class="text-truncate" title="<?php echo htmlspecialchars($t['email']); ?>">
                        <i class="fa-solid fa-envelope me-2 text-info"></i><?php echo htmlspecialchars($t['email']); ?>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-auto">
                    <span class="text-warning fw-bold small">
                        <i class="fa-solid fa-star me-1"></i><?php echo htmlspecialchars((string)$t['rating']); ?>/5.0
                    </span>
                    <div class="d-flex gap-1">
                        <button type="button"
                                class="btn btn-sm btn-outline-primary rounded-pill px-2 btn-edit-teacher"
                                title="Chỉnh sửa hồ sơ"
                                data-bs-toggle="modal"
                                data-bs-target="#editTeacherModal"
                                data-id="<?php echo (int)$t['teacher_id']; ?>"
                                data-name="<?php echo htmlspecialchars($t['full_name']); ?>"
                                data-spec="<?php echo htmlspecialchars($t['specialization']); ?>"
                                data-degree="<?php echo htmlspecialchars($t['degree']); ?>"
                                data-phone="<?php echo htmlspecialchars($t['phone']); ?>"
                                data-email="<?php echo htmlspecialchars($t['email']); ?>"
                                data-rating="<?php echo htmlspecialchars((string)$t['rating']); ?>">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        <button type="button"
                                class="btn btn-sm btn-outline-danger rounded-pill px-2 btn-delete-teacher"
                                title="Xóa giảng viên"
                                data-bs-toggle="modal"
                                data-bs-target="#deleteTeacherModal"
                                data-id="<?php echo (int)$t['teacher_id']; ?>"
                                data-name="<?php echo htmlspecialchars($t['full_name']); ?>">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- MODAL 1: THÊM GIẢNG VIÊN / GIA SƯ MỚI -->
<div class="modal fade" id="addTeacherModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form action="teachers.php" method="POST">
                <input type="hidden" name="action" value="add_teacher">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark">
                        <i class="fa-solid fa-user-plus text-success me-2"></i>Thêm Giảng Viên / Gia Sư Mới
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Họ và tên giảng viên <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" class="form-control" placeholder="VD: Thầy Nguyễn Minh Tuấn" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Chức danh / Chuyên môn phụ trách <span class="text-danger">*</span></label>
                        <input type="text" name="specialization" class="form-control" placeholder="VD: Chuyên gia Luyện thi IELTS 8.5+" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Bằng cấp &amp; Chứng chỉ quốc tế <span class="text-danger">*</span></label>
                        <input type="text" name="degree" class="form-control" placeholder="VD: IELTS 8.5 Overall, Thạc sĩ TESOL" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold small">Số điện thoại <span class="text-danger">*</span></label>
                            <input type="tel" name="phone" class="form-control" placeholder="0988 123 456" required>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold small">Điểm đánh giá (Rating)</label>
                            <input type="number" name="rating" class="form-control" step="0.1" min="4.0" max="5.0" value="4.9">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold small">Email công vụ</label>
                        <input type="email" name="email" class="form-control" placeholder="minhtuan.ielts@educenter.edu.vn">
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-semibold">
                        <i class="fa-solid fa-check me-1"></i>Lưu Hồ Sơ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL 2: CHỈNH SỬA HỒ SƠ GIẢNG VIÊN -->
<div class="modal fade" id="editTeacherModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form action="teachers.php" method="POST">
                <input type="hidden" name="action" value="edit_teacher">
                <input type="hidden" name="teacher_id" id="edit_teacher_id">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark">
                        <i class="fa-solid fa-pen-to-square text-primary me-2"></i>Cập Nhật Hồ Sơ Giảng Viên
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Họ và tên giảng viên <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" id="edit_full_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Chức danh / Chuyên môn phụ trách <span class="text-danger">*</span></label>
                        <input type="text" name="specialization" id="edit_specialization" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Bằng cấp &amp; Chứng chỉ quốc tế <span class="text-danger">*</span></label>
                        <input type="text" name="degree" id="edit_degree" class="form-control" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold small">Số điện thoại <span class="text-danger">*</span></label>
                            <input type="tel" name="phone" id="edit_phone" class="form-control" required>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold small">Điểm đánh giá (Rating)</label>
                            <input type="number" name="rating" id="edit_rating" class="form-control" step="0.1" min="4.0" max="5.0">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold small">Email công vụ</label>
                        <input type="email" name="email" id="edit_email" class="form-control">
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">
                        <i class="fa-solid fa-floppy-disk me-1"></i>Cập Nhật
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL 3: XÁC NHẬN XÓA GIẢNG VIÊN -->
<div class="modal fade" id="deleteTeacherModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow rounded-4">
            <form action="teachers.php" method="POST">
                <input type="hidden" name="action" value="delete_teacher">
                <input type="hidden" name="teacher_id" id="delete_teacher_id">
                <div class="modal-body text-center p-4">
                    <div class="bg-danger-subtle text-danger rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width:60px;height:60px;">
                        <i class="fa-solid fa-triangle-exclamation fs-3"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Xác nhận xóa hồ sơ?</h5>
                    <p class="text-muted small mb-4">
                        Bạn có chắc chắn muốn xóa hồ sơ của <strong id="delete_teacher_name" class="text-dark"></strong> khỏi hệ thống?
                    </p>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Hủy bỏ</button>
                        <button type="submit" class="btn btn-danger rounded-pill px-3 fw-semibold">Xóa ngay</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var editButtons = document.querySelectorAll('.btn-edit-teacher');
    editButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('edit_teacher_id').value     = this.getAttribute('data-id');
            document.getElementById('edit_full_name').value      = this.getAttribute('data-name');
            document.getElementById('edit_specialization').value = this.getAttribute('data-spec');
            document.getElementById('edit_degree').value         = this.getAttribute('data-degree');
            document.getElementById('edit_phone').value          = this.getAttribute('data-phone');
            document.getElementById('edit_email').value          = this.getAttribute('data-email');
            document.getElementById('edit_rating').value         = this.getAttribute('data-rating');
        });
    });

    var deleteButtons = document.querySelectorAll('.btn-delete-teacher');
    deleteButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('delete_teacher_id').value          = this.getAttribute('data-id');
            document.getElementById('delete_teacher_name').textContent  = this.getAttribute('data-name');
        });
    });
});
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
