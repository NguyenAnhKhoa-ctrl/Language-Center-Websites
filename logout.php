<?php
/**
 * EDUCENTER - HỆ THỐNG WEBSITE QUẢN LÝ TRUNG TÂM NGOẠI NGỮ & GIA SƯ
 * File: logout.php
 * Phụ trách (Bổ sung Gói A1 - Nguyễn Anh Khoa - 52400051):
 * Đăng xuất người dùng khỏi phiên làm việc và chuyển hướng về trang Đăng nhập
 */

require_once __DIR__ . '/includes/auth_check.php';

if (isset($_SESSION['edu_user'])) {
    unset($_SESSION['edu_user']);
}

header('Location: login.php?logged_out=1');
exit;
?>
