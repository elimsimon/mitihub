<?php
require_once dirname(__DIR__) . '/bootstrap.php';
require_once ROOT_PATH . '/app/auth.php';
require_once ROOT_PATH . '/school/functions.php';
require_role_mod('school');

$user = current_user();
$school_id = school_get_profile_id($user['id'] ?? null);

// Placeholder data
$stats = [
  'points' => 0,
  'badges' => [],
];

$page_title = 'Points & Badges';
$current_page = 'points';
include '_header.php';
?>

<!-- Points & Badges -->
<section class="card">
  <h2 class="section-title">Points & Badges</h2>
  <div class="grid">
    <div class="col-6">
      <h3>Points</h3>
      <p style="font-size:20px; font-weight:600; margin:4px 0;"><span data-points-total>0</span> points <span class="muted small">(<span data-level>—</span>)</span></p>
      <div data-points-breakdown>
        <ul class="muted small">
          <li>Planting: +10</li>
          <li>Adoption: +5</li>
          <li>Survival 6 months: +15</li>
          <li>Survival 1 year: +25</li>
          <li>Health update: +2/update</li>
        </ul>
      </div>
    </div>
    <div class="col-6">
      <h3>Badges</h3>
      <div data-badges>
        <span class="muted small">No badges earned yet.</span>
      </div>
      <ul class="muted small" style="margin-top:8px;">
        <li><strong>Green Starter</strong> – 10 trees</li>
        <li><strong>Top Planter</strong> – 100 trees</li>
        <li><strong>Adoption Hero</strong> – 20 adopted</li>
        <li><strong>Sustainability Star</strong> – 80% survival rate</li>
        <li><strong>Levels</strong> – Bronze/Silver/Gold Planter</li>
      </ul>
    </div>
  </div>
</section>
<script>
  document.addEventListener('DOMContentLoaded', function(){
    if (window.MitiHubSchool && window.MitiHubSchool.loadPointsPage) {
      window.MitiHubSchool.loadPointsPage();
    }
  });
</script>
<?php include '_footer.php'; ?>