<?php
require_once APP_ROOT . '/views/layouts/header.php';
?>

<div class="page-title"><?= $methodId ? 'Sửa Phương thức' : 'Tạo Phương thức Điểm danh' ?></div>
<p class="page-sub">Buổi: <?= date('d/m/Y', strtotime($session['session_date'])) ?> | <?= substr($session['start_time'],0,5) ?> – <?= substr($session['end_time'],0,5) ?></p>

<div style="max-width:560px">
<div class="card" style="padding:24px">
    <form method="POST">
        <input type="hidden" name="session_id" value="<?= $session['id'] ?>">
        <?php if ($methodId): ?>
            <input type="hidden" name="id" value="<?= $methodId ?>">
        <?php endif; ?>

        <div style="margin-bottom:16px">
            <label style="font-size:13px;font-weight:600;color:#374151;display:block;margin-bottom:6px">Loại Phương thức *</label>
            <select name="method_type" id="method_type" onchange="updateExpiryVisibility()" style="width:100%;padding:8px 12px;border:1px solid #E2E8F0;border-radius:8px;font-size:14px">
                <option value="">-- Chọn --</option>
                <option value="qr" <?= ($method && $method['method_type']==="qr") ? "selected" : "" ?>>QR Code</option>
                <option value="otp" <?= ($method && $method['method_type']==="otp") ? "selected" : "" ?>>OTP</option>
                <option value="manual" <?= ($method && $method['method_type']==="manual") ? "selected" : "" ?>>Thủ công</option>
            </select>
        </div>

        <div id="expiry-group" style="margin-bottom:16px">
            <label style="font-size:13px;font-weight:600;color:#374151;display:block;margin-bottom:6px">Thời gian hết hạn</label>
            <input type="datetime-local" name="expires_at" style="width:100%;padding:8px 12px;border:1px solid #E2E8F0;border-radius:8px;font-size:14px">
            <div style="font-size:12px;color:#94A3B8;margin-top:4px">Để trống nếu không giới hạn thời gian</div>
        </div>

        <div style="margin-bottom:20px">
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                <input type="checkbox" name="is_active" value="1" checked>
                <span style="font-size:13px;font-weight:600;color:#374151">Kích hoạt ngay</span>
            </label>
        </div>

        <div style="display:flex;gap:10px">
            <button type="submit" class="btn btn-primary"><?= $methodId ? "Cập nhật" : "Tạo" ?></button>
            <a href="<?= APP_URL ?>/teacher/attendance/methods_list.php?session_id=<?= $session['id'] ?>" class="btn btn-outline">Hủy</a>
        </div>
    </form>
</div>
</div>

<script>
function updateExpiryVisibility() {
    const type = document.getElementById("method_type").value;
    document.getElementById("expiry-group").style.display = type === "manual" ? "none" : "block";
}
updateExpiryVisibility();
</script>

<?php require_once APP_ROOT . "/views/layouts/footer.php"; ?>
