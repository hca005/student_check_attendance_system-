<?php
/**
 * public/index.php
 * Main entry point and query-string router.
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/helpers/middleware.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$page = $_GET['page'] ?? null;

$adminUserRoutes = [
    'admin_users',
    'admin_users_create',
    'admin_users_store',
    'admin_users_edit',
    'admin_users_update',
    'admin_users_deactivate',
    'admin_users_activate',
];

if (in_array($page, $adminUserRoutes, true)) {
    require_once dirname(__DIR__) . '/controllers/user_controller.php';

    $userController = new UserController();

    switch ($page) {
        case 'admin_users':
            $userController->index();
            break;

        case 'admin_users_create':
            $userController->create();
            break;

        case 'admin_users_store':
            $userController->store();
            break;

        case 'admin_users_edit':
            $userController->edit();
            break;

        case 'admin_users_update':
            $userController->update();
            break;

        case 'admin_users_deactivate':
            $userController->deactivate();
            break;

        case 'admin_users_activate':
            $userController->activate();
            break;
    }

    exit;
}

if (!empty($_SESSION['user_id'])) {
    switch ($_SESSION['role'] ?? '') {
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
            break;
    }
} else {
    header('Location: ' . APP_URL . '/login.php');
}

exit;
