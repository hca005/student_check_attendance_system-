<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle ?? APP_NAME) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
<style>
/* ── Layout shell ── */
.app-shell { display:flex; min-height:100vh; }

/* ── SIDEBAR ── */
.sidebar {
  width:var(--sidebar-w,240px); background:#0F172A; color:#94A3B8;
  display:flex; flex-direction:column; flex-shrink:0;
  position:fixed; top:0; left:0; height:100vh; z-index:200;
  transition:transform .25s;
}
.sidebar-header {
  display:flex; align-items:center; gap:10px;
  padding:18px 18px 14px; border-bottom:1px solid rgba(255,255,255,.06);
}
.sidebar-header img { width:36px; height:36px; border-radius:9px; }
.sidebar-brand-text { line-height:1.25; }
.sidebar-brand-text strong { display:block; font-size:13px; font-weight:700; color:#F8FAFC; }
.sidebar-brand-text span   { font-size:10px; color:#475569; text-transform:uppercase; letter-spacing:.06em; }

.sidebar-user {
  padding:12px 18px;
  display:flex; align-items:center; gap:10px;
  border-bottom:1px solid rgba(255,255,255,.06);
}
.sidebar-user .role-badge {
  font-size:10px; font-weight:700; padding:2px 8px; border-radius:20px;
}
.role-admin   { background:rgba(239,68,68,.15);  color:#FCA5A5; }
.role-teacher { background:rgba(37,99,235,.15);  color:#93C5FD; }
.role-student { background:rgba(16,185,129,.15); color:#6EE7B7; }
.sidebar-user-info strong { font-size:12.5px; color:#E2E8F0; font-weight:600; display:block; }

.sidebar-nav { flex:1; overflow-y:auto; padding:10px 0; }
.nav-section {
  padding:10px 18px 4px;
  font-size:10px; font-weight:700; color:#334155;
  text-transform:uppercase; letter-spacing:.08em;
}
.nav-item {
  display:flex; align-items:center; gap:10px;
  padding:9px 18px; color:#64748B;
  text-decoration:none; font-size:13px; font-weight:500;
  transition:background .15s, color .15s;
  position:relative;
}
.nav-item svg { flex-shrink:0; width:16px; height:16px; }
.nav-item:hover { background:rgba(255,255,255,.05); color:#CBD5E1; }
.nav-item.active {
  background:rgba(37,99,235,.18); color:#60A5FA;
  border-right:3px solid #3B82F6;
}
.nav-item.active svg { color:#60A5FA; }
.nav-badge {
  margin-left:auto; background:#EF4444; color:#fff;
  font-size:10px; font-weight:700; padding:1px 7px; border-radius:20px;
}

.sidebar-footer {
  padding:14px 18px; border-top:1px solid rgba(255,255,255,.06);
  display:flex; align-items:center; gap:10px;
}
.sidebar-footer .avatar { width:32px; height:32px; font-size:12px; }
.sidebar-footer-info { flex:1; overflow:hidden; }
.sidebar-footer-info strong { font-size:12px; color:#E2E8F0; display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.sidebar-footer-info span { font-size:11px; color:#475569; }
.logout-btn {
  background:none; border:none; cursor:pointer; color:#475569;
  padding:5px; border-radius:6px; transition:color .15s, background .15s;
  display:flex; align-items:center;
}
.logout-btn:hover { color:#EF4444; background:rgba(239,68,68,.1); }

/* ── MAIN ── */
.main-content { margin-left:240px; flex:1; display:flex; flex-direction:column; min-height:100vh; }

/* ── TOPBAR ── */
.topbar {
  background:#fff; border-bottom:1px solid #E2E8F0;
  padding:0 24px; height:60px;
  display:flex; align-items:center; gap:16px;
  position:sticky; top:0; z-index:100;
}
.topbar-search {
  flex:1; max-width:380px;
  display:flex; align-items:center; gap:10px;
  background:#F8FAFC; border:1.5px solid #E2E8F0; border-radius:10px;
  padding:7px 14px; color:#94A3B8;
}
.topbar-search input {
  border:none; background:none; outline:none; font-size:13px; color:#374151;
  width:100%; font-family:inherit;
}
.topbar-search input::placeholder { color:#94A3B8; }
.topbar-actions { margin-left:auto; display:flex; align-items:center; gap:10px; }
.icon-btn {
  width:38px; height:38px; border-radius:10px; border:1.5px solid #E2E8F0;
  background:#fff; cursor:pointer; display:flex; align-items:center; justify-content:center;
  color:#64748B; transition:border-color .15s, color .15s;
}
.icon-btn:hover { border-color:#2563EB; color:#2563EB; }
.icon-btn.has-badge { position:relative; }
.icon-btn .dot {
  position:absolute; top:6px; right:6px; width:8px; height:8px;
  background:#EF4444; border-radius:50%; border:2px solid #fff;
}
.topbar-user {
  display:flex; align-items:center; gap:8px;
  cursor:pointer; padding:5px 10px; border-radius:10px;
  transition:background .15s;
}
.topbar-user:hover { background:#F1F5F9; }
.topbar-user strong { font-size:13px; font-weight:600; color:#1E293B; }
.topbar-user span   { font-size:11px; color:#64748B; display:block; }
.topbar-user svg { color:#94A3B8; }

/* ── Page content ── */
.page-body { padding:24px; flex:1; }
.page-title { font-size:20px; font-weight:700; color:#0F172A; margin-bottom:4px; }
.page-sub   { font-size:13px; color:#64748B; margin-bottom:24px; }

/* ── Responsive mobile ── */
.sidebar-toggle {
  display:none; background:none; border:none; cursor:pointer;
  color:#374151; padding:6px;
}
.sidebar-overlay {
  display:none; position:fixed; inset:0; background:rgba(0,0,0,.4);
  z-index:199;
}
@media(max-width:900px){
  .sidebar { transform:translateX(-100%); }
  .sidebar.open { transform:none; }
  .sidebar-overlay.show { display:block; }
  .main-content { margin-left:0; }
  .sidebar-toggle { display:flex; }
}
</style>
</head>
<body>
<?php
$authUser   = Middleware::user();
$userRole   = $authUser['role'];
$userName   = $authUser['full_name'];
$userEmail  = $authUser['email'];
$userInitial= mb_strtoupper(mb_substr($userName, 0, 1));
$avatarColors = ['admin'=>'avatar-red','teacher'=>'avatar-blue','student'=>'avatar-green'];
$avatarCls    = $avatarColors[$userRole] ?? 'avatar-blue';
$roleLabel    = ['admin'=>'Super Administrator','teacher'=>'Instructor','student'=>'Student'];

// Alert count (for badge)
$alertCount = 0;
try {
  $db = Database::getInstance();
  if ($userRole === 'admin') {
    $alertCount = $db->query("SELECT COUNT(*) FROM alert_logs WHERE status='open'")->fetchColumn();
  } elseif ($userRole === 'teacher') {
    $courseIds = $db->query(
      "SELECT GROUP_CONCAT(course_id) FROM course_enrollments WHERE user_id=? AND role='teacher'",
      [$authUser['id']]
    )->fetchColumn();
    if ($courseIds) {
      $alertCount = $db->query("SELECT COUNT(*) FROM alert_logs WHERE course_id IN ($courseIds) AND status='open'")->fetchColumn();
    }
  }
} catch(Exception $e) {}
?>

<div class="app-shell">

<!-- ══════════ SIDEBAR ══════════ -->
<aside class="sidebar" id="sidebar">

  <!-- Brand -->
  <div class="sidebar-header">
    <img src="<?= APP_URL ?>/assets/images/logo.svg" alt="Logo">
    <div class="sidebar-brand-text">
      <strong>Attendance Tracker</strong>
      <span>Academic Management</span>
    </div>
  </div>

  <!-- Current user -->
  <div class="sidebar-user">
    <div class="avatar <?= $avatarCls ?>"><?= $userInitial ?></div>
    <div class="sidebar-user-info">
      <strong><?= htmlspecialchars($userName) ?></strong>
      <span class="role-badge role-<?= $userRole ?>"><?= ucfirst($userRole) ?></span>
    </div>
  </div>

  <!-- Navigation -->
  <nav class="sidebar-nav">

    <?php if ($userRole === 'admin'): ?>
    <div class="nav-section">Main</div>
    <a href="<?= APP_URL ?>/admin/dashboard.php" class="nav-item <?= ($currentPage??'')==='admin.dashboard'?'active':'' ?>">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
      Dashboard
    </a>
    <a href="<?= APP_URL ?>/index.php?page=admin_users" class="nav-item <?= ($currentPage??'')==='admin.users'?'active':'' ?>">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      Users
    </a>
    <a href="<?= APP_URL ?>/admin/courses.php" class="nav-item <?= ($currentPage??'')==='admin.courses'?'active':'' ?>">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
      Courses
    </a>
    <a href="<?= APP_URL ?>/admin/enrollments.php" class="nav-item <?= ($currentPage??'')==='admin.enrollments'?'active':'' ?>">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
      Enrollments
    </a>
    <a href="<?= APP_URL ?>/admin/sessions.php" class="nav-item <?= ($currentPage??'')==='admin.sessions'?'active':'' ?>">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
      Class Sessions
    </a>
    <div class="nav-section">Monitoring</div>
    <a href="<?= APP_URL ?>/admin/engagement.php" class="nav-item <?= ($currentPage??'')==='admin.engagement'?'active':'' ?>">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
      Engagement Scores
    </a>
    <a href="<?= APP_URL ?>/admin/alerts.php" class="nav-item <?= ($currentPage??'')==='admin.alerts'?'active':'' ?>">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
      Alerts
      <?php if ($alertCount > 0): ?><span class="nav-badge"><?= $alertCount ?></span><?php endif; ?>
    </a>

    <?php elseif ($userRole === 'teacher'): ?>
    <div class="nav-section">Classroom</div>
    <a href="<?= APP_URL ?>/teacher/dashboard.php" class="nav-item <?= ($currentPage??'')==='teacher.dashboard'?'active':'' ?>">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
      Dashboard
    </a>
    <a href="<?= APP_URL ?>/teacher/sessions.php" class="nav-item <?= ($currentPage??'')==='teacher.sessions'?'active':'' ?>">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
      Class Sessions
    </a>
    <a href="<?= APP_URL ?>/teacher/attendance.php" class="nav-item <?= ($currentPage??'')==='teacher.attendance'?'active':'' ?>">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
      Attendance
    </a>
    <a href="<?= APP_URL ?>/teacher/quiz.php" class="nav-item <?= ($currentPage??'')==='teacher.quiz'?'active':'' ?>">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
      Quizzes
    </a>
    <div class="nav-section">Analytics</div>
    <a href="<?= APP_URL ?>/teacher/engagement.php" class="nav-item <?= ($currentPage??'')==='teacher.engagement'?'active':'' ?>">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
      Engagement Scores
    </a>
    <a href="<?= APP_URL ?>/teacher/alerts.php" class="nav-item <?= ($currentPage??'')==='teacher.alerts'?'active':'' ?>">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
      Alerts
      <?php if ($alertCount > 0): ?><span class="nav-badge"><?= $alertCount ?></span><?php endif; ?>
    </a>

    <?php elseif ($userRole === 'student'): ?>
    <div class="nav-section">My Learning</div>
    <a href="<?= APP_URL ?>/student/dashboard.php" class="nav-item <?= ($currentPage??'')==='student.dashboard'?'active':'' ?>">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
      Overview
    </a>
    <a href="<?= APP_URL ?>/student/courses.php" class="nav-item <?= ($currentPage??'')==='student.courses'?'active':'' ?>">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
      My Courses
    </a>
    <a href="<?= APP_URL ?>/student/attendance.php" class="nav-item <?= ($currentPage??'')==='student.attendance'?'active':'' ?>">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
      Attendance
    </a>
    <a href="<?= APP_URL ?>/student/quiz.php" class="nav-item <?= ($currentPage??'')==='student.quiz'?'active':'' ?>">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
      Quizzes
    </a>
    <a href="<?= APP_URL ?>/student/engagement.php" class="nav-item <?= ($currentPage??'')==='student.engagement'?'active':'' ?>">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
      My Engagement
    </a>
    <?php endif; ?>

  </nav><!-- /.sidebar-nav -->

  <!-- Footer -->
  <div class="sidebar-footer">
    <div class="avatar <?= $avatarCls ?>"><?= $userInitial ?></div>
    <div class="sidebar-footer-info">
      <strong><?= htmlspecialchars($userName) ?></strong>
      <span><?= $roleLabel[$userRole] ?? ucfirst($userRole) ?></span>
    </div>
    <a href="<?= APP_URL ?>/logout.php" class="logout-btn" title="Sign out">
      <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
        <polyline points="16 17 21 12 16 7"/>
        <line x1="21" y1="12" x2="9" y2="12"/>
      </svg>
    </a>
  </div>

</aside><!-- /.sidebar -->

<!-- Overlay for mobile -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<!-- ══════════ MAIN ══════════ -->
<div class="main-content">

  <!-- Topbar -->
  <header class="topbar">
    <button class="sidebar-toggle" onclick="openSidebar()">
      <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
      </svg>
    </button>

    <div class="topbar-search">
      <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
      </svg>
      <input type="text" placeholder="Search for students, courses, or sessions...">
    </div>

    <div class="topbar-actions">
      <?php if ($alertCount > 0): ?>
      <button class="icon-btn has-badge" title="Alerts" onclick="location.href='<?= APP_URL ?>/<?= $userRole ?>/alerts.php'">
        <div class="dot"></div>
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
          <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
        </svg>
      </button>
      <?php else: ?>
      <button class="icon-btn" title="Notifications">
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
          <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
        </svg>
      </button>
      <?php endif; ?>

      <button class="icon-btn" title="Settings">
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="3"/>
          <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
        </svg>
      </button>

      <div class="topbar-user">
        <div class="avatar <?= $avatarCls ?>" style="width:32px;height:32px;font-size:12px;"><?= $userInitial ?></div>
        <div>
          <strong><?= htmlspecialchars($userName) ?></strong>
          <span><?= ucfirst($userRole) ?></span>
        </div>
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
      </div>
    </div>
  </header>

  <!-- Page body starts -->
  <div class="page-body">
