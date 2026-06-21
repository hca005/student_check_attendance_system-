<?php
// ============================================================
// models/EngagementScoreModel.php  
// Tổng hợp và tính điểm tham gia lớp học
// Repository Pattern + Business Logic
// ============================================================

require_once APP_ROOT . '/config/Database.php';

class EngagementScoreModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    // ──────────────────────────────────────────────────────
    // CREATE
    // ──────────────────────────────────────────────────────
    public function create(int $studentId, int $courseId): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO engagement_scores (student_id, course_id,
               total_sessions, attended_sessions, total_quiz_score,
               total_interaction_points, engagement_index)
             VALUES (?, ?, 0, 0, 0, 0, 0)'
        );
        $stmt->execute([$studentId, $courseId]);
        return (int) $this->db->lastInsertId();
    }

    // ──────────────────────────────────────────────────────
    // READ – 1 student + 1 course
    // ──────────────────────────────────────────────────────
    public function getByStudentAndCourse(int $studentId, int $courseId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, student_id, course_id, total_sessions, attended_sessions,
                    total_quiz_score, total_interaction_points, engagement_index, calculated_at
             FROM engagement_scores
             WHERE student_id = ? AND course_id = ? LIMIT 1'
        );
        $stmt->execute([$studentId, $courseId]);
        return $stmt->fetch() ?: null;
    }

    // ──────────────────────────────────────────────────────
    // READ – Tất cả engagement của 1 student
    // ──────────────────────────────────────────────────────
    public function getByStudentId(int $studentId): array
    {
        $stmt = $this->db->prepare(
            'SELECT es.id, es.student_id, es.course_id,
                    es.total_sessions, es.attended_sessions,
                    es.total_quiz_score, es.total_interaction_points,
                    es.engagement_index, es.calculated_at,
                    c.course_name, c.course_code, c.semester
             FROM engagement_scores es
             JOIN courses c ON es.course_id = c.id
             WHERE es.student_id = ?
             ORDER BY es.engagement_index DESC'
        );
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }

    // ──────────────────────────────────────────────────────
    // READ – Tất cả engagement theo course (Teacher/Admin)
    // ──────────────────────────────────────────────────────
    public function getByCourseId(int $courseId): array
    {
        $stmt = $this->db->prepare(
            'SELECT es.id, es.student_id, es.total_sessions, es.attended_sessions,
                    es.total_quiz_score, es.total_interaction_points,
                    es.engagement_index, es.calculated_at,
                    u.full_name, u.student_code
             FROM engagement_scores es
             JOIN users u ON es.student_id = u.id
             WHERE es.course_id = ?
             ORDER BY es.engagement_index DESC'
        );
        $stmt->execute([$courseId]);
        return $stmt->fetchAll();
    }

    // ──────────────────────────────────────────────────────
    // UPDATE
    // ──────────────────────────────────────────────────────
    public function update(int $id, array $data): bool
    {
        $allowed = [
            'total_sessions', 'attended_sessions',
            'total_quiz_score', 'total_interaction_points', 'engagement_index',
        ];
        $updates = [];
        $values  = [];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $updates[] = "$field = ?";
                $values[]  = $data[$field];
            }
        }

        if (empty($updates)) return false;

        $updates[] = 'calculated_at = NOW()';
        $values[]  = $id;
        $sql = 'UPDATE engagement_scores SET ' . implode(', ', $updates) . ' WHERE id = ?';
        return $this->db->prepare($sql)->execute($values);
    }

    // ──────────────────────────────────────────────────────
    // DELETE
    // ──────────────────────────────────────────────────────
    public function delete(int $id): bool
    {
        return $this->db->prepare('DELETE FROM engagement_scores WHERE id = ?')
                        ->execute([$id]);
    }

    // ──────────────────────────────────────────────────────
    // BUSINESS LOGIC – Tính lại engagement_index
    //
    // Công thức:
    //   attendance_rate  = attended / total_sessions         (trọng số 40%)
    //   quiz_rate        = quiz_score / max_quiz_score        (trọng số 30%)
    //   interaction_rate = interaction_pts / (sessions * 5)  (trọng số 30%)
    //   index = att*40 + quiz*30 + inter*30   → clamp 0–100
    // ──────────────────────────────────────────────────────
    public function recalculate(int $studentId, int $courseId): float
    {
        // 1. Tổng buổi đã kết thúc
        $stmtTotal = $this->db->prepare(
            "SELECT COUNT(*) FROM class_sessions WHERE course_id = ? AND status = 'ended'"
        );
        $stmtTotal->execute([$courseId]);
        $totalSessions = (int) $stmtTotal->fetchColumn();

        // 2. Số buổi present (chỉ tính buổi đã 'ended', loại trùng theo session_id)
        $stmtAtt = $this->db->prepare(
            "SELECT COUNT(DISTINCT ar.session_id) FROM attendance_records ar
             JOIN class_sessions cs ON ar.session_id = cs.id
             WHERE cs.course_id = ? AND ar.student_id = ? AND ar.status = 'present'
               AND cs.status = 'ended'"
        );
        $stmtAtt->execute([$courseId, $studentId]);
        $attendedSessions = (int) $stmtAtt->fetchColumn();

        // 3. Quiz score trong course
        $stmtQuiz = $this->db->prepare(
            'SELECT COALESCE(SUM(qs_sub.total_score),0) AS earned,
                    COALESCE(SUM(qs_sub.max_score),  0) AS possible
             FROM quiz_submissions qs_sub
             JOIN quiz_sessions qs  ON qs_sub.quiz_id  = qs.id
             JOIN class_sessions cs ON qs.session_id   = cs.id
             WHERE cs.course_id = ? AND qs_sub.student_id = ?'
        );
        $stmtQuiz->execute([$courseId, $studentId]);
        $quizRow       = $stmtQuiz->fetch();
        $totalQuiz     = (float) $quizRow['earned'];
        $maxQuiz       = (float) $quizRow['possible'];

        // 4. Interaction points
        $stmtInter = $this->db->prepare(
            'SELECT COALESCE(SUM(il.points_earned), 0)
             FROM interaction_logs il
             JOIN class_sessions cs ON il.session_id = cs.id
             WHERE cs.course_id = ? AND il.user_id = ?'
        );
        $stmtInter->execute([$courseId, $studentId]);
        $totalInter = (float) $stmtInter->fetchColumn();

        // 5. Tính tỷ lệ
        $attRate   = $totalSessions > 0 ? min(1, $attendedSessions / $totalSessions) : 0;
        $quizRate  = $maxQuiz       > 0 ? ($totalQuiz / $maxQuiz)              : 0;
        $interCeil = max(1, $totalSessions * 5);
        $interRate = min(1, $totalInter / $interCeil);

        // 6. Index 0–100
        $index = round($attRate * 40 + $quizRate * 30 + $interRate * 30, 2);
        $index = max(0.0, min(100.0, $index));

        // 7. Upsert
        $payload = [
            'total_sessions'           => $totalSessions,
            'attended_sessions'        => $attendedSessions,
            'total_quiz_score'         => $totalQuiz,
            'total_interaction_points' => $totalInter,
            'engagement_index'         => $index,
        ];

        $existing = $this->getByStudentAndCourse($studentId, $courseId);
        if ($existing) {
            $this->update($existing['id'], $payload);
        } else {
            $id = $this->create($studentId, $courseId);
            $this->update($id, $payload);
        }

        return $index;
    }
}