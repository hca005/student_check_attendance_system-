<?php

require_once __DIR__ . '/../config/database.php';

class EnrollmentModel
{
    private PDO $db;
    private int $perPage = 10;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getEnrollments(array $filters = []): array
    {
        [$whereSql, $params] = $this->buildWhere($filters);
        $orderSql = $this->buildOrderBy($filters['sort'] ?? 'newest');
        $page = max(1, (int)($filters['page'] ?? 1));
        $offset = ($page - 1) * $this->perPage;

        $sql = "
            SELECT
                ce.id,
                ce.course_id,
                ce.user_id,
                ce.role,
                ce.enrolled_at,
                u.full_name,
                u.email,
                u.student_code,
                u.is_active AS user_is_active,
                c.course_code,
                c.course_name,
                c.semester,
                c.is_active AS course_is_active
            FROM enrollments ce
            JOIN users u ON u.id = ce.user_id
            JOIN courses c ON c.id = ce.course_id
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

    public function countEnrollments(array $filters = []): int
    {
        [$whereSql, $params] = $this->buildWhere($filters);
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM enrollments ce
            JOIN users u ON u.id = ce.user_id
            JOIN courses c ON c.id = ce.course_id
            {$whereSql}
        ");
        $this->bindParams($stmt, $params);
        $stmt->execute();

        return (int)$stmt->fetchColumn();
    }

    public function getEnrollmentStats(): array
    {
        $row = $this->db->query("
            SELECT
                COUNT(*) AS total,
                COALESCE(SUM(role = 'teacher'), 0) AS teachers,
                COALESCE(SUM(role = 'student'), 0) AS students
            FROM enrollments
        ")->fetch(PDO::FETCH_ASSOC) ?: [];

        $activeCourses = $this->db->query("SELECT COUNT(*) FROM courses WHERE is_active = 1")->fetchColumn();

        return [
            'total' => (int)($row['total'] ?? 0),
            'teachers' => (int)($row['teachers'] ?? 0),
            'students' => (int)($row['students'] ?? 0),
            'active_courses' => (int)$activeCourses,
        ];
    }

    public function getEnrollmentById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT ce.id, ce.course_id, ce.user_id, ce.role, ce.enrolled_at
            FROM enrollments ce
            WHERE ce.id = :id
        ");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $enrollment = $stmt->fetch(PDO::FETCH_ASSOC);
        return $enrollment ?: null;
    }

    public function getCourses(): array
    {
        return $this->db->query("
            SELECT id, course_code, course_name, semester, is_active
            FROM courses
            ORDER BY is_active DESC, course_code ASC, semester DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAssignableUsers(): array
    {
        return $this->db->query("
            SELECT id, full_name, email, role, student_code, is_active
            FROM users
            WHERE role IN ('teacher', 'student')
            ORDER BY role ASC, full_name ASC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createEnrollment(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO enrollments (course_id, user_id, role)
            VALUES (:course_id, :user_id, :role)
        ");

        return $stmt->execute([
            ':course_id' => (int)$data['course_id'],
            ':user_id' => (int)$data['user_id'],
            ':role' => $data['role'],
        ]);
    }

    public function updateEnrollment(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE enrollments
            SET course_id = :course_id,
                user_id = :user_id,
                role = :role
            WHERE id = :id
        ");

        return $stmt->execute([
            ':id' => $id,
            ':course_id' => (int)$data['course_id'],
            ':user_id' => (int)$data['user_id'],
            ':role' => $data['role'],
        ]);
    }

    public function deleteEnrollment(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM enrollments WHERE id = :id');
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

    public function userMatchesRole(int $userId, string $role): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM users WHERE id = :id AND role = :role AND is_active = 1');
        $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':role', $role);
        $stmt->execute();

        return (int)$stmt->fetchColumn() > 0;
    }

    public function duplicateExists(int $courseId, int $userId, ?int $excludeId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM enrollments WHERE course_id = :course_id AND user_id = :user_id';
        if ($excludeId !== null) {
            $sql .= ' AND id <> :exclude_id';
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':course_id', $courseId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        if ($excludeId !== null) {
            $stmt->bindValue(':exclude_id', $excludeId, PDO::PARAM_INT);
        }
        $stmt->execute();

        return (int)$stmt->fetchColumn() > 0;
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
            $conditions[] = '(u.full_name LIKE :search OR u.email LIKE :search OR u.student_code LIKE :search OR c.course_code LIKE :search OR c.course_name LIKE :search)';
            $params[':search'] = '%' . $search . '%';
        }

        $courseId = (int)($filters['course_id'] ?? 0);
        if ($courseId > 0) {
            $conditions[] = 'ce.course_id = :course_id';
            $params[':course_id'] = $courseId;
        }

        $role = (string)($filters['role'] ?? '');
        if (in_array($role, ['teacher', 'student'], true)) {
            $conditions[] = 'ce.role = :role';
            $params[':role'] = $role;
        }

        $status = (string)($filters['status'] ?? '');
        if ($status === 'active') {
            $conditions[] = 'c.is_active = 1 AND u.is_active = 1';
        } elseif ($status === 'inactive') {
            $conditions[] = '(c.is_active = 0 OR u.is_active = 0)';
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
            'oldest' => 'ORDER BY ce.enrolled_at ASC, ce.id ASC',
            'user_asc' => 'ORDER BY u.full_name ASC, c.course_code ASC',
            'course_asc' => 'ORDER BY c.course_code ASC, u.full_name ASC',
            default => 'ORDER BY ce.enrolled_at DESC, ce.id DESC',
        };
    }
}
