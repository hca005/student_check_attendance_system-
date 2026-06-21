<?php
// views/teacher/attendance/methods_list.php
$page_title = 'Attendance Methods';
$active_nav = 'attendance';
require_once APP_ROOT . '/views/layouts/header.php';
?>

<<<<<<< HEAD
<div class="container mt-4">
    <div class="admin-page-title">
        <div class="left">
            <h1>Attendance Methods</h1>
            <p>Configure how students check in for this session.</p>
        </div>
        <div class="right">
            <a href="<?php echo APP_URL; ?>/teacher/attendance/methods_form.php?session_id=<?php echo $session['id']; ?>" class="btn btn-primary btn-sm">
                <i class="bi bi-plus"></i> Tạo Phương thức
=======
<div style="padding: 24px;">
    <div class="admin-page-title">
        <div class="left">
            <h1 style="font-size: 24px; margin-bottom: 4px;">Phương thức Điểm danh - <?php echo htmlspecialchars($session['course_code']); ?></h1>
            <p>Buổi: <?php echo date('d/m/Y H:i', strtotime($session['session_date'] . ' ' . $session['start_time'])); ?></p>
        </div>
        <div class="right">
            <a href="<?php echo APP_URL; ?>/teacher/attendance/methods_form.php?session_id=<?php echo $session['id']; ?>" class="btn btn-primary">
                Tạo Phương thức
>>>>>>> 1bfe985867e3f5e54a851fa8f599402e694d8060
            </a>
        </div>
    </div>

    <div class="card">
      <div class="card-header">
        <div>
          <div class="card-title"><?php echo htmlspecialchars($session['course_code']); ?> • Buổi ngày <?php echo date('d/m/Y', strtotime($session['session_date'])); ?></div>
          <div class="text-muted" style="font-size:13px;"><?php echo htmlspecialchars($session['title'] ?? 'No session title'); ?> • <?php echo date('H:i', strtotime($session['start_time'])); ?> – <?php echo date('H:i', strtotime($session['end_time'])); ?></div>
        </div>
      </div>

      <div class="card-body">
        <?php if (isset($_SESSION['success'])): ?>
          <div class="alert alert-success alert-dismissible fade show" role="alert">
              <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
              <button type="button" class="btn-close"></button>
          </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
              <button type="button" class="btn-close"></button>
          </div>
        <?php endif; ?>

        <div class="table-wrap">
          <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Loại</th>
                        <th>Token/OTP</th>
                        <th>Hết hạn</th>
                        <th>Trạng thái</th>
                        <th>Tạo lúc</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($methods)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Chưa có phương thức nào</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($methods as $method): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $method['method_type'] === 'qr' ? 'info' : 
                                             ($method['method_type'] === 'otp' ? 'warning' : 'secondary'); 
                                    ?>">
                                        <?php echo strtoupper($method['method_type']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($method['token']): ?>
                                        <code><?php echo htmlspecialchars(substr($method['token'], 0, 12) . '...'); ?></code>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo $method['expires_at'] ? date('d/m/Y H:i', strtotime($method['expires_at'])) : '<span class="text-muted">-</span>'; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $method['is_active'] ? 'success' : 'secondary'; ?>">
                                        <?php echo $method['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($method['created_at'])); ?></td>
                                <td>
                                    <a href="<?php echo APP_URL; ?>/teacher/attendance/methods_form.php?session_id=<?php echo $session['id']; ?>&id=<?php echo $method['id']; ?>" class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" action="<?php echo APP_URL; ?>/teacher/attendance/methods_delete.php" style="display:inline;" onsubmit="return confirm('Xác nhận xóa?')">
                                        <input type="hidden" name="id" value="<?php echo $method['id']; ?>">
                                        <input type="hidden" name="session_id" value="<?php echo $session['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
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
        <a href="<?php echo APP_URL; ?>/teacher/dashboard.php" class="btn btn-secondary">Quay lại</a>
    </div>
</div>

<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>
