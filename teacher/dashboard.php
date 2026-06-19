<?php
Middleware::requireTeacher();

$pageTitle   = 'Dashboard – Giảng viên';
$currentPage = 'teacher.dashboard';

$db      = Database::getInstance();
$userId  = Middleware::user()['id'];

// Courses I teach
$myCourses = $db->query(
    "SELECT c.* FROM courses c
     JOIN enrollments ce ON ce.course_id = c.id
     WHERE ce.user_id = ? AND ce.role = 'teacher' AND c.is_active = 1",
    [$userId]
)->fetchAll();

$courseIds = array_column($myCourses, 'id');

// Sessions count
$totalSessions = 0;
$activeSessions = [];
if ($courseIds) {
    $in = implode(',', array_fill(0, count($courseIds), '?'));
    $totalSessions = $db->query(
        "SELECT COUNT(*) FROM class_sessions WHERE course_id IN ($in)", $courseIds
    )->fetchColumn();
    $activeSessions = $db->query(
        "SELECT cs.*, c.course_name FROM class_sessions cs
         JOIN courses c ON cs.course_id = c.id
         WHERE cs.course_id IN ($in) AND cs.status = 'active'
         ORDER BY cs.session_date DESC", $courseIds
    )->fetchAll();
}

// Open alerts for my courses
$openAlerts = 0;
if ($courseIds) {
    $in = implode(',', array_fill(0, count($courseIds), '?'));
    $openAlerts = $db->query(
        "SELECT COUNT(*) FROM alert_logs WHERE course_id IN ($in) AND status = 'open'", $courseIds
    )->fetchColumn();
}

// Upcoming sessions (next 7 days)
$upcomingSessions = [];
if ($courseIds) {
    $in = implode(',', array_fill(0, count($courseIds), '?'));
    $params = array_merge($courseIds, [$userId]);
    $upcomingSessions = $db->query(
        "SELECT cs.*, c.course_name FROM class_sessions cs
         JOIN courses c ON cs.course_id = c.id
         WHERE cs.course_id IN ($in) AND cs.teacher_id = ?
           AND cs.status IN ('upcoming','active')
         ORDER BY cs.session_date ASC, cs.start_time ASC LIMIT 5",
        $params
    )->fetchAll();
}

require_once APP_ROOT . '/views/layouts/header.php';
?>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card stat-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#e8edff">
                    <i class="bi bi-journal-bookmark-fill" style="color:#4361ee"></i>
                </div>
                <div>
                    <div class="fw-bold fs-4"><?= count($myCourses) ?></div>
                    <div class="text-muted small">Học phần của tôi</div>
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
                    <div class="text-muted small">Tổng buổi học</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#fff3cd">
                    <i class="bi bi-record-circle-fill" style="color:#f59e0b"></i>
                </div>
                <div>
                    <div class="fw-bold fs-4"><?= count($activeSessions) ?></div>
                    <div class="text-muted small">Đang hoạt động</div>
                </div>
            </div>
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

<!-- Quick actions -->
<div class="card p-3 mb-4">
    <h6 class="fw-bold mb-3"><i class="bi bi-lightning-fill me-2 text-warning"></i>Thao tác nhanh</h6>
    <div class="d-flex flex-wrap gap-2">
        <a href="<?= APP_URL ?>/teacher/sessions.php?action=create" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle me-1"></i>Tạo buổi học
        </a>
        <a href="<?= APP_URL ?>/teacher/attendance.php" class="btn btn-success btn-sm">
            <i class="bi bi-qr-code me-1"></i>Mở điểm danh
        </a>
        <a href="<?= APP_URL ?>/teacher/quiz.php?action=create" class="btn btn-warning btn-sm text-dark">
            <i class="bi bi-patch-question me-1"></i>Tạo quiz
        </a>
        <?php if ($openAlerts > 0): ?>
        <a href="<?= APP_URL ?>/teacher/alerts.php" class="btn btn-danger btn-sm">
            <i class="bi bi-bell me-1"></i>Xem cảnh báo (<?= $openAlerts ?>)
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Upcoming sessions -->
<?php if (!empty($upcomingSessions)): ?>
<div class="card mb-4">
    <div class="card-header bg-white fw-bold border-0 pt-3">
        <i class="bi bi-calendar-event-fill text-primary me-2"></i>Buổi học sắp tới
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr><th>Ngày</th><th>Tiết</th><th>Học phần</th><th>Tên buổi</th><th>Trạng thái</th><th></th></tr>
            </thead>
            <tbody>
            <?php foreach ($upcomingSessions as $s): ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($s['session_date'])) ?></td>
                    <td class="small text-muted"><?= substr($s['start_time'],0,5) ?> – <?= substr($s['end_time'],0,5) ?></td>
                    <td class="fw-semibold"><?= htmlspecialchars($s['course_name']) ?></td>
                    <td class="text-muted small"><?= htmlspecialchars($s['title'] ?? '—') ?></td>
                    <td>
                        <?php $sc=['upcoming'=>'secondary','active'=>'success','ended'=>'dark'];?>
                        <span class="badge bg-<?= $sc[$s['status']] ?? 'secondary' ?>">
                            <?= ucfirst($s['status']) ?>
                        </span>
                    </td>
                    <td>
                        <a href="<?= APP_URL ?>/teacher/attendance.php?session_id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-primary">
                            Điểm danh
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- My courses -->
<div class="card">
    <div class="card-header bg-white fw-bold border-0 pt-3">
        <i class="bi bi-journal-bookmark-fill text-warning me-2"></i>Học phần của tôi
    </div>
    <div class="card-body">
        <?php if (empty($myCourses)): ?>
            <p class="text-muted">Chưa được gán vào học phần nào.</p>
        <?php else: ?>
        <div class="row g-3">
        <?php foreach ($myCourses as $c): ?>
            <div class="col-md-4">
                <div class="card border h-100">
                    <div class="card-body">
                        <span class="badge bg-primary mb-2"><?= htmlspecialchars($c['course_code']) ?></span>
                        <h6 class="fw-bold mb-1"><?= htmlspecialchars($c['course_name']) ?></h6>
                        <small class="text-muted">HK <?= htmlspecialchars($c['semester']) ?></small>
                        <div class="mt-2 d-flex gap-2">
                            <a href="<?= APP_URL ?>/teacher/sessions.php?course_id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary">Buổi học</a>
                            <a href="<?= APP_URL ?>/teacher/engagement.php?course_id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-secondary">Engagement</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>