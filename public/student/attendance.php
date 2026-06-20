<?php
/**
 * public/student/attendance.php
 * Route cho trang điểm danh của student
 * Xử lý cả GET (hiển thị) và POST/AJAX (checkin)
 */
require_once dirname(dirname(__DIR__)) . '/config/config.php';
require_once dirname(dirname(__DIR__)) . '/config/Database.php';
require_once dirname(dirname(__DIR__)) . '/helpers/middleware.php';

if (session_status() === PHP_SESSION_NONE) session_start();

Middleware::requireStudent();

require_once APP_ROOT . '/models/AttendanceRecordModel.php';
require_once APP_ROOT . '/models/AttendanceMethodModel.php';
require_once APP_ROOT . '/models/InteractionLogModel.php';
require_once APP_ROOT . '/models/EngagementScoreModel.php';
require_once APP_ROOT . '/models/AlertLogModel.php';

$db        = Database::getInstance();
$studentId = Middleware::user()['id'];
$action    = $_GET['action'] ?? '';

// ── AJAX: check-in bằng OTP ─────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'checkin') {
    header('Content-Type: application/json; charset=utf-8');

    $otp       = strtoupper(trim($_POST['otp_code'] ?? ''));
    $sessionId = (int)($_POST['session_id'] ?? 0);

    if (!$otp || !$sessionId) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng nhập mã OTP và chọn buổi học.']);
        exit;
    }

    $attRecordModel = new AttendanceRecordModel();
    $attMethodModel = new AttendanceMethodModel();

    // Kiểm tra đã điểm danh chưa
    $existing = $attRecordModel->getBySessionAndStudent($sessionId, $studentId);
    if ($existing && $existing['status'] === 'present') {
        echo json_encode(['success' => false, 'message' => 'Bạn đã điểm danh buổi học này rồi.']);
        exit;
    }

    // Xác thực OTP – tìm method có token khớp + session đang active
    $method = $db->query(
        'SELECT am.* FROM attendance_methods am
         WHERE am.session_id = ? AND am.token = ?
           AND am.is_active = 1
           AND (am.expires_at IS NULL OR am.expires_at > NOW())
         LIMIT 1',
        [$sessionId, $otp]
    )->fetch();

    if (!$method) {
        echo json_encode(['success' => false, 'message' => 'Mã OTP không hợp lệ hoặc đã hết hạn.']);
        exit;
    }

    // Ghi attendance_record
    if ($existing) {
        $attRecordModel->update($existing['id'], [
            'status'        => 'present',
            'method_id'     => $method['id'],
            'checked_in_at' => new DateTime(),
        ]);
        $recordId = $existing['id'];
    } else {
        $recordId = $attRecordModel->create($sessionId, $studentId, 'present', $method['id'], new DateTime());
    }

    // Lấy course
    $course = $db->query(
        'SELECT c.* FROM courses c JOIN class_sessions cs ON cs.course_id=c.id WHERE cs.id=? LIMIT 1',
        [$sessionId]
    )->fetch();

    // Ghi interaction_log
    $logModel = new InteractionLogModel();
    $logModel->create(
        $studentId, $sessionId,
        InteractionLogModel::ACTION_CHECKIN,
        $recordId, 'Check-in thành công',
        defined('DEFAULT_ATTEND_SCORE') ? DEFAULT_ATTEND_SCORE : 2.0
    );

    // Recalculate engagement + alerts
    if ($course) {
        $engModel   = new EngagementScoreModel();
        $alertModel = new AlertLogModel();
        $engModel->recalculate($studentId, (int)$course['id']);
        $alertModel->generateAlerts($studentId, (int)$course['id']);
    }

    echo json_encode(['success' => true, 'message' => 'Điểm danh thành công!']);
    exit;
}

// ── GET: Hiển thị trang điểm danh ──────────────────────────
$attRecordModel = new AttendanceRecordModel();

$courseId = (int)($_GET['course_id'] ?? 0);

// Lấy danh sách course student đang học
$courses = $db->query(
    "SELECT c.id, c.course_code, c.course_name, c.semester
     FROM courses c JOIN enrollments ce ON ce.course_id=c.id
     WHERE ce.user_id=? AND ce.role='student' AND c.is_active=1
     ORDER BY c.course_name ASC",
    [$studentId]
)->fetchAll();

if ($courseId === 0 && !empty($courses)) {
    $courseId = (int)$courses[0]['id'];
}

$currentCourse = null;
foreach ($courses as $c) {
    if ((int)$c['id'] === $courseId) { $currentCourse = $c; break; }
}

$records = [];
$stats   = [];
if ($courseId > 0) {
    $records = $db->query(
        'SELECT cs.id AS session_id, cs.session_date, cs.start_time, cs.end_time,
                cs.title, cs.status AS session_status,
                ar.status AS attendance_status, ar.checked_in_at, ar.note
         FROM class_sessions cs
         LEFT JOIN attendance_records ar ON ar.session_id=cs.id AND ar.student_id=?
         WHERE cs.course_id=?
         ORDER BY cs.session_date DESC, cs.start_time DESC',
        [$studentId, $courseId]
    )->fetchAll();

    $stats = $attRecordModel->getAttendanceStats($courseId, $studentId);
}

$pageTitle   = 'Attendance History';
$currentPage = 'student.attendance';
require_once APP_ROOT . '/views/student/attendance.php';