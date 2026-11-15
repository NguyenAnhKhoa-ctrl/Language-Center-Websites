-- ============================================================================
-- HỆ THỐNG WEBSITE QUẢN LÝ TRUNG TÂM NGOẠI NGỮ & GIA SƯ EDUCENTER
-- File: database.sql
-- Phần phụ trách (Tuần 1 - Nguyễn Anh Khoa - 52400051):
-- Khởi tạo CSDL & 5 bảng: Users, Students, Courses, Classes, Enrollments
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `QuanLyTrungTam`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `QuanLyTrungTam`;

-- ----------------------------------------------------------------------------
-- 1. Bảng Users (Tài khoản người dùng & Phân quyền)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `Enrollments`;
DROP TABLE IF EXISTS `Classes`;
DROP TABLE IF EXISTS `Courses`;
DROP TABLE IF EXISTS `Students`;
DROP TABLE IF EXISTS `Users`;

CREATE TABLE `Users` (
    `user_id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL COMMENT 'Mật khẩu đã mã hóa bcrypt',
    `role` ENUM('admin', 'teacher', 'student') NOT NULL DEFAULT 'student',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 2. Bảng Students (Hồ sơ Học viên)
-- ----------------------------------------------------------------------------
CREATE TABLE `Students` (
    `student_id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_code` VARCHAR(20) NOT NULL UNIQUE COMMENT 'Mã học viên (VD: HV001)',
    `user_id` INT DEFAULT NULL,
    `full_name` VARCHAR(100) NOT NULL,
    `dob` DATE NOT NULL,
    `phone` VARCHAR(20) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `status` VARCHAR(30) NOT NULL DEFAULT 'Đang học' COMMENT 'Đang học, Bảo lưu, Nghỉ',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_students_user` FOREIGN KEY (`user_id`) REFERENCES `Users`(`user_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 3. Bảng Courses (Khóa học & Dịch vụ Gia sư 1-1)
-- ----------------------------------------------------------------------------
CREATE TABLE `Courses` (
    `course_id` INT AUTO_INCREMENT PRIMARY KEY,
    `course_code` VARCHAR(30) NOT NULL UNIQUE,
    `course_name` VARCHAR(150) NOT NULL,
    `category` VARCHAR(50) NOT NULL COMMENT 'IELTS, TOEIC, Giao tiếp, Gia sư 1-1, Thiếu nhi',
    `badge` VARCHAR(50) DEFAULT 'Phổ biến' COMMENT 'Hot nhất, Cấp tốc, Phổ biến, 1 Kèm 1',
    `level_range` VARCHAR(100) DEFAULT 'Mọi trình độ',
    `target_goal` VARCHAR(100) DEFAULT NULL,
    `duration` VARCHAR(50) DEFAULT '3 tháng (36 buổi)',
    `description` TEXT,
    `fee` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 4. Bảng Classes (Lớp học thực tế mở theo khóa)
-- ----------------------------------------------------------------------------
CREATE TABLE `Classes` (
    `class_id` INT AUTO_INCREMENT PRIMARY KEY,
    `class_code` VARCHAR(30) NOT NULL UNIQUE,
    `course_id` INT NOT NULL,
    `teacher_id` INT DEFAULT NULL COMMENT 'Khóa ngoại liên kết bảng Teachers',
    `class_name` VARCHAR(100) NOT NULL,
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `room` VARCHAR(50) NOT NULL,
    `max_students` INT NOT NULL DEFAULT 15,
    `status` VARCHAR(30) NOT NULL DEFAULT 'Đang mở',
    CONSTRAINT `fk_classes_course` FOREIGN KEY (`course_id`) REFERENCES `Courses`(`course_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 5. Bảng Enrollments (Đăng ký học / Xếp lớp N-N)
-- ----------------------------------------------------------------------------
CREATE TABLE `Enrollments` (
    `enrollment_id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `class_id` INT NOT NULL,
    `enrollment_date` DATE NOT NULL,
    `tuition_status` VARCHAR(30) DEFAULT 'Đã hoàn thành',
    CONSTRAINT `fk_enrollments_student` FOREIGN KEY (`student_id`) REFERENCES `Students`(`student_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_enrollments_class` FOREIGN KEY (`class_id`) REFERENCES `Classes`(`class_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY `uk_student_class` (`student_id`, `class_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- DỮ LIỆU MẪU (SEED DATA)
-- ============================================================================

-- 1. Seed Users (Mật khẩu mặc định: 123456 đã băm bcrypt)
INSERT INTO `Users` (`user_id`, `username`, `password`, `role`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
(2, 'michael.roberts', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
(3, 'thaomy.ielts', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
(4, 'dinhkhoi.toeic', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
(5, 'baotram.tutor', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
(6, 'hv001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
(7, 'hv002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
(8, 'hv003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
(9, 'hv004', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
(10, 'hv005', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student');

-- 2. Seed Students
INSERT INTO `Students` (`student_id`, `student_code`, `user_id`, `full_name`, `dob`, `phone`, `email`, `status`) VALUES
(1, 'HV001', 6, 'Nguyễn Văn An', '2002-05-15', '0987654321', 'nguyenvanan@gmail.com', 'Đang học'),
(2, 'HV002', 7, 'Trần Thị Bích Ngọc', '2003-08-22', '0912345678', 'bichngoc.tran@gmail.com', 'Đang học'),
(3, 'HV003', 8, 'Lê Gia Huy', '2005-11-10', '0909090909', 'giahuy.le@gmail.com', 'Đang học'),
(4, 'HV004', 9, 'Hoàng Minh Quân', '2001-04-03', '0933221100', 'minhquan.h@gmail.com', 'Bảo lưu'),
(5, 'HV005', 10, 'Đỗ Phương Linh', '2004-12-19', '0978112233', 'phuonglinh.do@gmail.com', 'Đang học');

-- 3. Seed Courses
INSERT INTO `Courses` (`course_id`, `course_code`, `course_name`, `category`, `badge`, `level_range`, `target_goal`, `duration`, `description`, `fee`) VALUES
(1, 'IELTS-INT', 'Luyện thi IELTS Intensive (Mục tiêu 6.5 - 7.5+)', 'IELTS', 'Hot nhất', 'Đầu vào 5.0 - 5.5', 'IELTS 6.5 - 7.5+', '3 tháng (36 buổi)', 'Chinh phục toàn diện 4 kỹ năng Nghe - Nói - Đọc - Viết cùng chuyên gia 8.5+ IELTS. Chữa bài Writing & Speaking 1-1 hàng tuần.', 8900000.00),
(2, 'TOEIC-FAST', 'Luyện thi TOEIC Cấp Tốc (Cam kết 750+)', 'TOEIC', 'Cấp tốc', 'Đầu vào 400+', 'TOEIC 750 - 850+', '2 tháng (24 buổi)', 'Hệ thống hóa ngữ pháp trọng tâm, chiến thuật giải đề TOEIC Reading & Listening thực chiến theo format IIG mới nhất.', 4500000.00),
(3, 'COMM-PRO', 'Tiếng Anh Giao Tiếp Thực Chiến (100% Phản Xạ)', 'Giao tiếp', 'Phổ biến', 'Mất gốc - Cơ bản', 'Giao tiếp trôi chảy', '3 tháng (30 buổi)', 'Thực hành phản xạ 100% cùng giảng viên bản xứ và giảng viên Thạc sĩ quốc tế. Tự tin thuyết trình, phỏng vấn và làm việc.', 5200000.00),
(4, 'TUTOR-1ON1', 'Gia sư Tiếng Anh 1 Kèm 1 Theo Yêu Cầu', 'Gia sư 1-1', 'Cá nhân hóa', 'Tùy chỉnh 1-1', 'Theo mục tiêu riêng', 'Linh hoạt theo tuần', 'Lộ trình cá nhân hóa 1 thầy 1 trò (trực tiếp hoặc Online qua Zoom). Linh hoạt khung giờ, tập trung bứt phá điểm yếu nhanh nhất.', 6500000.00),
(5, 'KIDS-CAM', 'Tiếng Anh Thiếu Nhi Chuẩn Cambridge (Starters - Flyers)', 'Thiếu nhi', 'Chuẩn Quốc tế', '6 - 14 tuổi', 'Chứng chỉ Cambridge', '3 tháng (36 buổi)', 'Phương pháp học qua dự án sinh động, phát âm chuẩn bản xứ ngay từ nhỏ và cam kết đạt khiên tối đa kỳ thi Cambridge.', 4800000.00),
(6, 'IELTS-FOUND', 'IELTS Foundation - Xây Nền Toàn Diện (0 - 5.0+)', 'IELTS', 'Nền tảng', 'Mất gốc - 3.5', 'IELTS 5.0 - 5.5', '3.5 tháng (42 buổi)', 'Củng cố phát âm chuẩn IPA, từ vựng học thuật theo chủ đề và nền tảng ngữ pháp vững chắc trước khi bước vào giai đoạn luyện đề.', 6800000.00);

-- 4. Seed Classes
INSERT INTO `Classes` (`class_id`, `class_code`, `course_id`, `teacher_id`, `class_name`, `start_date`, `end_date`, `room`, `max_students`, `status`) VALUES
(1, 'IELTS_K42', 1, 2, 'IELTS Intensive K42', '2026-09-15', '2026-12-15', 'P.201', 12, 'Đang mở'),
(2, 'TOEIC_22', 2, 3, 'TOEIC 750+ Cấp Tốc', '2026-09-20', '2026-11-20', 'P.102', 15, 'Đang mở'),
(3, 'GT_05', 3, 1, 'Tiếng Anh Giao Tiếp VIP', '2026-10-01', '2026-12-30', 'P.101', 10, 'Đang mở'),
(4, 'TUTOR_VIP3', 4, 4, 'Gia sư 1-1 Luyện thi THPT', '2026-10-05', '2026-12-25', 'Phòng VIP 3', 1, 'Đang mở');

-- 5. Seed Enrollments
INSERT INTO `Enrollments` (`enrollment_id`, `student_id`, `class_id`, `enrollment_date`, `tuition_status`) VALUES
(1, 1, 1, '2026-09-10', 'Đã hoàn thành'),
(2, 2, 2, '2026-09-15', 'Đã hoàn thành'),
(3, 3, 4, '2026-10-01', 'Đã hoàn thành'),
(4, 4, 3, '2026-09-25', 'Bảo lưu'),
(5, 5, 1, '2026-09-12', 'Đã hoàn thành');
