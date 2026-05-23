<?php
// views/student/attendance.php
// Hiển thị lịch sử điểm danh + form check-in OTP
// Dữ liệu được truyền từ public/student/attendance.php:
//   $courses, $courseId, $currentCourse, $records, $stats
require_once APP_ROOT . '/views/layouts/header.php';
?>

<div class="page-title">Attendance History</div>
<p class="page-sub">Lịch sử điểm danh của bạn theo từng môn học</p>

<?php if (empty($courses)): ?>
<div class="alert alert-warning">Bạn chưa được ghi danh vào môn học nào. Liên hệ Admin để được ghi danh.</div>

<?php else: ?>

<!-- ── Chọn môn học ───────────────────────────────────── -->
<div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;margin-bottom:20px">
  <span style="font-size:13px;font-weight:600;color:#374151">Môn học:</span>
  <?php foreach ($courses as $c): ?>
  <a href="<?= APP_URL ?>/student/attendance.php?course_id=<?= $c['id'] ?>"
     style="padding:5px 14px;border-radius:20px;font-size:13px;font-weight:600;text-decoration:none;
            background:<?= (int)$c['id']===$courseId?'#2563EB':'#F1F5F9' ?>;
            color:<?= (int)$c['id']===$courseId?'#fff':'#374151' ?>">
    <?= htmlspecialchars($c['course_code']) ?>
  </a>
  <?php endforeach; ?>
</div>

<?php if ($currentCourse): ?>

<!-- ── Form Check-in OTP ─────────────────────────────── -->
<?php
$activeSessions = array_filter($records, fn($r) => $r['session_status'] === 'active');
?>
<div class="card" style="padding:20px;margin-bottom:22px;border-left:4px solid #2563EB">
  <div style="font-weight:700;font-size:14px;color:#0F172A;margin-bottom:4px">⚡ Điểm danh nhanh</div>
  <div style="font-size:13px;color:#64748B;margin-bottom:14px">Nhập mã OTP giảng viên cung cấp để điểm danh buổi học đang diễn ra</div>

  <?php if (empty($activeSessions)): ?>
  <div style="font-size:13px;color:#94A3B8;padding:10px;background:#F8FAFC;border-radius:8px">
    📋 Hiện không có buổi học nào đang diễn ra trong môn này.
  </div>
  <?php else: ?>
  <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
    <div>
      <label style="font-size:12px;color:#64748B;display:block;margin-bottom:5px">Buổi học</label>
      <select id="checkin-session"
              style="padding:9px 12px;border:1.5px solid #E2E8F0;border-radius:8px;font-size:14px;min-width:220px;background:#fff">
        <option value="">-- Chọn buổi học --</option>
        <?php foreach ($activeSessions as $r): ?>
        <option value="<?= $r['session_id'] ?>">
          <?= htmlspecialchars($r['session_date']) ?> – <?= htmlspecialchars($r['title'] ?? 'Buổi học') ?>
          <?php if ($r['attendance_status'] === 'present'): ?>(Đã điểm danh)<?php endif; ?>
        </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label style="font-size:12px;color:#64748B;display:block;margin-bottom:5px">Mã OTP</label>
      <input type="text" id="otp-input" placeholder="Nhập mã 6 ký tự" maxlength="20"
             style="padding:9px 12px;border:1.5px solid #E2E8F0;border-radius:8px;font-size:14px;
                    width:150px;text-transform:uppercase;letter-spacing:2px;text-align:center">
    </div>
    <button onclick="doCheckin()" id="checkin-btn"
            style="padding:9px 22px;background:#2563EB;color:#fff;border:none;border-radius:8px;
                   font-size:14px;font-weight:600;cursor:pointer">
      ✓ Điểm danh
    </button>
  </div>
  <div id="checkin-msg" style="margin-top:10px;font-size:13px;font-weight:600;display:none;
       padding:8px 14px;border-radius:8px"></div>
  <?php endif; ?>
</div>

<!-- ── Thống kê tổng quan ─────────────────────────────── -->
<?php if (!empty($stats) && (int)($stats['total_sessions'] ?? 0) > 0): ?>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:12px;margin-bottom:22px">

  <?php
  $statItems = [
    ['label'=>'Tổng buổi',  'value'=>$stats['total_sessions']??0,  'icon'=>'📅', 'color'=>'#2563EB', 'bg'=>'#EFF6FF'],
    ['label'=>'Có mặt',     'value'=>$stats['present_count']??0,   'icon'=>'✅', 'color'=>'#059669', 'bg'=>'#F0FDF4'],
    ['label'=>'Vắng mặt',   'value'=>$stats['absent_count']??0,    'icon'=>'❌', 'color'=>'#DC2626', 'bg'=>'#FEF2F2'],
    ['label'=>'Đi trễ',     'value'=>$stats['late_count']??0,      'icon'=>'⏰', 'color'=>'#D97706', 'bg'=>'#FFFBEB'],
    ['label'=>'Có phép',    'value'=>$stats['excused_count']??0,   'icon'=>'📝', 'color'=>'#7C3AED', 'bg'=>'#F5F3FF'],
  ];
  $total = max(1, (int)($stats['total_sessions'] ?? 1));
  $presentPct = round(($stats['present_count']??0) / $total * 100);
  ?>

  <?php foreach ($statItems as $s): ?>
  <div class="card" style="padding:16px;text-align:center;background:<?= $s['bg'] ?>">
    <div style="font-size:22px;margin-bottom:4px"><?= $s['icon'] ?></div>
    <div style="font-size:22px;font-weight:800;color:<?= $s['color'] ?>"><?= $s['value'] ?></div>
    <div style="font-size:11px;color:#64748B;margin-top:2px"><?= $s['label'] ?></div>
  </div>
  <?php endforeach; ?>

  <div class="card" style="padding:16px;text-align:center;background:#F8FAFC">
    <div style="font-size:22px;margin-bottom:4px">📊</div>
    <div style="font-size:22px;font-weight:800;color:<?= $presentPct>=80?'#059669':($presentPct>=60?'#D97706':'#DC2626') ?>"><?= $presentPct ?>%</div>
    <div style="font-size:11px;color:#64748B;margin-top:2px">Tỷ lệ có mặt</div>
  </div>
</div>
<?php endif; ?>

<!-- ── Bảng lịch sử ───────────────────────────────────── -->
<div class="card" style="overflow:hidden">
  <div style="padding:16px 20px;border-bottom:1px solid #F1F5F9;font-weight:700;font-size:14px;color:#0F172A">
    Lịch sử buổi học – <?= htmlspecialchars($currentCourse['course_name']) ?>
  </div>
  <div style="overflow-x:auto">
    <table style="width:100%;border-collapse:collapse;font-size:14px">
      <thead>
        <tr style="background:#F8FAFC">
          <th style="padding:11px 16px;text-align:left;font-size:12px;font-weight:600;color:#64748B;white-space:nowrap">Ngày</th>
          <th style="padding:11px 16px;text-align:left;font-size:12px;font-weight:600;color:#64748B">Buổi học</th>
          <th style="padding:11px 16px;text-align:left;font-size:12px;font-weight:600;color:#64748B">Giờ</th>
          <th style="padding:11px 16px;text-align:left;font-size:12px;font-weight:600;color:#64748B">Trạng thái buổi</th>
          <th style="padding:11px 16px;text-align:left;font-size:12px;font-weight:600;color:#64748B">Điểm danh</th>
          <th style="padding:11px 16px;text-align:left;font-size:12px;font-weight:600;color:#64748B">Check-in lúc</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($records)): ?>
        <tr><td colspan="6" style="padding:30px;text-align:center;color:#94A3B8">Chưa có buổi học nào.</td></tr>
        <?php else: ?>
        <?php foreach ($records as $r):
          // Badge điểm danh
          [$attBg, $attText] = match($r['attendance_status'] ?? null) {
            'present' => ['#D1FAE5;color:#065F46', '✅ Có mặt'],
            'absent'  => ['#FEE2E2;color:#991B1B', '❌ Vắng'],
            'late'    => ['#FEF3C7;color:#92400E', '⏰ Trễ'],
            'excused' => ['#EDE9FE;color:#4C1D95', '📝 Có phép'],
            default   => ['#F1F5F9;color:#475569',  '— Chưa ghi'],
          };
          // Badge trạng thái buổi
          [$sesBg, $sesText] = match($r['session_status']) {
            'active'   => ['#D1FAE5;color:#065F46', 'Đang học'],
            'ended'    => ['#F1F5F9;color:#475569',  'Kết thúc'],
            'planned'  => ['#DBEAFE;color:#1E40AF',  'Sắp diễn ra'],
            'cancelled'=> ['#FEE2E2;color:#991B1B',  'Đã hủy'],
            default    => ['#F1F5F9;color:#475569',   $r['session_status']],
          };
        ?>
        <tr style="border-bottom:1px solid #F8FAFC;transition:background .12s" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background=''">
          <td style="padding:12px 16px;color:#0F172A;white-space:nowrap"><?= htmlspecialchars($r['session_date']) ?></td>
          <td style="padding:12px 16px;color:#374151"><?= htmlspecialchars($r['title'] ?? 'Buổi học') ?></td>
          <td style="padding:12px 16px;color:#64748B;white-space:nowrap"><?= substr($r['start_time'],0,5) ?> – <?= substr($r['end_time'],0,5) ?></td>
          <td style="padding:12px 16px">
            <span style="background:<?= $sesBg ?>;padding:3px 10px;border-radius:99px;font-size:12px;font-weight:600"><?= $sesText ?></span>
          </td>
          <td style="padding:12px 16px">
            <span style="background:<?= $attBg ?>;padding:3px 10px;border-radius:99px;font-size:12px;font-weight:600"><?= $attText ?></span>
          </td>
          <td style="padding:12px 16px;color:#64748B;font-size:13px">
            <?= $r['checked_in_at'] ? date('H:i', strtotime($r['checked_in_at'])) : '—' ?>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php endif; ?><!-- /currentCourse -->
<?php endif; ?><!-- /courses -->

<script>
async function doCheckin() {
  const sessionId = document.getElementById('checkin-session')?.value;
  const otp       = document.getElementById('otp-input')?.value.trim();
  const msgEl     = document.getElementById('checkin-msg');
  const btn       = document.getElementById('checkin-btn');

  if (!sessionId) { showMsg('Vui lòng chọn buổi học.', false); return; }
  if (!otp)        { showMsg('Vui lòng nhập mã OTP.',   false); return; }

  btn.disabled    = true;
  btn.textContent = '⏳ Đang xử lý...';

  try {
    const res  = await fetch('<?= APP_URL ?>/student/attendance.php?action=checkin', {
      method:  'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body:    `session_id=${encodeURIComponent(sessionId)}&otp_code=${encodeURIComponent(otp)}`
    });
    const data = await res.json();
    showMsg(data.message, data.success);
    if (data.success) {
      setTimeout(() => location.reload(), 1500);
    }
  } catch {
    showMsg('Lỗi kết nối. Vui lòng thử lại.', false);
  } finally {
    btn.disabled    = false;
    btn.textContent = '✓ Điểm danh';
  }
}

function showMsg(text, success) {
  const el = document.getElementById('checkin-msg');
  el.textContent          = text;
  el.style.display        = 'block';
  el.style.background     = success ? '#D1FAE5' : '#FEE2E2';
  el.style.color          = success ? '#065F46' : '#991B1B';
}
</script>

<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>