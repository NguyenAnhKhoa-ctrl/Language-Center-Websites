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
});
