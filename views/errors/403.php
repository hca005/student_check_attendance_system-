<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>403 – Access Denied</title>
<style>
body{font-family:'Inter',sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;background:#F1F5F9;margin:0}
.box{text-align:center;max-width:400px}
.code{font-size:80px;font-weight:800;color:#EF4444;line-height:1}
h2{font-size:22px;color:#0F172A;margin:10px 0 8px}
p{color:#64748B;font-size:14px;margin-bottom:24px}
a{background:#2563EB;color:#fff;padding:10px 24px;border-radius:9px;text-decoration:none;font-weight:600;font-size:14px}
</style>
</head>
<body>
<div class="box">
  <div class="code">403</div>
  <h2>Access Denied</h2>
  <p>Bạn không có quyền truy cập trang này. Vui lòng đăng nhập bằng tài khoản phù hợp.</p>
  <a href="<?= defined('APP_URL') ? APP_URL : '/attendance_system/public' ?>/login.php">← Quay về trang đăng nhập</a>
</div>
</body>
</html>