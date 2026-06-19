<?php

require_once __DIR__ . '/../config/database.php';

class ClassSessionModel
{
    private PDO $db;
    private int $perPage = 10;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function getStats(): array
    {
        return [
            'total_sessions' => (int)$this->db->query("SELECT COUNT(*) FROM class_sessions")->fetchColumn(),
            'upcoming' => (int)$this->db->query("SELECT COUNT(*) FROM class_sessions WHERE status='upcoming'")->fetchColumn(),
            'active' => (int)$this->db->query("SELECT COUNT(*) FROM class_sessions WHERE status='active'")->fetchColumn(),
            'ended' => (int)$this->db->query("SELECT COUNT(*) FROM class_sessions WHERE status='ended'")->fetchColumn(),
        ];
    }

    public function getCourseOptions(): array
    {
        $stmt = $this->db->query(
            "SELECT id, course_code, course_name
             FROM courses
             WHERE is_active = 1
             ORDER BY course_code ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTeacherOptions(): array
    {
        $stmt = $this->db->query(
            "SELECT id, full_name, email
             FROM users
             WHERE role='teacher' AND is_active=1
             ORDER BY full_name ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSessions(array $filters): array
    {
        [$whereSql, $params] = $this->buildWhere($filters);
        $page = max(1, (int)($filters['page'] ?? 1));
        $offset = ($page - 1) * $this->perPage;

        $sql = "
            SELECT
                cs.*,
                c.course_code,
                c.course_name,
                u.full_name AS teacher_name,
                (
                    SELECT am.method_type
                    FROM attendance_methods am
                    WHERE am.session_id = cs.id
                    ORDER BY am.id DESC
                    LIMIT 1
                ) AS attendance_method
            FROM class_sessions cs
            JOIN courses c ON c.id = cs.course_id
            JOIN users u ON u.id = cs.teacher_id
            $whereSql
            ORDER BY cs.session_date DESC, cs.start_time DESC
            LIMIT {$this->perPage} OFFSET {$offset}
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countSessions(array $filters): int
    {
        [$whereSql, $params] = $this->buildWhere($filters);
        $stmt = $this->db->prepare(
            "SELECT COUNT(*)
             FROM class_sessions cs
             JOIN courses c ON c.id = cs.course_id
             JOIN users u ON u.id = cs.teacher_id
             $whereSql"
        );
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function getSessionById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM class_sessions WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function createSession(array $data): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO class_sessions (
                course_id, teacher_id, session_date, start_time, end_time, title, status, notes
             ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        return $stmt->execute([
            $data['course_id'],
            $data['teacher_id'],
            $data['session_date'],
            $data['start_time'],
            $data['end_time'],
            $data['title'],
            $data['status'],
            $data['notes'],
        ]);
    }

    public function updateSession(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE class_sessions SET
                course_id = ?,
                teacher_id = ?,
                session_date = ?,
                start_time = ?,
                end_time = ?,
                title = ?,
                status = ?,
                notes = ?
             WHERE id = ?"
        );
        return $stmt->execute([
            $data['course_id'],
            $data['teacher_id'],
            $data['session_date'],
            $data['start_time'],
            $data['end_time'],
            $data['title'],
            $data['status'],
            $data['notes'],
            $id,
        ]);
    }

    public function deleteSession(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM class_sessions WHERE id = ?");
        return $stmt->execute([$id]);
    }

    private function buildWhere(array $filters): array
    {
        $conditions = ['1=1'];
        $params = [];

        if (!empty($filters['search'])) {
            $conditions[] = "(cs.title LIKE ? OR c.course_name LIKE ? OR c.course_code LIKE ? OR u.full_name LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        if (!empty($filters['course_id'])) {
            $conditions[] = "cs.course_id = ?";
            $params[] = (int)$filters['course_id'];
        }

        if (!empty($filters['status']) && in_array($filters['status'], ['upcoming', 'active', 'ended'], true)) {
            $conditions[] = "cs.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['date'])) {
            $conditions[] = "cs.session_date = ?";
            $params[] = $filters['date'];
        }

        return ['WHERE ' . implode(' AND ', $conditions), $params];
    }
}
