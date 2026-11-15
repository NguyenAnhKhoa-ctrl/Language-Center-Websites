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
});
