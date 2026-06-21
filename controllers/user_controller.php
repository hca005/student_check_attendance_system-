<?php

require_once __DIR__ . '/../models/user_model.php';
require_once __DIR__ . '/../helpers/middleware.php';

class UserController
{
    private UserModel $model;

    public function __construct()
    {
        $this->model = new UserModel();
    }

    public function index(): void
    {
        Middleware::requireAdmin();

        $filters = [
            'search' => trim($_GET['search'] ?? ''),
            'role' => $_GET['role'] ?? '',
            'status' => $_GET['status'] ?? '',
            'sort' => $_GET['sort'] ?? 'newest',
            'page' => max(1, (int)($_GET['p'] ?? 1)),
        ];

        $users = $this->model->getAllUsers($filters);
        $totalCount = $this->model->countUsers($filters);
        $stats = $this->model->getUserStats();
        $perPage = $this->model->getPerPage();
        $totalPages = max(1, (int)ceil($totalCount / $perPage));
        $currentPageNum = max(1, min($filters['page'], $totalPages));

        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError = $_SESSION['flash_error'] ?? null;
        $createErrors = $_SESSION['create_errors'] ?? [];
        $createOld = $_SESSION['create_old'] ?? [];
        $showCreateModal = (bool)($_SESSION['show_create_modal'] ?? false);
        unset(
            $_SESSION['flash_success'],
            $_SESSION['flash_error'],
            $_SESSION['create_errors'],
            $_SESSION['create_old'],
            $_SESSION['show_create_modal']
        );

        require APP_ROOT . '/views/admin/users/index.php';
    }

    public function create(): void
    {
        Middleware::requireAdmin();
        $_SESSION['show_create_modal'] = true;
        header('Location: ' . APP_URL . '/index.php?page=admin_users');
        exit;
    }

    public function store(): void
    {
        Middleware::requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['flash_error'] = 'Invalid request method.';
            $this->redirectList();
        }

        $data = $this->collectPayload($_POST);
        $errors = $this->validate($data, null, true);

        if ($errors) {
            $_SESSION['create_errors'] = $errors;
            $_SESSION['create_old'] = $data;
            $_SESSION['show_create_modal'] = true;
            $this->redirectList();
        }

        $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        $ok = $this->model->createUser($data);

        $_SESSION['flash_' . ($ok ? 'success' : 'error')] = $ok
            ? 'User created successfully.'
            : 'Unable to create user.';
        $this->redirectList();
    }

    public function edit(): void
    {
        Middleware::requireAdmin();
        $id = (int)($_GET['id'] ?? 0);
        $user = $this->model->getUserById($id);
        if (!$user) {
            $_SESSION['flash_error'] = 'User not found.';
            $this->redirectList();
        }

        $errors = [];
        $old = $user;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old = array_merge($old, $this->collectPayload($_POST));
            $errors = $this->validate($old, $id, false);
            if (!$errors) {
                $payload = [
                    'full_name' => $old['full_name'],
                    'email' => $old['email'],
                    'role' => $old['role'],
                    'student_code' => $old['student_code'],
                    'is_active' => $old['is_active'],
                    'gender' => $old['gender'] ?? null,
                    'date_of_birth' => $old['date_of_birth'] ?? null,
                    'id_card_number' => $old['id_card_number'] ?? null,
                    'hometown' => $old['hometown'] ?? null,
                    'phone' => $old['phone'] ?? null,
                    'department' => $old['department'] ?? null,
                    'qualification' => $old['qualification'] ?? null,
                    'class_name' => $old['class_name'] ?? null,
                    'academic_year' => $old['academic_year'] ?? null,
                ];
                if (!empty($old['password'])) {
                    $payload['password_hash'] = password_hash($old['password'], PASSWORD_DEFAULT);
                }

                $ok = $this->model->updateUser($id, $payload);
                $_SESSION['flash_' . ($ok ? 'success' : 'error')] = $ok
                    ? 'User updated successfully.'
                    : 'Unable to update user.';
                $this->redirectList();
            }
        }

        require APP_ROOT . '/views/admin/users/edit.php';
    }

    public function update(): void
    {
        // Kept for backward compatibility with old route.
        $this->edit();
    }

    public function deactivate(): void
    {
        Middleware::requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['flash_error'] = 'Invalid request method.';
            $this->redirectList();
        }

        $id = (int)($_GET['id'] ?? 0);
        $sessionUser = Middleware::user();
        if ($id > 0 && $id === (int)($sessionUser['id'] ?? 0)) {
            $_SESSION['flash_error'] = 'You cannot deactivate your own account.';
            $this->redirectList();
        }

        $ok = $this->model->setActiveStatus($id, 0);
        $_SESSION['flash_' . ($ok ? 'success' : 'error')] = $ok
            ? 'User deactivated.'
            : 'Unable to deactivate user.';
        $this->redirectList();
    }

    public function activate(): void
    {
        Middleware::requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['flash_error'] = 'Invalid request method.';
            $this->redirectList();
        }

        $id = (int)($_GET['id'] ?? 0);
        $ok = $this->model->setActiveStatus($id, 1);
        $_SESSION['flash_' . ($ok ? 'success' : 'error')] = $ok
            ? 'User activated.'
            : 'Unable to activate user.';
        $this->redirectList();
    }

    public function show(): void
    {
        Middleware::requireAdmin();
        $id = (int)($_GET['id'] ?? 0);
        $user = $this->model->getUserById($id);
        if (!$user) {
            $_SESSION['flash_error'] = 'User not found.';
            $this->redirectList();
        }

        // Fetch additional data based on role
        $db = Database::getInstance()->getConnection();
        $attendanceStats = null;
        $attendanceHistory = [];
        $teacherClasses = [];

        if ($user['role'] === 'student') {
            // Calculate attendance stats
            $stmt = $db->prepare("
                SELECT 
                    COUNT(*) as total_sessions,
                    SUM(CASE WHEN ar.status = 'present' THEN 1 ELSE 0 END) as present_count,
                    SUM(CASE WHEN ar.status = 'late' THEN 1 ELSE 0 END) as late_count,
                    SUM(CASE WHEN ar.status = 'absent' THEN 1 ELSE 0 END) as absent_count
                FROM attendance_records ar
                JOIN class_sessions cs ON ar.session_id = cs.id
                WHERE ar.student_id = ?
            ");
            $stmt->execute([$id]);
            $attendanceStats = $stmt->fetch(PDO::FETCH_ASSOC);

            // Fetch history
            $stmt2 = $db->prepare("
                SELECT c.course_name, cs.title, cs.session_date, ar.status, ar.created_at as recorded_at
                FROM attendance_records ar
                JOIN class_sessions cs ON ar.session_id = cs.id
                JOIN courses c ON cs.course_id = c.id
                WHERE ar.student_id = ?
                ORDER BY cs.session_date DESC
                LIMIT 20
            ");
            $stmt2->execute([$id]);
            $attendanceHistory = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        } elseif ($user['role'] === 'teacher') {
            $stmt = $db->prepare("
                SELECT c.course_code, c.course_name, COUNT(cs.id) as session_count
                FROM courses c
                LEFT JOIN class_sessions cs ON c.id = cs.course_id AND cs.teacher_id = ?
                WHERE c.id IN (SELECT DISTINCT course_id FROM class_sessions WHERE teacher_id = ?)
                GROUP BY c.id
            ");
            $stmt->execute([$id, $id]);
            $teacherClasses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        require APP_ROOT . '/views/admin/users/show.php';
    }

    private function collectPayload(array $source): array
    {
        return [
            'full_name' => trim((string)($source['full_name'] ?? '')),
            'email' => trim((string)($source['email'] ?? '')),
            'password' => (string)($source['password'] ?? ''),
            'role' => (string)($source['role'] ?? 'student'),
            'student_code' => trim((string)($source['student_code'] ?? '')),
            'is_active' => (int)($source['is_active'] ?? 1),
            'gender' => !empty($source['gender']) ? $source['gender'] : null,
            'date_of_birth' => !empty($source['date_of_birth']) ? $source['date_of_birth'] : null,
            'id_card_number' => !empty($source['id_card_number']) ? trim($source['id_card_number']) : null,
            'hometown' => !empty($source['hometown']) ? trim($source['hometown']) : null,
            'phone' => !empty($source['phone']) ? trim($source['phone']) : null,
            'department' => !empty($source['department']) ? trim($source['department']) : null,
            'qualification' => !empty($source['qualification']) ? trim($source['qualification']) : null,
            'class_name' => !empty($source['class_name']) ? trim($source['class_name']) : null,
            'academic_year' => !empty($source['academic_year']) ? trim($source['academic_year']) : null,
        ];
    }

    private function validate(array $data, ?int $excludeId, bool $requirePassword): array
    {
        $errors = [];

        if ($data['full_name'] === '') {
            $errors['full_name'] = 'Full name is required.';
        }

        if ($data['email'] === '') {
            $errors['email'] = 'Email is required.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email is invalid.';
        } elseif ($this->model->emailExists($data['email'], $excludeId)) {
            $errors['email'] = 'Email already exists.';
        }

        if (!in_array($data['role'], ['admin', 'teacher', 'student'], true)) {
            $errors['role'] = 'Invalid role.';
        }

        if ($requirePassword && $data['password'] === '') {
            $errors['password'] = 'Password is required.';
        } elseif ($data['password'] !== '' && strlen($data['password']) < 6) {
            $errors['password'] = 'Password must be at least 6 characters.';
        }

        if ($data['role'] === 'student' && $data['student_code'] === '') {
            $errors['student_code'] = 'Student code is required for student role.';
        }

        return $errors;
    }

    private function redirectList(): void
    {
        header('Location: ' . APP_URL . '/index.php?page=admin_users');
        exit;
    }
}
