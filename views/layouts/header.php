<?php
// views/layouts/header.php
// Usage: include this at top of every page
// Required: $page_title (string), $active_nav (string)
if (!isset($page_title) && isset($pageTitle)) $page_title = $pageTitle;
if (!isset($page_title)) $page_title = 'Dashboard';

if (!isset($active_nav) && isset($currentPage)) $active_nav = $currentPage;
if (!isset($active_nav)) $active_nav = 'dashboard';

// Normalize active_nav to match sidebar keys by removing role prefixes
if (strpos($active_nav, 'admin.') === 0) $active_nav = substr($active_nav, 6);
elseif (strpos($active_nav, 'teacher.') === 0) $active_nav = substr($active_nav, 8);
elseif (strpos($active_nav, 'student.') === 0) $active_nav = substr($active_nav, 8);


$role = $_SESSION['role'] ?? 'admin';
$user_name = $_SESSION['full_name'] ?? 'Admin User';
$user_initials = strtoupper(substr($user_name, 0, 2));

$nav_items = [];
if ($role === 'admin') {
    // Lấy số alert pending cho badge
    try {
        $db = Database::getInstance();
        $openAlertCount = (int)$db->query("SELECT COUNT(*) FROM alerts WHERE status='pending'")->fetchColumn();
    } catch (\Exception $e) {
        $openAlertCount = 0;
    }
    $nav_items = [
        ['key' => 'dashboard',          'label' => 'Dashboard',          'icon' => '⊞',  'href' => APP_URL . '/admin/dashboard.php'],
        ['key' => 'users',              'label' => 'Users',              'icon' => '👥', 'href' => APP_URL . '/index.php?page=admin_users'],
        ['key' => 'courses',            'label' => 'Courses',            'icon' => '🎓', 'href' => APP_URL . '/index.php?page=admin_courses'],
        ['key' => 'enrollments',        'label' => 'Enrollments',        'icon' => '📋', 'href' => APP_URL . '/index.php?page=admin_enrollments'],
        ['key' => 'sessions',           'label' => 'Class Sessions',     'icon' => '📅', 'href' => APP_URL . '/index.php?page=admin_sessions'],
        ['key' => 'attendance',         'label' => 'Attendance',         'icon' => '✅', 'href' => APP_URL . '/teacher/attendance.php'],
        ['key' => 'quizzes',            'label' => 'Quizzes',            'icon' => '📝', 'href' => APP_URL . '/teacher/quiz.php'],
        ['key' => 'engagement',         'label' => 'Engagement Scores',  'icon' => '📊', 'href' => APP_URL . '/index.php?page=admin_engagement_scores'],
        ['key' => 'alerts',             'label' => 'Alerts',             'icon' => '🔔', 'href' => APP_URL . '/index.php?page=admin_alerts', 'badge' => $openAlertCount],
        ['key' => 'reports',            'label' => 'Reports',            'icon' => '📈', 'href' => APP_URL . '/index.php?page=admin_reports'],
    ];
} elseif ($role === 'teacher') {
    $nav_items = [
        ['key' => 'dashboard',  'label' => 'Dashboard',          'icon' => '⊞',  'href' => APP_URL . '/teacher/dashboard.php'],
        ['key' => 'sessions',   'label' => 'My Sessions',        'icon' => '📅', 'href' => APP_URL . '/teacher/sessions.php'],
        ['key' => 'attendance', 'label' => 'Attendance',         'icon' => '✅', 'href' => APP_URL . '/teacher/attendance.php'],
        ['key' => 'quizzes',    'label' => 'Quizzes',            'icon' => '📝', 'href' => APP_URL . '/teacher/quiz.php'],
        ['key' => 'engagement', 'label' => 'Engagement',         'icon' => '📊', 'href' => APP_URL . '/teacher/engagement.php'],
        ['key' => 'alerts',     'label' => 'Alerts',             'icon' => '🔔', 'href' => APP_URL . '/teacher/alerts.php'],
    ];
} else {
    $nav_items = [
        ['key' => 'dashboard',  'label' => 'Dashboard',          'icon' => '⊞',  'href' => APP_URL . '/student/dashboard.php'],
        ['key' => 'courses',    'label' => 'My Courses',         'icon' => '🎓', 'href' => APP_URL . '/student/courses.php'],
        ['key' => 'attendance', 'label' => 'Attendance',         'icon' => '✅', 'href' => APP_URL . '/student/attendance.php'],
        ['key' => 'quizzes',    'label' => 'Quizzes',            'icon' => '📝', 'href' => APP_URL . '/student/quiz.php'],
        ['key' => 'engagement', 'label' => 'Engagement Score',   'icon' => '📊', 'href' => APP_URL . '/student/engagement.php'],
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($page_title) ?> — Attendance Tracker</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
  <style>
    .topbar-avatar {
      transition: border-color 0.2s, box-shadow 0.2s;
      border: 2px solid transparent;
      cursor: pointer;
    }
    .topbar-avatar:hover {
      border-color: #e2e8f0;
      box-shadow: 0 0 0 2px rgba(255,255,255,0.8);
    }
    .profile-dropdown.show {
      display: block !important;
    }
    .profile-dropdown a:hover {
      background: #f1f5f9;
    }
  </style>
</head>
<body>
<div class="app-wrapper">

<!-- SIDEBAR -->
<aside class="sidebar">
  <div class="sidebar-brand">
    <a href="<?= APP_URL ?>/index.php" class="brand-logo" style="display:flex; align-items:center; gap:12px; text-decoration:none;">
      <img src="<?= APP_URL ?>/assets/images/logo.svg?v=20260619" alt="Logo" style="width: 44px; height: 44px; border-radius: 12px; flex-shrink: 0;">
      <div class="brand-text" style="display:flex; flex-direction:column; line-height: 1;">
        <div style="font-family: 'Montserrat', sans-serif; font-size: 20px; font-weight: 900; color: #ffffff; letter-spacing: -0.5px;">Attendance</div>
        <div style="font-family: 'Montserrat', sans-serif; font-size: 20px; font-weight: 900; color: #3b82f6; letter-spacing: -0.5px; margin-top: -2px;">Tracker</div>
        <div style="font-family: 'Montserrat', sans-serif; font-size: 8.5px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.15em; margin-top: 4px;">Academic Management</div>
      </div>
    </a>
  </div>

  <nav class="sidebar-nav">
    <?php foreach ($nav_items as $item): ?>
    <div class="nav-item">
      <a href="<?= $item['href'] ?>" class="nav-link <?= $active_nav === $item['key'] ? 'active' : '' ?>">
        <span class="nav-icon"><?= $item['icon'] ?></span>
        <span><?= $item['label'] ?></span>
        <?php if (!empty($item['badge'])): ?>
          <span class="nav-badge"><?= $item['badge'] ?></span>
        <?php endif; ?>
      </a>
    </div>
    <?php endforeach; ?>
  </nav>

  <div class="sidebar-footer">
    <div class="user-card" style="display: flex; align-items: center; gap: 10px; width: 100%;">
      <div class="user-avatar" style="flex-shrink: 0;"><?= $user_initials ?></div>
      <div class="user-info" style="flex: 1; overflow: hidden;">
        <div class="user-name" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-weight: 700; font-size: 14px;"><?= htmlspecialchars($user_name) ?></div>
        <div class="user-role" style="font-size: 11px; opacity: 0.7;"><?= ucfirst($role) ?> <?= $role === 'admin' ? '· Admin' : '' ?></div>
      </div>
      <a href="<?= APP_URL ?>/logout.php" title="Logout" style="color: #fff; padding: 8px; border-radius: 8px; background: rgba(255,255,255,0.1); transition: background 0.2s; flex-shrink: 0; display: flex; align-items: center; justify-content: center; text-decoration: none;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
      </a>
    </div>
  </div>
</aside>

<!-- MAIN -->
<main class="main-content">

<!-- TOPBAR -->
<header class="topbar" style="display: grid; grid-template-columns: 1fr auto 1fr; align-items: center;">
  <div></div>
  <div class="topbar-search" style="margin: 0 auto; max-width: 480px; width: 100%;">
    <span class="search-icon">
      <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
    </span>
    <input type="text" placeholder="Search for students, courses, or sessions...">
  </div>
  <div class="topbar-actions" style="justify-content: flex-end;">
    <span class="topbar-brand" style="font-family: 'Montserrat', sans-serif; font-size: 16px; font-weight: 800; color: #0f172a; margin-right: 8px;">Attendance Tracker</span>
    <a href="#" class="topbar-btn" title="Settings" style="display:flex; align-items:center; justify-content:center; color: #64748b; text-decoration:none;">
      <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><circle cx="12" cy="12" r="3"></circle></svg>
    </a>
    <a href="#" class="topbar-btn" title="Help" style="display:flex; align-items:center; justify-content:center; color: #64748b; text-decoration:none;">
      <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
    </a>
    <a href="<?= APP_URL ?>/index.php?page=admin_alerts" class="topbar-btn" title="Notifications" style="display:flex; align-items:center; justify-content:center; color: #64748b; text-decoration:none; position: relative;">
      <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
      <?php if ($role === 'admin' && isset($openAlertCount) && $openAlertCount > 0): ?><span class="badge" style="background: #ef4444; color: #fff; font-size: 10px; font-weight: 700; border-radius: 999px; padding: 2px 5px; position: absolute; top: -6px; right: -6px; line-height: 1; border: 2px solid #fff;"><?= $openAlertCount ?></span><?php endif; ?>
    </a>
    
    <div class="profile-menu-container" style="position: relative; margin-left: 8px;">
      <div class="topbar-avatar" style="width:36px; height:36px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700; background: #3b82f6; color: #fff;" onclick="document.getElementById('profileDropdown').classList.toggle('show')">
        <?= $user_initials ?>
      </div>
      
      <div id="profileDropdown" class="profile-dropdown" style="display: none; position: absolute; right: 0; top: 48px; background: #fff; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); width: 260px; z-index: 100; border: 1px solid #e2e8f0; overflow: hidden;">
        <div style="padding: 16px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; gap: 12px;">
          <div style="width: 44px; height: 44px; border-radius: 50%; background: #3b82f6; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 16px; border: 2px solid #22c55e;">
            <?= $user_initials ?>
          </div>
          <div style="font-weight: 700; color: #0f172a; font-size: 15px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= htmlspecialchars($user_name) ?></div>
        </div>
        <div style="padding: 8px 0;">
          <a href="<?= APP_URL ?>/index.php?page=profile" style="display: flex; align-items: center; gap: 12px; padding: 10px 16px; color: #475569; text-decoration: none; font-size: 14px; transition: background 0.2s; font-weight: 500;">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            Edit profile
          </a>
          <a href="<?= APP_URL ?>/index.php?page=settings" style="display: flex; align-items: center; gap: 12px; padding: 10px 16px; color: #475569; text-decoration: none; font-size: 14px; transition: background 0.2s; font-weight: 500;">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><circle cx="12" cy="12" r="3"></circle></svg>
            Account settings and privacy
          </a>
        </div>
        <div style="padding: 8px 0; border-top: 1px solid #e2e8f0;">
          <a href="<?= APP_URL ?>/logout.php" style="display: flex; align-items: center; gap: 12px; padding: 10px 16px; color: #ef4444; text-decoration: none; font-size: 14px; transition: background 0.2s; font-weight: 500;">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
            Log out
          </a>
        </div>
      </div>
    </div>
  </div>
</header>

<script>
document.addEventListener('click', function(event) {
  var container = document.querySelector('.profile-menu-container');
  var dropdown = document.getElementById('profileDropdown');
  if (container && !container.contains(event.target) && dropdown.classList.contains('show')) {
    dropdown.classList.remove('show');
  }
});
</script>

<!-- PAGE CONTENT STARTS BELOW -->
<div class="page-content">