<?php
// admin/_navbar.php
if (!isset($user)) {
  require_once __DIR__ . '/../app/auth.php';
  require_auth();
  $user = auth_user();
}
$name = htmlspecialchars($user['name'] ?? 'Admin');
$initial = strtoupper($name[0] ?? 'A');
// derive page title
$script = basename($_SERVER['PHP_SELF']);
$map = [
  'dashboard.php' => 'Dashboard',
  'user-management.php' => 'User Management',
  'reports.php' => 'Reports & Analytics',
  'settings.php' => 'System Settings',
  'announcements.php' => 'Announcements',
  'leaderboards.php' => 'Leaderboards',
  'profile.php' => 'Profile',
  'monetization.php' => 'Monetization',
];
$title = $map[$script] ?? ucfirst(str_replace(['-','.php'], [' ',''],$script));
?>
<div class="top-navbar">
  <div class="d-flex align-items-center gap-3 flex-grow-1">
    <a href="<?php echo base_url('admin/dashboard.php'); ?>" class="d-flex align-items-center text-decoration-none">
      <img src="<?php echo base_url('assets/image/logo_mitihub.png'); ?>" alt="MitiHub" style="height:36px;width:auto;object-fit:contain;" />
    </a>
    <h2 class="m-0 fs-6 text-success ms-2"><?php echo htmlspecialchars($title); ?></h2>
    <form action="<?php echo base_url('admin/reports.php'); ?>" method="get" class="ms-3 d-none d-sm-block" role="search">
      <input type="search" name="q" class="form-control form-control-sm" placeholder="Search users, sponsors, schools..." style="width:320px;max-width:50vw;">
    </form>
  </div>
  <div class="d-flex align-items-center gap-3">
    <div class="profile-dropdown" id="profileDropdown">
      <button class="profile-btn" id="profileBtn">
        <span class="profile-avatar"><?php echo $initial; ?></span>
        <span class="profile-name"><?php echo $name; ?></span>
        <i class="fa fa-chevron-down"></i>
      </button>
      <div class="dropdown-menu" id="profileDropdownMenu">
        <a href="<?php echo base_url('admin/profile.php'); ?>"><i class="fa fa-user"></i> Profile</a>
        <a href="<?php echo base_url('admin/settings.php'); ?>"><i class="fa fa-gear"></i> Settings</a>
        <a href="<?php echo base_url('logout.php'); ?>"><i class="fa fa-sign-out-alt"></i> Logout</a>
      </div>
    </div>
  </div>
</div>
<script>
const profileBtn = document.getElementById('profileBtn');
const profileDropdown = document.getElementById('profileDropdown');
profileBtn.addEventListener('click', function(e) {
  e.stopPropagation();
  profileDropdown.classList.toggle('open');
});
document.addEventListener('click', function(e) {
  if (!profileDropdown.contains(e.target)) {
    profileDropdown.classList.remove('open');
  }
});
</script>
