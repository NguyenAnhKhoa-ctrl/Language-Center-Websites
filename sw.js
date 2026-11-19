/**
 * EDUCENTER - HỆ THỐNG WEBSITE QUẢN LÝ TRUNG TÂM NGOẠI NGỮ & GIA SƯ
 * File: sw.js
 * Phụ trách (Bổ sung Gói A3 - Nguyễn Anh Khoa - 52400051):
 * Progressive Web App (PWA) Service Worker hỗ trợ truy cập Ngoại tuyến (Offline Mode)
 */

const CACHE_NAME = 'educenter-pwa-v1';
const OFFLINE_ASSETS = [
    'index.php',
    'courses.php',
    'teachers.php',
    'lookup.php',
    'register.php',
    'contact.php',
    'login.php',
    'signup.php',
    'manifest.json',
    'assets/css/style.css',
    'assets/js/main.js'
];

// 1. Cài đặt Service Worker & Lưu các trang cốt lõi vào Cache
self.addEventListener('install', function (event) {
    event.waitUntil(
        caches.open(CACHE_NAME).then(function (cache) {
            return cache.addAll(OFFLINE_ASSETS).catch(function () {
                // Bỏ qua lỗi từng file riêng lẻ nếu đang nạp lần đầu
            });
        })
    );
    self.skipWaiting();
});

// 2. Kích hoạt & Dọn dẹp Cache phiên bản cũ
self.addEventListener('activate', function (event) {
    event.waitUntil(
        caches.keys().then(function (keyList) {
            return Promise.all(
                keyList.map(function (key) {
                    if (key !== CACHE_NAME) {
                        return caches.delete(key);
                    }
                })
            );
        })
    );
    self.clients.claim();
});

// 3. Chiến lược Network-First kết hợp Offline Cache Fallback
self.addEventListener('fetch', function (event) {
    if (event.request.method !== 'GET') {
        return;
    }

    event.respondWith(
        fetch(event.request)
            .then(function (networkResponse) {
                const responseClone = networkResponse.clone();
                caches.open(CACHE_NAME).then(function (cache) {
                    cache.put(event.request, responseClone);
                });
                return networkResponse;
            })
            .catch(function () {
                return caches.match(event.request).then(function (cachedResponse) {
                    return cachedResponse || caches.match('index.php');
                });
            })
    );
});
