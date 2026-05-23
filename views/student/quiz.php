<?php
// views/student/quiz.php
// Dữ liệu từ public/student/quiz.php:
//   $courses, $courseId, $quizzes
require_once APP_ROOT . '/views/layouts/header.php';

$msgMap = [
  'already_submitted' => ['Bạn đã nộp bài quiz này rồi.', false],
  'quiz_closed'       => ['Quiz này đã đóng hoặc không tồn tại.', false],
];
[$flashText, $flashOk] = $msgMap[$_GET['msg'] ?? ''] ?? [null, null];
?>

<div class="page-title">My Quizzes</div>
<p class="page-sub">Danh sách quiz theo môn học</p>

<?php if ($flashText !== null): ?>
<div style="padding:12px 16px;border-radius:8px;margin-bottom:18px;font-size:14px;font-weight:600;
            background:<?= $flashOk?'#D1FAE5':'#FEE2E2' ?>;color:<?= $flashOk?'#065F46':'#991B1B' ?>">
  <?= htmlspecialchars($flashText) ?>
</div>
<?php endif; ?>

<?php if (empty($courses)): ?>
<div class="card" style="padding:40px;text-align:center;color:#94A3B8">
  <div style="font-size:36px;margin-bottom:10px">📋</div>
  <div>Bạn chưa được ghi danh vào môn học nào.</div>
</div>

<?php else: ?>

<!-- ── Chọn môn học ───────────────────────────────────── -->
<div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;margin-bottom:22px">
  <span style="font-size:13px;font-weight:600;color:#374151">Môn học:</span>
  <?php foreach ($courses as $c): ?>
  <a href="<?= APP_URL ?>/student/quiz.php?course_id=<?= $c['id'] ?>"
     style="padding:5px 14px;border-radius:20px;font-size:13px;font-weight:600;text-decoration:none;
            background:<?= (int)$c['id']===$courseId?'#2563EB':'#F1F5F9' ?>;
            color:<?= (int)$c['id']===$courseId?'#fff':'#374151' ?>">
    <?= htmlspecialchars($c['course_code']) ?>
  </a>
  <?php endforeach; ?>
</div>

<!-- ── Danh sách quiz ─────────────────────────────────── -->
<?php if (empty($quizzes)): ?>
<div class="card" style="padding:40px;text-align:center;color:#94A3B8">
  <div style="font-size:36px;margin-bottom:10px">📝</div>
  <div>Chưa có quiz nào trong môn học này.</div>
</div>

<?php else: ?>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px">
  <?php foreach ($quizzes as $q):
    $submitted  = !empty($q['submission_id']);
    $canTake    = $q['status'] === 'open' && (!$submitted || $q['allow_retake']);
    $myScore    = (float)($q['my_score']  ?? 0);
    $maxScore   = (float)($q['max_score'] ?? 0);
    $scorePct   = $maxScore > 0 ? round($myScore / $maxScore * 100) : 0;
    $scoreColor = $scorePct >= 70 ? '#059669' : ($scorePct >= 50 ? '#D97706' : '#DC2626');

    [$statusBg, $statusText] = match($q['status']) {
      'open'   => ['#D1FAE5;color:#065F46', '🟢 Đang mở'],
      'closed' => ['#F1F5F9;color:#475569',  '⚫ Đã đóng'],
      default  => ['#FEF3C7;color:#92400E',  '🟡 Nháp'],
    };
  ?>
  <div class="card" style="padding:20px;display:flex;flex-direction:column">
    <!-- Header -->
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
      <span style="font-size:11px;color:#94A3B8"><?= htmlspecialchars($q['session_date']) ?></span>
      <span style="background:<?= $statusBg ?>;padding:2px 10px;border-radius:99px;font-size:11px;font-weight:700">
        <?= $statusText ?>
      </span>
    </div>

    <!-- Tên quiz -->
    <div style="font-weight:700;font-size:15px;color:#0F172A;margin-bottom:4px">
      <?= htmlspecialchars($q['title']) ?>
    </div>
    <div style="font-size:12px;color:#94A3B8;margin-bottom:4px">
      <?= htmlspecialchars($q['session_title'] ?? '') ?>
    </div>
    <div style="font-size:12px;color:#64748B;margin-bottom:14px;display:flex;gap:12px">
      <span>📋 <?= (int)$q['question_count'] ?> câu hỏi</span>
      <?php if ($q['time_limit_minutes']): ?>
      <span>⏱ <?= $q['time_limit_minutes'] ?> phút</span>
      <?php else: ?>
      <span>⏱ Không giới hạn</span>
      <?php endif; ?>
      <?php if ($q['allow_retake']): ?>
      <span style="color:#059669">🔄 Được làm lại</span>
      <?php endif; ?>
    </div>

    <!-- Kết quả nếu đã nộp -->
    <?php if ($submitted): ?>
    <div style="background:#F8FAFC;border-radius:10px;padding:12px;margin-bottom:14px">
      <div style="font-size:11px;color:#64748B;margin-bottom:6px;font-weight:600">KẾT QUẢ CỦA BẠN</div>
      <div style="display:flex;align-items:center;gap:10px">
        <span style="font-size:24px;font-weight:800;color:<?= $scoreColor ?>">
          <?= $myScore ?>/<?= $maxScore ?>
        </span>
        <div>
          <div style="font-size:13px;font-weight:700;color:<?= $scoreColor ?>"><?= $scorePct ?>%</div>
          <div style="font-size:11px;color:#94A3B8"><?= $scorePct>=70?'Xuất sắc':($scorePct>=50?'Đạt':'Cần cố gắng') ?></div>
        </div>
      </div>
      <!-- Score bar -->
      <div style="background:#E2E8F0;border-radius:99px;height:5px;margin-top:8px">
        <div style="background:<?= $scoreColor ?>;height:5px;border-radius:99px;width:<?= $scorePct ?>%"></div>
      </div>
    </div>
    <?php else: ?>
    <div style="flex:1"></div>
    <?php endif; ?>

    <!-- Nút hành động -->
    <div>
      <?php if ($canTake): ?>
      <a href="<?= APP_URL ?>/student/quiz.php?action=take&quiz_id=<?= $q['id'] ?>"
         style="display:block;width:100%;padding:10px 0;background:#2563EB;color:#fff;border-radius:8px;
                text-align:center;font-size:14px;font-weight:700;text-decoration:none;box-sizing:border-box">
        <?= $submitted ? '🔄 Làm lại' : '✏️ Bắt đầu làm bài' ?>
      </a>
      <?php elseif ($submitted): ?>
      <div style="padding:10px 0;background:#F0FDF4;border-radius:8px;text-align:center;
                  font-size:14px;font-weight:600;color:#059669">
        ✅ Đã nộp bài
      </div>
      <?php else: ?>
      <div style="padding:10px 0;background:#F1F5F9;border-radius:8px;text-align:center;
                  font-size:14px;font-weight:600;color:#94A3B8">
        🔒 Quiz chưa mở
      </div>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?><!-- /quizzes -->
<?php endif; ?><!-- /courses -->

<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>