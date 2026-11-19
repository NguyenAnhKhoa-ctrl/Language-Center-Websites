<?php
/**
 * EDUCENTER - HỆ THỐNG WEBSITE QUẢN LÝ TRUNG TÂM NGOẠI NGỮ & GIA SƯ
 * File: profile.php
 * Phụ trách (Bổ sung Gói B1 - Lý Hạo Danh - 52400175):
 * Quản lý Hồ sơ cá nhân, Tải lên & Kiểm tra Avatar, Đổi mật khẩu bảo mật & Tùy chỉnh giao diện (User Preferences)
 */

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/config/db.php';

// Bắt buộc đăng nhập trước khi truy cập trang Hồ sơ cá nhân
if (getCurrentUser() === null) {
    header('Location: login.php?auth_required=1&redirect=profile.php');
    exit;
}

$pageTitle   = 'Hồ Sơ Cá Nhân & Tùy Chỉnh Tài Khoản - EduCenter';
$currentPage = 'profile.php';

$user      = getCurrentUser();
$userEmail = $user['email'];
$userRole  = $user['role'] ?? 'student';

$flashSuccess = '';
$flashError   = '';
$activeTab    = isset($_GET['tab']) ? trim($_GET['tab']) : 'profile';

// ============================================================================
// XỬ LÝ FORM CẬP NHẬT HỒ SƠ, AVATAR, ĐỔI MẬT KHẨU & TÙY CHỈNH GIAO DIỆN
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // 1. CẬP NHẬT TÊN HIỂN THỊ & ẢNH ĐẠI DIỆN (AVATAR UPLOAD & VALIDATION)
    if ($action === 'update_profile') {
        $activeTab   = 'profile';
        $displayName = trim($_POST['display_name'] ?? '');
        $resetAvatar = isset($_POST['reset_default_avatar']) && $_POST['reset_default_avatar'] === '1';

        if ($displayName === '' || mb_strlen($displayName, 'UTF-8') < 2) {
            $flashError = 'Tên hiển thị phải có tối thiểu 2 ký tự.';
        } else {
            $newAvatar = $user['avatar'] ?? getDefaultAvatarUrl($displayName, $userRole);

            if ($resetAvatar) {
                $newAvatar = getDefaultAvatarUrl($displayName, $userRole);
            } elseif (isset($_FILES['avatar_file']) && $_FILES['avatar_file']['error'] !== UPLOAD_ERR_NO_FILE) {
                $file = $_FILES['avatar_file'];

                if ($file['error'] !== UPLOAD_ERR_OK) {
                    $flashError = 'Có lỗi xảy ra khi tải lên tệp ảnh đại diện. Vui lòng thử lại.';
                } elseif ($file['size'] > 2 * 1024 * 1024) {
                    $flashError = 'Dung lượng ảnh đại diện vượt quá giới hạn cho phép (Tối đa 2MB).';
                } else {
                    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
                    $finfo        = finfo_open(FILEINFO_MIME_TYPE);
                    $detectedMime = finfo_file($finfo, $file['tmp_name']);
                    finfo_close($finfo);

                    if (!in_array($detectedMime, $allowedMimes, true)) {
                        $flashError = 'Định dạng tệp không hợp lệ! Hệ thống chỉ chấp nhận ảnh JPG, PNG, WEBP hoặc GIF.';
                    } else {
                        $rawBytes  = file_get_contents($file['tmp_name']);
                        $newAvatar = 'data:' . $detectedMime . ';base64,' . base64_encode($rawBytes);
                    }
                }
            }

            if ($flashError === '') {
                $_SESSION['edu_user']['display_name'] = $displayName;
                $_SESSION['edu_user']['avatar']       = $newAvatar;

                if (isset($_SESSION['registered_users'][$userEmail])) {
                    $_SESSION['registered_users'][$userEmail]['display_name'] = $displayName;
                    $_SESSION['registered_users'][$userEmail]['avatar']       = $newAvatar;
                }

                if ($db_connected && $pdo && !empty($user['user_id'])) {
                    try {
                        $stmt = $pdo->prepare("UPDATE Users SET display_name = :dn, avatar = :av WHERE user_id = :uid");
                        $stmt->execute([
                            ':dn'  => $displayName,
                            ':av'  => strlen($newAvatar) <= 255 ? $newAvatar : getDefaultAvatarUrl($displayName, $userRole),
                            ':uid' => (int)$user['user_id']
                        ]);
                    } catch (Exception $e) {
                        // Fallback Session
                    }
                }

                $user         = getCurrentUser();
                $flashSuccess = 'Đã cập nhật thông tin hồ sơ cá nhân và ảnh đại diện thành công!';
            }
        }
    }

    // 2. ĐỔI MẬT KHẨU (NHẬP MẬT KHẨU HIỆN TẠI + NHẬP 2 LẦN MẬT KHẨU MỚI)
    elseif ($action === 'change_password') {
        $activeTab       = 'password';
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword     = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
            $flashError = 'Vui lòng nhập đầy đủ mật khẩu hiện tại và mật khẩu mới (2 lần xác nhận).';
        } elseif (!verifyEduPassword($currentPassword, $user['password_hash'])) {
            $flashError = 'Mật khẩu hiện tại không chính xác! Vui lòng kiểm tra lại.';
        } elseif (strlen($newPassword) < 6) {
            $flashError = 'Mật khẩu mới phải có độ dài tối thiểu 6 ký tự.';
        } elseif ($newPassword !== $confirmPassword) {
            $flashError = 'Mật khẩu mới nhập lại lần 2 không trùng khớp.';
        } else {
            $newHash = password_hash($newPassword, PASSWORD_BCRYPT);

            $_SESSION['edu_user']['password_hash'] = $newHash;
            if (isset($_SESSION['registered_users'][$userEmail])) {
                $_SESSION['registered_users'][$userEmail]['password_hash'] = $newHash;
            }

            if ($db_connected && $pdo && !empty($user['user_id'])) {
                try {
                    $stmt = $pdo->prepare("UPDATE Users SET password = :pw WHERE user_id = :uid");
                    $stmt->execute([
                        ':pw'  => $newHash,
                        ':uid' => (int)$user['user_id']
                    ]);
                } catch (Exception $e) {
                    // Fallback Session
                }
            }

            // Làm mới Session ID để bảo vệ phiên xác thực sau khi đổi mật khẩu
            session_regenerate_id(true);
            $user         = getCurrentUser();
            $flashSuccess = 'Đã đổi mật khẩu thành công! Phiên đăng nhập của bạn đã được làm mới bảo mật.';
        }
    }

    // 3. CẬP NHẬT TÙY CHỈNH CÁ NHÂN (USER PREFERENCES: LIGHT/DARK THEME, FONT SIZE, CARD COLOR)
    elseif ($action === 'update_preferences') {
        $activeTab = 'preferences';
        $themePref = ($_POST['theme_pref'] ?? 'light') === 'dark' ? 'dark' : 'light';
        $fontSize  = in_array($_POST['font_size'] ?? 'medium', ['small', 'medium', 'large'], true) ? $_POST['font_size'] : 'medium';
        $noteColor = in_array($_POST['note_color'] ?? 'default', ['default', 'blue', 'amber', 'emerald'], true) ? $_POST['note_color'] : 'default';

        $_SESSION['edu_user']['theme']      = $themePref;
        $_SESSION['edu_user']['font_size']  = $fontSize;
        $_SESSION['edu_user']['note_color'] = $noteColor;

        if (isset($_SESSION['registered_users'][$userEmail])) {
            $_SESSION['registered_users'][$userEmail]['theme']      = $themePref;
            $_SESSION['registered_users'][$userEmail]['font_size']  = $fontSize;
            $_SESSION['registered_users'][$userEmail]['note_color'] = $noteColor;
        }

        if ($db_connected && $pdo && !empty($user['user_id'])) {
            try {
                $stmt = $pdo->prepare("UPDATE Users SET theme_pref = :tp, font_size = :fs WHERE user_id = :uid");
                $stmt->execute([
                    ':tp'  => $themePref,
                    ':fs'  => $fontSize,
                    ':uid' => (int)$user['user_id']
                ]);
            } catch (Exception $e) {
                // Fallback Session
            }
        }

        $user         = getCurrentUser();
        $flashSuccess = 'Đã lưu cài đặt giao diện cá nhân (Chế độ ' . ($themePref === 'dark' ? 'Tối - Dark Mode' : 'Sáng - Light Mode') . ', cỡ chữ ' . strtoupper($fontSize) . ') thành công!';
    }
}

$displayAvatar = !empty($user['avatar']) ? $user['avatar'] : getDefaultAvatarUrl($user['display_name'], $userRole);
$roleLabelMap  = [
    'admin'   => ['label' => 'Quản Trị Viên Hệ Thống', 'badge' => 'bg-danger'],
    'teacher' => ['label' => 'Giảng Viên & Gia Sư', 'badge' => 'bg-info text-dark'],
    'student' => ['label' => 'Học Viên Chính Thức', 'badge' => 'bg-success']
];
$roleMeta = $roleLabelMap[$userRole] ?? $roleLabelMap['student'];

require_once __DIR__ . '/includes/header.php';
?>

    <!-- 1. SUBPAGE HERO BANNER -->
    <section class="edu-page-banner">
        <div class="container text-center">
            <span class="edu-page-badge">QUẢN LÝ TÀI KHOẢN CÁ NHÂN</span>
            <h1 class="edu-page-title">Hồ Sơ Cá Nhân &amp; Tùy Chỉnh Giao Diện</h1>
            <p class="edu-page-subtitle">
                Quản lý thông tin hiển thị, ảnh đại diện, bảo mật mật khẩu và cá nhân hóa trải nghiệm học tập của bạn.
            </p>
        </div>
    </section>

    <!-- 2. NỘI DUNG QUẢN LÝ TÀI KHOẢN -->
    <section class="edu-section pt-5">
        <div class="container">
            <?php if ($flashSuccess !== ''): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4" role="alert">
                    <i class="fa-solid fa-circle-check me-2"></i><?php echo htmlspecialchars($flashSuccess); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if ($flashError !== ''): ?>
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4" role="alert">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i><?php echo htmlspecialchars($flashError); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                <!-- Cột trái: Thẻ tóm tắt Hồ sơ & Menu Tab -->
                <div class="col-lg-4">
                    <div class="edu-form-card text-center mb-4">
                        <div class="position-relative d-inline-block mb-3">
                            <img src="<?php echo htmlspecialchars($displayAvatar); ?>"
                                 alt="Avatar"
                                 id="profileAvatarPreview"
                                 class="rounded-circle shadow-sm border border-4 border-primary-subtle object-fit-cover"
                                 style="width: 115px; height: 115px;">
                            <span class="position-absolute bottom-0 end-0 bg-success border border-2 border-white rounded-circle p-2" title="Đang trực tuyến"></span>
                        </div>

                        <h4 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($user['display_name']); ?></h4>
                        <p class="text-muted small mb-2"><?php echo htmlspecialchars($user['email']); ?></p>
                        <span class="badge <?php echo htmlspecialchars($roleMeta['badge']); ?> rounded-pill px-3 py-2 mb-3">
                            <i class="fa-solid fa-user-check me-1"></i><?php echo htmlspecialchars($roleMeta['label']); ?>
                        </span>

                        <div class="p-3 rounded-3 bg-light border text-start small mt-2">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Trạng thái Email:</span>
                                <?php if (!empty($user['is_activated'])): ?>
                                    <span class="fw-bold text-success"><i class="fa-solid fa-circle-check me-1"></i>Đã kích hoạt</span>
                                <?php else: ?>
                                    <a href="profile.php?activate_account=1" class="fw-bold text-warning text-decoration-underline">Chưa kích hoạt (Bấm xác thực)</a>
                                <?php endif; ?>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Giao diện:</span>
                                <strong class="text-dark"><?php echo (($user['theme'] ?? 'light') === 'dark') ? 'Dark Mode (Tối)' : 'Light Mode (Sáng)'; ?></strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Cỡ chữ hiển thị:</span>
                                <strong class="text-primary text-uppercase"><?php echo htmlspecialchars($user['font_size'] ?? 'medium'); ?></strong>
                            </div>
                        </div>
                    </div>

                    <!-- Menu điều hướng 3 chức năng Account Management -->
                    <div class="list-group shadow-sm rounded-4 overflow-hidden" id="profileNavTabs" role="tablist">
                        <button type="button"
                                class="list-group-item list-group-item-action py-3 px-4 fw-semibold <?php echo ($activeTab === 'profile') ? 'active' : ''; ?>"
                                data-bs-toggle="list"
                                data-bs-target="#pane-profile"
                                role="tab">
                            <i class="fa-solid fa-id-badge me-2"></i>1. Thông tin Hồ sơ &amp; Ảnh đại diện
                        </button>
                        <button type="button"
                                class="list-group-item list-group-item-action py-3 px-4 fw-semibold <?php echo ($activeTab === 'password') ? 'active' : ''; ?>"
                                data-bs-toggle="list"
                                data-bs-target="#pane-password"
                                role="tab">
                            <i class="fa-solid fa-key me-2"></i>2. Đổi mật khẩu bảo mật
                        </button>
                        <button type="button"
                                class="list-group-item list-group-item-action py-3 px-4 fw-semibold <?php echo ($activeTab === 'preferences') ? 'active' : ''; ?>"
                                data-bs-toggle="list"
                                data-bs-target="#pane-preferences"
                                role="tab">
                            <i class="fa-solid fa-sliders me-2"></i>3. Tùy chỉnh giao diện (Preferences)
                        </button>
                    </div>
                </div>

                <!-- Cột phải: Nội dung chi tiết từng Tab -->
                <div class="col-lg-8">
                    <div class="tab-content">
                        <!-- TAB 1: CẬP NHẬT HỒ SƠ & AVATAR -->
                        <div class="tab-pane fade <?php echo ($activeTab === 'profile') ? 'show active' : ''; ?>" id="pane-profile" role="tabpanel">
                            <div class="edu-form-card">
                                <h4 class="fw-bold text-dark mb-1">
                                    <i class="fa-solid fa-user-pen text-primary me-2"></i>Cập Nhật Hồ Sơ &amp; Ảnh Đại Diện
                                </h4>
                                <p class="text-muted small mb-4">
                                    Thay đổi tên hiển thị và tải lên ảnh đại diện mới (hỗ trợ kiểm tra định dạng JPG, PNG, WEBP, GIF tối đa 2MB).
                                </p>

                                <form action="profile.php?tab=profile" method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="action" value="update_profile">

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold small">Địa chỉ Email đăng ký (Không thể thay đổi)</label>
                                        <input type="email" class="form-control edu-input bg-light" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                                    </div>

                                    <div class="mb-3">
                                        <label for="displayNameInput" class="form-label fw-semibold small">
                                            Tên hiển thị (Display Name) <span class="text-danger">*</span>
                                        </label>
                                        <input type="text"
                                               id="displayNameInput"
                                               name="display_name"
                                               class="form-control edu-input"
                                               value="<?php echo htmlspecialchars($user['display_name']); ?>"
                                               required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="avatarFileInput" class="form-label fw-semibold small">
                                            Tải lên ảnh đại diện mới (Tùy chọn)
                                        </label>
                                        <input type="file"
                                               id="avatarFileInput"
                                               name="avatar_file"
                                               class="form-control edu-input"
                                               accept="image/jpeg,image/png,image/webp,image/gif">
                                        <div class="form-text small">
                                            Hệ thống tự động kiểm tra định dạng tệp ảnh hợp lệ. Nếu chưa có ảnh riêng, bạn có thể dùng Avatar mặc định.
                                        </div>
                                    </div>

                                    <div class="form-check mb-4">
                                        <input class="form-check-input" type="checkbox" value="1" id="resetDefaultAvatarCheck" name="reset_default_avatar">
                                        <label class="form-check-label small text-secondary" for="resetDefaultAvatarCheck">
                                            Khôi phục về ảnh đại diện mặc định của hệ thống (Default Avatar)
                                        </label>
                                    </div>

                                    <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm">
                                        <i class="fa-solid fa-floppy-disk me-2"></i>Lưu Thay Đổi Hồ Sơ
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- TAB 2: ĐỔI MẬT KHẨU -->
                        <div class="tab-pane fade <?php echo ($activeTab === 'password') ? 'show active' : ''; ?>" id="pane-password" role="tabpanel">
                            <div class="edu-form-card">
                                <h4 class="fw-bold text-dark mb-1">
                                    <i class="fa-solid fa-shield-halved text-primary me-2"></i>Đổi Mật Khẩu Tài Khoản
                                </h4>
                                <p class="text-muted small mb-4">
                                    Để đảm bảo an toàn, vui lòng nhập mật khẩu hiện tại và nhập mật khẩu mới 2 lần để xác nhận.
                                </p>

                                <form action="profile.php?tab=password" method="POST">
                                    <input type="hidden" name="action" value="change_password">

                                    <div class="mb-3">
                                        <label for="currentPassInput" class="form-label fw-semibold small">
                                            Mật khẩu hiện tại <span class="text-danger">*</span>
                                        </label>
                                        <input type="password"
                                               id="currentPassInput"
                                               name="current_password"
                                               class="form-control edu-input"
                                               placeholder="Nhập mật khẩu đang sử dụng"
                                               required>
                                    </div>

                                    <div class="row g-3 mb-4">
                                        <div class="col-md-6">
                                            <label for="newPassInput" class="form-label fw-semibold small">
                                                Mật khẩu mới <span class="text-danger">*</span>
                                            </label>
                                            <input type="password"
                                                   id="newPassInput"
                                                   name="new_password"
                                                   class="form-control edu-input"
                                                   placeholder="Tối thiểu 6 ký tự"
                                                   required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="confirmPassInput" class="form-label fw-semibold small">
                                                Xác nhận mật khẩu mới (Nhập lần 2) <span class="text-danger">*</span>
                                            </label>
                                            <input type="password"
                                                   id="confirmPassInput"
                                                   name="confirm_password"
                                                   class="form-control edu-input"
                                                   placeholder="Nhập lại mật khẩu mới"
                                                   required>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm">
                                        <i class="fa-solid fa-key me-2"></i>Cập Nhật Mật Khẩu Mới
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- TAB 3: TÙY CHỈNH GIAO DIỆN (USER PREFERENCES) -->
                        <div class="tab-pane fade <?php echo ($activeTab === 'preferences') ? 'show active' : ''; ?>" id="pane-preferences" role="tabpanel">
                            <div class="edu-form-card">
                                <h4 class="fw-bold text-dark mb-1">
                                    <i class="fa-solid fa-palette text-primary me-2"></i>Tùy Chỉnh Giao Diện &amp; Hiển Thị (User Preferences)
                                </h4>
                                <p class="text-muted small mb-4">
                                    Điều chỉnh chế độ Sáng/Tối (Light/Dark Theme), kích thước phông chữ và tông màu thẻ thông tin theo sở thích của bạn.
                                </p>

                                <form action="profile.php?tab=preferences" method="POST" id="preferencesForm">
                                    <input type="hidden" name="action" value="update_preferences">

                                    <div class="mb-4">
                                        <label class="form-label fw-semibold small d-block mb-2">1. Chế độ giao diện (Theme Mode)</label>
                                        <div class="row g-3">
                                            <div class="col-sm-6">
                                                <label class="p-3 border rounded-3 d-flex align-items-center gap-3 w-100" style="cursor:pointer;">
                                                    <input type="radio"
                                                           name="theme_pref"
                                                           value="light"
                                                           class="form-check-input mt-0"
                                                           <?php echo (($user['theme'] ?? 'light') === 'light') ? 'checked' : ''; ?>>
                                                    <div>
                                                        <strong class="d-block"><i class="fa-solid fa-sun text-warning me-1"></i>Giao diện Sáng (Light)</strong>
                                                        <small class="text-muted">Trực quan, rõ nét ban ngày</small>
                                                    </div>
                                                </label>
                                            </div>
                                            <div class="col-sm-6">
                                                <label class="p-3 border rounded-3 d-flex align-items-center gap-3 w-100" style="cursor:pointer;">
                                                    <input type="radio"
                                                           name="theme_pref"
                                                           value="dark"
                                                           class="form-check-input mt-0"
                                                           <?php echo (($user['theme'] ?? 'light') === 'dark') ? 'checked' : ''; ?>>
                                                    <div>
                                                        <strong class="d-block"><i class="fa-solid fa-moon text-primary me-1"></i>Giao diện Tối (Dark)</strong>
                                                        <small class="text-muted">Dịu mắt khi học tập ban đêm</small>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row g-3 mb-4">
                                        <div class="col-md-6">
                                            <label for="fontSizeSelect" class="form-label fw-semibold small">2. Kích thước phông chữ (Font Size)</label>
                                            <?php $currFont = $user['font_size'] ?? 'medium'; ?>
                                            <select id="fontSizeSelect" name="font_size" class="form-select edu-input">
                                                <option value="small" <?php echo ($currFont === 'small') ? 'selected' : ''; ?>>Nhỏ gọn (Small - 14px)</option>
                                                <option value="medium" <?php echo ($currFont === 'medium') ? 'selected' : ''; ?>>Tiêu chuẩn (Medium - 16px)</option>
                                                <option value="large" <?php echo ($currFont === 'large') ? 'selected' : ''; ?>>Lớn dễ đọc (Large - 18px)</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="noteColorSelect" class="form-label fw-semibold small">3. Màu sắc thẻ ghi chú &amp; lịch học</label>
                                            <?php $currColor = $user['note_color'] ?? 'default'; ?>
                                            <select id="noteColorSelect" name="note_color" class="form-select edu-input">
                                                <option value="default" <?php echo ($currColor === 'default') ? 'selected' : ''; ?>>Mặc định hệ thống (Trắng)</option>
                                                <option value="blue" <?php echo ($currColor === 'blue') ? 'selected' : ''; ?>>Xanh dương học thuật (Ocean Blue)</option>
                                                <option value="amber" <?php echo ($currColor === 'amber') ? 'selected' : ''; ?>>Vàng ấm nổi bật (Warm Amber)</option>
                                                <option value="emerald" <?php echo ($currColor === 'emerald') ? 'selected' : ''; ?>>Xanh ngọc dịu mắt (Soft Emerald)</option>
                                            </select>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm">
                                        <i class="fa-solid fa-check me-2"></i>Lưu Cài Đặt Tùy Chỉnh
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Xem trước ảnh đại diện ngay khi chọn file
        var avatarInput = document.getElementById('avatarFileInput');
        var avatarPreview = document.getElementById('profileAvatarPreview');
        if (avatarInput && avatarPreview) {
            avatarInput.addEventListener('change', function () {
                if (this.files && this.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        avatarPreview.src = e.target.result;
                    };
                    reader.readAsDataURL(this.files[0]);
                }
            });
        }

        // Đồng bộ theme và font_size vào localStorage khi lưu form Preferences
        var prefForm = document.getElementById('preferencesForm');
        if (prefForm) {
            prefForm.addEventListener('submit', function () {
                var selectedTheme = prefForm.querySelector('input[name="theme_pref"]:checked');
                var selectedFont = document.getElementById('fontSizeSelect');
                if (selectedTheme) {
                    localStorage.setItem('educenter_theme', selectedTheme.value);
                    localStorage.setItem('edu_theme', selectedTheme.value);
                }
                if (selectedFont) {
                    localStorage.setItem('educenter_font_size', selectedFont.value);
                    localStorage.setItem('edu_font_size', selectedFont.value);
                }
            });
        }
    });
    </script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
