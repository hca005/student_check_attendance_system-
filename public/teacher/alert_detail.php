<?php
define('APP_ROOT', dirname(dirname(__DIR__)));
require_once APP_ROOT . '/config/config.php';
require_once APP_ROOT . '/helpers/middleware.php';
require_once APP_ROOT . '/config/database.php';

Middleware::teacher();

$student_id = $_GET['student_id'] ?? 0;
$course_id  = $_GET['course_id'] ?? 0;

$db = Database::getInstance()->getConnection();

$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

$stmt = $db->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->execute([$course_id]);
$course = $stmt->fetch();

$stmt = $db->prepare("SELECT * FROM alert_logs WHERE student_id = ? AND course_id = ? ORDER BY created_at DESC");
$stmt->execute([$student_id, $course_id]);
$alerts = $stmt->fetchAll();

require_once APP_ROOT . '/views/teacher/alert_detail.php';
