<?php require_once APP_ROOT . '/views/layouts/header.php'; ?>

<div class="page-title">Câu hỏi - <?= htmlspecialchars($quiz['title']) ?></div>
<p class="page-sub">Buổi: <?= date('d/m/Y', strtotime($quiz['session_date'])) ?> | <?= htmlspecialchars($quiz['course_code']) ?></p>

<?php if (isset($_SESSION['success'])): ?>
<div style="background:#D1FAE5;border:1px solid #10B981;color:#065F46;padding:12px 16px;border-radius:8px;margin-bottom:16px">
    ✓ <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
</div>
<?php endif; ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
    <div style="font-weight:700;font-size:14px"><?= count($questions) ?> câu hỏi</div>
    <a href="<?= APP_URL ?>/teacher/quiz/questions_form.php?quiz_id=<?= $quiz['id'] ?>" class="btn btn-primary btn-sm">+ Thêm Câu hỏi</a>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead><tr><th>#</th><th>Câu hỏi</th><th>Đáp án Đúng</th><th>Điểm</th><th>Hành động</th></tr></thead>
            <tbody>
            <?php if (empty($questions)): ?>
                <tr><td colspan="5" style="text-align:center;color:#94A3B8;padding:32px">Chưa có câu hỏi nào</td></tr>
            <?php else: ?>
            <?php foreach ($questions as $q): ?>
            <tr>
                <td style="color:#94A3B8;font-size:13px;font-weight:600"><?= $q['order_num'] ?></td>
                <td>
                    <div style="font-weight:600;font-size:13px;margin-bottom:4px"><?= htmlspecialchars($q['question_text']) ?></div>
                    <div style="font-size:12px;color:#64748B">
                        A: <?= htmlspecialchars($q['option_a']) ?> &nbsp;|&nbsp;
                        B: <?= htmlspecialchars($q['option_b']) ?>
                        <?php if ($q['option_c']): ?> &nbsp;|&nbsp; C: <?= htmlspecialchars($q['option_c']) ?><?php endif; ?>
                        <?php if ($q['option_d']): ?> &nbsp;|&nbsp; D: <?= htmlspecialchars($q['option_d']) ?><?php endif; ?>
                    </div>
                </td>
                <td><span class="badge badge-success"><?= $q['correct_option'] ?></span></td>
                <td style="font-weight:600"><?= $q['points'] ?></td>
                <td>
                    <div style="display:flex;gap:6px">
                        <a href="<?= APP_URL ?>/teacher/quiz/questions_form.php?quiz_id=<?= $quiz['id'] ?>&id=<?= $q['id'] ?>" class="btn btn-outline btn-sm">Sửa</a>
                        <form method="POST" action="<?= APP_URL ?>/teacher/quiz/delete_question.php" style="margin:0" onsubmit="return confirm('Xóa câu hỏi này?')">
                            <input type="hidden" name="id" value="<?= $q['id'] ?>">
                            <input type="hidden" name="quiz_id" value="<?= $quiz['id'] ?>">
                            <button class="btn btn-sm" style="background:#FEE2E2;color:#991B1B">Xóa</button>
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

<div style="margin-top:16px;display:flex;gap:10px">
    <a href="<?= APP_URL ?>/teacher/quiz/sessions_list.php?session_id=<?= $quiz['session_id'] ?>" class="btn btn-outline btn-sm">← Quay lại Quiz</a>
    <a href="<?= APP_URL ?>/teacher/dashboard.php" class="btn btn-outline btn-sm">Dashboard</a>
</div>

<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>
