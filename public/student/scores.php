<?php
/**
 * public/student/scores.php
 * Route: Trang My Scores – bảng điểm quiz + engagement
 */
require_once dirname(dirname(__DIR__)) . '/config/config.php';
require_once dirname(dirname(__DIR__)) . '/config/Database.php';
require_once dirname(dirname(__DIR__)) . '/helpers/middleware.php';

if (session_status() === PHP_SESSION_NONE) session_start();

Middleware::requireStudent();

require_once APP_ROOT . '/models/EngagementScoreModel.php';

$db        = Database::getInstance();
$studentId = Middleware::user()['id'];
$engModel  = new EngagementScoreModel();

$engagements = $engModel->getByStudentId($studentId);

$quizHistory = $db->query(
    "SELECT qs_sub.total_score, qs_sub.max_score, qs_sub.submitted_at,
            qs.title AS quiz_title,
            cs.session_date,
            c.course_name, c.course_code
     FROM quiz_submissions qs_sub
     JOIN quiz_sessions qs  ON qs_sub.quiz_id  = qs.id
     JOIN class_sessions cs ON qs.session_id   = cs.id
     JOIN courses c         ON cs.course_id    = c.id
     WHERE qs_sub.student_id = ?
     ORDER BY qs_sub.submitted_at DESC",
    [$studentId]
)->fetchAll();

$pageTitle   = 'My Scores';
$currentPage = 'student.scores';
require_once APP_ROOT . '/views/student/scores.php';
