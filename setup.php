<?php
// Updated to use the consolidated `app/` directory (project uses `app/` instead of `includes/`)
require_once __DIR__ . '/app/auth.php';
require_once __DIR__ . '/app/functions.php';
require_login();
$u = current_user();
if (!in_array($u['role'], ['school','sponsor'], true)) {
    header('Location: ' . base_url('index.php'));
    exit;
}
$err = null; $ok = null;
$uploadDir = __DIR__ . '/uploads';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
$logoPath = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check_global($_POST['csrf'] ?? '')) {
        $err = 'Invalid CSRF token';
    } else {
        if (!empty($_FILES['logo']['name'])) {
            $tmp = $_FILES['logo']['tmp_name'];
            $name = time() . '_' . preg_replace('/[^a-zA-Z0-9_.-]/','_', $_FILES['logo']['name']);
            $dest = $uploadDir . '/' . $name;
            if (move_uploaded_file($tmp, $dest)) {
                $logoPath = 'uploads/' . $name;
            }
        }
        if ($u['role'] === 'school') {
            $school_name = trim($_POST['school_name'] ?? '');
            $address = trim($_POST['address'] ?? '');
            save_school_profile($u['id'], $school_name, $address, $logoPath);
        } else {
            $org = trim($_POST['organization'] ?? '');
            $contact = trim($_POST['contact'] ?? '');
            save_sponsor_profile($u['id'], $org, $contact, $logoPath);
        }
        user_mark_setup_complete($u['id']);
        $ok = 'Your profile setup was successful!';
        // Redirect to dashboard
        if ($u['role'] === 'school') $target = base_url('school/dashboard.php');
        else $target = base_url('sponsor/dashboard.php');
        header('Refresh: 1; URL=' . $target);
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Profile Setup</title>
  <link rel="stylesheet" href="<?php echo base_url('assets/css/style.css'); ?>">
</head>
<body>
  <h1>Complete Your Profile</h1>
  <?php if ($err): ?><div class="alert alert-error"><?php echo htmlspecialchars($err); ?></div><?php endif; ?>
  <?php if ($ok): ?><div class="alert alert-success"><?php echo htmlspecialchars($ok); ?></div><?php endif; ?>

  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?php echo htmlspecialchars(csrf_token_global()); ?>">
    <?php if ($u['role'] === 'school'): ?>
      <label>Institution Name <input name="school_name" required></label>
      <label>Address <input name="address" required></label>
    <?php else: ?>
      <label>Organization Name <input name="organization" required></label>
      <label>Contact Info <input name="contact" required></label>
    <?php endif; ?>
    <label>Logo <input type="file" name="logo" accept="image/*"></label>
    <button>Save</button>
  </form>
</body>
</html>
