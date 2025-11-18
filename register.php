<?php
require_once __DIR__ . '/bootstrap.php';
require_once ROOT_PATH . '/app/auth.php';
$ok = null; $err = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'] ?? '';
    if (!in_array($role, ['school','sponsor'], true)) {
        $err = 'Invalid role selected';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm'] ?? '';
        if ($password !== $confirm) {
            $err = 'Passwords do not match';
        } else {
            try {
                register_user($name, $email, $password, $role);
                $ok = 'Your account has been created successfully. Please wait for admin approval.';
            } catch (Throwable $e) {
                $err = 'Registration error: ' . $e->getMessage();
            }
        }
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Register - Mitihub</title>
  <link rel="icon" type="image/svg+xml" href="<?php echo base_url('assets/image/favicon.svg'); ?>">
  <link rel="stylesheet" href="<?php echo base_url('assets/css/style.css'); ?>">
</head>
<body>
  <div class="auth-wrapper">
    <div class="auth-card">
      <div class="auth-header">
        <img src="<?php echo base_url('assets/image/logo_mitihub.png'); ?>" alt="Mitihub logo" class="auth-logo-img" />
        <h1 class="auth-title">Create your account</h1>
        <p class="auth-subtitle">Join as a School or Sponsor</p>
      </div>

      <?php if ($err): ?><div class="alert alert-error small"><?php echo htmlspecialchars($err); ?></div><?php endif; ?>
      <?php if ($ok): ?><div class="alert alert-success small"><?php echo htmlspecialchars($ok); ?></div><?php endif; ?>

      <form method="post" class="auth-form" novalidate>
        <div class="form-row">
          <label for="role">Role</label>
          <select id="role" name="role" required>
            <option value="school">School</option>
            <option value="sponsor">Sponsor</option>
          </select>
        </div>

        <div class="form-row">
          <label for="name">Full name</label>
          <input id="name" name="name" required placeholder="Jane Doe">
        </div>

        <div class="form-row">
          <label for="email">Email address</label>
          <input id="email" type="email" name="email" required placeholder="jane@example.com">
        </div>

        <div class="form-row">
          <label for="password">Password</label>
          <input id="password" type="password" name="password" required>
        </div>

        <div class="form-row">
          <label for="confirm">Confirm password</label>
          <input id="confirm" type="password" name="confirm" required>
        </div>

        <button type="submit" class="btn-primary">Create Account</button>
      </form>

      <div class="auth-footer">
        <span>Already have an account?</span>
        <a href="<?php echo base_url('index.php'); ?>">Login here</a>
      </div>
    </div>
  </div>

  <script>
    // Minimal client-side invalid hinting by adding .is-invalid on blur
    document.addEventListener('DOMContentLoaded', function(){
      document.querySelectorAll('.auth-form input, .auth-form select').forEach(function(el){
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
