<?php
Middleware::requireAdmin();
$pageTitle   = 'Dashboard';
$currentPage = 'admin.dashboard';

$db = Database::getInstance();

// Stats
$totalUsers    = $db->query("SELECT COUNT(*) FROM users WHERE is_active=1")->fetchColumn();
$totalCourses  = $db->query("SELECT COUNT(*) FROM courses WHERE is_active=1")->fetchColumn();
$activeSessions= $db->query("SELECT COUNT(*) FROM class_sessions WHERE status='active'")->fetchColumn();
$totalEnroll   = $db->query("SELECT COUNT(*) FROM course_enrollments")->fetchColumn();
$totalTeachers = $db->query("SELECT COUNT(*) FROM users WHERE role='teacher'")->fetchColumn();
$totalStudents = $db->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn();
$avgAttendance = $db->query("SELECT ROUND(AVG(CASE WHEN status='present' THEN 100 ELSE 0 END),0) FROM attendance_records")->fetchColumn() ?? 0;
$openAlerts    = $db->query("SELECT COUNT(*) FROM alert_logs WHERE status='open'")->fetchColumn();

// Recent interaction logs (activity feed)
$recentActivity = $db->query(
  "SELECT il.*, u.full_name, u.role, cs.title AS session_title,
          c.course_code
   FROM interaction_logs il
   JOIN users u ON il.user_id = u.id
   JOIN class_sessions cs ON il.session_id = cs.id
   JOIN courses c ON cs.course_id = c.id
   ORDER BY il.created_at DESC LIMIT 8"
)->fetchAll();

// Engagement analytics
$engHigh = $db->query("SELECT COUNT(*) FROM engagement_scores WHERE engagement_index >= 70")->fetchColumn();
$engLow  = $db->query("SELECT COUNT(*) FROM engagement_scores WHERE engagement_index < 40")->fetchColumn();
$engAvg  = $db->query("SELECT ROUND(AVG(engagement_index),1) FROM engagement_scores")->fetchColumn() ?? 0;

require_once APP_ROOT . '/views/layouts/header.php';
?>

<div class="page-title">Admin Dashboard</div>
<p class="page-sub">Distribution of core entities across the platform</p>

<!-- ── Stats ── -->
<div class="stat-cards">
  <div class="card stat-card">
    <div class="stat-icon" style="background:#EFF6FF">
      <svg fill="none" viewBox="0 0 24 24" stroke="#2563EB" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
    </div>
    <div>
      <div class="stat-value"><?= number_format($totalUsers) ?></div>
      <div class="stat-label">Total Users</div>
      <div style="font-size:11px;color:#94A3B8;margin-top:2px"><?= $totalTeachers ?> teachers · <?= $totalStudents ?> students</div>
    </div>
  </div>
  <div class="card stat-card">
    <div class="stat-icon" style="background:#F0FDF4">
      <svg fill="none" viewBox="0 0 24 24" stroke="#10B981" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
    </div>
    <div>
      <div class="stat-value"><?= $totalCourses ?></div>
      <div class="stat-label">Total Courses</div>
    </div>
  </div>
  <div class="card stat-card">
    <div class="stat-icon" style="background:#FFF7ED">
      <svg fill="none" viewBox="0 0 24 24" stroke="#F59E0B" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
    </div>
    <div>
      <div class="stat-value"><?= $activeSessions ?></div>
      <div class="stat-label">Active Sessions</div>
    </div>
  </div>
  <div class="card stat-card">
    <div class="stat-icon" style="background:#FDF4FF">
      <svg fill="none" viewBox="0 0 24 24" stroke="#A855F7" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
    </div>
    <div>
      <div class="stat-value"><?= number_format($totalEnroll) ?></div>
      <div class="stat-label">Enrollments</div>
    </div>
  </div>
</div>

<!-- ── Two column: Activity + Upcoming alert ── -->
<div style="display:grid;grid-template-columns:1fr 320px;gap:20px;margin-bottom:24px">

  <!-- Recent Activity -->
  <div class="card">
    <div style="padding:18px 20px 12px;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #F1F5F9">
      <span style="font-weight:700;font-size:14px">Recent Activities</span>
      <a href="#" style="font-size:12px;color:#2563EB;font-weight:600;text-decoration:none">View All</a>
    </div>
    <div class="table-wrap">
      <table>
        <thead><tr><th>User</th><th>Action</th><th>Module</th><th>Time</th></tr></thead>
        <tbody>
        <?php
        $actionMap = [
          'check_in'       => ['Check-in',        '#EFF6FF','#1D4ED8'],
          'submit_quiz'    => ['Submitted Quiz',   '#F0FDF4','#065F46'],
          'discussion'     => ['Discussion',       '#FFF7ED','#92400E'],
          'answer_question'=> ['Answered',         '#FDF4FF','#6B21A8'],
          'other'          => ['Activity',         '#F8FAFC','#475569'],
        ];
        foreach ($recentActivity as $a):
          [$label, $bg, $col] = $actionMap[$a['action_type']] ?? $actionMap['other'];
          $initials = mb_strtoupper(mb_substr($a['full_name'], 0, 2));
          $colors   = ['admin'=>'#EF4444','teacher'=>'#2563EB','student'=>'#10B981'];
          $avatarBg = $colors[$a['role']] ?? '#64748B';
          $timeAgo  = '';
          $diff = time() - strtotime($a['created_at']);
          if ($diff < 60) $timeAgo = 'just now';
          elseif ($diff < 3600) $timeAgo = round($diff/60) . ' mins ago';
          elseif ($diff < 86400) $timeAgo = round($diff/3600) . ' hrs ago';
          else $timeAgo = date('d/m', strtotime($a['created_at']));
        ?>
        <tr>
          <td>
            <div style="display:flex;align-items:center;gap:9px">
              <div style="width:30px;height:30px;border-radius:50%;background:<?= $avatarBg ?>;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#fff;flex-shrink:0">
                <?= $initials ?>
              </div>
              <span style="font-weight:600;font-size:13px"><?= htmlspecialchars($a['full_name']) ?></span>
            </div>
          </td>
          <td>
            <span style="background:<?= $bg ?>;color:<?= $col ?>;padding:3px 9px;border-radius:20px;font-size:11px;font-weight:600">
              <?= $label ?>
            </span>
          </td>
          <td style="font-size:12px;color:#64748B"><?= htmlspecialchars($a['course_code']) ?></td>
          <td style="font-size:12px;color:#94A3B8;white-space:nowrap"><?= $timeAgo ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($recentActivity)): ?>
        <tr><td colspan="4" style="text-align:center;color:#94A3B8;padding:30px">No recent activity</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Alert highlight card -->
  <?php
  $highlight = $db->query(
    "SELECT al.*, u.full_name, c.course_name FROM alert_logs al
     JOIN users u ON al.student_id = u.id
     JOIN courses c ON al.course_id = c.id
     WHERE al.status='open' ORDER BY al.created_at DESC LIMIT 1"
  )->fetch();
  ?>
  <?php if ($highlight): ?>
  <div class="card" style="background:linear-gradient(140deg,#1D4ED8,#2563EB);color:#fff;padding:24px;display:flex;flex-direction:column;gap:14px">
    <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;opacity:.7">⚠ Latest Alert</div>
    <div style="font-size:16px;font-weight:700;line-height:1.35"><?= htmlspecialchars($highlight['full_name']) ?></div>
    <div style="font-size:13px;opacity:.85"><?= htmlspecialchars($highlight['course_name']) ?></div>
    <div style="font-size:12px;background:rgba(255,255,255,.15);border-radius:8px;padding:10px 14px;line-height:1.5">
      <?= htmlspecialchars(mb_substr($highlight['alert_message'], 0, 90)) ?>...
    </div>
    <a href="<?= APP_URL ?>/admin/alerts.php" style="background:rgba(255,255,255,.2);color:#fff;padding:9px 16px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;text-align:center;border:1px solid rgba(255,255,255,.3)">
      View All Alerts (<?= $openAlerts ?>)
    </a>
  </div>
  <?php else: ?>
  <div class="card" style="padding:24px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:10px;text-align:center">
    <div style="font-size:36px">✅</div>
    <div style="font-weight:700;color:#0F172A">No open alerts</div>
    <div style="font-size:13px;color:#64748B">All students are on track</div>
  </div>
  <?php endif; ?>
</div>

<!-- ── System Overview ── -->
<div style="margin-bottom:24px">
  <div style="font-weight:700;font-size:15px;margin-bottom:4px">System Overview</div>
  <div style="font-size:13px;color:#64748B;margin-bottom:16px">Distribution of core entities across the platform</div>
  <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px">

    <?php
    $overviewCards = [
      ['label'=>'Teachers','count'=>$totalTeachers,'sub'=>'Instructional staff managing courses',
       'color'=>'#2563EB','bg'=>'#EFF6FF','badge'=>'Active: '.$totalTeachers,'bar'=>min(100,round($totalTeachers/10*100))],
      ['label'=>'Students','count'=>$totalStudents,'sub'=>'Enrolled participants tracking engagement',
       'color'=>'#10B981','bg'=>'#F0FDF4','badge'=>'Active: '.$totalStudents,'bar'=>min(100,round($avgAttendance))],
      ['label'=>'Courses','count'=>$totalCourses,'sub'=>'Academic modules and curriculum tracks',
       'color'=>'#F59E0B','bg'=>'#FFF7ED','badge'=>'Departments: 3','bar'=>60],
      ['label'=>'Sessions','count'=>$activeSessions,'sub'=>'Individual class meetings and events',
       'color'=>'#A855F7','bg'=>'#FDF4FF','badge'=>'Daily avg: '.round($activeSessions),'bar'=>80],
    ];
    foreach ($overviewCards as $oc):
    ?>
    <div class="card" style="padding:18px">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px">
        <div>
          <div style="font-size:11px;font-weight:700;color:<?= $oc['color'] ?>;background:<?= $oc['bg'] ?>;padding:2px 8px;border-radius:20px;margin-bottom:8px">
            <?= $oc['badge'] ?>
          </div>
          <div style="font-size:26px;font-weight:800;color:#0F172A;line-height:1"><?= $oc['count'] ?></div>
          <div style="font-size:13px;font-weight:700;color:#374151;margin-top:3px"><?= $oc['label'] ?></div>
        </div>
      </div>
      <div style="font-size:11px;color:#94A3B8;margin-bottom:10px"><?= $oc['sub'] ?></div>
      <div style="background:#F1F5F9;border-radius:99px;height:5px">
        <div style="background:<?= $oc['color'] ?>;height:5px;border-radius:99px;width:<?= $oc['bar'] ?>%"></div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- ── Engagement Analytics ── -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
  <div class="card" style="padding:24px">
    <div style="font-weight:700;font-size:15px;margin-bottom:5px">Engagement Analytics</div>
    <div style="font-size:13px;color:#64748B;margin-bottom:20px">Aggregate engagement distribution across all courses</div>
    <div style="display:flex;gap:20px;flex-wrap:wrap">
      <div style="text-align:center">
        <div style="font-size:32px;font-weight:800;color:#2563EB"><?= $engAvg ?>%</div>
        <div style="font-size:12px;color:#64748B">Avg Engagement</div>
      </div>
      <div style="text-align:center">
        <div style="font-size:32px;font-weight:800;color:#10B981"><?= $engHigh ?></div>
        <div style="font-size:12px;color:#64748B">High Performers</div>
      </div>
      <div style="text-align:center">
        <div style="font-size:32px;font-weight:800;color:#EF4444"><?= $engLow ?></div>
        <div style="font-size:12px;color:#64748B">At Risk</div>
      </div>
    </div>
  </div>

  <div class="card" style="padding:24px;background:linear-gradient(135deg,#1E293B,#0F172A);color:#fff">
    <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#F59E0B;margin-bottom:8px">🔍 Latest Insight</div>
    <div style="font-size:16px;font-weight:700;margin-bottom:10px">Predictive Attendance Model</div>
    <div style="font-size:13px;color:#94A3B8;line-height:1.6">
      <?= $openAlerts ?> student<?= $openAlerts != 1 ? 's are' : ' is' ?> currently flagged. Monitor engagement scores to prevent further absences.
    </div>
    <a href="<?= APP_URL ?>/admin/alerts.php" style="display:inline-block;margin-top:16px;background:#2563EB;color:#fff;padding:8px 16px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600">
      View Alerts →
    </a>
  </div>
</div>

<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>