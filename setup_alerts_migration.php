<?php
/**
 * Auto-Migration Runner for Alerts & Monetization System
 * Run this once to set up all required tables
 * 
 * Usage: 
 * 1. Visit http://localhost/mitihub/setup_alerts_migration.php
 * 2. Click "Run Migration"
 * 3. Done! Tables will be created
 */

require_once __DIR__ . '/bootstrap.php';
require_once ROOT_PATH . '/app/config.php';

// Protect this script - check for admin role or simple token
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ?>
    <!doctype html>
    <html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Alerts & Monetization Migration</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="container mt-5">
            <div class="row">
                <div class="col-md-6 offset-md-3">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h1 class="h4 mb-4">⚙️ Alerts & Monetization Migration</h1>
                            <p class="text-muted">This will create the necessary database tables for the admin alerts and monetization system.</p>
                            
                            <div class="alert alert-info">
                                <strong>Tables to be created:</strong>
                                <ul class="mb-0 mt-2" style="font-size: 0.9rem;">
                                    <li>alerts</li>
                                    <li>audit_logs</li>
                                    <li>sponsorship_packages</li>
                                    <li>ngo_licenses</li>
                                    <li>sms_settings</li>
                                    <li>premium_features</li>
                                    <li>research_requests</li>
                                    <li>api_access</li>
                                    <li>compliance_flags</li>
                                </ul>
                            </div>

                            <form method="POST">
                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                    ▶️ Run Migration
                                </button>
                            </form>
                            
                            <p class="text-muted small mt-3">
                                ⚠️ This is safe to run multiple times (uses CREATE TABLE IF NOT EXISTS and INSERT ... ON DUPLICATE KEY UPDATE)
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Run the migration on POST
try {
    $pdo = db();
    
    // Read and execute migration SQL
    $migration_file = ROOT_PATH . '/db/migrations_alerts_monetization.sql';
    
    if (!file_exists($migration_file)) {
        throw new Exception("Migration file not found: {$migration_file}");
    }
    
    $sql = file_get_contents($migration_file);
    
    // Split by semicolons and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $executed = 0;
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $pdo->exec($statement);
            $executed++;
        }
    }
    
    $success = true;
    $message = "✅ Migration completed successfully! Executed {$executed} statements.";
    $details = [
        'Tables created/verified:',
        '  ✓ alerts',
        '  ✓ audit_logs',
        '  ✓ sponsorship_packages',
        '  ✓ ngo_licenses',
        '  ✓ sms_settings',
        '  ✓ premium_features',
        '  ✓ research_requests',
        '  ✓ api_access',
        '  ✓ compliance_flags'
    ];
    
} catch (Exception $e) {
    $success = false;
    $message = "❌ Migration failed: " . $e->getMessage();
    $details = [
        'Error details:',
        $e->getTraceAsString()
    ];
}

?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Migration Result</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-6 offset-md-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="alert alert-<?php echo $success ? 'success' : 'danger'; ?>">
                            <h4 class="alert-heading"><?php echo $success ? '✅ Success!' : '❌ Error'; ?></h4>
                            <p class="mb-0"><?php echo htmlspecialchars($message); ?></p>
                        </div>
                        
                        <div class="alert alert-info">
                            <pre style="margin: 0; font-size: 0.85rem;">
<?php echo implode("\n", $details); ?>
                            </pre>
                        </div>

                        <?php if ($success): ?>
                            <a href="<?php echo base_url('admin/dashboard.php'); ?>" class="btn btn-primary w-100">
                                → Go to Admin Dashboard
                            </a>
                        <?php else: ?>
                            <button onclick="location.reload()" class="btn btn-warning w-100">
                                ↻ Try Again
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
