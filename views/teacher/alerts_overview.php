<?php
$page_title = 'Alerts Overview';
$active_nav = 'alerts';
require_once APP_ROOT . '/views/layouts/header.php';
?>
<div class="container mt-4">
  <div class="admin-page-title">
    <div class="left">
      <h1>Alerts Overview</h1>
      <p>Review and respond to student attendance alerts.</p>
    </div>
    <div class="right">
      <a href="<?= APP_URL ?>/teacher/dashboard.php" class="btn btn-outline-secondary btn-sm">Back to Dashboard</a>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <div>
        <div class="card-title">Alerts Overview</div>
        <div class="text-muted" style="font-size:13px;">Review and respond to attendance alerts from students.</div>
      </div>
    </div>
    <div class="card-body">
      <div class="table-wrap">
        <div class="table-responsive">
          <table class="table table-hover table-striped table-bordered mb-0">
            <thead><tr><th>Sinh viên</th><th>Mã SV</th><th>Môn</th><th>Loại</th><th>Nội dung</th><th>Trạng thái</th></tr></thead>
            <tbody>
        <?php if (empty($alerts)): ?>
        <tr><td colspan="6" class="text-center">Không có alert nào.</td></tr>
        <?php else: ?>
        <?php foreach ($alerts as $a): ?>
        <tr>
            <td><?= htmlspecialchars($a['full_name']) ?></td>
            <td><?= htmlspecialchars($a['student_code'] ?? '') ?></td>
            <td><?= htmlspecialchars($a['course_name']) ?></td>
            <td><span class="badge bg-danger"><?= $a['alert_type'] ?></span></td>
            <td><?= htmlspecialchars($a['alert_message']) ?></td>
            <td><?= $a['status'] ?></td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>
