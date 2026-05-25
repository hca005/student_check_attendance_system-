<?php
define('APP_ROOT', dirname(dirname(__DIR__)));
require_once APP_ROOT . '/config/config.php';
require_once APP_ROOT . '/helpers/middleware.php';
require_once APP_ROOT . '/config/database.php';

Middleware::teacher();

$db = Database::getInstance()->getConnection();
$teacher_id = $_SESSION['user_id'];

$stmt = $db->prepare("
    SELECT qs.*, cs.session_date, cs.title as session_title, c.course_name,
           COUNT(qq.id) as question_count
    FROM quiz_sessions qs
    JOIN class_sessions cs ON qs.session_id = cs.id
    JOIN courses c ON cs.course_id = c.id
    LEFT JOIN quiz_questions qq ON qq.quiz_id = qs.id
    WHERE cs.teacher_id = ?
    GROUP BY qs.id
    ORDER BY cs.session_date DESC
");
$stmt->execute([$teacher_id]);
$quizzes = $stmt->fetchAll();

require_once APP_ROOT . '/views/teacher/quiz_overview.php';
