<?php
/**
 * public/student/courses.php
 * Route: Trang "My Courses"
 * FIX: bỏ cột c.description (không có trong schema)
 *      bỏ cột c.planned (dùng đúng status 'ended')
 */
require_once dirname(dirname(__DIR__)) . '/config/config.php';
require_once dirname(dirname(__DIR__)) . '/config/Database.php';
require_once dirname(dirname(__DIR__)) . '/helpers/middleware.php';

if (session_status() === PHP_SESSION_NONE) session_start();

Middleware::requireStudent();

$db        = Database::getInstance();
$studentId = Middleware::user()['id'];

// Lấy tất cả course + tên giảng viên
// NOTE: bảng courses KHÔNG có cột description → bỏ ra
$courses = $db->query(
    "SELECT c.id, c.course_code, c.course_name, c.semester,
            u.full_name AS teacher_name
     FROM courses c
     JOIN course_enrollments ce ON ce.course_id = c.id AND ce.user_id = ? AND ce.role = 'student'
     LEFT JOIN course_enrollments ce2 ON ce2.course_id = c.id AND ce2.role = 'teacher'
     LEFT JOIN users u ON u.id = ce2.user_id
     WHERE c.is_active = 1
     ORDER BY c.course_name ASC",
    [$studentId]
)->fetchAll();

// Bổ sung stats cho mỗi course
foreach ($courses as &$c) {
    $cid = (int)$c['id'];

    $c['ended_sessions'] = (int)$db->query(
        "SELECT COUNT(*) FROM class_sessions WHERE course_id = ? AND status = 'ended'",
        [$cid]
    )->fetchColumn();

    $c['total_sessions'] = (int)$db->query(
        "SELECT COUNT(*) FROM class_sessions WHERE course_id = ?",
        [$cid]
    )->fetchColumn();

    $c['present_count'] = (int)$db->query(
        "SELECT COUNT(*) FROM attendance_records ar
         JOIN class_sessions cs ON ar.session_id = cs.id
         WHERE cs.course_id = ? AND ar.student_id = ? AND ar.status = 'present'",
        [$cid, $studentId]
    )->fetchColumn();

    $c['quiz_count'] = (int)$db->query(
        "SELECT COUNT(*) FROM quiz_submissions qs_sub
         JOIN quiz_sessions qs  ON qs_sub.quiz_id  = qs.id
         JOIN class_sessions cs ON qs.session_id   = cs.id
         WHERE cs.course_id = ? AND qs_sub.student_id = ?",
        [$cid, $studentId]
    )->fetchColumn();

    $c['engagement'] = (float)($db->query(
        "SELECT engagement_index FROM engagement_scores
         WHERE student_id = ? AND course_id = ? LIMIT 1",
        [$studentId, $cid]
    )->fetchColumn() ?: 0);

    $c['att_pct'] = $c['ended_sessions'] > 0
        ? round($c['present_count'] / $c['ended_sessions'] * 100)
        : 0;

    // Không có cột description trong schema → để trống
    $c['description'] = '';
}
unset($c);

$pageTitle   = 'My Courses';
$currentPage = 'student.courses';
require_once APP_ROOT . '/views/student/courses.php';
