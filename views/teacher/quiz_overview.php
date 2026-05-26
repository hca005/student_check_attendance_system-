<?php require_once APP_ROOT . '/views/layouts/header.php'; ?>

<div class="page-title">Quản lý Quiz</div>
<p class="page-sub">Tất cả quiz trong các buổi học của bạn</p>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px">
<?php if (empty($quizzes)): ?>
    <div class="card" style="padding:32px;text-align:center;color:#94A3B8;grid-column:1/-1">
        Chưa có quiz nào. Vào <a href="<?= APP_URL ?>/teacher/sessions.php">Class Sessions</a> để tạo quiz.
    </div>
<?php else: ?>
<?php foreach ($quizzes as $q): ?>
<div class="card" style="padding:20px">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px">
        <div style="font-weight:700;font-size:14px"><?= htmlspecialchars($q['title']) ?></div>
        <?php
        $st = ['draft'=>['Soạn thảo','badge-gray'],'open'=>['Đang mở','badge-success'],'closed'=>['Đã đóng','']];
        $s = $st[$q['status']] ?? [$q['status'],'badge-gray'];
        ?>
        <span class="badge <?= $s[1] ?>" style="<?= $q['status']==='closed'?'background:#FEE2E2;color:#991B1B':'' ?>"><?= $s[0] ?></span>
    </div>
    <div style="font-size:12px;color:#94A3B8;margin-bottom:10px">
        <?= htmlspecialchars($q['session_title'] ?? '') ?> · <?= date('d/m/Y', strtotime($q['session_date'])) ?>
    </div>
    <div style="display:flex;gap:16px;font-size:13px;color:#64748B;margin-bottom:14px">
        <div><strong style="color:#0F172A"><?= $q['question_count'] ?></strong> câu hỏi</div>
        <div><span class="badge badge-primary"><?= htmlspecialchars($q['course_name']) ?></span></div>
    </div>
    <a href="<?= APP_URL ?>/teacher/quiz/questions_list.php?quiz_id=<?= $q['id'] ?>" class="btn btn-primary btn-sm" style="width:100%;justify-content:center">
        Quản lý câu hỏi
    </a>
</div>
<?php endforeach; ?>
<?php endif; ?>
</div>

<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>
