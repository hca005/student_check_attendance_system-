<?php
Middleware::requireAdmin();
$pageTitle = 'Alert Logs';
$currentPage = 'admin.alerts';
require APP_ROOT . '/views/layouts/header.php';

$alertTypeMap = [
    'low_attendance' => 'Low Attendance',
    'low_engagement' => 'Low Engagement',
];

$statusClass = [
    'pending'  => 'badge-warning',
    'reviewed' => 'badge-primary',
    'resolved' => 'badge-success',
];

$severitySeen = ['high' => 0, 'medium' => 0, 'low' => 0];
foreach ($alerts as $a) {
    $s = (string)($a['severity'] ?? '');
    if (isset($severitySeen[$s])) {
        $severitySeen[$s]++;
    }
}
?>

<div class="admin-page-title">
  <div class="left">
    <h1>Alert Logs</h1>
    <p>Review students with attendance or engagement risks.</p>
  </div>
  <form method="post" action="<?= APP_URL ?>/index.php?page=admin_alert_generate">
    <button class="btn btn-primary" type="submit">Generate Alerts</button>
  </form>
</div>

<?php if (!empty($flashSuccess)): ?>
  <div class="alert alert-success"><?= htmlspecialchars((string)$flashSuccess) ?></div>
<?php endif; ?>
<?php if (!empty($flashError)): ?>
  <div class="alert alert-danger"><?= htmlspecialchars((string)$flashError) ?></div>
<?php endif; ?>

<div class="stat-cards">
  <div class="card stat-card">
    <div class="stat-icon" style="background:#fffbeb">
      <svg fill="none" viewBox="0 0 24 24" stroke="#f59e0b" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/></svg>
    </div>
    <div><div class="stat-label">Total Alerts</div><div class="stat-value"><?= (int)$stats['total'] ?></div></div>
  </div>
  <div class="card stat-card">
    <div class="stat-icon" style="background:#fee2e2">
      <svg fill="none" viewBox="0 0 24 24" stroke="#ef4444" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    </div>
    <div><div class="stat-label">Open Alerts</div><div class="stat-value"><?= (int)$stats['open'] ?></div></div>
  </div>
  <div class="card stat-card">
    <div class="stat-icon" style="background:#ecfdf5">
      <svg fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="9 12 12 15 16 10"/></svg>
    </div>
    <div><div class="stat-label">Resolved Alerts</div><div class="stat-value"><?= (int)$stats['resolved'] ?></div></div>
  </div>
  <div class="card stat-card">
    <div class="stat-icon" style="background:#fee2e2">
      <svg fill="none" viewBox="0 0 24 24" stroke="#ef4444" stroke-width="2"><path d="M12 9v4"/><path d="M12 17h.01"/><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/></svg>
    </div>
    <div><div class="stat-label">Critical Alerts</div><div class="stat-value"><?= (int)$stats['critical'] ?></div></div>
  </div>
</div>

<div class="card alert-layout">
  <div class="alert-filters">
    <h3 style="margin:0 0 10px;font-size:15px">Filters</h3>
    <form method="get" action="<?= APP_URL ?>/index.php">
      <input type="hidden" name="page" value="admin_alerts">

      <div class="form-group">
        <label>Search</label>
        <input type="text" name="search" value="<?= htmlspecialchars((string)$filters['search']) ?>" placeholder="student, course, message">
      </div>

      <div class="form-group">
        <label>Alert Type</label>
        <select name="alert_type">
          <option value="">All Types</option>
          <option value="low_attendance" <?= $filters['alert_type'] === 'low_attendance' ? 'selected' : '' ?>>Low Attendance</option>
          <option value="low_engagement" <?= $filters['alert_type'] === 'low_engagement' ? 'selected' : '' ?>>Low Engagement</option>
        </select>
      </div>

      <div class="form-group">
        <label>Severity</label>
        <select name="severity">
          <option value="">All Levels</option>
          <option value="high"   <?= $filters['severity'] === 'high'   ? 'selected' : '' ?>>High</option>
          <option value="medium" <?= $filters['severity'] === 'medium' ? 'selected' : '' ?>>Medium</option>
          <option value="low"    <?= $filters['severity'] === 'low'    ? 'selected' : '' ?>>Low</option>
        </select>
      </div>

      <div class="form-group">
        <label>Status</label>
        <select name="status">
          <option value="">All Status</option>
          <option value="pending"  <?= $filters['status'] === 'pending'  ? 'selected' : '' ?>>Pending</option>
          <option value="reviewed" <?= $filters['status'] === 'reviewed' ? 'selected' : '' ?>>Reviewed</option>
          <option value="resolved" <?= $filters['status'] === 'resolved' ? 'selected' : '' ?>>Resolved</option>
        </select>
      </div>

      <div style="display:flex;gap:8px">
        <button class="btn btn-primary" type="submit" style="flex:1">Apply</button>
        <a class="btn btn-outline" href="<?= APP_URL ?>/index.php?page=admin_alerts" style="flex:1">Reset</a>
      </div>
    </form>
  </div>

  <div class="alert-feed">
    <?php foreach ($alerts as $alert): ?>
      <?php
        $severity = (string)$alert['severity'];
        $severityClass = $severity === 'critical' ? 'critical' : ($severity === 'high' ? 'high' : 'medium');
      ?>
      <div class="alert-item <?= $severityClass ?>">
        <div class="alert-row">
          <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
            <span class="severity-pill severity-<?= htmlspecialchars($severity) ?>"><?= strtoupper($severity) ?></span>
            <strong><?= htmlspecialchars((string)($alertTypeMap[$alert['alert_type']] ?? $alert['alert_type'])) ?></strong>
            <span style="color:#64748b;font-size:12px">| <?= date('M d, H:i', strtotime((string)$alert['created_at'])) ?></span>
          </div>
          <span class="badge <?= $statusClass[$alert['status']] ?? 'badge-gray' ?>"><?= strtoupper((string)$alert['status']) ?></span>
        </div>

        <div style="margin-top:8px">
          <div style="font-size:18px;font-weight:700;line-height:1.2">
            <?= htmlspecialchars((string)$alert['student_name']) ?>
            <span style="font-size:12px;color:#64748b;font-weight:600">ID: <?= htmlspecialchars((string)($alert['student_code'] ?: 'N/A')) ?></span>
          </div>
          <div style="font-size:13px;color:#475569;margin-top:4px">
            Course: <?= htmlspecialchars((string)$alert['course_code']) ?> - <?= htmlspecialchars((string)$alert['course_name']) ?>
          </div>
          <div style="font-size:13px;color:#334155;margin-top:6px"><?= htmlspecialchars((string)$alert['alert_message']) ?></div>
        </div>

        <div class="alert-row" style="margin-top:10px">
          <a class="btn btn-outline btn-sm" href="<?= APP_URL ?>/index.php?page=admin_alert_detail&id=<?= (int)$alert['id'] ?>">View</a>
          <form method="post" action="<?= APP_URL ?>/index.php?page=admin_alert_resolve&id=<?= (int)$alert['id'] ?>">
            <input type="hidden" name="status" value="resolved">
            <button class="btn btn-sm" style="background:#dbeafe;color:#1d4ed8">Resolve</button>
          </form>
          <?php if ($alert['status'] === 'pending'): ?>
          <form method="post" action="<?= APP_URL ?>/index.php?page=admin_alert_resolve&id=<?= (int)$alert['id'] ?>">
            <input type="hidden" name="status" value="reviewed">
            <button class="btn btn-sm" style="background:#fef9c3;color:#854d0e">Mark Reviewed</button>
          </form>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>

    <?php foreach ($severitySeen as $severityKey => $count): ?>
      <?php if ($count === 0): ?>
        <div class="alert-item <?= $severityKey ?>">
          <div class="alert-row">
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
              <span class="severity-pill severity-<?= $severityKey ?>"><?= strtoupper($severityKey) ?></span>
              <strong>No <?= $severityKey ?> alerts in current filter</strong>
            </div>
            <span class="badge badge-gray">INFO</span>
          </div>
          <div style="font-size:13px;color:#64748b;margin-top:8px">
            This placeholder keeps severity styling consistent when no records are available for this level.
          </div>
        </div>
      <?php endif; ?>
    <?php endforeach; ?>

    <?php if (empty($alerts)): ?>
      <div style="padding:18px;color:#64748b;text-align:center">No alert logs found for current filters.</div>
    <?php endif; ?>

    <div class="list-meta">Showing <?= count($alerts) ?> of <?= (int)$totalCount ?> alerts</div>
    <?php if ($totalPages > 1): ?>
      <div class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <a class="page-btn <?= $i === $currentPageNum ? 'active' : '' ?>" href="<?= APP_URL ?>/index.php?page=admin_alerts&search=<?= urlencode((string)$filters['search']) ?>&alert_type=<?= urlencode((string)$filters['alert_type']) ?>&severity=<?= urlencode((string)$filters['severity']) ?>&status=<?= urlencode((string)$filters['status']) ?>&p=<?= $i ?>">
            <?= $i ?>
          </a>
        <?php endfor; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>
