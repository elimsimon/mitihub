<?php
require_once dirname(__DIR__) . '/bootstrap.php';
require_once ROOT_PATH . '/app/auth.php';
require_once ROOT_PATH . '/school/functions.php';
require_role_mod('school');

$user = current_user();
$school_id = school_get_profile_id($user['id'] ?? null);

$page_title = 'Leaderboard';
$current_page = 'leaderboard';
include '_header.php';
?>

<!-- Leaderboard -->
<section class="card">
  <h2 class="section-title">Leaderboard</h2>
  <p class="muted small">Your rank highlighted. Other schools summarized.</p>
  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th>Rank</th>
          <th>School</th>
          <th>Planted</th>
          <th>Survival Rate</th>
          <th>Adopted</th>
          <th>Badges</th>
        </tr>
      </thead>
      <tbody>
        <tr><td colspan="6" class="muted">Leaderboard data coming soon.</td></tr>
      </tbody>
    </table>
  </div>
</section>

<?php include '_footer.php'; ?>