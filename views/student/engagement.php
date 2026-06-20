<?php
// views/student/engagement.php
// Dữ liệu từ public/student/engagement.php:
//   $engagements, $quizHistory, $interactionLogs, $openAlerts
require_once APP_ROOT . '/views/layouts/header.php';
?>

<div class="page-title">My Engagement</div>
<p class="page-sub">Tổng hợp điểm tham gia và lịch sử tương tác của bạn</p>

<!-- ── Cảnh báo đang mở ─────────────────────────────────── -->
<?php if (!empty($openAlerts)): ?>
<div style="background:#FEF3C7;border:1.5px solid #F59E0B;border-radius:10px;padding:14px 18px;margin-bottom:22px">
  <div style="font-weight:700;color:#92400E;margin-bottom:8px">
    ⚠️ Bạn có <?= count($openAlerts) ?> cảnh báo đang mở
  </div>
  <?php foreach ($openAlerts as $al): ?>
  <div style="font-size:13px;color:#92400E;padding:6px 0;border-bottom:1px solid rgba(245,158,11,.2)">
    <strong>[<?= htmlspecialchars($al['course_code']) ?>]</strong>
    <?= htmlspecialchars($al['message']) ?>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- ── Engagement theo từng môn ─────────────────────────── -->
<?php if (empty($engagements)): ?>
<div class="card" style="padding:40px;text-align:center;color:#94A3B8">
  <div style="font-size:40px;margin-bottom:12px">📊</div>
  <div>Chưa có dữ liệu engagement. Hãy tích cực tham gia lớp học!</div>
</div>

<?php else: ?>
<div style="font-weight:700;font-size:14px;color:#374151;margin-bottom:14px">Engagement theo môn học</div>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px;margin-bottom:28px">
  <?php foreach ($engagements as $e):
    $idx   = (float)$e['engagement_index'];
    $color = $idx >= 70 ? '#059669' : ($idx >= 40 ? '#D97706' : '#DC2626');
    $bg    = $idx >= 70 ? '#F0FDF4' : ($idx >= 40 ? '#FFFBEB' : '#FEF2F2');
    $label = $idx >= 70 ? 'Tốt 🎉' : ($idx >= 40 ? 'Trung bình ⚠️' : 'Cần cải thiện ❗');
  ?>
  <div class="card" style="padding:20px">
    <!-- Course name -->
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:14px">
      <div>
        <span style="background:#EFF6FF;color:#2563EB;padding:2px 10px;border-radius:99px;
                     font-size:11px;font-weight:700"><?= htmlspecialchars($e['course_code']) ?></span>
        <div style="font-weight:700;font-size:14px;color:#0F172A;margin-top:6px">
          <?= htmlspecialchars($e['course_name']) ?>
        </div>
        <div style="font-size:11px;color:#94A3B8"><?= htmlspecialchars($e['semester'] ?? '') ?></div>
      </div>
      <div style="text-align:right">
        <div style="font-size:28px;font-weight:800;color:<?= $color ?>"><?= $idx ?>%</div>
        <div style="font-size:11px;font-weight:700;color:<?= $color ?>"><?= $label ?></div>
      </div>
    </div>

    <!-- Engagement index bar -->
    <div style="background:#F1F5F9;border-radius:99px;height:8px;margin-bottom:16px">
      <div style="background:<?= $color ?>;height:8px;border-radius:99px;
                  width:<?= $idx ?>%;transition:width .5s"></div>
    </div>

    <!-- Detail stats -->
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;margin-bottom:14px">
      <?php
      $attPct = $e['total_sessions'] > 0
        ? round($e['attended_sessions'] / $e['total_sessions'] * 100) : 0;
      ?>
      <div style="text-align:center;padding:10px 6px;background:<?= $bg ?>;border-radius:8px">
        <div style="font-size:16px;font-weight:800;color:<?= $color ?>"><?= $attPct ?>%</div>
        <div style="font-size:10px;color:#94A3B8;margin-top:2px">Điểm danh<br><?= $e['attended_sessions'] ?>/<?= $e['total_sessions'] ?></div>
      </div>
      <div style="text-align:center;padding:10px 6px;background:#F8FAFC;border-radius:8px">
        <div style="font-size:16px;font-weight:800;color:#7C3AED"><?= round($e['total_quiz_score'],1) ?></div>
        <div style="font-size:10px;color:#94A3B8;margin-top:2px">Điểm quiz</div>
      </div>
      <div style="text-align:center;padding:10px 6px;background:#F8FAFC;border-radius:8px">
        <div style="font-size:16px;font-weight:800;color:#0369A1"><?= round($e['total_interaction_points'],1) ?></div>
        <div style="font-size:10px;color:#94A3B8;margin-top:2px">Điểm tương tác</div>
      </div>
    </div>

    <!-- Cập nhật lúc -->
    <?php if (!empty($e['calculated_at'])): ?>
    <div style="font-size:11px;color:#94A3B8;text-align:right">
      Cập nhật: <?= date('d/m/Y H:i', strtotime($e['calculated_at'])) ?>
    </div>
    <?php endif; ?>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- ── Lịch sử nộp quiz ──────────────────────────────────── -->
<?php if (!empty($quizHistory)): ?>
<div style="font-weight:700;font-size:14px;color:#374151;margin-bottom:12px">Lịch sử nộp quiz (20 gần nhất)</div>
<div class="card" style="overflow:hidden;margin-bottom:28px">
  <div style="overflow-x:auto">
    <table style="width:100%;border-collapse:collapse;font-size:14px">
      <thead>
        <tr style="background:#F8FAFC">
          <th style="padding:11px 16px;text-align:left;font-size:12px;font-weight:600;color:#64748B">Quiz</th>
          <th style="padding:11px 16px;text-align:left;font-size:12px;font-weight:600;color:#64748B">Môn</th>
          <th style="padding:11px 16px;text-align:left;font-size:12px;font-weight:600;color:#64748B">Điểm</th>
          <th style="padding:11px 16px;text-align:left;font-size:12px;font-weight:600;color:#64748B">%</th>
          <th style="padding:11px 16px;text-align:left;font-size:12px;font-weight:600;color:#64748B">Thời gian</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($quizHistory as $q):
          $pct   = $q['max_score'] > 0 ? round($q['total_score'] / $q['max_score'] * 100) : 0;
          $color = $pct >= 70 ? '#059669' : ($pct >= 50 ? '#D97706' : '#DC2626');
        ?>
        <tr style="border-bottom:1px solid #F8FAFC" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background=''">
          <td style="padding:12px 16px;color:#0F172A;font-weight:600"><?= htmlspecialchars($q['quiz_title']) ?></td>
          <td style="padding:12px 16px">
            <span style="background:#EFF6FF;color:#2563EB;padding:2px 8px;border-radius:99px;font-size:11px;font-weight:700">
              <?= htmlspecialchars($q['course_code']) ?>
            </span>
          </td>
          <td style="padding:12px 16px;font-weight:700;color:<?= $color ?>">
            <?= $q['total_score'] ?> / <?= $q['max_score'] ?>
          </td>
          <td style="padding:12px 16px">
            <span style="background:<?= $pct>=70?'#D1FAE5':($pct>=50?'#FEF3C7':'#FEE2E2') ?>;
                         color:<?= $color ?>;padding:3px 10px;border-radius:99px;font-size:12px;font-weight:700">
              <?= $pct ?>%
            </span>
          </td>
          <td style="padding:12px 16px;color:#64748B;font-size:13px">
            <?= date('d/m/Y H:i', strtotime($q['submitted_at'])) ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<!-- ── Lịch sử tương tác ─────────────────────────────────── -->
<?php if (!empty($interactionLogs)): ?>
<div style="font-weight:700;font-size:14px;color:#374151;margin-bottom:12px">Lịch sử tương tác (30 gần nhất)</div>
<div class="card" style="overflow:hidden">
  <div style="overflow-x:auto">
    <table style="width:100%;border-collapse:collapse;font-size:14px">
      <thead>
        <tr style="background:#F8FAFC">
          <th style="padding:11px 16px;text-align:left;font-size:12px;font-weight:600;color:#64748B">Loại</th>
          <th style="padding:11px 16px;text-align:left;font-size:12px;font-weight:600;color:#64748B">Mô tả</th>
          <th style="padding:11px 16px;text-align:left;font-size:12px;font-weight:600;color:#64748B">Môn / Buổi</th>
          <th style="padding:11px 16px;text-align:left;font-size:12px;font-weight:600;color:#64748B">Điểm</th>
          <th style="padding:11px 16px;text-align:left;font-size:12px;font-weight:600;color:#64748B">Thời gian</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $actionIcons = [
          'check_in'        => ['🟢','Điểm danh'],
          'submit_quiz'     => ['📝','Nộp quiz'],
          'answer_question' => ['💬','Trả lời'],
          'discussion'      => ['🗣️','Thảo luận'],
          'other'           => ['⚡','Khác'],
        ];
        foreach ($interactionLogs as $log):
          [$icon, $label] = $actionIcons[$log['action_type']] ?? ['⚡','Khác'];
        ?>
        <tr style="border-bottom:1px solid #F8FAFC" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background=''">
          <td style="padding:11px 16px;white-space:nowrap">
            <span style="font-size:13px"><?= $icon ?> <?= $label ?></span>
          </td>
          <td style="padding:11px 16px;color:#374151;font-size:13px">
            <?= htmlspecialchars($log['description'] ?? '—') ?>
          </td>
          <td style="padding:11px 16px;font-size:12px;color:#64748B">
            <?= htmlspecialchars($log['course_name'] ?? '') ?><br>
            <span style="color:#94A3B8"><?= htmlspecialchars($log['session_date'] ?? '') ?></span>
          </td>
          <td style="padding:11px 16px;font-weight:700;color:<?= $log['points_earned']>0?'#059669':'#94A3B8' ?>">
            +<?= $log['points_earned'] ?>
          </td>
          <td style="padding:11px 16px;color:#64748B;font-size:12px;white-space:nowrap">
            <?= date('d/m H:i', strtotime($log['created_at'])) ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php if (empty($engagements) && empty($quizHistory) && empty($interactionLogs)): ?>
<div class="card" style="padding:40px;text-align:center;color:#94A3B8;margin-top:20px">
  <div style="font-size:40px;margin-bottom:12px">🌱</div>
  <div>Chưa có hoạt động nào được ghi nhận. Hãy điểm danh và làm quiz!</div>
</div>
<?php endif; ?>

<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>