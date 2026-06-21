<?php
define('APP_ROOT', dirname(dirname(__DIR__)));
require_once APP_ROOT . '/config/config.php';
require_once APP_ROOT . '/helpers/middleware.php';
require_once APP_ROOT . '/config/database.php';
 
Middleware::teacher();
 
$db = Database::getInstance()->getConnection();
$teacher_id = $_SESSION['user_id'];
 
$stmt = $db->prepare("
    SELECT al.*, al.message AS alert_message, u.full_name, u.student_code, c.course_name
    FROM alerts al
    JOIN users u ON al.student_id = u.id
    JOIN courses c ON al.course_id = c.id
    JOIN enrollments ce ON ce.course_id = c.id AND ce.user_id = ?
    WHERE ce.role = 'teacher'
    ORDER BY al.created_at DESC
");
$stmt->execute([$teacher_id]);
$alerts = $stmt->fetchAll();
 
require_once APP_ROOT . '/views/teacher/alerts_overview.php';
 