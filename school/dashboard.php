<?php
require_once dirname(__DIR__) . '/bootstrap.php';
require_once ROOT_PATH . '/app/auth.php';
require_once ROOT_PATH . '/school/functions.php';
require_role_mod('school');

$user = current_user();
$school_id = school_get_profile_id($user['id'] ?? null);
$list = school_list_sponsorships($user['id']);

// Placeholder stats and data until backend endpoints are implemented
$stats = [
  'trees_planted' => 0,
  'trees_adopted' => 0,
  'survival_rate' => 0.0,
  'points' => 0,
  'badges' => [],
];
$my_planted_trees = [];
$adoptable_trees = [];

$page_title = 'Dashboard';
$current_page = 'dashboard';
include '_header.php';
?>

<!-- Overview -->
<section class="overview" id="overview">
  <h2 class="section-title">Overview</h2>
  <p class="muted">This panel is scoped to your school only. You can plant and adopt trees, update health, view your map, see your ranking, download your reports, and manage your profile.</p>
</section>

<!-- Home -->
<section class="card" id="home">
  <h2 class="section-title">Home Dashboard</h2>
  <p class="muted">School: <strong><?php echo htmlspecialchars($user['school_name'] ?? ''); ?></strong> · Role: <strong>School Admin</strong></p>

  <div class="stats">
    <div class="stat">
      <div class="muted" style="font-size:12px;">Trees Planted</div>
      <div data-stat="trees_planted" style="font-size:22px;font-weight:600;">0</div>
    </div>
    <div class="stat">
      <div class="muted" style="font-size:12px;">Trees Adopted</div>
      <div data-stat="trees_adopted" style="font-size:22px;font-weight:600;">0</div>
    </div>
    <div class="stat">
      <div class="muted" style="font-size:12px;">Survival Rate</div>
      <div data-stat="survival_rate" style="font-size:22px;font-weight:600;">0.0%</div>
    </div>
    <div class="stat">
      <div class="muted" style="font-size:12px;">Points & Badges</div>
      <div data-stat="points" style="font-size:22px;font-weight:600;">0 pts</div>
      <div data-stat="badges"><span class="muted" style="font-size:12px;">No badges yet</span></div>
    </div>
  </div>

  <div class="action-buttons">
    <a class="btn alt" href="mytrees.php" title="Plant a Tree">Plant Tree</a>
    <a class="btn secondary" href="mytrees.php" title="Adopt a Tree">Adopt Tree</a>
    <a class="btn warn" href="qr.php" title="Scan QR Code">Scan QR Code</a>
    <a class="btn" href="map.php" title="Tree Map">Tree Map</a>
  </div>
</section>

<script>
  document.addEventListener('DOMContentLoaded', function(){
    if (window.MitiHubSchool && window.MitiHubSchool.loadDashboard) {
      window.MitiHubSchool.loadDashboard();
    }
  });
</script>
<?php include '_footer.php'; ?>
