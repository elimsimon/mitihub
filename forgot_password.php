<?php
require_once __DIR__ . '/bootstrap.php';
require_once ROOT_PATH . '/app/auth.php';
require_guest();

$ok = null; $err = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $err = 'Please enter a valid email address.';
    } elseif (!check_reset_rate_limit($email)) {
        $err = 'Too many reset attempts. Please try again in an hour.';
    } else {
      try {
        record_reset_attempt($email);
        $user = user_get_by_email($email);
        if (!$user) {
          $err = 'No account found with that email address.';
        } else {
          // Create a one-time token and show a reset link (site should email this in production)
          $token = create_password_reset_token($user['id']);
          $resetUrl = full_base_url('public/reset_password.php?token=' . urlencode($token));
          $ok = 'A password reset link has been generated (for testing it is shown below). In production this would be emailed to you.';
          $ok .= '<div style="margin-top:.5rem;"><a href="' . htmlspecialchars($resetUrl) . '">Reset password link</a></div>';
        }
      } catch (Throwable $e) {
        error_log('[mitihub] forgot_password error: ' . $e->getMessage());
        $err = 'Password reset error. Please try again later.';
      }
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Forgot Password - Mitihub</title>
  <link rel="stylesheet" href="<?php echo base_url('assets/css/style.css'); ?>">
</head>
<body>
  <div class="auth-wrapper">
    <div class="auth-card">
      <div class="auth-header">
        <img src="<?php echo base_url('assets/image/logo_mitihub.png'); ?>" alt="Mitihub logo" class="auth-logo-img" />
        <h1 class="auth-title">Forgot Password</h1>
        <p class="auth-subtitle">Enter your registered email</p>
      </div>

      <?php if ($err): ?><div class="alert alert-error small"><?php echo htmlspecialchars($err); ?></div><?php endif; ?>
      <?php if ($ok): ?><div class="alert alert-success small"><?php echo $ok; ?></div><?php endif; ?>

      <form method="post" class="auth-form" novalidate>
        <div class="form-row">
          <label for="email">Email address</label>
          <input id="email" type="email" name="email" required placeholder="you@example.com">
        </div>
        <button type="submit" class="btn-primary">Reset Password</button>
      </form>

      <div class="auth-footer">
        <a href="<?php echo htmlspecialchars(full_base_url('index.php')); ?>">Back to login</a>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function(){
      document.querySelectorAll('.auth-form input').forEach(function(el){
        el.addEventListener('blur', function(){
          if (el.required && !el.value.trim()) {
            el.classList.add('is-invalid');
          } else {
            el.classList.remove('is-invalid');
          }
        });
      });
    });
  </script>
</body>
</html>
