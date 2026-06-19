<?php
Middleware::requireAdmin();
$pageTitle = 'Class Sessions';
$currentPage = 'admin.sessions';
require APP_ROOT . '/views/layouts/header.php';

$methodLabel = [
    'qr' => 'QR Code',
    'otp' => 'OTP',
    'manual' => 'Manual',
    null => 'N/A',
];
?>

<div class="admin-page-title">
  <div class="left">
    <h1>Class Sessions</h1>
    <p>Manage scheduled teaching sessions across courses.</p>
  </div>
  <a href="<?= APP_URL ?>/index.php?page=admin_session_create" class="btn btn-primary">
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    Add New Session
  </a>
</div>

<?php if (!empty($flashSuccess)): ?>
  <div class="alert alert-success"><?= $flashSuccess ?></div>
<?php endif; ?>
<?php if (!empty($flashError)): ?>
  <div class="alert alert-danger"><?= $flashError ?></div>
<?php endif; ?>

<div class="stat-cards">
  <div class="card stat-card">
    <div class="stat-icon" style="background:#eff6ff">
      <svg fill="none" viewBox="0 0 24 24" stroke="#2563eb" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/></svg>
    </div>
    <div><div class="stat-label">Total Sessions</div><div class="stat-value"><?= $stats['total_sessions'] ?></div></div>
  </div>
  <div class="card stat-card">
    <div class="stat-icon" style="background:#ecfdf5">
      <svg fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="2"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="10"/></svg>
    </div>
    <div><div class="stat-label">Upcoming</div><div class="stat-value"><?= $stats['upcoming'] ?></div></div>
  </div>
  <div class="card stat-card">
    <div class="stat-icon" style="background:#eff6ff">
      <svg fill="none" viewBox="0 0 24 24" stroke="#2563eb" stroke-width="2"><circle cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8"/></svg>
    </div>
    <div><div class="stat-label">Active</div><div class="stat-value"><?= $stats['active'] ?></div></div>
  </div>
  <div class="card stat-card">
    <div class="stat-icon" style="background:#fee2e2">
      <svg fill="none" viewBox="0 0 24 24" stroke="#ef4444" stroke-width="2"><path d="M10 9l5 5M15 9l-5 5"/><circle cx="12" cy="12" r="10"/></svg>
    </div>
    <div><div class="stat-label">Ended</div><div class="stat-value"><?= $stats['ended'] ?></div></div>
  </div>
</div>

<div class="card admin-toolbar">
  <form method="get" action="<?= APP_URL ?>/index.php">
    <input type="hidden" name="page" value="admin_sessions">
    <div class="filter-grid">
      <input type="text" name="search" value="<?= htmlspecialchars($filters['search']) ?>" placeholder="Search by session title, course, teacher">
      <select name="course_id">
        <option value="">All Courses</option>
        <?php foreach ($courses as $course): ?>
          <option value="<?= (int)$course['id'] ?>" <?= (int)$filters['course_id'] === (int)$course['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($course['course_code']) ?> - <?= htmlspecialchars($course['course_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <select name="status">
        <option value="">All Status</option>
        <option value="upcoming" <?= $filters['status'] === 'upcoming' ? 'selected' : '' ?>>Upcoming</option>
        <option value="active" <?= $filters['status'] === 'active' ? 'selected' : '' ?>>Active</option>
        <option value="ended" <?= $filters['status'] === 'ended' ? 'selected' : '' ?>>Ended</option>
      </select>
      <input type="date" name="date" value="<?= htmlspecialchars($filters['date']) ?>">
    </div>
    <div class="filter-actions">
      <button class="btn btn-primary" type="submit">Apply Filters</button>
      <a class="btn btn-outline" href="<?= APP_URL ?>/index.php?page=admin_sessions">Reset</a>
    </div>
  </form>
</div>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Session Title</th>
          <th>Course</th>
          <th>Teacher</th>
          <th>Date</th>
          <th>Start Time</th>
          <th>End Time</th>
          <th>Attendance Method</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($sessions as $session): ?>
        <?php
          $statusBadge = $session['status'] === 'active'
            ? 'badge-success'
            : ($session['status'] === 'upcoming' ? 'badge-primary' : 'badge-gray');
        ?>
        <tr>
          <td><strong><?= htmlspecialchars($session['title'] ?: 'Untitled Session') ?></strong></td>
          <td><?= htmlspecialchars($session['course_code']) ?> - <?= htmlspecialchars($session['course_name']) ?></td>
          <td><?= htmlspecialchars($session['teacher_name']) ?></td>
          <td><?= htmlspecialchars($session['session_date']) ?></td>
          <td><?= htmlspecialchars(substr((string)$session['start_time'], 0, 5)) ?></td>
          <td><?= htmlspecialchars(substr((string)$session['end_time'], 0, 5)) ?></td>
          <td><?= htmlspecialchars($methodLabel[$session['attendance_method']] ?? ucfirst((string)$session['attendance_method'])) ?></td>
          <td><span class="badge <?= $statusBadge ?>"><?= ucfirst($session['status']) ?></span></td>
          <td>
            <div class="action-row">
              <a class="btn btn-outline btn-sm" href="<?= APP_URL ?>/index.php?page=admin_session_edit&id=<?= (int)$session['id'] ?>">Edit</a>
              <form method="post" action="<?= APP_URL ?>/index.php?page=admin_session_delete&id=<?= (int)$session['id'] ?>" onsubmit="return confirm('Delete this session?')">
                <button class="btn btn-sm" style="background:#fee2e2;color:#991b1b">Delete</button>
              </form>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($sessions)): ?>
        <tr><td colspan="9" style="text-align:center;color:#64748b;padding:24px">No sessions found.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="list-meta">Showing <?= count($sessions) ?> of <?= $totalCount ?> sessions</div>

  <?php if ($totalPages > 1): ?>
    <div class="pagination">
      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a class="page-btn <?= $i === $currentPageNum ? 'active' : '' ?>" href="<?= APP_URL ?>/index.php?page=admin_sessions&search=<?= urlencode($filters['search']) ?>&course_id=<?= (int)$filters['course_id'] ?>&status=<?= urlencode($filters['status']) ?>&date=<?= urlencode($filters['date']) ?>&p=<?= $i ?>">
          <?= $i ?>
        </a>
      <?php endfor; ?>
    </div>
  <?php endif; ?>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>
