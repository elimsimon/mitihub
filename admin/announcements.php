<?php
require_once dirname(__DIR__) . '/bootstrap.php';
require_once ROOT_PATH . '/app/auth.php';
require_role('admin');

$pdo = db();
$roles = ['school','guardian','sponsor','admin'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $body = trim($_POST['body'] ?? '');
    $role = in_array($_POST['role'] ?? '', $roles, true) ? $_POST['role'] : null;
    $schedule_at = trim($_POST['schedule_at'] ?? '') ?: null;
    if ($title && $body) {
        try {
            $stmt = $pdo->prepare('INSERT INTO announcements (title, body, role, schedule_at, status) VALUES (?,?,?,?,?)');
            $stmt->execute([$title, $body, $role, $schedule_at, 'pending']);
        } catch (Throwable $e) {
            // swallow for now
        }
    }
}

$ann = [];
try {
    $ann = $pdo->query('SELECT id,title,role,status,created_at,schedule_at FROM announcements ORDER BY created_at DESC LIMIT 100')->fetchAll();
} catch (Throwable $e) {
    $ann = [];
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Announcements</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo base_url('assets/css/admin.css'); ?>">
</head>
<body>
  <div class="admin-layout">
    <?php include __DIR__ . '/_sidebar.php'; ?>
    <main class="admin-content">
      <?php include __DIR__ . '/_navbar.php'; ?>
      <div class="container-fluid py-3">
        <h1 class="h3 mb-3">Announcements</h1>

        <div class="card p-3 mb-4">
          <h2 class="h5">Create Announcement</h2>
          <form method="post">
            <div class="mb-2"><label class="form-label">Title</label><input name="title" class="form-control" required></div>
            <div class="mb-2"><label class="form-label">Body</label><textarea name="body" class="form-control" rows="4" required></textarea></div>
            <div class="row g-2">
              <div class="col-md-4">
                <label class="form-label">Target Role</label>
                <select name="role" class="form-select">
                  <option value="">All</option>
                  <?php foreach ($roles as $r): ?><option value="<?php echo $r; ?>"><?php echo ucfirst($r); ?></option><?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label">Schedule At</label>
                <input type="datetime-local" name="schedule_at" class="form-control">
              </div>
              <div class="col-md-4 d-grid">
                <label class="form-label">&nbsp;</label>
                <button class="btn btn-primary">Create</button>
              </div>
            </div>
          </form>
        </div>

        <div class="card p-3">
          <h2 class="h5">Recent Announcements</h2>
          <div class="table-responsive">
            <table class="table align-middle">
              <thead><tr><th>ID</th><th>Title</th><th>Role</th><th>Status</th><th>Created</th><th>Schedule</th></tr></thead>
              <tbody>
                <?php foreach ($ann as $a): ?>
                <tr>
                  <td><?php echo (int)$a['id']; ?></td>
                  <td><?php echo htmlspecialchars($a['title']); ?></td>
                  <td><?php echo htmlspecialchars($a['role'] ?: 'All'); ?></td>
                  <td><?php echo htmlspecialchars($a['status']); ?></td>
                  <td><?php echo htmlspecialchars($a['created_at']); ?></td>
                  <td><?php echo htmlspecialchars($a['schedule_at']); ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <div class="alert alert-info">Approval/flagging and sponsor co-branded requests to be implemented.</div>
        </div>
      </div>
    </main>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
