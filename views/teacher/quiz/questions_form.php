<?php require_once APP_ROOT . '/views/layouts/header.php'; ?>

<div class="page-title"><?= $questionId ? 'Sửa Câu hỏi' : 'Thêm Câu hỏi Mới' ?></div>
<p class="page-sub">Quiz: <?= htmlspecialchars($quiz['title']) ?></p>

<?php if ($error): ?>
<div style="background:#FEE2E2;border:1px solid #EF4444;color:#991B1B;padding:12px 16px;border-radius:8px;margin-bottom:16px">
    ✕ <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<div style="max-width:600px">
<div class="card" style="padding:24px">
    <form method="POST">
        <input type="hidden" name="quiz_id" value="<?= $quiz['id'] ?>">
        <?php if ($questionId): ?>
        <input type="hidden" name="id" value="<?= $questionId ?>">
        <?php endif; ?>

        <div style="margin-bottom:16px">
            <label style="font-size:13px;font-weight:600;color:#374151;display:block;margin-bottom:6px">Nội dung Câu hỏi *</label>
            <textarea name="question_text" rows="3" required style="width:100%;padding:8px 12px;border:1px solid #E2E8F0;border-radius:8px;font-size:14px;resize:vertical"><?= htmlspecialchars($question['question_text'] ?? '') ?></textarea>
        </div>

        <?php foreach (['A','B','C','D'] as $opt): ?>
        <div style="margin-bottom:12px">
            <label style="font-size:13px;font-weight:600;color:#374151;display:block;margin-bottom:6px">
                Đáp án <?= $opt ?> <?= in_array($opt,['A','B']) ? '<span style="color:#EF4444">*</span>' : '<span style="color:#94A3B8">(tuỳ chọn)</span>' ?>
            </label>
            <div style="display:flex;align-items:center;gap:8px">
                <div style="width:28px;height:28px;border-radius:50%;background:<?= in_array($opt,['A','B'])?'#2563EB':'#E2E8F0' ?>;color:<?= in_array($opt,['A','B'])?'#fff':'#64748B' ?>;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;flex-shrink:0"><?= $opt ?></div>
                <input type="text" name="option_<?= strtolower($opt) ?>" <?= in_array($opt,['A','B']) ? 'required' : '' ?>
                    style="flex:1;padding:8px 12px;border:1px solid #E2E8F0;border-radius:8px;font-size:14px"
                    value="<?= htmlspecialchars($question['option_'.strtolower($opt)] ?? '') ?>"
                    placeholder="Nhập đáp án <?= $opt ?>...">
            </div>
        </div>
        <?php endforeach; ?>

        <div style="margin-bottom:16px;padding:16px;background:#F8FAFC;border-radius:8px;border:1px solid #E2E8F0">
            <label style="font-size:13px;font-weight:600;color:#374151;display:block;margin-bottom:10px">Đáp án Đúng *</label>
            <div style="display:flex;gap:10px">
                <?php foreach (['A','B','C','D'] as $opt): ?>
                <label style="display:flex;align-items:center;gap:6px;cursor:pointer;padding:8px 14px;border:2px solid #E2E8F0;border-radius:8px;font-weight:600;font-size:14px;transition:all 0.2s"
                    onclick="this.parentElement.querySelectorAll('label').forEach(l=>l.style.cssText='display:flex;align-items:center;gap:6px;cursor:pointer;padding:8px 14px;border:2px solid #E2E8F0;border-radius:8px;font-weight:600;font-size:14px');this.style.cssText='display:flex;align-items:center;gap:6px;cursor:pointer;padding:8px 14px;border:2px solid #2563EB;border-radius:8px;font-weight:600;font-size:14px;background:#EFF6FF;color:#2563EB'">
                    <input type="radio" name="correct_option" value="<?= $opt ?>" <?= ($question['correct_option'] ?? '') === $opt ? 'checked' : '' ?> style="accent-color:#2563EB">
                    <?= $opt ?>
                </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div style="margin-bottom:20px">
            <label style="font-size:13px;font-weight:600;color:#374151;display:block;margin-bottom:6px">Điểm *</label>
            <input type="number" name="points" step="0.25" min="0.25" required
                style="width:120px;padding:8px 12px;border:1px solid #E2E8F0;border-radius:8px;font-size:14px"
                value="<?= htmlspecialchars($question['points'] ?? '1.00') ?>">
        </div>

        <div style="display:flex;gap:10px">
            <button type="submit" class="btn btn-primary"><?= $questionId ? 'Cập nhật' : 'Thêm câu hỏi' ?></button>
            <a href="<?= APP_URL ?>/teacher/quiz/questions_list.php?quiz_id=<?= $quiz['id'] ?>" class="btn btn-outline">Quay lại</a>
        </div>
    </form>
</div>
</div>

<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>
