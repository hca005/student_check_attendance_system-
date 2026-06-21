<?php
// views/student/courses.php
// Dữ liệu từ public/student/courses.php:
//   $courses (array với đầy đủ stats)
require_once APP_ROOT . '/views/layouts/header.php';
?>

<div class="admin-page-title">
  <div class="left">
    <h1>My Courses</h1>
    <p>All courses you're currently enrolled in</p>
  </div>
</div>

<?php if (empty($courses)): ?>
<div class="card empty-state">
  <div class="icon-circle" style="background:#EFF6FF"><svg fill="none" viewBox="0 0 24 24" stroke="#2563EB" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5-10-5z"/><path d="M6 12v5c0 1.66 2.69 3 6 3s6-1.34 6-3v-5"/></svg></div>
  <div class="title">No courses yet</div>
  Contact your Admin to be enrolled.
</div>

<?php else:
  $totalAttended = array_sum(array_column($courses, 'present_count'));
  $totalSessions = array_sum(array_column($courses, 'ended_sessions'));
  $overallPct    = $totalSessions > 0 ? min(100, round($totalAttended / $totalSessions * 100)) : 0;
  $avgEngage     = count($courses) ? round(array_sum(array_column($courses,'engagement')) / count($courses), 1) : 0;
?>

<div class="stat-cards" style="grid-template-columns:repeat(3, minmax(0,1fr))">
  <div class="card stat-card">
    <div class="stat-icon" style="background:#EFF6FF"><svg fill="none" viewBox="0 0 24 24" stroke="#2563EB" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5-10-5z"/><path d="M6 12v5c0 1.66 2.69 3 6 3s6-1.34 6-3v-5"/></svg></div>
    <div><div class="stat-value"><?= count($courses) ?></div><div class="stat-label">Enrolled courses</div></div>
  </div>
  <div class="card stat-card">
    <div class="score-ring" style="--ring-size:48px;--pct:<?= $overallPct ?>;--ring-color:<?= $overallPct>=80?'#10B981':($overallPct>=60?'#F59E0B':'#EF4444') ?>"><span class="score-ring-label" style="font-size:12px"><?= $overallPct ?>%</span></div>
    <div><div class="stat-label" style="margin-top:4px">Avg attendance</div></div>
  </div>
  <div class="card stat-card">
    <div class="score-ring" style="--ring-size:48px;--pct:<?= $avgEngage ?>;--ring-color:<?= $avgEngage>=70?'#10B981':($avgEngage>=40?'#F59E0B':'#EF4444') ?>"><span class="score-ring-label" style="font-size:12px"><?= $avgEngage ?>%</span></div>
    <div><div class="stat-label" style="margin-top:4px">Avg engagement</div></div>
  </div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px">
<?php foreach ($courses as $c):
  $ringColor = $c['engagement'] >= 70 ? '#10B981' : ($c['engagement'] >= 40 ? '#F59E0B' : '#EF4444');
  $attColor  = $c['att_pct']    >= 80 ? '#10B981' : ($c['att_pct']    >= 60 ? '#F59E0B' : '#EF4444');
?>
<div class="card card-interactive" style="padding:20px">
  <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:14px">
    <div>
      <span class="badge badge-primary"><?= htmlspecialchars($c['course_code']) ?></span>
      <div style="font-weight:700;font-size:15px;margin-top:8px;line-height:1.3"><?= htmlspecialchars($c['course_name']) ?></div>
      <div style="font-size:12px;color:var(--text-muted);margin-top:2px">
        <?= htmlspecialchars($c['semester'] ?? '') ?><?= !empty($c['teacher_name']) ? ' · ' . htmlspecialchars($c['teacher_name']) : '' ?>
      </div>
    </div>
    <div class="score-ring" style="--ring-size:56px;--pct:<?= $c['engagement'] ?>;--ring-color:<?= $ringColor ?>">
      <span class="score-ring-label" style="font-size:12px"><?= $c['engagement'] ?>%</span>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:14px">
    <div style="text-align:center;padding:10px 4px;background:var(--bg);border-radius:8px">
      <div style="font-weight:800;color:<?= $attColor ?>"><?= $c['att_pct'] ?>%</div>
      <div style="font-size:10px;color:var(--text-muted)">Attendance</div>
    </div>
    <div style="text-align:center;padding:10px 4px;background:var(--bg);border-radius:8px">
      <div style="font-weight:800;color:var(--primary)"><?= $c['quiz_count'] ?></div>
      <div style="font-size:10px;color:var(--text-muted)">Quizzes done</div>
    </div>
  </div>

  <div class="progress-track" style="width:100%;margin-bottom:16px"><div class="progress-fill" style="width:<?= $c['att_pct'] ?>%;background:<?= $attColor ?>"></div></div>

  <div style="display:flex;gap:8px">
    <a href="<?= APP_URL ?>/student/attendance.php?course_id=<?= $c['id'] ?>" class="btn btn-outline btn-sm" style="flex:1;justify-content:center">Attendance</a>
    <a href="<?= APP_URL ?>/student/quiz.php?course_id=<?= $c['id'] ?>" class="btn btn-outline btn-sm" style="flex:1;justify-content:center">Quiz</a>
  </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>