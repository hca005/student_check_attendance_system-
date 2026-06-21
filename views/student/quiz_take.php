<?php
// views/student/quiz_take.php
// Giao diện làm bài quiz – submit bằng AJAX fetch
// Dữ liệu từ public/student/quiz.php (action=take):
//   $quiz (array), $questions (array)
require_once APP_ROOT . '/views/layouts/header.php';
?>

<a href="<?= APP_URL ?>/student/quiz.php" class="btn btn-outline btn-sm" style="margin-bottom:16px;display:inline-flex">
  <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
  Back to quizzes
</a>

<div class="admin-page-title">
  <div class="left">
    <h1><?= htmlspecialchars($quiz['title']) ?></h1>
    <?php if ($quiz['description']): ?><p><?= htmlspecialchars($quiz['description']) ?></p><?php endif; ?>
  </div>
</div>

<div class="card" style="padding:14px 18px;margin-bottom:20px;display:flex;gap:24px;align-items:center;flex-wrap:wrap">
  <span style="font-size:13px;color:var(--text-muted);display:flex;align-items:center;gap:6px">
    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
    <?= count($questions) ?> questions
  </span>
  <?php if ($quiz['time_limit_minutes']): ?>
  <span id="timer-wrap" style="font-size:13px;font-weight:700;color:#D97706;display:flex;align-items:center;gap:6px">
    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
    Time left: <span id="timer-display">--:--</span>
  </span>
  <?php else: ?>
  <span style="font-size:13px;color:var(--text-muted)">No time limit</span>
  <?php endif; ?>
  <span style="font-size:13px;color:var(--text-muted)">Answered: <strong id="cnt">0</strong>/<?= count($questions) ?></span>
</div>

<div class="progress-track" style="width:100%;margin-bottom:20px"><div id="progress-bar" class="progress-fill" style="width:0%"></div></div>

<div id="quiz-body">
  <?php foreach ($questions as $idx => $q):
    $opts = ['A' => $q['option_a'], 'B' => $q['option_b'], 'C' => $q['option_c'] ?? null, 'D' => $q['option_d'] ?? null];
  ?>
  <div class="card quiz-question-card" id="q-<?= $q['id'] ?>">
    <div style="display:flex;justify-content:space-between;margin-bottom:14px">
      <span class="badge badge-primary">Question <?= $idx + 1 ?></span>
      <span style="font-size:12px;color:var(--text-muted)"><?= $q['points'] ?> pts</span>
    </div>
    <div style="font-size:15px;font-weight:600;margin-bottom:16px;line-height:1.6"><?= htmlspecialchars($q['question_text']) ?></div>

    <?php foreach ($opts as $key => $text): if ($text === null) continue; ?>
    <label class="quiz-option" id="opt-<?= $q['id'] ?>-<?= $key ?>">
      <input type="radio" name="ans[<?= $q['id'] ?>]" value="<?= $key ?>" style="display:none" onchange="selectOption(<?= $q['id'] ?>, '<?= $key ?>')">
      <span class="opt-letter"><?= $key ?></span>
      <span style="font-size:14px"><?= htmlspecialchars($text) ?></span>
    </label>
    <?php endforeach; ?>
  </div>
  <?php endforeach; ?>
</div>

<div style="text-align:center;padding:16px 0 40px">
  <div id="submit-msg" class="alert alert-danger" style="display:none;justify-content:center;margin-bottom:14px"></div>
  <button id="submit-btn" class="btn btn-primary" style="padding:12px 40px" onclick="submitQuiz()">Submit quiz</button>
</div>

<div id="result-box" class="card" style="display:none;text-align:center;padding:48px 20px">
  <div id="r-ring" class="score-ring" style="--ring-size:120px;margin:0 auto 20px">
    <span class="score-ring-label" id="r-pct" style="font-size:30px"></span>
  </div>
  <div id="r-score" style="font-size:16px;font-weight:700;margin-bottom:6px"></div>
  <div id="r-msg" style="font-size:14px;color:var(--text-muted);margin-bottom:22px"></div>
  <a href="<?= APP_URL ?>/student/quiz.php" class="btn btn-primary">Back to quizzes</a>
</div>

<script>
const TOTAL_Q = <?= count($questions) ?>;
const QUIZ_ID = <?= (int)$quiz['id'] ?>;
const ANSWERS = {};

<?php if ($quiz['time_limit_minutes']): ?>
let timeLeft = <?= (int)$quiz['time_limit_minutes'] * 60 ?>;
const timerEl = document.getElementById('timer-display');
const fmt = s => String(Math.floor(s/60)).padStart(2,'0') + ':' + String(s%60).padStart(2,'0');
timerEl.textContent = fmt(timeLeft);
const timerInt = setInterval(() => {
  timeLeft--;
  timerEl.textContent = fmt(timeLeft);
  if (timeLeft <= 60) document.getElementById('timer-wrap').style.color = '#DC2626';
  if (timeLeft <= 0) { clearInterval(timerInt); submitQuiz(true); }
}, 1000);
<?php endif; ?>

function selectOption(qid, opt) {
  const prev = ANSWERS[qid];
  if (prev) document.getElementById(`opt-${qid}-${prev}`)?.classList.remove('selected');
  ANSWERS[qid] = opt;
  document.getElementById(`opt-${qid}-${opt}`)?.classList.add('selected');
  document.getElementById('cnt').textContent = Object.keys(ANSWERS).length;
  document.getElementById('progress-bar').style.width = Math.round(Object.keys(ANSWERS).length / TOTAL_Q * 100) + '%';
}

async function submitQuiz(auto = false) {
  if (!auto && Object.keys(ANSWERS).length < TOTAL_Q) {
    if (!confirm(`${TOTAL_Q - Object.keys(ANSWERS).length} question(s) left unanswered. Submit anyway?`)) return;
  }
  const btn = document.getElementById('submit-btn');
  btn.disabled = true; btn.textContent = 'Submitting...';

  const body = new URLSearchParams();
  body.append('quiz_id', QUIZ_ID);
  for (const [qid, opt] of Object.entries(ANSWERS)) body.append(`answers[${qid}]`, opt);

  try {
    const res  = await fetch('<?= APP_URL ?>/student/quiz.php?action=submit', {
      method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: body.toString()
    });
    const data = await res.json();
    if (data.success) {
      <?php if ($quiz['time_limit_minutes']): ?>clearInterval(timerInt);<?php endif; ?>
      const pct = data.max > 0 ? Math.round(data.score / data.max * 100) : 0;
      const color = pct >= 70 ? '#10B981' : (pct >= 50 ? '#F59E0B' : '#EF4444');
      document.getElementById('quiz-body').style.display = 'none';
      btn.style.display = 'none';
      const ring = document.getElementById('r-ring');
      ring.style.setProperty('--pct', pct);
      ring.style.setProperty('--ring-color', color);
      document.getElementById('r-pct').textContent = pct + '%';
      document.getElementById('r-score').textContent = `${data.score} / ${data.max} points`;
      document.getElementById('r-msg').textContent = pct >= 70 ? 'Great result!' : (pct >= 50 ? 'You passed. Keep practicing.' : 'Consider reviewing this material.');
      document.getElementById('result-box').style.display = 'block';
    } else {
      const m = document.getElementById('submit-msg'); m.textContent = data.message; m.style.display = 'flex';
      btn.disabled = false; btn.textContent = 'Submit quiz';
    }
  } catch {
    btn.disabled = false; btn.textContent = 'Submit quiz';
  }
}
</script>

<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>