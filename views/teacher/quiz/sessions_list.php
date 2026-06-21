<?php
// views/teacher/quiz/sessions_list.php
$page_title = 'Quiz Sessions';
$active_nav = 'quizzes';
require_once APP_ROOT . '/views/layouts/header.php';

$statusVN    = ['draft' => 'Soạn thảo', 'open' => 'Mở', 'closed' => 'Đóng'];
$statusBadge = ['draft' => 'badge-gray', 'open' => 'badge-success', 'closed' => 'badge-danger'];
?>

<div class="admin-page-title">
    <div class="left">
        <h1>Quiz Sessions</h1>
        <p>Review and manage quizzes for this class session.</p>
    </div>
    <div class="right">
        <a href="<?= APP_URL ?>/teacher/quiz.php" class="btn btn-outline-secondary btn-sm">Back to Quizzes</a>
        <a href="<?= APP_URL ?>/teacher/quiz/sessions_form.php?session_id=<?= (int)$session['id'] ?>" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg"></i> Tạo Quiz
        </a>
    </div>
</div>

<div class="card">
  <div class="card-header">
    <div>
      <div class="card-title">Quiz Sessions</div>
      <div class="text-muted" style="font-size:13px;"><?= htmlspecialchars($session['course_code'] ?? '') ?> • Buổi ngày <?= htmlspecialchars(date('d/m/Y', strtotime($session['session_date']))) ?> — Create, publish, and manage quizzes for this session.</div>
    </div>
  </div>
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
        <div class="alert alert-info">Chưa có quiz nào cho buổi học này. Nhấn "Tạo Quiz" để bắt đầu.</div>
    <?php else: ?>
        <div class="quiz-card-grid">
            <?php foreach ($quizzes as $quiz): ?>
                <div class="card quiz-card" style="padding: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; gap:8px;">
                        <h3 style="margin: 0; font-size: 16px;"><?= htmlspecialchars($quiz['title']) ?></h3>
                        <span class="badge <?= $statusBadge[$quiz['status']] ?? 'badge-gray' ?>">
                            <?= $statusVN[$quiz['status']] ?? htmlspecialchars($quiz['status']) ?>
                        </span>
                    </div>

                    <p style="color: #64748b; font-size: 13px; margin: 0 0 16px; flex-grow: 1;">
                        <?= htmlspecialchars($quiz['description'] ?? 'Không có mô tả') ?>
                    </p>

                    <div style="margin-bottom: 16px; font-size: 13px; color:#334155;">
                        <p style="margin: 0 0 4px;">
                            <strong>Câu hỏi:</strong> <?= (int)$quiz['question_count'] ?> &nbsp;|&nbsp;
                            <strong>Điểm:</strong> <?= number_format((float)$quiz['max_score'], 2) ?>
                        </p>
                        <?php if ($quiz['time_limit_minutes']): ?>
                            <p style="margin: 0;"><strong>Thời gian:</strong> <?= (int)$quiz['time_limit_minutes'] ?> phút</p>
                        <?php endif; ?>
                    </div>

                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                        <a href="<?= APP_URL ?>/teacher/quiz/questions_list.php?quiz_id=<?= (int)$quiz['id'] ?>" class="btn btn-outline btn-sm" style="flex: 1; justify-content: center;">
                            Câu hỏi
                        </a>
                        <a href="<?= APP_URL ?>/teacher/quiz/sessions_form.php?session_id=<?= (int)$session['id'] ?>&id=<?= (int)$quiz['id'] ?>" class="btn btn-outline btn-sm" style="flex: 1; justify-content: center;">
                            Sửa
                        </a>

                        <?php if ($quiz['status'] !== 'open' && $quiz['question_count'] > 0): ?>
                            <form method="POST" action="<?= APP_URL ?>/teacher/quiz/update_status.php" style="display:contents;">
                                <input type="hidden" name="id" value="<?= (int)$quiz['id'] ?>">
                                <input type="hidden" name="session_id" value="<?= (int)$session['id'] ?>">
                                <input type="hidden" name="status" value="open">
                                <button type="submit" class="btn btn-outline-success btn-sm" style="flex:1;justify-content:center;">
                                    Mở
                                </button>
                            </form>
                        <?php elseif ($quiz['status'] === 'open'): ?>
                            <form method="POST" action="<?= APP_URL ?>/teacher/quiz/update_status.php" style="display:contents;">
                                <input type="hidden" name="id" value="<?= (int)$quiz['id'] ?>">
                                <input type="hidden" name="session_id" value="<?= (int)$session['id'] ?>">
                                <input type="hidden" name="status" value="closed">
                                <button type="submit" class="btn btn-outline-danger btn-sm" style="flex:1;justify-content:center;">
                                    Đóng
                                </button>
                            </form>
                        <?php endif; ?>

                        <form method="POST" action="<?= APP_URL ?>/teacher/quiz/delete_session.php" style="display:contents;" onsubmit="return confirm('Xác nhận xóa? Tất cả câu hỏi sẽ bị xóa.')">
                            <input type="hidden" name="id" value="<?= (int)$quiz['id'] ?>">
                            <input type="hidden" name="session_id" value="<?= (int)$session['id'] ?>">
                            <button type="submit" class="btn btn-outline-danger btn-sm" style="flex:1;justify-content:center;">
                                Xóa
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
    <a href="<?= APP_URL ?>/teacher/dashboard.php" class="btn btn-secondary btn-sm">Quay lại Dashboard</a>
</div>

<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>