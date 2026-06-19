<?php

require_once __DIR__ . '/../config/database.php';

class CourseModel
{
    private PDO $db;
    private int $perPage = 8;

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
            'total_courses'     => (int)$this->db->query("SELECT COUNT(*) FROM courses")->fetchColumn(),
            'active_courses'    => (int)$this->db->query("SELECT COUNT(*) FROM courses WHERE is_active = 1")->fetchColumn(),
            'assigned_teachers' => (int)$this->db->query("SELECT COUNT(DISTINCT e.user_id) FROM enrollments e JOIN users u ON u.id=e.user_id WHERE u.role='teacher'")->fetchColumn(),
            'enrolled_students' => (int)$this->db->query("SELECT COUNT(*) FROM enrollments e JOIN users u ON u.id=e.user_id WHERE u.role='student'")->fetchColumn(),
        ];
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

    public function getCourses(array $filters = []): array
    {
        [$whereSql, $params] = $this->buildWhere($filters);
        $orderSql = $this->buildOrder($filters['sort'] ?? 'newest');

        $page = max(1, (int)($filters['page'] ?? 1));
        $offset = ($page - 1) * $this->perPage;

        $sql = "
            SELECT
                c.*,
                COALESCE(MAX(CASE WHEN u.role='teacher' THEN u.full_name END), 'Unassigned') AS teacher_name,
                SUM(CASE WHEN u.role='student' THEN 1 ELSE 0 END) AS students_count,
                (
                    SELECT COUNT(*)
                    FROM class_sessions cs
                    WHERE cs.course_id = c.id
                ) AS sessions_count
            FROM courses c
            LEFT JOIN enrollments e ON e.course_id = c.id
            LEFT JOIN users u ON u.id = e.user_id
            $whereSql
            GROUP BY c.id
            $orderSql
            LIMIT {$this->perPage} OFFSET {$offset}
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countCourses(array $filters = []): int
    {
        [$whereSql, $params] = $this->buildWhere($filters);

        $stmt = $this->db->prepare(
            "SELECT COUNT(DISTINCT c.id)
             FROM courses c
             LEFT JOIN enrollments e ON e.course_id = c.id
             LEFT JOIN users u ON u.id = e.user_id
             $whereSql"
        );
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function getCourseById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT c.*,
                    MAX(CASE WHEN u.role='teacher' THEN e.user_id END) AS teacher_id
             FROM courses c
             LEFT JOIN enrollments e ON e.course_id = c.id
             LEFT JOIN users u ON u.id = e.user_id
             WHERE c.id = ?
             GROUP BY c.id"
        );
        $stmt->execute([$id]);
        $course = $stmt->fetch(PDO::FETCH_ASSOC);
        return $course ?: null;
    }

    public function codeExists(string $courseCode, string $semester, ?int $excludeId = null): bool
    {
        if ($excludeId) {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) FROM courses
                 WHERE course_code = ? AND semester = ? AND id != ?"
            );
            $stmt->execute([$courseCode, $semester, $excludeId]);
        } else {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) FROM courses
                 WHERE course_code = ? AND semester = ?"
            );
            $stmt->execute([$courseCode, $semester]);
        }
        return (int)$stmt->fetchColumn() > 0;
    }

    public function createCourse(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO courses (
                course_code, course_name, semester, absence_threshold,
                low_engagement_threshold, attend_score, quiz_correct_score,
                discussion_score, is_active
             ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->execute([
            $data['course_code'],
            $data['course_name'],
            $data['semester'],
            $data['absence_threshold'],
            $data['low_engagement_threshold'],
            $data['attend_score'],
            $data['quiz_correct_score'],
            $data['discussion_score'],
            $data['is_active'],
        ]);

        $courseId = (int)$this->db->lastInsertId();
        $this->syncTeacher($courseId, $data['teacher_id'] ?? null);
        return $courseId;
    }

    public function updateCourse(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE courses SET
                course_code = ?,
                course_name = ?,
                semester = ?,
                absence_threshold = ?,
                low_engagement_threshold = ?,
                attend_score = ?,
                quiz_correct_score = ?,
                discussion_score = ?,
                is_active = ?
             WHERE id = ?"
        );

        $ok = $stmt->execute([
            $data['course_code'],
            $data['course_name'],
            $data['semester'],
            $data['absence_threshold'],
            $data['low_engagement_threshold'],
            $data['attend_score'],
            $data['quiz_correct_score'],
            $data['discussion_score'],
            $data['is_active'],
            $id,
        ]);

        if ($ok) {
            $this->syncTeacher($id, $data['teacher_id'] ?? null);
        }
        return $ok;
    }

    public function archiveCourse(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE courses SET is_active = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    private function syncTeacher(int $courseId, ?int $teacherId): void
    {
        // Xóa teacher enrollment cũ của khóa học này
        $delete = $this->db->prepare(
            "DELETE e FROM enrollments e
             JOIN users u ON u.id = e.user_id
             WHERE e.course_id = ? AND u.role='teacher'"
        );
        $delete->execute([$courseId]);

        if (!$teacherId) {
            return;
        }

        $check = $this->db->prepare("SELECT role FROM users WHERE id = ?");
        $check->execute([$teacherId]);
        $role = $check->fetchColumn();
        if ($role !== 'teacher') {
            return;
        }

        $insert = $this->db->prepare(
            "INSERT INTO enrollments (course_id, user_id, role)
             VALUES (?, ?, 'teacher')"
        );
        $insert->execute([$courseId, $teacherId]);
    }

    private function buildWhere(array $filters): array
    {
        $conditions = ['1=1'];
        $params = [];

        if (!empty($filters['search'])) {
            $conditions[] = "(c.course_code LIKE ? OR c.course_name LIKE ? OR u.full_name LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        if (!empty($filters['status']) && in_array($filters['status'], ['active', 'inactive'], true)) {
            $conditions[] = "c.is_active = ?";
            $params[] = $filters['status'] === 'active' ? 1 : 0;
        }

        return ['WHERE ' . implode(' AND ', $conditions), $params];
    }

    private function buildOrder(string $sort): string
    {
        return match ($sort) {
            'oldest' => 'ORDER BY c.created_at ASC',
            'code' => 'ORDER BY c.course_code ASC',
            'name' => 'ORDER BY c.course_name ASC',
            default => 'ORDER BY c.created_at DESC',
        };
    }
}
