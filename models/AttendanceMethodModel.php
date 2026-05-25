<?php
// ============================================================
// models/AttendanceMethodModel.php
// Quản lý phương thức điểm danh (QR/OTP/Manual)
// Repository Pattern – CRUD + helper methods
// ============================================================

require_once APP_ROOT . '/config/Database.php';

class AttendanceMethodModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    // ──────────────────────────────────────────────────────
    // CREATE – Tạo phương thức điểm danh
    // ──────────────────────────────────────────────────────
    public function create(int $sessionId, string $methodType, ?string $token = null, ?\DateTime $expiresAt = null): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO attendance_methods (session_id, method_type, token, expires_at, is_active)
             VALUES (?, ?, ?, ?, 1)'
        );
        $expiresAtStr = $expiresAt ? $expiresAt->format('Y-m-d H:i:s') : null;
        $stmt->execute([$sessionId, $methodType, $token, $expiresAtStr]);
        return (int) $this->db->lastInsertId();
    }

    // ──────────────────────────────────────────────────────
    // READ – Lấy 1 phương thức theo ID
    // ──────────────────────────────────────────────────────
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, session_id, method_type, token, expires_at, is_active, created_at
             FROM attendance_methods WHERE id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    // ──────────────────────────────────────────────────────
    // READ – Lấy tất cả phương thức theo session
    // ──────────────────────────────────────────────────────
    public function getBySessionId(int $sessionId): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, session_id, method_type, token, expires_at, is_active, created_at
             FROM attendance_methods WHERE session_id = ? ORDER BY created_at DESC'
        );
        $stmt->execute([$sessionId]);
        return $stmt->fetchAll();
    }

    // ──────────────────────────────────────────────────────
    // READ – Lấy phương thức active nhất theo session
    // ──────────────────────────────────────────────────────
    public function getActiveBySessionId(int $sessionId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, session_id, method_type, token, expires_at, is_active, created_at
             FROM attendance_methods WHERE session_id = ? AND is_active = 1 LIMIT 1'
        );
        $stmt->execute([$sessionId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    // ──────────────────────────────────────────────────────
    // UPDATE – Cập nhật phương thức
    // ──────────────────────────────────────────────────────
    public function update(int $id, array $data): bool
    {
        $allowedFields = ['method_type', 'token', 'expires_at', 'is_active'];
        $updates = [];
        $values = [];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                if ($field === 'expires_at' && $data[$field] instanceof DateTime) {
                    $values[] = $data[$field]->format('Y-m-d H:i:s');
                } else {
                    $values[] = $data[$field];
                }
            }
        }

        if (empty($updates)) {
            return false;
        }

        $values[] = $id;
        $sql = 'UPDATE attendance_methods SET ' . implode(', ', $updates) . ' WHERE id = ?';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }

    // ──────────────────────────────────────────────────────
    // DELETE – Xóa phương thức
    // ──────────────────────────────────────────────────────
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM attendance_methods WHERE id = ?');
        return $stmt->execute([$id]);
    }

    // ──────────────────────────────────────────────────────
    // HELPER – Sinh token QR (dạng hex)
    // ──────────────────────────────────────────────────────
    public static function generateQrToken(): string
    {
        return bin2hex(random_bytes(16));
    }

    // ──────────────────────────────────────────────────────
    // HELPER – Sinh mã OTP 6 chữ số
    // ──────────────────────────────────────────────────────
    public static function generateOtp(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    // ──────────────────────────────────────────────────────
    // HELPER – Kiểm tra token/OTP còn hiệu lực không
    // ──────────────────────────────────────────────────────
    public function isTokenValid(int $id): bool
    {
        $method = $this->getById($id);
        if (!$method) {
            return false;
        }

        // Nếu không có expiry hoặc chưa hết hạn
        if (!$method['expires_at']) {
            return true;
        }

        $expiresAt = new DateTime($method['expires_at']);
        $now = new DateTime();
        return $now < $expiresAt;
    }

    // ──────────────────────────────────────────────────────
    // HELPER – Lấy phương thức theo token
    // ──────────────────────────────────────────────────────
    public function getByToken(string $token): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, session_id, method_type, token, expires_at, is_active, created_at
             FROM attendance_methods WHERE token = ? LIMIT 1'
        );
        $stmt->execute([$token]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    // ──────────────────────────────────────────────────────
    // DEACTIVATE – Vô hiệu hóa phương thức (kết thúc)
    // ──────────────────────────────────────────────────────
    public function deactivate(int $id): bool
    {
        return $this->update($id, ['is_active' => 0]);
    }
}
