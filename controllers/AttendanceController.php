<?php
// ============================================================
// controllers/AttendanceController.php
// Xử lý logic điểm danh (methods + records)
// ============================================================

require_once APP_ROOT . '/config/Database.php';
require_once APP_ROOT . '/models/AttendanceMethodModel.php';
require_once APP_ROOT . '/models/AttendanceRecordModel.php';
require_once APP_ROOT . '/helpers/Middleware.php';

class AttendanceController
{
    private PDO $db;
    private AttendanceMethodModel $methodModel;
    private AttendanceRecordModel $recordModel;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->methodModel = new AttendanceMethodModel();
        $this->recordModel = new AttendanceRecordModel();
    }

    // ══════════════════════════════════════════════════════════════
    // ATTENDANCE METHODS – Quản lý phương thức điểm danh
    // ══════════════════════════════════════════════════════════════

    // ──────────────────────────────────────────────────────
    // GET /teacher/attendance/methods?session_id=X
    // Hiển thị danh sách phương thức điểm danh của 1 buổi
    // ──────────────────────────────────────────────────────
    public function listMethods(): void
    {
        Middleware::teacher();
        
        $sessionId = (int) ($_GET['session_id'] ?? 0);
        if (!$sessionId) {
            $_SESSION['error'] = 'Session không hợp lệ';
            header('Location: ' . APP_URL . '/teacher/dashboard.php');
            exit;
        }

        // Verify teacher owns this session
        $this->verifyTeacherOwnsSession($sessionId);

        $methods = $this->methodModel->getBySessionId($sessionId);
        $session = $this->getSessionInfo($sessionId);

        require_once APP_ROOT . '/views/teacher/attendance/methods_list.php';
    }

    // ──────────────────────────────────────────────────────
    // GET & POST /teacher/attendance/methods_form.php
    // Form tạo hoặc sửa phương thức
    // ──────────────────────────────────────────────────────
    public function methodsForm(): void
    {
        Middleware::teacher();

        $error = null;
        $success = null;
        $method = null;
        $sessionId = (int) ($_GET['session_id'] ?? $_POST['session_id'] ?? 0);
        $methodId = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

        if (!$sessionId) {
            $_SESSION['error'] = 'Session không hợp lệ';
            header('Location: ' . APP_URL . '/teacher/dashboard.php');
            exit;
        }

        $this->verifyTeacherOwnsSession($sessionId);
        $session = $this->getSessionInfo($sessionId);

        // Edit mode
        if ($methodId) {
            $method = $this->methodModel->getById($methodId);
            if (!$method || $method['session_id'] != $sessionId) {
                $_SESSION['error'] = 'Phương thức không tồn tại';
                header("Location: " . APP_URL . "/teacher/attendance/methods_list.php?session_id=$sessionId");
                exit;
            }
        }

        // Handle POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $methodType = trim($_POST['method_type'] ?? '');
            $timeLimitMinutes = (int) ($_POST['time_limit_minutes'] ?? 0);
            $expiresAtStr = trim($_POST['expires_at'] ?? '');

            // Validate
            if (!in_array($methodType, ['qr', 'otp', 'manual'])) {
                $error = 'Loại phương thức không hợp lệ';
            } elseif ($methodType !== 'manual' && empty($expiresAtStr)) {
                $error = 'Thời gian hết hạn bắt buộc với QR/OTP';
            } else {
                try {
                    $expiresAt = $expiresAtStr ? new DateTime($expiresAtStr) : null;

                    // Generate token/OTP
                    $token = null;
                    if ($methodType === 'qr') {
                        $token = AttendanceMethodModel::generateQrToken();
                    } elseif ($methodType === 'otp') {
                        $token = AttendanceMethodModel::generateOtp();
                    }

                    if ($methodId) {
                        // Update
                        $this->methodModel->update($methodId, [
                            'method_type' => $methodType,
                            'token' => $token,
                            'expires_at' => $expiresAt
                        ]);
                        $success = 'Cập nhật phương thức thành công!';
                    } else {
                        // Create
                        $this->methodModel->create($sessionId, $methodType, $token, $expiresAt);
                        $success = 'Tạo phương thức thành công!';
                    }

                    // Redirect về danh sách
                    $_SESSION['success'] = $success;
                    header("Location: " . APP_URL . "/teacher/attendance/methods_list.php?session_id=$sessionId");
                    exit;
                } catch (Exception $e) {
                    $error = 'Lỗi: ' . $e->getMessage();
                }
            }
        }

        require_once APP_ROOT . '/views/teacher/attendance/methods_form.php';
    }

    public function createMethod(): void
    {
        // Compatibility route for legacy POST /teacher/attendance/create.php
        $this->methodsForm();
    }

    // ──────────────────────────────────────────────────────
    // POST /teacher/attendance/methods_delete.php
    // Xóa phương thức điểm danh
    // ──────────────────────────────────────────────────────
    public function deleteMethod(): void
    {
        Middleware::teacher();

        $methodId = (int) ($_POST['id'] ?? 0);
        $sessionId = (int) ($_POST['session_id'] ?? 0);

        if (!$methodId || !$sessionId) {
            $_SESSION['error'] = 'Dữ liệu không hợp lệ';
            header('Location: ' . APP_URL . '/teacher/dashboard.php');
            exit;
        }

        $method = $this->methodModel->getById($methodId);
        if (!$method || $method['session_id'] != $sessionId) {
            $_SESSION['error'] = 'Phương thức không tồn tại';
            header("Location: " . APP_URL . "/teacher/attendance/methods_list.php?session_id=$sessionId");
            exit;
        }

        $this->verifyTeacherOwnsSession($sessionId);

        if ($this->methodModel->delete($methodId)) {
            $_SESSION['success'] = 'Xóa phương thức thành công!';
        } else {
            $_SESSION['error'] = 'Xóa phương thức thất bại';
        }

        header("Location: " . APP_URL . "/teacher/attendance/methods_list.php?session_id=$sessionId");
        exit;
    }

    // ══════════════════════════════════════════════════════════════
    // ATTENDANCE RECORDS – Quản lý bản ghi điểm danh
    // ══════════════════════════════════════════════════════════════

    // ──────────────────────────────────────────────────────
    // GET /teacher/attendance/records?session_id=X
    // Hiển thị danh sách điểm danh của 1 buổi
    // ──────────────────────────────────────────────────────
    public function listRecords(): void
    {
        Middleware::teacher();

        $sessionId = (int) ($_GET['session_id'] ?? 0);
        if (!$sessionId) {
            $_SESSION['error'] = 'Session không hợp lệ';
            header('Location: ' . APP_URL . '/teacher/dashboard.php');
            exit;
        }

        $this->verifyTeacherOwnsSession($sessionId);

        $records = $this->recordModel->getBySessionId($sessionId);
        $session = $this->getSessionInfo($sessionId);

        require_once APP_ROOT . '/views/teacher/attendance/records_list.php';
    }

    // ──────────────────────────────────────────────────────
    // POST /teacher/attendance/update_record.php
    // Cập nhật trạng thái điểm danh (Teacher update thủ công)
    // ──────────────────────────────────────────────────────
    public function updateRecord(): void
    {
        Middleware::teacher();

        $recordId = (int) ($_POST['id'] ?? 0);
        $sessionId = (int) ($_POST['session_id'] ?? 0);
        $status = trim($_POST['status'] ?? '');

        if (!$recordId || !$sessionId || !in_array($status, ['present', 'absent', 'late', 'excused'])) {
            $_SESSION['error'] = 'Dữ liệu không hợp lệ';
            header('Location: ' . APP_URL . '/teacher/dashboard.php');
            exit;
        }

        $record = $this->recordModel->getById($recordId);
        if (!$record || $record['session_id'] != $sessionId) {
            $_SESSION['error'] = 'Bản ghi điểm danh không tồn tại';
            header('Location: ' . APP_URL . '/teacher/dashboard.php');
            exit;
        }

        $this->verifyTeacherOwnsSession($sessionId);

        $note = trim($_POST['note'] ?? '');
        $updates = ['status' => $status, 'note' => $note, 'verified_by' => $_SESSION['user_id']];

        if (in_array($status, ['present', 'late'], true)) {
            $updates['checked_in_at'] = new DateTime();
        } else {
            $updates['checked_in_at'] = null;
        }

        if ($this->recordModel->update($recordId, $updates)) {
            $_SESSION['success'] = 'Cập nhật trạng thái thành công!';
        } else {
            $_SESSION['error'] = 'Cập nhật thất bại';
        }

        header("Location: " . APP_URL . "/teacher/attendance/records_list.php?session_id=$sessionId");
        exit;
    }

    // ──────────────────────────────────────────────────────
    // HELPER – Verify teacher owns session
    // ──────────────────────────────────────────────────────
    private function verifyTeacherOwnsSession(int $sessionId): void
    {
        $stmt = $this->db->prepare(
            'SELECT teacher_id FROM class_sessions WHERE id = ?'
        );
        $stmt->execute([$sessionId]);
        $session = $stmt->fetch();

        if (!$session || $session['teacher_id'] != $_SESSION['user_id']) {
            $_SESSION['error'] = 'Bạn không có quyền truy cập';
            header('Location: ' . APP_URL . '/teacher/dashboard.php');
            exit;
        }
    }

    // ──────────────────────────────────────────────────────
    // HELPER – Lấy thông tin session
    // ──────────────────────────────────────────────────────
    private function getSessionInfo(int $sessionId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT cs.id, cs.session_date, cs.start_time, cs.end_time, cs.title, cs.status,
                    c.course_code, c.course_name
             FROM class_sessions cs
             JOIN courses c ON cs.course_id = c.id
             WHERE cs.id = ?'
        );
        $stmt->execute([$sessionId]);
        return $stmt->fetch() ?: null;
    }
}
