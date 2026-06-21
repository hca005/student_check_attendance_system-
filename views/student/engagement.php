<?php
// views/student/engagement.php
// Dữ liệu từ public/student/engagement.php:
//   $engagements, $quizHistory, $interactionLogs, $openAlerts
require_once APP_ROOT . '/views/layouts/header.php';
?>

<div class="admin-page-title">
  <div class="left">
    <h1>My Engagement</h1>
    <p>Engagement index and activity history per course</p>
  </div>
</div>

<?php if (!empty($openAlerts)): ?>
<div class="alert alert-warning" style="align-items:flex-start;flex-direction:column">
  <div style="display:flex;align-items:center;gap:8px">
    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
    <strong>You have <?= count($openAlerts) ?> open alert<?= count($openAlerts) > 1 ? 's' : '' ?></strong>
  </div>
  <?php foreach ($openAlerts as $al): ?>
  <div style="font-size:13px;margin-top:6px;margin-left:24px">[<?= htmlspecialchars($al['course_code']) ?>] <?= htmlspecialchars($al['message']) ?></div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if (empty($engagements)): ?>
<div class="card empty-state">
  <div class="icon-circle" style="background:#FFF7ED"><svg fill="none" viewBox="0 0 24 24" stroke="#F59E0B" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg></div>
  No engagement data yet — attend a session or take a quiz to get started.
</div>

<?php else: ?>
<div style="font-weight:700;font-size:14px;margin-bottom:12px">Engagement by course</div>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(290px,1fr));gap:14px;margin-bottom:28px">
  <?php foreach ($engagements as $e):
    $idx   = (float)$e['engagement_index'];
    $color = $idx >= 70 ? '#10B981' : ($idx >= 40 ? '#F59E0B' : '#EF4444');
    $attPct = $e['total_sessions'] > 0 ? min(100, round($e['attended_sessions'] / $e['total_sessions'] * 100)) : 0;
  ?>
  <div class="card" style="padding:18px">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:14px">
      <div>
        <span class="badge badge-primary"><?= htmlspecialchars($e['course_code']) ?></span>
        <div style="font-weight:700;font-size:14px;margin-top:6px"><?= htmlspecialchars($e['course_name']) ?></div>
      </div>
      <div class="score-ring" style="--ring-size:64px;--pct:<?= $idx ?>;--ring-color:<?= $color ?>">
        <span class="score-ring-label" style="font-size:14px"><?= $idx ?>%</span>
      </div>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px">
      <div style="text-align:center;padding:8px 4px;background:var(--bg);border-radius:8px">
        <div style="font-weight:700;color:<?= $attPct>=80?'#10B981':($attPct>=60?'#F59E0B':'#EF4444') ?>"><?= $attPct ?>%</div>
        <div style="font-size:10px;color:var(--text-muted)">Attendance</div>
      </div>
      <div style="text-align:center;padding:8px 4px;background:var(--bg);border-radius:8px">
        <div style="font-weight:700;color:#7C3AED"><?= round($e['total_quiz_score'], 1) ?></div>
        <div style="font-size:10px;color:var(--text-muted)">Quiz pts</div>
      </div>
      <div style="text-align:center;padding:8px 4px;background:var(--bg);border-radius:8px">
        <div style="font-weight:700;color:#0369A1"><?= round($e['total_interaction_points'], 1) ?></div>
        <div style="font-size:10px;color:var(--text-muted)">Interaction</div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if (!empty($quizHistory)): ?>
<div style="font-weight:700;font-size:14px;margin-bottom:12px">Recent quiz submissions</div>
<div class="card table-wrap" style="margin-bottom:28px">
  <table>
    <thead><tr><th>Quiz</th><th>Course</th><th>Score</th><th>Submitted</th></tr></thead>
    <tbody>
    <?php foreach (array_slice($quizHistory, 0, 10) as $q):
      $pct = $q['max_score'] > 0 ? round($q['total_score'] / $q['max_score'] * 100) : 0;
    ?>
    <tr>
      <td><?= htmlspecialchars($q['quiz_title']) ?></td>
      <td><span class="badge badge-primary"><?= htmlspecialchars($q['course_code']) ?></span></td>
      <td><?= $q['total_score'] ?>/<?= $q['max_score'] ?> (<?= $pct ?>%)</td>
      <td><?= date('d/m/Y H:i', strtotime($q['submitted_at'])) ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

<?php if (!empty($interactionLogs)): ?>
<div style="font-weight:700;font-size:14px;margin-bottom:12px">Recent activity</div>
<div class="card table-wrap">
  <table>
    <thead><tr><th>Action</th><th>Description</th><th>Course</th><th>Points</th><th>When</th></tr></thead>
    <tbody>
    <?php
    $actionLabel = ['check_in'=>'Check-in','submit_quiz'=>'Quiz submitted','answer_question'=>'Answered','discussion'=>'Discussion','other'=>'Other'];
    foreach (array_slice($interactionLogs, 0, 15) as $log): ?>
    <tr>
      <td><?= $actionLabel[$log['action_type']] ?? 'Other' ?></td>
      <td><?= htmlspecialchars($log['description'] ?? '—') ?></td>
      <td><?= htmlspecialchars($log['course_name'] ?? '') ?></td>
      <td style="color:<?= $log['points_earned']>0?'#10B981':'#94A3B8' ?>">+<?= $log['points_earned'] ?></td>
      <td><?= date('d/m H:i', strtotime($log['created_at'])) ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>