<?php require_once APP_ROOT . '/views/layouts/header.php'; ?>

<div class="page-title">Alerts</div>
<p class="page-sub">Cảnh báo sinh viên có nguy cơ chuyên cần / tương tác thấp</p>

<?php
$openCount = count(array_filter($alerts ?? [], fn($a) => $a['status'] === 'open'));
if ($openCount > 0):
?>
<div style="background:#FEF2F2;border:1px solid #EF4444;color:#991B1B;padding:12px 16px;border-radius:8px;margin-bottom:16px">
    ⚠️ Có <strong><?= $openCount ?></strong> alert chưa được xử lý
</div>
<?php endif; ?>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Sinh viên</th><th>Mã SV</th><th>Môn</th><th>Loại</th><th>Nội dung</th><th>Trạng thái</th><th></th></tr>
            </thead>
            <tbody>
            <?php if (empty($alerts)): ?>
                <tr><td colspan="7" style="text-align:center;color:#94A3B8;padding:32px">Không có alert nào.</td></tr>
            <?php else: ?>
            <?php foreach ($alerts as $a): ?>
            <tr>
                <td style="font-weight:600"><?= htmlspecialchars($a['full_name']) ?></td>
                <td style="color:#64748B;font-size:13px"><?= htmlspecialchars($a['student_code'] ?? '—') ?></td>
                <td><span class="badge badge-primary"><?= htmlspecialchars($a['course_name']) ?></span></td>
                <td>
                    <?php
                    $typeMap = ['high_absence'=>['Vắng nhiều','#FEE2E2','#991B1B'],'low_engagement'=>['Tương tác thấp','#FEF3C7','#92400E'],'missed_quiz'=>['Bỏ lỡ quiz','#EFF6FF','#1D4ED8']];
                    $t = $typeMap[$a['alert_type']] ?? [$a['alert_type'],'#F1F5F9','#374151'];
                    ?>
                    <span class="badge" style="background:<?= $t[1] ?>;color:<?= $t[2] ?>"><?= $t[0] ?></span>
                </td>
                <td style="font-size:13px"><?= htmlspecialchars($a['alert_message']) ?></td>
                <td>
                    <?php
                    $sc = ['open'=>['Chưa xử lý','#FEE2E2','#991B1B'],'resolved'=>['Đã xử lý','#D1FAE5','#065F46'],'ignored'=>['Bỏ qua','#F1F5F9','#64748B']];
                    $st = $sc[$a['status']] ?? [$a['status'],'#F1F5F9','#374151'];
                    ?>
                    <span class="badge" style="background:<?= $st[1] ?>;color:<?= $st[2] ?>"><?= $st[0] ?></span>
                </td>
                <td><a href="<?= APP_URL ?>/teacher/alert_detail.php?student_id=<?= $a['student_id'] ?>&course_id=<?= $a['course_id'] ?>" class="btn btn-outline btn-sm">Chi tiết</a></td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>
