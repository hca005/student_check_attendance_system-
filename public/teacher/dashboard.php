<?php
require_once dirname(dirname(__DIR__)) . '/config/config.php';
require_once dirname(dirname(__DIR__)) . '/config/database.php';
require_once dirname(dirname(__DIR__)) . '/helpers/middleware.php';

if (session_status() === PHP_SESSION_NONE) session_start();

Middleware::requireTeacher();

require_once APP_ROOT . '/views/teacher/dashboard.php';
