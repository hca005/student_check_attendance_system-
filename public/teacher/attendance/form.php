<?php
// public/teacher/attendance/form.php
// Router: GET /teacher/attendance/form.php?session_id=X&method_id=Y (optional)
// Form tạo hoặc sửa phương thức điểm danh

require_once dirname(__DIR__, 3) . '/config/config.php';
require_once APP_ROOT . '/controllers/AttendanceController.php';

$controller = new AttendanceController();
$controller->methodsForm();
?>
