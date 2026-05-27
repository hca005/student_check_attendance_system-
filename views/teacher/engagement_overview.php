<?php require_once APP_ROOT . "/views/layouts/header.php"; ?>

<div class="page-title">Engagement Scores</div>
<p class="page-sub">Điểm tham gia của sinh viên theo môn học</p>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Sinh viên</th><th>Mã SV</th><th>Môn</th><th>Buổi tham gia</th><th>Quiz Score</th><th>Engagement Index</th><th></th></tr>
            </thead>
            <tbody>
            <?php if (empty($scores)): ?>
                <tr><td colspan="7" style="text-align:center;color:#94A3B8;padding:32px">Chưa có dữ liệu.</td></tr>
            <?php else: ?>
            <?php foreach ($scores as $s): ?>
            <tr>
                <td style="font-weight:600"><?= htmlspecialchars($s["full_name"]) ?></td>
                <td style="color:#64748B;font-size:13px"><?= htmlspecialchars($s["student_code"] ?? "") ?></td>
                <td><span class="badge badge-primary"><?= htmlspecialchars($s["course_name"]) ?></span></td>
                <td><?= $s["attended_sessions"] ?>/<?= $s["total_sessions"] ?></td>
                <td style="font-weight:600"><?= $s["total_quiz_score"] ?></td>
                <td><span style="font-weight:700;color:<?= $s["engagement_index"]>=70?"#10B981":($s["engagement_index"]>=40?"#F59E0B":"#EF4444") ?>"><?= $s["engagement_index"] ?></span>/100</td>
                <td><a href="<?= APP_URL ?>/teacher/engagement_detail.php?student_id=<?= $s["student_id"] ?>&course_id=<?= $s["course_id"] ?>" class="btn btn-outline btn-sm">Chi tiết</a></td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once APP_ROOT . "/views/layouts/footer.php"; ?>
