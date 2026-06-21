<?php

require_once __DIR__ . '/../models/enrollment_model.php';
require_once __DIR__ . '/../helpers/middleware.php';

class EnrollmentController
{
    private EnrollmentModel $model;

    public function __construct()
    {
        $this->model = new EnrollmentModel();
    }

    public function index(): void
    {
        Middleware::requireAdmin();

        $filters = [
            'search' => trim($_GET['search'] ?? ''),
            'course_id' => (int)($_GET['course_id'] ?? 0),
            'role' => $_GET['role'] ?? '',
            'status' => $_GET['status'] ?? '',
            'page' => max(1, (int)($_GET['p'] ?? 1)),
        ];

        $enrollments = $this->model->getEnrollments($filters);
        $totalCount = $this->model->countEnrollments($filters);
        $stats = $this->model->getStats();
        $courses = $this->model->getCourseOptions();
        $perPage = $this->model->getPerPage();
        $totalPages = max(1, (int)ceil($totalCount / $perPage));
        $currentPageNum = max(1, min($filters['page'], $totalPages));

        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        require APP_ROOT . '/views/admin/enrollments/index.php';
    }

    public function create(): void
    {
        Middleware::requireAdmin();

        $mode = 'create';
        $formAction = APP_URL . '/index.php?page=admin_enrollment_create';
        $courses = $this->model->getCourseOptions();
        $record = [
            'course_id' => '',
            'role' => 'student',
            'user_ids' => [],
        ];
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $record['course_id'] = (int)($_POST['course_id'] ?? 0);
            $record['role'] = (string)($_POST['role'] ?? 'student');
            $user_ids_raw = $_POST['user_ids'] ?? [];
            if (!is_array($user_ids_raw) && !empty($_POST['user_id'])) {
                $user_ids_raw = [$_POST['user_id']];
            }
            $record['user_ids'] = array_map('intval', (array)$user_ids_raw);

            if (!in_array($record['role'], ['teacher', 'student'], true)) {
                $errors['role'] = 'Role must be teacher or student.';
            }

            $courseIds = array_map(
                static fn(array $item): int => (int)$item['id'],
                $courses
            );
            if (!in_array($record['course_id'], $courseIds, true)) {
                $errors['course_id'] = 'Please select a valid course.';
            }

            if (empty($record['user_ids'])) {
                $errors['user_ids'] = 'Please select at least one user.';
            } else {
                foreach ($record['user_ids'] as $uid) {
                    $expectedRole = $this->model->userRoleById($uid);
                    if (!$expectedRole || $expectedRole !== $record['role']) {
                        $errors['user_ids'] = 'One or more selected users are invalid for this role.';
                        break;
                    }
                }
            }

            if (empty($errors)) {
                $successCount = 0;
                foreach ($record['user_ids'] as $uid) {
                    if (!$this->model->enrollmentExists($record['course_id'], $uid)) {
                        $enrollData = [
                            'course_id' => $record['course_id'],
                            'role' => $record['role'],
                            'user_id' => $uid
                        ];
                        if ($this->model->createEnrollment($enrollData)) {
                            $successCount++;
                        }
                    }
                }
                
                if ($successCount > 0) {
                    $_SESSION['flash_success'] = "$successCount enrollment(s) created successfully.";
                    $this->redirectList();
                } else {
                    $errors['general'] = 'No new enrollments created. Selected users might already be enrolled.';
                }
            }
        }

        $teacherUsers = $this->model->getUserOptions('teacher');
        $studentUsers = $this->model->getUserOptions('student');
        require APP_ROOT . '/views/admin/enrollments/create.php';
    }

    public function edit(): void
    {
        Middleware::requireAdmin();

        $id = (int)($_GET['id'] ?? 0);
        $row = $this->model->getEnrollmentById($id);
        if (!$row) {
            $_SESSION['flash_error'] = 'Enrollment not found.';
            $this->redirectList();
        }

        $mode = 'edit';
        $formAction = APP_URL . '/index.php?page=admin_enrollment_edit&id=' . $id;
        $courses = $this->model->getCourseOptions();
        $record = [
            'course_id' => (string)$row['course_id'],
            'role' => $row['role'],
            'user_id' => (string)$row['user_id'],
        ];
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $record = $this->collectPayload($_POST);
            $errors = $this->validate($record, $id);

            if (empty($errors)) {
                if ($this->model->updateEnrollment($id, $record)) {
                    $_SESSION['flash_success'] = 'Enrollment updated successfully.';
                    $this->redirectList();
                }
                $errors['general'] = 'Unable to update enrollment.';
            }
        }

        $teacherUsers = $this->model->getUserOptions('teacher');
        $studentUsers = $this->model->getUserOptions('student');
        require APP_ROOT . '/views/admin/enrollments/edit.php';
    }

    public function delete(): void
    {
        Middleware::requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['flash_error'] = 'Invalid request method.';
            $this->redirectList();
        }

        $id = (int)($_GET['id'] ?? 0);
        $ok = $id > 0 ? $this->model->deleteEnrollment($id) : false;
        $_SESSION['flash_' . ($ok ? 'success' : 'error')] = $ok
            ? 'Enrollment removed successfully.'
            : 'Unable to remove enrollment.';
        $this->redirectList();
    }

    private function collectPayload(array $source): array
    {
        return [
            'course_id' => (int)($source['course_id'] ?? 0),
            'role' => (string)($source['role'] ?? 'student'),
            'user_id' => (int)($source['user_id'] ?? 0),
        ];
    }

    private function validate(array $record, ?int $excludeId = null): array
    {
        $errors = [];

        if (!in_array($record['role'], ['teacher', 'student'], true)) {
            $errors['role'] = 'Role must be teacher or student.';
        }

        $courseIds = array_map(
            static fn(array $item): int => (int)$item['id'],
            $this->model->getCourseOptions()
        );
        if (!in_array($record['course_id'], $courseIds, true)) {
            $errors['course_id'] = 'Please select a valid course.';
        }

        $expectedRole = $this->model->userRoleById($record['user_id']);
        if (!$expectedRole) {
            $errors['user_id'] = 'Selected user does not exist.';
        } elseif ($expectedRole !== $record['role']) {
            $errors['user_id'] = 'User role does not match selected role.';
        }

        if (!$errors && $this->model->enrollmentExists($record['course_id'], $record['user_id'], $excludeId)) {
            $errors['general'] = 'This user is already enrolled in the selected course.';
        }

        return $errors;
    }

    private function redirectList(): void
    {
        header('Location: ' . APP_URL . '/index.php?page=admin_enrollments');
        exit;
    }
}
