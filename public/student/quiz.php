<?php
/**
 * public/student/quiz.php
 * Route cho quiz: danh sách, làm bài, nộp bài
 */
require_once dirname(dirname(__DIR__)) . '/config/config.php';
require_once dirname(dirname(__DIR__)) . '/config/Database.php';
require_once dirname(dirname(__DIR__)) . '/helpers/middleware.php';

if (session_status() === PHP_SESSION_NONE) session_start();

Middleware::requireStudent();

require_once APP_ROOT . '/models/QuizSessionModel.php';
require_once APP_ROOT . '/models/QuizQuestionModel.php';
require_once APP_ROOT . '/models/QuizSubmissionModel.php';
require_once APP_ROOT . '/models/InteractionLogModel.php';
require_once APP_ROOT . '/models/EngagementScoreModel.php';
require_once APP_ROOT . '/models/AlertLogModel.php';

$db        = Database::getInstance();
$studentId = Middleware::user()['id'];
$action    = $_GET['action'] ?? '';

// ── AJAX: Nộp bài quiz ──────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'submit') {
    header('Content-Type: application/json; charset=utf-8');

    if (!Middleware::isFromCampusWiFi()) {
        echo json_encode(['success' => false, 'message' => 'Cảnh báo: Bạn phải kết nối mạng WiFi của trường (Campus Network) để nộp bài!']);
        exit;
    }

    $quizId = (int)($_POST['quiz_id'] ?? 0);
    $subModel = new QuizSubmissionModel();

    // Lấy quiz
    $quiz = $db->query('SELECT * FROM quiz_sessions WHERE id=? LIMIT 1', [$quizId])->fetch();
    if (!$quiz || $quiz['status'] !== 'open') {
        echo json_encode(['success' => false, 'message' => 'Quiz không tồn tại hoặc đã đóng.']);
        exit;
    }

    // Kiểm tra đã nộp chưa
    if (!$quiz['allow_retake'] && $subModel->hasSubmitted($quizId, $studentId)) {
        echo json_encode(['success' => false, 'message' => 'Bạn đã nộp bài quiz này rồi.']);
        exit;
    }

    // Lấy answers từ POST
    $rawAnswers = $_POST['answers'] ?? [];
    $answers    = [];
    foreach ($rawAnswers as $qId => $opt) {
        $answers[(int)$qId] = strtoupper(trim($opt));
    }

    if (empty($answers)) {
        echo json_encode(['success' => false, 'message' => 'Bạn chưa trả lời câu hỏi nào.']);
        exit;
    }

    // Tạo submission (auto-grading trong model)
    $submissionId = $subModel->create($quizId, $studentId, $answers);
    $submission   = $subModel->getById($submissionId);

    // Ghi interaction_log
    $logModel = new InteractionLogModel();
    $logModel->create(
        $studentId,
        (int)$quiz['session_id'],
        InteractionLogModel::ACTION_SUBMIT_QUIZ,
        $submissionId,
        'Nộp quiz: ' . $quiz['title'],
        (float)$submission['total_score'] * (defined('DEFAULT_QUIZ_CORRECT_SCORE') ? DEFAULT_QUIZ_CORRECT_SCORE : 2.0)
    );

    // Recalculate engagement + alerts
    $course = $db->query(
        'SELECT c.* FROM courses c JOIN class_sessions cs ON cs.course_id=c.id
         JOIN quiz_sessions qs ON qs.session_id=cs.id WHERE qs.id=? LIMIT 1',
        [$quizId]
    )->fetch();

    if ($course) {
        $engModel   = new EngagementScoreModel();
        $alertModel = new AlertLogModel();
        $engModel->recalculate($studentId, (int)$course['id']);
        $alertModel->generateAlerts($studentId, (int)$course['id']);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Nộp bài thành công!',
        'score'   => $submission['total_score'],
        'max'     => $submission['max_score'],
    ]);
    exit;
}

// ── GET: Hiển thị giao diện làm bài ────────────────────────
if ($action === 'take') {
    $quizId = (int)($_GET['quiz_id'] ?? 0);
    $subModel = new QuizSubmissionModel();

    if (!Middleware::isFromCampusWiFi()) {
        header('Location: ' . APP_URL . '/student/quiz.php?msg=wifi_required');
        exit;
    }

    $quiz = $db->query('SELECT * FROM quiz_sessions WHERE id=? LIMIT 1', [$quizId])->fetch();
    if (!$quiz || $quiz['status'] !== 'open') {
        header('Location: ' . APP_URL . '/student/quiz.php?msg=quiz_closed');
        exit;
    }

    if (!$quiz['allow_retake'] && $subModel->hasSubmitted($quizId, $studentId)) {
        header('Location: ' . APP_URL . '/student/quiz.php?msg=already_submitted');
        exit;
    }

    $quizQuestionModel = new QuizQuestionModel();
    $questions = $quizQuestionModel->getByQuizId($quizId);

    $pageTitle   = 'Take Quiz: ' . htmlspecialchars($quiz['title']);
    $currentPage = 'student.quiz';
    require_once APP_ROOT . '/views/student/quiz_take.php';
    exit;
}

// ── GET: Danh sách quiz ─────────────────────────────────────
$courseId = (int)($_GET['course_id'] ?? 0);

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

$quizzes = [];
if ($courseId > 0) {
    $quizzes = $db->query(
        'SELECT qs.id, qs.title, qs.description, qs.time_limit_minutes,
                qs.status, qs.allow_retake,
                cs.session_date, cs.title AS session_title,
                (SELECT COUNT(*) FROM quiz_questions WHERE quiz_id=qs.id) AS question_count,
                (SELECT id   FROM quiz_submissions WHERE quiz_id=qs.id AND student_id=? LIMIT 1) AS submission_id,
                (SELECT total_score FROM quiz_submissions WHERE quiz_id=qs.id AND student_id=? LIMIT 1) AS my_score,
                (SELECT max_score   FROM quiz_submissions WHERE quiz_id=qs.id AND student_id=? LIMIT 1) AS max_score
         FROM quiz_sessions qs
         JOIN class_sessions cs ON qs.session_id=cs.id
         WHERE cs.course_id=?
         ORDER BY cs.session_date DESC, qs.id DESC',
        [$studentId, $studentId, $studentId, $courseId]
    )->fetchAll();
}

$pageTitle   = 'My Quizzes';
$currentPage = 'student.quiz';
require_once APP_ROOT . '/views/student/quiz.php';