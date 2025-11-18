<?php
require_once dirname(__DIR__) . '/bootstrap.php';
require_once ROOT_PATH . '/app/auth.php';
require_guest();
$err = null;
$flash = null;
if (!empty($_COOKIE['mitihub_flash'])) {
  $flash = urldecode($_COOKIE['mitihub_flash']);
  // clear cookie
  setcookie('mitihub_flash', '', time() - 3600, '/mitihub/');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!csrf_check($_POST['csrf'] ?? '')) { $err = 'Invalid CSRF token'; }
  else {
    // Accept either email or username (identifier)
    $identifier = trim((string)($_POST['identifier'] ?? ''));
    $password = $_POST['password'] ?? '';
    if (login($identifier, $password)) {
      // Redirect based on user role
      $user = auth_user();
      $role = $user['role'] ?? 'school';
      if ($role === 'admin') {
        redirect('/mitihub/admin/dashboard.php');
      } elseif ($role === 'sponsor') {
        redirect('/mitihub/sponsor/dashboard.php');
      } else {
        // school or default
        redirect('/mitihub/public/dashboard.php');
      }
    } else {
      $err = 'Invalid credentials';
    }
  }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Mitihub Login</title>
<link rel="stylesheet" href="<?php echo base_url('assets/css/style.css'); ?>">
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-card">
    <div class="auth-header">
      <div class="auth-logo" aria-hidden="true">M</div>
      <h1 class="auth-title">Sign in</h1>
      <p class="auth-subtitle">Welcome back to Mitihub</p>
    </div>

    <?php if ($flash): ?>
      <div class="alert alert-success small"><?php echo htmlspecialchars($flash); ?></div>
    <?php endif; ?>

    <?php if ($err): ?>
      <div class="alert alert-error small"><?php echo htmlspecialchars($err); ?></div>
    <?php endif; ?>

    <form method="post" class="auth-form" novalidate>
      <input type="hidden" name="csrf" value="<?php echo htmlspecialchars(csrf_token()); ?>">
      <div class="form-row">
        <label for="identifier">Email or username</label>
        <input id="identifier" type="text" name="identifier" required placeholder="email or username">
      </div>
      <div class="form-row">
        <label for="password">Password</label>
        <input id="password" type="password" name="password" required>
      </div>
      <button type="submit" class="btn-primary">Sign In</button>
    </form>

    <div class="auth-footer">
      <a href="/mitihub/forgot_password.php">Forgot your password? Reset here</a>
    </div>

    <p class="muted" style="text-align:center;margin-top:.5rem">Mitihub 1.0 © 2025 All Rights Reserved</p>
  </div>
</div>
</body>
</html>
