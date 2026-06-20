<?php
/**
 * public/student/alerts.php
 * Route: Trang My Alerts – xem + đánh dấu đã xem cảnh báo
 */
require_once dirname(dirname(__DIR__)) . '/config/config.php';
require_once dirname(dirname(__DIR__)) . '/config/Database.php';
require_once dirname(dirname(__DIR__)) . '/helpers/middleware.php';

if (session_status() === PHP_SESSION_NONE) session_start();

Middleware::requireStudent();

require_once APP_ROOT . '/models/AlertLogModel.php';

$db         = Database::getInstance();
$studentId  = Middleware::user()['id'];
$alertModel = new AlertLogModel();
$action     = $_GET['action'] ?? '';

// ── AJAX: đánh dấu đã xem (status pending → reviewed) ──────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'dismiss') {
    header('Content-Type: application/json; charset=utf-8');

    $id    = (int)($_POST['id'] ?? 0);
    $alert = $alertModel->getById($id);

    if (!$alert || (int)$alert['student_id'] !== $studentId) {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy cảnh báo.']);
        exit;
    }

    if ($alert['status'] !== AlertLogModel::STATUS_PENDING) {
        echo json_encode(['success' => false, 'message' => 'Cảnh báo này đã được xử lý rồi.']);
        exit;
    }

    $ok = $alertModel->update($id, [
        'status'      => AlertLogModel::STATUS_REVIEWED,
        'reviewed_by' => $studentId,
    ]);
    echo json_encode([
        'success' => $ok,
        'message' => $ok ? 'Đã đánh dấu đã xem.' : 'Cập nhật thất bại.',
    ]);
    exit;
}

// ── GET: Hiển thị trang ─────────────────────────────────────
$alerts = $alertModel->getByStudentId($studentId);

$pageTitle   = 'My Alerts';
$currentPage = 'student.alerts';
require_once APP_ROOT . '/views/student/alerts.php';
