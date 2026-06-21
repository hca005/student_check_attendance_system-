<?php
// views/teacher/attendance/records_list.php
$page_title = 'Attendance Records';
$active_nav = 'attendance';
require_once APP_ROOT . '/views/layouts/header.php';
?>

<div class="container mt-4">
    <div class="admin-page-title">
        <div class="left">
            <h1>Attendance Records</h1>
            <p>Update attendance status and notes for students in this session.</p>
        </div>
        <div class="right">
            <a href="<?php echo APP_URL; ?>/teacher/attendance/methods_list.php?session_id=<?php echo $session['id']; ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Back to Methods
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card">
      <div class="card-header">
        <div>
          <div class="card-title">Attendance Records</div>
          <div class="text-muted" style="font-size:13px;">Update attendance status and notes for each student.</div>
        </div>
      </div>
      <div class="card-body">
        <div class="table-wrap">
          <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Mã SV</th>
                        <th>Tên Sinh viên</th>
                        <th>Trạng thái</th>
                        <th>Check-in lúc</th>
                        <th>Ghi chú</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($records)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Chưa có sinh viên nào</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($records as $record): ?>
                            <tr>
                                <td><code><?php echo htmlspecialchars($record['student_code']); ?></code></td>
                                <td><?php echo htmlspecialchars($record['full_name']); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $record['status'] === 'present' ? 'success' :
                                             ($record['status'] === 'absent' ? 'danger' :
                                              ($record['status'] === 'late' ? 'warning' : 'info'));
                                    ?>">
                                        <?php 
                                            $statusVN = [
                                                'present' => 'Có mặt',
                                                'absent' => 'Vắng',
                                                'late' => 'Muộn',
                                                'excused' => 'Phép'
                                            ];
                                            echo $statusVN[$record['status']] ?? $record['status'];
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo $record['checked_in_at'] ? date('H:i:s', strtotime($record['checked_in_at'])) : '<span class="text-muted">-</span>'; ?>
                                </td>
                                <td><?php echo htmlspecialchars($record['note'] ?? '-'); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary" type="button" onclick="openModal('updateModal<?php echo $record['id']; ?>')">
                                    <i class="bi bi-pencil"></i> Cập nhật
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
      </div>
    </div>

    <?php if (!empty($records)): ?>
        <?php foreach ($records as $record): ?>
            <div class="modal-overlay" id="updateModal<?php echo $record['id']; ?>">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Cập nhật Điểm danh</h5>
                            <button type="button" class="btn-close"></button>
                        </div>
                        <form method="POST" action="<?php echo APP_URL; ?>/teacher/attendance/update_record.php">
                            <div class="modal-body">
                                <input type="hidden" name="id" value="<?php echo $record['id']; ?>">
                                <input type="hidden" name="session_id" value="<?php echo $session['id']; ?>">

                                <p class="mb-3"><strong><?php echo htmlspecialchars($record['full_name']); ?></strong></p>

                                <div class="mb-3">
                                    <label for="status<?php echo $record['id']; ?>" class="form-label">Trạng thái</label>
                                    <select name="status" id="status<?php echo $record['id']; ?>" class="form-select" required>
                                        <option value="present" <?php echo $record['status'] === 'present' ? 'selected' : ''; ?>>Có mặt</option>
                                        <option value="absent" <?php echo $record['status'] === 'absent' ? 'selected' : ''; ?>>Vắng</option>
                                        <option value="late" <?php echo $record['status'] === 'late' ? 'selected' : ''; ?>>Muộn</option>
                                        <option value="excused" <?php echo $record['status'] === 'excused' ? 'selected' : ''; ?>>Phép</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="note<?php echo $record['id']; ?>" class="form-label">Ghi chú</label>
                                    <textarea name="note" id="note<?php echo $record['id']; ?>" class="form-control" rows="2"><?php echo htmlspecialchars($record['note'] ?? ''); ?></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" onclick="closeModal('updateModal<?php echo $record['id']; ?>')">Hủy</button>
                                <button type="submit" class="btn btn-primary">Lưu</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div class="mt-3">
        <a href="<?php echo APP_URL; ?>/teacher/dashboard.php" class="btn btn-secondary">Quay lại</a>
    </div>
</div>

<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>
