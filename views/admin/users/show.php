<?php
Middleware::requireAdmin();
$pageTitle = 'User Profile';
$currentPage = 'admin.users';
require APP_ROOT . '/views/layouts/header.php';

$roleMap = [
    'admin' => 'badge-danger',
    'teacher' => 'badge-primary',
    'student' => 'badge-success',
];

$age = '--';
if (!empty($user['date_of_birth'])) {
    $dob = new DateTime($user['date_of_birth']);
    $now = new DateTime();
    $age = $now->diff($dob)->y;
}

$avatarColor = $user['role'] === 'admin' ? 'avatar-red' : ($user['role'] === 'teacher' ? 'avatar-blue' : 'avatar-green');
$initial = strtoupper(substr((string)$user['full_name'], 0, 1));
?>

<div class="admin-page-title">
  <div class="left">
    <h1>User Profile</h1>
    <p>Detailed view of user demographics and academic records.</p>
  </div>
  <a class="btn btn-outline" href="<?= APP_URL ?>/index.php?page=admin_users">
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
    Back to Users
  </a>
</div>

<div class="profile-layout" style="display:grid; grid-template-columns: 350px 1fr; gap: 24px; align-items: start;">
  
  <!-- Left Column: Core Identity -->
  <div class="card" style="text-align:center; padding: 32px 24px;">
    <div class="avatar <?= $avatarColor ?>" style="width: 120px; height: 120px; font-size: 48px; margin: 0 auto 16px;">
      <?= $initial ?>
    </div>
    <h2 style="margin: 0 0 8px; font-size: 24px; color: #0f172a;"><?= htmlspecialchars((string)$user['full_name']) ?></h2>
    <div style="margin-bottom: 16px;">
      <span class="badge <?= $roleMap[$user['role']] ?? 'badge-gray' ?>" style="font-size: 13px; padding: 6px 12px;"><?= ucfirst((string)$user['role']) ?></span>
      <span class="badge <?= (int)$user['is_active'] === 1 ? 'badge-success' : 'badge-gray' ?>" style="font-size: 13px; padding: 6px 12px;"><?= (int)$user['is_active'] === 1 ? 'Active' : 'Inactive' ?></span>
    </div>
    
    <div style="text-align: left; margin-top: 24px; border-top: 1px solid #e2e8f0; padding-top: 24px;">
      <div style="margin-bottom: 12px; display: flex; justify-content: space-between;">
        <span style="color:#64748b; font-size: 14px;">ID Code</span>
        <strong style="color:#0f172a; font-size: 14px;"><?= htmlspecialchars((string)($user['student_code'] ?: '--')) ?></strong>
      </div>
      <div style="margin-bottom: 12px; display: flex; justify-content: space-between;">
        <span style="color:#64748b; font-size: 14px;">Email</span>
        <strong style="color:#0f172a; font-size: 14px;"><?= htmlspecialchars((string)$user['email']) ?></strong>
      </div>
      <div style="margin-bottom: 12px; display: flex; justify-content: space-between;">
        <span style="color:#64748b; font-size: 14px;">Phone</span>
        <strong style="color:#0f172a; font-size: 14px;"><?= htmlspecialchars((string)($user['phone'] ?: '--')) ?></strong>
      </div>
      <div style="margin-bottom: 12px; display: flex; justify-content: space-between;">
        <span style="color:#64748b; font-size: 14px;">Gender</span>
        <strong style="color:#0f172a; font-size: 14px;"><?= htmlspecialchars((string)($user['gender'] ?: '--')) ?></strong>
      </div>
      <div style="margin-bottom: 12px; display: flex; justify-content: space-between;">
        <span style="color:#64748b; font-size: 14px;">Date of Birth</span>
        <strong style="color:#0f172a; font-size: 14px;"><?= htmlspecialchars((string)($user['date_of_birth'] ?: '--')) ?> (<?= $age ?> yrs)</strong>
      </div>
      <div style="margin-bottom: 12px; display: flex; justify-content: space-between;">
        <span style="color:#64748b; font-size: 14px;">ID Card (CCCD)</span>
        <strong style="color:#0f172a; font-size: 14px;"><?= htmlspecialchars((string)($user['id_card_number'] ?: '--')) ?></strong>
      </div>
      <div style="margin-bottom: 12px; display: flex; justify-content: space-between;">
        <span style="color:#64748b; font-size: 14px;">Hometown</span>
        <strong style="color:#0f172a; font-size: 14px; text-align: right;"><?= htmlspecialchars((string)($user['hometown'] ?: '--')) ?></strong>
      </div>
      <div style="margin-bottom: 12px; display: flex; justify-content: space-between;">
        <span style="color:#64748b; font-size: 14px;">Joined Date</span>
        <strong style="color:#0f172a; font-size: 14px;"><?= date('M d, Y', strtotime($user['created_at'])) ?></strong>
      </div>
    </div>
    
    <div style="margin-top: 24px;">
      <a class="btn btn-outline" style="width: 100%; display: block; box-sizing: border-box; text-align: center;" href="<?= APP_URL ?>/index.php?page=admin_users_edit&id=<?= (int)$user['id'] ?>">Edit User Information</a>
    </div>
  </div>

  <!-- Right Column: Academic Details & Stats -->
  <div class="right-panel" style="display:flex; flex-direction: column; gap: 24px;">
    
    <!-- Academic Info Card -->
    <div class="card">
      <h3 style="margin-top: 0; margin-bottom: 16px; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; color: #0f172a;">Academic Profile</h3>
      <div class="form-grid">
        <?php if ($user['role'] === 'student'): ?>
          <div class="form-group">
            <label style="color:#64748b; font-size: 13px; margin-bottom: 4px; display:block;">Class Name</label>
            <div style="font-weight: 500; font-size: 16px; color: #0f172a;"><?= htmlspecialchars((string)($user['class_name'] ?: '--')) ?></div>
          </div>
          <div class="form-group">
            <label style="color:#64748b; font-size: 13px; margin-bottom: 4px; display:block;">Academic Year</label>
            <div style="font-weight: 500; font-size: 16px; color: #0f172a;"><?= htmlspecialchars((string)($user['academic_year'] ?: '--')) ?></div>
          </div>
          <div class="form-group" style="grid-column: span 2;">
            <label style="color:#64748b; font-size: 13px; margin-bottom: 4px; display:block;">Major / Department</label>
            <div style="font-weight: 500; font-size: 16px; color: #0f172a;"><?= htmlspecialchars((string)($user['department'] ?: '--')) ?></div>
          </div>
        <?php elseif ($user['role'] === 'teacher'): ?>
          <div class="form-group">
            <label style="color:#64748b; font-size: 13px; margin-bottom: 4px; display:block;">Qualification</label>
            <div style="font-weight: 500; font-size: 16px; color: #0f172a;"><?= htmlspecialchars((string)($user['qualification'] ?: '--')) ?></div>
          </div>
          <div class="form-group">
            <label style="color:#64748b; font-size: 13px; margin-bottom: 4px; display:block;">Department</label>
            <div style="font-weight: 500; font-size: 16px; color: #0f172a;"><?= htmlspecialchars((string)($user['department'] ?: '--')) ?></div>
          </div>
        <?php else: ?>
          <div style="color: #64748b;">No specific academic profile needed for administrators.</div>
        <?php endif; ?>
      </div>
    </div>

    <?php if ($user['role'] === 'student'): ?>
      <!-- Student Analytics -->
      <div class="card">
        <h3 style="margin-top: 0; margin-bottom: 24px; color: #0f172a;">Attendance Analytics</h3>
        
        <?php 
          $total = (int)($attendanceStats['total_sessions'] ?? 0);
          $present = (int)($attendanceStats['present_count'] ?? 0);
          $late = (int)($attendanceStats['late_count'] ?? 0);
          $absent = (int)($attendanceStats['absent_count'] ?? 0);
          
          // Let's count Late as 0.5 present, or just use present / total for simple rate
          $attended = $present + $late;
          $attendanceRate = $total > 0 ? round(($attended / $total) * 100) : 0;
          $progressColor = $attendanceRate >= 80 ? '#10b981' : ($attendanceRate >= 50 ? '#f59e0b' : '#ef4444');
        ?>
        
        <div style="display: flex; gap: 24px; align-items: center; margin-bottom: 32px;">
          <div style="width: 120px; text-align: center;">
            <div style="font-size: 48px; font-weight: 700; color: <?= $progressColor ?>; line-height: 1;"><?= $attendanceRate ?>%</div>
            <div style="font-size: 13px; color: #64748b; margin-top: 8px;">Overall Rate</div>
          </div>
          <div style="flex: 1;">
            <div style="display:flex; justify-content:space-between; font-size:14px; margin-bottom:8px;">
              <span style="color:#64748b">Progress to 100%</span>
              <strong style="color:<?= $progressColor ?>"><?= $attendanceRate ?>%</strong>
            </div>
            <div style="height: 12px; background: #e2e8f0; border-radius: 6px; overflow: hidden;">
              <div style="height: 100%; width: <?= $attendanceRate ?>%; background: <?= $progressColor ?>; border-radius: 6px; transition: width 1s ease;"></div>
            </div>
          </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
           <div style="background: #f0fdf4; border: 1px solid #bbf7d0; padding: 16px; border-radius: 8px; text-align: center;">
             <div style="font-size: 24px; font-weight: 700; color: #166534;"><?= $present ?></div>
             <div style="font-size: 13px; color: #15803d; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 4px;">Present</div>
           </div>
           <div style="background: #fffbeb; border: 1px solid #fde68a; padding: 16px; border-radius: 8px; text-align: center;">
             <div style="font-size: 24px; font-weight: 700; color: #92400e;"><?= $late ?></div>
             <div style="font-size: 13px; color: #b45309; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 4px;">Late</div>
           </div>
           <div style="background: #fef2f2; border: 1px solid #fecaca; padding: 16px; border-radius: 8px; text-align: center;">
             <div style="font-size: 24px; font-weight: 700; color: #991b1b;"><?= $absent ?></div>
             <div style="font-size: 13px; color: #b91c1c; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 4px;">Absent</div>
           </div>
        </div>
      </div>

      <!-- Attendance History -->
      <div class="card">
        <h3 style="margin-top: 0; margin-bottom: 16px; color: #0f172a;">Recent Attendance History</h3>
        <?php if (empty($attendanceHistory)): ?>
          <div style="text-align: center; padding: 32px 0; color: #64748b; background: #f8fafc; border-radius: 8px; border: 1px dashed #cbd5e1;">
            No attendance records found for this student.
          </div>
        <?php else: ?>
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Course</th>
                  <th>Session Title</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($attendanceHistory as $rec): ?>
                  <tr>
                    <td><?= date('M d, Y', strtotime($rec['session_date'])) ?></td>
                    <td><strong style="color: #0f172a;"><?= htmlspecialchars((string)$rec['course_name']) ?></strong></td>
                    <td><?= htmlspecialchars((string)$rec['title']) ?></td>
                    <td>
                      <?php
                        $st = strtolower($rec['status']);
                        if ($st === 'present') echo '<span class="badge badge-success">Present</span>';
                        elseif ($st === 'late') echo '<span class="badge badge-warning">Late</span>';
                        else echo '<span class="badge badge-danger">Absent</span>';
                      ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    
    <?php elseif ($user['role'] === 'teacher'): ?>
      <!-- Teacher Classes -->
      <div class="card">
        <h3 style="margin-top: 0; margin-bottom: 16px; color: #0f172a;">Teaching Courses</h3>
        <?php if (empty($teacherClasses)): ?>
          <div style="text-align: center; padding: 32px 0; color: #64748b; background: #f8fafc; border-radius: 8px; border: 1px dashed #cbd5e1;">
            This teacher is currently not assigned to any courses with active sessions.
          </div>
        <?php else: ?>
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <?php foreach ($teacherClasses as $c): ?>
              <div style="border: 1px solid #e2e8f0; padding: 16px; border-radius: 8px; display: flex; align-items: center; gap: 16px;">
                <div style="background: #eff6ff; color: #2563eb; width: 48px; height: 48px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                  <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="24" height="24"><path d="M12 14l9-5-9-5-9 5 9 5z"/><path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/></svg>
                </div>
                <div>
                  <div style="font-weight: 600; color: #0f172a; font-size: 15px;"><?= htmlspecialchars((string)$c['course_name']) ?></div>
                  <div style="color: #64748b; font-size: 13px; margin-top: 4px;">Code: <?= htmlspecialchars((string)$c['course_code']) ?> • <?= $c['session_count'] ?> Sessions</div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>

  </div>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>
