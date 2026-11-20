# HỆ THỐNG WEBSITE QUẢN LÝ TRUNG TÂM NGOẠI NGỮ & GIA SƯ — EDUCENTER

**Học phần:** Lập trình Web & Ứng dụng (`503073` — Đại học Tôn Đức Thắng)  
**Giảng viên hướng dẫn:** Thầy Doãn Xuân Thanh  
**Công nghệ sử dụng:** PHP 8.2 (PDO) + MySQL/MariaDB + Bootstrap 5.3 + JavaScript (ES6) + HTML5/CSS3  
**Môi trường triển khai:** AWS EC2 Cloud Server (`http://3.0.209.145/`) & XAMPP Server  
**GitHub Repository:** https://github.com/NguyenAnhKhoa-ctrl/Language-Center-Websites

---

## 1. THÀNH VIÊN NHÓM THỰC HIỆN

| STT | Họ và Tên | MSSV | Phân hệ & Chức năng phụ trách |
| :-: | :--- | :---: | :--- |
| 1 | **Nguyễn Anh Khoa** | `52400051` | - Thiết kế CSDL (`Users`, `Students`, `Courses`, `Classes`, `Enrollments`)<br>- Client UI: Trang chủ (`index.php`), Khóa học & Gia sư (`courses.php`), Đăng ký học & Test trình độ (`register.php`)<br>- Admin Dashboard: Tổng quan (`admin/index.php`), Quản lý Học viên (`admin/students.php`), Hồ sơ Đăng ký trực tuyến (`admin/registrations.php`) |
| 2 | **Lý Hạo Danh** | `52400175` | - Thiết kế CSDL (`Teachers`, `Schedules`, `Attendance`, `Grades`, `Course_Registrations`) & Kết nối PDO + Mock Fallback (`config/db.php`)<br>- Client UI: Đội ngũ Giảng viên (`teachers.php`), Cổng Tra cứu Điểm & Lịch học (`lookup.php`), Liên hệ & Chi nhánh (`contact.php`)<br>- Admin Dashboard: Quản lý Giảng viên (`admin/teachers.php`), Xếp lịch & Điểm danh (`admin/schedule.php`), Quản lý Điểm số 4 kỹ năng (`admin/grades.php`) |

---

## 2. CẤU TRÚC THƯ MỤC DỰ ÁN

```text
Language-Center-Websites/
├── admin/                        # Phân hệ Quản trị trung tâm (Admin Dashboard)
│   ├── includes/
│   │   ├── admin_header.php      # Sidebar & Topbar quản trị
│   │   └── admin_footer.php      # Footer & Script đóng/mở menu quản trị
│   ├── index.php                 # Bảng điều khiển tổng quan (KPIs, Ca trực hôm nay)
│   ├── students.php              # Quản lý danh sách Học viên (Tìm kiếm, Lọc, Modal CRUD)
│   ├── teachers.php              # Quản lý đội ngũ Giảng viên & Gia sư (Card Layout, CRUD)
│   ├── schedule.php              # Ma trận Thời khóa biểu tuần & Điểm danh trực tiếp
│   ├── grades.php                # Quản lý Điểm 4 kỹ năng (Nghe, Nói, Đọc, Viết) & In bảng điểm
│   └── registrations.php         # Tiếp nhận & xử lý Hồ sơ đăng ký trực tuyến từ Client
├── assets/
│   ├── css/
│   │   └── style.css             # Hệ thống giao diện & Responsive đa thiết bị
│   └── js/
│       └── main.js               # Xử lý tương tác Client UI, Bộ lọc & Validation
├── config/
│   └── db.php                    # Kết nối PDO MySQL & Cơ chế Smart Mock Data Fallback
├── includes/
│   ├── header.php                # Topbar & Navbar phân hệ Khách hàng (Client UI)
│   └── footer.php                # Footer phân hệ Khách hàng
├── index.php                     # Trang chủ Client UI
├── courses.php                   # Danh sách Khóa học & Dịch vụ Gia sư 1-1
├── teachers.php                  # Đội ngũ Giảng viên & Gia sư tiêu biểu
├── lookup.php                    # Cổng Tra cứu Bảng điểm, Điểm danh & Lịch học trực tuyến
├── register.php                  # Form Đăng ký Khóa học & Đặt lịch Test trình độ 24/7
├── contact.php                   # Trang Liên hệ, Hệ thống Chi nhánh & FAQ
├── database.sql                  # Toàn bộ cấu trúc CSDL (10 bảng) & Dữ liệu mẫu (Seed Data)
├── Readme.txt                    # Hướng dẫn cài đặt và dữ liệu mẫu phục vụ chấm điểm
└── README.md                     # Tài liệu tổng quan dự án
```

---

## 3. HƯỚNG DẪN CÀI ĐẶT & CHẠY TRÊN XAMPP

1. **Bước 1:** Đặt mã nguồn dự án vào thư mục `htdocs` của XAMPP.
   - Có thể đặt trực tiếp tại `C:/xampp/htdocs/` hoặc `C:/xampp/htdocs/QuanLyTrungTam/` (mã nguồn sử dụng 100% đường dẫn tương đối `Relative URLs` nên tương thích hoàn hảo ở mọi vị trí và cổng chạy).
2. **Bước 2:** Mở **XAMPP Control Panel**, nhấn **Start** dịch vụ **Apache** và **MySQL**.
3. **Bước 3:** Mở trình duyệt truy cập `http://localhost/phpmyadmin`, tạo CSDL hoặc chọn tab **Import** và nhập trực tiếp file `database.sql` (file đã tích hợp sẵn lệnh `CREATE DATABASE IF NOT EXISTS QuanLyTrungTam`).
4. **Bước 4 (Cơ chế Smart Mock Data Fallback):**
   - Trong trường hợp máy chấm thi chưa bật dịch vụ MySQL hoặc chưa import `database.sql`, file `config/db.php` sẽ tự động kích hoạt chế độ **Mock Data Fallback** kết hợp **PHP Session** giúp toàn bộ các trang Client UI và Admin Dashboard vẫn hoạt động đầy đủ 100% chức năng mà không bị lỗi kết nối.

---

## 4. DỮ LIỆU MẪU ĐỂ KIỂM THỬ NHANH (SAMPLE DATA FOR GRADING)

### 4.1. Cổng Tra cứu Bảng điểm & Lịch học (`lookup.php`)
Giảng viên có thể nhập một trong các thông tin sau vào ô tra cứu (hoặc bấm trực tiếp các nút gợi ý trên giao diện):
- **Mã học viên 1:** `HV001` — **Nguyễn Văn An** (Lớp *IELTS Intensive K42* — Điểm Overall: `7.5`)
- **Mã học viên 2:** `HV002` — **Trần Thị Bích Ngọc** (Lớp *TOEIC 750+ Cấp Tốc* — Điểm Overall: `840/990`)
- **Mã học viên 3:** `HV003` — **Lê Gia Huy** (Lớp *Gia sư 1-1 Luyện thi THPT* — Điểm Overall: `8.65`)
- **Số điện thoại tra cứu nhanh:** `0987654321` hoặc `0912345678`

### 4.2. Tài khoản trong CSDL (`Users`)
- **Quản trị viên (Admin):** Username: `admin` | Password: `password`
- **Giảng viên (Teacher):** Username: `thaomy.ielts` | Password: `password`
- **Học viên (Student):** Username: `hv001` | Password: `password`
