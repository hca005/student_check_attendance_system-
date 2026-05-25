<?php require_once APP_ROOT . '/views/layouts/header.php'; ?>
<div class="container mt-4">
    <h3>Quản lý Quiz</h3>
    <table class="table table-bordered">
        <thead><tr><th>Ngày</th><th>Buổi học</th><th>Tên Quiz</th><th>Số câu</th><th>Trạng thái</th><th></th></tr></thead>
        <tbody>
        <?php if (empty($quizzes)): ?>
        <tr><td colspan="6" class="text-center">Chưa có quiz nào. Vào Class Sessions để tạo quiz.</td></tr>
        <?php else: ?>
        <?php foreach ($quizzes as $q): ?>
        <tr>
            <td><?= $q['session_date'] ?></td>
            <td><?= htmlspecialchars($q['session_title'] ?? '') ?></td>
            <td><?= htmlspecialchars($q['title']) ?></td>
            <td><?= $q['question_count'] ?> câu</td>
            <td><span class="badge bg-<?= $q['status']==='open'?'success':($q['status']==='closed'?'secondary':'warning') ?>"><?= $q['status'] ?></span></td>
            <td><a href="<?= APP_URL ?>/teacher/quiz/questions_list.php?quiz_id=<?= $q['id'] ?>" class="btn btn-sm btn-primary">Quản lý</a></td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>
