<?php
define('APP_ROOT', dirname(dirname(__DIR__)));
require_once APP_ROOT . '/config/config.php';
require_once APP_ROOT . '/helpers/middleware.php';
require_once APP_ROOT . '/config/database.php';

Middleware::teacher();

$db = Database::getInstance()->getConnection();
$teacher_id = $_SESSION['user_id'];

$stmt = $db->prepare("
    SELECT es.*, u.full_name, u.student_code, c.course_name
    FROM engagement_scores es
    JOIN users u ON es.student_id = u.id
    JOIN courses c ON es.course_id = c.id
    JOIN course_enrollments ce ON ce.course_id = c.id AND ce.user_id = ?
    WHERE ce.role = 'teacher'
    ORDER BY es.engagement_index DESC
");
$stmt->execute([$teacher_id]);
$scores = $stmt->fetchAll();

require_once APP_ROOT . '/views/teacher/engagement_overview.php';
