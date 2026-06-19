<?php

require_once __DIR__ . '/../config/database.php';

class ClassSessionModel
{
    private PDO $db;
    private int $perPage = 10;
    private array $validStatuses = ['upcoming', 'active', 'ended'];

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getSessions(array $filters = []): array
    {
        [$whereSql, $params] = $this->buildWhere($filters);
        $orderSql = $this->buildOrderBy($filters['sort'] ?? 'date_desc');
        $page = max(1, (int)($filters['page'] ?? 1));
        $offset = ($page - 1) * $this->perPage;

        $sql = "
            SELECT
                cs.id,
                cs.course_id,
                cs.teacher_id,
                cs.session_date,
                cs.start_time,
                cs.end_time,
                cs.title,
                cs.status,
                cs.notes,
                c.course_code,
                c.course_name,
                c.semester,
                u.full_name AS teacher_name,
                COALESCE(am.method_types, '-') AS attendance_methods
            FROM class_sessions cs
            JOIN courses c ON c.id = cs.course_id
            JOIN users u ON u.id = cs.teacher_id
            LEFT JOIN (
                SELECT session_id, GROUP_CONCAT(DISTINCT UPPER(method_type) ORDER BY method_type SEPARATOR ', ') AS method_types
                FROM attendance_methods
                GROUP BY session_id
            ) am ON am.session_id = cs.id
            {$whereSql}
            {$orderSql}
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $this->db->prepare($sql);
        $this->bindParams($stmt, $params);
        $stmt->bindValue(':limit', $this->perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countSessions(array $filters = []): int
    {
        [$whereSql, $params] = $this->buildWhere($filters);
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM class_sessions cs
            JOIN courses c ON c.id = cs.course_id
            JOIN users u ON u.id = cs.teacher_id
            {$whereSql}
        ");
        $this->bindParams($stmt, $params);
        $stmt->execute();

        return (int)$stmt->fetchColumn();
    }

    public function getSessionStats(): array
    {
        $row = $this->db->query("
            SELECT
                COUNT(*) AS total,
                COALESCE(SUM(status = 'upcoming'), 0) AS upcoming,
                COALESCE(SUM(status = 'active'), 0) AS active,
                COALESCE(SUM(status = 'ended'), 0) AS ended
            FROM class_sessions
        ")->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'total' => (int)($row['total'] ?? 0),
            'upcoming' => (int)($row['upcoming'] ?? 0),
            'active' => (int)($row['active'] ?? 0),
            'ended' => (int)($row['ended'] ?? 0),
        ];
    }

    public function getSessionById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT id, course_id, teacher_id, session_date, start_time, end_time, title, status, notes
            FROM class_sessions
            WHERE id = :id
        ");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $session = $stmt->fetch(PDO::FETCH_ASSOC);
        return $session ?: null;
    }

    public function getCourses(): array
    {
        return $this->db->query("
            SELECT id, course_code, course_name, semester, is_active
            FROM courses
            ORDER BY is_active DESC, course_code ASC, semester DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTeachers(): array
    {
        return $this->db->query("
            SELECT id, full_name, email
            FROM users
            WHERE role = 'teacher' AND is_active = 1
            ORDER BY full_name ASC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPrimaryTeacherForCourse(int $courseId): int
    {
        $stmt = $this->db->prepare("
            SELECT ce.user_id
            FROM enrollments ce
            JOIN users u ON u.id = ce.user_id AND u.role = 'teacher'
            WHERE ce.course_id = :course_id AND ce.role = 'teacher'
            ORDER BY ce.id ASC
            LIMIT 1
        ");
        $stmt->bindValue(':course_id', $courseId, PDO::PARAM_INT);
        $stmt->execute();

        return (int)($stmt->fetchColumn() ?: 0);
    }

    public function createSession(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO class_sessions (
                course_id,
                teacher_id,
                session_date,
                start_time,
                end_time,
                title,
                status,
                notes
            ) VALUES (
                :course_id,
                :teacher_id,
                :session_date,
                :start_time,
                :end_time,
                :title,
                :status,
                :notes
            )
        ");

        return $stmt->execute([
            ':course_id' => (int)$data['course_id'],
            ':teacher_id' => (int)$data['teacher_id'],
            ':session_date' => $data['session_date'],
            ':start_time' => $data['start_time'],
            ':end_time' => $data['end_time'],
            ':title' => $data['title'],
            ':status' => $data['status'],
            ':notes' => $data['notes'],
        ]);
    }

    public function updateSession(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE class_sessions
            SET course_id = :course_id,
                teacher_id = :teacher_id,
                session_date = :session_date,
                start_time = :start_time,
                end_time = :end_time,
                title = :title,
                status = :status,
                notes = :notes
            WHERE id = :id
        ");

        return $stmt->execute([
            ':id' => $id,
            ':course_id' => (int)$data['course_id'],
            ':teacher_id' => (int)$data['teacher_id'],
            ':session_date' => $data['session_date'],
            ':start_time' => $data['start_time'],
            ':end_time' => $data['end_time'],
            ':title' => $data['title'],
            ':status' => $data['status'],
            ':notes' => $data['notes'],
        ]);
    }

    public function deleteSession(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM class_sessions WHERE id = :id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function courseExists(int $courseId): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM courses WHERE id = :id');
        $stmt->bindValue(':id', $courseId, PDO::PARAM_INT);
        $stmt->execute();

        return (int)$stmt->fetchColumn() > 0;
    }

    public function teacherExists(int $teacherId): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE id = :id AND role = 'teacher' AND is_active = 1");
        $stmt->bindValue(':id', $teacherId, PDO::PARAM_INT);
        $stmt->execute();

        return (int)$stmt->fetchColumn() > 0;
    }

    public function getValidStatuses(): array
    {
        return $this->validStatuses;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    private function buildWhere(array $filters): array
    {
        $conditions = [];
        $params = [];

        $search = trim((string)($filters['search'] ?? ''));
        if ($search !== '') {
            $conditions[] = '(cs.title LIKE :search OR c.course_code LIKE :search OR c.course_name LIKE :search OR u.full_name LIKE :search)';
            $params[':search'] = '%' . $search . '%';
        }

        $courseId = (int)($filters['course_id'] ?? 0);
        if ($courseId > 0) {
            $conditions[] = 'cs.course_id = :course_id';
            $params[':course_id'] = $courseId;
        }

        $status = (string)($filters['status'] ?? '');
        if (in_array($status, $this->validStatuses, true)) {
            $conditions[] = 'cs.status = :status';
            $params[':status'] = $status;
        }

        $date = trim((string)($filters['date'] ?? ''));
        if ($date !== '') {
            $conditions[] = 'cs.session_date = :session_date';
            $params[':session_date'] = $date;
        }

        $whereSql = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
        return [$whereSql, $params];
    }

    private function bindParams(PDOStatement $stmt, array $params): void
    {
        foreach ($params as $name => $value) {
            $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($name, $value, $type);
        }
    }

    private function buildOrderBy(string $sort): string
    {
        return match ($sort) {
            'date_asc' => 'ORDER BY cs.session_date ASC, cs.start_time ASC, cs.id ASC',
            'course_asc' => 'ORDER BY c.course_code ASC, cs.session_date DESC',
            'status_asc' => 'ORDER BY cs.status ASC, cs.session_date DESC',
            default => 'ORDER BY cs.session_date DESC, cs.start_time DESC, cs.id DESC',
        };
    }
}
