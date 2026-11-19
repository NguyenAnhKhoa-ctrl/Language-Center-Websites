/**
 * EDUCENTER - HỆ THỐNG WEBSITE QUẢN LÝ TRUNG TÂM NGOẠI NGỮ & GIA SƯ
 * File: assets/js/main.js
 * Phụ trách (Tuần 1 - Nguyễn Anh Khoa - 52400051):
 * Xử lý tương tác giao diện Client UI (Active menu, bộ lọc khóa học, hiệu ứng cuộn)
 */

document.addEventListener('DOMContentLoaded', function () {
    // 1. Tự động đánh dấu active cho thanh điều hướng dựa trên URL hiện tại
    const currentFile = window.location.pathname.split('/').pop() || 'index.php';
    const navLinks = document.querySelectorAll('.edu-nav-link');

    navLinks.forEach(function (link) {
        const href = link.getAttribute('href');
        if (href === currentFile) {
            link.classList.add('active');
        }
    });

    // 2. Xử lý bộ lọc danh mục khóa học (dùng cho trang chủ & courses.php)
    const filterButtons = document.querySelectorAll('[data-course-filter]');
    const courseItems = document.querySelectorAll('[data-course-category]');

    if (filterButtons.length > 0 && courseItems.length > 0) {
        filterButtons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                const targetCategory = this.getAttribute('data-course-filter');

                // Cập nhật trạng thái nút active
                filterButtons.forEach(function (b) {
                    b.classList.remove('btn-primary', 'text-white');
                    b.classList.add('btn-light');
                });
                this.classList.remove('btn-light');
                this.classList.add('btn-primary', 'text-white');

                // Lọc thẻ khóa học
                courseItems.forEach(function (item) {
                    const itemCat = item.getAttribute('data-course-category');
                    if (targetCategory === 'all' || itemCat === targetCategory) {
                        item.style.display = '';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });
    }

    // 3. Kiểm tra dữ liệu đầu vào (Client-side Validation) cho biểu mẫu register.php
    const regForm = document.getElementById('courseRegistrationForm');
    if (regForm) {
        const fullNameInput = document.getElementById('full_name');
        const phoneInput = document.getElementById('phone');
        const emailInput = document.getElementById('email');

        regForm.addEventListener('submit', function (event) {
            let isValid = true;

            // Kiểm tra họ tên
            if (!fullNameInput.value || fullNameInput.value.trim().length < 2) {
                fullNameInput.classList.add('is-invalid');
                isValid = false;
            } else {
                fullNameInput.classList.remove('is-invalid');
            }

            // Kiểm tra số điện thoại Việt Nam (10-11 số)
            const cleanPhone = (phoneInput.value || '').replace(/[\s\.\-]+/g, '');
            const phoneRegex = /^(0|\+84)[0-9]{8,10}$/;
            if (!phoneRegex.test(cleanPhone)) {
                phoneInput.classList.add('is-invalid');
                isValid = false;
            } else {
                phoneInput.classList.remove('is-invalid');
            }

            // Kiểm tra email nếu người dùng có nhập
            const emailVal = (emailInput.value || '').trim();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (emailVal !== '' && !emailRegex.test(emailVal)) {
                emailInput.classList.add('is-invalid');
                isValid = false;
            } else {
                emailInput.classList.remove('is-invalid');
            }

            if (!isValid) {
                event.preventDefault();
                event.stopPropagation();
            }
        });

        // Xóa trạng thái lỗi ngay khi người dùng nhập lại
        [fullNameInput, phoneInput, emailInput].forEach(function (inputEl) {
            if (inputEl) {
                inputEl.addEventListener('input', function () {
                    this.classList.remove('is-invalid');
                });
            }
        });
    }

    // 4. Xử lý điền nhanh dữ liệu mẫu & In phiếu kết quả trên trang Tra cứu (lookup.php - Lý Hạo Danh)
    const demoButtons = document.querySelectorAll('.demo-fill-id');
    const searchInput = document.getElementById('studentSearchInput');
    const lookupForm = document.getElementById('lookupForm');

    if (demoButtons.length > 0 && searchInput && lookupForm) {
        demoButtons.forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                const sampleId = this.getAttribute('data-id');
                if (sampleId) {
                    searchInput.value = sampleId;
                    lookupForm.submit();
                }
            });
        });
    }

    const printBtn = document.getElementById('btnPrintResult');
    if (printBtn) {
        printBtn.addEventListener('click', function () {
            window.print();
        });
    }

    // 5. Chuyển đổi giao diện Sáng / Tối (User Preferences - Gói A3 Nguyễn Anh Khoa)
    const themeToggleBtn = document.getElementById('btnThemeToggle');
    const themeIcon = document.getElementById('themeToggleIcon');
    const savedTheme = localStorage.getItem('educenter_theme') || localStorage.getItem('edu_theme');
    const savedFont = localStorage.getItem('educenter_font_size') || localStorage.getItem('edu_font_size');

    if (savedTheme) {
        document.body.setAttribute('data-edu-theme', savedTheme);
        if (themeIcon) {
            themeIcon.className = (savedTheme === 'dark') ? 'fa-solid fa-sun text-warning' : 'fa-solid fa-moon';
        }
    }
    if (savedFont) {
        document.body.setAttribute('data-edu-font', savedFont);
    }

    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', function () {
            const currentTheme = document.body.getAttribute('data-edu-theme') === 'dark' ? 'dark' : 'light';
            const nextTheme = currentTheme === 'dark' ? 'light' : 'dark';
            document.body.setAttribute('data-edu-theme', nextTheme);
            localStorage.setItem('educenter_theme', nextTheme);
            if (themeIcon) {
                themeIcon.className = (nextTheme === 'dark') ? 'fa-solid fa-sun text-warning' : 'fa-solid fa-moon';
            }
        });
    }

    // 6. Đăng ký Service Worker PWA & Đồng bộ dữ liệu Offline vào LocalStorage (Gói A3)
    const offlineBanner = document.getElementById('offlineStatusBanner');
    function updateOnlineStatus() {
        if (!offlineBanner) return;
        if (navigator.onLine) {
            offlineBanner.classList.add('d-none');
        } else {
            offlineBanner.classList.remove('d-none');
        }
    }

    window.addEventListener('online', updateOnlineStatus);
    window.addEventListener('offline', updateOnlineStatus);
    updateOnlineStatus();

    // Lưu kết quả bảng điểm tra cứu gần nhất vào LocalStorage để xem được khi Offline
    const printAreaEl = document.getElementById('printArea');
    if (printAreaEl) {
        try {
            localStorage.setItem('educenter_last_lookup_html', printAreaEl.innerHTML);
            localStorage.setItem('educenter_last_lookup_time', new Date().toLocaleString('vi-VN'));
        } catch (e) {
            // Bỏ qua nếu bộ nhớ trình duyệt bị giới hạn
        }
    }

    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('sw.js').catch(function () {
            // Bỏ qua nếu chạy từ thư mục con hoặc môi trường chặn SW
        });
    }
});
