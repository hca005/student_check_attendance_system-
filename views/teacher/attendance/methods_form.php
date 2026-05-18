<?php
// views/teacher/attendance/methods_form.php
require_once APP_ROOT . '/views/layouts/header.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h2><?php echo $methodId ? 'Sửa Phương thức' : 'Tạo Phương thức Điểm danh'; ?></h2>
            <p class="text-muted">Buổi: <?php echo date('d/m/Y H:i', strtotime($session['session_date'] . ' ' . $session['start_time'])); ?></p>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="session_id" value="<?php echo $session['id']; ?>">
                        <?php if ($methodId): ?>
                            <input type="hidden" name="id" value="<?php echo $methodId; ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="method_type" class="form-label">Loại Phương thức *</label>
                            <select name="method_type" id="method_type" class="form-select" required onchange="updateExpiryVisibility()">
                                <option value="">-- Chọn --</option>
                                <option value="qr" <?php echo ($method && $method['method_type'] === 'qr') ? 'selected' : ''; ?>>QR Code</option>
                                <option value="otp" <?php echo ($method && $method['method_type'] === 'otp') ? 'selected' : ''; ?>>OTP (One-Time Password)</option>
                                <option value="manual" <?php echo ($method && $method['method_type'] === 'manual') ? 'selected' : ''; ?>>Điểm danh thủ công</option>
                            </select>
                            <small class="text-muted d-block mt-2">
                                <strong>QR:</strong> Sinh mã QR cho sinh viên quét<br>
                                <strong>OTP:</strong> Sinh mã 6 chữ số có thời gian hết hạn<br>
                                <strong>Manual:</strong> Giáo viên cập nhật thủ công
                            </small>
                        </div>

                        <div class="mb-3" id="expiry_section" style="display: none;">
                            <label for="expires_at" class="form-label">Thời gian Hết hạn *</label>
                            <input type="datetime-local" name="expires_at" id="expires_at" class="form-control" 
                                   value="<?php echo $method && $method['expires_at'] ? date('Y-m-d\TH:i', strtotime($method['expires_at'])) : ''; ?>">
                            <small class="text-muted d-block mt-2">
                                Thời điểm token/OTP không còn hiệu lực
                            </small>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check"></i> <?php echo $methodId ? 'Cập nhật' : 'Tạo'; ?>
                            </button>
                            <a href="<?php echo APP_URL; ?>/teacher/attendance/methods_list.php?session_id=<?php echo $session['id']; ?>" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Quay lại
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function updateExpiryVisibility() {
    const methodType = document.getElementById('method_type').value;
    const expirySection = document.getElementById('expiry_section');
    const expiryInput = document.getElementById('expires_at');
    
    if (methodType === 'manual') {
        expirySection.style.display = 'none';
        expiryInput.removeAttribute('required');
    } else {
        expirySection.style.display = 'block';
        expiryInput.setAttribute('required', 'required');
    }
}

// Init on page load
document.addEventListener('DOMContentLoaded', updateExpiryVisibility);
</script>

<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>
