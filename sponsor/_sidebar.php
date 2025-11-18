<?php
// sponsor/_sidebar.php
// Sidebar navigation for sponsor pages
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<nav class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <span class="sidebar-logo"><i class="fa-solid fa-leaf"></i> MitiHub</span>
    <button class="sidebar-toggle" id="sidebarToggle"><i class="fa fa-bars"></i></button>
  </div>
  <ul class="sidebar-menu">
    <li class="sidebar-item <?php if(basename($_SERVER['PHP_SELF'])=='dashboard.php') echo 'active'; ?>">
      <a href="dashboard.php"><i class="fa fa-gauge"></i> Dashboard</a>
    </li>
    <li class="sidebar-item <?php if(basename($_SERVER['PHP_SELF'])=='payments.php') echo 'active'; ?>">
      <a href="payments.php"><i class="fa fa-credit-card"></i> Payments</a>
    </li>
    <li class="sidebar-item <?php if(basename($_SERVER['PHP_SELF'])=='index.php') echo 'active'; ?>">
      <a href="index.php"><i class="fa fa-home"></i> Home</a>
    </li>
    <li class="sidebar-item <?php if(basename($_SERVER['PHP_SELF'])=='settings.php') echo 'active'; ?>">
      <a href="settings.php"><i class="fa fa-gear"></i> Settings</a>
    </li>
    <li class="sidebar-item <?php if(basename($_SERVER['PHP_SELF'])=='reports.php') echo 'active'; ?>">
      <a href="reports.php"><i class="fa fa-file-alt"></i> Reports</a>
    </li>
    <li class="sidebar-item">
      <a href="../public/logout.php"><i class="fa fa-sign-out-alt"></i> Logout</a>
    </li>
  </ul>
</nav>
<script>
// Sidebar toggle for mobile
const sidebar = document.getElementById('sidebar');
const toggleBtn = document.getElementById('sidebarToggle');
toggleBtn.addEventListener('click', function() {
  sidebar.classList.toggle('collapsed');
});
</script>
