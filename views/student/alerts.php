<?php
// views/student/alerts.php
// Trang xem toàn bộ cảnh báo cá nhân của student
require_once APP_ROOT . '/views/layouts/header.php';

$openAlerts     = array_filter($alerts, fn($a) => $a['status'] === 'pending');
$resolvedAlerts = array_filter($alerts, fn($a) => $a['status'] !== 'pending');
$typeLabel = ['low_attendance' => 'Low attendance', 'low_engagement' => 'Low engagement'];
?>

<div class="admin-page-title">
  <div class="left">
    <h1>My Alerts</h1>
    <p>System warnings about your attendance or engagement</p>
  </div>
</div>

<div class="stat-cards" style="grid-template-columns:repeat(2, minmax(0,1fr))">
  <div class="card stat-card">
    <div class="stat-icon" style="background:#FEF2F2"><svg fill="none" viewBox="0 0 24 24" stroke="#EF4444" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg></div>
    <div><div class="stat-value" style="color:#EF4444"><?= count($openAlerts) ?></div><div class="stat-label">Pending</div></div>
  </div>
  <div class="card stat-card">
    <div class="stat-icon" style="background:#F0FDF4"><svg fill="none" viewBox="0 0 24 24" stroke="#10B981" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="9 12 12 15 16 10"/></svg></div>
    <div><div class="stat-value" style="color:#10B981"><?= count($resolvedAlerts) ?></div><div class="stat-label">Reviewed / resolved</div></div>
  </div>
</div>

<?php if (empty($alerts)): ?>
<div class="card empty-state">
  <div class="icon-circle" style="background:#F0FDF4"><svg fill="none" viewBox="0 0 24 24" stroke="#10B981" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="9 12 12 15 16 10"/></svg></div>
  <div class="title">All clear</div>
  No alerts right now — keep up the good work.
</div>

<?php else: ?>

<?php foreach (array_merge($openAlerts, $resolvedAlerts) as $a):
  $sevClass = ['high' => 'critical', 'medium' => 'high', 'low' => 'medium'][$a['severity']] ?? 'medium';
  $pillClass = ['high' => 'severity-critical', 'medium' => 'severity-high', 'low' => 'severity-medium'][$a['severity']] ?? 'severity-medium';
?>
<div class="alert-item <?= $sevClass ?>">
  <div class="alert-row">
    <div>
      <span class="severity-pill <?= $pillClass ?>"><?= strtoupper($a['severity']) ?></span>
      <span class="badge badge-primary" style="margin-left:6px"><?= htmlspecialchars($a['course_code']) ?></span>
      <strong style="margin-left:6px;font-size:13px"><?= $typeLabel[$a['alert_type']] ?? $a['alert_type'] ?></strong>
    </div>
    <span style="font-size:12px;color:var(--text-muted)"><?= date('d/m/Y', strtotime($a['created_at'])) ?></span>
  </div>
  <div style="font-size:13px;color:var(--text);margin-top:10px;line-height:1.6"><?= htmlspecialchars($a['message']) ?></div>
  <div style="margin-top:10px;display:flex;justify-content:space-between;align-items:center">
    <span style="font-size:12px;color:var(--text-muted)">Course: <?= htmlspecialchars($a['course_name']) ?></span>
    <?php if ($a['status'] === 'pending'): ?>
    <button class="btn btn-outline btn-sm" onclick="dismissAlert(<?= $a['alert_id'] ?>, this)">
      <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
      Mark as seen
    </button>
    <?php else: ?>
    <span class="badge badge-success"><?= $a['status'] === 'resolved' ? 'Resolved' : 'Seen' ?></span>
    <?php endif; ?>
  </div>
</div>
<?php endforeach; ?>

<?php endif; ?>

<script>
async function dismissAlert(id, btn) {
  btn.disabled = true; btn.textContent = 'Updating...';
  try {
    const res  = await fetch('<?= APP_URL ?>/student/alerts.php?action=dismiss', {
      method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: `id=${id}`
    });
    const data = await res.json();
    if (data.success) location.reload();
    else { btn.disabled = false; btn.textContent = 'Mark as seen'; alert(data.message); }
  } catch {
    btn.disabled = false; btn.textContent = 'Mark as seen';
  }
}
</script>

<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>