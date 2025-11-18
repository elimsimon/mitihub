<?php
// admin/_sidebar.php
// Sidebar navigation for admin pages
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="<?php echo base_url('assets/css/admin.css'); ?>">
<nav class="sidebar admin-sidebar" id="sidebar">
  <div class="sidebar-header">
    <span class="sidebar-logo"><i class="fa-solid fa-leaf"></i> <span>MitiHub</span></span>
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar"><i class="fa fa-bars"></i></button>
  </div>
  <ul class="sidebar-menu">
    <li class="sidebar-item <?php if(basename($_SERVER['PHP_SELF'])=='dashboard.php') echo 'active'; ?>">
      <a href="dashboard.php"><i class="fa fa-gauge"></i> <span>Dashboard</span></a>
    </li>
    <li class="sidebar-item <?php if(basename($_SERVER['PHP_SELF'])=='user-management.php') echo 'active'; ?>">
      <a href="user-management.php"><i class="fa fa-users"></i> <span>Users</span></a>
    </li>
    <li class="sidebar-item <?php if(basename($_SERVER['PHP_SELF'])=='announcements.php') echo 'active'; ?>">
      <a href="announcements.php"><i class="fa fa-bullhorn"></i> <span>Announcements</span></a>
    </li>
    <li class="sidebar-item <?php if(basename($_SERVER['PHP_SELF'])=='reports.php') echo 'active'; ?>">
      <a href="reports.php"><i class="fa fa-file-alt"></i> <span>Reports</span></a>
    </li>
    <li class="sidebar-item <?php if(basename($_SERVER['PHP_SELF'])=='leaderboards.php') echo 'active'; ?>">
      <a href="leaderboards.php"><i class="fa fa-trophy"></i> <span>Leaderboards</span></a>
    </li>
    <li class="sidebar-item <?php if(basename($_SERVER['PHP_SELF'])=='monetization.php') echo 'active'; ?>">
      <a href="monetization.php"><i class="fa fa-sack-dollar"></i> <span>Monetization</span></a>
    </li>
    <li class="sidebar-item <?php if(basename($_SERVER['PHP_SELF'])=='tree-logs.php') echo 'active'; ?>">
      <a href="tree-logs.php"><i class="fa-solid fa-tree"></i> <span>Tree Logs</span></a>
    </li>
     <li class="sidebar-item <?php if(basename($_SERVER['PHP_SELF'])=='audit-logs.php') echo 'active'; ?>">
      <a href="audit-logs.php"><i class="fa fa-scroll"></i> <span>Audit Logs</span></a>
    </li>
    <li class="sidebar-item <?php if(basename($_SERVER['PHP_SELF'])=='sponsors.php') echo 'active'; ?>">
      <a href="sponsors.php"><i class="fa fa-handshake"></i> <span>Sponsors</span></a>
    </li>
    <li class="sidebar-item <?php if(basename($_SERVER['PHP_SELF'])=='settings.php') echo 'active'; ?>">
      <a href="settings.php"><i class="fa fa-gear"></i> <span>Settings</span></a>
    </li>
    <li class="sidebar-item">
      <a href="../logout.php"><i class="fa fa-sign-out-alt"></i> <span>Logout</span></a>
    </li>
  </ul>
</nav>
<script>
const sidebar = document.getElementById('sidebar');
const toggleBtn = document.getElementById('sidebarToggle');
function toggleSidebar() {
  // On desktop collapse to icon-only; on mobile slide in/out
  if (window.matchMedia('(max-width: 900px)').matches) {
    sidebar.classList.toggle('open');
  } else {
    sidebar.classList.toggle('collapsed');
  }
}
if (toggleBtn) {
  toggleBtn.addEventListener('click', toggleSidebar);
}
</script>
