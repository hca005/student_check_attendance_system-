<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/helpers/middleware.php';
require_once dirname(__DIR__) . '/controllers/user_controller.php';
require_once dirname(__DIR__) . '/controllers/course_controller.php';
require_once dirname(__DIR__) . '/controllers/enrollment_controller.php';
require_once dirname(__DIR__) . '/controllers/session_controller.php';
require_once dirname(__DIR__) . '/controllers/admin_monitoring_controller.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$page = $_GET['page'] ?? null;

$userController = new UserController();
$courseController = new CourseController();
$enrollmentController = new EnrollmentController();
$sessionController = new SessionController();
$monitorController = new AdminMonitoringController();

switch ($page) {
    case 'admin_dashboard':
        Middleware::requireAdmin();
        require APP_ROOT . '/views/admin/dashboard.php';
        exit;

    case 'admin_users':
        $userController->index();
        exit;
    case 'admin_users_create':
        $userController->create();
        exit;
    case 'admin_users_show':
        $userController->show();
        exit;
    case 'admin_users_store':
        $userController->store();
        exit;
    case 'admin_users_edit':
        $userController->edit();
        exit;
    case 'admin_users_update':
        $userController->update();
        exit;
    case 'admin_users_deactivate':
        $userController->deactivate();
        exit;
    case 'admin_users_activate':
        $userController->activate();
        exit;

    case 'admin_courses':
        $courseController->index();
        exit;
    case 'admin_course_create':
        $courseController->create();
        exit;
    case 'admin_course_edit':
        $courseController->edit();
        exit;
    case 'admin_course_delete':
        $courseController->delete();
        exit;

    case 'admin_enrollments':
        $enrollmentController->index();
        exit;
    case 'admin_enrollment_create':
        $enrollmentController->create();
        exit;
    case 'admin_enrollment_edit':
        $enrollmentController->edit();
        exit;
    case 'admin_enrollment_delete':
        $enrollmentController->delete();
        exit;

    case 'admin_sessions':
        $sessionController->index();
        exit;
    case 'admin_session_create':
        $sessionController->create();
        exit;
    case 'admin_session_edit':
        $sessionController->edit();
        exit;
    case 'admin_session_delete':
        $sessionController->delete();
        exit;

    case 'admin_engagement_scores':
        $monitorController->engagementScores();
        exit;
    case 'admin_alerts':
        $monitorController->alerts();
        exit;
    case 'admin_alert_detail':
        $monitorController->alertDetail();
        exit;
    case 'admin_alert_resolve':
        $monitorController->resolveAlert();
        exit;
    case 'admin_alert_generate':
        $monitorController->generateAlerts();
        exit;
    case 'admin_reports':
        Middleware::requireAdmin();
        require APP_ROOT . '/views/admin/reports.php';
        exit;
    case 'profile':
        require APP_ROOT . '/views/profile.php';
        exit;
    case 'settings':
        require APP_ROOT . '/views/settings.php';
        exit;
}

if (!empty($_SESSION['user_id'])) {
    Middleware::redirectByRole($_SESSION['role'] ?? '');
}

header('Location: ' . APP_URL . '/login.php');
exit;
