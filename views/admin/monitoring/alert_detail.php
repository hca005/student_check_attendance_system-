<?php
Middleware::requireAdmin();
$pageTitle = 'Alert Detail';
$currentPage = 'admin.alerts';
require APP_ROOT . '/views/layouts/header.php';

$typeLabel = [
    'high_absence' => 'Absence Risk',
    'low_engagement' => 'Low Engagement',
    'missed_quiz' => 'Missed Quiz',
];

$severity = $alert['alert_type'] === 'high_absence'
    ? 'Critical'
    : ($alert['alert_type'] === 'low_engagement' ? 'High' : 'Medium');
?>

<div class="admin-page-title">
  <div class="left">
    <h1>Alert Detail #<?= (int)$alert['id'] ?></h1>
    <p>Detailed student risk context and resolution workflow.</p>
  </div>
  <a href="<?= APP_URL ?>/index.php?page=admin_alerts" class="btn btn-outline">Back to Alerts</a>
</div>

<?php if (!empty($flashSuccess)): ?>
  <div class="alert alert-success"><?= $flashSuccess ?></div>
<?php endif; ?>
<?php if (!empty($flashError)): ?>
  <div class="alert alert-danger"><?= $flashError ?></div>
<?php endif; ?>

<div class="split-layout">
  <div class="card" style="padding:16px">
    <h3 style="margin:0 0 10px;font-size:16px">Alert Information</h3>
    <div class="form-grid">
      <div class="form-group">
        <label>Student</label>
        <div><strong><?= htmlspecialchars($alert['student_name']) ?></strong></div>
      </div>
      <div class="form-group">
        <label>Student Code</label>
        <div><?= htmlspecialchars($alert['student_code'] ?: 'N/A') ?></div>
      </div>
      <div class="form-group">
        <label>Email</label>
        <div><?= htmlspecialchars($alert['student_email']) ?></div>
      </div>
      <div class="form-group">
        <label>Course</label>
        <div><?= htmlspecialchars($alert['course_code']) ?> - <?= htmlspecialchars($alert['course_name']) ?></div>
      </div>
      <div class="form-group">
        <label>Alert Type</label>
        <div><span class="badge badge-warning"><?= htmlspecialchars($typeLabel[$alert['alert_type']] ?? $alert['alert_type']) ?></span></div>
      </div>
      <div class="form-group">
        <label>Severity</label>
        <div><span class="badge <?= $severity === 'Critical' ? 'badge-danger' : ($severity === 'High' ? 'badge-warning' : 'badge-gray') ?>"><?= $severity ?></span></div>
      </div>
    </div>

    <div class="form-group">
      <label>Alert Reason</label>
      <div style="padding:12px;border:1px solid #e2e8f0;border-radius:10px;background:#f8fafc"><?= htmlspecialchars($alert['alert_message']) ?></div>
    </div>

    <h3 style="margin:10px 0;font-size:16px">Evidence Summary</h3>
    <div class="form-grid">
      <div class="form-group">
        <label>Total Sessions</label>
        <div><strong><?= (int)($alert['total_sessions'] ?? 0) ?></strong></div>
      </div>
      <div class="form-group">
        <label>Attended Sessions</label>
        <div><strong><?= (int)($alert['attended_sessions'] ?? 0) ?></strong></div>
      </div>
      <div class="form-group">
        <label>Quiz Score</label>
        <div><strong><?= number_format((float)($alert['total_quiz_score'] ?? 0), 2) ?></strong></div>
      </div>
      <div class="form-group">
        <label>Interaction Points</label>
        <div><strong><?= number_format((float)($alert['total_interaction_points'] ?? 0), 2) ?></strong></div>
      </div>
      <div class="form-group">
        <label>Total Engagement Score</label>
        <div><strong><?= number_format((float)($alert['engagement_index'] ?? 0), 2) ?></strong></div>
      </div>
      <div class="form-group">
        <label>Current Status</label>
        <div><span class="badge <?= $alert['status'] === 'resolved' ? 'badge-success' : ($alert['status'] === 'open' ? 'badge-warning' : 'badge-gray') ?>"><?= strtoupper($alert['status']) ?></span></div>
      </div>
    </div>

    <div class="form-group">
      <label>Suggested Action</label>
      <div style="padding:12px;border:1px solid #e2e8f0;border-radius:10px;background:#f8fafc">
        Contact student advisor, notify class teacher, and schedule follow-up intervention if risk remains for next session cycle.
      </div>
    </div>
  </div>

  <div class="card quick-panel">
    <h3>Resolve Alert</h3>
    <p>Update alert status and resolution tracking.</p>
    <form method="post" action="<?= APP_URL ?>/index.php?page=admin_alert_resolve&id=<?= (int)$alert['id'] ?>">
      <div class="form-group">
        <label>Status</label>
        <select name="status">
          <option value="open" <?= $alert['status'] === 'open' ? 'selected' : '' ?>>Open</option>
          <option value="resolved" <?= $alert['status'] === 'resolved' ? 'selected' : '' ?>>Resolved</option>
          <option value="ignored" <?= $alert['status'] === 'ignored' ? 'selected' : '' ?>>Ignored</option>
        </select>
      </div>

      <div class="form-group">
        <label>Resolution Note</label>
        <textarea name="resolution_note" placeholder="Optional internal note for your team"></textarea>
      </div>

      <button class="btn btn-primary" type="submit" style="width:100%">Mark as Resolved</button>
    </form>

    <div style="margin-top:12px;font-size:12px;color:#64748b">
      <?php if (!empty($alert['resolved_by'])): ?>
        Resolved by <strong><?= htmlspecialchars($alert['resolver_name'] ?: 'Admin') ?></strong><br>
        at <?= htmlspecialchars((string)$alert['resolved_at']) ?>
      <?php else: ?>
        This alert has not been resolved yet.
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>
