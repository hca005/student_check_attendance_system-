<?php
require_once APP_ROOT . '/views/layouts/header.php';

$h = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
$editingSelf = isset($_SESSION['user_id'], $user['id']) && (int)$_SESSION['user_id'] === (int)$user['id'];
?>

<div class="users-page">
    <div class="page-heading">
        <div>
            <h1 class="page-title">Edit User</h1>
            <p class="page-sub">Update account details. Leave password blank to keep the current password.</p>
        </div>
        <a class="btn btn-outline" href="<?= APP_URL ?>/index.php?page=admin_users">Back to Users</a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <div>
                <strong>Please fix the following fields:</strong>
                <ul class="error-list">
                    <?php foreach ($errors as $message): ?>
                        <li><?= $h($message) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($editingSelf): ?>
        <div class="alert alert-info">You are editing your own account. The system will not allow this account to be deactivated.</div>
    <?php endif; ?>

    <form class="card user-form-card" method="post" action="<?= APP_URL ?>/index.php?page=admin_users_update&id=<?= (int)$user['id'] ?>">
        <div class="form-grid">
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input class="plain-input <?= isset($errors['full_name']) ? 'is-invalid' : '' ?>" type="text" id="full_name" name="full_name" value="<?= $h($old['full_name'] ?? '') ?>" required>
                <?php if (isset($errors['full_name'])): ?><div class="error-msg show"><?= $h($errors['full_name']) ?></div><?php endif; ?>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input class="plain-input <?= isset($errors['email']) ? 'is-invalid' : '' ?>" type="email" id="email" name="email" value="<?= $h($old['email'] ?? '') ?>" required>
                <?php if (isset($errors['email'])): ?><div class="error-msg show"><?= $h($errors['email']) ?></div><?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password">New Password</label>
                <input class="plain-input <?= isset($errors['password']) ? 'is-invalid' : '' ?>" type="password" id="password" name="password" minlength="6" placeholder="Leave blank to keep current password">
                <?php if (isset($errors['password'])): ?><div class="error-msg show"><?= $h($errors['password']) ?></div><?php endif; ?>
            </div>

            <div class="form-group">
                <label for="role">Role</label>
                <select class="plain-input <?= isset($errors['role']) ? 'is-invalid' : '' ?>" id="role" name="role" required>
                    <option value="admin" <?= ($old['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="teacher" <?= ($old['role'] ?? '') === 'teacher' ? 'selected' : '' ?>>Teacher</option>
                    <option value="student" <?= ($old['role'] ?? '') === 'student' ? 'selected' : '' ?>>Student</option>
                </select>
                <?php if (isset($errors['role'])): ?><div class="error-msg show"><?= $h($errors['role']) ?></div><?php endif; ?>
            </div>

            <div class="form-group">
                <label for="student_code">Student Code</label>
                <input class="plain-input <?= isset($errors['student_code']) ? 'is-invalid' : '' ?>" type="text" id="student_code" name="student_code" value="<?= $h($old['student_code'] ?? '') ?>" maxlength="20">
                <?php if (isset($errors['student_code'])): ?><div class="error-msg show"><?= $h($errors['student_code']) ?></div><?php endif; ?>
            </div>

            <div class="form-group">
                <label for="is_active">Status</label>
                <?php if ($editingSelf): ?>
                    <input type="hidden" name="is_active" value="1">
                <?php endif; ?>
                <select class="plain-input <?= isset($errors['is_active']) ? 'is-invalid' : '' ?>" id="is_active" name="is_active" <?= $editingSelf ? 'disabled' : '' ?>>
                    <option value="1" <?= (string)($old['is_active'] ?? '1') === '1' ? 'selected' : '' ?>>Active</option>
                    <option value="0" <?= (string)($old['is_active'] ?? '') === '0' ? 'selected' : '' ?>>Inactive</option>
                </select>
                <?php if (isset($errors['is_active'])): ?><div class="error-msg show"><?= $h($errors['is_active']) ?></div><?php endif; ?>
            </div>
        </div>

        <div class="form-actions">
            <a class="btn btn-outline" href="<?= APP_URL ?>/index.php?page=admin_users">Cancel</a>
            <button class="btn btn-primary" type="submit">Save Changes</button>
        </div>
    </form>
</div>

<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>
