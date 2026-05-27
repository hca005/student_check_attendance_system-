<?php require_once APP_ROOT . '/views/layouts/header.php'; ?>

<div class="page-title">Phương thức Điểm danh - <?= htmlspecialchars($session['course_code']) ?></div>
<p class="page-sub">Buổi: <?= date('d/m/Y', strtotime($session['session_date'])) ?> | <?= substr($session['start_time'],0,5) ?> – <?= substr($session['end_time'],0,5) ?></p>

<?php if (isset($_SESSION['success'])): ?>
<div style="background:#D1FAE5;border:1px solid #10B981;color:#065F46;padding:12px 16px;border-radius:8px;margin-bottom:16px">
    ✓ <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
</div>
<?php endif; ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
    <div style="font-weight:700;font-size:14px">Danh sách phương thức</div>
    <a href="<?= APP_URL ?>/teacher/attendance/methods_form.php?session_id=<?= $session['id'] ?>" class="btn btn-primary btn-sm">+ Tạo Phương thức</a>
</div>

<?php foreach ($methods as $m): ?>
<div class="card" style="margin-bottom:16px;padding:20px">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px">
        
        <!-- Left: Info -->
        <div style="flex:1;min-width:200px">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px">
                <?php if ($m['method_type']==='qr'): ?>
                    <span class="badge badge-primary">QR Code</span>
                <?php elseif ($m['method_type']==='otp'): ?>
                    <span class="badge badge-success">OTP</span>
                <?php else: ?>
                    <span class="badge badge-gray">Thủ công</span>
                <?php endif; ?>
                <?php if ($m['is_active']): ?>
                    <span class="badge badge-success">Đang hoạt động</span>
                <?php else: ?>
                    <span class="badge badge-gray">Tắt</span>
                <?php endif; ?>
            </div>

            <?php if ($m['expires_at']): ?>
            <div style="margin-bottom:12px">
                <div style="font-size:12px;color:#94A3B8;margin-bottom:4px">Hết hạn lúc</div>
                <div style="font-size:13px;font-weight:600"><?= date('H:i d/m/Y', strtotime($m['expires_at'])) ?></div>
                <div id="countdown-<?= $m['id'] ?>" style="font-size:13px;color:#EF4444;font-weight:600;margin-top:4px"></div>
            </div>
            <?php endif; ?>

            <!-- OTP display + copy -->
            <?php if ($m['method_type']==='otp' && $m['token']): ?>
            <div style="margin-bottom:12px">
                <div style="font-size:12px;color:#94A3B8;margin-bottom:6px">Mã OTP</div>
                <div style="display:flex;align-items:center;gap:10px">
                    <span id="otp-<?= $m['id'] ?>" style="font-size:32px;font-weight:800;letter-spacing:8px;color:#2563EB;font-family:monospace"><?= htmlspecialchars($m['token']) ?></span>
                    <button onclick="copyOTP('<?= htmlspecialchars($m['token']) ?>', 'copy-btn-<?= $m['id'] ?>')" id="copy-btn-<?= $m['id'] ?>" class="btn btn-outline btn-sm">📋 Copy</button>
                </div>
            </div>
            <?php endif; ?>

            <div style="display:flex;gap:6px;margin-top:8px">
                <a href="<?= APP_URL ?>/teacher/attendance/methods_form.php?session_id=<?= $session['id'] ?>&id=<?= $m['id'] ?>" class="btn btn-outline btn-sm">Sửa</a>
                <a href="<?= APP_URL ?>/teacher/attendance/methods_delete.php?id=<?= $m['id'] ?>&session_id=<?= $session['id'] ?>" class="btn btn-sm" style="background:#FEE2E2;color:#991B1B" onclick="return confirm('Xác nhận xóa?')">Xóa</a>
            </div>
        </div>

        <!-- Right: QR Code -->
        <?php if ($m['method_type']==='qr' && $m['token']): ?>
        <div style="text-align:center">
            <div style="font-size:12px;color:#94A3B8;margin-bottom:8px">QR Code điểm danh</div>
            <div id="qr-<?= $m['id'] ?>"></div>
            <div style="font-size:11px;color:#94A3B8;margin-top:6px;font-family:monospace;word-break:break-all;max-width:180px"><?= htmlspecialchars(substr($m['token'],0,16)) ?>...</div>
        </div>
        <?php endif; ?>

    </div>
</div>
<?php endforeach; ?>

<?php if (empty($methods)): ?>
<div class="card" style="padding:32px;text-align:center;color:#94A3B8">Chưa có phương thức nào</div>
<?php endif; ?>

<div style="margin-top:16px">
    <a href="<?= APP_URL ?>/teacher/attendance.php" class="btn btn-outline btn-sm">← Quay lại</a>
</div>

<!-- QR Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script>
// Generate QR codes
<?php foreach ($methods as $m): ?>
<?php if ($m['method_type']==='qr' && $m['token']): ?>
new QRCode(document.getElementById("qr-<?= $m['id'] ?>"), {
    text: "ATTEND:<?= htmlspecialchars($m['token']) ?>",
    width: 160,
    height: 160,
    colorDark: "#0F172A",
    colorLight: "#ffffff",
    correctLevel: QRCode.CorrectLevel.H
});
<?php endif; ?>
<?php endforeach; ?>

// Countdown timers
<?php foreach ($methods as $m): ?>
<?php if ($m['expires_at']): ?>
(function() {
    var expiry = new Date("<?= $m['expires_at'] ?>").getTime();
    var el = document.getElementById("countdown-<?= $m['id'] ?>");
    var timer = setInterval(function() {
        var now = new Date().getTime();
        var diff = expiry - now;
        if (diff <= 0) {
            el.innerHTML = "⛔ Đã hết hạn";
            el.style.color = "#EF4444";
            clearInterval(timer);
            return;
        }
        var h = Math.floor(diff / 3600000);
        var m = Math.floor((diff % 3600000) / 60000);
        var s = Math.floor((diff % 60000) / 1000);
        el.innerHTML = "⏱ Còn " + (h>0?h+"g ":"") + m + "p " + s + "s";
        el.style.color = diff < 60000 ? "#EF4444" : "#F59E0B";
    }, 1000);
})();
<?php endif; ?>
<?php endforeach; ?>

// Copy OTP
function copyOTP(token, btnId) {
    navigator.clipboard.writeText(token).then(function() {
        var btn = document.getElementById(btnId);
        btn.innerHTML = "✓ Đã copy!";
        btn.style.background = "#D1FAE5";
        btn.style.color = "#065F46";
        setTimeout(function() {
            btn.innerHTML = "📋 Copy";
            btn.style.background = "";
            btn.style.color = "";
        }, 2000);
    });
}
</script>

<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>
