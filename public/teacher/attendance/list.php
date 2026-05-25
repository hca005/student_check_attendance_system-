<?php
// public/teacher/attendance/list.php
// Router: GET /teacher/attendance/list.php?session_id=X
// Hiển thị danh sách phương thức điểm danh

require_once dirname(__DIR__, 3) . '/config/config.php';
require_once APP_ROOT . '/controllers/AttendanceController.php';

$controller = new AttendanceController();
$controller->listMethods();
?>
