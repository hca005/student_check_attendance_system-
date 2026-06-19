<?php
// Global configuration with define guards to avoid redefinition warnings.

if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
}
if (!defined('DB_PORT')) {
    define('DB_PORT', '3306');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', 'student_attendance_db');
}
if (!defined('DB_USER')) {
    define('DB_USER', 'root');
}
if (!defined('DB_PASS')) {
    define('DB_PASS', '');
}
if (!defined('DB_CHARSET')) {
    define('DB_CHARSET', 'utf8mb4');
}

if (!defined('APP_NAME')) {
    define('APP_NAME', 'Student Attendance System');
}
if (!defined('APP_VERSION')) {
    define('APP_VERSION', '1.0.0');
}
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}
if (!defined('APP_URL')) {
    define('APP_URL', 'http://localhost/attendance_system/public');
}

if (!defined('SESSION_LIFETIME')) {
    define('SESSION_LIFETIME', 3600);
}
if (!defined('DEFAULT_ATTEND_SCORE')) {
    define('DEFAULT_ATTEND_SCORE', 2.00);
}
if (!defined('DEFAULT_QUIZ_CORRECT_SCORE')) {
    define('DEFAULT_QUIZ_CORRECT_SCORE', 2.00);
}
if (!defined('DEFAULT_DISCUSSION_SCORE')) {
    define('DEFAULT_DISCUSSION_SCORE', 1.00);
}

if (!defined('APP_ENV')) {
    define('APP_ENV', 'development');
}

if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}
