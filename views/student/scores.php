<?php
// views/student/scores.php
// Trang tổng hợp điểm quiz và engagement của student
require_once APP_ROOT . '/views/layouts/header.php';
?>

<div class="admin-page-title">
  <div class="left">
    <h1>My Scores</h1>
    <p>Quiz results and engagement summary across courses</p>
  </div>
</div>

<?php if (!empty($engagements)):
  $totalQuizScore = array_sum(array_column($quizHistory, 'total_score'));
  $totalQuizMax   = array_sum(array_column($quizHistory, 'max_score'));
  $overallQuizPct = $totalQuizMax > 0 ? round($totalQuizScore / $totalQuizMax * 100) : 0;
  $avgEngagement  = count($engagements) ? round(array_sum(array_column($engagements, 'engagement_index')) / count($engagements), 1) : 0;
?>
<div class="stat-cards">
  <div class="card stat-card">
    <div class="stat-icon" style="background:#EFF6FF"><svg fill="none" viewBox="0 0 24 24" stroke="#2563EB" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg></div>
    <div><div class="stat-value"><?= count($quizHistory) ?></div><div class="stat-label">Quizzes submitted</div></div>
  </div>
  <div class="card stat-card">
    <div class="score-ring" style="--ring-size:48px;--pct:<?= $overallQuizPct ?>;--ring-color:<?= $overallQuizPct>=70?'#10B981':($overallQuizPct>=50?'#F59E0B':'#EF4444') ?>"><span class="score-ring-label" style="font-size:12px"><?= $overallQuizPct ?>%</span></div>
    <div><div class="stat-label" style="margin-top:4px">Avg quiz score</div></div>
  </div>
  <div class="card stat-card">
    <div class="score-ring" style="--ring-size:48px;--pct:<?= $avgEngagement ?>;--ring-color:<?= $avgEngagement>=70?'#10B981':($avgEngagement>=40?'#F59E0B':'#EF4444') ?>"><span class="score-ring-label" style="font-size:12px"><?= $avgEngagement ?>%</span></div>
    <div><div class="stat-label" style="margin-top:4px">Avg engagement</div></div>
  </div>
  <div class="card stat-card">
    <div class="stat-icon" style="background:#FAF5FF"><svg fill="none" viewBox="0 0 24 24" stroke="#7C3AED" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5-10-5z"/><path d="M6 12v5c0 1.66 2.69 3 6 3s6-1.34 6-3v-5"/></svg></div>
    <div><div class="stat-value"><?= count($engagements) ?></div><div class="stat-label">Courses</div></div>
  </div>
</div>

<div style="font-weight:700;font-size:14px;margin-bottom:12px">Engagement by course</div>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px;margin-bottom:28px">
  <?php foreach ($engagements as $e):
    $idx   = (float)$e['engagement_index'];
    $color = $idx >= 70 ? '#10B981' : ($idx >= 40 ? '#F59E0B' : '#EF4444');
  ?>
  <div class="card" style="padding:18px;display:flex;align-items:center;gap:14px">
    <div class="score-ring" style="--ring-size:58px;--pct:<?= $idx ?>;--ring-color:<?= $color ?>">
      <span class="score-ring-label" style="font-size:13px"><?= $idx ?>%</span>
    </div>
    <div>
      <span class="badge badge-primary"><?= htmlspecialchars($e['course_code']) ?></span>
      <div style="font-size:13px;font-weight:600;margin-top:4px"><?= htmlspecialchars($e['course_name']) ?></div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<div style="font-weight:700;font-size:14px;margin-bottom:12px">Quiz submission history</div>
<?php if (empty($quizHistory)): ?>
<div class="card empty-state">
  <div class="icon-circle" style="background:#EFF6FF"><svg fill="none" viewBox="0 0 24 24" stroke="#2563EB" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg></div>
  You haven't submitted any quiz yet.
</div>
<?php else: ?>
<div class="card table-wrap">
  <table>
    <thead><tr><th>#</th><th>Quiz</th><th>Course</th><th>Score</th><th>Result</th><th>Submitted</th></tr></thead>
    <tbody>
    <?php foreach ($quizHistory as $i => $q):
      $pct   = $q['max_score'] > 0 ? round($q['total_score'] / $q['max_score'] * 100) : 0;
      $badge = $pct >= 70 ? ['badge-success','Good'] : ($pct >= 50 ? ['badge-warning','Pass'] : ['badge-danger','Below pass']);
    ?>
    <tr>
      <td><?= $i + 1 ?></td>
      <td><?= htmlspecialchars($q['quiz_title']) ?></td>
      <td><span class="badge badge-primary"><?= htmlspecialchars($q['course_code']) ?></span></td>
      <td><strong><?= $q['total_score'] ?>/<?= $q['max_score'] ?></strong></td>
      <td><span class="badge <?= $badge[0] ?>"><?= $pct ?>% — <?= $badge[1] ?></span></td>
      <td><?= date('d/m/Y H:i', strtotime($q['submitted_at'])) ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>