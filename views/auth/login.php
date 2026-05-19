<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Đăng nhập – <?= APP_NAME ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;height:100vh;display:flex;overflow:hidden;background:#f1f5f9}

/* ── LEFT PANEL ── */
.login-left{
  width:42%;flex-shrink:0;
  background:linear-gradient(160deg,#1D4ED8 0%,#2563EB 55%,#3B82F6 100%);
  display:flex;flex-direction:column;padding:36px 40px;
  position:relative;overflow:hidden;color:#fff;
}
.left-brand{display:flex;align-items:center;gap:12px;margin-bottom:40px}
.left-brand img{width:42px;height:42px;border-radius:10px}
.left-brand span{font-size:16px;font-weight:700;letter-spacing:.01em}
.left-headline{font-size:28px;font-weight:800;line-height:1.25;margin-bottom:14px}
.left-sub{font-size:14px;color:rgba(255,255,255,.8);line-height:1.65;max-width:340px}
.left-image{
  flex:1;display:flex;align-items:flex-end;justify-content:center;
  margin:30px -40px 0;
}
.left-image img{width:100%;object-fit:cover;border-radius:12px 12px 0 0;max-height:280px}
/* Fallback classroom illustration */
.classroom-placeholder{
  width:100%;background:rgba(255,255,255,.1);border-radius:14px;
  height:230px;display:flex;align-items:center;justify-content:center;
  font-size:60px;
}
.left-footer{
  margin-top:20px;display:flex;align-items:center;gap:10px;
  font-size:12px;color:rgba(255,255,255,.75);
}
.avatar-stack{display:flex}
.avatar-stack span{
  width:26px;height:26px;border-radius:50%;border:2px solid rgba(255,255,255,.6);
  display:inline-flex;align-items:center;justify-content:center;
  font-size:10px;font-weight:700;color:#fff;margin-left:-8px;first-child{margin-left:0}
}
.avatar-stack span:nth-child(1){background:#F59E0B;margin-left:0}
.avatar-stack span:nth-child(2){background:#10B981}
.avatar-stack span:nth-child(3){background:#EF4444}

/* ── RIGHT PANEL ── */
.login-right{
  flex:1;display:flex;align-items:center;justify-content:center;
  padding:40px;overflow-y:auto;
}
.login-box{width:100%;max-width:400px}
.login-box h1{font-size:26px;font-weight:800;color:#0F172A;margin-bottom:6px}
.login-box .subtitle{font-size:14px;color:#64748B;margin-bottom:32px}

.form-group{margin-bottom:18px}
.form-group label{display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px}
.input-row{position:relative}
.input-row .icon{
  position:absolute;left:13px;top:50%;transform:translateY(-50%);
  color:#9CA3AF;pointer-events:none;font-size:16px;
}
.input-row input{
  width:100%;padding:11px 42px 11px 40px;
  border:1.5px solid #E2E8F0;border-radius:10px;
  font-size:14px;font-family:inherit;background:#fff;color:#0F172A;
  outline:none;transition:border-color .18s,box-shadow .18s;
}
.input-row input:focus{border-color:#2563EB;box-shadow:0 0 0 3px rgba(37,99,235,.12)}
.input-row input.error{border-color:#EF4444}
.input-row .toggle-pw{
  position:absolute;right:13px;top:50%;transform:translateY(-50%);
  background:none;border:none;cursor:pointer;color:#9CA3AF;font-size:18px;padding:2px;
}
.pw-label-row{display:flex;justify-content:space-between;align-items:center;margin-bottom:6px}
.pw-label-row label{margin-bottom:0}
.forgot-link{font-size:12px;color:#2563EB;text-decoration:none;font-weight:600}
.forgot-link:hover{text-decoration:underline}
.err-text{color:#EF4444;font-size:12px;margin-top:4px;display:none}
.err-text.show{display:block}

.remember-row{display:flex;align-items:center;gap:8px;margin-bottom:24px;font-size:13px;color:#374151}
.remember-row input[type=checkbox]{width:16px;height:16px;accent-color:#2563EB;cursor:pointer}

.btn-signin{
  width:100%;padding:13px;border-radius:10px;border:none;
  background:#2563EB;color:#fff;font-size:15px;font-weight:700;
  cursor:pointer;transition:background .18s;display:flex;align-items:center;justify-content:center;gap:8px;
}
.btn-signin:hover{background:#1D4ED8}
.btn-signin:disabled{background:#93C5FD;cursor:not-allowed}
.spinner{
  width:18px;height:18px;border:2px solid rgba(255,255,255,.4);
  border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite;display:none;
}
@keyframes spin{to{transform:rotate(360deg)}}

.divider{text-align:center;color:#CBD5E1;font-size:12px;margin:22px 0;position:relative}
.divider::before,.divider::after{content:'';position:absolute;top:50%;width:44%;height:1px;background:#E2E8F0}
.divider::before{left:0}.divider::after{right:0}

.demo-box{background:#F8FAFC;border:1px solid #E2E8F0;border-radius:10px;padding:14px}
.demo-box p{font-size:12px;color:#64748B;margin-bottom:8px;font-weight:600}
.demo-accounts{display:flex;flex-direction:column;gap:6px}
.demo-item{
  display:flex;align-items:center;justify-content:space-between;
  background:#fff;border:1px solid #E2E8F0;border-radius:7px;
  padding:7px 12px;cursor:pointer;transition:border-color .15s;
}
.demo-item:hover{border-color:#2563EB;background:#EFF6FF}
.demo-item-left{display:flex;align-items:center;gap:8px;font-size:12px;font-weight:600;color:#374151}
.demo-badge{
  padding:2px 7px;border-radius:20px;font-size:11px;font-weight:700;
}
.badge-admin{background:#FEE2E2;color:#991B1B}
.badge-teacher{background:#EFF6FF;color:#1E40AF}
.badge-student{background:#D1FAE5;color:#065F46}
.demo-pass{font-size:11px;color:#9CA3AF}

.login-trouble{text-align:center;margin-top:22px;font-size:13px;color:#64748B}
.login-trouble a{color:#2563EB;font-weight:600;text-decoration:none}
.login-trouble a:hover{text-decoration:underline}

.server-error{
  background:#FEF2F2;border:1px solid #FECACA;border-radius:10px;
  padding:12px 16px;color:#991B1B;font-size:13px;
  display:flex;align-items:center;gap:10px;margin-bottom:20px;
}

/* Responsive */
@media(max-width:768px){
  .login-left{display:none}
  .login-right{padding:28px 20px}
}
</style>
</head>
<body>

<!-- LEFT PANEL -->
<div class="login-left">
  <div class="left-brand">
    <img src="<?= APP_URL ?>/assets/images/logo.svg" alt="Logo">
    <span>Attendance Tracker</span>
  </div>

  <div class="left-headline">Empowering academic excellence through engagement.</div>
  <div class="left-sub">Track attendance, quizzes, interaction logs, engagement scores, and student alerts in one academic management system.</div>

  <div class="left-image">
    <div class="classroom-placeholder">🎓</div>
  </div>

  <div class="left-footer">
    <div class="avatar-stack">
      <span>JC</span><span>AI</span><span>MK</span>
    </div>
    <span>Trusted by over 500 academic institutions worldwide.</span>
  </div>
</div>

<!-- RIGHT PANEL -->
<div class="login-right">
  <div class="login-box">

    <h1>Welcome Back</h1>
    <p class="subtitle">Sign in with your institutional account to access the classroom management dashboard.</p>

    <?php if (!empty($error)): ?>
    <div class="server-error">
      <svg width="18" height="18" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M12 8v4M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
      <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <form id="loginForm" method="POST" novalidate>

      <!-- Email -->
      <div class="form-group">
        <label for="email">Institutional Email</label>
        <div class="input-row">
          <svg class="icon" width="16" height="16" fill="none" viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="16" rx="2" stroke="currentColor" stroke-width="2"/><path d="M2 7l10 7 10-7" stroke="currentColor" stroke-width="2"/></svg>
          <input type="email" id="email" name="email" placeholder="e.g. admin@ischool.vn"
                 value="<?= htmlspecialchars($oldEmail ?? '') ?>" autocomplete="email">
        </div>
        <div class="err-text" id="emailErr">Vui lòng nhập email hợp lệ.</div>
      </div>

      <!-- Password -->
      <div class="form-group">
        <div class="pw-label-row">
          <label for="password">Password</label>
          <a href="#" class="forgot-link">FORGOT?</a>
        </div>
        <div class="input-row">
          <svg class="icon" width="16" height="16" fill="none" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" stroke="currentColor" stroke-width="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4" stroke="currentColor" stroke-width="2"/></svg>
          <input type="password" id="password" name="password" placeholder="Enter your password" autocomplete="current-password">
          <button type="button" class="toggle-pw" id="togglePw" aria-label="Toggle password">
            <svg id="eyeOpen" width="18" height="18" fill="none" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/></svg>
            <svg id="eyeClose" width="18" height="18" fill="none" viewBox="0 0 24 24" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" stroke="currentColor" stroke-width="2"/><line x1="1" y1="1" x2="23" y2="23" stroke="currentColor" stroke-width="2"/></svg>
          </button>
        </div>
        <div class="err-text" id="pwErr">Vui lòng nhập mật khẩu.</div>
      </div>

      <!-- Remember -->
      <div class="remember-row">
        <input type="checkbox" id="remember" name="remember">
        <label for="remember">Keep me logged in for 30 days</label>
      </div>

      <button type="submit" class="btn-signin" id="submitBtn">
        <div class="spinner" id="spinner"></div>
        <span id="btnText">Sign In →</span>
      </button>
    </form>

    <div class="divider">or use a demo account</div>

    <!-- Demo accounts -->
    <div class="demo-box">
      <p>📋 Demo Accounts <span style="font-weight:400;color:#94A3B8">· password: <code>password</code></span></p>
      <div class="demo-accounts">
        <div class="demo-item" onclick="fillDemo('admin@ischool.vn')">
          <div class="demo-item-left">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z" fill="#991B1B" opacity=".2" stroke="#991B1B" stroke-width="2"/></svg>
            <span class="demo-badge badge-admin">Admin</span>
            admin@ischool.vn
          </div>
          <span class="demo-pass">Click to fill</span>
        </div>
        <div class="demo-item" onclick="fillDemo('an.teacher@ischool.vn')">
          <div class="demo-item-left">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><rect x="2" y="3" width="20" height="14" rx="2" stroke="#1E40AF" stroke-width="2"/><line x1="8" y1="21" x2="16" y2="21" stroke="#1E40AF" stroke-width="2"/></svg>
            <span class="demo-badge badge-teacher">Teacher</span>
            an.teacher@ischool.vn
          </div>
          <span class="demo-pass">Click to fill</span>
        </div>
        <div class="demo-item" onclick="fillDemo('cuong@ischool.vn')">
          <div class="demo-item-left">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M22 10v6M2 10l10-5 10 5-10 5-10-5z" stroke="#065F46" stroke-width="2"/><path d="M6 12v5c3 3 9 3 12 0v-5" stroke="#065F46" stroke-width="2"/></svg>
            <span class="demo-badge badge-student">Student</span>
            cuong@ischool.vn
          </div>
          <span class="demo-pass">Click to fill</span>
        </div>
      </div>
    </div>

    <div class="login-trouble">
      Trouble logging in? <a href="#">Contact IT Support</a>
    </div>

  </div>
</div>

<script>
// ── Fill demo account ───────────────────────────────────
function fillDemo(email) {
  document.getElementById('email').value = email;
  document.getElementById('password').value = 'password';
  document.getElementById('email').classList.remove('error');
  document.getElementById('password').classList.remove('error');
}

// ── Toggle password ─────────────────────────────────────
document.getElementById('togglePw').addEventListener('click', function() {
  const pw = document.getElementById('password');
  const eyeO = document.getElementById('eyeOpen');
  const eyeC = document.getElementById('eyeClose');
  if (pw.type === 'password') {
    pw.type = 'text'; eyeO.style.display = 'none'; eyeC.style.display = '';
  } else {
    pw.type = 'password'; eyeO.style.display = ''; eyeC.style.display = 'none';
  }
});

// ── Frontend validation ─────────────────────────────────
document.getElementById('loginForm').addEventListener('submit', function(e) {
  let ok = true;
  const email = document.getElementById('email');
  const pw    = document.getElementById('password');
  const emailErr = document.getElementById('emailErr');
  const pwErr    = document.getElementById('pwErr');

  // reset
  email.classList.remove('error'); emailErr.classList.remove('show');
  pw.classList.remove('error');    pwErr.classList.remove('show');

  if (!email.value.trim() || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) {
    email.classList.add('error'); emailErr.classList.add('show'); ok = false;
  }
  if (!pw.value) {
    pw.classList.add('error'); pwErr.classList.add('show'); ok = false;
  }

  if (!ok) { e.preventDefault(); return; }

  // Loading state
  const btn  = document.getElementById('submitBtn');
  const spin = document.getElementById('spinner');
  const txt  = document.getElementById('btnText');
  btn.disabled = true;
  spin.style.display = '';
  txt.textContent = 'Signing in...';
});
</script>
</body>
</html>