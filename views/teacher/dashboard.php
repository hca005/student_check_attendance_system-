<?php
Middleware::requireTeacher();
$pageTitle   = 'Teacher Dashboard';
$currentPage = 'teacher.dashboard';

$db     = Database::getInstance();
$userId = Middleware::user()['id'];

$myCourses = $db->query(
  "SELECT c.* FROM courses c JOIN enrollments ce ON ce.course_id=c.id
   WHERE ce.user_id=? AND ce.role='teacher' AND c.is_active=1", [$userId]
)->fetchAll();

$courseIds = array_column($myCourses,'id') ?: [0];
$in = implode(',', $courseIds);

$totalSessions  = $db->query("SELECT COUNT(*) FROM class_sessions WHERE course_id IN ($in)")->fetchColumn();
$activeSessions = $db->query("SELECT COUNT(*) FROM class_sessions WHERE course_id IN ($in) AND status='active'")->fetchColumn();
$totalStudents  = $db->query("SELECT COUNT(DISTINCT user_id) FROM enrollments WHERE course_id IN ($in) AND role='student'")->fetchColumn();
$openAlerts     = $db->query("SELECT COUNT(*) FROM alert_logs WHERE course_id IN ($in) AND status='open'")->fetchColumn();

$upcoming = $db->query(
  "SELECT cs.*, c.course_name FROM class_sessions cs
   JOIN courses c ON cs.course_id=c.id
   WHERE cs.teacher_id=? AND cs.status IN ('upcoming','active')
   ORDER BY cs.session_date ASC, cs.start_time ASC LIMIT 6", [$userId]
)->fetchAll();

require_once APP_ROOT . '/views/layouts/header.php';
?>

<div class="page-title">My Dashboard</div>
<p class="page-sub">Overview of your courses and classroom activity</p>

<div class="stat-cards">
  <div class="card stat-card">
    <div class="stat-icon" style="background:#EFF6FF">
      <svg fill="none" viewBox="0 0 24 24" stroke="#2563EB" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
    </div>
    <div><div class="stat-value"><?= count($myCourses) ?></div><div class="stat-label">My Courses</div></div>
  </div>
  <div class="card stat-card">
    <div class="stat-icon" style="background:#F0FDF4">
      <svg fill="none" viewBox="0 0 24 24" stroke="#10B981" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
    </div>
    <div><div class="stat-value"><?= $totalStudents ?></div><div class="stat-label">Students</div></div>
  </div>
  <div class="card stat-card">
    <div class="stat-icon" style="background:#FFF7ED">
      <svg fill="none" viewBox="0 0 24 24" stroke="#F59E0B" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
    </div>
    <div>
      <div class="stat-value"><?= $totalSessions ?></div>
      <div class="stat-label">Total Sessions</div>
      <?php if ($activeSessions): ?>
      <div style="font-size:11px;margin-top:2px"><span class="badge badge-success"><?= $activeSessions ?> active</span></div>
      <?php endif; ?>
    </div>
  </div>
  <div class="card stat-card">
    <div class="stat-icon" style="background:#FEF2F2">
      <svg fill="none" viewBox="0 0 24 24" stroke="#EF4444" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
    </div>
    <div><div class="stat-value"><?= $openAlerts ?></div><div class="stat-label">Open Alerts</div></div>
  </div>
</div>

<!-- Quick actions -->
<div class="card" style="padding:18px;margin-bottom:24px">
  <div style="font-weight:700;font-size:13px;margin-bottom:12px;color:#374151">⚡ Quick Actions</div>
  <div style="display:flex;flex-wrap:wrap;gap:10px">
    <a href="<?= APP_URL ?>/teacher/sessions.php?action=create" class="btn btn-primary btn-sm">
      <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      New Session
    </a>
    <a href="<?= APP_URL ?>/teacher/attendance.php" class="btn btn-success btn-sm">
      <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
      Take Attendance
    </a>
    <a href="<?= APP_URL ?>/teacher/quiz.php?action=create" class="btn btn-sm" style="background:#F59E0B;color:#fff">
      <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/></svg>
      Create Quiz
    </a>
    <?php if ($openAlerts > 0): ?>
    <a href="<?= APP_URL ?>/teacher/alerts.php" class="btn btn-danger btn-sm">
      View <?= $openAlerts ?> Alert<?= $openAlerts > 1 ? 's' : '' ?>
    </a>
    <?php endif; ?>
  </div>
</div>

<!-- Upcoming sessions -->
<?php if (!empty($upcoming)): ?>
<div class="card" style="margin-bottom:24px">
  <div style="padding:16px 20px 12px;font-weight:700;font-size:14px;border-bottom:1px solid #F1F5F9">📅 Upcoming &amp; Active Sessions</div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Date</th><th>Time</th><th>Course</th><th>Title</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($upcoming as $s):
        $statusCls = ['upcoming'=>'badge-gray','active'=>'badge-success','ended'=>''];
      ?>
      <tr>
        <td style="font-weight:600"><?= date('d/m/Y', strtotime($s['session_date'])) ?></td>
        <td style="color:#64748B;font-size:13px"><?= substr($s['start_time'],0,5) ?> – <?= substr($s['end_time'],0,5) ?></td>
        <td><span class="badge badge-primary"><?= htmlspecialchars($s['course_name']) ?></span></td>
        <td style="font-size:13px"><?= htmlspecialchars($s['title'] ?? '—') ?></td>
        <td><span class="badge <?= $statusCls[$s['status']] ?? 'badge-gray' ?>"><?= ucfirst($s['status']) ?></span></td>
        <td>
          <div style="display:flex;gap:6px">
            <a href="<?= APP_URL ?>/teacher/attendance.php?session_id=<?= $s['id'] ?>" class="btn btn-outline btn-sm">Attendance</a>
            <a href="<?= APP_URL ?>/teacher/quiz.php?session_id=<?= $s['id'] ?>" class="btn btn-sm" style="background:#F1F5F9;color:#374151">Quiz</a>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<!-- My courses grid -->
<div style="font-weight:700;font-size:15px;margin-bottom:14px">My Courses</div>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px">
<?php foreach ($myCourses as $c):
  $stuCount = $db->query("SELECT COUNT(*) FROM enrollments WHERE course_id=? AND role='student'", [$c['id']])->fetchColumn();
  $sesCount = $db->query("SELECT COUNT(*) FROM class_sessions WHERE course_id=?", [$c['id']])->fetchColumn();
?>
<div class="card" style="padding:20px">
  <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px">
    <span class="badge badge-primary"><?= htmlspecialchars($c['course_code']) ?></span>
    <span class="badge badge-gray">HK <?= htmlspecialchars($c['semester']) ?></span>
  </div>
  <div style="font-weight:700;font-size:14px;color:#0F172A;margin-bottom:4px"><?= htmlspecialchars($c['course_name']) ?></div>
  <div style="font-size:12px;color:#94A3B8;margin-bottom:16px"><?= $stuCount ?> students · <?= $sesCount ?> sessions</div>
  <div style="display:flex;gap:8px">
    <a href="<?= APP_URL ?>/teacher/sessions.php?course_id=<?= $c['id'] ?>" class="btn btn-outline btn-sm" style="flex:1;justify-content:center">Sessions</a>
    <a href="<?= APP_URL ?>/teacher/engagement.php?course_id=<?= $c['id'] ?>" class="btn btn-sm" style="flex:1;background:#F1F5F9;color:#374151;justify-content:center">Engagement</a>
  </div>
</div>
<?php endforeach; ?>
</div>

<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>