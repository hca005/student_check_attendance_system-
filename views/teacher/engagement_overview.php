<?php require_once APP_ROOT . '/views/layouts/header.php'; ?>
<div class="container mt-4">
    <h3>Engagement Scores</h3>
    <table class="table table-bordered">
        <thead><tr><th>Sinh viên</th><th>Mã SV</th><th>Môn</th><th>Buổi tham gia</th><th>Quiz Score</th><th>Engagement Index</th></tr></thead>
        <tbody>
        <?php if (empty($scores)): ?>
        <tr><td colspan="6" class="text-center">Chưa có dữ liệu engagement.</td></tr>
        <?php else: ?>
        <?php foreach ($scores as $s): ?>
        <tr>
            <td><?= htmlspecialchars($s['full_name']) ?></td>
            <td><?= htmlspecialchars($s['student_code'] ?? '') ?></td>
            <td><?= htmlspecialchars($s['course_name']) ?></td>
            <td><?= $s['attended_sessions'] ?>/<?= $s['total_sessions'] ?></td>
            <td><?= $s['total_quiz_score'] ?></td>
            <td><strong><?= $s['engagement_index'] ?></strong></td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>
