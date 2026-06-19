<?php
// ============================================================
// helpers/Middleware.php
// Phân quyền RBAC: Admin / Teacher / Student
// Điểm cộng kỹ thuật: RBAC Middleware (+5–10%)
// ============================================================

class Middleware
{
    // ── Khởi động session nếu chưa có ─────────────────────
    private static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => SESSION_LIFETIME,
                'path'     => '/',
                'secure'   => false,      // đổi true khi dùng HTTPS
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
        }
    }

    // ── Yêu cầu đã đăng nhập ──────────────────────────────
    public static function requireLogin(): void
    {
        self::startSession();
        if (empty($_SESSION['user_id'])) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '';
            header('Location: ' . APP_URL . '/login.php');
            exit;
        }
    }

    // ── Yêu cầu một hoặc nhiều role cụ thể ────────────────
    public static function requireRole(string|array $roles): void
    {
        self::requireLogin();
        $roles = (array) $roles;
        if (!in_array($_SESSION['role'] ?? '', $roles, true)) {
            http_response_code(403);
            require_once APP_ROOT . '/views/errors/403.php';
            exit;
        }
    }

    // ── Shortcut cho từng role ─────────────────────────────
    public static function requireAdmin(): void
    {
        self::requireRole('admin');
    }

    // Teacher hoặc Admin đều được phép
    public static function requireTeacher(): void
    {
        self::requireRole(['admin', 'teacher']);
    }

    public static function requireStudent(): void
    {
        self::requireRole('student');
    }

    // Compatibility aliases used by some legacy pages.
    public static function admin(): void
    {
        self::requireAdmin();
    }

    public static function teacher(): void
    {
        self::requireTeacher();
    }

    public static function student(): void
    {
        self::requireStudent();
    }

    // ── Chặn user đã login vào trang guest (login page) ───
    public static function guest(): void
    {
        self::startSession();
        if (!empty($_SESSION['user_id'])) {
            self::redirectByRole($_SESSION['role']);
        }
    }

    // ── Redirect theo role sau khi login ──────────────────
    public static function redirectByRole(string $role): void
    {
        switch ($role) {
            case 'admin':
                header('Location: ' . APP_URL . '/admin/dashboard.php');
                break;
            case 'teacher':
                header('Location: ' . APP_URL . '/teacher/dashboard.php');
                break;
            case 'student':
                header('Location: ' . APP_URL . '/student/dashboard.php');
                break;
            default:
                header('Location: ' . APP_URL . '/login.php');
        }
        exit;
    }

    // ── Lấy thông tin user hiện tại từ session ────────────
    public static function user(): array
    {
        self::startSession();
        return [
            'id'        => $_SESSION['user_id']   ?? null,
            'full_name' => $_SESSION['full_name']  ?? '',
            'email'     => $_SESSION['email']      ?? '',
            'role'      => $_SESSION['role']       ?? '',
        ];
    }

    // ── Kiểm tra role không redirect ──────────────────────
    public static function is(string $role): bool
    {
        self::startSession();
        return ($_SESSION['role'] ?? '') === $role;
    }
}
