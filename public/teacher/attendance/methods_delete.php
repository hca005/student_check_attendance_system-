<?php
// public/teacher/attendance/methods_delete.php
require_once dirname(__DIR__, 3) . '/config/config.php';
require_once APP_ROOT . '/controllers/AttendanceController.php';

session_start();

$controller = new AttendanceController();
$controller->deleteMethod();
