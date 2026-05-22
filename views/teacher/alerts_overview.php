<?php require_once APP_ROOT . '/views/layouts/header.php'; ?>
<div class="container mt-4">
    <h3>Alerts</h3>
    <table class="table table-bordered">
        <thead><tr><th>Sinh viên</th><th>Mã SV</th><th>Môn</th><th>Loại</th><th>Nội dung</th><th>Trạng thái</th></tr></thead>
        <tbody>
        <?php if (empty($alerts)): ?>
        <tr><td colspan="6" class="text-center">Không có alert nào.</td></tr>
        <?php else: ?>
        <?php foreach ($alerts as $a): ?>
        <tr>
            <td><?= htmlspecialchars($a['full_name']) ?></td>
            <td><?= htmlspecialchars($a['student_code'] ?? '') ?></td>
            <td><?= htmlspecialchars($a['course_name']) ?></td>
            <td><span class="badge bg-danger"><?= $a['alert_type'] ?></span></td>
            <td><?= htmlspecialchars($a['alert_message']) ?></td>
            <td><?= $a['status'] ?></td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>
