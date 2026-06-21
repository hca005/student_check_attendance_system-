<?php
// views/student/quiz.php
// Dữ liệu từ public/student/quiz.php:
//   $courses, $courseId, $quizzes
require_once APP_ROOT . '/views/layouts/header.php';

$msgMap = [
  'already_submitted' => 'You have already submitted this quiz.',
  'quiz_closed'       => 'This quiz is closed or no longer exists.',
];
$flashText = $msgMap[$_GET['msg'] ?? ''] ?? null;
?>

<div class="admin-page-title">
  <div class="left">
    <h1>My Quizzes</h1>
    <p>Quizzes available per course</p>
  </div>
</div>

<?php if ($flashText): ?>
<div class="alert alert-warning"><?= htmlspecialchars($flashText) ?></div>
<?php endif; ?>

<?php if (empty($courses)): ?>
<div class="card empty-state">
  <div class="icon-circle" style="background:#EFF6FF"><svg fill="none" viewBox="0 0 24 24" stroke="#2563EB" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5-10-5z"/><path d="M6 12v5c0 1.66 2.69 3 6 3s6-1.34 6-3v-5"/></svg></div>
  You're not enrolled in any course yet.
</div>

<?php else: ?>

<div class="course-tabs">
  <?php foreach ($courses as $c): ?>
  <a href="<?= APP_URL ?>/student/quiz.php?course_id=<?= $c['id'] ?>"
     class="course-tab <?= (int)$c['id'] === $courseId ? 'active' : '' ?>">
    <?= htmlspecialchars($c['course_code']) ?>
  </a>
  <?php endforeach; ?>
</div>

<?php if (empty($quizzes)): ?>
<div class="card empty-state">
  <div class="icon-circle" style="background:#F0FDF4"><svg fill="none" viewBox="0 0 24 24" stroke="#10B981" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg></div>
  No quizzes have been published for this course yet.
</div>

<?php else: ?>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px">
  <?php foreach ($quizzes as $q):
    $submitted  = !empty($q['submission_id']);
    $canTake    = $q['status'] === 'open' && (!$submitted || $q['allow_retake']);
    $myScore    = (float)($q['my_score']  ?? 0);
    $maxScore   = (float)($q['max_score'] ?? 0);
    $scorePct   = $maxScore > 0 ? round($myScore / $maxScore * 100) : 0;
    $ringColor  = $scorePct >= 70 ? '#10B981' : ($scorePct >= 50 ? '#F59E0B' : '#EF4444');
    $statusBadge = match($q['status']) {
      'open'   => ['badge-success', 'Open'],
      'closed' => ['badge-gray',    'Closed'],
      default  => ['badge-warning', 'Draft'],
    };
  ?>
  <div class="card card-interactive" style="padding:20px;display:flex;flex-direction:column">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
      <span style="font-size:11px;color:var(--text-muted)"><?= htmlspecialchars($q['session_date']) ?></span>
      <span class="badge <?= $statusBadge[0] ?>"><?= $statusBadge[1] ?></span>
    </div>

    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;margin-bottom:12px">
      <div style="flex:1">
        <div style="font-weight:700;font-size:15px;margin-bottom:4px"><?= htmlspecialchars($q['title']) ?></div>
        <div style="font-size:12px;color:var(--text-muted)">
          <?= (int)$q['question_count'] ?> questions
          <?= $q['time_limit_minutes'] ? ' · ' . $q['time_limit_minutes'] . ' min' : ' · No time limit' ?>
          <?= $q['allow_retake'] ? ' · Retake allowed' : '' ?>
        </div>
      </div>
      <?php if ($submitted): ?>
      <div class="score-ring" style="--ring-size:52px;--pct:<?= $scorePct ?>;--ring-color:<?= $ringColor ?>">
        <span class="score-ring-label" style="font-size:12px"><?= $scorePct ?>%</span>
      </div>
      <?php endif; ?>
    </div>

    <div style="flex:1"></div>

    <?php if ($canTake): ?>
    <a href="<?= APP_URL ?>/student/quiz.php?action=take&quiz_id=<?= $q['id'] ?>" class="btn btn-primary" style="justify-content:center">
      <?= $submitted ? 'Retake quiz' : 'Start quiz' ?>
    </a>
    <?php elseif ($submitted): ?>
    <div class="btn btn-sm" style="justify-content:center;background:#ECFDF5;color:#059669;cursor:default">
      <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
      Submitted
    </div>
    <?php else: ?>
    <div class="btn btn-sm" style="justify-content:center;background:#F1F5F9;color:#94A3B8;cursor:default">
      <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
      Not open yet
    </div>
    <?php endif; ?>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
<?php endif; ?>

<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>