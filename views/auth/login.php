<?php
$safeError = isset($error) ? (string)$error : '';
$safeOldEmail = isset($oldEmail) ? (string)$oldEmail : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign In - Attendance Tracker</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@800;900&family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background: #f1f5f9;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px;
      color: #0f172a;
    }
    .login-shell {
      width: 100%;
      max-width: 1000px;
      min-height: 540px;
      border-radius: 14px;
      border: 1px solid #dbe3f0;
      background: #ffffff;
      box-shadow: 0 18px 45px rgba(15, 23, 42, 0.12);
      display: grid;
      grid-template-columns: 1fr 1fr;
      overflow: hidden;
    }
    .login-left {
      background: linear-gradient(145deg, #eff6ff 0%, #dbeafe 100%);
      padding: 32px;
      display: flex;
      flex-direction: column;
      position: relative;
      border-right: 1px solid #dbe3f0;
    }
    .left-brand,
    .left-copy,
    .left-media,
    .left-footer { position: relative; z-index: 1; }
    .left-brand {
      display: flex;
      align-items: center;
      gap: 14px;
      margin-bottom: 28px;
    }
    .left-brand img {
      width: 60px;
      height: 60px;
      border-radius: 14px;
      display: block;
      object-fit: cover;
      flex-shrink: 0;
    }
    .left-brand .b-text {
      display: flex;
      flex-direction: column;
      line-height: 1.1;
    }
    .left-brand .b-line1 {
      font-family: 'Montserrat', sans-serif;
      font-size: 32px;
      font-weight: 900;
      color: #0f172a;
      letter-spacing: -1px;
    }
    .left-brand .b-line2 {
      font-family: 'Montserrat', sans-serif;
      font-size: 32px;
      font-weight: 900;
      color: #2563eb;
      letter-spacing: -1px;
      margin-top: -6px;
    }
    .left-brand .b-sub {
      font-family: 'Montserrat', sans-serif;
      font-size: 10px;
      font-weight: 700;
      color: #334155;
      text-transform: uppercase;
      letter-spacing: 0.25em;
      margin-top: 6px;
      padding-left: 2px;
    }
    .left-copy h2 {
      font-family: 'Plus Jakarta Sans', sans-serif;
      font-size: 22px;
      line-height: 1.4;
      color: #1e293b;
      margin-bottom: 24px;
      font-weight: 600;
      letter-spacing: -0.5px;
    }
    .left-copy p {
      color: rgba(255,255,255,0.86);
      font-size: 16px;
      line-height: 1.6;
      margin-bottom: 24px;
      max-width: 560px;
    }
    .left-media {
      flex: 1;
      border-radius: 12px;
      overflow: hidden;
      border: 1px solid rgba(15, 23, 42, 0.08);
      box-shadow: 0 16px 28px rgba(7, 17, 35, 0.08);
      min-height: 200px;
    }
    .left-media img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }
    .left-footer {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-top: 16px;
      color: #475569;
      font-size: 12px;
      font-style: italic;
    }
    .avatar-stack {
      display: flex;
      align-items: center;
    }
    .avatar-stack span {
      width: 22px;
      height: 22px;
      border-radius: 999px;
      border: 2px solid #dbeafe;
      margin-left: -6px;
    }
    .avatar-stack span:first-child { margin-left: 0; background: #93c5fd; }
    .avatar-stack span:nth-child(2) { background: #f9a8d4; }
    .avatar-stack span:nth-child(3) { background: #fdba74; }

    .login-right {
      padding: 32px 40px;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }
    .login-right h1 {
      font-size: 28px;
      font-weight: 800;
      margin-bottom: 8px;
      letter-spacing: 0;
    }
    .login-right p.sub {
      color: #64748b;
      font-size: 14px;
      margin-bottom: 28px;
      line-height: 1.6;
    }
    .error-box {
      margin-bottom: 14px;
      padding: 10px 12px;
      border-radius: 8px;
      border: 1px solid #fecaca;
      background: #fef2f2;
      color: #991b1b;
      font-size: 13px;
    }
    .field { margin-bottom: 16px; }
    .field .label-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 7px;
    }
    .field label {
      font-size: 12px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      color: #64748b;
    }
    .forgot-link {
      font-size: 12px;
      font-weight: 700;
      color: #2563eb;
      text-decoration: none;
    }
    .forgot-link:hover { text-decoration: underline; }
    .input-wrap {
      position: relative;
    }
    .input-icon {
      position: absolute;
      top: 50%;
      left: 11px;
      transform: translateY(-50%);
      color: #94a3b8;
      width: 17px;
      height: 17px;
      pointer-events: none;
    }
    .input-wrap input {
      width: 100%;
      border: 1px solid #d7e1ee;
      border-radius: 9px;
      height: 45px;
      padding: 0 38px 0 36px;
      font-size: 14px;
      background: #ffffff;
      color: #0f172a;
      transition: border-color .15s, box-shadow .15s;
      font-family: inherit;
    }
    .input-wrap input:focus {
      outline: none;
      border-color: #2563eb;
      box-shadow: 0 0 0 3px rgba(37,99,235,.12);
    }
    .toggle-pass {
      border: none;
      background: transparent;
      position: absolute;
      top: 50%;
      right: 10px;
      transform: translateY(-50%);
      color: #94a3b8;
      width: 20px;
      height: 20px;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }
    .row-check {
      margin: 4px 0 18px;
      color: #64748b;
      font-size: 13px;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .row-check input { accent-color: #2563eb; }
    .btn-login {
      width: 100%;
      height: 46px;
      border-radius: 8px;
      border: 1px solid #1d4ed8;
      background: #1d4ed8;
      color: #ffffff;
      font-weight: 700;
      font-size: 15px;
      cursor: pointer;
      transition: background .15s ease;
      font-family: inherit;
    }
    .btn-login:hover { background: #1b45bf; }
    .help-text {
      margin-top: 22px;
      padding-top: 20px;
      border-top: 1px solid #e2e8f0;
      text-align: center;
      color: #64748b;
      font-size: 13px;
    }
    .help-text a {
      color: #2563eb;
      text-decoration: none;
      font-weight: 700;
    }
    .meta-links {
      margin-top: 24px;
      display: flex;
      justify-content: center;
      gap: 16px;
      font-size: 11px;
    }
    .meta-links a {
      text-decoration: none;
      color: #94a3b8;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.04em;
    }
    .meta-links a:hover { color: #64748b; }
    @media (max-width: 1024px) {
      .login-shell { grid-template-columns: 1fr; min-height: auto; }
      .login-left { min-height: 420px; }
      .left-copy h2 { font-size: 20px; }
    }
    @media (max-width: 720px) {
      body { padding: 12px; }
      .login-left { display: none; }
      .login-right { padding: 32px 20px; }
      .login-right h1 { font-size: 30px; }
    }
  </style>
</head>
<body>
  <div class="login-shell">
    <section class="login-left">
      <div class="left-brand">
        <img src="<?= APP_URL ?>/assets/images/logo.svg?v=20260619" alt="Attendance Tracker logo">
        <div class="b-text">
          <div class="b-line1">Attendance</div>
          <div class="b-line2">Tracker</div>
          <div class="b-sub">Academic Management</div>
        </div>
      </div>

      <div class="left-copy">
        <h2>Empowering academic excellence through engagement.</h2>
      </div>

      <div class="left-media">
        <img
          src="<?= APP_URL ?>/assets/images/login-analytics.svg"
          alt="Academic analytics overview"
          onerror="this.src='<?= APP_URL ?>/assets/images/login-hero.svg';"
        >
      </div>

      <div class="left-footer">
        <div class="avatar-stack"><span></span><span></span><span></span></div>
        <span>Trusted by over 500 academic institutions worldwide.</span>
      </div>
    </section>

    <section class="login-right">
      <h1>Welcome Back</h1>
      <p class="sub">Please enter your credentials to access the academic management dashboard.</p>

      <?php if ($safeError !== ''): ?>
        <div class="error-box"><?= htmlspecialchars($safeError) ?></div>
      <?php endif; ?>

      <form method="post" action="<?= APP_URL ?>/login.php">
        <div class="field">
          <div class="label-row"><label for="email">Institutional Email</label></div>
          <div class="input-wrap">
            <svg class="input-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path d="M4 4h16v16H4z"/><path d="M22 6l-10 7L2 6"/>
            </svg>
            <input
              id="email"
              type="email"
              name="email"
              value="<?= htmlspecialchars($safeOldEmail) ?>"
              placeholder="e.g. professor@university.edu"
              autocomplete="email"
              required
            >
          </div>
        </div>

        <div class="field">
          <div class="label-row">
            <label for="passwordInput">Password</label>
            <a href="#" class="forgot-link">Forgot?</a>
          </div>
          <div class="input-wrap">
            <svg class="input-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <rect x="4" y="11" width="16" height="9" rx="2"/><path d="M8 11V8a4 4 0 1 1 8 0v3"/>
            </svg>
            <input
              id="passwordInput"
              type="password"
              name="password"
              placeholder="********"
              autocomplete="current-password"
              required
            >
            <button type="button" class="toggle-pass" onclick="togglePassword()" aria-label="Toggle password visibility">
              <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/><circle cx="12" cy="12" r="3"/>
              </svg>
            </button>
          </div>
        </div>

        <label class="row-check">
          <input type="checkbox" name="remember">
          Keep me logged in for 30 days
        </label>

        <button type="submit" class="btn-login">Sign In to Dashboard</button>
      </form>

      <p class="help-text">Trouble logging in? <a href="#">Contact IT Support</a></p>

      <div class="meta-links">
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
      </div>
    </section>
  </div>

  <script>
    function togglePassword() {
      const input = document.getElementById('passwordInput');
      input.type = input.type === 'password' ? 'text' : 'password';
    }
  </script>
</body>
</html>
