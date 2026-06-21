<?php
// views/teacher/quiz/questions_list.php
$page_title = 'Quiz Questions';
$active_nav = 'quizzes';
require_once APP_ROOT . '/views/layouts/header.php';
?>

<div class="container mt-4">
    <div class="admin-page-title">
        <div class="left">
            <h1>Câu hỏi Quiz</h1>
            <p>Quản lý câu hỏi cho quiz hiện tại.</p>
        </div>
        <div class="right">
            <a href="<?php echo APP_URL; ?>/teacher/quiz/questions_form.php?quiz_id=<?php echo $quiz['id']; ?>" class="btn btn-primary btn-sm">
                <i class="bi bi-plus"></i> Thêm Câu hỏi
            </a>
        </div>
    </div>

    <div class="card">
      <div class="card-header">
        <div>
          <div class="card-title"><?php echo htmlspecialchars($quiz['title']); ?></div>
          <div class="text-muted" style="font-size:13px;">Buổi: <?php echo date('d/m/Y H:i', strtotime($quiz['session_date'] . ' ' . $quiz['start_time'])); ?> | <?php echo htmlspecialchars($quiz['course_code']); ?></div>
        </div>
      </div>
      <div class="card-body">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                <button type="button" class="btn-close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                <button type="button" class="btn-close"></button>
            </div>
        <?php endif; ?>

        <div class="table-wrap">
          <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Câu hỏi</th>
                        <th>Đáp án Đúng</th>
                        <th>Điểm</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($questions)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Chưa có câu hỏi nào</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($questions as $q): ?>
                            <tr>
                                <td><?php echo $q['order_num']; ?></td>
                                <td>
                                    <div><?php echo htmlspecialchars(substr($q['question_text'], 0, 60)); ?></div>
                                    <small class="text-muted">
                                        A: <?php echo htmlspecialchars(substr($q['option_a'], 0, 30)); ?>... | 
                                        B: <?php echo htmlspecialchars(substr($q['option_b'], 0, 30)); ?>...
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?php echo $q['correct_option']; ?></span>
                                </td>
                                <td><?php echo number_format($q['points'], 2); ?></td>
                                <td>
                                    <a href="<?php echo APP_URL; ?>/teacher/quiz/questions_form.php?quiz_id=<?php echo $quiz['id']; ?>&id=<?php echo $q['id']; ?>" class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" action="<?php echo APP_URL; ?>/teacher/quiz/delete_question.php" style="display:inline;" onsubmit="return confirm('Xác nhận xóa?')">
                                        <input type="hidden" name="id" value="<?php echo $q['id']; ?>">
                                        <input type="hidden" name="quiz_id" value="<?php echo $quiz['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="mt-3 d-flex flex-wrap gap-2">
        <a href="<?php echo APP_URL; ?>/teacher/quiz/sessions_list.php?session_id=<?php echo $quiz['session_id']; ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại Quiz
        </a>
        <a href="<?php echo APP_URL; ?>/teacher/dashboard.php" class="btn btn-outline-secondary">
            <i class="bi bi-house"></i> Dashboard
        </a>
    </div>
</div>

<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>
