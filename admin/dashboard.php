<?php
require_once dirname(__DIR__, 1) . '/app/config.php';
require_once ROOT_PATH . '/app/auth.php';
require_role('admin');

$kpis = [
    'total_users' => (int)(db()->query('SELECT COUNT(*) FROM users')->fetchColumn() ?: 0),
    // No trees table/column in schema; use sponsorships count as proxy for total adoptions
    'total_trees' => (int)(db()->query('SELECT COUNT(*) FROM sponsorships')->fetchColumn() ?: 0),
    // No counties table in schema; fallback to 0.0 or compute from available data if added later
    'survival_pct' => 0.0,
    // Show active schools as an operational KPI instead
    'active_counties' => (int)(db()->query("SELECT COUNT(*) FROM users WHERE role='school' AND status='active'")->fetchColumn() ?: 0),
    // No campaigns table; placeholder 0
    'campaigns' => 0,
    // We have amount in sponsorships; use SUM(amount) as total revenue/donations
    'total_revenue' => (float)(db()->query('SELECT COALESCE(SUM(amount),0) FROM sponsorships')->fetchColumn() ?: 0.0),
];

$alerts = [
    'pending_approvals' => (int)(db()->query("SELECT COUNT(*) FROM users WHERE status='pending_approval'")->fetchColumn() ?: 0),
    // No tree_logs table in schema; set to 0
    'flagged_logs' => 0,
    // No sponsorship_requests table; approximate as pending sponsorships
    'sponsorship_requests' => (int)(db()->query("SELECT COUNT(*) FROM sponsorships WHERE status='pending'")->fetchColumn() ?: 0),
];

if (($_GET['export'] ?? '') === 'csv') {
    $rows = [
        ['Metric','Value'],
        ['Total Users',$kpis['total_users']],
        ['Total Trees Adopted',$kpis['total_trees']],
        ['Survival %',$kpis['survival_pct']],
        ['Active Schools',$kpis['active_counties']],
        ['Campaigns',$kpis['campaigns']],
        ['Total Revenue',$kpis['total_revenue']],
        ['Pending Approvals',$alerts['pending_approvals']],
        ['Flagged Logs',$alerts['flagged_logs']],
        ['Sponsorship Requests',$alerts['sponsorship_requests']],
    ];
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="admin_dashboard_overview.csv"');
    $out = fopen('php://output','w');
    foreach ($rows as $r) fputcsv($out, $r);
    fclose($out);
    exit;
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo base_url('assets/css/admin.css'); ?>">
</head>
<body>
  <div class="admin-layout">
    <?php require_once ROOT_PATH . '/admin/_sidebar.php'; ?>
    <main class="admin-content">
      <?php require_once ROOT_PATH . '/admin/_navbar.php'; ?>

      <div class="container-fluid py-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h1 class="h3 m-0">Overview</h1>
          <a class="btn btn-sm btn-primary" href="?export=csv">Export CSV</a>
        </div>

        <div class="row g-3">
          <div class="col-md-4">
            <div class="card p-3"><div class="muted small">Total Users</div><strong class="fs-3"><?php echo number_format($kpis['total_users']); ?></strong></div>
          </div>
          <div class="col-md-4">
            <div class="card p-3"><div class="muted small">Total Trees Adopted</div><strong class="fs-3"><?php echo number_format($kpis['total_trees']); ?></strong></div>
          </div>
          <div class="col-md-4">
            <div class="card p-3"><div class="muted small">Survival %</div><strong class="fs-3"><?php echo number_format($kpis['survival_pct'],2); ?>%</strong></div>
          </div>
          <div class="col-md-4">
            <div class="card p-3"><div class="muted small">Active Schools</div><strong class="fs-3"><?php echo number_format($kpis['active_counties']); ?></strong></div>
          </div>
          <div class="col-md-4">
            <div class="card p-3"><div class="muted small">Campaigns</div><strong class="fs-3"><?php echo number_format($kpis['campaigns']); ?></strong></div>
          </div>
          <div class="col-md-4">
            <div class="card p-3"><div class="muted small">Total Revenue</div><strong class="fs-3">$<?php echo number_format($kpis['total_revenue'],2); ?></strong></div>
          </div>
        </div>

        <h2 class="h5 mt-4">Alerts</h2>
        <div class="row g-3">
          <div class="col-md-4"><div class="card p-3"><div class="muted small">Pending Approvals</div><strong class="fs-4"><?php echo number_format($alerts['pending_approvals']); ?></strong></div></div>
          <div class="col-md-4"><div class="card p-3"><div class="muted small">Flagged Logs</div><strong class="fs-4"><?php echo number_format($alerts['flagged_logs']); ?></strong></div></div>
          <div class="col-md-4"><div class="card p-3"><div class="muted small">Sponsorship Requests</div><strong class="fs-4"><?php echo number_format($alerts['sponsorship_requests']); ?></strong></div></div>
        </div>

        <div class="card p-3 mt-4">
          <div class="muted">Widgets by role will be shown here (Admin: county data; Schools: performance; Sponsors: campaigns).</div>
        </div>
      </div>
    </main>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
