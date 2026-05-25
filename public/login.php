<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/helpers/middleware.php';
require_once dirname(__DIR__) . '/controllers/auth_controller.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$controller = new AuthController();
$controller->login();
