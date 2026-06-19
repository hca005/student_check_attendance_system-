<?php
Middleware::requireAdmin();
$pageTitle = 'Enrollment Management';
$currentPage = 'admin.enrollments';
require APP_ROOT . '/views/layouts/header.php';
?>

<div class="admin-page-title">
  <div class="left">
    <h1>Enrollment Management</h1>
    <p>Assign teachers and students to courses and manage course membership.</p>
  </div>
  <a href="<?= APP_URL ?>/index.php?page=admin_enrollment_create" class="btn btn-primary">
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    Add Enrollment
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
      <svg fill="none" viewBox="0 0 24 24" stroke="#2563eb" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
    </div>
    <div><div class="stat-label">Total Enrollments</div><div class="stat-value"><?= $stats['total_enrollments'] ?></div></div>
  </div>
  <div class="card stat-card">
    <div class="stat-icon" style="background:#ecfdf5">
      <svg fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5-10-5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
    </div>
    <div><div class="stat-label">Teachers Assigned</div><div class="stat-value"><?= $stats['teachers_assigned'] ?></div></div>
  </div>
  <div class="card stat-card">
    <div class="stat-icon" style="background:#eff6ff">
      <svg fill="none" viewBox="0 0 24 24" stroke="#2563eb" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
    </div>
    <div><div class="stat-label">Students Enrolled</div><div class="stat-value"><?= $stats['students_enrolled'] ?></div></div>
  </div>
  <div class="card stat-card">
    <div class="stat-icon" style="background:#ecfdf5">
      <svg fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
    </div>
    <div><div class="stat-label">Active Courses</div><div class="stat-value"><?= $stats['active_courses'] ?></div></div>
  </div>
</div>

<div class="card admin-toolbar">
  <form method="get" action="<?= APP_URL ?>/index.php">
    <input type="hidden" name="page" value="admin_enrollments">
    <div class="filter-grid">
      <input type="text" name="search" value="<?= htmlspecialchars($filters['search']) ?>" placeholder="Search by user, email, course code">
      <select name="course_id">
        <option value="">All Courses</option>
        <?php foreach ($courses as $course): ?>
          <option value="<?= (int)$course['id'] ?>" <?= (int)$filters['course_id'] === (int)$course['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($course['course_code']) ?> - <?= htmlspecialchars($course['course_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <select name="role">
        <option value="">All Roles</option>
        <option value="teacher" <?= $filters['role'] === 'teacher' ? 'selected' : '' ?>>Teacher</option>
        <option value="student" <?= $filters['role'] === 'student' ? 'selected' : '' ?>>Student</option>
      </select>
      <select name="status">
        <option value="">All Status</option>
        <option value="active" <?= $filters['status'] === 'active' ? 'selected' : '' ?>>Active</option>
        <option value="inactive" <?= $filters['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
      </select>
    </div>
    <div class="filter-actions">
      <button class="btn btn-primary" type="submit">Apply Filters</button>
      <a class="btn btn-outline" href="<?= APP_URL ?>/index.php?page=admin_enrollments">Reset</a>
    </div>
  </form>
</div>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>User</th>
          <th>Email</th>
          <th>Role</th>
          <th>Course</th>
          <th>Course Code</th>
          <th>Enrolled Date</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($enrollments as $row): ?>
        <?php
          $status = ((int)$row['user_active'] === 1 && (int)$row['course_active'] === 1) ? 'Active' : 'Inactive';
          $statusClass = $status === 'Active' ? 'badge-success' : 'badge-gray';
        ?>
        <tr>
          <td>
            <div class="list-row-user">
              <div class="avatar <?= $row['role'] === 'teacher' ? 'avatar-blue' : 'avatar-green' ?>"><?= strtoupper(substr($row['full_name'], 0, 1)) ?></div>
              <strong><?= htmlspecialchars($row['full_name']) ?></strong>
            </div>
          </td>
          <td><?= htmlspecialchars($row['email']) ?></td>
          <td><span class="badge <?= $row['role'] === 'teacher' ? 'badge-primary' : 'badge-success' ?>"><?= ucfirst($row['role']) ?></span></td>
          <td><?= htmlspecialchars($row['course_name']) ?></td>
          <td><?= htmlspecialchars($row['course_code']) ?></td>
          <td><?= date('Y-m-d', strtotime($row['enrolled_at'])) ?></td>
          <td><span class="badge <?= $statusClass ?>"><?= $status ?></span></td>
          <td>
            <div class="action-row">
              <a class="btn btn-outline btn-sm" href="<?= APP_URL ?>/index.php?page=admin_enrollment_edit&id=<?= (int)$row['id'] ?>">Edit</a>
              <form method="post" action="<?= APP_URL ?>/index.php?page=admin_enrollment_delete&id=<?= (int)$row['id'] ?>" onsubmit="return confirm('Remove this enrollment?')">
                <button class="btn btn-sm" style="background:#fee2e2;color:#991b1b">Remove</button>
              </form>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($enrollments)): ?>
        <tr><td colspan="8" style="text-align:center;color:#64748b;padding:24px">No enrollments found.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="list-meta">Showing <?= count($enrollments) ?> of <?= $totalCount ?> entries</div>

  <?php if ($totalPages > 1): ?>
    <div class="pagination">
      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a class="page-btn <?= $i === $currentPageNum ? 'active' : '' ?>" href="<?= APP_URL ?>/index.php?page=admin_enrollments&search=<?= urlencode($filters['search']) ?>&course_id=<?= (int)$filters['course_id'] ?>&role=<?= urlencode($filters['role']) ?>&status=<?= urlencode($filters['status']) ?>&p=<?= $i ?>">
          <?= $i ?>
        </a>
      <?php endfor; ?>
    </div>
  <?php endif; ?>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>
