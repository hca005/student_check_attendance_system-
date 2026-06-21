<?php
$page_title = 'Quizzes';
$active_nav = 'quizzes';
require_once APP_ROOT . '/views/layouts/header.php';
$totalQuizzes  = count($quizzes);
$openCount     = 0;
$closedCount   = 0;
$draftCount    = 0;
$totalSubs     = 0;
foreach ($quizzes as $q) {
if ($q['status'] === 'open') $openCount++;
elseif ($q['status'] === 'closed') $closedCount++;
else $draftCount++;
$totalSubs += (int)$q['submission_count'];
}
$statusVN    = ['draft' => 'Draft', 'open' => 'Open', 'closed' => 'Closed'];
$statusBadge = ['draft' => 'badge-gray', 'open' => 'badge-success', 'closed' => 'badge-gray'];
?>
<div class="admin-page-title">
<div class="left">
<h1>Quiz Management</h1>
<p>Manage quiz sessions and track student submissions.</p>
</div>
</div>
<div class="stat-cards">
<div class="card stat-card">
<div class="stat-icon" style="background:#EFF6FF">
<svg fill="none" viewBox="0 0 24 24" stroke="#2563EB" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
</div>
<div><div class="stat-value"><?= $totalQuizzes ?></div><div class="stat-label">Total Quizzes</div></div>
</div>
<div class="card stat-card">
<div class="stat-icon" style="background:#F0FDF4">
<svg fill="none" viewBox="0 0 24 24" stroke="#10B981" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="9 12 12 15 16 10"/></svg>
</div>
<div><div class="stat-value"><?= $openCount ?></div><div class="stat-label">Open</div></div>
</div>
<div class="card stat-card">
<div class="stat-icon" style="background:#F1F5F9">
<svg fill="none" viewBox="0 0 24 24" stroke="#64748B" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
</div>
<div><div class="stat-value"><?= $closedCount ?></div><div class="stat-label">Closed</div></div>
</div>
<div class="card stat-card">
<div class="stat-icon" style="background:#FFF7ED">
<svg fill="none" viewBox="0 0 24 24" stroke="#F59E0B" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
</div>
<div><div class="stat-value"><?= $totalSubs ?></div><div class="stat-label">Total Submissions</div></div>
</div>
</div>
<div class="card">
<div class="card-body">
<div class="table-wrap">
<table class="table table-hover table-striped mb-0">
<thead>
<tr>
<th>Date</th>
<th>Course</th>
<th>Session</th>
<th>Quiz Title</th>
<th>Submissions</th>
<th>Status</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
<?php if (empty($quizzes)): ?>
<tr><td colspan="7" class="text-center text-muted py-4">No quizzes found. Go to Class Sessions to create a quiz.</td></tr>
<?php else: ?>
<?php foreach ($quizzes as $q): ?>
<tr>
<td style="font-weight:600"><?= htmlspecialchars(date('d/m/Y', strtotime($q['session_date']))) ?></td>
<td><span class="badge badge-primary"><?= htmlspecialchars($q['course_code'] ?? '') ?></span> <?= htmlspecialchars($q['course_name']) ?></td>
<td><?= htmlspecialchars($q['session_title'] ?? 'N/A') ?></td>
<td style="font-weight:600"><?= htmlspecialchars($q['title']) ?></td>
<td><?= (int)$q['submission_count'] ?> submissions</td>
<td>
<span class="badge <?= $statusBadge[$q['status']] ?? 'badge-gray' ?>"><?= $statusVN[$q['status']] ?? ucfirst($q['status']) ?></span>
</td>
<td>
<div class="action-row">
<a href="<?= APP_URL ?>/teacher/quiz/sessions_list.php?session_id=<?= (int)$q['session_id'] ?>" class="btn btn-outline-primary btn-sm">Manage</a>
</div>
</td>
</tr>
<?php endforeach; ?>
<?php endif; ?>
</tbody>
</table>
</div>
</div>
</div>
<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>