<?php
// ============================================================
// models/QuizSessionModel.php
// Quản lý phiên quiz trong buổi học
// Repository Pattern – CRUD + helper methods
// ============================================================

require_once APP_ROOT . '/config/Database.php';

class QuizSessionModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    // ──────────────────────────────────────────────────────
    // CREATE – Tạo phiên quiz
    // ──────────────────────────────────────────────────────
    public function create(
        int $sessionId,
        string $title,
        ?string $description = null,
        ?int $timeLimitMinutes = null,
        string $status = 'draft',
        bool $allowRetake = false
    ): int {
        $stmt = $this->db->prepare(
            'INSERT INTO quiz_sessions (session_id, title, description, time_limit_minutes, status, allow_retake)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $sessionId,
            $title,
            $description,
            $timeLimitMinutes,
            $status,
            $allowRetake ? 1 : 0
        ]);
        return (int) $this->db->lastInsertId();
    }

    // ──────────────────────────────────────────────────────
    // READ – Lấy 1 quiz theo ID
    // ──────────────────────────────────────────────────────
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, session_id, title, description, time_limit_minutes, status, allow_retake, created_at, updated_at
             FROM quiz_sessions WHERE id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    // ──────────────────────────────────────────────────────
    // READ – Lấy tất cả quiz theo session
    // ──────────────────────────────────────────────────────
    public function getBySessionId(int $sessionId): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, session_id, title, description, time_limit_minutes, status, allow_retake, created_at, updated_at
             FROM quiz_sessions WHERE session_id = ? ORDER BY created_at DESC'
        );
        $stmt->execute([$sessionId]);
        return $stmt->fetchAll();
    }

    // ──────────────────────────────────────────────────────
    // READ – Lấy quiz với thông tin session + course
    // ──────────────────────────────────────────────────────
    public function getByIdWithSession(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT 
                qs.id, qs.session_id, qs.title, qs.description, qs.time_limit_minutes, 
                qs.status, qs.allow_retake, qs.created_at, qs.updated_at,
                cs.session_date, cs.start_time, cs.end_time,
                c.course_name, c.course_code
             FROM quiz_sessions qs
             JOIN class_sessions cs ON qs.session_id = cs.id
             JOIN courses c ON cs.course_id = c.id
             WHERE qs.id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    // ──────────────────────────────────────────────────────
    // UPDATE – Cập nhật quiz
    // ──────────────────────────────────────────────────────
    public function update(int $id, array $data): bool
    {
        $allowedFields = ['title', 'description', 'time_limit_minutes', 'status', 'allow_retake'];
        $updates = [];
        $values = [];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                $value = $data[$field];
                if ($field === 'allow_retake') {
                    $value = $value ? 1 : 0;
                }
                $values[] = $value;
            }
        }

        if (empty($updates)) {
            return false;
        }

        $values[] = $id;
        $sql = 'UPDATE quiz_sessions SET ' . implode(', ', $updates) . ', updated_at = NOW() WHERE id = ?';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }

    // ──────────────────────────────────────────────────────
    // DELETE – Xóa quiz (cascade sẽ xóa questions)
    // ──────────────────────────────────────────────────────
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM quiz_sessions WHERE id = ?');
        return $stmt->execute([$id]);
    }

    // ──────────────────────────────────────────────────────
    // HELPER – Cập nhật status (draft → open → closed)
    // ──────────────────────────────────────────────────────
    public function updateStatus(int $id, string $status): bool
    {
        $validStatuses = ['draft', 'open', 'closed'];
        if (!in_array($status, $validStatuses)) {
            return false;
        }
        return $this->update($id, ['status' => $status]);
    }

    // ──────────────────────────────────────────────────────
    // HELPER – Lấy số lượng câu hỏi của quiz
    // ──────────────────────────────────────────────────────
    public function getQuestionCount(int $id): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) as count FROM quiz_questions WHERE quiz_id = ?'
        );
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }

    // ──────────────────────────────────────────────────────
    // HELPER – Lấy tổng điểm tối đa của quiz
    // ──────────────────────────────────────────────────────
    public function getTotalMaxScore(int $id): float
    {
        $stmt = $this->db->prepare(
            'SELECT SUM(points) as total FROM quiz_questions WHERE quiz_id = ?'
        );
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return (float) ($result['total'] ?? 0);
    }

    // ──────────────────────────────────────────────────────
    // HELPER – Lấy tất cả quiz open trong course
    // ──────────────────────────────────────────────────────
    public function getOpenQuizzesByCoursId(int $courseId): array
    {
        $stmt = $this->db->prepare(
            'SELECT qs.id, qs.title, qs.session_id, cs.session_date
             FROM quiz_sessions qs
             JOIN class_sessions cs ON qs.session_id = cs.id
             WHERE cs.course_id = ? AND qs.status = "open"
             ORDER BY cs.session_date DESC'
        );
        $stmt->execute([$courseId]);
        return $stmt->fetchAll();
    }

    // ──────────────────────────────────────────────────────
    // HELPER – Kiểm tra quiz có câu hỏi chưa
    // ──────────────────────────────────────────────────────
    public function hasQuestions(int $id): bool
    {
        return $this->getQuestionCount($id) > 0;
    }
}
