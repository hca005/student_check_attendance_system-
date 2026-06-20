<?php
// views/student/scores.php
// Trang tổng hợp điểm quiz và engagement của student
require_once APP_ROOT . '/views/layouts/header.php';
?>

<div class="page-title">My Scores</div>
<p class="page-sub">Tổng hợp kết quả quiz và điểm tham gia theo từng môn học</p>

<?php if (!empty($engagements)):
    $totalQuizScore  = array_sum(array_column($quizHistory, 'total_score'));
    $totalQuizMax    = array_sum(array_column($quizHistory, 'max_score'));
    $overallQuizPct  = $totalQuizMax > 0 ? round($totalQuizScore / $totalQuizMax * 100) : 0;
    $avgEngagement   = count($engagements) > 0
        ? round(array_sum(array_column($engagements, 'engagement_index')) / count($engagements), 1) : 0;
?>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px;margin-bottom:26px">
    <div class="card" style="padding:16px;text-align:center;background:#EFF6FF">
        <div style="font-size:28px;font-weight:800;color:#2563EB"><?= count($quizHistory) ?></div>
        <div style="font-size:12px;color:#64748B;margin-top:4px">Quiz đã nộp</div>
    </div>
    <div class="card" style="padding:16px;text-align:center;background:<?= $overallQuizPct>=70?'#F0FDF4':($overallQuizPct>=50?'#FFFBEB':'#FEF2F2') ?>">
        <div style="font-size:28px;font-weight:800;color:<?= $overallQuizPct>=70?'#059669':($overallQuizPct>=50?'#D97706':'#DC2626') ?>">
            <?= $overallQuizPct ?>%
        </div>
        <div style="font-size:12px;color:#64748B;margin-top:4px">Điểm quiz trung bình</div>
    </div>
    <div class="card" style="padding:16px;text-align:center;background:<?= $avgEngagement>=70?'#F0FDF4':($avgEngagement>=40?'#FFFBEB':'#FEF2F2') ?>">
        <div style="font-size:28px;font-weight:800;color:<?= $avgEngagement>=70?'#059669':($avgEngagement>=40?'#D97706':'#DC2626') ?>">
            <?= $avgEngagement ?>%
        </div>
        <div style="font-size:12px;color:#64748B;margin-top:4px">Engagement trung bình</div>
    </div>
    <div class="card" style="padding:16px;text-align:center;background:#F8FAFC">
        <div style="font-size:28px;font-weight:800;color:#374151"><?= count($engagements) ?></div>
        <div style="font-size:12px;color:#64748B;margin-top:4px">Môn học</div>
    </div>
</div>

<div style="font-weight:700;font-size:14px;color:#374151;margin-bottom:12px">Điểm engagement theo môn</div>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px;margin-bottom:28px">
    <?php foreach ($engagements as $e):
        $idx   = (float)$e['engagement_index'];
        $color = $idx >= 70 ? '#059669' : ($idx >= 40 ? '#D97706' : '#DC2626');
        $attPct = $e['total_sessions'] > 0
            ? round($e['attended_sessions'] / $e['total_sessions'] * 100) : 0;
    ?>
    <div class="card" style="padding:18px">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px">
            <div>
                <span style="background:#EFF6FF;color:#2563EB;padding:2px 10px;
                             border-radius:99px;font-size:11px;font-weight:700">
                    <?= htmlspecialchars($e['course_code']) ?>
                </span>
                <div style="font-weight:700;font-size:14px;color:#0F172A;margin-top:6px">
                    <?= htmlspecialchars($e['course_name']) ?>
                </div>
            </div>
            <div style="text-align:right">
                <div style="font-size:26px;font-weight:800;color:<?= $color ?>"><?= $idx ?>%</div>
                <div style="font-size:10px;color:<?= $color ?>;font-weight:700">
                    <?= $idx>=70?'Tốt':($idx>=40?'Trung bình':'Cần cải thiện') ?>
                </div>
            </div>
        </div>

        <div style="background:#F1F5F9;border-radius:99px;height:7px;margin-bottom:14px">
            <div style="background:<?= $color ?>;height:7px;border-radius:99px;width:<?= $idx ?>%"></div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px">
            <div style="text-align:center;padding:8px 4px;background:#F8FAFC;border-radius:8px">
                <div style="font-size:14px;font-weight:700;color:<?= $attPct>=80?'#059669':($attPct>=60?'#D97706':'#DC2626') ?>">
                    <?= $attPct ?>%
                </div>
                <div style="font-size:10px;color:#94A3B8;margin-top:2px">Điểm danh</div>
            </div>
            <div style="text-align:center;padding:8px 4px;background:#F8FAFC;border-radius:8px">
                <div style="font-size:14px;font-weight:700;color:#7C3AED">
                    <?= round($e['total_quiz_score'], 1) ?>
                </div>
                <div style="font-size:10px;color:#94A3B8;margin-top:2px">Quiz</div>
            </div>
            <div style="text-align:center;padding:8px 4px;background:#F8FAFC;border-radius:8px">
                <div style="font-size:14px;font-weight:700;color:#0369A1">
                    <?= round($e['total_interaction_points'], 1) ?>
                </div>
                <div style="font-size:10px;color:#94A3B8;margin-top:2px">Tương tác</div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div style="font-weight:700;font-size:14px;color:#374151;margin-bottom:12px">Lịch sử nộp quiz</div>

<?php if (empty($quizHistory)): ?>
<div class="card" style="padding:40px;text-align:center;color:#94A3B8">
    <div style="font-size:36px;margin-bottom:10px">📋</div>
    <div>Bạn chưa nộp bài quiz nào.</div>
</div>
<?php else: ?>
<div class="card" style="overflow:hidden">
    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse;font-size:14px">
            <thead>
                <tr style="background:#F8FAFC">
                    <th style="padding:11px 16px;text-align:left;font-size:12px;font-weight:600;color:#64748B">#</th>
                    <th style="padding:11px 16px;text-align:left;font-size:12px;font-weight:600;color:#64748B">Quiz</th>
                    <th style="padding:11px 16px;text-align:left;font-size:12px;font-weight:600;color:#64748B">Môn học</th>
                    <th style="padding:11px 16px;text-align:left;font-size:12px;font-weight:600;color:#64748B">Điểm</th>
                    <th style="padding:11px 16px;text-align:left;font-size:12px;font-weight:600;color:#64748B">Kết quả</th>
                    <th style="padding:11px 16px;text-align:left;font-size:12px;font-weight:600;color:#64748B">Ngày nộp</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($quizHistory as $i => $q):
                    $pct   = $q['max_score'] > 0 ? round($q['total_score'] / $q['max_score'] * 100) : 0;
                    $color = $pct >= 70 ? '#059669' : ($pct >= 50 ? '#D97706' : '#DC2626');
                    $bg    = $pct >= 70 ? '#D1FAE5' : ($pct >= 50 ? '#FEF3C7' : '#FEE2E2');
                    $grade = $pct >= 70 ? 'Tốt' : ($pct >= 50 ? 'Đạt' : 'Chưa đạt');
                ?>
                <tr style="border-bottom:1px solid #F8FAFC"
                    onmouseover="this.style.background='#F8FAFC'"
                    onmouseout="this.style.background=''">
                    <td style="padding:12px 16px;color:#94A3B8;font-size:13px"><?= $i + 1 ?></td>
                    <td style="padding:12px 16px;font-weight:600;color:#0F172A">
                        <?= htmlspecialchars($q['quiz_title']) ?>
                        <div style="font-size:11px;color:#94A3B8;font-weight:400;margin-top:2px">
                            <?= htmlspecialchars($q['session_date']) ?>
                        </div>
                    </td>
                    <td style="padding:12px 16px">
                        <span style="background:#EFF6FF;color:#2563EB;padding:2px 8px;
                                     border-radius:99px;font-size:11px;font-weight:700">
                            <?= htmlspecialchars($q['course_code']) ?>
                        </span>
                        <div style="font-size:12px;color:#64748B;margin-top:3px">
                            <?= htmlspecialchars($q['course_name']) ?>
                        </div>
                    </td>
                    <td style="padding:12px 16px;font-weight:700;font-size:15px;color:<?= $color ?>">
                        <?= $q['total_score'] ?>/<?= $q['max_score'] ?>
                    </td>
                    <td style="padding:12px 16px">
                        <span style="background:<?= $bg ?>;color:<?= $color ?>;padding:3px 10px;
                                     border-radius:99px;font-size:12px;font-weight:700">
                            <?= $pct ?>% — <?= $grade ?>
                        </span>
                        <div style="background:#F1F5F9;border-radius:99px;height:4px;margin-top:6px;width:120px">
                            <div style="background:<?= $color ?>;height:4px;border-radius:99px;width:<?= $pct ?>%"></div>
                        </div>
                    </td>
                    <td style="padding:12px 16px;color:#64748B;font-size:13px;white-space:nowrap">
                        <?= date('d/m/Y', strtotime($q['submitted_at'])) ?>
                        <div style="font-size:11px;color:#94A3B8">
                            <?= date('H:i', strtotime($q['submitted_at'])) ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>
