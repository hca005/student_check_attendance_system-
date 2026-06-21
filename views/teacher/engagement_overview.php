<?php
$page_title = 'Engagement Overview';
$active_nav = 'engagement';
require_once APP_ROOT . '/views/layouts/header.php';
?>
<div class="container mt-4">
  <div class="admin-page-title">
    <div class="left">
      <h1>Engagement Overview</h1>
      <p>Track student participation and quiz performance efficiently.</p>
    </div>
    <div class="right">
      <a href="<?= APP_URL ?>/teacher/dashboard.php" class="btn btn-outline-secondary btn-sm">Back to Dashboard</a>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <div>
        <div class="card-title">Engagement Overview</div>
        <div class="text-muted" style="font-size:13px;">Student participation and performance summary.</div>
      </div>
    </div>
    <div class="card-body">
      <div class="table-wrap">
        <div class="table-responsive">
          <table class="table table-hover table-striped table-bordered mb-0">
            <thead><tr><th>Sinh viên</th><th>Mã SV</th><th>Môn</th><th>Buổi tham gia</th><th>Quiz Score</th><th>Engagement Index</th></tr></thead>
            <tbody>
        <?php if (empty($scores)): ?>
        <tr><td colspan="6" class="text-center">Chưa có dữ liệu engagement.</td></tr>
        <?php else: ?>
        <?php foreach ($scores as $s): ?>
        <tr>
            <td><?= htmlspecialchars($s['full_name']) ?></td>
            <td><?= htmlspecialchars($s['student_code'] ?? '') ?></td>
            <td><?= htmlspecialchars($s['course_name']) ?></td>
            <td><?= $s['attended_sessions'] ?>/<?= $s['total_sessions'] ?></td>
            <td><?= $s['total_quiz_score'] ?></td>
            <td><strong><?= $s['engagement_index'] ?></strong></td>
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
