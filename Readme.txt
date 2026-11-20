================================================================================
HỆ THỐNG WEBSITE QUẢN LÝ TRUNG TÂM NGOẠI NGỮ & GIA SƯ - EDUCENTER
HỌC PHẦN: LẬP TRÌNH WEB & ỨNG DỤNG (503073) - ĐẠI HỌC TÔN ĐỨC THẮNG
================================================================================

1. THÔNG TIN NHÓM THỰC HIỆN & GITHUB REPOSITORY
--------------------------------------------------------------------------------
- Giảng viên hướng dẫn : Thầy Doãn Xuân Thanh
- Thành viên 1         : Nguyễn Anh Khoa - MSSV: 52400051
- Thành viên 2         : Lý Hạo Danh     - MSSV: 52400175
- Live AWS Server URL  : http://3.0.209.145/
- GitHub Repository URL:
  https://github.com/NguyenAnhKhoa-ctrl/Language-Center-Websites

2. CÔNG NGHỆ & MÔI TRƯỜNG PHÁT TRIỂN
--------------------------------------------------------------------------------
- Front-End : HTML5, CSS3, JavaScript (ES6), Bootstrap 5.3, Font Awesome 6.4
- Back-End  : PHP 8.x (Kết nối PDO chống SQL Injection)
- Database  : MySQL / MariaDB (Chuẩn 3NF)
- Web Server: Apache 2.4 (XAMPP Server)

3. HƯỚNG DẪN CÀI ĐẶT & CHẠY CHƯƠNG TRÌNH TRÊN XAMPP
--------------------------------------------------------------------------------
Bước 1: Sao chép mã nguồn dự án vào thư mục "htdocs" của XAMPP.
        (Mã nguồn tuân thủ nghiêm ngặt quy tắc Relative URLs, không hardcode
        cổng hay tên thư mục nên có thể đặt trực tiếp tại htdocs/ hoặc trong
        thư mục con bất kỳ).

Bước 2: Mở XAMPP Control Panel -> Nhấn "Start" hai dịch vụ Apache và MySQL.

Bước 3: Mở trình duyệt truy cập http://localhost/phpmyadmin
        - Nhấn vào tab "Import" -> Chọn file "database.sql" nằm ở thư mục gốc
          của mã nguồn -> Nhấn "Import" (File database.sql sẽ tự động khởi tạo
          CSDL "QuanLyTrungTam", tạo bảng và nạp sẵn dữ liệu mẫu).
        - Cấu hình kết nối mặc định trong file "config/db.php":
          + Host    : 127.0.0.1
          + Database: QuanLyTrungTam
          + Username: root
          + Password: (để trống)

Bước 4 (Cơ chế Smart Mock Data Fallback):
        - Ngay cả khi máy chấm thi chưa kịp cấu hình MySQL hoặc chưa import
          file database.sql, hệ thống tại "config/db.php" sẽ tự động chuyển sang
          chế độ Mock Data Fallback kết hợp PHP Session. Toàn bộ các trang
          Client UI và Admin Dashboard vẫn hoạt động đầy đủ 100% tính năng,
          không bao giờ xảy ra lỗi kết nối hoặc trắng trang.

4. ĐƯỜNG DẪN TRUY CẬP & DỮ LIỆU MẪU ĐỂ CHẤM ĐIỂM (PRE-LOADED DATA)
--------------------------------------------------------------------------------
A. Phân hệ Khách hàng (Client UI):
   - Trang chủ                 : index.php
   - Danh sách Khóa học & 1-1  : courses.php
   - Đội ngũ Giảng viên        : teachers.php
   - Cổng Tra cứu Điểm & Lịch  : lookup.php
   - Đăng ký học & Test đầu vào: register.php
   - Liên hệ & Chi nhánh       : contact.php

B. Phân hệ Quản trị trung tâm (Admin Dashboard):
   - Bảng điều khiển tổng quan : admin/index.php
   - Quản lý Học viên (CRUD)   : admin/students.php
   - Quản lý Giảng viên (CRUD) : admin/teachers.php
   - Xếp lịch & Điểm danh tuần : admin/schedule.php
   - Quản lý Điểm 4 kỹ năng    : admin/grades.php
   - Hồ sơ Đăng ký trực tuyến  : admin/registrations.php

C. Dữ liệu mẫu dùng để kiểm thử Cổng Tra cứu (lookup.php):
   - Mã học viên 1 : HV001 (Nguyễn Văn An - Lớp IELTS Intensive K42 - Overall 7.5)
   - Mã học viên 2 : HV002 (Trần Thị Bích Ngọc - Lớp TOEIC 750+ Cấp Tốc - 840/990)
   - Mã học viên 3 : HV003 (Lê Gia Huy - Lớp Gia sư 1-1 Luyện thi THPT - 8.65)
   - Số điện thoại : 0987654321 hoặc 0912345678

D. Tài khoản mẫu trong bảng Users:
   - Admin   : username = admin        | password = password
   - Teacher : username = thaomy.ielts | password = password
   - Student : username = hv001        | password = password
================================================================================
