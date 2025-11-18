<?php
require_once dirname(__DIR__) . '/bootstrap.php';
require_once ROOT_PATH . '/app/auth.php';
require_role_mod('sponsor');
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sponsor Reports</title>
  <link rel="stylesheet" href="<?php echo base_url('assets/css/style.css'); ?>">
</head>
<body>
  <?php include __DIR__ . '/_sidebar.php'; ?>
  <div class="main-content">
    <h1>Reports</h1>
    <p>Reports page for sponsor (coming soon).</p>
  </div>
</body>
</html>
