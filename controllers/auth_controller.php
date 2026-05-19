<?php
// ============================================================
// controllers/AuthController.php
// Xử lý đăng nhập và đăng xuất
// ============================================================

require_once APP_ROOT . '/config/Database.php';
require_once APP_ROOT . '/helpers/Middleware.php';

class AuthController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    // ──────────────────────────────────────────────────────
    // GET  /login.php → hiển thị form
    // POST /login.php → xử lý đăng nhập
    // ──────────────────────────────────────────────────────
    public function login(): void
    {
        // Nếu đã đăng nhập → về dashboard ngay
        Middleware::guest();

        $error  = null;
        $oldEmail = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email    = trim($_POST['email']    ?? '');
            $password =      $_POST['password'] ?? '';
            $oldEmail = htmlspecialchars($email);

            // ── Validate frontend-safety check phía server ─
            if (empty($email) || empty($password)) {
                $error = 'Vui lòng nhập đầy đủ email và mật khẩu.';

            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Email không đúng định dạng.';

            } else {
                // ── Truy vấn user theo email ───────────────
                $stmt = $this->db->prepare(
                    'SELECT id, full_name, email, password_hash, role, is_active
                     FROM users WHERE email = ? LIMIT 1'
                );
                $stmt->execute([$email]);
                $user = $stmt->fetch();

                if (!$user || !password_verify($password, $user['password_hash'])) {
                    $error = 'Email hoặc mật khẩu không đúng.';

                } elseif (!$user['is_active']) {
                    $error = 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ Admin.';

                } else {
                    // ── Đăng nhập thành công ───────────────
                    // Tái tạo session ID để chống session fixation
                    session_regenerate_id(true);

                    $_SESSION['user_id']   = (int) $user['id'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['email']     = $user['email'];
                    $_SESSION['role']      = $user['role'];
                    $_SESSION['login_at']  = time();

                    Middleware::redirectByRole($user['role']);
                }
            }
        }

        // ── Render view ────────────────────────────────────
        require_once APP_ROOT . '/views/auth/login.php';
    }

    // ──────────────────────────────────────────────────────
    // GET /logout.php → hủy session và về trang login
    // ──────────────────────────────────────────────────────
    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Xóa toàn bộ session data
        $_SESSION = [];

        // Xóa session cookie
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(
                session_name(), '', time() - 42000,
                $p['path'], $p['domain'], $p['secure'], $p['httponly']
            );
        }

        session_destroy();

        header('Location: ' . APP_URL . '/login.php?logged_out=1');
        exit;
    }
}