<?php
// public/teacher/quiz/questions_form.php
define('APP_ROOT', dirname(dirname(dirname(__DIR__))));
define('APP_URL', 'http://localhost/student_check_attendance_system');
define('APP_ENV', 'development');

require_once APP_ROOT . '/config/config.php';
require_once APP_ROOT . '/controllers/QuizController.php';

session_start();

$controller = new QuizController();
$controller->questionsForm();
