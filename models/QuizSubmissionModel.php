<?php
// ============================================================
// models/QuizSubmissionModel.php
// Quản lý bài nộp quiz của sinh viên
// Repository Pattern – CRUD + auto-grading logic
// Thành viên 3 phụ trách
// ============================================================

require_once APP_ROOT . '/config/Database.php';

class QuizSubmissionModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    // ──────────────────────────────────────────────────────
    // CREATE – Tạo bài nộp quiz (auto-grading)
    // $answers = ['question_id' => 'A'/'B'/'C'/'D', ...]
    // ──────────────────────────────────────────────────────
    public function create(int $quizId, int $studentId, array $answers): int
    {
        // Lấy toàn bộ câu hỏi của quiz để chấm điểm
        $stmt = $this->db->prepare(
            'SELECT id, correct_option, points FROM quiz_questions WHERE quiz_id = ?'
        );
        $stmt->execute([$quizId]);
        $questions = $stmt->fetchAll();

        $totalScore = 0.0;
        $maxScore   = 0.0;

        foreach ($questions as $q) {
            $maxScore += (float) $q['points'];
            $chosen    = strtoupper($answers[$q['id']] ?? '');
            if ($chosen === $q['correct_option']) {
                $totalScore += (float) $q['points'];
            }
        }

        $stmt = $this->db->prepare(
            'INSERT INTO quiz_submissions (quiz_id, student_id, total_score, max_score, answers, submitted_at)
             VALUES (?, ?, ?, ?, ?, NOW())'
        );
        $stmt->execute([
            $quizId,
            $studentId,
            $totalScore,
            $maxScore,
            json_encode($answers, JSON_UNESCAPED_UNICODE),
        ]);

        return (int) $this->db->lastInsertId();
    }

    // ──────────────────────────────────────────────────────
    // READ – Lấy 1 bài nộp theo ID
    // ──────────────────────────────────────────────────────
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, quiz_id, student_id, total_score, max_score, answers, submitted_at
             FROM quiz_submissions WHERE id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            $row['answers'] = json_decode($row['answers'] ?? '{}', true);
        }
        return $row ?: null;
    }

    // ──────────────────────────────────────────────────────
    // READ – Lấy bài nộp của 1 student trong 1 quiz
    // ──────────────────────────────────────────────────────
    public function getByQuizAndStudent(int $quizId, int $studentId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, quiz_id, student_id, total_score, max_score, answers, submitted_at
             FROM quiz_submissions WHERE quiz_id = ? AND student_id = ? LIMIT 1'
        );
        $stmt->execute([$quizId, $studentId]);
        $row = $stmt->fetch();
        if ($row) {
            $row['answers'] = json_decode($row['answers'] ?? '{}', true);
        }
        return $row ?: null;
    }

    // ──────────────────────────────────────────────────────
    // READ – Lấy toàn bộ bài nộp của 1 student
    // ──────────────────────────────────────────────────────
    public function getByStudentId(int $studentId): array
    {
        $stmt = $this->db->prepare(
            'SELECT qs_sub.id, qs_sub.quiz_id, qs_sub.total_score, qs_sub.max_score,
                    qs_sub.submitted_at,
                    qs.title AS quiz_title, qs.status AS quiz_status,
                    cs.session_date, c.course_name, c.course_code
             FROM quiz_submissions qs_sub
             JOIN quiz_sessions qs    ON qs_sub.quiz_id    = qs.id
             JOIN class_sessions cs   ON qs.session_id     = cs.id
             JOIN courses c           ON cs.course_id      = c.id
             WHERE qs_sub.student_id = ?
             ORDER BY qs_sub.submitted_at DESC'
        );
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }

    // ──────────────────────────────────────────────────────
    // READ – Lấy toàn bộ bài nộp theo quiz (Teacher dùng)
    // ──────────────────────────────────────────────────────
    public function getByQuizId(int $quizId): array
    {
        $stmt = $this->db->prepare(
            'SELECT qs_sub.id, qs_sub.student_id, qs_sub.total_score, qs_sub.max_score,
                    qs_sub.submitted_at,
                    u.full_name, u.student_code
             FROM quiz_submissions qs_sub
             JOIN users u ON qs_sub.student_id = u.id
             WHERE qs_sub.quiz_id = ?
             ORDER BY qs_sub.total_score DESC'
        );
        $stmt->execute([$quizId]);
        return $stmt->fetchAll();
    }

    // ──────────────────────────────────────────────────────
    // UPDATE – Cập nhật điểm thủ công (nếu cần)
    // ──────────────────────────────────────────────────────
    public function update(int $id, array $data): bool
    {
        $allowed = ['total_score', 'answers'];
        $updates = [];
        $values  = [];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $updates[] = "$field = ?";
                $values[]  = ($field === 'answers')
                    ? json_encode($data[$field], JSON_UNESCAPED_UNICODE)
                    : $data[$field];
            }
        }

        if (empty($updates)) {
            return false;
        }

        $values[] = $id;
        $sql = 'UPDATE quiz_submissions SET ' . implode(', ', $updates) . ' WHERE id = ?';
        return $this->db->prepare($sql)->execute($values);
    }

    // ──────────────────────────────────────────────────────
    // DELETE – Xóa bài nộp
    // ──────────────────────────────────────────────────────
    public function delete(int $id): bool
    {
        return $this->db->prepare('DELETE FROM quiz_submissions WHERE id = ?')
                        ->execute([$id]);
    }

    // ──────────────────────────────────────────────────────
    // VALIDATE – Kiểm tra student đã nộp quiz chưa
    // ──────────────────────────────────────────────────────
    public function hasSubmitted(int $quizId, int $studentId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM quiz_submissions WHERE quiz_id = ? AND student_id = ?'
        );
        $stmt->execute([$quizId, $studentId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    // ──────────────────────────────────────────────────────
    // HELPER – Tổng điểm quiz của student trong 1 course
    // ──────────────────────────────────────────────────────
    public function getTotalQuizScoreByCourse(int $studentId, int $courseId): float
    {
        $stmt = $this->db->prepare(
            'SELECT COALESCE(SUM(qs_sub.total_score), 0)
             FROM quiz_submissions qs_sub
             JOIN quiz_sessions qs  ON qs_sub.quiz_id  = qs.id
             JOIN class_sessions cs ON qs.session_id   = cs.id
             WHERE qs_sub.student_id = ? AND cs.course_id = ?'
        );
        $stmt->execute([$studentId, $courseId]);
        return (float) $stmt->fetchColumn();
    }
}