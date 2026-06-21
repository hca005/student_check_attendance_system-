<?php
// views/teacher/quiz/sessions_list.php
$page_title = 'Quiz Sessions';
$active_nav = 'quizzes';
require_once APP_ROOT . '/views/layouts/header.php';
?>

<div class="container mt-4">
    <div class="admin-page-title">
        <div class="left">
            <h1>Quiz Sessions</h1>
            <p>Review and manage quizzes for this class session.</p>
        </div>
        <div class="right">
            <a href="<?php echo APP_URL; ?>/teacher/quiz/sessions_form.php?session_id=<?php echo $session['id']; ?>" class="btn btn-primary btn-sm">
                <i class="bi bi-plus"></i> Tạo Quiz
            </a>
        </div>
    </div>

    <div class="card">
      <div class="card-header">
        <div>
          <div class="card-title">Quiz Sessions</div>
          <div class="text-muted" style="font-size:13px;">Create, publish, and manage quizzes for this session.</div>
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

      <div class="row">
        <?php if (empty($quizzes)): ?>
            <div style="grid-column: 1 / -1;">
                <div class="alert alert-info">Chưa có quiz nào</div>
            </div>
        <?php else: ?>
            <?php foreach ($quizzes as $quiz): ?>
                <div>
                    <div class="card" style="height: 100%; padding: 16px; display: flex; flex-direction: column;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                            <h3 style="margin: 0; font-size: 16px;"><?php echo htmlspecialchars($quiz['title']); ?></h3>
                            <span class="badge badge-<?php 
                                echo $quiz['status'] === 'draft' ? 'gray' :
                                     ($quiz['status'] === 'open' ? 'success' : 'danger');
                            ?>">
                                <?php 
                                    $statusVN = ['draft' => 'Soạn thảo', 'open' => 'Mở', 'closed' => 'Đóng'];
                                    echo $statusVN[$quiz['status']] ?? $quiz['status'];
                                ?>
                            </span>
                        </div>

                        <p style="color: #64748b; font-size: 13px; margin: 0 0 16px; flex-grow: 1;">
                            <?php echo htmlspecialchars($quiz['description'] ?? 'Không có mô tả'); ?>
                        </p>

                        <div style="margin-bottom: 16px; font-size: 13px;">
                            <p style="margin: 0 0 4px;">
                                <strong>Câu hỏi:</strong> <?php echo $quiz['question_count']; ?> &nbsp;|&nbsp; 
                                <strong>Điểm:</strong> <?php echo number_format($quiz['max_score'], 2); ?>
                            </p>
                            <?php if ($quiz['time_limit_minutes']): ?>
                                <p style="margin: 0;"><strong>Thời gian:</strong> <?php echo $quiz['time_limit_minutes']; ?> phút</p>
                            <?php endif; ?>
                        </div>

                        <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                            <a href="<?php echo APP_URL; ?>/teacher/quiz/questions_list.php?quiz_id=<?php echo $quiz['id']; ?>" class="btn btn-outline btn-sm" style="flex: 1; justify-content: center;">
                                Câu hỏi
                            </a>
                            <a href="<?php echo APP_URL; ?>/teacher/quiz/sessions_form.php?session_id=<?php echo $session['id']; ?>&id=<?php echo $quiz['id']; ?>" class="btn btn-outline btn-sm" style="flex: 1; justify-content: center;">
                                Sửa
                            </a>

                            <?php if ($quiz['status'] !== 'open' && $quiz['question_count'] > 0): ?>
                                <form method="POST" action="<?php echo APP_URL; ?>/teacher/quiz/update_status.php" style="display:inline; flex: 1;">
                                    <input type="hidden" name="id" value="<?php echo $quiz['id']; ?>">
                                    <input type="hidden" name="session_id" value="<?php echo $session['id']; ?>">
                                    <input type="hidden" name="status" value="open">
                                    <button type="submit" class="btn btn-outline btn-sm" style="width: 100%; border-color: #10b981; color: #10b981;">
                                        Mở
                                    </button>
                                </form>
                            <?php elseif ($quiz['status'] === 'open'): ?>
                                <form method="POST" action="<?php echo APP_URL; ?>/teacher/quiz/update_status.php" style="display:inline; flex: 1;">
                                    <input type="hidden" name="id" value="<?php echo $quiz['id']; ?>">
                                    <input type="hidden" name="session_id" value="<?php echo $session['id']; ?>">
                                    <input type="hidden" name="status" value="closed">
                                    <button type="submit" class="btn btn-outline btn-sm" style="width: 100%; border-color: #ef4444; color: #ef4444;">
                                        Đóng
                                    </button>
                                </form>
                            <?php endif; ?>

                            <form method="POST" action="<?php echo APP_URL; ?>/teacher/quiz/delete_session.php" style="display:inline; flex: 1;" onsubmit="return confirm('Xác nhận xóa? Tất cả câu hỏi sẽ bị xóa.')">
                                <input type="hidden" name="id" value="<?php echo $quiz['id']; ?>">
                                <input type="hidden" name="session_id" value="<?php echo $session['id']; ?>">
                                <button type="submit" class="btn btn-outline btn-sm" style="width: 100%; border-color: #ef4444; color: #ef4444;">
                                    Xóa
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

    <div class="mt-3">
        <a href="<?php echo APP_URL; ?>/teacher/dashboard.php" class="btn btn-secondary">Quay lại</a>
    </div>
</div>

<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>
