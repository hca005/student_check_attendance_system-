<?php require_once APP_ROOT . '/views/layouts/header.php'; ?>

<div class="page-title">Class Sessions</div>
<p class="page-sub">Danh sách tất cả buổi học của bạn</p>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Ngày</th><th>Buổi học</th><th>Môn</th><th>Trạng thái</th><th>Attendance</th><th>Quiz</th></tr>
            </thead>
            <tbody>
            <?php if (empty($sessions)): ?>
                <tr><td colspan="6" style="text-align:center;color:#94A3B8;padding:32px">Chưa có buổi học nào</td></tr>
            <?php else: ?>
            <?php foreach ($sessions as $s): ?>
            <tr>
                <td style="font-weight:600"><?= date("d/m/Y", strtotime($s["session_date"])) ?></td>
                <td>
                    <div style="font-weight:600;font-size:13px"><?= htmlspecialchars($s["title"] ?? "Buổi học") ?></div>
                    <div style="font-size:12px;color:#94A3B8"><?= substr($s["start_time"],0,5) ?> – <?= substr($s["end_time"],0,5) ?></div>
                </td>
                <td><span class="badge badge-primary"><?= htmlspecialchars($s["course_name"]) ?></span></td>
                <td>
                    <?php
                    $statusMap = ["upcoming"=>["Sắp tới","badge-gray"],"active"=>["Đang học","badge-success"],"ended"=>["Đã kết thúc",""]];
                    $st = $statusMap[$s["status"]] ?? [$s["status"],"badge-gray"];
                    ?>
                    <span class="badge <?= $st[1] ?>" style="<?= $s["status"]==="ended"?"background:#FEE2E2;color:#991B1B":"" ?>"><?= $st[0] ?></span>
                </td>
                <td><a href="<?= APP_URL ?>/teacher/attendance/methods_list.php?session_id=<?= $s["id"] ?>" class="btn btn-primary btn-sm">Attendance</a></td>
                <td><a href="<?= APP_URL ?>/teacher/quiz/sessions_list.php?session_id=<?= $s["id"] ?>" class="btn btn-sm" style="background:#FEF3C7;color:#92400E">Quiz</a></td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once APP_ROOT . "/views/layouts/footer.php"; ?>
