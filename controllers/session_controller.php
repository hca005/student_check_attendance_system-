<?php

require_once __DIR__ . '/../models/class_session_model.php';
require_once __DIR__ . '/../helpers/middleware.php';

class SessionController
{
    private ClassSessionModel $model;

    public function __construct()
    {
        $this->model = new ClassSessionModel();
    }

    public function index(): void
    {
        Middleware::requireAdmin();

        $filters = [
            'search' => trim($_GET['search'] ?? ''),
            'course_id' => (int)($_GET['course_id'] ?? 0),
            'status' => $_GET['status'] ?? '',
            'date' => $_GET['date'] ?? '',
            'page' => max(1, (int)($_GET['p'] ?? 1)),
        ];

        $sessions = $this->model->getSessions($filters);
        $totalCount = $this->model->countSessions($filters);
        $stats = $this->model->getStats();
        $courses = $this->model->getCourseOptions();
        $perPage = $this->model->getPerPage();
        $totalPages = max(1, (int)ceil($totalCount / $perPage));
        $currentPageNum = max(1, min($filters['page'], $totalPages));

        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        require APP_ROOT . '/views/admin/sessions/index.php';
    }

    public function create(): void
    {
        Middleware::requireAdmin();

        $mode = 'create';
        $formAction = APP_URL . '/index.php?page=admin_session_create';
        $courses = $this->model->getCourseOptions();
        $teachers = $this->model->getTeacherOptions();
        $session = [
            'course_id' => '',
            'teacher_id' => '',
            'title' => '',
            'session_date' => '',
            'start_time' => '',
            'end_time' => '',
            'status' => 'upcoming',
            'notes' => '',
        ];
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $session = $this->collectPayload($_POST);
            $errors = $this->validate($session, $courses, $teachers);

            if (empty($errors)) {
                if ($this->model->createSession($session)) {
                    $_SESSION['flash_success'] = 'Class session created successfully.';
                    $this->redirectList();
                }
                $errors['general'] = 'Unable to create class session.';
            }
        }

        require APP_ROOT . '/views/admin/sessions/create.php';
    }

    public function edit(): void
    {
        Middleware::requireAdmin();
        $id = (int)($_GET['id'] ?? 0);
        $row = $this->model->getSessionById($id);
        if (!$row) {
            $_SESSION['flash_error'] = 'Session not found.';
            $this->redirectList();
        }

        $mode = 'edit';
        $formAction = APP_URL . '/index.php?page=admin_session_edit&id=' . $id;
        $courses = $this->model->getCourseOptions();
        $teachers = $this->model->getTeacherOptions();
        $session = [
            'course_id' => (string)$row['course_id'],
            'teacher_id' => (string)$row['teacher_id'],
            'title' => (string)($row['title'] ?? ''),
            'session_date' => (string)$row['session_date'],
            'start_time' => substr((string)$row['start_time'], 0, 5),
            'end_time' => substr((string)$row['end_time'], 0, 5),
            'status' => (string)$row['status'],
            'notes' => (string)($row['notes'] ?? ''),
        ];
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $session = $this->collectPayload($_POST);
            $errors = $this->validate($session, $courses, $teachers);

            if (empty($errors)) {
                if ($this->model->updateSession($id, $session)) {
                    $_SESSION['flash_success'] = 'Class session updated successfully.';
                    $this->redirectList();
                }
                $errors['general'] = 'Unable to update class session.';
            }
        }

        require APP_ROOT . '/views/admin/sessions/edit.php';
    }

    public function delete(): void
    {
        Middleware::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['flash_error'] = 'Invalid request method.';
            $this->redirectList();
        }

        $id = (int)($_GET['id'] ?? 0);
        $ok = $id > 0 ? $this->model->deleteSession($id) : false;

        $_SESSION['flash_' . ($ok ? 'success' : 'error')] = $ok
            ? 'Class session deleted successfully.'
            : 'Unable to delete class session.';
        $this->redirectList();
    }

    private function collectPayload(array $source): array
    {
        return [
            'course_id' => (int)($source['course_id'] ?? 0),
            'teacher_id' => (int)($source['teacher_id'] ?? 0),
            'title' => trim((string)($source['title'] ?? '')),
            'session_date' => trim((string)($source['session_date'] ?? '')),
            'start_time' => trim((string)($source['start_time'] ?? '')),
            'end_time' => trim((string)($source['end_time'] ?? '')),
            'status' => trim((string)($source['status'] ?? 'upcoming')),
            'notes' => trim((string)($source['notes'] ?? '')),
        ];
    }

    private function validate(array $session, array $courses, array $teachers): array
    {
        $errors = [];

        $courseIds = array_map(static fn(array $row): int => (int)$row['id'], $courses);
        if (!in_array($session['course_id'], $courseIds, true)) {
            $errors['course_id'] = 'Please select a valid course.';
        }

        $teacherIds = array_map(static fn(array $row): int => (int)$row['id'], $teachers);
        if (!in_array($session['teacher_id'], $teacherIds, true)) {
            $errors['teacher_id'] = 'Please select a valid teacher.';
        }

        if ($session['session_date'] === '') {
            $errors['session_date'] = 'Date is required.';
        }
        if ($session['start_time'] === '') {
            $errors['start_time'] = 'Start time is required.';
        }
        if ($session['end_time'] === '') {
            $errors['end_time'] = 'End time is required.';
        }
        if ($session['start_time'] !== '' && $session['end_time'] !== '' && $session['start_time'] >= $session['end_time']) {
            $errors['end_time'] = 'End time must be greater than start time.';
        }

        if (!in_array($session['status'], ['upcoming', 'active', 'ended'], true)) {
            $errors['status'] = 'Invalid status.';
        }

        return $errors;
    }

    private function redirectList(): void
    {
        header('Location: ' . APP_URL . '/index.php?page=admin_sessions');
        exit;
    }
}
