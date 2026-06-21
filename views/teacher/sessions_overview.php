<?php
// views/teacher/sessions_overview.php
$page_title = 'My Sessions';
$active_nav = 'sessions';
require_once APP_ROOT . '/views/layouts/header.php';

$totalSessions   = count($sessions);
$activeCount     = 0;
$upcomingCount   = 0;
$endedCount      = 0;
foreach ($sessions as $s) {
    if ($s['status'] === 'active') $activeCount++;
    elseif ($s['status'] === 'upcoming') $upcomingCount++;
    else $endedCount++;
}

$statusBadge = [
    'upcoming' => 'badge-gray',
    'active'   => 'badge-success',
    'ended'    => 'badge-gray',
];
?>

<div class="admin-page-title">
  <div class="left">
    <h1>My Sessions</h1>
    <p>Browse and manage all class sessions you teach.</p>
  </div>
  <div class="right">
    <a href="<?= APP_URL ?>/teacher/dashboard.php" class="btn btn-outline-secondary btn-sm">Back to Dashboard</a>
  </div>
</div>

<div class="stat-cards">
  <div class="card stat-card">
    <div class="stat-icon" style="background:#EFF6FF">
      <svg fill="none" viewBox="0 0 24 24" stroke="#2563EB" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
    </div>
    <div><div class="stat-value"><?= $totalSessions ?></div><div class="stat-label">Total Sessions</div></div>
  </div>
  <div class="card stat-card">
    <div class="stat-icon" style="background:#F0FDF4">
      <svg fill="none" viewBox="0 0 24 24" stroke="#10B981" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="9 12 12 15 16 10"/></svg>
    </div>
    <div><div class="stat-value"><?= $activeCount ?></div><div class="stat-label">Active Now</div></div>
  </div>
  <div class="card stat-card">
    <div class="stat-icon" style="background:#FFF7ED">
      <svg fill="none" viewBox="0 0 24 24" stroke="#F59E0B" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
    </div>
    <div><div class="stat-value"><?= $upcomingCount ?></div><div class="stat-label">Upcoming</div></div>
  </div>
  <div class="card stat-card">
    <div class="stat-icon" style="background:#F1F5F9">
      <svg fill="none" viewBox="0 0 24 24" stroke="#64748B" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
    </div>
    <div><div class="stat-value"><?= $endedCount ?></div><div class="stat-label">Completed</div></div>
  </div>
</div>

<div class="card">
  <div class="card-body" style="padding:0;">
    <div class="table-wrap">
      <table class="table table-hover table-striped mb-0">
        <thead>
          <tr>
            <th>Date</th>
            <th>Session</th>
            <th>Course</th>
            <th>Status</th>
            <th>Attendance</th>
            <th>Quiz</th>
          </tr>
        </thead>
        <tbody>
        <?php if (empty($sessions)): ?>
          <tr><td colspan="6" class="text-center text-muted py-4">No class sessions found yet.</td></tr>
        <?php else: ?>
          <?php foreach ($sessions as $s): ?>
          <tr>
            <td style="font-weight:600"><?= htmlspecialchars(date('d/m/Y', strtotime($s['session_date']))) ?></td>
            <td><?= htmlspecialchars($s['title'] ?? 'Session') ?></td>
            <td><span class="badge badge-primary"><?= htmlspecialchars($s['course_name']) ?></span></td>
            <td><span class="badge <?= $statusBadge[$s['status']] ?? 'badge-gray' ?>"><?= ucfirst(htmlspecialchars($s['status'])) ?></span></td>
            <td><a href="<?= APP_URL ?>/teacher/attendance/methods_list.php?session_id=<?= (int)$s['id'] ?>" class="btn btn-sm btn-primary">Attendance</a></td>
            <td><a href="<?= APP_URL ?>/teacher/quiz/sessions_list.php?session_id=<?= (int)$s['id'] ?>" class="btn btn-sm btn-warning">Quiz</a></td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>