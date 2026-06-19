<?php
Middleware::requireAdmin();
$pageTitle = 'User Management';
$currentPage = 'admin.users';
require APP_ROOT . '/views/layouts/header.php';

$roleMap = [
    'admin' => 'badge-danger',
    'teacher' => 'badge-primary',
    'student' => 'badge-success',
];

function u_status_badge(int $status): string
{
    return $status === 1 ? 'badge-success' : 'badge-gray';
}

$old = array_merge([
    'full_name' => '',
    'email' => '',
    'role' => 'student',
    'student_code' => '',
    'is_active' => 1,
], $createOld ?? []);
$nameParts = preg_split('/\s+/', trim($old['full_name']));
$firstName = $nameParts[0] ?? '';
$lastName = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : '';
?>

<div class="admin-page-title">
  <div class="left">
    <h1>User Management</h1>
    <p>Manage user accounts, roles, and activation status across the platform.</p>
  </div>
  <button class="btn btn-primary" type="button" onclick="openUserModal()">
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    Add New User
  </button>
</div>

<?php if (!empty($flashSuccess)): ?>
  <div class="alert alert-success"><?= htmlspecialchars((string)$flashSuccess) ?></div>
<?php endif; ?>
<?php if (!empty($flashError)): ?>
  <div class="alert alert-danger"><?= htmlspecialchars((string)$flashError) ?></div>
<?php endif; ?>

<div class="stat-cards">
  <div class="card stat-card">
    <div class="stat-icon" style="background:#eff6ff">
      <svg fill="none" viewBox="0 0 24 24" stroke="#2563eb" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
    </div>
    <div><div class="stat-label">Total Users</div><div class="stat-value"><?= (int)$stats['total'] ?></div></div>
  </div>
  <div class="card stat-card">
    <div class="stat-icon" style="background:#ecfdf5">
      <svg fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="9 12 12 15 16 10"/></svg>
    </div>
    <div><div class="stat-label">Active Users</div><div class="stat-value"><?= (int)$stats['active'] ?></div></div>
  </div>
  <div class="card stat-card">
    <div class="stat-icon" style="background:#eff6ff">
      <svg fill="none" viewBox="0 0 24 24" stroke="#2563eb" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
    </div>
    <div><div class="stat-label">Teachers</div><div class="stat-value"><?= (int)$stats['teachers'] ?></div></div>
  </div>
  <div class="card stat-card">
    <div class="stat-icon" style="background:#ecfdf5">
      <svg fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5-10-5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
    </div>
    <div><div class="stat-label">Students</div><div class="stat-value"><?= (int)$stats['students'] ?></div></div>
  </div>
</div>

<div class="card admin-toolbar">
  <form method="get" action="<?= APP_URL ?>/index.php">
    <input type="hidden" name="page" value="admin_users">
    <div class="filter-grid">
      <input type="text" name="search" placeholder="Search by name or email..." value="<?= htmlspecialchars((string)$filters['search']) ?>">
      <select name="role">
        <option value="">All Roles</option>
        <option value="admin" <?= $filters['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
        <option value="teacher" <?= $filters['role'] === 'teacher' ? 'selected' : '' ?>>Teacher</option>
        <option value="student" <?= $filters['role'] === 'student' ? 'selected' : '' ?>>Student</option>
      </select>
      <select name="status">
        <option value="">All Status</option>
        <option value="1" <?= $filters['status'] === '1' ? 'selected' : '' ?>>Active</option>
        <option value="0" <?= $filters['status'] === '0' ? 'selected' : '' ?>>Inactive</option>
      </select>
      <select name="sort">
        <option value="newest" <?= $filters['sort'] === 'newest' ? 'selected' : '' ?>>Newest</option>
        <option value="oldest" <?= $filters['sort'] === 'oldest' ? 'selected' : '' ?>>Oldest</option>
        <option value="name" <?= $filters['sort'] === 'name' ? 'selected' : '' ?>>Name A-Z</option>
      </select>
    </div>
    <div class="filter-actions">
      <button type="submit" class="btn btn-primary">Apply Filters</button>
      <a href="<?= APP_URL ?>/index.php?page=admin_users" class="btn btn-outline">Reset</a>
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
          <th>Student Code</th>
          <th>Status</th>
          <th>Created</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($users as $user): ?>
        <tr>
          <td>
            <div class="list-row-user">
              <div class="avatar <?= $user['role'] === 'admin' ? 'avatar-red' : ($user['role'] === 'teacher' ? 'avatar-blue' : 'avatar-green') ?>">
                <?= strtoupper(substr((string)$user['full_name'], 0, 1)) ?>
              </div>
              <strong><?= htmlspecialchars((string)$user['full_name']) ?></strong>
            </div>
          </td>
          <td><?= htmlspecialchars((string)$user['email']) ?></td>
          <td><span class="badge <?= $roleMap[$user['role']] ?? 'badge-gray' ?>"><?= ucfirst((string)$user['role']) ?></span></td>
          <td><?= htmlspecialchars((string)($user['student_code'] ?: '--')) ?></td>
          <td><span class="badge <?= u_status_badge((int)$user['is_active']) ?>"><?= (int)$user['is_active'] === 1 ? 'Active' : 'Inactive' ?></span></td>
          <td><?= htmlspecialchars(date('Y-m-d', strtotime((string)$user['created_at']))) ?></td>
          <td>
            <div class="action-row">
              <a class="btn btn-outline btn-sm" href="<?= APP_URL ?>/index.php?page=admin_users_edit&id=<?= (int)$user['id'] ?>">Edit</a>
              <?php if ((int)$user['is_active'] === 1): ?>
                <form method="post" action="<?= APP_URL ?>/index.php?page=admin_users_deactivate&id=<?= (int)$user['id'] ?>" onsubmit="return confirm('Deactivate this user?')">
                  <button class="btn btn-sm" style="background:#fee2e2;color:#991b1b">Deactivate</button>
                </form>
              <?php else: ?>
                <form method="post" action="<?= APP_URL ?>/index.php?page=admin_users_activate&id=<?= (int)$user['id'] ?>">
                  <button class="btn btn-sm" style="background:#dcfce7;color:#166534">Activate</button>
                </form>
              <?php endif; ?>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($users)): ?>
        <tr><td colspan="7" style="text-align:center;color:#64748b;padding:24px">No users found.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="list-meta">Showing <?= count($users) ?> of <?= (int)$totalCount ?> entries</div>

  <?php if ($totalPages > 1): ?>
    <div class="pagination">
      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a class="page-btn <?= $i === $currentPageNum ? 'active' : '' ?>" href="<?= APP_URL ?>/index.php?page=admin_users&search=<?= urlencode((string)$filters['search']) ?>&role=<?= urlencode((string)$filters['role']) ?>&status=<?= urlencode((string)$filters['status']) ?>&sort=<?= urlencode((string)$filters['sort']) ?>&p=<?= $i ?>">
          <?= $i ?>
        </a>
      <?php endfor; ?>
    </div>
  <?php endif; ?>
</div>

<div class="modal <?= !empty($showCreateModal) ? 'show' : '' ?>" id="userModal">
  <div class="modal-dialog">
    <div class="modal-header">
      <h3 class="modal-title">Add New User</h3>
      <button type="button" class="modal-close" onclick="closeUserModal()">&times;</button>
    </div>
    <form method="post" action="<?= APP_URL ?>/index.php?page=admin_users_store" id="userCreateForm">
      <div class="modal-body">
        <div class="avatar-upload">
          <div class="circle">U</div>
          <div style="font-size:11px;color:#2563eb;font-weight:700">UPLOAD AVATAR</div>
        </div>
        <?php if (!empty($createErrors['general'])): ?>
          <div class="alert alert-danger"><?= htmlspecialchars((string)$createErrors['general']) ?></div>
        <?php endif; ?>

        <input type="hidden" name="full_name" id="modal_full_name" value="<?= htmlspecialchars((string)$old['full_name']) ?>">
        <input type="hidden" name="password" value="password">

        <div class="form-grid">
          <div class="form-group">
            <label>First Name</label>
            <input type="text" id="modal_first_name" value="<?= htmlspecialchars($firstName) ?>" placeholder="e.g. John">
          </div>
          <div class="form-group">
            <label>Last Name</label>
            <input type="text" id="modal_last_name" value="<?= htmlspecialchars($lastName) ?>" placeholder="e.g. Doe">
          </div>
        </div>

        <div class="form-group">
          <label>Email Address</label>
          <input type="email" name="email" value="<?= htmlspecialchars((string)$old['email']) ?>" placeholder="john.doe@example.edu">
          <?php if (!empty($createErrors['email'])): ?><div class="error-text"><?= htmlspecialchars((string)$createErrors['email']) ?></div><?php endif; ?>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label>Assign Role</label>
            <select name="role" id="modal_role">
              <option value="student" <?= $old['role'] === 'student' ? 'selected' : '' ?>>Student</option>
              <option value="teacher" <?= $old['role'] === 'teacher' ? 'selected' : '' ?>>Teacher</option>
              <option value="admin" <?= $old['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
          </div>
          <div class="form-group">
            <label>Account Status</label>
            <select name="is_active">
              <option value="1" <?= (int)$old['is_active'] === 1 ? 'selected' : '' ?>>Active</option>
              <option value="0" <?= (int)$old['is_active'] === 0 ? 'selected' : '' ?>>Inactive</option>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label>Identification Number (ID)</label>
          <input type="text" name="student_code" id="modal_student_code" value="<?= htmlspecialchars((string)$old['student_code']) ?>" placeholder="AUTO-GENERATED">
          <?php if (!empty($createErrors['student_code'])): ?><div class="error-text"><?= htmlspecialchars((string)$createErrors['student_code']) ?></div><?php endif; ?>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeUserModal()">Cancel</button>
        <button type="submit" class="btn btn-primary">Save User Data</button>
      </div>
    </form>
  </div>
</div>

<script>
function openUserModal() {
  document.getElementById('userModal').classList.add('show');
}

function closeUserModal() {
  document.getElementById('userModal').classList.remove('show');
}

function updateFullName() {
  const first = document.getElementById('modal_first_name').value.trim();
  const last = document.getElementById('modal_last_name').value.trim();
  document.getElementById('modal_full_name').value = [first, last].filter(Boolean).join(' ');
}

function autoStudentCode() {
  const role = document.getElementById('modal_role').value;
  const codeInput = document.getElementById('modal_student_code');
  if (role === 'student' && codeInput.value.trim() === '') {
    const stamp = Date.now().toString().slice(-6);
    codeInput.value = 'STD-' + stamp;
  }
  if (role !== 'student') {
    codeInput.value = '';
  }
}

document.getElementById('modal_first_name').addEventListener('input', updateFullName);
document.getElementById('modal_last_name').addEventListener('input', updateFullName);
document.getElementById('modal_role').addEventListener('change', autoStudentCode);
document.getElementById('userCreateForm').addEventListener('submit', updateFullName);
autoStudentCode();
</script>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>
