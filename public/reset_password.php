<?php
require_once __DIR__ . '/../app/auth.php';
require_guest();
$err = null; $ok = null;
$token = $_GET['token'] ?? ($_POST['token'] ?? '');
if (!$token) {
  $err = 'Missing token.';
} else {
  // validate token exists and not expired
  $row = get_password_reset_by_token($token);
  if (!$row) {
    $err = 'Invalid or expired token.';
  }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$err) {
  $pw = $_POST['password'] ?? '';
  $pw2 = $_POST['password_confirm'] ?? '';
  if ($pw !== $pw2) {
    $err = 'Passwords do not match.';
  } else {
    // server-side strength validation
    [$okValid, $msg] = validate_password_strength($pw);
    if (!$okValid) { $err = $msg; }
    else {
    try {
      $user_id = (int)$row['user_id'];
      if (set_user_password($user_id, $pw)) {
        mark_password_reset_used($token);
        // redirect to login with flash
        setcookie('mitihub_flash', urlencode('Your password has been reset. Please sign in.'), 0, '/mitihub/');
        redirect(base_url('public/login.php'));
      } else {
        $err = 'Failed to update password. Please try again later.';
      }
    } catch (Throwable $e) {
      error_log('[mitihub] reset_password error: ' . $e->getMessage());
      $err = 'Password reset failed.';
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
  <title>Reset Password - Mitihub</title>
  <link rel="stylesheet" href="<?php echo base_url('assets/css/style.css'); ?>">
  <style>
    .strength-bar { height: 6px; border-radius: 3px; margin-top: .5rem; background: #e0e0e0; overflow: hidden; }
    .strength-bar-fill { height: 100%; width: 0%; transition: width 0.2s, background-color 0.2s; }
    .strength-text { font-size: .9rem; margin-top: .3rem; font-weight: 500; }
    .strength-tips { font-size: .85rem; color: #666; margin-top: .5rem; }
  </style>
</head>
<body>
  <div class="auth-wrapper">
    <div class="auth-card">
      <div class="auth-header">
        <div class="auth-logo">M</div>
        <h1 class="auth-title">Reset Password</h1>
        <p class="auth-subtitle">Enter a new password for your account</p>
      </div>

      <?php if ($err): ?><div class="alert alert-error small"><?php echo htmlspecialchars($err); ?></div><?php endif; ?>
      <?php if ($ok): ?><div class="alert alert-success small"><?php echo htmlspecialchars($ok); ?></div><?php endif; ?>

      <?php if (!$err || $_SERVER['REQUEST_METHOD'] === 'POST'): ?>
      <form method="post" class="auth-form" novalidate id="resetForm">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
        <div class="form-row">
          <label for="password">New password</label>
          <input id="password" type="password" name="password" required>
          <div class="strength-bar"><div class="strength-bar-fill" id="pwBar"></div></div>
          <div class="strength-text" id="pwText"></div>
          <div class="strength-tips" id="pwTips"></div>
        </div>
        <div class="form-row">
          <label for="password_confirm">Confirm new password</label>
          <input id="password_confirm" type="password" name="password_confirm" required>
        </div>
        <button type="submit" class="btn-primary" id="resetSubmit">Set new password</button>
      </form>
      <?php endif; ?>

      <div class="auth-footer">
        <a href="<?php echo htmlspecialchars(full_base_url('public/login.php')); ?>">Back to login</a>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/zxcvbn@4.4.2/dist/zxcvbn.js"></script>
  <script>
    // zxcvbn-powered password strength feedback
    (function(){
      const pw = document.getElementById('password');
      const bar = document.getElementById('pwBar');
      const text = document.getElementById('pwText');
      const tips = document.getElementById('pwTips');
      
      if (!pw || !bar || !text) return;
      
      function render(){
        const val = pw.value || '';
        if (!val) {
          bar.style.width = '0%';
          text.textContent = '';
          tips.textContent = '';
          return;
        }
        
        // Check minimum length requirement
        if (val.length < 8) {
          bar.style.backgroundColor = '#d32f2f';
          bar.style.width = Math.min(val.length / 8 * 20, 15) + '%';
          text.textContent = 'Too short (min 8 chars)';
          text.style.color = '#d32f2f';
          tips.textContent = '';
          return;
        }
        
        const result = zxcvbn(val);
        const score = result.score; // 0-4
        
        // Color and width based on score
        const colors = ['#d32f2f', '#f57c00', '#fbc02d', '#7cb342', '#388e3c'];
        const widths = ['20%', '40%', '60%', '80%', '100%'];
        const labels = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
        
        bar.style.backgroundColor = colors[score];
        bar.style.width = widths[score];
        text.textContent = labels[score];
        text.style.color = colors[score];
        
        // Display feedback
        let feedback = '';
        if (result.feedback && result.feedback.suggestions && result.feedback.suggestions.length) {
          feedback = 'Tip: ' + result.feedback.suggestions[0];
        }
        tips.textContent = feedback;
      }
      
      pw.addEventListener('input', render);
      render();
    })();
  </script>
</body>
</html>
