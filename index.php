<?php
require_once __DIR__ . '/bootstrap.php';
require_once ROOT_PATH . '/app/auth.php';
// Serve cached anonymous GET if available (short TTL)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !is_logged_in()) {
  if ($cached = file_cache_get('page_index', 20)) {
    echo $cached;
    // flush buffers
    if (ob_get_level()) ob_end_flush();
    exit;
  }
}

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
  <title>Login</title>
  <link rel="icon" type="image/svg+xml" href="<?php echo base_url('assets/image/favicon.svg'); ?>">
  <link rel="stylesheet" href="<?php echo base_url('assets/css/style.css'); ?>">
</head>
<body>
  <div class="auth-wrapper" style="background:linear-gradient(135deg,#f3f6ff 0%, #e8efff 100%);">
    <div class="auth-card">
      <div class="auth-header">
        <img src="<?php echo base_url('assets/image/logo_mitihub.png'); ?>" alt="Mitihub logo" class="auth-logo-img" />
        <h1 class="auth-title">Sign in to your account</h1>
        <p class="auth-subtitle">Welcome back — enter your details to continue</p>
      </div>

      <?php if ($err): ?>
        <div class="alert alert-error small"><?php echo htmlspecialchars($err); ?></div>
      <?php endif; ?>
      <?php if ($notice): ?>
        <div class="alert alert-success small"><?php echo htmlspecialchars($notice); ?></div>
      <?php endif; ?>

      <form method="post" class="auth-form" novalidate>
        <div class="form-row">
          <label for="email">Email</label>
          <input id="email" type="email" name="email" placeholder="you@example.com" required>
        </div>
        <div class="form-row">
          <label for="password">Password</label>
          <input id="password" type="password" name="password" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn-primary">Sign in</button>
      </form>

      <div style="display:flex;justify-content:space-between;align-items:center;margin-top:1rem;">
        <div><a href="<?php echo base_url('register.php'); ?>">Create account</a></div>
        <div><a href="<?php echo base_url('forgot_password.php'); ?>">Forgot password?</a></div>
      </div>

      <p class="text-center mt-2 muted">Mitihub 1.0 © 2025 All Rights Reserved</p>
    </div>
  </div>

</body>
</html>

<?php
// After rendering, store a cached copy for anonymous GET requests
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !is_logged_in()) {
  $out = ob_get_contents();
  if ($out !== false) {
    file_cache_set('page_index', $out);
  }
  // allow output to flush normally
  if (ob_get_level()) ob_end_flush();
}

