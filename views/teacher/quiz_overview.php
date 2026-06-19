<?php
$page_title = 'Quizzes';
$active_nav = 'quizzes';
require_once APP_ROOT . '/views/layouts/header.php';
?>

<div class="admin-page-title">
  <div class="left">
    <h1>Quiz Management</h1>
    <p>Manage quiz sessions and track student submissions.</p>
  </div>
</div>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Date</th>
          <th>Course</th>
          <th>Session</th>
          <th>Quiz Title</th>
          <th>Submissions</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php if (empty($quizzes)): ?>
        <tr><td colspan="7" style="text-align:center;color:#64748b;padding:24px">No quizzes found. Go to Class Sessions to create a quiz.</td></tr>
      <?php else: ?>
        <?php foreach ($quizzes as $q): ?>
        <tr>
          <td><?= htmlspecialchars($q['session_date']) ?></td>
          <td><?= htmlspecialchars($q['course_code'] ?? '') ?> — <?= htmlspecialchars($q['course_name']) ?></td>
          <td><?= htmlspecialchars($q['session_title'] ?? 'N/A') ?></td>
          <td><?= htmlspecialchars($q['title']) ?></td>
          <td><?= (int)$q['submission_count'] ?> bài nộp</td>
          <td>
            <?php
              $statusColor = match($q['status']) {
                'open'   => '#dcfce7;color:#166534',
                'closed' => '#e2e8f0;color:#334155',
                default  => '#fef3c7;color:#92400e',
              };
            ?>
            <span class="badge" style="background:<?= $statusColor ?>"><?= ucfirst($q['status']) ?></span>
          </td>
          <td>
            <div class="action-row">
              <a href="<?= APP_URL ?>/teacher/quiz/sessions_list.php?session_id=<?= (int)$q['session_id'] ?>" class="btn btn-outline btn-sm">Manage</a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>
