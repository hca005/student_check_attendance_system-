<?php
Middleware::requireStudent();
$pageTitle   = 'My Overview';
$currentPage = 'student.dashboard';

$db     = Database::getInstance();
$userId = Middleware::user()['id'];

$myCourses = $db->query(
  "SELECT c.* FROM courses c JOIN course_enrollments ce ON ce.course_id=c.id
   WHERE ce.user_id=? AND ce.role='student' AND c.is_active=1", [$userId]
)->fetchAll();

$courseIds = array_column($myCourses,'id') ?: [0];
$in = implode(',', $courseIds);

$totalPresent  = $db->query("SELECT COUNT(*) FROM attendance_records WHERE student_id=? AND status='present'", [$userId])->fetchColumn();
$totalAbsent   = $db->query("SELECT COUNT(*) FROM attendance_records WHERE student_id=? AND status='absent'",  [$userId])->fetchColumn();
$totalQuizDone = $db->query("SELECT COUNT(*) FROM quiz_submissions WHERE student_id=?", [$userId])->fetchColumn();
$avgEngage     = $db->query("SELECT ROUND(AVG(engagement_index),1) FROM engagement_scores WHERE student_id=?", [$userId])->fetchColumn() ?? 0;
$myAlerts      = $db->query("SELECT COUNT(*) FROM alert_logs WHERE student_id=? AND status='open'", [$userId])->fetchColumn();

require_once APP_ROOT . '/views/layouts/header.php';
?>

<div class="page-title">My Overview</div>
<p class="page-sub">Track your attendance, quizzes, and engagement</p>

<?php if ($myAlerts > 0): ?>
<div class="alert alert-warning" style="margin-bottom:20px">
  <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
  <span>You have <strong><?= $myAlerts ?> open alert<?= $myAlerts > 1 ? 's' : '' ?></strong>. Please check your attendance or engagement status.</span>
</div>
<?php endif; ?>

<div class="stat-cards">
  <div class="card stat-card">
    <div class="stat-icon" style="background:#F0FDF4">
      <svg fill="none" viewBox="0 0 24 24" stroke="#10B981" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
    </div>
    <div><div class="stat-value"><?= $totalPresent ?></div><div class="stat-label">Sessions Attended</div></div>
  </div>
  <div class="card stat-card">
    <div class="stat-icon" style="background:#FEF2F2">
      <svg fill="none" viewBox="0 0 24 24" stroke="#EF4444" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
    </div>
    <div><div class="stat-value"><?= $totalAbsent ?></div><div class="stat-label">Absences</div></div>
  </div>
  <div class="card stat-card">
    <div class="stat-icon" style="background:#EFF6FF">
      <svg fill="none" viewBox="0 0 24 24" stroke="#2563EB" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
    </div>
    <div><div class="stat-value"><?= $totalQuizDone ?></div><div class="stat-label">Quizzes Done</div></div>
  </div>
  <div class="card stat-card">
    <div class="stat-icon" style="background:#FFF7ED">
      <svg fill="none" viewBox="0 0 24 24" stroke="#F59E0B" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/></svg>
    </div>
    <div>
      <div class="stat-value"><?= $avgEngage ?>%</div>
      <div class="stat-label">Avg Engagement</div>
    </div>
  </div>
</div>

<!-- My courses -->
<div style="font-weight:700;font-size:15px;margin-bottom:14px">My Enrolled Courses</div>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px">
<?php foreach ($myCourses as $c):
  $eng = $db->query("SELECT engagement_index FROM engagement_scores WHERE student_id=? AND course_id=?", [$userId, $c['id']])->fetchColumn() ?? 0;
  $present = $db->query(
    "SELECT COUNT(*) FROM attendance_records ar JOIN class_sessions cs ON ar.session_id=cs.id
     WHERE ar.student_id=? AND cs.course_id=? AND ar.status='present'", [$userId, $c['id']]
  )->fetchColumn();
  $total = $db->query("SELECT COUNT(*) FROM class_sessions WHERE course_id=? AND status='ended'", [$c['id']])->fetchColumn();
  $pct = $total > 0 ? round($present / $total * 100) : 0;
  $engColor = $eng >= 70 ? '#10B981' : ($eng >= 40 ? '#F59E0B' : '#EF4444');
?>
<div class="card" style="padding:20px">
  <div style="display:flex;justify-content:space-between;margin-bottom:10px">
    <span class="badge badge-primary"><?= htmlspecialchars($c['course_code']) ?></span>
    <span style="font-size:13px;font-weight:700;color:<?= $engColor ?>"><?= $eng ?>% Engagement</span>
  </div>
  <div style="font-weight:700;font-size:14px;color:#0F172A;margin-bottom:12px"><?= htmlspecialchars($c['course_name']) ?></div>
  <div style="font-size:12px;color:#64748B;margin-bottom:6px">Attendance: <?= $present ?>/<?= $total ?> sessions (<?= $pct ?>%)</div>
  <div style="background:#F1F5F9;border-radius:99px;height:6px;margin-bottom:16px">
    <div style="background:<?= $pct>=80?'#10B981':($pct>=60?'#F59E0B':'#EF4444') ?>;height:6px;border-radius:99px;width:<?= $pct ?>%"></div>
  </div>
  <div style="display:flex;gap:8px">
    <a href="<?= APP_URL ?>/student/attendance.php?course_id=<?= $c['id'] ?>" class="btn btn-outline btn-sm" style="flex:1;justify-content:center">Attendance</a>
    <a href="<?= APP_URL ?>/student/quiz.php?course_id=<?= $c['id'] ?>" class="btn btn-sm" style="flex:1;background:#F1F5F9;color:#374151;justify-content:center">Quiz</a>
  </div>
</div>
<?php endforeach; ?>
</div>

<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>