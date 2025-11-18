<?php
require_once dirname(__DIR__) . '/bootstrap.php';
require_once ROOT_PATH . '/app/auth.php';
require_role_mod('admin');
$err = null; $ok = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';
    if ($action === 'approve') {
        user_set_status($id, 'active');
        $ok = 'User approved';
    } elseif ($action === 'reject') {
        user_set_status($id, 'rejected');
        $ok = 'User rejected';
    }
}
$pending = users_pending_approval();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Approve Users</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo base_url('assets/css/admin.css'); ?>">
</head>
<body>
  <div class="admin-layout">
    <?php include __DIR__ . '/_sidebar.php'; ?>
    <main class="admin-content">
      <?php include __DIR__ . '/_navbar.php'; ?>
      <div class="container-fluid py-3">
        <h1 class="h3 mb-3">Pending Approvals</h1>
        <?php if ($err): ?><div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div><?php endif; ?>
        <?php if ($ok): ?><div class="alert alert-success"><?php echo htmlspecialchars($ok); ?></div><?php endif; ?>
        <?php if (!$pending): ?>
          <div class="card p-3">No users pending approval.</div>
        <?php else: ?>
          <div class="card p-3">
            <div class="table-responsive">
              <table class="table align-middle">
                <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($pending as $u): ?>
                  <tr>
                    <td><?php echo (int)$u['id']; ?></td>
                    <td><?php echo htmlspecialchars($u['name']); ?></td>
                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                    <td><?php echo htmlspecialchars($u['role']); ?></td>
                    <td>
                      <form method="post" class="d-inline">
                        <input type="hidden" name="id" value="<?php echo (int)$u['id']; ?>">
                        <button class="btn btn-sm btn-success me-1" name="action" value="approve">Approve</button>
                        <button class="btn btn-sm btn-danger" name="action" value="reject">Reject</button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </main>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
