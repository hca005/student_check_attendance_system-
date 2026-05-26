<?php require_once APP_ROOT . '/views/layouts/header.php'; ?>

<div class="page-title">Quản lý Điểm danh</div>
<p class="page-sub">Chọn buổi học để quản lý điểm danh</p>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px">
<?php if (empty($sessions)): ?>
    <div class="card" style="padding:32px;text-align:center;color:#94A3B8">Chưa có buổi học nào</div>
<?php else: ?>
<?php foreach ($sessions as $s): ?>
<div class="card" style="padding:20px">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px">
        <div>
            <div style="font-weight:700;font-size:14px;margin-bottom:4px"><?= htmlspecialchars($s["title"] ?? "Buổi học") ?></div>
            <div style="font-size:12px;color:#94A3B8"><?= date("d/m/Y", strtotime($s["session_date"])) ?> | <?= substr($s["start_time"],0,5) ?> – <?= substr($s["end_time"],0,5) ?></div>
        </div>
        <?php
        $statusMap = ["upcoming"=>["Sắp tới","badge-gray"],"active"=>["Đang học","badge-success"],"ended"=>["Đã kết thúc",""]];
        $st = $statusMap[$s["status"]] ?? [$s["status"],"badge-gray"];
        ?>
        <span class="badge <?= $st[1] ?>" style="<?= $s["status"]==="ended"?"background:#FEE2E2;color:#991B1B":"" ?>"><?= $st[0] ?></span>
    </div>
    <div style="font-size:13px;color:#64748B;margin-bottom:14px">
        <span class="badge badge-primary"><?= htmlspecialchars($s["course_name"]) ?></span>
    </div>
    <div style="display:flex;gap:8px">
        <a href="<?= APP_URL ?>/teacher/attendance/methods_list.php?session_id=<?= $s["id"] ?>" class="btn btn-primary btn-sm" style="flex:1;justify-content:center">
            Phương thức điểm danh
        </a>
        <a href="<?= APP_URL ?>/teacher/attendance/records_list.php?session_id=<?= $s["id"] ?>" class="btn btn-outline btn-sm" style="flex:1;justify-content:center">
            Xem bản ghi
        </a>
    </div>
</div>
<?php endforeach; ?>
<?php endif; ?>
</div>

<?php require_once APP_ROOT . "/views/layouts/footer.php"; ?>
