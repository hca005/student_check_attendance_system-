<?php

require_once __DIR__ . '/../config/database.php';

class UserModel
{
    private PDO $db;
    private int $perPage = 10;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAllUsers(array $filters = []): array
    {
        [$whereSql, $params] = $this->buildWhere($filters);
        $orderSql = $this->buildOrderBy($filters['sort'] ?? 'newest');

        $page = max(1, (int)($filters['page'] ?? 1));
        $offset = ($page - 1) * $this->perPage;

        $sql = "
            SELECT id, full_name, email, role, student_code, is_active, created_at, updated_at
            FROM users
            {$whereSql}
            {$orderSql}
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $this->db->prepare($sql);
        $this->bindWhereParams($stmt, $params);
        $stmt->bindValue(':limit', $this->perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countUsers(array $filters = []): int
    {
        [$whereSql, $params] = $this->buildWhere($filters);
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users {$whereSql}");
        $this->bindWhereParams($stmt, $params);
        $stmt->execute();

        return (int)$stmt->fetchColumn();
    }

    public function getUserById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT id, full_name, email, password_hash, role, student_code, is_active, created_at, updated_at
            FROM users
            WHERE id = :id
        ");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM users WHERE email = :email';

        if ($excludeId !== null) {
            $sql .= ' AND id <> :exclude_id';
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':email', $email);

        if ($excludeId !== null) {
            $stmt->bindValue(':exclude_id', $excludeId, PDO::PARAM_INT);
        }

        $stmt->execute();
        return (int)$stmt->fetchColumn() > 0;
    }

    public function createUser(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO users (full_name, email, password_hash, role, student_code, is_active)
            VALUES (:full_name, :email, :password_hash, :role, :student_code, :is_active)
        ");

        return $stmt->execute([
            ':full_name' => $data['full_name'],
            ':email' => $data['email'],
            ':password_hash' => $data['password_hash'],
            ':role' => $data['role'],
            ':student_code' => $data['student_code'],
            ':is_active' => (int)$data['is_active'],
        ]);
    }

    public function updateUser(int $id, array $data): bool
    {
        $sets = [
            'full_name = :full_name',
            'email = :email',
            'role = :role',
            'student_code = :student_code',
            'is_active = :is_active',
        ];

        $params = [
            ':id' => $id,
            ':full_name' => $data['full_name'],
            ':email' => $data['email'],
            ':role' => $data['role'],
            ':student_code' => $data['student_code'],
            ':is_active' => (int)$data['is_active'],
        ];

        if (!empty($data['password_hash'])) {
            $sets[] = 'password_hash = :password_hash';
            $params[':password_hash'] = $data['password_hash'];
        }

        $sql = 'UPDATE users SET ' . implode(', ', $sets) . ' WHERE id = :id';
        $stmt = $this->db->prepare($sql);

        return $stmt->execute($params);
    }

    public function setActiveStatus(int $id, int $status): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET is_active = :status WHERE id = :id');
        $stmt->bindValue(':status', $status === 1 ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function getUserStats(): array
    {
        $stmt = $this->db->query("
            SELECT
                COUNT(*) AS total,
                COALESCE(SUM(is_active = 1), 0) AS active,
                COALESCE(SUM(is_active = 0), 0) AS inactive,
                COALESCE(SUM(role = 'admin'), 0) AS admins,
                COALESCE(SUM(role = 'teacher'), 0) AS teachers,
                COALESCE(SUM(role = 'student'), 0) AS students
            FROM users
        ");

        $stats = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'total' => (int)($stats['total'] ?? 0),
            'active' => (int)($stats['active'] ?? 0),
            'inactive' => (int)($stats['inactive'] ?? 0),
            'admins' => (int)($stats['admins'] ?? 0),
            'teachers' => (int)($stats['teachers'] ?? 0),
            'students' => (int)($stats['students'] ?? 0),
        ];
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
            $conditions[] = '(full_name LIKE :search OR email LIKE :search OR student_code LIKE :search)';
            $params[':search'] = '%' . $search . '%';
        }

        $role = (string)($filters['role'] ?? '');
        if (in_array($role, ['admin', 'teacher', 'student'], true)) {
            $conditions[] = 'role = :role';
            $params[':role'] = $role;
        }

        $status = (string)($filters['status'] ?? '');
        if ($status === '0' || $status === '1') {
            $conditions[] = 'is_active = :status';
            $params[':status'] = (int)$status;
        }

        $whereSql = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

        return [$whereSql, $params];
    }

    private function bindWhereParams(PDOStatement $stmt, array $params): void
    {
        foreach ($params as $name => $value) {
            $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($name, $value, $type);
        }
    }

    private function buildOrderBy(string $sort): string
    {
        return match ($sort) {
            'oldest' => 'ORDER BY created_at ASC, id ASC',
            'name_asc' => 'ORDER BY full_name ASC, id ASC',
            'name_desc' => 'ORDER BY full_name DESC, id DESC',
            default => 'ORDER BY created_at DESC, id DESC',
        };
    }
}
