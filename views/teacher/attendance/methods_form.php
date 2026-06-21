<?php
// views/teacher/attendance/methods_form.php
$page_title = 'Attendance Method';
$active_nav = 'attendance';
require_once APP_ROOT . '/views/layouts/header.php';
?>
 
<div class="admin-page-title">
    <div class="left">
        <h1><?= $methodId ? 'Edit Attendance Method' : 'New Attendance Method' ?></h1>
        <p>Session: <?= htmlspecialchars(date('d/m/Y H:i', strtotime($session['session_date'] . ' ' . $session['start_time']))) ?></p>
    </div>
    <div class="right">
        <a href="<?= APP_URL ?>/teacher/attendance/methods_list.php?session_id=<?= (int)$session['id'] ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>
 
<?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
 
<div class="card" style="max-width:640px;">
  <div class="card-body">
    <form method="POST">
        <input type="hidden" name="session_id" value="<?= (int)$session['id'] ?>">
        <?php if ($methodId): ?>
            <input type="hidden" name="id" value="<?= (int)$methodId ?>">
        <?php endif; ?>
 
        <div class="mb-3">
            <label for="method_type" class="form-label">Method Type *</label>
            <select name="method_type" id="method_type" class="form-select" required onchange="updateExpiryVisibility()">
                <option value="">-- Select --</option>
                <option value="qr" <?= ($method && $method['method_type'] === 'qr') ? 'selected' : '' ?>>QR Code</option>
                <option value="otp" <?= ($method && $method['method_type'] === 'otp') ? 'selected' : '' ?>>OTP (One-Time Password)</option>
                <option value="manual" <?= ($method && $method['method_type'] === 'manual') ? 'selected' : '' ?>>Manual Check-in</option>
            </select>
            <div class="method-type-hints">
                <div class="method-type-hint">
                    <span class="badge badge-primary">QR</span>
                    <span>Generate a QR code for students to scan</span>
                </div>
                <div class="method-type-hint">
                    <span class="badge badge-warning">OTP</span>
                    <span>Generate a 6-digit code with an expiry time</span>
                </div>
                <div class="method-type-hint">
                    <span class="badge badge-gray">Manual</span>
                    <span>Teacher marks attendance manually</span>
                </div>
            </div>
        </div>
 
        <div class="mb-3" id="expiry_section" style="display: none;">
            <label for="expires_at" class="form-label">Expires At *</label>
            <input type="datetime-local" name="expires_at" id="expires_at" class="form-control"
                   value="<?= $method && $method['expires_at'] ? date('Y-m-d\TH:i', strtotime($method['expires_at'])) : '' ?>">
            <small class="text-muted d-block mt-2">
                The moment after which the token/OTP is no longer valid
            </small>
        </div>
 
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check"></i> <?= $methodId ? 'Update' : 'Create' ?>
            </button>
            <a href="<?= APP_URL ?>/teacher/attendance/methods_list.php?session_id=<?= (int)$session['id'] ?>" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Cancel
            </a>
        </div>
    </form>
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
 
document.addEventListener('DOMContentLoaded', updateExpiryVisibility);
</script>
 
<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>
 