-- ============================================================================
-- HỆ THỐNG WEBSITE QUẢN LÝ TRUNG TÂM NGOẠI NGỮ & GIA SƯ EDUCENTER
-- File: database.sql
-- Phần phụ trách:
-- + Tuần 1 - Nguyễn Anh Khoa (52400051): Khởi tạo CSDL & 5 bảng: Users, Students, Courses, Classes, Enrollments
-- + Tuần 1 - Lý Hạo Danh (52400175): Bổ sung 5 bảng: Teachers, Schedules, Attendance, Grades, Course_Registrations & Seed Data
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `QuanLyTrungTam`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `QuanLyTrungTam`;

-- ----------------------------------------------------------------------------
-- 1. Bảng Users (Tài khoản người dùng & Phân quyền)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `Course_Registrations`;
DROP TABLE IF EXISTS `Grades`;
DROP TABLE IF EXISTS `Attendance`;
DROP TABLE IF EXISTS `Schedules`;
DROP TABLE IF EXISTS `Enrollments`;
DROP TABLE IF EXISTS `Classes`;
DROP TABLE IF EXISTS `Teachers`;
DROP TABLE IF EXISTS `Courses`;
DROP TABLE IF EXISTS `Students`;
DROP TABLE IF EXISTS `Users`;

CREATE TABLE `Users` (
    `user_id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) DEFAULT NULL COMMENT 'Địa chỉ email đăng ký & khôi phục mật khẩu',
    `display_name` VARCHAR(100) DEFAULT NULL COMMENT 'Tên hiển thị trên giao diện',
    `password` VARCHAR(255) NOT NULL COMMENT 'Mật khẩu đã mã hóa bcrypt',
    `role` ENUM('admin', 'teacher', 'student') NOT NULL DEFAULT 'student',
    `avatar` VARCHAR(255) DEFAULT NULL COMMENT 'Đường dẫn ảnh đại diện',
    `is_activated` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '0: Chưa kích hoạt email, 1: Đã kích hoạt',
    `theme_pref` VARCHAR(20) NOT NULL DEFAULT 'light' COMMENT 'light hoặc dark',
    `font_size` VARCHAR(20) NOT NULL DEFAULT 'medium' COMMENT 'small, medium, large',
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

-- ============================================================================
-- PHẦN BỔ SUNG (TUẦN 1 - LÝ HẠO DANH - 52400175)
-- Các bảng: Teachers, Schedules, Attendance, Grades, Course_Registrations
-- ============================================================================

-- ----------------------------------------------------------------------------
-- 6. Bảng Teachers (Đội ngũ Giảng viên & Gia sư)
-- ----------------------------------------------------------------------------
CREATE TABLE `Teachers` (
    `teacher_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT DEFAULT NULL,
    `full_name` VARCHAR(100) NOT NULL,
    `avatar` VARCHAR(255) DEFAULT NULL,
    `phone` VARCHAR(20) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `specialization` VARCHAR(120) NOT NULL COMMENT 'Chuyên môn giảng dạy',
    `degree` VARCHAR(255) NOT NULL COMMENT 'Bằng cấp & Chứng chỉ quốc tế',
    `experience` VARCHAR(255) DEFAULT NULL,
    `rating` DECIMAL(3, 1) NOT NULL DEFAULT 5.0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_teachers_user` FOREIGN KEY (`user_id`) REFERENCES `Users`(`user_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed Teachers trước khi gắn khóa ngoại vào bảng Classes
INSERT INTO `Teachers` (`teacher_id`, `user_id`, `full_name`, `avatar`, `phone`, `email`, `specialization`, `degree`, `experience`, `rating`) VALUES
(1, 2, 'Thầy Michael Roberts', 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=400&q=80', '0988 123 456', 'michael.roberts@educenter.edu.vn', 'Trưởng khoa Giao tiếp & Thuyết trình', 'Thạc sĩ Ngôn ngữ học (ĐH Oxford), CELTA loại Xuất sắc', '10 năm giảng dạy tại các viện ngôn ngữ quốc tế', 4.9),
(2, 3, 'Cô Nguyễn Thảo My', 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=400&q=80', '0977 234 567', 'thaomy.ielts@educenter.edu.vn', 'Chuyên gia Luyện thi IELTS 8.5+', 'IELTS 8.5 Overall (Writing 8.0, Speaking 8.5), Cử nhân Sư phạm Anh', '6 năm chuyên luyện thi IELTS cấp tốc du học', 5.0),
(3, 4, 'Thầy Trần Đình Khôi', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=400&q=80', '0912 345 678', 'dinhkhoi.toeic@educenter.edu.vn', 'Chuyên gia Luyện thi TOEIC & Ngữ Pháp', 'TOEIC 990/990 Tuyệt đối, Thủ khoa ĐH Ngoại Thương', '8 năm đào tạo hơn 3,000 học viên đạt chuẩn đầu ra đại học', 4.8),
(4, 5, 'Cô Lê Hoàng Bảo Trâm', 'https://images.unsplash.com/photo-1580489944761-15a19d654956?auto=format&fit=crop&w=400&q=80', '0933 456 789', 'baotram.tutor@educenter.edu.vn', 'Gia sư Cao cấp 1 Kèm 1 & Tiếng Anh Thiếu Nhi', 'Chứng chỉ TESOL Quốc tế, Cử nhân Tâm lý Giáo dục', '5 năm kinh nghiệm gia sư riêng cho học sinh trường quốc tế', 4.9);

-- Bổ sung khóa ngoại từ Classes.teacher_id tham chiếu đến Teachers.teacher_id
ALTER TABLE `Classes`
    ADD CONSTRAINT `fk_classes_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `Teachers`(`teacher_id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ----------------------------------------------------------------------------
-- 7. Bảng Schedules (Thời khóa biểu / Lịch học theo tuần)
-- ----------------------------------------------------------------------------
CREATE TABLE `Schedules` (
    `schedule_id` INT AUTO_INCREMENT PRIMARY KEY,
    `class_id` INT NOT NULL,
    `study_day` VARCHAR(30) NOT NULL COMMENT 'Thứ 2, Thứ 3, Thứ 4, Thứ 5, Thứ 6, Thứ 7, Chủ Nhật',
    `study_time` VARCHAR(50) NOT NULL COMMENT 'Khung giờ ca học (VD: 18:00 - 19:30)',
    `room` VARCHAR(50) NOT NULL,
    CONSTRAINT `fk_schedules_class` FOREIGN KEY (`class_id`) REFERENCES `Classes`(`class_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `Schedules` (`schedule_id`, `class_id`, `study_day`, `study_time`, `room`) VALUES
(1, 1, 'Thứ 2', '18:00 - 19:30', 'P.201'),
(2, 1, 'Thứ 4', '18:00 - 19:30', 'P.201'),
(3, 1, 'Thứ 6', '18:00 - 19:30', 'P.201'),
(4, 2, 'Thứ 3', '19:45 - 21:15', 'P.102'),
(5, 2, 'Thứ 5', '19:45 - 21:15', 'P.102'),
(6, 3, 'Thứ 7', '19:45 - 21:15', 'P.101'),
(7, 4, 'Thứ 7', '18:00 - 19:30', 'Phòng VIP 3'),
(8, 4, 'Chủ Nhật', '18:00 - 19:30', 'Phòng VIP 3');

-- ----------------------------------------------------------------------------
-- 8. Bảng Attendance (Nhật ký Điểm danh chuyên cần)
-- ----------------------------------------------------------------------------
CREATE TABLE `Attendance` (
    `attendance_id` INT AUTO_INCREMENT PRIMARY KEY,
    `class_id` INT NOT NULL,
    `student_id` INT NOT NULL,
    `attendance_date` DATE NOT NULL,
    `lesson_name` VARCHAR(255) NOT NULL,
    `status` VARCHAR(30) NOT NULL DEFAULT 'Có mặt' COMMENT 'Có mặt, Vắng mặt, Có phép',
    `note` VARCHAR(255) DEFAULT NULL,
    CONSTRAINT `fk_attendance_class` FOREIGN KEY (`class_id`) REFERENCES `Classes`(`class_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_attendance_student` FOREIGN KEY (`student_id`) REFERENCES `Students`(`student_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `Attendance` (`attendance_id`, `class_id`, `student_id`, `attendance_date`, `lesson_name`, `status`, `note`) VALUES
(1, 1, 1, '2026-10-10', 'Buổi 24: Luyện đề Mock Test Speaking', 'Có mặt', 'Chuẩn bị bài tốt'),
(2, 1, 1, '2026-10-08', 'Buổi 23: Chiến thuật Writing Task 1', 'Có mặt', 'Đúng giờ'),
(3, 1, 1, '2026-10-05', 'Buổi 22: Phân tích dạng đề Matching Headings', 'Có phép', 'Bận thi giữa kỳ tại trường'),
(4, 2, 2, '2026-10-09', 'Buổi 16: Thi thử TOEIC Full Test 3', 'Có mặt', 'Làm bài nghiêm túc'),
(5, 4, 3, '2026-10-11', 'Buổi 12: Chữa đề thi thử THPT Chuyên', 'Có mặt', 'Đạt 9.2 điểm');

-- ----------------------------------------------------------------------------
-- 9. Bảng Grades (Bảng điểm 4 kỹ năng & Đánh giá học thuật)
-- ----------------------------------------------------------------------------
CREATE TABLE `Grades` (
    `grade_id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `class_id` INT NOT NULL,
    `exam_type` VARCHAR(50) NOT NULL DEFAULT 'Giữa khóa',
    `listening_score` DECIMAL(5, 2) DEFAULT NULL,
    `speaking_score` DECIMAL(5, 2) DEFAULT NULL,
    `reading_score` DECIMAL(5, 2) DEFAULT NULL,
    `writing_score` DECIMAL(5, 2) DEFAULT NULL,
    `overall_score` DECIMAL(5, 2) NOT NULL,
    `rank_level` VARCHAR(50) NOT NULL,
    `teacher_comment` TEXT,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_grades_student` FOREIGN KEY (`student_id`) REFERENCES `Students`(`student_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_grades_class` FOREIGN KEY (`class_id`) REFERENCES `Classes`(`class_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `Grades` (`grade_id`, `student_id`, `class_id`, `exam_type`, `listening_score`, `speaking_score`, `reading_score`, `writing_score`, `overall_score`, `rank_level`, `teacher_comment`) VALUES
(1, 1, 1, 'Giữa khóa', 7.50, 7.50, 8.00, 7.00, 7.50, 'Giỏi (IELTS 7.5)', 'Tiếp thu bài cực kỳ nhanh, kỹ năng Nghe và Đọc rất vững. Cần chú trọng mở rộng vốn từ vựng học thuật cho Task 2 Writing.'),
(2, 2, 2, 'Giữa khóa', 430.00, NULL, 410.00, NULL, 840.00, 'Xuất sắc (840/990 TOEIC)', 'Khả năng bắt từ khóa phần Part 3 và Part 4 rất chuẩn xác. Ngữ pháp chắc chắn.'),
(3, 3, 4, 'Giữa khóa', NULL, NULL, 8.80, 8.50, 8.65, 'Giỏi (Mục tiêu 9+ THPT)', 'Tiến bộ vượt bậc sau 1 tháng gia sư riêng. Đã xử lý tốt các câu hỏi phân loại về Collocations và Idioms.'),
(4, 4, 3, 'Giữa khóa', 6.00, 6.50, 6.50, 6.00, 6.25, 'Khá', 'Phát âm cải thiện tốt, từ vựng phong phú.'),
(5, 5, 1, 'Giữa khóa', 7.00, 7.00, 7.50, 6.50, 7.00, 'Giỏi (IELTS 7.0)', 'Tiếp thu nhanh, tư duy làm bài mạch lạc.');

-- ----------------------------------------------------------------------------
-- 10. Bảng Course_Registrations (Hồ sơ Đăng ký trực tuyến từ Client UI)
-- ----------------------------------------------------------------------------
CREATE TABLE `Course_Registrations` (
    `registration_id` INT AUTO_INCREMENT PRIMARY KEY,
    `ticket_code` VARCHAR(30) NOT NULL UNIQUE,
    `full_name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20) NOT NULL,
    `email` VARCHAR(100) DEFAULT NULL,
    `course_name` VARCHAR(150) NOT NULL,
    `preferred_time` VARCHAR(80) DEFAULT NULL,
    `notes` TEXT,
    `status` VARCHAR(30) NOT NULL DEFAULT 'Chờ liên hệ',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `Course_Registrations` (`registration_id`, `ticket_code`, `full_name`, `phone`, `email`, `course_name`, `preferred_time`, `notes`, `status`) VALUES
(1, 'DK101234', 'Nguyễn Hoàng Nam', '0983 221 456', 'hoangnam.nguyen@gmail.com', 'Luyện thi IELTS Intensive (Mục tiêu 6.5 - 7.5+)', 'Ca Tối (18:00 - 19:30)', 'Cần thi IELTS trước tháng 12 để nộp hồ sơ du học Canada', 'Chờ liên hệ'),
(2, 'DK101189', 'Đỗ Minh Châu', '0912 889 001', 'minhchau.do@gmail.com', 'Gia sư Tiếng Anh 1 Kèm 1 Theo Yêu Cầu', 'Cuối tuần (Thứ 7 - CN)', 'Muốn học kèm riêng tại nhà hoặc qua Zoom cùng cô Bảo Trâm', 'Đã tư vấn'),
(3, 'DK101045', 'Vũ Thị Quỳnh Như', '0977 445 123', 'quynhnhu.vu@gmail.com', 'Tiếng Anh Giao Tiếp Thực Chiến (100% Phản Xạ)', 'Ca Tối (19:45 - 21:15)', 'Người đi làm, muốn cải thiện kỹ năng nghe nói họp trực tuyến', 'Đã xếp lớp');

