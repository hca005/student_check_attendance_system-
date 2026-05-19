<?php
/**
 * public/index.php – Entry point chính
 * Redirect về trang login hoặc dashboard tương ứng
 */
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/helpers/middleware.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!empty($_SESSION['user_id'])) {
    switch ($_SESSION['role']) {
        case 'admin':
            header('Location: ' . APP_URL . '/admin/dashboard.php');
            break;
        case 'teacher':
            header('Location: ' . APP_URL . '/teacher/dashboard.php');
            break;
        case 'student':
            header('Location: ' . APP_URL . '/student/dashboard.php');
            break;
        default:
            header('Location: ' . APP_URL . '/login.php');
    }
} else {
    header('Location: ' . APP_URL . '/login.php');
}
exit;