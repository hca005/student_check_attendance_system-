<?php
// ============================================================
// config/Database.php
// Singleton Pattern – chỉ tạo đúng 1 kết nối PDO duy nhất
// Điểm cộng kỹ thuật: Singleton Pattern (+5%)
// ============================================================

require_once __DIR__ . '/config.php';

class Database
{
    /** @var Database|null Instance duy nhất */
    private static ?Database $instance = null;

    /** @var PDO Kết nối PDO */
    private PDO $pdo;

    // ── Constructor private → không cho new Database() từ ngoài ──
    private function __construct()
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // throw exception khi lỗi
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,          // fetch mảng kết hợp
            PDO::ATTR_EMULATE_PREPARES   => false,                     // dùng prepared statement thật
        ];

        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Không lộ thông tin kết nối khi production
            if (APP_ENV === 'development') {
                die('<h3 style="color:red">Database Error: ' . $e->getMessage() . '</h3>');
            } else {
                die('<h3>Lỗi hệ thống. Vui lòng thử lại sau.</h3>');
            }
        }
    }

    // ── Chặn clone ─────────────────────────────────────────
    private function __clone() {}

    // ── Chặn unserialize ───────────────────────────────────
    public function __wakeup(): void
    {
        throw new \RuntimeException('Không thể unserialize singleton Database.');
    }

    // ── Lấy instance (tạo nếu chưa có) ────────────────────
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // ── Trả về object PDO để dùng trực tiếp ───────────────
    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    // ── Helper: chuẩn bị và thực thi câu query nhanh ──────
    public function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    // ── Helper: lấy lastInsertId ───────────────────────────
    public function lastId(): string
    {
        return $this->pdo->lastInsertId();
    }
}