<?php
// ============================================================
// models/InteractionLogModel.php
// Ghi nhận mọi hành động tương tác của sinh viên
// Repository Pattern – CRUD + filter helpers
// Thành viên 3 phụ trách
// ============================================================

require_once APP_ROOT . '/config/Database.php';

class InteractionLogModel
{
    private PDO $db;

    // Action types hợp lệ (theo ENUM trong schema)
    public const ACTION_CHECKIN       = 'check_in';
    public const ACTION_SUBMIT_QUIZ   = 'submit_quiz';
    public const ACTION_ANSWER        = 'answer_question';
    public const ACTION_DISCUSSION    = 'discussion';
    public const ACTION_OTHER         = 'other';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    // ──────────────────────────────────────────────────────
    // CREATE – Ghi 1 log tương tác
    // ──────────────────────────────────────────────────────
    public function create(
        int     $userId,
        int     $sessionId,
        string  $actionType,
        ?int    $referenceId   = null,
        ?string $description   = null,
        float   $pointsEarned  = 0.0
    ): int {
        $stmt = $this->db->prepare(
            'INSERT INTO interaction_logs
                (user_id, session_id, action_type, reference_id, description, points_earned)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $userId,
            $sessionId,
            $actionType,
            $referenceId,
            $description,
            $pointsEarned,
        ]);
        return (int) $this->db->lastInsertId();
    }

    // ──────────────────────────────────────────────────────
    // READ – Lấy 1 log theo ID
    // ──────────────────────────────────────────────────────
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, user_id, session_id, action_type, reference_id,
                    description, points_earned, created_at
             FROM interaction_logs WHERE id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    // ──────────────────────────────────────────────────────
    // READ – Lấy logs của 1 user trong 1 session
    // ──────────────────────────────────────────────────────
    public function getByUserAndSession(int $userId, int $sessionId): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, user_id, session_id, action_type, reference_id,
                    description, points_earned, created_at
             FROM interaction_logs
             WHERE user_id = ? AND session_id = ?
             ORDER BY created_at ASC'
        );
        $stmt->execute([$userId, $sessionId]);
        return $stmt->fetchAll();
    }

    // ──────────────────────────────────────────────────────
    // READ – Lấy toàn bộ logs của 1 user trong 1 course
    // ──────────────────────────────────────────────────────
    public function getByUserAndCourse(int $userId, int $courseId): array
    {
        $stmt = $this->db->prepare(
            'SELECT il.id, il.user_id, il.session_id, il.action_type,
                    il.reference_id, il.description, il.points_earned, il.created_at,
                    cs.session_date, cs.title AS session_title
             FROM interaction_logs il
             JOIN class_sessions cs ON il.session_id = cs.id
             WHERE il.user_id = ? AND cs.course_id = ?
             ORDER BY il.created_at DESC'
        );
        $stmt->execute([$userId, $courseId]);
        return $stmt->fetchAll();
    }

    // ──────────────────────────────────────────────────────
    // READ – Lấy logs theo session (Teacher xem toàn lớp)
    // ──────────────────────────────────────────────────────
    public function getBySessionId(int $sessionId): array
    {
        $stmt = $this->db->prepare(
            'SELECT il.id, il.user_id, il.action_type, il.description,
                    il.points_earned, il.created_at,
                    u.full_name, u.student_code
             FROM interaction_logs il
             JOIN users u ON il.user_id = u.id
             WHERE il.session_id = ?
             ORDER BY il.created_at DESC'
        );
        $stmt->execute([$sessionId]);
        return $stmt->fetchAll();
    }

    // ──────────────────────────────────────────────────────
    // UPDATE – Cập nhật log (hiếm dùng, nhưng cần cho CRUD)
    // ──────────────────────────────────────────────────────
    public function update(int $id, array $data): bool
    {
        $allowed = ['description', 'points_earned'];
        $updates = [];
        $values  = [];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $updates[] = "$field = ?";
                $values[]  = $data[$field];
            }
        }

        if (empty($updates)) {
            return false;
        }

        $values[] = $id;
        $sql = 'UPDATE interaction_logs SET ' . implode(', ', $updates) . ' WHERE id = ?';
        return $this->db->prepare($sql)->execute($values);
    }

    // ──────────────────────────────────────────────────────
    // DELETE – Xóa log
    // ──────────────────────────────────────────────────────
    public function delete(int $id): bool
    {
        return $this->db->prepare('DELETE FROM interaction_logs WHERE id = ?')
                        ->execute([$id]);
    }

    // ──────────────────────────────────────────────────────
    // HELPER – Tổng điểm interaction của student trong course
    // ──────────────────────────────────────────────────────
    public function getTotalPointsByCourse(int $userId, int $courseId): float
    {
        $stmt = $this->db->prepare(
            'SELECT COALESCE(SUM(il.points_earned), 0)
             FROM interaction_logs il
             JOIN class_sessions cs ON il.session_id = cs.id
             WHERE il.user_id = ? AND cs.course_id = ?'
        );
        $stmt->execute([$userId, $courseId]);
        return (float) $stmt->fetchColumn();
    }

    // ──────────────────────────────────────────────────────
    // HELPER – Kiểm tra student đã check-in session chưa
    // ──────────────────────────────────────────────────────
    public function hasCheckedIn(int $userId, int $sessionId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM interaction_logs
             WHERE user_id = ? AND session_id = ? AND action_type = ?'
        );
        $stmt->execute([$userId, $sessionId, self::ACTION_CHECKIN]);
        return (int) $stmt->fetchColumn() > 0;
    }
}