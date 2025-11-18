<?php
/**
 * Admin Alerts Widget Partial
 * Include this in admin/dashboard.php or top navigation
 * Shows pending alerts, approvals, flags, revenue alerts
 */

require_once __DIR__ . '/alerts_functions.php';

$alerts = get_pending_alerts(5);
$counts = get_alert_counts();
$pending_users = get_pending_user_approvals();
$flagged = get_flagged_issues(3);
$revenue = get_revenue_alerts(7);
?>

<div class="alerts-widget mb-4">
  <div class="alerts-header d-flex justify-content-between align-items-center mb-3">
    <h2 class="h6 m-0">🚨 Alerts & Governance</h2>
    <div class="d-flex gap-2">
      <?php if ($counts['total'] > 0): ?>
        <span class="badge bg-danger fw-bold">
          <?php echo $counts['total']; ?> Pending
        </span>
      <?php endif; ?>
    </div>
  </div>

  <!-- Alert Summary Cards -->
  <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3 mb-4">
    
    <!-- Pending Approvals -->
    <div class="col">
      <div class="h-100 p-3 border rounded bg-body">
        <div class="small text-primary fw-semibold mb-1">📋 Pending Approvals</div>
        <div class="fs-3 fw-bold text-primary"><?php echo count($pending_users); ?></div>
        <div class="small text-muted mt-1">Schools & Sponsors</div>
        <a href="<?php echo base_url('admin/approve_users.php'); ?>" class="d-inline-block mt-2 fw-semibold small text-decoration-none text-primary">Review →</a>
      </div>
    </div>

    <!-- Flagged Issues -->
    <div class="col">
      <div class="h-100 p-3 border rounded bg-warning-subtle">
        <div class="small text-warning fw-semibold mb-1">⚠️ Flagged Issues</div>
        <div class="fs-3 fw-bold text-warning"><?php echo count($flagged); ?></div>
        <div class="small text-muted mt-1">Data Quality</div>
        <a href="<?php echo base_url('admin/compliance-flags.php'); ?>" class="d-inline-block mt-2 fw-semibold small text-decoration-none text-warning">Review →</a>
      </div>
    </div>

    <!-- Revenue Alerts -->
    <div class="col">
      <div class="h-100 p-3 border rounded bg-success-subtle">
        <div class="small text-success fw-semibold mb-1">💰 Revenue (7d)</div>
        <div class="fs-5 fw-bold text-success">KSH <?php echo number_format(array_reduce($revenue, fn($sum, $r) => $sum + $r['amount'], 0)); ?></div>
        <div class="small text-muted mt-1"><?php echo count($revenue); ?> Sponsorships</div>
      </div>
    </div>

    <!-- Compliance Alerts -->
    <div class="col">
      <div class="h-100 p-3 border rounded bg-danger-subtle">
        <div class="small text-danger fw-semibold mb-1">🔒 Compliance</div>
        <div class="fs-3 fw-bold text-danger"><?php echo $counts['compliance'] ?? 0; ?></div>
        <div class="small text-muted mt-1">MRV & Data Integrity</div>
      </div>
    </div>

  </div>

  <!-- Quick Action Lists -->
  <div class="row g-3">
    
    <!-- Pending Users -->
    <div class="col-12 col-lg-6">
      <div class="card">
        <div class="card-body">
          <h3 class="h6 mb-3 text-dark">Pending User Approvals</h3>
          <?php if (count($pending_users) > 0): ?>
            <ul class="list-unstyled m-0">
          <?php foreach (array_slice($pending_users, 0, 5) as $user): ?>
            <li class="py-2 border-bottom d-flex justify-content-between align-items-center">
              <div>
                <div class="fw-semibold small"><?php echo htmlspecialchars($user['name']); ?></div>
                <div class="small text-muted"><?php echo htmlspecialchars($user['email']); ?></div>
                <div class="text-muted" style="font-size:.75rem">📍 <?php echo ucfirst($user['role']); ?></div>
              </div>
              <a href="<?php echo base_url('admin/approve_users.php?id=' . (int)$user['id']); ?>" class="btn btn-sm btn-primary">
                Approve
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
        <?php if (count($pending_users) > 5): ?>
          <a href="<?php echo base_url('admin/approve_users.php'); ?>" class="d-block mt-3 fw-semibold small text-decoration-none text-primary">
            View All →
          </a>
        <?php endif; ?>
      <?php else: ?>
        <p class="text-muted m-0 small">✓ No pending approvals</p>
      <?php endif; ?>
    </div>

    <!-- Recent Revenue -->
    <div class="col-12 col-lg-6">
      <div class="card">
        <div class="card-body">
          <h3 class="h6 mb-3 text-dark">Recent Sponsorships (7 days)</h3>
          <?php if (count($revenue) > 0): ?>
            <ul class="list-unstyled m-0">
          <?php foreach (array_slice($revenue, 0, 5) as $sp): ?>
            <li class="py-2 border-bottom">
              <div class="fw-semibold small">
                <?php echo htmlspecialchars($sp['organization']); ?> → <?php echo htmlspecialchars($sp['school_name']); ?>
              </div>
              <div class="small fw-bold text-success">
                KSH <?php echo number_format($sp['amount']); ?>
              </div>
              <div class="text-muted" style="font-size:.75rem">
                <?php echo date('M d, Y', strtotime($sp['created_at'])); ?>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p class="text-muted m-0 small">No sponsorships this week</p>
      <?php endif; ?>
    </div>

  </div>

</div>

<style>
.alerts-widget {
  font-family: inherit;
}

.alert-card {
  transition: all 0.2s ease;
}

.alert-card:hover {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transform: translateY(-2px);
}

.badge {
  display: inline-block;
}

@media (max-width: 768px) {
  .alert-summary {
    grid-template-columns: 1fr;
  }
  
  .alert-details {
    grid-template-columns: 1fr;
  }
}
</style>
