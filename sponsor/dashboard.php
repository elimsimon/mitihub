<?php
require_once dirname(__DIR__) . '/bootstrap.php';
require_once ROOT_PATH . '/app/auth.php';
require_once ROOT_PATH . '/sponsor/functions.php';
require_role_mod('sponsor');
$user = current_user();
$list = sponsor_list_sponsorships($user['id']);
$schools = sponsor_list_schools();
$err = null; $ok = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check_global($_POST['csrf'] ?? '')) { $err = 'Invalid CSRF token'; }
    else {
        $school_id = (int)($_POST['school_id'] ?? 0);
        $amount = (float)($_POST['amount'] ?? 0);
        sponsor_create_sponsorship($user['id'], $school_id, $amount);
        $ok = 'Sponsorship created';
        $list = sponsor_list_sponsorships($user['id']);
    }
}
?>

<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sponsor Dashboard</title>
  <link rel="stylesheet" href="<?php echo base_url('assets/css/style.css'); ?>">
</head>
<body>
  <?php include __DIR__ . '/_sidebar.php'; ?>
  <div class="main-content">
    <h1>Sponsor Dashboard</h1>
    <?php if ($err): ?><div class="alert alert-error"><?php echo htmlspecialchars($err); ?></div><?php endif; ?>
    <?php if ($ok): ?><div class="alert alert-success"><?php echo htmlspecialchars($ok); ?></div><?php endif; ?>

    <form method="post">
      <input type="hidden" name="csrf" value="<?php echo htmlspecialchars(csrf_token_global()); ?>">
      <label>School
        <select name="school_id">
          <?php foreach ($schools as $s): ?>
            <option value="<?php echo (int)$s['id']; ?>"><?php echo htmlspecialchars($s['school_name']); ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>Amount <input type="number" step="0.01" name="amount" required></label>
      <button>Create Sponsorship</button>
    </form>

    <h2>My Sponsorships</h2>
    <ul>
      <?php foreach ($list as $row): ?>
        <li>#<?php echo (int)$row['id']; ?> - <?php echo htmlspecialchars($row['school_name']); ?> - <?php echo number_format($row['amount'], 2); ?> - <?php echo htmlspecialchars($row['status']); ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
</body>
</html>
