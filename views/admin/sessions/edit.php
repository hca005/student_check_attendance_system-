<?php
Middleware::requireAdmin();
$pageTitle = 'Edit Session';
$currentPage = 'admin.sessions';
require APP_ROOT . '/views/layouts/header.php';
?>

<div class="admin-page-title">
  <div class="left">
    <h1>Edit Session</h1>
    <p>Update session schedule, status, and details.</p>
  </div>
  <a class="btn btn-outline" href="<?= APP_URL ?>/index.php?page=admin_sessions">Back to Sessions</a>
</div>

<div class="card" style="padding:16px;max-width:840px">
  <?php if (!empty($errors['general'])): ?><div class="alert alert-danger"><?= $errors['general'] ?></div><?php endif; ?>
  <form method="post" action="<?= $formAction ?>">
    <div class="form-grid">
      <div class="form-group">
        <label>Course</label>
        <select name="course_id" required>
          <option value="">Select course</option>
          <?php foreach ($courses as $course): ?>
            <option value="<?= (int)$course['id'] ?>" <?= (string)$session['course_id'] === (string)$course['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($course['course_code']) ?> - <?= htmlspecialchars($course['course_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Teacher</label>
        <select name="teacher_id" required>
          <option value="">Select teacher</option>
          <?php foreach ($teachers as $teacher): ?>
            <option value="<?= (int)$teacher['id'] ?>" <?= (string)$session['teacher_id'] === (string)$teacher['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($teacher['full_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="form-group">
      <label>Session Title</label>
      <input type="text" name="title" value="<?= htmlspecialchars($session['title']) ?>" placeholder="Session title">
    </div>

    <div class="form-grid">
      <div class="form-group">
        <label>Date</label>
        <input type="date" name="session_date" value="<?= htmlspecialchars($session['session_date']) ?>" required>
        <?php if (!empty($errors['session_date'])): ?><div class="error-text"><?= $errors['session_date'] ?></div><?php endif; ?>
      </div>
      <div class="form-group">
        <label>Status</label>
        <select name="status">
          <option value="upcoming" <?= $session['status'] === 'upcoming' ? 'selected' : '' ?>>Upcoming</option>
          <option value="active" <?= $session['status'] === 'active' ? 'selected' : '' ?>>Active</option>
          <option value="ended" <?= $session['status'] === 'ended' ? 'selected' : '' ?>>Ended</option>
        </select>
      </div>
    </div>

    <div class="form-grid">
      <div class="form-group">
        <label>Start Time</label>
        <input type="time" name="start_time" value="<?= htmlspecialchars($session['start_time']) ?>" required>
        <?php if (!empty($errors['start_time'])): ?><div class="error-text"><?= $errors['start_time'] ?></div><?php endif; ?>
      </div>
      <div class="form-group">
        <label>End Time</label>
        <input type="time" name="end_time" value="<?= htmlspecialchars($session['end_time']) ?>" required>
        <?php if (!empty($errors['end_time'])): ?><div class="error-text"><?= $errors['end_time'] ?></div><?php endif; ?>
      </div>
    </div>

    <div class="form-group">
      <label>Description / Notes</label>
      <textarea name="notes"><?= htmlspecialchars($session['notes']) ?></textarea>
    </div>

    <div style="display:flex;justify-content:flex-end;gap:8px">
      <a href="<?= APP_URL ?>/index.php?page=admin_sessions" class="btn btn-outline">Cancel</a>
      <button type="submit" class="btn btn-primary">Update Session</button>
    </div>
  </form>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>
