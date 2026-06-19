<?php
$currentPage = 'admin.reports';
$pageTitle = 'Reports & Export';
require APP_ROOT . '/views/layouts/header.php';
?>

<div class="admin-page-title">
  <div class="left">
    <h1>Reports & Export</h1>
    <p>Generate and export academic reports, attendance records, and engagement analytics.</p>
  </div>
</div>

<div class="card" style="margin-top: 24px;">
  <div class="card-body" style="text-align: center; padding: 60px 20px;">
    <svg style="width: 80px; height: 80px; color: #cbd5e1; margin-bottom: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
      <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
    </svg>
    <h3 style="font-size: 20px; font-weight: 700; color: #1e293b; margin-bottom: 12px;">Reporting Module Under Construction</h3>
    <p style="color: #64748b; max-width: 500px; margin: 0 auto; line-height: 1.6;">
      This module is currently being developed. In future updates, you will be able to seamlessly export attendance logs, student engagement summaries, and course performance analytics in CSV and PDF formats.
    </p>
    <br>
    <a href="<?= APP_URL ?>/index.php?page=admin_dashboard" class="btn btn-primary" style="margin-top: 16px; padding: 10px 20px; border-radius: 8px; text-decoration: none;">Return to Dashboard</a>
  </div>
</div>

<?php 
if (file_exists(APP_ROOT . '/views/layouts/footer.php')) {
    require APP_ROOT . '/views/layouts/footer.php'; 
} else {
    echo '</div></div></body></html>';
}
?>
