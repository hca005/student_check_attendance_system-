<?php
// ============================================================
// models/AlertLogModel.php
// Quản lý cảnh báo sinh viên vắng nhiều / tương tác thấp
// Repository Pattern – CRUD + alert generation logic
// Thành viên 3 phụ trách
//

require_once APP_ROOT . '/config/Database.php';

class AlertLogModel
{
    private PDO $db;

    public const TYPE_LOW_ATTENDANCE = 'low_attendance';
    public const TYPE_LOW_ENGAGEMENT = 'low_engagement';

    public const SEVERITY_LOW    = 'low';
    public const SEVERITY_MEDIUM = 'medium';
    public const SEVERITY_HIGH   = 'high';

    public const STATUS_PENDING  = 'pending';
    public const STATUS_REVIEWED = 'reviewed';
    public const STATUS_RESOLVED = 'resolved';

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
        string $message,
        string $severity = self::SEVERITY_MEDIUM
    ): int {
        $stmt = $this->db->prepare(
            'INSERT INTO alerts (student_id, course_id, alert_type, message, severity, status)
             VALUES (?, ?, ?, ?, ?, "pending")'
        );
        $stmt->execute([$studentId, $courseId, $alertType, $message, $severity]);
        return (int) $this->db->lastInsertId();
    }

    // ──────────────────────────────────────────────────────
    // READ – Lấy 1 alert theo ID
    // ──────────────────────────────────────────────────────
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT alert_id, student_id, course_id, alert_type, message, severity,
                    status, reviewed_by, reviewed_at, created_at
             FROM alerts WHERE alert_id = ? LIMIT 1'
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
            'SELECT al.alert_id, al.student_id, al.course_id, al.alert_type,
                    al.message, al.severity, al.status, al.created_at,
                    c.course_name, c.course_code
             FROM alerts al
             JOIN courses c ON al.course_id = c.id
             WHERE al.student_id = ?
             ORDER BY al.created_at DESC'
        );
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }

    // ──────────────────────────────────────────────────────
    // READ – Lấy alerts đang chờ xử lý (pending) của student
    // ──────────────────────────────────────────────────────
    public function getOpenAlertsByStudent(int $studentId): array
    {
        $stmt = $this->db->prepare(
            'SELECT al.alert_id, al.course_id, al.alert_type, al.message,
                    al.severity, al.created_at,
                    c.course_name, c.course_code
             FROM alerts al
             JOIN courses c ON al.course_id = c.id
             WHERE al.student_id = ? AND al.status = "pending"
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
        $sql = 'SELECT al.alert_id, al.student_id, al.alert_type, al.message,
                       al.severity, al.status, al.created_at, al.reviewed_at,
                       u.full_name, u.student_code
                FROM alerts al
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
        $allowed = ['status', 'message', 'severity'];
        $updates = [];
        $values  = [];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $updates[] = "$field = ?";
                $values[]  = $data[$field];
            }
        }

        if (isset($data['status']) && in_array($data['status'], [self::STATUS_REVIEWED, self::STATUS_RESOLVED], true)) {
            $updates[] = 'reviewed_at = NOW()';
            if (isset($data['reviewed_by'])) {
                $updates[] = 'reviewed_by = ?';
                $values[]  = (int) $data['reviewed_by'];
            }
        }

        if (empty($updates)) {
            return false;
        }

        $values[] = $id;
        $sql = 'UPDATE alerts SET ' . implode(', ', $updates) . ' WHERE alert_id = ?';
        return $this->db->prepare($sql)->execute($values);
    }

    // ──────────────────────────────────────────────────────
    // DELETE – Xóa alert
    // ──────────────────────────────────────────────────────
    public function delete(int $id): bool
    {
        return $this->db->prepare('DELETE FROM alerts WHERE alert_id = ?')
                        ->execute([$id]);
    }

    // ──────────────────────────────────────────────────────
    // BUSINESS LOGIC – Tự động sinh cảnh báo cho 1 student
    // ──────────────────────────────────────────────────────
    public function generateAlerts(int $studentId, int $courseId): array
    {
        $created = [];

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
            if (!$this->hasOpenAlert($studentId, $courseId, self::TYPE_LOW_ATTENDANCE)) {
                $msg = "Bạn đã vắng $absenceCount buổi trong môn {$course['course_name']} "
                     . "(ngưỡng: {$course['absence_threshold']} buổi). Vui lòng liên hệ giảng viên.";
                $severity = $absenceCount >= (int)$course['absence_threshold'] * 2
                    ? self::SEVERITY_HIGH : self::SEVERITY_MEDIUM;
                $this->create($studentId, $courseId, self::TYPE_LOW_ATTENDANCE, $msg, $severity);
                $created[] = self::TYPE_LOW_ATTENDANCE;
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
                $severity = $engIndex < (float)$course['low_engagement_threshold'] / 2
                    ? self::SEVERITY_HIGH : self::SEVERITY_MEDIUM;
                $this->create($studentId, $courseId, self::TYPE_LOW_ENGAGEMENT, $msg, $severity);
                $created[] = self::TYPE_LOW_ENGAGEMENT;
            }
        }

        return $created;
    }

    // ──────────────────────────────────────────────────────
    // HELPER – Kiểm tra có alert pending cùng loại chưa
    // ──────────────────────────────────────────────────────
    public function hasOpenAlert(int $studentId, int $courseId, string $alertType): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM alerts
             WHERE student_id = ? AND course_id = ? AND alert_type = ? AND status = "pending"'
        );
        $stmt->execute([$studentId, $courseId, $alertType]);
        return (int) $stmt->fetchColumn() > 0;
    }

    // ──────────────────────────────────────────────────────
    // HELPER – Đếm alert pending của student
    // ──────────────────────────────────────────────────────
    public function countOpenAlerts(int $studentId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM alerts WHERE student_id = ? AND status = "pending"'
        );
        $stmt->execute([$studentId]);
        return (int) $stmt->fetchColumn();
    }
}