<?php
require_once dirname(__DIR__) . '/bootstrap.php';
require_once ROOT_PATH . '/app/auth.php';
require_role('admin');

$pdo = db();
$u = current_user();
$ok = null; $err = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['name'], $_POST['email'])) {
        $stmt = $pdo->prepare('UPDATE users SET name=?, email=? WHERE id=?');
        $ok = $stmt->execute([trim($_POST['name']), trim($_POST['email']), (int)$u['id']]);
    }
    if (!empty($_POST['password'])) {
        $hash = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $stmt = $pdo->prepare('UPDATE users SET password_hash=? WHERE id=?');
        $ok = $stmt->execute([$hash, (int)$u['id']]) && $ok;
    }
}

$u = $pdo->prepare('SELECT id,name,email FROM users WHERE id=?');
$u->execute([current_user()['id']]);
$user = $u->fetch();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Profile</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo base_url('assets/css/admin.css'); ?>">
</head>
<body>
  <div class="admin-layout">
    <?php include __DIR__ . '/_sidebar.php'; ?>
    <main class="admin-content">
      <?php include __DIR__ . '/_navbar.php'; ?>
      <div class="container-fluid py-3">
        <h1 class="h3 mb-3">Profile Settings</h1>
  <?php if ($ok): ?><div class="alert alert-success">Profile updated</div><?php endif; ?>
  <?php if ($err): ?><div class="alert alert-danger">Error updating profile</div><?php endif; ?>

  <div class="row g-3">
    <div class="col-md-6">
      <div class="card p-3">
        <h2 class="h5">Details</h2>
        <form method="post">
          <div class="mb-2"><label class="form-label">Name</label><input name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required></div>
          <div class="mb-2"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required></div>
          <button class="btn btn-primary">Save</button>
        </form>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card p-3">
        <h2 class="h5">Change Password</h2>
        <form method="post">
          <div class="mb-2"><label class="form-label">New Password</label><input type="password" name="password" class="form-control"></div>
          <button class="btn btn-warning">Update Password</button>
        </form>
      </div>
    </div>
  </div>

  <div class="card p-3 mt-3">
    <h2 class="h5">Security</h2>
    <p>2FA toggle (developer mode) to be implemented.</p>
  </div>
    </main>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
