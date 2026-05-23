<?php
// views/student/courses.php
// Dữ liệu từ public/student/courses.php:
//   $courses (array với đầy đủ stats)
require_once APP_ROOT . '/views/layouts/header.php';
?>

<div class="page-title">My Courses</div>
<p class="page-sub">Tất cả môn học bạn đang ghi danh trong kỳ này</p>

<?php if (empty($courses)): ?>
<div class="card" style="padding:50px;text-align:center;color:#94A3B8">
  <div style="font-size:44px;margin-bottom:14px">📚</div>
  <div style="font-size:16px;font-weight:600;color:#374151;margin-bottom:6px">Chưa có môn học nào</div>
  <div style="font-size:14px">Liên hệ Admin để được ghi danh vào môn học.</div>
</div>

<?php else: ?>
<!-- Summary bar -->
<div style="display:flex;gap:12px;margin-bottom:24px;flex-wrap:wrap">
  <div class="card" style="padding:14px 20px;display:flex;align-items:center;gap:10px">
    <span style="font-size:20px">🎓</span>
    <div>
      <div style="font-size:20px;font-weight:800;color:#0F172A"><?= count($courses) ?></div>
      <div style="font-size:11px;color:#94A3B8">Môn đang học</div>
    </div>
  </div>
  <?php
  $totalAttended = array_sum(array_column($courses, 'present_count'));
  $totalSessions = array_sum(array_column($courses, 'ended_sessions'));
  $overallPct    = $totalSessions > 0 ? round($totalAttended / $totalSessions * 100) : 0;
  $avgEngage     = count($courses) > 0 ? round(array_sum(array_column($courses,'engagement')) / count($courses), 1) : 0;
  ?>
  <div class="card" style="padding:14px 20px;display:flex;align-items:center;gap:10px">
    <span style="font-size:20px">✅</span>
    <div>
      <div style="font-size:20px;font-weight:800;color:<?= $overallPct>=80?'#059669':($overallPct>=60?'#D97706':'#DC2626') ?>">
        <?= $overallPct ?>%
      </div>
      <div style="font-size:11px;color:#94A3B8">Điểm danh trung bình</div>
    </div>
  </div>
  <div class="card" style="padding:14px 20px;display:flex;align-items:center;gap:10px">
    <span style="font-size:20px">📊</span>
    <div>
      <div style="font-size:20px;font-weight:800;color:<?= $avgEngage>=70?'#059669':($avgEngage>=40?'#D97706':'#DC2626') ?>">
        <?= $avgEngage ?>%
      </div>
      <div style="font-size:11px;color:#94A3B8">Engagement trung bình</div>
    </div>
  </div>
</div>

<!-- Course cards -->
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(310px,1fr));gap:18px">
<?php foreach ($courses as $c):
  $engColor = $c['engagement'] >= 70 ? '#059669' : ($c['engagement'] >= 40 ? '#D97706' : '#DC2626');
  $attColor = $c['att_pct']    >= 80 ? '#059669' : ($c['att_pct']    >= 60 ? '#D97706' : '#DC2626');
?>
<div class="card" style="padding:22px;display:flex;flex-direction:column">
  <!-- Header -->
  <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:14px">
    <span style="background:#EFF6FF;color:#2563EB;padding:4px 12px;border-radius:99px;
                 font-size:12px;font-weight:700">
      <?= htmlspecialchars($c['course_code']) ?>
    </span>
    <span style="font-size:13px;font-weight:700;color:<?= $engColor ?>">
      <?= $c['engagement'] ?>% Engagement
    </span>
  </div>

  <!-- Tên môn -->
  <div style="font-weight:700;font-size:16px;color:#0F172A;margin-bottom:4px;line-height:1.4">
    <?= htmlspecialchars($c['course_name']) ?>
  </div>
  <div style="font-size:12px;color:#94A3B8;margin-bottom:14px">
    <?= htmlspecialchars($c['semester'] ?? '') ?>
    <?php if (!empty($c['teacher_name'])): ?>
      · 👤 <?= htmlspecialchars($c['teacher_name']) ?>
    <?php endif; ?>
  </div>

  <?php if (!empty($c['description'])): ?>
  <div style="font-size:13px;color:#64748B;margin-bottom:14px;line-height:1.5">
    <?= htmlspecialchars(mb_strimwidth($c['description'], 0, 110, '...')) ?>
  </div>
  <?php endif; ?>

  <!-- Stats grid -->
  <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;margin-bottom:14px">
    <div style="text-align:center;padding:10px 6px;background:#F8FAFC;border-radius:8px">
      <div style="font-size:17px;font-weight:800;color:<?= $attColor ?>"><?= $c['att_pct'] ?>%</div>
      <div style="font-size:10px;color:#94A3B8;margin-top:2px">Điểm danh</div>
    </div>
    <div style="text-align:center;padding:10px 6px;background:#F8FAFC;border-radius:8px">
      <div style="font-size:17px;font-weight:800;color:#2563EB"><?= $c['present_count'] ?>/<?= $c['ended_sessions'] ?></div>
      <div style="font-size:10px;color:#94A3B8;margin-top:2px">Buổi có mặt</div>
    </div>
    <div style="text-align:center;padding:10px 6px;background:#F8FAFC;border-radius:8px">
      <div style="font-size:17px;font-weight:800;color:#7C3AED"><?= $c['quiz_count'] ?></div>
      <div style="font-size:10px;color:#94A3B8;margin-top:2px">Quiz đã nộp</div>
    </div>
  </div>

  <!-- Attendance progress -->
  <div style="margin-bottom:16px">
    <div style="display:flex;justify-content:space-between;font-size:11px;color:#94A3B8;margin-bottom:4px">
      <span>Tỷ lệ điểm danh</span><span><?= $c['att_pct'] ?>%</span>
    </div>
    <div style="background:#F1F5F9;border-radius:99px;height:6px">
      <div style="background:<?= $attColor ?>;height:6px;border-radius:99px;width:<?= $c['att_pct'] ?>%;transition:width .4s"></div>
    </div>
  </div>

  <!-- Nút -->
  <div style="display:flex;gap:8px;margin-top:auto">
    <a href="<?= APP_URL ?>/student/attendance.php?course_id=<?= $c['id'] ?>"
       style="flex:1;padding:9px 0;background:#EFF6FF;color:#2563EB;border-radius:8px;
              text-align:center;font-size:12px;font-weight:700;text-decoration:none">
      📅 Attendance
    </a>
    <a href="<?= APP_URL ?>/student/quiz.php?course_id=<?= $c['id'] ?>"
       style="flex:1;padding:9px 0;background:#F0FDF4;color:#059669;border-radius:8px;
              text-align:center;font-size:12px;font-weight:700;text-decoration:none">
      📝 Quiz
    </a>
    <a href="<?= APP_URL ?>/student/engagement.php?course_id=<?= $c['id'] ?>"
       style="flex:1;padding:9px 0;background:#FFF7ED;color:#D97706;border-radius:8px;
              text-align:center;font-size:12px;font-weight:700;text-decoration:none">
      📊 Score
    </a>
  </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>