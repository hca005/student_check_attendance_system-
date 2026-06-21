<?php
$page_title = 'Attendance Sessions';
$active_nav = 'attendance';
require_once APP_ROOT . '/views/layouts/header.php';
?>
<div class="container mt-4">
    <div class="admin-page-title">
        <div class="left">
            <h1>Attendance Sessions</h1>
            <p>Review and manage attendance for your upcoming class sessions.</p>
        </div>
    </div>

    <div class="card">
      <div class="card-header">
        <div>
          <div class="card-title">Session Attendance</div>
          <div class="text-muted" style="font-size:13px;">Review and manage attendance for your upcoming class sessions.</div>
        </div>
        <a href="<?= APP_URL ?>/teacher/dashboard.php" class="btn btn-outline-secondary btn-sm">Back to Dashboard</a>
      </div>
      <div class="card-body">
        <?php if (empty($sessions)): ?>
          <div class="alert alert-info">No attendance sessions found yet.</div>
        <?php else: ?>
          <div class="table-wrap">
            <div class="table-responsive">
              <table class="table table-hover table-striped table-bordered mb-0">
                <thead><tr><th>Ngày</th><th>Buổi học</th><th>Môn</th><th>Trạng thái</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($sessions as $s): ?>
                <tr>
                    <td><?= htmlspecialchars($s['session_date']) ?></td>
                    <td><?= htmlspecialchars($s['title'] ?? 'Buổi học') ?></td>
                    <td><?= htmlspecialchars($s['course_name']) ?></td>
                    <td><span class="badge bg-<?php echo $s['status'] === 'active' ? 'success' : ($s['status'] === 'upcoming' ? 'info' : 'secondary'); ?>"><?= htmlspecialchars(ucfirst($s['status'])) ?></span></td>
                    <td>
                        <a href="<?= APP_URL ?>/teacher/attendance/methods_list.php?session_id=<?= $s['id'] ?>" class="btn btn-sm btn-primary">Quản lý</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>
