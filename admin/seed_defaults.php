<?php
// One-time default users seeder (password for all: "password")
// Security: Require current admin login to run. Delete after use.
require_once dirname(__DIR__) . '/bootstrap.php';
require_once ROOT_PATH . '/app/auth.php';
require_role('admin');

$pdo = db();

$defaults = [
  ['Admin User', 'admin@mitihub.local',   '$2y$10$zRz.6oKblWJrq6ygiBrS0O0iQ7Qk8y2q2c2rO1kG.IK1G7uY6aYQe', 'admin'],
  ['Default Sponsor', 'sponsor@mitihub.local', '$2y$10$zRz.6oKblWJrq6ygiBrS0O0iQ7Qk8y2q2c2rO1kG.IK1G7uY6aYQe', 'sponsor'],
  ['Default School',  'school@mitihub.local',  '$2y$10$zRz.6oKblWJrq6ygiBrS0O0iQ7Qk8y2q2c2rO1kG.IK1G7uY6aYQe', 'school'],
];

$inserted = 0; $skipped = 0; $errors = [];
$pdo->beginTransaction();
try {
    foreach ($defaults as [$name,$email,$hash,$role]) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email=? LIMIT 1');
        $stmt->execute([$email]);
        if ($stmt->fetchColumn()) { $skipped++; continue; }
        $ins = $pdo->prepare('INSERT INTO users (name, email, password_hash, role, status, setup_complete, created_at) VALUES (?,?,?,?,?,?,NOW())');
        $ok = $ins->execute([$name, $email, $hash, $role, 'active', 1]);
        if ($ok) { $inserted++; } else { $errors[] = 'Failed to insert ' . $email; }
    }
    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    $errors[] = $e->getMessage();
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Seed Default Users</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include __DIR__ . '/_nav.php'; ?>
<div class="container py-4">
  <h1 class="h3 mb-3">Seed Default Users</h1>
  <div class="alert alert-info">Inserts default users if they don't exist. Safe to re-run; existing users are skipped. Delete this file after use.</div>
  <ul class="list-group mb-3">
    <li class="list-group-item">Inserted: <?php echo (int)$inserted; ?></li>
    <li class="list-group-item">Skipped (already exists): <?php echo (int)$skipped; ?></li>
  </ul>
  <?php if ($errors): ?>
    <div class="alert alert-danger"><strong>Errors:</strong><br><?php echo htmlspecialchars(implode("\n", $errors)); ?></div>
  <?php else: ?>
    <div class="alert alert-success">Seeding completed successfully.</div>
  <?php endif; ?>
  <p><a class="btn btn-primary" href="<?php echo base_url('admin/dashboard.php'); ?>">Back to Dashboard</a></p>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
