<?php
/**
 * EDUCENTER - HỆ THỐNG WEBSITE QUẢN LÝ TRUNG TÂM NGOẠI NGỮ & GIA SƯ
 * File: includes/auth_check.php
 * Phụ trách (Bổ sung Gói A1 - Nguyễn Anh Khoa - 52400051):
 * Quản lý phiên đăng nhập (Session Auth), Kích hoạt tài khoản Email, Phân quyền & Tùy chỉnh giao diện
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Danh sách tài khoản mẫu khởi tạo sẵn (Mật khẩu hỗ trợ: "123456" hoặc "password")
$defaultHash = password_hash('123456', PASSWORD_BCRYPT);

if (!isset($_SESSION['registered_users'])) {
    $_SESSION['registered_users'] = [
        'admin@educenter.edu.vn' => [
            'user_id'       => 1,
            'username'      => 'admin',
            'email'         => 'admin@educenter.edu.vn',
            'display_name'  => 'Quản Trị Viên EduCenter',
            'password_hash' => $defaultHash,
            'role'          => 'admin',
            'avatar'        => 'https://ui-avatars.com/api/?name=Admin+Manager&background=1E40AF&color=fff',
            'is_activated'  => 1,
            'theme'         => 'light',
            'font_size'     => 'medium',
            'note_color'    => 'default'
        ],
        'thaomy.ielts@educenter.edu.vn' => [
            'user_id'       => 3,
            'username'      => 'thaomy.ielts',
            'email'         => 'thaomy.ielts@educenter.edu.vn',
            'display_name'  => 'Cô Nguyễn Thảo My (IELTS 8.5+)',
            'password_hash' => $defaultHash,
            'role'          => 'teacher',
            'avatar'        => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=300&q=80',
            'is_activated'  => 1,
            'theme'         => 'light',
            'font_size'     => 'medium',
            'note_color'    => 'default'
        ],
        'nguyenvanan@gmail.com' => [
            'user_id'       => 6,
            'username'      => 'hv001',
            'email'         => 'nguyenvanan@gmail.com',
            'display_name'  => 'Nguyễn Văn An (HV001)',
            'password_hash' => $defaultHash,
            'role'          => 'student',
            'student_code'  => 'HV001',
            'avatar'        => 'https://ui-avatars.com/api/?name=Nguyen+Van+An&background=059669&color=fff',
            'is_activated'  => 1,
            'theme'         => 'light',
            'font_size'     => 'medium',
            'note_color'    => 'default'
        ]
    ];
}

// Xử lý kích hoạt tài khoản qua liên kết xác thực Email (?activate_account=1)
if (isset($_GET['activate_account']) && $_GET['activate_account'] === '1' && isset($_SESSION['edu_user'])) {
    $_SESSION['edu_user']['is_activated'] = 1;
    $userEmail = $_SESSION['edu_user']['email'];
    if (isset($_SESSION['registered_users'][$userEmail])) {
        $_SESSION['registered_users'][$userEmail]['is_activated'] = 1;
    }
    $_SESSION['activation_flash'] = 'Tài khoản của bạn đã được kích hoạt thành công qua Email!';
}

/**
 * Lấy thông tin người dùng đang đăng nhập
 */
function getCurrentUser() {
    return $_SESSION['edu_user'] ?? null;
}

/**
 * Bắt buộc phải đăng nhập bằng Tài khoản Quản trị viên (role === 'admin') mới được vào Web Quản trị
 */
function requireAdminAccount() {
    $currentUser = getCurrentUser();
    if ($currentUser === null || ($currentUser['role'] ?? '') !== 'admin') {
        $currentScript = basename($_SERVER['PHP_SELF'] ?? 'index.php');
        header('Location: ../login.php?auth_required=1&redirect=' . urlencode('admin/' . $currentScript));
        exit;
    }
}

/**
 * Kiểm tra mật khẩu đăng nhập (hỗ trợ bcrypt hash + mật khẩu mẫu 123456 / password)
 */
function verifyEduPassword($inputPassword, $storedHash) {
    if (password_verify($inputPassword, $storedHash)) {
        return true;
    }
    if ($inputPassword === '123456' || $inputPassword === 'password') {
        return true;
    }
    return false;
}

/**
 * Tìm kiếm tài khoản theo Email hoặc Username
 */
function findEduUserByIdentifier($identifier) {
    $identifierLower = mb_strtolower(trim($identifier), 'UTF-8');
    if ($identifierLower === '') {
        return null;
    }
    foreach ($_SESSION['registered_users'] as $email => $user) {
        if (mb_strtolower($email, 'UTF-8') === $identifierLower || mb_strtolower($user['username'], 'UTF-8') === $identifierLower) {
            return $user;
        }
    }
    return null;
}
?>
