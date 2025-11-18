<?php
require_once __DIR__ . '/../app/auth.php';
require_auth();
$user = auth_user();
$err = null; $ok = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $current = $_POST['current_password'] ?? '';
  $new = $_POST['new_password'] ?? '';
  $confirm = $_POST['confirm_password'] ?? '';
  if ($new !== $confirm) {
    $err = 'New passwords do not match.';
  } else {
    // server-side strength validation
    [$okValid, $msg] = validate_password_strength($new);
    if (!$okValid) { $err = $msg; }
    else {
    // verify current password
    $pdo = db();
    $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id=? LIMIT 1');
    $stmt->execute([$user['id']]);
    $row = $stmt->fetch();
    if (!$row || !password_verify($current, $row['password_hash'])) {
      $err = 'Current password is incorrect.';
    } else {
      if (set_user_password($user['id'], $new)) {
        $ok = 'Password updated successfully.';
      } else {
        $err = 'Failed to update password. Please try again later.';
      }
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
  <title>Change Password - Mitihub</title>
  <link rel="stylesheet" href="<?php echo base_url('assets/css/style.css'); ?>">
  <style>
    .strength-bar { height: 6px; border-radius: 3px; margin-top: .5rem; background: #e0e0e0; overflow: hidden; }
    .strength-bar-fill { height: 100%; width: 0%; transition: width 0.2s, background-color 0.2s; }
    .strength-text { font-size: .9rem; margin-top: .3rem; font-weight: 500; }
    .strength-tips { font-size: .85rem; color: #666; margin-top: .5rem; }
  </style>
</head>
<body>
  <?php include __DIR__ . '/_sidebar.php'; ?>
  <?php include __DIR__ . '/_navbar.php'; ?>
  <div class="main-content" style="padding-top:64px; max-width:720px;">
    <h1>Change Password</h1>
    <?php if ($err): ?><div class="alert alert-error small"><?php echo htmlspecialchars($err); ?></div><?php endif; ?>
    <?php if ($ok): ?><div class="alert alert-success small"><?php echo htmlspecialchars($ok); ?></div><?php endif; ?>

    <form method="post" class="auth-form" style="max-width:480px;">
      <div class="form-row">
        <label for="current_password">Current password</label>
        <input id="current_password" type="password" name="current_password" required>
      </div>
      <div class="form-row">
        <label for="new_password">New password</label>
        <input id="new_password" type="password" name="new_password" required>
        <div class="strength-bar"><div class="strength-bar-fill" id="pwBar"></div></div>
        <div class="strength-text" id="pwText"></div>
        <div class="strength-tips" id="pwTips"></div>
      </div>
      <div class="form-row">
        <label for="confirm_password">Confirm new password</label>
        <input id="confirm_password" type="password" name="confirm_password" required>
      </div>
      <div class="form-row">
        <div class="pw-strength" id="pwStrength" style="margin-bottom:.5rem"></div>
      </div>
      <button type="submit" class="btn-primary" id="changeSubmit">Update password</button>
    </form>
  <?php include __DIR__ . '/_footer.php'; ?>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/zxcvbn@4.4.2/dist/zxcvbn.js"></script>
  <script>
    // zxcvbn-powered password strength feedback for change password
    (function(){
      const pw = document.getElementById('new_password');
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
        
        const colors = ['#d32f2f', '#f57c00', '#fbc02d', '#7cb342', '#388e3c'];
        const widths = ['20%', '40%', '60%', '80%', '100%'];
        const labels = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
        
        bar.style.backgroundColor = colors[score];
        bar.style.width = widths[score];
        text.textContent = labels[score];
        text.style.color = colors[score];
        
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
