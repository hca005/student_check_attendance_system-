<?php
// views/teacher/quiz/sessions_form.php
$page_title = $quizId ? 'Edit Quiz' : 'Create Quiz';
$active_nav = 'quizzes';
require_once APP_ROOT . '/views/layouts/header.php';
?>

<div class="admin-page-title">
    <div class="left">
        <h1><?= $quizId ? 'Edit Quiz' : 'Create Quiz' ?></h1>
        <p>Buổi: <?= htmlspecialchars(date('d/m/Y H:i', strtotime($session['session_date'] . ' ' . $session['start_time']))) ?></p>
    </div>
    <div class="right">
        <a href="<?= APP_URL ?>/teacher/quiz/sessions_list.php?session_id=<?= (int)$session['id'] ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back to dashboard
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="session_id" value="<?= (int)$session['id'] ?>">
                    <?php if ($quizId): ?>
                        <input type="hidden" name="id" value="<?= (int)$quizId ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="title" class="form-label">Quiz Title *</label>
                        <input type="text" name="title" id="title" class="form-control"
                               value="<?= htmlspecialchars($quiz['title'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" id="description" class="form-control" rows="3"><?= htmlspecialchars($quiz['description'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="time_limit_minutes" class="form-label">Time Limit (minutes)</label>
                        <input type="number" name="time_limit_minutes" id="time_limit_minutes" class="form-control"
                               value="<?= htmlspecialchars($quiz['time_limit_minutes'] ?? '') ?>" min="1">
                        <small class="text-muted d-block mt-2">Leave blank if no time limit</small>
                    </div>

                    <div class="form-check">
                        <input type="checkbox" name="allow_retake" id="allow_retake" class="form-check-input"
                               <?= ($quiz && $quiz['allow_retake']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="allow_retake">
                            Allow Quiz Retake
                        </label>
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i> <?= $quizId ? 'Update' : 'Create' ?>
                        </button>
                        <a href="<?= APP_URL ?>/teacher/quiz/sessions_list.php?session_id=<?= (int)$session['id'] ?>" class="btn btn-outline">
                            <i class="bi bi-x-lg"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>