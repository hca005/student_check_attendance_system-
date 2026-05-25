<?php
// public/teacher/attendance/update.php
// Router: POST /teacher/attendance/update.php
// Cập nhật phương thức điểm danh

require_once dirname(__DIR__, 3) . '/config/config.php';
require_once APP_ROOT . '/controllers/AttendanceController.php';

$controller = new AttendanceController();
$controller->updateMethod();
?>
