<?php
require_once dirname(__DIR__) . '/bootstrap.php';
require_once ROOT_PATH . '/app/auth.php';
require_role('admin');

$pdo = db();
$roles = ['school','sponsor','admin'];
$filter_role = $_GET['role'] ?? '';
$role_sql = in_array($filter_role, $roles, true) ? ' WHERE role=' . $pdo->quote($filter_role) : '';
$users = $pdo->query('SELECT id,name,email,role,status,created_at FROM users' . $role_sql . ' ORDER BY created_at DESC')->fetchAll();

// Example actions (approve, role change)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve_id'])) {
        $stmt = $pdo->prepare("UPDATE users SET status='active' WHERE id=?");
        $stmt->execute([(int)$_POST['approve_id']]);
        header('Location: ' . base_url('admin/user-management.php'));
        exit;
    }
    if (isset($_POST['role_id'], $_POST['role'])) {
        $r = in_array($_POST['role'], $roles, true) ? $_POST['role'] : 'school';
        $stmt = $pdo->prepare('UPDATE users SET role=? WHERE id=?');
        $stmt->execute([$r, (int)$_POST['role_id']]);
        header('Location: ' . base_url('admin/user-management.php'));
        exit;
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>User Management</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo base_url('assets/css/admin.css'); ?>">
</head>
<body>
  <div class="admin-layout">
    <?php include __DIR__ . '/_sidebar.php'; ?>
    <main class="admin-content">
      <?php include __DIR__ . '/_navbar.php'; ?>

      <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3">Users</h1>
        <form class="d-flex" method="get">
          <select name="role" class="form-select me-2" onchange="this.form.submit()">
            <option value="">All Roles</option>
            <?php foreach ($roles as $r): ?>
              <option value="<?php echo $r; ?>" <?php echo $filter_role===$r?'selected':''; ?>><?php echo ucfirst($r); ?></option>
            <?php endforeach; ?>
          </select>
          <a class="btn btn-outline-secondary" href="<?php echo base_url('admin/user-management.php'); ?>">Reset</a>
        </form>
      </div>
      <div class="table-responsive">
        <table class="table align-middle">
          <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead>
          <tbody>
          <?php foreach ($users as $u): ?>
            <tr>
              <td><?php echo (int)$u['id']; ?></td>
              <td><?php echo htmlspecialchars($u['name']); ?></td>
              <td><?php echo htmlspecialchars($u['email']); ?></td>
              <td><?php echo htmlspecialchars($u['role']); ?></td>
              <td><?php echo htmlspecialchars($u['status']); ?></td>
              <td><?php echo htmlspecialchars($u['created_at']); ?></td>
              <td>
                <?php if ($u['status'] !== 'active'): ?>
                <form method="post" class="d-inline">
                  <input type="hidden" name="approve_id" value="<?php echo (int)$u['id']; ?>">
                  <button class="btn btn-sm btn-success">Approve</button>
                </form>
                <?php endif; ?>
                <form method="post" class="d-inline">
                  <input type="hidden" name="role_id" value="<?php echo (int)$u['id']; ?>">
                  <select name="role" class="form-select form-select-sm d-inline w-auto">
                    <?php foreach ($roles as $r): ?>
                      <option value="<?php echo $r; ?>" <?php echo $u['role']===$r?'selected':''; ?>><?php echo ucfirst($r); ?></option>
                    <?php endforeach; ?>
                  </select>
                  <button class="btn btn-sm btn-primary">Update</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="alert alert-info">Workflows: Approvals for Schools (Primary/Junior/Secondary/College/University) and Sponsors (Corporates/NGOs), 2FA toggles, and audit logging to be implemented.</div>
    </main>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
