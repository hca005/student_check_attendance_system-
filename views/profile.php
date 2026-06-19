<?php
$page_title = 'My Profile';
$active_nav = 'profile'; // Keep sidebar state clean

require APP_ROOT . '/views/layouts/header.php';

$user_role = $_SESSION['role'] ?? 'admin';
$user_name = $_SESSION['full_name'] ?? 'Admin User';
$user_initials = strtoupper(substr($user_name, 0, 2));
$email = $_SESSION['email'] ?? 'admin@school.edu.vn'; 
?>

<div class="admin-page-title" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px;">
  <div class="left">
    <h1 style="font-size: 24px; font-weight: 800; color: #0f172a; margin-bottom: 4px;">My Profile</h1>
    <p style="color: #64748b; font-size: 14px;">Manage your account settings and administrative preferences.</p>
  </div>
  <button class="btn btn-primary" style="background: #1d4ed8; color: #fff; padding: 10px 20px; border-radius: 8px; font-weight: 600; display: flex; align-items: center; gap: 8px; border: none; cursor: pointer; transition: 0.2s;">
    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
    Save All Changes
  </button>
</div>

<!-- Header Card -->
<div class="card" style="margin-bottom: 24px; border-radius: 12px; border: 1px solid #e2e8f0; background: #fff;">
  <div class="card-body" style="padding: 24px; display: flex; align-items: center; gap: 24px;">
    <div style="position: relative;">
      <div style="width: 100px; height: 100px; border-radius: 50%; background: #3b82f6; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 36px; font-weight: 800; border: 3px solid #e2e8f0;">
        <?= $user_initials ?>
      </div>
      <div style="position: absolute; bottom: 0; right: 0; background: #fff; border: 1px solid #e2e8f0; border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
        <svg style="color: #64748b;" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
      </div>
    </div>
    <div>
      <h2 style="font-size: 20px; font-weight: 800; color: #0f172a; margin-bottom: 8px;"><?= htmlspecialchars($user_name) ?></h2>
      <div style="display: flex; gap: 12px; margin-bottom: 12px;">
        <span style="background: #f3e8ff; color: #9333ea; padding: 4px 12px; border-radius: 999px; font-size: 12px; font-weight: 700; display: flex; align-items: center; gap: 6px;">
          <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
          <?= ucfirst($user_role) ?> <?= $user_role === 'admin' ? 'Administrator' : '' ?>
        </span>
        <span style="background: #dcfce7; color: #16a34a; padding: 4px 12px; border-radius: 999px; font-size: 12px; font-weight: 700; display: flex; align-items: center; gap: 6px;">
          <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
          Active
        </span>
      </div>
      <p style="color: #475569; font-size: 13px; line-height: 1.5; max-width: 600px;">
        Managing university-wide attendance systems and student engagement metrics. Ensure data integrity across all academic departments.
      </p>
    </div>
  </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
  <!-- Left Column -->
  <div class="card" style="border-radius: 12px; border: 1px solid #e2e8f0; background: #fff;">
    <div class="card-body" style="padding: 24px;">
      <h3 style="font-size: 16px; font-weight: 700; color: #1e293b; margin-bottom: 24px; display: flex; align-items: center; gap: 8px; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;">
        <svg style="color: #3b82f6;" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
        Personal Information
      </h3>
      
      <div style="margin-bottom: 16px;">
        <label style="display: block; font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px;">Full Name</label>
        <input type="text" value="<?= htmlspecialchars($user_name) ?>" style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; color: #0f172a; outline: none; transition: border-color 0.2s;">
      </div>
      
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
        <div>
          <label style="display: block; font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px;">Email Address</label>
          <input type="email" value="<?= htmlspecialchars($email) ?>" style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; color: #0f172a; outline: none;">
        </div>
        <div>
          <label style="display: block; font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px;">Department</label>
          <select style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; color: #0f172a; outline: none; background: #fff;">
            <option>Academic Affairs</option>
            <option>Information Technology</option>
            <option>Student Services</option>
          </select>
        </div>
      </div>
      
      <div>
        <label style="display: block; font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px;">Professional Bio</label>
        <textarea rows="3" style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; color: #0f172a; outline: none; resize: none; line-height: 1.5;">Managing university-wide attendance systems and student engagement metrics.</textarea>
      </div>
    </div>
  </div>
  
  <!-- Right Column -->
  <div style="display: flex; flex-direction: column; gap: 24px;">
    <!-- System Details -->
    <div class="card" style="border-radius: 12px; border: 1px solid #e2e8f0; background: #fff;">
      <div class="card-body" style="padding: 24px;">
        <h3 style="font-size: 16px; font-weight: 700; color: #1e293b; margin-bottom: 24px; display: flex; align-items: center; gap: 8px; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;">
          <svg style="color: #3b82f6;" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
          System Details
        </h3>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; font-size: 13px;">
          <span style="color: #64748b;">Employee ID</span>
          <span style="font-family: monospace; background: #f1f5f9; padding: 4px 8px; border-radius: 6px; font-weight: 600; color: #0f172a; letter-spacing: 0.5px;">ADM-001</span>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; font-size: 13px;">
          <span style="color: #64748b;">Permissions Level</span>
          <span style="font-weight: 600; color: #0f172a;">Full Access</span>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 13px;">
          <span style="color: #64748b;">Last Login</span>
          <span style="font-weight: 600; color: #0f172a;">Today at 09:42 AM</span>
        </div>
      </div>
    </div>
    
    <!-- Security -->
    <div class="card" style="border-radius: 12px; border: 1px solid #e2e8f0; background: #fff;">
      <div class="card-body" style="padding: 24px;">
        <h3 style="font-size: 16px; font-weight: 700; color: #1e293b; margin-bottom: 24px; display: flex; align-items: center; gap: 8px; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;">
          <svg style="color: #3b82f6;" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
          Security
        </h3>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
          <div>
            <div style="font-size: 13px; font-weight: 600; color: #1e293b; margin-bottom: 2px;">Two-Factor Auth</div>
            <div style="font-size: 12px; color: #64748b;">Enhance account security</div>
          </div>
          <div style="width: 36px; height: 20px; background: #2563eb; border-radius: 999px; position: relative; cursor: pointer; border: 1px solid #2563eb;">
            <div style="width: 16px; height: 16px; background: #fff; border-radius: 50%; position: absolute; top: 1px; right: 2px; box-shadow: 0 1px 2px rgba(0,0,0,0.2);"></div>
          </div>
        </div>
        <button style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px; padding: 10px; border: 1px solid #e2e8f0; background: #fff; border-radius: 8px; font-weight: 600; font-size: 14px; color: #0f172a; cursor: pointer; transition: background 0.2s;">
          <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
          Change Password
        </button>
      </div>
    </div>
  </div>
</div>

<style>
  input:focus, textarea:focus, select:focus {
    border-color: #3b82f6 !important;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
  }
  .btn-primary:hover {
    background: #1e40af !important;
  }
  .card-body button:hover {
    background: #f8fafc !important;
  }
  @media (max-width: 1024px) {
    div[style*="grid-template-columns: 2fr 1fr"] {
      grid-template-columns: 1fr !important;
    }
  }
</style>

<?php 
if (file_exists(APP_ROOT . '/views/layouts/footer.php')) {
    require APP_ROOT . '/views/layouts/footer.php'; 
} else {
    echo '</div></div></body></html>';
}
?>
