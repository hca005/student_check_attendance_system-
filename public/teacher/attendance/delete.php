<?php
// public/teacher/attendance/delete.php
// Router: POST /teacher/attendance/delete.php
// Xóa phương thức điểm danh

require_once dirname(__DIR__, 3) . '/config/config.php';
require_once APP_ROOT . '/controllers/AttendanceController.php';

$controller = new AttendanceController();
$controller->deleteMethod();
?>
