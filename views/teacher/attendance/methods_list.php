<?php
// views/teacher/attendance/methods_list.php
$page_title = 'Attendance Methods';
$active_nav = 'attendance';
require_once APP_ROOT . '/views/layouts/header.php';
?>
 
<div class="admin-page-title">
    <div class="left">
        <h1>Attendance Methods</h1>
        <p><?= htmlspecialchars($session['course_code']) ?> &middot; <?= date('d/m/Y', strtotime($session['session_date'])) ?> &middot; <?= htmlspecialchars($session['title'] ?? 'No session title') ?> &middot; <?= date('H:i', strtotime($session['start_time'])) ?>&ndash;<?= date('H:i', strtotime($session['end_time'])) ?></p>
    </div>
    <div class="right">
        <a href="<?= APP_URL ?>/teacher/attendance/methods_form.php?session_id=<?= (int)$session['id'] ?>" class="btn btn-primary btn-sm">
            <i class="bi bi-plus"></i> New Method
        </a>
    </div>
</div>
 
<div class="card">
  <div class="card-body">
    <?php if (isset($_SESSION['success'])): ?>
      <div class="alert alert-success alert-dismissible">
          <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
          <button type="button" class="btn-close"></button>
      </div>
    <?php endif; ?>
 
    <?php if (isset($_SESSION['error'])): ?>
      <div class="alert alert-danger alert-dismissible">
          <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
          <button type="button" class="btn-close"></button>
      </div>
    <?php endif; ?>
 
    <div class="table-wrap">
      <table class="table table-hover table-striped mb-0">
          <thead>
              <tr>
                  <th>Type</th>
                  <th>Token/OTP</th>
                  <th>Expires</th>
                  <th>Status</th>
                  <th>Created</th>
                  <th>Actions</th>
              </tr>
          </thead>
          <tbody>
              <?php if (empty($methods)): ?>
                  <tr>
                      <td colspan="6" class="text-center text-muted py-4">No attendance methods yet.</td>
                  </tr>
              <?php else: ?>
                  <?php foreach ($methods as $method): ?>
                      <tr>
                          <td>
                              <span class="badge <?=
                                  $method['method_type'] === 'qr' ? 'badge-primary' :
                                  ($method['method_type'] === 'otp' ? 'badge-warning' : 'badge-gray')
                              ?>">
                                  <?= strtoupper($method['method_type']) ?>
                              </span>
                          </td>
                          <td>

<?php if ($method['method_type'] === 'qr' && $method['token']): ?>

<img
    src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=<?= urlencode($method['token']) ?>"
    width="90"
    alt="QR Code">

<br>
<small><?= htmlspecialchars(substr($method['token'],0,8)) ?>...</small>

<?php elseif ($method['token']): ?>

<code>
<?= htmlspecialchars(substr($method['token'],0,12)) ?>...
</code>

<?php else: ?>

<span class="text-muted">-</span>

<?php endif; ?>

</td>
                          <td>
                              <?= $method['expires_at'] ? date('d/m/Y H:i', strtotime($method['expires_at'])) : '<span class="text-muted">&ndash;</span>' ?>
                          </td>
                          <td>
                              <span class="badge <?= $method['is_active'] ? 'badge-success' : 'badge-gray' ?>">
                                  <?= $method['is_active'] ? 'Active' : 'Inactive' ?>
                              </span>
                          </td>
                          <td><?= date('d/m/Y H:i', strtotime($method['created_at'])) ?></td>
                          <td>
                              <div style="display:flex;gap:6px">
                                  <a href="<?= APP_URL ?>/teacher/attendance/methods_form.php?session_id=<?= (int)$session['id'] ?>&id=<?= (int)$method['id'] ?>" class="btn btn-sm btn-outline-warning" title="Edit">
                                      <i class="bi bi-pencil"></i>
                                  </a>
                                  <form method="POST" action="<?= APP_URL ?>/teacher/attendance/methods_delete.php" style="display:contents;" onsubmit="return confirm('Delete this method?')">
                                      <input type="hidden" name="id" value="<?= (int)$method['id'] ?>">
                                      <input type="hidden" name="session_id" value="<?= (int)$session['id'] ?>">
                                      <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                          <i class="bi bi-trash"></i>
                                      </button>
                                  </form>
                              </div>
                          </td>
                      </tr>
                  <?php endforeach; ?>
              <?php endif; ?>
          </tbody>
      </table>
    </div>
  </div>
</div>
 
<div class="mt-3">
    <a href="<?= APP_URL ?>/teacher/dashboard.php" class="btn btn-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Back to Dashboard
    </a>
</div>
 
<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>
 