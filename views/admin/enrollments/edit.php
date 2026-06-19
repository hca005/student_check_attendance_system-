<?php
Middleware::requireAdmin();
$pageTitle = 'Edit Enrollment';
$currentPage = 'admin.enrollments';
require APP_ROOT . '/views/layouts/header.php';
?>

<div class="admin-page-title">
  <div class="left">
    <h1>Edit Enrollment</h1>
    <p>Update course membership and role assignment.</p>
  </div>
  <a class="btn btn-outline" href="<?= APP_URL ?>/index.php?page=admin_enrollments">Back to Enrollments</a>
</div>

<div class="card" style="padding:16px;max-width:760px">
  <?php if (!empty($errors['general'])): ?><div class="alert alert-danger"><?= $errors['general'] ?></div><?php endif; ?>

  <form method="post" action="<?= $formAction ?>" id="enrollmentForm">
    <div class="form-grid">
      <div class="form-group">
        <label>Course</label>
        <select name="course_id" required>
          <option value="">Select course</option>
          <?php foreach ($courses as $course): ?>
            <option value="<?= (int)$course['id'] ?>" <?= (string)$record['course_id'] === (string)$course['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($course['course_code']) ?> - <?= htmlspecialchars($course['course_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <?php if (!empty($errors['course_id'])): ?><div class="error-text"><?= $errors['course_id'] ?></div><?php endif; ?>
      </div>
      <div class="form-group">
        <label>Role</label>
        <select name="role" id="roleSelect" required>
          <option value="student" <?= $record['role'] === 'student' ? 'selected' : '' ?>>Student</option>
          <option value="teacher" <?= $record['role'] === 'teacher' ? 'selected' : '' ?>>Teacher</option>
        </select>
        <?php if (!empty($errors['role'])): ?><div class="error-text"><?= $errors['role'] ?></div><?php endif; ?>
      </div>
    </div>

    <div class="form-group">
      <label>User</label>
      <select name="user_id" id="userSelect" required></select>
      <?php if (!empty($errors['user_id'])): ?><div class="error-text"><?= $errors['user_id'] ?></div><?php endif; ?>
    </div>

    <div style="display:flex;justify-content:flex-end;gap:8px">
      <a class="btn btn-outline" href="<?= APP_URL ?>/index.php?page=admin_enrollments">Cancel</a>
      <button class="btn btn-primary" type="submit">Update Enrollment</button>
    </div>
  </form>
</div>

<script>
const teacherUsers = <?= json_encode($teacherUsers, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
const studentUsers = <?= json_encode($studentUsers, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
let currentUserId = '<?= (string)$record['user_id'] ?>';

function fillUserOptions() {
  const role = document.getElementById('roleSelect').value;
  const list = role === 'teacher' ? teacherUsers : studentUsers;
  const select = document.getElementById('userSelect');
  select.innerHTML = '<option value="">Select user</option>';
  list.forEach((user) => {
    const option = document.createElement('option');
    option.value = String(user.id);
    option.textContent = `${user.full_name} (${user.email})`;
    if (String(user.id) === currentUserId) {
      option.selected = true;
    }
    select.appendChild(option);
  });
}

document.getElementById('roleSelect').addEventListener('change', () => {
  currentUserId = '';
  fillUserOptions();
});
fillUserOptions();
</script>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>
