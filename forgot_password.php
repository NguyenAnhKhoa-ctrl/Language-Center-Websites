<?php
/**
 * EDUCENTER - HỆ THỐNG WEBSITE QUẢN LÝ TRUNG TÂM NGOẠI NGỮ & GIA SƯ
 * File: forgot_password.php
 * Phụ trách (Bổ sung Gói B1 - Lý Hạo Danh - 52400175):
 * Khôi phục mật khẩu qua Email (Hỗ trợ cả nhập mã OTP 6 số và bấm Link trong Email)
 * Bắt buộc người dùng đăng nhập lại thủ công sau khi đặt mật khẩu mới
 */

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/config/db.php';

$pageTitle   = 'Khôi Phục Mật Khẩu Qua Email / OTP - EduCenter';
$currentPage = 'forgot_password.php';

$step        = isset($_GET['step']) ? trim($_GET['step']) : 'request';
$errorMsg    = '';
$infoMsg     = '';

// Hỗ trợ bấm trực tiếp vào liên kết trong Email (?step=verify&token=...)
if ($step === 'verify' && isset($_GET['otp']) && isset($_SESSION['password_reset_data'])) {
    $urlOtp = trim($_GET['otp']);
    if ($urlOtp === (string)$_SESSION['password_reset_data']['otp']) {
        $_SESSION['password_reset_data']['verified'] = true;
        $step = 'reset';
        $infoMsg = 'Đã xác thực liên kết khôi phục mật khẩu từ Email thành công! Vui lòng đặt mật khẩu mới.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // BƯỚC 1: NHẬP EMAIL ĐĂNG KÝ ĐỂ NHẬN MÃ OTP & LINK KHÔI PHỤC
    if ($action === 'send_reset_email') {
        $email = mb_strtolower(trim($_POST['email'] ?? ''), 'UTF-8');

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMsg = 'Vui lòng nhập địa chỉ Email hợp lệ.';
            $step     = 'request';
        } else {
            $matchedUser = findEduUserByIdentifier($email);

            if ($matchedUser === null) {
                $errorMsg = 'Không tìm thấy tài khoản nào đăng ký với địa chỉ Email này.';
                $step     = 'request';
            } else {
                $otpCode = (string)random_int(100000, 999999);
                $_SESSION['password_reset_data'] = [
                    'email'        => $matchedUser['email'],
                    'display_name' => $matchedUser['display_name'],
                    'otp'          => $otpCode,
                    'verified'     => false,
                    'expires_at'   => time() + 600
                ];
                $step = 'verify';
            }
        }
    }

    // BƯỚC 2: XÁC THỰC MÃ OTP 6 SỐ GỬI VỀ EMAIL
    elseif ($action === 'verify_otp') {
        $inputOtp = trim($_POST['otp_code'] ?? '');
        $resetCtx = $_SESSION['password_reset_data'] ?? null;

        if ($resetCtx === null) {
            $errorMsg = 'Phiên khôi phục mật khẩu đã hết hạn. Vui lòng nhập lại Email.';
            $step     = 'request';
        } elseif ($inputOtp === '' || $inputOtp !== (string)$resetCtx['otp']) {
            $errorMsg = 'Mã OTP không chính xác! Vui lòng kiểm tra mã OTP 6 số trong hộp thư.';
            $step     = 'verify';
        } else {
            $_SESSION['password_reset_data']['verified'] = true;
            $step    = 'reset';
            $infoMsg = 'Xác thực mã OTP thành công! Vui lòng nhập mật khẩu mới (2 lần xác nhận).';
        }
    }

    // BƯỚC 3: ĐẶT MẬT KHẨU MỚI & CHUYỂN VỀ TRANG ĐĂNG NHẬP THỦ CÔNG
    elseif ($action === 'set_new_password') {
        $newPassword     = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $resetCtx        = $_SESSION['password_reset_data'] ?? null;

        if ($resetCtx === null || empty($resetCtx['verified'])) {
            $errorMsg = 'Vui lòng xác thực mã OTP hoặc liên kết Email trước khi đặt mật khẩu mới.';
            $step     = 'request';
        } elseif (strlen($newPassword) < 6) {
            $errorMsg = 'Mật khẩu mới phải có tối thiểu 6 ký tự.';
            $step     = 'reset';
        } elseif ($newPassword !== $confirmPassword) {
            $errorMsg = 'Mật khẩu xác nhận lần 2 không khớp.';
            $step     = 'reset';
        } else {
            $targetEmail = $resetCtx['email'];
            $newHash     = password_hash($newPassword, PASSWORD_BCRYPT);

            if (isset($_SESSION['registered_users'][$targetEmail])) {
                $_SESSION['registered_users'][$targetEmail]['password_hash'] = $newHash;
            }

            if ($db_connected && $pdo) {
                try {
                    $stmt = $pdo->prepare("UPDATE Users SET password = :pw WHERE email = :em OR username = :un");
                    $stmt->execute([
                        ':pw' => $newHash,
                        ':em' => $targetEmail,
                        ':un' => explode('@', $targetEmail)[0]
                    ]);
                } catch (Exception $e) {
                    // Fallback Session
                }
            }

            // Hủy dữ liệu OTP và bắt buộc người dùng đăng nhập lại thủ công
            unset($_SESSION['password_reset_data']);
            unset($_SESSION['edu_user']);

            header('Location: login.php?reset_success=1');
            exit;
        }
    }
}

$resetData = $_SESSION['password_reset_data'] ?? null;

require_once __DIR__ . '/includes/header.php';
?>

<section class="edu-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="edu-form-card">
                    <div class="text-center mb-4">
                        <span class="edu-brand-icon mb-2" style="width:52px;height:52px;font-size:1.4rem;">
                            <i class="fa-solid fa-envelope-open-text"></i>
                        </span>
                        <h3 class="fw-bold text-dark mb-1">Khôi Phục Mật Khẩu</h3>
                        <p class="text-muted small mb-0">
                            Nhận mã xác thực OTP 6 số hoặc liên kết đặt lại mật khẩu qua Email đăng ký
                        </p>
                    </div>

                    <?php if ($errorMsg !== ''): ?>
                        <div class="alert alert-danger border-0 shadow-sm rounded-3 small mb-3">
                            <i class="fa-solid fa-triangle-exclamation me-1"></i><?php echo htmlspecialchars($errorMsg); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($infoMsg !== ''): ?>
                        <div class="alert alert-success border-0 shadow-sm rounded-3 small mb-3">
                            <i class="fa-solid fa-circle-check me-1"></i><?php echo htmlspecialchars($infoMsg); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($step === 'verify' && $resetData !== null): ?>
                        <!-- HỘP MÔ PHỎNG EMAIL GỬI TỚI NGƯỜI DÙNG (HỖ TRỢ CẢ LINK & MÃ OTP) -->
                        <div class="p-3 rounded-4 bg-warning-subtle border border-warning mb-4 small">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <strong class="text-dark">
                                    <i class="fa-solid fa-envelope-circle-check text-primary me-1"></i>Hộp thư đến mô phỏng (Email Sent)
                                </strong>
                                <span class="badge bg-primary">OTP &amp; Link</span>
                            </div>
                            <p class="mb-2 text-dark">
                                Hệ thống đã gửi email khôi phục tới <strong><?php echo htmlspecialchars($resetData['email']); ?></strong>:
                            </p>
                            <div class="p-2 bg-white rounded-3 border d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                <span>Mã xác thực OTP của bạn: <strong class="fs-5 text-danger ms-1"><?php echo htmlspecialchars($resetData['otp']); ?></strong></span>
                                <a href="forgot_password.php?step=verify&amp;otp=<?php echo urlencode($resetData['otp']); ?>"
                                   class="btn btn-sm btn-primary rounded-pill fw-semibold">
                                    <i class="fa-solid fa-link me-1"></i>Bấm vào Link trong Email
                                </a>
                            </div>
                            <small class="text-muted">Bạn có thể nhập mã OTP 6 số vào ô bên dưới hoặc bấm trực tiếp vào nút Link Email ở trên.</small>
                        </div>

                        <form action="forgot_password.php?step=verify" method="POST">
                            <input type="hidden" name="action" value="verify_otp">
                            <div class="mb-3">
                                <label for="otpCodeInput" class="form-label fw-semibold small">
                                    Nhập mã xác thực OTP (6 chữ số) <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       id="otpCodeInput"
                                       name="otp_code"
                                       class="form-control edu-input text-center fw-bold fs-5"
                                       placeholder="------"
                                       maxlength="6"
                                       value="<?php echo htmlspecialchars($resetData['otp']); ?>"
                                       required>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary py-2 fw-bold rounded-3 shadow-sm">
                                    <i class="fa-solid fa-shield-check me-2"></i>Xác Thực Mã OTP
                                </button>
                                <a href="forgot_password.php" class="btn btn-light btn-sm text-secondary">Nhập lại địa chỉ Email khác</a>
                            </div>
                        </form>

                    <?php elseif ($step === 'reset' && $resetData !== null): ?>
                        <!-- BƯỚC 3: NHẬP MẬT KHẨU MỚI 2 LẦN -->
                        <form action="forgot_password.php?step=reset" method="POST">
                            <input type="hidden" name="action" value="set_new_password">
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Tài khoản khôi phục</label>
                                <input type="text" class="form-control edu-input bg-light" value="<?php echo htmlspecialchars($resetData['email']); ?>" readonly>
                            </div>
                            <div class="mb-3">
                                <label for="resetNewPass" class="form-label fw-semibold small">
                                    Mật khẩu mới <span class="text-danger">*</span>
                                </label>
                                <input type="password"
                                       id="resetNewPass"
                                       name="new_password"
                                       class="form-control edu-input"
                                       placeholder="Nhập mật khẩu mới (tối thiểu 6 ký tự)"
                                       required>
                            </div>
                            <div class="mb-4">
                                <label for="resetConfirmPass" class="form-label fw-semibold small">
                                    Xác nhận mật khẩu mới (Nhập lần 2) <span class="text-danger">*</span>
                                </label>
                                <input type="password"
                                       id="resetConfirmPass"
                                       name="confirm_password"
                                       class="form-control edu-input"
                                       placeholder="Nhập lại mật khẩu mới"
                                       required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-success py-2 fw-bold rounded-3 shadow-sm">
                                    <i class="fa-solid fa-check-double me-2"></i>Hoàn Tất Đặt Lại Mật Khẩu
                                </button>
                            </div>
                        </form>

                    <?php else: ?>
                        <!-- BƯỚC 1: NHẬP ĐỊA CHỈ EMAIL ĐĂNG KÝ -->
                        <form action="forgot_password.php" method="POST">
                            <input type="hidden" name="action" value="send_reset_email">
                            <div class="mb-3">
                                <label for="resetEmailInput" class="form-label fw-semibold small">
                                    Địa chỉ Email đã đăng ký <span class="text-danger">*</span>
                                </label>
                                <input type="email"
                                       id="resetEmailInput"
                                       name="email"
                                       class="form-control edu-input"
                                       placeholder="VD: nguyenvanan@gmail.com"
                                       required>
                            </div>

                            <div class="p-3 rounded-3 bg-light border mb-3 small">
                                <div class="text-muted mb-2">
                                    <i class="fa-solid fa-lightbulb text-warning me-1"></i>Bấm để chọn nhanh Email tài khoản mẫu:
                                </div>
                                <div class="d-flex flex-wrap gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill py-0 px-2 fill-reset-email" data-email="nguyenvanan@gmail.com">
                                        nguyenvanan@gmail.com
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-success rounded-pill py-0 px-2 fill-reset-email" data-email="thaomy.ielts@educenter.edu.vn">
                                        thaomy.ielts@educenter.edu.vn
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-dark rounded-pill py-0 px-2 fill-reset-email" data-email="admin@educenter.edu.vn">
                                        admin@educenter.edu.vn
                                    </button>
                                </div>
                            </div>

                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary py-2 fw-bold rounded-3 shadow-sm">
                                    <i class="fa-solid fa-paper-plane me-2"></i>Gửi Mã OTP &amp; Liên Kết Khôi Phục
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>

                    <div class="text-center small text-muted border-top pt-3 mt-3">
                        Đã nhớ mật khẩu?
                        <a href="login.php" class="fw-bold text-primary">Quay lại trang Đăng nhập</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.fill-reset-email').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = document.getElementById('resetEmailInput');
            if (input) {
                input.value = this.getAttribute('data-email');
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
