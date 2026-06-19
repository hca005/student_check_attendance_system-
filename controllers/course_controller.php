<?php

require_once __DIR__ . '/../models/course_model.php';
require_once __DIR__ . '/../helpers/middleware.php';

class CourseController
{
    private CourseModel $model;

    public function __construct()
    {
        $this->model = new CourseModel();
    }

    public function index(): void
    {
        Middleware::requireAdmin();

        $filters = [
            'search' => trim($_GET['search'] ?? ''),
            'status' => $_GET['status'] ?? '',
            'sort' => $_GET['sort'] ?? 'newest',
            'page' => max(1, (int)($_GET['p'] ?? 1)),
        ];

        $courses = $this->model->getCourses($filters);
        $totalCount = $this->model->countCourses($filters);
        $stats = $this->model->getStats();
        $teachers = $this->model->getTeacherOptions();
        $perPage = $this->model->getPerPage();
        $totalPages = max(1, (int)ceil($totalCount / $perPage));
        $currentPageNum = max(1, min($filters['page'], $totalPages));

        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        require APP_ROOT . '/views/admin/courses/index.php';
    }

    public function create(): void
    {
        Middleware::requireAdmin();
        $formAction = APP_URL . '/index.php?page=admin_course_create';
        $mode = 'create';
        $teachers = $this->model->getTeacherOptions();
        $course = [
            'course_code' => '',
            'course_name' => '',
            'semester' => '',
            'absence_threshold' => 3,
            'low_engagement_threshold' => 40,
            'attend_score' => 2,
            'quiz_correct_score' => 2,
            'discussion_score' => 1,
            'teacher_id' => '',
            'is_active' => 1,
        ];
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $course = $this->collectCoursePayload($_POST);
            $errors = $this->validate($course);

            if (empty($errors)) {
                try {
                    $this->model->createCourse($course);
                    $_SESSION['flash_success'] = 'Course created successfully.';
                    $this->redirectList();
                } catch (Throwable $e) {
                    $errors['general'] = 'Unable to create course.';
                }
            }
        }

        require APP_ROOT . '/views/admin/courses/create.php';
    }

    public function edit(): void
    {
        Middleware::requireAdmin();
        $id = (int)($_GET['id'] ?? 0);
        $record = $this->model->getCourseById($id);

        if (!$record) {
            $_SESSION['flash_error'] = 'Course not found.';
            $this->redirectList();
        }

        $formAction = APP_URL . '/index.php?page=admin_course_edit&id=' . $id;
        $mode = 'edit';
        $teachers = $this->model->getTeacherOptions();
        $course = [
            'course_code' => $record['course_code'],
            'course_name' => $record['course_name'],
            'semester' => $record['semester'],
            'absence_threshold' => $record['absence_threshold'],
            'low_engagement_threshold' => $record['low_engagement_threshold'],
            'attend_score' => $record['attend_score'],
            'quiz_correct_score' => $record['quiz_correct_score'],
            'discussion_score' => $record['discussion_score'],
            'teacher_id' => (string)($record['teacher_id'] ?? ''),
            'is_active' => (int)$record['is_active'],
        ];
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $course = $this->collectCoursePayload($_POST);
            $errors = $this->validate($course, $id);

            if (empty($errors)) {
                try {
                    $this->model->updateCourse($id, $course);
                    $_SESSION['flash_success'] = 'Course updated successfully.';
                    $this->redirectList();
                } catch (Throwable $e) {
                    $errors['general'] = 'Unable to update course.';
                }
            }
        }

        require APP_ROOT . '/views/admin/courses/edit.php';
    }

    public function delete(): void
    {
        Middleware::requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['flash_error'] = 'Invalid request method.';
            $this->redirectList();
        }

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['flash_error'] = 'Course not found.';
            $this->redirectList();
        }

        $ok = $this->model->archiveCourse($id);
        $_SESSION['flash_' . ($ok ? 'success' : 'error')] = $ok
            ? 'Course archived successfully.'
            : 'Unable to archive course.';
        $this->redirectList();
    }

    private function collectCoursePayload(array $source): array
    {
        return [
            'course_code' => strtoupper(trim($source['course_code'] ?? '')),
            'course_name' => trim($source['course_name'] ?? ''),
            'semester' => trim($source['semester'] ?? ''),
            'absence_threshold' => trim((string)($source['absence_threshold'] ?? '3')),
            'low_engagement_threshold' => trim((string)($source['low_engagement_threshold'] ?? '40')),
            'attend_score' => trim((string)($source['attend_score'] ?? '2')),
            'quiz_correct_score' => trim((string)($source['quiz_correct_score'] ?? '2')),
            'discussion_score' => trim((string)($source['discussion_score'] ?? '1')),
            'teacher_id' => trim((string)($source['teacher_id'] ?? '')),
            'is_active' => (int)($source['is_active'] ?? 1),
        ];
    }

    private function validate(array &$course, ?int $courseId = null): array
    {
        $errors = [];

        if ($course['course_code'] === '') {
            $errors['course_code'] = 'Course code is required.';
        }
        if ($course['course_name'] === '') {
            $errors['course_name'] = 'Course name is required.';
        }
        if ($course['semester'] === '') {
            $errors['semester'] = 'Semester is required.';
        }

        if ($course['course_code'] !== '' && $course['semester'] !== ''
            && $this->model->codeExists($course['course_code'], $course['semester'], $courseId)) {
            $errors['course_code'] = 'Course code already exists in this semester.';
        }

        $numericFields = [
            'absence_threshold' => 'Absence threshold',
            'low_engagement_threshold' => 'Low engagement threshold',
            'attend_score' => 'Attendance score',
            'quiz_correct_score' => 'Quiz score',
            'discussion_score' => 'Discussion score',
        ];

        foreach ($numericFields as $key => $label) {
            if (!is_numeric($course[$key])) {
                $errors[$key] = $label . ' must be numeric.';
            }
        }

        $course['absence_threshold'] = (int)$course['absence_threshold'];
        $course['low_engagement_threshold'] = (float)$course['low_engagement_threshold'];
        $course['attend_score'] = (float)$course['attend_score'];
        $course['quiz_correct_score'] = (float)$course['quiz_correct_score'];
        $course['discussion_score'] = (float)$course['discussion_score'];

        if ($course['teacher_id'] !== '') {
            $teacherOptions = array_column($this->model->getTeacherOptions(), 'id');
            if (!in_array((int)$course['teacher_id'], array_map('intval', $teacherOptions), true)) {
                $errors['teacher_id'] = 'Selected teacher is invalid.';
            } else {
                $course['teacher_id'] = (int)$course['teacher_id'];
            }
        } else {
            $course['teacher_id'] = null;
        }

        $course['is_active'] = $course['is_active'] === 1 ? 1 : 0;

        return $errors;
    }

    private function redirectList(): void
    {
        header('Location: ' . APP_URL . '/index.php?page=admin_courses');
        exit;
    }
}
