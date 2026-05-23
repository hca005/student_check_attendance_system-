<?php
// views/student/quiz_take.php
// Giao diện làm bài quiz – submit bằng AJAX fetch
// Dữ liệu từ public/student/quiz.php (action=take):
//   $quiz (array), $questions (array)
require_once APP_ROOT . '/views/layouts/header.php';
?>

<!-- Back link -->
<a href="<?= APP_URL ?>/student/quiz.php"
   style="display:inline-flex;align-items:center;gap:6px;font-size:13px;color:#64748B;
          text-decoration:none;margin-bottom:16px">
  ← Quay lại danh sách quiz
</a>

<div class="page-title"><?= htmlspecialchars($quiz['title']) ?></div>
<?php if (!empty($quiz['description'])): ?>
<p class="page-sub"><?= htmlspecialchars($quiz['description']) ?></p>
<?php endif; ?>

<!-- Info bar -->
<div style="display:flex;gap:20px;align-items:center;flex-wrap:wrap;
            padding:12px 16px;background:#F8FAFC;border-radius:10px;margin-bottom:24px">
  <span style="font-size:13px;color:#64748B">
    📋 <strong style="color:#374151"><?= count($questions) ?></strong> câu hỏi
  </span>
  <?php if ($quiz['time_limit_minutes']): ?>
  <span style="font-size:13px;font-weight:700;color:#D97706" id="timer-wrap">
    ⏱ Còn: <span id="timer-display">--:--</span>
  </span>
  <?php else: ?>
  <span style="font-size:13px;color:#64748B">⏱ Không giới hạn thời gian</span>
  <?php endif; ?>
  <span style="font-size:13px;color:#64748B" id="answered-count">
    ✏️ Đã trả lời: <strong id="cnt">0</strong>/<?= count($questions) ?>
  </span>
</div>

<!-- Progress bar -->
<div style="background:#F1F5F9;border-radius:99px;height:6px;margin-bottom:24px">
  <div id="progress-bar" style="background:#2563EB;height:6px;border-radius:99px;width:0%;transition:width .3s"></div>
</div>

<!-- Questions -->
<div id="quiz-body">
  <?php foreach ($questions as $idx => $q):
    $opts = [
      'A' => $q['option_a'],
      'B' => $q['option_b'],
      'C' => $q['option_c'] ?? null,
      'D' => $q['option_d'] ?? null,
    ];
  ?>
  <div class="card" style="padding:22px;margin-bottom:16px" id="q-<?= $q['id'] ?>">
    <!-- Số thứ tự + điểm -->
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:14px">
      <div style="font-size:12px;font-weight:700;color:#2563EB;background:#EFF6FF;
                  padding:3px 10px;border-radius:99px">
        Câu <?= $idx + 1 ?>
      </div>
      <div style="font-size:12px;color:#94A3B8"><?= $q['points'] ?> điểm</div>
    </div>

    <!-- Nội dung câu hỏi -->
    <div style="font-size:15px;font-weight:600;color:#0F172A;margin-bottom:16px;line-height:1.6">
      <?= htmlspecialchars($q['question_text']) ?>
    </div>

    <!-- Các lựa chọn -->
    <div style="display:flex;flex-direction:column;gap:10px">
      <?php foreach ($opts as $key => $text):
        if ($text === null) continue; ?>
      <label id="opt-<?= $q['id'] ?>-<?= $key ?>"
             style="display:flex;align-items:center;gap:12px;padding:12px 16px;
                    border:2px solid #E2E8F0;border-radius:10px;cursor:pointer;transition:all .15s;
                    user-select:none">
        <input type="radio" name="ans[<?= $q['id'] ?>]" value="<?= $key ?>"
               style="display:none"
               onchange="selectOption(<?= $q['id'] ?>, '<?= $key ?>')">
        <div id="circle-<?= $q['id'] ?>-<?= $key ?>"
             style="width:22px;height:22px;border-radius:50%;border:2px solid #CBD5E1;
                    display:flex;align-items:center;justify-content:center;flex-shrink:0;
                    font-size:11px;font-weight:700;color:#64748B;transition:all .15s">
          <?= $key ?>
        </div>
        <span style="font-size:14px;color:#374151"><?= htmlspecialchars($text) ?></span>
      </label>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Submit section -->
<div style="text-align:center;padding:16px 0 40px">
  <div id="submit-msg" style="display:none;margin-bottom:14px;padding:12px 20px;
       border-radius:10px;font-size:14px;font-weight:600"></div>
  <button id="submit-btn" onclick="submitQuiz()"
          style="padding:14px 48px;background:#2563EB;color:#fff;border:none;border-radius:10px;
                 font-size:15px;font-weight:700;cursor:pointer;transition:opacity .2s">
    📤 Nộp bài
  </button>
</div>

<!-- Result overlay -->
<div id="result-box" style="display:none;text-align:center;padding:50px 20px">
  <div id="r-icon"   style="font-size:60px;margin-bottom:16px"></div>
  <div id="r-score"  style="font-size:32px;font-weight:800;color:#0F172A;margin-bottom:8px"></div>
  <div id="r-msg"    style="font-size:15px;color:#64748B;margin-bottom:24px"></div>
  <a href="<?= APP_URL ?>/student/quiz.php"
     style="padding:12px 32px;background:#2563EB;color:#fff;border-radius:10px;
            text-decoration:none;font-weight:700;font-size:15px">
    Xem danh sách quiz
  </a>
</div>

<script>
const TOTAL_Q  = <?= count($questions) ?>;
const QUIZ_ID  = <?= (int)$quiz['id'] ?>;
const ANSWERS  = {};   // { qid: 'A'/'B'/'C'/'D' }

<?php if ($quiz['time_limit_minutes']): ?>
// ── Timer ────────────────────────────────────────────────
let timeLeft = <?= (int)$quiz['time_limit_minutes'] * 60 ?>;
const timerEl = document.getElementById('timer-display');

function fmtTime(sec) {
  const m = String(Math.floor(sec/60)).padStart(2,'0');
  const s = String(sec % 60).padStart(2,'0');
  return `${m}:${s}`;
}
timerEl.textContent = fmtTime(timeLeft);

const timerInt = setInterval(() => {
  timeLeft--;
  timerEl.textContent = fmtTime(timeLeft);
  if (timeLeft <= 60) {
    document.getElementById('timer-wrap').style.color = '#DC2626';
  }
  if (timeLeft <= 0) {
    clearInterval(timerInt);
    submitQuiz(true);  // auto-submit
  }
}, 1000);
<?php endif; ?>

// ── Chọn đáp án ──────────────────────────────────────────
function selectOption(qid, opt) {
  const prevOpt = ANSWERS[qid];

  // Reset style của đáp án cũ
  if (prevOpt) {
    const oldLabel  = document.getElementById(`opt-${qid}-${prevOpt}`);
    const oldCircle = document.getElementById(`circle-${qid}-${prevOpt}`);
    if (oldLabel)  { oldLabel.style.borderColor = '#E2E8F0'; oldLabel.style.background = ''; }
    if (oldCircle) { oldCircle.style.background = ''; oldCircle.style.borderColor = '#CBD5E1'; oldCircle.style.color = '#64748B'; }
  }

  ANSWERS[qid] = opt;

  // Highlight đáp án mới
  const label  = document.getElementById(`opt-${qid}-${opt}`);
  const circle = document.getElementById(`circle-${qid}-${opt}`);
  if (label)  { label.style.borderColor = '#2563EB'; label.style.background = '#EFF6FF'; }
  if (circle) { circle.style.background = '#2563EB'; circle.style.borderColor = '#2563EB'; circle.style.color = '#fff'; }

  // Cập nhật progress
  updateProgress();
}

function updateProgress() {
  const done = Object.keys(ANSWERS).length;
  document.getElementById('cnt').textContent = done;
  document.getElementById('progress-bar').style.width = `${Math.round(done/TOTAL_Q*100)}%`;
}

// ── Nộp bài ──────────────────────────────────────────────
async function submitQuiz(auto = false) {
  if (!auto && Object.keys(ANSWERS).length < TOTAL_Q) {
    if (!confirm(`Bạn còn ${TOTAL_Q - Object.keys(ANSWERS).length} câu chưa trả lời. Vẫn nộp bài?`)) return;
  }

  const btn = document.getElementById('submit-btn');
  btn.disabled    = true;
  btn.textContent = '⏳ Đang nộp...';

  const body = new URLSearchParams();
  body.append('quiz_id', QUIZ_ID);
  for (const [qid, opt] of Object.entries(ANSWERS)) {
    body.append(`answers[${qid}]`, opt);
  }

  try {
    const res  = await fetch('<?= APP_URL ?>/student/quiz.php?action=submit', {
      method:  'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body:    body.toString(),
    });
    const data = await res.json();

    if (data.success) {
      <?php if ($quiz['time_limit_minutes']): ?>
      clearInterval(timerInt);
      <?php endif; ?>

      const pct   = data.max > 0 ? Math.round(data.score / data.max * 100) : 0;
      const color = pct >= 70 ? '#059669' : (pct >= 50 ? '#D97706' : '#DC2626');
      const icon  = pct >= 70 ? '🎉' : (pct >= 50 ? '👍' : '📚');
      const msg   = pct >= 70 ? 'Xuất sắc! Kết quả rất tốt.' : (pct >= 50 ? 'Đạt yêu cầu. Tiếp tục cố gắng!' : 'Cần ôn tập thêm nhé.');

      document.getElementById('quiz-body').style.display    = 'none';
      document.getElementById('submit-btn').style.display   = 'none';
      document.getElementById('r-icon').textContent         = icon;
      document.getElementById('r-score').textContent        = `${data.score} / ${data.max} điểm  (${pct}%)`;
      document.getElementById('r-score').style.color        = color;
      document.getElementById('r-msg').textContent          = msg;
      document.getElementById('result-box').style.display   = 'block';
      document.getElementById('progress-bar').style.background = color;
      document.getElementById('progress-bar').style.width   = '100%';
    } else {
      showErr(data.message);
      btn.disabled    = false;
      btn.textContent = '📤 Nộp bài';
    }
  } catch(e) {
    showErr('Lỗi kết nối. Vui lòng thử lại.');
    btn.disabled    = false;
    btn.textContent = '📤 Nộp bài';
  }
}

function showErr(msg) {
  const el = document.getElementById('submit-msg');
  el.textContent       = msg;
  el.style.background  = '#FEE2E2';
  el.style.color       = '#991B1B';
  el.style.display     = 'block';
}
</script>

<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>