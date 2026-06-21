<?php
// views/teacher/quiz/questions_list.php
$page_title = 'Quiz Questions';
$active_nav = 'quizzes';
require_once APP_ROOT . '/views/layouts/header.php';
?>

<div class="admin-page-title">
    <div class="left">
        <h1>Câu hỏi Quiz</h1>
        <p>Quản lý câu hỏi cho quiz hiện tại.</p>
    </div>
    <div class="right">
        <a href="<?= APP_URL ?>/teacher/quiz/sessions_list.php?session_id=<?= (int)$quiz['session_id'] ?>" class="btn btn-outline-secondary btn-sm">Back to Quiz</a>
        <a href="<?= APP_URL ?>/teacher/quiz/questions_form.php?quiz_id=<?= (int)$quiz['id'] ?>" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg"></i> Thêm Câu hỏi
        </a>
    </div>
</div>

<div class="card">
  <div class="card-header">
    <div>
      <div class="card-title"><?= htmlspecialchars($quiz['title']) ?></div>
      <div class="text-muted" style="font-size:13px;">Buổi: <?= htmlspecialchars(date('d/m/Y H:i', strtotime($quiz['session_date'] . ' ' . $quiz['start_time']))) ?> | <?= htmlspecialchars($quiz['course_code']) ?></div>
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

    <div class="table-wrap">
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
                      <td colspan="5" class="text-center text-muted py-4">Chưa có câu hỏi nào. Nhấn "Thêm Câu hỏi" để bắt đầu.</td>
                  </tr>
              <?php else: ?>
                  <?php foreach ($questions as $q): ?>
                      <tr>
                          <td style="font-weight:600"><?= (int)$q['order_num'] ?></td>
                          <td>
                              <div style="font-weight:500"><?= htmlspecialchars(substr($q['question_text'], 0, 60)) ?></div>
                              <small class="text-muted">
                                  A: <?= htmlspecialchars(substr($q['option_a'], 0, 30)) ?>... |
                                  B: <?= htmlspecialchars(substr($q['option_b'], 0, 30)) ?>...
                              </small>
                          </td>
                          <td>
                              <span class="badge badge-primary"><?= htmlspecialchars($q['correct_option']) ?></span>
                          </td>
                          <td><?= number_format((float)$q['points'], 2) ?></td>
                          <td>
                              <div style="display:flex;gap:6px">
                                  <a href="<?= APP_URL ?>/teacher/quiz/questions_form.php?quiz_id=<?= (int)$quiz['id'] ?>&id=<?= (int)$q['id'] ?>" class="btn btn-sm btn-outline-warning" title="Sửa">
                                      <i class="bi bi-pencil"></i>
                                  </a>
                                  <form method="POST" action="<?= APP_URL ?>/teacher/quiz/delete_question.php" style="display:inline;" onsubmit="return confirm('Xác nhận xóa?')">
                                      <input type="hidden" name="id" value="<?= (int)$q['id'] ?>">
                                      <input type="hidden" name="quiz_id" value="<?= (int)$quiz['id'] ?>">
                                      <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa">
                                          <i class="bi bi-trash"></i>
                                      </button>
                                  </form>
                              </div>
                          </td>
                      </tr>
                  <?php endforeach; ?>
              <?php endif; ?>
          </tbody>
      </table>
    </div>
  </div>
</div>

<div class="mt-3 d-flex flex-wrap gap-2">
    <a href="<?= APP_URL ?>/teacher/quiz/sessions_list.php?session_id=<?= (int)$quiz['session_id'] ?>" class="btn btn-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Quay lại Quiz
    </a>
    <a href="<?= APP_URL ?>/teacher/dashboard.php" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-house"></i> Dashboard
    </a>
</div>

<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>