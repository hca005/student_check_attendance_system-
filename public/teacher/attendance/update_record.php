<?php
// public/teacher/attendance/update_record.php
require_once dirname(__DIR__, 3) . '/config/config.php';
require_once APP_ROOT . '/controllers/AttendanceController.php';

session_start();

$controller = new AttendanceController();
$controller->updateRecord();
