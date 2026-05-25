<?php require_once APP_ROOT . '/views/layouts/header.php'; ?>
<div class="container mt-4">
    <h3>Class Sessions</h3>
    <table class="table table-bordered">
        <thead><tr><th>Ngày</th><th>Buổi học</th><th>Môn</th><th>Trạng thái</th><th>Attendance</th><th>Quiz</th></tr></thead>
        <tbody>
        <?php foreach ($sessions as $s): ?>
        <tr>
            <td><?= $s['session_date'] ?></td>
            <td><?= htmlspecialchars($s['title'] ?? 'Buổi học') ?></td>
            <td><?= htmlspecialchars($s['course_name']) ?></td>
            <td><?= $s['status'] ?></td>
            <td><a href="<?= APP_URL ?>/teacher/attendance/methods_list.php?session_id=<?= $s['id'] ?>" class="btn btn-sm btn-primary">Attendance</a></td>
            <td><a href="<?= APP_URL ?>/teacher/quiz/sessions_list.php?session_id=<?= $s['id'] ?>" class="btn btn-sm btn-warning">Quiz</a></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>
