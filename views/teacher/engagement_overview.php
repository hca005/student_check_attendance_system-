<?php
$page_title = 'Engagement Overview';
$active_nav = 'engagement';
require_once APP_ROOT . '/views/layouts/header.php';

$avgIndex = 0;
$riskCount = 0; $mediumCount = 0; $goodCount = 0;
if (!empty($scores)) {
    $sum = 0;
    foreach ($scores as $s) {
        $idx = (float)$s['engagement_index'];
        $sum += $idx;
        if ($idx >= 70) $goodCount++;
        elseif ($idx >= 40) $mediumCount++;
        else $riskCount++;
    }
    $avgIndex = round($sum / count($scores), 1);
}

function engagement_badge_class(float $idx): string
{
    if ($idx >= 70) return 'badge-success';
    if ($idx >= 40) return 'badge-warning';
    return 'badge-danger';
}
?>
<div class="admin-page-title">
  <div class="left">
    <h1>Engagement Overview</h1>
    <p>Track student participation and quiz performance efficiently.</p>
  </div>
  <div class="right">
    <a href="<?= APP_URL ?>/teacher/dashboard.php" class="btn btn-outline-secondary btn-sm">Back to Dashboard</a>
  </div>
</div>

<div class="stat-cards">
  <div class="card stat-card">
    <div class="stat-icon" style="background:#EFF6FF">
      <svg fill="none" viewBox="0 0 24 24" stroke="#2563EB" stroke-width="2"><path d="M3 3v18h18"/><path d="M18.7 8l-5.1 5.1-2.8-2.8L7 14"/></svg>
    </div>
    <div><div class="stat-value"><?= $avgIndex ?></div><div class="stat-label">Avg Engagement Index</div></div>
  </div>
  <div class="card stat-card">
    <div class="stat-icon" style="background:#F0FDF4">
      <svg fill="none" viewBox="0 0 24 24" stroke="#10B981" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="9 12 12 15 16 10"/></svg>
    </div>
    <div><div class="stat-value"><?= $goodCount ?></div><div class="stat-label">High Engagement (&ge;70)</div></div>
  </div>
  <div class="card stat-card">
    <div class="stat-icon" style="background:#FFF7ED">
      <svg fill="none" viewBox="0 0 24 24" stroke="#F59E0B" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/></svg>
    </div>
    <div><div class="stat-value"><?= $mediumCount ?></div><div class="stat-label">Medium (40-69)</div></div>
  </div>
  <div class="card stat-card">
    <div class="stat-icon" style="background:#FEF2F2">
      <svg fill="none" viewBox="0 0 24 24" stroke="#EF4444" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
    </div>
    <div><div class="stat-value"><?= $riskCount ?></div><div class="stat-label">At Risk (&lt;40)</div></div>
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
      <table class="table table-hover table-striped mb-0">
        <thead>
          <tr>
            <th>Sinh viên</th>
            <th>Mã SV</th>
            <th>Môn</th>
            <th>Buổi tham gia</th>
            <th>Quiz Score</th>
            <th>Engagement Index</th>
          </tr>
        </thead>
        <tbody>
        <?php if (empty($scores)): ?>
        <tr><td colspan="6" class="text-center text-muted py-4">Chưa có dữ liệu engagement.</td></tr>
        <?php else: ?>
        <?php foreach ($scores as $s):
            $idx = (float)$s['engagement_index'];
        ?>
        <tr>
            <td style="font-weight:600"><?= htmlspecialchars($s['full_name']) ?></td>
            <td><code><?= htmlspecialchars($s['student_code'] ?? '') ?></code></td>
            <td><span class="badge badge-primary"><?= htmlspecialchars($s['course_name']) ?></span></td>
            <td><?= (int)$s['attended_sessions'] ?>/<?= (int)$s['total_sessions'] ?></td>
            <td><?= htmlspecialchars($s['total_quiz_score']) ?></td>
            <td>
                <span class="badge <?= engagement_badge_class($idx) ?>"><?= $idx ?></span>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>