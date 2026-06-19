<?php
Middleware::requireAdmin();
$pageTitle = 'Course Management';
$currentPage = 'admin.courses';
require APP_ROOT . '/views/layouts/header.php';

$maxStudents = 1;
foreach ($courses as $row) {
    $maxStudents = max($maxStudents, (int)$row['students_count']);
}
?>

<div class="admin-page-title">
  <div class="left">
    <h1>Course Management</h1>
    <p>Manage academic courses, assigned teachers, thresholds, and course status.</p>
  </div>
  <a class="btn btn-primary" href="<?= APP_URL ?>/index.php?page=admin_course_create">
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    Add New Course
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
      <svg fill="none" viewBox="0 0 24 24" stroke="#2563eb" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
    </div>
    <div><div class="stat-label">Total Courses</div><div class="stat-value"><?= $stats['total_courses'] ?></div></div>
  </div>
  <div class="card stat-card">
    <div class="stat-icon" style="background:#ecfdf5">
      <svg fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="9 12 12 15 16 10"/></svg>
    </div>
    <div><div class="stat-label">Active Courses</div><div class="stat-value"><?= $stats['active_courses'] ?></div></div>
  </div>
  <div class="card stat-card">
    <div class="stat-icon" style="background:#eff6ff">
      <svg fill="none" viewBox="0 0 24 24" stroke="#2563eb" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
    </div>
    <div><div class="stat-label">Assigned Teachers</div><div class="stat-value"><?= $stats['assigned_teachers'] ?></div></div>
  </div>
  <div class="card stat-card">
    <div class="stat-icon" style="background:#ecfdf5">
      <svg fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/></svg>
    </div>
    <div><div class="stat-label">Enrolled Students</div><div class="stat-value"><?= $stats['enrolled_students'] ?></div></div>
  </div>
</div>

<div class="split-layout">
  <div>
    <div class="card admin-toolbar">
      <form method="get" action="<?= APP_URL ?>/index.php">
        <input type="hidden" name="page" value="admin_courses">
        <div class="filter-grid">
          <input type="text" name="search" value="<?= htmlspecialchars($filters['search']) ?>" placeholder="Search by course code, name, teacher">
          <select name="status">
            <option value="">All Status</option>
            <option value="active" <?= $filters['status'] === 'active' ? 'selected' : '' ?>>Active</option>
            <option value="inactive" <?= $filters['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
          </select>
          <select name="sort">
            <option value="newest" <?= $filters['sort'] === 'newest' ? 'selected' : '' ?>>Newest</option>
            <option value="oldest" <?= $filters['sort'] === 'oldest' ? 'selected' : '' ?>>Oldest</option>
            <option value="code" <?= $filters['sort'] === 'code' ? 'selected' : '' ?>>Code A-Z</option>
            <option value="name" <?= $filters['sort'] === 'name' ? 'selected' : '' ?>>Name A-Z</option>
          </select>
          <button class="btn btn-primary" type="submit">Apply Filters</button>
        </div>
        <div class="filter-actions">
          <a class="btn btn-outline" href="<?= APP_URL ?>/index.php?page=admin_courses">Reset</a>
        </div>
      </form>
    </div>

    <div class="card">
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Course Code</th>
              <th>Course Name</th>
              <th>Teacher</th>
              <th>Students</th>
              <th>Sessions</th>
              <th>Absence Threshold</th>
              <th>Low Engagement Threshold</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($courses as $course): ?>
            <?php $progress = (int)round(((int)$course['students_count'] / $maxStudents) * 100); ?>
            <tr>
              <td><a href="<?= APP_URL ?>/index.php?page=admin_course_edit&id=<?= (int)$course['id'] ?>" style="color:#2563eb;font-weight:700;text-decoration:none"><?= htmlspecialchars($course['course_code']) ?></a></td>
              <td>
                <strong><?= htmlspecialchars($course['course_name']) ?></strong><br>
                <span style="font-size:11px;color:#64748b">Semester <?= htmlspecialchars($course['semester']) ?></span>
              </td>
              <td><?= htmlspecialchars($course['teacher_name']) ?></td>
              <td>
                <div style="display:flex;align-items:center;gap:8px">
                  <strong><?= (int)$course['students_count'] ?></strong>
                  <div class="progress-track"><div class="progress-fill" style="width:<?= $progress ?>%"></div></div>
                </div>
              </td>
              <td><?= (int)$course['sessions_count'] ?></td>
              <td><?= (int)$course['absence_threshold'] ?></td>
              <td><?= (float)$course['low_engagement_threshold'] ?></td>
              <td><span class="badge <?= (int)$course['is_active'] === 1 ? 'badge-success' : 'badge-gray' ?>"><?= (int)$course['is_active'] === 1 ? 'Active' : 'Inactive' ?></span></td>
              <td>
                <div class="action-row">
                  <a class="btn btn-outline btn-sm" href="<?= APP_URL ?>/index.php?page=admin_course_edit&id=<?= (int)$course['id'] ?>">Edit</a>
                  <form method="post" action="<?= APP_URL ?>/index.php?page=admin_course_delete&id=<?= (int)$course['id'] ?>" onsubmit="return confirm('Archive this course?')">
                    <button class="btn btn-sm" style="background:#fee2e2;color:#991b1b">Archive</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($courses)): ?>
            <tr><td colspan="9" style="text-align:center;color:#64748b;padding:24px">No courses found.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>

      <div class="list-meta">Showing <?= count($courses) ?> of <?= $totalCount ?> courses</div>

      <?php if ($totalPages > 1): ?>
      <div class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <a class="page-btn <?= $i === $currentPageNum ? 'active' : '' ?>" href="<?= APP_URL ?>/index.php?page=admin_courses&search=<?= urlencode($filters['search']) ?>&status=<?= urlencode($filters['status']) ?>&sort=<?= urlencode($filters['sort']) ?>&p=<?= $i ?>"><?= $i ?></a>
        <?php endfor; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="card quick-panel">
    <h3>Quick Course Creation</h3>
    <p>Rapidly deploy a new course shell.</p>
    <form method="post" action="<?= APP_URL ?>/index.php?page=admin_course_create">
      <input type="hidden" name="is_active" value="1">
      <input type="hidden" name="attend_score" value="2">
      <input type="hidden" name="quiz_correct_score" value="2">
      <input type="hidden" name="discussion_score" value="1">

      <div class="form-group">
        <label>Course Name</label>
        <input type="text" name="course_name" placeholder="e.g. Intro to Biology" required>
      </div>
      <div class="form-group">
        <label>Course Code</label>
        <input type="text" name="course_code" placeholder="e.g. BIO101" required>
      </div>
      <div class="form-grid">
        <div class="form-group">
          <label>Semester</label>
          <input type="text" name="semester" placeholder="2026-1" required>
        </div>
        <div class="form-group">
          <label>Absence</label>
          <input type="number" name="absence_threshold" min="0" value="3" required>
        </div>
      </div>
      <div class="form-group">
        <label>Low Engagement Threshold</label>
        <input type="number" name="low_engagement_threshold" min="0" max="100" step="0.01" value="40.00" required>
      </div>
      <div class="form-group">
        <label>Assign Teacher</label>
        <select name="teacher_id">
          <option value="">Unassigned</option>
          <?php foreach ($teachers as $teacher): ?>
            <option value="<?= (int)$teacher['id'] ?>"><?= htmlspecialchars($teacher['full_name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div style="display:flex;gap:8px">
        <button type="reset" class="btn btn-outline" style="flex:1">Clear</button>
        <button type="submit" class="btn btn-primary" style="flex:1">Create Course</button>
      </div>
    </form>
  </div>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>
