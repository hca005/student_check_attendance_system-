<?php
// views/teacher/quiz/questions_list.php
$page_title = 'Quiz Questions';
$active_nav = 'quizzes';
require_once APP_ROOT . '/views/layouts/header.php';
?>
 
<div class="admin-page-title">
    <div class="left">
        <h1>Quiz Questions</h1>
        <p><?= htmlspecialchars($quiz['title']) ?> &middot; <?= htmlspecialchars(date('d/m/Y H:i', strtotime($quiz['session_date'] . ' ' . $quiz['start_time']))) ?> &middot; <?= htmlspecialchars($quiz['course_code']) ?></p>
    </div>
    <div class="right">
        <a href="<?= APP_URL ?>/teacher/quiz/sessions_list.php?session_id=<?= (int)$quiz['session_id'] ?>" class="btn btn-outline-secondary btn-sm">Back to Quiz</a>
        <a href="<?= APP_URL ?>/teacher/quiz/questions_form.php?quiz_id=<?= (int)$quiz['id'] ?>" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg"></i> Add Question
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
 
    <?php if (empty($questions)): ?>
        <div class="alert alert-info">No questions yet. Click "Add Question" to get started.</div>
    <?php else: ?>
        <div class="question-list">
            <?php foreach ($questions as $q):
                $options = [
                    'A' => $q['option_a'] ?? null,
                    'B' => $q['option_b'] ?? null,
                    'C' => $q['option_c'] ?? null,
                    'D' => $q['option_d'] ?? null,
                ];
            ?>
            <div class="question-card">
                <div class="question-card-head">
                    <span class="question-number">Q<?= (int)$q['order_num'] ?></span>
                    <p class="question-text"><?= htmlspecialchars($q['question_text']) ?></p>
                    <span class="badge badge-gray question-points"><?= number_format((float)$q['points'], 2) ?> pts</span>
                </div>
 
                <div class="question-options">
                    <?php foreach ($options as $letter => $text): ?>
                        <?php if ($text === null || $text === ''): continue; endif; ?>
                        <div class="question-option <?= $q['correct_option'] === $letter ? 'is-correct' : '' ?>">
                            <span class="option-letter"><?= $letter ?></span>
                            <span class="option-text"><?= htmlspecialchars($text) ?></span>
                            <?php if ($q['correct_option'] === $letter): ?>
                                <i class="bi bi-check-circle-fill option-check"></i>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
 
                <div class="question-card-actions">
                    <a href="<?= APP_URL ?>/teacher/quiz/questions_form.php?quiz_id=<?= (int)$quiz['id'] ?>&id=<?= (int)$q['id'] ?>" class="btn btn-sm btn-outline-warning">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <form method="POST" action="<?= APP_URL ?>/teacher/quiz/delete_question.php" style="display:contents;" onsubmit="return confirm('Delete this question?')">
                        <input type="hidden" name="id" value="<?= (int)$q['id'] ?>">
                        <input type="hidden" name="quiz_id" value="<?= (int)$quiz['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-trash"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
  </div>
</div>
 
<div class="mt-3 d-flex flex-wrap gap-2">
    <a href="<?= APP_URL ?>/teacher/quiz/sessions_list.php?session_id=<?= (int)$quiz['session_id'] ?>" class="btn btn-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Back to Quiz
    </a>
    <a href="<?= APP_URL ?>/teacher/dashboard.php" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-house"></i> Dashboard
    </a>
</div>
 
<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>
 