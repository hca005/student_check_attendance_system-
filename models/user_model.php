<?php
// ============================================================
// models/user_model.php
// Repository Pattern – toàn bộ truy vấn liên quan đến bảng users
// ============================================================

require_once __DIR__ . '/../config/database.php';

class UserModel
{
    private PDO $db;
    private int $perPage = 6;   // Số dòng / trang

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    // ── GET ALL (phân trang + filter) ─────────────────────
    public function getAllUsers(array $filters = []): array
    {
        [$where, $params] = $this->buildWhere($filters);
        $orderBy = $this->buildOrderBy($filters['sort'] ?? 'newest');

        $page   = max(1, (int)($filters['page'] ?? 1));
        $offset = ($page - 1) * $this->perPage;

        $sql  = "SELECT * FROM users $where $orderBy LIMIT {$this->perPage} OFFSET $offset";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── COUNT (cho pagination) ────────────────────────────
    public function countUsers(array $filters = []): int
    {
        [$where, $params] = $this->buildWhere($filters);
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users $where");
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    // ── WHERE builder – an toàn, dùng placeholder ─────────
    private function buildWhere(array $filters): array
    {
        $conditions = ['1=1'];
        $params     = [];

        if (!empty($filters['search'])) {
            $conditions[] = "(full_name LIKE ? OR email LIKE ? OR student_code LIKE ?)";
            $s = '%' . $filters['search'] . '%';
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
        }

        if (!empty($filters['role']) &&
            in_array($filters['role'], ['admin','teacher','student'], true)) {
            $conditions[] = "role = ?";
            $params[]     = $filters['role'];
        }

        if (isset($filters['status']) &&
            $filters['status'] !== '' &&
            $filters['status'] !== 'all') {
            $conditions[] = "is_active = ?";
            $params[]     = (int)$filters['status'];
        }

        return ['WHERE ' . implode(' AND ', $conditions), $params];
    }

    private function buildOrderBy(string $sort): string
    {
        return match($sort) {
            'oldest' => 'ORDER BY created_at ASC',
            'name'   => 'ORDER BY full_name ASC',
            default  => 'ORDER BY created_at DESC',
        };
    }

    // ── GET BY ID ─────────────────────────────────────────
    public function getUserById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    // ── EMAIL EXISTS CHECK ────────────────────────────────
    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        if ($excludeId !== null) {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) FROM users WHERE email = ? AND id != ?"
            );
            $stmt->execute([$email, $excludeId]);
        } else {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) FROM users WHERE email = ?"
            );
            $stmt->execute([$email]);
        }
        return (int)$stmt->fetchColumn() > 0;
    }

    // ── CREATE ────────────────────────────────────────────
    public function createUser(array $data): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO users (full_name, email, password_hash, role, student_code, is_active)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        return $stmt->execute([
            $data['full_name'],
            $data['email'],
            $data['password_hash'],
            $data['role'],
            $data['student_code'] ?? null,
            $data['is_active']    ?? 1,
        ]);
    }

    // ── UPDATE ────────────────────────────────────────────
    public function updateUser(int $id, array $data): bool
    {
        $sets   = ['full_name = ?', 'email = ?', 'role = ?', 'student_code = ?', 'is_active = ?'];
        $params = [
            $data['full_name'],
            $data['email'],
            $data['role'],
            $data['student_code'] ?? null,
            (int)($data['is_active'] ?? 1),
        ];

        if (!empty($data['password_hash'])) {
            $sets[]   = 'password_hash = ?';
            $params[] = $data['password_hash'];
        }

        $params[] = $id;
        $sql      = "UPDATE users SET " . implode(', ', $sets) . " WHERE id = ?";
        return $this->db->prepare($sql)->execute($params);
    }

    // ── ACTIVATE / DEACTIVATE ────────────────────────────
    public function setActiveStatus(int $id, int $status): bool
    {
        $stmt = $this->db->prepare("UPDATE users SET is_active = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    // ── STATS cho stat cards ──────────────────────────────
    public function getUserStats(): array
    {
        return [
            'total'    => (int)$this->db->query("SELECT COUNT(*) FROM users")->fetchColumn(),
            'active'   => (int)$this->db->query("SELECT COUNT(*) FROM users WHERE is_active = 1")->fetchColumn(),
            'teachers' => (int)$this->db->query("SELECT COUNT(*) FROM users WHERE role = 'teacher'")->fetchColumn(),
            'students' => (int)$this->db->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn(),
        ];
    }

    public function getPerPage(): int { return $this->perPage; }
}
