<?php
/**
 * public/student/engagement.php
 * Route: Trang "My Engagement"
 */
require_once dirname(dirname(__DIR__)) . '/config/config.php';
require_once dirname(dirname(__DIR__)) . '/config/Database.php';
require_once dirname(dirname(__DIR__)) . '/helpers/middleware.php';

if (session_status() === PHP_SESSION_NONE) session_start();

Middleware::requireStudent();

require_once APP_ROOT . '/models/EngagementScoreModel.php';
require_once APP_ROOT . '/models/AlertLogModel.php';

$db        = Database::getInstance();
$studentId = Middleware::user()['id'];
$engModel  = new EngagementScoreModel();

// Engagement scores theo từng course
$engagements = $engModel->getByStudentId($studentId);

// Lịch sử quiz submissions (20 gần nhất)
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
     ORDER BY qs_sub.submitted_at DESC
     LIMIT 20",
    [$studentId]
)->fetchAll();

// Lịch sử interaction logs (30 gần nhất)
$interactionLogs = $db->query(
    "SELECT il.action_type, il.description, il.points_earned, il.created_at,
            cs.session_date,
            c.course_name, c.course_code
     FROM interaction_logs il
     JOIN class_sessions cs ON il.session_id = cs.id
     JOIN courses c         ON cs.course_id  = c.id
     WHERE il.user_id = ?
     ORDER BY il.created_at DESC
     LIMIT 30",
    [$studentId]
)->fetchAll();

// Alerts đang mở
$alertModel = new AlertLogModel();
$openAlerts = $alertModel->getOpenAlertsByStudent($studentId);

$pageTitle   = 'My Engagement';
$currentPage = 'student.engagement';
require_once APP_ROOT . '/views/student/engagement.php';