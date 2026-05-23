<?php
// ============================================================
// models/AlertLogModel.php
// Quản lý cảnh báo sinh viên vắng nhiều / tương tác thấp
// Repository Pattern – CRUD + alert generation logic
// Thành viên 3 phụ trách
// ============================================================

require_once APP_ROOT . '/config/Database.php';

class AlertLogModel
{
    private PDO $db;

    // Alert types (theo ENUM trong schema)
    public const TYPE_HIGH_ABSENCE   = 'high_absence';
    public const TYPE_LOW_ENGAGEMENT = 'low_engagement';
    public const TYPE_MISSED_QUIZ    = 'missed_quiz';

    // Statuses
    public const STATUS_OPEN     = 'open';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_IGNORED  = 'ignored';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    // ──────────────────────────────────────────────────────
    // CREATE – Tạo cảnh báo mới
    // ──────────────────────────────────────────────────────
    public function create(
        int    $studentId,
        int    $courseId,
        string $alertType,
        string $alertMessage
    ): int {
        $stmt = $this->db->prepare(
            'INSERT INTO alert_logs (student_id, course_id, alert_type, alert_message, status)
             VALUES (?, ?, ?, ?, "open")'
        );
        $stmt->execute([$studentId, $courseId, $alertType, $alertMessage]);
        return (int) $this->db->lastInsertId();
    }

    // ──────────────────────────────────────────────────────
    // READ – Lấy 1 alert theo ID
    // ──────────────────────────────────────────────────────
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, student_id, course_id, alert_type, alert_message,
                    status, resolved_by, resolved_at, created_at
             FROM alert_logs WHERE id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    // ──────────────────────────────────────────────────────
    // READ – Lấy tất cả alerts của 1 student
    // ──────────────────────────────────────────────────────
    public function getByStudentId(int $studentId): array
    {
        $stmt = $this->db->prepare(
            'SELECT al.id, al.student_id, al.course_id, al.alert_type,
                    al.alert_message, al.status, al.created_at,
                    c.course_name, c.course_code
             FROM alert_logs al
             JOIN courses c ON al.course_id = c.id
             WHERE al.student_id = ?
             ORDER BY al.created_at DESC'
        );
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }

    // ──────────────────────────────────────────────────────
    // READ – Lấy alerts đang mở của student
    // ──────────────────────────────────────────────────────
    public function getOpenAlertsByStudent(int $studentId): array
    {
        $stmt = $this->db->prepare(
            'SELECT al.id, al.course_id, al.alert_type, al.alert_message, al.created_at,
                    c.course_name, c.course_code
             FROM alert_logs al
             JOIN courses c ON al.course_id = c.id
             WHERE al.student_id = ? AND al.status = "open"
             ORDER BY al.created_at DESC'
        );
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }

    // ──────────────────────────────────────────────────────
    // READ – Lấy tất cả alerts theo course (Teacher/Admin dùng)
    // ──────────────────────────────────────────────────────
    public function getByCourseId(int $courseId, string $status = ''): array
    {
        $sql = 'SELECT al.id, al.student_id, al.alert_type, al.alert_message,
                       al.status, al.created_at, al.resolved_at,
                       u.full_name, u.student_code
                FROM alert_logs al
                JOIN users u ON al.student_id = u.id
                WHERE al.course_id = ?';
        $params = [$courseId];

        if ($status !== '') {
            $sql    .= ' AND al.status = ?';
            $params[] = $status;
        }

        $sql .= ' ORDER BY al.created_at DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ──────────────────────────────────────────────────────
    // UPDATE – Cập nhật trạng thái alert
    // ──────────────────────────────────────────────────────
    public function update(int $id, array $data): bool
    {
        $allowed = ['status', 'alert_message'];
        $updates = [];
        $values  = [];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $updates[] = "$field = ?";
                $values[]  = $data[$field];
            }
        }

        // Nếu resolve thì ghi thêm resolved_at và resolved_by
        if (isset($data['status']) && $data['status'] === self::STATUS_RESOLVED) {
            $updates[] = 'resolved_at = NOW()';
            if (isset($data['resolved_by'])) {
                $updates[] = 'resolved_by = ?';
                $values[]  = (int) $data['resolved_by'];
            }
        }

        if (empty($updates)) {
            return false;
        }

        $values[] = $id;
        $sql = 'UPDATE alert_logs SET ' . implode(', ', $updates) . ' WHERE id = ?';
        return $this->db->prepare($sql)->execute($values);
    }

    // ──────────────────────────────────────────────────────
    // DELETE – Xóa alert
    // ──────────────────────────────────────────────────────
    public function delete(int $id): bool
    {
        return $this->db->prepare('DELETE FROM alert_logs WHERE id = ?')
                        ->execute([$id]);
    }

    // ──────────────────────────────────────────────────────
    // BUSINESS LOGIC – Tự động sinh cảnh báo cho 1 student
    //
    // Kiểm tra 2 điều kiện theo ngưỡng đặt trong bảng courses:
    //   1. absence_threshold   → vắng > ngưỡng → high_absence
    //   2. low_engagement_threshold → engagement_index thấp → low_engagement
    // Tránh tạo trùng alert cùng loại đang "open"
    // ──────────────────────────────────────────────────────
    public function generateAlerts(int $studentId, int $courseId): array
    {
        $created = [];

        // Lấy ngưỡng từ course
        $stmtCourse = $this->db->prepare(
            'SELECT absence_threshold, low_engagement_threshold, course_name
             FROM courses WHERE id = ? LIMIT 1'
        );
        $stmtCourse->execute([$courseId]);
        $course = $stmtCourse->fetch();
        if (!$course) {
            return $created;
        }

        // --- Kiểm tra 1: Vắng quá nhiều ---
        $stmtAbs = $this->db->prepare(
            'SELECT COUNT(*) FROM attendance_records ar
             JOIN class_sessions cs ON ar.session_id = cs.id
             WHERE cs.course_id = ? AND ar.student_id = ? AND ar.status = "absent"'
        );
        $stmtAbs->execute([$courseId, $studentId]);
        $absenceCount = (int) $stmtAbs->fetchColumn();

        if ($absenceCount > (int) $course['absence_threshold']) {
            if (!$this->hasOpenAlert($studentId, $courseId, self::TYPE_HIGH_ABSENCE)) {
                $msg = "Bạn đã vắng $absenceCount buổi trong môn {$course['course_name']} "
                     . "(ngưỡng: {$course['absence_threshold']} buổi). Vui lòng liên hệ giảng viên.";
                $this->create($studentId, $courseId, self::TYPE_HIGH_ABSENCE, $msg);
                $created[] = self::TYPE_HIGH_ABSENCE;
            }
        }

        // --- Kiểm tra 2: Engagement thấp ---
        $stmtEng = $this->db->prepare(
            'SELECT engagement_index FROM engagement_scores
             WHERE student_id = ? AND course_id = ? LIMIT 1'
        );
        $stmtEng->execute([$studentId, $courseId]);
        $engIndex = (float) ($stmtEng->fetchColumn() ?? 0);

        if ($engIndex > 0 && $engIndex < (float) $course['low_engagement_threshold']) {
            if (!$this->hasOpenAlert($studentId, $courseId, self::TYPE_LOW_ENGAGEMENT)) {
                $msg = "Điểm tham gia lớp học của bạn trong môn {$course['course_name']} "
                     . "là {$engIndex}% (ngưỡng tối thiểu: {$course['low_engagement_threshold']}%). "
                     . "Hãy tích cực tham gia hơn.";
                $this->create($studentId, $courseId, self::TYPE_LOW_ENGAGEMENT, $msg);
                $created[] = self::TYPE_LOW_ENGAGEMENT;
            }
        }

        return $created;
    }

    // ──────────────────────────────────────────────────────
    // HELPER – Kiểm tra có alert open cùng loại chưa
    // ──────────────────────────────────────────────────────
    public function hasOpenAlert(int $studentId, int $courseId, string $alertType): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM alert_logs
             WHERE student_id = ? AND course_id = ? AND alert_type = ? AND status = "open"'
        );
        $stmt->execute([$studentId, $courseId, $alertType]);
        return (int) $stmt->fetchColumn() > 0;
    }

    // ──────────────────────────────────────────────────────
    // HELPER – Đếm alert mở của student
    // ──────────────────────────────────────────────────────
    public function countOpenAlerts(int $studentId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM alert_logs WHERE student_id = ? AND status = "open"'
        );
        $stmt->execute([$studentId]);
        return (int) $stmt->fetchColumn();
    }
}