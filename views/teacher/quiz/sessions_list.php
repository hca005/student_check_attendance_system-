<?php
// views/teacher/quiz/sessions_list.php
require_once APP_ROOT . '/views/layouts/header.php';
?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Quiz - <?php echo htmlspecialchars($session['course_code']); ?></h2>
            <p class="text-muted">Buổi: <?php echo date('d/m/Y H:i', strtotime($session['session_date'] . ' ' . $session['start_time'])); ?></p>
        </div>
        <div class="col-md-4 text-end">
            <a href="<?php echo APP_URL; ?>/teacher/quiz/sessions_form.php?session_id=<?php echo $session['id']; ?>" class="btn btn-primary">
                <i class="bi bi-plus"></i> Tạo Quiz
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <?php if (empty($quizzes)): ?>
            <div class="col-12">
                <div class="alert alert-info">Chưa có quiz nào</div>
            </div>
        <?php else: ?>
            <?php foreach ($quizzes as $quiz): ?>
                <div class="col-md-6 mb-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title"><?php echo htmlspecialchars($quiz['title']); ?></h5>
                                <span class="badge bg-<?php 
                                    echo $quiz['status'] === 'draft' ? 'secondary' :
                                         ($quiz['status'] === 'open' ? 'success' : 'danger');
                                ?>">
                                    <?php 
                                        $statusVN = ['draft' => 'Soạn thảo', 'open' => 'Mở', 'closed' => 'Đóng'];
                                        echo $statusVN[$quiz['status']] ?? $quiz['status'];
                                    ?>
                                </span>
                            </div>

                            <p class="card-text text-muted small">
                                <?php echo htmlspecialchars($quiz['description'] ?? 'Không có mô tả'); ?>
                            </p>

                            <div class="mb-3">
                                <p class="mb-1">
                                    <strong>Câu hỏi:</strong> <?php echo $quiz['question_count']; ?> | 
                                    <strong>Điểm:</strong> <?php echo number_format($quiz['max_score'], 2); ?>
                                </p>
                                <?php if ($quiz['time_limit_minutes']): ?>
                                    <p class="mb-0"><strong>Thời gian:</strong> <?php echo $quiz['time_limit_minutes']; ?> phút</p>
                                <?php endif; ?>
                            </div>

                            <div class="btn-group btn-group-sm w-100" role="group">
                                <a href="<?php echo APP_URL; ?>/teacher/quiz/questions_list.php?quiz_id=<?php echo $quiz['id']; ?>" class="btn btn-outline-primary">
                                    <i class="bi bi-list-check"></i> Câu hỏi
                                </a>
                                <a href="<?php echo APP_URL; ?>/teacher/quiz/sessions_form.php?session_id=<?php echo $session['id']; ?>&id=<?php echo $quiz['id']; ?>" class="btn btn-outline-warning">
                                    <i class="bi bi-pencil"></i> Sửa
                                </a>

                                <?php if ($quiz['status'] !== 'open' && $quiz['question_count'] > 0): ?>
                                    <form method="POST" action="<?php echo APP_URL; ?>/teacher/quiz/update_status.php" style="display:inline; width:auto;">
                                        <input type="hidden" name="id" value="<?php echo $quiz['id']; ?>">
                                        <input type="hidden" name="session_id" value="<?php echo $session['id']; ?>">
                                        <input type="hidden" name="status" value="open">
                                        <button type="submit" class="btn btn-outline-success btn-sm">
                                            <i class="bi bi-play"></i> Mở
                                        </button>
                                    </form>
                                <?php elseif ($quiz['status'] === 'open'): ?>
                                    <form method="POST" action="<?php echo APP_URL; ?>/teacher/quiz/update_status.php" style="display:inline; width:auto;">
                                        <input type="hidden" name="id" value="<?php echo $quiz['id']; ?>">
                                        <input type="hidden" name="session_id" value="<?php echo $session['id']; ?>">
                                        <input type="hidden" name="status" value="closed">
                                        <button type="submit" class="btn btn-outline-danger btn-sm">
                                            <i class="bi bi-stop"></i> Đóng
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <form method="POST" action="<?php echo APP_URL; ?>/teacher/quiz/delete_session.php" style="display:inline; width:auto;" onsubmit="return confirm('Xác nhận xóa? Tất cả câu hỏi sẽ bị xóa.')">
                                    <input type="hidden" name="id" value="<?php echo $quiz['id']; ?>">
                                    <input type="hidden" name="session_id" value="<?php echo $session['id']; ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                        <i class="bi bi-trash"></i> Xóa
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="mt-3">
        <a href="<?php echo APP_URL; ?>/teacher/dashboard.php" class="btn btn-secondary">Quay lại</a>
    </div>
</div>

<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>
