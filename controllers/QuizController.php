<?php
// ============================================================
// controllers/QuizController.php
// Xử lý logic quiz (sessions + questions)
// ============================================================

require_once APP_ROOT . '/config/Database.php';
require_once APP_ROOT . '/models/QuizSessionModel.php';
require_once APP_ROOT . '/models/QuizQuestionModel.php';
require_once APP_ROOT . '/helpers/Middleware.php';

class QuizController
{
    private PDO $db;
    private QuizSessionModel $quizSessionModel;
    private QuizQuestionModel $quizQuestionModel;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->quizSessionModel = new QuizSessionModel();
        $this->quizQuestionModel = new QuizQuestionModel();
    }

    // ══════════════════════════════════════════════════════════════
    // QUIZ SESSIONS – Quản lý phiên quiz
    // ══════════════════════════════════════════════════════════════

    // ──────────────────────────────────────────────────────
    // GET /teacher/quiz/sessions?session_id=X
    // Hiển thị danh sách quiz của 1 buổi học
    // ──────────────────────────────────────────────────────
    public function listSessions(): void
    {
        Middleware::teacher();

        $sessionId = (int) ($_GET['session_id'] ?? 0);
        if (!$sessionId) {
            $_SESSION['error'] = 'Session không hợp lệ';
            header('Location: ' . APP_URL . '/teacher/dashboard.php');
            exit;
        }

        $this->verifyTeacherOwnsSession($sessionId);

        $quizzes = $this->quizSessionModel->getBySessionId($sessionId);
        $session = $this->getSessionInfo($sessionId);

        // Thêm thông tin số câu hỏi cho mỗi quiz
        foreach ($quizzes as &$quiz) {
            $quiz['question_count'] = $this->quizSessionModel->getQuestionCount($quiz['id']);
            $quiz['max_score'] = $this->quizSessionModel->getTotalMaxScore($quiz['id']);
        }

        require_once APP_ROOT . '/views/teacher/quiz/sessions_list.php';
    }

    // ──────────────────────────────────────────────────────
    // GET & POST /teacher/quiz/sessions_form.php
    // Form tạo hoặc sửa quiz
    // ──────────────────────────────────────────────────────
    public function sessionsForm(): void
    {
        Middleware::teacher();

        $error = null;
        $success = null;
        $quiz = null;
        $sessionId = (int) ($_GET['session_id'] ?? $_POST['session_id'] ?? 0);
        $quizId = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

        if (!$sessionId) {
            $_SESSION['error'] = 'Session không hợp lệ';
            header('Location: ' . APP_URL . '/teacher/dashboard.php');
            exit;
        }

        $this->verifyTeacherOwnsSession($sessionId);
        $session = $this->getSessionInfo($sessionId);

        // Edit mode
        if ($quizId) {
            $quiz = $this->quizSessionModel->getByIdWithSession($quizId);
            if (!$quiz || $quiz['session_id'] != $sessionId) {
                $_SESSION['error'] = 'Quiz không tồn tại';
                header("Location: " . APP_URL . "/teacher/quiz/sessions_list.php?session_id=$sessionId");
                exit;
            }
        }

        // Handle POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $timeLimitMinutes = !empty($_POST['time_limit_minutes']) ? (int) $_POST['time_limit_minutes'] : null;
            $allowRetake = isset($_POST['allow_retake']) ? 1 : 0;

            // Validate
            if (empty($title)) {
                $error = 'Tiêu đề quiz bắt buộc';
            } elseif ($timeLimitMinutes !== null && $timeLimitMinutes <= 0) {
                $error = 'Thời gian phải > 0 phút';
            } else {
                if ($quizId) {
                    // Update
                    $this->quizSessionModel->update($quizId, [
                        'title' => $title,
                        'description' => $description,
                        'time_limit_minutes' => $timeLimitMinutes,
                        'allow_retake' => $allowRetake
                    ]);
                    $success = 'Cập nhật quiz thành công!';
                } else {
                    // Create
                    $this->quizSessionModel->create(
                        $sessionId,
                        $title,
                        $description,
                        $timeLimitMinutes,
                        'draft',
                        $allowRetake
                    );
                    $success = 'Tạo quiz thành công!';
                }

                $_SESSION['success'] = $success;
                header("Location: " . APP_URL . "/teacher/quiz/sessions_list.php?session_id=$sessionId");
                exit;
            }
        }

        require_once APP_ROOT . '/views/teacher/quiz/sessions_form.php';
    }

    // ──────────────────────────────────────────────────────
    // POST /teacher/quiz/update_status.php
    // Cập nhật trạng thái quiz (draft → open → closed)
    // ──────────────────────────────────────────────────────
    public function updateStatus(): void
    {
        Middleware::teacher();

        $quizId = (int) ($_POST['id'] ?? 0);
        $sessionId = (int) ($_POST['session_id'] ?? 0);
        $status = trim($_POST['status'] ?? '');

        if (!$quizId || !$sessionId) {
            $_SESSION['error'] = 'Dữ liệu không hợp lệ';
            header('Location: ' . APP_URL . '/teacher/dashboard.php');
            exit;
        }

        $quiz = $this->quizSessionModel->getById($quizId);
        if (!$quiz || $quiz['session_id'] != $sessionId) {
            $_SESSION['error'] = 'Quiz không tồn tại';
            header('Location: ' . APP_URL . '/teacher/dashboard.php');
            exit;
        }

        $this->verifyTeacherOwnsSession($sessionId);

        // Validate status
        if (!in_array($status, ['draft', 'open', 'closed'])) {
            $_SESSION['error'] = 'Trạng thái không hợp lệ';
            header("Location: " . APP_URL . "/teacher/quiz/sessions_list.php?session_id=$sessionId");
            exit;
        }

        // If opening, check has questions
        if ($status === 'open' && !$this->quizSessionModel->hasQuestions($quizId)) {
            $_SESSION['error'] = 'Quiz phải có ít nhất 1 câu hỏi trước khi mở';
            header("Location: " . APP_URL . "/teacher/quiz/sessions_list.php?session_id=$sessionId");
            exit;
        }

        if ($this->quizSessionModel->updateStatus($quizId, $status)) {
            $_SESSION['success'] = "Cập nhật trạng thái thành công!";
        } else {
            $_SESSION['error'] = 'Cập nhật thất bại';
        }

        header("Location: " . APP_URL . "/teacher/quiz/sessions_list.php?session_id=$sessionId");
        exit;
    }

    // ──────────────────────────────────────────────────────
    // POST /teacher/quiz/delete_session.php
    // Xóa quiz
    // ──────────────────────────────────────────────────────
    public function deleteSession(): void
    {
        Middleware::teacher();

        $quizId = (int) ($_POST['id'] ?? 0);
        $sessionId = (int) ($_POST['session_id'] ?? 0);

        if (!$quizId || !$sessionId) {
            $_SESSION['error'] = 'Dữ liệu không hợp lệ';
            header('Location: ' . APP_URL . '/teacher/dashboard.php');
            exit;
        }

        $quiz = $this->quizSessionModel->getById($quizId);
        if (!$quiz || $quiz['session_id'] != $sessionId) {
            $_SESSION['error'] = 'Quiz không tồn tại';
            header('Location: ' . APP_URL . '/teacher/dashboard.php');
            exit;
        }

        $this->verifyTeacherOwnsSession($sessionId);

        if ($this->quizSessionModel->delete($quizId)) {
            $_SESSION['success'] = 'Xóa quiz thành công!';
        } else {
            $_SESSION['error'] = 'Xóa quiz thất bại';
        }

        header("Location: " . APP_URL . "/teacher/quiz/sessions_list.php?session_id=$sessionId");
        exit;
    }

    // ══════════════════════════════════════════════════════════════
    // QUIZ QUESTIONS – Quản lý câu hỏi
    // ══════════════════════════════════════════════════════════════

    // ──────────────────────────────────────────────────────
    // GET /teacher/quiz/questions?quiz_id=X
    // Hiển thị danh sách câu hỏi của 1 quiz
    // ──────────────────────────────────────────────────────
    public function listQuestions(): void
    {
        Middleware::teacher();

        $quizId = (int) ($_GET['quiz_id'] ?? 0);
        if (!$quizId) {
            $_SESSION['error'] = 'Quiz không hợp lệ';
            header('Location: ' . APP_URL . '/teacher/dashboard.php');
            exit;
        }

        $quiz = $this->quizSessionModel->getByIdWithSession($quizId);
        if (!$quiz) {
            $_SESSION['error'] = 'Quiz không tồn tại';
            header('Location: ' . APP_URL . '/teacher/dashboard.php');
            exit;
        }

        $this->verifyTeacherOwnsSession($quiz['session_id']);

        $questions = $this->quizQuestionModel->getByQuizId($quizId);

        require_once APP_ROOT . '/views/teacher/quiz/questions_list.php';
    }

    // ──────────────────────────────────────────────────────
    // GET & POST /teacher/quiz/questions_form.php
    // Form tạo hoặc sửa câu hỏi
    // ──────────────────────────────────────────────────────
    public function questionsForm(): void
    {
        Middleware::teacher();

        $error = null;
        $success = null;
        $question = null;
        $quizId = (int) ($_GET['quiz_id'] ?? $_POST['quiz_id'] ?? 0);
        $questionId = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

        if (!$quizId) {
            $_SESSION['error'] = 'Quiz không hợp lệ';
            header('Location: ' . APP_URL . '/teacher/dashboard.php');
            exit;
        }

        $quiz = $this->quizSessionModel->getByIdWithSession($quizId);
        if (!$quiz) {
            $_SESSION['error'] = 'Quiz không tồn tại';
            header('Location: ' . APP_URL . '/teacher/dashboard.php');
            exit;
        }

        $this->verifyTeacherOwnsSession($quiz['session_id']);

        // Edit mode
        if ($questionId) {
            $question = $this->quizQuestionModel->getById($questionId);
            if (!$question || $question['quiz_id'] != $quizId) {
                $_SESSION['error'] = 'Câu hỏi không tồn tại';
                header("Location: " . APP_URL . "/teacher/quiz/questions_list.php?quiz_id=$quizId");
                exit;
            }
        }

        // Handle POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $questionText = trim($_POST['question_text'] ?? '');
            $optionA = trim($_POST['option_a'] ?? '');
            $optionB = trim($_POST['option_b'] ?? '');
            $optionC = trim($_POST['option_c'] ?? '');
            $optionD = trim($_POST['option_d'] ?? '');
            $correctOption = trim($_POST['correct_option'] ?? 'A');
            $points = (float) ($_POST['points'] ?? 1.0);

            // Validate
            if (empty($questionText)) {
                $error = 'Nội dung câu hỏi bắt buộc';
            } elseif (empty($optionA) || empty($optionB)) {
                $error = 'Ít nhất phải có 2 đáp án (A, B)';
            } elseif (!in_array($correctOption, ['A', 'B', 'C', 'D'])) {
                $error = 'Đáp án đúng không hợp lệ';
            } elseif ($correctOption === 'C' && empty($optionC)) {
                $error = 'Đáp án C không được để trống nếu nó là đáp án đúng';
            } elseif ($correctOption === 'D' && empty($optionD)) {
                $error = 'Đáp án D không được để trống nếu nó là đáp án đúng';
            } elseif ($points <= 0) {
                $error = 'Điểm phải > 0';
            } else {
                if ($questionId) {
                    // Update
                    $this->quizQuestionModel->update($questionId, [
                        'question_text' => $questionText,
                        'option_a' => $optionA,
                        'option_b' => $optionB,
                        'option_c' => $optionC ?: null,
                        'option_d' => $optionD ?: null,
                        'correct_option' => $correctOption,
                        'points' => $points
                    ]);
                    $success = 'Cập nhật câu hỏi thành công!';
                } else {
                    // Create
                    $nextOrder = $this->quizQuestionModel->getNextOrderNum($quizId);
                    $this->quizQuestionModel->create(
                        $quizId,
                        $questionText,
                        $optionA,
                        $optionB,
                        $optionC ?: null,
                        $optionD ?: null,
                        $correctOption,
                        $points,
                        $nextOrder
                    );
                    $success = 'Thêm câu hỏi thành công!';
                }

                $_SESSION['success'] = $success;
                header("Location: " . APP_URL . "/teacher/quiz/questions_list.php?quiz_id=$quizId");
                exit;
            }
        }

        require_once APP_ROOT . '/views/teacher/quiz/questions_form.php';
    }

    // ──────────────────────────────────────────────────────
    // POST /teacher/quiz/delete_question.php
    // Xóa câu hỏi
    // ──────────────────────────────────────────────────────
    public function deleteQuestion(): void
    {
        Middleware::teacher();

        $questionId = (int) ($_POST['id'] ?? 0);
        $quizId = (int) ($_POST['quiz_id'] ?? 0);

        if (!$questionId || !$quizId) {
            $_SESSION['error'] = 'Dữ liệu không hợp lệ';
            header('Location: ' . APP_URL . '/teacher/dashboard.php');
            exit;
        }

        $question = $this->quizQuestionModel->getById($questionId);
        if (!$question || $question['quiz_id'] != $quizId) {
            $_SESSION['error'] = 'Câu hỏi không tồn tại';
            header('Location: ' . APP_URL . '/teacher/dashboard.php');
            exit;
        }

        $quiz = $this->quizSessionModel->getById($quizId);
        if (!$quiz) {
            $_SESSION['error'] = 'Quiz không tồn tại';
            header('Location: ' . APP_URL . '/teacher/dashboard.php');
            exit;
        }

        $this->verifyTeacherOwnsSession($quiz['session_id']);

        if ($this->quizQuestionModel->delete($questionId)) {
            $this->quizQuestionModel->reorderByQuizId($quizId);
            $_SESSION['success'] = 'Xóa câu hỏi thành công!';
        } else {
            $_SESSION['error'] = 'Xóa câu hỏi thất bại';
        }

        header("Location: " . APP_URL . "/teacher/quiz/questions_list.php?quiz_id=$quizId");
        exit;
    }

    // ──────────────────────────────────────────────────────
    // HELPER – Verify teacher owns session
    // ──────────────────────────────────────────────────────
    private function verifyTeacherOwnsSession(int $sessionId): void
    {
        $stmt = $this->db->prepare(
            'SELECT teacher_id FROM class_sessions WHERE id = ?'
        );
        $stmt->execute([$sessionId]);
        $session = $stmt->fetch();

        if (!$session || $session['teacher_id'] != $_SESSION['user_id']) {
            $_SESSION['error'] = 'Bạn không có quyền truy cập';
            header('Location: ' . APP_URL . '/teacher/dashboard.php');
            exit;
        }
    }

    // ──────────────────────────────────────────────────────
    // HELPER – Lấy thông tin session
    // ──────────────────────────────────────────────────────
    private function getSessionInfo(int $sessionId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT cs.id, cs.session_date, cs.start_time, cs.end_time, cs.title, cs.status,
                    c.course_code, c.course_name
             FROM class_sessions cs
             JOIN courses c ON cs.course_id = c.id
             WHERE cs.id = ?'
        );
        $stmt->execute([$sessionId]);
        return $stmt->fetch() ?: null;
    }
}
