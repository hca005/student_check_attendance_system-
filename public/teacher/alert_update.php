<?php
define('APP_ROOT', dirname(dirname(__DIR__)));
require_once APP_ROOT . '/config/config.php';
require_once APP_ROOT . '/helpers/middleware.php';
require_once APP_ROOT . '/config/database.php';

Middleware::teacher();

$id         = $_POST['id'] ?? 0;
$status     = $_POST['status'] ?? '';
$student_id = $_POST['student_id'] ?? 0;
$course_id  = $_POST['course_id'] ?? 0;

$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("UPDATE alert_logs SET status = ?, resolved_by = ?, resolved_at = NOW() WHERE id = ?");
$stmt->execute([$status, $_SESSION['user_id'], $id]);

$_SESSION['success'] = 'Đã cập nhật trạng thái alert!';
header('Location: ' . APP_URL . '/teacher/alert_detail.php?student_id=' . $student_id . '&course_id=' . $course_id);
exit;
