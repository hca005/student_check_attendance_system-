<?php require_once APP_ROOT . "/views/layouts/header.php"; ?>

<div style="display:flex;align-items:center;gap:12px;margin-bottom:4px">
    <a href="<?= APP_URL ?>/teacher/engagement.php" class="btn btn-outline btn-sm">← Quay lại</a>
    <div class="page-title" style="margin:0"><?= htmlspecialchars($student["full_name"]) ?></div>
</div>
<p class="page-sub">Mã SV: <?= htmlspecialchars($student["student_code"] ?? "—") ?> · <?= htmlspecialchars($course["course_name"]) ?></p>

<div class="stat-cards" style="margin-bottom:24px">
    <div class="card stat-card">
        <div class="stat-icon" style="background:#EFF6FF">
            <svg fill="none" viewBox="0 0 24 24" stroke="#2563EB" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        </div>
        <div>
            <div class="stat-value"><?= $score["attended_sessions"] ?>/<?= $score["total_sessions"] ?></div>
            <div class="stat-label">Buổi tham gia</div>
        </div>
    </div>
    <div class="card stat-card">
        <div class="stat-icon" style="background:#F0FDF4">
            <svg fill="none" viewBox="0 0 24 24" stroke="#10B981" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
        </div>
        <div>
            <div class="stat-value"><?= $score["total_quiz_score"] ?></div>
            <div class="stat-label">Tổng điểm Quiz</div>
        </div>
    </div>
    <div class="card stat-card">
        <div class="stat-icon" style="background:#FFF7ED">
            <svg fill="none" viewBox="0 0 24 24" stroke="#F59E0B" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <div>
            <div class="stat-value" style="color:<?= $score["engagement_index"]>=70?"#10B981":($score["engagement_index"]>=40?"#F59E0B":"#EF4444") ?>"><?= $score["engagement_index"] ?></div>
            <div class="stat-label">Engagement Index</div>
        </div>
    </div>
</div>

<div class="card" style="margin-bottom:24px">
    <div style="padding:16px 20px 12px;font-weight:700;font-size:14px;border-bottom:1px solid #F1F5F9">📅 Lịch sử Điểm danh</div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Ngày</th><th>Buổi học</th><th>Trạng thái</th><th>Check-in lúc</th></tr></thead>
            <tbody>
            <?php if (empty($attendances)): ?>
                <tr><td colspan="4" style="text-align:center;color:#94A3B8;padding:24px">Chưa có dữ liệu điểm danh</td></tr>
            <?php else: ?>
            <?php foreach ($attendances as $a): ?>
            <tr>
                <td style="font-weight:600"><?= date("d/m/Y", strtotime($a["session_date"])) ?></td>
                <td><?= htmlspecialchars($a["title"] ?? "Buổi học") ?></td>
                <td>
                    <?php
                    $st = ["present"=>["Có mặt","#D1FAE5","#065F46"],"absent"=>["Vắng","#FEE2E2","#991B1B"],"late"=>["Trễ","#FEF3C7","#92400E"],"excused"=>["Có phép","#EFF6FF","#1D4ED8"]];
                    $s = $st[$a["status"]] ?? [$a["status"],"#F1F5F9","#374151"];
                    ?>
                    <span class="badge" style="background:<?= $s[1] ?>;color:<?= $s[2] ?>"><?= $s[0] ?></span>
                </td>
                <td style="font-size:13px;color:#64748B"><?= $a["checked_in_at"] ? date("H:i", strtotime($a["checked_in_at"])) : "—" ?></td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div style="padding:16px 20px 12px;font-weight:700;font-size:14px;border-bottom:1px solid #F1F5F9">📝 Lịch sử Quiz</div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Quiz</th><th>Ngày</th><th>Điểm</th><th>Tỉ lệ</th></tr></thead>
            <tbody>
            <?php if (empty($quizSubmissions)): ?>
                <tr><td colspan="4" style="text-align:center;color:#94A3B8;padding:24px">Chưa có bài nộp nào</td></tr>
            <?php else: ?>
            <?php foreach ($quizSubmissions as $q): ?>
            <tr>
                <td style="font-weight:600"><?= htmlspecialchars($q["title"]) ?></td>
                <td style="font-size:13px;color:#64748B"><?= date("d/m/Y", strtotime($q["session_date"])) ?></td>
                <td><strong><?= $q["total_score"] ?></strong>/<?= $q["max_score"] ?></td>
                <td>
                    <?php $pct = $q["max_score"] > 0 ? round($q["total_score"]/$q["max_score"]*100) : 0; ?>
                    <span style="color:<?= $pct>=70?"#10B981":($pct>=40?"#F59E0B":"#EF4444") ?>"><?= $pct ?>%</span>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once APP_ROOT . "/views/layouts/footer.php"; ?>
