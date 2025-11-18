<?php
// One-time admin seeder: sets admin email/password as requested.
// Security: Require current login and admin role to run; then you may delete this file.
require_once dirname(__DIR__) . '/bootstrap.php';
require_once ROOT_PATH . '/app/auth.php';
require_role('admin');

$pdo = db();

// Desired credentials
$targetEmail = 'admin@mitihub.com';
$targetName  = 'Administrator';
$targetRole  = 'admin';
$targetStatus = 'active';
$targetPassword = 'admin@123';
$targetHash = password_hash($targetPassword, PASSWORD_BCRYPT);

$pdo->beginTransaction();
try {
    // Try to update an existing admin with target email
    $stmt = $pdo->prepare("UPDATE users SET name=?, role=?, status=?, password_hash=? WHERE email=?");
    $stmt->execute([$targetName, $targetRole, $targetStatus, $targetHash, $targetEmail]);
    $updated = $stmt->rowCount();

    if ($updated === 0) {
        // Try to capture old default admin email
        $stmt = $pdo->prepare("UPDATE users SET email=?, name=?, role=?, status=?, password_hash=? WHERE email=?");
        $stmt->execute([$targetEmail, $targetName, $targetRole, $targetStatus, $targetHash, 'admin@mitihub.local']);
        $updated = $stmt->rowCount();
    }

    if ($updated === 0) {
        // Insert if not exists
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email=? LIMIT 1');
        $stmt->execute([$targetEmail]);
        $exists = $stmt->fetchColumn();
        if (!$exists) {
            $ins = $pdo->prepare('INSERT INTO users (name, email, password_hash, role, status, setup_complete, created_at) VALUES (?,?,?,?,?,?,NOW())');
            $ins->execute([$targetName, $targetEmail, $targetHash, $targetRole, $targetStatus, 1]);
        }
    }

    $pdo->commit();
    $msg = 'Admin credentials set. You can now log in with ' . htmlspecialchars($targetEmail) . ' / ' . htmlspecialchars($targetPassword) . '. It is recommended to delete this file (public/admin/seed_admin.php).';
} catch (Throwable $e) {
    $pdo->rollBack();
    http_response_code(500);
    $msg = 'Error: ' . htmlspecialchars($e->getMessage());
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Seed Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include __DIR__ . '/_nav.php'; ?>
<div class="container py-4">
  <h1 class="h3 mb-3">Seed Admin</h1>
  <div class="alert alert-info"><?php echo $msg; ?></div>
  <p><a class="btn btn-primary" href="<?php echo base_url('admin/dashboard.php'); ?>">Back to Dashboard</a></p>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
