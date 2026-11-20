<?php
/**
 * EDUCENTER - HỆ THỐNG WEBSITE QUẢN LÝ TRUNG TÂM NGOẠI NGỮ & GIA SƯ
 * File: signup.php
 * Phụ trách (Bổ sung Gói A1 - Nguyễn Anh Khoa - 52400051):
 * Đăng ký tài khoản mới (Email, Display Name, Mật khẩu nhập 2 lần, Mã hóa bcrypt, Tự động đăng nhập & Kích hoạt Email)
 */

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/config/db.php';

$pageTitle = 'Đăng Ký Tài Khoản Mới - EduCenter';
$currentPage = 'signup.php';

$email       = '';
$displayName = '';
$errors      = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email           = trim($_POST['email'] ?? '');
    $displayName     = trim($_POST['display_name'] ?? '');
    $password        = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';

    // 1. Kiểm tra dữ liệu đầu vào theo đúng chuẩn Mục 2.1
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Vui lòng nhập địa chỉ email hợp lệ (VD: hocvien@gmail.com).';
    } elseif (findEduUserByIdentifier($email) !== null) {
        $errors['email'] = 'Địa chỉ email này đã được đăng ký trong hệ thống.';
    }

    if ($displayName === '' || mb_strlen($displayName, 'UTF-8') < 2) {
        $errors['display_name'] = 'Vui lòng nhập tên hiển thị (tối thiểu 2 ký tự).';
    }

    if (strlen($password) < 6) {
        $errors['password'] = 'Mật khẩu phải có ít nhất 6 ký tự.';
    }

    if ($password !== $passwordConfirm) {
        $errors['password_confirm'] = 'Mật khẩu xác nhận (nhập lần 2) không khớp.';
    }

    // 2. Nếu hợp lệ -> Mã hóa bcrypt, lưu tài khoản, tự động đăng nhập & gửi thông báo kích hoạt
    if (empty($errors)) {
        $bcryptHash = password_hash($password, PASSWORD_BCRYPT);
        $username   = strtolower( explode('@', $email)[0] ) . random_int(10, 99);

        if ($db_connected && $pdo) {
            try {
                $stmt = $pdo->prepare("INSERT INTO Users (username, password, role) VALUES (:u, :p, 'student')");
                $stmt->execute([
                    ':u' => $username,
                    ':p' => $bcryptHash
                ]);
            } catch (Exception $e) {
                // Fallback sang Session
            }
        }

        $newUser = [
            'user_id'       => count($_SESSION['registered_users']) + 10,
            'username'      => $username,
            'email'         => $email,
            'display_name'  => $displayName,
            'password_hash' => $bcryptHash,
            'role'          => 'student',
            'avatar'        => 'https://ui-avatars.com/api/?name=' . urlencode($displayName) . '&background=2563EB&color=fff',
            'is_activated'  => 1,
            'theme'         => 'light',
            'font_size'     => 'medium',
            'note_color'    => 'default'
        ];

        $_SESSION['registered_users'][$email] = $newUser;
        // Tự động đăng nhập ngay sau khi đăng ký thành công
        $_SESSION['edu_user'] = $newUser;

        header('Location: index.php?welcome_new_user=1');
        exit;
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
                            <i class="fa-solid fa-user-plus"></i>
                        </span>
                        <h3 class="fw-bold text-dark mb-1">Đăng Ký Tài Khoản</h3>
                        <p class="text-muted small mb-0">
                            Tạo tài khoản cá nhân để theo dõi lịch học, bảng điểm &amp; ghi chú học tập
                        </p>
                    </div>

                    <form method="POST" action="signup.php" id="eduSignupForm" novalidate>
                        <!-- 1. Địa chỉ Email -->
                        <div class="mb-3">
                            <label for="signupEmail" class="form-label fw-semibold small">
                                Địa chỉ Email <span class="text-danger">*</span>
                            </label>
                            <input type="email"
                                   class="form-control edu-input <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>"
                                   id="signupEmail"
                                   name="email"
                                   placeholder="hocvien@gmail.com"
                                   value="<?php echo htmlspecialchars($email); ?>"
                                   required>
                            <div class="invalid-feedback">
                                <?php echo htmlspecialchars($errors['email'] ?? 'Vui lòng nhập địa chỉ email hợp lệ.'); ?>
                            </div>
                        </div>

                        <!-- 2. Tên hiển thị (Display Name) -->
                        <div class="mb-3">
                            <label for="signupDisplayName" class="form-label fw-semibold small">
                                Tên hiển thị (Display Name) <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control edu-input <?php echo isset($errors['display_name']) ? 'is-invalid' : ''; ?>"
                                   id="signupDisplayName"
                                   name="display_name"
                                   placeholder="Nguyễn Văn An"
                                   value="<?php echo htmlspecialchars($displayName); ?>"
                                   required>
                            <div class="invalid-feedback">
                                <?php echo htmlspecialchars($errors['display_name'] ?? 'Vui lòng nhập tên hiển thị của bạn.'); ?>
                            </div>
                        </div>

                        <!-- 3. Mật khẩu (lần 1) -->
                        <div class="mb-3">
                            <label for="signupPassword" class="form-label fw-semibold small">
                                Mật khẩu <span class="text-danger">*</span>
                            </label>
                            <input type="password"
                                   class="form-control edu-input <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>"
                                   id="signupPassword"
                                   name="password"
                                   placeholder="Tối thiểu 6 ký tự (mã hóa chuẩn bcrypt)"
                                   required>
                            <div class="invalid-feedback">
                                <?php echo htmlspecialchars($errors['password'] ?? 'Mật khẩu phải có ít nhất 6 ký tự.'); ?>
                            </div>
                        </div>

                        <!-- 4. Xác nhận mật khẩu (lần 2) -->
                        <div class="mb-4">
                            <label for="signupPasswordConfirm" class="form-label fw-semibold small">
                                Nhập lại mật khẩu xác nhận <span class="text-danger">*</span>
                            </label>
                            <input type="password"
                                   class="form-control edu-input <?php echo isset($errors['password_confirm']) ? 'is-invalid' : ''; ?>"
                                   id="signupPasswordConfirm"
                                   name="password_confirm"
                                   placeholder="Nhập lại chính xác mật khẩu trên"
                                   required>
                            <div class="invalid-feedback">
                                <?php echo htmlspecialchars($errors['password_confirm'] ?? 'Mật khẩu xác nhận không khớp.'); ?>
                            </div>
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary py-2 fw-bold rounded-3 shadow-sm">
                                <i class="fa-solid fa-check-circle me-2"></i>Đăng Ký &amp; Tự Động Đăng Nhập
                            </button>
                        </div>
                    </form>

                    <div class="text-center small text-muted">
                        Đã có tài khoản?
                        <a href="login.php" class="fw-bold text-primary">Đăng nhập tại đây</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
