<?php require_once APP_ROOT . '/views/layouts/header.php'; ?>

<div style="display:flex;align-items:center;gap:12px;margin-bottom:4px">
    <a href="<?= APP_URL ?>/teacher/alerts.php" class="btn btn-outline btn-sm">← Quay lại</a>
    <div class="page-title" style="margin:0"><?= htmlspecialchars($student['full_name']) ?></div>
</div>
<p class="page-sub">Mã SV: <?= htmlspecialchars($student['student_code'] ?? '—') ?> · <?= htmlspecialchars($course['course_name']) ?></p>

<?php if (isset($_SESSION['success'])): ?>
<div style="background:#D1FAE5;border:1px solid #10B981;color:#065F46;padding:12px 16px;border-radius:8px;margin-bottom:16px">
    ✓ <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
</div>
<?php endif; ?>

<div class="card">
    <div style="padding:16px 20px 12px;font-weight:700;font-size:14px;border-bottom:1px solid #F1F5F9">⚠️ Danh sách Alert</div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Loại</th><th>Nội dung</th><th>Ngày tạo</th><th>Trạng thái</th><th>Hành động</th></tr></thead>
            <tbody>
            <?php foreach ($alerts as $a): ?>
            <tr>
                <td>
                    <?php
                    $typeMap = ['high_absence'=>['Vắng nhiều','#FEE2E2','#991B1B'],'low_engagement'=>['Tương tác thấp','#FEF3C7','#92400E'],'missed_quiz'=>['Bỏ lỡ quiz','#EFF6FF','#1D4ED8']];
                    $t = $typeMap[$a['alert_type']] ?? [$a['alert_type'],'#F1F5F9','#374151'];
                    ?>
                    <span class="badge" style="background:<?= $t[1] ?>;color:<?= $t[2] ?>"><?= $t[0] ?></span>
                </td>
                <td style="font-size:13px"><?= htmlspecialchars($a['alert_message']) ?></td>
                <td style="font-size:13px;color:#64748B"><?= date('d/m/Y H:i', strtotime($a['created_at'])) ?></td>
                <td>
                    <?php
                    $sc = ['open'=>['Chưa xử lý','#FEE2E2','#991B1B'],'resolved'=>['Đã xử lý','#D1FAE5','#065F46'],'ignored'=>['Bỏ qua','#F1F5F9','#64748B']];
                    $st = $sc[$a['status']] ?? [$a['status'],'#F1F5F9','#374151'];
                    ?>
                    <span class="badge" style="background:<?= $st[1] ?>;color:<?= $st[2] ?>"><?= $st[0] ?></span>
                </td>
                <td>
                    <?php if ($a['status'] === 'open'): ?>
                    <div style="display:flex;gap:6px">
                        <form method="POST" action="<?= APP_URL ?>/teacher/alert_update.php" style="margin:0">
                            <input type="hidden" name="id" value="<?= $a['id'] ?>">
                            <input type="hidden" name="status" value="resolved">
                            <input type="hidden" name="student_id" value="<?= $student['id'] ?>">
                            <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                            <button class="btn btn-sm" style="background:#D1FAE5;color:#065F46;border:none">✓ Đã xử lý</button>
                        </form>
                        <form method="POST" action="<?= APP_URL ?>/teacher/alert_update.php" style="margin:0">
                            <input type="hidden" name="id" value="<?= $a['id'] ?>">
                            <input type="hidden" name="status" value="ignored">
                            <input type="hidden" name="student_id" value="<?= $student['id'] ?>">
                            <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                            <button class="btn btn-sm" style="background:#F1F5F9;color:#64748B;border:none">Bỏ qua</button>
                        </form>
                    </div>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>
