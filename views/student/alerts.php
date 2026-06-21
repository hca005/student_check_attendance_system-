<?php
// views/student/alerts.php
// Trang xem toàn bộ cảnh báo cá nhân của student
require_once APP_ROOT . '/views/layouts/header.php';
?>

<div class="page-title">My Alerts</div>
<p class="page-sub">Danh sách cảnh báo từ hệ thống về tình trạng học tập của bạn</p>

<?php
$openAlerts     = array_filter($alerts, fn($a) => $a['status'] === 'pending');
$resolvedAlerts = array_filter($alerts, fn($a) => $a['status'] !== 'pending');

$typeLabel = [
    'low_attendance' => ['label' => 'Vắng nhiều',     'icon' => '❌', 'bg' => '#FEE2E2', 'color' => '#991B1B'],
    'low_engagement' => ['label' => 'Tương tác thấp', 'icon' => '📉', 'bg' => '#FEF3C7', 'color' => '#92400E'],
];
$severityLabel = [
    'high'   => ['label' => 'Nghiêm trọng', 'bg' => '#FEE2E2', 'color' => '#991B1B'],
    'medium' => ['label' => 'Trung bình',   'bg' => '#FEF3C7', 'color' => '#92400E'],
    'low'    => ['label' => 'Nhẹ',          'bg' => '#F1F5F9', 'color' => '#475569'],
];
?>

<div style="display:flex;gap:12px;margin-bottom:24px;flex-wrap:wrap">
    <div class="card" style="padding:14px 20px;display:flex;align-items:center;gap:12px;min-width:150px">
        <span style="font-size:24px">⚠️</span>
        <div>
            <div style="font-size:22px;font-weight:800;color:#DC2626"><?= count($openAlerts) ?></div>
            <div style="font-size:12px;color:#94A3B8">Cảnh báo chưa xử lý</div>
        </div>
    </div>
    <div class="card" style="padding:14px 20px;display:flex;align-items:center;gap:12px;min-width:150px">
        <span style="font-size:24px">✅</span>
        <div>
            <div style="font-size:22px;font-weight:800;color:#059669"><?= count($resolvedAlerts) ?></div>
            <div style="font-size:12px;color:#94A3B8">Đã xử lý</div>
        </div>
    </div>
</div>

<?php if (empty($alerts)): ?>
<div class="card" style="padding:50px;text-align:center;color:#94A3B8">
    <div style="font-size:48px;margin-bottom:14px">🎉</div>
    <div style="font-size:16px;font-weight:700;color:#0F172A;margin-bottom:6px">Tuyệt vời! Không có cảnh báo nào</div>
    <div style="font-size:14px">Bạn đang tham gia học tập rất tốt. Hãy tiếp tục duy trì nhé!</div>
</div>

<?php else: ?>

<?php if (!empty($openAlerts)): ?>
<div style="font-weight:700;font-size:14px;color:#374151;margin-bottom:12px">
    🔴 Cảnh báo chưa xử lý (<?= count($openAlerts) ?>)
</div>
<div style="display:flex;flex-direction:column;gap:12px;margin-bottom:28px">
    <?php foreach ($openAlerts as $a):
        $t = $typeLabel[$a['alert_type']] ?? ['label'=>$a['alert_type'],'icon'=>'⚠️','bg'=>'#FEF3C7','color'=>'#92400E'];
        $s = $severityLabel[$a['severity']] ?? $severityLabel['medium'];
    ?>
    <div style="background:#fff;border-radius:12px;padding:18px 20px;
                border-left:4px solid <?= $t['color'] ?>;
                box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:8px">
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                <span style="font-size:20px"><?= $t['icon'] ?></span>
                <span style="background:<?= $t['bg'] ?>;color:<?= $t['color'] ?>;
                             padding:2px 10px;border-radius:99px;font-size:11px;font-weight:700">
                    <?= $t['label'] ?>
                </span>
                <span style="background:<?= $s['bg'] ?>;color:<?= $s['color'] ?>;
                             padding:2px 10px;border-radius:99px;font-size:11px;font-weight:700">
                    <?= $s['label'] ?>
                </span>
                <span style="background:#EFF6FF;color:#2563EB;
                             padding:2px 10px;border-radius:99px;font-size:11px;font-weight:700">
                    <?= htmlspecialchars($a['course_code']) ?>
                </span>
            </div>
            <span style="font-size:12px;color:#94A3B8">
                <?= date('d/m/Y', strtotime($a['created_at'])) ?>
            </span>
        </div>
        <div style="font-size:13px;color:#374151;margin-top:12px;line-height:1.6">
            <?= htmlspecialchars($a['message']) ?>
        </div>
        <div style="font-size:12px;color:#94A3B8;margin-top:8px">
            Môn: <strong><?= htmlspecialchars($a['course_name']) ?></strong>
        </div>
        <button onclick="dismissAlert(<?= $a['alert_id'] ?>, this)"
                style="margin-top:12px;padding:6px 16px;background:#F1F5F9;color:#374151;
                       border:none;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer">
            Đánh dấu đã xem
        </button>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if (!empty($resolvedAlerts)): ?>
<div style="font-weight:700;font-size:14px;color:#374151;margin-bottom:12px">
    ✅ Đã xử lý (<?= count($resolvedAlerts) ?>)
</div>
<div style="display:flex;flex-direction:column;gap:10px">
    <?php foreach ($resolvedAlerts as $a):
        $t = $typeLabel[$a['alert_type']] ?? ['label'=>$a['alert_type'],'icon'=>'⚠️','bg'=>'#F1F5F9','color'=>'#475569'];
    ?>
    <div style="background:#F8FAFC;border-radius:12px;padding:16px 20px;
                border-left:4px solid #CBD5E1;opacity:.8">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:8px">
            <div style="display:flex;align-items:center;gap:8px">
                <span style="font-size:18px"><?= $t['icon'] ?></span>
                <span style="background:#F1F5F9;color:#64748B;padding:2px 10px;
                             border-radius:99px;font-size:11px;font-weight:700"><?= $t['label'] ?></span>
                <span style="background:#F1F5F9;color:#64748B;padding:2px 10px;
                             border-radius:99px;font-size:11px;font-weight:700"><?= htmlspecialchars($a['course_code']) ?></span>
            </div>
            <div style="display:flex;align-items:center;gap:8px">
                <span style="font-size:12px;color:#94A3B8"><?= date('d/m/Y', strtotime($a['created_at'])) ?></span>
                <span style="background:#D1FAE5;color:#065F46;padding:3px 10px;
                             border-radius:99px;font-size:11px;font-weight:700">
                    <?= $a['status'] === 'resolved' ? 'Đã giải quyết' : 'Đã xem' ?>
                </span>
            </div>
        </div>
        <div style="font-size:13px;color:#64748B;margin-top:10px;line-height:1.6">
            <?= htmlspecialchars($a['message']) ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php endif; ?>

<script>
async function dismissAlert(id, btn) {
    btn.disabled = true;
    btn.textContent = 'Đang xử lý...';
    try {
        const res  = await fetch('<?= APP_URL ?>/student/alerts.php?action=dismiss', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `id=${id}`
        });
        const data = await res.json();
        if (data.success) {
            location.reload();
        } else {
            btn.disabled = false;
            btn.textContent = 'Đánh dấu đã xem';
            alert(data.message);
        }
    } catch {
        btn.disabled = false;
        btn.textContent = 'Đánh dấu đã xem';
    }
}
</script>

<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>
