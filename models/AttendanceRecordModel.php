<?php
// ============================================================
// models/AttendanceRecordModel.php
// Quản lý bản ghi điểm danh của sinh viên
// Repository Pattern – CRUD + validation
// ============================================================

require_once APP_ROOT . '/config/Database.php';

class AttendanceRecordModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    // ──────────────────────────────────────────────────────
    // CREATE – Tạo bản ghi điểm danh
    // ──────────────────────────────────────────────────────
    public function create(
        int $sessionId,
        int $studentId,
        string $status = 'absent',
        ?int $methodId = null,
        ?\DateTime $checkedInAt = null,
        ?string $note = null
    ): int {
        $stmt = $this->db->prepare(
            'INSERT INTO attendance_records (session_id, student_id, method_id, status, checked_in_at, note)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $checkedInAtStr = $checkedInAt ? $checkedInAt->format('Y-m-d H:i:s') : null;
        $stmt->execute([$sessionId, $studentId, $methodId, $status, $checkedInAtStr, $note]);
        return (int) $this->db->lastInsertId();
    }

    // ──────────────────────────────────────────────────────
    // READ – Lấy 1 bản ghi theo ID
    // ──────────────────────────────────────────────────────
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, session_id, student_id, method_id, status, checked_in_at, note, created_at, updated_at
             FROM attendance_records WHERE id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    // ──────────────────────────────────────────────────────
    // READ – Lấy tất cả bản ghi theo session
    // ──────────────────────────────────────────────────────
    public function getBySessionId(int $sessionId): array
    {
        $stmt = $this->db->prepare(
            'SELECT ar.id, ar.session_id, ar.student_id, ar.method_id, ar.status, 
                    ar.checked_in_at, ar.note, ar.created_at, ar.updated_at,
                    u.full_name, u.student_code
             FROM attendance_records ar
             JOIN users u ON ar.student_id = u.id
             WHERE ar.session_id = ?
             ORDER BY u.full_name ASC'
        );
        $stmt->execute([$sessionId]);
        return $stmt->fetchAll();
    }

    // ──────────────────────────────────────────────────────
    // READ – Lấy bản ghi của 1 student trong 1 session
    // ──────────────────────────────────────────────────────
    public function getBySessionAndStudent(int $sessionId, int $studentId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, session_id, student_id, method_id, status, checked_in_at, note, created_at, updated_at
             FROM attendance_records WHERE session_id = ? AND student_id = ? LIMIT 1'
        );
        $stmt->execute([$sessionId, $studentId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    // ──────────────────────────────────────────────────────
    // READ – Lấy tất cả bản ghi của 1 student
    // ──────────────────────────────────────────────────────
    public function getByStudentId(int $studentId): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, session_id, student_id, method_id, status, checked_in_at, note, created_at, updated_at
             FROM attendance_records WHERE student_id = ? ORDER BY created_at DESC'
        );
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }

    // ──────────────────────────────────────────────────────
    // UPDATE – Cập nhật bản ghi
    // ──────────────────────────────────────────────────────
    public function update(int $id, array $data): bool
    {
        $allowedFields = ['status', 'checked_in_at', 'method_id', 'note', 'verified_by'];
        $updates = [];
        $values = [];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                if ($field === 'checked_in_at' && $data[$field] instanceof DateTime) {
                    $values[] = $data[$field]->format('Y-m-d H:i:s');
                } else {
                    $values[] = $data[$field];
                }
            }
        }

        if (empty($updates)) {
            return false;
        }

        $values[] = $id;
        $sql = 'UPDATE attendance_records SET ' . implode(', ', $updates) . ', updated_at = NOW() WHERE id = ?';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }

    // ──────────────────────────────────────────────────────
    // DELETE – Xóa bản ghi
    // ──────────────────────────────────────────────────────
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM attendance_records WHERE id = ?');
        return $stmt->execute([$id]);
    }

    // ──────────────────────────────────────────────────────
    // VALIDATE – Kiểm tra record duy nhất per session per student
    // ──────────────────────────────────────────────────────
    public function isUniqueRecord(int $sessionId, int $studentId, ?int $excludeId = null): bool
    {
        $sql = 'SELECT COUNT(*) as count FROM attendance_records WHERE session_id = ? AND student_id = ?';
        $params = [$sessionId, $studentId];

        if ($excludeId) {
            $sql .= ' AND id != ?';
            $params[] = $excludeId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['count'] == 0;
    }

    // ──────────────────────────────────────────────────────
    // HELPER – Lấy thống kê điểm danh của student trong course
    // ──────────────────────────────────────────────────────
    public function getAttendanceStats(int $courseId, int $studentId): array
    {
        $stmt = $this->db->prepare(
            'SELECT 
                COUNT(*) as total_sessions,
                SUM(CASE WHEN ar.status = "present" THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN ar.status = "absent" THEN 1 ELSE 0 END) as absent_count,
                SUM(CASE WHEN ar.status = "late" THEN 1 ELSE 0 END) as late_count,
                SUM(CASE WHEN ar.status = "excused" THEN 1 ELSE 0 END) as excused_count
             FROM attendance_records ar
             JOIN class_sessions cs ON ar.session_id = cs.id
             WHERE cs.course_id = ? AND ar.student_id = ?'
        );
        $stmt->execute([$courseId, $studentId]);
        return $stmt->fetch() ?: [];
    }

    // ──────────────────────────────────────────────────────
    // HELPER – Tính tỷ lệ vắng
    // ──────────────────────────────────────────────────────
    public function getAbsencePercentage(int $courseId, int $studentId): float
    {
        $stats = $this->getAttendanceStats($courseId, $studentId);
        if ($stats['total_sessions'] == 0) {
            return 0.0;
        }
        return ($stats['absent_count'] / $stats['total_sessions']) * 100;
    }
}
