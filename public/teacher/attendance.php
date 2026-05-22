<?php
define('APP_ROOT', dirname(dirname(__DIR__)));
require_once APP_ROOT . '/config/config.php';
require_once APP_ROOT . '/helpers/middleware.php';
require_once APP_ROOT . '/config/database.php';

Middleware::teacher();

$db = Database::getInstance()->getConnection();
$teacher_id = $_SESSION['user_id'];

$stmt = $db->prepare("
    SELECT cs.*, c.course_name 
    FROM class_sessions cs
    JOIN courses c ON cs.course_id = c.id
    WHERE cs.teacher_id = ?
    ORDER BY cs.session_date DESC
");
$stmt->execute([$teacher_id]);
$sessions = $stmt->fetchAll();

require_once APP_ROOT . '/views/teacher/attendance/sessions_overview.php';
