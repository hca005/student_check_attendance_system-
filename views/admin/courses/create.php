<?php
Middleware::requireAdmin();
$pageTitle = 'Create Course';
$currentPage = 'admin.courses';
require APP_ROOT . '/views/layouts/header.php';
?>

<div class="admin-page-title">
  <div class="left">
    <h1>Create Course</h1>
    <p>Add a new academic course and optionally assign a teacher.</p>
  </div>
  <a href="<?= APP_URL ?>/index.php?page=admin_courses" class="btn btn-outline">Back to Courses</a>
</div>

<div class="card" style="padding:16px;max-width:920px">
  <?php if (!empty($errors['general'])): ?>
    <div class="alert alert-danger"><?= $errors['general'] ?></div>
  <?php endif; ?>

  <form method="post" action="<?= $formAction ?>">
    <div class="form-grid">
      <div class="form-group">
        <label>Course Code</label>
        <input type="text" name="course_code" value="<?= htmlspecialchars($course['course_code']) ?>" required>
        <?php if (!empty($errors['course_code'])): ?><div class="error-text"><?= $errors['course_code'] ?></div><?php endif; ?>
      </div>
      <div class="form-group">
        <label>Course Name</label>
        <input type="text" name="course_name" value="<?= htmlspecialchars($course['course_name']) ?>" required>
        <?php if (!empty($errors['course_name'])): ?><div class="error-text"><?= $errors['course_name'] ?></div><?php endif; ?>
      </div>
    </div>

    <div class="form-grid">
      <div class="form-group">
        <label>Semester</label>
        <input type="text" name="semester" value="<?= htmlspecialchars($course['semester']) ?>" placeholder="2026-1" required>
        <?php if (!empty($errors['semester'])): ?><div class="error-text"><?= $errors['semester'] ?></div><?php endif; ?>
      </div>
      <div class="form-group">
        <label>Assign Teacher</label>
        <select name="teacher_id">
          <option value="">Unassigned</option>
          <?php foreach ($teachers as $teacher): ?>
            <option value="<?= (int)$teacher['id'] ?>" <?= (string)$course['teacher_id'] === (string)$teacher['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($teacher['full_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <?php if (!empty($errors['teacher_id'])): ?><div class="error-text"><?= $errors['teacher_id'] ?></div><?php endif; ?>
      </div>
    </div>

    <div class="form-grid">
      <div class="form-group">
        <label>Absence Threshold</label>
        <input type="number" min="0" name="absence_threshold" value="<?= htmlspecialchars((string)$course['absence_threshold']) ?>" required>
        <?php if (!empty($errors['absence_threshold'])): ?><div class="error-text"><?= $errors['absence_threshold'] ?></div><?php endif; ?>
      </div>
      <div class="form-group">
        <label>Low Engagement Threshold</label>
        <input type="number" min="0" max="100" step="0.01" name="low_engagement_threshold" value="<?= htmlspecialchars((string)$course['low_engagement_threshold']) ?>" required>
        <?php if (!empty($errors['low_engagement_threshold'])): ?><div class="error-text"><?= $errors['low_engagement_threshold'] ?></div><?php endif; ?>
      </div>
    </div>

    <div class="form-grid">
      <div class="form-group">
        <label>Attendance Score</label>
        <input type="number" min="0" step="0.01" name="attend_score" value="<?= htmlspecialchars((string)$course['attend_score']) ?>" required>
        <?php if (!empty($errors['attend_score'])): ?><div class="error-text"><?= $errors['attend_score'] ?></div><?php endif; ?>
      </div>
      <div class="form-group">
        <label>Quiz Correct Score</label>
        <input type="number" min="0" step="0.01" name="quiz_correct_score" value="<?= htmlspecialchars((string)$course['quiz_correct_score']) ?>" required>
        <?php if (!empty($errors['quiz_correct_score'])): ?><div class="error-text"><?= $errors['quiz_correct_score'] ?></div><?php endif; ?>
      </div>
    </div>

    <div class="form-grid">
      <div class="form-group">
        <label>Discussion Score</label>
        <input type="number" min="0" step="0.01" name="discussion_score" value="<?= htmlspecialchars((string)$course['discussion_score']) ?>" required>
        <?php if (!empty($errors['discussion_score'])): ?><div class="error-text"><?= $errors['discussion_score'] ?></div><?php endif; ?>
      </div>
      <div class="form-group">
        <label>Status</label>
        <select name="is_active">
          <option value="1" <?= (int)$course['is_active'] === 1 ? 'selected' : '' ?>>Active</option>
          <option value="0" <?= (int)$course['is_active'] === 0 ? 'selected' : '' ?>>Inactive</option>
        </select>
      </div>
    </div>

    <div style="display:flex;justify-content:flex-end;gap:8px">
      <a href="<?= APP_URL ?>/index.php?page=admin_courses" class="btn btn-outline">Cancel</a>
      <button type="submit" class="btn btn-primary">Save Course</button>
    </div>
  </form>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>
