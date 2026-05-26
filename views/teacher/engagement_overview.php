<?php require_once APP_ROOT . '/views/layouts/header.php'; ?>

<div class="page-title">Engagement Scores</div>
<p class="page-sub">Điểm tham gia của sinh viên theo môn học</p>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Sinh viên</th><th>Mã SV</th><th>Môn</th><th>Buổi tham gia</th><th>Quiz Score</th><th>Engagement Index</th></tr>
            </thead>
            <tbody>
            <?php if (empty($scores)): ?>
                <tr><td colspan="6" style="text-align:center;color:#94A3B8;padding:32px">Chưa có dữ liệu engagement.</td></tr>
            <?php else: ?>
            <?php foreach ($scores as $s): ?>
            <tr>
                <td style="font-weight:600"><?= htmlspecialchars($s['full_name']) ?></td>
                <td style="color:#64748B;font-size:13px"><?= htmlspecialchars($s['student_code'] ?? '—') ?></td>
                <td><span class="badge badge-primary"><?= htmlspecialchars($s['course_name']) ?></span></td>
                <td>
                    <div style="display:flex;align-items:center;gap:8px">
                        <?= $s['attended_sessions'] ?>/<?= $s['total_sessions'] ?>
                        <div style="flex:1;background:#F1F5F9;border-radius:4px;height:6px;max-width:80px">
                            <div style="background:#2563EB;height:6px;border-radius:4px;width:<?= $s['total_sessions']>0?round($s['attended_sessions']/$s['total_sessions']*100):0 ?>%"></div>
                        </div>
                    </div>
                </td>
                <td style="font-weight:600"><?= $s['total_quiz_score'] ?></td>
                <td>
                    <?php $idx = $s['engagement_index']; ?>
                    <span style="font-weight:700;font-size:15px;color:<?= $idx>=70?'#10B981':($idx>=40?'#F59E0B':'#EF4444') ?>">
                        <?= $idx ?>
                    </span>
                    <span style="font-size:11px;color:#94A3B8">/100</span>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>
