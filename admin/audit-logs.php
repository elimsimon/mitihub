<?php
require_once dirname(__DIR__) . '/bootstrap.php';
require_once ROOT_PATH . '/app/auth.php';
require_once ROOT_PATH . '/admin/alerts_functions.php';
require_role('admin');

$admin = current_user();
$limit = 100;
$filter_admin = $_GET['admin'] ?? null;
$filter_action = $_GET['action'] ?? null;
$filter_entity = $_GET['entity'] ?? null;
$export = $_GET['export'] ?? null;

// Get audit logs
$logs = get_audit_logs($limit, $filter_admin, $filter_action, $filter_entity);

// CSV Export
if ($export === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="audit_logs_' . date('Y-m-d') . '.csv"');
    $fp = fopen('php://output', 'w');
    fputcsv($fp, ['Date', 'Admin', 'Action', 'Entity', 'Details', 'IP Address']);
    foreach ($logs as $log) {
        fputcsv($fp, [
            $log['created_at'],
            $log['admin_name'],
            $log['action'],
            $log['entity_type'] . ' #' . $log['entity_id'],
            $log['details'],
            $log['ip_address']
        ]);
    }
    fclose($fp);
    exit;
}

// Get unique values for filters
$all_admins = db()->query('SELECT DISTINCT al.admin_id, u.name FROM audit_logs al JOIN users u ON al.admin_id=u.id ORDER BY u.name')->fetchAll();
$all_actions = db()->query('SELECT DISTINCT action FROM audit_logs ORDER BY action')->fetchAll();
$all_entities = db()->query('SELECT DISTINCT entity_type FROM audit_logs ORDER BY entity_type')->fetchAll();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Audit Logs - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo base_url('assets/css/admin.css'); ?>">
</head>
<body>
  <div class="admin-layout">
    <?php include __DIR__ . '/_sidebar.php'; ?>
    <main class="admin-content">
      <?php include __DIR__ . '/_navbar.php'; ?>

<div class="container-fluid py-3">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2">📋 Audit Logs - Governance Trail</h1>
    <a href="?export=csv" class="btn btn-outline-secondary">📥 Export CSV</a>
  </div>

  <!-- Filters -->
  <div class="card mb-4">
    <div class="card-body">
      <form method="GET" class="row g-3">
        <div class="col-md-3">
          <label class="form-label">Filter by Admin</label>
          <select name="admin" class="form-select">
            <option value="">All Admins</option>
            <?php foreach ($all_admins as $adm): ?>
              <option value="<?php echo $adm['admin_id']; ?>" <?php echo $filter_admin == $adm['admin_id'] ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($adm['name']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Filter by Action</label>
          <select name="action" class="form-select">
            <option value="">All Actions</option>
            <?php foreach ($all_actions as $act): ?>
              <option value="<?php echo htmlspecialchars($act['action']); ?>" <?php echo $filter_action == $act['action'] ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($act['action']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Filter by Entity</label>
          <select name="entity" class="form-select">
            <option value="">All Entities</option>
            <?php foreach ($all_entities as $ent): ?>
              <option value="<?php echo htmlspecialchars($ent['entity_type']); ?>" <?php echo $filter_entity == $ent['entity_type'] ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($ent['entity_type']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">&nbsp;</label>
          <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Audit Logs Table -->
  <div class="table-responsive">
    <table class="table table-striped table-hover align-middle">
      <thead style="background: #f3f6fb;">
        <tr>
          <th>Date & Time</th>
          <th>Admin</th>
          <th>Action</th>
          <th>Entity</th>
          <th>Details</th>
          <th>IP Address</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($logs as $log): ?>
          <tr>
            <td style="font-size: 0.9rem;">
              <strong><?php echo date('M d', strtotime($log['created_at'])); ?></strong><br>
              <small class="text-muted"><?php echo date('H:i:s', strtotime($log['created_at'])); ?></small>
            </td>
            <td><?php echo htmlspecialchars($log['admin_name']); ?></td>
            <td>
              <span class="badge bg-info"><?php echo htmlspecialchars($log['action']); ?></span>
            </td>
            <td>
              <small><?php echo htmlspecialchars($log['entity_type']); ?></small><br>
              <strong>#<?php echo $log['entity_id']; ?></strong>
            </td>
            <td style="font-size: 0.9rem;">
              <?php echo htmlspecialchars($log['details']); ?>
              <?php if ($log['old_values'] || $log['new_values']): ?>
                <button type="button" class="btn btn-xs btn-link" data-bs-toggle="modal" data-bs-target="#detailsModal"
                        onclick="showDetails('<?php echo htmlspecialchars(json_encode(['old' => json_decode($log['old_values'], true), 'new' => json_decode($log['new_values'], true)])); ?>')">
                  [View Changes]
                </button>
              <?php endif; ?>
            </td>
            <td>
              <code style="font-size: 0.8rem;"><?php echo htmlspecialchars($log['ip_address']); ?></code>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php if (count($logs) === 0): ?>
    <div class="alert alert-info">No audit logs found matching the filters.</div>
  <?php endif; ?>

</div>
    </main>
  </div>

<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Change Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="detailsContent"></div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function showDetails(jsonStr) {
  const data = JSON.parse(jsonStr);
  let html = '';
  
  if (data.old) {
    html += '<h6>Previous Values:</h6><pre>' + JSON.stringify(data.old, null, 2) + '</pre>';
  }
  if (data.new) {
    html += '<h6>New Values:</h6><pre>' + JSON.stringify(data.new, null, 2) + '</pre>';
  }
  
  document.getElementById('detailsContent').innerHTML = html;
}
</script>
</body>
</html>
