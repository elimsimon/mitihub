<?php
require_once dirname(__DIR__) . '/bootstrap.php';
require_once ROOT_PATH . '/app/auth.php';
require_once ROOT_PATH . '/school/functions.php';
require_role_mod('school');

$user = current_user();
$school_id = school_get_profile_id($user['id'] ?? null);

$page_title = 'Reports';
$current_page = 'reports';
include '_header.php';
?>

<!-- Reports -->
<section class="card">
  <h2 class="section-title">Reports</h2>
  <p class="muted small">School-level reports only: planting, adoption, health updates, monthly summaries, points. PDF/CSV + auto-email.</p>
  <div class="action-buttons">
    <a class="btn" href="#" aria-disabled="true">Download PDF (Coming Soon)</a>
    <a class="btn secondary" href="#" aria-disabled="true">Download CSV (Coming Soon)</a>
    <a class="btn alt" href="#" aria-disabled="true">Email Monthly (Coming Soon)</a>
  </div>
</section>

<?php include '_footer.php'; ?>