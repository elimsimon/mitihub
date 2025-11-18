<?php
require_once dirname(__DIR__) . '/bootstrap.php';
require_once ROOT_PATH . '/app/auth.php';
require_once ROOT_PATH . '/school/functions.php';
require_role_mod('school');

$user = current_user();
$school_id = school_get_profile_id($user['id'] ?? null);

$page_title = 'Tree Map';
$current_page = 'map';
include '_header.php';
?>

<!-- Tree Map -->
<section class="card">
  <h2 class="section-title">Tree Map</h2>
  <div class="map-container">Interactive map showing trees planted or adopted by your school</div>
  <p class="muted small">Marker popup: Tree ID, Species, Age, Health, Planted/Adopted by THIS school.</p>
</section>

<?php include '_footer.php'; ?>