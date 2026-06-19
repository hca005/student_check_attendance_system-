<?php
Middleware::requireAdmin();
$pageTitle = 'Engagement Monitoring';
$currentPage = 'admin.engagement';
require APP_ROOT . '/views/layouts/header.php';

function engagement_risk(float $score): array
{
    if ($score > 75) {
        return ['GOOD', 'badge-success', 'risk-good'];
    }
    if ($score >= 50) {
        return ['MEDIUM', 'badge-warning', 'risk-medium'];
    }
    return ['AT RISK', 'badge-danger', 'risk-risk'];
}

function user_initials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name));
    $a = strtoupper(substr($parts[0] ?? 'U', 0, 1));
    $b = strtoupper(substr($parts[1] ?? '', 0, 1));
    return $a . $b;
}

$palette = ['avatar-blue', 'avatar-green', 'avatar-orange', 'avatar-red'];
$barItems = $barData;
if (count($barItems) < 7) {
    for ($i = count($barItems); $i < 7; $i++) {
        $barItems[] = ['course_code' => 'W' . ($i + 1), 'avg_score' => 0];
    }
}
?>

<div class="admin-page-title">
  <div class="left">
    <h1>Engagement Monitoring</h1>
    <p>Real-time analysis of student interaction and performance metrics.</p>
  </div>
  <button class="btn btn-primary" type="button" onclick="window.print()">
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
    Export Report
  </button>
</div>

<?php if (!empty($flashSuccess)): ?>
  <div class="alert alert-success"><?= htmlspecialchars((string)$flashSuccess) ?></div>
<?php endif; ?>
<?php if (!empty($flashError)): ?>
  <div class="alert alert-danger"><?= htmlspecialchars((string)$flashError) ?></div>
<?php endif; ?>

<div class="stat-cards">
  <div class="card stat-card">
    <div class="stat-icon" style="background:#eff6ff">
      <svg fill="none" viewBox="0 0 24 24" stroke="#2563eb" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
    </div>
    <div><div class="stat-label">Average Engagement</div><div class="stat-value"><?= (int)$stats['avg'] ?>%</div></div>
  </div>
  <div class="card stat-card">
    <div class="stat-icon" style="background:#ecfdf5">
      <svg fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="9 12 12 15 16 10"/></svg>
    </div>
    <div><div class="stat-label">High Engagement</div><div class="stat-value"><?= (int)$stats['high'] ?></div></div>
  </div>
  <div class="card stat-card">
    <div class="stat-icon" style="background:#fffbeb">
      <svg fill="none" viewBox="0 0 24 24" stroke="#f59e0b" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
    </div>
    <div><div class="stat-label">At Risk Students</div><div class="stat-value"><?= (int)$stats['risk'] ?></div></div>
  </div>
  <div class="card stat-card">
    <div class="stat-icon" style="background:#fee2e2">
      <svg fill="none" viewBox="0 0 24 24" stroke="#ef4444" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
    </div>
    <div><div class="stat-label">Low Score Alerts</div><div class="stat-value"><?= (int)$stats['low_alerts'] ?></div></div>
  </div>
</div>

<div class="chart-grid">
  <div class="card chart-card">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
      <h3 style="margin:0">Engagement Overview</h3>
      <span class="badge badge-gray">Current Semester</span>
    </div>
    <div class="bar-chart">
      <?php foreach ($barItems as $idx => $item): ?>
        <?php
          $height = max(8, min(100, (float)$item['avg_score']));
          $code = trim((string)$item['course_code']) !== '' ? (string)$item['course_code'] : ('W' . ($idx + 1));
          $barColor = $height >= 75 ? '#1d4ed8' : ($height >= 50 ? '#4f7fe4' : '#9dbbf7');
        ?>
        <div class="bar-col">
          <div class="bar" style="--h:<?= $height ?>%;background:<?= $barColor ?>"></div>
          <div class="bar-label"><?= htmlspecialchars($code) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="card chart-card">
    <h3>Score Distribution</h3>
    <div class="donut-wrap">
      <div class="donut" style="--good:<?= (int)$distribution['good'] ?>;--medium:<?= (int)$distribution['medium'] ?>;--risk:<?= (int)$distribution['risk'] ?>;">
        <div class="donut-center">
          <strong><?= (int)$stats['avg'] ?>%</strong>
          <span>Avg Score</span>
        </div>
      </div>
    </div>
    <div class="legend">
      <div class="legend-item"><div class="legend-left"><span class="dot dot-good"></span><span>Good (&gt;75)</span></div><strong><?= (int)$distribution['good'] ?>%</strong></div>
      <div class="legend-item"><div class="legend-left"><span class="dot dot-medium"></span><span>Medium (50-75)</span></div><strong><?= (int)$distribution['medium'] ?>%</strong></div>
      <div class="legend-item"><div class="legend-left"><span class="dot dot-risk"></span><span>At Risk (&lt;50)</span></div><strong><?= (int)$distribution['risk'] ?>%</strong></div>
    </div>
  </div>
</div>

<div class="card admin-toolbar">
  <form method="get" action="<?= APP_URL ?>/index.php">
    <input type="hidden" name="page" value="admin_engagement_scores">
    <div class="filter-grid">
      <input type="text" name="search" value="<?= htmlspecialchars((string)$filters['search']) ?>" placeholder="Search student or course">
      <select name="course_id">
        <option value="">All Courses</option>
        <?php foreach ($courseOptions as $course): ?>
          <option value="<?= (int)$course['id'] ?>" <?= (int)$filters['course_id'] === (int)$course['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars((string)$course['course_code']) ?> - <?= htmlspecialchars((string)$course['course_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <select name="score_range">
        <option value="">All Levels</option>
        <option value="high" <?= $filters['score_range'] === 'high' ? 'selected' : '' ?>>High</option>
        <option value="medium" <?= $filters['score_range'] === 'medium' ? 'selected' : '' ?>>Medium</option>
        <option value="low" <?= $filters['score_range'] === 'low' ? 'selected' : '' ?>>Low / At Risk</option>
      </select>
      <select name="sort">
        <option value="lowest" <?= $filters['sort'] === 'lowest' ? 'selected' : '' ?>>Lowest First</option>
        <option value="highest" <?= $filters['sort'] === 'highest' ? 'selected' : '' ?>>Highest First</option>
      </select>
    </div>
    <div class="filter-actions">
      <button class="btn btn-primary" type="submit">Apply Filters</button>
      <a class="btn btn-outline" href="<?= APP_URL ?>/index.php?page=admin_engagement_scores">Reset</a>
    </div>
  </form>
</div>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Student</th>
          <th>Student Code</th>
          <th>Course</th>
          <th>Attendance Rate</th>
          <th>Quiz Score</th>
          <th>Interaction Points</th>
          <th>Total Score</th>
          <th>Risk Level</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($scores as $idx => $score): ?>
        <?php
          [$riskLabel, $riskBadge, $riskTextClass] = engagement_risk((float)$score['engagement_index']);
          $paletteClass = $palette[$idx % count($palette)];
          $attendanceRate = (int)$score['attendance_rate'];
        ?>
        <tr>
          <td>
            <div class="list-row-user">
              <div class="avatar <?= $paletteClass ?>"><?= user_initials((string)$score['student_name']) ?></div>
              <strong><?= htmlspecialchars((string)$score['student_name']) ?></strong>
            </div>
          </td>
          <td><?= htmlspecialchars((string)($score['student_code'] ?: '--')) ?></td>
          <td><?= htmlspecialchars((string)$score['course_code']) ?></td>
          <td class="<?= $attendanceRate >= 80 ? 'risk-good' : ($attendanceRate >= 50 ? 'risk-medium' : 'risk-risk') ?>"><?= $attendanceRate ?>%</td>
          <td><?= number_format((float)$score['total_quiz_score'], 2) ?></td>
          <td><?= number_format((float)$score['total_interaction_points'], 2) ?></td>
          <td><strong class="<?= $riskTextClass ?>"><?= number_format((float)$score['engagement_index'], 2) ?></strong></td>
          <td><span class="badge <?= $riskBadge ?>"><?= $riskLabel ?></span></td>
          <td>
            <a class="btn btn-outline btn-sm" href="<?= APP_URL ?>/index.php?page=admin_alerts&search=<?= urlencode((string)$score['student_name']) ?>">View Detail</a>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($scores)): ?>
        <tr><td colspan="9" style="text-align:center;color:#64748b;padding:24px">No engagement score records found.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="list-meta">Showing <?= count($scores) ?> of <?= (int)$totalCount ?> entries</div>

  <?php if ($totalPages > 1): ?>
    <div class="pagination">
      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a class="page-btn <?= $i === $currentPageNum ? 'active' : '' ?>" href="<?= APP_URL ?>/index.php?page=admin_engagement_scores&search=<?= urlencode((string)$filters['search']) ?>&course_id=<?= (int)$filters['course_id'] ?>&score_range=<?= urlencode((string)$filters['score_range']) ?>&sort=<?= urlencode((string)$filters['sort']) ?>&p=<?= $i ?>">
          <?= $i ?>
        </a>
      <?php endfor; ?>
    </div>
  <?php endif; ?>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>
