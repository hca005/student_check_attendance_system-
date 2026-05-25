<?php
// public/teacher/attendance/create.php
// Router: POST /teacher/attendance/create.php
// Tạo phương thức điểm danh mới

require_once dirname(__DIR__, 3) . '/config/config.php';
require_once APP_ROOT . '/controllers/AttendanceController.php';

$controller = new AttendanceController();
$controller->createMethod();
?>
