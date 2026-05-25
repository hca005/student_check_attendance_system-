<?php
require_once APP_ROOT . '/views/layouts/header.php';

$h = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
$buildUrl = static function (array $overrides = []) use ($filters): string {
    $query = ['page' => 'admin_users'];

    foreach (['search', 'role', 'status', 'sort'] as $key) {
        if (($filters[$key] ?? '') !== '') {
            $query[$key] = $filters[$key];
        }
    }

    $query['p'] = $filters['page'] ?? 1;

    foreach ($overrides as $key => $value) {
        if ($value === null || $value === '') {
            unset($query[$key]);
        } else {
            $query[$key] = $value;
        }
    }

    return APP_URL . '/index.php?' . http_build_query($query);
};
?>

<div class="users-page">
    <div class="page-heading">
        <div>
            <h1 class="page-title">Admin User Management</h1>
            <p class="page-sub">Manage admin, teacher, and student accounts from the shared users table.</p>
        </div>
        <a class="btn btn-primary" href="<?= APP_URL ?>/index.php?page=admin_users_create">Add New User</a>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= $h($success) ?></div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= $h($error) ?></div>
    <?php endif; ?>

    <div class="stat-cards">
        <div class="card stat-card">
            <div class="stat-icon stat-blue">All</div>
            <div>
                <div class="stat-value"><?= (int)$stats['total'] ?></div>
                <div class="stat-label">Total users</div>
            </div>
        </div>
        <div class="card stat-card">
            <div class="stat-icon stat-green">On</div>
            <div>
                <div class="stat-value"><?= (int)$stats['active'] ?></div>
                <div class="stat-label">Active accounts</div>
            </div>
        </div>
        <div class="card stat-card">
            <div class="stat-icon stat-blue">T</div>
            <div>
                <div class="stat-value"><?= (int)$stats['teachers'] ?></div>
                <div class="stat-label">Teachers</div>
            </div>
        </div>
        <div class="card stat-card">
            <div class="stat-icon stat-orange">S</div>
            <div>
                <div class="stat-value"><?= (int)$stats['students'] ?></div>
                <div class="stat-label">Students</div>
            </div>
        </div>
    </div>

    <form class="card users-filter" method="get" action="<?= APP_URL ?>/index.php">
        <input type="hidden" name="page" value="admin_users">

        <div class="filters-grid">
            <div class="form-group">
                <label for="search">Search</label>
                <input class="plain-input" type="text" id="search" name="search" value="<?= $h($filters['search'] ?? '') ?>" placeholder="Name, email, or student code">
            </div>

            <div class="form-group">
                <label for="role">Role</label>
                <select class="plain-input" id="role" name="role">
                    <option value="">All roles</option>
                    <option value="admin" <?= ($filters['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="teacher" <?= ($filters['role'] ?? '') === 'teacher' ? 'selected' : '' ?>>Teacher</option>
                    <option value="student" <?= ($filters['role'] ?? '') === 'student' ? 'selected' : '' ?>>Student</option>
                </select>
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select class="plain-input" id="status" name="status">
                    <option value="">All statuses</option>
                    <option value="1" <?= (string)($filters['status'] ?? '') === '1' ? 'selected' : '' ?>>Active</option>
                    <option value="0" <?= (string)($filters['status'] ?? '') === '0' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>

            <div class="form-group">
                <label for="sort">Sort</label>
                <select class="plain-input" id="sort" name="sort">
                    <option value="newest" <?= ($filters['sort'] ?? '') === 'newest' ? 'selected' : '' ?>>Newest first</option>
                    <option value="oldest" <?= ($filters['sort'] ?? '') === 'oldest' ? 'selected' : '' ?>>Oldest first</option>
                    <option value="name_asc" <?= ($filters['sort'] ?? '') === 'name_asc' ? 'selected' : '' ?>>Name A-Z</option>
                    <option value="name_desc" <?= ($filters['sort'] ?? '') === 'name_desc' ? 'selected' : '' ?>>Name Z-A</option>
                </select>
            </div>
        </div>

        <div class="filter-actions">
            <button class="btn btn-primary" type="submit">Apply Filters</button>
            <a class="btn btn-outline" href="<?= APP_URL ?>/index.php?page=admin_users">Reset</a>
        </div>
    </form>

    <div class="card users-table-card">
        <div class="table-header">
            <div>
                <strong>Users</strong>
                <span><?= (int)$totalCount ?> result<?= (int)$totalCount === 1 ? '' : 's' ?></span>
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Student Code</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">No users found for the current filters.</div>
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($users as $user): ?>
                        <?php
                            $role = $user['role'] ?? 'student';
                            $isActive = (int)($user['is_active'] ?? 0) === 1;
                            $initial = mb_strtoupper(mb_substr($user['full_name'] ?? 'U', 0, 1));
                        ?>
                        <tr>
                            <td>
                                <div class="user-cell">
                                    <div class="avatar <?= $role === 'admin' ? 'avatar-red' : ($role === 'teacher' ? 'avatar-blue' : 'avatar-green') ?>"><?= $h($initial) ?></div>
                                    <div>
                                        <strong><?= $h($user['full_name']) ?></strong>
                                        <span>ID #<?= (int)$user['id'] ?></span>
                                    </div>
                                </div>
                            </td>
                            <td><?= $h($user['email']) ?></td>
                            <td><span class="badge badge-primary"><?= $h(ucfirst($role)) ?></span></td>
                            <td><?= $user['student_code'] ? $h($user['student_code']) : '<span class="text-muted">-</span>' ?></td>
                            <td>
                                <span class="badge <?= $isActive ? 'badge-success' : 'badge-gray' ?>">
                                    <?= $isActive ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td><?= !empty($user['created_at']) ? date('d/m/Y', strtotime($user['created_at'])) : '-' ?></td>
                            <td>
                                <div class="action-group">
                                    <a class="btn btn-outline btn-sm" href="<?= APP_URL ?>/index.php?page=admin_users_edit&id=<?= (int)$user['id'] ?>">Edit</a>
                                    <?php if ($isActive): ?>
                                        <a class="btn btn-danger btn-sm" href="<?= APP_URL ?>/index.php?page=admin_users_deactivate&id=<?= (int)$user['id'] ?>" onclick="return confirm('Deactivate this user?')">Deactivate</a>
                                    <?php else: ?>
                                        <a class="btn btn-success btn-sm" href="<?= APP_URL ?>/index.php?page=admin_users_activate&id=<?= (int)$user['id'] ?>" onclick="return confirm('Activate this user?')">Activate</a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($currentPageNumber > 1): ?>
                    <a href="<?= $buildUrl(['p' => $currentPageNumber - 1]) ?>">Previous</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a class="<?= $i === $currentPageNumber ? 'active' : '' ?>" href="<?= $buildUrl(['p' => $i]) ?>"><?= $i ?></a>
                <?php endfor; ?>

                <?php if ($currentPageNumber < $totalPages): ?>
                    <a href="<?= $buildUrl(['p' => $currentPageNumber + 1]) ?>">Next</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>
