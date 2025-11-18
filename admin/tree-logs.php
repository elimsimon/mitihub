<?php
require_once dirname(__DIR__) . '/bootstrap.php';
require_once ROOT_PATH . '/app/auth.php';
require_role('admin');

$pdo = db();
$logs = [];
try {
    $logs = $pdo->query('SELECT id, county, species, survival, gps_lat, gps_lng, created_at, flagged FROM tree_logs ORDER BY created_at DESC LIMIT 200')->fetchAll();
} catch (Throwable $e) {
    $logs = [];
}

if (($_GET['export'] ?? '') === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="tree_logs.csv"');
    $out = fopen('php://output','w');
    fputcsv($out, ['ID','County','Species','Survival','GPS Lat','GPS Lng','Created','Flagged']);
    foreach ($logs as $r) {
        fputcsv($out, [$r['id'], $r['county'], $r['species'], $r['survival'], $r['gps_lat'], $r['gps_lng'], $r['created_at'], $r['flagged']]);
    }
    fclose($out);
    exit;
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tree Logs</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo base_url('assets/css/admin.css'); ?>">
</head>
<body>
  <div class="admin-layout">
    <?php include __DIR__ . '/_sidebar.php'; ?>
    <main class="admin-content">
      <?php include __DIR__ . '/_navbar.php'; ?>
      <div class="container-fluid py-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Tree Adoptions & Logs</h1>
    <a class="btn btn-sm btn-primary" href="?export=csv">Export CSV</a>
  </div>

  <div class="alert alert-warning">MRV standards (GPS+timestamp validation, mandatory survival photos, metadata storage) to be enforced here. Override approval/flagging workflows pending.</div>

  <div class="table-responsive">
    <table class="table table-striped align-middle">
      <thead><tr><th>ID</th><th>County</th><th>Species</th><th>Survival</th><th>GPS</th><th>Created</th><th>Flagged</th></tr></thead>
      <tbody>
      <?php foreach ($logs as $r): ?>
        <tr>
          <td><?php echo (int)$r['id']; ?></td>
          <td><?php echo htmlspecialchars($r['county']); ?></td>
          <td><?php echo htmlspecialchars($r['species']); ?></td>
          <td><?php echo htmlspecialchars($r['survival']); ?>%</td>
          <td><?php echo htmlspecialchars($r['gps_lat'] . ', ' . $r['gps_lng']); ?></td>
          <td><?php echo htmlspecialchars($r['created_at']); ?></td>
          <td><?php echo $r['flagged'] ? 'Yes' : 'No'; ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
    </main>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
