<?php
require_once dirname(dirname(__DIR__)) . '/config/config.php';
require_once dirname(dirname(__DIR__)) . '/config/Database.php';
require_once dirname(dirname(__DIR__)) . '/helpers/middleware.php';

if (session_status() === PHP_SESSION_NONE) session_start();

Middleware::requireAdmin();

require_once APP_ROOT . '/views/admin/dashboard.php';