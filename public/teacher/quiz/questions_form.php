<?php
// public/teacher/quiz/questions_form.php
require_once dirname(__DIR__, 3) . '/config/config.php';
require_once APP_ROOT . '/controllers/QuizController.php';

session_start();

$controller = new QuizController();
$controller->questionsForm();
