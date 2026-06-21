<?php
// views/teacher/quiz/questions_form.php
$page_title = $questionId ? 'Edit Quiz Question' : 'Add Quiz Question';
$active_nav = 'quizzes';
require_once APP_ROOT . '/views/layouts/header.php';
?>

<div class="admin-page-title">
    <div class="left">
        <h1><?= $questionId ? 'Sửa Câu hỏi' : 'Thêm Câu hỏi Mới' ?></h1>
        <p>Quiz: <?= htmlspecialchars($quiz['title']) ?></p>
    </div>
    <div class="right">
        <a href="<?= APP_URL ?>/teacher/quiz/questions_list.php?quiz_id=<?= (int)$quiz['id'] ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Quay lại
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
                    <input type="hidden" name="quiz_id" value="<?= (int)$quiz['id'] ?>">
                    <?php if ($questionId): ?>
                        <input type="hidden" name="id" value="<?= (int)$questionId ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="question_text" class="form-label">Nội dung Câu hỏi *</label>
                        <textarea name="question_text" id="question_text" class="form-control" rows="3" required><?= htmlspecialchars($question['question_text'] ?? '') ?></textarea>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="option_a" class="form-label">Đáp án A *</label>
                            <input type="text" name="option_a" id="option_a" class="form-control"
                                   value="<?= htmlspecialchars($question['option_a'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="option_b" class="form-label">Đáp án B *</label>
                            <input type="text" name="option_b" id="option_b" class="form-control"
                                   value="<?= htmlspecialchars($question['option_b'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="option_c" class="form-label">Đáp án C</label>
                            <input type="text" name="option_c" id="option_c" class="form-control"
                                   value="<?= htmlspecialchars($question['option_c'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="option_d" class="form-label">Đáp án D</label>
                            <input type="text" name="option_d" id="option_d" class="form-control"
                                   value="<?= htmlspecialchars($question['option_d'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="correct_option" class="form-label">Đáp án Đúng *</label>
                            <select name="correct_option" id="correct_option" class="form-select" required>
                                <option value="A" <?= ($question && $question['correct_option'] === 'A') ? 'selected' : '' ?>>A</option>
                                <option value="B" <?= ($question && $question['correct_option'] === 'B') ? 'selected' : '' ?>>B</option>
                                <option value="C" <?= ($question && $question['correct_option'] === 'C') ? 'selected' : '' ?>>C</option>
                                <option value="D" <?= ($question && $question['correct_option'] === 'D') ? 'selected' : '' ?>>D</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="points" class="form-label">Điểm *</label>
                            <input type="number" name="points" id="points" class="form-control"
                                   value="<?= htmlspecialchars($question['points'] ?? '1.00') ?>"
                                   step="0.01" min="0.01" required>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i> <?= $questionId ? 'Cập nhật' : 'Thêm' ?>
                        </button>
                        <a href="<?= APP_URL ?>/teacher/quiz/questions_list.php?quiz_id=<?= (int)$quiz['id'] ?>" class="btn btn-outline">
                            <i class="bi bi-x-lg"></i> Hủy
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>