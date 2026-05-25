<?php

require_once __DIR__ . '/../models/user_model.php';
require_once __DIR__ . '/../helpers/middleware.php';

class UserController
{
    private UserModel $model;
    private array $validRoles = ['admin', 'teacher', 'student'];

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

        $totalCount = $this->model->countUsers($filters);
        $perPage = $this->model->getPerPage();
        $totalPages = max(1, (int)ceil($totalCount / $perPage));
        $filters['page'] = min($filters['page'], $totalPages);

        $users = $this->model->getAllUsers($filters);
        $stats = $this->model->getUserStats();
        $currentPageNumber = $filters['page'];

        $success = $_SESSION['flash_success'] ?? null;
        $error = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        $pageTitle = 'Admin User Management';
        $currentPage = 'admin.users';

        require APP_ROOT . '/views/admin/users/index.php';
    }

    public function create(): void
    {
        Middleware::requireAdmin();

        $errors = $_SESSION['form_errors'] ?? [];
        $old = $_SESSION['form_old'] ?? [
            'full_name' => '',
            'email' => '',
            'role' => 'student',
            'student_code' => '',
            'is_active' => 1,
        ];
        unset($_SESSION['form_errors'], $_SESSION['form_old']);

        $pageTitle = 'Add New User';
        $currentPage = 'admin.users';

        require APP_ROOT . '/views/admin/users/create.php';
    }

    public function store(): void
    {
        Middleware::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['flash_error'] = 'Invalid request method.';
            $this->redirectToCreate();
        }

        $data = $this->readUserForm();
        $errors = $this->validateCreate($data);

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_old'] = $data;
            $this->redirectToCreate();
        }

        $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);

        try {
            $created = $this->model->createUser($data);
        } catch (PDOException $e) {
            $created = false;
            $_SESSION['flash_error'] = 'Cannot create user because the email may already exist.';
        }

        if ($created) {
            $_SESSION['flash_success'] = 'User created successfully.';
        } elseif (empty($_SESSION['flash_error'])) {
            $_SESSION['flash_error'] = 'Failed to create user. Please try again.';
        }

        $this->redirectToList();
    }

    public function edit(): void
    {
        Middleware::requireAdmin();

        $id = $this->getRequestedId();
        $user = $this->model->getUserById($id);

        if (!$user) {
            $_SESSION['flash_error'] = 'User not found.';
            $this->redirectToList();
        }

        $errors = $_SESSION['form_errors'] ?? [];
        $old = $_SESSION['form_old'] ?? $user;
        unset($_SESSION['form_errors'], $_SESSION['form_old']);

        $pageTitle = 'Edit User';
        $currentPage = 'admin.users';

        require APP_ROOT . '/views/admin/users/edit.php';
    }

    public function update(): void
    {
        Middleware::requireAdmin();

        $id = $this->getRequestedId();
        $user = $this->model->getUserById($id);

        if (!$user) {
            $_SESSION['flash_error'] = 'User not found.';
            $this->redirectToList();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['flash_error'] = 'Invalid request method.';
            $this->redirectToEdit($id);
        }

        $data = $this->readUserForm(false);
        $errors = $this->validateUpdate($data, $id);

        if ($this->isCurrentUser($id) && (int)$data['is_active'] === 0) {
            $errors['is_active'] = 'You cannot deactivate your own account.';
        }

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_old'] = $data;
            $this->redirectToEdit($id);
        }

        $updateData = [
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'student_code' => $data['student_code'],
            'is_active' => $data['is_active'],
        ];

        if ($data['password'] !== '') {
            $updateData['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        try {
            $updated = $this->model->updateUser($id, $updateData);
        } catch (PDOException $e) {
            $updated = false;
            $_SESSION['flash_error'] = 'Cannot update user because the email may already belong to another user.';
        }

        if ($updated) {
            $_SESSION['flash_success'] = 'User updated successfully.';
        } elseif (empty($_SESSION['flash_error'])) {
            $_SESSION['flash_error'] = 'Failed to update user. Please try again.';
        }

        $this->redirectToList();
    }

    public function deactivate(): void
    {
        Middleware::requireAdmin();

        $id = $this->getRequestedId();

        if ($this->isCurrentUser($id)) {
            $_SESSION['flash_error'] = 'You cannot deactivate your own account.';
            $this->redirectToList();
        }

        $user = $this->model->getUserById($id);
        if (!$user) {
            $_SESSION['flash_error'] = 'User not found.';
            $this->redirectToList();
        }

        if ($this->model->setActiveStatus($id, 0)) {
            $_SESSION['flash_success'] = 'User deactivated successfully.';
        } else {
            $_SESSION['flash_error'] = 'Failed to deactivate user.';
        }

        $this->redirectToList();
    }

    public function activate(): void
    {
        Middleware::requireAdmin();

        $id = $this->getRequestedId();
        $user = $this->model->getUserById($id);

        if (!$user) {
            $_SESSION['flash_error'] = 'User not found.';
            $this->redirectToList();
        }

        if ($this->model->setActiveStatus($id, 1)) {
            $_SESSION['flash_success'] = 'User activated successfully.';
        } else {
            $_SESSION['flash_error'] = 'Failed to activate user.';
        }

        $this->redirectToList();
    }

    private function readUserForm(bool $passwordRequired = true): array
    {
        $role = $_POST['role'] ?? 'student';
        $studentCode = trim($_POST['student_code'] ?? '');

        return [
            'full_name' => trim($_POST['full_name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'password' => $passwordRequired ? ($_POST['password'] ?? '') : trim($_POST['password'] ?? ''),
            'role' => $role,
            'student_code' => $role === 'student' && $studentCode !== '' ? $studentCode : null,
            'is_active' => (isset($_POST['is_active']) && (string)$_POST['is_active'] === '0') ? 0 : 1,
        ];
    }

    private function validateCreate(array $data): array
    {
        $errors = $this->validateShared($data);

        if ($data['password'] === '') {
            $errors['password'] = 'Password is required.';
        } elseif (strlen($data['password']) < 6) {
            $errors['password'] = 'Password must be at least 6 characters.';
        }

        if (!isset($errors['email']) && $this->model->emailExists($data['email'])) {
            $errors['email'] = 'Email already exists.';
        }

        return $errors;
    }

    private function validateUpdate(array $data, int $excludeId): array
    {
        $errors = $this->validateShared($data);

        if ($data['password'] !== '' && strlen($data['password']) < 6) {
            $errors['password'] = 'New password must be at least 6 characters.';
        }

        if (!isset($errors['email']) && $this->model->emailExists($data['email'], $excludeId)) {
            $errors['email'] = 'Email already belongs to another user.';
        }

        return $errors;
    }

    private function validateShared(array $data): array
    {
        $errors = [];

        if ($data['full_name'] === '') {
            $errors['full_name'] = 'Full name is required.';
        } elseif (mb_strlen($data['full_name']) > 100) {
            $errors['full_name'] = 'Full name must be 100 characters or fewer.';
        }

        if ($data['email'] === '') {
            $errors['email'] = 'Email is required.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email format is invalid.';
        } elseif (mb_strlen($data['email']) > 100) {
            $errors['email'] = 'Email must be 100 characters or fewer.';
        }

        if (!in_array($data['role'], $this->validRoles, true)) {
            $errors['role'] = 'Role must be admin, teacher, or student.';
        }

        if (!in_array((int)$data['is_active'], [0, 1], true)) {
            $errors['is_active'] = 'Status is invalid.';
        }

        if ($data['student_code'] !== null && mb_strlen($data['student_code']) > 20) {
            $errors['student_code'] = 'Student code must be 20 characters or fewer.';
        }

        return $errors;
    }

    private function getRequestedId(): int
    {
        return max(0, (int)($_GET['id'] ?? 0));
    }

    private function isCurrentUser(int $id): bool
    {
        return $id > 0 && isset($_SESSION['user_id']) && $id === (int)$_SESSION['user_id'];
    }

    private function redirectToList(): void
    {
        header('Location: ' . APP_URL . '/index.php?page=admin_users');
        exit;
    }

    private function redirectToCreate(): void
    {
        header('Location: ' . APP_URL . '/index.php?page=admin_users_create');
        exit;
    }

    private function redirectToEdit(int $id): void
    {
        header('Location: ' . APP_URL . '/index.php?page=admin_users_edit&id=' . $id);
        exit;
    }
}
