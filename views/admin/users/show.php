<?php
Middleware::requireAdmin();
$pageTitle = 'User Profile';
$currentPage = 'admin.users';
require APP_ROOT . '/views/layouts/header.php';

$roleMap = [
    'admin' => ['bg' => '#fee2e2', 'text' => '#b91c1c'],
    'teacher' => ['bg' => '#e0e7ff', 'text' => '#4338ca'],
    'student' => ['bg' => '#d1fae5', 'text' => '#065f46'],
];
$activeMap = [
    1 => ['bg' => '#d1fae5', 'text' => '#065f46', 'label' => 'Active'],
    0 => ['bg' => '#f1f5f9', 'text' => '#475569', 'label' => 'Inactive'],
];

$age = '--';
if (!empty($user['date_of_birth'])) {
    $dob = new DateTime($user['date_of_birth']);
    $now = new DateTime();
    $age = $now->diff($dob)->y;
}

$avatarColor = $user['role'] === 'admin' ? 'background: #ef4444;' : ($user['role'] === 'teacher' ? 'background: #3b82f6;' : 'background: #10b981;');
$initial = strtoupper(substr((string)$user['full_name'], 0, 1));
?>

<div class="admin-page-title" style="margin-bottom: 32px; display: flex; justify-content: space-between; align-items: flex-start;">
  <div class="left">
    <h1 style="font-size: 24px; font-weight: 700; color: #0f172a; margin: 0 0 4px;">User Profile</h1>
    <p style="color: #64748b; font-size: 14px; margin: 0;">Detailed view of user demographics and academic records.</p>
  </div>
  <a class="btn btn-outline" style="border-radius: 8px; font-size: 13px; font-weight: 500;" href="<?= APP_URL ?>/index.php?page=admin_users">
    &larr; Back to Users
  </a>
</div>

<div class="profile-layout" style="display:grid; grid-template-columns: 320px 1fr; gap: 32px; align-items: start;">
  
  <!-- Left Column: Core Identity -->
  <div style="background: #fff; border: 1px solid #f1f5f9; border-radius: 16px; padding: 32px 24px; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
    <div style="width: 100px; height: 100px; border-radius: 50%; <?= $avatarColor ?> color: #fff; font-size: 40px; font-weight: 600; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
      <?= $initial ?>
    </div>
    <h2 style="margin: 0 0 12px; font-size: 20px; font-weight: 700; color: #0f172a;"><?= htmlspecialchars((string)$user['full_name']) ?></h2>
    
    <div style="margin-bottom: 24px; display: flex; gap: 8px; justify-content: center;">
      <?php $r = $roleMap[$user['role']] ?? $roleMap['student']; ?>
      <span style="background: <?= $r['bg'] ?>; color: <?= $r['text'] ?>; padding: 4px 16px; border-radius: 999px; font-size: 12px; font-weight: 600; text-transform: capitalize;">
        <?= htmlspecialchars((string)$user['role']) ?>
      </span>
      <?php $a = $activeMap[(int)$user['is_active']] ?? $activeMap[0]; ?>
      <span style="background: <?= $a['bg'] ?>; color: <?= $a['text'] ?>; padding: 4px 16px; border-radius: 999px; font-size: 12px; font-weight: 600;">
        <?= $a['label'] ?>
      </span>
    </div>
    
    <div style="text-align: left; margin-top: 32px; border-top: 1px solid #f1f5f9; padding-top: 32px;">
      <?php
      $fields = [
          'ID Code' => $user['student_code'] ?: '--',
          'Email' => $user['email'],
          'Phone' => $user['phone'] ?: '--',
          'Gender' => $user['gender'] ?: '--',
          'Date of Birth' => ($user['date_of_birth'] ? $user['date_of_birth'] . ' <span style="color:#94a3b8;font-weight:400">(' . $age . ' yrs)</span>' : '--'),
          'ID Card (CCCD)' => $user['id_card_number'] ?: '--',
          'Hometown' => $user['hometown'] ?: '--',
          'Joined Date' => date('M d, Y', strtotime($user['created_at']))
      ];
      foreach ($fields as $label => $val):
      ?>
      <div style="margin-bottom: 16px; display: flex; justify-content: space-between; align-items: baseline;">
        <span style="color:#64748b; font-size: 13px; white-space: nowrap;"><?= $label ?></span>
        <strong style="color:#0f172a; font-size: 13px; font-weight: 600; text-align: right; max-width: 180px; word-break: break-all;"><?= $val ?></strong>
      </div>
      <?php endforeach; ?>
    </div>
    
    <div style="margin-top: 32px;">
      <a style="display: block; width: 100%; padding: 10px 0; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; color: #334155; font-size: 13px; font-weight: 600; text-decoration: none; text-align: center; transition: all 0.2s;" href="<?= APP_URL ?>/index.php?page=admin_users_edit&id=<?= (int)$user['id'] ?>" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#fff'">
        Edit User Information
      </a>
    </div>
  </div>

  <!-- Right Column: Academic Details & Stats -->
  <div style="display:flex; flex-direction: column;">
    
    <!-- Academic Profile section -->
    <h3 style="margin: 0 0 16px; font-size: 16px; font-weight: 600; color: #0f172a;">Academic Profile</h3>
    <div style="display: flex; gap: 16px;">
      <?php if ($user['role'] === 'student'): ?>
        <div style="flex: 1; background: #fff; border: 1px solid #f1f5f9; border-radius: 12px; padding: 24px 16px; text-align: center; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
          <div style="color:#94a3b8; font-size: 11px; margin-bottom: 8px; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Class Name</div>
          <div style="font-weight: 700; font-size: 18px; color: #0f172a;"><?= htmlspecialchars((string)($user['class_name'] ?: '--')) ?></div>
        </div>
        <div style="flex: 1; background: #fff; border: 1px solid #f1f5f9; border-radius: 12px; padding: 24px 16px; text-align: center; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
          <div style="color:#94a3b8; font-size: 11px; margin-bottom: 8px; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Academic Year</div>
          <div style="font-weight: 700; font-size: 18px; color: #0f172a;"><?= htmlspecialchars((string)($user['academic_year'] ?: '--')) ?></div>
        </div>
        <div style="flex: 1; background: #fff; border: 1px solid #f1f5f9; border-radius: 12px; padding: 24px 16px; text-align: center; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
          <div style="color:#94a3b8; font-size: 11px; margin-bottom: 8px; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Major / Department</div>
          <div style="font-weight: 700; font-size: 18px; color: #0f172a;"><?= htmlspecialchars((string)($user['department'] ?: '--')) ?></div>
        </div>
      <?php elseif ($user['role'] === 'teacher'): ?>
        <div style="flex: 1; background: #fff; border: 1px solid #f1f5f9; border-radius: 12px; padding: 24px 16px; text-align: center; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
          <div style="color:#94a3b8; font-size: 11px; margin-bottom: 8px; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Qualification</div>
          <div style="font-weight: 700; font-size: 18px; color: #0f172a;"><?= htmlspecialchars((string)($user['qualification'] ?: '--')) ?></div>
        </div>
        <div style="flex: 1; background: #fff; border: 1px solid #f1f5f9; border-radius: 12px; padding: 24px 16px; text-align: center; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
          <div style="color:#94a3b8; font-size: 11px; margin-bottom: 8px; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Department</div>
          <div style="font-weight: 700; font-size: 18px; color: #0f172a;"><?= htmlspecialchars((string)($user['department'] ?: '--')) ?></div>
        </div>
      <?php else: ?>
        <div style="color: #64748b; font-size: 14px;">No specific academic profile needed for administrators.</div>
      <?php endif; ?>
    </div>

    <?php if ($user['role'] === 'student'): ?>
      <!-- Student Analytics -->
      <h3 style="margin: 32px 0 16px; font-size: 16px; font-weight: 600; color: #0f172a;">Attendance Analytics</h3>
      <div style="background: #fff; border: 1px solid #f1f5f9; border-radius: 12px; padding: 24px; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
        
        <?php 
          $total = (int)($attendanceStats['total_sessions'] ?? 0);
          $present = (int)($attendanceStats['present_count'] ?? 0);
          $late = (int)($attendanceStats['late_count'] ?? 0);
          $absent = (int)($attendanceStats['absent_count'] ?? 0);
          
          $attended = $present + $late;
          $attendanceRate = $total > 0 ? round(($attended / $total) * 100) : 0;
          $progressColor = $attendanceRate >= 80 ? '#10b981' : ($attendanceRate >= 50 ? '#f59e0b' : '#ef4444');
        ?>
        
        <div style="display: flex; gap: 32px; align-items: center; margin-bottom: 32px;">
          <div style="width: 80px; text-align: center;">
            <div style="font-size: 40px; font-weight: 700; color: <?= $progressColor ?>; line-height: 1;"><?= $attendanceRate ?>%</div>
            <div style="font-size: 12px; color: #64748b; margin-top: 8px;">Overall Rate</div>
          </div>
          <div style="flex: 1;">
            <div style="display:flex; justify-content:space-between; font-size:13px; margin-bottom:12px; font-weight: 500;">
              <span style="color:#64748b">Progress to 100%</span>
              <strong style="color:<?= $progressColor ?>"><?= $attendanceRate ?>%</strong>
            </div>
            <div style="height: 10px; background: #f1f5f9; border-radius: 5px; overflow: hidden;">
              <div style="height: 100%; width: <?= $attendanceRate ?>%; background: <?= $progressColor ?>; border-radius: 5px; transition: width 1s ease;"></div>
            </div>
          </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
           <div style="background: #f0fdf4; border: 1px solid #dcfce7; padding: 20px; border-radius: 8px; text-align: center;">
             <div style="font-size: 24px; font-weight: 700; color: #166534;"><?= $present ?></div>
             <div style="font-size: 11px; color: #15803d; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; margin-top: 4px;">Present</div>
           </div>
           <div style="background: #fffbeb; border: 1px solid #fef3c7; padding: 20px; border-radius: 8px; text-align: center;">
             <div style="font-size: 24px; font-weight: 700; color: #b45309;"><?= $late ?></div>
             <div style="font-size: 11px; color: #b45309; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; margin-top: 4px;">Late</div>
           </div>
           <div style="background: #fef2f2; border: 1px solid #fee2e2; padding: 20px; border-radius: 8px; text-align: center;">
             <div style="font-size: 24px; font-weight: 700; color: #b91c1c;"><?= $absent ?></div>
             <div style="font-size: 11px; color: #b91c1c; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; margin-top: 4px;">Absent</div>
           </div>
        </div>
      </div>

      <!-- Attendance History -->
      <div style="display: flex; justify-content: space-between; align-items: baseline; margin-top: 32px; margin-bottom: 16px;">
        <h3 style="margin: 0; font-size: 16px; font-weight: 600; color: #0f172a;">Recent Attendance History</h3>
        <a href="#" style="color: #2563eb; font-size: 13px; text-decoration: none; font-weight: 500;">View Detailed Logs &rarr;</a>
      </div>

      <?php if (empty($attendanceHistory)): ?>
        <div style="border: 1px dashed #cbd5e1; border-radius: 12px; padding: 48px; text-align: center; color: #64748b; font-size: 14px; background: #fafafa;">
          <svg style="margin: 0 auto 12px; color: #cbd5e1;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="40" height="40">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
          </svg>
          No attendance records found for this student.
        </div>
      <?php else: ?>
        <div style="background: #fff; border: 1px solid #f1f5f9; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
          <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
              <tr style="background: #f8fafc; border-bottom: 1px solid #f1f5f9;">
                <th style="padding: 12px 16px; font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase;">Date</th>
                <th style="padding: 12px 16px; font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase;">Course</th>
                <th style="padding: 12px 16px; font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase;">Session Title</th>
                <th style="padding: 12px 16px; font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; text-align: right;">Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($attendanceHistory as $rec): ?>
                <tr style="border-bottom: 1px solid #f1f5f9;">
                  <td style="padding: 16px; font-size: 13px; color: #334155; font-weight: 500;"><?= date('M d, Y', strtotime($rec['session_date'])) ?></td>
                  <td style="padding: 16px; font-size: 13px; color: #0f172a; font-weight: 600;"><?= htmlspecialchars((string)$rec['course_name']) ?></td>
                  <td style="padding: 16px; font-size: 13px; color: #475569;"><?= htmlspecialchars((string)$rec['title']) ?></td>
                  <td style="padding: 16px; font-size: 13px; text-align: right;">
                    <?php
                      $st = strtolower($rec['status']);
                      if ($st === 'present') echo '<span style="background: #dcfce7; color: #166534; padding: 4px 12px; border-radius: 999px; font-size: 11px; font-weight: 600;">Present</span>';
                      elseif ($st === 'late') echo '<span style="background: #fef3c7; color: #b45309; padding: 4px 12px; border-radius: 999px; font-size: 11px; font-weight: 600;">Late</span>';
                      else echo '<span style="background: #fee2e2; color: #b91c1c; padding: 4px 12px; border-radius: 999px; font-size: 11px; font-weight: 600;">Absent</span>';
                    ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    
    <?php elseif ($user['role'] === 'teacher'): ?>
      <!-- Teacher Classes -->
      <h3 style="margin: 32px 0 16px; font-size: 16px; font-weight: 600; color: #0f172a;">Teaching Courses</h3>
      <?php if (empty($teacherClasses)): ?>
        <div style="border: 1px dashed #cbd5e1; border-radius: 12px; padding: 48px; text-align: center; color: #64748b; font-size: 14px; background: #fafafa;">
          <svg style="margin: 0 auto 12px; color: #cbd5e1;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="40" height="40">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
          </svg>
          This teacher is currently not assigned to any courses with active sessions.
        </div>
      <?php else: ?>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
          <?php foreach ($teacherClasses as $c): ?>
            <div style="background: #fff; border: 1px solid #f1f5f9; padding: 20px; border-radius: 12px; display: flex; align-items: center; gap: 16px; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
              <div style="background: #eff6ff; color: #2563eb; width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="24" height="24"><path d="M12 14l9-5-9-5-9 5 9 5z"/><path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/></svg>
              </div>
              <div>
                <div style="font-weight: 600; color: #0f172a; font-size: 15px; margin-bottom: 4px;"><?= htmlspecialchars((string)$c['course_name']) ?></div>
                <div style="color: #64748b; font-size: 13px;">Code: <?= htmlspecialchars((string)$c['course_code']) ?> &bull; <?= $c['session_count'] ?> Sessions</div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>

  </div>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>
