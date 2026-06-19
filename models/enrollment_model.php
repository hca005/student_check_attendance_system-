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

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function getStats(): array
    {
        return [
            'total_enrollments' => (int)$this->db->query("SELECT COUNT(*) FROM enrollments")->fetchColumn(),
            'teachers_assigned' => (int)$this->db->query("SELECT COUNT(*) FROM enrollments e JOIN users u ON u.id=e.user_id WHERE u.role='teacher'")->fetchColumn(),
            'students_enrolled' => (int)$this->db->query("SELECT COUNT(*) FROM enrollments e JOIN users u ON u.id=e.user_id WHERE u.role='student'")->fetchColumn(),
            'active_courses'    => (int)$this->db->query("SELECT COUNT(*) FROM courses WHERE is_active=1")->fetchColumn(),
        ];
    }

    public function getCourseOptions(): array
    {
        $stmt = $this->db->query(
            "SELECT id, course_code, course_name, semester
             FROM courses
             ORDER BY course_code ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserOptions(string $role): array
    {
        if (!in_array($role, ['teacher', 'student'], true)) {
            return [];
        }

        $stmt = $this->db->prepare(
            "SELECT id, full_name, email, student_code
             FROM users
             WHERE role = ? AND is_active = 1
             ORDER BY full_name ASC"
        );
        $stmt->execute([$role]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEnrollments(array $filters): array
    {
        [$whereSql, $params] = $this->buildWhere($filters);
        $page = max(1, (int)($filters['page'] ?? 1));
        $offset = ($page - 1) * $this->perPage;

        $sql = "
            SELECT
                e.*,
                u.full_name,
                u.email,
                u.student_code,
                u.role AS user_role,
                u.is_active AS user_active,
                c.course_code,
                c.course_name,
                c.semester,
                c.is_active AS course_active
            FROM enrollments e
            JOIN users u ON u.id = e.user_id
            JOIN courses c ON c.id = e.course_id
            $whereSql
            ORDER BY e.enrolled_at DESC
            LIMIT {$this->perPage} OFFSET {$offset}
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countEnrollments(array $filters): int
    {
        [$whereSql, $params] = $this->buildWhere($filters);
        $stmt = $this->db->prepare(
            "SELECT COUNT(*)
             FROM enrollments e
             JOIN users u ON u.id = e.user_id
             JOIN courses c ON c.id = e.course_id
             $whereSql"
        );
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function getEnrollmentById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM enrollments WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function enrollmentExists(int $courseId, int $userId, ?int $excludeId = null): bool
    {
        if ($excludeId) {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) FROM enrollments
                 WHERE course_id = ? AND user_id = ? AND id != ?"
            );
            $stmt->execute([$courseId, $userId, $excludeId]);
        } else {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) FROM enrollments
                 WHERE course_id = ? AND user_id = ?"
            );
            $stmt->execute([$courseId, $userId]);
        }
        return (int)$stmt->fetchColumn() > 0;
    }

    public function createEnrollment(array $data): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO enrollments (course_id, user_id, role)
             VALUES (?, ?, ?)"
        );
        return $stmt->execute([
            $data['course_id'],
            $data['user_id'],
            $data['role'],
        ]);
    }

    public function updateEnrollment(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE enrollments
             SET course_id = ?, user_id = ?, role = ?
             WHERE id = ?"
        );
        return $stmt->execute([
            $data['course_id'],
            $data['user_id'],
            $data['role'],
            $id,
        ]);
    }

    public function deleteEnrollment(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM enrollments WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function userRoleById(int $userId): ?string
    {
        $stmt = $this->db->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $role = $stmt->fetchColumn();
        return $role ? (string)$role : null;
    }

    private function buildWhere(array $filters): array
    {
        $conditions = ['1=1'];
        $params = [];

        if (!empty($filters['search'])) {
            $conditions[] = "(u.full_name LIKE ? OR u.email LIKE ? OR c.course_code LIKE ? OR c.course_name LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        if (!empty($filters['course_id'])) {
            $conditions[] = "e.course_id = ?";
            $params[] = (int)$filters['course_id'];
        }

        if (!empty($filters['role']) && in_array($filters['role'], ['teacher', 'student'], true)) {
            $conditions[] = "u.role = ?";
            $params[] = $filters['role'];
        }

        if (!empty($filters['status']) && in_array($filters['status'], ['active', 'inactive'], true)) {
            if ($filters['status'] === 'active') {
                $conditions[] = "u.is_active = 1 AND c.is_active = 1";
            } else {
                $conditions[] = "(u.is_active = 0 OR c.is_active = 0)";
            }
        }

        return ['WHERE ' . implode(' AND ', $conditions), $params];
    }
}
