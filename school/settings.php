<?php
require_once dirname(__DIR__) . '/bootstrap.php';
require_once ROOT_PATH . '/app/auth.php';
require_once ROOT_PATH . '/school/functions.php';
require_role_mod('school');

$user = current_user();
$school_id = school_get_profile_id($user['id'] ?? null);

$page_title = 'Settings';
$current_page = 'settings';
include '_header.php';
?>

<!-- Settings -->
<section class="card">
  <h2 class="section-title">Settings</h2>
  <ul class="muted">
    <li>Profile: name, address, website (Coming Soon)</li>
    <li>Club details: club name, patrons, member count (Coming Soon)</li>
    <li>Logo / profile image (Coming Soon)</li>
    <li>Change password: <a href="<?php echo base_url('public/change_password.php'); ?>">Change Password</a></li>
    <li>Offline sync settings (Coming Soon)</li>
  </ul>
</section>

<?php include '_footer.php'; ?>