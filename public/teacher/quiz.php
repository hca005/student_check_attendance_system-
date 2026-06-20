<?php
define('APP_ROOT', dirname(dirname(__DIR__)));
require_once APP_ROOT . '/config/config.php';
require_once APP_ROOT . '/helpers/middleware.php';
require_once APP_ROOT . '/config/database.php';

Middleware::teacher();

$db = Database::getInstance()->getConnection();
$teacher_id = $_SESSION['user_id'];

if ($_SESSION['role'] === 'admin') {
    $stmt = $db->query("
        SELECT qs.*,
               cs.session_date,
               cs.title as session_title,
               c.course_name,
               c.course_code,
               COUNT(DISTINCT sub.id) as submission_count
        FROM quiz_sessions qs
        JOIN class_sessions cs ON qs.session_id = cs.id
        JOIN courses c ON cs.course_id = c.id
        LEFT JOIN quiz_submissions sub ON sub.quiz_id = qs.id
        GROUP BY qs.id
        ORDER BY cs.session_date DESC
    ");
    $quizzes = $stmt->fetchAll();
} else {
    $stmt = $db->prepare("
        SELECT qs.*,
               cs.session_date,
               cs.title as session_title,
               c.course_name,
               c.course_code,
               COUNT(DISTINCT sub.id) as submission_count
        FROM quiz_sessions qs
        JOIN class_sessions cs ON qs.session_id = cs.id
        JOIN courses c ON cs.course_id = c.id
        LEFT JOIN quiz_submissions sub ON sub.quiz_id = qs.id
        WHERE cs.teacher_id = ?
        GROUP BY qs.id
        ORDER BY cs.session_date DESC
    ");
    $stmt->execute([$teacher_id]);
    $quizzes = $stmt->fetchAll();
}

require_once APP_ROOT . '/views/teacher/quiz_overview.php';
