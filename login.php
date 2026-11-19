<?php
/**
 * EDUCENTER - HỆ THỐNG WEBSITE QUẢN LÝ TRUNG TÂM NGOẠI NGỮ & GIA SƯ
 * File: login.php
 * Phụ trách (Bổ sung Gói A1 - Nguyễn Anh Khoa - 52400051):
 * Trang Đăng nhập Hệ thống (Hỗ trợ xác thực bcrypt, Phân quyền Admin/Giảng viên/Học viên)
 */

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/config/db.php';

$pageTitle = 'Đăng Nhập Hệ Thống - EduCenter';
$currentPage = 'login.php';

$redirectTarget = isset($_GET['redirect']) ? trim($_GET['redirect']) : '';

// Nếu đã đăng nhập, chuyển hướng về trang tương ứng (trừ khi đang yêu cầu đăng nhập quyền Admin)
if (getCurrentUser() !== null && !isset($_GET['switch']) && !isset($_GET['auth_required'])) {
    $u = getCurrentUser();
    if ($redirectTarget !== '') {
        if (strpos($redirectTarget, 'admin/') === 0 && ($u['role'] ?? '') !== 'admin') {
            // Không phải tài khoản Admin thì không tự chuyển vào admin/
        } else {
            header('Location: ' . $redirectTarget);
            exit;
        }
    } else {
        if (($u['role'] ?? '') === 'admin') {
            header('Location: admin/index.php');
        } else {
            header('Location: index.php');
        }
        exit;
    }
}

$identifier = '';
$loginError = '';
$infoBanner = '';

if (isset($_GET['logged_out'])) {
    $infoBanner = 'Bạn đã đăng xuất khỏi hệ thống an toàn.';
}
if (isset($_GET['reset_success'])) {
    $infoBanner = 'Đặt lại mật khẩu thành công! Vui lòng đăng nhập lại bằng mật khẩu mới.';
}
if (isset($_GET['auth_required'])) {
    $infoBanner = 'Khu vực Quản trị Hệ thống yêu cầu đăng nhập bằng Tài khoản Quản trị viên (Admin: admin@educenter.edu.vn / 123456).';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier     = trim($_POST['identifier'] ?? '');
    $password       = $_POST['password'] ?? '';
    $redirectTarget = trim($_POST['redirect'] ?? '');

    if ($identifier === '' || $password === '') {
        $loginError = 'Vui lòng nhập đầy đủ Email/Tên đăng nhập và Mật khẩu.';
    } else {
        $matchedUser = findEduUserByIdentifier($identifier);

        // Kiểm tra thêm trong bảng Users của MySQL nếu đang kết nối
        if ($matchedUser === null && $db_connected && $pdo) {
            try {
                $stmt = $pdo->prepare("SELECT * FROM Users WHERE username = :u LIMIT 1");
                $stmt->execute([':u' => $identifier]);
                $dbRow = $stmt->fetch();
                if ($dbRow) {
                    $matchedUser = [
                        'user_id'       => $dbRow['user_id'],
                        'username'      => $dbRow['username'],
                        'email'         => $dbRow['username'] . '@educenter.edu.vn',
                        'display_name'  => ucfirst($dbRow['username']),
                        'password_hash' => $dbRow['password'],
                        'role'          => $dbRow['role'],
                        'avatar'        => 'https://ui-avatars.com/api/?name=' . urlencode($dbRow['username']) . '&background=1E40AF&color=fff',
                        'is_activated'  => 1,
                        'theme'         => 'light',
                        'font_size'     => 'medium'
                    ];
                }
            } catch (Exception $e) {
                // Fallback sang danh sách tài khoản trong Session
            }
        }

        if ($matchedUser !== null && verifyEduPassword($password, $matchedUser['password_hash'])) {
            // Nếu người dùng đang cố truy cập trang Quản trị (admin/*) nhưng đăng nhập tài khoản không phải Admin
            if (strpos($redirectTarget, 'admin/') === 0 && ($matchedUser['role'] ?? '') !== 'admin') {
                $loginError = 'Tài khoản này không có quyền Quản trị viên! Vui lòng đăng nhập bằng tài khoản Quản trị viên (admin@educenter.edu.vn) để vào Web Quản trị.';
            } else {
                $_SESSION['edu_user'] = $matchedUser;

                if ($redirectTarget !== '') {
                    header('Location: ' . $redirectTarget);
                    exit;
                }
                if (($matchedUser['role'] ?? '') === 'admin') {
                    header('Location: admin/index.php');
                } else {
                    header('Location: index.php');
                }
                exit;
            }
        } else {
            $loginError = 'Email/Tên đăng nhập hoặc mật khẩu không chính xác. Vui lòng kiểm tra lại.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="edu-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-8">
                <div class="edu-form-card">
                    <div class="text-center mb-4">
                        <span class="edu-brand-icon mb-2" style="width:52px;height:52px;font-size:1.4rem;">
                            <i class="fa-solid fa-user-lock"></i>
                        </span>
                        <h3 class="fw-bold text-dark mb-1">Đăng Nhập Hệ Thống</h3>
                        <p class="text-muted small mb-0">
                            Truy cập cổng Học viên, Giảng viên &amp; Ban Quản Trị EduCenter
                        </p>
                    </div>

                    <?php if ($infoBanner !== ''): ?>
                        <div class="alert alert-info border-0 shadow-sm rounded-3 small mb-3">
                            <i class="fa-solid fa-circle-info me-1"></i> <?php echo htmlspecialchars($infoBanner); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($loginError !== ''): ?>
                        <div class="alert alert-danger border-0 shadow-sm rounded-3 small mb-3">
                            <i class="fa-solid fa-triangle-exclamation me-1"></i> <?php echo htmlspecialchars($loginError); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="login.php" id="eduLoginForm" novalidate>
                        <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirectTarget); ?>">

                        <div class="mb-3">
                            <label for="loginIdentifier" class="form-label fw-semibold small">
                                Email hoặc Tên đăng nhập <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted">
                                    <i class="fa-solid fa-envelope"></i>
                                </span>
                                <input type="text"
                                       class="form-control edu-input border-start-0"
                                       id="loginIdentifier"
                                       name="identifier"
                                       placeholder="admin@educenter.edu.vn hoặc admin"
                                       value="<?php echo htmlspecialchars($identifier); ?>"
                                       required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="loginPassword" class="form-label fw-semibold small mb-0">
                                    Mật khẩu <span class="text-danger">*</span>
                                </label>
                                <a href="forgot_password.php" class="small text-primary fw-semibold">Quên mật khẩu?</a>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted">
                                    <i class="fa-solid fa-lock"></i>
                                </span>
                                <input type="password"
                                       class="form-control edu-input border-start-0"
                                       id="loginPassword"
                                       name="password"
                                       placeholder="Nhập mật khẩu của bạn"
                                       required>
                            </div>
                        </div>

                        <div class="d-grid mb-3 pt-1">
                            <button type="submit" class="btn btn-primary py-2 fw-bold rounded-3 shadow-sm">
                                <i class="fa-solid fa-right-to-bracket me-2"></i>Đăng Nhập Ngay
                            </button>
                        </div>
                    </form>

                    <!-- Hộp chọn nhanh tài khoản mẫu phục vụ Giảng viên chấm điểm -->
                    <div class="p-3 rounded-3 bg-light border mb-3">
                        <div class="small fw-bold text-secondary mb-2">
                            <i class="fa-solid fa-bolt text-warning me-1"></i> Bấm để điền nhanh tài khoản chấm điểm (Mật khẩu: <code>123456</code>):
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button"
                                    class="btn btn-sm btn-outline-primary rounded-pill quick-login-fill"
                                    data-user="admin@educenter.edu.vn"
                                    data-pass="123456">
                                <i class="fa-solid fa-user-shield me-1"></i>Admin
                            </button>
                            <button type="button"
                                    class="btn btn-sm btn-outline-success rounded-pill quick-login-fill"
                                    data-user="thaomy.ielts@educenter.edu.vn"
                                    data-pass="123456">
                                <i class="fa-solid fa-chalkboard-user me-1"></i>Giảng viên
                            </button>
                            <button type="button"
                                    class="btn btn-sm btn-outline-dark rounded-pill quick-login-fill"
                                    data-user="nguyenvanan@gmail.com"
                                    data-pass="123456">
                                <i class="fa-solid fa-user-graduate me-1"></i>Học viên (HV001)
                            </button>
                        </div>
                    </div>

                    <div class="text-center small text-muted">
                        Chưa có tài khoản?
                        <a href="signup.php" class="fw-bold text-primary">Đăng ký tài khoản mới</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.quick-login-fill').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('loginIdentifier').value = this.getAttribute('data-user');
            document.getElementById('loginPassword').value = this.getAttribute('data-pass');
        });
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
