<?php
// Shared admin nav
?>
<!-- Base site styles -->
<link rel="stylesheet" href="<?php echo base_url('assets/css/style.css'); ?>">
<!-- Admin theme styles -->
<link rel="stylesheet" href="<?php echo base_url('assets/css/admin.css'); ?>">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="<?php echo base_url('admin/dashboard.php'); ?>">Admin</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarsExample" aria-controls="navbarsExample" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarsExample">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="<?php echo base_url('admin/dashboard.php'); ?>">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo base_url('admin/user-management.php'); ?>">Users</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo base_url('admin/monetization.php'); ?>">💰 Monetization</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo base_url('admin/audit-logs.php'); ?>">📋 Audit Logs</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo base_url('admin/tree-logs.php'); ?>">Tree Logs</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo base_url('admin/leaderboards.php'); ?>">Leaderboards</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo base_url('admin/announcements.php'); ?>">Announcements</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo base_url('admin/reports.php'); ?>">Reports</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo base_url('admin/sponsors.php'); ?>">Sponsors</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo base_url('admin/settings.php'); ?>">Settings</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo base_url('admin/profile.php'); ?>">Profile</a></li>
      </ul>
      <a class="btn btn-outline-light" href="<?php echo base_url('logout.php'); ?>">Logout</a>
    </div>
  </div>
</nav>
