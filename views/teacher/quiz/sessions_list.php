<?php
// views/teacher/quiz/sessions_list.php
$page_title = 'Quiz Sessions';
$active_nav = 'quizzes';
require_once APP_ROOT . '/views/layouts/header.php';

$statusVN    = ['draft' => 'Draft', 'open' => 'Open', 'closed' => 'Closed'];
$statusBadge = ['draft' => 'badge-gray', 'open' => 'badge-success', 'closed' => 'badge-danger'];
?>

<div class="admin-page-title">
    <div class="left">
        <h1>Quiz Sessions</h1>
        <p><?= htmlspecialchars($session['course_code'] ?? '') ?> &middot; <?= htmlspecialchars(date('d/m/Y', strtotime($session['session_date']))) ?> — Create, publish, and manage quizzes for this session.</p>
    </div>
    <div class="right">
        <a href="<?= APP_URL ?>/teacher/quiz.php" class="btn btn-outline-secondary btn-sm">Back to Quizzes</a>
        <a href="<?= APP_URL ?>/teacher/quiz/sessions_form.php?session_id=<?= (int)$session['id'] ?>" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg"></i> New Quiz
        </a>
    </div>
</div>

<div class="card">
  <div class="card-body">
    <?php if (isset($_SESSION['success'])): ?>
      <div class="alert alert-success alert-dismissible">
          <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
          <button type="button" class="btn-close"></button>
      </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
      <div class="alert alert-danger alert-dismissible">
          <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
          <button type="button" class="btn-close"></button>
      </div>
    <?php endif; ?>

    <?php if (empty($quizzes)): ?>
        <div class="alert alert-info">No quizzes yet for this session. Click "New Quiz" to get started.</div>
    <?php else: ?>
        <div class="quiz-card-grid">
            <?php foreach ($quizzes as $quiz): ?>
                <div class="card quiz-card">
                    <div class="quiz-card-head">
                        <h3 class="quiz-card-title"><?= htmlspecialchars($quiz['title']) ?></h3>
                        <span class="badge <?= $statusBadge[$quiz['status']] ?? 'badge-gray' ?>">
                            <?= $statusVN[$quiz['status']] ?? htmlspecialchars($quiz['status']) ?>
                        </span>
                    </div>

                    <p class="quiz-card-desc">
                        <?= htmlspecialchars($quiz['description'] ?? 'No description') ?>
                    </p>

                    <div class="quiz-card-meta">
                        <span><strong><?= (int)$quiz['question_count'] ?></strong> questions</span>
                        <span class="quiz-card-meta-sep">&middot;</span>
                        <span><strong><?= number_format((float)$quiz['max_score'], 2) ?></strong> pts</span>
                        <?php if ($quiz['time_limit_minutes']): ?>
                            <span class="quiz-card-meta-sep">&middot;</span>
                            <span><strong><?= (int)$quiz['time_limit_minutes'] ?></strong> min</span>
                        <?php endif; ?>
                    </div>

                    <div class="quiz-card-actions">
                        <a href="<?= APP_URL ?>/teacher/quiz/questions_list.php?quiz_id=<?= (int)$quiz['id'] ?>" class="btn btn-outline btn-sm">
                            Questions
                        </a>
                        <a href="<?= APP_URL ?>/teacher/quiz/sessions_form.php?session_id=<?= (int)$session['id'] ?>&id=<?= (int)$quiz['id'] ?>" class="btn btn-outline btn-sm">
                            Edit
                        </a>

                        <?php if ($quiz['status'] !== 'open' && $quiz['question_count'] > 0): ?>
                            <form method="POST" action="<?= APP_URL ?>/teacher/quiz/update_status.php" style="display:contents;">
                                <input type="hidden" name="id" value="<?= (int)$quiz['id'] ?>">
                                <input type="hidden" name="session_id" value="<?= (int)$session['id'] ?>">
                                <input type="hidden" name="status" value="open">
                                <button type="submit" class="btn btn-outline-success btn-sm">
                                    Open
                                </button>
                            </form>
                        <?php elseif ($quiz['status'] === 'open'): ?>
                            <form method="POST" action="<?= APP_URL ?>/teacher/quiz/update_status.php" style="display:contents;">
                                <input type="hidden" name="id" value="<?= (int)$quiz['id'] ?>">
                                <input type="hidden" name="session_id" value="<?= (int)$session['id'] ?>">
                                <input type="hidden" name="status" value="closed">
                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                    Close
                                </button>
                            </form>
                        <?php endif; ?>

                        <form method="POST" action="<?= APP_URL ?>/teacher/quiz/delete_session.php" style="display:contents;" onsubmit="return confirm('Delete this quiz? All its questions will be removed.')">
                            <input type="hidden" name="id" value="<?= (int)$quiz['id'] ?>">
                            <input type="hidden" name="session_id" value="<?= (int)$session['id'] ?>">
                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
  </div>
</div>

<div class="mt-3">
    <a href="<?= APP_URL ?>/teacher/dashboard.php" class="btn btn-secondary btn-sm">Back to Dashboard</a>
</div>

<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>