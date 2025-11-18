<?php
require_once dirname(__DIR__) . '/bootstrap.php';
require_once ROOT_PATH . '/app/auth.php';
require_role('admin');

$pdo = db();
$sponsors = [];
try {
    $sponsors = $pdo->query('SELECT id, name, tier, status, created_at FROM sponsors ORDER BY created_at DESC')->fetchAll();
} catch (Throwable $e) {
    $sponsors = [];
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sponsors & Donors</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo base_url('assets/css/admin.css'); ?>">
</head>
<body>
  <div class="admin-layout">
    <?php include __DIR__ . '/_sidebar.php'; ?>
    <main class="admin-content">
      <?php include __DIR__ . '/_navbar.php'; ?>
      <div class="container-fluid py-3">
        <h1 class="h3 mb-3">Sponsors & Donors</h1>

  <div class="alert alert-info">Manage sponsor accounts, campaigns, tiers (Bronze/Silver/Gold), and visibility in leaderboards/announcements. Approvals pending implementation.</div>

  <div class="table-responsive">
    <table class="table table-striped align-middle">
      <thead><tr><th>ID</th><th>Name</th><th>Tier</th><th>Status</th><th>Created</th></tr></thead>
      <tbody>
      <?php foreach ($sponsors as $s): ?>
        <tr>
          <td><?php echo (int)$s['id']; ?></td>
          <td><?php echo htmlspecialchars($s['name']); ?></td>
          <td><?php echo htmlspecialchars($s['tier']); ?></td>
          <td><?php echo htmlspecialchars($s['status']); ?></td>
          <td><?php echo htmlspecialchars($s['created_at']); ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
    </main>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
