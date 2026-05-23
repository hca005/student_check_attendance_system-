<?php
// ============================================================
// controllers/StudentController.php
// Xử lý toàn bộ chức năng của sinh viên
// Thành viên 3 phụ trách
// ============================================================

require_once APP_ROOT . '/config/Database.php';
require_once APP_ROOT . '/helpers/Middleware.php';
require_once APP_ROOT . '/models/QuizSubmissionModel.php';
require_once APP_ROOT . '/models/InteractionLogModel.php';
require_once APP_ROOT . '/models/EngagementScoreModel.php';
require_once APP_ROOT . '/models/AlertLogModel.php';
// Dùng models của Thành viên 2 để đọc quiz/attendance
require_once APP_ROOT . '/models/AttendanceRecordModel.php';
require_once APP_ROOT . '/models/AttendanceMethodModel.php';
require_once APP_ROOT . '/models/QuizSessionModel.php';
require_once APP_ROOT . '/models/QuizQuestionModel.php';

class StudentController
{
    private PDO                  $db;
    private QuizSubmissionModel  $subModel;
    private InteractionLogModel  $logModel;
    private EngagementScoreModel $engModel;
    private AlertLogModel        $alertModel;
    private AttendanceRecordModel $attRecordModel;
    private AttendanceMethodModel $attMethodModel;
    private QuizSessionModel     $quizSessionModel;
    private QuizQuestionModel    $quizQuestionModel;

    public function __construct()
    {
        Middleware::requireStudent();
        $this->db                = Database::getInstance()->getConnection();
        $this->subModel          = new QuizSubmissionModel();
        $this->logModel          = new InteractionLogModel();
        $this->engModel          = new EngagementScoreModel();
        $this->alertModel        = new AlertLogModel();
        $this->attRecordModel    = new AttendanceRecordModel();
        $this->attMethodModel    = new AttendanceMethodModel();
        $this->quizSessionModel  = new QuizSessionModel();
        $this->quizQuestionModel = new QuizQuestionModel();
    }

    // ══════════════════════════════════════════════════════
    // ATTENDANCE – Xem lịch sử điểm danh
    // GET /student/attendance.php?course_id=X
    // ══════════════════════════════════════════════════════
    public function attendance(): void
    {
        $studentId = Middleware::user()['id'];
        $courseId  = (int) ($_GET['course_id'] ?? 0);

        // Lấy danh sách course student đang học
        $courses = $this->getEnrolledCourses($studentId);

        // Nếu chưa chọn course, dùng course đầu tiên
        if ($courseId === 0 && !empty($courses)) {
            $courseId = (int) $courses[0]['id'];
        }

        // Lấy course hiện tại
        $currentCourse = null;
        foreach ($courses as $c) {
            if ((int) $c['id'] === $courseId) {
                $currentCourse = $c;
                break;
            }
        }

        $records    = [];
        $stats      = [];
        if ($courseId > 0) {
            // Lấy danh sách buổi học + trạng thái điểm danh
            $stmt = $this->db->prepare(
                'SELECT cs.id AS session_id, cs.session_date, cs.start_time, cs.end_time,
                        cs.title, cs.status AS session_status,
                        ar.status AS attendance_status, ar.checked_in_at, ar.note
                 FROM class_sessions cs
                 LEFT JOIN attendance_records ar
                        ON ar.session_id = cs.id AND ar.student_id = ?
                 WHERE cs.course_id = ?
                 ORDER BY cs.session_date DESC, cs.start_time DESC'
            );
            $stmt->execute([$studentId, $courseId]);
            $records = $stmt->fetchAll();

            $stats = $this->attRecordModel->getAttendanceStats($courseId, $studentId);
        }

        $pageTitle   = 'Attendance History';
        $currentPage = 'student.attendance';
        require_once APP_ROOT . '/views/student/attendance.php';
    }

    // ══════════════════════════════════════════════════════
    // CHECK-IN – Student điểm danh bằng OTP
    // POST /student/attendance.php (action=checkin)
    // ══════════════════════════════════════════════════════
    public function checkin(): void
    {
        $studentId = Middleware::user()['id'];
        $otp       = trim($_POST['otp_code'] ?? '');
        $sessionId = (int) ($_POST['session_id'] ?? 0);

        $result = ['success' => false, 'message' => ''];

        if (empty($otp) || $sessionId === 0) {
            $result['message'] = 'Vui lòng nhập mã OTP và chọn buổi học.';
            $this->jsonResponse($result);
            return;
        }

        // Kiểm tra đã điểm danh chưa
        $existing = $this->attRecordModel->getBySessionAndStudent($sessionId, $studentId);
        if ($existing && $existing['status'] === 'present') {
            $result['message'] = 'Bạn đã điểm danh buổi học này rồi.';
            $this->jsonResponse($result);
            return;
        }

        // Xác thực OTP
        $method = $this->attMethodModel->getActiveBySessionAndToken($sessionId, $otp);
        if (!$method) {
            $result['message'] = 'Mã OTP không hợp lệ hoặc đã hết hạn.';
            $this->jsonResponse($result);
            return;
        }

        // Ghi attendance_record
        if ($existing) {
            $this->attRecordModel->update($existing['id'], [
                'status'       => 'present',
                'method_id'    => $method['id'],
                'checked_in_at' => new DateTime(),
            ]);
            $recordId = $existing['id'];
        } else {
            $recordId = $this->attRecordModel->create(
                $sessionId, $studentId, 'present', $method['id'], new DateTime()
            );
        }

        // Ghi interaction_log (check_in)
        $course  = $this->getCourseBySession($sessionId);
        $attScore = $course ? (float) $course['attend_score'] : (float) DEFAULT_ATTEND_SCORE;

        $this->logModel->create(
            $studentId, $sessionId,
            InteractionLogModel::ACTION_CHECKIN,
            $recordId,
            'Check-in thành công',
            $attScore
        );

        // Recalculate engagement
        if ($course) {
            $this->engModel->recalculate($studentId, (int) $course['id']);
            $this->alertModel->generateAlerts($studentId, (int) $course['id']);
        }

        $result['success'] = true;
        $result['message'] = 'Điểm danh thành công!';
        $this->jsonResponse($result);
    }

    // ══════════════════════════════════════════════════════
    // QUIZ LIST – Danh sách quiz student có thể làm
    // GET /student/quiz.php?course_id=X
    // ══════════════════════════════════════════════════════
    public function quizList(): void
    {
        $studentId = Middleware::user()['id'];
        $courseId  = (int) ($_GET['course_id'] ?? 0);

        $courses = $this->getEnrolledCourses($studentId);
        if ($courseId === 0 && !empty($courses)) {
            $courseId = (int) $courses[0]['id'];
        }

        $quizzes = [];
        if ($courseId > 0) {
            // Lấy quiz open theo course + trạng thái đã nộp chưa
            $stmt = $this->db->prepare(
                'SELECT qs.id, qs.title, qs.description, qs.time_limit_minutes,
                        qs.status, qs.allow_retake, qs.created_at,
                        cs.session_date, cs.title AS session_title,
                        (SELECT COUNT(*) FROM quiz_questions WHERE quiz_id = qs.id) AS question_count,
                        (SELECT id FROM quiz_submissions
                         WHERE quiz_id = qs.id AND student_id = ? LIMIT 1) AS submission_id,
                        (SELECT total_score FROM quiz_submissions
                         WHERE quiz_id = qs.id AND student_id = ? LIMIT 1) AS my_score,
                        (SELECT max_score FROM quiz_submissions
                         WHERE quiz_id = qs.id AND student_id = ? LIMIT 1) AS max_score
                 FROM quiz_sessions qs
                 JOIN class_sessions cs ON qs.session_id = cs.id
                 WHERE cs.course_id = ?
                 ORDER BY cs.session_date DESC, qs.created_at DESC'
            );
            $stmt->execute([$studentId, $studentId, $studentId, $courseId]);
            $quizzes = $stmt->fetchAll();
        }

        $pageTitle   = 'My Quizzes';
        $currentPage = 'student.quiz';
        require_once APP_ROOT . '/views/student/quiz.php';
    }

    // ══════════════════════════════════════════════════════
    // QUIZ TAKE – Giao diện làm quiz
    // GET /student/quiz.php?action=take&quiz_id=X
    // ══════════════════════════════════════════════════════
    public function quizTake(): void
    {
        $studentId = Middleware::user()['id'];
        $quizId    = (int) ($_GET['quiz_id'] ?? 0);

        $quiz = $this->quizSessionModel->getById($quizId);
        if (!$quiz || $quiz['status'] !== 'open') {
            header('Location: ' . APP_URL . '/student/quiz.php');
            exit;
        }

        // Kiểm tra đã nộp chưa (allow_retake = 0)
        if (!$quiz['allow_retake'] && $this->subModel->hasSubmitted($quizId, $studentId)) {
            header('Location: ' . APP_URL . '/student/quiz.php?msg=already_submitted');
            exit;
        }

        $questions = $this->quizQuestionModel->getByQuizId($quizId);

        $pageTitle   = 'Take Quiz: ' . htmlspecialchars($quiz['title']);
        $currentPage = 'student.quiz';
        require_once APP_ROOT . '/views/student/quiz_take.php';
    }

    // ══════════════════════════════════════════════════════
    // QUIZ SUBMIT – Student nộp bài
    // POST /student/quiz.php (action=submit)
    // ══════════════════════════════════════════════════════
    public function quizSubmit(): void
    {
        $studentId = Middleware::user()['id'];
        $quizId    = (int) ($_POST['quiz_id'] ?? 0);

        $result = ['success' => false, 'message' => '', 'score' => 0, 'max' => 0];

        $quiz = $this->quizSessionModel->getById($quizId);
        if (!$quiz || $quiz['status'] !== 'open') {
            $result['message'] = 'Quiz không tồn tại hoặc đã đóng.';
            $this->jsonResponse($result);
            return;
        }

        if (!$quiz['allow_retake'] && $this->subModel->hasSubmitted($quizId, $studentId)) {
            $result['message'] = 'Bạn đã nộp bài quiz này rồi.';
            $this->jsonResponse($result);
            return;
        }

        // Lấy answers từ POST: answers[question_id] = 'A'/'B'/'C'/'D'
        $rawAnswers = $_POST['answers'] ?? [];
        $answers    = [];
        foreach ($rawAnswers as $qId => $opt) {
            $answers[(int) $qId] = strtoupper(trim($opt));
        }

        if (empty($answers)) {
            $result['message'] = 'Bạn chưa trả lời câu hỏi nào.';
            $this->jsonResponse($result);
            return;
        }

        // Tạo submission (auto-grading bên trong model)
        $submissionId = $this->subModel->create($quizId, $studentId, $answers);
        $submission   = $this->subModel->getById($submissionId);

        // Ghi interaction_log (submit_quiz)
        $course   = $this->getCourseByQuiz($quizId);
        $quizScore = $course ? (float) $course['quiz_correct_score'] : (float) DEFAULT_QUIZ_CORRECT_SCORE;

        $this->logModel->create(
            $studentId,
            (int) $quiz['session_id'],
            InteractionLogModel::ACTION_SUBMIT_QUIZ,
            $submissionId,
            'Nộp quiz: ' . $quiz['title'],
            (float) $submission['total_score'] * $quizScore
        );

        // Recalculate engagement + check alerts
        if ($course) {
            $this->engModel->recalculate($studentId, (int) $course['id']);
            $this->alertModel->generateAlerts($studentId, (int) $course['id']);
        }

        $result['success'] = true;
        $result['message'] = 'Nộp bài thành công!';
        $result['score']   = $submission['total_score'];
        $result['max']     = $submission['max_score'];
        $this->jsonResponse($result);
    }

    // ══════════════════════════════════════════════════════
    // SCORES – Xem điểm engagement tổng hợp
    // GET /student/scores.php
    // ══════════════════════════════════════════════════════
    public function scores(): void
    {
        $studentId   = Middleware::user()['id'];
        $engagements = $this->engModel->getByStudentId($studentId);
        $quizHistory = $this->subModel->getByStudentId($studentId);

        $pageTitle   = 'My Scores & Engagement';
        $currentPage = 'student.scores';
        require_once APP_ROOT . '/views/student/scores.php';
    }

    // ══════════════════════════════════════════════════════
    // ALERTS – Xem cảnh báo cá nhân
    // GET /student/alerts.php
    // ══════════════════════════════════════════════════════
    public function alerts(): void
    {
        $studentId = Middleware::user()['id'];
        $alerts    = $this->alertModel->getByStudentId($studentId);

        $pageTitle   = 'My Alerts';
        $currentPage = 'student.alerts';
        require_once APP_ROOT . '/views/student/alerts.php';
    }

    // ══════════════════════════════════════════════════════
    // PRIVATE HELPERS
    // ══════════════════════════════════════════════════════

    private function getEnrolledCourses(int $studentId): array
    {
        $stmt = $this->db->prepare(
            'SELECT c.id, c.course_code, c.course_name, c.semester
             FROM courses c
             JOIN course_enrollments ce ON ce.course_id = c.id
             WHERE ce.user_id = ? AND ce.role = "student" AND c.is_active = 1
             ORDER BY c.course_name ASC'
        );
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }

    private function getCourseBySession(int $sessionId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT c.* FROM courses c
             JOIN class_sessions cs ON cs.course_id = c.id
             WHERE cs.id = ? LIMIT 1'
        );
        $stmt->execute([$sessionId]);
        return $stmt->fetch() ?: null;
    }

    private function getCourseByQuiz(int $quizId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT c.* FROM courses c
             JOIN class_sessions cs ON cs.course_id = c.id
             JOIN quiz_sessions qs  ON qs.session_id = cs.id
             WHERE qs.id = ? LIMIT 1'
        );
        $stmt->execute([$quizId]);
        return $stmt->fetch() ?: null;
    }

    private function jsonResponse(array $data): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}