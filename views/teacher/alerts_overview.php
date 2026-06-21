<?php
$page_title = 'Alerts Overview';
$active_nav = 'alerts';
require_once APP_ROOT . '/views/layouts/header.php';

$alertTypeMap = [
    'low_attendance' => 'Low Attendance',
    'low_engagement' => 'Low Engagement',
];
$statusVN = [
    'pending'  => 'Pending',
    'reviewed' => 'Reviewed',
    'resolved' => 'Resolved',
];
$statusBadge = [
    'pending'  => 'badge-warning',
    'reviewed' => 'badge-primary',
    'resolved' => 'badge-success',
];

$pendingCount = 0; $reviewedCount = 0; $resolvedCount = 0;
foreach ($alerts as $a) {
    if ($a['status'] === 'pending') $pendingCount++;
    elseif ($a['status'] === 'reviewed') $reviewedCount++;
    elseif ($a['status'] === 'resolved') $resolvedCount++;
}
?>
<div class="admin-page-title">
  <div class="left">
    <h1>Alerts Overview</h1>
    <p>Review and respond to student attendance alerts.</p>
  </div>
  <div class="right">
    <a href="<?= APP_URL ?>/teacher/dashboard.php" class="btn btn-outline-secondary btn-sm">Back to Dashboard</a>
  </div>
</div>

<div class="stat-cards">
  <div class="card stat-card">
    <div class="stat-icon" style="background:#FEF2F2">
      <svg fill="none" viewBox="0 0 24 24" stroke="#EF4444" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
    </div>
    <div><div class="stat-value"><?= count($alerts) ?></div><div class="stat-label">Total Alerts</div></div>
  </div>
  <div class="card stat-card">
    <div class="stat-icon" style="background:#FFF7ED">
      <svg fill="none" viewBox="0 0 24 24" stroke="#F59E0B" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
    </div>
    <div><div class="stat-value"><?= $pendingCount ?></div><div class="stat-label">Pending</div></div>
  </div>
  <div class="card stat-card">
    <div class="stat-icon" style="background:#EFF6FF">
      <svg fill="none" viewBox="0 0 24 24" stroke="#2563EB" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
    </div>
    <div><div class="stat-value"><?= $reviewedCount ?></div><div class="stat-label">Reviewed</div></div>
  </div>
  <div class="card stat-card">
    <div class="stat-icon" style="background:#F0FDF4">
      <svg fill="none" viewBox="0 0 24 24" stroke="#10B981" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="9 12 12 15 16 10"/></svg>
    </div>
    <div><div class="stat-value"><?= $resolvedCount ?></div><div class="stat-label">Resolved</div></div>
  </div>
</div>

<div class="card">
  <div class="card-body">
    <div class="table-wrap">
      <table class="table table-hover table-striped mb-0">
        <thead>
          <tr>
            <th>Student</th>
            <th>Student ID</th>
            <th>Course</th>
            <th>Type</th>
            <th>Message</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
        <?php if (empty($alerts)): ?>
        <tr><td colspan="6" class="text-center text-muted py-4">No alerts found.</td></tr>
        <?php else: ?>
        <?php foreach ($alerts as $a): ?>
        <tr>
            <td style="font-weight:600"><?= htmlspecialchars($a['full_name']) ?></td>
            <td><code><?= htmlspecialchars($a['student_code'] ?? '') ?></code></td>
            <td><span class="badge badge-primary"><?= htmlspecialchars($a['course_name']) ?></span></td>
            <td><span class="badge badge-danger"><?= htmlspecialchars($alertTypeMap[$a['alert_type']] ?? $a['alert_type']) ?></span></td>
            <td style="font-size:13px;color:#334155;max-width:420px;"><?= htmlspecialchars($a['alert_message']) ?></td>
            <td><span class="badge <?= $statusBadge[$a['status']] ?? 'badge-gray' ?>"><?= $statusVN[$a['status']] ?? htmlspecialchars($a['status']) ?></span></td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>