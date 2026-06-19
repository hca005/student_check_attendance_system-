<?php
$page_title = 'Account Settings & Privacy';
$active_nav = 'settings'; 

require APP_ROOT . '/views/layouts/header.php';

$role = $_SESSION['role'] ?? 'admin';
$email = $_SESSION['email'] ?? 'admin@ischool.vn';
?>
<div class="admin-page-title" style="margin-bottom: 24px; border-bottom: 1px solid #e2e8f0; padding-bottom: 16px;">
  <h1 style="font-size: 20px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 10px;">
    <svg style="color: #f59e0b;" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><circle cx="12" cy="12" r="3"></circle></svg>
    Account Settings & Privacy
  </h1>
</div>

<div style="display: grid; grid-template-columns: 240px 1fr; gap: 24px;">
  <!-- Settings Sidebar -->
  <div class="card" style="border-radius: 12px; border: 1px solid #e2e8f0; background: #fff; height: fit-content;">
    <div class="card-body" style="padding: 12px;">
      <div class="settings-tab active" onclick="switchTab('password')" id="tab-password">
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
        Change Password
      </div>
      <div class="settings-tab" onclick="switchTab('email')" id="tab-email">
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
        Email Settings
      </div>
      <div class="settings-tab" onclick="switchTab('notifications')" id="tab-notifications">
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
        Notifications
      </div>
      <div class="settings-tab" onclick="switchTab('privacy')" id="tab-privacy">
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
        Privacy
      </div>
      <div style="border-top: 1px solid #e2e8f0; margin: 12px 0;"></div>
      <div class="settings-tab text-red-500" onclick="switchTab('delete')" id="tab-delete">
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
        Delete Account
      </div>
    </div>
  </div>

  <!-- Settings Content -->
  <div class="card" style="border-radius: 12px; border: 1px solid #e2e8f0; background: #fff; min-height: 400px;">
    
    <!-- Tab: Password -->
    <div id="content-password" class="settings-content" style="display: block;">
      <div style="padding: 24px; border-bottom: 1px solid #e2e8f0;">
        <h2 style="font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
          <svg style="color: #3b82f6;" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
          Change Password
        </h2>
        <p style="color: #64748b; font-size: 13px;">Ensure your account is using a strong password to stay secure. We recommend using a combination of letters, numbers, and symbols.</p>
      </div>
      <div style="padding: 24px;">
        <div class="form-group">
          <label>Current Password</label>
          <div style="position: relative;">
            <input type="password" class="form-input">
            <svg class="pwd-eye" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
          </div>
        </div>
        <div class="form-group">
          <label>New Password</label>
          <div style="position: relative;">
            <input type="password" class="form-input">
            <svg class="pwd-eye" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
          </div>
          <div style="font-size: 11px; color: #94a3b8; margin-top: 6px;">Minimum 8 characters, including a number and a symbol.</div>
        </div>
        <div class="form-group">
          <label>Confirm New Password</label>
          <div style="position: relative;">
            <input type="password" class="form-input">
            <svg class="pwd-eye" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
          </div>
        </div>
        <button class="btn-primary" style="margin-top: 8px;">
          <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="margin-right:6px; display:inline-block; vertical-align:middle;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
          <span style="vertical-align:middle;">Update Password</span>
        </button>
      </div>
    </div>

    <!-- Tab: Email -->
    <div id="content-email" class="settings-content" style="display: none;">
      <div style="padding: 24px; border-bottom: 1px solid #e2e8f0;">
        <h2 style="font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
          <span style="color: #ea580c;">@</span>
          Email Settings
        </h2>
        <p style="color: #64748b; font-size: 13px;">Manage your email address and email preferences. Your email is used for account recovery and notifications.</p>
      </div>
      <div style="padding: 24px;">
        <div class="form-group">
          <label>Current Email</label>
          <input type="text" value="<?= htmlspecialchars($email) ?>" readonly class="form-input" style="background: #f8fafc; color: #64748b; cursor: not-allowed;">
        </div>
        <div class="form-group">
          <label>New Email Address</label>
          <input type="email" placeholder="Enter new email address" class="form-input">
        </div>
        <div class="form-group">
          <label>Confirm with Password</label>
          <div style="position: relative;">
            <input type="password" placeholder="Enter your password" class="form-input">
            <svg class="pwd-eye" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
          </div>
        </div>
        <button class="btn-primary" style="margin-top: 8px;">
          <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="margin-right:6px; display:inline-block; vertical-align:middle;"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
          <span style="vertical-align:middle;">Update Email</span>
        </button>
      </div>
    </div>

    <!-- Tab: Notifications -->
    <div id="content-notifications" class="settings-content" style="display: none;">
      <div style="padding: 24px; border-bottom: 1px solid #e2e8f0;">
        <h2 style="font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
          <svg style="color: #f59e0b;" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
          Notification Preferences
        </h2>
        <p style="color: #64748b; font-size: 13px;">Choose what notifications you want to receive. You can change these settings at any time to manage your academic alerts.</p>
      </div>
      <div style="padding: 24px;">
        <div class="toggle-row">
          <div>
            <div class="toggle-title">Email Notifications</div>
            <div class="toggle-desc">Receive email updates about your account activity and security alerts.</div>
          </div>
          <div class="toggle-switch active"></div>
        </div>
        <div class="toggle-row">
          <div>
            <div class="toggle-title">System Alerts</div>
            <div class="toggle-desc">Get notified for critical system maintenance and platform updates.</div>
          </div>
          <div class="toggle-switch active"></div>
        </div>
        <div class="toggle-row">
          <div>
            <div class="toggle-title">Course Updates</div>
            <div class="toggle-desc">Get notified when new courses are added or enrollment statuses change.</div>
          </div>
          <div class="toggle-switch active"></div>
        </div>
        <div class="toggle-row">
          <div>
            <div class="toggle-title">Monthly Reports</div>
            <div class="toggle-desc">Receive a monthly summary of attendance and engagement metrics.</div>
          </div>
          <div class="toggle-switch"></div>
        </div>
        <button class="btn-primary" style="margin-top: 16px;">
          <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="margin-right:6px; display:inline-block; vertical-align:middle;"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
          <span style="vertical-align:middle;">Save Preferences</span>
        </button>
      </div>
    </div>

    <!-- Tab: Privacy -->
    <div id="content-privacy" class="settings-content" style="display: none;">
      <div style="padding: 24px; border-bottom: 1px solid #e2e8f0;">
        <h2 style="font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
          <svg style="color: #3b82f6;" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
          Privacy Settings
        </h2>
        <p style="color: #64748b; font-size: 13px;">Control who can see your profile and academic activity. Your data security is our priority.</p>
      </div>
      <div style="padding: 24px;">
        <div class="toggle-row">
          <div>
            <div class="toggle-title">Public Profile</div>
            <div class="toggle-desc">Allow other users in your institution to view your basic profile information.</div>
          </div>
          <div class="toggle-switch active">
            <svg style="color: #1d4ed8; position: absolute; right: 4px; top: 2px;" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
          </div>
        </div>
        <div class="toggle-row">
          <div>
            <div class="toggle-title">Show Activity Status</div>
            <div class="toggle-desc">Let other authorized users see when you are active on the platform.</div>
          </div>
          <div class="toggle-switch"></div>
        </div>
        <div class="toggle-row">
          <div>
            <div class="toggle-title">Show Enrollments</div>
            <div class="toggle-desc">Display the courses you are currently enrolled in or managing on your public profile.</div>
          </div>
          <div class="toggle-switch"></div>
        </div>
        <div class="toggle-row">
          <div>
            <div class="toggle-title">Search Engine Indexing</div>
            <div class="toggle-desc">Allow external search engines to index your public profile page.</div>
          </div>
          <div class="toggle-switch active">
            <svg style="color: #1d4ed8; position: absolute; right: 4px; top: 2px;" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
          </div>
        </div>
        <div style="display: flex; gap: 12px; margin-top: 16px; justify-content: flex-end;">
          <button style="padding: 10px 16px; border-radius: 8px; border: 1px solid #cbd5e1; background: #fff; color: #475569; font-weight: 600; cursor: pointer; transition: 0.2s;">Cancel</button>
          <button class="btn-primary">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="margin-right:6px; display:inline-block; vertical-align:middle;"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
            <span style="vertical-align:middle;">Save Privacy Settings</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Tab: Delete Account -->
    <div id="content-delete" class="settings-content" style="display: none;">
      <div style="padding: 24px; border-bottom: 1px solid #e2e8f0; background: #fef2f2; border-radius: 12px 12px 0 0;">
        <h2 style="font-size: 16px; font-weight: 700; color: #b91c1c; margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
          <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
          Danger Zone: Delete Account
        </h2>
        <p style="color: #991b1b; font-size: 13px;">Deleting your account is permanent and cannot be undone. All your academic data, courses, and session metrics will be permanently wiped.</p>
      </div>
      <div style="padding: 24px;">
        <div style="background: #fff8f1; border-left: 4px solid #f97316; padding: 16px; margin-bottom: 24px; border-radius: 4px;">
          <h4 style="color: #c2410c; font-size: 14px; font-weight: 700; margin-bottom: 4px;">Authorization Required</h4>
          <p style="color: #c2410c; font-size: 13px; line-height: 1.5;">As an <strong><?= htmlspecialchars(ucfirst($role)) ?></strong>, you must enter an Authorization Code provided by the Superior Management to proceed with account deletion. This prevents accidental deletion of administrative accounts.</p>
        </div>
        
        <div class="form-group">
          <label>Authorization Code</label>
          <input type="text" placeholder="e.g. AUTH-XXX-YYYY" class="form-input" style="font-family: monospace; letter-spacing: 1px;">
        </div>
        
        <div class="form-group">
          <label>Confirm Password</label>
          <div style="position: relative;">
            <input type="password" placeholder="Enter your current password" class="form-input">
            <svg class="pwd-eye" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
          </div>
        </div>

        <div style="display: flex; gap: 8px; align-items: flex-start; margin-bottom: 24px;">
          <input type="checkbox" id="confirm_delete" style="margin-top: 4px; width: 16px; height: 16px; accent-color: #dc2626;">
          <label for="confirm_delete" style="font-size: 13px; color: #475569; font-weight: 400; cursor: pointer; user-select: none;">I understand that this action is irreversible and I have the authority to perform this deletion.</label>
        </div>

        <button style="background: #dc2626; color: white; padding: 10px 20px; border-radius: 8px; font-weight: 600; display: flex; align-items: center; gap: 8px; border: none; cursor: pointer; transition: 0.2s;" onmouseover="this.style.background='#b91c1c'" onmouseout="this.style.background='#dc2626'">
          <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
          Permanently Delete Account
        </button>
      </div>
    </div>

  </div>
</div>

<style>
  .settings-tab {
    padding: 12px 16px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    color: #475569;
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    transition: all 0.2s;
    margin-bottom: 4px;
  }
  .settings-tab:hover {
    background: #f1f5f9;
  }
  .settings-tab.active {
    background: #1d4ed8;
    color: #fff;
  }
  .settings-tab.active.text-red-500 {
    background: #fef2f2;
    color: #dc2626;
  }
  .settings-tab.text-red-500 {
    color: #dc2626;
  }
  .settings-tab.text-red-500:hover {
    background: #fef2f2;
  }
  .form-group {
    margin-bottom: 20px;
  }
  .form-group label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 8px;
  }
  .form-input {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 14px;
    color: #0f172a;
    outline: none;
    transition: border-color 0.2s;
    box-sizing: border-box;
  }
  .form-input:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
  }
  .pwd-eye {
    position: absolute;
    right: 12px;
    top: 10px;
    width: 20px;
    height: 20px;
    color: #94a3b8;
    cursor: pointer;
  }
  .pwd-eye:hover {
    color: #475569;
  }
  .btn-primary {
    background: #1d4ed8;
    color: #fff;
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: 0.2s;
    display: inline-block;
  }
  .btn-primary:hover {
    background: #1e40af;
  }
  
  .toggle-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 0;
    border-bottom: 1px solid #f1f5f9;
  }
  .toggle-row:last-of-type {
    border-bottom: none;
  }
  .toggle-title {
    font-size: 14px;
    font-weight: 600;
    color: #0f172a;
    margin-bottom: 4px;
  }
  .toggle-desc {
    font-size: 13px;
    color: #64748b;
  }
  .toggle-switch {
    width: 44px;
    height: 24px;
    background: #cbd5e1;
    border-radius: 999px;
    position: relative;
    cursor: pointer;
    transition: background 0.2s;
    flex-shrink: 0;
  }
  .toggle-switch::after {
    content: '';
    position: absolute;
    top: 2px;
    left: 2px;
    width: 20px;
    height: 20px;
    background: #fff;
    border-radius: 50%;
    transition: left 0.2s;
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
  }
  .toggle-switch.active {
    background: #2563eb;
  }
  .toggle-switch.active::after {
    left: 22px;
  }
  @media (max-width: 768px) {
    div[style*="grid-template-columns: 240px"] {
      grid-template-columns: 1fr !important;
    }
  }
</style>

<script>
  function switchTab(tabId) {
    // Hide all contents
    document.querySelectorAll('.settings-content').forEach(function(el) {
      el.style.display = 'none';
    });
    // Remove active class from tabs
    document.querySelectorAll('.settings-tab').forEach(function(el) {
      el.classList.remove('active');
    });
    
    // Show selected content
    document.getElementById('content-' + tabId).style.display = 'block';
    // Add active class to clicked tab
    document.getElementById('tab-' + tabId).classList.add('active');
  }

  // Toggle switch interactivity
  document.querySelectorAll('.toggle-switch').forEach(function(el) {
    el.addEventListener('click', function() {
      this.classList.toggle('active');
      const svg = this.querySelector('svg');
      if(svg) {
          svg.style.display = this.classList.contains('active') ? 'block' : 'none';
      }
    });
  });
</script>

<?php 
if (file_exists(APP_ROOT . '/views/layouts/footer.php')) {
    require APP_ROOT . '/views/layouts/footer.php'; 
} else {
    echo '</div></div></body></html>';
}
?>
