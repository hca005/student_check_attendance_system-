<?php
Middleware::requireAdmin();
$pageTitle = 'Admin Dashboard';
$currentPage = 'admin.dashboard';

$db = Database::getInstance();

$totalUsers = (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalCourses = (int)$db->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$activeSessions = (int)$db->query("SELECT COUNT(*) FROM class_sessions WHERE status='active'")->fetchColumn();
$totalEnrollments = (int)$db->query("SELECT COUNT(*) FROM enrollments")->fetchColumn();
$totalTeachers = (int)$db->query("SELECT COUNT(*) FROM users WHERE role='teacher'")->fetchColumn();
$totalStudents = (int)$db->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn();
$avgEngagement = (float)($db->query("SELECT ROUND(AVG(engagement_index), 0) FROM engagement_scores")->fetchColumn() ?: 0);
$openAlerts = (int)$db->query("SELECT COUNT(*) FROM alerts WHERE status='pending'")->fetchColumn();
$avgAttendance = (float)($db->query("SELECT ROUND(AVG(CASE WHEN status='present' THEN 100 ELSE 0 END), 0) FROM attendance_records")->fetchColumn() ?: 0);

$recentActivities = $db->query(
    "SELECT
        il.action_type,
        il.created_at,
        u.full_name,
        c.course_code
     FROM interaction_logs il
     JOIN users u ON u.id = il.user_id
     LEFT JOIN class_sessions cs ON cs.id = il.session_id
     LEFT JOIN courses c ON c.id = cs.course_id
     ORDER BY il.created_at DESC
     LIMIT 8"
)->fetchAll(PDO::FETCH_ASSOC);

$upcoming = $db->query(
    "SELECT cs.title, cs.session_date, cs.start_time, cs.end_time, c.course_name
     FROM class_sessions cs
     JOIN courses c ON c.id = cs.course_id
     WHERE cs.status IN ('upcoming', 'active')
     ORDER BY cs.session_date ASC, cs.start_time ASC
     LIMIT 1"
)->fetch(PDO::FETCH_ASSOC);

require APP_ROOT . '/views/layouts/header.php';

function activity_label(string $type): string
{
    return match ($type) {
        'check_in' => 'Logged Attendance',
        'submit_quiz' => 'Created Quiz',
        'discussion' => 'Discussion Activity',
        'answer_question' => 'Answered Question',
        default => 'Session Update',
    };
}
?>

<div class="admin-page-title">
  <div class="left">
    <h1>Admin Dashboard</h1>
    <p>Central overview for users, courses, sessions, and engagement risk.</p>
  </div>
</div>

<div class="stat-cards">
  <div class="card stat-card">
    <div class="stat-icon" style="background:#eff6ff">
      <svg fill="none" viewBox="0 0 24 24" stroke="#2563eb" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
    </div>
    <div><div class="stat-label">Total Users</div><div class="stat-value"><?= $totalUsers ?></div></div>
  </div>
  <div class="card stat-card">
    <div class="stat-icon" style="background:#ecfdf5">
      <svg fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
    </div>
    <div><div class="stat-label">Total Courses</div><div class="stat-value"><?= $totalCourses ?></div></div>
  </div>
  <div class="card stat-card">
    <div class="stat-icon" style="background:#fffbeb">
      <svg fill="none" viewBox="0 0 24 24" stroke="#f59e0b" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/></svg>
    </div>
    <div><div class="stat-label">Active Sessions</div><div class="stat-value"><?= $activeSessions ?></div></div>
  </div>
  <div class="card stat-card">
    <div class="stat-icon" style="background:#eff6ff">
      <svg fill="none" viewBox="0 0 24 24" stroke="#2563eb" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
    </div>
    <div><div class="stat-label">Enrollments</div><div class="stat-value"><?= $totalEnrollments ?></div></div>
  </div>
</div>

<div class="dashboard-grid">
  <div class="card">
    <div style="padding:14px 14px 0;display:flex;justify-content:space-between;align-items:center">
      <h3 style="margin:0;font-size:15px">Recent Activities</h3>
      <a href="<?= APP_URL ?>/index.php?page=admin_engagement_scores" style="font-size:12px;color:#2563eb;font-weight:600;text-decoration:none">View All</a>
    </div>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>User</th>
            <th>Action</th>
            <th>Module</th>
            <th>Time</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($recentActivities as $row): ?>
          <tr>
            <td><?= htmlspecialchars((string)$row['full_name']) ?></td>
            <td><span class="badge badge-primary"><?= htmlspecialchars(activity_label((string)$row['action_type'])) ?></span></td>
            <td><?= htmlspecialchars((string)($row['course_code'] ?: 'N/A')) ?></td>
            <td><?= htmlspecialchars(date('H:i', strtotime((string)$row['created_at']))) ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($recentActivities)): ?>
          <tr><td colspan="4" style="text-align:center;color:#64748b;padding:22px">No activity yet.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="card dashboard-right-card">
    <div style="font-size:11px;opacity:.8;text-transform:uppercase;font-weight:700;letter-spacing:.06em">Upcoming Session</div>
    <?php if ($upcoming): ?>
      <div style="font-size:18px;font-weight:800;margin-top:8px;line-height:1.3"><?= htmlspecialchars((string)($upcoming['title'] ?: 'Class Session')) ?></div>
      <div style="font-size:13px;margin-top:6px;opacity:.92"><?= htmlspecialchars((string)$upcoming['course_name']) ?></div>
      <div style="font-size:12px;margin-top:10px;opacity:.88">
        <?= htmlspecialchars((string)$upcoming['session_date']) ?> |
        <?= substr((string)$upcoming['start_time'], 0, 5) ?> - <?= substr((string)$upcoming['end_time'], 0, 5) ?>
      </div>
    <?php else: ?>
      <div style="font-size:14px;margin-top:10px;opacity:.9">No upcoming sessions scheduled.</div>
    <?php endif; ?>
    <a href="<?= APP_URL ?>/index.php?page=admin_sessions" class="btn btn-sm" style="margin-top:14px;background:rgba(255,255,255,.2);border-color:rgba(255,255,255,.3);color:#fff">Monitor Real-time</a>
  </div>
</div>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
  <div>
    <div style="font-size:20px;font-weight:800">System Overview</div>
    <div style="font-size:13px;color:#64748b">Distribution of core entities across the platform</div>
  </div>
</div>

<div class="system-overview-grid">
  <div class="card overview-box">
    <div class="overview-title">Teachers</div>
    <div class="overview-sub">Instructional staff managing courses.</div>
    <div class="stat-value"><?= $totalTeachers ?></div>
  </div>
  <div class="card overview-box">
    <div class="overview-title">Students</div>
    <div class="overview-sub">Enrolled participants tracking engagement.</div>
    <div class="stat-value"><?= $totalStudents ?></div>
  </div>
  <div class="card overview-box">
    <div class="overview-title">Courses</div>
    <div class="overview-sub">Academic modules and curriculum tracks.</div>
    <div class="stat-value"><?= $totalCourses ?></div>
  </div>
  <div class="card overview-box">
    <div class="overview-title">Sessions</div>
    <div class="overview-sub">Individual class meetings and events.</div>
    <div class="stat-value"><?= $activeSessions ?></div>
  </div>
</div>

<div class="insight-grid">
  <div class="card insight-tile">
    <h3 style="margin:0 0 8px;font-size:24px">Engagement Analytics</h3>
    <p style="margin:0 0 14px;color:#64748b">
      Aggregate engagement overview across all courses. Current attendance average is <strong><?= $avgAttendance ?>%</strong>.
    </p>
    <div style="display:flex;gap:10px;flex-wrap:wrap">
      <span class="badge badge-primary" style="padding:8px 12px;font-size:13px"><?= $avgEngagement ?>% STEM ENGAGEMENT</span>
      <span class="badge badge-warning" style="padding:8px 12px;font-size:13px"><?= $openAlerts ?> OPEN ALERTS</span>
    </div>
  </div>
  <div class="card insight-hero">
    <div style="position:relative;z-index:1">
      <div style="font-size:10px;letter-spacing:.08em;text-transform:uppercase;color:#334155;font-weight:700">Latest Insight</div>
      <div style="font-size:20px;font-weight:800;margin-top:8px">Predictive Attendance Model</div>
      <div style="margin-top:8px;color:#475569;font-size:13px">Open alerts are currently concentrated in low-attendance patterns.</div>
    </div>
  </div>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>
