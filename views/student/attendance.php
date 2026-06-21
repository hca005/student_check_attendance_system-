<?php
// views/student/attendance.php
// Hiển thị lịch sử điểm danh + form check-in OTP
// Dữ liệu được truyền từ public/student/attendance.php:
//   $courses, $courseId, $currentCourse, $records, $stats
require_once APP_ROOT . '/views/layouts/header.php';
?>

<div class="admin-page-title">
  <div class="left">
    <h1>Attendance History</h1>
    <p>Your attendance record per course, plus quick check-in</p>
  </div>
</div>

<?php if (empty($courses)): ?>
<div class="alert alert-warning">You're not enrolled in any course yet. Contact your Admin to get enrolled.</div>

<?php else: ?>

<div class="course-tabs">
  <?php foreach ($courses as $c): ?>
  <a href="<?= APP_URL ?>/student/attendance.php?course_id=<?= $c['id'] ?>"
     class="course-tab <?= (int)$c['id'] === $courseId ? 'active' : '' ?>">
    <?= htmlspecialchars($c['course_code']) ?>
  </a>
  <?php endforeach; ?>
</div>

<?php if ($currentCourse):
  $activeSessions = array_filter($records, fn($r) => $r['session_status'] === 'active');
?>

<div class="card" style="padding:20px;margin-bottom:20px;border-left:4px solid var(--primary)">
  <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px">
    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="var(--primary)" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
    <div style="font-weight:700;font-size:14px">Quick check-in</div>
  </div>
  <div style="font-size:13px;color:var(--text-muted);margin-bottom:14px">Enter the OTP your teacher shared for the session in progress</div>

  <?php if (empty($activeSessions)): ?>
  <div class="empty-state" style="padding:10px 0">No session is currently in progress for this course.</div>
  <?php else: ?>
  <form class="otp-form" onsubmit="return false">
    <div class="form-group" style="margin:0">
      <label>Session</label>
      <select id="checkin-session">
        <option value="">-- Choose a session --</option>
        <?php foreach ($activeSessions as $r): ?>
        <option value="<?= $r['session_id'] ?>">
          <?= htmlspecialchars($r['session_date']) ?> — <?= htmlspecialchars($r['title'] ?? 'Session') ?>
          <?= $r['attendance_status'] === 'present' ? '(already checked in)' : '' ?>
        </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group" style="margin:0">
      <label>OTP code</label>
      <input type="text" id="otp-input" placeholder="CODE" maxlength="20">
    </div>
    <button type="button" class="btn btn-primary" onclick="doCheckin()">
      <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
      Check in
    </button>
  </form>
  <div id="checkin-msg" class="alert" style="display:none;margin-top:12px;margin-bottom:0"></div>
  <?php endif; ?>
</div>

<?php if (!empty($stats) && (int)($stats['total_sessions'] ?? 0) > 0):
  $total = max(1, (int)$stats['total_sessions']);
  $presentPct = round(($stats['present_count'] ?? 0) / $total * 100);
?>
<div class="stat-cards" style="grid-template-columns:repeat(5, minmax(0,1fr))">
  <div class="card stat-card">
    <div class="stat-icon" style="background:#EFF6FF"><svg fill="none" viewBox="0 0 24 24" stroke="#2563EB" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div>
    <div><div class="stat-value"><?= $stats['total_sessions'] ?? 0 ?></div><div class="stat-label">Total sessions</div></div>
  </div>
  <div class="card stat-card">
    <div class="stat-icon" style="background:#F0FDF4"><svg fill="none" viewBox="0 0 24 24" stroke="#10B981" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg></div>
    <div><div class="stat-value" style="color:#10B981"><?= $stats['present_count'] ?? 0 ?></div><div class="stat-label">Present</div></div>
  </div>
  <div class="card stat-card">
    <div class="stat-icon" style="background:#FEF2F2"><svg fill="none" viewBox="0 0 24 24" stroke="#EF4444" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg></div>
    <div><div class="stat-value" style="color:#EF4444"><?= $stats['absent_count'] ?? 0 ?></div><div class="stat-label">Absent</div></div>
  </div>
  <div class="card stat-card">
    <div class="stat-icon" style="background:#FFFBEB"><svg fill="none" viewBox="0 0 24 24" stroke="#F59E0B" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
    <div><div class="stat-value" style="color:#F59E0B"><?= $stats['late_count'] ?? 0 ?></div><div class="stat-label">Late</div></div>
  </div>
  <div class="card stat-card">
    <div class="score-ring" style="--ring-size:48px;--pct:<?= $presentPct ?>;--ring-color:<?= $presentPct>=80?'#10B981':($presentPct>=60?'#F59E0B':'#EF4444') ?>">
      <span class="score-ring-label" style="font-size:12px"><?= $presentPct ?>%</span>
    </div>
    <div><div class="stat-label" style="margin-top:4px">Attendance rate</div></div>
  </div>
</div>
<?php endif; ?>

<div class="card">
  <div style="padding:16px 18px;border-bottom:1px solid var(--line);font-weight:700;font-size:14px">
    Session log — <?= htmlspecialchars($currentCourse['course_name']) ?>
  </div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Date</th><th>Session</th><th>Time</th><th>Status</th><th>Checked in at</th></tr></thead>
      <tbody>
        <?php if (empty($records)): ?>
        <tr><td colspan="5"><div class="empty-state">No sessions recorded yet.</div></td></tr>
        <?php else: foreach ($records as $r):
          $badge = match($r['attendance_status'] ?? null) {
            'present' => ['badge-success', 'Present'],
            'absent'  => ['badge-danger',  'Absent'],
            'late'    => ['badge-warning', 'Late'],
            'excused' => ['badge-primary', 'Excused'],
            default   => ['badge-gray',    'Not recorded'],
          };
        ?>
        <tr>
          <td><?= htmlspecialchars($r['session_date']) ?></td>
          <td><?= htmlspecialchars($r['title'] ?? 'Session') ?></td>
          <td><?= substr($r['start_time'],0,5) ?>–<?= substr($r['end_time'],0,5) ?></td>
          <td><span class="badge <?= $badge[0] ?>"><?= $badge[1] ?></span></td>
          <td><?= $r['checked_in_at'] ? date('H:i', strtotime($r['checked_in_at'])) : '—' ?></td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php endif; ?>
<?php endif; ?>

<script>
async function doCheckin() {
  const sessionId = document.getElementById('checkin-session')?.value;
  const otp       = document.getElementById('otp-input')?.value.trim();

  if (!sessionId) { showMsg('Please choose a session.', false); return; }
  if (!otp)        { showMsg('Please enter the OTP code.', false); return; }

  try {
    const res  = await fetch('<?= APP_URL ?>/student/attendance.php?action=checkin', {
      method:  'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body:    `session_id=${encodeURIComponent(sessionId)}&otp_code=${encodeURIComponent(otp)}`
    });
    const data = await res.json();
    showMsg(data.message, data.success);
    if (data.success) setTimeout(() => location.reload(), 1200);
  } catch {
    showMsg('Connection error. Please try again.', false);
  }
}

function showMsg(text, success) {
  const el = document.getElementById('checkin-msg');
  el.textContent = text;
  el.className   = 'alert ' + (success ? 'alert-success' : 'alert-danger');
  el.style.display = 'flex';
}
</script>

<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>