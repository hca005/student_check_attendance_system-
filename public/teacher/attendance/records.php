<?php
// public/teacher/attendance/records.php
// Router: GET /teacher/attendance/records.php?session_id=X
// Danh sách sinh viên đã check-in

require_once dirname(__DIR__, 3) . '/config/config.php';
require_once APP_ROOT . '/controllers/AttendanceController.php';

$controller = new AttendanceController();
$controller->listRecords();
?>
