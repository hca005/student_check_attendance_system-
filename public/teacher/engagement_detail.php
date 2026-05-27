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

$stmt = $db->prepare("SELECT * FROM engagement_scores WHERE student_id = ? AND course_id = ?");
$stmt->execute([$student_id, $course_id]);
$score = $stmt->fetch();

$stmt = $db->prepare("
    SELECT ar.*, cs.session_date, cs.title, cs.start_time, cs.end_time
    FROM attendance_records ar
    JOIN class_sessions cs ON ar.session_id = cs.id
    WHERE ar.student_id = ? AND cs.course_id = ?
    ORDER BY cs.session_date DESC
");
$stmt->execute([$student_id, $course_id]);
$attendances = $stmt->fetchAll();

$stmt = $db->prepare("
    SELECT qs2.total_score, qs2.max_score, qs2.submitted_at, qs.title, cs.session_date
    FROM quiz_submissions qs2
    JOIN quiz_sessions qs ON qs2.quiz_id = qs.id
    JOIN class_sessions cs ON qs.session_id = cs.id
    WHERE qs2.student_id = ? AND cs.course_id = ?
    ORDER BY qs2.submitted_at DESC
");
$stmt->execute([$student_id, $course_id]);
$quizSubmissions = $stmt->fetchAll();

require_once APP_ROOT . '/views/teacher/engagement_detail.php';
