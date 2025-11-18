<?php
require_once dirname(__DIR__) . '/app/auth.php';
require_once dirname(__DIR__) . '/app/Helpers/helpers.php';
$err = null; $notice = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $result = login_user($email, $password);
    if ($result === true) {
        $u = current_user();
        if (($u['setup_complete'] ?? 1) == 0 && in_array($u['role'], ['school','sponsor'], true)) {
            header('Location: ' . base_url('setup.php'));
            exit;
        }
        if ($u['role'] === 'admin') header('Location: ' . base_url('admin/dashboard.php'));
        elseif ($u['role'] === 'sponsor') header('Location: ' . base_url('sponsor/dashboard.php'));
        else header('Location: ' . base_url('school/dashboard.php'));
        exit;
    } elseif ($result === 'pending') {
        $notice = 'Your account is pending approval by an admin.';
    } else {
        $err = 'Invalid credentials';
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Mitihub Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { min-height:100vh; display:flex; align-items:center; justify-content:center; background:linear-gradient(135deg,#f3f6ff 0%, #e8efff 100%); }
    .card { border:none; border-radius:16px; box-shadow:0 10px 25px rgba(0,0,0,.06); }
    .brand { font-weight:800; color:#2f6fed; }
    .btn-primary { background:#2f6fed; border-color:#2f6fed; }
    .btn-primary:hover { background:#275fcb; border-color:#275fcb; }
    .muted { color:#6c757d; }
  </style>
</head>
<body>
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-12 col-md-8 col-lg-5">
        <div class="text-center mb-4">
          <img src="https://via.placeholder.com/72x72.png?text=M" class="mb-2" alt="Logo" width="72" height="72">
          <h1 class="h3 brand">Mitihub</h1>
          <p class="muted">Sign in to your account</p>
        </div>
        <?php if ($err): ?>
          <div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div>
        <?php endif; ?>
        <?php if ($notice): ?>
          <div class="alert alert-info"><?php echo htmlspecialchars($notice); ?></div>
        <?php endif; ?>
        <div class="card p-4">
          <form method="post" novalidate>
            <div class="mb-3">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control form-control-lg" placeholder="you@example.com" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Password</label>
              <input type="password" name="password" class="form-control form-control-lg" placeholder="••••••••" required>
            </div>
            <div class="d-grid">
              <button class="btn btn-primary btn-lg" type="submit">Sign in</button>
            </div>
          </form>
        </div>
        <div class="text-center mt-3">
          <a href="<?php echo base_url('register.php'); ?>">Create a School or Sponsor account</a>
        </div>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
