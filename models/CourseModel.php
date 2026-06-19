<?php

require_once __DIR__ . '/../config/database.php';

class CourseModel
{
    private PDO $db;
    private int $perPage = 10;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getCourses(array $filters = []): array
    {
        [$whereSql, $params] = $this->buildWhere($filters);
        $orderSql = $this->buildOrderBy($filters['sort'] ?? 'newest');
        $page = max(1, (int)($filters['page'] ?? 1));
        $offset = ($page - 1) * $this->perPage;

        $sql = "
            SELECT
                c.id,
                c.course_code,
                c.course_name,
                c.semester,
                c.absence_threshold,
                c.low_engagement_threshold,
                c.is_active,
                c.created_at,
                COALESCE(t.teacher_names, 'Unassigned') AS teacher_names,
                COALESCE(t.teacher_count, 0) AS teacher_count,
                COALESCE(s.student_count, 0) AS student_count,
                COALESCE(cs.session_count, 0) AS session_count
            FROM courses c
            LEFT JOIN (
                SELECT ce.course_id,
                       GROUP_CONCAT(u.full_name ORDER BY u.full_name SEPARATOR ', ') AS teacher_names,
                       COUNT(DISTINCT u.id) AS teacher_count
                FROM enrollments ce
                JOIN users u ON u.id = ce.user_id AND u.role = 'teacher'
                WHERE ce.role = 'teacher'
                GROUP BY ce.course_id
            ) t ON t.course_id = c.id
            LEFT JOIN (
                SELECT ce.course_id, COUNT(DISTINCT ce.user_id) AS student_count
                FROM enrollments ce
                JOIN users u ON u.id = ce.user_id AND u.role = 'student'
                WHERE ce.role = 'student'
                GROUP BY ce.course_id
            ) s ON s.course_id = c.id
            LEFT JOIN (
                SELECT course_id, COUNT(*) AS session_count
                FROM class_sessions
                GROUP BY course_id
            ) cs ON cs.course_id = c.id
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

    public function countCourses(array $filters = []): int
    {
        [$whereSql, $params] = $this->buildWhere($filters);
        $sql = "
            SELECT COUNT(DISTINCT c.id)
            FROM courses c
            LEFT JOIN (
                SELECT ce.course_id,
                       GROUP_CONCAT(u.full_name ORDER BY u.full_name SEPARATOR ', ') AS teacher_names
                FROM enrollments ce
                JOIN users u ON u.id = ce.user_id AND u.role = 'teacher'
                WHERE ce.role = 'teacher'
                GROUP BY ce.course_id
            ) t ON t.course_id = c.id
            {$whereSql}
        ";

        $stmt = $this->db->prepare($sql);
        $this->bindParams($stmt, $params);
        $stmt->execute();

        return (int)$stmt->fetchColumn();
    }

    public function getCourseStats(): array
    {
        $courseStats = $this->db->query("
            SELECT
                COUNT(*) AS total,
                COALESCE(SUM(is_active = 1), 0) AS active
            FROM courses
        ")->fetch(PDO::FETCH_ASSOC) ?: [];

        $teacherCount = $this->db->query("
            SELECT COUNT(DISTINCT ce.user_id)
            FROM enrollments ce
            JOIN users u ON u.id = ce.user_id AND u.role = 'teacher'
            WHERE ce.role = 'teacher'
        ")->fetchColumn();

        $studentCount = $this->db->query("
            SELECT COUNT(DISTINCT ce.user_id)
            FROM enrollments ce
            JOIN users u ON u.id = ce.user_id AND u.role = 'student'
            WHERE ce.role = 'student'
        ")->fetchColumn();

        return [
            'total' => (int)($courseStats['total'] ?? 0),
            'active' => (int)($courseStats['active'] ?? 0),
            'teachers' => (int)$teacherCount,
            'students' => (int)$studentCount,
        ];
    }

    public function getCourseById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT
                c.id,
                c.course_code,
                c.course_name,
                c.semester,
                c.absence_threshold,
                c.low_engagement_threshold,
                c.attend_score,
                c.quiz_correct_score,
                c.discussion_score,
                c.is_active,
                c.created_at,
                c.updated_at,
                (
                    SELECT ce.user_id
                    FROM enrollments ce
                    JOIN users u ON u.id = ce.user_id AND u.role = 'teacher'
                    WHERE ce.course_id = c.id AND ce.role = 'teacher'
                    ORDER BY ce.id ASC
                    LIMIT 1
                ) AS teacher_id
            FROM courses c
            WHERE c.id = :id
        ");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $course = $stmt->fetch(PDO::FETCH_ASSOC);
        return $course ?: null;
    }

    public function getActiveCourses(): array
    {
        return $this->db->query("
            SELECT id, course_code, course_name, semester, is_active
            FROM courses
            ORDER BY course_code ASC, semester DESC
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

    public function createCourse(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO courses (
                course_code,
                course_name,
                semester,
                absence_threshold,
                low_engagement_threshold,
                is_active
            ) VALUES (
                :course_code,
                :course_name,
                :semester,
                :absence_threshold,
                :low_engagement_threshold,
                :is_active
            )
        ");

        $stmt->execute([
            ':course_code' => $data['course_code'],
            ':course_name' => $data['course_name'],
            ':semester' => $data['semester'],
            ':absence_threshold' => (int)$data['absence_threshold'],
            ':low_engagement_threshold' => (float)$data['low_engagement_threshold'],
            ':is_active' => (int)$data['is_active'],
        ]);

        $courseId = (int)$this->db->lastInsertId();
        $this->syncTeacher($courseId, (int)($data['teacher_id'] ?? 0));

        return $courseId;
    }

    public function updateCourse(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE courses
            SET course_code = :course_code,
                course_name = :course_name,
                semester = :semester,
                absence_threshold = :absence_threshold,
                low_engagement_threshold = :low_engagement_threshold,
                is_active = :is_active
            WHERE id = :id
        ");

        $updated = $stmt->execute([
            ':id' => $id,
            ':course_code' => $data['course_code'],
            ':course_name' => $data['course_name'],
            ':semester' => $data['semester'],
            ':absence_threshold' => (int)$data['absence_threshold'],
            ':low_engagement_threshold' => (float)$data['low_engagement_threshold'],
            ':is_active' => (int)$data['is_active'],
        ]);

        $this->syncTeacher($id, (int)($data['teacher_id'] ?? 0));

        return $updated;
    }

    public function archiveCourse(int $id): bool
    {
        $stmt = $this->db->prepare('UPDATE courses SET is_active = 0 WHERE id = :id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function courseCodeExists(string $courseCode, string $semester, ?int $excludeId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM courses WHERE course_code = :course_code AND semester = :semester';
        if ($excludeId !== null) {
            $sql .= ' AND id <> :exclude_id';
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':course_code', $courseCode);
        $stmt->bindValue(':semester', $semester);
        if ($excludeId !== null) {
            $stmt->bindValue(':exclude_id', $excludeId, PDO::PARAM_INT);
        }
        $stmt->execute();

        return (int)$stmt->fetchColumn() > 0;
    }

    public function teacherExists(int $teacherId): bool
    {
        if ($teacherId <= 0) {
            return true;
        }

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE id = :id AND role = 'teacher' AND is_active = 1");
        $stmt->bindValue(':id', $teacherId, PDO::PARAM_INT);
        $stmt->execute();

        return (int)$stmt->fetchColumn() > 0;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    private function syncTeacher(int $courseId, int $teacherId): void
    {
        $delete = $this->db->prepare("DELETE FROM enrollments WHERE course_id = :course_id AND role = 'teacher'");
        $delete->bindValue(':course_id', $courseId, PDO::PARAM_INT);
        $delete->execute();

        if ($teacherId <= 0) {
            return;
        }

        $insert = $this->db->prepare("
            INSERT IGNORE INTO enrollments (course_id, user_id, role)
            VALUES (:course_id, :user_id, 'teacher')
        ");
        $insert->bindValue(':course_id', $courseId, PDO::PARAM_INT);
        $insert->bindValue(':user_id', $teacherId, PDO::PARAM_INT);
        $insert->execute();
    }

    private function buildWhere(array $filters): array
    {
        $conditions = [];
        $params = [];

        $search = trim((string)($filters['search'] ?? ''));
        if ($search !== '') {
            $conditions[] = '(c.course_code LIKE :search OR c.course_name LIKE :search OR c.semester LIKE :search OR t.teacher_names LIKE :search)';
            $params[':search'] = '%' . $search . '%';
        }

        $status = (string)($filters['status'] ?? '');
        if ($status === 'active') {
            $conditions[] = 'c.is_active = 1';
        } elseif ($status === 'inactive') {
            $conditions[] = 'c.is_active = 0';
        }

        $whereSql = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
        return [$whereSql, $params];
    }

    private function bindParams(PDOStatement $stmt, array $params): void
    {
        foreach ($params as $name => $value) {
            $stmt->bindValue($name, $value);
        }
    }

    private function buildOrderBy(string $sort): string
    {
        return match ($sort) {
            'oldest' => 'ORDER BY c.created_at ASC, c.id ASC',
            'code_asc' => 'ORDER BY c.course_code ASC, c.semester DESC',
            'name_asc' => 'ORDER BY c.course_name ASC, c.id ASC',
            'students_desc' => 'ORDER BY student_count DESC, c.course_code ASC',
            default => 'ORDER BY c.created_at DESC, c.id DESC',
        };
    }
}
