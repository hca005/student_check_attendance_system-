<?php
// views/teacher/quiz/sessions_form.php
$page_title = $quizId ? 'Edit Quiz' : 'Create Quiz';
$active_nav = 'quizzes';
require_once APP_ROOT . '/views/layouts/header.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h2><?php echo $quizId ? 'Sửa Quiz' : 'Tạo Quiz Mới'; ?></h2>
            <p class="text-muted">Buổi: <?php echo date('d/m/Y H:i', strtotime($session['session_date'] . ' ' . $session['start_time'])); ?></p>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="session_id" value="<?php echo $session['id']; ?>">
                        <?php if ($quizId): ?>
                            <input type="hidden" name="id" value="<?php echo $quizId; ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="title" class="form-label">Tiêu đề Quiz *</label>
                            <input type="text" name="title" id="title" class="form-control" 
                                   value="<?php echo htmlspecialchars($quiz['title'] ?? ''); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Mô tả</label>
                            <textarea name="description" id="description" class="form-control" rows="3"><?php echo htmlspecialchars($quiz['description'] ?? ''); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="time_limit_minutes" class="form-label">Giới hạn Thời gian (phút)</label>
                            <input type="number" name="time_limit_minutes" id="time_limit_minutes" class="form-control" 
                                   value="<?php echo htmlspecialchars($quiz['time_limit_minutes'] ?? ''); ?>" min="1">
                            <small class="text-muted">Để trống nếu không giới hạn thời gian</small>
                        </div>

                        <div class="form-check mb-3">
                            <input type="checkbox" name="allow_retake" id="allow_retake" class="form-check-input"
                                   <?php echo ($quiz && $quiz['allow_retake']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="allow_retake">
                                Cho phép Làm lại Quiz
                            </label>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check"></i> <?php echo $quizId ? 'Cập nhật' : 'Tạo'; ?>
                            </button>
                            <a href="<?php echo APP_URL; ?>/teacher/quiz/sessions_list.php?session_id=<?php echo $session['id']; ?>" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Quay lại
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>
