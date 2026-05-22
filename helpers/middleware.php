<?php
if (!class_exists("Middleware")) { class Middleware
{
    private static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => SESSION_LIFETIME,
                'path'     => '/',
                'secure'   => false,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
        }
    }

    public static function requireLogin(): void
    {
        self::startSession();
        if (empty($_SESSION['user_id'])) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '';
            header('Location: ' . APP_URL . '/login.php');
            exit;
        }
    }

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

    public static function requireAdmin(): void { self::requireRole('admin'); }
    public static function requireTeacher(): void { self::requireRole(['admin', 'teacher']); }
    public static function teacher(): void { self::requireRole(['admin', 'teacher']); }
    public static function requireStudent(): void { self::requireRole('student'); }

    public static function guest(): void
    {
        self::startSession();
        if (!empty($_SESSION['user_id'])) {
            self::redirectByRole($_SESSION['role']);
        }
    }

    public static function redirectByRole(string $role): void
    {
        switch ($role) {
            case 'admin':   header('Location: ' . APP_URL . '/admin/dashboard.php'); break;
            case 'teacher': header('Location: ' . APP_URL . '/teacher/dashboard.php'); break;
            case 'student': header('Location: ' . APP_URL . '/student/dashboard.php'); break;
            default:        header('Location: ' . APP_URL . '/login.php');
        }
        exit;
    }

    public static function user(): array
    {
        self::startSession();
        return [
            'id'        => $_SESSION['user_id']  ?? null,
            'full_name' => $_SESSION['full_name'] ?? '',
            'email'     => $_SESSION['email']     ?? '',
            'role'      => $_SESSION['role']      ?? '',
        ];
    }

    public static function is(string $role): bool
    {
        self::startSession();
        return ($_SESSION['role'] ?? '') === $role;
    }
}}
