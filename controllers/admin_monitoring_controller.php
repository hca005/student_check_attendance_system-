<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/middleware.php';

class AdminMonitoringController
{
    private PDO $db;
    private int $perPage = 10;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function engagementScores(): void
    {
        Middleware::requireAdmin();

        $filters = [
            'search' => trim($_GET['search'] ?? ''),
            'course_id' => (int)($_GET['course_id'] ?? 0),
            'score_range' => $_GET['score_range'] ?? '',
            'sort' => $_GET['sort'] ?? 'lowest',
            'page' => max(1, (int)($_GET['p'] ?? 1)),
        ];

        [$whereSql, $params] = $this->buildEngagementWhere($filters);
        $orderSql = $filters['sort'] === 'highest'
            ? 'ORDER BY es.engagement_index DESC'
            : 'ORDER BY es.engagement_index ASC';

        $offset = ($filters['page'] - 1) * $this->perPage;

        $sql = "
            SELECT
                es.*,
                u.full_name AS student_name,
                u.student_code,
                c.course_code,
                c.course_name,
                ROUND(
                    CASE
                        WHEN es.total_sessions > 0 THEN (es.attended_sessions / es.total_sessions) * 100
                        ELSE 0
                    END, 0
                ) AS attendance_rate
            FROM engagement_scores es
            JOIN users u ON u.id = es.student_id
            JOIN courses c ON c.id = es.course_id
            $whereSql
            $orderSql
            LIMIT {$this->perPage} OFFSET {$offset}
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $scores = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $countStmt = $this->db->prepare(
            "SELECT COUNT(*)
             FROM engagement_scores es
             JOIN users u ON u.id = es.student_id
             JOIN courses c ON c.id = es.course_id
             $whereSql"
        );
        $countStmt->execute($params);
        $totalCount = (int)$countStmt->fetchColumn();
        $totalPages = max(1, (int)ceil($totalCount / $this->perPage));
        $currentPageNum = max(1, min($filters['page'], $totalPages));

        $courseOptions = $this->db->query(
            "SELECT id, course_code, course_name FROM courses ORDER BY course_code ASC"
        )->fetchAll(PDO::FETCH_ASSOC);

        $stats = [
            'avg' => (float)($this->db->query("SELECT ROUND(AVG(engagement_index), 0) FROM engagement_scores")->fetchColumn() ?: 0),
            'high' => (int)$this->db->query("SELECT COUNT(*) FROM engagement_scores WHERE engagement_index > 75")->fetchColumn(),
            'risk' => (int)$this->db->query("SELECT COUNT(*) FROM engagement_scores WHERE engagement_index < 50")->fetchColumn(),
            'low_alerts' => (int)$this->db->query("SELECT COUNT(*) FROM alerts WHERE alert_type='low_engagement' AND status='pending'")->fetchColumn(),
        ];

        $distributionRaw = [
            'good' => (int)$this->db->query("SELECT COUNT(*) FROM engagement_scores WHERE engagement_index > 75")->fetchColumn(),
            'medium' => (int)$this->db->query("SELECT COUNT(*) FROM engagement_scores WHERE engagement_index >= 50 AND engagement_index <= 75")->fetchColumn(),
            'risk' => (int)$this->db->query("SELECT COUNT(*) FROM engagement_scores WHERE engagement_index < 50")->fetchColumn(),
        ];
        $distTotal = max(1, array_sum($distributionRaw));
        $distribution = [
            'good' => (int)round(($distributionRaw['good'] / $distTotal) * 100),
            'medium' => (int)round(($distributionRaw['medium'] / $distTotal) * 100),
            'risk' => (int)round(($distributionRaw['risk'] / $distTotal) * 100),
        ];

        $barData = $this->db->query(
            "SELECT c.course_code, ROUND(AVG(es.engagement_index), 0) AS avg_score
             FROM engagement_scores es
             JOIN courses c ON c.id = es.course_id
             GROUP BY c.id, c.course_code
             ORDER BY c.course_code ASC
             LIMIT 7"
        )->fetchAll(PDO::FETCH_ASSOC);
        if (!$barData) {
            $barData = [
                ['course_code' => 'W1', 'avg_score' => 0],
                ['course_code' => 'W2', 'avg_score' => 0],
                ['course_code' => 'W3', 'avg_score' => 0],
            ];
        }

        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        require APP_ROOT . '/views/admin/monitoring/engagement_scores.php';
    }

    public function alerts(): void
    {
        Middleware::requireAdmin();

        $filters = [
            'search' => trim($_GET['search'] ?? ''),
            'alert_type' => $_GET['alert_type'] ?? '',
            'severity' => $_GET['severity'] ?? '',
            'status' => $_GET['status'] ?? '',
            'page' => max(1, (int)($_GET['p'] ?? 1)),
        ];

        [$whereSql, $params] = $this->buildAlertWhere($filters);
        $offset = ($filters['page'] - 1) * $this->perPage;

        $sql = "
            SELECT
                al.alert_id AS id,
                al.student_id,
                al.course_id,
                al.alert_type,
                al.message AS alert_message,
                al.severity,
                al.status,
                al.created_at,
                al.reviewed_by,
                al.reviewed_at,
                u.full_name AS student_name,
                u.student_code,
                c.course_code,
                c.course_name
            FROM alerts al
            JOIN users u ON u.id = al.student_id
            JOIN courses c ON c.id = al.course_id
            $whereSql
            ORDER BY al.created_at DESC
            LIMIT {$this->perPage} OFFSET {$offset}
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $countStmt = $this->db->prepare(
            "SELECT COUNT(*)
             FROM alerts al
             JOIN users u ON u.id = al.student_id
             JOIN courses c ON c.id = al.course_id
             $whereSql"
        );
        $countStmt->execute($params);
        $totalCount = (int)$countStmt->fetchColumn();
        $totalPages = max(1, (int)ceil($totalCount / $this->perPage));
        $currentPageNum = max(1, min($filters['page'], $totalPages));

        $stats = [
            'total'    => (int)$this->db->query("SELECT COUNT(*) FROM alerts")->fetchColumn(),
            'open'     => (int)$this->db->query("SELECT COUNT(*) FROM alerts WHERE status='pending'")->fetchColumn(),
            'resolved' => (int)$this->db->query("SELECT COUNT(*) FROM alerts WHERE status='resolved'")->fetchColumn(),
            'critical' => (int)$this->db->query("SELECT COUNT(*) FROM alerts WHERE severity='high'")->fetchColumn(),
        ];

        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        require APP_ROOT . '/views/admin/monitoring/alerts.php';
    }

    public function alertDetail(): void
    {
        Middleware::requireAdmin();

        $id = (int)($_GET['id'] ?? 0);
        $stmt = $this->db->prepare(
            "SELECT
                al.alert_id AS id,
                al.student_id,
                al.course_id,
                al.alert_type,
                al.message AS alert_message,
                al.severity,
                al.status,
                al.created_at,
                al.reviewed_by,
                al.reviewed_at,
                u.full_name AS student_name,
                u.email AS student_email,
                u.student_code,
                c.course_code,
                c.course_name,
                es.total_sessions,
                es.attended_sessions,
                es.total_quiz_score,
                es.total_interaction_points,
                es.engagement_index,
                reviewer.full_name AS resolver_name
             FROM alerts al
             JOIN users u ON u.id = al.student_id
             JOIN courses c ON c.id = al.course_id
             LEFT JOIN engagement_scores es
                ON es.student_id = al.student_id AND es.course_id = al.course_id
             LEFT JOIN users reviewer ON reviewer.id = al.reviewed_by
             WHERE al.alert_id = ?"
        );
        $stmt->execute([$id]);
        $alert = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$alert) {
            $_SESSION['flash_error'] = 'Alert not found.';
            header('Location: ' . APP_URL . '/index.php?page=admin_alerts');
            exit;
        }

        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        require APP_ROOT . '/views/admin/monitoring/alert_detail.php';
    }

    public function resolveAlert(): void
    {
        Middleware::requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['flash_error'] = 'Invalid request method.';
            header('Location: ' . APP_URL . '/index.php?page=admin_alerts');
            exit;
        }

        $id = (int)($_GET['id'] ?? 0);
        $status = (string)($_POST['status'] ?? 'resolved');
        if (!in_array($status, ['pending', 'reviewed', 'resolved'], true)) {
            $status = 'resolved';
        }

        $userId = (int)(Middleware::user()['id'] ?? 0);
        $reviewedBy = in_array($status, ['reviewed', 'resolved']) ? $userId : null;
        $reviewedAt = in_array($status, ['reviewed', 'resolved']) ? date('Y-m-d H:i:s') : null;

        $stmt = $this->db->prepare(
            "UPDATE alerts
             SET status = ?, reviewed_by = ?, reviewed_at = ?
             WHERE alert_id = ?"
        );
        $ok = $stmt->execute([$status, $reviewedBy, $reviewedAt, $id]);

        $_SESSION['flash_' . ($ok ? 'success' : 'error')] = $ok
            ? 'Alert updated successfully.'
            : 'Unable to update alert.';

        header('Location: ' . APP_URL . '/index.php?page=admin_alert_detail&id=' . $id);
        exit;
    }

    public function generateAlerts(): void
    {
        Middleware::requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['flash_error'] = 'Invalid request method.';
            header('Location: ' . APP_URL . '/index.php?page=admin_alerts');
            exit;
        }

        $rows = $this->db->query(
            "SELECT
                es.student_id,
                es.course_id,
                es.total_sessions,
                es.attended_sessions,
                es.engagement_index,
                c.course_code,
                c.absence_threshold,
                c.low_engagement_threshold
             FROM engagement_scores es
             JOIN courses c ON c.id = es.course_id"
        )->fetchAll(PDO::FETCH_ASSOC);

        $insertStmt = $this->db->prepare(
            "INSERT INTO alerts (student_id, course_id, alert_type, message, severity, status)
             VALUES (?, ?, ?, ?, ?, 'pending')"
        );
        $existsStmt = $this->db->prepare(
            "SELECT COUNT(*) FROM alerts
             WHERE student_id = ? AND course_id = ? AND alert_type = ? AND status='pending'"
        );

        $created = 0;
        foreach ($rows as $row) {
            $absenceCount = max(0, (int)$row['total_sessions'] - (int)$row['attended_sessions']);
            $threshold = (int)$row['absence_threshold'];
            if ($threshold > 0 && $absenceCount >= $threshold) {
                $existsStmt->execute([$row['student_id'], $row['course_id'], 'low_attendance']);
                if ((int)$existsStmt->fetchColumn() === 0) {
                    $message = "Attendance risk detected in {$row['course_code']}: {$absenceCount} absences vượt ngưỡng {$threshold} buổi.";
                    $insertStmt->execute([$row['student_id'], $row['course_id'], 'low_attendance', $message, 'high']);
                    $created++;
                }
            }

            $lowThreshold = (float)$row['low_engagement_threshold'];
            if ((float)$row['engagement_index'] < $lowThreshold) {
                $existsStmt->execute([$row['student_id'], $row['course_id'], 'low_engagement']);
                if ((int)$existsStmt->fetchColumn() === 0) {
                    $message = "Low engagement detected in {$row['course_code']}: score {$row['engagement_index']} dưới ngưỡng {$lowThreshold}.";
                    $insertStmt->execute([$row['student_id'], $row['course_id'], 'low_engagement', $message, 'medium']);
                    $created++;
                }
            }
        }

        $_SESSION['flash_success'] = $created > 0
            ? "Generated {$created} new alert(s)."
            : 'No new alerts were generated.';
        header('Location: ' . APP_URL . '/index.php?page=admin_alerts');
        exit;
    }

    private function buildEngagementWhere(array $filters): array
    {
        $conditions = ['1=1'];
        $params = [];

        if (!empty($filters['search'])) {
            $conditions[] = "(u.full_name LIKE ? OR u.student_code LIKE ? OR c.course_code LIKE ? OR c.course_name LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        if (!empty($filters['course_id'])) {
            $conditions[] = "es.course_id = ?";
            $params[] = (int)$filters['course_id'];
        }

        if (!empty($filters['score_range'])) {
            switch ($filters['score_range']) {
                case 'high':
                    $conditions[] = "es.engagement_index > 75";
                    break;
                case 'medium':
                    $conditions[] = "es.engagement_index >= 50 AND es.engagement_index <= 75";
                    break;
                case 'low':
                    $conditions[] = "es.engagement_index < 50";
                    break;
            }
        }

        return ['WHERE ' . implode(' AND ', $conditions), $params];
    }

    private function buildAlertWhere(array $filters): array
    {
        $conditions = ['1=1'];
        $params = [];

        if (!empty($filters['search'])) {
            $conditions[] = "(u.full_name LIKE ? OR c.course_code LIKE ? OR c.course_name LIKE ? OR al.message LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        if (!empty($filters['alert_type']) && in_array($filters['alert_type'], ['low_attendance', 'low_engagement'], true)) {
            $conditions[] = "al.alert_type = ?";
            $params[] = $filters['alert_type'];
        }

        if (!empty($filters['status']) && in_array($filters['status'], ['pending', 'reviewed', 'resolved'], true)) {
            $conditions[] = "al.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['severity']) && in_array($filters['severity'], ['low', 'medium', 'high'], true)) {
            $conditions[] = "al.severity = ?";
            $params[] = $filters['severity'];
        }

        return ['WHERE ' . implode(' AND ', $conditions), $params];
    }
}
