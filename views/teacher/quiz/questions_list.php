<?php require_once APP_ROOT . '/views/layouts/header.php'; ?>

<div class="page-title">Câu hỏi - <?= htmlspecialchars($quiz['title']) ?></div>
<p class="page-sub">Buổi: <?= date('d/m/Y', strtotime($quiz['session_date'])) ?> | <?= htmlspecialchars($quiz['course_code']) ?></p>

<?php if (isset($_SESSION['success'])): ?>
<div style="background:#D1FAE5;border:1px solid #10B981;color:#065F46;padding:12px 16px;border-radius:8px;margin-bottom:16px">
    ✓ <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
</div>
<?php endif; ?>

<!-- Thống kê nhanh -->
<?php
$totalPoints = array_sum(array_column($questions, 'points'));
$countA = count(array_filter($questions, fn($q) => $q['correct_option']==='A'));
$countB = count(array_filter($questions, fn($q) => $q['correct_option']==='B'));
$countC = count(array_filter($questions, fn($q) => $q['correct_option']==='C'));
$countD = count(array_filter($questions, fn($q) => $q['correct_option']==='D'));
?>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:12px;margin-bottom:20px">
    <div class="card" style="padding:16px;text-align:center">
        <div style="font-size:24px;font-weight:800;color:#2563EB"><?= count($questions) ?></div>
        <div style="font-size:12px;color:#94A3B8">Tổng câu hỏi</div>
    </div>
    <div class="card" style="padding:16px;text-align:center">
        <div style="font-size:24px;font-weight:800;color:#10B981"><?= $totalPoints ?></div>
        <div style="font-size:12px;color:#94A3B8">Tổng điểm</div>
    </div>
    <div class="card" style="padding:16px;text-align:center">
        <div style="display:flex;justify-content:center;gap:8px;margin-bottom:4px">
            <span style="background:#EFF6FF;color:#2563EB;padding:2px 8px;border-radius:4px;font-weight:700">A:<?= $countA ?></span>
            <span style="background:#F0FDF4;color:#10B981;padding:2px 8px;border-radius:4px;font-weight:700">B:<?= $countB ?></span>
            <span style="background:#FFF7ED;color:#F59E0B;padding:2px 8px;border-radius:4px;font-weight:700">C:<?= $countC ?></span>
            <span style="background:#FEF2F2;color:#EF4444;padding:2px 8px;border-radius:4px;font-weight:700">D:<?= $countD ?></span>
        </div>
        <div style="font-size:12px;color:#94A3B8">Phân bố đáp án</div>
    </div>
</div>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
    <div style="font-size:13px;color:#64748B">Kéo thả để sắp xếp thứ tự</div>
    <a href="<?= APP_URL ?>/teacher/quiz/questions_form.php?quiz_id=<?= $quiz['id'] ?>" class="btn btn-primary btn-sm">+ Thêm Câu hỏi</a>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th style="width:40px"></th><th>#</th><th>Câu hỏi</th><th>Đáp án Đúng</th><th>Điểm</th><th>Hành động</th></tr>
            </thead>
            <tbody id="sortable-questions">
            <?php if (empty($questions)): ?>
                <tr><td colspan="6" style="text-align:center;color:#94A3B8;padding:32px">Chưa có câu hỏi nào</td></tr>
            <?php else: ?>
            <?php foreach ($questions as $q): ?>
            <tr data-id="<?= $q['id'] ?>" style="cursor:grab">
                <td style="color:#94A3B8;font-size:18px;text-align:center">⠿</td>
                <td style="color:#94A3B8;font-size:13px;width:40px"><?= $q['order_num'] ?></td>
                <td>
                    <!-- Preview popup on hover -->
                    <div class="question-preview" style="position:relative">
                        <div style="font-weight:600;font-size:13px;margin-bottom:6px;cursor:pointer" onmouseenter="showPreview(<?= $q['id'] ?>)" onmouseleave="hidePreview(<?= $q['id'] ?>)">
                            <?= htmlspecialchars($q['question_text']) ?>
                            <span style="font-size:11px;color:#2563EB;margin-left:6px">👁 xem</span>
                        </div>
                        <!-- Popup -->
                        <div id="preview-<?= $q['id'] ?>" style="display:none;position:absolute;left:0;top:100%;z-index:999;background:#fff;border:1px solid #E2E8F0;border-radius:12px;padding:16px;min-width:320px;box-shadow:0 8px 24px rgba(0,0,0,0.12)">
                            <div style="font-weight:700;font-size:13px;margin-bottom:12px;color:#0F172A"><?= htmlspecialchars($q['question_text']) ?></div>
                            <?php foreach (['a'=>'A','b'=>'B','c'=>'C','d'=>'D'] as $key=>$label): ?>
                            <?php if ($q['option_'.$key]): ?>
                            <div style="display:flex;gap:8px;align-items:center;margin-bottom:8px;padding:8px;border-radius:8px;background:<?= $q['correct_option']===$label?'#D1FAE5':'#F8FAFC' ?>">
                                <span style="width:24px;height:24px;border-radius:50%;background:<?= $q['correct_option']===$label?'#10B981':'#E2E8F0' ?>;color:<?= $q['correct_option']===$label?'#fff':'#64748B' ?>;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;flex-shrink:0"><?= $label ?></span>
                                <span style="font-size:13px;color:<?= $q['correct_option']===$label?'#065F46':'#374151' ?>"><?= htmlspecialchars($q['option_'.$key]) ?></span>
                                <?php if ($q['correct_option']===$label): ?><span style="margin-left:auto;font-size:12px;color:#10B981">✓ Đúng</span><?php endif; ?>
                            </div>
                            <?php endif; ?>
                            <?php endforeach; ?>
                            <div style="font-size:12px;color:#94A3B8;margin-top:8px">Điểm: <?= $q['points'] ?></div>
                        </div>
                    </div>
                    <div style="font-size:12px;color:#64748B;display:flex;gap:8px;flex-wrap:wrap">
                        <span style="background:#F1F5F9;padding:2px 8px;border-radius:4px">A: <?= htmlspecialchars(mb_substr($q['option_a'],0,20)) ?><?= mb_strlen($q['option_a'])>20?'...':'' ?></span>
                        <span style="background:#F1F5F9;padding:2px 8px;border-radius:4px">B: <?= htmlspecialchars(mb_substr($q['option_b'],0,20)) ?><?= mb_strlen($q['option_b'])>20?'...':'' ?></span>
                        <?php if ($q['option_c']): ?><span style="background:#F1F5F9;padding:2px 8px;border-radius:4px">C: <?= htmlspecialchars(mb_substr($q['option_c'],0,20)) ?><?= mb_strlen($q['option_c'])>20?'...':'' ?></span><?php endif; ?>
                        <?php if ($q['option_d']): ?><span style="background:#F1F5F9;padding:2px 8px;border-radius:4px">D: <?= htmlspecialchars(mb_substr($q['option_d'],0,20)) ?><?= mb_strlen($q['option_d'])>20?'...':'' ?></span><?php endif; ?>
                    </div>
                </td>
                <td>
                    <span style="width:28px;height:28px;border-radius:50%;background:#D1FAE5;color:#065F46;display:inline-flex;align-items:center;justify-content:center;font-weight:700;font-size:13px">
                        <?= $q['correct_option'] ?>
                    </span>
                </td>
                <td style="font-weight:600"><?= $q['points'] ?></td>
                <td>
                    <div style="display:flex;gap:6px">
                        <a href="<?= APP_URL ?>/teacher/quiz/questions_form.php?quiz_id=<?= $quiz['id'] ?>&id=<?= $q['id'] ?>" class="btn btn-outline btn-sm">Sửa</a>
                        <form method="POST" action="<?= APP_URL ?>/teacher/quiz/delete_question.php" style="margin:0" onsubmit="return confirm('Xóa câu hỏi này?')">
                            <input type="hidden" name="id" value="<?= $q['id'] ?>">
                            <input type="hidden" name="quiz_id" value="<?= $quiz['id'] ?>">
                            <button type="submit" class="btn btn-sm" style="background:#FEE2E2;color:#991B1B;border:none">Xóa</button>
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

<div style="margin-top:16px;display:flex;gap:10px">
    <a href="<?= APP_URL ?>/teacher/quiz/sessions_list.php?session_id=<?= $quiz['session_id'] ?>" class="btn btn-outline btn-sm">← Quay lại Quiz</a>
    <a href="<?= APP_URL ?>/teacher/dashboard.php" class="btn btn-outline btn-sm">Dashboard</a>
</div>

<!-- SortableJS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
<script>
// Kéo thả sắp xếp
var el = document.getElementById('sortable-questions');
if (el) {
    new Sortable(el, {
        animation: 150,
        handle: 'tr',
        ghostClass: 'sortable-ghost',
        onEnd: function(evt) {
            var rows = el.querySelectorAll('tr[data-id]');
            var ids = [];
            rows.forEach(function(row, idx) {
                ids.push(row.getAttribute('data-id'));
                row.querySelector('td:nth-child(2)').textContent = idx + 1;
            });
            // Save order
            fetch('<?= APP_URL ?>/teacher/quiz/reorder_questions.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({quiz_id: <?= $quiz['id'] ?>, ids: ids})
            });
        }
    });
}

// Preview popup
function showPreview(id) {
    document.getElementById('preview-' + id).style.display = 'block';
}
function hidePreview(id) {
    setTimeout(function() {
        var el = document.getElementById('preview-' + id);
        if (el) el.style.display = 'none';
    }, 200);
}
</script>

<style>
.sortable-ghost { opacity: 0.4; background: #EFF6FF; }
tr[data-id]:hover { background: #F8FAFC; }
</style>

<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>
