<?php
require_once dirname(__DIR__) . '/bootstrap.php';
require_once ROOT_PATH . '/app/auth.php';
require_role('admin');

$feature_toggles = [
  'leaderboards' => true,
  'badges' => true,
  'campaigns' => true,
];

$workflows = [
  'two_step_log_approval' => false,
  'gps_mandatory' => true,
  'biomass_estimate_enabled' => false,
];
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>System Settings</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo base_url('assets/css/admin.css'); ?>">
</head>
<body>
  <div class="admin-layout">
    <?php include __DIR__ . '/_sidebar.php'; ?>
    <main class="admin-content">
      <?php include __DIR__ . '/_navbar.php'; ?>
      <div class="container-fluid py-3">
        <h1 class="h3 mb-3">System Settings</h1>

  <div class="card p-3 mb-3">
    <h2 class="h5">Permissions</h2>
    <p>Define permissions per role (who can approve, announce, manage logs).</p>
    <button class="btn btn-secondary">Edit Permissions</button>
  </div>

  <div class="card p-3 mb-3">
    <h2 class="h5">Feature Toggles</h2>
    <ul class="list-group">
      <?php foreach ($feature_toggles as $k=>$v): ?>
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <span><?php echo ucfirst(str_replace('_',' ',$k)); ?></span>
          <span class="badge bg-<?php echo $v?'success':'secondary'; ?>"><?php echo $v?'ON':'OFF'; ?></span>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>

  <div class="card p-3">
    <h2 class="h5">Workflows & Carbon Settings</h2>
    <ul class="list-group">
      <li class="list-group-item d-flex justify-content-between"><span>2-Step Log Approval</span><span><?php echo $workflows['two_step_log_approval']?'Enabled':'Disabled'; ?></span></li>
      <li class="list-group-item d-flex justify-content-between"><span>GPS Mandatory</span><span><?php echo $workflows['gps_mandatory']?'Yes':'No'; ?></span></li>
      <li class="list-group-item d-flex justify-content-between"><span>Biomass Estimate</span><span><?php echo $workflows['biomass_estimate_enabled']?'Enabled':'Disabled'; ?></span></li>
    </ul>
  </div>
    </main>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
