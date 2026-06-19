<?php
Middleware::requireAdmin();
$pageTitle = 'Edit User';
$currentPage = 'admin.users';
require APP_ROOT . '/views/layouts/header.php';
?>

<div class="admin-page-title">
  <div class="left">
    <h1>Edit User</h1>
    <p>Update user profile, role, and account status.</p>
  </div>
  <a class="btn btn-outline" href="<?= APP_URL ?>/index.php?page=admin_users">Back to Users</a>
</div>

<?php if (!empty($errors['general'])): ?>
  <div class="alert alert-danger"><?= $errors['general'] ?></div>
<?php endif; ?>

<div class="card" style="padding:16px;max-width:760px">
  <form method="post" action="<?= APP_URL ?>/index.php?page=admin_users_edit&id=<?= (int)$user['id'] ?>">
    <div class="form-grid">
      <div class="form-group">
        <label>Full Name</label>
        <input type="text" name="full_name" value="<?= htmlspecialchars($old['full_name']) ?>">
        <?php if (!empty($errors['full_name'])): ?><div class="error-text"><?= $errors['full_name'] ?></div><?php endif; ?>
      </div>
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($old['email']) ?>">
        <?php if (!empty($errors['email'])): ?><div class="error-text"><?= $errors['email'] ?></div><?php endif; ?>
      </div>
    </div>

    <div class="form-grid">
      <div class="form-group">
        <label>Role</label>
        <select name="role">
          <option value="admin" <?= $old['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
          <option value="teacher" <?= $old['role'] === 'teacher' ? 'selected' : '' ?>>Teacher</option>
          <option value="student" <?= $old['role'] === 'student' ? 'selected' : '' ?>>Student</option>
        </select>
      </div>
      <div class="form-group">
        <label>Status</label>
        <select name="is_active">
          <option value="1" <?= (int)$old['is_active'] === 1 ? 'selected' : '' ?>>Active</option>
          <option value="0" <?= (int)$old['is_active'] === 0 ? 'selected' : '' ?>>Inactive</option>
        </select>
      </div>
    </div>

    <div class="form-grid">
      <div class="form-group">
        <label>Student Code</label>
        <input type="text" name="student_code" value="<?= htmlspecialchars((string)$old['student_code']) ?>">
      </div>
      <div class="form-group">
        <label>New Password (optional)</label>
        <input type="password" name="password" placeholder="Leave blank to keep current password">
        <?php if (!empty($errors['password'])): ?><div class="error-text"><?= $errors['password'] ?></div><?php endif; ?>
      </div>
    </div>

    <div style="display:flex;gap:8px;justify-content:flex-end">
      <a class="btn btn-outline" href="<?= APP_URL ?>/index.php?page=admin_users">Cancel</a>
      <button type="submit" class="btn btn-primary">Save Changes</button>
    </div>
  </form>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>
