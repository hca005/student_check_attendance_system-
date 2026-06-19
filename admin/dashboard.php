<?php
// ── Bảo vệ trang ────────────────────────────────────────
Middleware::requireAdmin();

$pageTitle   = 'Dashboard – Quản trị';
$currentPage = 'admin.dashboard';

// ── Truy vấn thống kê ────────────────────────────────────
$db = Database::getInstance();

$totalUsers    = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalTeachers = $db->query("SELECT COUNT(*) FROM users WHERE role = 'teacher'")->fetchColumn();
$totalStudents = $db->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
$totalCourses  = $db->query("SELECT COUNT(*) FROM courses WHERE is_active = 1")->fetchColumn();
$totalSessions = $db->query("SELECT COUNT(*) FROM class_sessions")->fetchColumn();
$openAlerts    = $db->query("SELECT COUNT(*) FROM alert_logs WHERE status = 'open'")->fetchColumn();
$activeSessions= $db->query("SELECT COUNT(*) FROM class_sessions WHERE status = 'active'")->fetchColumn();

// Recent alerts
$recentAlerts  = $db->query(
    "SELECT al.*, u.full_name AS student_name, c.course_name
     FROM alert_logs al
     JOIN users u ON al.student_id = u.id
     JOIN courses c ON al.course_id = c.id
     WHERE al.status = 'open'
     ORDER BY al.created_at DESC LIMIT 5"
)->fetchAll();

require_once APP_ROOT . '/views/layouts/header.php';
?>

<!-- ── Stats cards ── -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card stat-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#e8edff">
                    <i class="bi bi-people-fill" style="color:#4361ee"></i>
                </div>
                <div>
                    <div class="fw-bold fs-4"><?= $totalUsers ?></div>
                    <div class="text-muted small">Tổng người dùng</div>
                </div>
            </div>
            <div class="mt-2 small text-muted">
                <span class="text-primary fw-semibold"><?= $totalTeachers ?></span> GV &nbsp;|&nbsp;
                <span class="text-success fw-semibold"><?= $totalStudents ?></span> SV
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#fff3cd">
                    <i class="bi bi-journal-bookmark-fill" style="color:#f59e0b"></i>
                </div>
                <div>
                    <div class="fw-bold fs-4"><?= $totalCourses ?></div>
                    <div class="text-muted small">Học phần</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#d1fae5">
                    <i class="bi bi-calendar3" style="color:#10b981"></i>
                </div>
                <div>
                    <div class="fw-bold fs-4"><?= $totalSessions ?></div>
                    <div class="text-muted small">Buổi học</div>
                </div>
            </div>
            <?php if ($activeSessions > 0): ?>
            <div class="mt-2">
                <span class="badge bg-success"><?= $activeSessions ?> đang hoạt động</span>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#fee2e2">
                    <i class="bi bi-bell-fill" style="color:#ef4444"></i>
                </div>
                <div>
                    <div class="fw-bold fs-4"><?= $openAlerts ?></div>
                    <div class="text-muted small">Cảnh báo mở</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ── Quick actions ── -->
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card p-3">
            <h6 class="fw-bold mb-3"><i class="bi bi-lightning-fill me-2 text-warning"></i>Thao tác nhanh</h6>
            <div class="d-flex flex-wrap gap-2">
                <a href="<?= APP_URL ?>/index.php?page=admin_users_create" class="btn btn-primary btn-sm">
                    <i class="bi bi-person-plus me-1"></i>Tạo tài khoản
                </a>
                <a href="<?= APP_URL ?>/index.php?page=admin_course_create" class="btn btn-warning btn-sm text-dark">
                    <i class="bi bi-plus-circle me-1"></i>Tạo học phần
                </a>
                <a href="<?= APP_URL ?>/index.php?page=admin_enrollments" class="btn btn-info btn-sm text-dark">
                    <i class="bi bi-person-fill-add me-1"></i>Gán người vào lớp
                </a>
                <a href="<?= APP_URL ?>/index.php?page=admin_alerts" class="btn btn-danger btn-sm">
                    <i class="bi bi-bell me-1"></i>Xem cảnh báo (<?= $openAlerts ?>)
                </a>
            </div>
        </div>
    </div>
</div>

<!-- ── Recent alerts ── -->
<?php if (!empty($recentAlerts)): ?>
<div class="card">
    <div class="card-header bg-white fw-bold border-0 pt-3">
        <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>Cảnh báo gần đây
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Sinh viên</th>
                    <th>Học phần</th>
                    <th>Loại cảnh báo</th>
                    <th>Thời gian</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($recentAlerts as $alert): ?>
                <tr>
                    <td class="fw-semibold"><?= htmlspecialchars($alert['student_name']) ?></td>
                    <td class="text-muted small"><?= htmlspecialchars($alert['course_name']) ?></td>
                    <td>
                        <?php
                        $typeMap = [
                            'high_absence'   => ['bg-danger',  'Vắng nhiều'],
                            'low_engagement' => ['bg-warning text-dark', 'Tương tác thấp'],
                            'missed_quiz'    => ['bg-info text-dark',    'Bỏ lỡ quiz'],
                        ];
                        [$cls, $label] = $typeMap[$alert['alert_type']] ?? ['bg-secondary', 'Khác'];
                        ?>
                        <span class="badge <?= $cls ?>"><?= $label ?></span>
                    </td>
                    <td class="text-muted small"><?= date('d/m/Y H:i', strtotime($alert['created_at'])) ?></td>
                    <td>
                        <a href="<?= APP_URL ?>/index.php?page=admin_alerts" class="btn btn-sm btn-outline-secondary">
                            Xem
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php else: ?>
<div class="alert alert-success d-flex align-items-center gap-2">
    <i class="bi bi-check-circle-fill"></i>
    Không có cảnh báo nào đang mở. Hệ thống hoạt động bình thường.
</div>
<?php endif; ?>

<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>
