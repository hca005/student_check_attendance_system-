<?php
// ============================================================
// config/config.php
// Cấu hình toàn cục cho ứng dụng
// ============================================================

// ── Database ──────────────────────────────────────────────
define('DB_HOST',    'localhost');
define('DB_PORT',    '3306');
define('DB_NAME',    'attendance_system');
define('DB_USER',    'root');
define('DB_PASS',    '');          // XAMPP mặc định: rỗng
define('DB_CHARSET', 'utf8mb4');

// ── Application ───────────────────────────────────────────
define('APP_NAME',    'Attendance & Engagement Tracker');
define('APP_VERSION', '1.0.0');
define('APP_ROOT',    dirname(__DIR__));     // thư mục gốc dự án
define('APP_URL',     'http://localhost/attendance_system/public'); // KHÔNG có dấu / cuối

// ── Session ───────────────────────────────────────────────
define('SESSION_LIFETIME', 3600);   // 1 giờ (giây)

// ── Engagement rules mặc định (dùng khi chưa set ở course) ─
define('DEFAULT_ATTEND_SCORE',       2.00);
define('DEFAULT_QUIZ_CORRECT_SCORE', 2.00);
define('DEFAULT_DISCUSSION_SCORE',   1.00);

// ── Dev / Prod ────────────────────────────────────────────
define('APP_ENV', 'development');   // đổi thành 'production' khi deploy

if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}